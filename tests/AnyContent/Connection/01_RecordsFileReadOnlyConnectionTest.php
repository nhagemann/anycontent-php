<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\RecordsFileReadOnlyConnection;

class RecordsFileReadOnlyConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileReadOnlyConnection */
    public $connection;


    public function setUp()
    {
        $configuration = new RecordsFileConfiguration();

        $configuration->addContentType('profiles',__DIR__ . '/../../resources/SimpleFileConnection/profiles.cmdl', __DIR__ . '/../../resources/SimpleFileConnection/profiles.json');

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
            $id          = $record->getId();
            $fetchRecord = $connection->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getId());
        }
    }

}