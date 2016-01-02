<?php

namespace AnyContent\Client\Util;

use AnyContent\Client\Record;

class RecordsSorter
{

    /**
     * @param Record[] $records
     *
     * @return array
     */
    public static function orderRecords(array $records, $order)
    {

        if (!is_array($order))
        {
            $order = [ $order ];
        }

        $instructions = [ ];
        foreach ($order as $property)
        {
            $property  = trim($property);
            $sortorder = substr($property, -1);

            switch ($sortorder)
            {
                case '-';
                case '+':
                    $property = substr($property, 0, -1);
                    break;
                default:
                    $sortorder = '+';
                    break;

            }

            $instructions[] = [ 'property' => $property, 'order' => $sortorder ];

        }


        uasort($records, function (Record $a, Record $b) use ($instructions)
        {

            foreach ($instructions as $instruction)
            {
                $property = $instruction['property'];
                $order    = $instruction['order'];

                if ($order == '+')
                {
                    if ($a->getProperty($property) < $b->getProperty($property))
                    {
                        return -1;
                    }
                    if ($a->getProperty($property) > $b->getProperty($property))
                    {
                        return 1;
                    }
                }
                else
                {
                    if ($a->getProperty($property) > $b->getProperty($property))
                    {
                        return -1;
                    }
                    if ($a->getProperty($property) < $b->getProperty($property))
                    {
                        return 1;
                    }
                }

            }

        });

        return $records;
    }

}



