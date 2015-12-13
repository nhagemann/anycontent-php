<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Connection\RecordsFileReadOnlyConnection;
use AnyContent\Connection\RecordsFileReadWriteConnection;
use Symfony\Component\Filesystem\Filesystem;

class RecordsFileConfiguration extends AbstractConfiguration
{

    /**
     * @param      $filenameRecords
     * @param      $filenameCMDL
     * @param null $contentTypeName
     * @param null $contentTypeTitle
     *
     * @return $this
     * @throws AnyContentClientException
     */
    public function addContentType($contentTypeName, $filenameCMDL, $filenameRecords, $contentTypeTitle = null)
    {
        $fs = new Filesystem();

        /*if (!$fs->exists($filenameCMDL))
        {
            throw new AnyContentClientException('File ' . $filenameRecords . ' not found.');
        }

        if (!$fs->exists($filenameRecords))
        {
            throw new AnyContentClientException('File ' . $filenameRecords . ' not found.');
        }*/

        $this->contentTypes[$contentTypeName] = [ 'records' => $filenameRecords, 'cmdl' => $filenameCMDL, 'title' => $contentTypeTitle ];

        return $this;
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
            return $this->contentTypes[$contentTypeName]['records'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }


    public function createReadOnlyConnection()
    {
        return new RecordsFileReadOnlyConnection($this);
    }


    public function createReadWriteConnection()
    {
        return new RecordsFileReadWriteConnection($this);
    }

}