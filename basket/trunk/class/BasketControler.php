<?php
/*
*    Copyright 2008,2009,2010 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the controler of the Basket Object (create, save, modify, etc...)
* 
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
	require_once("core/class/class_db.php");
	require_once("modules/basket/class/Basket.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Basket Object 
*
*<ul>
*  <li>Get an basket object from an id</li>
*  <li>Save in the database a basket</li>
*  <li>Manage the operation on the baskets related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class BasketControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	private static $db;
	
	/**
	* Baskets table
    */
	public static $baskets_table;
	
	/**
	* Groupbasket table
    */
	public static $groupbasket_table;
	
	/**
	* Groupbasket_redirect table
    */
	public static $groupbasket_redirect_table;
	
	/**
	* Actions_groupbasket table
    */
	public static $actions_groupbaskets_table;
	
	/**
	* Opens a database connexion and values the tables variables
	*/
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
	
	/**
	* Close the database connexion
	*/
	public function disconnect()
	{
		self::$db->disconnect();
	}	
	
	/**
	* Returns a Basket Object based on a basket identifier
	*
	* @param  $basket_id string Basket identifier
	* @param  $can_be_disabled bool  if true gets the basket even if it is disabled in the database (false by default)
	* @return User object with properties from the database or null
	*/
	public function get($basket_id, $can_be_disabled = false)
	{
		if(!isset($basket_id) || empty($basket_id))
			return null;

		self::connect();

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
	
	/**
	* Saves in the database a basket object 
	*
	* @param  $basket Basket object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
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
	
	/**
	* Inserts in the database (baskets table) a Basket object
	*
	* @param  $basket Basket object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($basket)
	{
		if(!isset($basket) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($basket);

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

	/**
	* Updates a basket in the database (baskets table) with a Basket object
	*
	* @param  $basket Basket object
	* @return bool true if the update is complete, false otherwise
	*/
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
	
	/**
	* Deletes in the database (baskets related tables) a given basket (basket_id)
	*
	* @param  $basket_id string  Basket identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
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
	
	/**
	* Cleans the groupbasket and actions_groupbasket tables in the database from a given field (basket_id by default)
	*
	* @param  $id string  object identifier
	* @param  $field string  Field name (basket_id by default)
	* @return bool true if the cleaning is complete, false otherwise
	*/
	public function cleanFullGroupbasket($id , $field = 'basket_id' )
	{
		if(!isset($id)|| empty($id) || !isset($field) || empty($field) )
			return false;
			
		$ok = self::cleanGroupbasket($id, $field);
		
		if($ok)
			$ok = self::cleanActionsGroupbasket($id, $field);
		
		return $ok;
	}
	
	
	/**
	* Cleans the groupbasket table in the database from a given field 
	*
	* @param  $id string  object identifier
	* @param  $field string  Field name 
	* @return bool true if the cleaning is complete, false otherwise
	*/
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
	
	/**
	* Cleans the actions_groupbasket table in the database from a given field 
	*
	* @param  $id string  object identifier
	* @param  $field string  Field name 
	* @return bool true if the cleaning is complete, false otherwise
	*/
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
	
	/**
	* Prepares the update query for a given Basket object
	*
	* @param  $basket Basket object
	* @return String containing the fields and the values 
	*/
	private function update_prepare($basket)
	{
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
	
	/**
	* Prepares the insert query for a given Basket object
	*
	* @param  $basket Basket object
	* @return Array containing the fields and the values 
	*/
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
	
	/**
	* Disables a given basket
	* 
	* @param  $basket_id String Basket identifier
	* @return bool true if the disabling is complete, false otherwise 
	*/
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
	
	/**
	* Enables a given basket
	* 
	* @param  $basket_id String Basket identifier
	* @return bool true if the enabling is complete, false otherwise 
	*/
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
	
	/**
	* Asserts if a given basket (basket_id) exists in the database
	* 
	* @param  $basket_id String Basket identifier
	* @return bool true if the basket exists, false otherwise 
	*/
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
