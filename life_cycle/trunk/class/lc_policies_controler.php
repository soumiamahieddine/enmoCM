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
	require_once ("modules/life_cycle/class/lc_policies.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e) {
	echo $e->getMessage() . ' // ';
}

/**
* @brief  Controler of the lc_policies object 
*
*<ul>
*  <li>Get an lc_policies object from an id</li>
*  <li>Save in the database a lc_policies</li>
*  <li>Manage the operation on the lc_policies related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup life_cycle
*/
class lc_policies_controler extends ObjectControler implements ObjectControlerIF {

	/**
	* Returns an lc_policies object based on a lc_policies identifier
	*
	* @param  $policy_id string  lc_policies identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the policy even if it is disabled in the database (false by default)
	* @return lc_policies object with properties from the database or null
	*/
	public function get($policy_id, $comp_where = '', $can_be_disabled = false) {
		self :: set_foolish_ids(array('policy_id'));
		self :: set_specific_id('policy_id');
		$policy = self :: advanced_get($policy_id, _LC_POLICIES_TABLE_NAME);

		if (isset ($policy_id))
			return $policy;
		else
			return null;
	}

	/**
	* Saves in the database a lc_policies object 
	*
	* @param  $policy lc_policies object to be saved
	* @return bool true if the save is complete, false otherwise
	*/
	public function save($policy) {
		if (!isset ($policy))
			return false;

		self :: set_foolish_ids(array('policy_id'));
		self :: set_specific_id('policy_id');
		if (self :: policyExists($policy->policy_id))
			return self :: update($policy);
		else
			return self :: insert($policy);
	}
		
	/**
	* Inserts in the database (lc_policies table) a lc_policies object
	*
	* @param  $policy lc_policies object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($policy) {
		return self::advanced_insert($policy);
	}

	/**
	* Updates in the database (lc_policies table) a lc_policies object
	*
	* @param  $policy lc_policies object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($policy) {
		return self::advanced_update($policy);
	}

	/**
	* Deletes in the database (lc_policies related tables) a given lc_policies (policy_id)
	*
	* @param  $policy_id string  lc_policies identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($policy_id) {
		if(!isset($policy_id)|| empty($policy_id) )
			return false;
		
		if(!self::policyExists($policy_id))
			return false;
				
		if(self::linkExists($policy_id))
			return false;

		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._LC_POLICIES_TABLE_NAME." where policy_id ='".functions::protect_string_db($policy_id)."'";
		
		try {
			if($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			echo _CANNOT_DELETE_POLICY_ID." ".$policy_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		
		return $ok;
	}

	/**
	* Disables a given lc_policies
	* 
	* @param  $policy lc_policies object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($policy) {
		self::set_foolish_ids(array('policy_id'));
		self::set_specific_id('policy_id');
		return self::advanced_disable($policy);
	}
	
	/**
	* Enables a given lc_policies
	* 
	* @param  $policy lc_policies object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($policy) {
		self::set_foolish_ids(array('policy_id'));
		self::set_specific_id('policy_id');
		return self::advanced_enable($policy);
	}

	public function policyExists($policy_id) {
		if (!isset ($policy_id) || empty ($policy_id))
			return false;
		self :: $db = new dbquery();
		self :: $db->connect();

		//LKE = BULL ===== SPEC FONC : ==== Cycles de vie : lc_policies (ID1)
		// Ajout du contr�le pour v�rifier l'existence de la combinaison "policy_id"	
		$query = "select policy_id from " . _LC_POLICIES_TABLE_NAME . " where policy_id = '" . $policy_id . "'";

		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _LC_POLICY . " " . $policy_id . ' // ';
		}

		if (self :: $db->nb_result() > 0) {
			self :: $db->disconnect();
			return true;
		}
		self :: $db->disconnect();
		return false;
	}

	public function linkExists($policy_id) {
		if(!isset($policy_id) || empty($policy_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		
		$query = "select policy_id from "._LC_STACK_TABLE_NAME." where policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		$query = "select policy_id from "._LC_CYCLE_STEPS_TABLE_NAME." where policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		/*$query = "select policy_id from "._LC_RES_X_TABLE_NAME." where policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		$query = "select policy_id from "._LC_ADR_X_TABLE_NAME." where policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}*/
		$query = "select policy_id from "._LC_CYCLES_TABLE_NAME." where policy_id = '".$policy_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
	}

	public function getAllId() {
		self :: $db = new dbquery();
		self :: $db->connect();
		$query = "select policy_id from " . _LC_POLICIES_TABLE_NAME . " ";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			self :: $db->query($query);
		} catch (Exception $e) {
			echo _NO_LC_POLICY_LOCATION . ' // ';
		}
		if (self :: $db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = self :: $db->fetch_object()) {
				$result[$cptId] = $queryResult->policy_id;
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
