<?php
/*
*   Copyright 2013 Maarch
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
* @brief  Contains the controler of the note Object
*
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup notes
*/

/**
* @brief  Controler of the note Object
*
* @ingroup notes
*/
class notes_controler
{
    #####################################
    ## add note on a resource
    #####################################
    public function addNote($resId, $collId, $noteContent)
    {
        $status = 'ok';
        $error = '';
        //control parameters
        if (isset($resId) && empty($resId)) {
            $status = 'ko';
            $error = 'resId empty ';
        }
        if (isset($collId) && empty($collId)) {
            $status = 'ko';
            $error = 'collId empty ';
        }
        if (isset($noteContent) && empty($noteContent)) {
            $status = 'ko';
            $error .= 'noteContent empty ';
        }
        //process
        if ($status == 'ok') {
            require_once 'core/class/class_security.php';
            require_once 'modules/notes/notes_tables.php';
            $security = new security();
            $view = $security->retrieve_view_from_coll_id($collId);
            $table = $security->retrieve_table_from_coll($collId);
            $db = new request();
            $db->connect();
            $query = "select res_id from " . $view . " where res_id = "
                   . $resId;
            $db->query($query);
            if ($db->nb_result() == 0) {
                $status = 'ko';
                $error .= 'resId not exists';
            } else {
                $query =
                    "insert into " . NOTES_TABLE . "(identifier, note_text, "
                    . "date_note, user_id, coll_id, tablename) values"
                    . " (" . $resId . ", '" . $db->protect_string_db($noteContent)
                    . "', " . $db->current_datetime(). ", '"
                    . $db->protect_string_db($_SESSION['user']['UserId'])
                    . "', '" . $collId . "', '" . $table . "')";
                if (!$db->query($query)) {
                    $status = 'ko';
                    $error .= 'SQL insert pb';
                } else {
                    $hist = new history();
                    $db->query(
                        "select id from " . NOTES_TABLE . " where "
                        . "identifier = " . $resId . " and user_id = '"
                        . $_SESSION['user']['UserId']
                        . "' and coll_id = '" . $collId . "' order by id desc"
                    );
                    $res = $db->fetch_object();
                    $id = $res->id;
                    $hist->add(
                        $view, $resId, 'UP', 'resup', _ADDITION_NOTE
                        . _ON_DOC_NUM . $resId . ' (' . $id . ') ' . _FROM_WS,
                        $_SESSION['config']['databasetype'], 'notes'
                    );
                    $hist->add(
                        NOTES_TABLE, $id, 'ADD', 'noteadd', _NOTES_ADDED
                        . ' (' . $id . ') ' . _FROM_WS,
                        $_SESSION['config']['databasetype'], 'notes'
                    );
                }
            }
        }
        $returnArray = array(
            'status' => $status,
            'value' => $id,
            'error' => $error,
        );
        return $returnArray;
    }
}
