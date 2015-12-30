<?php

namespace KVMLogger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class KVMLogger extends AbstractLogger implements LoggerInterface
{

    protected $realm = 'application';

    protected $requestMonitor = false;

    protected $cliMonitor = false;

    protected $logger = [ ];

    /**
     * Log Levels
     *
     * @var array
     */
    protected $logLevels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7
    );


    public function __construct($realm = 'application')
    {
        $this->setRealm($realm);
    }


    /**
     * @return string
     */
    public function getRealm()
    {
        return $this->realm;
    }


    /**
     * @param string $realm
     */
    public function setRealm($realm)
    {
        $this->realm = $realm;
    }


    public function getTiming()
    {
        $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        $time = number_format($time * 1000, 1, '.', '');

        return $time;
    }


    public function createLogMessage($message = '', $logValues = [ ])
    {
        $logMessage = new LogMessage($message);
        $logMessage->setTiming($this->getTiming());

        foreach ($logValues as $k => $v)
        {
            $logMessage->addLogValue($k, $v);
        }

        return $logMessage;
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {

        if ($message instanceof LogMessage)
        {
            if ($message->getRealm() == '')
            {
                $message->setRealm($this->getRealm());
            }
        }
        if (array_key_exists($level, $this->logLevels))
        {

            foreach ($this->logger as $logger)
            {
                if ($this->logLevels[$level] <= $this->logLevels[$logger['threshold']])
                {
                    $logger['logger']->log($level, $message, $context);
                }
            }
        }
    }


    public function logMemoryUsage($message = '', $level = LogLevel::DEBUG, array $context = array())
    {
        $message = $this->createLogMessage($message);
        $message->addLogValue('memory', number_format(memory_get_usage(true) / 1048576, 1, '.', ''));
        $this->log($level, $message, $context);
    }


    public function addLogger(LoggerInterface $logger, $logLevelThreshold = LogLevel::DEBUG, $logMonitoringEvents = true)
    {
        if (array_key_exists($logLevelThreshold, $this->logLevels))
        {
            $this->logger[] = [ 'logger' => $logger, 'threshold' => $logLevelThreshold, 'monitor' => $logMonitoringEvents ];
        }
    }


    public function addMonitor()
    {

    }


    public function enableRequestMonitor($logLevel = LogLevel::DEBUG)
    {
        $this->requestMonitor = $logLevel;
    }


    public function enableCLIMonitor($logLevel = LogLevel::DEBUG)
    {
        $this->cliMonitor = $logLevel;
    }


    public function __destruct()
    {
        if (php_sapi_name() == "cli" && $this->cliMonitor == true)
        {
            $message = $this->createLogMessage();
            $message->setMethod('cli');

            $message->addLogValue('memory', number_format(memory_get_usage(true) / 1048576, 1, '.', ''));
            $message->addLogValue('duration', $message->getTiming());
            if (isset($_SERVER['LOGNAME']))
            {
                $message->addLogValue('user', $_SERVER['LOGNAME']);
            }
            if (isset($_SERVER['SCRIPT_FILENAME']))
            {
                $message->addLogValue('script', $_SERVER['SCRIPT_FILENAME']);
            }
            if (isset($_SERVER['argv']))
            {
                $message->addLogValue('argv', join(' ', $_SERVER['argv']));
            }

            $this->log($this->cliMonitor, $message);
        }

    }

}