<?php
namespace AnyContent\Connection\Util;

class Database
{

    /** @var  \PDO */
    protected $pdo;


    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /**
     * @return \\PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }


    public function insert($tableName, $insert, $update = false)
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();

        $sql = 'INSERT INTO `' . $tableName;
        $sql .= '` (`' . join('`,`', array_keys($insert)) . '`)';
        $sql .= ' VALUES ( ?';
        $sql .= str_repeat(' , ?', count($insert) - 1);
        $sql .= ')';

        $values = array_values($insert);

        if ($update)
        {
            $sql .= ' ON DUPLICATE KEY UPDATE `' . join('` = ? , `', array_keys($update)) . '` = ?';
            $values = array_merge($values, array_values($update));
        }

        $stmt = $dbh->prepare($sql);

        return $stmt->execute($values);
    }


    public function update($tableName, $update, $where = false)
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();

        $values = array_values($update);

        $sql = ' UPDATE `' . $tableName;
        $sql .= '` SET `' . join('` = ? , `', array_keys($update)) . '` = ?';

        if ($where)
        {
            $sql .= ' WHERE `' . join('` = ? AND `', array_keys($where)) . '` = ?';
            $values = array_merge($values, array_values($where));
        }

        $stmt = $dbh->prepare($sql);

        return $stmt->execute($values);
    }


    public function execute($pdoSQL, $params = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();

        $stmt = $dbh->prepare($pdoSQL);

        return $stmt->execute($params);
    }


    public function fetchOne($tableName, $where = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();
        $sql = 'SELECT * FROM `' . $tableName;
        $sql .= '` WHERE `' . join('` = ? AND `', array_keys($where)) . '` = ?';
        $params = array_values($where);
        $stmt   = $dbh->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetch();

    }


    public function fetchOneSQL($pdoSQL, $params = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();

        $stmt = $dbh->prepare($pdoSQL);

        $stmt->execute($params);

        return $stmt->fetch();

    }


    public function fetchColumn($tableName, $column, $where = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();
        $sql = 'SELECT * FROM `' . $tableName;
        $sql .= '` WHERE `' . join('` = ? AND `', array_keys($where)) . '` = ?';
        $params = array_values($where);
        $stmt   = $dbh->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchColumn($column);

    }


    public function fetchColumnSQL($pdoSQL, $column, $params = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();

        $stmt = $dbh->prepare($pdoSQL);

        $stmt->execute($params);

        return $stmt->fetchColumn($column);

    }


    public function fetchAll($tableName, $where = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();
        $sql = 'SELECT * FROM `' . $tableName;
        $sql .= '` WHERE `' . join('` = ? AND , `', array_keys($where)) . '` = ?';
        $params = array_values($where);
        $stmt   = $dbh->prepare($sql);

        $stmt->execute($params);

        $rows = $stmt->fetchAll();

        return $rows;

    }


    public function fetchAllSQL($pdoSQL, $params = array())
    {
        /** @var \PDO $db */
        $dbh = $this->getConnection();

        $stmt = $dbh->prepare($pdoSQL);

        $stmt->execute($params);

        $rows = $stmt->fetchAll();

        return $rows;

    }


    /**
     * http://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-\PDO-prepared-statements
     */
    public function debugPrintQuery($pdoSQL, $params = array())
    {
        $keys = array();

        # build a regular expression for each parameter
        foreach ($params as $key => &$value)
        {
            if (is_string($key))
            {
                $keys[] = '/:' . $key . '/';
            }
            else
            {
                $keys[] = '/[?]/';
            }
            $value = '#' . $value . '#';
        }

        $query = preg_replace($keys, $params, $pdoSQL, 1, $count);

        return $query;
    }

}