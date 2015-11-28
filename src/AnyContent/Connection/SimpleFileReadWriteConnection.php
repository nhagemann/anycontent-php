<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Connection\Interfaces\SimpleWriteConnection;
use AnyContent\Connection\Traits\CMDLCache;
use AnyContent\Connection\Traits\CMDLParser;
use AnyContent\Connection\Traits\Factories;
use AnyContent\Connection\Traits\Logger;

use AnyContent\Repository\Record;
use CMDL\ContentTypeDefinition;

use Symfony\Component\Filesystem\Filesystem;

class SimpleFileReadWriteConnection extends SimpleFileReadOnlyConnection implements SimpleWriteConnection
{

    public function saveRecord(Record $record)
    {
        $records = [ $record ];

        return $this->saveRecords($records);
    }


    public function saveRecords(array $records)
    {
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
        }

        $data = json_encode([ 'records' => $allRecords ]);

        if ($this->writeData($this->contentTypes[$this->getCurrentContentTypeName()]['json'], $data))
        {
            $this->contentTypes[$this->getCurrentContentTypeName()]['records']=$allRecords;
            return $record->getID();

        }
        throw new AnyContentClientException('Error when saving record ' . $record->getID() . ' of content type ' . $this->getCurrentContentTypeName());
    }


    public function deleteRecord(Record $record)
    {

    }


    public function deleteRecords(array $records)
    {

    }


    protected function writeData($fileName, $data)
    {
        return file_put_contents($fileName, $data);
    }
}