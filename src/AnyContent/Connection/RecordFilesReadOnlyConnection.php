<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Connection\Configuration\RecordFilesConfiguration;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RecordFilesReadOnlyConnection extends RecordsFileReadOnlyConnection implements ReadOnlyConnection
{

    /**
     * @return RecordFilesConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * @return int
     * @throws AnyContentClientException
     */
    public function countRecords($contentTypeName = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName);

        $finder = new Finder();
        $finder->in($folder)->depth(0);

        return $finder->files()->name('*.json')->count();

    }


    /**
     * @param $recordId
     *
     * @return Record
     * @throws AnyContentClientException
     */
    public function getRecord($recordId)
    {
        $contentTypeName = $this->getCurrentContentTypeName();

        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName);

        $fileName = $folder . '/' . $recordId . '.json';

        if ($this->fileExists($fileName))
        {
            $data = $this->readRecord($fileName);

            if ($data)
            {
                $data = json_decode($data, true);

                $definition = $this->getContentTypeDefinition($contentTypeName);

                $record = $this->getRecordFactory()
                               ->createRecordFromJSONObject($definition, $data);

                return $record;
            }
        }

        throw new AnyContentClientException ('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());
    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     * @throws AnyContentClientException
     */
    public function getAllRecords($contentTypeName = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($this->hasLoadedAllRecords($contentTypeName))
        {
            return $this->records[$contentTypeName];
        }

        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName);

        $finder = new Finder();
        $finder->in($folder)->depth(0);

        $data = [ ];

        /** @var SplFileInfo $file */
        foreach ($finder->files()->name('*.json') as $file)
        {
            $data[] = json_decode($file->getContents(), true);

        }

        $definition = $this->getContentTypeDefinition($contentTypeName);

        $records = $this->getRecordFactory()
                        ->createRecordsFromJSONArray($definition, $data);

        $this->records[$contentTypeName]= $records;

        return $records;

    }

}