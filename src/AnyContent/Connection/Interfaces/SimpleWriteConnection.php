<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Repository\Record;

interface SimpleWriteConnection
{

    public function saveRecord(Record $record);


    public function saveRecords(array $records);


    public function deleteRecord($recordId);


    public function deleteRecords(array $recordIds);


    public function deleteAllRecords();

}