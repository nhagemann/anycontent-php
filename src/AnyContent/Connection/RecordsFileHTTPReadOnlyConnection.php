<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadOnly;

use AnyContent\Connection\Configuration\RecordsFileHttpConfiguration;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class RecordsFileHttpReadOnlyConnection extends RecordsFileReadOnlyConnection implements ReadOnlyConnection
{

    /**
     * @return RecordsFileHttpConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }



    /**
     * @param $fileName
     *
     * @return \GuzzleHttp\Stream\StreamInterface|null
     * @throws ClientException
     */
    protected function readData($fileName)
    {
        $client   = new Client([ 'defaults' => [ 'timeout' => $this->getConfiguration()->getTimeout() ] ]);
        $response = $client->get($fileName);

        return $response->getBody();
    }
}