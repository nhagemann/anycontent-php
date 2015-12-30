<?php

namespace KVMLogger;

use Katzgrau\KLogger\Logger;

class KVMLoggerFactory
{

    /**
     * @var KVMLogger
     */
    private static $instance = null;


    /**
     * @param string $realm
     *
     * @return KVMLogger
     */
    public static function create($realm = 'application')
    {
        $kvmLogger = new KVMLogger($realm);

        self::$instance = $kvmLogger;

        return $kvmLogger;
    }


    /**
     * @param string $realm
     *
     * @return KVMLogger
     */
    public static function createWithKLogger($path, $realm = 'application')
    {

        $kLogger = new Logger($path);

        $kvmLogger = new KVMLogger($realm);;

        $kvmLogger->addLogger($kLogger);

        self::$instance = $kvmLogger;

        return $kvmLogger;
    }


    /**
     * @param string $realm
     *
     * @return KVMLogger
     */
    public static function instance($realm = null)
    {
        if (!self::$instance)
        {
            self::$instance = new KVMNullLogger();
        }

        $kvmLogger = self::$instance;
        if ($realm)
        {
            $kvmLogger->setRealm($realm);
        }

        return $kvmLogger;
    }

}