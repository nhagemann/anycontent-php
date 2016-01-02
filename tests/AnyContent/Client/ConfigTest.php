<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use CMDL\Parser;

use KVMLogger\KVMLoggerFactory;
use Symfony\Component\Filesystem\Filesystem;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');
    }


    public function testRecordsFileReadOnlyConnection()
    {

        $configuration = new RecordsFileConfiguration();

        $configuration->addConfigType('config1', __DIR__ . '/../../resources/RecordsFileExample/config1.cmdl', __DIR__ . '/../../resources/RecordsFileExample/config1.json');

        $connection = $configuration->createReadOnlyConnection();

        $config = $connection->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals('', $config->getProperty('city'));
        //var_dump ($config);

        /*

        $repository = $this->client->getRepository();

        $config = $repository->getConfig('config1');

        $this->assertEquals('Madrid',$config->getProperty('city'));
        $this->assertEquals('Spain',$config->getProperty('country'));

        $config = $repository->getConfig('config2');

        $this->assertEquals('',$config->getProperty('value1'));
        $this->assertEquals('',$config->getProperty('value2'));
        $this->assertEquals('',$config->getProperty('value3'));
        $this->assertEquals('',$config->getProperty('value4'));


        $config->setProperty('value1','a');
        $repository->saveConfig($config);
        $config->setProperty('value1','');
        $repository->saveConfig($config);
        */
    }


    public function testRecordsFileReadWriteConnection()
    {
        $target = __DIR__ . '/../../../tmp/RecordsFileExample';
        $source = __DIR__ . '/../../resources/RecordsFileExample';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

        $configuration = new RecordsFileConfiguration();

        $configuration->addConfigType('config1', __DIR__ . '/../../../tmp/RecordsFileExample/config1.cmdl', __DIR__ . '/../../../tmp/RecordsFileExample/config1.json');

        $connection = $configuration->createReadWriteConnection();

        $config = $connection->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals('', $config->getProperty('city'));

        $config->setProperty('city', 'Frankfurt');

        $connection->saveConfig($config);

        $config = $connection->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals('Frankfurt', $config->getProperty('city'));

    }

    public function testRecordsFileNewReadWriteConnection()
    {
        $configuration = new RecordsFileConfiguration();

        $configuration->addConfigType('config1', __DIR__ . '/../../../tmp/RecordsFileExample/config1.cmdl', __DIR__ . '/../../../tmp/RecordsFileExample/config1.json');

        $connection = $configuration->createReadWriteConnection();

        $config = $connection->getConfig('config1');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals('Frankfurt', $config->getProperty('city'));

    }
}
