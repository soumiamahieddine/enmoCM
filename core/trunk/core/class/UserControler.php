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
* @brief  Contains the controler of the User Object (create, save, modify, etc...)
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
	require_once("core/class/User.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the User Object 
*
*<ul>
*  <li>Get an user object from an id</li>
*  <li>Save in the database a user</li>
*  <li>Manage the operation on the users related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class UserControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	private static $db;
	
	/**
	* Users table
    */
	public static $users_table ;
	
	/**
	* Usergroup_content table
    */
	public static $usergroup_content_table ;
	
	/**
	* Opens a database connexion and values the tables variables
	*/
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$users_table = $_SESSION['tablename']['users'];
		self::$usergroup_content_table = $_SESSION['tablename']['usergroup_content'];
		
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
	* Returns an User Object based on a user identifier
	*
	* @param  $user_id string  User identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the user even if it is disabled in the database (false by default)
	* @return User object with properties from the database or null
	*/
	public function get($user_id, $comp_where = '', $can_be_disabled = false)
	{
		if(empty($user_id))
			return null;

		self::connect();
		$query = "select * from ".self::$users_table." where user_id = '".functions::protect_string_db($user_id)."'";
		if(!$can_be_disabled)
			$query .= " and enabled = 'Y'";
		$query .= $comp_where;
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_USER_WITH_ID.' '.$user_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			$user = new User();
			$queryResult=self::$db->fetch_object();  // TO DO : rajouter les entités
			foreach($queryResult as $key => $value){
				$user->$key=$value;
			}
			self::disconnect();
			return $user;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	/**
	* Returns in an array all the groups associated with a user (user_id, group_id, primary_group and role)
	*
	* @param  $user_id string  User identifier
	* @return Array or null
	*/
	public function getGroups($user_id)
	{	
		$groups = array();
		if(empty($user_id))
			return null;

		self::connect();
		$query = "select group_id, primary_group, role from ".self::$usergroup_content_table." where user_id = '".functions::protect_string_db($user_id)."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
					self::$db->query($query);
		} catch (Exception $e){
					echo _NO_USER_WITH_ID.' '.$user_id.' // ';
		}
		
		while($res = self::$db->fetch_object())
		{
			array_push($groups, array('USER_ID' => $user_id, 'GROUP_ID' => $res->group_id, 'PRIMARY' => $res->primary_group, 'ROLE' => $res->role));
		}
		self::disconnect();
		return $groups;
	}
	
	/**
	* Saves in the database a User object 
	*
	* @param  $group User object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($user, $mode)
	{
		if(!isset($user) )
			return false;
			
		if($mode == "up")
			return self::update($user);
		elseif($mode =="add") 
			return self::insert($user);
		
		return false;
	}
	
	/**
	* Inserts in the database (users table) a User object
	*
	* @param  $user User object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($user)
	{
		if(!isset($user) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($user);

		$query="insert into ".self::$users_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_USER." ".$user->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	/**
	* Updates a user in the database (users table) with a User object
	*
	* @param  $user User object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($user)
	{
		if(!isset($user) )
			return false;
			
		self::connect();
		$query="update ".self::$users_table." set "
					.self::update_prepare($user)
					." where user_id='".functions::protect_string_db($user->user_id)."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_USER." ".$user->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Deletes in the database (users related tables) a given user (user_id)
	*
	* @param  $user_id string  User identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(! self::userExists($user_id))
			return false;
			
		self::connect();
		$query="update ".self::$users_table." set status = 'DEL' where user_id='".functions::protect_string_db($user_id)."'";
		// Logic deletion only , status becomes DEL to keep the user data
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_USER_ID." ".$user_id.' // ';
			$ok = false;
		}
		self::disconnect();
		if($ok)
			$ok = self::cleanUsergroupContent($user_id);
		
		return $ok;
	}
	
	/**
	* Cleans the usergroup_content table in the database from a given user (user_id)
	*
	* @param  $user_id string  User identifier
	* @return bool true if the cleaning is complete, false otherwise
	*/
	public function cleanUsergroupContent($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		
		self::connect();
		$query="delete from ".self::$usergroup_content_table."  where user_id='".functions::protect_string_db($user_id)."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_USER_ID." ".$user_id.' // ';
			$ok = false;
		}
		
		self::disconnect();
		return $ok;
	}
	
	/**
	* Asserts if a given user (user_id) exists in the database
	* 
	* @param  $user_id String User identifier
	* @return bool true if the user exists, false otherwise 
	*/
	public function userExists($user_id)
	{
		if(!isset($user_id) || empty($user_id))
			return false;

		self::connect();
		$query = "select user_id from ".self::$users_table." where user_id = '".functions::protect_string_db($user_id)."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN.' '._USER." ".$user_id.' // ';
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
	* Prepares the update query for a given User object
	*
	* @param  $user User object
	* @return String containing the fields and the values 
	*/
	private function update_prepare($user)
	{
		$result=array();
		foreach($user->getArray() as $key => $value)
		{
			// For now all fields in the users table are strings or dates
			if(!empty($value))
			{
				$result[]=$key."='".functions::protect_string_db($value)."'";		
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	} 
	
	/**
	* Prepares the insert query for a given User object
	*
	* @param  $user User object
	* @return Array containing the fields and the values 
	*/
	private function insert_prepare($user)
	{
		$columns=array();
		$values=array();
		foreach($user->getArray() as $key => $value)
		{
			//For now all fields in the users table are strings or dates
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".functions::protect_string_db($value)."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	/**
	* Disables a given user
	* 
	* @param  $user_id String User identifier
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(! self::userExists($user_id))
			return false;
			
		self::connect();
		$query="update ".self::$users_table." set enabled = 'N' where user_id='".functions::protect_string_db($user_id)."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DISABLE_USER." ".$user_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Enables a given user
	* 
	* @param  $user_id String User identifier
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(! self::userExists($user_id))
			return false;
		
		self::connect();
		$query="update ".self::$users_table." set enabled = 'Y' where user_id='".functions::protect_string_db($user_id)."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_ENABLE_USER." ".$user_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Loads into the usergroup_content table the given data for a given user
	* 
	* @param  $user_id String User identifier
	* @param  $array Array 
	* @return bool true if the loadng is complete, false otherwise 
	*/
	public function loadDbUsergroupContent($user_id, $array)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(!isset($array) || count($array) == 0)
			return false;
			
		self::connect();
		$ok = true;
		for($i=0; $i < count($array ); $i++)
		{
			if($ok) 
			{
				$query = "INSERT INTO ".self::$usergroup_content_table." (user_id, group_id, primary_group, role) VALUES ('".functions::protect_string_db($user_id)."', '".functions::protect_string_db($array[$i]['GROUP_ID'])."', '".functions::protect_string_db($array[$i]['PRIMARY'])."', '".functions::protect_string_db($array[0]['ROLE'])."')";
				try{
					if($_ENV['DEBUG']){echo $query.' // ';}
					self::$db->query($query);
					$ok = true;
				} catch (Exception $e){
					$ok = false;
				}
			}
			else
				break;
		}
		self::disconnect();
		return $ok;		
	}
}
?>
