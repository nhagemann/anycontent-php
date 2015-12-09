<?php
namespace AnyContent\Connection\Configuration;

class AbstractConfiguration
{

    protected $contentTypes = [ ];


    public function hasContentType($contentTypeName)
    {
        return array_key_exists($contentTypeName, $this->contentTypes);
    }


    public function getContentTypeNames()
    {
        return array_keys($this->contentTypes);
    }


    public function getContentTypeTitle($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->contentTypes[$contentTypeName]['title'];
        }

        return null;
    }


    public function apply()
    {

    }
}