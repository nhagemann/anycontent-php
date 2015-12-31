<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;

use AnyContent\Connection\ContentArchiveReadWriteConnection;

use Symfony\Component\Filesystem\Filesystem;

class RepositoryLanguagesAndWorkspacesTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadWriteConnection */
    public $connection;

    /** @var  Repository */
    public $repository;


    public static function setUpBeforeClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';
        $source = __DIR__ . '/../../resources/ExampleContentArchive';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

    }


    public static function tearDownAfterClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';

        $fs = new Filesystem();
        $fs->remove($target);

    }


    public function setUp()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';

        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder($target);

        $connection = $configuration->createReadWriteConnection();

        $this->connection = $connection;

        $this->repository = new Repository($this->connection);

    }


    public function testSaveRecords()
    {
        $this->repository->selectContentType('example01');

        for ($i = 1; $i <= 5; $i++)
        {
            $record = $this->repository->createRecord('New Record ' . $i);
            $record->setProperty('article', 'Test ' . $i);
            $id = $this->repository->saveRecord($record);
            $this->assertEquals($i, $id);
        }

        $this->repository->selectLanguage('es');

        for ($i = 1; $i <= 5; $i++)
        {
            $record = $this->repository->createRecord('New Record ' . (5 + $i));
            $record->setProperty('article', 'Test ' . (5 + $i));
            $id = $this->repository->saveRecord($record);
            $this->assertEquals(5 + $i, $id);
        }

        $this->repository->selectWorkspace('live');

        for ($i = 1; $i <= 5; $i++)
        {
            $record = $this->repository->createRecord('New Record ' . (10 + $i));
            $record->setProperty('article', 'Test ' . (10 + $i));
            $id = $this->repository->saveRecord($record);
            $this->assertEquals(10 + $i, $id);
        }

        $this->repository->reset();

        $c              = $this->repository->countRecords();
        $this->assertEquals(5, $c);

        $this->repository->selectLanguage('es');
        $c = $this->repository->countRecords();
        $this->assertEquals(5, $c);

        $this->repository->selectWorkspace('live');
        $c = $this->repository->countRecords();
        $this->assertEquals(5, $c);

        $this->repository->selectLanguage('default');
        $c = $this->repository->countRecords();
        $this->assertEquals(0, $c);;
    }

}