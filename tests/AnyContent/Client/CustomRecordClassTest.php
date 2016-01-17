<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use CMDL\Parser;

use KVMLogger\KVMLoggerFactory;
use KVMLogger\KVMLogger;
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

        KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');
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
        $repository = new Repository('phpunit', $this->connection);

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

        $repository->registerRecordClassForContentType('example01', 'AnyContent\Client\AlternateRecordClass');

        $records = $repository->getRecords();

        $i = 0;
        foreach ($records as $id => $record)
        {
            $i++;
            $this->assertInstanceOf('AnyContent\Client\AlternateRecordClass', $record);
            $this->assertEquals($i, $id);
            $this->assertEquals('New Record ' . $i, $record->getName());
            $this->assertEquals('Test ' . $i, $record->getProperty('article'));
        }

    }


    public function testGetConfig()
    {
        $repository = new Repository('phpunit', $this->connection);

        $repository->registerRecordClassForConfigType('config1', 'AnyContent\Client\AlternateConfigRecordClass');

        $config = $repository->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\AlternateConfigRecordClass', $config);

        $config->setProperty('city', 'Hamburg');

        $repository->saveConfig($config);
    }


    public function testGetConfigNewConnection()
    {
        $repository = new Repository('phpunit', $this->connection);

        $config = $repository->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals('Hamburg',$config->getProperty('city'));

        $repository->registerRecordClassForConfigType('config1', 'AnyContent\Client\AlternateConfigRecordClass');

        $config = $repository->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\AlternateConfigRecordClass', $config);

        $config->setProperty('city', 'Hamburg');
    }

}


class AlternateRecordClass extends Record
{

    public function getArticle()
    {
        return $this->getProperty('article');
    }
}

class AlternateConfigRecordClass extends Config
{

    public function getCity()
    {
        return $this->getProperty('city');
    }
}