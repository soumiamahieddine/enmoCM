<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
* @brief   Action : mark as read
*
* mark as read a mail so that it doesn't appear anymore in the basket
*
* @file
* @date $date$
* @version $Revision$
* @ingroup apps
*/

/**
* $confirm  bool true
*/
 $confirm = true;

/**
* $etapes  array Contains only one etap, the status modification
*/
 $etapes = array('markAsRead');


function manage_markAsRead($arr_id, $history, $id_action, $label_action, $status)
{
    $db = new Database();
    $result = '';
    require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
    require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php');
    $sec = new security();

    $ind_coll = $sec->get_ind_collection($_POST['coll_id']);

    for ($i=0; $i<count($arr_id);$i++) {
        $result .= $arr_id[$i].'#';

        $stmt = $db->query("SELECT * FROM res_mark_as_read WHERE res_id = ? AND user_id = ? AND basket_id = ?", array($arr_id[$i], $_SESSION['user']['UserId'], $_SESSION['current_basket']['id']));

        $lineExist = false;
        while ($result1 = $stmt->fetchObject()) {
            $lineExist = true;
        }
        if (!$lineExist) {
            $query = "INSERT INTO res_mark_as_read VALUES(?, ?, ?)";
            $db->query($query, array($arr_id[$i], $_SESSION['user']['UserId'], $_SESSION['current_basket']['id']));
        }
    }
    return array('result' => $result, 'history_msg' => '');
}
