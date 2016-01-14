<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\AbstractRecord;
use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Client\RecordFactory;
use AnyContent\Client\Repository;
use AnyContent\Client\UserInfo;
use AnyContent\Connection\Configuration\AbstractConfiguration;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\Parser;
use CMDL\Util;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

abstract class AbstractConnection
{

    protected $precalculations = [ ];

    /**
     * @var AbstractConfiguration
     */
    protected $configuration;

    /** @var  ContentTypeDefinition[] */
    protected $contentTypeDefinitions = [ ];

    protected $currentContentTypeName = null;

    /** @var  ContentTypeDefinition */
    protected $currentContentTypeDefinition = null;

    /** @var DataDimensions */
    protected $dataDimensions;

    /** @var  RecordFactory */
    protected $recordFactory;

    /** @var  UserInfo */
    protected $userInfo;

    protected $recordsStash = [ ];

    protected $configStash = [ ];

    protected $hasStashedAllRecords = [ ];

    /** @var  Repository */
    protected $repository;

    /** @var  CacheProvider */
    protected $cache;

    protected $cacheDuration;


    public function __construct(AbstractConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->configuration->apply($this);
        $this->userInfo = new UserInfo();
    }


    /**
     * gets called, when this connection is added to a repository
     *
     * @param Repository $repository
     */

    public function apply(Repository $repository)
    {
        $this->repository = $repository;

    }


    /**
     * @return AbstractConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * @return Repository
     */
    public function getRepository()
    {
        if (!$this->repository)
        {
            throw new AnyContentClientException('Repository object not set within connection of type ' . get_class($this));
        }

        return $this->repository;
    }


