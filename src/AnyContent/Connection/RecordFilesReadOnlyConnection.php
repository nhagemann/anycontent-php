<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Connection\Abstracts\AbstractRecordFilesReadOnly;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RecordFilesReadOnlyConnection extends AbstractRecordFilesReadOnly implements ReadOnlyConnection
{

    /**
     * @return int
     * @throws AnyContentClientException
     */
    public function countRecords($contentTypeName = null)
    {

        $folder = $this->getContentTypeConnectionData($contentTypeName, 'folder');

        $finder = new Finder();
        $finder->in($folder)->depth(0);

        return $finder->files()->name('*.json')->count();

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
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

        $folder = $this->getContentTypeConnectionData($contentTypeName, 'folder');

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
            return $this->getContentTypeConnectionData($contentTypeName, 'records');
        }

        $folder = $this->getContentTypeConnectionData($contentTypeName, 'folder');

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

        $this->contentTypes[$contentTypeName]['records'] = $records;

        return $records;

    }

}