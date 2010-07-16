<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/class_db.php");
	require_once("modules/entities/class/Entity.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class EntityControler{
	
	private static $db;
	public static $entities_table ;
	public static $users_entities_table ;
	public static $groupbasket_redirect_table ;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$entities_table = $_SESSION['tablename']['ent_entities'];
		self::$users_entities_table = $_SESSION['tablename']['ent_users_entities'];
		self::$groupbasket_redirect_table = $_SESSION['tablename']['ent_groupbasket_redirect'];
		
		self::$db=$db;
	}	
	
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	public function get($entity_id, $can_be_disabled = false)
	{
		if(empty($entity_id))
			return null;

		self::connect();
		// Querying database
		$query = "select * from ".self::$entities_table." where entity_id = '".$entity_id."'";
		if(!$can_be_disabled)
			$query .= " and enabled = 'Y'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_ENTITY_WITH_ID.' '.$entity_id.' // ';
		}
		if(self::$db->nb_result() > 0)
		{
			// Constructing object
			$entity = new EntityObj();
			$queryResult=self::$db->fetch_object();  
			foreach($queryResult as $key => $value){
				$entity->$key=$value;
			}
			self::disconnect();
			return $entity;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	
	public function save($entity, $mode)
	{
		if(!isset($entity) )
			return false;
			
		if($mode == "up")
			return self::update($entity);
		elseif($mode =="add") 
			return self::insert($entity);
		
		return false;
	}
	
	
	private function insert($entity)
	{
		if(!isset($entity) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($entity);
		
		// Inserting object
		$query="insert into ".self::$entities_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_ENTITY." ".$entity->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	private function update($entity)
	{
		if(!isset($entity) )
			return false;
			
		self::connect();
		$query="update ".self::$entities_table." set "
					.self::update_prepare($entity)
					." where entity_id='".$entity->entity_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_ENTITY." ".$entity->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function delete($entity_id)
	{
		if(!isset($entity_id)|| empty($entity_id) )
			return false;
		if(! self::userExists($entity_id))
			return false;
			
		self::connect();
		$query="delete from ".self::$entities_table."  where entity_id='".$entity_id."'";
	
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_ENTITY_ID." ".$entity_id.' // ';
			$ok = false;
		}
		
		if($ok)
			$ok = cleanGroupbasketRedirect($entity_id);
		
		if($ok)
			$ok = cleanUsersentities($entity_id);

		self::disconnect();
		return $ok;
	}
	
	public function cleanUsersentities($entity_id)
	{
		if(!isset($entity_id)|| empty($entity_id) )
			return false;
		
		self::connect();
		$query="delete from ".self::$users_entities_table." where entity_id='".$entity_id."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_ENTITY." ".$entity_id.' // ';
			$ok = false;
		}

		self::disconnect();
		return $ok;
	}
	
	public function cleanGroupbasketRedirect($id, $field = 'entity_id')
	{
		if(!isset($id)|| empty($id) )
			return false;
		
		self::connect();
		$query="delete from ".self::$groupbasket_redirect_table." where ".$field."='".$id."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE.' '.$field." ".$id.' // ';
			$ok = false;
		}

		self::disconnect();
		return $ok;
	}
	
	public function entityExists($entity_id)
	{
		if(!isset($entity_id) || empty($entity_id))
			return false;

		self::connect();
		$query = "select entity_id from ".self::$entities_table." where entity_id = '".$entity_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN.' '._ENTITY." ".$entity_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}
	
	private function update_prepare($entity)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($entity->getArray() as $key => $value)
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
	private function insert_prepare($entity)
	{
		$columns=array();
		$values=array();
		foreach($entity->getArray() as $key => $value)
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
	
	public function disable($entity_id)
	{
		if(!isset($entity_id)|| empty($entity_id) )
			return false;
		if(! self::entityExists($entity_id))
			return false;
			
		self::connect();
		$query="update ".self::$entities_table." set enabled = 'N' where entity_id='".$entity_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DISABLE_ENTITY." ".$entity_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function enable($entity_id)
	{
		if(!isset($entity_id)|| empty($entity_id) )
			return false;
		if(! self::entityExists($entity_id))
			return false;
		
		self::connect();
		$query="update ".self::$entities_table." set enabled = 'Y' where entity_id='".$entity_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_ENABLE_ENTITY." ".$entity_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
}
?>
