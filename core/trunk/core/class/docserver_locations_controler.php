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
* @brief  Contains the docserver_locations_controler Object (herits of the BaseObject class)
* 
* 
* @file
* @author Luc KEULEYAN - BULL
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup core
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;

// Loads the required class
try {
	require_once ("core/core_tables.php");
	require_once ("core/class/docserver_locations.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
	//require_once("apps/maarch_entreprise/tools/Net_Ping-2.4.5/Ping.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the docserver_locations object 
*
*<ul>
*  <li>Get an docserver_locations object from an id</li>
*  <li>Save in the database a docserver_locations</li>
*  <li>Manage the operation on the docserver_locations related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class docserver_locations_controler extends ObjectControler implements ObjectControlerIF {
	
	public function testMethod($myVar) {
		return $myVar;
	}
	
	/**
     * Save given object in database:
     * - make an update if object already exists,
     * - make an insert if new object.
     * Return updated object.
     * @param docservers_locations $docservers_locations
     * @return array
     */
	public function save($docserver_location, $mode = "") {
        $control = array();
        if (!isset($docserver_location) || empty($docserver_location)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_EMPTY);
            return $control;
        }
        $docserver_location = $this->isADocserverLocation($docserver_location);
        $this->set_foolish_ids(array('docserver_location_id'));
		$this->set_specific_id('docserver_location_id');
        if ($mode == "up") {
            $control = $this->control($docserver_location, "up");
            if ($control['status'] == "ok") {
                //Update existing docserver
                if ($this->update($docserver_location)) {
                    $control = array("status" => "ok", "value" => $docserver_location->docserver_location_id);
                    //history
                    if ($_SESSION['history']['docserverslocationsadd'] == "true") {
                        $history = new history();
                        $history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_location->docserver_location_id, "UP", _DOCSERVER_LOCATION_UPDATED." : ".$docserver_location->docserver_location_id, $_SESSION['config']['databasetype']);
                    }
                } else {
                    $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER_LOCATION);
                }
                return $control;
            }
        } else {
            $control = $this->control($docserver_location, "add");
            if ($control['status'] == "ok") {
                //Insert new docserver
                if ($this->insert($docserver_location)) {
                    $control = array("status" => "ok", "value" => $docserver_location->docserver_location_id);
                    //history
                    if ($_SESSION['history']['docserverslocationsadd'] == "true") {
                        $history = new history();
                        $history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_location->docserver_location_id, "ADD", _DOCSERVER_LOCATION_ADDED." : ".$docserver_location->docserver_location_id, $_SESSION['config']['databasetype']);
                    }
                } else {
                    $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER_LOCATION);
                }
            }
        }
        return $control;
	}
	
	/**
    * control the docserver location object before action
    *
    * @param  $docserver_locations docserver location object
    * @return array ok if the object is well formated, ko otherwise
    */
    private function control($docserver_locations, $mode) {
        $f = new functions();
        $error = "";
        if (isset($docserver_locations->docserver_location_id) && !empty($docserver_locations->docserver_location_id)) {
			// Update, so values exist
			$docserver_locations->docserver_location_id=$f->protect_string_db($f->wash($docserver_locations->docserver_location_id, "nick", _DOCSERVER_LOCATION_ID." ", "yes", 0, 32));
		} else {
			$error .= _DOCSERVER_LOCATION_ID . " " . _IS_EMPTY . "#";
		}
		$docserver_locations->ipv4=$f->protect_string_db($f->wash($docserver_locations->ipv4, "no", _IPV4." ", 'yes', 0, 255));
		if (!$this->ipv4Control($docserver_locations->ipv4)) {	
			$error .= _IP_V4_FORMAT_NOT_VALID . "#";
		}
		/*if (!empty($docserver_locations->ipv4)) {
			if (!$this->pingIpv4($docserver_locations->ipv4))
				$error .= _IP_V4_ADRESS_NOT_VALID."#";
		}*/
		$docserver_locations->ipv6=$f->protect_string_db($f->wash($docserver_locations->ipv6, "no", _IPV6." ", 'no', 0, 255));
		if (!$this->ipv6Control($docserver_locations->ipv6)) {	
			$error .= _IP_V6_NOT_VALID . "#";
		}
		$docserver_locations->net_domain=$f->protect_string_db($f->wash($docserver_locations->net_domain, "no", _NET_DOMAIN." ", 'no', 0, 32));
		$docserver_locations->mask=$f->protect_string_db($f->wash($docserver_locations->mask, "no", _MASK." ", 'no', 0, 255));
		if (!$this->maskControl($docserver_locations->mask)) {	
			$error .= _MASK_NOT_VALID . "#";
		}
		$docserver_locations->net_link=$f->protect_string_db($f->wash($docserver_locations->net_link, "no", _NET_LINK." ", 'no', 0, 255));
        if ($mode == "add" && $this->docserverLocationExists($docserver_locations->docserver_location_id)) {	
			$error .= $docserver_locations->docserver_location_id." "._ALREADY_EXISTS."#";
		}
        $error .= $_SESSION['error'];
        //TODO:rewrite wash to return errors without html
        $error = str_replace("<br />", "#", $error);
        $return = array();
        if (!empty($error)) {
                $return = array("status" => "ko", "value" => $docserver_locations->docserver_location_id, "error" => $error);
        } else {
            $return = array("status" => "ok", "value" => $docserver_locations->docserver_location_id);
        }
        return $return;
    }
	
	/**
	* Inserts in the database (docserver_locations table) a docserver_locations object
	*
	* @param  $docserver_location docserver_locations object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($docserver_location) {
		return $this->advanced_insert($docserver_location);
	}

	/**
	* Updates in the database (docserver_locations table) a docserver_locations object
	*
	* @param  $docserver_location docserver_locations object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($docserver_location) {
		return $this->advanced_update($docserver_location);
	}
	
	/**
	* Returns an docserver_locations object based on a docserver_locations identifier
	*
	* @param  $docserver_location_id string  docserver_locations identifier
	* @param  $comp_where string  where clause arguments (must begin with and or or)
	* @param  $can_be_disabled bool  if true gets the docserver_location even if it is disabled in the database (false by default)
	* @return docserver_locations object with properties from the database or null
	*/
	public function get($docserver_location_id, $comp_where = '', $can_be_disabled = false) {
		$this->set_foolish_ids(array('docserver_location_id'));
		$this->set_specific_id('docserver_location_id');
		$docserver_location = $this->advanced_get($docserver_location_id, _DOCSERVER_LOCATIONS_TABLE_NAME);

		if (isset ($docserver_location_id))
			return $docserver_location;
		else
			return null;
	}
	
	/**
    * get docserver_locations with given id for a ws.
    * Can return null if no corresponding object.
    * @param $docserver_location_id of docserver_location to send
    * @return docserver_locations
    */
    public function getWs($docserver_location_id) {
        $this->set_foolish_ids(array('docserver_location_id'));
		$this->set_specific_id('docserver_location_id');
        $docserver_location = $this->advanced_get($docserver_location_id, _DOCSERVER_LOCATIONS_TABLE_NAME);
        if (get_class($docserver_location) <> "docserver_locations") {
            return null;
        } else {
            $docserver_location = $docserver_location->getArray();
            return $docserver_location;
        }
    }

	/**
	* Deletes in the database (docserver_locations related tables) a given docserver_locations (docserver_location_id)
	*
	* @param  $docserver_location_id string  docserver_locations identifier
	* @return bool true if the deletion is complete, false otherwise
	*/
	public function delete($docserver_location) {
		$func = new functions();
		$control = array();
        if (!isset($docserver_location) || empty($docserver_location)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_LOCATION_EMPTY);
            return $control;
        }
        $docserver_location = $this->isADocserverLocation($docserver_location);
        if (!$this->docserverLocationExists($docserver_location->docserver_location_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_LOCATION_NOT_EXISTS);
            return $control;
        }
        if ($this->linkExists($docserver_location->docserver_location_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _LINK_EXISTS);
            return $control;
        }
		$db=new dbquery();
		$db->connect();
		$query="delete from "._DOCSERVER_LOCATIONS_TABLE_NAME." where docserver_location_id ='".$func->protect_string_db($docserver_location->docserver_location_id)."'";
		try {
			if ($_ENV['DEBUG']) {echo $query.' // ';}
			$db->query($query);
		} catch (Exception $e) {
			$control = array("status" => "ko", "value" => "", "error" => _CANNOT_DELETE_DOCSERVER_LOCATION_ID." ".$docserver_location->docserver_location_id);
		}
		$db->disconnect();
		$control = array("status" => "ok", "value" => $docserver_location->docserver_location_id);
		if ($_SESSION['history']['docserverslocationsdel'] == "true") {
			$history = new history();
			$history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_location->docserver_location_id, "DEL", _DOCSERVER_LOCATION_DELETED." : ".$docserver_location->docserver_location_id, $_SESSION['config']['databasetype']);
		}
		return $control;
	}

	/**
	* Disables a given docserver_locations
	* 
	* @param  $docserver_location docserver_locations object 
	* @return array
	*/
	public function disable($docserver_location) {
		$control = array();
        if (!isset($docserver_location) || empty($docserver_location)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_LOCATION_EMPTY);
            return $control;
        }
        $docserver_location = $this->isADocserverLocation($docserver_location);
        if ($this->linkExists($docserver_location->docserver_location_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _LINK_EXISTS);
            return $control;
        }
        $this->set_foolish_ids(array('docserver_location_id'));
        $this->set_specific_id('docserver_location_id');
        if ($this->advanced_disable($docserver_location)) {
            $control = array("status" => "ok", "value" => $docserver_location->docserver_location_id);
            if ($_SESSION['history']['docserverslocationsban'] == "true") {
                $history = new history();
                $history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_location->docserver_location_id, "BAN", _DOCSERVER_LOCATION_DISABLED." : ".$docserver_location->docserver_location_id, $_SESSION['config']['databasetype']);
            }
        } else {
            $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER_LOCATION);
        }
        return $control;
	}
	
	/**
	* Enables a given docserver_locations
	* 
	* @param  $docserver_location docserver_locations object  
	* @return array
	*/
	public function enable($docserver_location) {
		$control = array();
        if (!isset($docserver_location) || empty($docserver_location)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_LOCATION_EMPTY);
            return $control;
        }
        $docserver_location = $this->isADocserverLocation($docserver_location);
        $this->set_foolish_ids(array('docserver_location_id'));
        $this->set_specific_id('docserver_location_id');
        if ($this->advanced_enable($docserver_location)) {
            $control = array("status" => "ok", "value" => $docserver_location->docserver_location_id);
            if ($_SESSION['history']['docserverslocationsallow'] == "true") {
                $history = new history();
                $history->add(_DOCSERVER_LOCATIONS_TABLE_NAME, $docserver_location->docserver_location_id, "BAN", _DOCSERVER_LOCATION_ENABLED." : ".$docserver_location->docserver_location_id, $_SESSION['config']['databasetype']);
            }
        } else {
            $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER_LOCATION);
        }
        return $control;
	}
	
	/**
    * Fill a docserver_locations object with an object if it's not a docserver_locations
    *
    * @param  $object ws docserver_locations object
    * @return object docserver_locations
    */
    private function isADocserverLocation($object) {
        if (get_class($object) <> "docserver_locations") {
            $func = new functions();
            $docserverLocationsObject = new docserver_locations();
            $array = array();
            $array = $func->object2array($object);
            foreach(array_keys($array) as $key) {
                $docserverLocationsObject->$key = $array[$key];
            }
            return $docserverLocationsObject;
        } else {
            return $object;
        }
    }

	/** 
	* Checks if a docserver_locations exists
	* 
	* @param $docserver_location_id docserver_locations object
	* @return bool true if the docserver_locations exists
	*/
	public function docserverLocationExists($docserver_location_id) {
		if (!isset ($docserver_location_id) || empty ($docserver_location_id))
			return false;
		$db = new dbquery();
		$db->connect();
		$query = "select docserver_location_id from " . _DOCSERVER_LOCATIONS_TABLE_NAME . " where docserver_location_id = '" . $docserver_location_id . "'";
		try {
			if ($_ENV['DEBUG']) {
				echo $query . ' // ';
			}
			$db->query($query);
		} catch (Exception $e) {
			echo _UNKNOWN . _DOCSERVER_LOCATION . " " . $docserver_location_id . ' // ';
		}
		if ($db->nb_result() > 0) {
			$db->disconnect();
			return true;
		}
		$db->disconnect();
		return false;
	}

	/**
	*  Checks if a docserver_locations is linked
	* 
	* @param $docserver_location_id docserver_locations object
	* @return bool true if the docserver_locations is linked
	*/
	public function linkExists($docserver_location_id) {
		if (!isset($docserver_location_id) || empty($docserver_location_id))
			return false;
		$db=new dbquery();
		$db->connect();
		$query = "select docserver_location_id from "._DOCSERVERS_TABLE_NAME." where docserver_location_id = '".$docserver_location_id."'";
		$db->query($query);
		if ($db->nb_result()>0) {
			$db->disconnect();
			return true;
		}
		$db->disconnect();
	}
	
	/** 
	*  Check if the docserver location ipV4 is valid
	* 
	*  @param ipv4 docservers 
	*  @return bool true if it's valid  
	* 
	*/ 	
	public function ipv4Control($ipv4) {
		if (empty($ipv4))
		return true;
		$ipv4 = htmlspecialchars($ipv4);	
		if (preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" .
			"(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ipv4)) {		
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Check if the docserver location ipV6 is valid
	* 
	* @param ipv6 docservers 
	* @return bool true if it's valid 
	*/	
	public function ipv6Control($ipv6) {
		if (empty($ipv6))
			return true;
		$ipv6 = htmlspecialchars($ipv6);
		$patternIpv6 = '/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/';		
		if (preg_match($patternIpv6, $ipv6)) {		
			return true;
		} else {
			return false;
		}
	}
	
	/** 
	* Check if the docserver location mask is valid
	* 
	* @param mask docservers 
	* @return bool true if it's valid  
	*/	
	public function maskControl($mask) {
		if (empty($mask))
			return true;
		$mask = htmlspecialchars($mask);
		if (preg_match("/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}0$/", $mask)) {		
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Returns in an array all the docservers of a docserver location (docserver_id only) 
	* 
	* @param  $docserver_location_id string  Docserver_location identifier
	* @return Array of docserver_id or null
	*/
	public function getDocservers($docserver_location_id) {		
		if (empty($docserver_location_id))
			return null;
		$docservers = array();
		$db=new dbquery();
		$db->connect();
		$query = "select docserver_id from "._DOCSERVERS_TABLE_NAME." where docserver_location_id = '".$docserver_location_id."'";
		try{
			if ($_ENV['DEBUG']) {echo $query.' // ';}
					$db->query($query);
		} catch (Exception $e) {
					echo _NO_DOCSERVER_LOCATION_WITH_ID.' '.$docserver_location_id.' // ';
		}
		while($res = $db->fetch_object()) {
			array_push($docservers, $res->docserver_id);
		}
		$db->disconnect();
		return $docservers;
	}

	/**
	* Return all docservers locations ID
	* @return array of docservers locations
	*/
	public function getAllId($can_be_disabled = false) {
		$db = new dbquery();
		$db->connect();
		$query = "select docserver_location_id from " . _DOCSERVER_LOCATIONS_TABLE_NAME . " ";
		if (!$can_be_disabled)
			$query .= " where enabled = 'Y'";
		try {
			if ($_ENV['DEBUG'])
				echo $query . ' // ';
			$db->query($query);
		} catch (Exception $e) {
			echo _NO_DOCSERVER_LOCATION . ' // ';
		}
		if ($db->nb_result() > 0) {
			$result = array ();
			$cptId = 0;
			while ($queryResult = $db->fetch_object()) {
				$result[$cptId] = $queryResult->docserver_location_id;
				$cptId++;
			}
			$db->disconnect();
			return $result;
		} else {
			$db->disconnect();
			return null;
		}
	}
	
	/**
	* Ping the ipv4
	* 
	* @param ipv4 docservers
	* @return bool true if valid 	
	*/
	public function pingIpv4 ($ipv4) {
		$ping = Net_Ping::factory();
		if (PEAR::isError($ping)) {
			return false;
		} else {
			$response = $ping->ping($ipv4);
			if ($response->getReceived() == $response->getTransmitted()) {
				return true;
			} else {
				return false;
			}
		}
	}
}

?>
