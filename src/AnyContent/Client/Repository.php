<?php

namespace AnyContent\Client;

use AnyContent\AnyContentClientException;
use AnyContent\Client\Util\RecordsFilter;
use AnyContent\Client\Util\RecordsPager;
use AnyContent\Client\Util\RecordsSorter;
use AnyContent\Connection\AbstractConnection;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use AnyContent\Connection\Interfaces\WriteConnection;
use AnyContent\Filter\Interfaces\Filter;
use KVMLogger\KVMLoggerFactory;

class Repository
{

    /** @var  AbstractConnection */
    protected $readConnection;

    /** @var AbstractConnection */
    protected $writeConnection;

    /** @var DataDimensions */
    protected $dataDimensions;

    /** @var  UserInfo */
    protected $userInfo;

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
        $this->userInfo = new UserInfo();

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


    public function selectContentType($contentTypeName, $resetDataDimensions = true)
    {
        $this->readConnection->selectContentType($contentTypeName);

        if ($resetDataDimensions)
        {
            $this->reset();
        }

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


    /**
     * Reset data dimensions to default values (workspace: default, language: default, view: default, no timeshift)
     *
     * @return $this
     */
    public function reset()
    {

        $this->dataDimensions = new DataDimensions($this->getCurrentContentType());

        return $this;
    }


    public function getCurrentDataDimensions()
    {
        if (!$this->dataDimensions)
        {
            return $this->reset();
        }

        return $this->dataDimensions;
    }


    public function createRecord($name = '', $recordId = null)
    {
        /**
         * We use the readConnection, since you might want to create records, even if you're not gonna be able to store them
         *
         * @var Record $record
         */
        $record = $this->readConnection->getRecordFactory()
                                       ->createRecord($this->getContentTypeDefinition(), [ ], $this->getCurrentDataDimensions()
                                                                                                   ->getViewName(), $this->getCurrentDataDimensions()
                                                                                                                         ->getWorkspace(), $this->getCurrentDataDimensions()
                                                                                                                                                ->getLanguage());
        $record->setId($recordId);
        $record->setName($name);

        $userInfo = $this->getCurrentUserInfo();

        $record->setCreationUserInfo($userInfo);
        $record->setLastChangeUserInfo($userInfo);

        return $record;
    }


    /**
     * @param      $recordId
     *
     * @return Record
     */
    public function getRecord($recordId)
    {

        $dataDimensions = $this->getCurrentDataDimensions();

        return $this->readConnection->getRecord($recordId, $this->getCurrentContentTypeName(), $dataDimensions);
    }


    /**
     *
     * @return Record[]
     */
    /**
     * @param string|Filter $filter
     * @param int           $page
     * @param null          $count
     * @param string|Array  $order
     *
     * @return Record[]
     */
    public function getRecords($filter = '', $page = 1, $count = null, $order = [ 'name' ])
    {

        $dataDimensions = $this->getCurrentDataDimensions();

        $records = $this->readConnection->getAllRecords($this->getCurrentContentTypeName(), $dataDimensions);

        if ($filter != '')
        {
            $records = RecordsFilter::filterRecords($records, $filter);
        }

        $records = RecordsSorter::orderRecords($records, $order);

        if ($count != null)
        {
            $records = RecordsPager::sliceRecords($records, $page, $count);
        }

        return $records;
    }


    public function countRecords()
    {

        $dataDimensions = $this->getCurrentDataDimensions();

        return $this->readConnection->countRecords($this->getCurrentContentTypeName(), $dataDimensions);
    }


    public function getSortedRecords($parentId, $includeParent = true, $depth = null)
    {

    }


    public function saveRecord(Record $record, $dataDimensions = null)
    {
        if (!$this->writeConnection)
        {
            throw new AnyContentClientException ('Current connection(s) doesn\'t support write operations.');
        }
        if (!$dataDimensions)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $userInfo = $this->getCurrentUserInfo();

        $record->setLastChangeUserInfo($userInfo);

        $result = $this->writeConnection->saveRecord($record, $dataDimensions);

        KVMLoggerFactory::instance('anycontent-repository')
                        ->info('Saving record ' . $record->getId() . ' for content type ' . $record->getContentTypeName());

        return $result;

    }


    public function registerRecordClassForContentType($contentTypeName, $classname)
    {

        if ($this->hasContentType($contentTypeName))
        {
            $this->contentRecordClassMap[$contentTypeName] = $classname;
            $this->readConnection->registerRecordClassForContentType($contentTypeName, $classname);
            if ($this->writeConnection && $this->readConnection != $this->writeConnection)
            {
                $this->writeConnection->registerRecordClassForContentType($contentTypeName, $classname);
            }

            KVMLoggerFactory::instance('anycontent-repository')
                            ->info('Custom record class ' . $classname . ' for content type ' . $contentTypeName);

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


    /**
     * @return UserInfo
     */
    public function getCurrentUserInfo()
    {
        $this->userInfo->setTimestampToNow();

        return clone $this->userInfo;
    }


    /**
     * @param UserInfo $userInfo
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;
    }

}