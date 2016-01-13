<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Connection\RestLikeBasicReadOnlyConnection;

class RestLikeConfiguration extends AbstractConfiguration
{

    protected $timeout = 30;

    protected $uri;


    /**
     * @return mixed
     */
    public function getUri()
    {
        if (!$this->uri)
        {
            throw new AnyContentClientException('Basi uri not set.');
        }

        return $this->uri;
    }


    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $uri       = rtrim($uri, '/');
        $uri       = rtrim($uri, '/info');
        $uri       = $uri . '/';
        $this->uri = $uri;

    }


    public function addContentTypes()
    {
        /** @var RestLikeBasicReadOnlyConnection $connection */
        $connection = $this->getConnection();
        $info       = $connection->getRepositoryInfo();

        $contentTypes = array_keys($info['content']);

        $this->contentTypes = array_fill_keys($contentTypes, [ ]);

        return $this;
    }


    public function addConfigTypes()
    {

        //$this->contentTypes[$contentTypeName] = [ 'uri' => $uriRecords, 'cmdl' => $urlCMDL ];

        return $this;
    }


    public function createReadOnlyConnection()
    {
        return new RestLikeBasicReadOnlyConnection($this);
    }


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

        return $this;
    }

}