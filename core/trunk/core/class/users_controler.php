<?php
/*
*    Copyright 2008-2010 Maarch
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
* @brief  Contains the controler of the users object (create, save, modify, etc...)
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
	require_once("core/core_tables.php");
	require_once("core/class/users.php");
	require_once("core/class/ObjectControlerAbstract.php");
	require_once("core/class/ObjectControlerIF.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the users object 
*
*<ul>
*  <li>Get an users object from an id</li>
*  <li>Save in the database a user</li>
*  <li>Manage the operation on the users related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class users_controler extends ObjectControler implements ObjectControlerIF
{
	/**
	* Returns an users object based on a user identifier
	*
	* @param  $user_id string  User identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the user even if it is disabled in the database (false by default)
	* @return users object with properties from the database or null
	*/
	public function get($user_id, $comp_where = '', $can_be_disabled = false)
	{
		self::set_foolish_ids(array('user_id'));
		self::set_specific_id('user_id');
		$user = self::advanced_get($user_id,USERS_TABLE);	
		
		if(isset($user) && $user->__get('status') == 'OK')
			return $user;
		else
			return null;
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

		self::$db=new dbquery();
		self::$db->connect();
		$query = "select group_id, primary_group, role from ".USERGROUP_CONTENT_TABLE." where user_id = '".functions::protect_string_db($user_id)."'";
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
		self::$db->disconnect();
		return $groups;
	}
	
	/**
	* Saves in the database a users object 
	*
	* @param  $group users object to be saved
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($user)
	{
		if(!isset($user) )
			return false;
		
		self::set_foolish_ids(array('user_id'));
		self::set_specific_id('user_id');
		if(self::userExists($user->user_id))
			return self::update($user);
		else
			return self::insert($user);
		
		return false;
	}
	
	/**
	* Inserts in the database (users table) a users object
	*
	* @param  $user users object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($user)
	{
		return self::advanced_insert($user);
	}

	/**
	* Updates a user in the database (users table) with a users object
	*
	* @param  $user users object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($user)
	{
		return self::advanced_update($user);
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
			
		self::$db=new dbquery();
		self::$db->connect();
		$query="update ".USERS_TABLE." set status = 'DEL' where user_id='".functions::protect_string_db($user_id)."'";
		// Logic deletion only , status becomes DEL to keep the user data
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_USER_ID." ".$user_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
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
		
		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from ".USERGROUP_CONTENT_TABLE."  where user_id='".functions::protect_string_db($user_id)."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_USER_ID." ".$user_id.' // ';
			$ok = false;
		}
		
		self::$db->disconnect();
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

		self::$db=new dbquery();
		self::$db->connect();
		$query = "select user_id from ".USERS_TABLE." where user_id = '".functions::protect_string_db($user_id)."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN.' '._USER." ".$user_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
		return false;
	}
	

	/**
	* Disables a given user
	* 
	* @param  $user users object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($user)
	{
		self::set_foolish_ids(array('user_id'));
		self::set_specific_id('user_id');
		return self::advanced_disable($user);
	}
	
	/**
	* Enables a given user
	* 
	* @param  $user users object 
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($user)
	{
		self::set_foolish_ids(array('user_id'));
		self::set_specific_id('user_id');
		return self::advanced_enable($user);
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
			
		self::$db=new dbquery();
		self::$db->connect();
		$ok = true;
		for($i=0; $i < count($array ); $i++)
		{
			if($ok) 
			{
				$query = "INSERT INTO ".USERGROUP_CONTENT_TABLE." (user_id, group_id, primary_group, role) VALUES ('".functions::protect_string_db($user_id)."', '".functions::protect_string_db($array[$i]['GROUP_ID'])."', '".functions::protect_string_db($array[$i]['PRIMARY'])."', '".functions::protect_string_db($array[0]['ROLE'])."')";
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
		self::$db->disconnect();
		return $ok;		
	}
}
?>
