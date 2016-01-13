<?php

namespace AnyContent\Cache;

use AnyContent\Client\File;
use AnyContent\Client\Folder;
use AnyContent\Client\Record;
use AnyContent\Client\RecordFactory;
use AnyContent\Client\Repository;
use AnyContent\Filter\Interfaces\Filter;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

class CachingRepository extends Repository
{

    const CACHE_STRATEGY_HEARTBEAT = 1;
    const CACHE_STRATEGY_EXPIRATION = 2;

    /** @var  CacheProvider */
    protected $cacheProvider;

    /*
     * caching realms
     *
     */

    protected $cmdlCaching = false;

    protected $singleContentRecordCaching = false;

    protected $allContentRecordsCaching = false;

    protected $contentQueryRecordsCaching = false;

    protected $contentRecordsForwardCaching = false;

    protected $configRecordCaching = false;

    protected $filesCaching = false;

    /*
     * caching strategies
     */

    protected $contentCachingStrategy = self::CACHE_STRATEGY_HEARTBEAT;


    /**
     * @return Wrapper | CacheProvider
     */
    public function getCacheProvider()
    {
        if (!$this->cacheProvider)
        {
            $this->cacheProvider = new ArrayCache();
        }

        return $this->cacheProvider;
    }


    /**
     * @param CacheProvider $cacheProvider
     */
    public function setCacheProvider($cacheProvider)
    {
        $cacheProvider = new Wrapper($cacheProvider);

        $this->cacheProvider = $cacheProvider;
    }


    /**
     * @return boolean
     */
    public function isCmdlCaching()
    {
        return $this->cmdlCaching;
    }


    public function setCmdlCaching($duration)
    {
        $this->cmdlCaching = $duration;
    }


    /**
     * @return boolean
     */
    public function isSingleContentRecordCaching()
    {
        return $this->singleContentRecordCaching;
    }


    public function setSingleContentRecordCaching($duration)
    {
        $this->singleContentRecordCaching = $duration;
    }


    /**
     * @return boolean
     */
    public function isAllContentRecordsCaching()
    {
        return $this->allContentRecordsCaching;
    }


    public function setAllContentRecordsCaching($duration)
    {
        $this->allContentRecordsCaching = $duration;
    }


    /**
     * @return boolean
     */
    public function isContentQueryRecordsCaching()
    {
        return $this->contentQueryRecordsCaching;
    }


    public function setContentQueryRecordsCaching($duration)
    {
        $this->contentQueryRecordsCaching = $duration;
    }


    /**
     * @return boolean
     */
    public function isContentRecordsForwardCaching()
    {
        return $this->contentRecordsForwardCaching;
    }


    public function setContentRecordsForwardCaching($threshold)
    {
        $this->contentRecordsForwardCaching = $threshold;
    }


    /**
     * @return boolean
     */
    public function isConfigRecordCaching()
    {
        return $this->configRecordCaching;
    }


    public function setConfigRecordCaching($duration)
    {
        $this->configRecordCaching = $duration;
    }


    /**
     * @return boolean
     */
    public function isFilesCaching()
    {
        return $this->filesCaching;
    }


    public function setFilesCaching($duration)
    {
        $this->filesCaching = $duration;
    }


    protected function createCacheKey($namespace, $params = [ ])
    {
        $dataDimensions = $this->getCurrentDataDimensions();
        $cacheKey       = '['.$this->getName().'][' . $namespace . '][' . (string)$dataDimensions . '][' . join(';', $params) . ']';

        return $cacheKey;
    }


    public function createRecord($name = '', $recordId = null)
    {
        return parent::createRecord($name, $recordId);
    }


    /**
     * @param $recordId
     *
     * @return Record
     */
    public function getRecord($recordId)
    {
        if ($this->isSingleContentRecordCaching())
        {
            $cacheKey = $this->createCacheKey('record', [ $this->getCurrentContentTypeName(), $recordId ]);

            $data = $this->getCacheProvider()->fetch($cacheKey);
            if ($data)
            {
                $data = json_decode($data, true);

                $recordFactory = new RecordFactory([ 'validateProperties' => false ]);
                $record        = $recordFactory->createRecordFromJSON($this->getCurrentContentTypeDefinition(), $data);

                return $record;
            }

            $record = parent::getRecord($recordId);

            $data = json_encode($record);

            $this->getCacheProvider()->save($cacheKey, $data, $this->singleContentRecordCaching);

            return $record;
        }

        return parent::getRecord($recordId);
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
    public function getRecords($filter = '', $page = 1, $count = null, $order = [ '.id' ])
    {
        return parent::getRecords($filter, $page, $count, $order);
    }


    public function countRecords($filter = '')
    {
        return parent::countRecords($filter);
    }


    public function getSortedRecords($parentId, $includeParent = false, $depth = null)
    {
        return parent::getSortedRecords($parentId, $includeParent, $depth);
    }


    public function saveRecord(Record $record)
    {
        return parent::saveRecord($record);
    }


    public function saveRecords($records)
    {
        return parent::saveRecords($records);

    }


    public function deleteRecord($recordId)
    {
        return parent::deleteRecord($recordId);
    }


    public function deleteRecords($recordIds)
    {
        return parent::deleteRecord($recordIds);
    }


    /**
     * Updates parent and positiong properties of all records of current content type
     *
     * @param array $sorting array [recordId=>parentId]
     */
    public function sortRecords(array $sorting)
    {
        return parent::sortRecords($sorting);
    }


    public function deleteAllRecords()
    {
        return parent::deleteAllRecords();
    }


    /**
     * @param string $path
     *
     * @return Folder|bool
     */
    public function getFolder($path = '')
    {
        return parent::getFolder($path);
    }


    /**
     * @param $id
     *
     * @return File|bool
     */
    public function getFile($fileId)
    {
        return parent::getFile($fileId);
    }


    public function saveFile($fileId, $binary)
    {
        return parent::saveFile($fileId, $binary);
    }


    public function deleteFile($fileId, $deleteEmptyFolder = true)
    {
        return parent::deleteFile($fileId, $deleteEmptyFolder);
    }


    public function createFolder($path)
    {
        return parent::createFolder($path);
    }


    public function deleteFolder($path, $deleteIfNotEmpty = false)
    {
        return parent::deleteFolder($path, $deleteIfNotEmpty);
    }

}