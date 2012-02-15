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
* @brief  Contains the controler of the user object (create, save, modify)
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
    require_once 'core/core_tables.php' ;
    require_once 'core/class/users.php' ;
    require_once 'core/class/ObjectControlerAbstract.php';
    require_once 'core/class/ObjectControlerIF.php';
    require_once 'core/class/class_history.php';
} catch (Exception $e){
    echo $e->getMessage() . ' // ';
}

/**
* @brief  controler of the user object
*
*<ul>
*  <li>Get an user object from an id</li>
*  <li>Save in the database a user</li>
*  <li>Manage the operation on the users related tables in the database
*   (insert, select, update, delete)</li>
*</ul>
* @ingroup core
*/
class users_controler extends ObjectControler implements ObjectControlerIF
{
    /**
    * Returns an user object based on a user identifier
    *
    * @param  $userId string  User identifier
    * @param  $compWhere string  where clause arguments
    *               (must begin with and or or)
    * @param  $canBeDisabled bool if true gets the user even if it is
    *               disabled in the database (false by default)
    * @return user object with properties from the database or null
    */
    public function get($userId, $compWhere='', $canBeDisabled=false)
    {
        self::set_foolish_ids(array('user_id', 'docserver_location_id'));
        self::set_specific_id('user_id');
        $user = self::advanced_get($userId, USERS_TABLE, $compWhere);

        if (isset($user)
        	&& ($user->__get('status') == 'OK' 
        	|| $user->__get('status') == 'ABS')
        ) {
            return $user;
        } else {
            return null;
        }
    }

    /**
    * Returns in an array all the groups associated with a user (user_id,
    * group_id, primary_group and role)
    *
    * @param  $userId string  User identifier
    * @return Array or null
    */
    public function getGroups($userId)
    {
        $groups = array();
        if (empty($userId)) {
            return null;
        }

        self::$db = new dbquery();
        self::$db->connect();
        $func = new functions();
        $query = 'select uc.group_id, uc.primary_group, uc.role from '
               . USERGROUP_CONTENT_TABLE . ' uc, ' . USERGROUPS_TABLE
               . " u where uc.user_id = '"
               . $func->protect_string_db($userId)
               . "' and u.enabled = 'Y' and uc.group_id = u.group_id ";
        try{
            self::$db->query($query);
        } catch (Exception $e){
            echo _NO_USER_WITH_ID.' '.$userId.' // ';
        }

        while ($res = self::$db->fetch_object()) {
            array_push(
            	$groups, 
            	array(
            		'USER_ID' => $userId,
                    'GROUP_ID' => $res->group_id,
                    'PRIMARY' => $res->primary_group,
                    'ROLE' => $res->role,
                )
            );
        }
        self::$db->disconnect();
        return $groups;
    }


