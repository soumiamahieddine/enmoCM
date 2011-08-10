<?php
/*
*    Copyright 2008 - 2011 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief   Embedded sql functions (connection, database selection, query ).
* Allow to changes the databases server
*
* @file
* @author  Claire Figueras <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
* @author  Loic Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Embedded sql functions (connection, database selection, query ).
* Allow to changes the databases server
*
* <ul>
*  <li>Compatibility with the following databases : Mysql, Postgres,
*  		Mssql Server, Oracle
*  <li>Connection to the Maarch database</li>
* <li>Execution of SQL queries to the Maarch database</li>
* <li>Getting results of SQL queries</li>
* <li>Managing the database errors</li>
* </ul>
* @ingroup core
*/
class dbquery extends functions
{
    /**
    * Debug mode activation.
    * Integer 1,0
         */
    private $_debug;             // debug mode

    /**
    * Debug query (debug mode). String
    */
    private $_debugQuery;       // request for the debug mode

    /**
    * SQL link identifier
    * Integer
    */
    private $_sqlLink;          // sql link identifier


    /**
    * To know where the script was stopped
    *  Integer
    */
    private $_sqlError;    // to know where the script was stopped

    /**
    * SQL query
    * String
         */
    public $query;              // query

    /**
    * Number of queries made with this identifier
         * Integer
         */
    private $_nbQuery;          // number of queries made with this identifier

    /**
    * Sent query result
         * String
         */
    private $_result;            // sent query result

    /**
    * OCI query identifier
    * @access private
    * @var integer
    */
    private $_statement  ;       // OCI query identifier

    private $_server;
    private $_port;
    private $_user;
    private $_password;
    private $_database;
    private $_databasetype;
    //private $workspace;

    public function __construct()
    {
        $args = func_get_args();
        if (count($args) < 1) {
            if (isset($_SESSION['config']['databaseserver'])) {
                $this->_server = $_SESSION['config']['databaseserver'];
            }
            if (isset($_SESSION['config']['databaseserverport'])) {
                $this->_port = $_SESSION['config']['databaseserverport'];
            }
            if (isset($_SESSION['config']['databaseuser'])) {
                $this->_user = $_SESSION['config']['databaseuser'];
            }
            if (isset($_SESSION['config']['databasepassword'])) {
                $this->_password = $_SESSION['config']['databasepassword'];
            }
            if (isset($_SESSION['config']['databasename'])) {
                $this->_database = $_SESSION['config']['databasename'];
            }
            //$this->workspace = $_SESSION['config']['databaseworkspace'];
            if (isset($_SESSION['config']['databasetype'])) {
                $this->_databasetype = $_SESSION['config']['databasetype'];
            }
        } else {
            $errorArgs = true;
            if (is_array($args[0])) {
                if (! isset($args[0]['server'])) {
                    $this->_server = '127.0.0.1';
                } else {
                    $this->_server = $args[0]['server'];
                }
                if (! isset($args[0]['databasetype'])) {
                    $this->_databasetype = 'MYSQL';
                } else {
                    $this->_databasetype = $args[0]['databasetype'];
                }
                if (! isset($args[0]['port'])) {
                    $this->_port = '3304';
                } else {
                    $this->_port = $args[0]['port'];
                }
                if (! isset($args[0]['user'])) {
                    $this->_user = 'root';
                } else {
                    $this->_user = $args[0]['user'];
                }
                //if(!isset($args[0]['workspace']))
                //{
                //  $this->workspace = 'public';
                //}
                //else
                //{
                //  $this->workspace = $args[0]['workspace'];
                //}
                if (! isset($args[0]['pass'])) {
                    $this->_password = '';
                } else {
                    $this->_password = $args[0]['pass'];
                }
                if (! isset($args[0]['base'])) {
                    $this->_database = '';
                } else {
                    $this->_database = $args[0]['base'];
                }
                $errorArgs = false;
            } else if (is_string($args[0]) && file_exists($args[0])) {
                $xmlconfig = simplexml_load_file($args[0]);
                $config = $xmlconfig->CONFIG_BASE;
                $this->_server = (string) $config->databaseserver;
                $this->_port = (string) $config->databaseserverport;
                $this->_databasetype = (string) $config->databasetype;
                $this->_database = (string) $config->databasename;
                $this->_user = (string) $config->databaseuser;
                $this->_password = (string) $config->databasepassword;
                //if (isset($config->databaseworkspace)) {
                //  $this->workspace = (string) $config->databaseworkspace;
                // }
                $errorArgs = false;
            }
            if ($errorArgs) {
                $this->_sqlError = 5; // error constructor
                $this->error();
            }
        }
    }
    /**
    * Connects to the database
    *
    */
    public function connect()
    {
        $this->_debug = 0;
        $this->_nbQuery = 0;
        if ($this->_databasetype == 'MYSQL') {
            $this->_sqlLink = @mysqli_connect(
                $this->_server,
                $this->_user,
                $this->_password,
                $this->_database,
                $this->_port
            );
        } else if ($this->_databasetype == 'SQLSERVER') {
            $this->_sqlLink = @mssql_connect(
                $this->_server, $this->_user, $this->_password
            );
        } else if ($this->_databasetype == 'POSTGRESQL') {
            $this->_sqlLink = @pg_connect(
            	'host=' . $this->_server . ' user=' . $this->_user
                . ' password=' . $this->_password . ' dbname='
                . $this->_database . ' port=' . $this->_port
               // , PGSQL_CONNECT_FORCE_NEW
            );
        } else if ($this->_databasetype == 'ORACLE') {
            if ($this->_server <> '') {
                $this->_sqlLink = oci_connect(
                    $this->_user, $this->_password, '//' . $this->_server . '/'
                    . $this->_database, 'UTF8'
                );
            } else {
                $this->_sqlLink = oci_connect(
                    $this->_user, $this->_password, $this->database, 'UTF8'
                );
            }
            // ALTER SESSIONS MUST BE MANAGED BY TRIGGERS DIRECTLY IN THE DB
            //$this->query("alter session set nls_date_format='dd-mm-yyyy'");
        } else {
            $this->_sqlLink = false;
        }

        if (! $this->_sqlLink) {
            $this->_sqlError = 1; // error connexion
            $this->error();
        } else {
            if ($this->_databasetype <> 'POSTGRESQL'
                && $this->_databasetype <> 'MYSQL'
                && $this->_databasetype <> 'ORACLE'
            ) {
                $this->select_db();
            }
        }
    }

