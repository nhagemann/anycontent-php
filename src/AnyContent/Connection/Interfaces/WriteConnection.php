<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\Record;

interface WriteConnection
{

    public function saveRecord(Record $record);


    public function saveRecords(array $records);


    public function deleteRecord($recordId);


    public function deleteRecords(array $recordIds);


    public function deleteAllRecords();


    public function registerRecordClassForContentType($contentTypeName, $classname);


    public function getClassForContentType($contentTypeName);

}