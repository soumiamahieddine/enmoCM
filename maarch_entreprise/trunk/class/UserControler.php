<?php

define ("_DEBUG", false);
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
	public static $users_table;
	
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
	
	public function get($user_id)
	{
		if(empty($user_id))
		{
			// Nothing to get
			return null;
		} 
		// Querying database
		$query = "select * from ".self::$users_table." where user_id = '".$user_id."' and enabled = 'Y'";
		try{
			if(_DEBUG){echo $select.' // ';}
			self::$db->query($select);
		} catch (Exception $e){
			echo _NO_USER_WITH_ID.' '.$user_id.' // ';
		}
		// Constructing object
		$user=new User();
		$queryResult=self::$db->fetch_object();  // TO DO : rajouter les entités
		foreach($queryResult as $key => $value){
			$user->$key=$value;
		}
		return $user;
	}
	
	
	public function save($user)
	{
		if($user->user_id > 0){
			// Update existing user
			self::update($user);
		} else {
			// Insert new user
			self::insert($user);
		}
	}
	
	private function insert($user)
	{
		$prep_query = self::insert_prepare($user);
		
		// Inserting object
		$query="insert into ".self::$users_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if(_DEBUG){ echo $query.' // '; }
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_INSERT_USER." ".$user->toString().' // ';
		}
	}

	private function update($user)
	{
		$query="update ".self::$users_table." "
					.self::update_prepare($user)
					." where user_id='".$user->user_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_UPDATE_USER." ".$user->toString().' // ';
		}
	}
	
	public function delete($user_id){
		$query="delete from ".self::$users_table." where user_id='".$user_id."'";
		try{
			if(DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_DELETE_USER_ID." ".$user_id.' // ';
		}
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
	private function insert_prepare($user){
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
		$query="update ".self::$users_table." set enabled = 'N' where user_id='".$user_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_DISABLE_USER." ".$user_id.' // ';
		}
	}
	
	public function enable($user_id)
	{
		$query="update ".self::$users_table." set enabled = 'Y' where user_id='".$user_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_ENABLE_USER." ".$user_id.' // ';
		}
	}
}
?>
