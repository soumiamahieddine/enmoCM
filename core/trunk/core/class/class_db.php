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
* @brief   Embedded sql functions (connection, database selection, query ). Allow to changes the databases server
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
* @brief   Embedded sql functions (connection, database selection, query ). Allow to changes the databases server
*
* <ul>
*  <li>Compatibility with the following databases : Mysql, Postgres, Mssql Server, Oracle
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
    private $debug;             // debug mode

    /**
    * Debug query (debug mode). String
    */
    private $debug_query;       // request for the debug mode

    /**
    * SQL link identifier
    * Integer
    */
    private $sql_link;          // sql link identifier


    /**
    * To know where the script was stopped
    *  Integer
    */
    private $what_sql_error;    // to know where the script was stopped

    /**
    * SQL query
    * String
         */
    public $query;              // query

    /**
    * Number of queries made with this identifier
         * Integer
         */
    private $nb_query;          // number of queries made with this identifier

    /**
    * Sent query result
         * String
         */
    private $result;            // sent query result

    /**
    * OCI query identifier
    * @access private
    * @var integer
    */
    private $statement  ;       // OCI query identifier

    private $server;
    private $port;
    private $user;
    private $pass;
    private $base;
    private $databasetype;
    //private $workspace;

    function __construct()
    {
        $args = func_get_args();
        if(count($args) < 1)
        {
            if(isset($_SESSION['config']['databaseserver']))
            {
                $this->server = $_SESSION['config']['databaseserver'];
            }
            if(isset($_SESSION['config']['databaseserverport']))
            {
                $this->port = $_SESSION['config']['databaseserverport'];
            }
            if(isset($_SESSION['config']['databaseuser']))
            {
                $this->user = $_SESSION['config']['databaseuser'];
            }
            if(isset($_SESSION['config']['databasepassword']))
            {
                $this->pass = $_SESSION['config']['databasepassword'];
            }
            if(isset($_SESSION['config']['databasename']))
            {
                $this->base = $_SESSION['config']['databasename'];
            }
            //$this->workspace = $_SESSION['config']['databaseworkspace'];
            if(isset($_SESSION['config']['databasetype']))
            {
                $this->databasetype = $_SESSION['config']['databasetype'];
            }
        }
        else
        {
            $error_args = true;
            if(is_array($args[0]))
            {
                if(!isset($args[0]['server']))
                {
                    $this->server = '127.0.0.1';
                }
                else
                {
                    $this->server = $args[0]['server'];
                }
                if(!isset($args[0]['databasetype']))
                {
                    $this->databasetype = 'MYSQL';
                }
                else
                {
                    $this->databasetype = $args[0]['databasetype'];
                }
                if(!isset($args[0]['port']))
                {
                    $this->port = '3304';
                }
                else
                {
                    $this->port = $args[0]['port'];
                }
                if(!isset($args[0]['user']))
                {
                    $this->user = 'root';
                }
                else
                {
                    $this->user = $args[0]['user'];
                }
                //if(!isset($args[0]['workspace']))
                //{
                //  $this->workspace = 'public';
                //}
                //else
                //{
                //  $this->workspace = $args[0]['workspace'];
                //}
                if(!isset($args[0]['pass']))
                {
                    $this->pass = '';
                }
                else
                {
                    $this->pass = $args[0]['pass'];
                }
                if(!isset($args[0]['base']))
                {
                    $this->base = '';
                }
                else
                {
                    $this->base = $args[0]['base'];
                }
                $error_args = false;
            }
            else if(is_string($args[0]) && file_exists($args[0]))
            {
                $xmlconfig = simplexml_load_file($args[0]);
                $CONFIG = $xmlconfig->CONFIG_BASE;
                $this->server = (string) $CONFIG->databaseserver;
                $this->port = (string) $CONFIG->databaseserverport;
                $this->databasetype = (string) $CONFIG->databasetype;
                $this->base= (string) $CONFIG->databasename;
                $this->user = (string) $CONFIG->databaseuser;
                $this->pass = (string) $CONFIG->databasepassword;
                //if (isset($CONFIG->databaseworkspace))
                //  $this->workspace = (string) $CONFIG->databaseworkspace;
                $error_args = false;
            }
            if($error_args)
            {
                $this->what_sql_error = 5; // error constructor
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
        //var_dump($this);
        $this->debug = 0;
        $this->nb_query = 0;
        if($this->databasetype == "MYSQL")
        {
            $this->sql_link = @mysqli_connect($this->server,$this->user,$this->pass, $this->base, $this->port);
        }
        elseif($this->databasetype== "SQLSERVER")
        {
            $this->sql_link = @mssql_connect($this->server,$this->user,$this->pass);
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            $this->sql_link = @pg_connect("host=".$this->server." user=".$this->user." password=".$this->pass." dbname=".$this->base." port=".$this->port, PGSQL_CONNECT_FORCE_NEW);
        }
        elseif($this->databasetype == "ORACLE")
        {
            if($this->server <> "")
            {
                $this->sql_link = oci_connect($this->user, $this->pass, "//".$this->server."/".$this->base,'UTF8');
            }
            else
            {
                $this->sql_link = oci_connect($this->user, $this->pass, $this->base ,'UTF8');
            }
            // ALTER SESSIONS MUST BE MANAGED BY TRIGGERS DIRECTLY IN THE DB
            //$this->query("alter session set nls_date_format='dd-mm-yyyy'");
        }
        else
        {
            $this->sql_link = FALSE;
        }

        if(!$this->sql_link)
        {
            $this->what_sql_error = 1; // error connexion
            $this->error();
        }
        else
        {
            if($this->databasetype <> "POSTGRESQL" && $this->databasetype <> "MYSQL" && $this->databasetype <> "ORACLE")
            {
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
        if($this->databasetype == "SQLSERVER")
        {
            if(!@mssql_select_db($this->base))
            {
                $this->what_sql_error = 2;
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
        if($this->databasetype == "SQLSERVER"){
            return true;
        }
        elseif($this->databasetype == "MYSQL"){
            return true;
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            $this->connect();
            $this->query("select column_name from information_schema.columns where table_name = '".$table."' and column_name = '".$field."'");
            $res = $this->nb_result();
            $this->disconnect();
            if ($res > 0)
                return true;
            else
                return false;
        }
        elseif($this->databasetype == "ORACLE")
        {
            $this->connect();
            $this->query("SELECT * from USER_TAB_COLUMNS where TABLE_NAME = '".$table."' AND COLUMN_NAME = '".$field."'");
            $res = $this->nb_result();
            $this->disconnect();
            if ($res > 0)
                return true;
            else
                return false;
        }
    }

    /**
    * Execution the sql query
    *
    * @param  $q_sql string SQL query
    * @param  $catch_error bool In case of error, catch the error or not, if not catched, the error is displayed (false by default)
    */
    public function query($q_sql, $catch_error = false)
    {
        // query
        $this->debug_query = $q_sql;
        if($this->databasetype == "MYSQL")
        {
            $this->query = @mysqli_query($this->sql_link,$q_sql);
        }
        elseif($this->databasetype== "SQLSERVER")
        {
            $this->query = @mssql_query($q_sql);
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            $this->query = @pg_query($q_sql);
        }
        elseif($this->databasetype == "ORACLE")
        {
            $this->statement = @oci_parse($this->sql_link, $q_sql);
            if($this->statement == false)
            {
                if($catch_error)
                {
                    return false;
                }
                $this->what_sql_error = 6;
                $this->error();
                exit();
            }
            else
            {
                if (!@oci_execute($this->statement))
                {
                    if($catch_error)
                    {
                        return false;
                    }
                    //$error = oci_error($this->statement);
                    $this->what_sql_error = 3;
                    $this->error();
                    //print_r($error);
                }
            }
        }
        else
        {
            $this->query = false;

        }
        if ((($this->databasetype == "ORACLE" && $this->statement == false)|| ($this->databasetype <> "ORACLE" && $this->query == false))  && !$catch_error)
        //if($this->query == false && !$catch_error)
        {
            $this->what_sql_error = 3;
            $this->error();
        }
        $this->nb_query++;
        if($this->databasetype == "ORACLE")
            return $this->statement;
        else
            return $this->query;
    }

    /**
    * Returns the query results in an object
    *
    * @return Object
    */
    public function fetch_object()
    {
        if($this->databasetype == "MYSQL")
        {
            return @mysqli_fetch_object($this->query);
        }
        elseif($this->databasetype == "SQLSERVER")
        {
            return @mssql_fetch_object($this->query);
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            return @pg_fetch_object($this->query);
        }
        elseif($this->databasetype == "ORACLE")
        {
            $myObject = @oci_fetch_object($this->statement);
            $myLowerObject = false;
            if(isset($myObject) && !empty($myObject))
            {
                foreach($myObject as $key => $value)
                {
                    if(oci_field_type($this->statement, $key) == 'CLOB')
                    {
                        $key2 = strtolower($key);
                        $MyBlob = $myObject->$key;
                        if(isset($MyBlob))
                        {
                            $myLowerObject->$key2 = $MyBlob->read($MyBlob->size());
                        }
                    }
                    else
                    {
                        $key2 = strtolower($key);
                        $myLowerObject->$key2 = $myObject->$key;
                    }
                }
                return $myLowerObject;
            }
            else
            {
                return false;
            }
        }
        else
        {

        }
    }

    /**
    * Returns the query results in an array
    *
    * @return array
    */
    public function fetch_array()
    {
        if($this->databasetype == "MYSQL")
        {
           return @mysqli_fetch_array($this->query);
         }
        elseif($this->databasetype == "SQLSERVER")
        {
            return @mssql_fetch_array($this->query);
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            return @pg_fetch_array($this->query);
        }
        elseif($this->databasetype == "ORACLE")
        {
            $tmp_statement = array();
            $tmp_statement = @oci_fetch_array($this->statement);

            if (is_array($tmp_statement))
            {
                //$this->show_array($tmp_statement);
                foreach(array_keys($tmp_statement) as $key)
                {
                    if(!is_numeric($key) && oci_field_type($this->statement, $key) == 'CLOB')
                    {
                        if(isset($tmp_statement[$key]))
                        {
                            $tmp = $tmp_statement[$key];
                            $tmp_statement[$key] = $tmp->read($tmp->size());
                        }
                    }
                }
                return array_change_key_case($tmp_statement ,CASE_LOWER);
            }
        }
        else
        {

        }
    }

    /**
    * Returns the query results in a row
    *
    * @return array
    */
    public function fetch_row()
    {
        if($this->databasetype== "MYSQL")
        {
            return @mysqli_fetch_row($this->query);
        }
        elseif($this->databasetype == "SQLSERVER")
        {
            return @mssql_fetch_row($this->query);
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            return @pg_fetch_row($this->query);
        }
        elseif($_SESSION['config']['databasetype'] == "ORACLE")
        {
            return @oci_fetch_row($this->statement);
        }
        else
        {

        }
    }

    /**
    * Returns the number of results for the current query
    *
    * @return integer Results number
    */
    public function nb_result()
    {
        if($this->databasetype== "MYSQL")
        {
           return @mysqli_num_rows($this->query);
        }
        elseif($this->databasetype== "SQLSERVER")
        {
            return @mssql_num_rows($this->query);
        }
        elseif($this->databasetype== "POSTGRESQL")
        {
            return @pg_num_rows($this->query);
        }
        elseif($this->databasetype == "ORACLE")
        {


            // 2eme version Maarch
            $db = new dbquery();
            $db->connect();
            $db->query("SELECT COUNT(*) FROM  (".$this->debug_query.")");
            $row = $db->fetch_array();
            return $row[0];
        }
        else
        {

        }
    }

    /**
    * Closes database connexion
    *
    */
    public function disconnect()
    {
        if($this->databasetype == "MYSQL")
        {
            if(!mysqli_close($this->sql_link))
            {
                $this->what_sql_error = 4;
                $this->error();
            }
        }
        elseif($this->databasetype == "SQLSERVER")
        {
             if(!mssql_close($this->sql_link))
            {
                $this->what_sql_error = 4;
                $this->error();
            }
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
           if(get_resource_type($this->sql_link) == "pgsql link")
           {
                if(!pg_close($this->sql_link))
                {
                    $this->what_sql_error = 4;
                    $this->error();
                }
            }
        }
        elseif($this->databasetype == "ORACLE")
        {
             if(!oci_close($this->sql_link))
            {
                $this->what_sql_error = 4;
                $this->error();
            }
        }
        else
        {

        }
    }

    /**
    * SQL Error management
    *
    */
    private function error()
    {

        // Connexion error
        if($this->what_sql_error == 1)
        {
            // Shows the connexion data (server, port, user, pass)
            echo "- <b>"._DB_CONNEXION_ERROR."</b>";
            //if($_SESSION['config']['debug'] == 'true')
            {

                echo " -<br /><br />"._DATABASE_SERVER." : ".$this->server."<br/>"._DB_PORT.' : '.$this->port."<br/>"._DB_TYPE." : ".$this->databasetype."<br/>"._DB_NAME.". : ".$this->base."<br/>"._DB_USER." : ".$this->user."<br/>"._PASSWORD." : ".$this->pass;

            }
            exit();

        }

        // Selection error
        if($this->what_sql_error == 2)
        {
            echo "- <b>"._SELECTION_BASE_ERROR."</b>";
            if($_SESSION['config']['debug'] == 'true')
            {
                echo " -<br /><br />"._DATABASE." : ".$this->base;
            }
            exit();
        }

        // Query error
        if($this->what_sql_error == 3)
        {
            echo "- <b>"._QUERY_ERROR."</b> -<br /><br />";
            if($this->databasetype == "MYSQL")
            {
                echo _ERROR_NUM.@mysqli_errno($this->sql_link)." "._HAS_JUST_OCCURED." :<br />";
                echo _MESSAGE." : ". @mysqli_error($this->sql_link)."<br />";
            }
            elseif($this->databasetype == "POSTGRESQL")
            {
                @pg_send_query($this->sql_link, $this->debug_query);
                $res = @pg_get_result($this->sql_link);
                echo @pg_result_error($res);
            }
            elseif($this->databasetype == "SQLSERVER")
            {
                echo @mssql_get_last_message();
            }
            elseif($this->databasetype == "ORACLE")
            {
                $res = @oci_error($this->statement);
                echo $res['message'];
            }
            echo "<br/>"._QUERY." : <textarea cols=\"70\" rows=\"10\">".$this->debug_query."</textarea>";
            exit();
        }

        // Closing connexion error
        if($this->what_sql_error == 4)
        {
            echo "- <b>"._CLOSE_CONNEXION_ERROR."</b> -<br /><br />";
            exit();
        }

        // Constructor error
        if($this->what_sql_error == 5)
        {
            echo "- <b>"._DB_INIT_ERROR."</b> <br />";
            exit();
        }
        // QUery Preparation error (ORACLE)
        if($this->what_sql_error == 6)
        {
            echo "- <b>"._QUERY_PREP_ERROR."</b> <br />";
            exit();
        }
    }

    /**
    * Shows the query for debug
    *
    */
    public function show()
    {
        echo _LAST_QUERY." : <textarea cols=\"70\" rows=\"10\">".$this->debug_query."</textarea>";
    }

    /**
    * Returns the last insert id for the current query in case  of autoincrement id
    *
    * @return integer  last increment id
    */
    public function last_insert_id($sequence_name ='')
    {
        if($this->databasetype == "MYSQL")
        {
            return @mysqli_insert_id($this->sql_link);
        }
        elseif($this->databasetype == "POSTGRESQL")
        {
            $this->query = @pg_query("select currval('".$sequence_name."')as lastinsertid");
            $line = @pg_fetch_object($this->query);

            return $line->lastinsertid;
        }
        elseif($this->databasetype == "SQLSERVER")
        {

        }
        elseif($this->databasetype == "ORACLE")
        {

        }
        else
        {

        }

    }
}
?>
