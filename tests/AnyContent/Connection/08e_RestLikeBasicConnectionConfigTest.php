<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Configuration\RestLikeConfiguration;

use KVMLogger\KVMLoggerFactory;

class RestLikeBasicConnectionConfigTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RestLikeBasicReadWriteConnection */
    public $connection;

    static $randomString;


    public static function setUpBeforeClass()
    {
        self::$randomString = md5(time());
    }


    public function setUp()
    {
        if (defined('PHPUNIT_CREDENTIALS_RESTLIKE_URL'))
        {
            $configuration = new RestLikeConfiguration();

            $configuration->setUri(PHPUNIT_CREDENTIALS_RESTLIKE_URL);
            $connection = $configuration->createReadWriteConnection();

            $configuration->addContentTypes();
            $configuration->addConfigTypes();

            $this->connection = $connection;

            KVMLoggerFactory::createWithKLogger(__DIR__ . '/../../../tmp');
        }

    }


    public function testConfigSameConnection()
    {
        $connection = $this->connection;

        $config = $connection->getConfig('dtag_search_notfound');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertTrue($config->hasProperty('copytext5'));

        $config->setProperty('copytext5', self::$randomString);

        $connection->saveConfig($config);

        $config = $connection->getConfig('dtag_search_notfound');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals(self::$randomString, $config->getProperty('copytext5'));
    }


    public function testConfigNewConnection()
    {
        $connection = $this->connection;

        $config = $connection->getConfig('dtag_search_notfound');

        $this->assertInstanceOf('AnyContent\Client\Config', $config);

        $this->assertEquals(self::$randomString, $config->getProperty('copytext5'));
    }


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