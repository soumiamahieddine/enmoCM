<?php

/*
*   Copyright 2012 Maarch
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @defgroup content_management content_management Module
*/

/**
* @brief   content_management web service interaction with the java applet
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup content_management
*/
//manage session
include_once('../../core/init.php');

//Create XML
function createXML($root_name,$parameters) 
{
    global $debug, $debug_file;
    $r_xml = new DomDocument("1.0","UTF-8");
    $r_root_node = $r_xml->createElement($root_name);
    $r_xml->appendChild($r_root_node);
    if (is_array($parameters)) {
        foreach ($parameters as $k_par => $d_par) {
            $node = $r_xml->createElement($k_par,$d_par);
            $r_root_node->appendChild($node);
        }
    } else {
        $r_root_node->nodeValue = $parameters;
    }
    if ($debug) {
        $r_xml->save($debug_file);
    }
    header("content-type: application/xml");
    echo $r_xml->saveXML();
    $text = $r_xml->saveXML();
    $inF = fopen('wsresult.log','a');
    fwrite($inF, $text);
    fclose($inF);
    exit;
}

//parameters
$letterbox_root_dir = "";
$debug = false;
$debug_file = "content_management.log";
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
require_once('modules/content_management/class/class_content_management_tools.php');
$coreTools = new core_tools();
$coreTools->load_lang();
////////////////////////////  DEBUG  //////////////////////////////
file_put_contents(dirname( __FILE__ ) . 'log.txt',
    date("Y-m-d H:i:s")."\n:".var_export($_REQUEST,true)
    ."\n------------------------------------------------------\n",FILE_APPEND
);
///////////////////////////////////////////////////////////////////
//Vérification globale des paramètres
if (
    !(
        (
            isset($_REQUEST['action']) &&  $_REQUEST['action'] == "open" &&
            isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) &&
            isset($_REQUEST['password']) && !empty($_REQUEST['password']) &&
            isset($_REQUEST['res_table']) && !empty($_REQUEST['res_table']) &&
            isset($_REQUEST['res_id']) && is_numeric($_REQUEST['res_id'])
        )
        OR
        (
            isset($_REQUEST['action']) &&  $_REQUEST['action'] == "close" &&
            isset($_REQUEST['content_management_id']) && !empty($_REQUEST['content_management_id']) &&
            isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) &&
            isset($_REQUEST['password']) && !empty($_REQUEST['password'])
        )
        OR
        (
            isset($_REQUEST['action']) &&  $_REQUEST['action'] == "save" &&
            isset($_REQUEST['content_management_id']) && !empty($_REQUEST['content_management_id'])
        )
        OR
        (
            isset($_REQUEST['action']) &&  $_REQUEST['action'] == "time" &&
            isset($_REQUEST['content_management_id']) && !empty($_REQUEST['content_management_id'])
        )
    )
) {
    createXML("ERROR",_ERROR_CALL_WS);
}

//////////////////////////////////////////////////////////////////
//Connection Base de données
$db = new dbquery();
$db->connect();
//////////////////////////////////////////////////////////////////
$queryLogin = "select count(*) AS login_ok from users 
    where user_id = '" . $_REQUEST['user_id'] . "' and enabled = 'Y'";
$db->query($queryLogin);
//////////////////////////////////////////////////////////////////
if (
    $_REQUEST['action'] != "time" 
    && $_REQUEST['action'] != "save" 
    && $res = $db->fetch_object()
) {
    if ($res->login_ok != 1) {
        createXML("ERROR",_ERROR_LOGIN_WS);
    }
}

