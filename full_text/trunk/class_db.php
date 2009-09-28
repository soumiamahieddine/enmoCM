<?php 
/**
* Dbquery Class
*
* Embedded sql functions (connection, database selection, query ). Allow to change the databases server 
*
* @package Maarch Framework
* @version 3
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
* 
*/

/**
* Class dbquery : Personnal Sql classes. Allow to change the databases server.
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL v3
* @package  Maarch Framework
* @version 3
*/

class dbquery
{	
	/**
	* Debug mode activation
    * @access private
    * @var integer 1,0
    */
	private $debug;				// debug mode
	
	/**
	* Debug query (debug mode)
    * @access private
    * @var string
    */
	private $debug_query;		// request for the debug mode
	
	/**
	* SQL link identifier
    * @access private
    * @var integer
    */
	private $sql_link;			// sql link identifier
	
	
	/**
	* To know where the script was stopped
    * @access private
    * @var integer
    */
	private $what_sql_error;	// to know where the script was stopped
	
	/**
	* SQL query
    * @access private
    * @var string
    */
	public $query;				// query
	
	/**
	* Number of queries made with this identifier
    * @access private
    * @var integer
    */
	private $nb_query;			// number of queries made with this identifier
	
	/**
	* Sent query result
    * @access private
    * @var string
    */
	private $result;			// sent query result
	
	
	
	/**
	* Database connection 
	*
	*/
	public function connect()
	{
		// database connection
		$server = $_ENV['databaseserver'];
		$port = $_ENV['databaseport'];
		$user = $_ENV['databaseuser'];
		$pass = $_ENV['databasepwd'];
		$this->base = $_ENV['database'];

		$this->debug = 0;
		$this->nb_query = 0;
		
		if($_ENV['databasetype'] == "MYSQL")
		{
			$this->sql_link = mysqli_connect($server,$user,$pass, $this->base, (integer)$port);
		}
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			//$this->sql_link = @mssql_connect($server.':'.$port,$user,$pass);
			$this->sql_link = mssql_connect($server,$user,$pass);
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			$this->sql_link = @pg_connect("host=".$server." user=".$user." password=".$pass." dbname=".$this->base." port=".$port);
			
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
			if($_ENV['databasetype'] <> "POSTGRESQL" && $_ENV['databasetype'] <> "MYSQL")
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
		if($_ENV['databasetype'] == "SQLSERVER")
		{
			if(!@mssql_select_db($this->base))
			{
				$this->what_sql_error = 2;
				$this->error();
			}
		}
		
		
	}
	
