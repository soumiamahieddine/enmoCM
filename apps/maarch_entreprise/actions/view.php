<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   view
* @author  dev <dev@maarch.org>
* @ingroup apps
*/

$etapes = array('form');

$frm_width='';

$frm_height = '';

$mode_form = 'fullscreen';

/**
 * Returns the indexing form text
 *
 * @param $values Array Contains the res_id of the document to process
 * @param $path_manage_action String Path to the PHP file called in Ajax
 * @param $id_action String Action identifier
 * @param $table String Table
 * @param $module String Origin of the action
 * @param $coll_id String Collection identifier
 * @param $mode String Action mode 'mass' or 'page'
 * @return String The form content text
 **/
function get_form_txt($values, $path_manage_action, $id_action, $table, $module, $coll_id, $mode)
{
    $res_id = $values[0];
    $frm_str = '';
    $_SESSION['doc_id'] = $res_id;
    $frm_str .= '<div>';
    $frm_str .= '	<center><input name="close" style="padding:5px;font-weight:600;" id="close" type="button" value="'._CLOSE.'" class="button" onClick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();window.location.reload();"/></center>';
    $frm_str .= '    </br>';
    $frm_str .= '	<iframe src="../../rest/resources/'.$res_id.'/content" name="viewframe" id="viewframe"  scrolling="auto" frameborder="0" ></iframe>';
    $frm_str .= '</div>';
    $frm_str .= '<script type="text/javascript">resize_frame_view("modal_'.$id_action.'", "viewframe", true, true);window.scrollTo(0,0);</script>';
    return addslashes($frm_str);
}
