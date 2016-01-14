<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Configuration\RestLikeConfiguration;

use KVMLogger\KVMLoggerFactory;

class RestLikeBasicConnectionConfigTest extends \PHPUnit_Framework_TestCase
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

    public function testTest()
    {

    }

//    public function testConfigSameConnection()
//    {
//        $connection = $this->connection;
//
//        $config = $connection->getConfig('dtag_search_notfound');
//
//        $this->assertInstanceOf('AnyContent\Client\Config', $config);
//
//        $this->assertEquals('', $config->getProperty('city'));
//
//        $config->setProperty('city', 'Frankfurt');
//
//        $connection->saveConfig($config);
//
//        $config = $connection->getConfig('config1');
//
//        $this->assertInstanceOf('AnyContent\Client\Config', $config);
//
//        $this->assertEquals('Frankfurt', $config->getProperty('city'));
//    }
//
//
//    public function testConfigNewConnection()
//    {
//        $connection = $this->connection;
//
//        $config = $connection->getConfig('config1');
//
//        $this->assertInstanceOf('AnyContent\Client\Config', $config);
//
//        $this->assertEquals('Frankfurt', $config->getProperty('city'));
//    }
//
//
//    public function testViewsConfigSameConnection()
//    {
//        $connection = $this->connection;
//
//        $connection->selectView('test');
//
//        $config = $connection->getConfig('config1');
//
//        $this->assertInstanceOf('AnyContent\Client\Config', $config);
//
//        $this->assertEquals('', $config->getProperty('comment'));
//
//        $config->setProperty('comment', 'Test');
//
//        $connection->saveConfig($config);
//
//        $config = $connection->getConfig('config1');
//
//        $this->assertInstanceOf('AnyContent\Client\Config', $config);
//
//        $this->assertEquals('Test', $config->getProperty('comment'));
//
//        $connection->selectView('default');
//
//        $config = $connection->getConfig('config1');
//
//        $this->assertInstanceOf('AnyContent\Client\Config', $config);
//
//        $this->assertEquals('Frankfurt', $config->getProperty('city'));
//
//    }

}