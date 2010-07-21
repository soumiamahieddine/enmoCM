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
* @brief  Contains the controler of the Usergroup Object (create, save, modify, etc...)
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
	require_once("core/class/Usergroup.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}


/**
* @brief  Controler of the Usergroup Object 
*
*<ul>
*  <li>Get an usergroup object from an id</li>
*  <li>Save in the database a usergroup</li>
*  <li>Manage the operation on the usergroups related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class UsergroupControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	static $db;
	
	/**
	* Usergroups table
    */
	static $usergroups_table;
	
	/**
	* Usergroup_content table
    */
	static $usergroup_content_table;
	
	/**
	* Groupbasket table
    */
	static $groupbasket_table ;
	
	/**
	* Usergroups_services table
    */
	static $groups_services_table;
	
	
	/**
	* Opens a database connexion and values the tables variables
	*/
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		
		self::$usergroups_table = $_SESSION['tablename']['usergroups'];
		self::$usergroup_content_table = $_SESSION['tablename']['usergroup_content'];
		self::$groupbasket_table = $_SESSION['tablename']['bask_groupbasket'];
		self::$groups_services_table = $_SESSION['tablename']['usergroup_services'];
		
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
	* Returns an Usergroup Object based on a usegroup identifier
	*
	* @param  $group_id string  Usergroup identifier
	* @param  $can_be_disabled bool  if true gets the group even if it is disabled in the database (false by default)
	* @return Usergroup object with properties from the database or null
	*/
	public function get($group_id, $can_be_disabled = false)
	{	
		// If no group_id specified return null
		if(empty($group_id))
			return null;
		
		self::connect();
		$query = "select * from ".self::$usergroups_table." where group_id = '".$group_id."' ";
		
		if(!$can_be_disabled)
			$query .= " and enabled = 'Y'";

		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			$group=new Usergroup();
			$queryResult=self::$db->fetch_object();
			foreach($queryResult as $key => $value){
				$group->$key=$value;
			}
			self::disconnect();
			return $group;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	/**
	* Returns all usergroups (enabled by default) from the database in an array of Usergroup Objects (ordered by group_desc by default)
	*
	* @param  $order_str string  Order string passed to the query ("order by group_desc asc" by default)
	* @param  $enabled_only bool  if true returns only the enabled usergroups, otherwise returns even the disabled (true by default)
	* @return Array of Usergroup objects with properties from the database
	*/
	public function getAllUsergroups($order_str = "order by group_desc asc", $enabled_only = true)
	{
		self::connect();
		$query = "select * from ".self::$usergroups_table." ";
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
			$group=new Usergroup();
			$tmp_array = array('group_id' => $res->group_id, 'group_desc' => $res->group_desc, 'enabled' => $res->enabled);
			$group->setArray($tmp_array);
			array_push($groups, $group);
		}
		self::disconnect();
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
		self::connect();
		$query = "select user_id from ".self::$usergroup_content_table." where group_id = '".$group_id."'";
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
		self::disconnect();
		return $users;
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
		self::connect();
		$query = "select basket_id from ".self::$groupbasket_table." where group_id = '".$group_id."'";
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
		self::disconnect();
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

		self::connect();
		$query = "select service_id from ".self::$groups_services_table." where group_id = '".$group_id."'";
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
		self::disconnect();
		return $services;
	}
	
	/**
	* Saves in the database a Usergroup object 
	*
	* @param  $group Usergroup object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($group, $mode)
	{
		if(!isset($group) )
			return false;
			
		if($mode == "up")
			return self::update($group);
		elseif($mode =="add") 
			return self::insert($group);
		
		return false;
	}
	
	/**
	* Inserts in the database (usergroups table) a Usergroup object
	*
	* @param  $group Usergroup object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($group)
	{
		if(!isset($group) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($group);
		
		// Inserting object
		$query="insert into ".self::$usergroups_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_GROUP." ".$group->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	/**
	* Updates a usergroup in the database (usergroups table) with a Usergroup object
	*
	* @param  $group Usergroup object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($group)
	{
		if(!isset($group) )
			return false;

		self::connect();
		$query="update ".self::$usergroups_table." set "
					.self::update_prepare($group)
					." where group_id='".$group->group_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_GROUP." ".$group->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Deletes in the database (usergroups related tables) a given usergroup (group_id)
	*
	* @param  $group_id string  Usergroup identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
		if(! self::groupExists($group_id))
			return false;
			
		self::connect();
		$query="delete from ".self::$usergroups_table." where group_id='".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_GROUP_ID." ".$group_id.' // ';
			$ok = false;
		}
		self::disconnect();
		if($ok)
			$ok = self::cleanUsergroupContent($group_id);

		if($ok)
			$ok = self::deleteServicesForGroup($group_id);

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
		
		self::connect();
		$query="delete from ".self::$usergroup_content_table." where group_id='".$group_id."'";
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
	* Prepares the update query for a given Usergroup object
	*
	* @param  $group Usergroup object
	* @return String containing the fields and the values 
	*/
	private function update_prepare($group)
	{	
		$result=array();
		foreach($group->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings
			if(!empty($value))
			{
				$result[]=$key."='".$value."'";
			}
		}

		return implode(",",$result);
	} 
	
	/**
	* Prepares the insert query for a given Usergroup object
	*
	* @param  $group Usergroup object
	* @return Array containing the fields and the values 
	*/
	private function insert_prepare($group)
	{
		$columns=array();
		$values=array();
		foreach($group->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".$value."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	/**
	* Disables a given usergroup
	* 
	* @param  $group_id String Usergroup identifier
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
		if(! self::groupExists($group_id))
			return false;
			
		self::connect();
		$query="update ".self::$usergroups_table." set enabled = 'N' where group_id='".$group_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DISABLE_GROUP." ".$group_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	/**
	* Enables a given usergroup
	* 
	* @param  $group_id String Usergroup identifier
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
		if(! self::groupExists($group_id))
			return false;
			
		self::connect();
		$query="update ".self::$usergroups_table." set enabled = 'Y' where group_id='".$group_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_ENABLE_GROUP." ".$group_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
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

		self::connect();
		$query = "select group_id from ".self::$usergroups_table." where group_id = '".$group_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._GROUP." ".$group_id.' // ';
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
	* Deletes all the services for a given usergroup in the usergroups_service table
	* 
	* @param  $group_id String Usergroup identifier
	* @return bool true if the deleting is complete, false otherwise 
	*/
	public function deleteServicesForGroup($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
		self::connect();
		$query="delete from ".self::$groups_services_table." where group_id='".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_GROUP_ID." ".$group_id.' // ';
			$ok = false;
		}
		self::disconnect();
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
			
		self::connect();
		$query = "insert into ".self::$groups_services_table." (group_id, service_id) values ('".$group_id."', '".$service_id."')";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT." ".$group_id.' '.$service_id.' // ';
			$ok = false;
		}
		self::disconnect();
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
			
		self::connect();
		$query = "select user_id from ".self::$usergroup_content_table." where user_id ='".$user_id."' and group_id = '".$group_id."'";

		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_FIND." ".$group_id.' '.$user_id.' // ';
		}
		self::disconnect();
		
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
		self::connect();
		
		$query = "select group_id from ".self::$usergroups_table." " ;
		if($enabled_only)
			$query .= "where enabled ='Y'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){}
		
		$nb = self::$db->nb_result();			
		self::disconnect();
		return $nb;
	}
}
?>
