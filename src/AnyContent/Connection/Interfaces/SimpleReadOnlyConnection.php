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


    public function count();


    public function getAllRecords();


    public function getRecord($recordId);

}