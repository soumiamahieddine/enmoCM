<?php

/*
*   Copyright 2008-2012 Maarch
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
require_once 'core/class/class_db.php';
require_once 'core/class/docservers_controler.php';
require_once 'core/class/class_security.php';
require_once 'core/core_tables.php';

class content_management_tools
{
    //Parameters
    private $extensions_xml_path = 'xml/extensions.xml';
    private $programs_xml_path = 'xml/programs.xml';
    private $parameter_id  = 'content_management_reservation';
    private $templateMasterPath = 'modules/templates/templates_src/';
    //Variables
    private $db;

    public function __construct()
    {
        if (!isset($_SESSION) OR count($_SESSION) == 0)
            return null;

        $this->db = new dbquery();
        $this->db->connect();
        //TODO: PUT IT AN CONFIG FILE WITH 30
        $_SESSION['config']['content_management_reserved_time'] = 30;
        if (!is_dir('modules/content_management/tmp/')) {
            mkdir('modules/content_management/tmp/');
        }
    }

    public function get_application($userId, $format)
    {
        $xml_programs = DOMDocument::load($this->programs_xml_path);
        $xp_xml_programs = new domxpath($xml_programs);
        //Is an application definied for the format ?
        $this->db->query("SELECT NAME, PATH
                    FROM ".$_SESSION['tablename']['ext_applications']."
                    WHERE USER_ID = '".$userId."'
                    AND FORMAT = '".$format."'");
        if ($res2 = $this->db->fetch_object()) {
            $return = array("APP_NAME" => $res2->NAME,
                            "APP_PATH" => $res2->PATH);
        } else {
            $req_prog = $xp_xml_programs->query("//APPLICATION[FORMAT='".$format."']");
            if ($req_prog->length > 0) {
                $return = array("APP_NAME" => $req_prog->item(0)->getAttribute("NAME"),
                                "APP_PATH" => $req_prog->item(0)->getAttribute("PATH"));
            } else {
                return _NO_APPLICATION_FORMAT;
            }
        }
        //print_r($return);exit;
        return $return;
    }

    /**
    * Returns who reserved the resource
    *
    * @param  string $objectTable res table, attachement table, model table, ...
    * @param  bigint $objectId id of the object res_id, model_id, ...
    * @return array the user who reserved the resource, else false
    */
    public function isReservedBy($objectTable, $objectId)
    {
        $timeLimit = $this->computeTimeLimit();
        $charTofind = $this->parameter_id . '#%#' . $objectTable . '#' . $objectId;
        $query = "select id from " . PARAM_TABLE . " where id like '"
            . $charTofind . "' and param_value_int > " . $timeLimit;
        //return $query;
        $this->db->query($query);
        if ($res = $this->db->fetch_object()) {
            $arrayUser = array();
            $arrayUser = explode("#", $res->id);
            if ($arrayUser[1] <> '') {
                $query = "select user_id, lastname, firstname "
                    . "from " . USERS_TABLE . " where user_id = '"
                    . $arrayUser[1] . "' and enabled = 'Y'";
                //return $query;
                $this->db->query($query);
                $arrayReturn = array();
                if ($resUser = $this->db->fetch_object()) {
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
            . " where id = '" . $CMId . "'";
        $this->db->query($query);
    }

    /**
    * Update the expiration date of the content_management reservation for the connected user
    *
    * @param  string $CMId the content_management id
    * @param  string $userId the content_management id
    * @return nothing
    */
    public function updateExpiryDate($CMId, $userId)
    {
        $timeLimit = $this->computeTimeLimit() + (
            $_SESSION['config']['content_management_reserved_time'] * 60
        );
        $charTofind = $this->parameter_id . '#' . $userId . '%';
        $query = "update " . PARAM_TABLE
               . " set param_value_int = " . $timeLimit
               . " where id like '" . $charTofind . "'"
               . " and param_value_string = '" . $CMId . "'";
        $this->db->query($query);
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
               . " where id = '" . $charTofind . "'";
        $this->db->query($query);
        $query = "insert into " . PARAM_TABLE
               . " (id, param_value_int)"
               . " values('" . $charTofind . "', " . $timeLimit . ")";
        $this->db->query($query);
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
        /*$this->db->query("select param_value_string from " . PARAM_TABLE
            . " where param_value_int < " . $timeLimit . " and id like '"
            . $this->parameter_id . "%'"
        );
        while ($res = $this->db->fetch_object()) {
            if ($res->param_value_string <> '') {
                $this->deleteDirectory(
                    'modules/content_management/tmp/' . $res->param_value_string
                );
            }
        }*/
        $this->db->query("delete from " . PARAM_TABLE
            . " where param_value_int < " . $timeLimit
            . " and id like '" . $this->parameter_id . "%'"
        );
    }

    /**
    * Delete the content_management tmp if necessary
    *
    * @param string $dir path to the tmp dir
    * @return nothing
    */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!$this->deleteDirectory($dir . "/" . $item)) {
                    chmod($dir . "/" . $item, 0777);
                    if (!$this->deleteDirectory($dir . "/" . $item)) return false;
                };
            }
            return rmdir($dir);
    }

    /**
    * Returns time before expiration of the content_management reservation
    *
    * @param  string $CMId the content_management id
    * @return bigint the time in secon before expiration
    */
    public function timeBeforeExpiration($CMId)
    {
        $now = $this->computeTimeLimit();
        $charTofind = $this->parameter_id . '%';
        $query = "select param_value_int as time"
               . " from " . PARAM_TABLE
               . " where id like '" . $charTofind . "'"
               . " and param_value_string = '" . $CMId . "'";
        $this->db->query($query);
        if ($res = $this->db->fetch_object()) {
            $secBeforeExpiration = $res->time - $now;
            if ($secBeforeExpiration < 0)  {
                return 0;
            } else {
                return $secBeforeExpiration;
            }
        } else {
            return 0;
        }
    }

    /**
    * Returns the program to update the resource with content_management
    *
    * @param  string $mimeType mime type of the resource
    * @return array the program and status ok if mime type allowed for content_management
    */
    public function isMimeTypeAllowedForCM($mimeType, $ext) {
        $typeState = 'ko';
        $programPath = '';
        if ($mimeType <> '' && $ext <> '') {
            $path = $_SESSION['config']['corepath'] . 'custom/'
                  . $_SESSION['custom_override_id'] . '/apps/'
                  . $_SESSION['config']['app_id'] . '/xml/extensions.xml';
            if (!file_exists($path)) {
                $path =  $_SESSION['config']['corepath'] . '/apps/'
                      . $_SESSION['config']['app_id'] . '/xml/extensions.xml';
            }
            $xmlconfig = simplexml_load_file($path);
            $extList = array();
            $i = 0;
            foreach ($xmlconfig->FORMAT as $FORMAT) {
                $extList[$i] = array(
                    'name' => (string) $FORMAT->name,
                    'mime' => (string) $FORMAT->mime,
                    'web_dav_update' => (string) $FORMAT->web_dav_update,
                    'default_program' => (string) $FORMAT->default_program,
                );
                $i++;
            }
            for ($i=0;$i<count($extList);$i++) {
                if (
                    $extList[$i]['mime'] == $mimeType
                    && strtolower($extList[$i]['name']) == strtolower($ext)
                    && strtolower($extList[$i]['web_dav_update']) == 'true'
                ) {
                    $typeState = 'ok';
                    $programPath = $extList[$i]['default_program'];
                    break;
                }
            }
        }
        $arrayReturn = array(
            'status' => $typeState,
            'programPath' => $programPath,
        );
        return $arrayReturn;
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
