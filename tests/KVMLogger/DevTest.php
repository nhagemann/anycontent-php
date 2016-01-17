<?php

namespace KVMLogger;


class DevTest extends \PHPUnit_Framework_TestCase
{

    public function testLogger()
    {

        KVMLoggerFactory::createWithKLogger(__DIR__);

        $kvm = KVMLogger::instance();
        $kvm->debug('test');

    }
}