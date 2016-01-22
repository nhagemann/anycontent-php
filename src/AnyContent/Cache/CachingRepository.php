<?php

namespace AnyContent\Cache;

use AnyContent\Client\Config;
use AnyContent\Client\File;
use AnyContent\Client\Folder;
use AnyContent\Client\Record;
use AnyContent\Client\RecordFactory;
use AnyContent\Client\Repository;
use AnyContent\Filter\Interfaces\Filter;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ChainCache;

class CachingRepository extends Repository
{

    /**
     * Items are cached with last modified date of content/config type. Cache doesn't have to be flushed, but last
     * modified dates must be retrieved regulary.
     */
    const CACHE_STRATEGY_LASTMODIFIED = 1;

    /**
     * Every save operation leads to a full flash of the cache. Very fast, if you don't have too much
     * write operations. Only eventually consistent, if you have more than one writing client connecting to
     * your repositories.
     */
    const CACHE_STRATEGY_EXPIRATION = 2;

    protected $cacheStrategy = self::CACHE_STRATEGY_EXPIRATION;

    protected $duration = 300;

    protected $confidence = 0;

    /** @var  CacheProvider */
    protected $cacheProvider;

    protected $cmdlCaching = false;

    protected $singleContentRecordCaching = false;

    protected $allContentRecordsCaching = false;

    protected $contentQueryRecordsCaching = false;

    protected $contentRecordsForwardCaching = false;

    protected $configRecordCaching = false;

    protected $filesCaching = false;

    protected $lastModified = 0;


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
        $namespace = '[<>]' . rtrim($this->getName() . '|' . $cacheProvider->getNamespace(), '|') . '[<>]';

        $arrayCache = new ArrayCache();

        $cacheChain = new ChainCache([ $arrayCache, $cacheProvider ]);

        $cacheProvider = new Wrapper($cacheChain);

        $cacheProvider->setNamespace($namespace);

        $this->cacheProvider = $cacheProvider;

        $this->readConnection->setCacheProvider($cacheProvider);

        if ($this->writeConnection)
        {
            $this->writeConnection->setCacheProvider($cacheProvider);
        }
    }


    public function selectExpirationCacheStrategy($duration = 300)
    {
        $this->cacheStrategy = self::CACHE_STRATEGY_EXPIRATION;
        $this->duration      = 300;
    }


    public function selectLastModifiedCacheStrategy($confidence = 0)
    {
        $this->cacheStrategy = self::CACHE_STRATEGY_LASTMODIFIED;
        $this->confidence    = 0;
    }


    public function hasLastModifiedCacheStrategy()
    {
        return $this->cacheStrategy == self::CACHE_STRATEGY_LASTMODIFIED ? true : false;
    }


    public function hasExpirationCacheStrategy()
    {

        return $this->cacheStrategy == self::CACHE_STRATEGY_EXPIRATION ? true : false;
    }


    /**
     * @return boolean
     */
    public function isCmdlCaching()
    {
        return $this->cmdlCaching;
    }


    /**
     * Allow connection to cache CMDL definitions. Adjustable via duration if you're not sure how likely CMDL changes occur.
     *
     * @param $duration
     */
    public function enableCmdlCaching($duration = 60)
    {
        $this->cmdlCaching = $duration;
        $this->readConnection->enableCMDLCaching($duration);
        if ($this->writeConnection)
        {
            $this->writeConnection->enableCMDLCaching($duration);
        }
    }


    /**
     * @return boolean
     */
    public function isSingleContentRecordCaching()
    {
        return $this->singleContentRecordCaching;
    }


    public function enableSingleContentRecordCaching($duration)
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


    public function enableAllContentRecordsCaching($duration)
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


    public function enableContentQueryRecordsCaching($duration)
    {
        $this->contentQueryRecordsCaching = $duration;
    }


//    /**
//     * @return boolean
//     */
//    public function isContentRecordsForwardCaching()
//    {
//        return $this->contentRecordsForwardCaching;
//    }
//
//
//    public function setContentRecordsForwardCaching($threshold)
//    {
//        $this->contentRecordsForwardCaching = $threshold;
//    }

//    /**
//     * @return boolean
//     */
//    public function isConfigRecordCaching()
//    {
//        return $this->configRecordCaching;
//    }
//
//
//    public function setConfigRecordCaching($duration)
//    {
//        $this->configRecordCaching = $duration;
//    }

//    /**
//     * @return boolean
//     */
//    public function isFilesCaching()
//    {
//        return $this->filesCaching;
//    }
//
//
//    public function setFilesCaching($duration)
//    {
//        $this->filesCaching = $duration;
//    }

    protected function createCacheKey($realm, $params = [ ])
    {

        $dataDimensions = $this->getCurrentDataDimensions();
        $cacheKey       = '[' . $this->getName() . '][' . $realm . '][' . (string)$dataDimensions . '][' . join(';', $params) . ']';

        if ($this->hasLastModifiedCacheStrategy())
        {

            $cacheKey = '[' . $this->getLastModifiedDate() . ']' . $cacheKey;

        }

        return $cacheKey;
    }


    protected function flushCacheBeforeChange()
    {
        if ($this->hasExpirationCacheStrategy())
        {
            $this->getCacheProvider()->flushAll();
        }
        else
        {
            $this->lastModified = $this->getLastModifiedDate();
        }
    }


    protected function flushCacheAfterChange()
    {
        if ($this->hasLastModifiedCacheStrategy())
        {
            if ($this->lastModified == $this->getLastModifiedDate()) // clear cache, if last modified date hasn't change, otherwise old values could be retrieved accidentially
            {
                $this->getCacheProvider()->flushAll();
            }
        }

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
        $this->flushCacheBeforeChange();

        $result = parent::saveRecord($record);

        $this->flushCacheAfterChange();

        return $result;
    }


    public function saveRecords($records)
    {
        $this->flushCacheBeforeChange();

        $result = parent::saveRecords($records);

        $this->flushCacheAfterChange();

        return $result;

    }


    public function deleteRecord($recordId)
    {
        $this->flushCacheBeforeChange();

        $result = parent::deleteRecord($recordId);

        $this->flushCacheAfterChange();

        return $result;
    }


    public function deleteRecords($recordIds)
    {
        $this->flushCacheBeforeChange();

        $result = parent::deleteRecord($recordIds);
        $this->flushCacheAfterChange();

        return $result;
    }


    /**
     * Updates parent and positiong properties of all records of current content type
     *
     * @param array $sorting array [recordId=>parentId]
     */
    public function sortRecords(array $sorting)
    {
        $this->flushCacheBeforeChange();

        $result = parent::sortRecords($sorting);
        $this->flushCacheAfterChange();

        return $result;
    }


    public function deleteAllRecords()
    {
        $this->flushCacheBeforeChange();

        $result = parent::deleteAllRecords();
        $this->flushCacheAfterChange();

        return $result;
    }


    public function getConfig($configTypeName)
    {

        return parent::getConfig($configTypeName);
    }


    public function saveConfig(Config $config)
    {
        return parent::saveConfig($config);
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