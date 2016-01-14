<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\AbstractRecord;
use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use KVMLogger\KVMLoggerFactory;

class RecordsFileReadOnlyConnection extends AbstractConnection implements ReadOnlyConnection
{

    /**
     * @return RecordsFileConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    public function getCMDLForContentType($contentTypeName)
    {
        $fileName = $this->getConfiguration()->getUriCMDLForContentType($contentTypeName);

        return $this->readCMDL($fileName);
    }


    public function getCMDLForConfigType($configTypeName)
    {
        $fileName = $this->getConfiguration()->getUriCMDLForConfigType($configTypeName);

        return $this->readCMDL($fileName);
    }


    /**
     * @return int
     * @throws AnyContentClientException
     */
    public function countRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        return count($this->getAllRecords($contentTypeName, $dataDimensions));
    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     * @throws AnyContentClientException
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

        if ($this->getConfiguration()->hasContentType($contentTypeName))
        {

            if ($this->hasStashedAllRecords($contentTypeName, $dataDimensions, $this->getRecordClassForContentType($contentTypeName)))
            {
                return $this->getStashedAllRecords($contentTypeName, $dataDimensions, $this->getRecordClassForContentType($contentTypeName));
            }
            $records = $this->getAllMultiViewRecords($contentTypeName, $dataDimensions);

            $records = $this->exportRecords($records, $dataDimensions->getViewName());

            $this->stashAllRecords($records, $dataDimensions);

            return $records;
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     * @throws AnyContentClientException
     */
    protected function getAllMultiViewRecords($contentTypeName = null, DataDimensions $dataDimensions)
    {

        $data = $this->readRecords($this->getConfiguration()->getUriRecords($contentTypeName));

        if ($data)
        {
            $data = json_decode($data, true);

            $data['records'] = array_filter($data['records']);

            $definition = $this->getContentTypeDefinition($contentTypeName);

            $records = $this->getRecordFactory()
                            ->createRecordsFromJSONRecordsArray($definition, $data['records']);

            return $records;
        }

        return [ ];

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

        $records = $this->getAllRecords($contentTypeName, $dataDimensions);

        if (array_key_exists($recordId, $records))
        {
            return $records[$recordId];
        }

        KVMLoggerFactory::instance('anycontent-connection')
                        ->info('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());

        return false;

    }


    protected function getMultiViewRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        $records = $this->getAllMultiViewRecords($contentTypeName, $dataDimensions);

        if (array_key_exists($recordId, $records))
        {
            return $records[$recordId];
        }

        KVMLoggerFactory::instance('anycontent')
                        ->info('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());

        return false;

    }


    protected function mergeExistingRecord(Record $record, DataDimensions $dataDimensions)
    {
        if ($record->getID() != '')
        {
            $existingRecord = $this->getMultiViewRecord($record->getId(), $record->getContentTypeName(), $dataDimensions);
            if ($existingRecord)
            {
                $record->setRevision($existingRecord->getRevision());

                $existingProperties = $existingRecord->getProperties();
                $mergedProperties   = array_merge($existingProperties, $record->getProperties());

                $mergedRecord = clone $record;
                $mergedRecord->setProperties($mergedProperties);

                return $mergedRecord;
            }
        }

        return $record;

    }


    /**
     *
     * @return Config
     */
    public function getConfig($configTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        return $this->exportRecord($this->getMultiViewConfig($configTypeName, $dataDimensions), $dataDimensions->getViewName());

    }


    protected function getMultiViewConfig($configTypeName = null, DataDimensions $dataDimensions)
    {
        $definition = $this->getConfigTypeDefinition($configTypeName);

        $data = $this->readConfig($this->getConfiguration()->getUriConfig($configTypeName, $dataDimensions));

        if ($data)
        {
            $data = json_decode($data, true);

            $config = $this->getRecordFactory()->createRecordFromJSON($definition, $data);

        }
        else
        {
            $config = $this->getRecordFactory()->createConfig($definition);

            KVMLoggerFactory::instance('anycontent-connection')
                            ->info('Config ' . $configTypeName . ' not found');
        }

        return $config;

    }


    protected function mergeExistingConfig(Config $config, DataDimensions $dataDimensions)
    {
        $configTypeName = $config->getConfigTypeName();

        $existingConfig = $this->getMultiViewConfig($configTypeName, $dataDimensions);
        if ($existingConfig)
        {
            $config->setRevision($existingConfig->getRevision());

            $existingProperties = $existingConfig->getProperties();
            $mergedProperties   = array_merge($existingProperties, $config->getProperties());

            $mergedRecord = clone $config;
            $mergedRecord->setProperties($mergedProperties);

            return $mergedRecord;
        }

        return $config;

    }


    protected function fileExists($filename)
    {
        return file_exists($filename);
    }


    protected function readData($fileName)
    {
        if ($this->fileExists($fileName))
        {
            return file_get_contents($fileName);
        }

        KVMLoggerFactory::instance('anycontent-connection')->warning('Could not open file ' . $fileName);

        return false;
    }


    protected function readCMDL($filename)
    {
        return $this->readData($filename);
    }


    protected function readRecord($filename)
    {
        return $this->readData($filename);
    }


    protected function readConfig($filename)
    {
        return $this->readData($filename);
    }


    protected function readRecords($filename)
    {
        return $this->readData($filename);
    }

}