<?php
namespace AnyContent\Connection\Traits;

use AnyContent\Repository\RecordFactory;

trait Factories
{

    /** @var  RecordFactory */
    protected $recordFactory;


    public function getRecordFactory()
    {
        if (!$this->recordFactory)
        {
            $this->recordFactory = new RecordFactory();

        }

        return $this->recordFactory;

    }

}