<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;

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

        if ($this->getConfiguration()->hasContentType($contentTypeName))
        {

            if (array_key_exists($contentTypeName, $this->records))
            {
                return $this->records[$contentTypeName];
            }

            $data = $this->readRecords($this->getConfiguration()->getUriRecords($contentTypeName));

            if ($data)
            {
                $data = json_decode($data, true);

                $definition = $this->getContentTypeDefinition($contentTypeName);

                $records = $this->getRecordFactory()
                                ->createRecordsFromJSONArray($definition, $data['records']);

                $this->records[$contentTypeName] = $records;

                return $records;
            }

        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

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

        $records = $this->getAllRecords($contentTypeName);

        if (array_key_exists($recordId, $records))
        {
            return $records[$recordId];
        }

        throw new AnyContentClientException ('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());
    }


    protected function fileExists($filename)
    {
        return file_exists($filename);
    }


    protected function readData($fileName)
    {
        return file_get_contents($fileName);
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