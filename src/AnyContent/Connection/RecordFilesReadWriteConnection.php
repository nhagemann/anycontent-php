<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;

use AnyContent\Connection\Interfaces\WriteConnection;
use Symfony\Component\Filesystem\Filesystem;

class RecordFilesReadWriteConnection extends RecordFilesReadOnlyConnection implements WriteConnection
{

    public function saveRecord(Record $record, DataDimensions $dataDimensions = null)
    {

        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        if ($record->getID() == '')
        {
            $nextId = 1;
            if (count($this->getAllRecords($record->getContentTypeName(), $dataDimensions)) > 0)
            {
                $nextId = max(array_keys($this->getAllRecords())) + 1;
            }
            $record->setID($nextId);
            $record->setRevision(0);
        }

        $record->setRevision($record->getRevision() + 1);
        //$record->setRevisionTimestamp(time());

        $filename = $this->getConfiguration()
                         ->getFolderNameRecords($record->getContentTypeName(), $dataDimensions);
        $filename .= '/' . $record->getID() . '.json';

        $data = json_encode($record, JSON_PRETTY_PRINT);

        $this->stashRecord($record, $dataDimensions);

        if (!$this->writeData($filename, $data))
        {
            throw new AnyContentClientException('Error when saving record of content type ' . $record->getContentTypeName());
        }

        return $record->getID();
    }


    /**
     * @param Record[] $records
     *
     * @return mixed
     * @throws AnyContentClientException
     */
    public function saveRecords(array $records, DataDimensions $dataDimensions = null)
    {
        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $recordIds = [ ];
        foreach ($records as $record)
        {
            $recordIds[] = $this->saveRecord($record, $dataDimensions);
        }

        return $recordIds;

    }


    public function deleteRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null)
    {

        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }
        if (!$contentTypeName)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        $filename = realpath($this->getConfiguration()
                                  ->getFolderNameRecords($contentTypeName, $dataDimensions));
        $filename .= '/' . $recordId . '.json';

        $this->unstashRecord($contentTypeName, $recordId, $dataDimensions);

        if ($this->deleteData($filename))
        {
            return $recordId;
        }

        return false;
    }


    public function deleteRecords(array $recordsIds, $contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }
        if (!$contentTypeName)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        $recordIds = [ ];
        foreach ($recordsIds as $recordId)
        {
            if ($this->deleteRecord($recordId, $contentTypeName, $dataDimensions))
            {
                $recordIds[] = $recordId;
            }
        }

        return $recordIds;

    }


    public function deleteAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }
        if (!$contentTypeName)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }
        $recordIds = [ ];

        $allRecords = $this->getAllRecords($contentTypeName, $dataDimensions);

        foreach ($allRecords as $record)
        {
            if ($this->deleteRecord($record->getId(), $contentTypeName, $dataDimensions))
            {
                $recordIds[] = $record->getId();
            }
        }

        return $recordIds;
    }


    protected function writeData($fileName, $data)
    {
        $fs = new Filesystem();

        $dir = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!file_exists($dir))
        {
            $fs->mkdir($dir);
        }

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