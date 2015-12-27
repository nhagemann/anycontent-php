<?php

namespace AnyContent\Connection;

use AnyContent\Client\Record;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\RecordsFileReadWriteConnection;
use Symfony\Component\Filesystem\Filesystem;

class RecordsFileReadWriteConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileReadWriteConnection */
    public $connection;


    public static function setUpBeforeClass()
    {
        $target = __DIR__ . '/../../../tmp/RecordsFileExample';
        $source = __DIR__ . '/../../resources/SimpleFileConnection';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

    }


        public function setUp()
    {
        $configuration = new RecordsFileConfiguration();

        $configuration->addContentType('profiles', __DIR__ . '/../../../tmp/RecordsFileExample/profiles.cmdl', __DIR__ . '/../../../tmp/RecordsFileExample/profiles.json');
        $configuration->addContentType('test', __DIR__ . '/../../../tmp/RecordsFileExample/test.cmdl', __DIR__ . '/../../../tmp/RecordsFileExample/test.json');

        $connection = $configuration->createReadWriteConnection();

        $this->connection = $connection;
    }


    public function testSaveRecordSameConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(1);

        $this->assertEquals('UDG United Digital Group', $record->getProperty('name'));

        $record->setProperty('name', 'UDG');

        $connection->saveRecord($record);

        $record = $connection->getRecord(1);

        $this->assertEquals('UDG', $record->getProperty('name'));

    }


    public function testSaveRecordNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(1);

        $this->assertEquals('UDG', $record->getProperty('name'));

    }


    public function testAddRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = new Record($connection->getCurrentContentType(), 'test');

        $id = $connection->saveRecord($record);

        $this->assertEquals(629, $record->getID());
        $this->assertEquals(629, $id);

    }


    public function testSaveRecordsSameConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(609, $connection->countRecords());

        $records = [ ];

        for ($i = 1; $i <= 5; $i++)
        {
            $record    = new Record($connection->getCurrentContentType(), 'Test ' . $i);
            $records[] = $record;
        }

        $connection->saveRecords($records);

        $this->assertEquals(614, $connection->countRecords());

    }


    public function testSaveRecordsNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(614, $connection->countRecords());
    }


    public function testDeleteRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $result = $connection->deleteRecord(1);

        $this->assertEquals(1, $result);
        $this->assertEquals(613, $connection->countRecords());

        $result = $connection->deleteRecord(999);

        $this->assertEquals(false, $result);
        $this->assertEquals(613, $connection->countRecords());

    }


    public function testDeleteRecordNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(613, $connection->countRecords());
    }


    public function testDeleteRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $result = $connection->deleteRecords([ 2, 5, 999 ]);

        $this->assertCount(2, $result);
        $this->assertEquals(611, $connection->countRecords());

    }


    public function testDeleteRecordsNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(611, $connection->countRecords());
    }


    public function testDeleteAllRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $result = $connection->deleteAllRecords();

        $this->assertCount(611, $result);
        $this->assertEquals(0, $connection->countRecords());

    }


    public function testDeleteAllRecordsNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(0, $connection->countRecords());
    }

}