<?php

namespace AnyContent\Connection\Interfaces;

interface SimpleReadOnlyConnection
{

    public function getContentTypeNames();


    public function getContentTypes();


    public function selectContentType($contentTypeName);


    public function getContentTypeDefinition($contentTypeName);


    public function getCurrentContentType();


    public function getCurrentContentTypeName();


    public function countRecords($contentTypeName = null);


    public function getAllRecords($contentTypeName = null);


    public function getRecord($recordId);

}