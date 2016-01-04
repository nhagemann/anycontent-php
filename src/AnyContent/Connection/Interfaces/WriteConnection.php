<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

interface WriteConnection
{

    /**
     * @param UserInfo $userInfo
     */
    public function setUserInfo($userInfo);


    public function saveRecord(Record $record, DataDimensions $dataDimensions = null);


    public function saveRecords(array $records, DataDimensions $dataDimensions = null);


    public function deleteRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null);


    public function deleteRecords(array $recordIds, $contentTypeName = null, DataDimensions $dataDimensions = null);


    public function deleteAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null);


//    public function registerRecordClassForContentType($contentTypeName, $classname);
//
//
//    public function getClassForContentType($contentTypeName);

}