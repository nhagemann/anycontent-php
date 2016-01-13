<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Client\Config;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Record;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Response;
use KVMLogger\KVMLoggerFactory;
use KVMLogger\LogMessage;

class RestLikeBasicReadOnlyConnection extends AbstractConnection implements ReadOnlyConnection
{

    /**
     * @var Client
     */
    protected $client;

    protected $repositoryInfo = [ ];


    /**
     * @return Client
     */
    public function getClient()
    {

        if (!$this->client)
        {

            $client = new Client([ 'base_url' => $this->getConfiguration()->getUri(),
                                   'defaults' => [ 'timeout' => $this->getConfiguration()->getTimeout() ]
                                 ]);

            $this->client = $client;

            $emitter = $client->getEmitter();

            $emitter->on('complete', function (CompleteEvent $event)
            {

                $kvm = KVMLoggerFactory::instance('anycontent-connection');

                $response = $event->getResponse();

                $duration = (int)($event->getTransferInfo('total_time') * 1000);

                $message = new LogMessage();
                $message->addLogValue('method', $event->getRequest()->getMethod());
                $message->addLogValue('code', $response->getStatusCode());
                $message->addLogValue('duration', $duration);
                $message->addLogValue('url', $response->getEffectiveUrl());

                $kvm->debug($message);

            });
        }

        return $this->client;
    }


    public function getRepositoryInfo(DataDimensions $dataDimensions = null)
    {
        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        if (!array_key_exists((string)$dataDimensions, $this->repositoryInfo))
        {
            $response = $this->getClient()
                             ->get('info');// . $dataDimensions->getWorkspace() . '/' . $dataDimensions->getLanguage() . '?timeshift=' . $dataDimensions->getTimeShift());
            $json     = $response->json();

            $this->repositoryInfo[(string)$dataDimensions] = $json;
        }

        return $this->repositoryInfo[(string)$dataDimensions];
    }


    /**
     * @param $contentTypeName
     *
     * @return string
     */
    public function getCMDLForContentType($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            $response = $this->getClient()->get('content/' . $contentTypeName . '/cmdl');
            $json     = $response->json();

            return $json['cmdl'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

    }


    /**
     * @param $configTypeName
     *
     * @return string
     */
    public function getCMDLForConfigType($configTypeName)
    {
        if ($this->getConfiguration()->hasConfigType($configTypeName))
        {

            $response = $this->getClient()->get('content/' . $configTypeName . '/cmdl');
            $json     = $response->json();

            return $json['cmdl'];

        }

        throw new AnyContentClientException ('Unknown config type ' . $configTypeName);

    }


    /**
     * @param null $contentTypeName
     *
     * @return int
     */
    public function countRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        if ($this->hasContentType($contentTypeName))
        {

            $info = $this->getRepositoryInfo($dataDimensions);

            return $info['content'][$contentTypeName]['count'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     */
    public function getAllRecords($contentTypeName = null, DataDimensions $dataDimensions = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        if ($this->hasContentType($contentTypeName))
        {
            $url = 'content/' . $contentTypeName . '/records';

            $response = $this->getClient()->get($url);

            $json = $response->json();
            $records = $this->getRecordFactory()
                           ->createRecordsFromJSONRecordsArray($this->getContentTypeDefinition($contentTypeName), $json['records']);

            return $records;
        }
    }


    /**
     * @param $recordId
     *
     * @return Record
     */
    public function getRecord($recordId, $contentTypeName = null, DataDimensions $dataDimensions = null)
    {

        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if ($dataDimensions == null)
        {
            $dataDimensions = $this->getCurrentDataDimensions();
        }

        if ($this->hasContentType($contentTypeName))
        {
            $url = 'content/' . $contentTypeName . '/record/' . $recordId;

            try
            {
                $response = $this->getClient()->get($url);
            }
            catch (ClientException $e)
            {
                if ($e->getCode() == 404)
                {
                    return false;
                }
                throw new AnyContentClientException ($e->getMessage());
            }

            $json = $response->json();

            $record = $this->getRecordFactory()
                           ->createRecordFromJSON($this->getContentTypeDefinition($contentTypeName), $json['record']);

            return $record;
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

//        if ($contentTypeName == null)
//        {
//            $contentTypeName = $this->getCurrentContentTypeName();
//        }
//
//        if ($dataDimensions == null)
//        {
//            $dataDimensions = $this->getCurrentDataDimensions();
//        }
//
//        $tableName = $this->getContentTypeTableName($contentTypeName);
//
//        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = ? AND workspace = ? AND language = ? AND deleted = 0 AND validfrom_timestamp <= ? AND validuntil_timestamp > ?';
//
//        $timestamp = TimeShifter::getTimeshiftTimestamp($dataDimensions->getTimeShift());
//
//        $rows = $this->getDatabase()
//                     ->fetchAllSQL($sql, [ $recordId, $dataDimensions->getWorkspace(), $dataDimensions->getLanguage(), $timestamp, $timestamp ]);
//
//        if (count($rows) == 1)
//        {
//            return $this->createRecordFromRow(reset($rows), $contentTypeName, $dataDimensions);
//        }
//
//        return false;

    }


    /**
     *
     * @return Config
     */
    public function getConfig($configTypeName = null, DataDimensions $dataDimensions = null)
    {
//        if ($dataDimensions == null)
//        {
//            $dataDimensions = $this->getCurrentDataDimensions();
//        }
//
//        return $this->exportRecord($this->getMultiViewConfig($configTypeName, $dataDimensions), $dataDimensions->getViewName());

    }

}