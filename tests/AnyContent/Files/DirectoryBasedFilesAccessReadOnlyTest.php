<?php

namespace AnyContent\Connection;

use AnyContent\Connection\FileManager\DirectoryBasedFilesAccess;
use AnyContent\Connection\Interfaces\FileManager;
use KVMLogger\KVMLoggerFactory;

class DirectoryBasedFilesAccessReadOnlyTest extends \PHPUnit_Framework_TestCase
{

    /** @var  FileManager */
    public $fileManager;


    public function setUp()
    {

        $fileManager = new DirectoryBasedFilesAccess(__DIR__ . '/../../resources/Files');
        $fileManager->enableImageSizeCalculation();

        $this->fileManager = $fileManager;

        KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');

    }


    public function testGetFolder()
    {
        $fileManager = $this->fileManager;

        $folder = $fileManager->getFolder();
        $this->assertInstanceOf('AnyContent\Client\Folder',$folder);
        $this->assertCount(2, $folder->listSubFolders());

        $folder = $fileManager->getFolder('Music/');
        $this->assertCount(2, $folder->listSubFolders());

        $folder = $fileManager->getFolder('Music/Alternative');
        $this->assertCount(0, $folder->listSubFolders());

        $folder = $fileManager->getFolder('Music/Pop');
        $this->assertCount(0, $folder->listSubFolders());

        $folder = $fileManager->getFolder('Music/Jazz');
        $this->assertFalse($folder);
    }


    public function testGetFile()
    {
        $fileManager = $this->fileManager;

        $file = $fileManager->getFile('a.txt');
        $this->assertInstanceOf('AnyContent\Client\File',$file);

        $file = $fileManager->getFile('Music/c.txt');
        $this->assertEquals('c.txt',$file->getName());
        $this->assertEquals('Music/c.txt',$file->getId());

        $file = $fileManager->getFile('z.txt');
        $this->assertFalse($file);
    }
}