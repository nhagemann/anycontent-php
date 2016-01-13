<?php

namespace KVMLogger;

use Katzgrau\KLogger\Logger;
use Psr\Log\LogLevel;

class KVMLoggerFactory
{

    /**
     * @var KVMLogger
     */
    private static $instance = null;


    /**
     * @param string $namespace
     *
     * @return KVMLogger
     */
    public static function create($namespace = 'application')
    {
        $kvmLogger = new KVMLogger($namespace);

        self::$instance = $kvmLogger;

        return $kvmLogger;
    }


    /**
     * @param string $realm
     *
     * @return KVMLogger
     */
    public static function createWithKLogger($path, $logLevelThreshold = LogLevel::DEBUG, $realm = 'application', $options = [ 'filename' => 'kvm.log' ])
    {

        $kLogger = new Logger($path, LogLevel::DEBUG, $options);

        $kvmLogger = new KVMLogger($realm);;

        $kvmLogger->addLogger($kLogger, $logLevelThreshold);

        self::$instance = $kvmLogger;

        return $kvmLogger;
    }


    /**
     * @param string $namespace
     *
     * @return KVMLogger
     */
    public static function instance($namespace = 'application')
    {
        if (!self::$instance)
        {
            self::$instance = new KVMNullLogger();
        }

        $kvmLogger = self::$instance;
        $kvmLogger->setNamespace($namespace);

        return $kvmLogger;
    }

}