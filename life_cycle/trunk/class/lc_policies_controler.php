<?php

/*
*    Copyright 2008-2011 Maarch
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

// Loads the required class
try {
	require_once ("modules/life_cycle/class/lc_policies.php");
	require_once ("modules/life_cycle/life_cycle_tables_definition.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
	require_once ("core/class/class_history.php");
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
    * Save given object in database:
    * - make an update if object already exists,
    * - make an insert if new object.
    * @param object $policy
    * @param string mode up or add
    * @return array
    */
	public function save($policy, $mode = "") {
		$control = array();
        if (!isset($policy) || empty($policy)) {
            $control = array("status" => "ko", "value" => "", "error" => _POLICY_ID_EMPTY);
            return $control;
        }
		$policy = self::isAPolicy($policy);
		self::set_foolish_ids(array('policy_id'));
		self::set_specific_id('policy_id');
		if ($mode == "up") {
            $control = self::control($policy, "up");
            if ($control['status'] == "ok") {
                //Update existing policy
                if (self::update($policy)) {
                    $control = array("status" => "ok", "value" => $policy->policy_id);
                    //history
                    if ($_SESSION['history']['lcadd'] == "true") {
                        $history = new history();
                        $history->add(_LC_POLICIES_TABLE_NAME, $policy->policy_id, "UP", _LC_POLICY_UPDATED." : ".$policy->policy_id, $_SESSION['config']['databasetype']);
                    }
                } else {
                    $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_POLICY);
                }
                return $control;
            }
        } else {
            $control = self::control($policy, "add");
            if ($control['status'] == "ok") {
                //Insert new policy
                if (self::insert($policy)) {
                    $control = array("status" => "ok", "value" => $policy->policy_id);
                    //history
                    if ($_SESSION['history']['lcadd'] == "true") {
                        $history = new history();
                        $history->add(_LC_POLICIES_TABLE_NAME, $policy->policy_id, "ADD", _LC_POLICY_ADDED." : ".$policy->policy_id, $_SESSION['config']['databasetype']);
                    }
                } else {
                    $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_POLICY);
                }
            }
        }
        return $control;
	}

	/**
    * control the policy object before action
    *
    * @param  object $policy policy object
    * @param  string $mode up or add
    * @return array ok if the object is well formated, ko otherwise
    */
    private function control($policy, $mode) {
        $f = new functions();
        $error = "";
        // Update, so values exist
		$policy->policy_id=$f->protect_string_db($f->wash($_REQUEST['id'], "nick", _LC_POLICY_ID." ", 'yes', 0, 32));
		$policy->policy_name=$f->protect_string_db($f->wash($_REQUEST['policy_name'], "no", _POLICY_NAME." ", 'yes', 0, 255));
		$policy->policy_desc=$f->protect_string_db($f->wash($_REQUEST['policy_desc'], "no", _POLICY_DESC." ", 'yes', 0, 255));
		$lcPoliciesControler = new lc_policies_controler();
		if ($mode == "add" && $lcPoliciesControler->policyExists($policy->policy_id)) {	
			$error .= $policy->policy_id." "._ALREADY_EXISTS."<br />";
		}
        $error .= $_SESSION['error'];
        //TODO:rewrite wash to return errors without html
        $error = str_replace("<br />", "#", $error);
        $return = array();
        if (!empty($error)) {
                $return = array("status" => "ko", "value" => $policy->policy_id, "error" => $error);
        } else {
            $return = array("status" => "ok", "value" => $policy->policy_id);
        }
        return $return;
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
	* Returns an lc_policies object based on a lc_policies identifier
	*
	* @param  $policy_id string  lc_policies identifier
	* @return lc_policies object with properties from the database or null
	*/
	public function get($policy_id) {
		self::set_foolish_ids(array('policy_id'));
		self::set_specific_id('policy_id');
		$policy = self::advanced_get($policy_id, _LC_POLICIES_TABLE_NAME);
		//var_dump($policy);
        if (get_class($policy) <> "lc_policies") {
            return null;
        } else {
            //var_dump($policy);
            return $policy;
        }
	}

	/**
    * get lc_policies with given id for a ws.
    * Can return null if no corresponding object.
    * @param $policy_id of policy to send
    * @return policy
    */
    public function getWs($policy_id) {
        self::set_foolish_ids(array('policy_id'));
        self::set_specific_id('policy_id');
        $policy = self::advanced_get($policy_id, _LC_POLICIES_TABLE_NAME);
        if (get_class($policy) <> "lc_policies") {
            return null;
        } else {
            $policy = $policy->getArray();
            return $policy;
        }
    }

	/**
	* Deletes in the database (lc_policies related tables) a given lc_policies (policy_id)
	*
	* @param  $policy object  policy object
	* @return array true if the deletion is complete, false otherwise
	*/
	public function delete($policy) {
		$control = array();
        if (!isset($policy) || empty($policy)) {
            $control = array("status" => "ko", "value" => "", "error" => _LC_POLICY_EMPTY);
            return $control;
        }
        $policy = self::isAPolicy($policy);
        if (!self::policyExists($policy->policy_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _LC_POLICY_NOT_EXISTS);
            return $control;
        }
		if (self::linkExists($policy->policy_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _LINK_EXISTS);
            return $control;
        }
		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._LC_POLICIES_TABLE_NAME." where policy_id ='".self::$db->protect_string_db($policy->policy_id)."'";
		try {
			if ($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			$control = array("status" => "ko", "value" => "", "error" => _CANNOT_DELETE_POLICY_ID." ".$policy->policy_id);
			$ok = false;
		}
		self::$db->disconnect();
		$control = array("status" => "ok", "value" => $policy->policy_id);
		if ($_SESSION['history']['lcdel'] == "true") {
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
			$history = new history();
			$history->add(_LC_POLICIES_TABLE_NAME, $policy->policy_id, "DEL", _LC_POLICY_DELETED." : ".$policy->policy_id, $_SESSION['config']['databasetype']);
		}
		return $control;
	}

	/**
	* Disables a given lc_policies
	* 
	* @param  $policy lc_policies object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($policy) {
		//
	}

	/**
	* Enables a given lc_policies
	* 
	* @param  $policy lc_policies object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($policy) {
		//
	}

	/**
    * Fill a policy object with an object if it's not a policy
    *
    * @param  $object ws policy object
    * @return object policy
    */
    private function isAPolicy($object) {
        if (get_class($object) <> "lc_policies") {
            $func = new functions();
            $policyObject = new lc_policies();
            $array = array();
            $array = $func->object2array($object);
            foreach(array_keys($array) as $key) {
                $policyObject->$key = $array[$key];
            }
            return $policyObject;
        } else {
            return $object;
        }
    }

	/**
	* checks if the lc_policy exists
	* 
	* @param $policy_id lc_policy identifier
	* @return bool true if lc_policy exists
	*/
	public function policyExists($policy_id) {
		if (!isset ($policy_id) || empty ($policy_id))
			return false;
		self::$db = new dbquery();
		self::$db->connect();
		$query = "select policy_id from " . _LC_POLICIES_TABLE_NAME . " where policy_id = '" . $policy_id . "'";
		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			self::$db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _LC_POLICY . " " . $policy_id . ' // ';
		}
		if (self::$db->nb_result() > 0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
		return false;
	}

	/**
	* checks if the lc_policy is linked
	* 
	* @param $policy_id lc_policy identifier
	* @return bool true if lc_policy is linked
	*/
	public function linkExists($policy_id) {
		if (!isset($policy_id) || empty($policy_id))
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

	/**
	* Return all policies ID
	* 
	* @return array of policies
	*/
	public function getAllId() {
		self::$db = new dbquery();
		self::$db->connect();
		$query = "select policy_id from " . _LC_POLICIES_TABLE_NAME . " ";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			self::$db->query($query);
		} catch (Exception $e) {
			echo _NO_LC_POLICY_LOCATION . ' // ';
		}
		if (self::$db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = self::$db->fetch_object()) {
				$result[$cptId] = $queryResult->policy_id;
				$cptId++;
			}
			self::$db->disconnect();
			return $result;
		} else {
			self::$db->disconnect();
			return null;
		}
	}
}
?>
