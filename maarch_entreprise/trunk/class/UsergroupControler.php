<?php

define ("_DEBUG", false);
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/class_db.php");
	require_once("apps/maarch_entreprise/class/Usergroup.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class UsergroupControler{
	
	private static $db;
	private static $usergroups_table;
	private static $usergroup_content_table;
	private static $groupbasket_table;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		
		self::$db=$db;
	}	
	
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	public function get_group($group_id)
	{
		if(empty($group_id))
		{
			// Nothing to get
			return null;
		} 
		// Querying database
		$query = "select * from ".self::$usergroups_table." where group_id = '".$group_id."' and enabled = 'Y'";
		try{
			if(_DEBUG){echo $select.' // ';}
			self::$db->query($select);
		} catch (Exception $e){
			echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		// Constructing object
		$group=new Usergroup();
		$queryResult=self::$db->fetch_object();
		foreach($queryResult as $key => $value){
			$group->$key=$value;
		}
		return $group;
	}
	
	public function get_users($group_id)
	{
		$users = array();
		if(empty($group_id))
		{
			// Nothing to get
			return null;
		}
		$query = "select user_id from ".self::$usergroup_content_table." where group_id = '".$group_id."'";
		try{
			if(_DEBUG){echo $select.' // ';}
					self::$db->query($select);
		} catch (Exception $e){
					echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		while($res = self::$db->fetch_object())
		{
			array_push($users, $res->user_id);
		}
		
		return $users;
	}
	
	public function get_baskets($group_id)
	{
		$baskets = array();
		if(empty($group_id))
		{
			// Nothing to get
			return null;
		}
		$query = "select basket_id from ".self::$groupbasket_table." where group_id = '".$group_id."'";
		try{
			if(_DEBUG){echo $select.' // ';}
					self::$db->query($select);
		} catch (Exception $e){
					echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		while($res = self::$db->fetch_object())
		{
			array_push($baskets, $res->basket_id);
		}
		
		return $baskets;
	}
	
	public function save($group){
		if($group->group_id > 0){
			// Update existing aggregation
			self::update($group);
		} else {
			// Insert new aggregation
			self::insert($group);
		}
	}
	
	private function insert($group)
	{
		$prep_query = self::insert_prepare($group);
		
		// Inserting object
		$query="insert into ".self::$usergroups_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if(_DEBUG){ echo $query.' // '; }
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_INSERT_GROUP." ".$group->toString().' // ';
		}
	}

	private function update($group)
	{
		$query="update ".self::$usergroups_table." "
					.self::update_prepare($group)
					." where group_id='".$group->group_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_UPDATE_GROUP." ".$group->toString().' // ';
		}
	}
	
	public function delete($group_id){
		$query="delete from ".self::$usergroups_table." where group_id='".$group_id."'";
		try{
			if(DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_DELETE_GROUP_ID." ".$group_id.' // ';
		}
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
		$query="update ".self::$usergroups_table." set enabled = 'N' where group_id='".$group_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_DISABLE_GROUP." ".$group_id.' // ';
		}
	}
	
	public function enable($group_id)
	{
		$query="update ".self::$usergroups_table." set enabled = 'Y' where group_id='".$group_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_ENABLE_GROUP." ".$group_id.' // ';
		}
	}
}
?>
