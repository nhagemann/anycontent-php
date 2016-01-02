<?php

namespace AnyContent\Client;

use AnyContent\Client\Util\RecordsSorter;
use AnyContent\Connection\Configuration\ContentArchiveConfiguration;

use AnyContent\Connection\ContentArchiveReadWriteConnection;
use KVMLogger\KVMLoggerFactory;

class OrderText extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadWriteConnection */
    public $connection;

    /** @var  Repository */
    public $repository;


    public static function setUpBeforeClass()
    {

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


    public function testSliceRecords()
    {
        $records = [ ];

        $this->repository->selectContentType('example01');

        for ($i = 1; $i <= 10; $i++)
        {
            $record = $this->repository->createRecord('New Record');
            $record->setProperty('source', $i);
            $records[$i] = $record;
        }

        $this->assertEquals([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ], array_keys($records));
        $records = RecordsSorter::orderRecords($records, 'source+');
        $this->assertEquals([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ], array_keys($records));
        $records = RecordsSorter::orderRecords($records, 'source-');
        $this->assertEquals([ 10, 9, 8, 7, 6, 5, 4, 3, 2, 1 ], array_keys($records));
    }

}