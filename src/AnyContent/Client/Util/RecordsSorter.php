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


    /**
     * @param Record[] $records
     *
     * @return array
     */
    public static function sortRecords(array $records, $parentId = 0, $includeParent = true, $depth = null)
    {
        $list = [ ];
        $map  = [ ];

        $records = self::orderRecords($records, 'position');

        foreach ($records as $record)
        {
            if ($record->getParent() === 0 || $record->getParent() > 0) // include 0 and numbers, exclude null and ''
            {
                $map[$record->getId()] = $record;
                $list[]                = [ 'id' => $record->getId(), 'parentId' => $record->getParent() ];
            }
        }

        $util      = new AdjacentList2NestedSet($list);
        $nestedSet = $util->getNestedSet();

        $result = [ ];

        if ($parentId != 0)
        {
            $root  = $nestedSet[$parentId];
            $depth = $depth + $root['level'];

            if ($includeParent)
            {
                $result[$parentId] = $map[$parentId];
            }
        }

        foreach ($nestedSet as $id => $positioning)
        {

            if ($depth === null || $positioning['level'] <= $depth)
            {
                if ($parentId == 0 || ($positioning['left'] > $root['left'] && $positioning['right'] < $root['right']))
                {
                    $result[$id] = $map[$id];
                }
            }
        }

        return $result;

    }
}



