<?php

namespace AnyContent\Repository;

use AnyContent\Connection\SimpleFileReadOnlyConnection;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\SQLite3Cache;
use Symfony\Component\Filesystem\Filesystem;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    /** @var  BulkCachingRepository */
    protected $repository;


    public function setUp()
    {
        $connection = new SimpleFileReadOnlyConnection();
        $connection->addContentTypeFile(__DIR__ . '/../../resources/SimpleFileConnection/profiles.json', __DIR__ . '/../../resources/SimpleFileConnection/profiles.cmdl');

        $repository = new BulkCachingRepository($connection);

        $cache = new PhpFileCache(__DIR__ . '/../../resources/phpfilecache');

        $fs = new Filesystem();

        $fs->remove(__DIR__ . '/../../resources/phpfilecache');
        $fs->mkdir(__DIR__ . '/../../resources/phpfilecache');

        $repository->setContentCache($cache);
        $this->repository = $repository;

    }


    public function testGetRecords()
    {
        $repository = $this->repository;

        $repository->selectContentType('profiles');

        $records = $repository->getRecords();

        $this->assertCount(608, $records);

        foreach ($records as $record)
        {
            $id          = $record->getID();
            $fetchRecord = $repository->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getID());
        }


    }
}