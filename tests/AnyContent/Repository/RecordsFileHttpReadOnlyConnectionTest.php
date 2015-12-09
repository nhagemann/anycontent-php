<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\RecordsFileHttpConfiguration;
use AnyContent\Connection\RecordsFileHttpReadOnlyConnection;

class RecordsFileHttpReadOnlyConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileHttpReadOnlyConnection */
    public $connection;


    public function setUp()
    {
        $configuration = new RecordsFileHttpConfiguration();

        $configuration->addContentType('profiles', 'https://s3-eu-west-1.amazonaws.com/backup01.contentbox.io/da08517dc866617a075c0c2d38c5fb95/profiles.default.default.json', 'https://s3-eu-west-1.amazonaws.com/backup01.contentbox.io/da08517dc866617a075c0c2d38c5fb95/profiles.cmdl');

        $connection = $configuration->createReadOnlyConnection();

        $this->connection = $connection;
    }


    public function testContentTypeNotSelected()
    {
        $connection = $this->connection;

        $this->setExpectedException('AnyContent\AnyContentClientException');
        $this->assertEquals(12, $connection->countRecords());
    }


    public function testContentTypeNames()
    {
        $connection = $this->connection;

        $contentTypeNames = $connection->getContentTypeNames();

        $this->assertContains('profiles', $contentTypeNames);
    }


    public function testContentTypeDefinitions()
    {
        $connection = $this->connection;

        $contentTypes = $connection->getContentTypes();

        $this->assertArrayHasKey('profiles', $contentTypes);

        $contentType = $contentTypes['profiles'];
        $this->assertInstanceOf('CMDL\ContentTypeDefinition', $contentType);
    }


    public function testCountRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(608, $connection->countRecords());

    }


    public function testGetRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(1);

        $this->assertInstanceOf('AnyContent\Client\Record', $record);

        $this->assertEquals('UDG United Digital Group', $record->getProperty('name'));

    }


    public function testGetRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $records = $connection->getAllRecords();

        $this->assertCount(608, $records);

        foreach ($records as $record)
        {
            $id          = $record->getID();
            $fetchRecord = $connection->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getID());
        }
    }

}