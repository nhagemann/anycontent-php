<?php

namespace AnyContent\Client\Util;



use AnyContent\Filter\PropertyFilter;

class RecordsFilter
{

    public static function filterRecords(array $records, $filter)
    {
        $filter = new PropertyFilter($filter);

        return $records;
    }
}



