<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\Record;
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
    public function getCurrentContentType();


    /**
     * @return string
     */
    public function getCurrentContentTypeName();


    /**
     * @param null $contentTypeName
     *
     * @return int
     */
    public function countRecords($contentTypeName = null);


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     */
    public function getAllRecords($contentTypeName = null);


    /**
     * @param $recordId
     *
     * @return Record
     */
    public function getRecord($recordId);

}