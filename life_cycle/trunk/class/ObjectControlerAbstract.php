<?php

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Implementing few generic features for controlers of type
 * "all-the-properties-of-the-object-are-the-columns-of-the-
 * database-table", i.e. TableObject-kind.
 * 
 * @author boulio
 *
 */
abstract class ObjectControler {
	static protected $db;
	static protected $computed_properties=array(
							);
	static protected $foolish_ids=array("docservers_id", "docserver_types_id", "docserver_locations_id", "coll_id","lc_policies_id");
									
	/**
	 * Insert given object in given table.
	 * Return inserted object if succeeded.
	 * @param unknown_type $object
	 * @return unknown_type 
	 */
	protected function advanced_insert($object){
		$table_name=get_class($object);
		if(!isset($object) )
			return false;
			
		// Inserting object
		$preparation=self::insert_prepare($object, self::$computed_properties);
		$query="insert into $table_name ("
					.$preparation['properties']
					.") values("
					.$preparation['values']
					.")";
		self::$db=new dbquery();
		self::$db->connect();
		try{
			if(_DEBUG){echo "insert: $query // ";}
			self::$db->query($query);
			$result=true;
		} catch (Exception $e){
			echo "Impossible to insert object ".$object->toString().' // ';
			$result=false;
		}
		self::$db->disconnect();
		return $result;
	}

	/**
	 * Prepare two strings for insert query :
	 * - 'properties' for properties field of insert query,
	 * - 'values' for values field of insert query.
	 * Needs list of values to _exclude_ of insert query (i.e. 
	 * usually values computed in the get() function of controler).
	 * Result in an array.
	 * @param Any $object
	 * @param string[] $computed_properties
	 * @return string[]
	 */
	private function insert_prepare($object, $computed_properties){
		$result=array();
		$properties=array();
		$values=array();
		foreach($object->getArray() as $key => $value){
			if(!in_array($key,$computed_properties)){
				// Adding property
				$properties[]=$key;
				// Adding property value
				if(substr_compare($key, "_id", -3)==0 || substr_compare($key, "_number", -7)==0){
					if(in_array($key, self::$foolish_ids)){
						/*
						 * UNBELIEVABLE! THERE ARE IDS WHICH ARE NOT LONG INTEGERS!
						 * A choice needs to be done, and if string is kept, random
						 * generating must be implemented.
						 */
						$values[]="'".$value."'";
					} else {
						// Number
						if(empty($value)){
							// Default value
							$value=0;
						}
						$values[]=$value;
					}
				} elseif(substr_compare($key, "is_", 0, 3)==0 || substr_compare($key, "can_", 0, 4)==0){
					// Boolean
					if($value===true){
						$values[]="'true'";
					} elseif($value===false) {
						$values[]="'false'";
					}
				} else {
					// Character or date
					$values[]="'".$value."'";
				}
			}
							
		}
		$result['properties']=implode(",",$properties);
		$result['values']=implode(",",$values);
		return $result;
	}
	
	/**
	 * Update given object in given table, according
	 * with given table id name.
	 * Return updated object if succeeded.
	 * @param unknown_type $object
	 * @return unknown_type
	 */
	protected function advanced_update($object){
		if(!isset($object) )
			return false;
		
		$table_name=get_class($object);
		$table_id=$table_name."_id";
		
		if(in_array($table_id, self::$foolish_ids)){
			$query="update $table_name set "
						.self::update_prepare($object, self::$computed_properties)
						." where $table_id='".$object->$table_id."'"; 
		} else {
			$query="update $table_name set "
						.self::update_prepare($object, self::$computed_properties)
						." where $table_id=".$object->$table_id;
		}
		self::$db=new dbquery();
		self::$db->connect();
		try{
			if(_DEBUG){echo "update: $query // ";}
			self::$db->query($query);
			$result=true;
		} catch (Exception $e){
			echo "Impossible to update object ".$object->toString().' // ';
			$result=false;
		}
		self::$db->disconnect();
		return $result;
	}
	
	/**
	 * Prepare string for update query
	 * @param Any $object
	 * @param string[] $computed_properties
	 * @return String
	 */
	private function update_prepare($object, $computed_properties){
		$result=array();
		foreach($object->getArray() as $key => $value){
			if(!in_array($key,$computed_properties)){
				if(substr_compare($key, "_id", -3)==0 || substr_compare($key, "_number", -7)==0){
					if(in_array($key, self::$foolish_ids)){
						$result[]=$key."='".$value."'";
					} else {
						// Number
						if(empty($value)){
							// Default value
							$value=0;
						}
						$result[]=$key."=".$value;
					}
				} elseif(substr_compare($key, "is_", 0, 3)==0 || substr_compare($key, "can_", 0, 4)==0){
					// Boolean
					if($value===true){
						$result[]=$key."=true";
					} elseif($value===false) {
						$result[]=$key."=false";
					}
				} else {
					// Character or date
					$result[]=$key."='".$value."'";
				}
			}
		}
		// Return created string minus last ", "
		return implode(",",$result);
	}

