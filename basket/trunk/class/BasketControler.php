<?php

$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/class_db.php");
	require_once("modules/basket/class/Basket.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class BasketControler{
	
	private static $db;
	public static $baskets_table;
	public static $groupbasket_table;
	public static $groupbasket_redirect_table;
	public static $actions_groupbaskets_table;
	
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$baskets_table = $_SESSION['tablename']['bask_baskets'];
		self::$groupbasket_table = $_SESSION['tablename']['bask_groupbasket'];
		self::$actions_groupbaskets_table = $_SESSION['tablename']['bask_actions_groupbaskets'];
		self::$groupbasket_redirect_table = $_SESSION['tablename']['ent_groupbasket_redirect'];
			
		self::$db=$db;
	}	
	
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	public function get($basket_id, $can_be_disabled = false)
	{
		if(!isset($basket_id) || empty($basket_id))
			return null;

		self::connect();
		// Querying database
		$query = "select * from ".self::$baskets_table." where basket_id = '".$basket_id."'";
		if(!$can_be_disabled)
		{
			$query .= " and enabled = 'Y'";
		}
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_BASKET_WITH_ID.' '.$basket_id.' // ';
		}
		if(self::$db->nb_result() > 0)
		{	
			$basket=new Basket();
			$queryResult=self::$db->fetch_object();
			foreach($queryResult as $key => $value){
				$basket->$key=$value;
			}
			self::disconnect();
			return $basket;
		}
		else
		{
			self::disconnect();
			return null;
		}
	}
	
	
	public function save($basket, $mode)
	{
		if(!isset($basket) )
			return false;

		if($mode == "up")
			return self::update($basket);
		elseif($mode =="add") 
			return self::insert($basket);
		
		return false;
	}
	
	private function insert($basket)
	{
		if(!isset($basket) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($basket);
		
		// Inserting object
		$query="insert into ".self::$baskets_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if($_ENV['DEBUG']){ echo $query.' // '; }
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_INSERT_BASKET." ".$basket->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}

	private function update($basket)
	{
		if(!isset($basket) )
			return false;
			
		self::connect();
		$query="update ".self::$baskets_table." set "
					.self::update_prepare($basket)
					." where basket_id='".$basket->basket_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_UPDATE_BASKET." ".$basket->toString().' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function delete($basket_id)  
	{
		if(!isset($basket_id)|| empty($basket_id) )
			return false;
		if(! self::basketExists($basket_id))
			return false;
			
		self::connect();
		$query="delete from ".self::$baskets_table." where basket_id='".$basket_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE_BASKET_ID." ".$basket_id.' // ';
			$ok = false;
		}
		
		if($ok)
			$ok = self::cleanFullGroupbasket($basket_id);
		
		self::disconnect();
		return $ok;
	}
	
	public function cleanFullGroupbasket($id , $field = 'basket_id' )
	{
		if(!isset($id)|| empty($id) || !isset($field) || empty($field) )
			return false;
			
		$ok = self::cleanGroupbasket($id, $field);
		
		if($ok)
			$ok = self::cleanActionsGroupbasket($id, $field);
		
		return $ok;
	}
	
	public function cleanGroupbasket($id, $field)
	{
		if(!isset($id)|| empty($id) || !isset($field) || empty($field) )
			return false;
		
		self::connect();
		$query="delete from ".self::$groupbasket_table." where ".$field."='".$id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE.' '.$field.' '.$id.' // ';
			$ok = false;
		}
		
		self::disconnect();
		return $ok;
	}
	
	public function cleanActionsGroupbasket($id, $field)
	{
		if(!isset($id)|| empty($id) || !isset($field) || empty($field) )
			return false;
		
		self::connect();
		$query="delete from ".self::$actions_groupbaskets_table." where ".$field."='".$basket_id."'";
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE.' '.$field.' '.$id.' // ';
			$ok = false;
		}
		
		self::disconnect();
		return $ok;
	}
	
	private function update_prepare($basket)
	{
		$prep_query = array('COLUMNS' => '', 'VALUES'	=> '');
		
		$result=array();
		foreach($basket->getArray() as $key => $value)
		{
			// For now all fields in the baskets table are strings
			if(!empty($value))
			{
				$result[]=$key."='".$value."'";		
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	} 
	

	private function insert_prepare($basket){
		$columns=array();
		$values=array();
		foreach($basket->getArray() as $key => $value)
		{
			//For now all fields in the baskets table are strings or dates
			if(!empty($value))
			{
				$columns[]=$key;
				$values[]="'".$value."'";
			}
		}
		return array('COLUMNS' => implode(",",$columns), 'VALUES' => implode(",",$values));
	}
	
	public function disable($basket_id)
	{
		if(!isset($basket_id)|| empty($basket_id) )
			return false;
		if(! self::basketExists($basket_id))
			return false;
			
		self::connect();
		$query="update ".self::$baskets_table." set enabled = 'N' where basket_id='".$basket_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DISABLE_BASKET." ".$basket_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function enable($basket_id)
	{
		if(!isset($basket_id)|| empty($basket_id) )
			return false;
		if(! self::basketExists($basket_id))
			return false;
			
		self::connect();
		$query="update ".self::$baskets_table." set enabled = 'Y' where basket_id='".$basket_id."'"; 
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_ENABLE_BASKET." ".basket_id.' // ';
			$ok = false;
		}
		self::disconnect();
		return $ok;
	}
	
	public function basketExists($basket_id)
	{
		if(!isset($basket_id) || empty($basket_id))
			return false;

		self::connect();
		$query = "select basket from ".self::$baskets_table." where basket_id = '".$basket_id."'";
					
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN.' '._BASKET." ".$basket_id.' // ';
		}
		
		if(self::$db->nb_result() > 0)
		{
			self::disconnect();
			return true;
		}
		self::disconnect();
		return false;
	}
}
?>
