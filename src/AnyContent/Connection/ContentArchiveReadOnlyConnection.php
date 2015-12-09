<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadOnly;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;

class ContentArchiveConnection extends AbstractRecordsFileReadOnly implements ReadOnlyConnection
{

    protected $path;

    protected $filename = null;


    public function setContentArchiveFolder($path)
    {
        $this->path = $path;
    }


    public function getContentArchiveFolder()
    {
        return $this->path;
    }


    public function setContentArchiveFile($path)
    {
       // TODO
    }



}