    /**
     * @param Repository $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }


    /**
     * @return bool
     */
    public function hasRepository()
    {
        return (boolean)$this->repository;
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


    /**
     * @param $contentTypeName
     *
     * @return string
     */
    public function getCMDLForContentType($contentTypeName)
    {
        throw new AnyContentClientException ('Method getCMDLForContentType must be implemented.');

    }


    public function getCMDLForConfigType($configTypeName)
    {
        throw new AnyContentClientException ('Method getCMDLForConfigType must be implemented.');

    }


    public function getRecordFactory()
    {
        if ($this->hasRepository())
        {
            return $this->getRepository()->getRecordFactory();
        }
        if (!$this->recordFactory)
        {
            $this->recordFactory = new RecordFactory([ 'validateProperties' => false ]);

        }

        return $this->recordFactory;

    }


    public function getRecordClassForContentType($contentTypeName)
    {
        if ($this->hasRepository())
        {
            return $this->getRepository()->getRecordClassForContentType($contentTypeName);
        }

        return 'AnyContent\Client\Record';
    }


    public function getRecordClassForConfigType($configTypeName)
    {
        if ($this->hasRepository())
        {
            return $this->getRepository()->getRecordClassForContentType($configTypeName);
        }

        return 'AnyContent\Client\Config';
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

            $cmdl = $this->getCMDLForContentType($contentTypeName);

            if ($cmdl)
            {

                $parser = $this->getParser();

                $definition = $parser->parseCMDLString($cmdl, $contentTypeName);

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
     * @param $configTypeName
     *
     * @return \CMDL\ConfigTypeDefinition|ContentTypeDefinition|\CMDL\DataTypeDefinition|null
     * @throws AnyContentClientException
     * @throws \CMDL\CMDLParserException
     */
    public function getConfigTypeDefinition($configTypeName)
    {
        if ($this->getConfiguration()->hasConfigType($configTypeName))
        {
            $cacheToken = 'cmdl-configtype-' . $configTypeName;
            if ($this->getCMDLCache()->contains($cacheToken))
            {
                return $this->getCMDLCache()->fetch($cacheToken);
            }

            $cmdl = $this->getCMDLForConfigType($configTypeName);

            if ($cmdl)
            {

                $parser = $this->getParser();

                $definition = $parser->parseCMDLString($cmdl, $configTypeName, null, 'config');

                if ($definition)
                {
                    //$this->contentTypeDefinitions[$configTypeName]['definition'] = $definition;
                    $this->getCMDLCache()->save($cacheToken, $definition, $this->cacheDuration);

                    return $definition;
                }
            }

        }

        throw new AnyContentClientException ('Unknown config type ' . $configTypeName);
    }


    /**
     * @return array
     */
    public function getContentTypeNames()
    {
        return $this->configuration->getContentTypeNames();
    }


    /**
     * @return array
     */
    public function getConfigTypeNames()
    {
        return $this->configuration->getConfigTypeNames();
    }


    /**
     * @return ContentTypeDefinition[]
     * @throws AnyContentClientException
     * @deprecated
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
     * @return ConfigTypeDefinition[]
     * @throws AnyContentClientException
     */
    public function getConfigTypeDefinitions()
    {
        $configTypes = [ ];
        foreach ($this->getConfiguration()->getContentTypeNames() as $configTypeName)
        {
            $definition                          = $this->getConfigTypeDefinition($configTypeName);
            $configTypes[$definition->getName()] = $definition;
        }

        return $configTypes;

    }


    public function hasContentType($contentTypeName)
    {
        if (in_array($contentTypeName, $this->getContentTypeNames()))
        {
            return true;
        }

        return false;
    }


    public function hasConfigType($configTypeName)
    {
        if (in_array($configTypeName, $this->getConfigTypeNames()))
        {
            return true;
        }

        return false;
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
     * @deprecated
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
     * @return ContentTypeDefinition
     * @throws AnyContentClientException
     */
    public function getCurrentContentTypeDefinition()
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

        $this->dataDimensions = new DataDimensions($this->getCurrentContentTypeDefinition());

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


    protected function hasStashedRecord($contentTypeName, $recordId, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {
        return (boolean)$this->getStashedRecord($contentTypeName, $recordId, $dataDimensions, $recordClass);

    }


    protected function getStashedRecord($contentTypeName, $recordId, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {
        if (!$dataDimensions->hasRelativeTimeShift())
        {
            $hash = md5($contentTypeName . $dataDimensions . $recordClass);
            if (array_key_exists($hash, $this->recordsStash))
            {
                if (array_key_exists($recordId, $this->recordsStash[$hash]))
                {
                    return $this->recordsStash[$hash][$recordId];
                }
            }
        }

        return false;

    }


    protected function stashRecord(Record $record, DataDimensions $dataDimensions)
    {
        if (!$dataDimensions->hasRelativeTimeShift())
        {
            $hash                                        = md5($record->getContentTypeName() . $dataDimensions . get_class($record));
            $this->recordsStash[$hash][$record->getID()] = $record;
        }
    }


    protected function unstashRecord($contentTypeName, $recordId, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {
        $hash = md5($contentTypeName . $dataDimensions . $recordClass);
        unset($this->recordsStash[$hash][$recordId]);
    }


    protected function hasStashedAllRecords($contentTypeName, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {
        if (!$dataDimensions->hasRelativeTimeShift())
        {
            $hash = md5($contentTypeName . $dataDimensions . $recordClass);
            if (array_key_exists($hash, $this->hasStashedAllRecords))
            {

                return $this->hasStashedAllRecords[$hash];

            }
        }

        return false;
    }


    protected function getStashedAllRecords($contentTypeName, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {

        $hash = md5($contentTypeName . $dataDimensions . $recordClass);

        if ($this->hasStashedAllRecords($contentTypeName, $dataDimensions, $recordClass))
        {
            return $this->recordsStash[$hash];
        }

        return false;
    }


    protected function stashAllRecords($records, DataDimensions $dataDimensions)
    {

        if (!$dataDimensions->hasRelativeTimeShift())
        {
            if (count($records) > 0)
            {
                /** @var Record $firstRecord */
                $firstRecord = reset($records);

                $this->unstashAllRecords($firstRecord->getContentTypeName(), $dataDimensions, get_class($firstRecord));

                foreach ($records as $record)
                {
                    $this->stashRecord($record, $dataDimensions);
                }

                $hash = md5($firstRecord->getContentTypeName() . $dataDimensions . get_class($firstRecord));

                $this->hasStashedAllRecords[$hash] = true;

            }
        }
    }


    protected function unstashAllRecords($contentTypeName, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {
        if (!$dataDimensions->hasRelativeTimeShift())
        {
            $hash = md5($contentTypeName . $dataDimensions . $recordClass);

            unset($this->recordsStash[$hash]);
            $this->hasStashedAllRecords[$hash] = false;

        }
    }


    protected function stashConfig(Config $config, DataDimensions $dataDimensions)
    {
        if (!$dataDimensions->hasRelativeTimeShift())
        {
            $hash                     = md5($config->getConfigTypeName() . $dataDimensions . get_class($config));
            $this->configStash[$hash] = $config;
        }
    }


    protected function unstashConfig($configTypeName, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Config')
    {
        $hash = md5($configTypeName . $dataDimensions . $recordClass);
        unset($this->configStash[$hash]);
    }


    protected function hasStashedConfig($configTypeName, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Config')
    {
        return (boolean)$this->getStashedConfig($configTypeName, $dataDimensions, $recordClass);

    }


    protected function getStashedConfig($configTypeName, DataDimensions $dataDimensions, $recordClass = 'AnyContent\Client\Record')
    {
        if (!$dataDimensions->hasRelativeTimeShift())
        {
            $hash = md5($configTypeName . $dataDimensions . $recordClass);
            if (array_key_exists($hash, $this->configStash))
            {
                return $this->configStash[$hash];
            }
        }

        return false;

    }


    /**
     * @param $userInfo
     *
     * @return $this
     */
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;

        return $this;
    }


    /**
     * Make sure the returned record is a new instance an does only contain properties of it's
     * current view
     *
     * @param AbstractRecord $record - multi view record !
     */
    protected function exportRecord(AbstractRecord $record, $viewName)
    {
        $precalculate      = $this->precalculateExportRecord($record, $this->getCurrentDataDimensions());
        $allowedProperties = array_intersect_key($record->getProperties(), $precalculate['allowedProperties']);
        $record            = clone $record;
        $record->setProperties($allowedProperties);

        return $record;
    }


    protected function precalculateExportRecord(AbstractRecord $record, DataDimensions $dataDimensions)
    {
        $key = 'exportrecord-' . $record->getDataType() . '-' . $record->getDataTypeName() . '-' . $dataDimensions->getViewName();
        if (array_key_exists($key, $this->precalculations))
        {
            $precalculate = $this->precalculations[$key];
        }
        else
        {

            $definition        = $record->getDataTypeDefinition();
            $allowedProperties = $definition->getProperties($dataDimensions->getViewName());

            $allowedProperties = array_combine($allowedProperties, $allowedProperties);

            $precalculate                      = [ ];
            $precalculate['allowedProperties'] = $allowedProperties;

            $this->precalculations[$key] = $precalculate;
        }

        return $precalculate;
    }


    protected function exportRecords($records, $viewName)
    {
        $result = [ ];
        foreach ($records as $record)
        {
            $result[$record->getId()] = $this->exportRecord($record, $viewName);
        }

        return $result;
    }


    /**
     * remove protected properties and execute @name annotation
     *
     * @param Record $record
     */
    protected function finalizeRecord(Record $record, DataDimensions $dataDimensions)
    {

        // Apply @name annotation
        if ($record->getDataTypeDefinition()->hasNamingPattern())
        {
            $record->setName(Util::applyNamingPattern($record->getProperties(), $record->getDataTypeDefinition()
                                                                                       ->getNamingPattern()));
        }

        // remove protected properties
        $properties = $record->getProperties();
        foreach ($record->getDataTypeDefinition()->getProtectedProperties($dataDimensions->getViewName()) as $property)
        {
            unset ($properties[$property]);
        }
        $record->setProperties($properties);

        return $record;
    }

}
