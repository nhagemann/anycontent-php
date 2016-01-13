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

    /**
     * Items are cached with last modified date of content/config type. Cache doesn't have to be flushed, but last
     * modified dates must be retrieved regulary.
     */
    const CACHE_STRATEGY_HEARTBEAT = 1;

    /**
     * Every save operation leads to a full flash of the cache. Very fast, if you don't have too much
     * write operations. Only eventually consistent, if you have more than one writing client connecting to
     * your repositories.
     */
    const CACHE_STRATEGY_FULL_FLASH = 2;

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
        $cacheKey       = '[' . $this->getName() . '][' . $namespace . '][' . (string)$dataDimensions . '][' . join(';', $params) . ']';

        return $cacheKey;
    }


    protected function flushCache()
    {
        // TODO Check for caching strategy
        // TODO Check if namespaced deleteAll is working too, if so use sub namespaces
        $this->getCacheProvider()->flushAll();
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
        if ($this->isContentQueryRecordsCaching())
        {
            if ($filter != '' || $count != null)
            {
                if (!is_array($order))
                {
                    $order = [ $order ];
                }

                $cacheKey = $this->createCacheKey('records-query', [ $this->getCurrentContentTypeName(), $filter, $page, $count, join(',', $order) ]);

                $data = $this->getCacheProvider()->fetch($cacheKey);
                if ($data)
                {
                    $data = json_decode($data, true);

                    $recordFactory = new RecordFactory([ 'validateProperties' => false ]);
                    $records       = $recordFactory->createRecordsFromJSONRecordsArray($this->getCurrentContentTypeDefinition(), $data);

                    return $records;
                }

                $records = parent::getRecords($filter, $page, $count, $order);

                $data = json_encode($records);

                $this->getCacheProvider()->save($cacheKey, $data, $this->contentQueryRecordsCaching);

                return $records;
            }
        }

        return parent::getRecords($filter, $page, $count, $order);
    }


    protected function getAllRecords()
    {
        if ($this->isAllContentRecordsCaching())
        {
            $cacheKey = $this->createCacheKey('allrecords', [ $this->getCurrentContentTypeName() ]);

            $data = $this->getCacheProvider()->fetch($cacheKey);
            if ($data)
            {
                $data = json_decode($data, true);

                $recordFactory = new RecordFactory([ 'validateProperties' => false ]);
                $records       = $recordFactory->createRecordsFromJSONRecordsArray($this->getCurrentContentTypeDefinition(), $data);

                return $records;
            }

            $records = parent::getAllRecords();

            $data = json_encode($records);

            $this->getCacheProvider()->save($cacheKey, $data, $this->allContentRecordsCaching);

            return $records;
        }

        return parent::getAllRecords();
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
        $this->flushCache();

        return parent::saveRecord($record);
    }


    public function saveRecords($records)
    {
        $this->flushCache();

        return parent::saveRecords($records);

    }


    public function deleteRecord($recordId)
    {
        $this->flushCache();

        return parent::deleteRecord($recordId);
    }


    public function deleteRecords($recordIds)
    {
        $this->flushCache();

        return parent::deleteRecord($recordIds);
    }


    /**
     * Updates parent and positiong properties of all records of current content type
     *
     * @param array $sorting array [recordId=>parentId]
     */
    public function sortRecords(array $sorting)
    {
        $this->flushCache();

        return parent::sortRecords($sorting);
    }


    public function deleteAllRecords()
    {
        $this->flushCache();

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