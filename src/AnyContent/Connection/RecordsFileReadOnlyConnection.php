<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
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


    public function getCMDL($contentTypeName)
    {
        $fileName = $this->getConfiguration()->getUriCMDL($contentTypeName);

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

            if ($this->hasStashedAllRecords($contentTypeName, $dataDimensions, $this->getClassForContentType($contentTypeName)))
            {
                return $this->getStashedAllRecords($contentTypeName, $dataDimensions, $this->getClassForContentType($contentTypeName));
            }

            $records = $this->exportRecords($this->getAllMultiViewRecords($contentTypeName,$dataDimensions),$dataDimensions->getViewName());

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

            $data['records']=array_filter($data['records']);

            $definition = $this->getContentTypeDefinition($contentTypeName);

            $records = $this->getRecordFactory()
                            ->createRecordsFromJSONArray($definition, $data['records']);

            return $records;
        }

        return [ ];

    }


    /**
     * @param $recordId
     *
     * @return Record
     * @throws AnyContentClientException
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

        KVMLoggerFactory::instance('anycontent')
                        ->info('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());

        return false;

    }




//    /**
//     * @param $recordId
//     *
//     * @return Record
//     * @throws AnyContentClientException
//     */
//    public function getRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null)
//    {
//        return $this->exportRecord($this->getMultiViewRecord($recordId, $contentTypeName, $dataDimensions));
//
//    }
//
//
    protected function getMultiViewRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        $records = $this->getAllMultiViewRecords($contentTypeName,$dataDimensions);

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
            $existingRecord = $this->getMultiViewRecord($record->getId(),$record->getContentTypeName(),$dataDimensions);
            if ($existingRecord)
            {
                $record->setRevision($existingRecord->getRevision());



                $existingProperties = $existingRecord->getProperties();
                $mergedProperties = array_merge($existingProperties,$record->getProperties());

                $mergedRecord = clone $record;
                $mergedRecord->setProperties($mergedProperties);

                return $mergedRecord;
            }
        }

        return $record;

    }


    /**
     * Make sure the returned record is not connected to stashed records an does only contain properties of it's
     * current view
     *
     * @param Record $record - multi view record !
     */
    protected function exportRecord(Record $record,$viewName)
    {
        $definition        = $record->getContentTypeDefinition();
        $allowedProperties = $definition->getProperties($viewName);

        $allowedProperties = array_combine($allowedProperties, $allowedProperties);

        $allowedProperties = array_intersect_key($record->getProperties(), $allowedProperties);

        $record = clone $record;
        $record->setProperties($allowedProperties);

        return $record;
    }


    protected function exportRecords($records,$viewName)
    {
        $result = [ ];
        foreach ($records as $record)
        {
            $result[$record->getId()] = $this->exportRecord($record,$viewName);
        }

        return $result;
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

        KVMLoggerFactory::instance('anycontent')->warning('Could not open file ' . $fileName);

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


    protected function readRecords($filename)
    {
        return $this->readData($filename);
    }

}