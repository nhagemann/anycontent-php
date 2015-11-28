<?php

namespace AnyContent\Repository;

use CMDL\ContentTypeDefinition;

class RecordFactory
{

    protected $options = [ 'validateProperties' => true ];

    protected $contentRecordClassMap = array();


    public function construct($options = [ ])
    {

    }


    public function getOption($option)
    {
        return true;
    }


    public function createRecordsFromJSONArray(ContentTypeDefinition $contentTypeDefinition, $jsonRecords, $viewName = "default", $workspace = "default", $language = "default")
    {
        $records = [ ];

        foreach ($jsonRecords as $jsonRecord)
        {
            $record = $this->createRecordFromJSONObject($contentTypeDefinition, $jsonRecord);
            $records[$record->getID()] = $record;
        }

        return $records;
    }


    public function createRecordFromJSONObject(ContentTypeDefinition $contentTypeDefinition, $jsonRecord, $viewName = "default", $workspace = "default", $language = "default")
    {
        $classname = $this->getClassForContentType($contentTypeDefinition->getName());

        /** @var Record $record */
        $record = new $classname($contentTypeDefinition, $jsonRecord['properties']['name'], $viewName, $workspace, $language);
        $record->setID($jsonRecord['id']);

        $revision = isset($jsonRecord['revision']) ? $jsonRecord['revision'] : 1;
        $record->setRevision($revision);

        if ($this->getOption('validateProperties') == true)
        {
            foreach ($jsonRecord['properties'] AS $property => $value)
            {
                $record->setProperty($property, $value);
            }
        }
        else
        {
            $record->setProperties($jsonRecord['properties']);
        }

        if (isset($jsonRecord['info']))
        {
            $record->setRevisionTimestamp($jsonRecord['info']['revision_timestamp']);
            $record->setHash($jsonRecord['info']['hash']);
            $record->setPosition($jsonRecord['info']['position']);
            $record->setLevelWithinSortedTree($jsonRecord['info']['level']);
            $record->setParentRecordId($jsonRecord['info']['parent_id']);
            $record->setCreationUserInfo(new UserInfo($jsonRecord['info']['creation']['username'], $jsonRecord['info']['creation']['firstname'], $jsonRecord['info']['creation']['lastname'], $jsonRecord['info']['creation']['timestamp']));
            $record->setLastChangeUserInfo(new UserInfo($jsonRecord['info']['lastchange']['username'], $jsonRecord['info']['lastchange']['firstname'], $jsonRecord['info']['lastchange']['lastname'], $jsonRecord['info']['lastchange']['timestamp']));
        }

        return $record;
    }


    public function registerRecordClassForContentType($contentTypeName, $classname)
    {
        if ($this->hasContentType($contentTypeName))
        {
            $this->contentRecordClassMap[$contentTypeName] = $classname;

            return true;
        }

        return false;
    }


    public function getClassForContentType($contentTypeName)
    {
        if (array_key_exists($contentTypeName, $this->contentRecordClassMap))
        {
            return $this->contentRecordClassMap[$contentTypeName];
        }

        return 'AnyContent\Repository\Record';
    }
}

