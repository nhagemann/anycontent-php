<?php

namespace AnyContent\Client;

use AnyContent\Client\Util\RecordsSorter;
use AnyContent\Connection\Configuration\ContentArchiveConfiguration;

use AnyContent\Connection\ContentArchiveReadWriteConnection;
use KVMLogger\KVMLoggerFactory;
use Symfony\Component\Filesystem\Filesystem;

class OrderText extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadWriteConnection */
    public $connection;

    /** @var  Repository */
    public $repository;


    public static function setUpBeforeClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';
        $source = __DIR__ . '/../../resources/ContentArchiveExample2';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

        KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');
    }


    public function setUp()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';

        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder($target);

        $connection = $configuration->createReadWriteConnection();

        $this->connection = $connection;

        $this->repository = new Repository($this->connection);

    }


    public function testOrderRecords()
    {
        $records = [ ];

        $this->repository->selectContentType('example01');

        for ($i = 1; $i <= 5; $i++)
        {
            $record = $this->repository->createRecord('New Record');
            $record->setProperty('source', $i);
            $record->setProperty('article', 'A');
            $records[$i] = $record;
        }
        for ($i = 6; $i <= 10; $i++)
        {
            $record = $this->repository->createRecord('New Record');
            $record->setProperty('source', $i);
            $record->setProperty('article', 'B');
            $records[$i] = $record;
        }

        $this->assertEquals([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ], array_keys($records));
        $records = RecordsSorter::orderRecords($records, 'source+');
        $this->assertEquals([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ], array_keys($records));
        $records = RecordsSorter::orderRecords($records, 'source-');
        $this->assertEquals([ 10, 9, 8, 7, 6, 5, 4, 3, 2, 1 ], array_keys($records));

        $records = RecordsSorter::orderRecords($records, 'article+');
        foreach (array_slice($records, 0, 5) as $record)
        {
            $this->assertEquals($record->getProperty('article'), 'A');
        }
        foreach (array_slice($records, 5, 5) as $record)
        {
            $this->assertEquals($record->getProperty('article'), 'B');
        }

        $records = RecordsSorter::orderRecords($records, 'article-');
        foreach (array_slice($records, 0, 5) as $record)
        {
            $this->assertEquals($record->getProperty('article'), 'B');
        }
        foreach (array_slice($records, 5, 5) as $record)
        {
            $this->assertEquals($record->getProperty('article'), 'A');
        }

        $records = RecordsSorter::orderRecords($records, [ 'article-', 'source+' ]);

        $this->assertEquals([ 6, 7, 8, 9, 10, 1, 2, 3, 4, 5 ], array_keys($records));
    }

}