<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Connection\Configuration\RecordFilesConfiguration;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use KVMLogger\KVMLogger;
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

        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName, $dataDimensions);

        $folder = realpath($folder);

        if ($folder)
        {

            $finder = new Finder();
            $finder->in($folder)->depth(0);

            return $finder->files()->name('*.json')->count();
        }

        return 0;

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

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName, $dataDimensions);

        $fileName = $folder . '/' . $recordId . '.json';

        if ($this->fileExists($fileName))
        {
            $data = $this->readRecord($fileName);

            if ($data)
            {
                $data = json_decode($data, true);

                $definition = $this->getContentTypeDefinition($contentTypeName);

                $record = $this->getRecordFactory()
                               ->createRecordFromJSON($definition, $data, $dataDimensions->getViewName(), $dataDimensions->getWorkspace(), $dataDimensions->getLanguage());

                return $this->exportRecord($record,$dataDimensions->getViewName());
            }
        }

        KVMLogger::instance('anycontent-connection')
                        ->info('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());

        return false;

    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     * @throws AnyContentClientException
     */
    protected function getAllMultiViewRecords($contentTypeName = null, DataDimensions $dataDimensions)
    {


        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName, $dataDimensions);

        if (file_exists($folder))
        {
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
                            ->createRecordsFromJSONRecordsArray($definition, $data);

            return $records;

        }



        return [ ];

    }

}