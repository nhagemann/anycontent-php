<?php

namespace AnyContent\Client;

use CMDL\DataTypeDefinition;
use CMDL\Util;

class DataDimensions
{

    /** @var  DataTypeDefinition */
    protected $definition;

    protected $viewName = 'default';

    protected $workspace = 'default';

    protected $language = 'default';

    protected $timeShift = 0;

    const MAX_TIMESHIFT = 315532800; // roundabout 10 years, equals to 1.1.1980


    public function __construct(DataTypeDefinition $definition)
    {
        $this->definition = $definition;

    }


    /**
     * @return null
     */
    public function getViewName()
    {
        return $this->viewName;
    }


    /**
     * @param null $viewName
     */
    public function setViewName($viewName)
    {
        $this->viewName = $viewName;
    }


    /**
     * @return null
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }


    /**
     * @param null $workspace
     */
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }


    /**
     * @return null
     */
    public function getLanguage()
    {
        return $this->language;
    }


    /**
     * @param null $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }


    /**
     * @return null
     */
    public function getTimeShift()
    {
        return $this->timeShift;
    }


    /**
     * @param null $timeShift
     */
    public function setTimeShift($timeShift)
    {
        $this->timeShift = $timeShift;
    }


    /**
     *
     */
    public function getTimeStamp()
    {
        if ($this->timeShift < self::MAX_TIMESHIFT)
        {
            return time() - $this->timeShift;
        }

        return 0;
    }


    public function __toString()
    {
        return 'workspace: ' . $this->getWorkspace() . ', language: ' . $this->getLanguage() . ', view: ' . $this->getViewName() . ', timestamp: ' . $this->getTimeStamp();
    }
}