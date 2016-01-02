<?php

namespace AnyContent\Client;

use AnyContent\Connection\Configuration\ContentArchiveConfiguration;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use CMDL\Parser;
use Symfony\Component\Filesystem\Filesystem;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ContentArchiveReadWriteConnection */
    public $connection;


    public static function setUpBeforeClass()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';
        $source = __DIR__ . '/../../resources/ContentArchiveExample2';

        $fs = new Filesystem();

        if (file_exists($target))
        {
            $fs->remove($target);
        }

        $fs->mirror($source, $target);

    }


    public function setUp()
    {
        $target = __DIR__ . '/../../../tmp/ExampleContentArchive';

        $configuration = new ContentArchiveConfiguration();

        $configuration->setContentArchiveFolder($target);

        $connection = $configuration->createReadWriteConnection();

        $this->connection = $connection;
    }


    public function testContentTypes()
    {
        $repository = new Repository($this->connection);

        $contentTypeNames = $repository->getContentTypeNames();

        $this->assertCount(6,$contentTypeNames);

        $this->assertTrue($repository->hasContentType('example01'));
    }
}