<?php

namespace AnyContent\Connection\Interfaces;

use AnyContent\Client\File;
use AnyContent\Client\Folder;

interface FileManager
{

    /**
     * @param string $path
     *
     * @return Folder|bool
     */
    public function getFolder($path = '');


    /**
     * @param $id
     *
     * @return  File|bool
     */
    public function getFile($id);


    public function getBinary(File $file);


    public function saveFile($id, $binary);


    public function deleteFile($id, $deleteEmptyFolder = true);


    public function createFolder($path);


    public function deleteFolder($path, $deleteIfNotEmpty = false);

}