//updated by lgi
if (
    isset($_SESSION['m_admin']['template']) 
    && isset($_SESSION['m_admin']['template']['ID']) 
    && $_SESSION['m_admin']['template']['ID'] <> ''
) {
    $_SESSION['m_admin']['template']['on_progress'] = true;
    //Edition d'un nouveau document
    if ($_REQUEST['action'] == "open") {
        $wd = new content_management_tools();
        $wd->deleteExpiredCM();
        $wd = new content_management_tools();
        $result = $wd->mountCMCreateTemplate(
            $_SESSION['m_admin']['template']['ID'], 
            $_REQUEST['user_id']
        );
        //createXML('ERROR', $result);
        if (is_array($result) && count($result) > 0) {
            $result['APP_NAME'] = '';
            //RESERVED THE RESPONSE
            $wd->addExpiryDate(
                'templates', 
                $_SESSION['m_admin']['template']['ID'], 
                $result['ID'], 
                $_REQUEST['user_id']
            );
            createXML('SUCCESS', $result);
        } else {
            createXML('ERROR', constant($result));
        }
    }
    //Fin edition d'un nouveau document
    if ($_REQUEST['action'] == "save") {
        $wd = new content_management_tools();
        $result = $wd->save($_REQUEST['content_management_id']);
        if ($result === true) {
            createXML('SUCCESS', '');
        } else {
            createXML('ERROR', constant($result));
        }
    }
    //Fin edition d'un nouveau document
    if ($_REQUEST['action'] == "close") {
        $wd = new content_management_tools();
        $wd->deleteExpiredCM();
        $wd->closeCM($_REQUEST['content_management_id']);
        $result["APP_NAME"] = "";
        createXML("SUCCESS", "");
    }
    //Temps en seconde avant expiration du delai de reservation
    if ($_REQUEST['action'] == "time") {
        $wd = new content_management_tools();
        $result["TIME"] = $wd->timeBeforeExpiration($_REQUEST['content_management_id']);
        createXML("SUCCESS", $result);
    }
} else {
    //Edit a resource
    if ($_REQUEST['action'] == "open") {
        $wd = new content_management_tools();
        $wd->deleteExpiredCM();
        //Check if the resource is not reserved
        //$wd = new content_management_tools();
        $reservedBy = array();
        $reservedBy = $wd->isReservedBy(
            $_REQUEST['res_table'],
            $_REQUEST['res_id']
        );
        //createXML('ERROR', 'ici ' . $reservedBy['fullname']);
        if (
            $reservedBy !== false
             && $reservedBy['fullname'] != $_REQUEST['user_id']
        ) {
            if ($reservedBy['fullname'] <> 'empty') {
                createXML(
                    'ERROR',
                    _RESPONSE_ALREADY_RESERVED . ' ' . _BY . ' : ' 
                    . $reservedBy['fullname']
                );
            } else {
                createXML('ERROR', _RESPONSE_ALREADY_RESERVED);
            }
        }
        $result = $wd->mountCM(
            $_REQUEST['user_id'],
            $_REQUEST['res_table'],
            $_REQUEST['res_id']
        );
        //createXML('ERROR', $result['status']);
        if ($result['status'] == 'ok') {
            //updated by lgi for oo_generate
            //$result['APP_NAME'] = _LAUNCH_APPLICATION." : ".$result['APP_NAME'];
            //$result['APP_NAME'] = '';
            //$result['APP_PATH'] = $result['programPath'];
            //reserved the res for content_management
            $wd->addExpiryDate(
                $_REQUEST['res_table'],
                $_REQUEST['res_id'],
                $result['ID'],
                $_REQUEST['user_id']
            );
            //createXML('ERROR', $wd->closeCM($result['ID']));
            createXML('SUCCESS', $result);
        } else {
            createXML('ERROR', constant($result['status']));
        }
    }
    //Fin edition d'un nouveau document
    //if ($_REQUEST['action'] == "save") {
    //    $wd = new content_management_tools();
    //    $result = $wd->save($_REQUEST['content_management_id']);
    //    if ($result === true) {
    //        createXML("SUCCESS","");
    //    } else {
    //        createXML("ERROR",constant($result));
    //    }
    //}
    //Fin edition d'un nouveau document
    if ($_REQUEST['action'] == "close") {
        $wd = new content_management_tools();
        $wd->deleteExpiredCM();
        $wd->closeCM($_REQUEST['content_management_id']);
        createXML("SUCCESS","");
    }
    //Temps en seconde avant expiration du delai de reservation
    if ($_REQUEST['action'] == "time") {
        $wd = new content_management_tools();
        $result["TIME"] = $wd->timeBeforeExpiration($_REQUEST['content_management_id']);
        createXML("SUCCESS",$result);
    }

}
