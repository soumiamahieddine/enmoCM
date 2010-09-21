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
	require_once("modules/life_cycle/class/docserver_locations.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
* @brief  Controler of the docserver_locations object 
*
*<ul>
*  <li>Get an docserver_locations object from an id</li>
*  <li>Save in the database a docserver_locations</li>
*  <li>Manage the operation on the docserver_locations related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup life_cycle
*/
class docserver_locations_controler extends ObjectControler implements ObjectControlerIF {
	
	/**
	* Returns an docserver_locations object based on a docserver_locations identifier
	*
	* @param  $docserver_location_id string  docserver_locations identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the docserver_location even if it is disabled in the database (false by default)
	* @return docserver_locations object with properties from the database or null
	*/
	public function get($docserver_location_id, $comp_where = '', $can_be_disabled = false) {
		self :: set_foolish_ids(array('docserver_location_id'));
		self :: set_specific_id('docserver_location_id');
		$docserver_location = self :: advanced_get($docserver_location_id, _DOCSERVER_LOCATIONS_TABLE_NAME);

		if (isset ($docserver_location_id))
			return $docserver_location;
		else
			return null;
	}

	/**
	* Saves in the database a docserver_locations object 
	*
	* @param  $docserver_location docserver_locations object to be saved
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($docserver_location) {
		if (!isset ($docserver_location))
			return false;

		self :: set_foolish_ids(array('docserver_location_id'));
		self :: set_specific_id('docserver_location_id');
		if (self :: docserverLocationExists($docserver_location->docserver_location_id))
			return self :: update($docserver_location);
		else
			return self :: insert($docserver_location);
	}
		
	/**
	* Inserts in the database (docserver_locations table) a docserver_locations object
	*
	* @param  $docserver_location docserver_locations object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($docserver_location) {
		return self::advanced_insert($docserver_location);
	}

	/**
	* Updates in the database (docserver_locations table) a docserver_locations object
	*
	* @param  $docserver_location docserver_locations object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($docserver_location) {
		return self::advanced_update($docserver_location);
	}

	/**
	* Deletes in the database (docserver_locations related tables) a given docserver_locations (docserver_location_id)
	*
	* @param  $docserver_location_id string  docserver_locations identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($docserver_location) {
		if(!isset($docserver_location) || empty($docserver_location) )
			return false;
		
		if(!self::docserverLocationExists($docserver_location->docserver_location_id))
			return false;
				
		if(self::linkExists($docserver_location->docserver_location_id))
			return false;

		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._DOCSERVER_LOCATIONS_TABLE_NAME." where docserver_location_id ='".functions::protect_string_db($docserver_location->docserver_location_id)."'";
		
		try {
			if($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			echo _CANNOT_DELETE_CYCLE_ID." ".$docserver_location->docserver_location_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		
		return $ok;
	}

	/**
	* Disables a given docserver_locations
	* 
	* @param  $docserver_location docserver_locations object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($docserver_location) {
		self :: set_foolish_ids(array('docserver_location_id'));
		self::set_specific_id('docserver_location_id');
		
		if(self::linkExists($docserver_location->docserver_location_id))
			return false;
		return self::advanced_disable($docserver_location);
	}
	
	/**
	* Enables a given docserver_locations
	* 
	* @param  $docserver_location docserver_locations object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($docserver_location) {
		self :: set_foolish_ids(array('docserver_location_id'));
		self::set_specific_id('docserver_location_id');
		return self::advanced_enable($docserver_location);
	}

	public function docserverLocationExists($docserver_location_id) {
		if (!isset ($docserver_location_id) || empty ($docserver_location_id))
			return false;
		self :: $db = new dbquery();
		self :: $db->connect();

		$query = "select docserver_location_id from " . _DOCSERVER_LOCATIONS_TABLE_NAME . " where docserver_location_id = '" . $docserver_location_id . "'";

		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _LC_CYCLE . " " . $docserver_location_id . ' // ';
		}

		if (self :: $db->nb_result() > 0) {
			self :: $db->disconnect();
			return true;
		}
		self :: $db->disconnect();
		return false;
	}

	public function linkExists($docserver_location_id) {
		if(!isset($docserver_location_id) || empty($docserver_location_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select docserver_location_id from "._DOCSERVERS_TABLE_NAME." where docserver_location_id = '".$docserver_location_id."'";
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
		$query = "select docserver_location_id from " . _DOCSERVER_LOCATIONS_TABLE_NAME . " ";
		if (!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _NO_DOCSERVER_LOCATION . ' // ';
		}
		if (self :: $db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = self :: $db->fetch_object()) {
				$result[$cptId] = $queryResult->docserver_location_id;
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
