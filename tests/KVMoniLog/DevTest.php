<?php

namespace KVMoniLog;

use Katzgrau\KLogger\Logger;

class DevTest extends \PHPUnit_Framework_TestCase
{

    public function testLogger()
    {

        $klogger = new Logger(__DIR__);

        $moniLog = new KVMoniLog();

        $moniLog->addLogger($klogger);
        $moniLog->enableCLIMonitor();


        $message = $moniLog->createKVLogMessage('test');

        $moniLog->debug($message);


    }
}