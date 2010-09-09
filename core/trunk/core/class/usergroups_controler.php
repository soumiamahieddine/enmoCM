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
* @brief  Contains the controler of the usergroups object (create, save, modify, etc...)
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
	require_once("modules/basket/basket_tables.php");
	require_once("core/class/usergroups.php");
	require_once("core/class/ObjectControlerAbstract.php");
	require_once("core/class/ObjectControlerIF.php");
	require_once("core/class/SecurityControler.php");
	
} catch (Exception $e){
	echo $e->getMessage().' // ';
}


/**
* @brief  Controler of the usergroups object 
*
*<ul>
*  <li>Get an usergroups object from an id</li>
*  <li>Save in the database a usergroup</li>
*  <li>Manage the operation on the usergroups related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class usergroups_controler extends ObjectControler implements ObjectControlerIF
{

	/**
	* Returns an usergroups object based on a usegroup identifier
	*
	* @param  $group_id string  Usergroup identifier
	* @param  $can_be_disabled bool  if true gets the group even if it is disabled in the database (false by default)
	* @return usergroups object with properties from the database or null
	*/
	public function get($group_id, $can_be_disabled = false)
	{	
		self::set_foolish_ids(array('group_id'));
		self::set_specific_id('group_id');
		return self::advanced_get($group_id,USERGROUPS_TABLE);	
	}
	
	/**
	* Returns all usergroups (enabled by default) from the database in an array of usergroups objects (ordered by group_desc by default)
	*
	* @param  $order_str string  Order string passed to the query ("order by group_desc asc" by default)
	* @param  $enabled_only bool  if true returns only the enabled usergroups, otherwise returns even the disabled (true by default)
	* @return Array of usergroups objects with properties from the database
	*/
	public function getAllUsergroups($order_str = "order by group_desc asc", $enabled_only = true)
	{
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select * from ".USERGROUPS_TABLE." ";
		if($enabled_only)
			$query .= "where enabled = 'Y'";
		
		$query.= $order_str;
		
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){}
		
		$groups = array();
		while($res = self::$db->fetch_object())
		{
			$group=new usergroups();
			$tmp_array = array('group_id' => $res->group_id, 'group_desc' => $res->group_desc, 'enabled' => $res->enabled);
			$group->setArray($tmp_array);
			array_push($groups, $group);
		}
		self::$db->disconnect();
		return $groups;
	}
	
	/**
	* Returns in an array all the members of a usergroup (user_id only) 
	*
	* @param  $group_id string  Usergroup identifier
	* @return Array of user_id or null
	*/
	public function getUsers($group_id)
	{		
		if(empty($group_id))
			return null;

		$users = array();
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select user_id from ".USERGROUP_CONTENT_TABLE." where group_id = '".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
					self::$db->query($query);
		} catch (Exception $e){
					echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		while($res = self::$db->fetch_object())
		{
			array_push($users, $res->user_id);
		}
		self::$db->disconnect();
		return $users;
	}
	
	/**
	* Returns the id of the primary group for a given user_id
	*
	* @param  $user_id string  User identifier
	* @return String  group_id or null
	*/
	public function getPrimaryGroup($user_id)
	{		
		if(empty($user_id))
			return null;

		$users = array();
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select group_id from ".USERGROUP_CONTENT_TABLE." where user_id = '".$user_id."' and primary_group = 'Y'";

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
					self::$db->query($query);
		} catch (Exception $e){
					echo _NO_USER_WITH_ID.' '.$user_id.' // ';
		}
		
		$res = self::$db->fetch_object();
		$group_id = $res->group_id;
		self::$db->disconnect();
		return $group_id;
	}
	
	/**
	* Returns in an array all the baskets associated with a usergroup (basket_id only) 
	*
	* @param  $group_id string  Usergroup identifier
	* @return Array of basket_id or null
	*/
	public function getBaskets($group_id)
	{
		if(empty($group_id))
			return null;
		
		$baskets = array();
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select basket_id from ".GROUPBASKET_TABLE." where group_id = '".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
					self::$db->query($query);
		} catch (Exception $e){
					echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		while($res = self::$db->fetch_object())
		{
			array_push($baskets, $res->basket_id);
		}
		self::$db->disconnect();
		return $baskets;
	}
	
	/**
	* Returns in an array all the services linked to a usergroup (service_id only) 
	*
	* @param  $group_id string  Usergroup identifier
	* @return Array of service_id or null
	*/
	public function getServices($group_id)
	{
		if(empty($group_id))
			return null;

		self::$db=new dbquery();
		self::$db->connect();
		$query = "select service_id from ".USERGROUPS_SERVICES_TABLE." where group_id = '".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		$services = array();
		while($queryResult=self::$db->fetch_object())
		{
			array_push($services,trim($queryResult->service_id));
		}
		self::$db->disconnect();
		return $services;
	}
	
	/**
	* Saves in the database a usergroups object 
	*
	* @param  $group usergroups object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($group)
	{
		if(!isset($group) )
			return false;
		
		self::set_foolish_ids(array('group_id'));
		self::set_specific_id('group_id');
		if(self::groupExists($group->group_id))
			return self::update($group);
		else
			return self::insert($group);
		
		return false;
	}
	
	/**
	* Inserts in the database (usergroups table) a usergroups object
	*
	* @param  $group usergroups object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($group)
	{
		return self::advanced_insert($group);
	}

	/**
	* Updates a usergroup in the database (usergroups table) with a usergroups object
	*
	* @param  $group usergroups object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($group)
	{
		return self::advanced_update($group);
	}
	
	/**
	* Deletes in the database (usergroups related tables) a given usergroup (group_id)
	*
	* @param  $group usergroups object
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($group)
	{
		self::set_foolish_ids(array('group_id'));
		self::set_specific_id('group_id');
		
		$group_id = $group->__get('group_id');
		$ok = self::advanced_delete($group);
		if($ok)
			$ok = self::cleanUsergroupContent($group_id);

		if($ok)
			$ok = self::deleteServicesForGroup($group_id);
		
		if($ok)
			$ok = SecurityControler::deleteForGroup($group_id);

		return $ok;
	}
	
	/**
	* Cleans the usergroup_content table in the database from a given usergroup (group_id)
	*
	* @param  $group_id string  Usergroup identifier
	* @return bool true if the cleaning is complete, false otherwise
	*/
	private function cleanUsergroupContent($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
		
		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from ".USERGROUP_CONTENT_TABLE." where group_id='".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_GROUP_ID." ".$group_id.' // ';
			$ok = false;
		}
		
		return $ok;
	}
	
	
	/**
	* Disables a given usergroup
	* 
	* @param  $group usergroups object
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($group)
	{
		self::set_foolish_ids(array('group_id'));
		self::set_specific_id('group_id');
		return self::advanced_disable($group);
	}
	
	/**
	* Enables a given usergroup
	* 
	* @param  $group usergroups object
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($group)
	{
		self::set_foolish_ids(array('group_id'));
		self::set_specific_id('group_id');
		return self::advanced_enable($group);
	}
	
	/**
	* Asserts if a given usergroup (group_id) exists in the database
	* 
	* @param  $group_id String Usergroup identifier
	* @return bool true if the usergroup exists, false otherwise 
	*/
	public function groupExists($group_id)
	{
		if(!isset($group_id) || empty($group_id))
			return false;

		self::$db=new dbquery();
		self::$db->connect();
		$query = "select group_id from ".USERGROUPS_TABLE." where group_id = '".$group_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._GROUP." ".$group_id.' // ';
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
	* Deletes all the services for a given usergroup in the usergroups_service table
	* 
	* @param  $group_id String Usergroup identifier
	* @return bool true if the deleting is complete, false otherwise 
	*/
	public function deleteServicesForGroup($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from ".USERGROUPS_SERVICES_TABLE." where group_id='".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_GROUP_ID." ".$group_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		return $ok;
	}
	
	/**
	* Inserts a given service for a given group into the usergroups_services table
	* 
	* @param  $group_id String Usergroup identifier
	* @param  $service_id String Service identifier
	* @return bool true if the insertion is complete, false otherwise 
	*/
	public function insertServiceForGroup($group_id, $service_id)
	{
		if(!isset($group_id)|| empty($group_id) || !isset($service_id)|| empty($service_id) )
			return false;
			
		self::$db=new dbquery();
		self::$db->connect();
		$query = "insert into ".USERGROUPS_SERVICES_TABLE." (group_id, service_id) values ('".$group_id."', '".$service_id."')";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT." ".$group_id.' '.$service_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		return $ok;
	}
	
	/**
	* Checks if a given user is a member of the given group
	* 
	* @param  $user_id String User identifier
	* @param  $group_id String Usergroup identifier
	* @return bool true if the user is a member, false otherwise
	*/
	public function inGroup($user_id, $group_id)
	{
		if(!isset($group_id)|| empty($group_id) || !isset($user_id)|| empty($user_id) )
			return false;
			
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select user_id from ".USERGROUP_CONTENT_TABLE." where user_id ='".$user_id."' and group_id = '".$group_id."'";

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_FIND." ".$group_id.' '.$user_id.' // ';
		}
		self::$db->disconnect();
		
		if(self::$db->nb_result() > 0)
			return true;
		else
			return false;
	}
	
	/**
	* Returns the number of usergroup of the usergroups table (only the enabled by default)
	* 
	* @param  $enabled_only Bool if true counts only the enabled ones, otherwise counts all usergroups even the disabled ones (true by default)
	* @return Integer the number of usergroups in the usergroups table
	*/
	public function getUsergroupsCount($enabled_only = true)
	{
		$nb = 0;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select group_id from ".USERGROUPS_TABLE." " ;
		if($enabled_only)
			$query .= "where enabled ='Y'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){}
		
		$nb = self::$db->nb_result();			
		self::$db->disconnect();
		return $nb;
	}
}
?>
