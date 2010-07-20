<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/class_db.php");
	require_once("core/class/Usergroup.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class UsergroupControler
{
	
	static $db;
	static $usergroups_table;
	static $usergroup_content_table;
	static $groupbasket_table ;
	static $groups_services_table;
	
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
	
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	public function get($group_id, $can_be_disabled = false)
	{	
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
			// Constructing object
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
	
	public function getUsers($group_id)
	{	
		$users = array();
		if(empty($group_id))
			return null;

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
	
	public function getBaskets($group_id)
	{
		$baskets = array();
		if(empty($group_id))
			return null;

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
	
	public function getServices($group_id)
	{
		if(empty($group_id))
			return null;

		self::connect();
		// Querying database
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
	
	private function update_prepare($group)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($group->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings
			if(!empty($value))
			{
				$result[]=$key."='".$value."'";
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	} 
	
	/**
	 * Prepare string for update query
	 * @param Aggregation $aggregation
	 * @return String
	 */
	private function insert_prepare($group){
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
