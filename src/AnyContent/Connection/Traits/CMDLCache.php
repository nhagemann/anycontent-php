<?php
namespace AnyContent\Connection\Traits;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

trait CMDLCache
{

    /** @var  CacheProvider */
    protected $cache;

    protected $cacheDuration;


    /**
     * @param CacheProvider $cache
     * @param int           $confidence
     * @param int           $storage
     * @param string        $namespace
     */
    public function setCMDLCache(CacheProvider $cache, $confidence = 600, $storageDuration = 600, $namespace = 'cmdl')
    {
        $this->cache = $cache;
        $this->cache->setNamespace($namespace);
        $this->cacheDuration = min($confidence, $storageDuration);
    }


    /**
     * @return CacheProvider
     */
    protected function getCMDLCache()
    {
        if (!$this->cache)
        {
            $this->cache = new ArrayCache();
        }

        return $this->cache;
    }

}