<?php
namespace AnyContent\Connection\Abstracts\Traits;

use AnyContent\AnyContentClientException;
use Symfony\Component\Filesystem\Filesystem;

trait AddRecordFiles{

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

        $this->contentTypes[$contentTypeName] = [ 'json' => $folderRecords, 'cmdl' => $filenameCMDL, 'definition' => false, 'records' => false, 'title' => $contentTypeTitle ];

        return $this;
    }
}