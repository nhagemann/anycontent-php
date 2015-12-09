<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadOnly;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use CMDL\ContentTypeDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ContentArchiveReadOnlyConnection extends RecordFilesReadOnlyConnection implements ReadOnlyConnection
{

    protected $path;

    protected $filename = null;


    public function setContentArchiveFolder($path)
    {
        $path       = rtrim($path, '/');
        $this->path = $path;

        $this->initContentTypes();
    }


    public function getContentArchiveFolder()
    {
        return $this->path;
    }


    public function setContentArchiveFile($path)
    {
        // TODO
    }


    protected function initContentTypes()
    {
        $finder = new Finder();

        $finder->in($this->getContentArchiveFolder())->depth(1);

        /** @var SplFileInfo $file */
        foreach ($finder->files('*.cmdl') as $file)
        {
            $contentTypeName = $file->getBasename('.cmdl');

            $filenameCMDL    = $this->getContentArchiveFolder() . '/cmdl/' . $contentTypeName . '.cmdl';
            $filenameRecords = $this->getContentArchiveFolder() . '/data/content/' . $contentTypeName;

            $this->contentTypes[$contentTypeName] = [ 'json' => $filenameRecords, 'cmdl' => $filenameCMDL, 'definition' => false, 'records' => false, 'title' => false, 'folder'=>$filenameRecords ];

        }
    }



}