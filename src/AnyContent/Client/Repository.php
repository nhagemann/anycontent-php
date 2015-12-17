<?php

namespace AnyContent\Client;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use AnyContent\Connection\Interfaces\WriteConnection;

class Repository
{

    /** @var  ReadOnlyConnection */
    protected $readConnection;

    /** @var WriteConnection */
    protected $writeConnection;

    /** @var DataDimensions */
    protected $dataDimensions;

    /**
     * @var string unique identifier extracted from the connection
     */
    protected $id;

    /**
     * @var string human readable title
     */
    protected $title;

    /**
     * @var string custom identifier
     */
    protected $shortcut;

    protected $contentRecordClassMap = [ ];


    public function __construct($readConnection, $writeConnection = null)
    {
        $this->readConnection = $readConnection;
        if ($writeConnection != null)
        {
            $this->writeConnection = $writeConnection;
        }
        elseif ($readConnection instanceof WriteConnection)
        {
            $this->writeConnection = $readConnection;
        }

    }


    /**
     * @return ReadOnlyConnection
     */
    public function getReadConnection()
    {
        return $this->readConnection;
    }


    /**
     * @param ReadOnlyConnection $readConnection
     */
    public function setReadConnection($readConnection)
    {
        $this->readConnection = $readConnection;
    }


    /**
     * @return
     */
    public function getWriteConnection()
    {
        return $this->writeConnection;
    }


    /**
     * @param boolean $writeConnection
     */
    public function setWriteConnection($writeConnection)
    {
        $this->writeConnection = $writeConnection;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * @return string
     */
    public function getShortcut()
    {
        return $this->shortcut;
    }


    /**
     * @param string $shortcut
     */
    public function setShortcut($shortcut)
    {
        $this->shortcut = $shortcut;

        return $this;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }


    public function getContentTypeNames()
    {
        return $this->readConnection->getContentTypeNames();
    }


    /**
     * @return \CMDL\ContentTypeDefinition[]
     */
    public function getContentTypes()
    {
        return $this->readConnection->getContentTypes();
    }


    public function hasContentType($contentTypeName)
    {
        return $this->readConnection->hasContentType($contentTypeName);
    }

    public function getContentTypeDefinition($contentTypeName = null)
    {

        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

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
        $this->getCurrentDataDimensions()->setViewName($viewName);

        return $this;
    }


    public function setDataDimensions(DataDimensions $dataDimensions)
    {
        $this->dataDimensions = $dataDimensions;

        return $this;
    }


    public function selectDataDimensions($workspace, $language = null, $timeshift = null)
    {
        $dataDimension = $this->getCurrentDataDimensions();

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
        $this->getCurrentDataDimensions()->setWorkspace($workspace);

        return $this;
    }


    public function selectLanguage($language)
    {
        $this->getCurrentDataDimensions()->setLanguage($language);

        return $this;
    }


    public function setTimeShift($timeshift)
    {
        $this->getCurrentDataDimensions()->setTimeShift($timeshift);

        return $this;
    }


    public function resetDataDimensions()
    {

        $this->dataDimensions = new DataDimensions($this->getCurrentContentType());

        return $this->dataDimensions;
    }


    public function getCurrentDataDimensions()
    {
        if (!$this->dataDimensions)
        {
            return $this->resetDataDimensions();
        }

        return $this->dataDimensions;
    }


    public function getRecord($recordId, $dataDimensions = null)
    {
        return $this->readConnection->getRecord($recordId, $this->getCurrentContentTypeName(),$dataDimensions);
    }


    /**
     * @param null $dataDimensions
     *
     * @return Record[]
     */
    public function getRecords($dataDimensions = null)
    {


        return $this->readConnection->getAllRecords($this->getCurrentContentTypeName(),$dataDimensions);
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


    /*
    public function getRecordProperties($properties=[],$dataDimensions = null)
    {

    } */

    public function saveRecord($record, $dataDimensions = null)
    {
        return $this->writeConnection->saveRecord($record);
    }


    public function registerRecordClassForContentType($contentTypeName, $classname)
    {

        if ($this->hasContentType($contentTypeName))
        {
            $this->contentRecordClassMap[$contentTypeName] = $classname;
            $this->readConnection->registerRecordClassForContentType($contentTypeName,$classname);
            if ($this->writeConnection & $this->readConnection!=$this->writeConnection)
            {
                $this->writeConnection->registerRecordClassForContentType($contentTypeName,$classname);
            }
            return true;
        }

        return false;
    }


    public function getClassForContentType($contentTypeName)
    {
        if (array_key_exists($contentTypeName, $this->contentRecordClassMap))
        {
            return $this->contentRecordClassMap[$contentTypeName];
        }

        return 'AnyContent\Client\Record';
    }
}