<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\Configuration\RestLikeConfiguration;
use AnyContent\Connection\RecordsFileReadOnlyConnection;
use KVMLogger\KVMLoggerFactory;

class RestLikeBasicConnectionReadOnlyTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RestLikeBasicReadOnlyConnection */
    public $connection;


    public function setUp()
    {
        if (defined('PHPUNIT_CREDENTIALS_RESTLIKE_URL'))
        {
            $configuration = new RestLikeConfiguration();

            $configuration->setUri(PHPUNIT_CREDENTIALS_RESTLIKE_URL);
            $connection = $configuration->createReadOnlyConnection();

            $configuration->addContentTypes();

            $this->connection = $connection;

            KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');
        }

    }


    public function testContentTypeNotSelected()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        if (!$connection)
        {
            $this->markTestSkipped('RestLike Basic Connection credentials missing.');
        }

        $this->setExpectedException('AnyContent\AnyContentClientException');
        $this->assertEquals(12, $connection->countRecords());
    }


    public function testContentTypeNames()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        if (!$connection)
        {
            $this->markTestSkipped('RestLike Basic Connection credentials missing.');
        }

        $contentTypeNames = $connection->getContentTypeNames();

        $this->assertContains('dtag_searchresult_product', $contentTypeNames);
    }


    public function testContentTypeDefinitions()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        if (!$connection)
        {
            $this->markTestSkipped('RestLike Basic Connection credentials missing.');
        }

        $contentTypes = $connection->getContentTypes();

        $this->assertArrayHasKey('dtag_searchresult_product', $contentTypes);

        $contentType = $contentTypes['dtag_searchresult_product'];
        $this->assertInstanceOf('CMDL\ContentTypeDefinition', $contentType);
    }


    public function testCountRecords()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        if (!$connection)
        {
            $this->markTestSkipped('RestLike Basic Connection credentials missing.');
        }

        $connection->selectContentType('dtag_searchresult_product');

        $this->assertEquals(149, $connection->countRecords());

    }


    public function testGetRecord()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        if (!$connection)
        {
            $this->markTestSkipped('RestLike Basic Connection credentials missing.');
        }

        $connection->selectContentType('dtag_searchresult_product');

        $record = $connection->getRecord(98);

        $this->assertInstanceOf('AnyContent\Client\Record', $record);

        $this->assertEquals('Apple iPad Air', $record->getProperty('name'));

        $record = $connection->getRecord(1);

        $this->assertFalse($record);

    }


    public function testGetRecords()
    {
        KVMLoggerFactory::instance()->debug(__METHOD__);

        $connection = $this->connection;

        if (!$connection)
        {
            $this->markTestSkipped('RestLike Basic Connection credentials missing.');
        }

        $connection->selectContentType('dtag_searchresult_product');

        $records = $connection->getAllRecords();

        $this->assertCount(149, $records);

        foreach ($records as $record)
        {
            $id          = $record->getId();
            $fetchRecord = $connection->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getId());
        }
    }

}