<?php

namespace AnyContent\Cache;

use AnyContent\Connection\Configuration\RecordsFileConfiguration;

use Doctrine\Common\Cache\PhpFileCache;

use KVMLogger\KVMLoggerFactory;
use Symfony\Component\Filesystem\Filesystem;

class CacheingRepositoryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  CachingRepository */
    protected $repository;



    public function setUp()
    {

        $configuration = new RecordsFileConfiguration();

        $configuration->addContentType('profiles', __DIR__ . '/../../resources/RecordsFileExample/profiles.cmdl', __DIR__ . '/../../resources/RecordsFileExample/profiles.json');

        $connection = $configuration->createReadOnlyConnection();

        $repository = new CachingRepository('phpunit', $connection);

        $fs = new Filesystem();

        $fs->remove(__DIR__ . '/../../../tmp/phpfilecache');
        $fs->mkdir(__DIR__ . '/../../../tmp/phpfilecache');

        $cache = new PhpFileCache(__DIR__ . '/../../../tmp/phpfilecache');

        $repository->setCacheProvider($cache);
        $this->repository = $repository;

        KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');
    }


    public function testGetRecords()
    {
        $repository = $this->repository;
        $repository->setAllContentRecordsCaching(60);


        $repository->selectContentType('profiles');

        $records = $repository->getRecords();

        $this->assertCount(608, $records);

        $records = $repository->getRecords();

        $this->assertCount(608, $records);

        $this->assertEquals(2,$repository->getCacheProvider()->getMissCounter('records'));
        $this->assertEquals(1,$repository->getCacheProvider()->getHitCounter('records'));
    }


    public function testGetRecord()
    {
        $repository = $this->repository;
        $repository->setSingleContentRecordCaching(60);

        $repository->selectContentType('profiles');

        $repository->getRecord(1);
        $repository->getRecord(1);

        $this->assertEquals(2,$repository->getCacheProvider()->getMissCounter('records'));
        $this->assertEquals(1,$repository->getCacheProvider()->getHitCounter('records'));

    }


}