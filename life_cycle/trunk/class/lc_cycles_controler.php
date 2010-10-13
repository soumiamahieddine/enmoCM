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
* @brief  Contains the controler of life_cycle object (create, save, modify, etc...)
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
	require_once ("modules/life_cycle/class/lc_cycles.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e) {
	echo $e->getMessage() . ' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
* @brief  Controler of the lc_cycles object 
*
*<ul>
*  <li>Get an lc_cycles object from an id</li>
*  <li>Save in the database a lc_cycles</li>
*  <li>Manage the operation on the lc_cycles related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup life_cycle
*/
class lc_cycles_controler extends ObjectControler implements ObjectControlerIF {
	
/**
	* Returns an lc_cycles object based on a lc_cycles identifier
	*
	* @param  $cycle_id string  lc_cycles identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the cycle even if it is disabled in the database (false by default)
	* @return lc_cycles object with properties from the database or null
*/
	public function get($cycle_id, $comp_where = '', $can_be_disabled = false) {
		self :: set_foolish_ids(array('policy_id', 'cycle_id'));
		self :: set_specific_id('cycle_id');
		$cycle = self :: advanced_get($cycle_id, _LC_CYCLES_TABLE_NAME);

		if (isset ($cycle_id))
			return $cycle;
		else
			return null;
	}

/**
	* Saves in the database a lc_cycles object 
	*
	* @param  $cycle lc_cycles object to be saved
	* @return bool true if the save is complete, false otherwise
*/
	public function save($cycle) {
		if (!isset ($cycle))
			return false;

		self :: set_foolish_ids(array('policy_id', 'cycle_id'));
		self :: set_specific_id('cycle_id');
		if (self :: cycleExists($cycle->cycle_id))
			return self :: update($cycle);
		else
			return self :: insert($cycle);
	}
		
/**
	* Inserts in the database (lc_cycles table) a lc_cycles object
	*
	* @param  $cycle lc_cycles object
	* @return bool true if the insertion is complete, false otherwise
*/
	private function insert($cycle) {
		return self::advanced_insert($cycle);
	}

/**
	* Updates in the database (lc_cycles table) a lc_cycles object
	*
	* @param  $cycle lc_cycles object
	* @return bool true if the update is complete, false otherwise
*/
	private function update($cycle) {
		return self::advanced_update($cycle);
	}

/**
	* Deletes in the database (lc_cycles related tables) a given lc_cycles (cycle_id)
	*
	* @param  $cycle_id string  lc_cycles identifier
	* @return bool true if the deletion is complete, false otherwise
*/
	public function delete($cycle_id) {
		if(!isset($cycle_id)|| empty($cycle_id) )
			return false;
		
		if(!self::cycleExists($cycle_id))
			return false;
				
		if(self::linkExists($policy_id, $cycle_id))
			return false;

		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._LC_CYCLES_TABLE_NAME." where cycle_id ='".functions::protect_string_db($cycle_id)."'";
		
		try {
			if($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			echo _CANNOT_DELETE_CYCLE_ID." ".$cycle_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		
		return $ok;
	}

/**
	* Disables a given lc_cycles
	* 
	* @param  $cycle lc_cycles object 
	* @return bool true if the disabling is complete, false otherwise 
*/
	public function disable($cycle) {
		self :: set_foolish_ids(array('policy_id', 'cycle_id'));
		self::set_specific_id('cycle_id');
		return self::advanced_disable($cycle);
	}
	
/**
	* Enables a given lc_cycles
	* 
	* @param  $cycle lc_cycles object  
	* @return bool true if the enabling is complete, false otherwise 
*/
	public function enable($cycle) {
		self :: set_foolish_ids(array('policy_id', 'cycle_id'));
		self::set_specific_id('cycle_id');
		return self::advanced_enable($cycle);
	}

/**
 * 
 * Checks if the life cycle cycle exists
 * @param $cycle_id lc_cycle identifier
 * @return bool true if the cycle exists
 */
	public function cycleExists($cycle_id) {
		if (!isset ($cycle_id) || empty ($cycle_id))
			return false;
		self :: $db = new dbquery();
		self :: $db->connect();

		$query = "select cycle_id from " . _LC_CYCLES_TABLE_NAME . " where cycle_id = '" . $cycle_id . "'";

		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _LC_CYCLE . " " . $cycle_id . ' // ';
		}

		if (self :: $db->nb_result() > 0) {
			self :: $db->disconnect();
			return true;
		}
		self :: $db->disconnect();
		return false;
	}

/**
 * 
 * Checks if the life cycle cycle is linked 
 * @param $cycle_id lc_cycle identifier
 * @param $policy_id lc_cycle policy identifier
 * @return bool true if the cycle is linked
 */
	public function linkExists($policy_id, $cycle_id) {
		if(!isset($policy_id) || empty($policy_id))
			return false;
		if(!isset($cycle_id) || empty($cycle_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select cycle_id from "._LC_STACK_TABLE_NAME." where cycle_id = '".$cycle_id."' and policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		$query = "select cycle_id from "._LC_CYCLE_STEPS_TABLE_NAME." where cycle_id = '".$cycle_id."' and policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		/*$query = "select cycle_id from "._LC_RES_X_TABLE_NAME." where cycle_id = '".$cycle_id."' and policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		$query = "select cycle_id from "._LC_ADR_X_TABLE_NAME." where cycle_id = '".$cycle_id."' and policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}*/
		self::$db->disconnect();
	}

	public function getAllId($can_be_disabled = false) {
		self :: $db = new dbquery();
		self :: $db->connect();
		$query = "select cycle_id from " . _LC_CYCLES_TABLE_NAME . " ";
		if (!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _NO_LC_CYCLE . ' // ';
		}
		if (self :: $db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = self :: $db->fetch_object()) {
				$result[$cptId] = $queryResult->cycle_id;
				$cptId++;
			}
			self :: $db->disconnect();
			return $result;
		} else {
			self :: $db->disconnect();
			return null;
		}
	}
	
/**
 * Displays lc_cycle according to a given policy_id 
 * 
 * @param $policy_id lc_cycle policy identifier
 * @return array lc_cycle identifier
 */
	public function getAllIdByPolicy($policy_id) {
		self :: $db = new dbquery();
		self :: $db->connect();
		$query = "select cycle_id from " . _LC_CYCLES_TABLE_NAME . " where policy_id = '".$policy_id."'";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _NO_LC_CYCLE . ' // ';
		}
		if (self :: $db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = self :: $db->fetch_object()) {
				$result[$cptId] = $queryResult->cycle_id;
				$cptId++;
			}
			self :: $db->disconnect();
			return $result;
		} else {
			self :: $db->disconnect();
			return null;
		}
	}
	
/**
	* Check the where clause syntax
	*
	* @param  $where_clause string The where clause to check
	* @return bool true if the syntax is correct, false otherwise
*/
	public function where_test($where_clause) {
		$res = true;
		self::$db=new dbquery();
		self::$db->connect();
		if(!empty($where_clause)) {
			$res = self::$db->query("select res_id from ".$_SESSION['collections'][0]['view']." where ".$where_clause, true);
		}
		if(!$res) {
			$res = false;
		}
		self::$db->disconnect();
		return $res;
	}
	
/**
	* Check the where clause syntax
	*
	* @param  $where_clause string The where clause to check
	* @return bool true if the syntax is correct, false otherwise
*/
	public function where_test_secure($where_clause) {
		$string = $where_clause;
		$search1="'drop|insert|delete|update'";
		preg_match($search1, $string, $out);
		$count=count($out[0]);
		if($count == 1) {
			$find1 = true;
		}
		else {
			$find1 = false;
		}
		return $find1;
	}
}

?>
