<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\RecordsFileConfiguration;
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
