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
*
*
* @file
* @author Loic Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

include_once 'modules/tags/route.php';
include_once 'modules/tags/templates/init.php';

if (!$core_tools)
{
    $core_tools = new core_tools();
}


$tags = new tag_controler();
$tags->load_sessiontag($res_id,$coll_id);   


//--------------------------------------



$frm_str .= '<h3 onclick="new Effect.toggle(\'tag_div\', \'blind\', {delay:0.2});';
$frm_str .= 'whatIsTheDivStatus(\'tag_div\', \'divStatus_tag_div\');return false;" '
    . 'onmouseover="this.style.cursor=\'pointer\';" class="tag" style="width:90%;">';
$frm_str .= ' <span id="divStatus_tag_div" style="color:#1C99C5;"><<</span>';
$frm_str .= '&nbsp;<b>'._TAGS.'</b>';
$frm_str .= '<span class="lb1-details">&nbsp;</span>';
$frm_str .= '</h3>';
$frm_str .= '<div id="tag_div"  style="display:none">';
$frm_str .= '<div>';
 
$frm_str .= '<table width="98%" align="center" border="0" align="center">';

$tag_customsize = '200px';
$tag_customcols = '26';
include_once 'modules/tags/templates/addtag_userform.php'; //CHARGEMENT DU FORMULAIRE D'AJOUT DE DROITS 

$frm_str .= '<tr id="tag_tr" style="display:'.$display_value.';">';
//$frm_str .= '<td><label for="tag" class="tag_title" ></label></td>';
$frm_str .= '<td><div id="tag_displayed" style="display:block;width:300px;"></div></td>';


$frm_str .= '</tr>';
$frm_str .= '</table>';
$frm_str .= '</div>';
$frm_str .= '</div>';
$frm_str .= '<br/>';
$frm_str .= '<input type="hidden" name="res_id" id="res_id"  value="'.$res_id.'" />';


$frm_str .= '<script type="text/javascript">load_tags('.$route_tag_ui_script.', \''.$res_id.'\', \''.$coll_id.'\');';
$frm_str .= '</script>';
   
?>
