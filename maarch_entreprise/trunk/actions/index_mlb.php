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
* @brief   Action : indexing a file
*
* Open a modal box to displays the indexing form, make the form checks and loads
*  the result in database. Used by the core (manage_action.php page).
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

//$_SESSION['validStep'] = "ko";
include_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'definition_mail_categories.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
        . 'class_security.php';
require_once 'core/core_tables.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_request.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_resource.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_business_app_tools.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_types.php';
require_once 'modules' . DIRECTORY_SEPARATOR . 'basket' . DIRECTORY_SEPARATOR
    . 'class' . DIRECTORY_SEPARATOR . 'class_modules_tools.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_indexing_searching_app.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'docservers_controler.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_chrono.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_history.php';
$core = new core_tools();
if ($core->is_module_loaded('entities')) {
    require_once 'modules/entities/entities_tables.php';
}
if ($core->is_module_loaded('folder')) {
    require_once 'modules/folder/folder_tables.php';
}
if ($core->is_module_loaded('physical_archive')) {
    require_once 'modules' . DIRECTORY_SEPARATOR . 'physical_archive'
        . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
        . 'class_modules_tools.php';
    require_once 'modules/physical_archive/physical_archive_tables.php';
}

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
/**
 * Returns the indexing form text
 *
 * @param $values Array Not used here
 * @param $pathManageAction String Path to the PHP file called in Ajax
 * @param $actionId String Action identifier
 * @param $table String Table
 * @param $module String Origin of the action
 * @param $collId String Collection identifier
 * @param $mode String Action mode 'mass' or 'page'
 * @return String The form content text
 **/
function get_form_txt($values, $pathManageAction,  $actionId, $table,
$module, $collId, $mode )
{
    if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
        $ieBrowser = true;
        $displayValue = 'block';
    } else if (preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"])
        && ! preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"])
    ) {
        $ieBrowser = true;
        $displayValue = 'block';
    } else {
        $ieBrowser = false;
        $displayValue = 'table-row';
    }
    $_SESSION['req'] = "action";
    $resId = $values[0];
    $frmStr = '';

    $type = new types();
    $sec = new security();
    $b = new basket();
    $core = new core_tools();
    $business = new business_app_tools();
    if ($_SESSION['features']['show_types_tree'] == 'true') {
        $doctypes = $type->getArrayStructTypes($collId);
    } else {
        $doctypes = $type->getArrayTypes($collId);
    }
    $today = date('d-m-Y');
    $tmp = $business->get_titles();
    $titles = $tmp['titles'];
    $defaultTitle = $tmp['default_title'];
    if ($core->is_module_loaded('entities')) {
        $services = array();
        $db = new dbquery();
        $db->connect();

        if (count($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$actionId]['entities']) > 0) {
            $db->query(
                "select entity_id, entity_label, short_label from "
                . ENT_ENTITIES . " where entity_id in ("
                . $_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$actionId]['entities']
                . ") and enabled= 'Y' order by entity_label"
            );
            while ($res = $db->fetch_object()) {
                array_push(
                    $services,
                    array(
                        'ID' => $res->entity_id,
                        'LABEL' => $db->show_string($res->entity_label),
                        'SHORT_LABEL' => $db->show_string($res->short_label),
                    )
                );
            }
        }
    }

    if ($core->is_module_loaded('physical_archive')) {
        $boxes = array();
        $db = new dbquery();
        $db->connect();

        $db->query(
            "select arbox_id, title from " . PA_AR_BOXES
            . " where status = 'NEW' order by title"
        );
        while ($res = $db->fetch_object()) {
            array_push(
                $boxes,
                array(
                    'ID' => $res->arbox_id,
                    'LABEL' => $db->show_string($res->title),
                )
            );
        }
    }
    $frmStr .= '<div id="validleft">';
    $frmStr .= '<div id="index_div" style="display:none;";>';
    $frmStr .= '<h1 class="tit" id="action_title"><img src="'
            . $_SESSION['config']['businessappurl'] . 'static.php?filename='
            . 'file_index_b.gif"  align="middle" alt="" />' . _INDEXATION_TITLE;
    $frmStr .= '</h1>';
    $frmStr .= '<div id="frm_error_' . $actionId . '" class="indexing_error">'
            . '</div>';
    $frmStr .= '<form name="index_file" method="post" id="index_file" action="#"'
            . ' class="forms indexingform" style="text-align:left;">';
    $frmStr .= '<input type="hidden" name="values" id="values" value="' . $resId
            . '" />';
    $frmStr .= '<input type="hidden" name="action_id" id="action_id" value="'
            . $actionId . '" />';
    $frmStr .= '<input type="hidden" name="mode" id="mode" value="' . $mode
            . '" />';
    $frmStr .= '<input type="hidden" name="table" id="table" value="' . $table
            . '" />';
    $frmStr .= '<input type="hidden" name="coll_id" id="coll_id" value="'
            . $collId . '" />';
    $frmStr .= '<input type="hidden" name="module" id="module" value="'
            . $module . '" />';
    $frmStr .= '<input type="hidden" name="req" id="req" value="second_request"'
            . ' />';

    $frmStr .= '<div  style="display:block">';
    if ($core->is_module_loaded('webtwain')
        && $_SESSION['user']['services']['scan'] === true
    ) { //Ajout yck
        $frmStr .= '<div style="display:block;" id="choose_scan">'
                . '<a href="javascript://" onclick="hide_scan(true);">'
                . _SCAN_DOCUMENT . '&nbsp;<img src="'
                . $_SESSION['config']['businessappurl']
                . 'static.php?filename=lot.gif" alt="'
                . _SCAN_DOCUMENT . '"/></a></div>';
        $frmStr .= '<div style="display:none;" id="choose_file">'
                . '<a href="javascript://" onclick="hide_scan(false);">'
                . _SCAN_CHOOSE_FILE . '&nbsp;<img src="'
                . $_SESSION['config']['businessappurl']
                . 'static.php?filename=lot.gif" alt="'
                . _SCAN_CHOOSE_FILE . '"/></a></div><br/>';
    }

    if (! isset($_SESSION['FILE']['extension'])
        || $_SESSION['FILE']['extension'] == ""
    ) {
        $frmStr .= '<div  style="display:block" id="choose_file_div">';
        $frmStr .= '<iframe src="' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&dir=indexing_searching&page='
                . 'choose_file" name="choose_file" id="choose_file" '
                . 'frameborder="0" scrolling="no" width="100%" height="40">'
                . '</iframe>';
        $frmStr .= '</div>';
    }

    if ($core->test_service('index_attachment', 'attachments', false)) {
        $frmStr .= '<table width="100%" align="center" border="0" >';
        $frmStr .= '<tr id="attachment_tr" style="display:' . $displayValue
                . ';">';
        $frmStr .= '<td><label for="attachment" class="form_title" >'
                . _ATTACH_TO_DOC . ' : </label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><input type="radio" '
                . 'name="attachment" id="attach" value="true" '
                . 'onclick="hide_index(true, \'' . $displayValue . '\');" /> '
                . _YES . ' <input type="radio" name="attachment" id="no_attach"'
                . ' value="false" checked="checked" '
                . 'onclick="hide_index(false, \'' . $displayValue . '\');" /> '
                . _NO . '</td>';
        $frmStr .= ' <td><span class="red_asterisk" id="attachment_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '<tr id="attach_title_tr" style="display:none;">';
        $frmStr .= '<td><label for="attach_title" class="form_title" >'
                . _TITLE . ' : </label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><input type="text" '
                . 'name="attach_title" id="attach_title" value="" /></td>';
        $frmStr .= '<td><span class="red_asterisk" id="res_id_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '<tr id="attach_link_tr" style="display:none;">';
        $frmStr .= '<td><label for="attach" class="form_title" >' . _NUM_GED
                . ' : </label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><input type="text" name="res_id"'
                . ' id="res_id" class="readonly" readonly="readonly" value="" />'
                . '<br/><a href="javascript://" onclick="window.open(\''
                . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&dir=indexing_searching&page='
                . 'search_adv&mode=popup&action_form=show_res_id&modulename='
                . 'attachments&init_search&nodetails\','
                . '\'search_doc_for_attachment\', \'scrollbars=yes,menubar=no,'
                . 'toolbar=no,resizable=yes,status=no,width=1020,height=710\');"'
                . ' title="' . _SEARCH . '"><em>' . _SEARCH_DOC
                . '</em></a></td>';
        $frmStr .= '<td><span class="red_asterisk" id="res_id_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '</table>';
    }
    $frmStr .= '<table width="100%" align="center" border="0" '
            . 'id="indexing_fields" style="display:block;">';
    /*** Category ***/
    $frmStr .= '<tr id="category_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:200px;"><label for="category_id" '
            . 'class="form_title" >' . _CATEGORY . '</label></td>';
    $frmStr .= '<td style="width:10px;">&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><select name="category_id" '
            . 'id="category_id" onchange="clear_error(\'frm_error_' . $actionId
            . '\');change_category(this.options[this.selectedIndex].value, \''
            . $displayValue . '\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'change_category\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=get_content_js\');">';
    $frmStr .= '<option value="">' . _CHOOSE_CATEGORY . '</option>';
    foreach (array_keys($_SESSION['mail_categories']) as $catId) {
        $frmStr .= '<option value="' . $catId . '"';
        if ($_SESSION['default_category'] == $catId
            || (isset($_SESSION['indexing']['category_id'])
                && $_SESSION['indexing']['category_id'] == $catId)
        ) {
            $frmStr .= 'selected="selected"';
        }

        $frmStr .= '>' . $_SESSION['mail_categories'][$catId] . '</option>';
    }
    $frmStr .= '</select></td>';
    $frmStr .= '<td><span class="red_asterisk" id="category_id_mandatory" '
            . 'style="display:inline;">*</span></td>';
    $frmStr .= '</tr>';
    /*** Doctype ***/
    $frmStr .= '<tr id="type_id_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="type_id"><span class="form_title" '
            . 'id="doctype_res" style="display:none;">' . _DOCTYPE
            . '</span><span class="form_title" id="doctype_mail" '
            . 'style="display:inline;">' . _DOCTYPE_MAIL
            . '</span></label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><select name="type_id" id="type_id" '
            . 'onchange="clear_error(\'frm_error_' . $actionId . '\');'
            . 'change_doctype(this.options[this.selectedIndex].value, \''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&dir=indexing_searching&page=change_doctype\', \''
            . _ERROR_DOCTYPE . '\', \'' . $actionId . '\', \''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&page=get_content_js\', \'' . $displayValue . '\');">';
    $frmStr .= '<option value="">' . _CHOOSE_TYPE . '</option>';
