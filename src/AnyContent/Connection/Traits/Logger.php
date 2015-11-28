<?php
namespace AnyContent\Connection\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait Logger
{

    /** @var  LoggerInterface */
    protected $logger;


    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /** @var  LoggerInterface */
    public function getLogger()
    {
        if ($this->logger)
        {
            return $this->logger;
        }

        return new NullLogger();
    }
}