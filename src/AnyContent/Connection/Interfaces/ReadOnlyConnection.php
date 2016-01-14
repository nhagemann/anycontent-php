<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Client\Repository;
use CMDL\ContentTypeDefinition;

interface ReadOnlyConnection
{

    /**
     * @return string[]
     */
    public function getContentTypeNames();


    /**
     * @return ContentTypeDefinition[]
     */
    public function getContentTypes();


    /**
     * @param $contentTypeName
     *
     * @return mixed
     */
    public function hasContentType($contentTypeName);


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

    public function getRecordClassForConfigType($configTypeName);

    /**
     *
     * @return Config
     */
    public function getConfig($configTypeName = null, DataDimensions $dataDimensions = null);


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


    //public function registerRecordClassForContentType($contentTypeName, $classname);

//    public function registerRecordClassForConfigType($configTypeName, $classname);
//
//
//    public function getRecordClassForConfigType($configTypeName);

    /**
     * Check for last content/config or cmdl change within repository or for a distinct content/config type
     *
     * @param null $contentTypeName
     * @param null $configTypeName
     */
    //public function getLastModifiedDate($contentTypeName = null, $configTypeName = null);

}