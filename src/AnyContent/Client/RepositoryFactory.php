<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;

class RepositoryFactory
{

    public function createContentArchiveRepository($folder)
    {
        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder($folder);

        $connection = $configuration->createReadWriteConnection();

        $repository = new Repository($connection);

        return $repository;

    }
}

