<?php
/*
*   Copyright 2008-2013 Maarch
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
* @brief   Action : Document validation
*
* Open a modal box to displays the validation form, make the form checks
* and loads the result in database. Used by the core (manage_action.php page).
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
/**
* $etapes  array Contains only one etap : form
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

include('apps/' . $_SESSION['config']['app_id'] . '/definition_mail_categories_business.php');
if ($core->is_module_loaded('folder')) {
    require_once 'modules/folder/folder_tables.php';
}

///////////////////// Pattern to check dates
$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

/**
 * Gets the path of the file to displays
 *
 * @param $res_id String Resource identifier
 * @param $coll_id String Collection identifier
 * @return String File path
 **/
function get_file_path($res_id, $coll_id)
{
    require_once('core/class/class_security.php');
    $sec =new security();
    $view = $sec->retrieve_view_from_coll_id($coll_id);
    if (empty($view)) {
        $view = $sec->retrieve_table_from_coll($coll_id);
    }
    $db = new dbquery();
    $db->connect();
    $db->query('select docserver_id, path, filename from ' 
        . $view . ' where res_id = ' . $res_id);
    $res = $db->fetch_object();
    $path = preg_replace('/#/', DIRECTORY_SEPARATOR, $res->path);
    $docserver_id = $res->docserver_id;
    $filename = $res->filename;
    $db->query('select path_template from ' 
        . $_SESSION['tablename']['docservers'] 
        . " where docserver_id = '".$docserver_id."'");
    $res = $db->fetch_object();
    $docserver_path = $res->path_template;

    return $docserver_path.$path.$filename;
}

function check_category($coll_id, $res_id)
{
    require_once('core/class/class_security.php');
    $sec =new security();
    $view = $sec->retrieve_view_from_coll_id($coll_id);

    $db = new dbquery();
    $db->connect();
    $db->query("select category_id from " 
        . $view . " where res_id = " . $res_id);
    $res = $db->fetch_object();

    if (!isset($res->category_id)) {
        $ind_coll = $sec->get_ind_collection($coll_id);
        $table_ext = $_SESSION['collections'][$ind_coll]['extensions'][0];
        $db->query("insert into " . $table_ext 
            . " (res_id, category_id) VALUES (" . $res_id . ", '" 
            . $_SESSION['coll_categories']['business_coll']['default_category'] . "')");
        //$db->show();
    }
}

