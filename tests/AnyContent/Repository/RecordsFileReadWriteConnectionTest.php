<?php

namespace AnyContent\Client;

use AnyContent\Connection\RecordsFileReadWriteConnection;
use Symfony\Component\Filesystem\Filesystem;

class RecordsFileReadWriteConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileReadWriteConnection */
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
        $connection = new RecordsFileReadWriteConnection();
        $connection->addContentType('temp',__DIR__ . '/../../resources/SimpleFileConnection/temp.cmdl', __DIR__ . '/../../resources/SimpleFileConnection/temp.json');

        $this->connection = $connection;
    }


    public function testSaveRecordSameConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

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

        $connection->selectContentType('temp');

        $record = $connection->getRecord(1);

        $this->assertEquals('UDG', $record->getProperty('name'));

    }


    public function testAddRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $record = new Record($connection->getCurrentContentType(), 'test');

        $id = $connection->saveRecord($record);

        $this->assertEquals(629, $record->getID());
        $this->assertEquals(629, $id);

    }


    public function testSaveRecordsSameConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

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

        $connection->selectContentType('temp');

        $this->assertEquals(614, $connection->countRecords());
    }


    public function testDeleteRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $result = $connection->deleteRecord(1);

        $this->assertCount(1, $result);
        $this->assertEquals(613, $connection->countRecords());

        $result = $connection->deleteRecord(999);

        $this->assertCount(0, $result);
        $this->assertEquals(613, $connection->countRecords());

    }


    public function testDeleteRecordNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $this->assertEquals(613, $connection->countRecords());
    }


    public function testDeleteRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $result = $connection->deleteRecords([ 2, 5, 999 ]);

        $this->assertCount(2, $result);
        $this->assertEquals(611, $connection->countRecords());

    }


    public function testDeleteRecordsNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $this->assertEquals(611, $connection->countRecords());
    }


    public function testDeleteAllRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $result = $connection->deleteAllRecords();

        $this->assertCount(611, $result);
        $this->assertEquals(0, $connection->countRecords());

    }


    public function testDeleteAllRecordsNewConnection()
    {
        $connection = $this->connection;

        $connection->selectContentType('temp');

        $this->assertEquals(0, $connection->countRecords());
    }
}