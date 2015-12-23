<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;

interface WriteConnection
{

    public function saveRecord(Record $record, DataDimensions $dataDimensions = null);


    public function saveRecords(array $records, DataDimensions $dataDimensions = null);


    public function deleteRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null);


    public function deleteRecords(array $recordIds, $contentTypeName = null, DataDimensions $dataDimensions = null);


    public function deleteAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null);


    public function registerRecordClassForContentType($contentTypeName, $classname);


    public function getClassForContentType($contentTypeName);

}