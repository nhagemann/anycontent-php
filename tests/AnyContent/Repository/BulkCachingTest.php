<?php

namespace AnyContent\Repository;

use AnyContent\Connection\RecordsFileReadOnlyConnection;
use Doctrine\Common\Cache\PhpFileCache;

use Symfony\Component\Filesystem\Filesystem;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    /** @var  BulkCachingRepository */
    protected $repository;


    public function setUp()
    {
        $connection = new RecordsFileReadOnlyConnection();
        $connection->addContentTypeFile(__DIR__ . '/../../resources/SimpleFileConnection/profiles.json', __DIR__ . '/../../resources/SimpleFileConnection/profiles.cmdl');

        $repository = new BulkCachingRepository($connection);

        $fs = new Filesystem();

        $fs->remove(__DIR__ . '/../../../tmp/phpfilecache');
        $fs->mkdir(__DIR__ . '/../../../tmp/phpfilecache');

        $cache = new PhpFileCache(__DIR__ . '/../../../tmp/phpfilecache');

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