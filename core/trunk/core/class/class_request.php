<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief   Contains all the function to build a SQL query
*
* @file
* @author  Loïc Vinet  <dev@maarch.org>
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Contains all the function to build a SQL query (select, insert and update)
*
* @ingroup core
*/
class request extends dbquery
{
	/**
	* Constructs the select query and returns the results in an array
	*
	* @param  $select array Query fields
	* @param  $where  string Where clause of the query
	* @param  $other  string Query complement (order by, ...)
	* @param  $database_type string Type of the database (MYSQL, POSTGRESQL, ...)
	* @param  $limit string Maximum numbers of results (500 by default)
	* @param  $left_join boolean Is the request is a left join ? (false by default)
	* @param  $first_join_table string Name of the first join table (empty by default)
	* @param  $second_join_table string Name of the second join table (empty by default)
	* @param  $join_key string  Key of the join (empty by default)
	* @param  $add_security string  Add the user security where clause or not (true by default)
	* @param  $distinct_argument  Add the distinct parameters in the sql query (false by default)
	* @return array Results of the built query
	*/
	public function select($select, $where, $other, $database_type, $limit="default", $left_join=false, $first_join_table="", $second_join_table="", $join_key="", $add_security = true, $catch_error = false, $distinct_argument = false)
	{
		if($limit == 0)
		{
			$limit=$_SESSION['config']['databasesearchlimit'];
		}
		elseif($limit == "default")
		{
			$limit=$_SESSION['config']['databasesearchlimit'];
		}
		//Extracts data in the first argument : $select.
		$tab_field = array();
		$table = '';
		$table_string = '';
		foreach (array_keys($select) as $value)
		{
			$table = $value;
			$table_string .= $table.",";
			foreach ($select[$value] as $subvalue)
			{
				$field = $subvalue;
				$field_string .= $table.".".$field.",";
			}
			//Query fields and table names have been wrote in 2 strings
		}
		//Strings need to be cleaned
		$table_string = substr($table_string, 0, -1);
		$field_string = substr($field_string, 0, -1);

		//Extracts data from the second argument : the where clause
		if (trim($where) <> "")
		{
			$where_string = " where ".$where;
		}
		else
		{
			$where_string = "";
		}

		if($left_join)
		{
			//Reste table string
			$table_string = "";

			//Add more table in join syntax
			foreach (array_keys($select) as $value)
			{
				if ($value <> $first_join_table && $value <> $second_join_table)
				{
					$table_string = $value.",";
				}
			}

			$join = " left join ";
			$table_string .= $first_join_table;
			$join .= $second_join_table." on ".$second_join_table.".".$join_key." = ".$first_join_table.".".$join_key;
		}

		$where2 = "";
		for($i=0; $i < count($_SESSION['user']['security']); $i++)
		{
			if(isset($_SESSION['user']['security'][$i]['table']) && isset($_SESSION['user']['security'][$i]['coll_id']))
			{
				if(preg_match('/'.$_SESSION['user']['security'][$i]['table'].'/',$table_string) )
				{
					if(empty($where))
					{
						$where2 = " where ( ".$_SESSION['user']['security'][$i]['where']." ) ";
					}
					else
					{
						$where2 = " and ( ".$_SESSION['user']['security'][$i]['where']." ) ";
					}
				}
			}
		}
		//Time to create the SQL Query
		$query = "";
		if($distinct_argument == true)
		{
			$dist = " distinct ";
		}
		if($database_type == "SQLSERVER")
		{
			$query = "SELECT TOP ".$limit." ".$dist.$field_string." FROM ".$table_string." ".$join." ".$where_string." ".$other;
		}
		elseif($database_type == "MYSQL" )
		{
			$query = "SELECT ".$dist.$field_string." FROM ".$table_string.' '.$join.' '.$where_string." ".$other." LIMIT 0,".$limit." ";
		}
		elseif($database_type == "POSTGRESQL" )
		{
			$query = "SELECT ".$dist.$field_string." FROM ".$table_string.' '.$join.' '.$where_string." ".$other." OFFSET 0 LIMIT ".$limit." ";
		}
		elseif($database_type == "ORACLE" )
		{
			if ($limit <> '')
			{
				$orcl_limit = $limit;
				$orcl_limit = " rownum <= ".$orcl_limit;
				if ($where_string <> '') { $orcl_limit = " and ".$orcl_limit; } else { $orcl_limit = " where ".$orcl_limit; }
			}
			$query = "SELECT ".$dist.$field_string." FROM ".$table_string.' '.$join.' '.$where_string." ".$orcl_limit." ".$other." ";
		}
		$this->connect();
		
		$res_query = $this->query($query, $catch_error);
		if($catch_error && !$res_query)
		{
			return false;
		}
		$result=array();
		while($line = $this->fetch_array())
		{
			$temp= array();
            foreach (array_keys($line) as $resval)
            {
            	if (!is_int($resval))
            	{
					if ($_SESSION['config']['databasetype'] == "ORACLE")
					{
						array_push($temp,array('column'=>strtolower($resval),'value'=>$line[$resval]));
					}
					else
					{
						array_push($temp,array('column'=>$resval,'value'=>$line[$resval]));
					}
            	}
            }
			array_push($result,$temp);
		}
		if(count($result) == 0 && $catch_error)
		{
			return true;
		}
		return $result;
	}

