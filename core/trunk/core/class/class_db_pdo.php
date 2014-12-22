<?php

class Database {

    private $databasetype;
    private $server;
    private $port;
    private $user;
    private $password;
    private $database;
 
    private $dbh;
    private $error;

    private $stmt;

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
            $this->databasetype = $_SESSION['config']['databasetype'];
            if ($this->databasetype == 'POSTGRESQL') {
                $this->databasetype = 'pgsql';
            } elseif ($this->databasetype == 'MYSQL') {
                $this->databasetype = 'mysql';
            } elseif ($this->databasetype == 'ORACLE') {
                $this->databasetype = 'oracle';
            }
        }
        
        // Set DSN
        $dsn = $this->databasetype 
        	. ':host=' . $this->server
        	. ';port=' . $this->port
        	. ';dbname=' . $this->database;

        // Set options
        $options = array (
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );
        // Create a new PDO instanace
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->password, $options);
        }
        // Catch any errors
        catch (PDOException $e) {
            $this->error = $e->getMessage();
        }

        if ($this->error && $_SESSION['config']['debug'] == 'true') {
        	print_r('SQL ERROR:' . $this->error);
        }
        
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultset() 
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->dbh->beginTransaction();
    }

    public function endTransaction()
    {
        return $this->dbh->commit();
    }

    public function cancelTransaction()
    {
        return $this->dbh->rollBack();
    }

    public function debugDumpParams()
    {
	    return $this->stmt->debugDumpParams();
    }
}

