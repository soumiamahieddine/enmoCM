<?php

/*
*    Copyright 2008-2010 Maarch
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
* @brief  Contains the controler of life_cycle_steps object (create, save, modify, etc...)
* 
* 
* @file
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
	require_once ("modules/life_cycle/class/lc_cycle_steps.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e) {
	echo $e->getMessage() . ' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
* @brief  Controler of the lc_cycle_steps object 
*
*<ul>
*  <li>Get an lc_cycle_steps object from an id</li>
*  <li>Save in the database a lc_cycle_steps</li>
*  <li>Manage the operation on the lc_cycle_steps related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup life_cycle
*/
class lc_cycle_steps_controler extends ObjectControler implements ObjectControlerIF {
	
	/**
	* Returns an lc_cycle_steps object based on a lc_cycle_steps identifier
	*
	* @param  $cycle_step_id string  lc_cycle_steps identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the cycle even if it is disabled in the database (false by default)
	* @return lc_cycle_steps object with properties from the database or null
	*/
	public function get($cycle_step_id, $comp_where = '', $can_be_disabled = false) {
		self :: set_foolish_ids(array('policy_id', 'cycle_id', 'cycle_step_id', 'docserver_type_id'));
		self :: set_specific_id('cycle_step_id');
		$cycle = self :: advanced_get($cycle_step_id, _LC_CYCLE_STEPS_TABLE_NAME);

		if (isset ($cycle_step_id))
			return $cycle;
		else
			return null;
	}

	/**
	* Saves in the database a lc_cycle_steps object 
	*
	* @param  $cycle lc_cycle_steps object to be saved
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($cycle) {
		if (!isset ($cycle))
			return false;

		self :: set_foolish_ids(array('policy_id', 'cycle_id', 'cycle_step_id', 'docserver_type_id'));
		self :: set_specific_id('cycle_step_id');
		if (self :: cycleExists($cycle->cycle_step_id))
			return self :: update($cycle);
		else
			return self :: insert($cycle);
	}
		
	/**
	* Inserts in the database (lc_cycle_steps table) a lc_cycle_steps object
	*
	* @param  $cycle lc_cycle_steps object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($cycle) {
		return self::advanced_insert($cycle);
	}

	/**
	* Updates in the database (lc_cycle_steps table) a lc_cycle_steps object
	*
	* @param  $cycle lc_cycle_steps object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($cycle) {
		return self::advanced_update($cycle);
	}

	/**
	* Deletes in the database (lc_cycle_steps related tables) a given lc_cycle_steps (cycle_step_id)
	*
	* @param  $cycle_step_id string  lc_cycle_steps identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($cycle_step_id) {
		if(!isset($cycle_step_id)|| empty($cycle_step_id) )
			return false;
		
		if(!self::cycleExists($cycle_step_id))
			return false;
				
		if(self::linkExists($cycle_step_id))
			return false;

		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._LC_CYCLE_STEPS_TABLE_NAME." where cycle_step_id ='".functions::protect_string_db($cycle_step_id)."'";
		
		try {
			if($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			echo _CANNOT_DELETE_CYCLE_ID." ".$cycle_step_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		
		return $ok;
	}

	/**
	* Disables a given lc_cycle_steps
	* 
	* @param  $cycle lc_cycle_steps object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($cycle) {
		self :: set_foolish_ids(array('policy_id', 'cycle_id', 'cycle_step_id', 'docserver_type_id'));
		self::set_specific_id('cycle_step_id');
		return self::advanced_disable($cycle);
	}
	
	/**
	* Enables a given lc_cycle_steps
	* 
	* @param  $cycle lc_cycle_steps object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($cycle) {
		self :: set_foolish_ids(array('policy_id', 'cycle_id', 'cycle_step_id', 'docserver_type_id'));
		self::set_specific_id('cycle_step_id');
		return self::advanced_enable($cycle);
	}

	public function cycleExists($cycle_step_id) {
		if (!isset ($cycle_step_id) || empty ($cycle_step_id))
			return false;
		self :: $db = new dbquery();
		self :: $db->connect();

		$query = "select cycle_step_id from " . _LC_CYCLE_STEPS_TABLE_NAME . " where cycle_step_id = '" . $cycle_step_id . "'";

		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _LC_CYCLE_STEPS . " " . $cycle_step_id . ' // ';
		}

		if (self :: $db->nb_result() > 0) {
			self :: $db->disconnect();
			return true;
		}
		self :: $db->disconnect();
		return false;
	}

	public function linkExists($policy_id, $cycle_step_id) {
		if(!isset($policy_id) || empty($policy_id))
			return false;
		if(!isset($cycle_step_id) || empty($cycle_step_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select cycle_step_id from "._LC_STACK_TABLE_NAME." where cycle_step_id = '".$cycle_step_id."' and policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		
		$query = "select cycle_step_id from "._LC_ADR_X_TABLE_NAME." where cycle_step_id = '".$cycle_step_id."' and policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
	}
}

?>