    /**
    * Saves in the database a user object
    *
    * @param  $user user object to be saved
    * @param  $groups Groups data,
    *            array( 'USER_ID'    => User Identifier,
    *                   'GROUP_ID'   => Group identifier,
    *                   'LABEL'      => Group label,
    *                   'PRIMARY'    => Y / N (Is the group,
    *                               the primary group for the user),
    *                   'ROLE'       => User role in the group (string)
    *                  )
    * @param  $mode Mode (add or up)
    * @param  $params More parameters,
    *           array('modules_services' => $_SESSION['modules_services']
    *                                       type array,
    *                 'log_user_up'      => 'true' / 'false':
    *                                       log user modification ,
    *                 'log_user_add'     => 'true' / 'false': log user addition,
    *                 'databasetype'     => Type of the database ('POSTGRESQL'),
    *                 'userdefaultpassword' => Default password for user,
    *                 'manageGroups'     => If true manage groups for the user
    *                                       )
    * @return array (   'status' => 'ok' / 'ko',
    *                   'value'  => User identifier or empty in case of error,
    *                   'error'  => Error message, defined only in case of error
    */
    public function save($user, $groups=array(), $mode='', $params=array())
    {
        $control = array();
        // If user not defined or empty, return an error
        if (! isset($user) || empty($user)) {
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _USER_EMPTY,          
            );
            return $control;
        }
        // If mode not up or add, return an error
        if (! isset($mode) || empty($mode) 
        	|| ($mode <> 'add' && $mode <> 'up' )
        ) {
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _MODE . ' ' ._UNKNOWN,
            );
            return $control;
        }
        $user = self::_isAUser($user);
        self::set_foolish_ids(array('user_id', 'docserver_location_id'));
        self::set_specific_id('user_id');

        // Data checks
        $control = self::_control($user, $groups, $mode, $params);

        if ($control['status'] == 'ok') {
        	if (! isset($params['manageGroups']) 
        		|| $params['manageGroups'] == true
        	) {
            	self::cleanUsergroupContent($user->user_id);
            	self::loadDbUsergroupContent($user->user_id, $groups);
        	}
            $core = new core_tools();

            $_SESSION['service_tag'] = 'user_' . $mode;
            if (isset($params['modules_services'])) {
	            $core->execute_modules_services(
	            	$params['modules_services'], 'users_add_db', 'include'
	            );
            }
            if ($mode == 'up') {
                //Update existing user
                if (self::_update($user)) {
                    $control = array(
                    	'status' => 'ok',
                        'value'  => $user->user_id,
                    );
                    //log
                    if ($params['log_user_up'] == 'true') {
                        $history = new history();
                        $history->add(
                        	USERS_TABLE, $user->user_id, 'UP', 'usersup',
                            _USER_UPDATE . ' : ' . $user->user_id,
                            $params['databasetype']
                        );
                    }
                } else {
                    $control = array(
                    	'status' => 'ko',
                        'value'  => '',
                        'error'  => _PB_WITH_USER_UPDATE,
                    );
                }
            } else { //mode == add
                if (self::_insert($user)) {
                    $control = array(
                    	'status' => 'ok',
                        'value'  => $user->user_id,
                    );
                    //log
                    if ($params['log_user_add'] == 'true') {
                        $history = new history();
                        $history->add(
                        	USERS_TABLE, $user->user_id, 'ADD', 'usersadd',
                            _USER_ADDED . ' : ' . $user->user_id,
                            $params['databasetype']
                        );
                    }
                } else {
                    $control = array(
                    	'status' => 'ko',
                        'value'  => '',
                        'error'  => _PB_WITH_USER,
                    );
                }
            }
        }
        unset($_SESSION['service_tag']);
        return $control;
    }

    /**
    * Fill a user object with an object if it's not a user
    *
    * @param  $object ws users object
    * @return object users
    */
    private function _isAUser($object)
    {
        if (get_class($object) <> 'users') {
            $func = new functions();
            $userObject = new users();
            $array = array();
            $array = $func->object2array($object);
            foreach (array_keys($array) as $key) {
                $userObject->$key = $array[$key];
            }
            return $userObject;
        } else {
            return $object;
        }
    }

    /**
    * _control the data of user object
    *
    * @param  $user user object
    * @param  $groups Groups data,
    *               array( 'USER_ID'       => User Identifier,
    *                      'GROUP_ID'      => Group identifier,
    *                      'LABEL'         => Group label,
    *                      'PRIMARY'       => Y / N (Is the group, the primary
    *                                         group for the user),
    *                      'ROLE'          => User role in the group (string)
    *                )
    * @param  $mode Mode (add or up)
    * @param  $params More parameters,
    *               array('modules_services'   => $_SESSION['modules_services']
    *                                             type array,
    *                     'log_user_up'        => 'true' / 'false': log user
    *                                             modification ,
    *                     'log_user_add'       => 'true' / 'false': log user
    *                                             addition ,
    *                     'databasetype'       => Type of the database
    *                                             ('POSTGRESQL', ...),
    *                     'userdefaultpassword' => Default password for user,
    *                     'manageGroups'     => If true manage groups for the user
    *                )
    * @return array (  'status' => 'ok' / 'ko',
    *                  'value'  => User identifier or empty in case of error,
    *                  'error'  => Error message, defined only in case of error
    *               )
    */
    private function _control($user, $groups, $mode, $params=array())
    {
        $error = "";
        $f = new functions();
        $user->user_id = $f->protect_string_db(
        	$f->wash($user->user_id, 'no', _THE_ID, 'yes', 0, 32)
       	);

        if ($mode == 'add') {
            $user->password = $f->protect_string_db(
            	md5($params['userdefaultpassword'])
            );

            if (self::userExists($user->user_id)) {
                $error .= _USER . ' ' . _ALREADY_EXISTS . '#';
            }
        }

        $user->firstname = $f->protect_string_db(
        	$f->wash($user->firstname, 'no', _THE_FIRSTNAME, 'yes', 0, 255)
        );
        $user->lastname = $f->protect_string_db(
        	$f->wash($user->lastname, 'no', _THE_LASTNAME, 'yes', 0, 255)
        );

        if (isset($user->department) && ! empty($user->department)) {
            $user->department = $f->protect_string_db(
            	$f->wash($user->department, 'no', _DEPARTMENT, 'yes', 0, 50)
            );
        }

        if (isset($user->phone) && ! empty($user->phone)) {
            $user->phone = $f->protect_string_db(
            	$f->wash($user->phone, 'no', _PHONE, 'yes', 0, 15)
            );
        }

        if (isset($user->loginmode) && ! empty($user->loginmode)) {
            $user->loginmode  = $f->protect_string_db(
            	$f->wash($user->loginmode, 'no', _LOGIN_MODE, 'yes', 0, 50)
            );
        }

        $user->mail = $f->protect_string_db(
        	$f->wash($user->mail, 'mail', _MAIL, 'yes', 0, 255)
        );

        if ($user->user_id <> 'superadmin' && (! isset($params['manageGroups']) 
        	|| $params['manageGroups'] == true)
        ) {
            $primarySet = false;
            for ($i = 0; $i < count($groups); $i ++) {
                if ($groups[$i]['PRIMARY'] == 'Y') {
                    $primarySet = true;
                    break;
                }
            }
            if ($primarySet == false) {
                $error .= _PRIMARY_GROUP . ' ' . _MANDATORY . '#';
            }
        }

        $_SESSION['service_tag'] = 'user_check';
        $core = new core_tools();
        if (isset($params['modules_services'])) {
       		$core->execute_modules_services(
       			$params['modules_services'], 'user_check', 'include'
       		);
        }
        $error .= $_SESSION['error'];
        //TODO:rewrite wash to return errors without html and not in the session
        $error = str_replace("<br />", "#", $error);
        $return = array();
        if (! empty($error)) {
        	$return = array(
            	'status' => 'ko',
                'value'  => $user->user_id,
                'error'  => $error,
            );
        } else {
            $return = array(
            	'status' => 'ok',
                'value'  => $user->user_id,
            );
        }
        unset($_SESSION['service_tag']);
        return $return;
    }

    /**
    * inserts in the database (users table) a user object
    *
    * @param  $user user object
    * @return bool true if the insertion is complete, false otherwise
    */
    private function _insert($user)
    {
        return self::advanced_insert($user);
    }

    /**
    * Updates a user in the database (users table) with a user object
    *
    * @param  $user user object
    * @return bool true if the update is complete, false otherwise
    */
    private function _update($user)
    {
        return self::advanced_update($user);
    }

    /**
    * Deletes in the database (users related tables) a given user (user_id)
    *
    * @param  $user User Object
    * @return bool true if the deletion is complete, false otherwise
    */
    public function delete($user, $params=array())
    {
        $control = array();
        if (! isset($user) || empty($user)) {
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _USER_EMPTY,
            );
            return $control;
        }
        $user = self::_isAUser($user);
        if (! self::userExists($user->user_id)) {
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _USER_NOT_EXISTS,
            );
            return $control;
        }

        self::$db = new dbquery();
        self::$db->connect();
        $func = new functions();
        $query = 'update ' . USERS_TABLE . " set status = 'DEL' where user_id='"
               . $func->protect_string_db($user->user_id) . "'";
        // Logic deletion only , status becomes DEL to keep the user data

        try{
            self::$db->query($query);
            $ok = true;
        } catch (Exception $e){
            $control = array(
            	'status' => 'ko',
                'value' => '',
                'error' => _CANNOT_DELETE_USER_ID . ' ' . $user->user_id,
            );
            $ok = false;
        }

        self::$db->disconnect();
        if ($ok) {
            $control = self::cleanUsergroupContent($user->user_id);
        }

        if ($control['status'] == 'ok') {
            if (isset($params['log_user_del'])
                && ($params['log_user_del'] == "true"
                    || $params['log_user_del'] == true)
            ) {
                $history = new history();
                $history->add(
                	USERS_TABLE, $user->user_id, 'DEL', 'usersdel',
                    _DELETED_USER . ' : ' . $user->lastname . ' '
                    . $user->firstname . ' (' . $user->user_id . ')',
                    $params['databasetype']
                );
            }
        }
        return $control;
    }

    /**
    * Cleans the usergroup_content table in the database from a given user
    *   (user_id)
    *
    * @param  $userId string  User identifier
    * @return bool true if the cleaning is complete, false otherwise
    */
    public function cleanUsergroupContent($userId)
    {
        $control = array();
        if (! isset($userId) || empty($userId)) {
            $control = array(
            	'status' => 'ko',
                'value' => '',
                'error' => _USER_ID_EMPTY,
            );
            return $control;
        }

        self::$db = new dbquery();
        self::$db->connect();
        $func = new functions();
        $query = 'delete from ' . USERGROUP_CONTENT_TABLE . " where user_id='"
               . $func->protect_string_db($userId) . "'";
        try{
            self::$db->query($query);
            $control = array(
            	'status' => 'ok',
                'value'  => $userId,
            );
        } catch (Exception $e){
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _CANNOT_CLEAN_USERGROUP_CONTENT . ' ' . $userId,
            );
        }
        self::$db->disconnect();
        return $control;
    }

    /**
    * Asserts if a given user (user_id) exists in the database
    *
    * @param  $userId String User identifier
    * @return bool true if the user exists, false otherwise
    */
    public function userExists($userId)
    {
        if (! isset($userId) || empty($userId)) {
            return false;
        }

        self::$db = new dbquery();
        self::$db->connect();
        $func = new functions();
        $query = 'select user_id from ' . USERS_TABLE . " where user_id = '"
               . $func->protect_string_db($userId) . "'";

        try{
            self::$db->query($query);
        } catch (Exception $e){
            echo _UNKNOWN . ' ' . _USER . ' ' . $userId . ' // ';
        }

        if (self::$db->nb_result() > 0) {
            self::$db->disconnect();
            return true;
        }
        self::$db->disconnect();
        return false;
    }


    /**
    * Disables a given user
    *
    * @param  $user user object
    * @return bool true if the disabling is complete, false otherwise
    */
    public function disable($user, $params=array())
    {
        $control = array();
        if (! isset($user) || empty($user)) {
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _USER_EMPTY,
            );
            return $control;
        }
        $user = self::_isAUser($user);
        self::set_foolish_ids(array('user_id', 'docserver_location_id'));
        self::set_specific_id('user_id');

        if (self::advanced_disable($user)) {
            $control = array(
            	'status' => 'ok',
                'value'  => $user->user_id,
            );
            if (isset($params['log_user_disabled'])
                && ($params['log_user_disabled'] == 'true'
                    || $params['log_user_disabled'] == true)
            ) {
                $history = new history();
                $history->add(
                	USERS_TABLE, $user->user_id, 'BAN', 'usersban',
                    _SUSPENDED_USER . ' : ' . $user->lastname . ' '
                    . $user->firstname . ' (' . $user->user_id . ')',
                    $params['databasetype']
                );
            }
        } else {
            $control = array(
            	'status' => 'ko',
                'value'  => '',
                'error'  => _PB_WITH_USER_ID,
            );
        }
        return $control;
    }

    /**
    * Enables a given user
    *
    * @param  $user user object
    * @return bool true if the enabling is complete, false otherwise
    */
    public function enable($user, $params=array())
    {
        $control = array();
        if (! isset($user) || empty($user)) {
            $control = array(
            	'status' => 'ko',
                'value' => '',
                'error' => _USER_EMPTY,
            );
            return $control;
        }
        $user = self::_isAUser($user);
        self::set_foolish_ids(array('user_id', 'docserver_location_id'));
        self::set_specific_id('user_id');

        if (self::advanced_enable($user)) {
            $control = array(
            	'status' => 'ok',
                'value' => $user->user_id,
            );
            if (isset($params['log_user_enabled'])
                && ($params['log_user_enabled'] == 'true'
                    || $params['log_user_enabled'] == true)
            ) {
                $history = new history();
                $history->add(
                	USERS_TABLE, $user->user_id, 'VAL', 'usersval',
                    _AUTORIZED_USER .' : ' . $user->lastname . ' '
                    . $user->firstname . ' (' . $user->user_id . ')',
                    $params['databasetype']
                );
            }
        } else {
            $control = array(
            	'status' => 'ko',
                'value' => '',
                'error' => _PB_WITH_USER_ID,
            );
        }
        return $control;
    }

    /**
    * Loads into the usergroup_content table the given data for a given user
    *
    * @param  $userId String User identifier
    * @param  $array Array
    * @return bool true if the loadng is complete, false otherwise
    */
    public function loadDbUsergroupContent($userId, $array)
    {
        if (! isset($userId) || empty($userId)) {
            return false;
        }
        if (! isset($array) || count($array) == 0) {
            return false;
        }
        self::$db = new dbquery();
        self::$db->connect();
        $func = new functions();
        $ok = true;
        for ($i = 0; $i < count($array); $i ++) {
            if ($ok) {
                $query = 'insert INTO ' . USERGROUP_CONTENT_TABLE
                       . " (user_id, group_id, primary_group, role) VALUES ('"
                       . $func->protect_string_db($userId) . "', '"
                       . $func->protect_string_db($array[$i]['GROUP_ID'])
                       . "', '". $func->protect_string_db($array[$i]['PRIMARY'])
                       . "', '" . $func->protect_string_db($array[0]['ROLE'])
                       . "')";
                try{
                    self::$db->query($query);
                    $ok = true;
                } catch (Exception $e){
                    $ok = false;
                }
            } else {
                break;
            }
        }
        self::$db->disconnect();
        return $ok;
    }
    
    public function changePassword($userId, $newPassword)
    {
     	if (! isset($userId) || empty($userId) || ! isset($newPassword) 
     		|| empty($newPassword)
     	) {
            return false;
        }
        self::$db = new dbquery();
        self::$db->connect();
        $func = new functions();
        $query = 'update ' . USERS_TABLE. " set password = '" 
        	. $func->protect_string_db($newPassword) 
        	. "', change_password = 'Y' where user_id = '".$userId."'";
       	return self::$db->query($query, true);
    }
}
