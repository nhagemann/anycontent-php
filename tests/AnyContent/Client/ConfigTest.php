<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use CMDL\Parser;

use KVMLogger\KVMLoggerFactory;
use Symfony\Component\Filesystem\Filesystem;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {


        KVMLoggerFactory::createWithKLogger(__DIR__.'/../../../tmp');


    }



    public function testRecordsFileConnection()
    {
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
}
