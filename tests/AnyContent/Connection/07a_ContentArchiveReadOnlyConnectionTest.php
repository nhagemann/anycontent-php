<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\ContentArchiveReadOnlyConnection;

class ContentArchiveConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadOnlyConnection */
    public $connection;


    public function setUp()
    {
        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder(__DIR__ . '/../../resources/ContentArchiveExample1');

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

        $this->assertEquals(3, $connection->countRecords());

    }


    public function testGetRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(5);

        $this->assertInstanceOf('AnyContent\Client\Record', $record);

        $this->assertEquals('dmc digital media center', $record->getProperty('name'));

    }


    public function testGetRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $records = $connection->getAllRecords();

        $this->assertCount(3, $records);

        foreach ($records as $record)
        {
            $id          = $record->getID();
            $fetchRecord = $connection->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getID());
        }
    }


    public function testWorkspaces()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $connection->selectWorkspace('live');

        $records = $connection->getAllRecords();

        $this->assertCount(2, $records);
    }

}