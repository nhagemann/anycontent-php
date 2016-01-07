<?php
namespace AnyContent\Connection\Configuration;

use AnyContent\AnyContentClientException;
use AnyContent\Connection\Util\Database;
use AnyContent\Client\DataDimensions;
use AnyContent\Connection\AbstractConnection;
use AnyContent\Connection\ContentArchiveReadOnlyConnection;
use AnyContent\Connection\ContentArchiveReadWriteConnection;
use AnyContent\Connection\MySQLSchemalessReadOnlyConnection;
use AnyContent\Connection\MySQLSchemalessReadWriteConnection;
use Symfony\Component\Finder\Finder;

class MySQLSchemalessConfiguration extends AbstractConfiguration
{

    protected $path;

    /** @var  Database */
    protected $database;


    public function initDatabase($host, $dbName, $username, $password, $port = 3306)
    {
        // http://stackoverflow.com/questions/18683471/pdo-setting-pdomysql-attr-found-rows-fails
        $pdo = new \PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbName, $username, $password, array( \PDO::MYSQL_ATTR_FOUND_ROWS => true ));

        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec("SET NAMES utf8");

        $this->database = new Database($pdo);

        $this->ensureInfoTablesArePresent();
    }


    protected function ensureInfoTablesArePresent()
    {
        $sql = 'SHOW TABLES LIKE ?';

        $stmt = $this->getDatabase()->getConnection()->prepare($sql);
        $stmt->execute(array( '_cmdl_' ));

        if ($stmt->rowCount() == 0)
        {
            $sql = <<< TEMPLATE_CMDLTABLE
        CREATE TABLE `_cmdl_` (
        `repository` varchar(255) NOT NULL DEFAULT '',
        `data_type` ENUM('content', 'config', ''),
        `name` varchar(255) NOT NULL DEFAULT '',
        `cmdl` text,
        `lastchange_timestamp` varchar(16) DEFAULT NULL,
        UNIQUE KEY `index1` (`repository`,`data_type`,`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

TEMPLATE_CMDLTABLE;

            $stmt = $this->getDatabase()->getConnection()->prepare($sql);

            try
            {
                $stmt->execute();
            }
            catch (\PDOException $e)
            {
                throw new AnyContentClientException('Could not create mandatory table _cmdl_');
            }

        }

        $sql = "Show Tables Like '_counter_'";

        $stmt = $this->getDatabase()->getConnection()->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() == 0)
        {
            $sql = <<< TEMPLATE_COUNTERTABLE
CREATE TABLE `_counter_` (
  `repository` varchar(128) NOT NULL DEFAULT '',
  `content_type` varchar(128) NOT NULL DEFAULT '',
  `counter` bigint(20) DEFAULT 0,
  PRIMARY KEY (`repository`,`content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
TEMPLATE_COUNTERTABLE;

            $stmt = $this->getDatabase()->getConnection()->prepare($sql);

            try
            {
                $stmt->execute();
            }
            catch (\PDOException $e)
            {
                throw new AnyContentClientException('Could not create mandatory table _cmdl_');
            }
        }
    }


    public function addContentTypes($repositoryName)
    {
        if (!$this->getDatabase())
        {
            throw new AnyContentClientException('Database must be initalized first.');
        }

        $sql = 'SELECT name, data_type FROM _cmdl_ WHERE repository = ?';

        $rows = $this->getDatabase()->fetchAllSQL($sql, [ $repositoryName ]);

        foreach ($rows as $row)
        {
            if ($row['data_type'] == 'content')
            {
                $contentTypeName                      = $row['name'];
                $this->contentTypes[$contentTypeName] = [ 'title' => null ];
            }
        }
    }


    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }


    /**
     * @param Database $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }


    public function getContentArchiveFolder()
    {
        return $this->path;
    }


    public function apply(AbstractConnection $connection)
    {
        $connection->setDatabase($this->getDatabase());
    }


    public function createReadOnlyConnection()
    {
        return new MySQLSchemalessReadOnlyConnection($this);
    }


    public function createReadWriteConnection()
    {
        return new MySQLSchemalessReadWriteConnection($this);
    }

}