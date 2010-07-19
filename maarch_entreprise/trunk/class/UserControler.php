<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/class_db.php");
	require_once("apps/maarch_entreprise/class/User.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class UserControler{
	
	private static $db;
	public static $users_table ;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$users_table = $_SESSION['tablename']['users'];
		
		self::$db=$db;
	}	
	
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	public function get($user_id, $can_be_disabled = false)
	{
		if(empty($user_id))
			return null;

		self::connect();
		// Querying database
		$query = "select * from ".self::$users_table." where user_id = '".$user_id."'";
		if(!$can_be_disabled)
			$query .= " and enabled = 'Y'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_USER_WITH_ID.' '.$user_id.' // ';
		}
		if(self::$db->nb_result() > 0)
		{
			// Constructing object
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
	
	
	private function insert($user)
	{
		if(!isset($user) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($user);
		
		// Inserting object
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

	private function update($user)
	{
		if(!isset($user) )
			return false;
			
		self::connect();
		$query="update ".self::$users_table." set "
					.self::update_prepare($user)
					." where user_id='".$user->user_id."'"; 
					
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
	
	public function delete($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(! self::userExists($user_id))
			return false;
			
		self::connect();
		$query="update ".self::$users_table." set status = 'DEL' where user_id='".$user_id."'";
		// On passe le status à DEL pour pouvoir conserver les infos
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_USER_ID." ".$user_id.' // ';
			$ok = false;
		}
		
		if($ok)
			$ok = self::cleanUsergroupContent($user_id);
		// suppression dans user_abs + user_entities à faire dans le controler de page si module entities chargé
		self::disconnect();
		return $ok;
	}
	
	private function cleanUsergroupContent($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		
		self::connect();
		$query="delete from ".self::$usergroup_content_table."  where user_id='".$user_id."'";
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
	
	public function userExists($user_id)
	{
		if(!isset($user_id) || empty($user_id))
			return false;

		self::connect();
		$query = "select user_id from ".self::$users_table." where user_id = '".$user_id."'";
					
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
	
	private function update_prepare($user)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($user->getArray() as $key => $value)
		{
			// For now all fields in the users table are strings or dates
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
	 * @param User $user
	 * @return String
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
				$values[]="'".$value."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	public function disable($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(! self::userExists($user_id))
			return false;
			
		self::connect();
		$query="update ".self::$users_table." set enabled = 'N' where user_id='".$user_id."'"; 
					
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
	
	public function enable($user_id)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(! self::userExists($user_id))
			return false;
		
		self::connect();
		$query="update ".self::$users_table." set enabled = 'Y' where user_id='".$user_id."'"; 
					
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
}
?>
