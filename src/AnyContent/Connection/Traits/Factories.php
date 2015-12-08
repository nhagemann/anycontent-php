<?php
namespace AnyContent\Connection\Traits;

use AnyContent\Client\RecordFactory;

trait Factories
{

    /** @var  RecordFactory */
    protected $recordFactory;


    public function getRecordFactory()
    {
        if (!$this->recordFactory)
        {
            $this->recordFactory = new RecordFactory([ 'validateProperties' => false ]);

        }

        return $this->recordFactory;

    }

}