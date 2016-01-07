<?php

namespace AnyContent\Connection\FileManager;

use AnyContent\Client\File;
use AnyContent\Client\Folder;
use AnyContent\Connection\Interfaces\FileManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DirectoryBasedFilesAccess implements FileManager
{

    /**
     * @var Filesystem null
     */
    protected $filesystem = null;

    protected $baseFolder = null;

    protected $imagesize = true;

    protected $publicUrl = false;


    public function __construct($baseFolder, $baseUrl = false)
    {
        $this->baseFolder = $baseFolder;
        $this->filesystem = new Filesystem();

        if ($baseUrl)
        {
            $this->setPublicUrl($baseUrl);
        }
    }


    public function enableImageSizeCalculation()
    {
        $this->imagesize = true;

        return $this;
    }


    public function disableImageSizeCalculation()
    {
        $this->imagesize = true;

        return $this;
    }


    /**
     * @return boolean
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }


    /**
     * @param boolean $publicUrl
     */
    public function setPublicUrl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }


    /**
     * @param string $path
     *
     * @return Folder|bool
     */
    public function getFolder($path = '')
    {
        if (file_exists($this->baseFolder . '/' . $path))
        {
            $result = [ 'folder' => $this->listSubFolder($path), 'files' => $this->listFiles($path) ];

            $folder = new Folder($path, $result);

            return $folder;
        }

        return false;
    }


    public function getFile($id)
    {
        $id = trim(trim($id, '/'));
        if ($id != '')
        {
            $pathinfo = pathinfo($id);
            $folder   = $this->getFolder($pathinfo['dirname']);
            if ($folder)
            {
                return $folder->getFile($id);
            }
        }

        return false;
    }


    public function getBinary(File $file)
    {
        $id = trim($file->getId(), '/');

        $fileName = pathinfo($id, PATHINFO_FILENAME);

        if ($fileName != '') // No access to .xxx-files
        {

            return @file_get_contents($this->baseFolder . '/' . $id);

        }

        return false;
    }


    public function saveFile($id, $binary)
    {

        $id       = trim($id, '/');
        $fileName = pathinfo($id, PATHINFO_FILENAME);

        if ($fileName != '') // No writing of .xxx-files
        {
            $this->filesystem->dumpFile($this->baseFolder . '/' . $id, $binary);

            return true;
        }

        return false;
    }


    public function deleteFile($id, $deleteEmptyFolder = true)
    {
        try
        {
            if ($this->filesystem->exists($this->baseFolder . '/' . $id))
            {
                $this->filesystem->remove($this->baseFolder . '/' . $id);
            }

            if ($deleteEmptyFolder)
            {
                $this->deleteFolder(pathinfo($id, PATHINFO_DIRNAME));
            }

            return true;
        }
        catch (\Exception $e)
        {

        }

        return false;
    }


    public function createFolder($path)
    {
        try
        {
            $path = trim($path, '/');

            $this->filesystem->mkdir($this->baseFolder . '/' . $path . '/');

            return true;
        }
        catch (\Exception $e)
        {

        }

        return false;
    }


    public function deleteFolder($path, $deleteIfNotEmpty = false)
    {

        $folder = $this->getFolder($path);
        if ($folder)
        {
            if ($folder->isEmpty() || $deleteIfNotEmpty)
            {
                $path = trim($path, '/');

                $folder = $this->baseFolder . '/' . $path;

                try
                {
                    if ($this->filesystem->exists($folder))
                    {
                        $this->filesystem->remove($folder);

                    }

                    return true;
                }
                catch (\Exception $e)
                {

                }
            }
        }

        return false;

    }


    public function listSubFolder($path)
    {
        $path    = trim($path, '/');
        $folders = array();
        $finder  = new Finder();

        $finder->depth(0);

        try
        {

            /* @var $file \SplFileInfo */
            foreach ($finder->in($this->baseFolder . '/' . $path) as $file)
            {
                if ($file->isDir())
                {
                    $folders[] = $file->getFilename();
                }
            }

        }
        catch (\Exception $e)
        {
            return false;
        }

        return $folders;
    }


    protected function listFiles($path)
    {

        $path = trim($path, '/');

        $files  = array();
        $finder = new Finder();

        $finder->depth('==0');

        try
        {
            /* @var $file \SplFileInfo */
            foreach ($finder->in($this->baseFolder . '/' . $path) as $file)
            {
                if (!$file->isDir())
                {
                    $item                         = array();
                    $item['id']                   = trim($path . '/' . $file->getFilename(), '/');
                    $item['name']                 = $file->getFilename();
                    $item['urls']                 = array();
                    $item['type']                 = 'binary';
                    $item['size']                 = $file->getSize();
                    $item['timestamp_lastchange'] = $file->getMTime();

                    $extension = strtolower($extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION)); // To be compatible with some older PHP 5.3 versions

                    if (in_array($extension, array( 'gif', 'png', 'jpg', 'jpeg' )))
                    {
                        $item['type'] = 'image';

                        if ($this->imagesize == true)
                        {

                            $content = $file->getContents();

                            if (function_exists('imagecreatefromstring'))
                            {
                                $image = @imagecreatefromstring($content);
                                if ($image)
                                {

                                    $item['width']  = imagesx($image);
                                    $item['height'] = imagesy($image);
                                }
                            }
                        }

                    }

                    if ($this->publicUrl != false)
                    {
                        $item['url'] = $this->publicUrl . '/' . $item['id'];
                    }

                    $files[$file->getFilename()] = $item;
                }

            }
        }
        catch (\Exception $e)
        {
            return false;
        }

        return $files;
    }

}
