<?php

try {
	require_once("modules/life_cycle/class/ObjectControlerAbstract.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

if(!defined("_CODE_SEPARATOR"))define("_CODE_SEPARATOR","/");
if(!defined("_CODE_INCREMENT"))define("_CODE_INCREMENT",1);
if(!defined("_RELATIVE_CODE_NAME")) define("_RELATIVE_CODE_NAME","mr_classification_code");
if(!defined("_FULL_CODE_NAME")) define("_FULL_CODE_NAME","mr_full_classification_code");

/**
 * Class to manage classification codes and children. 
 * @author boulio
 *
 */
abstract class ClassifiedObjectControler extends ObjectControler {
	/**
	 * Give direct children of given object
	 * @param unknown_type $object
	 * @param String $orderBy
	 */
	protected function advanced_getChildren($object,$orderBy){
		// Checking object
		if(!isset($object) )
			return null;
			
		$tableName=get_class($object);
		$objectIdName=$tableName."_id";
		// Checking id value
		if(empty($object->$objectIdName))
			return null;
			
		$parentIdName=self::getParentIdName($tableName);
								
		$result=array();
		
		// Checking order by clause
		if(empty($orderBy)){
			$orderBy=$objectIdName;
		}
		
		self::$db=new dbquery();
		self::$db->connect();
		
		// Querying database
		$select="select $objectIdName from $tableName where $parentIdName=".$object->$objectIdName." order by $orderBy";
		try {
			if(_DEBUG){echo "getChildren: $select // ";}
			self::$db->query($select);
			// Constructing children id table
			while($queryLine=self::$db->fetch_object()){
				$queryResult[]=$queryLine;
			}
			self::$db->disconnect();
			// Constructing result table
			foreach((array)$queryResult as $childId){
				$classif=self::advanced_get($childId->$objectIdName,$tableName);
				$result[]=$classif;
			}
		} catch (Exception $e) {
			echo "Impossible to get children of $objectIdName in table $tableName // ";
		}
		
		return $result;
	}
	
	/**
	 * Give next possible fully qualified classification
	 * code according with given children.
	 * @param $classifiedObject
	 * @param $children
	 */
	protected function new_full_classification_code($classifiedObject, $children){
		// Searching max relative code amongst children
		$max_code=0;
		$parent_full_code=0;
		$table_name=get_class($classifiedObject);
		$relativeCodeName=_RELATIVE_CODE_NAME;
		$fullCodeName=_FULL_CODE_NAME;
		$parentIdName=self::getParentIdName($table_name);
		
		/* 
		 * It could be possible to improve following implementation with
		 * $children ordered by $relativeCodeName directly in previous
		 * sql query.
		 */
		$parent_full_code = self::get_parent_code($classifiedObject);
		if(!empty($children)){
			foreach($children as $child){
				if($child->$relativeCodeName > $max_code) $max_code = $child->$relativeCodeName;
			}
		}
		$classifiedObject->$relativeCodeName=self::format_classification_code($max_code + _CODE_INCREMENT);
		return $parent_full_code._CODE_SEPARATOR.$classifiedObject->$relativeCodeName; 
	}

	/**
	 * Get full code of parent
	 * @param $classifiedObject
	 */
	private function get_parent_code($classifiedObject){
		$fullCodeName=_FULL_CODE_NAME;
		$table_name=get_class($classifiedObject);
		$parentIdName=self::getParentIdName($table_name);
		
		self::$db=new dbquery();
		self::$db->connect();
		// Querying database
		$select="select $fullCodeName from "._AGGREGATION_TABLE_NAME." where mr_aggregation_id=".$classifiedObject->$parentIdName;
		try {
			if(_DEBUG){echo "getChildren: $select // ";}
			self::$db->query($select);
			if(self::$db->nb_result()>0){
				// Constructing children id table
				$queryResult=self::$db->fetch_object();
				$result=$queryResult->$fullCodeName;
			} else {
				// Root aggregation
				$select="select mr_full_classification_code from "._CLASSIFICATION_SCHEME_TABLE_NAME." where mr_classification_scheme_id=".$classifiedObject->$parentIdName;
				if(_DEBUG){echo "getChildren: $select // ";}
				self::$db->query($select);
				$queryResult=self::$db->fetch_object();
				$result=$queryResult->cs_code;
			}
		} catch (Exception $e) {
			echo "Impossible to get children of $objectIdName in table $tableName // ";
		}
		self::$db->disconnect();
		return $result; 
		
	}
	
	/**
	 * Format given code according to organization rules
	 * @param $code
	 */
	protected function format_classification_code($code){
		switch(strlen($code)){
			case 1:
				return "00".$code; break;
			case 2:
				return "0".$code; break;
			default:
				return $code; 
		}
	}
	
	/**
	 * Give level of given full classification code
	 * using number of _CODE_SEPARATOR occurrences.
	 * @param $full_classification_code
	 * @return Integer
	 */
	protected function get_level($full_classification_code){
		if(_ADVANCED_DEBUG){
			echo "Computing level with full code: $full_classification_code // ";
		}
		return substr_count($full_classification_code,_CODE_SEPARATOR);
	}
	
	/**
	 * Return parent id name with checking of table type
	 * @param string $table_name
	 * @return string
	 */
	private function getParentIdName($table_name){
		if(substr($table_name,0,3)=="mr_")
			// Single-object table
			$result="parent_".$table_name."_id";
		else {
			/*
			 * Current table is relation table between several tables, 
			 * its first subword indicate its role.
			 * Got to search parent id name.
			 */ 
			$result=self::getRelationParentIdName($table_name);
			
		}
		return $result;
	}
	
	/**
	 * Return parent column name of given table
	 * if exists, "parent_$table_name_id" otherwise
	 * @param string $table_name
	 * @return string
	 */
	private function getRelationParentIdName($table_name){
		self::$db=new dbquery();
		self::$db->connect();
		// Querying database
		$select="select * from $table_name limit 1";
		$result="parent_".$table_name."_id";
		try {
			if(_DEBUG){echo "getChildren: $select // ";}
			self::$db->query($select);
			// Checking columns
			$queryResult=self::$db->fetch_object();
			foreach($queryResult as $key => $value){
				if(strlen($key)>8 && substr($key,0,7)=="parent_"){
					$result=$key;
				}
			} 
		} catch (Exception $e) {
			echo "Impossible to get children of parent name in table $tableName // ";
		}
		self::$db->disconnect();
		return $result; 
		
	}
}