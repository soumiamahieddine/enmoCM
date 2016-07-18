<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief   Action : simple confirm
*
* Open a modal box to confirm a status modification. Used by the core (manage_action.php page).
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

/**
* $confirm  bool false
*/
$confirm = false;
$etapes = array('form');
$frm_width='800px';
$frm_height = 'auto';

 function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
 {
//var_dump($_REQUEST['values']);
$mode='add';
$parameters = '&coll_id='.$_REQUEST['coll_id'].'&size=full';
        $frm_str .='<iframe name="form_mail" id="form_mail" src="'. $_SESSION['config']['businessappurl'].'index.php?display=true&module=sendmail&page=mail_form_to_contact&identifier='.$_REQUEST['values'].'&origin=document&coll_id=letterbox_coll&mode='.$mode.$parameters.'" frameborder="0" width="100%" style="height:540px;padding:0px;overflow-x:hidden;overflow-y: auto;"></iframe>';



    return $frm_str;
 }
?>