if ($_SESSION['features']['show_types_tree'] == 'true') {
	    for ($i = 0; $i < count($doctypes); $i ++) {
		    $frmStr .= '<option value="" class="' //doctype_level1
		    		. $doctypes[$i]['style'] . '" title="'
		            . $doctypes[$i]['label'] . '" label="'
		            . $doctypes[$i]['label'] . '" >' . $doctypes[$i]['label']
		            . '</option>';
			for ($j = 0; $j < count($doctypes[$i]['level2']); $j ++) {
			    $frmStr .= '<option value="" class="' //doctype_level2
			    		. $doctypes[$i]['level2'][$j]['style'] .'" title="'
			            . $doctypes[$i]['level2'][$j]['label'] . '" label="'
			            . $doctypes[$i]['level2'][$j]['label'] . '" >&nbsp;&nbsp;'
			            . $doctypes[$i]['level2'][$j]['label'] .'</option>';
			    for ($k = 0; $k < count($doctypes[$i]['level2'][$j]['types']);
			        $k ++
			    ) {
				    $frmStr .= '<option value="'
				            . $doctypes[$i]['level2'][$j]['types'][$k]['id']
				            . '" title="'
				            . $doctypes[$i]['level2'][$j]['types'][$k]['label']
				            . '" label="'
				            . $doctypes[$i]['level2'][$j]['types'][$k]['label']
				            . '">&nbsp;&nbsp;&nbsp;&nbsp;'
				            . $doctypes[$i]['level2'][$j]['types'][$k]['label']
				            . '</option>';
				}
			}
		}
	} else {
	    for ($i = 0; $i < count($doctypes); $i ++) {
			$frmStr .= '<option value="' . $doctypes[$i]['ID'] . '" >'
			        . $doctypes[$i]['LABEL'] . '</option>';
		}
	}
    $frmStr .= '</select></td>';
    $frmStr .= '<td><span class="red_asterisk" id="type_id_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Priority ***/
    $frmStr .= '<tr id="priority_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="priority" class="form_title" >' . _PRIORITY
            . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><select name="priority" '
            . 'id="priority" onchange="clear_error(\'frm_error_' . $actionId
            . '\');">';
    $frmStr .= '<option value="">' . _CHOOSE_PRIORITY . '</option>';
    for ($i = 0; $i < count($_SESSION['mail_priorities']); $i ++) {
        $frmStr .= '<option value="' . $i . '" ';
        if ($_SESSION['default_mail_priority'] == $i) {
            $frmStr .= 'selected="selected"';
        }
        $frmStr .= '>' . $_SESSION['mail_priorities'][$i] . '</option>';
    }
    $frmStr .= '</select></td>';
    $frmStr .= '<td><span class="red_asterisk" id="priority_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Doc date ***/
    $frmStr .= '<tr id="doc_date_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="doc_date" class="form_title" '
            . 'id="mail_date_label" style="display:inline;" >' . _MAIL_DATE
            . '</label><label for="doc_date" class="form_title" '
            . 'id="doc_date_label" style="display:none;" >' . _DOC_DATE
            . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="doc_date" type="text" '
            . 'id="doc_date" value="" onclick="clear_error(\'frm_error_'
            . $actionId . '\');showCalender(this);" /></td>';
    $frmStr .= '<td><span class="red_asterisk" id="doc_date_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr >';
    /*** Author ***/
    $frmStr .= '<tr id="author_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="author" class="form_title" >' . _AUTHOR
            . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="author" type="text" '
            . 'id="author" onchange="clear_error(\'frm_error_' . $actionId
            . '\');"/></td>';
    $frmStr .= '<td><span class="red_asterisk" id="author_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Admission date ***/
    $frmStr .= '<tr id="admission_date_tr" style="display:' . $displayValue
            . ';">';
    $frmStr .= '<td><label for="admission_date" class="form_title" >'
            . _RECEIVING_DATE . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="admission_date" '
            . 'type="text" id="admission_date" value="' . $today
            . '" onclick="clear_error(\'frm_error_' . $actionId . '\');'
            . 'showCalender(this);"/></td>';
    $frmStr .= '<td><span class="red_asterisk" id="admission_date_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Contact ***/
    $frmStr .= '<tr id="contact_choose_tr" style="display:' . $displayValue
            . ';">';
    $frmStr .= '<td><label for="type_contact" class="form_title" >'
            . '<span id="exp_contact_choose_label">' . _SHIPPER_TYPE . '</span>'
            . '<span id="dest_contact_choose_label">' . _DEST_TYPE . '</span>'
            . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input type="radio" class="check" '
            . 'name="type_contact" id="type_contact_internal" value="internal" '
            . 'onclick="clear_error(\'frm_error_' . $actionId . '\');'
            . 'change_contact_type(\'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'autocomplete_contacts\', true);"  />' . _INTERNAL2
            . '<input type="radio" name="type_contact" '
            . 'id="type_contact_external" value="external" '
            . 'onclick="clear_error(\'frm_error_' . $actionId . '\');'
            . 'change_contact_type(\'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching'
            . '&autocomplete_contacts\', true);"  class="check"/>' . _EXTERNAL
            . '</td>';
    $frmStr .= '<td><span class="red_asterisk" id="type_contact_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr id="contact_id_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="contact" class="form_title" >'
            . '<span id="exp_contact">' . _SHIPPER . '</span>'
            . '<span id="dest_contact">' . _DEST . '</span>';
    if ($_SESSION['features']['personal_contact'] == "true"
       // && $core->test_service('my_contacts', 'apps', false)
    ) {
        $frmStr .= ' <a href="#" id="create_contact" title="' . _CREATE_CONTACT
                . '" onclick="new Effect.toggle(\'create_contact_div\', '
                . '\'blind\', {delay:0.2});return false;" '
                . 'style="display:inline;" ><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'modif_liste.png" alt="' . _CREATE_CONTACT . '"/></a>';
    }
    $frmStr .= '</label></td>';
    $frmStr .= '<td><a href="#" id="contact_card" title="' . _CONTACT_CARD
            . '" onclick="open_contact_card(\''
            . $_SESSION ['config']['businessappurl'] . 'index.php?display=true'
            . '&page=contact_info\', \'' . $_SESSION ['config']['businessappurl']
            . 'index.php?display=true&page=user_info\');" '
            . 'style="visibility:hidden;" ><img src="'
            . $_SESSION['config']['businessappurl'] . 'static.php?filename='
            . 'my_contacts_off.gif" alt="' . _CONTACT_CARD . '" /></a>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input type="text" name="contact" '
            . 'id="contact" onblur="clear_error(\'frm_error_' . $actionId . '\');'
            . 'display_contact_card(\'visible\');" /><div id="show_contacts" '
            . 'class="autocomplete"></div></td>';
    $frmStr .= '<td><span class="red_asterisk" id="contact_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Nature ***/
    $frmStr .= '<tr id="nature_id_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="nature_id" class="form_title" >' . _NATURE
            . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><select name="nature_id" '
            . 'id="nature_id" onchange="clear_error(\'frm_error_' . $actionId
            . '\');">';
    $frmStr .= '<option value="">' . _CHOOSE_NATURE . '</option>';
    foreach (array_keys($_SESSION['mail_natures']) as $nature) {
        $frmStr .= '<option value="' . $nature . '"';
        if ($_SESSION['default_mail_nature'] == $nature) {
            $frmStr .= 'selected="selected"';
        }
        $frmStr .= '>' . $_SESSION['mail_natures'][$nature] . '</option>';
    }
    $frmStr .= '</select></td>';
    $frmStr .= '<td><span class="red_asterisk" id="nature_id_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Subject ***/
    $frmStr .= '<tr id="subject_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="subject" class="form_title" >' . _SUBJECT
            . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><textarea name="subject" '
            . 'id="subject"  rows="4" onchange="clear_error(\'frm_error_'
            . $actionId . '\');" ></textarea></td>';
    $frmStr .= '<td><span class="red_asterisk" id="subject_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Entities : department + diffusion list ***/
    if ($core->is_module_loaded('entities')) {
        $frmStr .= '<tr id="department_tr" style="display:' . $displayValue
                . ';">';
        $frmStr .= '<td><label for="department" class="form_title" '
                . 'id="label_dep_dest" style="display:inline;" >'
                . _DEPARTMENT_DEST . '</label><label for="department" '
                . 'class="form_title" id="label_dep_exp" style="display:none;" >'
                . _DEPARTMENT_EXP . '</label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><select name="destination" '
                . 'id="destination" onchange="clear_error(\'frm_error_'
                . $actionId . '\');'
                . 'change_entity(this.options[this.selectedIndex].value, \''
                . $_SESSION['config']['businessappurl'].'index.php?display=true'
                . '&module=entities&page=load_listinstance'.'\','
                . '\'diff_list_div\', \'indexing\', \'' . $displayValue
                . '\');">';
        $frmStr .= '<option value="">' . _CHOOSE_DEPARTMENT . '</option>';
        for ($i = 0; $i < count($services); $i ++) {
            $frmStr .= '<option value="' . $services[$i]['ID'] . '" >'
                    . $db->show_string($services[$i]['SHORT_LABEL'])
                    . '</option>';
        }
        $frmStr .= '</select></td>';
        $frmStr .= '<td><span class="red_asterisk" id="destination_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '<tr id="diff_list_tr" style="display:none;">';
        $frmStr .= '<td colspan="3">';
        $frmStr .= '<div id="diff_list_div" class="scroll_div" '
                . 'style="height:200px; border: 1px solid;"></div>';
        $frmStr .= '</td>';
        $frmStr .= '</tr>';
    }
    /*** Physical_archive : Arbox ***/
    if ($core->is_module_loaded('physical_archive')) {
        $frmStr .= '<tr id="box_id_tr" style="display:' . $displayValue . ';">';
        $frmStr .= '<td><label for="arbox_id" class="form_title" id="label_box"'
                . ' style="display:inline;" >' . _BOX_ID . '</label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><select name="arbox_id" '
                . 'id="arbox_id" onchange="clear_error(\'frm_error_' . $actionId
                . '\');" >';
        $frmStr .= '<option value="">' . _CHOOSE_BOX . '</option>';
        for ($i = 0; $i < count($boxes); $i ++) {
            $frmStr .= '<option value="' . $boxes[$i]['ID'] . '" >'
                    . $db->show_string($boxes[$i]['LABEL']) . '</option>';
        }
        $frmStr .= '</select></td>';
        $frmStr .= '<td><span class="red_asterisk" id="arbox_id_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
    }
    /*** Process limit date ***/
    $frmStr .= '<tr id="process_limit_date_use_tr" style="display:'
            . $displayValue . ';">';
    $frmStr .= '<td><label for="process_limit_date_use" class="form_title" >'
            . _PROCESS_LIMIT_DATE_USE . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input type="radio" '
            . 'name="process_limit_date_use" id="process_limit_date_use_yes" '
            . 'class="check" value="yes" checked="checked" '
            . 'onclick="clear_error(\'frm_error_' . $actionId . '\');'
            . 'activate_process_date(true,\'' . $displayValue . '\' );" />'
            . _YES . '<input type="radio" name="process_limit_date_use" '
            . 'id="process_limit_date_use_no" value="no" class="check" '
            . 'onclick="clear_error(\'frm_error_' . $actionId . '\');'
            . 'activate_process_date(false, \'' . $displayValue . '\');"/>'
            . _NO . '</td>';
    $frmStr .= '<td><span class="red_asterisk" '
            . 'id="process_limit_date_use_mandatory" style="display:inline;">*'
            . '</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr id="process_limit_date_tr" style="display:' . $displayValue
            . ';">';
    $frmStr .= '<td><label for="process_limit_date" class="form_title" >'
            . _PROCESS_LIMIT_DATE . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="process_limit_date" '
            . 'type="text" id="process_limit_date" value="" '
            . 'onclick="showCalender(this);" '
            . 'onchange="clear_error(\'frm_error_' . $actionId . '\');"/></td>';
    $frmStr .= '<td><span class="red_asterisk" id="process_limit_date_mandatory"'
            . ' style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';

    /*** Chrono number ***/
    $frmStr .= '<tr id="chrono_number_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td><label for="chrono_number" class="form_title" >'
            . _CHRONO_NUMBER . '</label></td>';
    $frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input type="text" '
            . 'name="chrono_number" id="chrono_number" '
            . 'onchange="clear_error(\'frm_error_' . $actionId . '\');"/></td>';
    $frmStr .= '<td><span class="red_asterisk" id="chrono_number_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';

    /*** Folder : Market & Project ***/
    if ($core->is_module_loaded('folder')) {
        $frmStr .= '<tr id="project_tr" style="display:' . $displayValue . ';">';
        $frmStr .= '<td><label for="project" class="form_title" >' . _PROJECT
                . '</label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><input type="text" '
                . 'name="project" id="project" onblur="clear_error(\'frm_error_'
                . $actionId . '\');return false;" /><div id="show_project" '
                . 'class="autocomplete"></div></td>';// $(\'market\').value=\'\';
        $frmStr .= '<td><span class="red_asterisk" id="project_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '<tr id="market_tr" style="display:' . $displayValue . ';">';
        $frmStr .= '<td><label for="market" class="form_title" >' . _MARKET
                . '</label></td>';
        $frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field"><input type="text" name="market"'
                . ' id="market" onblur="clear_error(\'frm_error_' . $actionId
                . '\');fill_project(\'' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&module=folder&page=ajax_get_project\');'
                . 'return false;" /><div id="show_market" class="autocomplete">'
                . '</div></td>';
        $frmStr .= '<td><span class="red_asterisk" id="market_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
    }
    $frmStr .= '</table>';

    $frmStr .= '<div id="comp_indexes" style="display:block;">';
    $frmStr .= '</div>';
    $frmStr .= '</div>';
    /*** Actions ***/
    $frmStr .= '<hr width="90%"/>';
    $frmStr .= '<p align="center">';
    $frmStr .= '<b>' . _ACTIONS . ' : </b>';

    $actions  = $b->get_actions_from_current_basket(
        $resId, $collId, 'PAGE_USE', false
    );
    if (count($actions) > 0) {
        $frmStr .= '<select name="chosen_action" id="chosen_action">';
        $frmStr .= '<option value="">' . _CHOOSE_ACTION . '</option>';
        for ($indAct = 0; $indAct < count($actions); $indAct ++) {
            $frmStr .= '<option value="' . $actions[$indAct]['VALUE'] . '"';
            if ($indAct == 0) {
                $frmStr .= 'selected="selected"';
            }
            $frmStr .= '>' . $actions[$indAct]['LABEL'] . '</option>';
        }
        $frmStr .= '</select> ';
        $frmStr .= '<input type="button" name="send" id="send" value="'
                . _VALIDATE . '" class="button" '
                . 'onclick="valid_action_form(\'index_file\', \''
                . $pathManageAction . '\', \'' . $actionId . '\', \'' . $resId
                . '\', \'' . $table . '\', \'' . $module . '\', \'' . $collId
                . '\', \'' . $mode . '\', false);"/> ';
    }
    $frmStr .= '<input name="close" id="close" type="button" value="'
            . _CANCEL . '" class="button" '
            . 'onclick="javascript:window.top.location.href=\''
            . $_SESSION['config']['businessappurl'] . 'index.php\';reinit();"/>';
    $frmStr .= '</p>';
    $frmStr .= '</form>';
    $frmStr .= '</div>';
    $frmStr .= '</div>';
    $frmStr .= '</div>';

    /*** Frame to display the doc ***/
    $frmStr .= '<div id="validright">';

    /**** Contact form start *******/
    $frmStr .= '<div id="create_contact_div" style="display:none">';
    $frmStr .= '<div >';
    $frmStr .= '<fieldset style="border:1px solid;">';
    $frmStr .= '<legend ><b>' . _CREATE_CONTACT . '</b></legend>';
    $frmStr .= '<form name="indexingfrmcontact" id="indexingfrmcontact" '
            . 'method="post" action="' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=contact_info" >';
    $frmStr .= '<table>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="is_corporate">' . _IS_CORPORATE_PERSON . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input type="radio" class="check" name="is_corporate" '
            . 'id="is_corporate_Y" value="Y" ';
    $frmStr .= ' checked="checked"';
    $frmStr .= 'onclick="javascript:show_admin_contacts(true, \''
            . $displayValue . '\');">' . _YES;
    $frmStr .= '<input type="radio" id="is_corporate_N" class="check" '
            . 'name="is_corporate" value="N"';
    $frmStr .= ' onclick="javascript:show_admin_contacts( false, \''
            . $displayValue . '\');"/>' . _NO;
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr id="title_p" style="display:none">';
    $frmStr .= '<td  colspan="2">';
    $frmStr .= '<label for="title"> ' . _TITLE2 . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2" >';
    $frmStr .= '<select name="title" id="title" >';
    $frmStr .= '<option value="">' . _CHOOSE_TITLE . '</option>';
    foreach (array_keys($titles) as $key) {
        $frmStr .= '<option value="' . $key . '" ';
        if ($key == $defaultTitle) {
            $frmStr .= 'selected="selected"';
        }
        $frmStr .= '>' . $titles[$key] . '</option>';
    }
    $frmStr .= '</select>';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr id="lastname_p" style="display:';
    $frmStr .= $displayValue;
    $frmStr .= '">';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="lastname">' . _LASTNAME . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="lastname" type="text"  id="lastname" value="" /> ';
    $frmStr .= '<span class="red_asterisk" id="lastname_mandatory" '
            . 'style="display:inline;">*</span>';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr id="firstname_p" style="display:';
    $frmStr .= $displayValue;
    $frmStr .= '">';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="firstname">' . _FIRSTNAME . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="firstname" type="text"  id="firstname" value=""/>';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="society">' . _SOCIETY . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="society" type="text"  id="society" value="" />';
    $frmStr .= '<span class="red_asterisk" id="society_mandatory" '
            . 'style="display:inline;">*</span>';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr id="function_p" style="display:';
    $frmStr .= 'block';
    $frmStr .= '">';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="function">' . _FUNCTION . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="function" type="text"  id="function" value="" />';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="phone">' . _PHONE . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="phone" type="text"  id="phone" value="" />';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="mail">' . _MAIL . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="mail" type="text" id="mail" value="" />';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td>';
    $frmStr .= '<label for="num">' . _NUM . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td>';
    $frmStr .= '<input name="num" type="text" class="small" id="num" value=""/>';
    $frmStr .= '</td>';
    $frmStr .= '<td>';
    $frmStr .= '<label for="street">' . _STREET . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td>';
    $frmStr .= '<input name="street" type="text" class="medium"  id="street" '
            . 'value="" />';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="add_comp">' . _COMPLEMENT . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="add_comp" type="text"  id="add_comp" value="" />';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td>';
    $frmStr .= '<label for="cp">' . _POSTAL_CODE . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td>';
    $frmStr .= '<input name="cp" type="text" id="cp" value="" class="small" />';
    $frmStr .= '</td>';
    $frmStr .= '<td>';
    $frmStr .= '<label for="town">' . _TOWN . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td>';
    $frmStr .= '<input name="town" type="text" id="town" value="" class="medium"/>';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="country">' . _COUNTRY . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<input name="country" type="text"  id="country" value="" />';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<tr>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<label for="comp_data">' . _COMP_DATA . ' : </label>';
    $frmStr .= '</td>';
    $frmStr .= '<td colspan="2">';
    $frmStr .= '<textarea name="comp_data" id="comp_data" ></textarea>';
    $frmStr .= '</td>';
    $frmStr .= '</tr>';
    $frmStr .= '</table>';
    $frmStr .= '<div align="center">';
    $frmStr .= '<input name="submit" type="button" value="' . _VALIDATE . '" '
            . 'class="button" onclick="create_contact(\''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&page=create_contact\', \'' . $actionId . '\');" />';
    $frmStr .= ' <input name="cancel" type="button" value="' . _CANCEL . '" '
            . 'onclick="new Effect.toggle(\'create_contact_div\', \'blind\', '
            . '{delay:0.2});clear_form(\'indexingfrmcontact\');return false;" '
            . 'class="button" />';
    $frmStr .= '</div>';
    $frmStr .= '</fieldset >';
    $frmStr .= '</form >';
    $frmStr .= '</div><br/>';
    $frmStr .= '</div>';
    /**** Contact form end *******/
    $frmStr .= '<script type="text/javascript">show_admin_contacts( true);</script>';
    //$frmStr .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=file_iframe" name="file_iframe" id="file_iframe" scrolling="auto" frameborder="0" style="display:block;" ></iframe>';
    if ($_SESSION['origin'] == "scan") {
        $frmStr .= '<iframe src="' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&dir=indexing_searching&page='
                . 'file_iframe&#navpanes=0" name="file_iframe" id="file_iframe"'
                . ' scrolling="auto" frameborder="0" style="display:block;">'
                . '</iframe>';
    } else {
        $frmStr .= '<iframe src="' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&dir=indexing_searching&page='
                . 'file_iframe" name="file_iframe" id="file_iframe" '
                . 'scrolling="auto" frameborder="0" style="display:block;">'
                . '</iframe>';
    }
    $strJs = '';
    if ($core->is_module_loaded('webtwain')
        && $_SESSION['user']['services']['scan'] === true
    ) {//Ajout yck
        //$frmStr .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&module=webtwain&page=scan" name="file_iframe" id="scan_iframe" scrolling="auto" frameborder="0" ></iframe>';
        $frmStr .= $core->execute_modules_services(
            $_SESSION['modules_services'], 'index_mlb', 'frame', 'scan', 'webtwain'
        );
        $strJs = 'resize_frame_process(\'modal_' . $actionId
               . '\', \'scan_iframe\', true, true);';
    }

    $frmStr .= '</div>';

    /*** Extra javascript ***/
    $frmStr .= '<script type="text/javascript">resize_frame_process(\'modal_'
            . $actionId . '\', \'file_iframe\', true, true); ' . $strJs
            . 'window.scrollTo(0,0);change_category(\''
            . $_SESSION['default_category'] . '\', \'' . $displayValue
            . '\', \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'change_category\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=get_content_js\');'
            . 'launch_autocompleter_contacts(\''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&dir=indexing_searching&page=autocomplete_contacts\');';
    if ($core->is_module_loaded('folder')) {
        $frmStr .= 'launch_autocompleter_folders(\''
                . $_SESSION['config']['businessappurl'] . 'index.php?display='
                . 'true&module=folder&page=autocomplete_folders&mode=project\','
                . ' \'project\');launch_autocompleter_folders(\''
                . $_SESSION['config']['businessappurl'] . 'index.php?display='
                . 'true&module=folder&page=autocomplete_folders&mode=market\','
                . ' \'market\');';
    }
    $frmStr .= '$(\'baskets\').style.visibility=\'hidden\';'
            . 'var item  = $(\'index_div\'); if(item)'
            . '{item.style.display=\'block\';}</script>';

    return addslashes($frmStr);
}

