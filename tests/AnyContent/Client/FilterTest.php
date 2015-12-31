<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;

use AnyContent\Connection\ContentArchiveReadWriteConnection;

use Symfony\Component\Filesystem\Filesystem;

class FilterTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadWriteConnection */
    public $connection;

    /** @var  Repository */
    public $repository;


    public static function setUpBeforeClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';
        $source = __DIR__ . '/../../resources/ExampleContentArchive';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

    }


    public static function tearDownAfterClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';

        $fs = new Filesystem();
        $fs->remove($target);

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


    public function testSaveRecords()
    {
        $this->repository->selectContentType('example01');

        $record = $this->repository->createRecord('New Record');
        $record->setProperty('source', 'a');
        $id = $this->repository->saveRecord($record);
        $this->assertEquals(1, $id);


        $record = $this->repository->createRecord('New Record');
        $record->setProperty('source', 'b');
        $id = $this->repository->saveRecord($record);
        $this->assertEquals(2, $id);


        $record = $this->repository->createRecord('Differing Name');
        $record->setProperty('source', 'c');
        $id = $this->repository->saveRecord($record);
        $this->assertEquals(3, $id);


        $records = $this->repository->getRecords('name = new Record AND (a=b OR c=d)');
        $this->assertCount(2,$records);

        //        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('name', '=', 'New Record');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(2, $records);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('name', '=', 'New Record');
//        $filter->addCondition('name', '=', 'Differing Name');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(3, $records);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('source', '>', 'b');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(1, $records);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('source', '>', 'a');
//        $filter->nextConditionsBlock();
//        $filter->addCondition('name', '=', 'Differing Name');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(1, $records);
    }


//    public function testSaveRecords()
//    {
//        // Execute admin call to delete all existing data of the test content types
//        $guzzle  = new \Guzzle\Http\Client('http://acrs.github.dev');
//        $request = $guzzle->delete('1/example/content/example01/records', null, null, array('query'=>array('global' => 1 )));
//        $result  = $request->send()->getBody();
//
//        $cmdl = $this->client->getCMDL('example01');
//
//        $contentTypeDefinition = Parser::parseCMDLString($cmdl);
//        $contentTypeDefinition->setName('example01');
//
//        $record = new Record($contentTypeDefinition, 'New Record');
//        $record->setProperty('source', 'a');
//        $id = $this->client->saveRecord($record);
//        $this->assertEquals(1, $id);
//
//        $record = new Record($contentTypeDefinition, 'New Record');
//        $record->setProperty('source', 'b');
//        $id = $this->client->saveRecord($record);
//        $this->assertEquals(2, $id);
//
//        $t1 = $this->client->getLastContentTypeChangeTimestamp($contentTypeDefinition->getName());
//
//        $record = new Record($contentTypeDefinition, 'Differing Name');
//        $record->setProperty('source', 'c');
//        $id = $this->client->saveRecord($record);
//        $this->assertEquals(3, $id);
//
//        $t2 = $this->client->getLastContentTypeChangeTimestamp($contentTypeDefinition->getName());
//
//        $this->assertNotEquals($t1, $t2);
//
//        $repository = $this->client->getRepository();
//        $repository->selectContentType('example01');
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('name', '=', 'New Record');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(2, $records);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('name', '=', 'New Record');
//        $filter->addCondition('name', '=', 'Differing Name');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(3, $records);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('source', '>', 'b');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(1, $records);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('source', '>', 'a');
//        $filter->nextConditionsBlock();
//        $filter->addCondition('name', '=', 'Differing Name');
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(1, $records);
//    }
//
//
//    public function testSimpleFilter()
//    {
//
//        $repository = $this->client->getRepository();
//        $repository->selectContentType('example01');
//
//        $filter  = 'name = New Record';
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(2, $records);
//
//        $filter  = 'name = New Record, name = Differing Name';
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(3, $records);
//
//        $filter  = 'source > b';
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(1, $records);
//
//        $filter  = 'source > a + name = Differing Name';
//        $records = $repository->getRecords('default', 'default', 'default', 'id', array(), null, 1, $filter);
//        $this->assertCount(1, $records);
//    }
//
//
//    public function testFilterToStringConversion()
//    {
//        $cmdl = $this->client->getCMDL('example01');
//
//        $contentTypeDefinition = Parser::parseCMDLString($cmdl);
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('name', '=', 'New Record');
//
//        $this->assertEquals('name = New Record', $filter->getSimpleQuery());
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('name', '=', 'New Record');
//        $filter->addCondition('name', '=', 'Differing Name');
//
//        $this->assertEquals('name = New Record , name = Differing Name', $filter->getSimpleQuery());
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('source', '>', 'b');
//
//        $this->assertEquals('source > b', $filter->getSimpleQuery());
//
//        $filter = new ContentFilter($contentTypeDefinition);
//        $filter->addCondition('source', '>', 'a');
//        $filter->nextConditionsBlock();
//        $filter->addCondition('name', '=', 'Differing Name');
//
//        $this->assertEquals('source > a + name = Differing Name', $filter->getSimpleQuery());
//    }

}