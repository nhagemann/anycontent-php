<?php

namespace AnyContent\Connection;

use AnyContent\Client\Record;
use AnyContent\Connection\Configuration\RecordsFileConfiguration;

class RecordStashTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RecordsFileReadOnlyConnection */
    public $connection;


    public function setUp()
    {
        $configuration = new RecordsFileConfiguration();

        $configuration->addContentType('profiles', __DIR__ . '/../../resources/SimpleFileConnection/profiles.cmdl', __DIR__ . '/../../resources/SimpleFileConnection/profiles.json');

        $connection = $configuration->createReadOnlyConnection();

        $connection->selectContentType('profiles');

        $this->connection = $connection;
    }


    public function testSingleRecordStashed()
    {
        $record = new Record($this->connection->getCurrentContentType(), 'New Record');
        $record->setID(1);

        $dataDimensions = $this->connection->getCurrentDataDimensions();

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertFalse($result);

        $this->invokeMethod($this->connection, 'stashRecord', [ $record, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertTrue($result);

        $this->invokeMethod($this->connection, 'unStashRecord', [ 'profiles', 1, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertFalse($result);

        $this->invokeMethod($this->connection, 'unStashRecord', [ 'profiles', 1, $dataDimensions ]);

    }


    public function testDifferingDimensionsStashed()
    {
        $record = new Record($this->connection->getCurrentContentType(), 'New Record');
        $record->setID(1);

        $dataDimensions = $this->connection->getCurrentDataDimensions();

        $this->invokeMethod($this->connection, 'stashRecord', [ $record, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertTrue($result);

        $dataDimensions->setWorkspace('live');

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertFalse($result);

        $this->invokeMethod($this->connection, 'stashRecord', [ $record, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertTrue($result);

    }


    public function testRelativeTimeShift()
    {
        $record = new Record($this->connection->getCurrentContentType(), 'New Record');
        $record->setID(1);

        $dataDimensions = $this->connection->getCurrentDataDimensions();

        $dataDimensions->setTimeShift(60);

        $this->invokeMethod($this->connection, 'stashRecord', [ $record, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertFalse($result);
    }


    public function testAlternateRecordClass()
    {
        $record = new AlternateRecordClass($this->connection->getCurrentContentType(), 'New Record');
        $record->setID(1);

        $dataDimensions = $this->connection->getCurrentDataDimensions();

        $this->invokeMethod($this->connection, 'stashRecord', [ $record, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions ]);
        $this->assertFalse($result);

        $result = $this->invokeMethod($this->connection, 'hasStashedRecord', [ 'profiles', 1, $dataDimensions, 'AnyContent\Connection\AlternateRecordClass' ]);
        $this->assertTrue($result);
    }


    public function testAllRecordsStash()
    {
        $record1 = new Record($this->connection->getCurrentContentType(), 'New Record');
        $record1->setID(1);

        $record2 = new Record($this->connection->getCurrentContentType(), 'New Record');
        $record2->setID(2);

        $allRecords = [ $record1, $record2 ];

        $dataDimensions = $this->connection->getCurrentDataDimensions();

        $result = $this->invokeMethod($this->connection, 'hasStashedAllRecords', [ 'profiles', $dataDimensions ]);
        $this->assertFalse($result);

        $this->invokeMethod($this->connection, 'stashRecord', [ $record1, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedAllRecords', [ 'profiles', $dataDimensions ]);
        $this->assertFalse($result);

        $this->invokeMethod($this->connection, 'stashAllRecords', [ $allRecords, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedAllRecords', [ 'profiles', $dataDimensions ]);
        $this->assertTrue($result);

        $result = $this->invokeMethod($this->connection, 'getStashedAllRecords', [ 'profiles', $dataDimensions ]);

        $this->assertCount(2, $result);

        $this->invokeMethod($this->connection, 'unStashRecord', [ 'profiles', 1, $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'getStashedAllRecords', [ 'profiles', $dataDimensions ]);

        $this->assertCount(1, $result);

        $this->invokeMethod($this->connection, 'unStashAllRecords', [ 'profiles', $dataDimensions ]);

        $result = $this->invokeMethod($this->connection, 'hasStashedAllRecords', [ 'profiles', $dataDimensions ]);
        $this->assertFalse($result);

        $result = $this->invokeMethod($this->connection, 'getStashedAllRecords', [ 'profiles', $dataDimensions ]);

        $this->assertFalse($result);
    }


    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}


class AlternateRecordClass extends Record
{

    public function getArticle()
    {
        return $this->getProperty('article');
    }
}