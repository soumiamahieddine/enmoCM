<?php
/*
*   Copyright 2011-2015 Maarch
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
* @brief  Contains the controler of the Resource Object
*
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
    require_once 'core/class/resources.php';
    require_once 'core/core_tables.php';
    require_once 'core/class/class_functions.php';
    require_once 'core/class/docservers_controler.php';
    require_once 'core/class/class_resource.php';
} catch (Exception $e) {
    echo functions::xssafe($e->getMessage()).' // ';
}

/**
* @brief  Controler of the Resource Object
*
* @ingroup core
*/
class resources_controler
{

    #####################################
    ## Web Service to retrieve attachment from an identifier
    #####################################
    public function retrieveMasterResByChrono($identifier, $collId)
    {
        try {
            $db = new Database();
            $resultArr = array();
            
            if ($identifier == '') {
                $resultArr = array(
                    'returnCode' => (int) -2,
                    'resId' => '',
                    'title' => '',
                    'identifier' => '',
                    'status' => '',
                    'attachment_type' => '',
                    'dest_contact_id' => '',
                    'dest_address_id' => '',
                    'error' => 'param identifier empty',
                );
                return $resultArr;
            }

            if ($collId == '') {
                $resultArr = array(
                    'returnCode' => (int) -2,
                    'resId' => '',
                    'title' => '',
                    'identifier' => '',
                    'status' => '',
                    'attachment_type' => '',
                    'dest_contact_id' => '',
                    'dest_address_id' => '',
                    'error' => 'param collId empty',
                );
                return $resultArr;
            }

            $queryAttachments = "select * from res_attachments where "
                . "identifier = ? and coll_id = ? order by res_id";
            $stmt = $db->query(
                $queryAttachments, 
                array($identifier, $collId)
            );

            $line = $stmt->fetchObject();

            //var_dump($line);

            if ($line->res_id_master == '') {
                $resultArr = array(
                    'returnCode' => (int) -3,
                    'resId' => '',
                    'title' => '',
                    'identifier' => '',
                    'status' => '',
                    'attachment_type' => '',
                    'dest_contact_id' => '',
                    'dest_address_id' => '',
                    'error' => 'resource not found : ' 
                        . $identifier . ' ' . $collId,
                );
                return $resultArr;
            } else {
                $resultArr = array(
                    'returnCode' => (int) 0,
                    'resId' => $line->res_id_master,
                    'title' => $line->title,
                    'identifier' => $line->identifier,
                    'status' => $line->status,
                    'attachment_type' => $line->attachment_type,
                    'dest_contact_id' => $line->dest_contact_id,
                    'dest_address_id' => $line->dest_address_id,
                    'error' => '',
                );
                return $resultArr;
            }
            
            return $resultArr;
        } catch (Exception $e) {
            $resultArr = array(
                'returnCode' => (int) -1,
                'resId' => '',
                'title' => '',
                'identifier' => '',
                'status' => '',
                'attachment_type' => '',
                'dest_contact_id' => '',
                'dest_address_id' => '',
                'error' => 'unknown error : ' 
                    . $e->getMessage(),
            );
            return $resultArr;
        }
    }
}