/**
 * Returns the validation form text
 *
 * @param $values Array Contains the res_id of the document to validate
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
    if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
        $browser_ie = true;
        $display_value = 'block';
    } elseif (
        preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) 
        && !preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"])
    ) {
        $browser_ie = true;
        $display_value = 'block';
    } else {
        $browser_ie = false;
        $display_value = 'table-row';
    }
    $_SESSION['req'] = "action";
    $res_id = $values[0];
    $_SESSION['doc_id'] = $res_id;
    $frm_str = '';
    require_once('core/class/class_security.php');
    require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_business_app_tools.php');
    require_once('modules/basket/class/class_modules_tools.php');
    require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php');
    require_once('core/class/class_request.php');

    $sec =new security();
    $core_tools =new core_tools();
    $b = new basket();
    $type = new types();
    $business = new business_app_tools();

    if ($_SESSION['features']['show_types_tree'] == 'true') {
        $doctypes = $type-> getArrayStructTypes($coll_id);
    } else {
        $doctypes = $type->getArrayTypes($coll_id);
    }
    $db = new dbquery();
    $db->connect();
    $hidden_doctypes = array();
    $tmp = $business->get_titles();
    $titles = $tmp['titles'];
    $default_title = $tmp['default_title'];
    if ($core_tools->is_module_loaded('templates')) {
        $db->query("select type_id from " 
            . $_SESSION['tablename']['temp_templates_doctype_ext'] 
            . " where is_generated = 'Y'");
        while ($res = $db->fetch_object()) {
            array_push($hidden_doctypes, $res->type_id);
        }
    }
    $today = date('d-m-Y');

    if ($core_tools->is_module_loaded('entities')) {
        $EntitiesIdExclusion = array();
        $db = new dbquery();
        $db->connect();
        if (count($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']) > 0) {
            $db->query(
                "select entity_id from "
                . ENT_ENTITIES . " where entity_id not in ("
                . $_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']
                . ") and enabled= 'Y' order by entity_id"
            );
            while ($res = $db->fetch_object()) {
                array_push($EntitiesIdExclusion, $res->entity_id);
            }
        }
        require_once 'modules/entities/class/class_manage_entities.php';
        $ent = new entity();
        $allEntitiesTree= array();
        $allEntitiesTree = $ent->getShortEntityTreeAdvanced(
            $allEntitiesTree, 'all', '', $EntitiesIdExclusion, 'all'
        );
        //LOADING LISTMODEL
        require_once('modules/entities/class/class_manage_listdiff.php');
        $diff_list = new diffusion_list();
        $load_listmodel = true;
        $db->query("select res_id from " . $_SESSION['tablename']['ent_listinstance']." where coll_id = '" 
            . $coll_id . "' and res_id = " . $res_id);
        if ($db->nb_result() > 0) {
            $load_listmodel = false;
            $_SESSION['indexing']['diff_list'] = $diff_list->get_listinstance($res_id, false, $coll_id);
        }
        //LOADING DIFFLIST TYPES
        $groupbasket_difflist_types = 
            $diff_list->list_groupbasket_difflist_types(
                $_SESSION['user']['primarygroup'],
                $_SESSION['current_basket']['id'],
                $id_action
            );
        $difflistTypes = array();
        $listmodels = array();
        foreach($groupbasket_difflist_types as $difflist_type_id) {
            $difflistTypes[$difflist_type_id] = $diff_list->get_difflist_type($difflist_type_id);
            $listmodels[$difflist_type_id] = $diff_list->select_listmodels($difflist_type_id);
        }
    }
    
    check_category($coll_id, $res_id);
    $data = get_general_data($coll_id, $res_id, 'minimal');
/*
    echo '<pre>';
    print_r($data);
    echo '</pre>';exit;
*/
    $frm_str .= '<div id="validleft">';
    $frm_str .= '<div id="valid_div" style="display:none;";>';
        $frm_str .= '<h1 class="tit" id="action_title"><img src="'
            .$_SESSION['config']['businessappurl'].'static.php?filename=file_index_b.gif"  align="middle" alt="" />'
                . _VALIDATE_QUALIF . ' ' . _NUM . $res_id;
                    $frm_str .= '</h1>';
                    $frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';
                    $frm_str .= '<form name="index_file" method="post" id="index_file" action="#" class="forms indexingform" style="text-align:left;">';

                    $frm_str .= '<input type="hidden" name="values" id="values" value="'.$res_id.'" />';
                    $frm_str .= '<input type="hidden" name="action_id" id="action_id" value="'.$id_action.'" />';
                    $frm_str .= '<input type="hidden" name="mode" id="mode" value="'.$mode.'" />';
                    $frm_str .= '<input type="hidden" name="table" id="table" value="'.$table.'" />';
                    $frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="'.$coll_id.'" />';
                    $frm_str .= '<input type="hidden" name="module" id="module" value="'.$module.'" />';
                    $frm_str .= '<input type="hidden" name="req" id="req" value="second_request" />';

            $frm_str .= '<div  style="display:block">';
    
    $frm_str .= '<hr width="90%" align="center"/>';
    
    $frm_str .= '<h4 onclick="new Effect.toggle(\'general_infos_div\', \'blind\', {delay:0.2});'
        . 'whatIsTheDivStatus(\'general_infos_div\', \'divStatus_general_infos_div\');" '
        . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
    $frm_str .= ' <span id="divStatus_general_infos_div" style="color:#1C99C5;">>></span>&nbsp;' 
        ._GENERAL_INFO;
    $frm_str .= '</h4>';
    $frm_str .= '<div id="general_infos_div"  style="display:inline">';
    $frm_str .= '<div class="ref-unit">';
    
    $frm_str .= '<table width="100%" align="center" '
        . 'border="0"  id="indexing_fields" style="display:block;">';
    
    /*** category ***/
    $frm_str .= '<tr id="category_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;align=center;"><span id="category_img_purchase" style="display:' . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_purchase.png" alt="' . _PURCHASE . '"/></span>'
            . '<span id="category_img_sell" style="display:' . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_sell.png" alt="' . _SELL . '"/></span>'
            . '<span id="category_img_enterprise_document" style="display:' . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_enterprise_document.png" alt="' . _ENTERPRISE_DOCUMENT . '"/></span>'
            . '<span id="category_img_human_resources" style="display:' . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_human_resources.png" alt="' . _HUMAN_RESOURCES . '"/></span></td>';
    $frm_str .= '<td style="width:200px;"><label for="category_id" class="form_title" >' . _CATEGORY . '</label></td>';
    //$frm_str .= '<td style="width:1px;">&nbsp;</td>';
    $frm_str .= '<td class="indexing_field"><select name="category_id" '
            . 'id="category_id" onchange="clear_error(\'frm_error_' . $id_action
            . '\');change_category(this.options[this.selectedIndex].value, \''
            . $display_value . '\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'change_category\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=get_content_js\');launch_autocompleter_contacts(\''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&dir=indexing_searching&page=autocomplete_contacts\');$(\'contact\').value=\'\'">';
    $frm_str .= '<option value="">' . _CHOOSE_CATEGORY . '</option>';
    foreach (array_keys($_SESSION['coll_categories']['business_coll']) as $cat_id) {
        if ($cat_id <> 'default_category') {
            $frm_str .= '<option value="' . $cat_id . '"';
            if (
                (isset($data['category_id']['value']) && $data['category_id']['value'] == $cat_id)
                || $_SESSION['coll_categories']['business_coll']['default_category'] == $cat_id
                || $_SESSION['indexing']['category_id'] == $cat_id
            ) {
/*
            if ($_SESSION['coll_categories']['business_coll']['default_category'] == $cat_id
                || (isset($data['category_id']['value'])
                    && $data['category_id']['value']== $cat_id)
            ) {
*/
                $frm_str .= 'selected="selected"';
                //$data['category_id']['value'] = $cat_id;
            }
            $frm_str .= '>' . $_SESSION['coll_categories']['business_coll'][$cat_id] . '</option>';
        }
    }
    $frm_str .= '</select></td>';
    $frm_str .= '<td><span class="red_asterisk" id="category_id_mandatory" '
            . 'style="display:inline;">*</span></td>';
    $frm_str .= '</tr>';
    
    /*** Doctype ***/
    $frm_str .= '<tr id="type_id_tr" style="display:'.$display_value.';">';
    $frm_str .= '<td style="width:30px;align:center;"><span class="form_title" '
            . 'id="doctype_res"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'document.png" alt="' . _FILING . '"/>'
            . '</span></td><td class="indexing_label"><label for="type_id"><span class="form_title" id="doctype_res" style="display:none;">' 
        . _DOCTYPE . '</span><span class="form_title" id="doctype_mail" style="display:inline;" >'._FILING.'</span></label></td>';
    $frm_str .='<td class="indexing_field"><select name="type_id" id="type_id" onchange="clear_error(\'frm_error_' 
        . $id_action . '\');change_doctype(this.options[this.selectedIndex].value, \'' 
        . $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_doctype&coll_id=' . $coll_id . '\', \'' 
        . _ERROR_DOCTYPE.'\', \''.$id_action.'\', \''.$_SESSION['config']['businessappurl'] 
        . 'index.php?display=true&page=get_content_js\' , \''.$display_value.'\','.$res_id.', \''.$coll_id.'\', true);">';
            $frm_str .='<option value="">'._CHOOSE_TYPE.'</option>';
            if ($_SESSION['features']['show_types_tree'] == 'true') {
                for ($i = 0; $i < count($doctypes); $i ++) {
                    $frm_str .= '<option value="" class="' //doctype_level1
                            . $doctypes[$i]['style'] . '" title="'
                            . $doctypes[$i]['label'] . '" label="'
                            . $doctypes[$i]['label'] . '" >' . $doctypes[$i]['label']
                            . '</option>';
                    for ($j = 0; $j < count($doctypes[$i]['level2']); $j ++) {
                        $frm_str .= '<option value="" class="' //doctype_level2
                                . $doctypes[$i]['level2'][$j]['style'] .'" title="'
                                . $doctypes[$i]['level2'][$j]['label'] . '" label="'
                                . $doctypes[$i]['level2'][$j]['label'] . '" >&nbsp;&nbsp;'
                                . $doctypes[$i]['level2'][$j]['label'] .'</option>';
                        for ($k = 0; $k < count($doctypes[$i]['level2'][$j]['types']);
                            $k ++
                        ) {
                            if (!in_array($doctypes[$i]['level2'][$j]['types'][$k]['id'],$hidden_doctypes)) {
                                $frm_str .='<option value="'.$doctypes[$i]['level2'][$j]['types'][$k]['id'].'" ';
                                if (isset($data['type_id']) && !empty($data['type_id']) && $data['type_id'] == $doctypes[$i]['level2'][$j]['types'][$k]['id']) {
                                    $frm_str .= ' selected="selected" ';
                                }
                                $frm_str .=' title="'.$doctypes[$i]['level2'][$j]['types'][$k]['label']
                                . '" label="'.$doctypes[$i]['level2'][$j]['types'][$k]['label']
                                . '">&nbsp;&nbsp;&nbsp;&nbsp;'.$doctypes[$i]['level2'][$j]['types'][$k]['label'].'</option>';
                            }
                        }
                    }
                }
            } else {
                for ($i=0; $i<count($doctypes);$i++) {
                    $frm_str .='<option value="'.$doctypes[$i]['ID'].'" ';
                    if (isset($data['type_id']) && !empty($data['type_id']) && $data['type_id'] == $doctypes[$i]['ID'])
                    {
                        $frm_str .= ' selected="selected" ';
                    }
                    $frm_str .=' >'.$doctypes[$i]['LABEL'].'</option>';
                }
            }
            $frm_str .='</select>';
            $frm_str .= '<td><span class="red_asterisk" id="type_id_mandatory" style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** Subject ***/
    $frm_str .= '<tr id="subject_tr" style="display:'.$display_value.';">';
        $frm_str .='<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'subject.png" alt="' . _SUBJECT 
                . '"/></td><td class="indexing_label">'
                . '<label for="subject" class="form_title" >'._SUBJECT.'</label></td>';
        $frm_str .='<td class="indexing_field"><textarea name="subject" id="subject" rows="2" onchange="clear_error(\'frm_error_'.$id_action.'\');" >';
        if (isset($data['subject']) && !empty($data['subject'])) {
            $frm_str .= $data['subject'];
        }
         $frm_str .= '</textarea></td>';
         $frm_str .= '<td><span class="red_asterisk" id="subject_mandatory" style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** Contact ***/
    $contact_mode = 'view';
    if($core->test_service('update_contacts','apps', false)) $contact_mode = 'up';
    $frm_str .= '<tr id="contact_id_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;">'
            . '<a href="#" id="contact_card" title="' . _CONTACT_CARD
            . '" onclick="open_contact_card(\''
            . $_SESSION ['config']['businessappurl'] . 'index.php?display=true'
            . '&page=contact_info&mode=' . $contact_mode 
            . '\', \'' . $_SESSION ['config']['businessappurl']
            . 'index.php?display=true&page=user_info\');"><span id="contact_purchase_img" style="display:' 
        . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'supplier.png" alt="' . _SUPPLIER . '"/></span>'
            . '<span id="contact_sell_img" style="display:' 
                . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'purchaser.png" alt="' . _PURCHASER . '"/></span>'
            . '<span id="contact_enterprise_document_img" style="display:' 
                . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'my_contacts_off.gif" alt="' . _CONTACT . '"/></span>'
            . '<span id="contact_human_resources_img" style="display:' 
                . $display_value . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'employee.png" alt="' . _EMPLOYEE . '"/></span></td>'
            . '<td><label for="contact" class="form_title" >'
            . '<span id="contact_label_purchase" style="display:' 
                . $display_value . ';">' . _SUPPLIER . '</span>'
            . '<span id="contact_label_sell" style="display:' 
                . $display_value . ';">' . _PURCHASER . '</span>'
            . '<span id="contact_label_enterprise_document" style="display:' 
                . $display_value . ';">' . _CONTACT . '</span>'
            . '<span id="contact_label_human_resources" style="display:' 
                . $display_value . ';">' . _EMPLOYEE . '</span></a>';

    if ($_SESSION['features']['personal_contact'] == "true"
       // && $core->test_service('my_contacts', 'apps', false)
    ) {
        $frm_str .= ' <a href="#" id="create_contact" title="' . _CREATE_CONTACT
                . '" onclick="new Effect.toggle(\'create_contact_div\', '
                . '\'blind\', {delay:0.2});return false;" '
                . 'style="display:inline;" ><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'modif_liste.png" alt="' . _CREATE_CONTACT . '"/></a>';
    }

    $frm_str .= '</label></td>';
    $frm_str .= '<td class="indexing_field"><input type="text" name="contact" '
            . 'id="contact" onblur="clear_error(\'frm_error_' . $id_action . '\');'
            . 'display_contact_card(\'visible\');" ';
    if (isset($data['contact_id']) && !empty($data['contact_id'])) {
        $frm_str .= ' value="' . $data['contact_id'].'" ';
    }
     $frm_str .= '/><div id="show_contacts" '
            . 'class="autocomplete autocompleteIndex"></div></td>';
    $frm_str .= '<td><span class="red_asterisk" id="contact_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** Identifier ***/
    $frm_str .= '<tr id="identifier_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'identifier.png" alt="' . _IDENTIFIER 
                . '"/></td><td><label for="identifier" class="form_title" >' . _IDENTIFIER
            . '</label></td>';
    $frm_str .= '<td class="indexing_field"><input name="identifier" type="text" '
            . 'id="identifier" onchange="clear_error(\'frm_error_' . $id_action
            . '\');"value="';
    if (isset($data['identifier'])&& !empty($data['identifier'])) {
        $frm_str .= $data['identifier'];
    }
    $frm_str .= '"/></td>';
    $frm_str .= '<td><span class="red_asterisk" id="identifier_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** Doc date ***/
    $frm_str .= '<tr id="doc_date_tr" style="display:'.$display_value.';">';
        $frm_str .='<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'date.png" alt="' . _DOC_DATE 
                . '"/></td><td class="indexing_label">'
                . '<label for="doc_date" class="form_title" id="mail_date_label" style="display:inline;" >'
                ._DOC_DATE.'</label><label for="doc_date" class="form_title" id="doc_date_label" style="display:none;" >'._DOC_DATE.'</label></td>';
        $frm_str .='<td class="indexing_field"><input name="doc_date" type="text" id="doc_date" value="';
        if (isset($data['doc_date'])&& !empty($data['doc_date'])) {
            $frm_str .= $data['doc_date'];
        } else {
            $frm_str .= $today;
        }
        $frm_str .= '" onclick="clear_error(\'frm_error_'.$id_action.'\');showCalender(this);"/></td>';
        $frm_str .= '<td><span class="red_asterisk" id="doc_date_mandatory" style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr >';
    
    /*** currency ***/
    $frm_str .= '<tr id="currency_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'currency.png" alt="' . _CURRENCY 
                . '"/></td><td><label for="currency" class="form_title">' . _CURRENCY
            . '</label></td>';
    $frm_str .= '<td class="indexing_field">'
        . '<select id="currency" name="currency" onchange="clear_error(\'frm_error_' . $id_action
        . '\');convertAllBusinessAmount();">';
    foreach (array_keys($_SESSION['currency']) as $currency) {
        $frm_str .= '<option value="' . $currency . '"';
        if ($data['currency'] == $currency) {
                $frm_str .= 'selected="selected"';
        }
        $frm_str .= '>' . $_SESSION['currency'][$currency] . '</option>';
    }
    
    $frm_str .= '</select></td>';
    $frm_str .= '<td><span class="red_asterisk" id="currency_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** net_sum ***/
    $frm_str .= '<tr id="net_sum_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'amount.png" alt="' . _NET_SUM 
                . '"/></td><td><label for="net_sum_use" class="form_title" >' . _NET_SUM
            . '</label></td>';
    $frm_str .= '<td class="indexing_field">'
        . '<input name="net_sum_use" type="text" '
        . 'id="net_sum_use" onchange="clear_error(\'frm_error_' . $id_action
        . '\');$(\'net_sum_preformatted\').value=convertAmount($(\'currency\').options[$(\'currency\').selectedIndex].value, this.value);'
        . '$(\'net_sum\').value=convertAmount(\'\', this.value);computeTotalAmount();" '
        . 'class="amountLeft" value="';
        if (isset($data['net_sum'])&& !empty($data['net_sum'])) {
            $frm_str .= $data['net_sum'];
        } else {
            $frm_str .= '0';
        }
        $frm_str .= '"/>&nbsp;'
        . '<input name="net_sum_preformatted" type="text" '
        . 'id="net_sum_preformatted" readonly="readonly" class="amountRight readonly" />'
        . '<input name="net_sum" id="net_sum" type="hidden" />'
        . '</td>';
    $frm_str .= '<td><span class="red_asterisk" id="net_sum_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** tax_sum ***/
    $frm_str .= '<tr id="tax_sum_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;">&nbsp;</td><td><label for="tax_sum_use" class="form_title" >' . _TAX_SUM
            . '</label></td>';
    $frm_str .= '<td class="indexing_field">'
        . '<input name="tax_sum_use" type="text" '
        . 'id="tax_sum_use" onchange="clear_error(\'frm_error_' . $id_action
        . '\');$(\'tax_sum_preformatted\').value=convertAmount($(\'currency\').options[$(\'currency\').selectedIndex].value, this.value);'
        . '$(\'tax_sum\').value=convertAmount(\'\', this.value);computeTotalAmount();" '
        . 'class="amountLeft" value="';
        if (isset($data['tax_sum'])&& !empty($data['tax_sum'])) {
            $frm_str .= $data['tax_sum'];
        } else {
            $frm_str .= '0';
        }
        $frm_str .= '"/>&nbsp;'
        . '<input name="tax_sum_preformatted" type="text" '
        . 'id="tax_sum_preformatted" readonly="readonly" class="amountRight readonly" />'
        . '<input name="tax_sum" id="tax_sum" type="hidden" />'
        . '</td>';
    $frm_str .= '<td><span class="red_asterisk" id="tax_sum_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** total_sum ***/
    $frm_str .= '<tr id="total_sum_tr" style="display:' . $display_value . ';">';
    $frm_str .= '<td style="width:30px;align:center;">&nbsp;</td><td><label for="total_sum_use" class="form_title" >' . _TOTAL_SUM
            . '</label></td>';
    $frm_str .= '<td class="indexing_field">'
        . '<input name="total_sum_use" type="text" '
        . 'id="total_sum_use" onchange="clear_error(\'frm_error_' . $id_action
        . '\');$(\'total_sum_preformatted\').value=convertAmount($(\'currency\').options[$(\'currency\').selectedIndex].value, this.value);'
        . '$(\'total_sum\').value=convertAmount(\'\', this.value);controlTotalAmount();" '
        . 'class="amountLeft" value="';
        if (isset($data['total_sum'])&& !empty($data['total_sum'])) {
            $frm_str .= $data['total_sum'];
        } else {
            $frm_str .= '0';
        }
        $frm_str .= '"/>&nbsp;'
        . '<input name="total_sum_preformatted" type="text" '
        . 'id="total_sum_preformatted" readonly="readonly" class="amountRight readonly" />'
        . '<input name="total_sum" id="total_sum" type="hidden" />'
        . '</td>';
    $frm_str .= '<td><span class="red_asterisk" id="total_sum_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
     /*** Entities : department + diffusion list ***/
    if ($core_tools->is_module_loaded('entities')) {
        $_SESSION['validStep'] = "ok";
        $frm_str .= '<tr id="department_tr" style="display:'.$display_value.';">';
        $frm_str .= '<td style="width:30px;align:center;"><img src="'
            . $_SESSION['config']['businessappurl'] . 'static.php?module=entities&filename='
            . 'department.png" alt="' . _DEPARTMENT_OWNER
            . '"/></td><td><label for="department" class="form_title" '
            . 'id="label_dep_dest" style="display:inline;" >'
            . _DEPARTMENT_OWNER . '</label></td>';
        $frm_str .='<td class="indexing_field">'
            . '<select name="destination" id="destination" onchange="clear_error(\'frm_error_'. $id_action . '\');">';
        $frm_str .='<option value="">'._CHOOSE_DEPARTMENT.'</option>';
        $countAllEntities = count($allEntitiesTree);
        for ($cptEntities = 0;$cptEntities < $countAllEntities;$cptEntities++) {
            if (!$allEntitiesTree[$cptEntities]['KEYWORD']) {
                $frm_str .= '<option data-object_type="entity_id" value="' . $allEntitiesTree[$cptEntities]['ID'] . '"';
                 if (isset($data['destination'])&& $data['destination'] == $allEntitiesTree[$cptEntities]['ID']) {
                    $frm_str .=' selected="selected"';
                }
                if ($allEntitiesTree[$cptEntities]['DISABLED']) {
                    $frm_str .= ' disabled="disabled" class="disabled_entity"';
                } else {
                     //$frm_str .= ' style="font-weight:bold;"';
                }
                $frm_str .=  '>' 
                    .  $db->show_string($allEntitiesTree[$cptEntities]['SHORT_LABEL']) 
                    . '</option>';
            }
        }
        $frm_str .='</select></td>';
        $frm_str .= '<td><span class="red_asterisk" id="destination_mandatory" style="display:inline;">*</span>&nbsp;</td>';
        $frm_str .= '</tr>';
      
        # Diffusion list model            
        $frm_str .= '<tr id="difflist_tr" style="display:' . $display_value . ';">';
        $frm_str .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?module=entities&filename='
                . 'department.png" alt="' . _DIFFUSION_LIST 
                . '"/></td><td><label for="difflist" class="form_title" '
                . 'id="label_dep_dest" style="display:inline;" >'
                . _DIFFUSION_LIST . '</label></td>';
        //$frm_str .= '<td>&nbsp;</td>';
        $frm_str .= '<td class="indexing_field">';
        $frm_str .= '<select name="difflist" id="difflist" onchange="'
                    . 'clear_error(\'frm_error_' . $id_action . '\');'
                    . 'load_listmodel(this.options[this.selectedIndex], \'diff_list_div\', \'indexing\');'
                    . '$(\'diff_list_tr\').style.display=\''.$display_value.'\''
                . ';" >';
        $frm_str .= '<option value="">' . _CHOOSE_DIFFUSION_LIST . '</option>';
        if(count($listmodels) > 0) {
            foreach($listmodels as $difflist_type_id => $listmodel) {
                $frm_str .= '<optgroup label="'.$difflistTypes[$difflist_type_id]->difflist_type_label . '">';
                for($i=0, $l=count($listmodel); $i<$l; $i++) {
                    $frm_str .= '<option data-object_type="'.$difflist_type_id.'" value="' . $listmodel[$i]['object_id'] . '"';
					if (isset($_SESSION['indexing']['diff_list']['difflist_type'])
						&& $_SESSION['indexing']['diff_list']['difflist_type'] == $difflist_type_id) {
						$frm_str .=' selected="selected"';
					}
					$frm_str .=  '>'  
                        .  $db->show_string($listmodel[$i]['description']) 
						. '</option>';
                }
                $frm_str .= '</optgroup>';
            }
        }
        $frm_str .= '</select></td>';
        $frm_str .= '<td><span class="red_asterisk" id="destination_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr id="diff_list_tr" style="display:none;">';
        $frm_str .= '<td colspan="4">';
        $frm_str .= '<div id="diff_list_div" class="scroll_div" '
                //. 'style="height:200px; width:420px; border: 1px solid;"></div>';
                . 'style="width:420px; border: 1px solid;"></div>';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
    }
    
    /*** Process limit date ***/
    $frm_str .= '<tr id="process_limit_date_use_tr" style="display:'.$display_value.';">';
    $frm_str .='<td style="width:30px;align:center;">&nbsp;</td><td class="indexing_label"><label for="process_limit_date_use" class="form_title" >'
        . _PROCESS_LIMIT_DATE_USE.'</label></td>';
    $frm_str .='<td class="indexing_field"><input type="radio"  class="check" name="process_limit_date_use" id="process_limit_date_use_yes" value="yes" ';
    if ($data['process_limit_date_use'] == true || !isset($data['process_limit_date_use'])) {
        $frm_str .=' checked="checked"';
    }
    $frm_str .=' onclick="clear_error(\'frm_error_'.$id_action.'\');activate_process_date(true, \''.$display_value.'\');" />' 
        . _YES.'<input type="radio" name="process_limit_date_use"  class="check"  id="process_limit_date_use_no" value="no" onclick="clear_error(\'frm_error_'
        . $id_action.'\');activate_process_date(false, \''.$display_value.'\');" ';
    if (isset($data['process_limit_date_use']) && $data['process_limit_date_use'] == false) {
        $frm_str .=' checked="checked"';
    }
    $frm_str .='/>'._NO.'</td>';
    $frm_str .= '<td><span class="red_asterisk" id="process_limit_date_use_mandatory" style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    $frm_str .= '<tr id="process_limit_date_tr" style="display:'.$display_value.';">';
    $frm_str .='<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'process_limit_date.png" alt="' . _PROCESS_LIMIT_DATE 
                . '"/></td><td class="indexing_label"><label for="process_limit_date" class="form_title" >'
                . _PROCESS_LIMIT_DATE.'</label></td>';
    $frm_str .='<td class="indexing_field"><input name="process_limit_date" type="text" id="process_limit_date"  onclick="clear_error(\'frm_error_'
        . $id_action.'\');showCalender(this);" value="';
    if (isset($data['process_limit_date'])&& !empty($data['process_limit_date'])) {
        $frm_str .= $data['process_limit_date'];
    }
    $frm_str .='"/></td>';
    $frm_str .= '<td><span class="red_asterisk" id="process_limit_date_mandatory" style="display:inline;">*</span>&nbsp;</td>';
    $frm_str .= '</tr>';
    
    /*** Status ***/
    // Select statuses from groupbasket
    $statuses = array();
    $db = new dbquery();
    $db->connect();
    $query = "SELECT status_id, label_status FROM " . GROUPBASKET_STATUS . " left join " . $_SESSION['tablename']['status']
        . " on status_id = id "
        . " where basket_id= '" . $_SESSION['current_basket']['id']
        . "' and group_id = '" . $_SESSION['user']['primarygroup']
        . "' and action_id = " . $id_action;
    $db->query($query);

    if ($db->nb_result() > 0) {
        while ($status = $db->fetch_object()) {
            $statuses[] = array(
                'ID' => $status->status_id,
                'LABEL' => $db->show_string($status->label_status)
            );
        }
    }
    if (count($statuses) > 0) {
        $frm_str .= '<tr id="status" style="display:' . $display_value . ';">';
        $frm_str .= '<td><td style="width:30px;align:center;">&nbsp;</td><label for="status" class="form_title" >' . _STATUS
                . '</label></td>';
        $frm_str .= '<td class="indexing_field"><select name="status" '
                . 'id="status" onchange="clear_error(\'frm_error_' . $id_action
                . '\');">';
        $frm_str .= '<option value="">' . _CHOOSE_STATUS . '</option>';
        for ($i = 0; $i < count($statuses); $i ++) {
            $frm_str .= '<option value="' . $statuses[$i]['ID'] . '" ';
            if ($statuses[$i]['ID'] == 'NEW') {
                $frm_str .= 'selected="selected"';
            }
            $frm_str .= '>' . $statuses[$i]['LABEL'] . '</option>';
        }
        $frm_str .= '</select></td><td><span class="red_asterisk" id="market_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frm_str .= '</tr>';
    }
    
    $frm_str .= '</table>';

    $frm_str .= '</div>';
    $frm_str .= '</div>';

    /*** CUSTOM INDEXES ***/
    $frm_str .= '<div id="comp_indexes" style="display:block;">';
    $frm_str .= '</div>';
        
    /*** Complementary fields ***/
    $frm_str .= '<hr />';
    
    $frm_str .= '<h4 onclick="new Effect.toggle(\'complementary_fields\', \'blind\', {delay:0.2});'
        . 'whatIsTheDivStatus(\'complementary_fields\', \'divStatus_complementary_fields\');" '
        . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
    $frm_str .= ' <span id="divStatus_complementary_fields" style="color:#1C99C5;"><<</span>&nbsp;' 
        . _OPT_INDEXES;
    $frm_str .= '</h4>';
    $frm_str .= '<div id="complementary_fields"  style="display:none">';
    $frm_str .= '<div>';
    
    $frm_str .= '<table width="100%" align="center" border="0" '
        . 'id="indexing_fields" style="display:block;">';

    /*** Folder  ***/
    if ($core_tools->is_module_loaded('folder')) {
        $folder = '';
        if (isset($data['folder'])&& !empty($data['folder']))
        {
            $folder = $data['folder'];
        }
        $frm_str .= '<tr id="folder_tr" style="display:'.$display_value.';">';
        $frm_str .= '<td style="width:30px;align:center;"><img src="'
            . $_SESSION['config']['businessappurl'] . 'static.php?module=folder&filename='
            . 'folders.gif" alt="' . _FOLDER 
            . '"/></td><td><label for="folder" class="form_title" >' . _FOLDER . '</label></td>';
        $frm_str .='<td><input type="text" name="folder" id="folder" value="'
            . $folder . '" onblur=""/><div id="show_folder" class="autocomplete"></div>';
        $frm_str .= '</tr>';
    }

    if ($core_tools->is_module_loaded('tags') 
        && ($core_tools->test_service('tag_view', 'tags', false) == 1)
    ) {
        include_once('modules/tags/templates/validate_mail/index.php');
    }

    $frm_str .= '</table>';
    
    $frm_str .= '</div>';
    $frm_str .= '</div>';

    /*** Actions ***/
    $frm_str .= '<hr width="90%" align="center"/>';
    $frm_str .= '<p align="center">';
        $frm_str .= '<b>'._ACTIONS.' : </b>';

        $actions  = $b->get_actions_from_current_basket($res_id, $coll_id, 'PAGE_USE');
        if (count($actions) > 0)
        {
            $frm_str .='<select name="chosen_action" id="chosen_action">';
                $frm_str .='<option value="">'._CHOOSE_ACTION.'</option>';
                for($ind_act = 0; $ind_act < count($actions);$ind_act++)
                {
                    $frm_str .='<option value="'.$actions[$ind_act]['VALUE'].'"';
                    if ($ind_act==0)
                    {
                        $frm_str .= 'selected="selected"';
                    }
                    $frm_str .= '>'.$actions[$ind_act]['LABEL'].'</option>';
                }
            $frm_str .='</select> ';
            $frm_str .= '<input type="button" name="send" id="send" value="'
                . _VALIDATE . '" class="button" onclick="valid_action_form( \'index_file\', \'' 
                . $path_manage_action.'\', \'' . $id_action.'\', \''.$res_id.'\', \''
                . $table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
        }
        $frm_str .= '<input name="close" id="close" type="button" value="' 
            . _CANCEL.'" class="button" onclick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'
            . $id_action.'\');reinit();"/>';
    $frm_str .= '</p>';
