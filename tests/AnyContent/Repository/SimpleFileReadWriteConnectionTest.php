<?php

namespace AnyContent\Repository;

use AnyContent\Connection\SimpleFileReadWriteConnection;
use Symfony\Component\Filesystem\Filesystem;

class SimpleFileReadWriteConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  SimpleFileReadWriteConnection */
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
        $connection = new SimpleFileReadWriteConnection();
        $connection->addContentTypeFile(__DIR__ . '/../../resources/SimpleFileConnection/temp.json', __DIR__ . '/../../resources/SimpleFileConnection/temp.cmdl');

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

}