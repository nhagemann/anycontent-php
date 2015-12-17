<?php
namespace HahnAir\AnyContent\Connection\Mapper;

use AnyContent\Client\Record;

abstract class Mapper
{

    protected $data;

    /** @var  Record */
    protected $record;


    public function mapEntity(Record $record, $data)
    {
        $this->data   = $data;
        $this->record = $record;
        $this->record->setId($this->data['nid']);
        $this->record->setProperty('name', $this->data['title']);

        return $this->record;

    }


    public function getFieldValue($fieldName)
    {
        $fieldName = 'field_' . $fieldName;
        if (array_key_exists($fieldName, $this->data))
        {
            if (array_key_exists('und', $this->data[$fieldName]))
            {

                $values = [ ];
                foreach ($this->data[$fieldName]['und'] as $field)
                {
                    if ($field['value'] != '')
                    {
                        $values[] = $field['value'];
                    }
                }

                return join(PHP_EOL, $values);
            }

        }

        return '';
    }
}
