<?php

namespace AnyContent\Client;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;

class Repository
{

    /** @var  ReadOnlyConnection */
    protected $readConnection;

    protected $writeConnection = false;

    /** @var DataDimensions */
    protected $dataDimensions;


    public function __construct($readConnection, $writeConnection = null)
    {
        $this->readConnection = $readConnection;
    }


    public function getContentTypeNames()
    {
        return $this->readConnection->getContentTypeNames();
    }


    public function getContentTypes()
    {
        return $this->readConnection->getContentTypes();
    }


    public function getContentTypeDefinition($contentTypeName)
    {
        return $this->readConnection->getContentTypeDefinition($contentTypeName);
    }


    public function getCurrentContentType()
    {
        return $this->readConnection->getCurrentContentType();
    }


    public function getCurrentContentTypeName()
    {
        return $this->readConnection->getCurrentContentTypeName();
    }


    public function countRecords()
    {
        return $this->readConnection->countRecords($this->getCurrentContentTypeName());
    }


    public function selectContentType($contentTypeName)
    {
        $this->readConnection->selectContentType($contentTypeName);

        return $this;
    }


    public function selectView($viewName)
    {
        $this->getDataDimensions()->setViewName($viewName);

        return $this;
    }


    public function setDataDimensions(DataDimensions $dataDimensions)
    {
        $this->dataDimensions = $dataDimensions;

        return $this;
    }


    public function selectDataDimensions($workspace, $language = null, $timeshift = null)
    {
        $dataDimension = $this->getDataDimensions();

        $dataDimension->setWorkspace($workspace);
        if ($language !== null)
        {
            $dataDimension->setLanguage($language);
        }
        if ($timeshift !== null)
        {
            $dataDimension->setTimeShift($timeshift);
        }

        return $this;

    }


    public function selectWorkspace($workspace)
    {
        $this->getDataDimensions()->setWorkspace($workspace);

        return $this;
    }


    public function selectLanguage($language)
    {
        $this->getDataDimensions()->setLanguage($language);

        return $this;
    }


    public function setTimeShift($timeshift)
    {
        $this->getDataDimensions()->setTimeShift($timeshift);

        return $this;
    }


    public function resetDataDimensions()
    {

        $this->dataDimensions = new DataDimensions($this->getCurrentContentType());

        return $this->dataDimensions;
    }


    public function getDataDimensions()
    {
        if (!$this->dataDimensions)
        {
            return $this->resetDataDimensions();
        }

        return $this->dataDimensions;
    }


    public function getRecord($recordId, $dataDimensions = null)
    {
        return $this->readConnection->getRecord($recordId, $dataDimensions);
    }


    public function getRecords($dataDimensions = null)
    {
        return $this->readConnection->getAllRecords();
    }


    public function getPaginatedRecords($page = 1, $count = 100, $dataDimensions = null)
    {
        return $this->readConnection->getAllRecords($this->getCurrentContentTypeName());
    }


    public function getFilteredRecords($filter, $count, $page, $dataDimensions = null)
    {

    }


    public function getSortedRecords($parentId, $includeParent = true, $depth = null, $dataDimensions = null)
    {

    }

}