/**
 * Checks the action form
 *
 * @param $formId String Identifier of the form to check
 * @param $values Array Values of the form
 * @return Bool true if no error, false otherwise
 **/
function check_form($formId, $values)
{
    //print_r($values);
    $_SESSION['action_error'] = '';
    if (count($values) < 1 || empty($formId)) {
        $_SESSION['action_error'] = _FORM_ERROR;
        return false;
    } else {
        //print_r($values);
        $attach = get_value_fields($values, 'attach');
        $collId = get_value_fields($values, 'coll_id');
        if (! $attach) {
            $catId = get_value_fields($values, 'category_id');
            if (! $catId) {
                $_SESSION['action_error'] = _CATEGORY . ' ' . _IS_EMPTY;
                return false;
            }
            $noError = process_category_check($catId, $values);

            if ($noError == false) {
                //$_SESSION['action_error'] .= _ERROR_CATEGORY;
                return false;
            }

            if (isset($_SESSION['upfile']['format'])
                && $_SESSION['upfile']['format'] <> 'maarch'
            ) {
                $is = new indexing_searching_app();
                $state = $is->is_filetype_allowed(
                    $_SESSION['upfile']['format']
                );
                if (! $state) {
                    $_SESSION['action_error'] .= '<br/>'
                        . $_SESSION['upfile']['format'] . _FILETYPE . ' '
                        . _NOT_ALLOWED;
                    return false;
                }
            }
        } else {
            $title = get_value_fields($values, 'attach_title');
            if (! $title || empty($title)) {
                $_SESSION['action_error'] .= _TITLE . ' ' . _IS_EMPTY . '<br/>';
            }
            $idDoc = get_value_fields($values, 'res_id');
            if (! $idDoc || empty($idDoc)) {
                $_SESSION['action_error'] .= _NUM_GED . ' ' . _IS_EMPTY . '<br/>';
            }
            if (! empty($_SESSION['action_error'])) {
                return false;
            }
        }
        return check_docserver($collId);
    }
}

