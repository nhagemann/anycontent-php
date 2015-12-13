<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Connection\RecordFilesReadOnlyConnection;
use Symfony\Component\Filesystem\Filesystem;

class RecordFilesConfiguration extends AbstractConfiguration
{

    /**
     * @return \AnyContent\Connection\Abstracts\AbstractRecordsFileReadOnly
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
            throw new AnyContentClientException('Folder ' . $folderRecords . ' not found.');
        }

        $this->contentTypes[$contentTypeName] = [ 'records' => $folderRecords, 'cmdl' => $filenameCMDL, 'title' => $contentTypeTitle ];

        return $this;
    }


    public function createReadOnlyConnection()
    {
        return new RecordFilesReadOnlyConnection($this);
    }


    public function getUriCMDL($contentTypeName)
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