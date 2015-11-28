<?php

namespace AnyContent\Connection;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SimpleHttpReadOnlyConnection extends SimpleFileReadOnlyConnection
{

    protected $timeout = 30;


    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }


    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }


    public function addContentTypeUrl($contentTypeName, $urlRecords, $urlCMDL, $contentTypeTitle = null)
    {

        $this->contentTypes[$contentTypeName] = [ 'json' => $urlRecords, 'cmdl' => $urlCMDL, 'definition' => false, 'records' => false, 'title' => $contentTypeTitle ];

        return $this;
    }


    /**
     * @param $fileName
     *
     * @return \GuzzleHttp\Stream\StreamInterface|null
     * @throws ClientException
     */
    protected function readData($fileName)
    {
        $client   = new Client([ 'defaults' => [ 'timeout' => $this->getTimeout() ] ]);
        $response = $client->get($fileName);

        return $response->getBody();
    }
}