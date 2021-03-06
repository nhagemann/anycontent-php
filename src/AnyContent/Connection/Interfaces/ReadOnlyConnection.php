<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Client\Repository;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use Doctrine\Common\Cache\CacheProvider;

interface ReadOnlyConnection
{

    /**
     * @return string[]
     */
    public function getContentTypeNames();


    /**
     * @return string[]
     */
    public function getConfigTypeNames();


    /**
     * @return ContentTypeDefinition[]
     */
    public function getContentTypeDefinitions();


    /**
     * @return ConfigTypeDefinition[]
     */
    public function getConfigTypeDefinitions();


    /**
     * @param $contentTypeName
     *
     * @return mixed
     */
    public function hasContentType($contentTypeName);


    /**
     * @param $contentTypeName
     *
     * @return mixed
     */
    public function hasConfigType($contentTypeName);


    /**
     * @param $contentTypeName
     *
     * @return ReadOnlyConnection
     */
    public function selectContentType($contentTypeName);


    /**
     * @param $contentTypeName
     *
     * @return ContentTypeDefinition
     */
    public function getContentTypeDefinition($contentTypeName);


    /**
     * @return ContentTypeDefinition
     */
    public function getCurrentContentTypeDefinition();


    /**
     * @return string
     */
    public function getCurrentContentTypeName();


    /**
     * @param DataDimensions $dataDimensions
     *
     * @return ReadOnlyConnection
     */
    public function setDataDimensions(DataDimensions $dataDimensions);


    /**
     * @return DataDimensions
     */
    public function getCurrentDataDimensions();


    /**
     * @param null $contentTypeName
     *
     * @return int
     */
    public function countRecords($contentTypeName = null, DataDimensions $dataDimensions = null);


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     */
    public function getAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null);


    /**
     * @param $recordId
     *
     * @return Record
     */
    public function getRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null);


    public function getRecordClassForContentType($contentTypeName);


    /**
     *
     * @return Config
     */
    public function getConfig($configTypeName = null, DataDimensions $dataDimensions = null);


    public function getRecordClassForConfigType($configTypeName);


    /**
     * @param Repository $repository
     */
    public function apply(Repository $repository);


    /**
     * @return Repository
     */
    public function getRepository();


    /**
     * @param Repository $repository
     */
    public function setRepository($repository);


    /**
     * @return bool
     */
    public function hasRepository();



    public function setCacheProvider(CacheProvider $cacheProvider);

    public function enableCMDLCaching($duration = 60);

    /**
     * Check for last content/config or cmdl change within repository or for a distinct content/config type
     *
     * @param null $contentTypeName
     * @param null $configTypeName
     */
    public function getLastModifiedDate($contentTypeName = null, $configTypeName = null, DataDimensions $dataDimensions = null);

}