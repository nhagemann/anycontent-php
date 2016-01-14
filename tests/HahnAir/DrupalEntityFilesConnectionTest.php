<?php

namespace AnyContent\Connection;


use HahnAir\AnyContent\Connection\DrupalEntityFilesConfiguration;

class DrupalEntityFilesConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordFilesReadOnlyConnection */
    public $connection;


    public function setUp()
    {

        $configuration = new DrupalEntityFilesConfiguration();

        $configuration->addContentType('airline', '/var/www/drupalexport/cmdl/airline.cmdl', '/var/www/drupalexport/data/airline');

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

        $this->assertContains('airline', $contentTypeNames);
   }


    public function testContentTypeDefinitions()
    {
        $connection = $this->connection;

        $contentTypes = $connection->getContentTypeDefinitions();

        $this->assertArrayHasKey('airline', $contentTypes);

        $contentType = $contentTypes['airline'];


        $this->assertInstanceOf('CMDL\ContentTypeDefinition', $contentType);
    }


    public function testCountRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('airline');

        $this->assertEquals(410, $connection->countRecords());

    }

    public function testGetRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('airline');

        $record = $connection->getRecord(28);

        $this->assertInstanceOf('AnyContent\Client\Record', $record);

        $this->assertEquals('Branson AirExpress', $record->getProperty('name'));

    }


    public function testGetRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('airline');

        $records = $connection->getAllRecords();

        $this->assertCount(410, $records);

        foreach ($records as $record)
        {
            $id          = $record->getID();
            $fetchRecord = $connection->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getID());
        }
    }

}