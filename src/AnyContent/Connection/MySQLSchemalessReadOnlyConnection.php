<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;

use AnyContent\Client\UserInfo;
use AnyContent\Client\Util\TimeShifter;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use AnyContent\Connection\Util\Database;

use CMDL\Util;

class MySQLSchemalessReadOnlyConnection extends AbstractConnection implements ReadOnlyConnection
{

    /** @var  Database */
    protected $database;


    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }


    /**
     * @param Database $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }


    /**
     * @param $contentTypeName
     *
     * @return string
     */
    public function getCMDLForContentType($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            $sql = 'SELECT cmdl FROM _cmdl_ WHERE repository = ? AND name = ? AND data_type="content"';

            $row = $this->getDatabase()->fetchOneSQL($sql, [ $this->getRepository()->getName(), $contentTypeName ]);

            return $row['cmdl'];

        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

    }


    /**
     * @param $configTypeName
     *
     * @return string
     */
    public function getCMDLForConfigType($configTypeName)
    {
        if ($this->hasConfigType($configTypeName))
        {
            $sql = 'SELECT cmdl FROM _cmdl_ WHERE repository = ? AND name = ? AND data_type="config"';

            $row = $this->getDatabase()->fetchOneSQL($sql, [ $this->getRepository()->getName(), $configTypeName ]);

            return $row['cmdl'];

        }

        throw new AnyContentClientException ('Unknown config type ' . $configTypeName);

    }


    protected function getTableName($contentTypeName)
    {
        $repository = $this->getRepository();

        $tableName = $repository->getName() . '$' . $contentTypeName;

        if ($tableName != Util::generateValidIdentifier($repository->getName()) . '$' . Util::generateValidIdentifier($contentTypeName))
        {
            throw new \Exception ('Invalid repository and/or content type name(s).');
        }

        return $tableName;
    }


    protected function createRecordFromRow($row, $contentTypeName, DataDimensions $dataDimensions)
    {
        $definition = $this->getCurrentContentTypeDefinition();

        $record = $this->getRecordFactory()->createRecord($definition);

        $record->setId($row['id']);

        $properties = [ ];

        foreach ($definition->getProperties($dataDimensions->getViewName()) as $property)
        {
            if (array_key_exists('property_' . $property, $row))
            {
                $properties[$property] = $row['property_' . $property];
            }
        }

        $record->setProperties($properties);

        $record->setRevision($row['revision']);
        $record->setPosition($row['position']);
        $record->setParent($row['parent_id']);
        $record->setLevel($row['position_level']);

        $userInfo = new UserInfo($row['creation_username'], $row['creation_firstname'], $row['creation_lastname'], $row['creation_timestamp']);
        $record->setCreationUserInfo($userInfo);

        $userInfo = new UserInfo($row['lastchange_username'], $row['lastchange_firstname'], $row['lastchange_lastname'], $row['lastchange_timestamp']);
        $record->setLastChangeUserInfo($userInfo);

        return $record;
    }


    /**
     * @param null $contentTypeName
     *
     * @return int
     */
    public function countRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $tableName = $this->getTableName($contentTypeName);

        $sql = 'SELECT COUNT(*) AS C FROM ' . $tableName . ' WHERE workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <= ? AND validuntil_timestamp > ?';

        $timestamp = TimeShifter::getTimeshiftTimestamp($dataDimensions->getTimeShift());

        $row = $this->getDatabase()
                    ->fetchOneSQL($sql, [ $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timestamp, $timestamp ]);

        return $row['C'];

    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     */
    public function getAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $tableName = $this->getTableName($contentTypeName);

        $sql = 'SELECT * FROM ' . $tableName . ' WHERE workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <= ? AND validuntil_timestamp > ?';

        $timestamp = TimeShifter::getTimeshiftTimestamp($dataDimensions->getTimeShift());

        $rows = $this->getDatabase()
                     ->fetchAllSQL($sql, [ $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timestamp, $timestamp ]);

        $records = [ ];
        foreach ($rows as $row)
        {
            $records[$row['id']] = $this->createRecordFromRow($row, $contentTypeName, $dataDimensions);
        }

        return $records;
    }


    /**
     * @param $recordId
     *
     * @return Record
     */
    public function getRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $tableName = $this->getTableName($contentTypeName);

        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = ? AND workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <= ? AND validuntil_timestamp > ?';

        $timestamp = TimeShifter::getTimeshiftTimestamp($dataDimensions->getTimeShift());

        $rows = $this->getDatabase()
                     ->fetchAllSQL($sql, [ $recordId, $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timestamp, $timestamp ]);

        if (count($rows) == 1)
        {
            return $this->createRecordFromRow(reset($rows), $contentTypeName, $dataDimensions);
        }

        return false;

    }

}