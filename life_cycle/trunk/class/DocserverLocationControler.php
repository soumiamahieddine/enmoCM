<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."DocserverLocation.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class DocserverLocationControler
{
	
	private static $db;
	private static $docserver_locations_table;
	private static $docservers_table;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$docserver_locations_table = $_SESSION['tablename']['docserver_locations'];
		self::$docservers_table = $_SESSION['tablename']['docservers'];
		
		self::$db=$db;
	}
	
	public function disconnect()
	{
		self::$db->disconnect();
	}
	
	public function get($docserver_location_id, $can_be_disabled = false)
	{
		if(empty($docserver_location_id))
			return null;
		self::connect();
		$query = "select * from ".self::$docserver_locations_table." where docserver_location_id = '".$docserver_location_id."' and enabled = 'Y'";
		if(!$can_be_disabled)
			$query .= " and enabled = 'Y'";
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_DOCSERVER_LOCATION_WITH_ID.' '.$docserver_location_id.' // ';
		}
		if(self::$db->nb_result() > 0)
		{
			// Constructing object
			$docserverLocation = new DocserverLocation();
			$queryResult = self::$db->fetch_object();
			if($queryResult)
			{
				foreach($queryResult as $key => $value)
				{
					$docserverLocation->$key=$value;
				}
			}
			self::disconnect();
			return $docserverLocation;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	public function getAllId($can_be_disabled = false)
	{
		self::connect();
		$query = "select docserver_location_id from ".self::$docserver_locations_table." ";
		if(!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_DOCSERVER_LOCATION.' // ';
		}
		if(self::$db->nb_result() > 0)
		{
			$result = array();
			$cptId = 0;
			while($queryResult = self::$db->fetch_object())
			{
				$result[$cptId] = $queryResult->docserver_location_id;
				$cptId++;
			}
			self::disconnect();
			return $result;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	public function save($docserverLocation, $mode)
	{
		if(!isset($docserverLocation))
			return false;
			
		if($mode == "up")
			return self::update($docserverLocation);
		elseif($mode =="add") 
			return self::insert($docserverLocation);
		
		return false;
	}

	private function insert($docserverLocation)
	{
		if(!isset($docserverLocation))
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($docserverLocation);
		
		// Inserting object
		$query="insert into ".self::$docserver_locations_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_DOCSERVER_LOCATION." ".$docserverLocation->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update($docserverLocation)
	{
		if(!isset($docserverLocation))
			return false;

		self::connect();
		$query="update ".self::$docserver_locations_table." set "
					.self::update_prepare($docserverLocation)
					." where docserver_location_id='".$docserverLocation->docserver_location_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_DOCSERVER_LOCATION." ".$docserverLocation->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function delete($docserver_location_id)
	{
		if(!isset($docserver_location_id) || empty($docserver_location_id))
			return false;
		if(!self::docserverLocationExists($docserver_location_id))
			return false;
		if(!self::noDocservers($docserver_location_id))
			return false;
		self::connect();
		$query="delete from ".self::$docserver_locations_table." where docserver_location_id='".$docserver_location_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
			if($_SESSION['history']['docserverslocationsdel'] == "true")
			{
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$history = new history();
				$history->add($_SESSION['tablename']['docserver_locations'], $docserver_location_id, "DEL", _DOCSERVER_LOCATION_DELETED." : ".$docserver_location_id, $_SESSION['config']['databasetype']);
			}
		} catch (Exception $e){
			echo _CANNOT_DELETE_DOCSERVER_LOCATION_ID." ".$docserver_location_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update_prepare($docserverLocation)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($docserverLocation->getArray() as $key => $value)
		{
			// For now all fields in the docservers table are strings
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
	 * @param docserver $docserverLocation
	 * @return String
	 */
	private function insert_prepare($docserverLocation)
	{
		$columns=array();
		$values=array();
		foreach($docserverLocation->getArray() as $key => $value)
		{
			// For now all fields in the docservers table are strings
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".$value."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	public function disable($docserver_location_id)
	{
		if(!isset($docserver_location_id)|| empty($docserver_location_id))
			return false;
		if(! self::docserverLocationExists($docserver_location_id))
			return false;
			
		self::connect();
		$query="update ".self::$docserver_locations_table." set enabled = 'N' where docserver_location_id='".$docserver_location_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DISABLE_DOCSERVER_LOCATION." ".$docserver_location_id.' // ';
			$ok = false;
		}
		if($_SESSION['history']['docserverslocationsban'] == "true")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename']['docserver_locations'], $docserver_location_id, "BAN", _DOCSERVER_LOCATION_DISABLED." : ".$docserver_location_id, $_SESSION['config']['databasetype']);
		}
		self::disconnect();
		return $ok;
	}
	
	public function enable($docserver_location_id)
	{
		if(!isset($docserver_location_id)|| empty($docserver_location_id))
			return false;
		if(! self::docserverLocationExists($docserver_location_id))
			return false;
		
		self::connect();
		$query="update ".self::$docserver_locations_table." set enabled = 'Y' where docserver_location_id='".$docserver_location_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_ENABLE_DOCSERVER_LOCATION." ".$docserver_location_id.' // ';
			$ok = false;
		}
		if($_SESSION['history']['docserverslocationsban'] == "true")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename']['docserver_locations'], $docserver_location_id, "VAL",_DOCSERVER_LOCATION_ENABLED." : ".$docserver_location_id, $_SESSION['config']['databasetype']);
		}
		self::disconnect();
		return $ok;
	}
	
	public function docserverLocationExists($docserver_location_id)
	{
		if(!isset($docserver_location_id) || empty($docserver_location_id))
			return false;

		self::connect();
		$query = "select docserver_location_id from ".self::$docserver_locations_table." where docserver_location_id = '".$docserver_location_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER_LOCATION." ".$docserver_location_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}
	
	public function noDocservers($docserver_location_id)
	{
		if(!isset($docserver_location_id) || empty($docserver_location_id))
			return false;

		self::connect();
		$query = "select docserver_locations_docserver_location_id from ".self::$docservers_table." where docserver_locations_docserver_location_id = '".$docserver_location_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER_LOCATION." ".$docserver_location_id.' // ';
		}
		if(self::$db->nb_result() == 0)
		{
			self::disconnect();
			return true;
		}
		$_SESSION['error'] = _CANNOT_DEL_DOCSERVER_LOCATION;
		self::disconnect();
		return false;
	}
}
?>
