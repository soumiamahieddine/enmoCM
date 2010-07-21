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
	require_once("modules/entities/class/Entity.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Entity Object 
*
*<ul>
*  <li>Get an entity object from an id</li>
*  <li>Save in the database an entity</li>
*  <li>Manage the operation on the entities related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class EntityControler
{
	/**
	* Dbquery object used to connnect to the database
    */
	private static $db;
	
	/**
	* Entities table
    */
	public static $entities_table ;
	
	/**
	* Users_entities table
    */
	public static $users_entities_table ;
	
	/**
	* Groupbasket_redirect table
    */
	public static $groupbasket_redirect_table ;
	
	/**
	* Opens a database connexion and values the tables variables
	*/
	public function connect()
	{
		$db = new dbquery();
		$db->connect();
		self::$entities_table = $_SESSION['tablename']['ent_entities'];
		self::$users_entities_table = $_SESSION['tablename']['ent_users_entities'];
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
	* Returns an Entity Object based on a entity identifier
	*
	* @param  $entity_id string Entity identifier
	* @param  $can_be_disabled bool  if true gets the basket even if it is disabled in the database (false by default)
	* @return User object with properties from the database or null
	*/
	public function get($entity_id, $can_be_disabled = false)
	{
		if(empty($entity_id))
			return null;

		self::connect();

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
	
	/**
	* Returns all entities (enabled by default) from the database in an array of Entity Objects (ordered by group_desc by default)
	*
	* @param  $order_str string  Order string passed to the query ("order by short_label asc" by default)
	* @param  $enabled_only bool  if true returns only the enabled entities, otherwise returns even the disabled (true by default)
	* @return Array of Entity objects with properties from the database
	*/
	public function getAllEntities($order_str = "order by short_label asc", $enabled_only = true)
	{
		self::connect();
		$query = "select * from ".self::$entities_table." ";
		if($enabled_only)
			$query .= "where enabled = 'Y'";
		
		$query.= $order_str;
		
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
		} catch (Exception $e){}
		
		$entities = array();
		while($res = self::$db->fetch_object())
		{
			$ent=new EntityObj();
			foreach($res as $key => $value)
				$tmp_array[$key] = $value;
			
			$ent->setArray($tmp_array);
			array_push($entities, $ent);
		}
		self::disconnect();
		return $entities;
	}
	
	/**
	* Returns in an array all the members of an entity (user_id only) 
	*
	* @param  $user_id string  User identifier
	* @return Array (user_id, user_role, primary_entity) or null
	*/
	public function getUsersEntities($user_id)
	{
		if(empty($user_id))
			return null;
			
		self::connect();
		$query = "select entity_id, user_role, primary_entity from ".self::$users_entities_table." where user_id = '".functions::protect_string_db($user_id)."'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _NO_USER_WITH_ID.' '.$user_id.' // ';
		}
		$entities = array();
		while($res=self::$db->fetch_object())
		{
			array_push($entities, array('USER_ID' => $user_id, 'ENTITY_ID' => $res->entity_id, 'PRIMARY' => $res->primary_entity, 'ROLE' => $res->user_role));
		}
		self::disconnect();
		return $entities;
	}
	
	/**
	* Saves in the database an entity object 
	*
	* @param  $entity Entity object to be saved
	* @param  $mode string  Saving mode : add or up
	* @return bool true if the save is complete, false otherwise
	*/
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
	
	/**
	* Inserts in the database (entities table) an Entity object
	*
	* @param  $entity Entity object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($entity)
	{
		if(!isset($entity) )
			return false;
			
		self::connect();
		$prep_query = self::insert_prepare($entity);

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

	/**
	* Updates an entity in the database (entities table) with an Entity object
	*
	* @param  $entity Entity object
	* @return bool true if the update is complete, false otherwise
	*/
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
	
	/**
	* Deletes in the database (entities related tables) a given entity (entity_id)
	*
	* @param  $entity_id string  Entity identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
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
	
	/**
	* Cleans the users_entities table in the database from a given field (entity_id by default)
	*
	* @param  $id string  object identifier
	* @param  $field string  Field name (entity_id by default)
	* @return bool true if the cleaning is complete, false otherwise
	*/
	public function cleanUsersentities($id, $field = 'entity_id')
	{
		if(!isset($id)|| empty($id) )
			return false;
		
		self::connect();
		$query="delete from ".self::$users_entities_table." where ".$field."='".$id."'";
		
		try{
			if($_ENV['DEBUG'])
				echo $query.' // ';
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e){
			echo _CANNOT_DELETE.' '.$field." ".$id.' // ';
			$ok = false;
		}

		self::disconnect();
		return $ok;
	}
	
	/**
	* Cleans the groupbasket_redirect table in the database from a given field (entity_id by default)
	*
	* @param  $id string  object identifier
	* @param  $field string  Field name (entity_id by default)
	* @return bool true if the cleaning is complete, false otherwise
	*/
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
	
	/**
	* Asserts if a given entity (entity_id) exists in the database
	* 
	* @param  $entity_id String Entity identifier
	* @return bool true if the basket exists, false otherwise 
	*/
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
	
	/**
	* Prepares the update query for a given Entity object
	*
	* @param  $entity Entity object
	* @return String containing the fields and the values 
	*/
	private function update_prepare($entity)
	{
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
	* Prepares the insert query for a given Entity object
	*
	* @param  $entity Entity object
	* @return Array containing the fields and the values 
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
	
	/**
	* Disables a given entity
	* 
	* @param  $entity_id String Entity identifier
	* @return bool true if the disabling is complete, false otherwise 
	*/
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
	
	/**
	* Enables a given entity
	* 
	* @param  $entity_id String Entity identifier
	* @return bool true if the enabling is complete, false otherwise 
	*/
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
	
	/**
	* Returns the number of entities of the entities table (only the enabled by default)
	* 
	* @param  $enabled_only Bool if true counts only the enabled ones, otherwise counts all entities even the disabled ones (true by default)
	* @return Integer the number of entities in the entities table
	*/
	public function getEntitiesCount($enabled_only = true)
	{
		$nb = 0;
		self::connect();
		
		$query = "select entity_id  from ".self::$entities_table." " ;
		if($enabled_only)
			$query .= "where enabled ='Y'";
		
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){}
		
		$nb = self::$db->nb_result();			
		self::disconnect();
		return $nb;
	}
	
	/**
	* Loads into the users_entities table the given data for a given user
	* 
	* @param  $user_id String User identifier
	* @param  $array Array 
	* @return bool true if the loadng is complete, false otherwise 
	*/
	public function loadDbUsersentities($user_id, $array)
	{
		if(!isset($user_id)|| empty($user_id) )
			return false;
		if(!isset($array) || count($array) == 0)
			return false;
			
		self::connect();
		$ok = true;
		for($i=0; $i < count($array ); $i++)
		{
			if($ok) 
			{
				$query = "INSERT INTO ".self::$users_entities_table." (user_id, entity_id, primary_entity, user_role) VALUES ('".functions::protect_string_db($user_id)."', '".functions::protect_string_db($array[$i]['ENTITY_ID'])."', '".functions::protect_string_db($array[$i]['PRIMARY'])."', '".functions::protect_string_db($array[0]['ROLE'])."')";
				try{
					if($_ENV['DEBUG'])
						echo $query.' // ';
					self::$db->query($query);
					$ok = true;
				} catch (Exception $e){
					$ok = false;
				}
			}
			else
				break;
		}
		self::disconnect();
		return $ok;		
	}
}
?>