	/**
	* Builds the insert query and sends it to the database
	*
	* @param string $table table to insert
	* @param array $data data to insert
	* @param array $database_type type of the database (MYSQL, POSTGRESQL, ...)
	* @return bool True if the query was sent ok and processed by the database without error, False otherwise
	*/
	public function insert($table, $data, $database_type)
	{
		$field_string = "( ";
		$value_string = "( ";

		for($i=0; $i < count($data);$i++)
		{
			$field_string .= $data[$i]['column'].",";
			if($data[$i]['type'] == "string" || $data[$i]['type'] == "date")
			{
				$value_string .= "'".$data[$i]['value']."',";
			}
			else
			{
				$value_string .= $data[$i]['value'].",";
			}
		}
		$value_string = substr($value_string, 0, -1);
		$field_string = substr($field_string, 0, -1);

		$value_string .= ")";
		$field_string .= ")";

		//Time to create the SQL Query
		$query = "";
		$query = "INSERT INTO ".$table." ".$field_string." VALUES ".$value_string ;

		$this->connect();
		return ($this->query($query, true));
	}

	/**
	* Constructs the update query and sends it to the database
	*
	* @param  $table string Table to update
	* @param  $data array Data to update
	* @param  $where array Where clause of the query
	* @param  $database_type array Type of the database (MYSQL, POSTGRESQL, ...)
	*/
	public function update($table, $data, $where, $databasetype)
	{
		$update_string = "";
		for($i=0; $i < count($data);$i++)
		{
			if($data[$i]['type'] == "string" || $data[$i]['type'] == "date")
			{
				if($databasetype == "POSTGRESQL" && $data[$i]['type'] == "date" && ($data[$i]['value'] == '' || $data[$i]['value'] == ' '))
				{
					$update_string .= $data[$i]['column']."=NULL,";
				}
				else
				{
					if(trim(strtoupper($data[$i]['value'])) == "SYSDATE")
					{
						$update_string .= $data[$i]['column']."=sysdate,";
					}
					else
					{
						$update_string .= $data[$i]['column']."='".$data[$i]['value']."',";
					}
				}
			}
			else
			{
				$update_string .= $data[$i]['column']."=".$data[$i]['value'].",";
			}
		}
		$update_string = substr($update_string, 0, -1);
		if ($where <> "")
		{
			$where_string = " WHERE ".$where;
		}
		else
		{
			$where_string = "";
		}
		//Time to create the SQL Query
		$query = "";
		$query = "UPDATE ".$table." SET ".$update_string.$where_string;
		//echo $query;
		$this->connect();
		return $this->query($query, true);
	}

