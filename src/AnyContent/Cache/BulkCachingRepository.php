<?php

namespace AnyContent\Client;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

class BulkCachingRepository extends Repository
{

    /** @var  CacheProvider */
    protected $cache;

    protected $cacheDuration;

    protected $cacheConfidence;


    public function setContentCache(CacheProvider $cache, $confidence = 600, $storageDuration = 600, $namespace = 'repository')
    {
        $this->cache = $cache;
        $this->cache->setNamespace($namespace);

        $this->cacheConfidence = $confidence;
        $this->cacheDuration   = $storageDuration;

    }


    public function getContentCache()
    {
        if (!$this->cache)
        {
            $this->cache = new ArrayCache();
        }

        return $this->cache;
    }


    /**
     * @param null $dataDimensions
     *
     * @return Record[]
     */
    public function getRecords($dataDimensions = null)
    {
        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        $cacheKey = 'records-all-' . md5((string)$dataDimensions);

        if ($this->getContentCache()->contains($cacheKey))
        {
            $data = $this->getContentCache()->fetch($cacheKey);
            if ($data)
            {
                $data = json_decode($data,true);

                $recordFactory = new RecordFactory([ 'validateProperties' => false ]);
                $records = $recordFactory->createRecordsFromJSONArray($this->getCurrentContentType(),$data);
                return $records;
            }
        }

        $records = parent::getRecords($dataDimensions);

        $data = json_encode($records);


        $this->getContentCache()->save($cacheKey, $data, $this->cacheDuration);

        return $records;

    }

}