<?php

namespace AnyContent\Repository;

use AnyContent\Connection\RecordsFileGitReadWriteConnection;

class RecordsFileGitReadWriteConnectionTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileGitReadWriteConnection */
    public $connection;

    static $randomString;


    public static function setUpBeforeClass()
    {
        self::$randomString = md5(time());
    }


    public function setUp()
    {

        $connection = new RecordsFileGitReadWriteConnection();

        $options = [ 'filenameRecords' => 'phpunit/profiles.json', 'filenameCMDL' => 'phpunit/profiles.cmdl', 'repositoryUrl' => 'git@bitbucket.org:nhagemann/anycontent-git-repository.git', 'repositoryPath' => __DIR__ . '/../../../tmp/git', 'fileNamePrivateKey' => '/home/vagrant/.ssh/id_rsa' ];

        $connection->addContentType($options);
        $this->connection = $connection;

        $connection->getGit()->config('user.name', 'nhagemann');
        $connection->getGit()->config('user.email', 'nhagemann@bitbucket.org');

    }


    public function testContentTypeNotSelected()
    {
        $connection = $this->connection;

        $this->setExpectedException('AnyContent\AnyContentClientException');
        $this->assertEquals(12, $connection->countRecords());
    }


    public function testContentTypeNames()
    {
        $connection = $this->connection;

        $contentTypeNames = $connection->getContentTypeNames();

        $this->assertContains('profiles', $contentTypeNames);
    }


    public function testContentTypeDefinitions()
    {
        $connection = $this->connection;

        $contentTypes = $connection->getContentTypes();

        $this->assertArrayHasKey('profiles', $contentTypes);

        $contentType = $contentTypes['profiles'];
        $this->assertInstanceOf('CMDL\ContentTypeDefinition', $contentType);
    }


    public function testCountRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $this->assertEquals(608, $connection->countRecords());

    }


    public function testGetRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(1);

        $this->assertInstanceOf('AnyContent\Repository\Record', $record);

        $this->assertEquals('UDG United Digital Group', $record->getProperty('name'));

    }


    public function testGetRecords()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $records = $connection->getAllRecords();

        $this->assertCount(608, $records);

        foreach ($records as $record)
        {
            $id          = $record->getID();
            $fetchRecord = $connection->getRecord($id);
            $this->assertEquals($id, $fetchRecord->getID());
        }
    }


    public function testChangeRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(5);

        $this->assertInstanceOf('AnyContent\Repository\Record', $record);

        $record->setProperty('name', self::$randomString);

        $connection->saveRecord($record);

    }


    public function testChangedRecord()
    {
        $connection = $this->connection;

        $connection->selectContentType('profiles');

        $record = $connection->getRecord(5);

        $this->assertInstanceOf('AnyContent\Repository\Record', $record);

        $this->assertEquals(self::$randomString, $record->getName());

    }
}