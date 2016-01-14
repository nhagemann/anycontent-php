<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Client\UserInfo;

interface WriteConnection extends ReadOnlyConnection
{

    /**
     * @param $userInfo
     *
     * @return $this
     */
    public function setUserInfo($userInfo);


    /**
     * @param Record              $record
     * @param DataDimensions|null $dataDimensions
     *
     * @return int
     */
    public function saveRecord(Record $record, DataDimensions $dataDimensions = null);


    /**
     * @param array               $records
     * @param DataDimensions|null $dataDimensions
     *
     * @return int[]
     */
    public function saveRecords(array $records, DataDimensions $dataDimensions = null);


    /**
     * @param                     $recordId
     * @param null                $contentTypeName
     * @param DataDimensions|null $dataDimensions
     *
     * @return int|bool
     */
    public function deleteRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null);


    /**
     * @param array               $recordIds
     * @param null                $contentTypeName
     * @param DataDimensions|null $dataDimensions
     *
     * @return int[]
     */
    public function deleteRecords(array $recordIds, $contentTypeName = null, DataDimensions $dataDimensions = null);


    public function deleteAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null);


    public function saveConfig(Config $config, DataDimensions $dataDimensions = null);


}