$frm_str .= '</form>';
$frm_str .= '</div>';
$frm_str .= '</div>';
$frm_str .= '</div>';

        $frm_str .= '<div id="validright">';
        
        /*** TOOLBAR ***/
        $frm_str .= '<div class="block" align="center" style="height:20px;width=95%;">';
        
        $frm_str .= '<table width="95%" cellpadding="0" cellspacing="0">';
        $frm_str .= '<tr align="center">';
        
        //CONTACT
         if ($_SESSION['features']['personal_contact'] == "true"
        ) {
            $frm_str .= '<td>';
            $frm_str .= '|<span onclick="new Effect.toggle(\'create_contact_div\', \'appear\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'create_contact_div\', \'divStatus_create_contact_div\');return false;" '
                . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
            $frm_str .= '<span id="divStatus_create_contact_div" style="color:#1C99C5;"><<</span><b>'
                . '<small>' . _CREATE_CONTACT . '</small>';
            $frm_str .= '</b></span>|';
            $frm_str .= '</td>';
        }
        
        // HISTORY
        $frm_str .= '<td>';
        $frm_str .= '|<span onclick="new Effect.toggle(\'history_div\', \'appear\', {delay:0.2});'
            . 'whatIsTheDivStatus(\'history_div\', \'divStatus_history_div\');return false;" '
            . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
        $frm_str .= '<span id="divStatus_history_div" style="color:#1C99C5;"><<</span><b>'
           . '<small>' . _DOC_HISTORY . '</small>';
        $frm_str .= '</b></span>|';
        $frm_str .= '</td>';
        
        //NOTE
        if ($core_tools->is_module_loaded('notes')) {
            $frm_str .= '<td>';
            require_once 'modules/notes/class/class_modules_tools.php';
            $notes_tools    = new notes();
            //Count notes
            $nbr_notes = $notes_tools->countUserNotes($res_id, $coll_id);
            $nbr_notes = ' ('.$nbr_notes.')';
            $frm_str .= '|<span onclick="new Effect.toggle(\'notes_div\', \'appear\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'notes_div\', \'divStatus_notes_div\');return false;" '
                . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
            $frm_str .= '<span id="divStatus_notes_div" style="color:#1C99C5;"><<</span><b>'
                . '<small>' . _NOTES . $nbr_notes . '</small>';
            $frm_str .= '</b></span>|';
            $frm_str .= '</td>';
        }
        
        //ATTACHMENTS
        if ($core_tools->is_module_loaded('attachments')) {
            $frm_str .= '<td>';
            $req = new request;
            $req->connect();
            $req->query("select res_id from "
                . $_SESSION['tablename']['attach_res_attachments']
                . " where status <> 'DEL' and res_id_master = " . $res_id . " and coll_id = '" . $coll_id . "'");
            if ($req->nb_result() > 0) {
                $nb_attach = $req->nb_result();
            } else {
                $nb_attach = 0;
            }
            if ($answer <> '') {
                $answer .= ': ';
            }
            $frm_str .= '|<span onclick="new Effect.toggle(\'list_answers_div\', \'appear\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'list_answers_div\', \'divStatus_done_answers_div\');return false;" '
                . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
            $frm_str .= '<span id="divStatus_done_answers_div" style="color:#1C99C5;"><<</span><b>'
                . '<small>' . _PJ . ' (' . $answer .'<span id="nb_attach">' . $nb_attach . '</span>)</small>';
            $frm_str .= '</b></span>|';
            $frm_str .= '</td>';
        }
        
        //test service add new version
        $viewVersions = false;
        if ($core->test_service('add_new_version', 'apps', false)) {
            $viewVersions = true;
        }
        //VERSIONS
        if ($core->is_module_loaded('content_management') && $viewVersions) {
            $versionTable = $sec->retrieve_version_table_from_coll_id(
                $coll_id
            );
            $selectVersions = "select res_id from "
                . $versionTable . " where res_id_master = "
                . $res_id . " and status <> 'DEL' order by res_id desc";
            $dbVersions = new dbquery();
            $dbVersions->connect();
            $dbVersions->query($selectVersions);
            $nb_versions_for_title = $dbVersions->nb_result();
            $lineLastVersion = $dbVersions->fetch_object();
            $lastVersion = $lineLastVersion->res_id;
            if ($lastVersion <> '') {
                $objectId = $lastVersion;
                $objectTable = $versionTable;
            } else {
                $objectTable = $sec->retrieve_table_from_coll(
                    $coll_id
                );
                $objectId = $res_id;
                $_SESSION['cm']['objectId4List'] = $res_id;
            }
            if ($nb_versions_for_title == 0) {
                $extend_title_for_versions = '0';
            } else {
                $extend_title_for_versions = $nb_versions_for_title;
            }
            $_SESSION['cm']['resMaster'] = '';
            $frm_str .= '<td>';
            $frm_str .= '|<span onclick="new Effect.toggle(\'versions_div\', \'appear\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'versions_div\', \'divStatus_versions_div\');return false;" '
                . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
            $frm_str .= '<span id="divStatus_versions_div" style="color:#1C99C5;"><<</span><b>'
                . '<small>' . _VERSIONS . ' (<span id="nbVersions">' . $extend_title_for_versions . '</span>)</small>';
            $frm_str .= '</b></span>|';
            $frm_str .= '</td>';
        }
        
        //LINKS
        $frm_str .= '<td>';
        require_once('core/class/LinkController.php');
        $Class_LinkController = new LinkController();
        $nbLink = $Class_LinkController->nbDirectLink(
            $res_id,
            $coll_id,
            'all'
        );
        $frm_str .= '|<span onclick="new Effect.toggle(\'links_div\', \'appear\', {delay:0.2});'
            . 'whatIsTheDivStatus(\'links_div\', \'divStatus_links_div\');return false;" '
            . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
        $frm_str .= '<span id="divStatus_links_div" style="color:#1C99C5;"><<</span><b>'
             . '<small>' . _LINK_TAB . ' (<span id="nbLinks">' . $nbLink . '</span>)</small>';
        $frm_str .= '</b></span>|';
        $frm_str .= '</td>';
        
        //END TOOLBAR
        $frm_str .= '</table>';
        $frm_str .= '</div>';
        
        //FRAME FOR TOOLS
        
        //CONTACT CREATION
        $frm_str .= '<div id="create_contact_div" style="display:none">';
        $frm_str .= '<div>';
        $frm_str .= '<fieldset style="border:1px solid;">';
        $frm_str .= '<legend ><b>'._CREATE_CONTACT.'</b></legend>';
        $frm_str .= '<form name="indexingfrmcontact" id="indexingfrmcontact" method="post" action="' 
            . $_SESSION['config']['businessappurl'].'index.php?display=true&mpage=contact_info" >';
        $frm_str .= '<table>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .= '<label for="is_corporate">'._IS_CORPORATE_PERSON.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input type="radio" class="check" name="is_corporate" id="is_corporate_Y" value="Y" ';
        $frm_str .=' checked="checked"';
        $frm_str .= 'onclick="javascript:show_admin_contacts(true, \''.$display_value.'\');">'._YES;
        $frm_str .='<input type="radio" id="is_corporate_N" class="check" name="is_corporate" value="N"';
        $frm_str .=' onclick="javascript:show_admin_contacts( false, \''.$display_value.'\');"/>'._NO;
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr id="title_p" style="display:';
        $frm_str .= $display_value;
        $frm_str .='">';
        $frm_str .= '<td  colspan="2">';
        $frm_str .='<label for="title">'._TITLE2.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2" >';
        $frm_str .='<select name="title" id="title" >';
        $frm_str .='<option value="">'._CHOOSE_TITLE.'</option>';
        foreach (array_keys($titles) as $key) {
            $frm_str .='<option value="'.$key.'" ';
            if ($key == $default_title) {
                $frm_str .= 'selected="selected"';
            }
            $frm_str .='>'.$titles[$key].'</option>';
        }
        $frm_str .='</select>';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr id="lastname_p" style="display:';
        $frm_str .= $display_value;
        $frm_str .='">';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="lastname">'._LASTNAME.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="lastname" type="text"  id="lastname" value="" /> ';
        $frm_str .='<span class="red_asterisk" id="lastname_mandatory" style="display:inline;">*</span>';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr id="firstname_p" style="display:';
        $frm_str .= $display_value;
        $frm_str .='">';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="firstname">'._FIRSTNAME.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="firstname" type="text"  id="firstname" value=""/>';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="society">'._SOCIETY.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="society" type="text"  id="society" value="" />';
        $frm_str .='<span class="red_asterisk" id="society_mandatory" style="display:inline;">*</span>';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr id="function_p" style="display:';
        $frm_str .= 'block';
        $frm_str .='">';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="function">'._FUNCTION.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="function" type="text"  id="function" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="phone">'._PHONE.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="phone" type="text"  id="phone" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="mail">'._MAIL.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="mail" type="text" id="mail" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td>';
        $frm_str .='<label for="num">'._NUM.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td>';
        $frm_str .='<input name="num" type="text" class="small"  id="num" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '<td>';
        $frm_str .='<label for="street">'._STREET.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td>';
        $frm_str .='<input name="street" type="text" class="medium"  id="street" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="add_comp">'._COMPLEMENT.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="add_comp" type="text"  id="add_comp" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td>';
        $frm_str .='<label for="cp">'._POSTAL_CODE.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td>';
        $frm_str .='<input name="cp" type="text" id="cp" value="" class="small" />';
        $frm_str .= '</td>';
        $frm_str .= '<td>';
        $frm_str .='<label for="town">'._TOWN.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td>';
        $frm_str .='<input name="town" type="text" id="town" value="" class="medium" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="country">'._COUNTRY.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<input name="country" type="text"  id="country" value="" />';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="comp_data">'._COMP_DATA.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<textarea name="comp_data" id="comp_data" ></textarea>';
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '<tr>';
        $frm_str .= '<td colspan="2">';
        $frm_str .='<label for="is_private">'._IS_PRIVATE.' : </label>';
        $frm_str .= '</td>';
        $frm_str .= '<td colspan="2">';
        $frm_str .= '<input type="radio" class="check" name="is_private" '
            . 'id="is_private" value="Y"/>' . _YES;
        $frm_str .= '<input type="radio" id="is_private_N" class="check" '
            . 'name="is_private" value="N" checked="checked"/>' . _NO;
        $frm_str .= '</td>';
        $frm_str .= '</tr>';
        $frm_str .= '</table>';
        $frm_str .='<div align="center">';
        $frm_str .='<input name="submit" type="button" value="'._VALIDATE.'"  class="button" onclick="create_contact(\'' 
            . $_SESSION['config']['businessappurl'].'index.php?display=true&page=create_contact\', \''.$id_action.'\');" />';
        $frm_str .=' <input name="cancel" type="button" value="'._CANCEL 
            . '"  onclick="new Effect.toggle(\'create_contact_div\', \'blind\', {delay:0.2});clear_form(\'indexingfrmcontact\');return false;" class="button" />';
        $frm_str .='</div>';
        $frm_str .= '</fieldset >';
        $frm_str .='</form >';
        $frm_str .= '</div><br/>';
        $frm_str .= '<hr />';
        $frm_str .= '</div>';
        $frm_str .= '<script type="text/javascript">show_admin_contacts(true);</script>';
        
        //HISTORY FRAME
        $frm_str .= '<div class="desc" id="history_div" style="display:none">';
        $frm_str .= '<div class="ref-unit">';
        $frm_str .= '<center><h2 onclick="new Effect.toggle(\'history_div\', \'blind\', {delay:0.2});';
        $frm_str .= 'whatIsTheDivStatus(\'history_div\', \'divStatus_history_div\');';
        $frm_str .= 'return false;" onmouseover="this.style.cursor=\'pointer\';">' . _HISTORY. '</h2></center>';
        $frm_str .= '<iframe src="'
            . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page=document_history&id='
            . $res_id . '&coll_id=' . $coll_id . '&load&size=medium" '
            . 'name="hist_doc_process" width="100%" height="690px" align="center" '
            . 'scrolling="auto" frameborder="0" id="hist_doc_process" style="height: 690px; max-height: 690px; overflow: scroll;"></iframe>';
        $frm_str .= '</div>';
        $frm_str .= '<hr />';
        $frm_str .= '</div>';
        
        //NOTES
        if ($core_tools->is_module_loaded('notes')) {
            //Iframe notes
            $frm_str .= '<div id="notes_div" style="display:none;">';
            $frm_str .= '<div class="ref-unit">';
            $frm_str .= '<center><h2 onclick="new Effect.toggle(\'notes_div\', \'blind\', {delay:0.2});';
            $frm_str .= 'whatIsTheDivStatus(\'notes_div\', \'divStatus_notes_div\');';
            $frm_str .= 'return false;" onmouseover="this.style.cursor=\'pointer\';">' . _NOTES. '</h2></center>';
            $frm_str .= '<iframe name="list_notes_doc" id="list_notes_doc" src="'
                . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&module=notes&page=notes&identifier='
                . $res_id . '&origin=document&coll_id=' . $coll_id . '&load&size=medium"'
                . ' frameborder="0" width="100%" height="650px"></iframe>';
            $frm_str .= '</div>';
            $frm_str .= '<hr />';
            $frm_str .= '</div>';
        }
        
        //ATTACHMENTS
        if ($core_tools->is_module_loaded('attachments')) {
            require 'modules/templates/class/templates_controler.php';
            $templatesControler = new templates_controler();
            $templates = array();
            $templates = $templatesControler->getAllTemplatesForProcess($data['destination']);
            
            $frm_str .= '<div id="list_answers_div" style="display:none">';

            $frm_str .= '<center><h2>';
            $frm_str .= _ATTACHMENTS . ', ' . _DONE_ANSWERS . '</h2></center>';
            $req = new request;
            $req->connect();
            $req->query("select res_id from ".$_SESSION['tablename']['attach_res_attachments']
                . " where status = 'NEW' and res_id_master = " . $res_id . " and coll_id = '" . $coll_id ."'");
            //$req->show();
            $nb_attach = 0;
            if ($req->nb_result() > 0) {
                $nb_attach = $req->nb_result();
            }
            $frm_str .= '<center>';
            if ($core_tools->is_module_loaded('templates')) {
                $objectTable = $sec->retrieve_table_from_coll($coll_id);
                $frm_str .= _GENERATE_ATTACHMENT_FROM 
                    . ' <br><select name="templateOffice" id="templateOffice" style="width:250px" onchange="';
                //$frm_str .= 'loadApplet(\''
                $frm_str .= 'window.open(\''
                    . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
                    . '&module=content_management&page=applet_popup_launcher&objectType=attachmentFromTemplate'
                    . '&objectId='
                    . '\' + $(\'templateOffice\').value + \''
                    . '&objectTable='
                    . $objectTable
                    . '&resMaster='
                    . $res_id
                    //. '\', $(\'templateOffice\').value);">';
                    . '\', \'\', \'height=301, width=301,scrollbars=no,resizable=no,directories=no,toolbar=no\');">';
                    $frm_str .= '<option value="">' . _OFFICE . '</option>';
                        for ($i=0;$i<count($templates);$i++) {
                            if ($templates[$i]['TYPE'] == 'OFFICE') {
                                $frm_str .= '<option value="';
                                    $frm_str .= $templates[$i]['ID'];
                                    $frm_str .= '">';
                                    //$frm_str .= $templates[$i]['TYPE'] . ' : ';
                                    $frm_str .= $templates[$i]['LABEL'];
                                }
                            $frm_str .= '</option>';
                        }
                    $frm_str .= '</select>&nbsp;|&nbsp;';
                    $frm_str .= '<select name="templateHtml" id="templateHtml" style="width:250px" '
                        //. 'onchange="window.alert(\'\' + $(\'templateHtml\').value + \'\');">';
                        . 'onchange="checkBeforeOpenBlank(\''
                        . $_SESSION['config']['businessappurl']
                        . 'index.php?display=true&module=templates&page=generate_attachment_html&mode=add&template='
                        . '\' + $(\'templateHtml\').value + \''
                        . '&res_id=' . $res_id
                        . '&coll_id=' . $coll_id
                        . '\', $(\'templateHtml\').value);">';
                    $frm_str .= '<option value="">' . _HTML . '</option>';
                        for ($i=0;$i<count($templates);$i++) {
                            if ($templates[$i]['TYPE'] == 'HTML') {
                                $frm_str .= '<option value="';
                                    $frm_str .= $templates[$i]['ID'];
                                    $frm_str .= '">';
                                    //$frm_str .= $templates[$i]['TYPE'] . ' : ';
                                    $frm_str .= $templates[$i]['LABEL'];
                                }
                            $frm_str .= '</option>';
                        }
                    $frm_str .= '</select><br>' . _OR . '&nbsp;';
                    $frm_str .= '<input type="button" name="attach" id="attach" class="button" value="'
                        . _ATTACH_FROM_HDD
                        . '" onclick="javascript:window.open(\'' . $_SESSION['config']['businessappurl']
                        . 'index.php?display=true&module=attachments&page=join_file\',\'\', \'scrollbars=yes,'
                        . 'menubar=no,toolbar=no,resizable=yes,status=no,width=550,height=200\');" />';
                }
                $frm_str .= '</center><iframe name="list_attach" id="list_attach" src="'
                . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&module=attachments&page=frame_list_attachments&resId=' . $res_id . '" '
                . 'frameborder="0" width="100%" height="450px"></iframe>';
            $frm_str .= '<hr />';
            $frm_str .= '</div>';
        }
        
        //VERSIONS FRAME
        //test service add new version
        $addNewVersion = false;
        if ($core->test_service('add_new_version', 'apps', false)) {
            $addNewVersion = true;
        }
        $frm_str .= '<div id="versions_div" style="display:none" onmouseover="this.style.cursor=\'pointer\';">';
            $frm_str .= '<div>';
                    //$frm_str .= '<center><h2>' . _VERSIONS . '</h2></center>';
                    $frm_str .= '<h2 onclick="new Effect.toggle(\'versions_div\', \'blind\', {delay:0.2});';
                    $frm_str .= 'whatIsTheDivStatus(\'versions_div\', \'divStatus_versions_div\');';
                        $frm_str .= 'return false;">';
                        $frm_str .= '<center>' . _VERSIONS . '</center>';
                    $frm_str .= '</h2>';
                    $frm_str .= '<div class="error" id="divError" name="divError"></div>';
                    $frm_str .= '<div style="text-align:center;">';
                        $frm_str .= '<a href="';
                            $frm_str .=  $_SESSION['config']['businessappurl'];
                            $frm_str .= 'index.php?display=true&dir=indexing_searching&page=view_resource_controler&original&id=';
                            $frm_str .= $res_id;
                            $frm_str .= '" target="_blank">';
                            $frm_str .= '<img alt="' . _VIEW_ORIGINAL . '" src="';
                            $frm_str .= $_SESSION['config']['businessappurl'];
                            $frm_str .= 'static.php?filename=picto_dld.gif" border="0" alt="" />';
                            $frm_str .= _VIEW_ORIGINAL . ' | ';
                        $frm_str .= '</a>';
                        if ($addNewVersion) {
                            $_SESSION['cm']['objectTable'] = $objectTable;
                            $frm_str .= '<div id="createVersion" style="display: inline;"></div>';
                        }
                        $frm_str .= '<div id="loadVersions"></div>';
                        $frm_str .= '<script language="javascript">';
                            $frm_str .= 'showDiv("loadVersions", "nbVersions", "createVersion", "';
                                $frm_str .= $_SESSION['urltomodules'];
                                $frm_str .= 'content_management/list_versions.php")';
                        $frm_str .= '</script>';
                    $frm_str .= '</div><br>';
            $frm_str .= '</div>';
            $frm_str .= '<hr />';
        $frm_str .= '</div>';
        
        //LINKS
        $frm_str .= '<div id="links_div" style="display:none" onmouseover="this.style.cursor=\'pointer\';">';
        $frm_str .= '<div style="text-align: left;">';
        $frm_str .= '<h2>';
        $frm_str .= '<center>' . _LINK_TAB . '</center>';
        $frm_str .= '</h2>';
        $frm_str .= '<div id="loadLinks">';
        $nbLinkDesc = $Class_LinkController->nbDirectLink(
            $res_id,
            $coll_id,
            'desc'
        );
        if ($nbLinkDesc > 0) {
            $frm_str .= '<img src="static.php?filename=cat_doc_incoming.gif"/>';
            $frm_str .= $Class_LinkController->formatMap(
                $Class_LinkController->getMap(
                    $res_id,
                    $coll_id,
                    'desc'
                ),
                'desc'
            );
            $frm_str .= '<br />';
        }
        $nbLinkAsc = $Class_LinkController->nbDirectLink(
            $res_id,
            $coll_id,
            'asc'
        );
        if ($nbLinkAsc > 0) {
            $frm_str .= '<img src="static.php?filename=cat_doc_outgoing.gif" />';
            $frm_str .= $Class_LinkController->formatMap(
                $Class_LinkController->getMap(
                    $res_id,
                    $coll_id,
                    'asc'
                ),
                'asc'
            );
            $frm_str .= '<br />';
        }
        $frm_str .= '</div>';
        if ($core_tools->test_service('add_links', 'apps', false)) {
            include 'apps/'.$_SESSION['config']['app_id'].'/add_links.php';
            $frm_str .= $Links;
        }
        $frm_str .= '</div>';
        $frm_str .= '<hr />';
        $frm_str .= '</div>';
        
        //DOCUMENT VIEWER
        $path_file = get_file_path($res_id, $coll_id);
        $frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&id='
            . $res_id.'&coll_id='.$coll_id.'" name="viewframevalid" id="viewframevalid"  scrolling="auto" frameborder="0" ></iframe>';
            
        //END RIGHT DIV
        $frm_str .= '</div>';

        /*** Extra javascript ***/
        $frm_str .= '<script type="text/javascript">resize_frame_process("modal_' 
            . $id_action . '", "viewframevalid", true, true);resize_frame_process("modal_' 
            . $id_action . '", "hist_doc", true, false);window.scrollTo(0,0);'
            . 'change_category(\''
            . $data['category_id']['value'] . '\', \'' . $display_value
            . '\', \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'change_category\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=get_content_js\');launch_autocompleter_contacts(\'' 
            . $_SESSION['config']['businessappurl'].'index.php?display=true'
            . '&dir=indexing_searching&page=autocomplete_contacts\');convertAllBusinessAmount();';
        if ($core_tools->is_module_loaded('folder')) {
            $frm_str .= ' initList(\'folder\', \'show_folder\',\''
                . $_SESSION['config']['businessappurl'] . 'index.php?display='
                . 'true&module=folder&page=autocomplete_folders&mode=folder\','
                . ' \'Input\', \'2\');';   
        }


        $frm_str .= '$(\'baskets\').style.visibility=\'hidden\';var item = $(\'valid_div\'); if (item){item.style.display=\'block\';}';
        $frm_str .='var type_id = $(\'type_id\');';
        $frm_str .='if (type_id){change_doctype(type_id.options[type_id.selectedIndex].value, \'' 
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=indexing_searching&page=change_doctype&coll_id=' 
            . $coll_id . '\', \''._ERROR_DOCTYPE.'\', \''.$id_action.'\', \''.$_SESSION['config']['businessappurl'].'index.php?display=true&page=get_content_js\' , \''
            . $display_value.'\', '.$res_id.', \''. $coll_id.'\', true);}';
        if ($core_tools->is_module_loaded('entities') && isset($data['destination'])) {
            $frm_str .='change_entity(\''.$data['destination'].'\', \'' 
            . $_SESSION['config']['businessappurl'] 
            . 'index.php?display=true&module=entities&page=load_listinstance'.'\',\'diff_list_div\', \'indexing\', \''.$display_value.'\'';
            if (!$load_listmodel) {
                $frm_str .= ',\'false\'';
            }
            $frm_str .= ');';
        }
        $frm_str .='</script>';
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
    $_SESSION['action_error'] = '';
    if (count($values) < 1 || empty($form_id)) {
        $_SESSION['action_error'] =  _FORM_ERROR;
        return false;
    } else {
        $attach = get_value_fields($values, 'attach');
        $coll_id = get_value_fields($values, 'coll_id');
        if ($attach) {
            $idDoc = get_value_fields($values, 'res_id');
            if (! $idDoc || empty($idDoc)) {
                $_SESSION['action_error'] .= _LINK_REFERENCE . '<br/>';
            }
            if (! empty($_SESSION['action_error'])) {
                return false;
            }
        }
        $cat_id = get_value_fields($values, 'category_id');
        if ($cat_id == false) {
            $_SESSION['action_error'] = _CATEGORY.' '._IS_EMPTY;
            return false;
        }
        $no_error = process_category_check($cat_id, $values);
        return $no_error;
    }
}


