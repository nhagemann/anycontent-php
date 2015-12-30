<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
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
                               ->createRecordFromJSONObject($definition, $data, $dataDimensions->getViewName(), $dataDimensions->getWorkspace(), $dataDimensions->getTimeShift());

                return $this->exportRecord($record,$dataDimensions->getViewName());
            }
        }

        // upgrade decide Exception vs false
        return false;
        throw new AnyContentClientException ('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());
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
                            ->createRecordsFromJSONArray($definition, $data);

            return $records;

        }



        return [ ];

    }

//
//    /**
//     * @param null $contentTypeName
//     *
//     * @return Record[]
//     * @throws AnyContentClientException
//     */
//    public function getAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
//    {
//        $records = [ ];
//
//        if ($contentTypeName == null)
//        {
//            $contentTypeName = $this->getCurrentContentTypeName();
//        }
//        if ($dataDimensions == null)
//        {
//            $dataDimensions = $this->getCurrentDataDimensions();
//        }
//
//        if ($this->hasStashedAllRecords($contentTypeName, $dataDimensions, $this->getClassForContentType($contentTypeName)))
//        {
//            return $this->getStashedAllRecords($contentTypeName, $dataDimensions, $this->getClassForContentType($contentTypeName));
//        }
//
//        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName, $dataDimensions);
//
//        if (file_exists($folder))
//        {
//            $finder = new Finder();
//            $finder->in($folder)->depth(0);
//
//            $data = [ ];
//
//            /** @var SplFileInfo $file */
//            foreach ($finder->files()->name('*.json') as $file)
//            {
//                $data[] = json_decode($file->getContents(), true);
//
//            }
//
//            $definition = $this->getContentTypeDefinition($contentTypeName);
//
//            $records = $this->getRecordFactory()
//                            ->createRecordsFromJSONArray($definition, $data);
//
//        }
//        $this->stashAllRecords($records, $dataDimensions);
//
//        return $records;
//
//    }

}