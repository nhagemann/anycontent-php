<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\RecordsFileReadOnlyConnection;
use KVMLogger\KVMLoggerFactory;

class RecordsFileReadOnlyConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileReadOnlyConnection */
    public $connection;


    public function setUp()
    {
        $configuration = new RecordsFileConfiguration();

        $configuration->addContentType('profiles',__DIR__ . '/../../resources/RecordsFileExample/profiles.cmdl', __DIR__ . '/../../resources/RecordsFileExample/profiles.json');

        $connection = $configuration->createReadOnlyConnection();

        $this->connection = $connection;

        KVMLoggerFactory::createWithKLogger(__DIR__.'/../../../tmp');

    }


    public function testContentTypeNotSelected()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        $this->setExpectedException('AnyContent\AnyContentClientException');
        $this->assertEquals(12, $connection->countRecords());
    }


    public function testContentTypeNames()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        $contentTypeNames = $connection->getContentTypeNames();

        $this->assertContains('profiles', $contentTypeNames);
    }


    public function testContentTypeDefinitions()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        $contentTypes = $connection->getContentTypeDefinitions();

        $this->assertArrayHasKey('profiles', $contentTypes);

        $contentType = $contentTypes['profiles'];
        $this->assertInstanceOf('CMDL\ContentTypeDefinition', $contentType);
    }


    public function testCountRecords()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(608, $connection->countRecords());

    }


    public function testGetRecord()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(1);

        $this->assertInstanceOf('AnyContent\Client\Record', $record);

        $this->assertEquals('UDG United Digital Group', $record->getProperty('name'));

    }


    public function testGetRecords()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

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