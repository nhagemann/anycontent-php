<?php

namespace AnyContent\Connection;


use AnyContent\AnyContentClientException;
use AnyContent\Client\Record;
use AnyContent\Connection\Abstracts\AbstractRecordsFileReadWrite;
use AnyContent\Connection\Interfaces\WriteConnection;



class RecordsFileReadWriteConnection extends RecordsFileReadOnlyConnection implements WriteConnection
{
    public function saveRecord(Record $record)
    {
        $records = [ $record ];

        $recordIds = $this->saveRecords($records);
        return array_pop($recordIds);
    }


    /**
     * @param Record[] $records
     *
     * @return mixed
     * @throws AnyContentClientException
     */
    public function saveRecords(array $records)
    {
        $recordIds = [];
        $allRecords = $this->getAllRecords();

        foreach ($records as $record)
        {
            if ($record->getID() == '')
            {
                $nextId = max(array_keys($allRecords)) + 1;
                $record->setID($nextId);
                $record->setRevision(0);
            }

            $record->setRevision($record->getRevision() + 1);
            $record->setRevisionTimestamp(time());
            $allRecords[$record->getID()] = $record;
            $recordIds[]=$record->getID();
        }

        $data = json_encode([ 'records' => $allRecords ],JSON_PRETTY_PRINT);

        if ($this->writeData($this->getConfiguration()->getUriRecords($this->getCurrentContentTypeName()), $data))
        {
            $this->records = $allRecords;

            return $recordIds;

        }
        throw new AnyContentClientException('Error when saving records of content type ' . $this->getCurrentContentTypeName());
    }


    public function deleteRecord($recordId)
    {

        return $this->deleteRecords([ $recordId ]);
    }


    public function deleteRecords(array $recordsIds)
    {
        $result = [ ];

        $allRecords = $this->getAllRecords();

        foreach ($recordsIds as $recordId)
        {
            if (array_key_exists($recordId, $allRecords))
            {
                unset  ($allRecords[$recordId]);
                $this->records = $allRecords;

                $result[] = $recordId;
            }

        }

        if (count($result) > 0)
        {
            $data = json_encode([ 'records' => $allRecords ]);

            if ($this->writeData($this->getConfiguration()->getUriRecords($this->getCurrentContentTypeName()), $data))
            {
                $this->records = $allRecords;

                return $result;

            }
            throw new AnyContentClientException('Error when deleting records of content type ' . $this->getCurrentContentTypeName());
        }

        return $result;
    }


    public function deleteAllRecords()
    {

        $allRecords = $this->getAllRecords();

        $data = json_encode([ 'records' => [ ] ]);

        if ($this->writeData($this->getConfiguration()->getUriRecords($this->getCurrentContentTypeName()), $data))
        {
            $this->records = [ ];

            return array_keys($allRecords);

        }
        throw new AnyContentClientException('Error when deleting records of content type ' . $this->getCurrentContentTypeName());
    }


    protected function writeData($fileName, $data)
    {
        return file_put_contents($fileName, $data);
    }
}