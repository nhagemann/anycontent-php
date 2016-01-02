<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Connection\RecordFilesReadOnlyConnection;
use AnyContent\Connection\RecordFilesReadWriteConnection;
use KVMLogger\KVMLoggerFactory;
use Symfony\Component\Filesystem\Filesystem;

class RecordFilesConfiguration extends AbstractConfiguration
{

    /**
     * @return RecordFilesConfiguration
     * @throws AnyContentClientException
     */
    public function addContentType($contentTypeName = null, $filenameCMDL, $folderRecords, $contentTypeTitle = null)
    {
        $fs = new Filesystem();

        if (!$fs->exists($filenameCMDL))
        {
            throw new AnyContentClientException('File ' . $filenameCMDL . ' not found.');
        }

        if (!$fs->exists($folderRecords))
        {
            KVMLoggerFactory::instance('anycontent')->warning('Folder ' . $folderRecords . ' not found.');
        }

        $this->contentTypes[$contentTypeName] = [ 'records' => $folderRecords, 'cmdl' => $filenameCMDL, 'title' => $contentTypeTitle ];

        return $this;
    }


    public function createReadOnlyConnection()
    {
        return new RecordFilesReadOnlyConnection($this);
    }

    public function createReadWriteConnection()
    {
        return new RecordFilesReadWriteConnection($this);
    }

    public function getUriCMDLForContentType($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->contentTypes[$contentTypeName]['cmdl'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }


    public function getFolderNameRecords($contentTypeName, DataDimensions $dataDimensions)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->contentTypes[$contentTypeName]['records'];
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }

}