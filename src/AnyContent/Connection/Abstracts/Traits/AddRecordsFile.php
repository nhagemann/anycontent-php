<?php
namespace AnyContent\Connection\Abstracts\Traits;

use AnyContent\AnyContentClientException;
use Symfony\Component\Filesystem\Filesystem;

trait AddRecordsFile
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

        if (!$fs->exists($filenameCMDL))
        {
            throw new AnyContentClientException('File ' . $filenameRecords . ' not found.');
        }

        if (!$fs->exists($filenameRecords))
        {
            throw new AnyContentClientException('File ' . $filenameRecords . ' not found.');
        }

        $this->contentTypes[$contentTypeName] = [ 'json' => $filenameRecords, 'cmdl' => $filenameCMDL, 'definition' => false, 'records' => false, 'title' => $contentTypeTitle ];

        return $this;
    }
}