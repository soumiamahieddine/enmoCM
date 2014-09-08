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
* @brief   Action : indexing a file for the business collection
*
* Open a modal box to displays the indexing form, make the form checks and loads
* the result in database. Used by the core (manage_action.php page).
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

//$_SESSION['validStep'] = "ko";
include_once 'apps/' . $_SESSION['config']['app_id'] . '/definition_mail_categories_business.php';
require_once 'core/class/class_security.php';
require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';
require_once 'core/class/class_resource.php';
require_once 'apps/' . $_SESSION['config']['app_id']. '/class/class_business_app_tools.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php';
require_once 'modules/basket/class/class_modules_tools.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_indexing_searching_app.php';
require_once 'core/class/docservers_controler.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_chrono.php';
require_once 'core/class/class_history.php';
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
require_once 'apps/' . $_SESSION['config']['app_id'] . '/apps_tables.php';
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
function get_form_txt($values, $pathManageAction,  $actionId, $table, $module, $collId, $mode )
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
        $EntitiesIdExclusion = array();
        $db = new dbquery();
        $db->connect();
        
        # Redirect entities
        if (count($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$actionId]['entities']) > 0) {
            $db->query(
                "select entity_id from "
                . ENT_ENTITIES . " where entity_id not in ("
                . $_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$actionId]['entities']
                . ") and enabled= 'Y' order by entity_id"
            );
            //$db->show();
            while ($res = $db->fetch_object()) {
                array_push($EntitiesIdExclusion, $res->entity_id);
            }
        }
        
        # Diffusion list types
        require_once 'modules/entities/class/class_manage_listdiff.php';
        $diffList = new diffusion_list();
        
        $groupbasket_difflist_types = 
            $diffList->list_groupbasket_difflist_types(
                $_SESSION['user']['primarygroup'],
                $_SESSION['current_basket']['id'],
                $actionId
            );
        $difflistTypes = array();
        $listmodels = array();
        foreach($groupbasket_difflist_types as $difflist_type_id) {
            $difflistTypes[$difflist_type_id] = $diffList->get_difflist_type($difflist_type_id);
            $listmodels[$difflist_type_id] = $diffList->select_listmodels($difflist_type_id);
        }
        
        
    }
    //var_dump($EntitiesIdExclusion);
    require_once 'modules/entities/class/class_manage_entities.php';
    $ent = new entity();
    $allEntitiesTree= array();
    $allEntitiesTree = $ent->getShortEntityTreeAdvanced(
        $allEntitiesTree, 'all', '', $EntitiesIdExclusion, 'all'
    );

    // Select statuses from groupbasket
    $statuses = array();
    $db = new dbquery();
    $db->connect();
    $query = "SELECT status_id, label_status FROM " 
        . GROUPBASKET_STATUS . " left join " . $_SESSION['tablename']['status']
        . " on status_id = id "
        . " where basket_id= '" . $_SESSION['current_basket']['id']
        . "' and group_id = '" . $_SESSION['user']['primarygroup']
        . "' and action_id = " . $actionId;
    $db->query($query);

    if($db->nb_result() > 0) {
        while($status = $db->fetch_object()) {
            $statuses[] = array(
                'ID' => $status->status_id,
                'LABEL' => $db->show_string($status->label_status)
            );
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
	
	require_once 'modules/records_management/class/RecordsManagementController.php';
    require_once 'core/tests/class/ViewController.php';
    $RecordsManagementController = new RecordsManagementController();
    $ViewController = new ViewController();
    $view = $ViewController->createView();
    $folder_select = $view->createSelect();
    $folder_select->setAttribute('id', 'schedule');
    $folder_select->setAttribute('name', 'schedule');
    $folder_select->setAttribute('onChange', 'ArchiveObjectIndexSchedule__select()');
    $Schedules = 
        $RecordsManagementController->read(
            'Schedule',
            '100'
        );
    $folder_select->addOption(
        '', 'Sélectionnez un type d\'archive...'
    );
    makeScheduleList(
        $Schedules,
        $folder_select
    );
	

    $frmStr .= '<div id="validleft">';
    $frmStr .= '<div id="index_div" style="display:none;";>';
    $frmStr .= '<h1 class="tit" id="action_title"><img src="'
            . $_SESSION['config']['businessappurl'] . 'static.php?filename='
            . 'file_index_b.gif"  align="middle" alt="" />' . _INDEXING_BUSINESS;
    $frmStr .= '</h1>';
    $frmStr .= '<div id="frm_error_' . $actionId . '" class="indexing_error">'
            . '</div>';
    $frmStr .= '<form name="index_file" method="post" id="index_file" action="#"'
            . ' class="forms indexingformBusiness" style="text-align:left;">';
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

    if (! isset($_SESSION['FILE']['extension'])
        || $_SESSION['FILE']['extension'] == ""
    ) {
        $frmStr .= '<div  style="display:block" id="choose_file_div">';
        $frmStr .= '<iframe src="' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&dir=indexing_searching&page='
                . 'choose_file" name="choose_file" id="choose_file" '
                . 'frameborder="0" scrolling="no" width="100%" height="25">'
                . '</iframe>';
        $frmStr .= '</div>';
    }
    $frmStr .= '<hr />';
    
    $frmStr .= '<h4 onclick="new Effect.toggle(\'general_infos_div\', \'blind\', {delay:0.2});'
        . 'whatIsTheDivStatus(\'general_infos_div\', \'divStatus_general_infos_div\');" '
        . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
    $frmStr .= ' <span id="divStatus_general_infos_div" style="color:#1C99C5;">>></span>&nbsp;' 
        ._GENERAL_INFO;
    $frmStr .= '</h4>';
    $frmStr .= '<div id="general_infos_div"  style="display:inline">';
    $frmStr .= '<div class="ref-unit">';
    
    // $frmStr .= '<table width="100%" align="center" '
        // . 'border="1"  id="indexing_fields" style="display:block;">';
    
    $frmStr .= '<table width="100%" align="center" border="0" '
            . 'id="indexing_fields" style="display:block;">';
    /*** category ***/
    $frmStr .= '<tr id="category_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;align=center;"><span id="category_img_purchase" style="display:' . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_purchase.png" alt="' . _PURCHASE . '"/></span>'
            . '<span id="category_img_sell" style="display:' . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_sell.png" alt="' . _SELL . '"/></span>'
            . '<span id="category_img_enterprise_document" style="display:' . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_enterprise_document.png" alt="' . _ENTERPRISE_DOCUMENT . '"/></span>'
            . '<span id="category_img_human_resources" style="display:' . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'cat_doc_human_resources.png" alt="' . _HUMAN_RESOURCES . '"/></span></td>';
    $frmStr .= '<td style="width:200px;"><label for="category_id" class="form_title" >' . _CATEGORY . '</label></td>';
    //$frmStr .= '<td style="width:1px;">&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><select name="category_id" '
            . 'id="category_id" onchange="clear_error(\'frm_error_' . $actionId
            . '\');change_category(this.options[this.selectedIndex].value, \''
            . $displayValue . '\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'change_category\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=get_content_js\');launch_autocompleter_contacts(\''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&dir=indexing_searching&page=autocomplete_contacts\');$(\'contact\').value=\'\'">';
    $frmStr .= '<option value="">' . _CHOOSE_CATEGORY . '</option>';
    foreach (array_keys($_SESSION['coll_categories']['business_coll']) as $catId) {
        if ($catId <> 'default_category') {
            $frmStr .= '<option value="' . $catId . '"';
            if ($_SESSION['coll_categories']['business_coll']['default_category'] == $catId
                || (isset($_SESSION['indexing']['category_id'])
                    && $_SESSION['indexing']['category_id'] == $catId)
            ) {
                $frmStr .= 'selected="selected"';
            }
            $frmStr .= '>' . $_SESSION['coll_categories']['business_coll'][$catId] . '</option>';
        }
    }
    $frmStr .= '</select></td>';
    $frmStr .= '<td><span class="red_asterisk" id="category_id_mandatory" '
            . 'style="display:inline;">*</span></td>';
    $frmStr .= '</tr>';
    /*** doctype ***/
    $frmStr .= '<tr id="doctype_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;"><span class="form_title" '
            . 'id="doctype_res"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'document.png" alt="' . _FILING . '"/>'
            . '</span></td><td><label for="type_id">' . _FILING . '</label></td>';
    //$frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><select name="type_id" id="type_id" '
            . 'onchange="clear_error(\'frm_error_' . $actionId . '\');'
            . 'change_doctype(this.options[this.selectedIndex].value, \''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&dir=indexing_searching&page=change_doctype&coll_id=' . $collId . '\', \''
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
    /*** subject ***/
    $frmStr .= '<tr id="subject_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'subject.png" alt="' . _SUBJECT . '"/></td><td class="indexing_label"><label for="subject" class="form_title" >' . _SUBJECT
            . '</label></td>';
    $frmStr .= '<td class="indexing_field"><textarea name="subject" '
            . 'id="subject"  rows="2" onchange="clear_error(\'frm_error_'
            . $actionId . '\');" ></textarea></td>';
    $frmStr .= '<td><span class="red_asterisk" id="subject_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Contact ***/
    $contact_mode = "view";
    if($core->test_service('update_contacts','apps', false)) $contact_mode = 'up';
    $frmStr .= '<tr id="contact_id_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;">'
            . '<a href="#" id="contact_card" title="' . _CONTACT_CARD
            . '" onclick="document.getElementById(\'info_contact_iframe\').src=\'' . $_SESSION['config']['businessappurl']
                . 'index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid=\'+document.getElementById(\'contactid\').value+\'&addressid=\'+document.getElementById(\'addressid\').value;new Effect.toggle(\'info_contact_div\', '
                . '\'blind\', {delay:0.2});return false;"><span id="contact_purchase_img" style="display:' 
        . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'supplier.png" alt="' . _SUPPLIER . '"/></span>'
            . '<span id="contact_sell_img" style="display:' 
                . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'purchaser.png" alt="' . _PURCHASER . '"/></span>'
            . '<span id="contact_enterprise_document_img" style="display:' 
                . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'my_contacts_off.gif" alt="' . _AUTHOR . '"/></span>'
            . '<span id="contact_human_resources_img" style="display:' 
                . $displayValue . ';"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'employee.png" alt="' . _EMPLOYEE . '"/></span></td>'
            . '<td><label for="contact" class="form_title" >'
            . '<span id="contact_label_purchase" style="display:' 
                . $displayValue . ';">' . _SUPPLIER . '</span>'
            . '<span id="contact_label_sell" style="display:' 
                . $displayValue . ';">' . _PURCHASER . '</span>'
            . '<span id="contact_label_enterprise_document" style="display:' 
                . $displayValue . ';">' . _AUTHOR . '</span>'
            . '<span id="contact_label_human_resources" style="display:' 
                . $displayValue . ';">' . _EMPLOYEE . '</span></a>';
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
    $frmStr .= '<td class="indexing_field"><input type="text" name="contact" '
            . 'id="contact" onblur="clear_error(\'frm_error_' . $actionId . '\');'
            . 'display_contact_card(\'visible\');" /><div id="show_contacts" '
            . 'class="autocomplete autocompleteIndex"></div></td>';
    $frmStr .= '<td><span class="red_asterisk" id="contact_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    $frmStr .= '<input type="hidden" id="contactid" />';
    $frmStr .= '<input type="hidden" id="addressid" />';
    /*** Identifier ***/
    $frmStr .= '<tr id="identifier_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'identifier.png" alt="' . _IDENTIFIER 
                . '"/></td><td><label for="identifier" class="form_title" >' . _IDENTIFIER
            . '</label></td>';
    //$frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="identifier" type="text" '
            . 'id="identifier" onchange="clear_error(\'frm_error_' . $actionId
            . '\');"/></td>';
    $frmStr .= '<td><span class="red_asterisk" id="identifier_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** doc_date ***/
    $frmStr .= '<tr id="doc_date_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'date.png" alt="' . _DOC_DATE 
                . '"/></td><td><label for="doc_date" class="form_title" '
            . 'id="doc_date_label">' . _DOC_DATE
            . '</label></td>';
    //$frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="doc_date" type="text" '
            . 'id="doc_date" value="" onclick="clear_error(\'frm_error_'
            . $actionId . '\');showCalender(this);" /></td>';
    $frmStr .= '<td><span class="red_asterisk" id="doc_date_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr >';
    /*** currency ***/
    $frmStr .= '<tr id="currency_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'currency.png" alt="' . _CURRENCY 
                . '"/></td><td><label for="currency" class="form_title">' . _CURRENCY
            . '</label></td>';
    $frmStr .= '<td class="indexing_field">'
        . '<select id="currency" name="currency" onchange="clear_error(\'frm_error_' . $actionId
        . '\');convertAllBusinessAmount();">';
	foreach (array_keys($_SESSION['currency']) as $currency) {
		$frmStr .= '<option value="' . $currency . '"';
		if ($_SESSION['default_currency'] == $currency) {
			$frmStr .= 'selected="selected"';
		}
		$frmStr .= '>' . $_SESSION['currency'][$currency] . '</option>';
	}
		/*
    $frmStr .=  '<option value="EUR">EURO €</option>'
        . '<option value="USD">DOLLAR $</option>'
        . '<option value="JPY">YEN ¥</option>'
        . '<option value="GBP">POUND £</option>'
        . '<option value="XOF">CFA F</option>';*/
		
    $frmStr .= '</select>'
        . '</td>';
    $frmStr .= '<td><span class="red_asterisk" id="currency_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** net_sum ***/
    $frmStr .= '<tr id="net_sum_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'amount.png" alt="' . _NET_SUM 
                . '"/></td><td><label for="net_sum_use" class="form_title" >' . _NET_SUM
            . '</label></td>';
    $frmStr .= '<td class="indexing_field">'
        . '<input name="net_sum_use" type="text" '
        . 'id="net_sum_use" onchange="clear_error(\'frm_error_' . $actionId
        . '\');$(\'net_sum_preformatted\').value=convertAmount($(\'currency\').options[$(\'currency\').selectedIndex].value, this.value);'
        . '$(\'net_sum\').value=convertAmount(\'\', this.value);computeTotalAmount();" '
        . 'class="amountLeft" value="0" />&nbsp;'
        . '<input name="net_sum_preformatted" type="text" '
        . 'id="net_sum_preformatted" readonly="readonly" class="amountRight readonly" />'
        . '<input name="net_sum" id="net_sum" type="hidden" />'
        . '</td>';
    $frmStr .= '<td><span class="red_asterisk" id="net_sum_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** tax_sum ***/
    $frmStr .= '<tr id="tax_sum_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;">'
                . '&nbsp;'
                . '</td><td><label for="tax_sum" class="form_title" >' . _TAX_SUM
            . '</label></td>';
    $frmStr .= '<td class="indexing_field">'
        . '<input name="tax_sum_use" type="text" '
        . 'id="tax_sum_use" onchange="clear_error(\'frm_error_' . $actionId
        . '\');$(\'tax_sum_preformatted\').value=convertAmount($(\'currency\').options[$(\'currency\').selectedIndex].value, this.value);'
        . '$(\'tax_sum\').value=convertAmount(\'\', this.value);computeTotalAmount();" '
        . 'class="amountLeft" value="0" />&nbsp;'
        . '<input name="tax_sum_preformatted" type="text" '
        . 'id="tax_sum_preformatted" readonly="readonly" class="amountRight readonly" />'
        . '<input name="tax_sum" id="tax_sum" type="hidden" />'
        . '</td>';
    $frmStr .= '<td><span class="red_asterisk" id="tax_sum_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** total_sum ***/
    $frmStr .= '<tr id="total_sum_tr" style="display:' . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;">'
                 . '&nbsp;'
                . '</td><td><label for="total_sum_use" class="form_title" >' . _TOTAL_SUM
            . '</label></td>';
    $frmStr .= '<td class="indexing_field">'
        . '<input name="total_sum_use" type="text" '
        . 'id="total_sum_use" onchange="clear_error(\'frm_error_' . $actionId
        . '\');$(\'total_sum_preformatted\').value=convertAmount($(\'currency\').options[$(\'currency\').selectedIndex].value, this.value);'
        . '$(\'total_sum\').value=convertAmount(\'\', this.value);controlTotalAmount();" '
        . 'class="amountLeft" value="0" />&nbsp;'
        . '<input name="total_sum_preformatted" type="text" '
        . 'id="total_sum_preformatted" readonly="readonly" class="amountRight readonly" />'
        . '<input name="total_sum" id="total_sum" type="hidden" />'
        . '</td>';
    $frmStr .= '<td><span class="red_asterisk" id="total_sum_mandatory" '
            . 'style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    /*** Entities : department + diffusion list ***/
    if ($core->is_module_loaded('entities')) {
        $frmStr .= '<tr id="department_tr" style="display:' . $displayValue . ';">';
        $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?module=entities&filename='
                . 'department.png" alt="' . _DEPARTMENT_OWNER 
                . '"/></td><td><label for="department" class="form_title" '
                . 'id="label_dep_dest" style="display:inline;" >'
                . _DEPARTMENT_OWNER . '</label></td>';
        //$frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field">';
        $frmStr .= '<select name="destination" id="destination" onchange="clear_error(\'frm_error_' . $actionId . '\');" >';
            $frmStr .= '<option value="">' . _CHOOSE_DEPARTMENT . '</option>';
            $countAllEntities = count($allEntitiesTree);
            for ($cptEntities = 0;$cptEntities < $countAllEntities;$cptEntities++) {
                if (!$allEntitiesTree[$cptEntities]['KEYWORD']) {
                    $frmStr .= '<option value="' . $allEntitiesTree[$cptEntities]['ID'] . '"';
                    if ($allEntitiesTree[$cptEntities]['DISABLED']) {
                        $frmStr .= ' disabled="disabled" class="disabled_entity"';
                    } else {
                         //$frmStr .= ' style="font-weight:bold;"';
                    }
                    $frmStr .=  '>' 
                        .  $db->show_string($allEntitiesTree[$cptEntities]['SHORT_LABEL']) 
                        . '</option>';
                }
            }
        $frmStr .= '</select></td>';
        $frmStr .= '<td><span class="red_asterisk" id="destination_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        
        # Diffusion list model
        $frmStr .= '<tr id="difflist_tr" style="display:' . $displayValue . ';">';
        $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?module=entities&filename='
                . 'department.png" alt="' . _DIFFUSION_LIST 
                . '"/></td><td><label for="difflist" class="form_title" '
                . 'id="label_dep_dest" style="display:inline;" >'
                . _DIFFUSION_LIST . '</label></td>';
        //$frmStr .= '<td>&nbsp;</td>';
        $frmStr .= '<td class="indexing_field">';
        $frmStr .= '<select name="difflist" id="difflist" onchange="'
                    . 'clear_error(\'frm_error_' . $actionId . '\');'
                    . 'load_listmodel(this.options[this.selectedIndex], \'diff_list_div\', \'indexing\');'
                    . '$(\'diff_list_tr\').style.display=\''.$displayValue.'\''
                . ';" >';
        $frmStr .= '<option value="">' . _CHOOSE_DIFFUSION_LIST . '</option>';
        if(count($listmodels) > 0) {
            foreach($listmodels as $difflist_type_id => $listmodel) {
                $frmStr .= '<optgroup label="'.$difflistTypes[$difflist_type_id]->difflist_type_label . '">';
                for($i=0, $l=count($listmodel); $i<$l; $i++) {
                    $frmStr .= '<option data-object_type="'.$difflist_type_id.'" value="' . $listmodel[$i]['object_id'] . '">' 
                                .  $db->show_string($listmodel[$i]['description']) 
                            . '</option>';
                }
                $frmStr .= '</optgroup>';
            }
        }
        $frmStr .= '</select></td>';
        $frmStr .= '<td><span class="red_asterisk" id="difflist_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '<tr id="diff_list_tr" style="display:none;">';
        $frmStr .= '<td colspan="4">';
        $frmStr .= '<div id="diff_list_div" class="scroll_div" '
                //. 'style="height:200px; width:420px; border: 1px solid;"></div>';
                . 'style="width:420px; border: 1px solid;"></div>';
        $frmStr .= '</td>';
        $frmStr .= '</tr>';
    }
    
    /*** Process limit date ***/
    $frmStr .= '<tr id="process_limit_date_use_tr" style="display:'
            . $displayValue . ';">';
    $frmStr .= '<td style="width:30px;align:center;">&nbsp;</td><td><label for="process_limit_date_use" class="form_title" >'
            . _PROCESS_LIMIT_DATE_USE . '</label></td>';
    //$frmStr .= '<td>&nbsp;</td>';
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
    $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'process_limit_date.png" alt="' . _PROCESS_LIMIT_DATE 
                . '"/></td><td><label for="process_limit_date" class="form_title" >'
            . _PROCESS_LIMIT_DATE . '</label></td>';
    //$frmStr .= '<td>&nbsp;</td>';
    $frmStr .= '<td class="indexing_field"><input name="process_limit_date" '
            . 'type="text" id="process_limit_date" value="" '
            . 'onclick="showCalender(this);" '
            . 'onchange="clear_error(\'frm_error_' . $actionId . '\');"/></td>';
    $frmStr .= '<td><span class="red_asterisk" id="process_limit_date_mandatory"'
            . ' style="display:inline;">*</span>&nbsp;</td>';
    $frmStr .= '</tr>';
    
    /*** Status ***/
    if(count($statuses) > 0) {
        $frmStr .= '<tr id="status" style="display:' . $displayValue . ';">';
        $frmStr .= '<td style="width:30px;align:center;">&nbsp;</td><td><label for="status" class="form_title" >' . _STATUS
                . '</label></td>';
        $frmStr .= '<td class="indexing_field"><select name="status" '
                . 'id="status" onchange="clear_error(\'frm_error_' . $actionId
                . '\');">';
        $frmStr .= '<option value="">' . _CHOOSE_STATUS . '</option>';
        for ($i = 0; $i < count($statuses); $i ++) {
            $frmStr .= '<option value="' . $statuses[$i]['ID'] . '" ';
            if ($statuses[$i]['ID'] == 'NEW') {
                $frmStr .= 'selected="selected"';
            }
            $frmStr .= '>' . $statuses[$i]['LABEL'] . '</option>';
        }
        $frmStr .= '</select></td><td><span class="red_asterisk" id="market_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
    }
    
    $frmStr .= '</table>';
    
    $frmStr .= '</div>';
    $frmStr .= '</div>';
    
    /*** CUSTOM INDEXES ***/
    $frmStr .= '<div id="comp_indexes" style="display:block;">';
    $frmStr .= '</div>';
    
    $frmStr .= '<hr />';
  
	$frmStr .= '<h4 onclick="new Effect.toggle(\'classement\', \'blind\', {delay:0.2});'
        . 'whatIsTheDivStatus(\'classement\', \'divStatus_classement\');" '
        . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
    $frmStr .= ' <span id="divStatus_classement" style="color:#1C99C5;">>></span>&nbsp;Classement';
    $frmStr .= '</h4>';
    $frmStr .= '<div id="classement"  style="display:inline">';
    $frmStr .= '<div>';
	$frmStr .= '<table width="100%" align="center" border="0" >';
	
		/*** Boite archive ***/
	if ($core->is_module_loaded('physical_archive')) {
        $frmStr .= '<tr id="box_id_tr" style="display:' . $displayValue . ';">';
		$frmStr .= '<td style="width:30px;align:center;"></td>';
		$frmStr .= '<td><label for="arbox_id" class="form_title" id="label_box"'
                . ' style="display:inline;" >' . _BOX_ID . '</label></td>';
        $frmStr .= '<td class="indexing_field"><select name="arbox_id" '
                . 'id="arbox_id" onchange="clear_error(\'frm_error_' . $actionId
                . '\');" >';
        $frmStr .= '<option value="">' . _CHOOSE_BOX . '</option>';
        for ($i = 0; $i < count($boxes); $i ++) {
            $frmStr .= '<option value="' . $boxes[$i]['ID'] . '" >'
                    . $db->show_string($boxes[$i]['LABEL']) . '</option>';
        }
        $frmStr .= '</select></td>';
        $frmStr .= '</tr>';
    }
	
	    /*** Folder ***/
    if ($core->is_module_loaded('folder')) {
        $frmStr .= '<tr id="folder_tr" style="display:' . $displayValue . ';">';
        $frmStr .= '<td style="width:30px;align:center;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?module=folder&filename='
                . 'folders.gif" alt="' . _FOLDER 
                . '"/></td><td><label for="folder" class="form_title" >' . _FOLDER
                . '</label></td>';
        $frmStr .= '<td class="indexing_field"><input type="text" '
                . 'name="folder" id="folder" onblur="clear_error(\'frm_error_'
                . $actionId . '\');return false;" /><div id="show_folder" '
                . 'class="autocomplete"></div></td>';
        $frmStr .= '<td><span class="red_asterisk" id="folder_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
    }
	
		/*** Classement organique ***/
	$frmStr .= '<tr id="project_tr" style="display:' . $displayValue . ';">';
		$frmStr .= '<td style="width:30px;align:center;"></td>';
		$frmStr .= '<td>';
			$frmStr .= '<label for="project" class="form_title" >';
				$frmStr .= _ITEM_FOLDER;
			$frmStr .= '</label>';
		$frmStr .= '</td>';
		$frmStr .= '<td class="indexing_field">';
			$frmStr .= '<input type="text" name="project" id="project" value = "" onblur="clear_error(\'frm_error_'. $actionId . '\');return false;" />';
			$frmStr .= '<div id="project_autocomplete" class="autocomplete"></div>';
		$frmStr .= '</td>';
	$frmStr .= '</tr>';
	
	
		/*** _TYPE_DARCHIVE ***/
	$frmStr .= '<tr id="type_id_tr" style="display:' . $displayValue . ';">';
			$frmStr .= '<td style="width:30px;align:center;"></td>';
		$frmStr .= '<td>';
			$frmStr .= '<label for="schedule">';
				$frmStr .= '<span class="form_title" id="doctype_res" style="display:none;">';
					$frmStr .= _RM_DOCTYPE;
				$frmStr .= '</span>';
				$frmStr .= '<span class="form_title" id="doctype_mail" style="display:inline;">';
					$frmStr .= _RM_DOCTYPE;
				$frmStr .= '</span>';
			$frmStr .= '</label>';
		$frmStr .= '</td>';
		$frmStr .= '<td class="indexing_field">';
			$frmStr .= $folder_select->C14N();
		$frmStr .= '</td>';
	$frmStr .= '</tr>';
	
	/*** REGLE DE GESTION ***/
	$frmStr .= '<tr id="appraisal_code_tr" style="display:' . $displayValue . ';">';
			$frmStr .= '<td style="width:30px;align:center;"></td>';
		$frmStr .= '<td>';
			$frmStr .= '<label for="appraisal_code" class="form_title" style="display:inline;" >';
				$frmStr .= _APPRAISAL;
			$frmStr .= '</label>';
		$frmStr .= '</td>';
		$frmStr .= '<td class="indexing_field">';
			$frmStr .= '<select name="appraisal_code" id="appraisal_code" disabled="disabled" class="readonly" onchange="clear_error(\'frm_error_' . $actionId . '\');">';
				//$frmStr .= '<option value="">' . _CHOOSE_APPRAISAL . '</option>';
				$frmStr .= '<option value="conserver">Conserver</option>';
				$frmStr .= '<option value="detruire">Détruire</option>';
			$frmStr .= '</select>';
		$frmStr .= '</td>';
	$frmStr .= '</tr>';
	
	/*** DUA ***/
	$frmStr .= '<tr id="appraisal_duration_tr" style="display:' . $displayValue . ';">';
			$frmStr .= '<td style="width:30px;align:center;"></td>';
		$frmStr .= '<td>';
			$frmStr .= '<label for="appraisal_duration" class="form_title" style="display:inline;" >';
				$frmStr .= _DUA;
			$frmStr .= '</label>';
		$frmStr .= '</td>';
		$frmStr .= '<td class="indexing_field">';
			$frmStr .= '<input name="appraisal_duration" disabled="disabled" class="readonly" type="text" id="appraisal_duration" onclick="clear_error(\'frm_error_' . $actionId . '\');" />';
		$frmStr .= '</td>';
	$frmStr .= '</tr>';
	
	/*** COMMUNICABILITE ***/
	$frmStr .= '<tr id="access_restriction_code_tr" style="display:' . $displayValue . ';">';
			$frmStr .= '<td style="width:30px;align:center;"></td>';
		$frmStr .= '<td>';
			$frmStr .= '<label for="access_restriction_code" class="form_title" style="display:inline;" >';
				$frmStr .= _ACCESS_RESTRICTION;
			$frmStr .= '</label>';
		$frmStr .= '</td>';
		$frmStr .= '<td class="indexing_field">';
			$frmStr .= '<input name="access_restriction_code" disabled="disabled" class="readonly" type="text" id="access_restriction_code" onclick="clear_error(\'frm_error_' . $actionId . '\');" />';
		$frmStr .= '</td>';
	$frmStr .= '</tr>';	
	
	$frmStr .= '</table>';
	$frmStr .= '</div>';
    $frmStr .= '</div>';
	
	$frmStr .= '<hr />';

	/*** Complementary fields ***/
    $frmStr .= '<h4 onclick="new Effect.toggle(\'complementary_fields\', \'blind\', {delay:0.2});'
        . 'whatIsTheDivStatus(\'complementary_fields\', \'divStatus_complementary_fields\');" '
        . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
    $frmStr .= ' <span id="divStatus_complementary_fields" style="color:#1C99C5;"><<</span>&nbsp;' 
        . _OPT_INDEXES;
    $frmStr .= '</h4>';
    $frmStr .= '<div id="complementary_fields"  style="display:none">';
    $frmStr .= '<div>';
    
    if ($core->test_service('add_links', 'apps', false)) {
        $frmStr .= '<table width="100%" align="center" border="0" >';
        $frmStr .= '<tr id="attachment_tr" style="display:' . $displayValue
                . ';">';
        $frmStr .= '<td style="width:24px;align:left;"><img src="'
                . $_SESSION['config']['businessappurl'] . 'static.php?filename='
                . 'link.png" alt="' . _LINK_TO_DOC . '"  title="' . _LINK_TO_DOC . '"/>'
            . '</td><td style="width:200px;align:left;"><label for="attachment" class="form_title" >'
                . _LINK_TO_DOC . ' </label></td>';
        $frmStr .= '<td class="indexing_field"><input type="radio" '
                . 'name="attachment" id="attach" value="true" '
                . 'onclick="show_attach(\'true\');"'
                . ' /> '
                . _YES . ' <input type="radio" name="attachment" id="no_attach"'
                . ' value="false" checked="checked" '
                . 'onclick="show_attach(\'false\');"'
                . ' /> '
                . _NO . '</td>';
        $frmStr .= ' <td><span class="red_asterisk" id="attachment_mandatory" '
                . 'style="display:inline;">*</span>&nbsp;</td>';
        $frmStr .= '</tr>';
        $frmStr .= '<tr id="attach_show" style="display:none;">';
            $frmStr .= '<td>&nbsp;</td>';
            $frmStr .= '<td style="text-align: right;">';
                $frmStr .= '<a ';
                  $frmStr .= 'href="javascript://" ';
                  $frmStr .= 'onclick="window.open(';
                    $frmStr .= '\'' . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=indexing_searching&page=search_adv_business&mode=popup&action_form=show_res_id&modulename=attachments&init_search&nodetails\', ';
                    $frmStr .= '\'search_doc_for_attachment\', ';
                    $frmStr .= '\'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1100,height=775\'';
                  $frmStr .= ');"';
                  $frmStr .= ' title="' . _SEARCH . '"';
                $frmStr .= '>';
                    $frmStr .= '<span style="font-weight: bold;">';
                        $frmStr .= '<img ';
                          $frmStr .= 'src="' . $_SESSION['config']['businessappurl'] . 'static.php?filename=folder_search.gif" ';
                          $frmStr .= 'width="20px" ';
                          $frmStr .= 'height="20px" ';
                        $frmStr .= '/>';
                    $frmStr .= '</span>';
                $frmStr .= '</a>';
            $frmStr .= '</td>';
            $frmStr .= '<td style="text-align: right;">';
                $frmStr .= '<input ';
                  $frmStr .= 'type="text" ';
                  $frmStr .= 'name="res_id" ';
                  $frmStr .= 'id="res_id" ';
                  $frmStr .= 'class="readonly" ';
                  $frmStr .= 'readonly="readonly" ';
                  $frmStr .= 'value="" ';
                $frmStr .= '/>';
            $frmStr .= '</td>';
            $frmStr .= '<td>';
                $frmStr .= '<span class="red_asterisk" id="category_id_mandatory" style="display:inline;">';
                    $frmStr .= '*';
                $frmStr .= '</span>';
            $frmStr .= '</td>';
        $frmStr .= '</tr>';
        $frmStr .= '</table>';
    }
    
    $frmStr .= '<table width="100%" align="center" border="0" '
            . 'id="indexing_fields" style="display:block;">';


    
    /*** Tags ***/
    if ($core->is_module_loaded('tags') 
        && ($core->test_service('tag_view', 'tags',false) == 1)
        && ($core->test_service('add_tag_to_res', 'tags',false) == 1)
    ) {
        include_once('modules/tags/templates/index_mlb/index.php');
    }
    
    $frmStr .= '</table>';
    
    // Fin
    
    $frmStr .= '</div>';
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
                . 'onclick="getIframeContent(\''
                . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
                . '&page=getIframeTemplateContent\');valid_action_form(\'index_file\', \''
                . $pathManageAction . '\', \'' . $actionId . '\', \'' . $resId
                . '\', \'' . $table . '\', \'' . $module . '\', \'' . $collId
                . '\', \'' . $mode . '\', true);"/> ';
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
	
	/*** CAPTURE TOOLBAR ***/
    if (
		($core->test_service('scan', 'webtwain', false) === true)
        || 
		($core->test_service('photo_capture', 'photo_capture', false) === true)
    )
    {
		$frmStr .= '<div class="block" align="center" style="height:20px;width=100%;">';
		
		$frmStr .= '<table width="95%" cellpadding="0" cellspacing="0">';
		$frmStr .= '<tr align="center">';
		
		//Webtwain
        if ($core->test_service('scan', 'webtwain', false) === true) {
			$frmStr .= '<td>';
			$frmStr .= '|<span onclick="new Effect.toggle(\'webtwain_div\', \'appear\', {delay:0.2});'
				. 'whatIsTheDivStatus(\'webtwain_div\', \'divStatus_webtwain_div\');return false;" '
				. 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
			$frmStr .= '<span id="divStatus_webtwain_div" style="color:#1C99C5;"><<</span><b>'
				. '<small>' . _SCAN_DOCUMENT . '</small>';
			$frmStr .= '</b></span>|';
			$frmStr .= '</td>';
        }
		
		//Photo capture
        if ($core->test_service('photo_capture', 'photo_capture', false) === true){
			$frmStr .= '<td>';
			$frmStr .= '|<span onclick="new Effect.toggle(\'photo_capture_div\', \'appear\', {delay:0.2});'
				. 'whatIsTheDivStatus(\'photo_capture_div\', \'divStatus_photo_capture_div\');return false;" '
				. 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
			$frmStr .= '<span id="divStatus_photo_capture_div" style="color:#1C99C5;"><<</span><b>'
				. '<small>' . _PHOTO_CAPTURE . '</small>';
			$frmStr .= '</b></span>|';
			$frmStr .= '</td>';
        }
		//END TOOLBAR
		$frmStr .= '</table>';
		$frmStr .= '</div>';
	}
	
	//Webtwain frame
     if ($core->test_service('scan', 'webtwain', false) === true) {
        $frmStr .= '<div class="desc" id="webtwain_div" style="display:none;">';
        $frmStr .= '<div class="ref-unit">';
        $frmStr .= '<center><h2 onclick="new Effect.toggle(\'webtwain_div\', \'blind\', {delay:0.2});';
        $frmStr .= 'whatIsTheDivStatus(\'webtwain_div\', \'divStatus_webtwain_div\');';
        $frmStr .= 'return false;" onmouseover="this.style.cursor=\'pointer\';">' . _SCAN_DOCUMENT. '</h2></center>';
        $frmStr .= '<iframe src="'
            . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&module=webtwain&page=scan" '
            . 'name="scan_iframe" id="scan_iframe" width="100%" height="630px" align="center" '
            . 'scrolling="auto" frameborder="0" ></iframe>';
        $frmStr .= '</div>';
        $frmStr .= '</div>';
    }
	
	//Photo capture frame
    if ($core->test_service('photo_capture', 'photo_capture', false) === true){
		$_SESSION['photofile'] = array();
        $frmStr .= '<div class="desc" id="photo_capture_div" style="display:none;">';
        $frmStr .= '<div class="ref-unit">';
        $frmStr .= '<center><h2 onclick="new Effect.toggle(\'webtwain_div\', \'blind\', {delay:0.2});';
        $frmStr .= 'whatIsTheDivStatus(\'photo_capture_div\', \'divStatus_photo_capture_div\');';
        $frmStr .= 'return false;" onmouseover="this.style.cursor=\'pointer\';">' . _PHOTO_CAPTURE. '</h2></center>';
        $frmStr .= '<iframe src="'
            . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&module=photo_capture&page=photo_capture'
            . '&origin=document" name="photo_iframe" id="photo_iframe" '
			. 'width="100%" height="450px" align="center" '
            . 'scrolling="auto" frameborder="0" ></iframe>';
        $frmStr .= '</div>';
        $frmStr .= '</div>';
    }
	
    /**** Contact form start *******/
    $frmStr .= '<div id="create_contact_div" style="display:none">';
        $frmStr .= '<iframe width="100%" height="700" src="' . $_SESSION['config']['businessappurl']
                . 'index.php?display=false&dir=my_contacts&page=create_contact_iframe" name="contact_iframe" id="contact_iframe"'
                . ' scrolling="auto" frameborder="0" style="display:block;">'
                . '</iframe>';
    $frmStr .= '</div>';
    /**** Contact form end *******/
    /**** Contact info start *******/
    $frmStr .= '<div id="info_contact_div" style="display:none">';
        $frmStr .= '<iframe width="100%" height="800" name="info_contact_iframe" id="info_contact_iframe"'
                . ' scrolling="auto" frameborder="0" style="display:block;">'
                . '</iframe>';
    $frmStr .= '</div>';
    /**** Contact info end *******/     
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
        && isset($_SESSION['user']['services']['scan'])
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
            . $_SESSION['coll_categories']['business_coll']['default_category'] . '\', \'' . $displayValue
            . '\', \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page='
            . 'change_category\',  \'' . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&page=get_content_js\');'
            . 'launch_autocompleter_contacts_v2(\''
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
            . '&dir=indexing_searching&page=autocomplete_contacts\', \'contact\', \'show_contacts\', \'\', \'contactid\', \'addressid\');convertAllBusinessAmount();';

    if ($core->is_module_loaded('folder')) {
        $frmStr .= ' initList(\'folder\', \'show_folder\',\''
                . $_SESSION['config']['businessappurl'] . 'index.php?display='
                . 'true&module=folder&page=autocomplete_folders&mode=folder\','
                . ' \'Input\', \'2\');';
    }
	        $frmStr .= ' initList(\'project\', \'project_autocomplete\',\''
                . $_SESSION['config']['businessappurl'] . 'index.php?display='
                . 'true&module=folder&page=autocomplete_fileplan&mode=folder\','
                . ' \'Input\', \'2\');';
	
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
        if ($attach) {
            $idDoc = get_value_fields($values, 'res_id');
            if (! $idDoc || empty($idDoc)) {
                $_SESSION['action_error'] .= _LINK_REFERENCE . '<br/>';
            }
            if (! empty($_SESSION['action_error'])) {
                return false;
            }
        }
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
        // if (!empty($contact)) {
        //     if (preg_match('/\([0-9]+\)$/', $contact) == 0) {
        //         $_SESSION['action_error'] = $_ENV['categories'][$catId]['contact_id']['label']
        //             . ' ' . _WRONG_FORMAT . '.<br/>'. _USE_AUTOCOMPLETION;
        //         return false;
        //     }
        // }
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
        if (! empty($folder)) {
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
            $paReturnValue = $physicalArchive->load_box_db_business(
                $boxId, $catId, $_SESSION['user']['UserId']
            );
            if ($paReturnValue == false) {
                $_SESSION['action_error'] = _ERROR_TO_INDEX_NEW_BATCH_WITH_PHYSICAL_ARCHIVE;
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
    for ($i = 0; $i < count($values); $i ++) {
        if ($values[$i]['ID'] == $field) {
            return  $values[$i]['VALUE'];
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
 *          $data['result'] : res_id of the new file followed by #
 *          $data['history_msg'] : Log complement (empty by default)
 *          $data['page_result'] : Page to load when action is done and modal closed
 **/
function manage_form($arrId, $history, $actionId, $label_action, $status, $collId, $table, $formValues )
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
	
	$custom_id = get_value_fields($formValues, 'schedule');
	if($custom_id != ''){ 
		array_push(
			$_SESSION['data'],
			array(
				'column' => 'custom_n1',
				'value' => $custom_id,
				'type' => 'integer',
			)
		);
	}
	
	$project_id = get_value_fields($formValues, 'project');
	preg_match('#\(+(.*)\)+#', $project_id, $result); 
	
	if($result[1] != ''){ 
		array_push(
			$_SESSION['data'],
			array(
				'column' => 'custom_n2',
				'value' => $result[1],
				'type' => 'integer',
			)
		);
	}
	
	$arbox_id = get_value_fields($formValues, 'arbox_id');
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'arbox_id',
            'value' => $arbox_id,
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
    //store the initiator entity
    if (isset($_SESSION['user']['primaryentity']['id'])) {
        array_push(
            $_SESSION['data'],
            array(
                'column' => 'initiator',
                'value' => $_SESSION['user']['primaryentity']['id'],
                'type' => 'string',
            )
        );
    }
    $status_id = get_value_fields($formValues, 'status');
    if(empty($status_id) || $status_id === "") $status_id = 'BAD';
    array_push(
        $_SESSION['data'],
        array(
            'column' => 'status',
            'value' => $status_id,
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
            if ($formValues[$i]['VALUE'] == '') {
                $formValues[$i]['VALUE'] = '0';
            }
            if (isset($_ENV['categories'][$catId][$tmpId]['table'])
                && $_ENV['categories'][$catId][$tmpId]['table'] == 'res'
            ) {
                    array_push(
                        $_SESSION['data'],
                        array(
                            'column' => $tmpId,
                            'value'  => str_replace(",", ".", $formValues[$i]['VALUE']),
                            'type'   => 'integer',
                        )
                    );
            } else if (isset($_ENV['categories'][$catId][$tmpId]['table'])
                && $_ENV['categories'][$catId][$tmpId]['table'] == 'coll_ext'
            ) {
                $queryExtFields .= $tmpId . ',';
                $queryExtValues .= str_replace(",", ".", $formValues[$i]['VALUE']) . ',';
            }
        } else if (isset($_ENV['categories'][$catId][$tmpId]['type_field'])
            && isset($_ENV['categories'][$catId][$tmpId]['table'])
            && $_ENV['categories'][$catId][$tmpId]['type_field'] == 'string'
            && $_ENV['categories'][$catId][$tmpId]['table'] <> 'none'
        ) {
            //FIX BUG WITH -- and ;
            $formValues[$i]['VALUE']=str_replace(';', ' ', $formValues[$i]['VALUE']);
            $formValues[$i]['VALUE']=str_replace('--', '-', $formValues[$i]['VALUE']);

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
    if (isset($_ENV['categories'][$catId]['contact_id'])) {
        $contact = get_value_fields($formValues, 'contact');
        //echo 'contact '.$contact.', type '.$contactType;
        // $contactId = str_replace(
        //     ')', '', substr($contact, strrpos($contact, '(') + 1)
        // );
        $contactId = get_value_fields($formValues, 'contactid');

        if ($contactId <> '') {
            $queryExtFields .= 'contact_id,';
            $queryExtValues .= $contactId . ',';

            $addressId = get_value_fields($formValues, 'addressid');
            
            $queryExtFields .= 'address_id,';
            $queryExtValues .= "'" . $db->protect_string_db($addressId)
                            . "',";
        }
    }
    if ($core->is_module_loaded('folder')) {
        $folderId = '';
        $folder = get_value_fields($formValues, 'folder');
        $folderId = str_replace(
            ')', '', substr($folder, strrpos($folder, '(') + 1)
        );
        
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
	if ($core->is_module_loaded('physical_archive')) {
        // Arbox_id + Arbatch_id
        $boxId = get_value_fields($formValues, 'arbox_id');
        if ($boxId <> '') {
            /*array_push(
                $_SESSION['data'],
                array(
                    'column' => 'arbox_id',
                    'value' => $boxId,
                    'type' => 'integer',
                )
            );*/
            $physicalArchive = new physical_archive();
            $paReturnValue = $physicalArchive->load_box_db_business(
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
        //######
        $queryExtFields = preg_replace('/,$/', ',res_id)', $queryExtFields);
        $queryExtValues = preg_replace(
            '/,$/', ',' . $resId . ')', $queryExtValues
        );
        $queryExt = ' insert into ' . $tableExt . ' ' . $queryExtFields
                   . ' values ' . $queryExtValues;
        $db->connect();
        $db->query($queryExt);
        if ($core->is_module_loaded('folder') && ! empty($folderId)
            && $_SESSION['history']['folderup']
        ) {
            //  echo 'folder '.$resId. " ";
            $hist = new history();
            $hist->add(
                $_SESSION['tablename']['fold_folders'], $folderId, "UP", 'folderup',
                _DOC_NUM . $resId . _ADDED_TO_FOLDER,
                $_SESSION['config']['databasetype'], 'apps'
            );
        }
        //$db->show();
        if ($core->is_module_loaded('entities')) {
            //  echo 'entities '.$resId. " ";
            $loadListDiff = true;
            if ($loadListDiff) {
                require_once 'modules/entities/class/class_manage_listdiff.php';
                $diffList = new diffusion_list();
				$params = array(
                    'mode' => 'listinstance',
                    'table' => $_SESSION['tablename']['ent_listinstance'],
                    'coll_id' => $collId,
                    'res_id' => $resId,
                    'user_id' => $_SESSION['user']['UserId'],
                );
                $diffList->load_list_db(
                    $_SESSION['indexing']['diff_list'], $params, 
					'DOC', $_SESSION['indexing']['diff_list']['object_type']
                );
            }
        }
		
        if ($core->is_module_loaded('tags')) {
                include_once('modules/tags/tags_update.php');
        }
		
		//Photo capture module
		if ($core->is_module_loaded('photo_capture') && isset($_SESSION['photofile']['name'])) {
			require_once("modules".DIRECTORY_SEPARATOR."photo_capture"
				.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR
				."class_modules_tools.php");
			$photo_capture = new photo_capture();
			$photo_capture->addPhoto($collId, $resId);
		}
    } else {
        $_SESSION['action_error'] = _ERROR_RES_ID;
        return false;
    }

    if ($attach) {
        $idDoc = get_value_fields($formValues, 'res_id');
        $queryLink = "INSERT INTO res_linked (res_parent, res_child, coll_id) VALUES('" . $idDoc . "', '" . $resId . "', '" . $_SESSION['collection_id_choice'] . "')";

        $db->connect();
        $db->query($queryLink);

        $hist2 = new history();
        $hist2->add($table,
           $resId,
           "ADD",
           'linkadd',
           _LINKED_TO . $idDoc,
           $_SESSION['config']['databasetype'],
           'apps'
        );

        $hist3 = new history();
        $hist3->add($table,
            $idDoc,
           "UP",
           'linkup',
           '(doc. ' . $resId . ')' . _NOW_LINK_WITH_THIS_ONE,
           $_SESSION['config']['databasetype'],
           'apps'
        );

    }
    if ($core->is_module_loaded('tags')) {
        include_once('modules/tags/tags_update.php');
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
                         . 'index.php?page=details_business&dir=indexing_searching'
                         . '&coll_id=' . $collId . '&id=' . $resId
    );
}

function makeScheduleList(
    $ScheduleTree,
    $Schedule__select,
    $level = 0
) {
           
    $subSchedules = $ScheduleTree->Schedule;
    $l = $subSchedules->length;
    for($i=0; $i<$l; $i++) {
        $subSchedule = $subSchedules[$i];
        $option = 
            $Schedule__select->addOption(
                $subSchedule->schedule_id,
                str_repeat(' ', $level) . $subSchedule->Identifier
            );
        $option->setDataAttribute(
            'appraisal_code',
            $subSchedule->AppraisalCode
        );
        $option->setDataAttribute(
            'appraisal_duration',
            $subSchedule->AppraisalDuration
        );
        $option->setDataAttribute(
            'access_restriction_code',
            $subSchedule->AccessRestrictionCode
        );
        
        if($level < 1)
            $option->setStyle('font-variant', 'small-caps');
        if($level < 2)
            $option->setStyle('text-decoration', 'underline');
        if($level < 3)
            $option->setStyle('font-weight', 'bold');
        
        makeScheduleList(
            $subSchedule,
            $Schedule__select,
            $level+1
        );
    }
}
