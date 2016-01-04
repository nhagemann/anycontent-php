<?php
namespace HahnAir\AnyContent\Connection\Mapper;

use AnyContent\Client\Record;

class Airline extends Mapper
{

    public function mapEntity(Record $record, $data)
    {
        $record = parent::mapEntity($record, $data);


        $properties = $record->getProperties();

        $properties['status'] = (int)$data['status'];
        //$record->setProperty('status', (int)$data['status']);

        $mapping = [ 'field_code'                   => 'code',
                     'field_airline_history'        => 'history',
                     'field_airline_fleet'          => 'fleet',
                     'field_airline_services'       => 'services',
                     'field_airline_ffp'            => 'ffp',
                     'field_airline_online_checkin' => 'onlinecheckin',
                     'field_airline_lounges'        => 'lounges',
                     'field_foundation_year'        => 'year'
        ];

        foreach ($mapping as $fieldName => $property)
        {
            $properties[$property] = $this->getFieldValue($fieldName);
            //$record->setProperty($property, $this->getFieldValue($fieldName));
        }

        $sequence   = [ ];
        $sequence[] = [ 'richtext' => [ 'richtext' => $this->getFieldValue('field_airline_profile') ] ];

        $properties['content'] = json_encode($sequence);
        //$record->setProperty('content',json_encode($sequence));

        $record->setProperties($properties);

        return $record;
    }
}


