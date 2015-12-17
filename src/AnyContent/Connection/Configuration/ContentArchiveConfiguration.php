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

    protected $filename = null;


    public function setContentArchiveFolder($path)
    {
        $path       = rtrim($path, '/');
        $this->path = realpath($path);

    }


    public function getContentArchiveFolder()
    {
        return $this->path;
    }


    public function setContentArchiveFile($path)
    {
        $this->setContentArchiveFolder(pathinfo($path, PATHINFO_DIRNAME));
        $this->filename = basename($path);
    }


    public function apply(AbstractConnection $connection)
    {

        $finder = new Finder();

        $uri = 'file://'.$this->getContentArchiveFolder().'/cmdl';




        /*
        if ($this->filename!=null)
        {
            $uri = 'zip://./'.$this->getContentArchiveFolder().'/'.$this->filename;
        }
        */



        $z = new \ZipArchive();
        if ($z->open($this->getContentArchiveFolder().'/'.$this->filename)) {
            //$uri = $z->getStream('ContentArchiveReadOnlyConnection/test.txt');
            //var_dump ($a);
            //$uri = 'zip:/'.$this->getContentArchiveFolder().'/'.$this->filename.'#'.$this->filename.'/cmdl';
            //  $uri = 'zip://' . realpath($this->getContentArchiveFolder(). '/'.$this->filename).'#ContentArchiveReadOnlyConnection/cmdl/';

        }
        /*
        $fp = fopen('zip://' . realpath($this->getContentArchiveFolder(). '/'.$this->filename).'#ContentArchiveReadOnlyConnection/test.txt', 'r');
        if (!$fp) {
            exit("Datei kann nicht geöffnet werden\n");
        }
        */


        //var_dump(stream_get_meta_data($uri));

        /*$contents = '';
        $fp = fopen('zip:/' . $this->getContentArchiveFolder(). '/'.$this->filename.'#test.txt', 'r');
        if (!$fp) {
            exit("Datei kann nicht geöffnet werden\n");
        }
        while (!feof($fp)) {
            $contents .= fread($fp, 2);
        }
        echo "$contents\n";
        fclose($fp);
        echo "Erledigt.\n";

         */
       /*
        var_dump($this->getContentArchiveFolder().'/'.$this->filename);

        $uri = 'zip://file://'.$this->getContentArchiveFolder().'/'.$this->filename;

        $xml = file_get_contents($uri);

        var_dump ($uri);
         */


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