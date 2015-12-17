<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use AnyContent\Connection\RecordFilesReadWriteConnection;
use CMDL\Parser;
use Symfony\Component\Filesystem\Filesystem;

class RepositoryRecordsFileTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordFilesReadWriteConnection */
    public $connection;

    public static function setUpBeforeClass()
    {
        $fs = new Filesystem();
        $fs->copy(__DIR__ . '/../../resources/SimpleFileConnection/profiles.json', __DIR__ . '/../../resources/SimpleFileConnection/temp.json', true);
        $fs->copy(__DIR__ . '/../../resources/SimpleFileConnection/profiles.cmdl', __DIR__ . '/../../resources/SimpleFileConnection/temp.cmdl', true);
    }


    public static function tearDownAfterClass()
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/../../resources/SimpleFileConnection/temp.json');
        $fs->remove(__DIR__ . '/../../resources/SimpleFileConnection/temp.cmdl');
    }



    public function setUp()
    {
        $configuration = new RecordsFileConfiguration();

        $configuration->addContentType('temp',__DIR__ . '/../../resources/SimpleFileConnection/temp.cmdl', __DIR__ . '/../../resources/SimpleFileConnection/temp.json');

        $connection = $configuration->createReadWriteConnection();

        $this->connection = $connection;
    }



    public function testContentTypes()
    {
        $repository = new Repository($this->connection);

        $contentTypeNames = $repository->getContentTypeNames();

        $this->assertCount(1,$contentTypeNames);

        $this->assertTrue($repository->hasContentType('temp'));
    }


    public function testGetRecord()
    {
        $repository = new Repository($this->connection);

        $repository->selectContentType('temp');

        $record = $repository->getRecord(1);

        $this->assertEquals(1,$record->getID());
    }


    public function testGetRecords()
    {
        $repository = new Repository($this->connection);

        $repository->selectContentType('temp');

        $records = $repository->getRecords();

        $this->assertCount(608,$records);
    }
}