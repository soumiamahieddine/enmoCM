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
* @brief  Contains the controler of the Security Object
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// Loads the required class
try {
    require_once 'core/core_tables.php';
    require_once 'core/manage_bitmask.php';
    require_once 'core/class/class_db.php';
    require_once 'core/class/users_controler.php';
    require_once 'core/class/session_security_controler.php';
    require_once 'core/class/Security.php';
    if (! defined('_CLASSIFICATION_SCHEME_VIEW')) {
        define('_CLASSIFICATION_SCHEME_VIEW', 'mr_classification_scheme_view');
    }
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

/**
* @brief  Controler of the Security Object
*
*<ul>
*  <li>Get an security object from an id</li>
*  <li>Save in the database a security</li>
*  <li>Manage the operation on the security table in the database
*   (_insert, select, _update, delete)</li>
*</ul>
* @ingroup core
*/


class SecurityControler
{
    /**
    * Returns an Security Object based on a security identifier
    *
    * @param  $securityId string  Security identifier
    * @return Security object with properties from the database or null
    */
    public function get($securityId)
    {
        if (empty($securityId)) {
            return null;
        }
        $db = new dbquery();
        $db->connect();

        $query = "select * from " . SECURITY_TABLE . " where security_id = "
               . $securityId;
        try {
            $db->query($query);
        } catch (Exception $e){
            echo _NO_ACCESS_WITH_ID . ' ' . $securityId . ' // ';
        }

        if ($db->nb_result() > 0) {
            $access = new SecurityObj();
            $queryResult = $db->fetch_object();
            foreach ($queryResult as $key => $value) {
                $access->$key = $value;
            }
            return $access;
        } else {
            return null;
        }
    }

    /**
    * Returns all security object for a given usergroup
    *
    * @param  $groupId string  Usergroup identifier
    * @return Array of security objects or null
    */
    public function getAccessForGroup($groupId)
    {
        if (empty($groupId)) {
            return null;
        }
        $db = new dbquery();
        $db->connect();
        // Querying database
        $query = "select * from " . SECURITY_TABLE . " where group_id = '"
               . $groupId . "'";

        try {
            $db->query($query);
        } catch (Exception $e) {
            echo _NO_GROUP_WITH_ID . ' ' . $groupId . ' // ';
        }

        $security = array();
        if ($db->nb_result() > 0) {
            while ($queryResult = $db->fetch_object()) {
                $access = new SecurityObj();
                foreach ($queryResult as $key => $value) {
                    $access->$key = $value;
                }
                array_push($security, $access);
            }
        }
        return $security;
    }

    /**
    * Saves in the database a security object
    *
    * @param  $security Security object to be saved
    * @param  $mode string  Saving mode : add or up (add by default)
    * @return bool true if the save is complete, false otherwise
    */
    public function save($security, $mode="add")
    {
        if (! isset($security)) {
            return false;
        }

        if ($mode == "up") {
            return $this->_update($security);
        } else if ($mode == "add") {
            return $this->_insert($security);
        }

        return false;
    }

    /**
    * Inserts in the database (security table) a Security object
    *
    * @param  $security Security object
    * @return bool true if the _insertion is complete, false otherwise
    */
    private function _insert($security)
    {
        if (! isset($security)) {
            return false;
        }
        $db = new dbquery();
        $db->connect();
        $prepQuery = $this->_insertPrepare($security);

        $query = "insert into " . SECURITY_TABLE . " (" . $prepQuery['COLUMNS']
               . ") values (" . $prepQuery['VALUES'] . ")";
        try {
            $db->query($query);
            $ok = true;
        } catch (Exception $e) {
            echo _CANNOT_INSERT_ACCESS . " " . $security->toString() . ' // ';
            $ok = false;
        }
        return $ok;
    }

    /**
    * Updates a security in the database (security table) with a Security object
    *
    * @param  $security Security object
    * @return bool true if the _update is complete, false otherwise
    */
    private function _update($security)
    {
        if (! isset($security)) {
            return false;
        }
        $db = new dbquery();
        $db->connect();
        $query = "update " . SECURITY_TABLE . " set "
               . $this->_updatePrepare($security) . " where security_id="
               . $security->security_id;

        try {
            $db->query($query);
            $ok = true;
        } catch (Exception $e) {
            echo _CANNOT_UPDATE_ACCESS . " " . $security->toString() . ' // ';
            $ok = false;
        }
        return $ok;
    }

    /**
    * Deletes in the database (security table) a given security
    *
    * @param  $securityId string  Security identifier
    * @return bool true if the deletion is complete, false otherwise
    */
    public function delete($securityId)
    {
        if (! isset($securityId) || empty($securityId)) {
            return false;
        }
        $db = new dbquery();
        $db->connect();
        $query = "delete from " . SECURITY_TABLE . " where security_id="
               . $securityId;
        try {
            $db->query($query);
            $ok = true;
        } catch (Exception $e) {
            echo _CANNOT_DELETE_SECURITY_ID . " " . $securityId . ' // ';
            $ok = false;
        }
        return $ok;
    }

    /**
    * Deletes in the database (security table) all security of a given usergroup
    *
    * @param  $groupId string  Usergroup identifier
    * @return bool true if the deletion is complete, false otherwise
    */
    public function deleteForGroup($groupId)
    {
        if (! isset($groupId) || empty($groupId)) {
            return false;
        }
        $db = new dbquery();
        $db->connect();
        $query = "delete from " . SECURITY_TABLE . " where group_id='"
               . $groupId . "'";
        try {
            $db->query($query);
            $ok = true;
        } catch (Exception $e) {
            echo _CANNOT_DELETE . ' ' . _GROUP_ID . " " . $groupId . ' // ';
            $ok = false;
        }
        return $ok;
    }

    /**
    * Prepares the _update query for a given Security object
    *
    * @param  $security Security object
    * @return String containing the fields and the values
    */
    private function _updatePrepare($security)
    {
        $result = array();
        foreach ($security->getArray() as $key => $value) {
            // For now all fields in the usergroups table are strings or date
            // excepts the security_id
            if (! empty($value)) {
                if ($key <> 'security_id') {
                    $result[] = $key . "='" . $value . "'";
                }
            }
        }
        // Return created string minus last ", "
        return implode(",", $result);
    }

    /**
    * Prepares the _insert query for a given Security object
    *
    * @param  $security Security object
    * @return Array containing the fields and the values
    */
    private function _insertPrepare($security)
    {
        $columns = array();
        $values = array();
        foreach ($security->getArray() as $key => $value) {
            // For now all fields in the security table are strings
            // or date excepts the security_id
            if (! empty($value)) {
                if ($key <> 'security_id') {
                    $columns[] = $key;
                    $values[] = "'" . $value . "'";
                }
            }
        }
        return array(
            'COLUMNS' => implode(",", $columns),
            'VALUES'  => implode(",", $values),
        );
    }

    public function check_where_clause($collId, $target, $whereClause,
       $view, $userId)
    {
        $res = array(
            'RESULT' => false,
            'TXT' => '',
        );

        if (empty($collId) || empty($target) || empty($whereClause)) {
            $res['TXT'] = _ERROR_PARAMETERS_FUNCTION;
            return $res;
        }
        $where = ' ' . $whereClause;
        $where = str_replace('\\', '', $where);
        $where = $this->process_security_where_clause($where, $userId);
        if (str_replace(' ', '', $where) == '') {
            $where = '';
        }
        $where = str_replace('where', ' ', $where);
        $db = new dbquery();
        $db->connect();

        if ($target == 'ALL' || $target == 'DOC') {
            $query = 'select res_id from ' . $view . ' where ' . $where;
        }
        if ($target == 'ALL' || $target == 'CLASS') {
            $query = 'select mr_aggregation_id from ' . $view
                   . ' where  '. $where;
        }

        $ok = $db->query($query, true);
        if (!$ok) {
            $res['TXT'] = _SYNTAX_ERROR_WHERE_CLAUSE;
            return $res;
        } else {
            $res['TXT'] = _SYNTAX_OK;
            $res['RESULT'] = true;
        }
        return $res;
    }

    /**
    * Process a where clause, using the process_where_clause methods of the
    * modules, the core and the apps
    *
    * @param  $whereClause string Where clause to process
    * @param  $userId string User identifier
    * @return string Proper where clause
    */
    public function process_security_where_clause($whereClause, $userId)
    {
        if (! empty($whereClause)) {
            $where = ' where ' . $whereClause;
            // Process with the core vars
            $where = $this->process_where_clause($where, $userId);
            // Process with the modules vars
            foreach (array_keys($_SESSION['modules_loaded']) as $key) {
                $pathModuleTools = $_SESSION['modules_loaded'][$key]['path']
                                   . "class" . DIRECTORY_SEPARATOR
                                   . "class_modules_tools.php";
                if (file_exists($pathModuleTools)) {
                    require_once($pathModuleTools);
                    if (class_exists($key)) {
                        $object = new $key;
                        if (method_exists(
                            $object, 'process_where_clause'
                        ) == true
                        ) {
                            $where = $object->process_where_clause(
                                $where, $userId
                            );
                        }
                    }
                }
            }

            $where = preg_replace('/, ,/', ',', $where);
            $where = preg_replace('/\( ?,/', '(', $where);
            $where = preg_replace('/, ?\)/', ')', $where);

            // Process with the apps vars
            require_once 'apps' . DIRECTORY_SEPARATOR
                . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class'
                . DIRECTORY_SEPARATOR . 'class_business_app_tools.php';
            $object = new business_app_tools();
            if (method_exists($object, 'process_where_clause')) {
                $where = $object->process_where_clause($where, $userId);
            }
            return $where;
        } else {
            return '';
        }
    }

    /**
    * Process a where clause with the core specific vars
    *
    * @param  $whereClause string Where clause to process
    * @param  $userId string User identifier
    * @return string Proper where clause
    */
    public function process_where_clause($whereClause, $userId)
    {
        $where = $whereClause;
        if (preg_match('/@user/', $whereClause)) {
            $where = str_replace(
                "@user", "'" . trim($userId) . "'", $whereClause
            );
        }
        return $where;
    }

    /**
    * Loads into session, the security parameters corresponding to the user
    * groups.
    *
    * @param  $userId string User Identifier
    */
    public function load_security($userId)
    {
        require_once 'apps/' . $_SESSION['config']['app_id']
            . '/security_bitmask.php';
        $tab['collections'] = array();
        $tab['security'] = array();
        $func = new functions();
        $db = new dbquery();
        $db->connect();

        if ($userId == "superadmin") {
            for ($i = 0; $i < count($_SESSION['collections']); $i ++) {
                $tab['security'][ $_SESSION['collections'][$i]['id']] = array();
                foreach (array_keys($_ENV['targets']) as $key) {
                    $tab['security'][ $_SESSION['collections'][$i]['id']][$key] = array(
                        'table'  => $_SESSION['collections'][$i]['table'],
                        'label_coll' => $_SESSION['collections'][$i]['label'],
                        'view'  => $_SESSION['collections'][$i]['view'],
                        'where' => " (1=1) ",
                        'securityBitmask' => MAX_BITMASK,
                    );
                }
                array_push(
                    $tab['collections'], $_SESSION['collections'][$i]['id']
                );
            }
        } else {
            $uc = new users_controler();
            $groups = $uc->getGroups($userId);

            $access = array();
            for ($i = 0; $i < count($groups); $i ++) {
                $tmp = $this->getAccessForGroup($groups[$i]['GROUP_ID']);
                for ($j = 0; $j < count($tmp);$j ++) {
                    array_push($access, $tmp[$j]);
                }
            }
            for ($i = 0; $i < count($access); $i ++) {
                // TO DO : vérifier les dates
                $startDate = $access[$i]->__get('mr_start_date');
                $stopDate = $access[$i]->__get('mr_stop_date');

                $bitmask = $access[$i] ->__get('rights_bitmask');
                $target = $access[$i]->__get('where_target');
                $collId = $access[$i]->__get('coll_id');
                $whereClause = $access[$i]->__get('where_clause');
                $whereClause = $this->process_security_where_clause(
                    $whereClause, $userId
                );
                $whereClause = str_replace('where', '', $whereClause);

                $ind = $this->get_ind_collection($collId);

                if (trim($whereClause) == "") {
                    $where = "-1";
                } else {
                    $where = "( " . $func->show_string($whereClause) . " )";
                }
                if (! in_array($collId, $tab['collections'])) {
                    $tab['security'][$collId] = array();

                    if ($target == 'ALL') {
                        foreach (array_keys($_ENV['targets']) as $key) {
                            $tab['security'][$collId][$key] = array(
                                'table'  => $_SESSION['collections'][$ind]['table'],
                                'label_coll'  => $_SESSION['collections'][$ind]['label'],
                                'view'  => $_SESSION['collections'][$ind]['view'],
                                'where'  => $where,
                                'securityBitmask' => $bitmask,
                            );
                        }
                    } else {
                        $tab['security'][$collId][$target] = array(
                            'table'  => $_SESSION['collections'][$ind]['table'],
                            'label_coll'  => $_SESSION['collections'][$ind]['label'],
                            'view'  => $_SESSION['collections'][$ind]['view'],
                            'where'  => $where,
                            'securityBitmask' => $bitmask,
                        );
                    }
                    array_push($tab['collections'], $collId);
                } else {
                    if (isset($tab['security'][$collId][$target])
                        && count($tab['security'][$collId][$target]) > 0
                    ) {
                        $tab['security'][ $collId][$target]['securityBitmask'] = set_right(
                            $tab['security'][ $collId][$target]['securityBitmask'],
                            $bitmask
                        );
                        $tab['security'][ $collId][$target]['where'] .= " or "
                            . $where;
                    } else if ($target == 'ALL') {
                        foreach (array_keys($_ENV['targets']) as $key) {
                            if (isset($tab['security'][$collId][$key])
                                && count($tab['security'][$collId][$key]) > 0
                            ) {
                                $tab['security'][ $collId][$target]['securityBitmask'] = set_right(
                                    $tab['security'][ $collId][$target]['securityBitmask'],
                                    $bitmask
                                );
                                $tab['security'][$collId][$key]['where'] .= " or "
                                    . $where;
                            } else {
                                $tab['security'][$collId][$key] = array(
                                    'table'  => $_SESSION['collections'][$ind]['table'],
                                    'label_coll'  => $_SESSION['collections'][$ind]['label'],
                                    'view'  => $_SESSION['collections'][$ind]['view'],
                                    'where'  => $where,
                                    'securityBitmask' => $bitmask,
                                );
                            }
                        }
                    } else {
                        $tab['security'][$collId][$target] = array(
                            'table'  => $_SESSION['collections'][$ind]['table'],
                            'label_coll'  => $_SESSION['collections'][$ind]['label'],
                            'view'  => $_SESSION['collections'][$ind]['view'],
                            'where'  => $where,
                            'securityBitmask' => $bitmask,
                        );
                    }
                }
            }
        }
        return $tab;
    }

    /**
    * Gets the indice of the collection in the  $_SESSION['collections'] array
    *
    * @param  $collId string  Collection identifier
    * @return integer Indice of the collection in the $_SESSION['collections']
    *           or -1 if not found
    */
    public function get_ind_collection($collId)
    {
        for ($i = 0; $i < count($_SESSION['collections']); $i ++) {
            if (trim($_SESSION['collections'][$i]['id']) == trim($collId)) {
                return $i;
            }
        }
        return -1;
    }


    /**
     * Give action bitmask for given $userId over given
     * object
     * @param varchar(32) $userId
     * @param bigint $objectId
     * @return bitmask
     */
    public function getActions($userId, $objectId, $objectType='aggregation')
    {
        $ctrl = new session_security_controler();
        // Select from security session table
        $sessionSec = $ctrl->get($userId);
        if ($sessionSec->__get('last_object_id') == $objectId) {
            return $sessionSec->__get('last_available_bitmask');
        } else {
            return $this->setActions($userId, $objectId, $objectType);
        }
    }

    /**
     * Update security session table with
     * bitmask, according with given user
     * and aggregation.
     * Return computed bitmask
     * @param varchar(32) $userId
     * @param bigint $objectId
     * @return bitmask
     */
    public function setActions($userId, $objectId, $objectType)
    {
        if ($userId == 'superadmin') {
            return MAX_BITMASK;
        }
        // Compute action bitmask
        $fullBitmask = 0;
        $uc = new users_controler();
        $groups = $uc->getGroups($userId);
        //print_r($groups);

        $fullWhere = "";
        for ($i = 0; $i < count($groups); $i ++) {
            $access = $this->getAccessForGroup($groups[$i]['GROUP_ID']);
            //var_dump($access);
            for ($j = 0; $j < count($access); $j ++) {
                $target = $access[$j]->__get('where_target');
                $collId = $access[$j]->__get('coll_id');
                $whereClause = $access[$j]->__get('where_clause');
                $whereClause = $this->process_security_where_clause(
                    $whereClause, $userId
                );
                $whereClause = str_replace('where', '', $whereClause);
                $bitmask = $access[$j]->__get('rights_bitmask');

                $ind = $this->get_ind_collection($collId);
                if (trim($whereClause) == "") {
                    $where = "-1";
                } else {
                    $where = "( " . $this->show_string($whereClause) . " )";
                }

                $query = '';
                if ($objectType == 'aggregation'
                    && ($target == 'CLASS' || $target == 'ALL')
                ) {
                    $query = "select mr_aggregation_id from "
                           . _CLASSIFICATION_SCHEME_VIEW . " where (" . $where
                           . ') ';
                    if (isset($objectId) && ! empty($objectId)) {
                        $query .= 'and mr_aggregation_id = ' . $objectId;
                    }
                } else if ($objectType == 'classification_scheme'
                    && ($target == 'CLASS' || $target == 'ALL')
                ) {
                    $query = "select mr_classification_scheme_id from "
                           . _CLASSIFICATION_SCHEME_VIEW . " where (" . $where
                           . ') and mr_classification_scheme_id = ' . $objectId;
                } else if ($objectType == 'doc'
                    && ($target == 'DOC' || $target == 'ALL')
                ) {
                    $query = "select res_id from "
                           . $_SESSION['collections'][$ind]['view'] . " where ("
                           . $where . ') and res_id = ' . $objectId;
                }
                //echo $query;
                $db = new dbquery();
                $db->connect();
                if (! empty($query)) {
                    $db->query($query);
                }
                if ($db->nb_result() > 0) {
                    if ($bitmask > 0) {
                        $fullBitmask = set_right($fullBitmask, $bitmask);
                    }

                    if (! empty($fullWhere)) {
                        $fullWhere .= " and (" . $where . ") ";
                    } else {
                        $fullWhere .= $where;
                    }
                }
            }
        }

        // Update security session table
        $func = new functions();
        $sessionSecurity = new session_security();
        $sessionSecurity->setArray(
            array(
                'user_id' => $func->protect_string_db($userId),
                'session_begin_date' => date("Y-m-d H:i"),
                'full_where_clause' => $func->protect_string_db($fullWhere),
                'last_available_bitmask' => $fullBitmask,
                'last_object_id' => $func->protect_string_db($objectId)
            )
        ); // TO DO : calculate the session_end_date
        $ctrl = new session_security_controler();
        $ctrl->save($sessionSecurity);

        return $fullBitmask;
    }
    
    /**
    * Check the where clause syntax
    *
    * @param  $whereClause string The where clause to check
    * @return bool true if the request is not secure, false otherwise
    */
    public function isUnsecureRequest($whereClause)
    {
        $search1 = '#\b(?:abort|alter|copy|create|delete|disgard|drop|'
                . 'execute|grant|insert|load|lock|move|reset|truncate|update)\b#i';
        preg_match($search1, $whereClause, $out);
        if (isset($out[0])) {
            $count = count($out[0]);
            if ($count == 1) {
                $find1 = true;
            } else {
                $find1 = false;
            }
        } else {
            $find1 = false;
        }
        return $find1;
    }
}
