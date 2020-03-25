<?php

/*
*   Copyright 2008-2017 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Contains the functions to manage content_management directory and expiration
*
* @file
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup content_management
*/

require_once 'core/class/class_functions.php';
require_once 'core/class/class_db_pdo.php';
require_once 'core/class/docservers_controler.php';
require_once 'core/class/class_security.php';
require_once 'core/core_tables.php';

abstract class content_management_tools_Abstract
{
    //Parameters
    protected $extensions_xml_path = 'xml/extensions.xml';
    protected $programs_xml_path = 'xml/programs.xml';
    protected $parameter_id  = 'content_management_reservation';
    protected $templateMasterPath = 'modules/templates/templates_src/';
    //Variables
    protected $db;

    public function __construct()
    {
        if (!isset($_SESSION) or count($_SESSION) == 0) {
            return null;
        }

        $this->db = new Database();
        //TODO: PUT IT AN CONFIG FILE WITH 30
        $_SESSION['config']['content_management_reserved_time'] = 30;
        if (!is_dir('modules/content_management/tmp/')) {
            mkdir('modules/content_management/tmp/');
        }
    }
    
    public function getCmParameters()
    {
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom/'
            . $_SESSION['custom_override_id']
            . '/modules/content_management/xml/content_management_features.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom/'
                . $_SESSION['custom_override_id']
                . '/modules/content_management/xml/content_management_features.xml';
        } else {
            $path = $_SESSION['config']['corepath']
                . 'modules/content_management/xml/content_management_features.xml';
        }
        $cMFeatures = array();
        if (file_exists($path)) {
            $func = new functions();
            $cMFeatures = $func->object2array(
                simplexml_load_file($path)
            );
        } else {
            $cMFeatures['CONFIG']['psExecMode'] = 'KO';
            $cMFeatures['CONFIG']['userMaarchOnClient'] = '';
            $cMFeatures['CONFIG']['userPwdMaarchOnClient'] = '';
        }
        return $cMFeatures;
    }

    /**
    * Returns who reserved the resource
    *
    * @param  string $objectTable res table, attachment table, model table, ...
    * @param  bigint $objectId id of the object res_id, model_id, ...
    * @return array the user who reserved the resource, else false
    */
    public function isReservedBy($objectTable, $objectId)
    {
        $timeLimit = $this->computeTimeLimit();
        $charTofind = $this->parameter_id . '#%#' . $objectTable . '#' . $objectId;

        $query = "select id from " . PARAM_TABLE . " where id like (?) and param_value_int > ?";

        $stmt = $this->db->query($query, array($charTofind, $timeLimit));
        
        if ($res = $stmt->fetchObject()) {
            $arrayUser = array();
            $arrayUser = explode("#", $res->id);
            if ($arrayUser[1] <> '') {
                $query = "select user_id, lastname, firstname "
                    . "from " . USERS_TABLE . " where user_id = ? and enabled = 'Y'";
                
                $stmt = $this->db->query($query, array($arrayUser[1]));
                
                $arrayReturn = array();
                if ($resUser = $stmt->fetchObject()) {
                    $arrayReturn['fullname'] = $resUser->firstname . ' '
                        . $resUser->lastname;
                    $arrayReturn['user_id'] = $resUser->user_id;
                } else {
                    $arrayReturn['fullname'] = 'empty';
                }
                $arrayReturn['status'] = 'ok';
                return $arrayReturn;
            } else {
                $arrayReturn['status'] = 'ko';
            }
        } else {
            $arrayReturn['status'] = 'ko';
        }
        return $arrayReturn;
    }

    /**
    * Close the content_management reservation
    *
    * @param string $CMId content_management id
    * @return nothing
    */
    public function closeReservation($CMId)
    {
        $query = "delete from " . PARAM_TABLE
            . " where id = ?";
        $stmt = $this->db->query($query, array($CMId));
    }

    /**
    * Reserved the object for content_management
    * Add an expiration date of the content_management reservation for the connected user
    *
    * @param  string $objectTable the res table
    * @param  string $objectId the res_id
    * @param  string $CMId the content_management id
    * @param  string $userId the content_management id
    * @return string the reservation id
    */
    public function reserveObject($objectTable, $objectId, $userId)
    {
        $timeLimit = $this->computeTimeLimit() + (
            $_SESSION['config']['content_management_reserved_time'] * 60
        );
        //If exists Delete
        $charTofind = $this->parameter_id . '#' . $userId . '#' . $objectTable
                    . '#' . $objectId;
        $query = "delete from " . PARAM_TABLE
               . " where id = ?";
        $stmt = $this->db->query($query, array($charTofind));
        $query = "insert into " . PARAM_TABLE
               . " (id, param_value_int)"
               . " values(?, ?)";
        $stmt = $this->db->query($query, array($charTofind, $timeLimit));
        return $charTofind;
    }

    /**
    * Delete the resource in the tmp content_management dir if necessary
    *
    * @return nothing
    */
    public function deleteExpiredCM()
    {
        $timeLimit = $this->computeTimeLimit();
        $query = "delete from " . PARAM_TABLE
            . " where param_value_int < ? "
            . " and id like ? ";
        $stmt = $this->db->query($query, array($timeLimit, $this->parameter_id . '%'));
    }
    
    /**
    * Delete the resource for the disconnected user
    *
    * @return nothing
    */
    public function deleteUserCM()
    {
        $query = "delete from " . PARAM_TABLE
            . " where id like ?";
        $stmt = $this->db->query(
            $query,
            array('content_management_reservation#'
            . $_SESSION['user']['UserId'] . '%')
        );
    }

    /**
    * Compute the time limit for a content_management session
    *
    * @return string the time limit in timestamp
    */
    public function computeTimeLimit()
    {
        $timeLimit = mktime(
            date('H'),
            date('i'),
            date('s'),
            date('m'),
            date('d'),
            date('Y')
        );
        return $timeLimit;
    }
}
