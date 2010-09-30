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
* @brief  Contains the life_cycle Object (herits of the BaseObject class)
* 
* 
* @file
* @author Luc KEULEYAN - BULL
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup life_cycle
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class

try {
	require_once ("modules/life_cycle/class/docserver_types.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
* @brief  Controler of the docserver_types_controler object 
*
*<ul>
*  <li>Get an docserver_types_controler object from an id</li>
*  <li>Save in the database a docserver_types_controler</li>
*  <li>Manage the operation on the docserver_types_controler related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup life_cycle
*/
class docserver_types_controler extends ObjectControler implements ObjectControlerIF {
	
	/**
	* Returns an docserver_types object based on a docserver_types identifier
	*
	* @param  $docserver_type_id string  docserver_types identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the docserver_type even if it is disabled in the database (false by default)
	* @return docserver_types object with properties from the database or null
	*/
	public function get($docserver_type_id, $comp_where = '', $can_be_disabled = false) {
		self :: set_foolish_ids(array('docserver_type_id'));
		self :: set_specific_id('docserver_type_id');
		$docserver_type = self :: advanced_get($docserver_type_id, _DOCSERVER_TYPES_TABLE_NAME);

		if (isset ($docserver_type_id))
			return $docserver_type;
		else
			return null;
	}

	/**
	* Saves in the database a docserver_types object 
	*
	* @param  $docserver_type docserver_types object to be saved
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($docserver_type) {
		if (!isset ($docserver_type))
			return false;

		self :: set_foolish_ids(array('docserver_type_id'));
		self :: set_specific_id('docserver_type_id');
		if (self :: docserverTypeExists($docserver_type->docserver_type_id))
			return self :: update($docserver_type);
		else
			return self :: insert($docserver_type);
	}
		
	/**
	* Inserts in the database (docserver_types table) a docserver_types object
	*
	* @param  $docserver_type docserver_types object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($docserver_type) {
		return self::advanced_insert($docserver_type);
	}

	/**
	* Updates in the database (docserver_types table) a docserver_types object
	*
	* @param  $docserver_type docserver_types object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($docserver_type) {
		return self::advanced_update($docserver_type);
	}

	/**
	* Deletes in the database (docserver_types related tables) a given docserver_types (docserver_type_id)
	*
	* @param  $docserver_type_id string  docserver_types identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($docserver_type) {
		if(!isset($docserver_type) || empty($docserver_type) )
			return false;
		
		if(!self::docserverTypeExists($docserver_type->docserver_type_id))
			return false;
				
		if(self::docserverLinkExists($docserver_type->docserver_type_id))
			return false;
			
		if(self::lcCycleStepsLinkExists($docserver_type->docserver_type_id))
			return false;

		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._DOCSERVER_TYPES_TABLE_NAME." where docserver_type_id ='".functions::protect_string_db($docserver_type->docserver_type_id)."'";
		
		try {
			if($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			echo _CANNOT_DELETE_CYCLE_ID." ".$docserver_type->docserver_type_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		
		return $ok;
	}

	/**
	* Disables a given docserver_types
	* 
	* @param  $docserver_type docserver_types object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($docserver_type) {
		self :: set_foolish_ids(array('docserver_type_id'));
		self::set_specific_id('docserver_type_id');
		
		if(self::docserverLinkExists($docserver_type->docserver_type_id)) { 
			return false;
		}
		if(self::lcCycleStepsLinkExists($docserver_type->docserver_type_id)) { 
			return false;
		}
		return self::advanced_disable($docserver_type);
	}
	
	/**
	* Enables a given docserver_types
	* 
	* @param  $docserver_type docserver_types object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($docserver_type) {
		self :: set_foolish_ids(array('docserver_type_id'));
		self::set_specific_id('docserver_type_id');
		return self::advanced_enable($docserver_type);
	}

	public function docserverTypeExists($docserver_type_id) {
		if (!isset ($docserver_type_id) || empty ($docserver_type_id))
			return false;
		self :: $db = new dbquery();
		self :: $db->connect();

		$query = "select docserver_type_id from " . _DOCSERVER_TYPES_TABLE_NAME . " where docserver_type_id = '" . $docserver_type_id . "'";

		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _LC_CYCLE . " " . $docserver_type_id . ' // ';
		}

		if (self :: $db->nb_result() > 0) {
			self :: $db->disconnect();
			return true;
		}
		self :: $db->disconnect();
		return false;
	}

	public function docserverLinkExists($docserver_type_id) {
		if(!isset($docserver_type_id) || empty($docserver_type_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select docserver_type_id from "._DOCSERVERS_TABLE_NAME." where docserver_type_id = '".$docserver_type_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
	}
	
	public function lcCycleStepsLinkExists($docserver_type_id) {
		if(!isset($docserver_type_id) || empty($docserver_type_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select docserver_type_id from "._LC_CYCLE_STEPS_TABLE_NAME." where docserver_type_id = '".$docserver_type_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
	}

	public function getAllId($can_be_disabled = false) {
		self :: $db = new dbquery();
		self :: $db->connect();
		$query = "select docserver_type_id from " . _DOCSERVER_TYPES_TABLE_NAME . " ";
		if (!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _NO_DOCSERVER_TYPE . ' // ';
		}
		if (self :: $db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = self :: $db->fetch_object()) {
				$result[$cptId] = $queryResult->docserver_type_id;
				$cptId++;
			}
			self :: $db->disconnect();
			return $result;
		} else {
			self :: $db->disconnect();
			return null;
		}
	}
}

?>
