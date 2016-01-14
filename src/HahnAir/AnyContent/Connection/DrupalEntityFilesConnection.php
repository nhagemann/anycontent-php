<?php

namespace HahnAir\AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;

use AnyContent\Connection\Interfaces\WriteConnection;
use AnyContent\Connection\RecordFilesReadWriteConnection;
use HahnAir\AnyContent\Connection\Mapper\Mapper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DrupalEntityFilesConnection extends RecordFilesReadWriteConnection
{

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

                $record = $this->getRecordFactory()->createRecord($definition);

                $classNameForMapper = 'HahnAir\AnyContent\Connection\Mapper\\'. ucfirst($contentTypeName);

                /** @var Mapper $mapper */
                $mapper = new $classNameForMapper;

                $mapper->mapEntity($record,$data);

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
    public function getAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        $records = [ ];

        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }
        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        if ($this->hasStashedAllRecords($contentTypeName,$dataDimensions,$this->getRecordClassForContentType($contentTypeName)))
        {
            return $this->getStashedAllRecords($contentTypeName,$dataDimensions,$this->getRecordClassForContentType($contentTypeName));
        }

        $folder = $this->getConfiguration()->getFolderNameRecords($contentTypeName, $dataDimensions);

        if (file_exists($folder))
        {
            $finder = new Finder();
            $finder->in($folder)->depth(0);

            $records = [];

            /** @var SplFileInfo $file */
            foreach ($finder->files()->name('*.json') as $file)
            {
                $records[] = $this->getRecord($file->getBasename('.json'),$contentTypeName,$dataDimensions);

            }

        }
        $this->stashAllRecords($records,$dataDimensions);

        return $records;

    }


//    public function saveRecord(Record $record)
//    {
//
//        if ($record->getID() == '')
//        {
//            $nextId = 1;
//            if (count($this->getAllRecords()) > 0)
//            {
//                $nextId = max(array_keys($this->getAllRecords())) + 1;
//            }
//            $record->setID($nextId);
//            $record->setRevision(0);
//        }
//
//        $record->setRevision($record->getRevision() + 1);
//        $record->setRevisionTimestamp(time());
//
//        $filename = $this->getConfiguration()
//                         ->getFolderNameRecords($this->getCurrentContentTypeName(), $this->getCurrentDataDimensions());
//        $filename .= '/' . $record->getID() . '.json';
//
//        $data = json_encode($record, JSON_PRETTY_PRINT);
//
//        $this->stashRecord($record,$this->getCurrentDataDimensions());
//
//        if (!$this->writeData($filename, $data))
//        {
//            throw new AnyContentClientException('Error when saving record of content type ' . $this->getCurrentContentTypeName());
//        }
//
//        return $record->getID();
//    }
//
//
//    /**
//     * @param Record[] $records
//     *
//     * @return mixed
//     * @throws AnyContentClientException
//     */
//    public function saveRecords(array $records)
//    {
//        $recordIds = [ ];
//        foreach ($records as $record)
//        {
//            $recordIds[] = $this->saveRecord($record);
//        }
//
//        return $recordIds;
//
//    }
//
//
//    public function deleteRecord($recordId)
//    {
//
//        $filename = realpath($this->getConfiguration()
//                                  ->getFolderNameRecords($this->getCurrentContentTypeName(), $this->getCurrentDataDimensions()));
//        $filename .= '/' . $recordId . '.json';
//
//        $this->unstashRecord($this->getCurrentContentTypeName(),$recordId,$this->getCurrentDataDimensions());
//
//        if ($this->deleteData($filename))
//        {
//            return $recordId;
//        }
//
//        return false;
//    }
//
//
//    public function deleteRecords(array $recordsIds)
//    {
//        $recordIds = [ ];
//        foreach ($recordsIds as $recordId)
//        {
//            if ($this->deleteRecord($recordId))
//            {
//                $recordIds[] = $recordId;
//            }
//        }
//
//        return $recordIds;
//
//    }
//
//
//    public function deleteAllRecords()
//    {
//
//        $recordIds = [ ];
//
//        $allRecords = $this->getAllRecords();
//
//        foreach ($allRecords as $record)
//        {
//            if ($this->deleteRecord($record->getId()))
//            {
//                $recordIds[] = $record->getId();
//            }
//        }
//
//        return $recordIds;
//    }
//
//
//    protected function writeData($fileName, $data)
//    {
//        $fs = new Filesystem();
//
//        $dir = pathinfo($fileName, PATHINFO_DIRNAME);
//        if (!file_exists($dir))
//        {
//            $fs->mkdir($dir);
//        }
//
//        var_dump($fileName);
//
//        return file_put_contents($fileName, $data);
//    }
//
//
//    protected function deleteData($fileName)
//    {
//        if (file_exists($fileName))
//        {
//            return (unlink($fileName));
//        }
//
//        return false;
//    }
}