	/**
	* Execution the sql query
	*
	* @param string $q_sql requete sql
	* @param bool	$catch_error In case of error, catch the error or not, if not catched, the error is displayed (false by default)
	*/
	public function query($q_sql, $catch_error = false)
	{
		// query
		$this->debug_query = $q_sql;
		
		if($_ENV['databasetype'] == "MYSQL")
		{
			$this->query = @mysqli_query($this->sql_link,$q_sql);
		}
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			$this->query = @mssql_query($q_sql);
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			$this->query = @pg_query($q_sql);
		}
		else
		{
			$this->query = false;
			
		}
		if($this->query == false && !$catch_error)
		{
			$this->what_sql_error = 3;
			$this->error();
		}
		$this->nb_query++;
		return $this->query;
	}
	
	/**
	* Returns the query results in an object
	*
	*/
	public function fetch_object()
	{
	
		if($_ENV['databasetype'] == "MYSQL")
		{
            return @mysqli_fetch_object($this->query);
		}
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			return @mssql_fetch_object($this->query);
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			return @pg_fetch_object($this->query);
		}
		else
		{
		
		}
	 }
	
	/**
	* Returns the query results in an array
	*
	*/
	public function fetch_array()
	{
		if($_ENV['databasetype'] == "MYSQL")
		{
           return @mysqli_fetch_array($this->query);	
		 }
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			return @mssql_fetch_array($this->query);
			
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			return @pg_fetch_array($this->query);
			
		}
		else
		{
		
		}
	}
	
	/**
	* Returns the query results in a row
	*
	*/
	public function fetch_row()
	{

		if($_ENV['databasetype'] == "MYSQL")
		{
            return @mysqli_fetch_row($this->query);
		}
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			return @mssql_fetch_row($this->query);
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			return @pg_fetch_row($this->query);
		}
		else
		{
		
		}
	}
	/**
	* Returns the number of results for the current query
	*
	*/
	public function nb_result()
	{
	
		if($_ENV['databasetype'] == "MYSQL")
		{
           return @mysqli_num_rows($this->query);
		 }
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			return @mssql_num_rows($this->query);
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			return @pg_num_rows($this->query);
		}
		else
		{
		
		}
	}
	
	/**
	* Database disconnection
	*
	*/
	public function close_conn()
	{
		if($_ENV['databasetype'] == "MYSQL")
		{
			if(!mysqli_close($this->sql_link))
			{
				$this->what_sql_error = 4;
				$this->error();
			}
		}
		elseif($_ENV['databasetype'] == "SQLSERVER")
		{
			 if(!mssql_close($this->sql_link))
			{
				$this->what_sql_error = 4;
				$this->error();
			}
		}
		elseif($_ENV['databasetype'] == "POSTGRESQL")
		{
			 if(!pg_close($this->sql_link))
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
	* Error management
	*
	*/
	public function error( $error_function = false)
	{
	
		// Connexion error
		if($this->what_sql_error == 1)
		{
			// show the connexion data (server, port, user, pass)
			echo "- <b>"._DB_CONNEXION_ERROR."</b> -<br /><br />"._DATABASE_SERVER." : ".$_ENV['databaseserver']."<br/>"._DB_PORT.' : '.$_ENV['databaseport']."<br/>"._DB_TYPE." : ".$_ENV['databasetype']."<br/>"._DB_USER." : ".$_ENV['databaseuser']."<br/>"._PASSWORD." : ".$_ENV['databasepwd'];
			if($_ENV['databasetype'] == "POSTGRESQL")
			{
				echo "<br/>"._DATABASE.' : '.$this->base;
			}
			exit();
			
		}
		
		// Selection error
		if($this->what_sql_error == 2)
		{
			echo "- <b>"._SELECTION_BASE_ERROR."</b> -<br /><br />"._DATABASE." : ".$this->base;
			exit();
		}
		
		// Query error
		if($this->what_sql_error == 3 && !$error_function)
		{
			echo "- <b>"._QUERY_ERROR."</b> -<br /><br />";
			if($_ENV['databasetype'] == "MYSQL")
			{
				echo _ERROR_NUM.@mysqli_errno($this->sql_link)." "._HAS_JUST_OCCURED." :<br />";
				echo _MESSAGE." : ". @mysqli_error($this->sql_link)."<br />";
			}
			elseif($_ENV['databasetype'] == "POSTGRESQL")
			{
				@pg_send_query($this->sql_link, $this->debug_query);
				$res = @pg_get_result($this->sql_link);
				echo @pg_result_error($res);
			}
			elseif($_ENV['databasetype'] == "SQLSERVER")
			{
				echo @mssql_get_last_message();
			}
			echo "<br/>"._QUERY." : <textarea cols=\"70\" rows=\"10\">".$this->debug_query."</textarea>";
			exit();
		}
		else
		{
			if($_ENV['databasetype'] == "MYSQL")
			{
				return @mysqli_error($this->sql_link);
			}
			elseif($_ENV['databasetype'] == "POSTGRESQL")
			{
				@pg_send_query($this->sql_link, $this->debug_query);
				$res = @pg_get_result($this->sql_link);
				return @pg_result_error($res);
			}
			elseif($_ENV['databasetype'] == "SQLSERVER")
			{
				return @mssql_get_last_message();
			}
		}
		
		// Disconnexion error
		if($this->what_sql_error == 4)
		{
			echo "- <b>"._CLOSE_CONNEXION_ERROR."</b> -<br /><br />";
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
}
?>