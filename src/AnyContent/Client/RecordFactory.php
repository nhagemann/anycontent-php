<?php

namespace AnyContent\Client;

use AnyContent\AnyContentClientException;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\DataTypeDefinition;

class RecordFactory
{

    protected $options = [ 'validateProperties' => true ];

    protected $contentRecordClassMap = array();

    protected $configRecordClassMap = array();


    public function __construct($options = [ ])
    {
        $this->options = array_merge($this->options, $options);
    }


    public function getOption($option)
    {
        if (array_key_exists($option, $this->options))
        {
            return $this->options[$option];
        }
        throw new AnyContentClientException('Missing option ' . $option . ' in RecordFactory');
    }


    public function createRecordsFromJSONArray(ContentTypeDefinition $contentTypeDefinition, $jsonRecords, $viewName = "default", $workspace = "default", $language = "default")
    {
        $records = [ ];

        foreach ($jsonRecords as $jsonRecord)
        {
            $record                    = $this->createRecordFromJSONObject($contentTypeDefinition, $jsonRecord, $viewName, $workspace, $language);
            $records[$record->getID()] = $record;
        }

        return $records;
    }


    public function createRecordFromJSONObject(DataTypeDefinition $dataTypeDefinition, $jsonRecord, $viewName = "default", $workspace = "default", $language = "default")
    {
        if ($dataTypeDefinition instanceof ConfigTypeDefinition)
        {
            $classname = $this->getClassForConfigType($dataTypeDefinition->getName());

            /** @var Config $record */
            $record = new $classname($dataTypeDefinition,  $viewName, $workspace, $language);
        }
        else
        {
            $classname = $this->getClassForContentType($dataTypeDefinition->getName());

            /** @var Record $record */
            $record = new $classname($dataTypeDefinition, '', $viewName, $workspace, $language);
            $record->setID($jsonRecord['id']);

            $name = '';

            if (isset($jsonRecord['properties']['name']))
            {
                $name = $jsonRecord['properties']['name'];
            }
        }

        $revision = isset($jsonRecord['info']['revision']) ? $jsonRecord['info']['revision'] : 1;
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

//            $record->setRevisionTimestamp($jsonRecord['info']['revision_timestamp']);
//            $record->setHash($jsonRecord['info']['hash']);
//            $record->setPosition($jsonRecord['info']['position']);
//            $record->setLevelWithinSortedTree($jsonRecord['info']['level']);
//            $record->setParentRecordId($jsonRecord['info']['parent_id']);
            if (isset($jsonRecord['info']['creation']))
            {
                $record->setCreationUserInfo(new UserInfo($jsonRecord['info']['creation']['username'], $jsonRecord['info']['creation']['firstname'], $jsonRecord['info']['creation']['lastname'], $jsonRecord['info']['creation']['timestamp']));
            }
            if (isset($jsonRecord['info']['lastchange']))
            {

                $record->setLastChangeUserInfo(new UserInfo($jsonRecord['info']['lastchange']['username'], $jsonRecord['info']['lastchange']['firstname'], $jsonRecord['info']['lastchange']['lastname'], $jsonRecord['info']['lastchange']['timestamp']));
            }
        }

        return $record;
    }


    public function createRecord(ContentTypeDefinition $contentTypeDefinition, $properties = [ ], $viewName = "default", $workspace = "default", $language = "default")
    {
        $classname = $this->getClassForContentType($contentTypeDefinition->getName());

        /** @var Record $record */
        $record = new $classname($contentTypeDefinition, '', $viewName, $workspace, $language);

        $revision = isset($jsonRecord['revision']) ? $jsonRecord['revision'] : 0;
        $record->setRevision($revision);

        if ($this->getOption('validateProperties') == true)
        {
            foreach ($properties AS $property => $value)
            {
                $record->setProperty($property, $value);
            }
        }
        else
        {
            $record->setProperties($properties);
        }

        return $record;
    }


    public function createConfig(ConfigTypeDefinition $configTypeDefinition, $properties = [ ], $viewName = "default", $workspace = "default", $language = "default")
    {
        $classname = $this->getClassForConfigType($configTypeDefinition->getName());

        /** @var Config $config */
        $config = new $classname($configTypeDefinition, '', $viewName, $workspace, $language);

        $config->setRevision(0);

        if ($this->getOption('validateProperties') == true)
        {
            foreach ($properties AS $property => $value)
            {
                $config->setProperty($property, $value);
            }
        }
        else
        {
            $config->setProperties($properties);
        }

        return $config;
    }


    public function registerRecordClassForContentType($contentTypeName, $classname)
    {

        $this->contentRecordClassMap[$contentTypeName] = $classname;

    }


    public function getClassForContentType($contentTypeName)
    {

        if (array_key_exists($contentTypeName, $this->contentRecordClassMap))
        {
            return $this->contentRecordClassMap[$contentTypeName];

        }

        return 'AnyContent\Client\Record';
    }


    public function registerRecordClassForConfigType($configTypeName, $classname)
    {

        $this->configRecordClassMap[$configTypeName] = $classname;

    }


    public function getClassForConfigType($configTypeName)
    {

        if (array_key_exists($configTypeName, $this->contentRecordClassMap))
        {
            return $this->contentRecordClassMap[$configTypeName];

        }

        return 'AnyContent\Client\Config';
    }
}

