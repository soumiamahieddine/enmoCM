<?php

/*
*   Copyright 2008-2011 Maarch
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
* @brief Action : Process a document
*
* Open a modal box to displays the process form, make the form checks and loads the result in database. Used by the core (manage_action.php page).
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

/**
* $confirm  bool false
*/
$confirm = false;
/**
* $etapes  array Contains 2 etaps : form and status (order matters)
*/
$etapes = array('form');
/**
* $frm_width  Width of the modal (empty)
*/
$frm_width='';
/**
* $frm_height  Height of the modal (empty)
*/
$frm_height = '';
/**
* $mode_form  Mode of the modal : fullscreen
*/
$mode_form = 'fullscreen';

include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');

/**
 * Gets the folder data for a given document
 *
 * @param $coll_id string Collection identifier
 * @param $res_id string Resource identifier
 * @return Array Folder data (market + project)
 **/
function get_folder_data($coll_id, $res_id)
{
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    $sec =new security();
    $view = $sec->retrieve_view_from_coll_id($coll_id);
    if (empty($view)) {
        $view = $sec->retrieve_table_from_coll($coll_id);
    }
    $db = new dbquery();
    $db->connect();
    $market = '';
    $project = '';
    $db->query("select folders_system_id, folder_name, fold_parent_id, fold_subject, folder_level from ".$view." where res_id = ".$res_id);
    $res = $db->fetch_object();
    if ($res->folder_level == 2) {
        $market = $res->folder_name.', '.$res->fold_subject.' ('.$res->folders_system_id.')';
        $parent_id = $res->fold_parent_id;
        $db->query("select folder_name, parent_id, subject from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$parent_id);
        if ($db->nb_result() == 1) {
            $res = $db->fetch_object();
            $project = $res->folder_name.', '.$res->fold_subject.' ('.$parent_id.')';
        }
    } else if ($res->folder_level == 1) {
        $project = $res->folder_name.', '.$res->fold_subject.' ('.$res->folders_system_id.')';
    }
    return array('project' => $project, 'market' => $market);
}


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
function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode)
{
    //print_r($_SESSION['current_basket']);
    if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
        $browser_ie = true;
        $display_value = 'block';
    } elseif (preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"])) {
        $browser_ie = true;
        $display_value = 'block';
    } else {
        $browser_ie = false;
        $display_value = 'table-row';
    }
    $_SESSION['req'] = "action";
    $res_id = $values[0];
    $frm_str = '';
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_types.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_chrono.php");
    $type = new types();
    $sec =new security();
    $core_tools =new core_tools();
    $doctypes = $type->getArrayTypes($coll_id);
    $b = new basket();
    $is = new indexing_searching_app();
    $cr = new chrono();
    $data = array();
    $params_data = array('show_market' => false, 'show_project' => false);
    $data = get_general_data($coll_id, $res_id, 'full', $params_data);
    $process_data = $is->get_process_data($coll_id, $res_id);
    $chrono_number = $cr->get_chrono_number($res_id, $sec->retrieve_view_from_table($table));
    $_SESSION['doc_id'] = $res_id;
    $indexes = array();
    if (isset($data['type_id'])) {
        $indexes = $type->get_indexes($data['type_id']['value'], $coll_id);
        $fields = 'res_id';
        foreach (array_keys($indexes) as $key) {
            $fields .= ','.$key;
        }
        $b->connect();
        $b->query("select ".$fields." from ".$table." where res_id = ".$res_id);
        $values_fields = $b->fetch_object();
        //print_r($indexes);
    }
    //  to activate locking decomment these lines
    /*if ($b->reserve_doc($_SESSION['user']['UserId'], $res_id, $coll_id) == false)
    {
        $frm_str = '<div>';
        $frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=file_index_b.gif"  align="middle" alt="" />'._DOC_NUM." ".$res_id ;
                    $frm_str .= '</h1>';
            $frm_str .= '<div>'._DOC_ALREADY_RSV.'</div>';
            $frm_str .= '<div><input type="button" name="close" id="close" value="'._CLOSE_WINDOW.'" class="button" onclick="javascript:destroyModal(\'modal_'.$id_action.'\');reinit();"/></div>';
            $frm_str .= '</div>';

    }
    else
    {*/
        $frm_str = '<div id="validleftprocess">';
            $frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=file_index_b.gif"  align="middle" alt="" />'._PROCESS._DOC_NUM." ".$res_id   ;
                    $frm_str .= '</h1>';
                    $frm_str .= '<div id="frm_error_'.$id_action.'" class="error"></div>';
                    $frm_str .= '<form name="process" method="post" id="process" action="#" class="forms addforms2" style="text-align:left;">';

                    $frm_str .= '<input type="hidden" name="values" id="values" value="'.$res_id.'" />';
                    $frm_str .= '<input type="hidden" name="action_id" id="action_id" value="'.$id_action.'" />';
                    $frm_str .= '<input type="hidden" name="mode" id="mode" value="'.$mode.'" />';
                    $frm_str .= '<input type="hidden" name="table" id="table" value="'.$table.'" />';
                    $frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="'.$coll_id.'" />';
                    $frm_str .= '<input type="hidden" name="module" id="module" value="'.$module.'" />';
                    $frm_str .= '<input type="hidden" name="req" id="req" value="second_request" />';

                    $frm_str .= '<h2 onclick="new Effect.toggle(\'general_datas_div\', \'blind\', {delay:0.2});return false;"  class="categorie" style="width:90%;">';
                        $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" id="img_general_data" />&nbsp;<b>'._GENERAL_INFO.' :</b>';
                $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h2>';
            $frm_str .= '<div id="general_datas_div"  style="display:block">';
                $frm_str .= '<div>';
                  $frm_str .= '<table width="90%" align="center" border="0">';
                  // Displays the document indexes
                foreach (array_keys($data) as $key) {
                    $frm_str .= '<tr>';
                    $frm_str .= '<td width="33%" align="left"><span class="form_title" >'.$data[$key]['label'].' :</span></td>';
                    $frm_str .= '<td >';
                    if ($data[$key]['display'] == 'textinput') {
                        $frm_str .= '<input type="text" name="'.$key.'" id="'.$key.'" value="'.$data[$key]['show_value'].'" readonly="readonly" class="readonly" style="border:none;" />';
                        if (isset($data[$key]['addon'])) {
                            $frm_str .= $data[$key]['addon'];
                        }
                    } elseif ($data[$key]['display'] == 'textarea') {
                        $frm_str .= '<input type="text" name="'.$key.'" id="'.$key.'" value="'.$data[$key]['show_value'].'" readonly="readonly" class="readonly" style="border:none;" alt="'.$data[$key]['show_value'].'" title="'.$data[$key]['show_value'].'" /> ';
                        if (isset($data[$key]['addon']))
                        {
                            $frm_str .= $data[$key]['addon'];
                        }
                        //$frm_str .= '<teaxtarea name="'.$key.'" id="'.$key.'"  readonly="readonly" class="readonly" style="border:none;display:block;width:204px;"  >'.$data[$key]['show_value'].'</teaxtarea>';
                    }
                    $frm_str .= '</td >';
                    $frm_str .= '</tr>';
                }
                if ($chrono_number <> '') {
                    $frm_str .= '<tr>';
                    $frm_str .= '<td width="33%" align="left"><span class="form_title" >'._CHRONO_NUMBER.' :</span></td>';
                    $frm_str .= '<td >';
                    $frm_str .= '<input type="text" name="alt_identifier" id="alt_identifier" value="'.$chrono_number.'" readonly="readonly" class="readonly" style="border:none;" />';
                    $frm_str .= '</td >';
                    $frm_str .= '</tr>';
                }
                if (count($indexes) > 0) {
                    foreach (array_keys($indexes) as $key) {
                        $frm_str .= '<tr>';
                            $frm_str .= '<td width="33%" align="left"><span class="form_title" >'.$indexes[$key]['label'].' :</span></td>';
                            $frm_str .= '<td >';
                            $frm_str .= '<input type="text" name="'.$key.'" id="'.$key.'" readonly="readonly" class="readonly" style="border:none;" ';
                            if ($indexes[$key]['type_field'] == 'input') {
                                $frm_str .= ' value="'.$values_fields->$key.'" ';
                            } else {
                                $val = '';
                                for ($i=0; count($indexes[$key]['values']); $i++) {
                                    if ($values_fields->$key == $indexes[$key]['values'][$i]['id']) {
                                        $val =     $indexes[$key]['values'][$i]['label'];
                                        break;
                                    }
                                }
                                $frm_str .= ' value="'.$val.'" ';
                            }
                            $frm_str .= ' />';
                            $frm_str .= '</td >';
                        $frm_str .= '</tr>';
                    }
                }
                //extension
                $db = new dbquery();
                $db->connect();
                $db->query("select format from ".$table." where res_id = ".$res_id);
                $formatLine = $db->fetch_object();
                $frm_str .= '<tr>';
                $frm_str .= '<td width="33%" align="left"><span class="form_title" >'._FORMAT.' :</span></td>';
                $frm_str .= '<td >';
                $frm_str .= '<input type="text" name="alt_identifier" id="alt_identifier" value="'.$formatLine->format.'" readonly="readonly" class="readonly" style="border:none;" />';
                $frm_str .= '</td >';
                $frm_str .= '</tr>';
            $frm_str .= '</table>';
            $frm_str .= '</div>';

         $frm_str .= '</div><br/>';

        if ($core_tools->is_module_loaded('cases')) {
            require_once("modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');
            $cases = new cases();
            $case_id = $cases->get_case_id($res_id);
            if ($case_id <> false) {
                $case_properties = $cases->get_case_info($case_id);
            } else {
                $case_properties = '';
            }
            $frm_str .= '<h2 onclick="new Effect.toggle(\'cases_div\', \'blind\', {delay:0.2});return false;"  class="categorie" style="width:90%;">';
            $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" id="img_cases" />&nbsp;<b>'._CASE.' :</b>';
            $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h2>';
            $frm_str .= '<div id="cases_div"  style="display:none">';
                $frm_str .= '<div>';
                    $frm_str .= '<table width="98%" align="center" border="0">';
                        $frm_str .= '<tr >';
                            $frm_str .= '<td><label for="case_id" class="form_title" >'._CASE.'</label></td>';
                                $frm_str .= '<td>&nbsp;</td>';
                                $frm_str .='<td><input type="text" readonly="readonly" class="readonly" name="case_id" id="case_id" value="'.$case_properties['case_id'].'"  onblur=""/>';
                                $frm_str .='</td>';
                                $frm_str .= '</tr>';
                                $frm_str .= '<tr >';
                                $frm_str .= '<td><label for="case_label" class="case_label" >'._CASE_LABEL.'</label></td>';
                                $frm_str .= '<td>&nbsp;</td>';
                                $frm_str .='<td><input type="text" readonly="readonly" class="readonly" name="case_label" id="case_label" onblur="" value="'.$case_properties['case_label'].'" />';
                                $frm_str .='</td>';
                                $frm_str .= '</tr>';
                                $frm_str .= '<tr >';
                                $frm_str .= '<td><label for="case_description" class="case_description" >'._CASE_DESCRIPTION.'</label></td>';
                                $frm_str .= '<td>&nbsp;</td>';
                                $frm_str .='<td><input type="text" readonly="readonly" class="readonly" name="case_description" id="case_description" onblur="" value="'.$case_properties['case_description'].'" />';
                                $frm_str .='</td>';
                                $frm_str .= '</tr>';
                                $frm_str .= '<tr >';

                                if ($core_tools->test_service('join_res_case_in_process', 'cases',false) == 1) {
                                        $frm_str .= '<td colspan="3"> <input type="button" class="button" name="search_case" id="search_case" value="';
                                        if ($case_properties['case_id']<>'') {
                                            $frm_str .= _MODIFY_CASE;
                                        } else {
                                            $frm_str .= _JOIN_CASE;
                                        }
                                        $frm_str .= '" onclick="window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=cases&page=search_adv_for_cases&searched_item=res_id_in_process&searched_value='.$_SESSION['doc_id'].'\',\'\', \'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1020,height=710\');"/></td>';
                                }
                            $frm_str .= '</tr>';
                    $frm_str .= '</table>';
                $frm_str .= '</div>';
            $frm_str .= '</div>';
        }
        if ($core_tools->is_module_loaded('entities')) {
             // Displays the diffusion list (only copies)
            require_once("modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
            $diff_list = new diffusion_list();
            $_SESSION['process']['diff_list'] = $diff_list->get_listinstance($res_id);
            $frm_str .= '<h2 onclick="new Effect.toggle(\'diff_list_div\', \'blind\', {delay:0.2});return false;"  class="categorie" style="width:90%;">';
                        $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" id="img_diff_list" />&nbsp;<b>'._DIFF_LIST_COPY.' :</b>';
                $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h2>';
            $frm_str .= '<div id="diff_list_div"  style="display:none">';
                $frm_str .= '<div>';
            if (count($_SESSION['process']['diff_list']['copy']['users']) == 0 && count($_SESSION['process']['diff_list']['copy']['entities']) == 0) {
                $frm_str .= _NO_COPY;
            } else {
                $frm_str .= '<table cellpadding="0" cellspacing="0" border="0" class="listing3">';
                $color = ' class="col"';
                for ($i=0;$i<count($_SESSION['process']['diff_list']['copy']['entities']);$i++) {
                    if ($color == ' class="col"') {
                        $color = '';
                    } else {
                        $color = ' class="col"';
                    }
                    $frm_str .= '<tr '.$color.' >';
                    $frm_str .= '<td><img src="'.$_SESSION['config']['businessappurl'].'static.php?module=entities&filename=manage_entities_b_small.gif" alt="'._ENTITY.'" title="'._ENTITY.'" /></td>';
                    $frm_str .= '<td >'.$_SESSION['process']['diff_list']['copy']['entities'][$i]['entity_id'].'</td>';
                    $frm_str .= '<td colspan="2">'.$_SESSION['process']['diff_list']['copy']['entities'][$i]['entity_label'].'</td>';
                    $frm_str .= '</tr>';
                }
                for ($i=0;$i<count($_SESSION['process']['diff_list']['copy']['users']);$i++) {
                    if ($color == ' class="col"') {
                        $color = '';
                    } else {
                        $color = ' class="col"';
                    }
                    $frm_str .= '<tr '.$color.' >';
                        $frm_str .= '<td><img src="'.$_SESSION['config']['businessappurl'].'static.php?module=entities&filename=manage_users_entities_b_small.gif" alt="'._USER.'" title="'._USER.'" /></td>';
                        $frm_str .= '<td >'.$_SESSION['process']['diff_list']['copy']['users'][$i]['firstname'].'</td>';
                        $frm_str .= '<td >'.$_SESSION['process']['diff_list']['copy']['users'][$i]['lastname'].'</td>';
                        $frm_str .= '<td>'.$_SESSION['process']['diff_list']['copy']['users'][$i]['entity_label'].'</td>';
                    $frm_str .= '</tr>';
                }
                $frm_str .= '</table>';
            }
            if ($core_tools->test_service('add_copy_in_process', 'entities', false)) {
                $frm_str .= '<a href="#" onclick="window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=manage_listinstance&origin=process&only_cc\', \'\', \'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no\');" title="'._ADD_COPIES.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=modif_liste.png" alt="'._ADD_COPIES.'" />'._ADD_COPIES.'</a>';
            }
            $frm_str .= '</div>';
             $frm_str .= '</div>';
        }
        if ($core_tools->is_module_loaded('folder')) {
             // Displays the folder data
            $arr_tmp = get_folder_data($coll_id, $res_id);
            $project = $arr_tmp['project'];
            $market = $arr_tmp['market'];
            $frm_str .= '<h2 onclick="new Effect.toggle(\'folder_div\', \'blind\', {delay:0.2});return false;"  class="categorie" style="width:90%;">';
                        $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" id="img_folder" />&nbsp;<b>'._FOLDER_ATTACH.' :</b>';
                $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h2>';
            $frm_str .= '<div id="folder_div"  style="display:none">';
                $frm_str .= '<div>';
                    $frm_str .= '<table width="98%" align="center" border="0">';
                            $frm_str .= '<tr id="project_tr" style="display:'.$display_value.';">';
                        $frm_str .= '<td><label for="project" class="form_title" >'._PROJECT.'</label></td>';
                        $frm_str .= '<td>&nbsp;</td>';
                         $frm_str .='<td><input type="text" name="project" id="project" value="'.$project.'"  onblur=""/><div id="show_project" class="autocomplete"></div>'; //$(\'market\').value=\'\';
                    $frm_str .= '</tr>';
                    $frm_str .= '<tr id="market_tr" style="display:'.$display_value.';">';
                        $frm_str .= '<td><label for="market" class="form_title" >'._MARKET.'</label></td>';
                        $frm_str .= '<td>&nbsp;</td>';
                         $frm_str .='<td><input type="text" name="market" id="market" onblur="fill_project(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=ajax_get_project\');" value="'.$market.'" /><div id="show_market" class="autocomplete"></div>';
                    $frm_str .= '</tr>';
                    $frm_str .= '</table>';
                $frm_str .= '</div>';
             $frm_str .= '</div>';
              $frm_str .='<input type="hidden" name="res_id" id="res_id"  value="'.$res_id.'" />';
        }
        $frm_str .= '<h2 onclick="new Effect.toggle(\'history_div\', \'blind\', {delay:0.2});return false;" class="categorie" style="width:90%;">';
            $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" />&nbsp;<b>'. _DOC_HISTORY.' :</b>';
            $frm_str .= '<span class="lb1-details">&nbsp;</span>';
        $frm_str .= '</h2>';
        $frm_str .= '<div class="desc" id="history_div" style="display:none">';
            $frm_str .= '<div class="ref-unit">';
                $frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=hist_doc&id='.$res_id.'" name="hist_doc_process" width="400" height="180" align="left" scrolling="auto" frameborder="0" id="hist_doc_process"></iframe>';
            $frm_str .= '</div>';
        $frm_str .= '</div>';
        if ($core_tools->is_module_loaded('notes')) {
             // Displays the notes
            $select_notes[$_SESSION['tablename']['users']] = array();
            array_push($select_notes[$_SESSION['tablename']['users']],"user_id","lastname","firstname");
            $select_notes[$_SESSION['tablename']['not_notes']] = array();
            array_push($select_notes[$_SESSION['tablename']['not_notes']],"id", "date_note", "note_text", "user_id");
            $where_notes = " identifier = ".$res_id." ";
            $request_notes = new request;
            $tab_notes=$request_notes->select($select_notes,$where_notes,"order by ".$_SESSION['tablename']['not_notes'].".date_note desc",$_SESSION['config']['databasetype'], "500", true,$_SESSION['tablename']['not_notes'], $_SESSION['tablename']['users'], "user_id");
            $frm_str .= '<h2 onclick="new Effect.toggle(\'notes_div\', \'blind\', {delay:0.2});return false;"  class="categorie" style="width:90%;">';
            $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" />&nbsp;<b>'._NOTES." (".count($tab_notes).")".' :</b>';
            $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h2>';
            $frm_str .= '<div class="desc" id="notes_div" style="display:none;">';
            $frm_str .= '<div class="ref-unit">';
            $frm_str .= '<div style="text-align:center;">';
            $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?module=notes&filename=modif_note.png" border="0" alt="" />';
                                $frm_str .= '<a href="javascript://" onclick="ouvreFenetre(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=note_add&identifier='.$_SESSION['doc_id'].'&coll_id='.$_SESSION['collection_id_choice'].'\', 450, 300)" >';
                                    $frm_str .= _ADD_NOTE;
                                $frm_str .= '</a>';
                $frm_str .= '</div>';
            $frm_str .= '<iframe name="list_notes_doc" id="list_notes_doc" src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=frame_notes_doc" frameborder="0" width="430px" height="150px"></iframe>';
            $frm_str .= '</div>';
            $frm_str .= '</div>';
        }
         // Displays the process data
        $nb_attach = 0;
        if ($core_tools->is_module_loaded('attachments')) {
            $req = new request;
            $req->connect();
            $req->query("select res_id from ".$_SESSION['tablename']['attach_res_attachments']." where status <> 'DEL' and res_id_master = ".$res_id);
            if ($req->nb_result() > 0)
            {
                $nb_attach = $req->nb_result();
            }
        }
            $frm_str .= '<h2 onclick="new Effect.toggle(\'done_answers_div\', \'blind\', {delay:0.2});return false;"  class="categorie"  style="width:90%;">';
            $frm_str .= '<img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=plus.png" alt="" />&nbsp;<b>'._DONE_ANSWERS.' ('.$nb_attach .'):</b>';
            $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h2>';
            $frm_str .= '<div class="desc" id="done_answers_div" style="display:none;width:90%;">';
                $frm_str .= '<div class="ref-unit" style="width:95%;">';
                    $frm_str .= '<table width="95%">';
                        $frm_str .= '<tr>';
                                    $frm_str .= '<td>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="direct_contact" id="direct_contact" value="true"';
                                    if ($process_data['direct_contact']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .= ' onclick="unmark_empty_process(\'no_answer\');" />'._DIRECT_CONTACT.'<br/>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="fax" id="fax" value="true"';
                                    if ($process_data['fax']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .=' onclick="unmark_empty_process(\'no_answer\');" />'._FAX.'<br/>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="email" id="email"  value="true"';
                                    if ($process_data['email']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .='onclick="unmark_empty_process(\'no_answer\');" />'._EMAIL.'<br/>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="simple_mail" id="simple_mail"  value="true" ';
                                    if ($process_data['simple_mail']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .= 'onclick="unmark_empty_process(\'no_answer\');" />'._SIMPLE_MAIL.'<br/>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="registered_mail" id="registered_mail" value="true" ';
                                    if ($process_data['registered_mail']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .='onclick="unmark_empty_process(\'no_answer\');" />'._REGISTERED_MAIL.'<br/>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="no_answer" id="no_answer" value="true"';
                                    if ($process_data['no_answer']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .='onclick="unmark_empty_process(\'no_answer\');" />'._NO_ANSWER.'<br/>';
                                    $frm_str .= '<input type="checkbox"  class="check" name="other" id="other" value="true"';
                                    if ($process_data['other']) {
                                        $frm_str .= 'checked="checked"';
                                    }
                                    $frm_str .='onclick="unmark_empty_process(\'no_answer\');" />'._OTHER.' : <input type="text" name="other_answer" id="other_answer" value="';
                                    if (!empty($process_data['other_answer_desc'])) {
                                        $frm_str .= $process_data['other_answer_desc'];
                                    } else {
                                        $frm_str .='['._DEFINE.']';
                                    }
                                    $frm_str .='"';
                                    if (empty($process_data['other_answer_desc']))
                                    {
                                        $frm_str .= ' onfocus="if (this.value==\'['._DEFINE.']\'){this.value=\'\';}" ';
                                    }
                                    $frm_str .=' /><br/>';
                                    $frm_str .= '</td>';
                                    $frm_str .= '<td>&nbsp;</td>';

                                    $frm_str .= '</tr>';
                                    $frm_str .= '<tr>';
                                    $frm_str .= '<td><label for="process_notes">'._PROCESS_NOTES.' : </label><br/><textarea name="process_notes" id="process_notes" style="display:block;" rows="8" cols="5">'.$process_data['process_notes'].'</textarea></td>';
                                    $frm_str .= '</tr>';
                                    $frm_str .= '</table>';
                if ($core_tools->is_module_loaded('attachments')) {
                    $req = new request;
                    $req->connect();
                    $req->query("select res_id from ".$_SESSION['tablename']['attach_res_attachments']." where status = 'NEW' and res_id_master = ".$res_id);
                    //$req->show();
                    $nb_attach = 0;
                    if ($req->nb_result() > 0) {
                        $nb_attach = $req->nb_result();
                    }
                    $frm_str .= '<div class="ref-unit">';
                    $frm_str .= '<input type="button" name="attach" id="attach" class="button" value="'._ATTACH.'" onclick="javascript:window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=join_file\',\'\', \'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=550,height=200\');" /> ';
                    if ($core_tools->is_module_loaded("templates")) {
                        $frm_str .= '<input type="button" name="template" id="template" class="button" value="'._GENERATE.'" onclick="javascript:window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=templates&page=choose_template&entity='.$data['destination']['value'].'&res_id='.$res_id.'&coll_id='.$coll_id.'\',\'\', \'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=355,height=210\');" />';
                    }
                    $frm_str .= '<iframe name="list_attach" align="left" id="list_attach" src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments" frameborder="0" width="430px" height="100px"></iframe>';
                    $frm_str .= '</div>';
                }
            $frm_str .= '</div>';
            $frm_str .= '</div><br/>';

        $frm_str .= '<hr class="hr_process"/>';
        $frm_str .= '<p align="center" style="width:90%;">';
            $frm_str .= '<b>'._ACTIONS.' : </b>';

            $actions  = $b->get_actions_from_current_basket($res_id, $coll_id, 'PAGE_USE');
            if (count($actions) > 0) {
                $frm_str .='<select name="chosen_action" id="chosen_action">';
                    $frm_str .='<option value="">'._CHOOSE_ACTION.'</option>';
                    for ($ind_act = 0; $ind_act < count($actions);$ind_act++) {
                        $frm_str .='<option value="'.$actions[$ind_act]['VALUE'].'"';
                        if ($ind_act==0) {
                            $frm_str .= 'selected="selected"';
                        }
                        $frm_str .= '>'.$actions[$ind_act]['LABEL'].'</option>';
                    }
                $frm_str .='</select> ';
                $frm_str .= '<input type="button" name="send" id="send" value="'._VALIDATE.'" class="button" onclick="valid_action_form(\'process\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
            }
            $frm_str .= '<input name="close" id="close" type="button" value="'._CANCEL.'" class="button" onclick="javascript:var tmp_bask=$(\'baskets\');if (tmp_bask){tmp_bask.style.visibility=\'visible\';}var tmp_ent =$(\'entity\');if (tmp_ent){tmp_ent.style.visibility=\'visible\';} var tmp_cat =$(\'category\'); if (tmp_cat){tmp_cat.style.visibility=\'visible\';}destroyModal(\'modal_'.$id_action.'\');reinit();"/>';
        $frm_str .= '</p>';
    $frm_str .= '</form>';
    $frm_str .= '</div>';
        $frm_str .= '</div>';

        $frm_str .= '<div id="validright">';
        $frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&id='.$res_id.'" name="viewframe" id="viewframe"  scrolling="auto" frameborder="0" ></iframe>';
        $frm_str .= '</div>';
        $frm_str .= '<script type="text/javascript">resize_frame_process("modal_'.$id_action.'", "viewframe", true, true);resize_frame_process("modal_'.$id_action.'", "hist_doc", true, false);window.scrollTo(0,0);';
        if ($core_tools->is_module_loaded('folder')) {
          $frm_str .= 'launch_autocompleter_folders(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=autocomplete_folders&mode=project\', \'project\');launch_autocompleter_folders(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=folder&page=autocomplete_folders&mode=market\', \'market\');';
          }
        $frm_str .='$(\'entity\').style.visibility=\'hidden\';';
        $frm_str .='$(\'category\').style.visibility=\'hidden\';';
        $frm_str .='$(\'baskets\').style.visibility=\'hidden\';</script>';


    //}
    return addslashes($frm_str);
}

/**
 * Checks the action form
 *
 * @param $form_id String Identifier of the form to check
 * @param $values Array Values of the form
 * @return Bool true if no error, false otherwise
 **/
function check_form($form_id,$values)
{
    $check = false;
    $other_checked = false;
    $other_txt = '';
    $market = '';
    $project = '';
    $core = new core_tools();
    //print_r($values);
    for ($i=0; $i<count($values); $i++) {
        if ($values[$i]['ID'] == "direct_contact" && $values[$i]['VALUE'] == "true") {
            $check = true;
        }
        if ($values[$i]['ID'] == "fax" && $values[$i]['VALUE'] == "true") {
            $check = true;
        }
        if ($values[$i]['ID'] == "email" && $values[$i]['VALUE'] == "true") {
            $check = true;
        }
        if ($values[$i]['ID'] == "simple_mail" && $values[$i]['VALUE'] == "true") {
            $check = true;
        }
        if ($values[$i]['ID'] == "registered_mail" && $values[$i]['VALUE'] == "true") {
            $check = true;
        }
        if ($values[$i]['ID'] == "no_answer" && $values[$i]['VALUE'] == "true") {
            $check = true;
        }
        if ($values[$i]['ID'] == "other" && $values[$i]['VALUE'] == "true")
        {
            $check = true;
            $other_checked = true;
        }
        if ($values[$i]['ID'] == "other_answer"  && trim($values[$i]['VALUE']) <> html_entity_decode('['._DEFINE.']', ENT_NOQUOTES, 'UTF-8')) {
            $other_txt = $values[$i]['VALUE'];
        }
        if ($values[$i]['ID'] == "market") {
            $market = $values[$i]['VALUE'];
        }
        if ($values[$i]['ID'] == "project") {
            $project = $values[$i]['VALUE'];
        }
        if ($values[$i]['ID'] == "coll_id") {
            $coll_id = $values[$i]['VALUE'];
        }
        if ($values[$i]['ID'] == "res_id") {
            $res_id = $values[$i]['VALUE'];
        }
    }
    if ($core->is_module_loaded('folder')) {
        $db = new dbquery();
        $db->connect();
        $project_id = '';
        $market_id = '';
        /*if (empty($market))
        {
            $_SESSION['action_error'] = _MARKET.' '._IS_EMPTY;
            return false;
        }*/
        if (!empty($market)) {
            if (!preg_match('/\([0-9]+\)$/', $market)) {
                $_SESSION['action_error'] = _MARKET." "._WRONG_FORMAT."";
                    return false;
            }
            $market_id = str_replace(')', '', substr($market, strrpos($market,'(')+1));
            $db->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$market_id);
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _MARKET.' '.$market_id.' '._UNKNOWN;
                return false;
            }
        }
        /*if (empty($project))
        {
            $_SESSION['action_error'] = _PROJECT.' '._IS_EMPTY;
            return false;
        }*/
        if (!empty($project)) {
            if (!preg_match('/\([0-9]+\)$/', $project)) {
                $_SESSION['action_error'] = _PROJECT." "._WRONG_FORMAT."";
                return false;
            }
            $project_id = str_replace(')', '', substr($project, strrpos($project,'(')+1));
            $db->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$project_id);
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _PROJECT.' '.$project_id.' '._UNKNOWN;
                return false;
            }
        }
        if (!empty($project_id) && !empty($market_id)) {
            $db->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$market_id." and parent_id = ".$project_id);
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _INCOMPATIBILITY_MARKET_PROJECT;
                return false;
            }
        }
        if (!empty($res_id) && !empty($coll_id) && (!empty($project_id) || !empty($market_id))) {
            require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
            $sec = new security();
            $table = $sec->retrieve_table_from_coll($coll_id);
            if (empty($table)) {
                $_SESSION['action_error'] .= _COLLECTION.' '._UNKNOWN;
                return false;
            }
            $db->query("select type_id from ".$table." where res_id = ".$res_id);
            $res = $db->fetch_object();
            $type_id = $res->type_id;
            $foldertype_id = '';
            if (!empty($market_id)) {
                $db->query("select foldertype_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$market_id);
            } else  {
                //!empty($project_id)
                $db->query("select foldertype_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$project_id);
            }
            $res = $db->fetch_object();
            $foldertype_id = $res->foldertype_id;
            $db->query("select fdl.foldertype_id from ".$_SESSION['tablename']['fold_foldertypes_doctypes_level1']." fdl, ".$_SESSION['tablename']['doctypes']." d where d.doctypes_first_level_id = fdl.doctypes_first_level_id and fdl.foldertype_id = ".$foldertype_id." and d.type_id = ".$type_id);
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] .= _ERROR_COMPATIBILITY_FOLDER;
                return false;
            }
        }
    }
    if ($other_checked && $other_txt == '') {
        $_SESSION['action_error'] = _MUST_DEFINE_ANSWER_TYPE;
        return false;
    }
    if ($check == false) {
        $_SESSION['action_error'] = _MUST_CHECK_ONE_BOX;
    }
    return $check;
}

/**
 * Action of the form : loads the index in the db
 *
 * @param $arr_id Array Not used here
 * @param $history String Log the action in history table or not
 * @param $id_action String Action identifier
 * @param $label_action String Action label
 * @param $status String  Not used here
 * @param $coll_id String Collection identifier
 * @param $table String Table
 * @param $values_form String Values of the form to load
 * @return false or an array
 *             $data['result'] : res_id of the new file followed by #
 *             $data['history_msg'] : Log complement (empty by default)
 **/
function manage_form($arr_id, $history, $id_action, $label_action, $status,  $coll_id, $table, $values_form)
{
    if (empty($values_form) || count($arr_id) < 1 || empty($coll_id)) {
        return false;
    }
    //require_once("core/class/class_db.php");
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    $sec =new security();
    $db = new dbquery();
    $core = new core_tools();
    $db->connect();
    $res_table = $sec->retrieve_table_from_coll($coll_id);
    $ind = $sec->get_ind_collection($coll_id);
    $table = $_SESSION['collections'][$ind]['extensions'][0];
    $simple_mail = '0';
    $AR_mail = '0';
    $contact = '0';
    $email = '0';
    $fax = '0';
    $other = '0';
    $no_answer = '0';
    $other_txt = '';
    $process_notes = '';
    $project = '';
    $market = '';
    for ($j=0; $j<count($values_form); $j++) {
        if ($values_form[$j]['ID'] == "simple_mail" && $values_form[$j]['VALUE'] == "true") {
            $simple_mail = '1';
        }
        if ($values_form[$j]['ID'] == "registered_mail" && $values_form[$j]['VALUE'] == "true") {
            $AR_mail = '1';
        }
        if ($values_form[$j]['ID'] == "direct_contact" && $values_form[$j]['VALUE'] == "true") {
            $contact = '1';
        }
        if ($values_form[$j]['ID'] == "email" && $values_form[$j]['VALUE'] == "true") {
            $email = '1';
        }
        if ($values_form[$j]['ID'] == "fax" && $values_form[$j]['VALUE'] == "true") {
            $fax = '1';
        }
        if ($values_form[$j]['ID'] == "other" && $values_form[$j]['VALUE'] == "true") {
            $other = '1';
        }
        if ($values_form[$j]['ID'] == "no_answer" && $values_form[$j]['VALUE'] == "true") {
            $no_answer = '1';
        }
        if ($values_form[$j]['ID'] == "other_answer" && !empty($values_form[$j]['ID']) && trim($values_form[$j]['ID']) <> html_entity_decode('['._DEFINE.']', ENT_NOQUOTES, 'UTF-8')) {
            $other_txt = $values_form[$j]['VALUE'];
        }
        if ($values_form[$j]['ID'] == "process_notes") {
            $process_notes = $values_form[$j]['VALUE'];
        }
        if ($values_form[$j]['ID'] == "market") {
            $market = $values_form[$j]['VALUE'];
        }
        if ($values_form[$j]['ID'] == "project") {
            $project = $values_form[$j]['VALUE'];
        }
    }
    if ($no_answer == '1') {
        $bitmask = '000000';
    } else {
        $bitmask = $other.$fax.$email.$contact.$AR_mail.$simple_mail;
    }

    if ($core->is_module_loaded('folder') && (!empty($market) || !empty($project))) {
        $folder_id = '';
        $db->connect();
        $db->query("select folders_system_id from ".$res_table." where res_id = ".$arr_id[0]);
        $res = $db->fetch_object();
        $old_folder_id = $res->folders_system_id;
        if (!empty($market)) {
            $folder_id = str_replace(')', '', substr($market, strrpos($market,'(')+1));
        } else if (!empty($project)) {
            $folder_id = str_replace(')', '', substr($project, strrpos($project,'(')+1));
        }
        if ($folder_id <> $old_folder_id && $_SESSION['history']['folderup']) {
            require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
            $hist = new history();
            $hist->add($_SESSION['tablename']['fold_folders'], $folder_id, "UP", _DOC_NUM.$arr_id[0]._ADDED_TO_FOLDER, $_SESSION['config']['databasetype'],'apps');
            if (isset($old_folder_id) && !empty($old_folder_id)) {
                $hist->add($_SESSION['tablename']['fold_folders'], $old_folder_id, "UP", _DOC_NUM.$arr_id[0]._DELETED_FROM_FOLDER, $_SESSION['config']['databasetype'],'apps');
            }
        }
        $db->connect();
        $db->query("update ".$res_table." set folders_system_id =".$folder_id." where res_id =".$arr_id[0]);
    }
    if ($core->is_module_loaded('entities')) {
        require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
        $list = new diffusion_list();
        $params = array('mode'=> 'listinstance', 'table' => $_SESSION['tablename']['ent_listinstance'], 'coll_id' => $coll_id, 'res_id' => $arr_id[0], 'user_id' => $_SESSION['user']['UserId'], 'concat_list' => true, 'only_cc' => true);
        $list->load_list_db($_SESSION['process']['diff_list'], $params); //pb enchainement avec action redirect
    }
    unset($_SESSION['redirection']);
    $db->query("update ".$table." set answer_type_bitmask = '".$bitmask."', process_notes = '".$db->protect_string_db($process_notes)."', other_answer_desc ='".$db->protect_string_db($other_txt)."'
    WHERE res_id=".$arr_id[0]);
    return array('result' => $arr_id[0].'#', 'history_msg' => '');
}

function manage_unlock($arr_id, $history, $id_action, $label_action, $status, $coll_id, $table)
{
    $db = new dbquery();
    $db->connect();
    $result = '';
    for ($i=0; $i<count($arr_id);$i++) {
        $result .= $arr_id[$i].'#';
        $req = $db->query("update ".$table. " set video_user = '', video_time = 0 where res_id = ".$arr_id[$i], true);
        if (!$req) {
            $_SESSION['action_error'] = _SQL_ERROR;
            return false;
        }
    }
    return array('result' => $result, 'history_msg' => '');
}