	/**
	* Return current datetime instruction for each SQL database
	*
	* @author  Loïc Vinet  <dev@maarch.org
	*/
	public function current_datetime()
	{

		if($_SESSION['config']['databasetype'] == "SQLSERVER")
		{
			return ' getdate() ';
		}
		elseif( ($_SESSION['config']['databasetype'] == "MYSQL" || $_SESSION['config']['databasetype'] == "POSTGRESQL"))
		{
			return ' now() ';
		}
		elseif($_SESSION['config']['databasetype'] == "ORACLE")
		{
			return ' sysdate ';
		}
	}

	/**
	* Returns the correct SQL instruction (depending of the database type) for extracting a date or a date part from a datetime field
	*
	* @param $date_field String The name of the date field
	* @param $arg String Date part : 'year', 'month', 'day', 'hour', 'minute' or 'second'; if empty return the all date, empty by default
	* @return String SQL instruction
	*/
	public function extract_date($date_field, $arg = '')
	{

		if($_SESSION['config']['databasetype'] == "SQLSERVER")
		{
			// TO DO
			return $date_field;
		}
		elseif( $_SESSION['config']['databasetype'] == "MYSQL" || $_SESSION['config']['databasetype'] == "POSTGRESQL" )
		{
			if(empty($arg))
			{
				return ' date('.$date_field.')';
			}
			else
			{
				if($_SESSION['config']['databasetype'] == "MYSQL")
				{
					switch($arg)
					{
						case 'year' :
							return ' date_format('.$date_field.', %Y)';
						case 'month' :
							return ' date_format('.$date_field.', %m)';
						case 'day' :
							return ' date_format('.$date_field.', %d)';
						case 'hour' :
							return ' date_format('.$date_field.', %k)';
						case 'minute' :
							return ' date_format('.$date_field.', %i)';
						case 'second' :
							return ' date_format('.$date_field.', %s)';
						default	 :
							return ' date('.$date_field.')';
					}
				}
				else if($_SESSION['config']['databasetype'] == "POSTGRESQL")
				{
					switch($arg)
					{
						case 'year' :
							return " date_part( 'year', ".$date_field.")";
						case 'month' :
							return " date_part( 'month', ".$date_field.")";
						case 'day' :
							return " date_part( 'day', ".$date_field.")";
						case 'hour' :
							return " date_part( 'hour', ".$date_field.")";
						case 'minute' :
							return " date_part( 'minute', ".$date_field.")";
						case 'second' :
							return " date_part( 'second', ".$date_field.")";
						default	 :
							return ' date('.$date_field.')';
					}
				}

			}
		}
		elseif($_SESSION['config']['databasetype'] == "ORACLE")
		{
			switch($arg)
			{
				case 'year' :
					return " to_char(".$date_field.", 'YYYY')";
				case 'month' :
					return " to_char(".$date_field.", 'MM')";
				case 'day' :
					return " to_char(".$date_field.", 'DD')";
				case 'hour' :
					return " to_char(".$date_field.", 'HH24')";
				case 'minute' :
					return " to_char(".$date_field.", 'MI')";
				case 'second' :
					return " to_char(".$date_field.", 'SS')";
				default	 :
					return " to_char(".$date_field.", 'DD/MM/YYYY')";
			}
		}
	}

	public function get_date_diff($date1, $date2)
	{
		if($_SESSION['config']['databasetype'] == "MYSQL")
		{
			return 'datediff('.$date1.', '.$date2.')';
		}
		elseif($_SESSION['config']['databasetype'] == "POSTGRESQL")
		{
			return $this->extract_date($date1).' - '.$this->extract_date($date2);
		}
		elseif($_SESSION['config']['databasetype'] == "ORACLE")
		{
			return $this->extract_date($date1).' - '.$this->extract_date($date2);
		}
		else if($_SESSION['config']['databasetype'] == "SQLSERVER")
		{
			// TO DO
			return '';
		}
	}
}
?>
