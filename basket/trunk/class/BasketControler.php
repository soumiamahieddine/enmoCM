<?php

define ("_DEBUG", false);
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
	
	public function get($basket_id)
	{
		if(empty($basket_id))
		{
			// Nothing to get
			return null;
		} 
		// Querying database
		$query = "select * from ".self::$baskets_table." where basket_id = '".$basket_id."' and enabled = 'Y'";
		try{
			if(_DEBUG){echo $select.' // ';}
			self::$db->query($select);
		} catch (Exception $e){
			echo _NO_BASKET_WITH_ID.' '.$basket_id.' // ';
		}
		// Constructing object
		$basket=new Basket();
		$queryResult=self::$db->fetch_object();
		foreach($queryResult as $key => $value){
			$basket->$key=$value;
		}
		return $basket;
	}
	
	
	public function save($basket)
	{
		if($basket->basket_id > 0){
			// Update existing basket
			self::update($basket);
		} else {
			// Insert new basket
			self::insert($basket);
		}
	}
	
	private function insert($basket)
	{
		$prep_query = self::insert_prepare($basket);
		
		// Inserting object
		$query="insert into ".self::$baskets_table." ("
					.$prep_query['COLUMNS']
					.") values("
					.$prep_query['VALUES']
					.")";
		try{
			if(_DEBUG){ echo $query.' // '; }
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_INSERT_BASKET." ".$basket->toString().' // ';
		}
	}

	private function update($basket)
	{
		$query="update ".self::$baskets_table." "
					.self::update_prepare($basket)
					." where basket_id='".$basket->basket_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_UPDATE_BASKET." ".$basket->toString().' // ';
		}
	}
	
	public function delete($basket_id){
		$query="delete from ".self::$baskets_table." where basket_id='".$basket_id."'";
		try{
			if(DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_DELETE_BASKET_ID." ".$basket_id.' // ';
		}
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
		$query="update ".self::$baskets_table." set enabled = 'N' where basket_id='".$basket_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_DISABLE_BASKET." ".$basket_id.' // ';
		}
	}
	
	public function enable($basket_id)
	{
		$query="update ".self::$baskets_table." set enabled = 'Y' where basket_id='".$basket_id."'"; 
					
		try{
			if(_DEBUG){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _CANNOT_ENABLE_BASKET." ".basket_id.' // ';
		}
	}
}
?>
