<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."Docserver.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class DocserverControler
{
	
	private static $db;
	private static $docservers_table;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$docservers_table = $_SESSION['tablename']['docservers'];
		
		self::$db=$db;
	}
	
	public function disconnect()
	{
		self::$db->disconnect();
	}
	
	public function get($docserver_id, $can_be_disabled = false)
	{
		if(empty($docserver_id))
			return null;
		self::connect();
		$query = "select * from ".self::$docservers_table." where docserver_id = '".$docserver_id."' and enabled = 'Y'";
		if(!$can_be_disabled)
			$query .= " and enabled = 'Y'";
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_DOCSERVER_WITH_ID.' '.$docserver_id.' // ';
		}
		if(self::$db->nb_result() > 0)
		{
			// Constructing object
			$docserver = new Docserver();
			$queryResult = self::$db->fetch_object();
			if($queryResult)
			{
				foreach($queryResult as $key => $value)
				{
					$docserver->$key=$value;
				}
			}
			self::disconnect();
			return $docserver;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	public function save($docserver, $mode)
	{
		if(!isset($docserver))
			return false;
			
		if($mode == "up")
			return self::update($docserver);
		elseif($mode =="add") 
			return self::insert($docserver);
		
		return false;
	}

	private function insert($docserver)
	{
		if(!isset($docserver))
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($docserver);
		
		// Inserting object
		$query="insert into ".self::$docservers_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_DOCSERVER." ".$docserver->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update($docserver)
	{
		if(!isset($docserver))
			return false;

		self::connect();
		$query="update ".self::$docservers_table." set "
					.self::update_prepare($docserver)
					." where docserver_id='".$docserver->docserver_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_DOCSERVER." ".$docserver->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function delete($docserver_id)
	{
		if(!isset($docserver_id) || empty($docserver_id))
			return false;
		if(!self::docserverExists($docserver_id))
			return false;
		if(!self::noResources($docserver_id))
			return false;
		self::connect();
		$query="delete from ".self::$docservers_table." where docserver_id='".$docserver_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_DOCSERVER_ID." ".$docserver_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update_prepare($docserver)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($docserver->getArray() as $key => $value)
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
	 * @param docserver $docserver
	 * @return String
	 */
	private function insert_prepare($docserver){
		$columns=array();
		$values=array();
		foreach($docserver->getArray() as $key => $value)
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
	
	public function disable($docserver_id)
	{
		if(!isset($docserver_id)|| empty($docserver_id))
			return false;
		if(! self::docserverExists($docserver_id))
			return false;
			
		self::connect();
		$query="update ".self::$docservers_table." set enabled = 'N' where docserver_id='".$docserver_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DISABLE_DOCSERVER." ".$docserver_id.' // ';
			$ok = false;
		}
		if($_SESSION['history']['docserversban'] == "true")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename']['docservers'], $docserver_id, "BAN", _DOCSERVER_DISABLED." : ".$docserver_id, $_SESSION['config']['databasetype']);
		}
		self::disconnect();
		return $ok;
	}
	
	public function enable($docserver_id)
	{
		if(!isset($docserver_id)|| empty($docserver_id))
			return false;
		if(! self::docserverExists($docserver_id))
			return false;
			
		self::connect();
		$query="update ".self::$docservers_table." set enabled = 'Y' where docserver_id='".$docserver_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_ENABLE_DOCSERVER." ".$docserver_id.' // ';
			$ok = false;
		}
		if($_SESSION['history']['docserversban'] == "true")
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add($_SESSION['tablename']['docservers'], $docserver_id, "VAL",_DOCSERVER_ENABLED." : ".$docserver_id, $_SESSION['config']['databasetype']);
		}
		self::disconnect();
		return $ok;
	}
	
	public function docserverExists($docserver_id)
	{
		if(!isset($docserver_id) || empty($docserver_id))
			return false;

		self::connect();
		$query = "select docserver_id from ".self::$docservers_table." where docserver_id = '".$docserver_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$docserver_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}
	
	public function noResources($docserver_id)
	{
		if(!isset($docserver_id) || empty($docserver_id))
			return false;

		self::connect();
		$query = "select docserver_id, coll_id from ".self::$docservers_table." where docserver_id = '".$docserver_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$docserver_id.' // ';
		}
		$collIdValues = self::$db->fetch_object();
		if(empty($collIdValues->coll_id))
		{
			return false;
		}
		else
		{
			for($cptCollection=0;$cptCollection<count($_SESSION['collections']);$cptCollection++)
			{
				if($_SESSION['collections'][$cptCollection]['id'] == $collIdValues->coll_id)
				{
					$resTable = $_SESSION['collections'][$cptCollection]['table'];
				}
			}
		}
		$query = "select res_id from ".$resTable." where docserver_id = '".$docserver_id."'";
			
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _ERROR." ".$docserver_id.' // ';
		}
		if(self::$db->nb_result() == 0)
		{
			self::disconnect();
			return true;
		}
		$_SESSION['error'] = _CANNOT_DEL_DOCSERVER;
		self::disconnect();
		return false;
	}
}
?>
