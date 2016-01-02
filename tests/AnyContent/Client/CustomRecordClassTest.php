<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use CMDL\Parser;

use KVMLogger\KVMLoggerFactory;
use Symfony\Component\Filesystem\Filesystem;

class CustomRecordClassTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadWriteConnection */
    public $connection;


    public static function setUpBeforeClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';
        $source = __DIR__ . '/../../resources/ContentArchiveExample2';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

    }


    public function setUp()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';

        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder($target);

        $connection = $configuration->createReadWriteConnection();

        $this->connection = $connection;

        KVMLoggerFactory::createWithKLogger(__DIR__.'/../../../tmp');
    }


    public function testSaveRecords()
    {

        $cmdl = $this->connection->getCMDLForContentType('example01');

        $contentTypeDefinition = Parser::parseCMDLString($cmdl);
        $contentTypeDefinition->setName('example01');

        $this->connection->selectContentType('example01');

        for ($i = 1; $i <= 5; $i++)
        {
            $record = new Record($contentTypeDefinition, 'New Record ' . $i);
            $record->setProperty('article', 'Test ' . $i);

            $id = $this->connection->saveRecord($record);
            $this->assertEquals($i, $id);
        }

    }


    public function testGetRecords()
    {
        $repository = new Repository($this->connection);

        $repository->selectContentType('example01');

        $records = $repository->getRecords();

        $this->assertCount(5, $records);
        $this->assertEquals(5, $repository->countRecords());

        $i = 0;
        foreach ($records as $id => $record)
        {
            $i++;
            $this->assertEquals($i, $id);
            $this->assertEquals('Test ' . $i, $record->getProperty('article'));
        }

        $repository->registerRecordClassForContentType('example01','AnyContent\Client\AlternateRecordClass');

        $records = $repository->getRecords();

        $i = 0;
        foreach ($records as $id => $record)
        {
            $i++;
            $this->assertInstanceOf('AnyContent\Client\AlternateRecordClass',$record);
            $this->assertEquals($i, $id);
            $this->assertEquals('New Record '.$i,$record->getName());
            $this->assertEquals('Test ' . $i, $record->getProperty('article'));
        }

    }



}


class AlternateRecordClass extends Record
{

    public function getArticle()
    {
        return $this->getProperty('article');
    }
}