/**
 * Makes all the checks on the docserver and store the file
 *
 * @param $catId String Collection identifier
 * @return Bool true if no error, false otherwise
 **/
function check_docserver($collId) {
    if (isset($_SESSION['indexing']['path_template'])
        && ! empty($_SESSION['indexing']['path_template'])
        && isset($_SESSION['indexing']['destination_dir'])
        && ! empty($_SESSION['indexing']['destination_dir'])
        && isset($_SESSION['indexing']['docserver_id'])
        && ! empty($_SESSION['indexing']['docserver_id'])
        && isset($_SESSION['indexing']['file_destination_name'])
        && ! empty($_SESSION['indexing']['file_destination_name'])
    ) {
        $_SESSION['action_error'] = _CHECK_FORM_OK;
        return true;
    }
    $core = new core_tools();
    if ($core->is_module_loaded('templates')
        && $_SESSION['upfile']['format'] == "maarch"
    ) {
        if (!isset($_SESSION['template_content'])
            || empty($_SESSION['template_content'])
        ) {
            $_SESSION['action_error'] = _TEMPLATE . ' ' . _IS_EMPTY;
            return false;
        }
        if (
            !isset($_SESSION['upfile']['name'])
            && $_SESSION['upfile']['name'] == ''
        ) {
            $_SESSION['upfile']['name'] = 'tmp_file_'
                . $_SESSION['user']['UserId'] . '_' . rand() . '.maarch';
            $tmpPath = $_SESSION['config']['tmppath'] . DIRECTORY_SEPARATOR
                . $_SESSION['upfile']['name'];
            $myfile = fopen($tmpPath, "w");
            if (!$myfile) {
                $_SESSION['action_error'] .= _FILE_OPEN_ERROR . '.<br/>';
                return false;
            }
            fwrite($myfile, $_SESSION['template_content']);
            fclose($myfile);
            $_SESSION['upfile']['size'] = filesize($tmpPath);
        }
    }
    if ($_SESSION['origin'] == "scan") {
        $newFileName = "tmp_file_" . $_SESSION['upfile']['md5'] . '.'
                     . strtolower($_SESSION['upfile']['format']);
    } else {
        //$newFileName = "tmp_file_" . $_SESSION['user']['UserId'] . '.'
        //             . strtolower($_SESSION['upfile']['format']);
        $newFileName = $_SESSION['upfile']['name'];
    }

    $docserverControler = new docservers_controler();
    $fileInfos = array(
        "tmpDir"      => $_SESSION['config']['tmppath'],
        "size"        => $_SESSION['upfile']['size'],
        "format"      => $_SESSION['upfile']['format'],
        "tmpFileName" => $newFileName,
    );
    //print_r($fileInfos);
    $storeResult = array();
    $storeResult = $docserverControler->storeResourceOnDocserver(
        $collId, $fileInfos
    );
    //print_r($storeResult);
    if (isset($storeResult['error']) && $storeResult['error'] <> "") {
        $_SESSION['action_error'] = $storeResult['error'];
        return false;
    } else {
        $_SESSION['indexing']['path_template'] = $storeResult['path_template'];
        $_SESSION['indexing']['destination_dir'] = $storeResult['destination_dir'];
        $_SESSION['indexing']['docserver_id'] = $storeResult['docserver_id'];
        $_SESSION['indexing']['file_destination_name'] = $storeResult['file_destination_name'];
        $_SESSION['action_error'] = _CHECK_FORM_OK;
        return true;
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
    //print_r($values);
    $core = new core_tools();
    // If No category : Error
    if (! isset($_ENV['categories'][$catId])) {
        $_SESSION['action_error'] = _CATEGORY . ' ' . _UNKNOWN . ': ' . $catId;
        return false;
    }

    // Simple cases
    for ($i = 0; $i < count($values); $i ++) {

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
            && preg_match("/^[0-9]*$/", $values[$i]['VALUE']) == 0
        ) {
            $_SESSION['action_error'] = $_ENV['categories'][$catId][$tmpId]['label']
                                      . ' ' . _WRONG_FORMAT;
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
    require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
        . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
        . 'class_types.php';
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
            $_SESSION['store_process_limit_date'] = "ok";
            $processLimitDate = get_value_fields($values, 'process_limit_date');
            if (trim($processLimitDate) == ""
                || preg_match($_ENV['date_pattern'], $processLimitDate) == 0
            ) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['process_limit_date']['label']
                    . " " . _WRONG_FORMAT;
                return false;
            }
        } else if ($processLimitDateUseNo == 'no') {
            $_SESSION['store_process_limit_date'] = "ko";
        }
    }

    // Contact
    if (isset($_ENV['categories'][$catId]['other_cases']['contact'])) {
        $contactType = get_value_fields($values, 'type_contact_external');
        if (! $contactType) {
            $contactType = get_value_fields($values, 'type_contact_internal');
        }
        if (! $contactType) {
            $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['type_contact']['label']
                . " " . _MANDATORY;
            return false;
        }
        $contact = get_value_fields($values, 'contact');
        if ($_ENV['categories'][$catId]['other_cases']['contact']['mandatory'] == true) {
            if (empty($contact)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['contact']['label']
                    . ' ' . _IS_EMPTY;
                return false;
            }
        }
        if (! empty($contact)) {
            if ($contactType == 'external'
                && preg_match('/\([0-9]+\)$/', $contact) == 0
            ) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['contact']['label']
                    . " " . _WRONG_FORMAT . ".<br/>" . _USE_AUTOCOMPLETION;
                return false;
            } else if ($contactType == 'internal'
                && preg_match('/\((\s|\d|\h|\w)+\)$/i', $contact) == 0
            ) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['contact']['label']
                    . " " . _WRONG_FORMAT . ".<br/>" . _USE_AUTOCOMPLETION;
                return false;
            }
        }
    }

    if ($core->is_module_loaded('entities')) {
        // Diffusion list
        if (isset($_ENV['categories'][$catId]['other_cases']['diff_list'])
            && $_ENV['categories'][$catId]['other_cases']['diff_list']['mandatory'] == true
        ) {
            if (empty($_SESSION['indexing']['diff_list']['dest']['user_id'])
                || ! isset($_SESSION['indexing']['diff_list']['dest']['user_id'])
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
        $market = get_value_fields($values, 'market');
        $projectId = '';
        $marketId = '';
        if (isset($_ENV['categories'][$catId]['other_cases']['market'])
            && $_ENV['categories'][$catId]['other_cases']['market']['mandatory'] == true
        ) {
            if (empty($market)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['market']['label']
                    . ' ' . _IS_EMPTY;
                return false;
            }
        }
        if (! empty($market)) {
            if (! preg_match('/\([0-9]+\)$/', $market)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['market']['label']
                    . " " . _WRONG_FORMAT;
                return false;
            }
            $marketId = str_replace(
                ')', '', substr($market, strrpos($market, '(') + 1)
            );
            $db->query(
                "select folders_system_id from " . FOLD_FOLDERS_TABLE
                . " where folders_system_id = " . $marketId
            );
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _MARKET . ' ' . $marketId . ' '
                                          . _UNKNOWN;
                return false;
            }
        }
        $project = get_value_fields($values, 'project');
        if (isset($_ENV['categories'][$catId]['other_cases']['project'])
            && $_ENV['categories'][$catId]['other_cases']['project']['mandatory'] == true
        ) {
            if (empty($project)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['project']['label']
                    . ' ' . _IS_EMPTY;
                return false;
            }
        }
        if (! empty($project)) {
            if (! preg_match('/\([0-9]+\)$/', $project)) {
                $_SESSION['action_error'] = $_ENV['categories'][$catId]['other_cases']['project']['label']
                    . " " . _WRONG_FORMAT;
                return false;
            }
            $projectId = str_replace(
                ')', '', substr($project, strrpos($project, '(') + 1)
            );
            $db->query(
                "select folders_system_id from " . FOLD_FOLDERS_TABLE
                . " where folders_system_id = " . $projectId
            );
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _MARKET . ' ' . $projectId . ' '
                                          . _UNKNOWN;
                return false;
            }
        }
        if (! empty($projectId) && ! empty($marketId)) {
            $db->query(
                "select folders_system_id from " . FOLD_FOLDERS_TABLE
                . " where folders_system_id = " . $marketId
                . " and parent_id = " . $projectId
            );
            if ($db->nb_result() == 0) {
                $_SESSION['action_error'] = _INCOMPATIBILITY_MARKET_PROJECT;
                return false;
            }
        }
        if (! empty($typeId ) && (! empty($projectId) || ! empty($marketId))) {
            $foldertypeId = '';
            if (! empty($marketId)) {
                $db->query(
                    "select foldertype_id from " . FOLD_FOLDERS_TABLE
                    . " where folders_system_id = " . $marketId
                );
            } else {
                $db->query(
                    "select foldertype_id from " . FOLD_FOLDERS_TABLE
                    ." where folders_system_id = " . $projectId
                );
            }
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

    if ($core->is_module_loaded('physical_archive')) {
        // Arbox id
        $boxId = get_value_fields($values, 'arbox_id');
        if (isset($_ENV['categories'][$catId]['other_cases']['arbox_id'])
            && $_ENV['categories'][$catId]['other_cases']['arbox_id']['mandatory'] == true
        ) {
            if ($boxId == false) {
                $_SESSION['action_error'] = _NO_BOX_SELECTED . ' ';
                return false;
            }
        }
        if ($boxId != false && preg_match('/^[0-9]+$/', $boxId)) {
            $physicalArchive = new physical_archive();
            $paReturnValue = $physicalArchive->load_box_db(
                $boxId, $catId, $_SESSION['user']['UserId']
            );
            if ($paReturnValue == false) {
                $_SESSION['action_error'] = _ERROR_TO_INDEX_NEW_BATCH_WITH_PHYSICAL_ARCHIVE;
                return false;
            }
        }
    }

    //For specific case => chrono number
    $chronoOut = get_value_fields($values, 'chrono_number');
    if (isset($_ENV['categories'][$catId]['other_cases']['chrono_number'])
        && $_ENV['categories'][$catId]['other_cases']['arbox_id']['mandatory'] == true
    ) {
        if ($chronoOut == false) {
            $_SESSION['action_error'] = _NO_CHRONO_NUMBER_DEFINED . ' ';
            return false;
        }
    }
    if ($chronoOut != false && preg_match('/^[0-9]+$/', $chronoOut)) {
        $physicalArchive = new physical_archive();
        $paReturnValue = $physicalArchive->load_box_db(
            $boxId, $catId, $_SESSION['user']['UserId']
        );
        if ($paReturnValue == false) {
            $_SESSION['action_error'] = _ERROR_TO_INDEX_NEW_BATCH_WITH_PHYSICAL_ARCHIVE;
            return false;
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
    for ($i = 0; $i < count($values); $i ++) {
        if ($values[$i]['ID'] == $field) {
            return 	$values[$i]['VALUE'];
        }
    }
    return false;
}

/**
 * Action of the form : loads the index in the db
 *
 * @param $arrId Array Not used here
 * @param $history String Log the action in history table or not
 * @param $actionId String Action identifier
 * @param $label_action String Action label
 * @param $status String  Not used here
 * @param $collId String Collection identifier
 * @param $table String Table
 * @param $formValues String Values of the form to load
 * @return false or an array
 * 			$data['result'] : res_id of the new file followed by #
 * 			$data['history_msg'] : Log complement (empty by default)
 * 			$data['page_result'] : Page to load when action is done and modal closed
 **/
function manage_form($arrId, $history, $actionId, $label_action, $status,
$collId, $table, $formValues )
{
    if (empty($formValues) || count($arrId) < 1 || empty($collId)) {
        $_SESSION['action_error'] = _ERROR_MANAGE_FORM_ARGS;
        return false;
    }
    $resId = '';
    $db = new dbquery();
    $sec = new security();
    $req = new request();
    $core = new core_tools();
    $table = $sec->retrieve_table_from_coll($collId);
    $indColl = $sec->get_ind_collection($collId);
    $tableExt = $_SESSION['collections'][$indColl]['extensions'][0];
    $queryExtFields = '(';
    $queryExtValues = '(';
    $resource = new resource();
    $_SESSION['data'] = array();

    // Load in the $_SESSION['data'] minimal indexes
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'typist',
            'value' => $_SESSION['user']['UserId'],
            'type' => 'string',
        )
    );
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'docserver_id',
            'value' => $_SESSION['indexing']['docserver_id'],
            'type' => 'string',
        )
    );
    if (isset($_SESSION['upfile']['format'])) {
        array_push(
            $_SESSION['data'],
            array(
                'column' => 'format',
                'value' => $_SESSION['upfile']['format'],
                'type' => 'string',
            )
        );
    }
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'status',
            'value' => 'NEW',
            'type' => 'string',
        )
    );
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'offset_doc',
            'value' => '',
            'type' => 'string',
        )
    );
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'logical_adr',
            'value' => '',
            'type' => 'string',
        )
    );

    if (isset($_SESSION['origin']) && $_SESSION['origin'] == 'scan') {
        array_push(
            $_SESSION['data'],
            array(
                'column' => 'scan_user',
                'value' => $_SESSION['user']['UserId'],
                'type' => 'string',
            )
        );
        array_push(
            $_SESSION['data'],
            array(
                'column' => 'scan_date',
                'value' => $req->current_datetime(),
                'type' => 'function',
            )
        );
    }

    $attach = get_value_fields($formValues, 'attach');

    if ($attach == false) {
        $catId = get_value_fields($formValues, 'category_id');

        $queryExtFields .= 'category_id,' ;
        $queryExtValues .= "'" . $catId . "'," ;

        $_SESSION['origin'] = "";
        // Specific indexes : values from the form
        // Simple cases
        for ($i = 0; $i < count($formValues); $i ++) {
            $tmpId = $formValues[$i]['ID'];
            if (isset($_ENV['categories'][$catId][$tmpId]['type_field'])
                && $_ENV['categories'][$catId][$tmpId]['type_field'] == 'integer'
                && $_ENV['categories'][$catId][$tmpId]['table'] <> 'none'
            ) {
                if (isset($_ENV['categories'][$catId][$tmpId]['table'])
                    && $_ENV['categories'][$catId][$tmpId]['table'] == 'res'
                ) {
                    array_push(
                        $_SESSION['data'],
                        array(
                            'column' => $tmpId,
                            'value'  => $formValues[$i]['VALUE'],
                            'type'   => 'integer',
                        )
                    );
                } else if (isset($_ENV['categories'][$catId][$tmpId]['table'])
                    && $_ENV['categories'][$catId][$tmpId]['table'] == 'coll_ext'
                ) {
                    $queryExtFields .= $tmpId . ',';
                    $queryExtValues .= $formValues[$i]['VALUE'] . ',';
                }
            } else if (isset($_ENV['categories'][$catId][$tmpId]['type_field'])
                && isset($_ENV['categories'][$catId][$tmpId]['table'])
                && $_ENV['categories'][$catId][$tmpId]['type_field'] == 'string'
                && $_ENV['categories'][$catId][$tmpId]['table'] <> 'none'
            ) {
                if ($_ENV['categories'][$catId][$tmpId]['table'] == 'res') {
                    array_push(
                        $_SESSION['data'],
                        array(
                            'column' => $tmpId,
                            'value' => $db->protect_string_db($formValues[$i]['VALUE']),
                            'type' => 'string'
                        )
                    );
                } else if ($_ENV['categories'][$catId][$tmpId]['table'] == 'coll_ext') {
                    $queryExtFields .= $formValues[$i]['ID'] . ',';
                    $queryExtValues .= "'" . $db->protect_string_db(
                        $formValues[$i]['VALUE']
                    ) . "',";
                }
            } else if (isset($_ENV['categories'][$catId][$tmpId]['type_field'])
                && isset($_ENV['categories'][$catId][$tmpId]['table'])
                && $_ENV['categories'][$catId][$tmpId]['type_field'] == 'date'
                && $_ENV['categories'][$catId][$tmpId]['table'] <> 'none'
            ) {
                if ($_ENV['categories'][$catId][$tmpId]['table'] == 'res') {
                    array_push(
                        $_SESSION['data'],
                        array(
                            'column' => $tmpId,
                            'value' => $db->format_date_db($formValues[$i]['VALUE']),
                            'type' => 'date',
                        )
                    );
                } else if ($_ENV['categories'][$catId][$tmpId]['table'] == 'coll_ext') {
                    $queryExtFields .= $formValues[$i]['ID'] . ',';
                    $queryExtValues .= "'" . $db->format_date_db(
                        $formValues[$i]['VALUE']
                    ) . "',";
                }
            }
        }
        ///// Manages the complementary indexes depending on the doctype
        $type = new types();
        $typeId = get_value_fields($formValues, 'type_id');
        $indexes = $type->get_indexes($typeId, $collId, 'minimal');
        $valIndexes = array();
        for ($i = 0; $i < count($indexes); $i ++) {
            $valIndexes[$indexes[$i]] = get_value_fields(
                $formValues, $indexes[$i]
            );
        }
        $_SESSION['data'] = $type->fill_data_array(
            $typeId, $collId, $valIndexes, $_SESSION['data']
        );

        ///////////////////////// Other cases
        // Process limit Date
        if (isset($_ENV['categories'][$catId]['other_cases']['process_limit_date'])) {
            $processLimitDate = get_value_fields(
                $formValues, 'process_limit_date'
            );
            if ($_ENV['categories'][$catId]['other_cases']['process_limit_date']['table'] == 'res') {
                array_push(
                    $_SESSION['data'],
                    array(
                        'column' => 'process_limit_date',
                        'value' => $db->format_date_db($processLimitDate),
                        'type' => 'date',
                    )
                );
            } else if ($_ENV['categories'][$catId]['other_cases']['process_limit_date']['table'] == 'coll_ext') {
                if ($_SESSION['store_process_limit_date'] == "ok") {
                    $queryExtFields .= 'process_limit_date,';
                    $queryExtValues .= "'" . $db->format_date_db(
                        $processLimitDate
                    ) . "',";
                }
                $_SESSION['store_process_limit_date'] = "";
            }
        }

        // Contact
        if (isset($_ENV['categories'][$catId]['other_cases']['contact'])) {
            $contact = get_value_fields($formValues, 'contact');
            $contactType = get_value_fields(
                $formValues, 'type_contact_external'
            );
            if (! $contactType) {
                $contactType = get_value_fields(
                    $formValues, 'type_contact_internal'
                );
            }
            //echo 'contact '.$contact.', type '.$contactType;
            $contactId = str_replace(
                ')', '', substr($contact, strrpos($contact, '(') + 1)
            );
            if ($contactType == 'internal') {
                if ($catId == 'incoming') {
                    $queryExtFields .= 'exp_user_id,';
                    $queryExtValues .= "'" . $db->protect_string_db($contactId)
                                    . "',";
                } else if ($catId == 'outgoing' || $catId == 'internal') {
                    $queryExtFields .= 'dest_user_id,';
                    $queryExtValues .= "'" . $db->protect_string_db($contactId)
                                    . "',";
                }
            } else if ($contactType == 'external') {
                if ($catId == 'incoming') {
                    $queryExtFields .= 'exp_contact_id,';
                    $queryExtValues .= $contactId . ",";
                } else if ($catId == 'outgoing' || $catId == 'internal') {
                    $queryExtFields .= 'dest_contact_id,';
                    $queryExtValues .= $contactId . ",";
                }
            }
        }
        if ($core->is_module_loaded('folder')) {
            $market = get_value_fields($formValues, 'market');
            $folderId = '';
            if (! empty($market)) {
                $folderId = str_replace(
                    ')', '', substr($market, strrpos($market, '(') + 1)
                );
            } else {
                $project = get_value_fields($formValues, 'project');
                $folderId = str_replace(
                    ')', '', substr($project, strrpos($project, '(') + 1)
                );
            }
            if (! empty($folderId)) {
                array_push(
                    $_SESSION['data'],
                    array(
                        'column' => 'folders_system_id',
                        'value' => $folderId,
                        'type' => 'integer',
                    )
                );
            }
        }

        if ($core->is_module_loaded('entities')) {
            // Diffusion list
            $loadListDiff = false;
            if (isset($_ENV['categories'][$catId]['other_cases']['diff_list'])) {
                if (! empty($_SESSION['indexing']['diff_list']['dest']['user_id'])
                    && isset($_SESSION['indexing']['diff_list']['dest']['user_id'])
                ) {
                    array_push(
                        $_SESSION['data'],
                        array(
                            'column' => 'dest_user',
                            'value' => $db->protect_string_db(
                                $_SESSION['indexing']['diff_list']['dest']['user_id']
                             ),
                             'type' => 'string'
                        )
                    );
                }
                $loadListDiff = true;
            }
        }

        if ($core->is_module_loaded('physical_archive')) {
            // Arbox_id + Arbatch_id
            $boxId = get_value_fields($formValues, 'arbox_id');
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'arbox_id',
                    'value' => $boxId,
                    'type' => 'integer',
                )
            );
            $physicalArchive = new physical_archive();
            $paReturnValue = $physicalArchive->load_box_db(
                $boxId, $catId, $_SESSION['user']['UserId']
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'arbatch_id',
                    'value' => $paReturnValue,
                    'type' => 'integer',
                )
            );
        }
        //print_r($_SESSION['data']);
        $resId = $resource->load_into_db(
            $table, $_SESSION['indexing']['destination_dir'],
            $_SESSION['indexing']['file_destination_name'],
            $_SESSION['indexing']['path_template'],
            $_SESSION['indexing']['docserver_id'], $_SESSION['data'],
            $_SESSION['config']['databasetype']
        );
        //echo 'load '.$resId. " ";
        if ($resId <> false) {
            //Create chrono number
            //######
            $cBoxId = get_value_fields($formValues, 'arbox_id');
            $cTypeId = get_value_fields($formValues, 'type_id');
            $cEntity = get_value_fields($formValues, 'destination');

            $cChronoOut = get_value_fields($formValues, 'chrono_number');
            $chronoX = new chrono();
            $myVars = array(
                'entity_id' => $cEntity,
                'arbox_id' => $cBoxId,
                'type_id' => $cTypeId,
                'category_id' => $catId,
            );
            $myForm = array(
                'chrono_out' => $cChronoOut,
            );
            $myChrono = $chronoX->generate_chrono($catId, $myVars, $myForm);

            $queryExtFields .= 'alt_identifier,';
            $queryExtValues .= "'" . $db->protect_string_db($myChrono) . "',";
            //######
            //echo $resId. " ";
            $queryExtFields = preg_replace('/,$/', ',res_id)', $queryExtFields);
            $queryExtValues = preg_replace(
                '/,$/', ',' . $resId . ')', $queryExtValues
            );
            //echo $resId. " ";
            $queryExt = " insert into " . $tableExt . " " . $queryExtFields
                       . ' values ' . $queryExtValues ;
            //echo $queryExt;
            $db->connect();
            $db->query($queryExt);
            if ($core->is_module_loaded('folder') && ! empty($folderId)
                && $_SESSION['history']['folderup']
            ) {
                //	echo 'folder '.$resId. " ";
                $hist = new history();
                $hist->add(
                    $_SESSION['tablename']['fold_folders'], $folderId, "UP",
                    _DOC_NUM . $resId . _ADDED_TO_FOLDER,
                    $_SESSION['config']['databasetype'], 'apps'
                );
            }
            //$db->show();
            if ($core->is_module_loaded('entities')) {
                //	echo 'entities '.$resId. " ";
                if ($loadListDiff) {
                    require_once 'modules' . DIRECTORY_SEPARATOR . 'entities'
                        . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
                        . 'class_manage_listdiff.php';
                    $diffList = new diffusion_list();
                    $params = array(
                        'mode' => 'listinstance',
                        'table' => $_SESSION['tablename']['ent_listinstance'],
                        'coll_id' => $collId,
                        'res_id' => $resId,
                        'user_id' => $_SESSION['user']['UserId'],
                    );
                    $diffList->load_list_db(
                        $_SESSION['indexing']['diff_list'], $params
                    );
                }
                //	echo 'entities '.$resId. " ";
            }
        } else {
            $_SESSION['action_error'] = _ERROR_RES_ID;
            return false;
        }

        //$_SESSION['indexing'] = array();
        unset($_SESSION['upfile']);
        unset($_SESSION['data']);
        $_SESSION['action_error'] = _NEW_DOC_ADDED;
        $_SESSION['indexation'] = true;
        return array(
            'result' => $resId . '#',
            'history_msg' => '',
            'page_result' => $_SESSION['config']['businessappurl']
                             . 'index.php?page=details&dir=indexing_searching'
                             . '&coll_id=' . $collId . '&id=' . $resId
        );
    } else {
        $title = get_value_fields($formValues, 'attach_title');
        $idDoc = get_value_fields($formValues, 'res_id');

        array_push(
            $_SESSION['data'],
            array(
                'column' => 'title',
                'value' => $db->protect_string_db($title),
                'type' => 'string',
            )
        );
        array_push(
            $_SESSION['data'],
            array(
                'column' => 'res_id_master',
                'value' => $idDoc,
                'type' => 'integer',
            )
        );
        array_push(
            $_SESSION['data'],
            array(
                'column' => 'coll_id',
                'value' => $db->protect_string_db($collId),
                'type' => 'string',
            )
        );

        $resId = $resource->load_into_db(
            $_SESSION['tablename']['attach_res_attachments'],
            $_SESSION['indexing']['destination_dir'],
            $_SESSION['indexing']['file_destination_name'],
            $_SESSION['indexing']['path_template'],
            $_SESSION['indexing']['docserver_id'], $_SESSION['data'],
            $_SESSION['config']['databasetype']
        );

        if ($resId == false) {
            $_SESSION['action_error'] = _ERROR_RES_ID;
            return false;
        } else {
            unset($_SESSION['upfile']);
            unset($_SESSION['data']);
            $_SESSION['action_error'] = _NEW_ATTACH_ADDED;
            $_SESSION['indexation'] = true;
            $msg = '';
            if ($_SESSION['history']['attachadd'] == "true") {
                $msg = ucfirst(_DOC_NUM) . $resId . ' ' . _ATTACH_TO_DOC_NUM
                     . $idDoc;
            }
            return array(
                'result' => $idDoc . '#',
                'history_msg' => $msg,
                'page_result' => $_SESSION['config']['businessappurl']
                                 . 'index.php?page=details&dir='
                                 . 'indexing_searching&coll_id=' . $collId
                                 . '&id=' . $idDoc,
                'table_dest' => $_SESSION['tablename']['attach_res_attachments']
            );
        }
    }
}
