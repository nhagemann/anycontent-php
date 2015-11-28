<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Repository\Record;

interface SimpleWriteConnection
{

    public function saveRecord(Record $record);


    public function saveRecords(array $allRecords);


    public function deleteRecord(Record $record);


    public function deleteRecords(array $records);

}