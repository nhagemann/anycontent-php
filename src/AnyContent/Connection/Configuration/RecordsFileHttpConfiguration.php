<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Connection\RecordsFileHttpReadOnlyConnection;

class RecordsFileHttpConfiguration extends AbstractConfiguration
{

    protected $timeout = 30;


    public function addContentType($contentTypeName, $urlRecords, $urlCMDL, $contentTypeTitle = null)
    {

        $this->contentTypes[$contentTypeName] = [ 'url' => $urlRecords, 'cmdl' => $urlCMDL, 'title' => $contentTypeTitle ];

        return $this;
    }


    public function createReadOnlyConnection()
    {
        return new RecordsFileHttpReadOnlyConnection($this);
    }


    public function getUriCMDL($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->contentTypes[$contentTypeName]['cmdl'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }


    public function getUriRecords($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->contentTypes[$contentTypeName]['url'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
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