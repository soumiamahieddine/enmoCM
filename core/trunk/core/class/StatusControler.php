<?php
/*
*    Copyright 2008,2009,2010 Maarch
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
* @brief  Contains the controler of the Status Object (create, save, modify, etc...)
* 
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
	require_once("core/class/class_db.php");
	require_once("core/class/Status.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Status Object 
*
*<ul>
*  <li>Get an status object from an id</li>
*  <li>Save in the database a status</li>
*  <li>Manage the operation on the status related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class StatusControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	private static $db;
	
	/**
	* Status table
    */
	public static $status_table ;

	/**
	* Opens a database connexion and values the tables variables
	*/
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$status_table = $_SESSION['tablename']['status'];
		
		self::$db=$db;
	}	
	
	/**
	* Close the database connexion
	*/
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	/**
	* Returns an Status Object based on a status identifier
	*
	* @param  $status_id string  Status identifier
	* @return Status object with properties from the database or null
	*/
	public function get($status_id)
	{
		if(empty($status_id))
			return null;

		self::connect();
		$query = "select * from ".self::$status_table." where id = '".functions::protect_string_db($status_id)."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
		echo _NO_STATS_WITH_ID.' '.$status_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			$status = new Status();
			$queryResult=self::$db->fetch_object(); 
			foreach($queryResult as $key => $value){
				$status->$key=$value;
			}
			self::disconnect();
			return $status;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	
	/**
	* Saves in the database a Status object 
	*
	* @param  $group Status object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($status, $mode)
	{
		if(!isset($status) )
			return false;
			
		if($mode == "up")
			return self::update($status);
		elseif($mode =="add") 
			return self::insert($status);
		
		return false;
	}
	
	/**
	* Inserts in the database (statuss table) a Status object
	*
	* @param  $status Status object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($status)
	{
		if(!isset($status) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($status);

		$query="insert into ".self::$status_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_STATUS." ".$status->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	/**
	* Updates a status in the database (status table) with a Status object
	*
	* @param  $status Status object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($status)
	{
		if(!isset($status) )
			return false;
			
		self::connect();
		$query="update ".self::$status_table." set "
					.self::update_prepare($status)
					." where id='".functions::protect_string_db($status->id)."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_STATUS." ".$status->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Deletes in the database (status table) a given status (status_id)
	*
	* @param  $status_id string  Status identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($status_id)
	{
		if(!isset($status_id)|| empty($status_id) )
			return false;
		if(! self::statusExists($status_id))
			return false;
			
		self::connect();
		$query="delete from ".self::$status_table." where id='".$status_id."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_STATUS_ID." ".$status_id.' // ';
			$ok = false;
		}
		self::disconnect();
	
		return $ok;
	}
	
	
	/**
	* Asserts if a given status (status_id) exists in the database
	* 
	* @param  $status_id String Status identifier
	* @return bool true if the status exists, false otherwise 
	*/
	public function statusExists($status_id)
	{
		if(!isset($status_id) || empty($status_id))
			return false;

		self::connect();
		$query = "select id from ".self::$status_table." where id = '".functions::protect_string_db($status_id)."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN.' '._STATUS." ".$status_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}
	
	/**
	* Prepares the update query for a given Status object
	*
	* @param  $status Status object
	* @return String containing the fields and the values 
	*/
	private function update_prepare($status)
	{
		$result=array();
		foreach($status->getArray() as $key => $value)
		{
			// For now all fields in the status table are strings or dates
			if(!empty($value))
			{
				$result[]=$key."='".functions::protect_string_db($value)."'";		
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	} 
	
	/**
	* Prepares the insert query for a given Status object
	*
	* @param  $status Status object
	* @return Array containing the fields and the values 
	*/
	private function insert_prepare($status)
	{
		$columns=array();
		$values=array();
		foreach($status->getArray() as $key => $value)
		{
			//For now all fields in the statuss table are strings or dates
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".functions::protect_string_db($value)."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
}
?>
