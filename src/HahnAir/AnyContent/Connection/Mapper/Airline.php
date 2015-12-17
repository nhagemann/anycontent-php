<?php
namespace HahnAir\AnyContent\Connection\Mapper;

use AnyContent\Client\Record;

class Airline extends Mapper
{

    public function mapEntity(Record $record, $data)
    {
        $record = parent::mapEntity($record, $data);

        $record->setProperty('status', (int)$data['status']);

        $mapping = [ 'code'          => 'code',
                     'history'       => 'history',
                     'fleet'         => 'fleet',
                     'service'       => 'service',
                     'onlinecheckin' => 'onlinecheckin',
                     'profile'       => 'profile',
        ];

        foreach ($mapping as $fieldName => $property)
        {
            $record->setProperty($property, $this->getFieldValue($fieldName));
        }

        return $record;
    }
}


