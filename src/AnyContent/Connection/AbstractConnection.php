<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Client\RecordFactory;
use AnyContent\Connection\Configuration\AbstractConfiguration;
use CMDL\ContentTypeDefinition;
use CMDL\Parser;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

class AbstractConnection
{

    /**
     * @var AbstractConfiguration
     */
    protected $configuration;

    protected $currentContentTypeName = null;

    /** @var  ContentTypeDefinition[] */
    protected $contentTypeDefinitions = [ ];

    /** @var  ContentTypeDefinition */
    protected $currentContentTypeDefinition = null;

    /** @var  CacheProvider */
    protected $cache;

    protected $cacheDuration;

    /** @var  RecordFactory */
    protected $recordFactory;

    /** @var Record[] */
    protected $records = [ ];

    /** @var DataDimensions */
    protected $dataDimensions;


    public function __construct(AbstractConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->configuration->apply($this);
    }


    /**
     * @return AbstractConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


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


    public function getParser()
    {
        return new Parser();
    }


    public function getCMDL($contentTypeName)
    {
        throw new AnyContentClientException ('Method getCMDL must be implemented.');

    }


    public function getRecordFactory()
    {
        if (!$this->recordFactory)
        {
            $this->recordFactory = new RecordFactory([ 'validateProperties' => false ]);

        }

        return $this->recordFactory;

    }


    /**
     * @param $contentTypeName
     *
     * @return \CMDL\ConfigTypeDefinition|ContentTypeDefinition|\CMDL\DataTypeDefinition|null
     * @throws AnyContentClientException
     * @throws \CMDL\CMDLParserException
     */
    public function getContentTypeDefinition($contentTypeName)
    {
        if ($this->getConfiguration()->hasContentType($contentTypeName))
        {
            if ($this->getCMDLCache()->contains($contentTypeName))
            {
                return $this->getCMDLCache()->fetch($contentTypeName);
            }

            $cmdl = $this->getCMDL($contentTypeName);

            if ($cmdl)
            {

                $parser = $this->getParser();

                $definition = $parser->parseCMDLString($cmdl, $contentTypeName, $this->getConfiguration()
                                                                                     ->getContentTypeTitle($contentTypeName));

                if ($definition)
                {
                    $this->contentTypeDefinitions[$contentTypeName]['definition'] = $definition;
                    $this->getCMDLCache()->save($contentTypeName, $definition, $this->cacheDuration);

                    return $definition;
                }
            }

        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }


    /**
     * @return array
     */
    public function getContentTypeNames()
    {
        return $this->configuration->getContentTypeNames();
    }


    /**
     * @return ContentTypeDefinition[]
     * @throws AnyContentClientException
     */
    public function getContentTypes()
    {
        $contentTypes = [ ];
        foreach ($this->getConfiguration()->getContentTypeNames() as $contentTypeName)
        {
            $definition                           = $this->getContentTypeDefinition($contentTypeName);
            $contentTypes[$definition->getName()] = $definition;
        }

        return $contentTypes;

    }


    /**
     * @param $contentTypeName
     *
     * @return $this
     * @throws AnyContentClientException
     */
    public function selectContentType($contentTypeName)
    {
        $definition = $this->getContentTypeDefinition($contentTypeName);

        $this->currentContentTypeDefinition = $definition;
        $this->currentContentTypeName       = $definition->getName();

        return $this;
    }


    /**
     * @return ContentTypeDefinition
     * @throws AnyContentClientException
     */
    public function getCurrentContentType()
    {
        if ($this->currentContentTypeDefinition == null)
        {
            throw new AnyContentClientException('No content type selected.');
        }

        return $this->currentContentTypeDefinition;

    }


    /**
     * @return string
     * @throws AnyContentClientException
     */
    public function getCurrentContentTypeName()
    {
        if ($this->currentContentTypeName == null)
        {
            throw new AnyContentClientException('No content type selected.');
        }

        return $this->currentContentTypeName;
    }


    /**
     * @param $contentTypeName
     *
     * @return bool
     * @throws AnyContentClientException
     */
    public function hasLoadedAllRecords($contentTypeName = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($this->getConfiguration()->hasContentType($contentTypeName))
        {

            if (array_key_exists($contentTypeName, $this->records))
            {
                return true;
            }
        }

        return false;
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

    /*
     public function selectWorkspace($workspace)
     {
         $definition = $this->getCurrentContentType();

         if ($definition->hasWorkspace($workspace))
         {
             $this->workspace = $workspace;

             return $this;
         }
         throw new AnyContentClientException('Content Type ' . $definition->getName() . ' does not support workspace ' . $workspace);
     }


     public function selectLanguage($language)
     {
         $definition = $this->getCurrentContentType();
         if ($definition->hasLangauge($language))
         {
             $this->language = $language;

             return $this;
         }
         throw new AnyContentClientException('Content Type ' . $definition->getName() . ' does not support language ' . $language);
     } */
}
