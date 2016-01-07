<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;

use AnyContent\Client\Util\TimeShifter;
use AnyContent\Connection\Interfaces\WriteConnection;

class MySQLSchemalessReadWriteConnection extends MySQLSchemalessReadOnlyConnection implements WriteConnection
{

    protected $checksContentTypeTableIsUpToDate = [ ];


    public function ensureContentTypeTableIsUpToDate($contentTypeName)
    {
        if (in_array($contentTypeName, $this->checksContentTypeTableIsUpToDate))
        {
            return true;
        }

        $tableName = $this->getTableName($contentTypeName);

        $contentTypeDefinition = $this->getContentTypeDefinition($contentTypeName);

        $sql = 'Show Tables Like ?';

        $stmt = $this->getDatabase()->getConnection()->prepare($sql);
        $stmt->execute(array( $tableName ));

        if ($stmt->rowCount() == 0)
        {

            $sql = <<< TEMPLATE_CONTENTTABLE

        CREATE TABLE %s (
          `id` int(11) unsigned NOT NULL,
          `hash` varchar(32) NOT NULL,
          `property_name` varchar(255) DEFAULT NULL,
          `workspace` varchar(255) NOT NULL DEFAULT 'default',
          `language` varchar(255) NOT NULL DEFAULT 'default',
          `property_subtype` varchar(255) DEFAULT NULL,
          `property_status` varchar(255) DEFAULT '1',
          `property_parent` int(11) DEFAULT NULL,
          `property_position` int(11) DEFAULT NULL,
          `parent_id` int(11) DEFAULT NULL,
          `position` int(11) DEFAULT NULL,
          `position_left` int(11) DEFAULT NULL,
          `position_right` int(11) DEFAULT NULL,
          `position_level` int(11) DEFAULT NULL,
          `revision` int(11) DEFAULT NULL,
          `deleted` tinyint(1) DEFAULT '0',
          `creation_timestamp` int(11) DEFAULT NULL,
          `creation_apiuser` varchar(255) DEFAULT NULL,
          `creation_clientip` varchar(255) DEFAULT NULL,
          `creation_username` varchar(255) DEFAULT NULL,
          `creation_firstname` varchar(255) DEFAULT NULL,
          `creation_lastname` varchar(255) DEFAULT NULL,
          `lastchange_timestamp` int(11) DEFAULT NULL,
          `lastchange_apiuser` varchar(255) DEFAULT NULL,
          `lastchange_clientip` varchar(255) DEFAULT NULL,
          `lastchange_username` varchar(255) DEFAULT NULL,
          `lastchange_firstname` varchar(255) DEFAULT NULL,
          `lastchange_lastname` varchar(255) DEFAULT NULL,
          `validfrom_timestamp` varchar(16) DEFAULT NULL,
          `validuntil_timestamp` varchar(16) DEFAULT NULL,
          KEY `id` (`id`),
          KEY `workspace` (`workspace`,`language`),
          KEY `validfrom_timestamp` (`validfrom_timestamp`,`validuntil_timestamp`,`id`,`deleted`)

         ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TEMPLATE_CONTENTTABLE;

            $sql  = sprintf($sql, $tableName);
            $stmt = $this->getDatabase()->getConnection()->prepare($sql);

            try
            {
                $stmt->execute();
            }
            catch (\PDOException $e)
            {

                throw new AnyContentClientException('Could not create table schema for content type ' . $contentTypeName);
            }

        }

        $sql = sprintf('DESCRIBE %s', $tableName);

        $stmt = $this->getDatabase()->getConnection()->prepare($sql);
        $stmt->execute();

        $fields = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        $properties = array();

        foreach ($contentTypeDefinition->getProperties() as $property)
        {
            $properties[] = 'property_' . $property;
        }

        $newfields = array();
        foreach (array_diff($properties, $fields) as $field)
        {
            $newfields[] = 'ADD COLUMN `' . $field . '` LONGTEXT';
        }

        if (count($newfields) != 0)
        {
            $sql = sprintf('ALTER TABLE %s', $tableName);
            $sql .= ' ' . join($newfields, ',');
            $stmt = $this->getDatabase()->getConnection()->prepare($sql);
            try
            {
                $stmt->execute();
            }
            catch (\PDOException $e)
            {

                throw new AnyContentClientException('Could not update table schema for content type ' . $contentTypeName);
            }
        }

        $this->checksContentTypeTableIsUpToDate[] = $contentTypeName;

        return true;
    }


    public function saveRecord(Record $record, DataDimensions $dataDimensions = null)
    {
        $this->ensureContentTypeTableIsUpToDate($record->getContentTypeName());

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $tableName = $this->getTableName($record->getContentTypeName());

        $repositoryName = $this->getRepository()->getName();

        $contentTypeName = $record->getContentTypeName();

        $record = $this->finalizeRecord($record, $dataDimensions);

        $definition = $record->getContentTypeDefinition();

        $mode = 'insert';
        $record->setRevision(1);

        $values              = [ ];
        $values['revision']  = 1;
        $values['workspace'] = $dataDimensions->getWorkspace();
        $values['language']  = $dataDimensions->getLanguage();
        $values['deleted']   = 0;

        if ($record->getId() != '')
        {
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = ? AND workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <= ? AND validuntil_timestamp > ?';

            $timestamp = TimeShifter::getTimeshiftTimestamp($dataDimensions->getTimeShift());

            $rows = $this->getDatabase()
                         ->fetchAllSQL($sql, [ $record->getId(), $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timestamp, $timestamp ]);

            if (count($rows) == 1)
            {
                $values             = reset($rows);
                $values['revision'] = $values['revision'] + 1;
                $mode               = 'update';
            }

        }

        if ($mode == 'insert' AND $record->getId() == '')
        {
            // update counter for new record

            $sql = 'INSERT INTO _counter_ (repository,content_type,counter) VALUES (? , ? ,1) ON DUPLICATE KEY UPDATE counter=counter+1;';
            $this->getDatabase()->execute($sql, [ $repositoryName, $contentTypeName ]);

            $sql    = 'SELECT counter FROM _counter_ WHERE repository = ? AND content_type = ?';
            $nextId = $this->getDatabase()->fetchColumnSQL($sql, 0, [ $repositoryName, $contentTypeName ]);

            $record->setId($nextId);

            // make sure counter is always at least greater than the largest id, e.g. if the counter row got deleted

            $sql    = 'SELECT MAX(id)+1 FROM ' . $tableName;
            $nextId = $this->getDatabase()->fetchColumnSQL($sql, 0);

            if ($nextId > $record->getId())
            {
                $record->setId($nextId);

                $sql = 'INSERT INTO _counter_ (repository,content_type,counter) VALUES (? , ? ,?) ON DUPLICATE KEY UPDATE counter=?;';
                $this->getDatabase()->execute($sql, [ $repositoryName, $contentTypeName, $nextId, $nextId ]);

            }
        }

        $values['id'] = $record->getId();

        $timeshiftTimestamp = TimeShifter::getTimeshiftTimestamp();

        if ($mode == 'update')
        {
            // invalidate current revision

            $sql      = 'UPDATE ' . $tableName . ' SET validuntil_timestamp = ? WHERE id = ? AND workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <=? AND validuntil_timestamp >?';
            $params   = array();
            $params[] = $timeshiftTimestamp;
            $params[] = $record->getId();
            $params[] = $dataDimensions->getWorkspace();
            $params[] = $dataDimensions->getLanguage();
            $params[] = $timeshiftTimestamp;
            $params[] = $timeshiftTimestamp;

            $this->getDatabase()->execute($sql, $params);

        }

        if ($mode == 'insert')
        {
            $values['creation_timestamp'] = $timeshiftTimestamp;
            $values['creation_username']  = $this->userInfo->getUsername();
            $values['creation_firstname'] = $this->userInfo->getFirstname();
            $values['creation_lastname']  = $this->userInfo->getLastname();
        }

        $values['lastchange_timestamp'] = $timeshiftTimestamp;
        $values['lastchange_username']  = $this->userInfo->getUsername();
        $values['lastchange_firstname'] = $this->userInfo->getFirstname();
        $values['lastchange_lastname']  = $this->userInfo->getLastname();

        $values['validfrom_timestamp']  = $timeshiftTimestamp;
        $values['validuntil_timestamp'] = TimeShifter::getMaxTimestamp();

        foreach ($definition->getViewDefinition($dataDimensions->getViewName())->getProperties() as $property)
        {
            $values['property_' . $property] = $record->getProperty($property);
        }

        $values['parent_id'] = $record->getParent();
        $values['position']  = $record->getPosition();

        // TODO: Rebuild Nested Set On Change, but only if not within saveRecords
        //        if ($mode == 'update')
        //        {
        //
        //            $values['parent_id']      = $row['parent_id'];
        //            $values['position']       = $row['position'];
        //            $values['position_left']  = $row['position_left'];
        //            $values['position_right'] = $row['position_right'];
        //            $values['position_level'] = $row['position_level'];
        //
        //        }

        $this->getDatabase()->insert($tableName, $values);

        return $record->getId();

    }


    /**
     * @param Record[] $records
     *
     * @return mixed
     * @throws AnyContentClientException
     */
    public function saveRecords(array $records, DataDimensions $dataDimensions = null)
    {

        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $recordIds = [ ];
        foreach ($records as $record)
        {
            $recordIds[] = $this->saveRecord($record, $dataDimensions);
        }

        return $recordIds;

    }


    public function deleteRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $tableName = $this->getTableName($contentTypeName);

        $values = [ ];

        // get row of current revision

        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = ? AND workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <= ? AND validuntil_timestamp > ?';

        $timeshiftTimestamp = TimeShifter::getTimeshiftTimestamp($dataDimensions->getTimeShift());

        $rows = $this->getDatabase()
                     ->fetchAllSQL($sql, [ $recordId, $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timeshiftTimestamp, $timeshiftTimestamp ]);

        if (count($rows) == 1)
        {
            $values             = reset($rows);
            $values['revision'] = $values['revision'] + 1;
        }

        // invalidate current revision

        $sql = 'UPDATE ' . $tableName . ' SET validuntil_timestamp = ? WHERE id = ? AND workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <=? AND validuntil_timestamp >?';
        $this->getDatabase()
             ->execute($sql, [ $timeshiftTimestamp, $recordId, $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timeshiftTimestamp, $timeshiftTimestamp ]);

        // copy last revision row and mark record as deleted

        $values['deleted']              = 1;
        $values['lastchange_timestamp'] = $timeshiftTimestamp;
        $values['lastchange_username']  = $this->userInfo->getUsername();
        $values['lastchange_firstname'] = $this->userInfo->getFirstname();
        $values['lastchange_lastname']  = $this->userInfo->getLastname();

        $values['validfrom_timestamp']  = $timeshiftTimestamp;
        $values['validuntil_timestamp'] = TimeShifter::getMaxTimestamp();

        $this->getDatabase()->insert($tableName, $values);

        return $recordId;
    }


    public function deleteRecords(array $recordsIds, $contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }
        if (!$contentTypeName)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        $recordIds = [ ];
        foreach ($recordsIds as $recordId)
        {
            if ($this->deleteRecord($recordId, $contentTypeName, $dataDimensions))
            {
                $recordIds[] = $recordId;
            }
        }

        return $recordIds;

    }


    public function deleteAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }
        if (!$contentTypeName)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }
        $recordIds = [ ];

        $allRecords = $this->getAllRecords($contentTypeName, $dataDimensions);

        foreach ($allRecords as $record)
        {
            if ($this->deleteRecord($record->getId(), $contentTypeName, $dataDimensions))
            {
                $recordIds[] = $record->getId();
            }
        }

        return $recordIds;
    }
}