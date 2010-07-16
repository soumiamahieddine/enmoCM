<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/class_db.php");
	require_once("core/class/Security.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class SecurityControler{
	
	private static $db;
	private static $security_table;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		
		self::$security_table = $_SESSION['tablename']['security'];
		self::$db=$db;
	}	
	
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	public function get($security_id)
	{
		if(empty($security_id))
			return null;

		self::connect();
		// Querying database
		$query = "select * from ".self::$security_table." where security_id = ".$security_id;
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_ACCESS_WITH_ID.' '.$security_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			$access=new SecurityObj();
			$queryResult=self::$db->fetch_object();  
			foreach($queryResult as $key => $value){
				$access->$key=$value;
			}
			self::disconnect();
			return $access;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	public function getAccessForGroup($group_id)
	{
		if(empty($group_id))
			return null;
			
		self::connect();
		// Querying database
		$query = "select * from ".self::$security_table." where group_id = '".$group_id."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_GROUP_WITH_ID.' '.$group_id.' // ';
		}
		
		$security = array();
		if(self::$db->nb_result() > 0)
		{
			while($queryResult = self::$db->fetch_object())
			{
				$access=new SecurityObj();
				foreach($queryResult as $key => $value){
					$access->$key=$value;
				}
				array_push($security, $access);
			}
		}
		self::disconnect();
		return $security;
	}
	
	public function save($security, $mode="add")
	{
		if(!isset($security))
			return false;
			
		if($mode == "up")
			return self::update($security); 
		elseif($mode == "add") 
			return self::insert($security);
			
		return false;
	}
	
	private function insert($security)
	{
		if(!isset($security))
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($security);
		
		// Inserting object
		$query="insert into ".self::$security_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_ACCESS." ".$security->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	private function update($security)
	{
		if(!isset($security))
			return false;
			
		self::connect();
		$query="update ".self::$security_table." set "
					.self::update_prepare($security)
					." where security_id=".$security->security_id; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_ACCESS." ".$security->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function delete($security_id)
	{
		if(!isset($security_id)|| empty($security_id) )
			return false;
			
		self::connect();
		$query="delete from ".self::$security_table." where security_id=".$security_id;
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_SECURITY_ID." ".$security_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function deleteForGroup($group_id)
	{
		if(!isset($group_id)|| empty($group_id) )
			return false;
			
		self::connect();
		$query="delete from ".self::$security_table." where group_id='".$group_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE.' '._GROUP_ID." ".$group_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update_prepare($security)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($security->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings or date excepts the security_id
			if(!empty($value))
			{
				if($key <> 'security_id')
					$result[]=$key."='".$value."'";
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	} 
	
	private function insert_prepare($security)
	{
		$columns=array();
		$values=array();
		foreach($security->getArray() as $key => $value)
		{
			// For now all fields in the usergroups table are strings or date excepts the security_id
			if(!empty($value))
			{
				if($key <> 'security_id')
				{
					$columns[]=$key;
					$values[]="'".$value."'";
				}
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
}
?>
