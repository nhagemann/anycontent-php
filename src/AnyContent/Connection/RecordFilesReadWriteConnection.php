<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\Record;

use AnyContent\Connection\Interfaces\WriteConnection;
use Symfony\Component\Filesystem\Filesystem;

class RecordFilesReadWriteConnection extends RecordFilesReadOnlyConnection //implements WriteConnection
{

    public function saveRecord(Record $record)
    {

        if ($record->getID() == '')
        {
            $nextId = 1;
            if (count($this->getAllRecords()) > 0)
            {
                $nextId = max(array_keys($this->getAllRecords())) + 1;
            }
            $record->setID($nextId);
            $record->setRevision(0);
        }

        $record->setRevision($record->getRevision() + 1);
        $record->setRevisionTimestamp(time());

        $filename = $this->getConfiguration()
                         ->getFolderNameRecords($this->getCurrentContentTypeName(), $this->getDataDimensions());
        $filename .= '/' . $record->getID() . '.json';

        $data = json_encode($record, JSON_PRETTY_PRINT);

        if ($this->hasLoadedAllRecords($this->getCurrentContentTypeName()))
        {
            $this->records[$this->getCurrentContentTypeName()][$record->getID()] = $record;
        }

        if (!$this->writeData($filename, $data))
        {
            throw new AnyContentClientException('Error when saving record of content type ' . $this->getCurrentContentTypeName());
        }

        return $record->getID();
    }


    /**
     * @param Record[] $records
     *
     * @return mixed
     * @throws AnyContentClientException
     */
    public function saveRecords(array $records)
    {
        $recordIds = [ ];
        foreach ($records as $record)
        {
            $recordIds[] = $this->saveRecord($record);
        }

        return $recordIds;

    }


    public function deleteRecord($recordId)
    {

        $filename = realpath($this->getConfiguration()
                                  ->getFolderNameRecords($this->getCurrentContentTypeName(), $this->getDataDimensions()));
        $filename .= '/' . $recordId . '.json';

        if ($this->hasLoadedAllRecords($this->getCurrentContentTypeName()))
        {
            unset ($this->records[$this->getCurrentContentTypeName()][$recordId]);
        }

        if ($this->deleteData($filename))
        {
            return $recordId;
        }

        return false;
    }


    public function deleteRecords(array $recordsIds)
    {
        $recordIds = [ ];
        foreach ($recordsIds as $recordId)
        {
            if ($this->deleteRecord($recordId))
            {
                $recordIds[] = $recordId;
            }
        }

        return $recordIds;

    }


    public function deleteAllRecords()
    {

        $recordIds = [ ];

        $allRecords = $this->getAllRecords();

        foreach ($allRecords as $record)
        {
            if ($this->deleteRecord($record->getId()))
            {
                $recordIds[] = $record->getId();
            }
        }

        return $recordIds;
    }


    protected function writeData($fileName, $data)
    {
        $fs = new Filesystem();

        $dir = pathinfo($fileName,PATHINFO_DIRNAME);
        if (!file_exists($dir))
        {
            $fs->mkdir($dir);
        }

        var_dump ($fileName);

        return file_put_contents($fileName, $data);
    }


    protected function deleteData($fileName)
    {
        if (file_exists($fileName))
        {
            return (unlink($fileName));
        }

        return false;
    }
}