/**
 * Checks the values of the action form for a given category
 *
 * @param $catId String Category identifier
 * @param $values Array Values of the form to check
 * @return Bool true if no error, false otherwise
 **/
function process_category_check($catId, $values)
{
    $core = new core_tools();
    // Simple cases
    for ($i = 0; $i < count($values); $i ++) {
        //var_dump($_ENV['categories'][$catId][$tmpId]);
        if (! isset($values[$i]['ID'])) {
            $tmpId = 'none';
        } else {
            $tmpId = $values[$i]['ID'];
        }

        if (isset($_ENV['categories'][$catId][$tmpId]['mandatory'])
           && $_ENV['categories'][$catId][$tmpId]['mandatory'] == true
           && empty($values[$i]['VALUE'])
        ) {
            $_SESSION['action_error'] = $_ENV['categories'][$catId][$tmpId]['label']
                                      . ' ' . _IS_EMPTY;
            return false;
        }
        if (isset($_ENV['categories'][$catId][$tmpId]['type_form'])
            && $_ENV['categories'][$catId][$tmpId]['type_form'] == 'date'
            && ! empty($values[$i]['VALUE'])
            && preg_match($_ENV['date_pattern'], $values[$i]['VALUE']) == 0
        ) {
            $_SESSION['action_error'] = $_ENV['categories'][$catId][$tmpId]['label']
                                      . ' ' . _WRONG_FORMAT;
            return false;
        }
        if (isset($_ENV['categories'][$catId][$tmpId]['type_form'])
            && $_ENV['categories'][$catId][$tmpId]['type_form'] == 'integer'
            && ! empty($values[$i]['VALUE'])
            && ! is_numeric($values[$i]['VALUE'])
        ) {
            $_SESSION['action_error'] = $_ENV['categories'][$catId][$tmpId]['label']
                                      . ' ' . _WRONG_FORMAT . ' ' . $values[$i]['VALUE'];
            return false;
        }
        if (isset($_ENV['categories'][$catId][$tmpId]['type_form'])
            && $_ENV['categories'][$catId][$tmpId]['type_form'] == 'radio'
            && ! empty($values[$i]['VALUE'])
            && ! in_array(
                $values[$i]['VALUE'], $_ENV['categories'][$catId][$tmpId]['values']
            )
        ) {
            $_SESSION['action_error'] = $_ENV['categories'][$catId][$tmpId]['label']
                                      . ' ' . _WRONG_FORMAT;
            return false;
        }
    }
    ///// Checks the complementary indexes depending on the doctype
    require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php';
    $type = new types();
    $typeId = get_value_fields($values, 'type_id');
    $collId = get_value_fields($values, 'coll_id');
    $indexes = $type->get_indexes($typeId, $collId, 'minimal');
    $valIndexes = array();
    for ($i = 0; $i < count($indexes); $i ++) {
        $valIndexes[$indexes[$i]] = get_value_fields($values, $indexes[$i]);
    }
    $testType = $type->check_indexes($typeId, $collId, $valIndexes );
    if (! $testType) {
        $_SESSION['action_error'] .= $_SESSION['error'];
        $_SESSION['error'] = '';
        return false;
    }

    ///////////////////////// Other cases
    // Process limit Date
    $_SESSION['store_process_limit_date'] = "";
    if (isset($_ENV['categories'][$catId]['other_cases']['process_limit_date'])) {
        $processLimitDateUseYes = get_value_fields(
            $values, 'process_limit_date_use_yes'
        );
        $processLimitDateUseNo = get_value_fields(
            $values, 'process_limit_date_use_no'
        );
        if ($processLimitDateUseYes == 'yes') {
            $_SESSION['store_process_limit_date'] = 'ok';
            $processLimitDate = get_value_fields($values, 'process_limit_date');
            if (trim($processLimitDate) == ""
                || preg_match($_ENV['date_pattern'], $processLimitDate) == 0
            ) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['process_limit_date']['label']
                    . ' ' . _WRONG_FORMAT;
                return false;
            }
        } else if ($processLimitDateUseNo == 'no') {
            $_SESSION['store_process_limit_date'] = 'ko';
        }
    }

    // Contact
    if (isset($_ENV['categories'][$catId]['contact_id'])) {
        $contact = get_value_fields($values, 'contact');
        if ($_ENV['categories'][$catId]['contact_id']['mandatory'] == true) {
            if (empty($contact)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['contact_id']['label']
                    . ' ' . _IS_EMPTY;
                return false;
            }
        }
        if (!empty($contact)) {
            if (preg_match('/\([0-9]+\)$/', $contact) == 0) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['contact_id']['label']
                    . ' ' . _WRONG_FORMAT . '.<br/>'. _USE_AUTOCOMPLETION;
                return false;
            }
        }
    }

    if ($core->is_module_loaded('entities')) {
        // Diffusion list
        if (isset($_ENV['categories'][$catId]['other_cases']['diff_list'])
            && $_ENV['categories'][$catId]['other_cases']['diff_list']['mandatory'] == true
        ) {
            if (empty($_SESSION['indexing']['diff_list'])
                || ! isset($_SESSION['indexing']['diff_list'])
            ) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['diff_list']['label']
                    . " " . _MANDATORY;
                return false;
            }
        }
    }
    
    if ($core->is_module_loaded('folder')) {
        $db = new dbquery();
        $db->connect();
        $folderId = '';
        
        $folder = get_value_fields($values, 'folder');
        if (isset($_ENV['categories'][$catId]['other_cases']['folder'])
            && $_ENV['categories'][$catId]['other_cases']['folder']['mandatory'] == true
        ) {
            if (empty($folder)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['folder']['label']
                    . ' ' . _IS_EMPTY;
                return false;
            }
        }
        if (!empty($folder)) {
            if (! preg_match('/\([0-9]+\)$/', $folder)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['folder']['label']
                    . " " . _WRONG_FORMAT;
                return false;
            }
            $folderId = str_replace(
                ')', '', substr($folder, strrpos($folder, '(') + 1)
            );
            $db->query(
                "select folders_system_id from " . FOLD_FOLDERS_TABLE
                . " where folders_system_id = " . $folderId
            );
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _FOLDER . ' ' . $folderId . ' '
                                          . _UNKNOWN;
                return false;
            }
        }
        
        if (! empty($typeId ) && ! empty($folderId)) {
            $foldertypeId = '';
            
            $db->query(
                "select foldertype_id from " . FOLD_FOLDERS_TABLE
                ." where folders_system_id = " . $folderId
            );
            $res = $db->fetch_object();
            $foldertypeId = $res->foldertype_id;
            $db->query(
                "select fdl.foldertype_id from "
                . FOLD_FOLDERTYPES_DOCTYPES_LEVEL1_TABLE . " fdl, "
                . DOCTYPES_TABLE . " d where d.doctypes_first_level_id = "
                . "fdl.doctypes_first_level_id and fdl.foldertype_id = "
                . $foldertypeId . " and d.type_id = " . $typeId
            );
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] .= _ERROR_COMPATIBILITY_FOLDER;
                return false;
            }
        }
    }
    return true;
}

