<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;

class RepositoryFactory
{

    public function createContentArchiveRepository($name, $folder)
    {
        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder($folder);

        $connection = $configuration->createReadWriteConnection();

        $repository = new Repository($name, $connection);

        return $repository;

    }
}

