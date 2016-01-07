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
    }


    public function disableImageSizeCalculation()
    {
        $this->imagesize = true;
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

    }


    public function saveFile($id, $binary)
    {

    }


    public function deleteFile($id, $deleteEmptyFolder = true)
    {

    }


    public function createFolder($path)
    {

    }


    public function deleteFolder($path, $deleteIfNotEmpty = false)
    {

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
//
//
//    public function getFile($id)
//    {
//
//        $id = trim($id, '/');
//
//        $fileName = pathinfo($id, PATHINFO_FILENAME);
//
//        if ($fileName != '') // No access to .xxx-files
//        {
//
//            return @file_get_contents($this->directory . '/' . $id);
//
//        }
//
//        return false;
//
//    }
//
//
//    public function saveFile($id, $binary)
//    {
//        $id       = trim($id, '/');
//        $fileName = pathinfo($id, PATHINFO_FILENAME);
//
//        if ($fileName != '') // No writing of .xxx-files
//        {
//            $this->filesystem->dumpFile($this->directory . '/' . $id, $binary);
//
//            return true;
//        }
//
//        return false;
//    }
//
//
//    public function deleteFile($id)
//    {
//        try
//        {
//            if ($this->filesystem->exists($this->directory . '/' . $id))
//            {
//                $this->filesystem->remove($this->directory . '/' . $id);
//
//                return true;
//            }
//        }
//        catch (\Exception $e)
//        {
//
//        }
//
//        return false;
//    }
//
//
//    public function createFolder($path)
//    {
//        $path = trim($path, '/');
//
//        return $this->filesystem->mkdir($this->directory . '/' . $path . '/');
//    }
//
//
//    public function deleteFolder($path)
//    {
//
//        $path = trim($path, '/');
//
//        $folder = $this->directory . '/' . $path;
//
//        try
//        {
//            if ($this->filesystem->exists($folder))
//            {
//                $this->filesystem->remove($folder);
//
//                return true;
//            }
//
//        }
//        catch (\Exception $e)
//        {
//            echo $e->getMessage();
//        }
//
//        return false;
//    }
}
