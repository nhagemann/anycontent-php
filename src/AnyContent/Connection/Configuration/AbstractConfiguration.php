<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\Connection\AbstractConnection;

class AbstractConfiguration
{

    protected $contentTypes = [ ];

    protected $configTypes = [ ];


    public function hasContentType($contentTypeName)
    {
        return array_key_exists($contentTypeName, $this->contentTypes);
    }


    public function getContentTypeNames()
    {
        return array_keys($this->contentTypes);
    }


    public function hasConfigType($configTypeName)
    {
        return array_key_exists($configTypeName, $this->configTypes);
    }


    public function getConfigTypeNames()
    {
        return array_keys($this->configTypes);
    }


    public function apply(AbstractConnection $connection)
    {

    }
}