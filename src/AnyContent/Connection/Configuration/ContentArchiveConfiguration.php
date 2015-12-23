<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Client\DataDimensions;
use AnyContent\Connection\AbstractConnection;
use AnyContent\Connection\ContentArchiveReadOnlyConnection;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use Symfony\Component\Finder\Finder;

class ContentArchiveConfiguration extends AbstractConfiguration
{

    protected $path;


    public function setContentArchiveFolder($path)
    {
        $path       = rtrim($path, '/');
        $this->path = realpath($path);

    }


    public function getContentArchiveFolder()
    {
        return $this->path;
    }


    public function apply(AbstractConnection $connection)
    {

        $finder = new Finder();

        $uri = 'file://'.$this->getContentArchiveFolder().'/cmdl';

        $finder->in($uri)->depth(0);

        /** @var SplFileInfo $file */
        foreach ($finder->files('*.cmdl') as $file)
        {
            $contentTypeName = $file->getBasename('.cmdl');

            $this->contentTypes[$contentTypeName] = [ 'title' => null ];

        }
    }


    public function createReadOnlyConnection()
    {
        return new ContentArchiveReadOnlyConnection($this);
    }

    public function createReadWriteConnection()
    {
        return new ContentArchiveReadWriteConnection($this);
    }



    public function getUriCMDL($contentTypeName)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->getContentArchiveFolder() . '/cmdl/' . $contentTypeName . '.cmdl';
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }


    public function getFolderNameRecords($contentTypeName, DataDimensions $dataDimensions)
    {
        if ($this->hasContentType($contentTypeName))
        {
            return $this->getContentArchiveFolder() . '/data/content/' . $contentTypeName . '/' . $dataDimensions->getWorkspace() . '/' . $dataDimensions->getLanguage();
        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }
}