	/**
	 * Get object of given class with given id from 
	 * good table and according with given class name.
	 * Can return null if no corresponding object.
	 * @param long $id Id of object to get
	 * @param string $class_name
	 * @return unknown_type 
	 */
	protected function advanced_get($id,$table_name) {
		if(strlen($id)==0){
			return null;
		}
		$table_id=$table_name."_id";
		self::$db=new dbquery();
		self::$db->connect();
		if(in_array($table_id, self::$foolish_ids)){
			 $select="select * from $table_name where $table_id='$id'";
		} else{
			$select="select * from $table_name where $table_id=$id";
		}
		
		try {
			if(_DEBUG){echo "get: $select // ";}
			self::$db->query($select);
			if(self::$db->nb_result()==0){
				return null;
			} else {
				// Constructing result
				$object=new $table_name();
				$queryResult=self::$db->fetch_object();
				foreach((array)$queryResult as $key => $value){
					if(_ADVANCED_DEBUG){
						echo "Getting property: $key with value: $value // ";
					}
					if($value=='t') {			/* BUG FROM PGSQL DRIVER! */
						$value=true;  			/*						  */
					} elseif($value=='f') {		/*						  */
						$value=false; 			/*						  */
					}							/**************************/
					$object->$key=$value;
				}
			}
		} catch (Exception $e) {
			echo "Impossible to get object $id // ";
		}
		
		self::$db->disconnect();
		return $object;
	}
	
		/**
	 * Delete given object from given table, according with
	 * given table id name.
	 * Return true if succeeded.
	 * @param Any $object
	 * @return boolean
	 */
	protected function advanced_delete($object){
		if(!isset($object))
			return false;
			
		$table_name=get_class($object);
		$table_id=$table_name."_id";
		
		self::$db=new dbquery();
		self::$db->connect();
		if(in_array($table_id, self::$foolish_ids)){
			 $query="delete from $table_name where $table_id='".$object->$table_id."'";
		} else{
			$query="delete from $table_name where $table_id=".$object->$table_id;
		}
		
		try{
			if(_DEBUG){echo "delete: $query // ";}
			self::$db->query($query);
			$result=true;
		} catch (Exception $e){
			echo "Impossible to delete object with id=".$object->$table_id." // ";
			$result=false;
		}
		self::$db->disconnect();
		return $result;
	}

	/**
	 * Enable given object from given table, according with
	 * given table id name.
	 * Return true if succeeded.
	 * @param Any $object
	 * @return boolean
	 */
	protected function advanced_enable($object){
		if(!isset($object))
			return false;
			
		$table_name=get_class($object);
		$table_id=$table_name."_id";
		
		self::$db=new dbquery();
		self::$db->connect();
		if(in_array($table_id, self::$foolish_ids)){
			 $query="update $table_name set enabled = 'Y' where $table_id='".$object->$table_id."'";
		} else{
			$query="update $table_name set enabled = 'Y' where $table_id=".$object->$table_id;
		}
		try{
			if(_DEBUG){echo "enable: $query // ";}
			self::$db->query($query);
			$result=true;
		} catch (Exception $e){
			echo "Impossible to enable object with id=".$object->$table_id." // ";
			$result=false;
		}
		self::$db->disconnect();
		return $result;
	}

	/**
	 * Disable given object from given table, according with
	 * given table id name.
	 * Return true if succeeded.
	 * @param Any $object
	 * @return boolean
	 */
	protected function advanced_disable($object){
		if(!isset($object))
			return false;
			
		$table_name=get_class($object);
		$table_id=$table_name."_id";
		
		self::$db=new dbquery();
		self::$db->connect();
		if(in_array($table_id, self::$foolish_ids)){
			 $query="update $table_name set enabled = 'N' where $table_id='".$object->$table_id."'";
		} else{
			$query="update $table_name set enabled = 'N' where $table_id=".$object->$table_id;
		}
		try{
			if(_DEBUG){echo "disable: $query // ";}
			self::$db->query($query);
			$result=true;
		} catch (Exception $e){
			echo "Impossible to disable object with id=".$object->$table_id." // ";
			$result=false;
		}
		self::$db->disconnect();
		return $result;
	}

}