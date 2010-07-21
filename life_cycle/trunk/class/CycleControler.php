<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
	require_once("modules".DIRECTORY_SEPARATOR."life_cycle".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."Cycle.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class CycleControler
{
	private static $db;
	private static $cycle_table;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$cycle_table = $_SESSION['tablename']['lc_cycle'];
		
		self::$db=$db;
	}
	
	public function disconnect()
	{
		self::$db->disconnect();
	}
	
	public function get($cycle_id)
	{
		if(empty($cycle_id))
			return null;
		self::connect();
		$query = "select * from ".self::$cycle_table." where cycle_id = '".$cycle_id."'";
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_CYCLE_WITH_ID.' '.$cycle_id.' // ';
		}
		if(self::$db->nb_result() > 0)
		{
			// Constructing object
			$cycle = new Cycle();
			$queryResult = self::$db->fetch_object();
			if($queryResult)
			{
				foreach($queryResult as $key => $value)
				{
					$cycle->$key=$value;
				}
			}
			self::disconnect();
			return $cycle;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	public function save($cycle, $mode)
	{
		if(!isset($cycle))
			return false;
			
		if($mode == "up")
			return self::update($cycle);
		elseif($mode =="add") 
			return self::insert($cycle);
		
		return false;
	}

	private function insert($cycle)
	{
		if(!isset($cycle))
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($cycle);
		
		// Inserting object
		$query="insert into ".self::$cycle_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_CYCLE." ".$cycle->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update($cycle)
	{
		if(!isset($cycle))
			return false;

		self::connect();
		$query="update ".self::$cycle_table." set "
					.self::update_prepare($cycle)
					." where cycle_id='".$cycle->cycle_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_CYCLE." ".$cycle->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function delete($cycle_id)
	{
		if(!isset($cycle_id) || empty($cycle_id))
			return false;
		if(!self::cycleExists($cycle_id))
			return false;
		//if(!self::noResources($cycle_id))
		//	return false;
		self::connect();
		$query="delete from ".self::$cycle_table." where cycle_id='".$cycle_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
			if($_SESSION['history']['lcdel'] == "true")
			{
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$history = new history();
				$history->add($_SESSION['tablename']['lc_cycle'], $cycle_id, "DEL", _CYCLE_DELETED." : ".$cycle_id, $_SESSION['config']['databasetype']);
			}
		} catch (Exception $e){
			echo _CANNOT_DELETE_CYCLE_ID." ".$cycle_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	private function update_prepare($cycle)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($cycle->getArray() as $key => $value)
		{
			// For now all fields in the cycle table are strings
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
	 * @param cycle $cycle
	 * @return String
	 */
	private function insert_prepare($cycle)
	{
		$columns=array();
		$values=array();
		foreach($cycle->getArray() as $key => $value)
		{
			// For now all fields in the cycle table are strings
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".$value."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	public function cycleExists($cycle_id)
	{
		if(!isset($cycle_id) || empty($cycle_id))
			return false;

		self::connect();
		$query = "select cycle_id from ".self::$cycle_table." where cycle_id = '".$cycle_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._CYCLE." ".$cycle_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}
	
	public function noResources($cycle_id)
	{
		if(!isset($cycle_id) || empty($cycle_id))
			return false;

		self::connect();
		$query = "select cycle_id, coll_id from ".self::$cycle_table." where cycle_id = '".$cycle_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._CYCLE." ".$cycle_id.' // ';
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
		$query = "select res_id from ".$resTable." where cycle_id = '".$cycle_id."'";
			
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _ERROR." ".$cycle_id.' // ';
		}
		if(self::$db->nb_result() == 0)
		{
			self::disconnect();
			return true;
		}
		$_SESSION['error'] = _CANNOT_DEL_CYCLE;
		self::disconnect();
		return false;
	}
}
?>
