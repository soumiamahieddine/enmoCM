<?php
/*
 * Copyright (C) 2015 Maarch
 *
 * This file is part of Maarch.
 *
 * Maarch is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Maarch is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Maarch.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Class for database queries
 * 
 * @package Core
 */
class Database
{
    /**
     * Prepared statements indexed by dsn and queryString
     * @var array
     */
    private static $preparedStmt = array();

    private $driver;
    private $server;
    private $port;
    private $user;
    private $password;
    private $database;
    private $dsn;
 
    private $pdo;
    
    private $error;

    private $stmt;

    /**
     * Constructor. Connects to the database if connection parameters are available in the session config 
     */
    public function __construct()
    {
        if (isset($_SESSION['config']['databaseserver'])) {
            $this->server = $_SESSION['config']['databaseserver'];
        }
        if (isset($_SESSION['config']['databaseserverport'])) {
            $this->port = $_SESSION['config']['databaseserverport'];
        }
        if (isset($_SESSION['config']['databaseuser'])) {
            $this->user = $_SESSION['config']['databaseuser'];
        }
        if (isset($_SESSION['config']['databasepassword'])) {
            $this->password = $_SESSION['config']['databasepassword'];
        }
        if (isset($_SESSION['config']['databasename'])) {
            $this->database = $_SESSION['config']['databasename'];
        }
        if (isset($_SESSION['config']['databasetype'])) {
            switch($_SESSION['config']['databasetype']) {
                case 'POSTGRESQL': 
                    $this->driver = 'pgsql';
                    break;
                case 'MYSQL': 
                    $this->driver = 'mysql';
                    break;

                case 'ORACLE':
                    $this->driver = 'oci';
                    break;

                default:
                    print_r('DRIVER ERROR: Unknown database driver ' . $_SESSION['config']['databasetype']);
            }
        }
        
        // Set DSN
        $this->dsn = $this->driver 
            . ':host=' . $this->server
            . ';port=' . $this->port
            . ';dbname=' . $this->database
        ;

        if (!isset(self::$preparedStmt[$this->dsn])) {
            self::$preparedStmt[$this->dsn] = array();
        }

        // Set options
        $options = array (
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE          => PDO::CASE_LOWER
        );
        // Create a new PDO instanace
        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password, $options);
        } catch (PDOException $PDOException) {
            $this->error = $PDOException->getMessage();
        }

        if ($this->error && $_SESSION['config']['debug'] == 'true') {
            print_r('SQL ERROR:' . $this->error);
        }
        
    }

    /**
     * Begin a new transaction
     * 
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback()
    {
        return $this->pdo->rollback();
    }

    /**
     * Check if in a transaction
     * 
     * @return bool
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Prepare a query and returns the statement.
     * Save the prepared statement for a later execution with parameters
     * @param string $queryString The SQL query string to prepare
     * 
     * @return PDOStatement
     */
    public function prepare($queryString)
    {
        if (!isset(self::$preparedStmt[$this->dsn][$queryString])) {
            self::$preparedStmt[$this->dsn][$queryString] = $this->pdo->prepare($queryString);
        }

        return self::$preparedStmt[$this->dsn][$queryString];
    }

    /**
     * Prepare and execute a query. Returns the prepared and executed statement.
     * Statement can be used to fetch resulting rows OR by a later call to a fetch method
     * @param string $queryString     The SQL query string
     * @param array  $parameters      An indexed or associative array of parameters
     * @param bool   $catchExceptions Indicates wheter the PDO exceptions must be caught 
     * 
     * @return PDOStatement The prepared and executed statement
     * 
     * @throws PDOException If a PDO error occurs during preparation or execution
     */
    public function query($queryString, $parameters=null, $catchExceptions=false)
    {
        try {
            $this->stmt = $this->prepare($queryString);

            $executed = $this->stmt->execute($parameters);
        } catch (PDOException $PDOException) {
            if ($catchExceptions) {
                $this->error = $PDOException->getMessage();

                return false;
            } else {
                throw $PDOException;
            }
        }

        return $this->stmt;
    }

}