    /**
    * Database selection (only for SQLSERVER)
    *
    */
    public function select_db()
    {
        if ($this->_databasetype == 'SQLSERVER') {
            if (! @mssql_select_db($this->_database)) {
                $this->_sqlError = 2;
                $this->error();
            }
        }
    }

    /**
    * Test if the specified column exists in the database
    *
    * @param  $table : Name of searched table
    * @param  $field : Name of searched field in table
    *  ==Return : true is field is founed, false is not
    */
    public function test_column($table, $field)
    {
        if ($this->_databasetype == 'SQLSERVER') {
            return true;
        } else if ($this->_databasetype == 'MYSQL') {
            return true;
        } else if ($this->_databasetype == 'POSTGRESQL') {
            $this->connect();
            $this->query(
            	"select column_name from information_schema.columns where "
                . "table_name = '" . $table . "' and column_name = '" . $field
                . "'"
            );
            $res = $this->nb_result();
            $this->disconnect();
            if ($res > 0) {
                return true;
            } else {
                return false;
            }
        } else if ($this->_databasetype == 'ORACLE') {
            $this->connect();
            $this->query(
            	"SELECT * from USER_TAB_COLUMNS where TABLE_NAME = '" . $table
                . "' AND COLUMN_NAME = '" . $field . "'"
            );
            $res = $this->nb_result();
            $this->disconnect();
            if ($res > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
    * Execution the sql query
    *
    * @param  $sqlQuery string SQL query
    * @param  $catchError bool In case of error, catch the error or not,
    * 			if not catched, the error is displayed (false by default)
    */
    public function query($sqlQuery, $catchError = false)
    {
        // query
        $this->_debugQuery = $sqlQuery;
        if ($this->_databasetype == 'MYSQL') {
            $this->query = @mysqli_query($this->_sqlLink, $sqlQuery);
        } else if ($this->_databasetype == 'SQLSERVER') {
            $this->query = @mssql_query($sqlQuery);
        } else if ($this->_databasetype == 'POSTGRESQL') {
            $this->query = @pg_query($sqlQuery);
        } else if ($this->_databasetype == 'ORACLE') {
            $this->statement = @oci_parse($this->_sqlLink, $sqlQuery);
            if ($this->statement == false) {
                if ($catchError) {
                    return false;
                }
                $this->_sqlError = 6;
                $this->error();
                exit();
            } else {
                if (! @oci_execute($this->statement)) {
                    if ($catchError) {
                        return false;
                    }
                    //$error = oci_error($this->statement);
                    $this->_sqlError = 3;
                    $this->error();
                    //print_r($error);
                }
            }
        } else {
            $this->query = false;
        }
		//$this->show();
        if ((($this->_databasetype == 'ORACLE' && $this->statement == false)
            || ($this->_databasetype <> 'ORACLE' && $this->query == false))
            && ! $catchError
        ) {
            $this->_sqlError = 3;
            $this->error();
        }
        $this->_nbQuery ++;
        if ($this->_databasetype == 'ORACLE') {
            return $this->statement;
        } else {
            return $this->query;
        }
    }

    /**
    * Returns the query results in an object
    *
    * @return Object
    */
    public function fetch_object()
    {
        if ($this->_databasetype == 'MYSQL') {
            return @mysqli_fetch_object($this->query);
        } else if ($this->_databasetype == 'SQLSERVER') {
            return @mssql_fetch_object($this->query);
        } else if ($this->_databasetype == 'POSTGRESQL') {
            return @pg_fetch_object($this->query);
        } else if ($this->_databasetype == 'ORACLE') {
            $myObject = @oci_fetch_object($this->statement);
            $myLowerObject = false;
            if (isset($myObject) && ! empty($myObject)) {
                foreach ($myObject as $key => $value) {
                    $myKey = strtolower($key);
                    if (oci_field_type($this->statement, $key) == 'CLOB') {
                        $myBlob = $myObject->$key;
                        if (isset($myBlob)) {
                            $myLowerObject->$myKey = $myBlob->read(
                                $myBlob->size()
                            );
                        }
                    } else {
                        $myLowerObject->$myKey = $myObject->$key;
                    }
                }
                return $myLowerObject;
            } else {
                return false;
            }
        } else {

        }
    }

    /**
    * Returns the query results in an array
    *
    * @return array
    */
    public function fetch_array()
    {
        if ($this->_databasetype == 'MYSQL') {
            return @mysqli_fetch_array($this->query);
        } else if ($this->_databasetype == 'SQLSERVER') {
            return @mssql_fetch_array($this->query);
        } else if ($this->_databasetype == 'POSTGRESQL') {
            return @pg_fetch_array($this->query);
        } else if ($this->_databasetype == 'ORACLE') {
            $tmpStatement = array();
            $tmpStatement = @oci_fetch_array($this->statement);

            if (is_array($tmpStatement)) {
                //$this->show_array($tmp_statement);
                foreach (array_keys($tmpStatement) as $key) {
                    if (! is_numeric($key)
                        && oci_field_type($this->statement, $key) == 'CLOB'
                    ) {
                        if (isset($tmpStatement[$key])) {
                            $tmp = $tmpStatement[$key];
                            $tmpStatement[$key] = $tmp->read($tmp->size());
                        }
                    }
                }
                return array_change_key_case($tmpStatement, CASE_LOWER);
            }
        } else {

        }
    }

    /**
    * Returns the query results in a row
    *
    * @return array
    */
    public function fetch_row()
    {
        if ($this->_databasetype == 'MYSQL') {
            return @mysqli_fetch_row($this->query);
        } else if ($this->_databasetype == 'SQLSERVER') {
            return @mssql_fetch_row($this->query);
        } else if ($this->_databasetype == 'POSTGRESQL') {
            return @pg_fetch_row($this->query);
        } else if ($_SESSION['config']['databasetype'] == 'ORACLE') {
            return @oci_fetch_row($this->statement);
        } else {

        }
    }

    /**
    * Returns the number of results for the current query
    *
    * @return integer Results number
    */
    public function nb_result()
    {
        if ($this->_databasetype == 'MYSQL') {
            return @mysqli_num_rows($this->query);
        } else if ($this->_databasetype == 'SQLSERVER') {
            return @mssql_num_rows($this->query);
        } else if ($this->_databasetype == 'POSTGRESQL') {
            return @pg_num_rows($this->query);
        } else if ($this->_databasetype == 'ORACLE') {

            $db = new dbquery();
            $db->connect();
            $db->query("SELECT COUNT(*) FROM  (" . $this->_debugQuery . ")");
            $row = $db->fetch_array();
            return $row[0];
        } else {

        }
    }

    /**
    * Closes database connexion
    *
    */
    public function disconnect()
    {
        if ($this->_databasetype == 'MYSQL') {
            if (! mysqli_close($this->_sqlLink)) {
                $this->_sqlError = 4;
                $this->error();
            }
        } else if ($this->_databasetype == 'SQLSERVER') {
            if (! mssql_close($this->_sqlLink)) {
                $this->_sqlError = 4;
                $this->error();
            }
        } else if ($this->_databasetype == 'POSTGRESQL') {
            //  if (get_resource_type($this->_sqlLink) == 'pgsql link') {
            if (! pg_close($this->_sqlLink)) {
                $this->_sqlError = 4;
                $this->error();
            }
            // }
        } else if ($this->_databasetype == 'ORACLE') {
            if (! oci_close($this->_sqlLink)) {
                $this->_sqlError = 4;
                $this->error();
            }
        } else {

        }
    }

    /**
    * SQL Error management
    *
    */
    private function error()
    {
        require_once('core' . DIRECTORY_SEPARATOR . 'class' 
            . DIRECTORY_SEPARATOR . 'class_history.php');
        $trace = new history();
        
        // Connexion error
        if ($this->_sqlError == 1) {
            $trace->add("", 0, "CONNECT", _CONNECTION_DB_FAILED." : ".$this->_user."@".$this->_server.":".$this->_port, $_SESSION['config']['databasetype'], "database", true, _KO, _LEVEL_FATAL);
            // Shows the connexion data (server, port, user, pass)
            echo '- <b>' . _DB_CONNEXION_ERROR . '</b>';
            if ($_SESSION['config']['debug'] == 'true') {
                echo ' -<br /><br />' . _DATABASE_SERVER . ' : '
                    . $this->_server . '<br/>' . _DB_PORT . ' : ' . $this->_port
                    . '<br/>' . _DB_TYPE . ' : ' . $this->_databasetype
                    . '<br/>' . _DB_NAME . ' : ' . $this->_database . '<br/>'
                    . _DB_USER . ' : ' . $this->_user . '<br/>' . _PASSWORD
                    . ' : ' . $this->_password;
            }
            exit();
        }

        // Selection error
        if ($this->_sqlError == 2) {
            echo '- <b>' . _SELECTION_BASE_ERROR . '</b>';
            if ($_SESSION['config']['debug'] == 'true') {
                echo ' -<br /><br />' . _DATABASE . ' : ' . $this->_database;
            }
            $trace->add("", 0, "SELECTDB", _SELECT_DB_FAILED." : ".$this->_database, $_SESSION['config']['databasetype'], "database", true, _KO, _LEVEL_FATAL);
            exit();
        }

        // Query error
        if ($this->_sqlError == 3) {
            echo '- <b>' . _QUERY_ERROR . '</b> -<br /><br />';
            if ($this->_databasetype == 'MYSQL') {
                echo _ERROR_NUM . @mysqli_errno($this->_sqlLink) . ' '
                    . _HAS_JUST_OCCURED . ' :<br />';
                echo _MESSAGE . ' : ' .  @mysqli_error($this->_sqlLink)
                    . '<br />';
            } else if ($this->_databasetype == 'POSTGRESQL') {
                @pg_send_query($this->_sqlLink, $this->_debugQuery);
                $res = @pg_get_result($this->_sqlLink);
                echo @pg_result_error($res);
            } else if ($this->_databasetype == 'SQLSERVER') {
                echo @mssql_get_last_message();
            } else if ($this->_databasetype == 'ORACLE') {
                $res = @oci_error($this->statement);
                echo $res['message'];
            }
            echo '<br/>' . _QUERY . ' : <textarea cols="70" rows="10">'
                . $this->_debugQuery . '</textarea>';
            $trace->add("", 0, "QUERY", _QUERY_DB_FAILED." : ".$this->_debugQuery, $_SESSION['config']['databasetype'], "database", true, _KO, _LEVEL_ERROR);
            exit();
        }

        // Closing connexion error
        if ($this->_sqlError == 4) {
            echo '- <b>' . _CLOSE_CONNEXION_ERROR . '</b> -<br /><br />';
            $trace->add("", 0, "CLOSE", _CLOSE_DB_FAILED, $_SESSION['config']['databasetype'], "database", true, _KO, _LEVEL_ERROR);
            exit();
        }

        // Constructor error
        if ($this->_sqlError == 5) {
            echo '- <b>' . _DB_INIT_ERROR . '</b> <br />';
            $trace->add("", 0, "INIT", _INIT_DB_FAILED, $_SESSION['config']['databasetype'], "database", true, _KO, _LEVEL_ERROR);
            exit();
        }
        // Query Preparation error (ORACLE)
        if ($this->_sqlError == 6) {
            echo '- <b>' . _QUERY_PREP_ERROR . '</b> <br />';
            $trace->add("", 0, "QUERY", _PREPARE_QUERY_DB_FAILED, $_SESSION['config']['databasetype'], "database", true, _KO, _LEVEL_ERROR);
            exit();
        }
    }

    /**
    * Shows the query for debug
    *
    */
    public function show()
    {
        echo _LAST_QUERY . ' : <textarea cols="70" rows="10">'
            . $this->_debugQuery . '</textarea>';
    }

    /**
    * Returns the last insert id for the current query in case  of
    * 	autoincrement id
    *
    * @return integer  last increment id
    */
    public function last_insert_id($sequenceName = '')
    {
        if ($this->_databasetype == 'MYSQL') {
            return @mysqli_insert_id($this->_sqlLink);
        } else if ($this->_databasetype == 'POSTGRESQL') {
            $this->query = @pg_query(
            	"select currval('" . $sequenceName . "') as lastinsertid"
            );
            $line = @pg_fetch_object($this->query);

            return $line->lastinsertid;
        } else if ($this->_databasetype == 'SQLSERVER') {

        } else if ($this->_databasetype == 'ORACLE') {

        } else {

        }

    }
}
