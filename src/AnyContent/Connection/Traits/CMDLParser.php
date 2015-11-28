<?php
namespace AnyContent\Connection\Traits;

use CMDL\Parser;

trait CMDLParser
{

    public function getParser()
    {
        return new Parser();
    }
}