/**
 * Get the value of a given field in the values returned by the form
 *
 * @param $values Array Values of the form to check
 * @param $field String the field
 * @return String the value, false if the field is not found
 **/
function get_value_fields($values, $field)
{
    for ($i=0;$i<count($values);$i++) {
        if ($values[$i]['ID'] == $field) {
            return  $values[$i]['VALUE'];
        }
    }
    return false;
}

/**
 * Action of the form : update the database
 *
 * @param $arr_id Array Contains the res_id of the document to validate
 * @param $history String Log the action in history table or not
 * @param $id_action String Action identifier
 * @param $label_action String Action label
 * @param $status String  Not used here
 * @param $coll_id String Collection identifier
 * @param $table String Table
 * @param $values_form String Values of the form to load
 **/
function manage_form($arr_id, $history, $id_action, $label_action, $status,  $coll_id, $table, $values_form )
{

    if (empty($values_form) || count($arr_id) < 1 || empty($coll_id)) {
        $_SESSION['action_error'] = _ERROR_MANAGE_FORM_ARGS;
        return false;
    }
    require_once('core/class/class_security.php');
    require_once('core/class/class_request.php');
    require_once('core/class/class_resource.php');
    $db = new dbquery();
    $sec = new security();
    $core = new core_tools();
    $resource = new resource();
    $table = $sec->retrieve_table_from_coll($coll_id);
    $ind_coll = $sec->get_ind_collection($coll_id);
    $table_ext = $_SESSION['collections'][$ind_coll]['extensions'][0];
    $res_id = $arr_id[0];

    $attach = get_value_fields($values_form, 'attach');

    if ($core->is_module_loaded('tags')) {
        include_once('modules/tags/tags_update.php');
    }

    $query_ext = "update ".$table_ext." set ";
    $query_res = "update ".$table." set ";

    $cat_id = get_value_fields($values_form, 'category_id');

    $query_ext .= " category_id = '".$cat_id."' " ;
    //$query_res .= " status = 'NEW' " ;


    // Specific indexes : values from the form
    // Simple cases
    for ($i=0; $i<count($values_form); $i++) {
        if (
            $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['type_field'] == 'integer' 
            && $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] <> 'none'
        ) {
            if ($values_form[$i]['VALUE'] == '') {
                $values_form[$i]['VALUE'] = '0';
            }
            if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'res') {
                $query_res .= ", ".$values_form[$i]['ID']." = ".$values_form[$i]['VALUE'];
            } else if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'coll_ext') {
                $query_ext .= ", ".$values_form[$i]['ID']." = ".$values_form[$i]['VALUE'];
            }
        } else if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['type_field'] == 'string' 
            && $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] <> 'none') {
            if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'res') {
                $query_res .= ", ".$values_form[$i]['ID']." = '".$db->protect_string_db($values_form[$i]['VALUE'])."'";
            } else if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'coll_ext') {
                $query_ext .= ", ".$values_form[$i]['ID']." = '".$db->protect_string_db($values_form[$i]['VALUE'])."'";
            }
        } else if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['type_field'] == 'date' 
            && $_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] <> 'none') {
            if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'res') {
                $query_res .= ", ".$values_form[$i]['ID']." = '".$db->format_date_db($values_form[$i]['VALUE'])."'";
            } else if ($_ENV['categories'][$cat_id][$values_form[$i]['ID']]['table'] == 'coll_ext') {
                $query_ext .= ", ".$values_form[$i]['ID']." = '".$db->format_date_db($values_form[$i]['VALUE'])."'";
            }
        }
    }
    
    $status_id = get_value_fields($values_form, 'status');
    if (empty($status_id) || $status_id === "") $status_id = 'BAD';
    $query_res .= ", status = '" . $status_id . "'";

    ///////////////////////// Other cases
    require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php');
    $type = new types();
    $type->inits_opt_indexes($coll_id, $res_id);
    $type_id =  get_value_fields($values_form, 'type_id');
    $indexes = $type->get_indexes( $type_id,$coll_id, 'minimal');
    $val_indexes = array();
    for ($i=0; $i<count($indexes);$i++) {
        $val_indexes[$indexes[$i]] =  get_value_fields($values_form, $indexes[$i]);
    }
    $query_res .=  $type->get_sql_update($type_id, $coll_id, $val_indexes);
    
    // Process limit Date
    if (isset($_ENV['categories'][$cat_id]['other_cases']['process_limit_date'])) {
        $process_limit_date = get_value_fields($values_form, 'process_limit_date');
        if ($_SESSION['store_process_limit_date'] == 'ok') {
            $query_ext .= ", process_limit_date = '" . $db->format_date_db($process_limit_date) . "'";
        }
        $_SESSION['store_process_limit_date'] = '';
    }

    // Contact
    if (isset($_ENV['categories'][$cat_id]['contact_id'])) {
        $contact = get_value_fields($values_form, 'contact');
        //echo 'contact '.$contact.', type '.$contact_type;
        $contact_id = str_replace(')', '', substr($contact, strrpos($contact,'(')+1));
        if ($contact_id <> '') {
            $query_ext .= ", contact_id = " . $contact_id;
        }
    }
    
    if ($core->is_module_loaded('folder')) {
        $folder_id = '';
        $db->connect();
        $db->query("select folders_system_id from " .$table ." where res_id = " . $res_id);
        $res = $db->fetch_object();
        $old_folder_id = $res->folders_system_id;
        $folder = get_value_fields($values_form, 'folder');
        $folder_id = str_replace(')', '', substr($folder, strrpos($folder,'(')+1));
        if (!empty($folder_id)) {
            $query_res .= ", folders_system_id = ".$folder_id."";
        } else if (empty($folder_id) && !empty($old_folder_id)) {
            $query_res .= ", folders_system_id = NULL";
        }
        if ($folder_id <> $old_folder_id && $_SESSION['history']['folderup']) {
            require_once('core/class/class_history.php');
            $hist = new history();
            $hist->add($_SESSION['tablename']['fold_folders'], $folder_id, "UP", 'folderup', 
                _DOC_NUM.$res_id._ADDED_TO_FOLDER, $_SESSION['config']['databasetype'],'apps');
            if (isset($old_folder_id) && !empty($old_folder_id)) {
                $hist->add($_SESSION['tablename']['fold_folders'], $old_folder_id, "UP", 'folderup', 
                    _DOC_NUM.$res_id._DELETED_FROM_FOLDER, $_SESSION['config']['databasetype'],'apps');
            }
        }
    }
   
    $query_res = preg_replace('/set ,/', 'set ', $query_res);
    //$query_res = substr($query_res, strpos($query_string, ','));
    $_SESSION['arbox_id'] = "";
    $db->connect();
    $db->query($query_res." where res_id =".$res_id);
    $db->query($query_ext." where res_id =".$res_id);
    //$db->show();
    if ($core->is_module_loaded('entities')) {
        $load_list_diff = true;
        if ($load_list_diff) {
            require_once('modules/entities/class/class_manage_listdiff.php');
            $diff_list = new diffusion_list();
            $params = array('mode'=> 'listinstance', 'table' => $_SESSION['tablename']['ent_listinstance'], 
                'coll_id' => $coll_id, 'res_id' => $res_id, 'user_id' => $_SESSION['user']['UserId']);
			$diff_list->load_list_db($_SESSION['indexing']['diff_list'], $params, 'DOC', $_SESSION['indexing']['diff_list']['difflist_type']);
			/*
            $origin = $_SESSION['origin'];
            $diff_list->save_listinstance(
                $_SESSION['indexing']['diff_list'], 
                $_SESSION['indexing']['diff_list']['object_type'],
                $coll_id, 
                $res_id, 
                $_SESSION['user']['UserId']
            );*/
        }
    }


    //$_SESSION['indexing'] = array();
    unset($_SESSION['upfile']);

    //$_SESSION['indexation'] = true;
    return array('result' => $res_id.'#', 'history_msg' => '');
}
