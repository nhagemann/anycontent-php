<?php

namespace AnyContent\Client;

use CMDL\DataTypeDefinition;

abstract class AbstractRecord
{

    /** @var  DataTypeDefinition */
    protected $dataTypeDefinition;

    protected $workspace = 'default';
    protected $language = 'default';

    public $properties = array();

    public $revision = 0;


    public function getDataTypeName()
    {
        return $this->dataTypeDefinition->getName();
    }


    public function getDataTypeDefinition()
    {
        return $this->dataTypeDefinition;
    }


    public function hasProperty($property, $viewName = null)
    {
        return $this->dataTypeDefinition->hasProperty($property, $viewName);
    }

}