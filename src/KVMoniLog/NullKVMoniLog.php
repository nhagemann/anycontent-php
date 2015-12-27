<?php

namespace KVMoniLog;

use Psr\Log\LoggerInterface;

class NullKVMoniLog extends KVMoniLog implements LoggerInterface
{
    public function log($level, $message, array $context = array())
    {
        //nop
    }
}