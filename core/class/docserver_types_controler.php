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
* @brief  Contains the docserver_types_controler Object (herits of the BaseObject class)
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
    require_once ("core/class/docserver_types.php");
    require_once ("core/core_tables.php");
    require_once ("core/class/ObjectControlerAbstract.php");
    require_once ("core/class/ObjectControlerIF.php");
} catch (Exception $e) {
    echo functions::xssafe($e->getMessage()).' // ';
}

/**
* @brief  Controler of the docserver_types_controler object 
*
*<ul>
*  <li>Get an docserver_types_controler object from an id</li>
*  <li>Save in the database a docserver_types_controler</li>
*  <li>Manage the operation on the docserver_types_controler related tables in the database (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class docserver_types_controler extends ObjectControler implements ObjectControlerIF {
    
    /**
     * Save given object in database:
     * - make an update if object already exists,
     * - make an insert if new object.
     * Return updated object.
     * @param docservers_types $docservers_types
     * @return array
     */
    public function save($docserver_type, $mode = "") {
    }

    public function delete($args)
    {
    }

    /**
    * Returns an docserver_types object based on a docserver_types identifier
    *
    * @param  $docserver_type_id string  docserver_types identifier
    * @param  $comp_where string  where clause arguments (must begin with and or or)
    * @param  $can_be_disabled bool  if true gets the docserver_type even if it is disabled in the database (false by default)
    * @return docserver_types object with properties from the database or null
    */
    public function get($docserver_type_id, $comp_where = '', $can_be_disabled = false) {
        $this->set_foolish_ids(array('docserver_type_id'));
        $this->set_specific_id('docserver_type_id');
        $docserver_type = $this->advanced_get($docserver_type_id, _DOCSERVER_TYPES_TABLE_NAME);

        if (isset ($docserver_type_id))
            return $docserver_type;
        else
            return null;
    }

    /**
    * get docserver_types with given id for a ws.
    * Can return null if no corresponding object.
    * @param $docserver_type_id of docserver_type to send
    * @return docserver_types
    */
    public function getWs($docserver_type_id) {
        $this->set_foolish_ids(array('docserver_type_id'));
        $this->set_specific_id('docserver_type_id');
        $docserver_type = $this->advanced_get($docserver_type_id, _DOCSERVER_TYPES_TABLE_NAME);
        if (get_class($docserver_type) <> "docserver_types") {
            return null;
        } else {
            $docserver_type = $docserver_type->getArray();
            return $docserver_type;
        }
    }

    /**
    * Disables a given docserver_types
    * 
    * @param  $docserver_type docserver_types object 
    * @return bool true if the disabling is complete, false otherwise 
    */
    public function disable($docserver_type) {
        if ($docserver_type <> 'TEMPLATES') {
            $control = array();
            if (!isset($docserver_type) || empty($docserver_type)) {
                $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_TYPE_EMPTY);
                return $control;
            }
            $docserver_type = $this->isADocserverType($docserver_type);
            $this->set_foolish_ids(array('docserver_type_id'));
            $this->set_specific_id('docserver_type_id');
            if ($this->docserverLinkExists($docserver_type->docserver_type_id)) {
                $control = array("status" => "ko", "value" => "", "error" => _LINK_EXISTS);
                return $control;
            }
            if ($this->lcCycleStepsLinkExists($docserver_type->docserver_type_id)) {
                $control = array("status" => "ko", "value" => "", "error" => _LINK_EXISTS);
                return $control;
            }
            if ($this->advanced_disable($docserver_type)) {
                $control = array("status" => "ok", "value" => $docserver_type->docserver_type_id);
                if ($_SESSION['history']['docserverstypesban'] == "true") {
                    $history = new history();
                    $history->add(
                        _DOCSERVER_TYPES_TABLE_NAME, 
                        $docserver_type->docserver_type_id, "BAN", 'docserverstypesban',
                        _DOCSERVER_TYPE_DISABLED." : ".$docserver_type->docserver_type_id, 
                        $_SESSION['config']['databasetype']);
                }
            } else {
                $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER_TYPE);
            }
        } else {
            $control = array(
                'status' => 'ko', 
                'value' => '', 
                'error' => _CANNOT_DISABLE_DOCSERVER_TYPE_ID . ' '. $docserver_type->docserver_type_id,
            );
        }
        return $control;
    }

    /**
    * Enables a given docserver_types
    * 
    * @param  $docserver_type docserver_types object  
    * @return bool true if the enabling is complete, false otherwise 
    */
    public function enable($docserver_type) {
        $control = array();
        if (!isset($docserver_type) || empty($docserver_type)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_TYPE_EMPTY);
            return $control;
        }
        $docserver_type = $this->isADocserverType($docserver_type);
        $this->set_foolish_ids(array('docserver_type_id'));
        $this->set_specific_id('docserver_type_id');
        if ($this->advanced_enable($docserver_type)) {
            $control = array("status" => "ok", "value" => $docserver_type->docserver_type_id);
            if ($_SESSION['history']['docserverstypesallow'] == "true") {
                $history = new history();
                $history->add(
                    _DOCSERVER_TYPES_TABLE_NAME, 
                    $docserver_type->docserver_type_id, "BAN", 'docserverstypesallow',
                    _DOCSERVER_TYPE_ENABLED." : ".$docserver_type->docserver_type_id, 
                    $_SESSION['config']['databasetype']);
            }
        } else {
            $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER_TYPE);
        }
        return $control;
    }

    /**
    * Fill a docserver_types object with an object if it's not a docserver_types
    *
    * @param  $object ws docserver_types object
    * @return object docserver_types
    */
    private function isADocserverType($object) {
        if (get_class($object) <> "docserver_types") {
            $func = new functions();
            $docserverTypesObject = new docserver_types();
            $array = array();
            $array = $func->object2array($object);
            foreach(array_keys($array) as $key) {
                $docserverTypesObject->{$key} = $array[$key];
            }
            return $docserverTypesObject;
        } else {
            return $object;
        }
    }
    
    /** 
    * Checks if a docserver_types exists
    * 
    * @param $docserver_type_id docserver_types object
    * @return bool true if the docserver_types exists
    */
    public function docserverTypeExists($docserver_type_id) {
        if (!isset ($docserver_type_id) || empty ($docserver_type_id))
            return false;
        $db = new Database();
        $query = "select docserver_type_id from " 
            . _DOCSERVER_TYPES_TABLE_NAME . " where docserver_type_id = ?";
        try {
            $stmt = $db->query($query, array($docserver_type_id));
        } catch (Exception $e) {
            echo _UNKNOWN . _LC_CYCLE . " " . functions::xssafe($docserver_type_id) . ' // ';
        }
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
    
    /**
    *  Checks if a docserver is linked
    * 
    * @param $docserver_id docserver id
    * @return bool true if the docserver is linked
    */
    public function docserverLinkExists($docserver_type_id) {
        if (!isset($docserver_type_id) || empty($docserver_type_id))
            return false;
        $db = new Database();        
        $query = "select docserver_type_id from "
            . _DOCSERVERS_TABLE_NAME . " where docserver_type_id = ?";
        $stmt = $db->query($query, array($docserver_type_id));
        if ($stmt->rowCount()>0) {
            return true;
        }
    }
    
    /**
    *  Checks if a cycle_steps is linked
    * 
    * @param $docserver_id docserver id
    * @return bool true if the cycle_steps is linked
    */
    public function lcCycleStepsLinkExists($docserver_type_id) {
        if (!isset($docserver_type_id) || empty($docserver_type_id))
            return false;
        $db = new Database();
        $query = "select docserver_type_id from " 
            . _LC_CYCLE_STEPS_TABLE_NAME . " where docserver_type_id = ?";
        $stmt = $db->query($query, array($docserver_type_id));
        if ($stmt->rowCount()>0) {
            return true;
        }
    }

    /**
    * Returns in an array all the members of a docserver type (docserver_id only) 
    *
    * @param  $docserver_id string  Docserver identifier
    * @return Array of docserver_id or null
    */
    public function getDocservers($docserver_type_id) {        
        if (empty($docserver_type_id))
            return null;
        $docservers = array();
        $db = new Database();
        $query = "select docserver_id from "
            . _DOCSERVERS_TABLE_NAME . " where docserver_type_id = ?";
        try{
            $stmt = $db->query($query, array($docserver_type_id));
        } catch (Exception $e) {
            echo _NO_TYPE_WITH_ID.' '.functions::xssafe($docserver_type_id).' // ';
        }
        while ($res = $stmt->fetchObject()) {
            array_push($docservers, $res->docserver_id);
        }
        return $docservers;
    }
    
    /**
    * Return all docservers types ID
    * @return array of docservers types
    */
    public function getAllId($can_be_disabled = false) {
        $db = new Database();
        $query = "select docserver_type_id from " . _DOCSERVER_TYPES_TABLE_NAME . " ";
        if (!$can_be_disabled)
            $query .= " where enabled = 'Y'";
        try {
            $stmt = $db->query($query);
        } catch (Exception $e) {
            echo _NO_DOCSERVER_TYPE . ' // ';
        }
        if ($stmt->rowCount() > 0) {
            $result = array();
            $cptId = 0;
            while ($queryResult = $stmt->fetchObject()) {
                $result[$cptId] = $queryResult->docserver_type_id;
                $cptId++;
            }
            return $result;
        } else {
            return null;
        }
    }
}
