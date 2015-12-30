<?php

namespace KVMoniLog;

trait KVMoniLogAwareTrait
{

    /** @var KVMoniLog */
    protected $kvmonilog;


    public function setKVMoniLog(KVMoniLog $logger)
    {
        $this->kvmonilog = $logger;
    }


    public function getKVMoniLog($realm = 'application')
    {
        if (!$this->kvmonilog)
        {
            $this->kvmonilog = new NullKVMoniLog();
        }

        $this->kvmonilog->setRealm($realm);

        return $this->kvmonilog;
    }
}
