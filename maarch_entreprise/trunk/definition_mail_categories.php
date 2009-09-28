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
* @brief   Contains data structure used to get the proper index for a given category, to checks data and to loads in db (indexing, process, validation, details, ...) and the function to access it
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

///////////////////// Pattern to check dates
if($_SESSION['config']['databasetype'] == "SQLSERVER")
{
	$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
else // MYSQL & POSTGRESQL
{
	$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}

/*
 * Categories are described in a global variable : $_ENV['categories']
 * $_ENV['categories'][category_id]
 * 		['img_cat'] : url of the icon category
 *		[id_field] // id_field must be an identifier of a field in the form and in the database
 * 			['type_form'] = Form type field: 'integer', 'date', 'string', 'yes_no', 'special'
 * 			['type_field'] = Database type field :'integer', 'date', 'string', 'yes_no', 'special'
 * 			['mandatory'] = true or false
 * 			['label'] = label of the field
 * 			['img'] = Image url (optional)
 * 			['table'] = keyword : 'res' = field in the res_x like table
 * 								  'coll_ext' = field in collection ext table (ex : mlb_ext_coll)
 * 					  			  'none' = field in no table, only in form for special functionnality
 * 								  'special' = field in other table, handled in the code
 * 			['modify'] = true or false (optional) : can we modify this field (used in details.php)
 * 			['form_show'] = keyword (used with modify = true)
 * 							 'select' = displayed in a select item
 * 							 'textfield' = displayed in text input
 * 							 'date' = displayed in a date input (with calendar activated)
 *		['other_cases'] // particular cases handled in code
 *            [identifier] // keyword handled in the code
 * 					['type_form'] = Form type field:'integer', 'date', 'string', 'yes_no', 'special'
 * 					['type_field'] = Databse type field :'integer', 'date', 'string', 'yes_no', 'special'
 * 					['mandatory'] = true or false
 * 					['label'] = label of the field
 * 					['table'] = keyword : 'res' = field in the res_x like table
 * 							     		  'coll_ext' = field in collection ext table (ex : mlb_ext_coll)
 * 										  'none' = field in no table, only in form for special functionnality
 * 										  'special' = field in aother table, handled in the code
 * 					['modify'] = true or false (optional) : can we modify this field (used in details.php)
 * 					['form_show'] = keyword (used with modify = true)
 * 								'select' = displayed in a select item
 * 							 	'textfield' = keyword : 'date' = date input field
 * 							 	'date' = displayed in a date input (with calendar activated)
 *
 */

/**************************** Categories descriptions******************************/
$_ENV['categories'] = array();

///////////////////////////// INCOMING ////////////////////////////////////////////////
$_ENV['categories']['incoming'] = array();
$_ENV['categories']['incoming']['img_cat'] = $_SESSION['config']['businessappurl'].'img/cat_doc_incoming.gif';
$_ENV['categories']['incoming']['other_cases'] = array();
$_ENV['categories']['incoming']['priority'] = array( 'type_form' => 'integer', 'type_field' => 'integer','mandatory' => true, 'label' => _PRIORITY, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['incoming']['type_id'] = array('type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _DOCTYPE_MAIL, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['incoming']['doc_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'mandatory' => true, 'label' => _MAIL_DATE, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/date_arr.gif', 'modify' => true, 'form_show' => 'date');
$_ENV['categories']['incoming']['admission_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'mandatory' => true, 'label' => _RECEIVING_DATE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/date_arriv.gif', 'modify' => true, 'form_show' => 'date');
$_ENV['categories']['incoming']['nature_id'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _NATURE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/nature_send.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['incoming']['subject'] = array('type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _SUBJECT, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/object.gif', 'modify' => true, 'form_show' => 'textfield');
$_ENV['categories']['incoming']['process_limit_date_use'] = array('type_form' => 'radio', 'mandatory' => true, 'label' => _PROCESS_LIMIT_DATE_USE, 'table' => 'none',  'values' => array('Y', 'N'), 'modify' => false);
$_ENV['categories']['incoming']['other_cases']['process_limit_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'label' => _PROCESS_LIMIT_DATE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/date_limit.gif' , 'modify' => false);
$_ENV['categories']['incoming']['type_contact'] = array( 'type_form' => 'radio', 'mandatory' => true, 'label' => _SHIPPER_TYPE, 'table' => 'none', 'values' => array('internal', 'external'), 'modify' => false);
$_ENV['categories']['incoming']['other_cases']['contact'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _SHIPPER, 'table' => 'coll_ext', 'special' => 'exp_user_id,exp_contact_id', 'modify' => false);


///////////////////////////// OUTGOING ////////////////////////////////////////////////
$_ENV['categories']['outgoing'] = array();
$_ENV['categories']['outgoing']['img_cat'] = $_SESSION['config']['businessappurl'].'img/cat_doc_outgoing.gif';
$_ENV['categories']['outgoing']['other_cases'] = array();
$_ENV['categories']['outgoing']['priority'] = array( 'type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _PRIORITY, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['outgoing']['type_id'] = array('type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _DOCTYPE_MAIL, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['outgoing']['doc_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'mandatory' => true, 'label' => _MAIL_DATE, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/small_calend.gif', 'modify' => true, 'form_show' => 'date');
$_ENV['categories']['outgoing']['nature_id'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _NATURE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/nature_send.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['outgoing']['subject'] = array('type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _SUBJECT, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/object.gif', 'modify' => true, 'form_show' => 'textfield');
$_ENV['categories']['outgoing']['other_cases']['chrono_number'] = array('type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _CHRONO_NUMBER, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/object.gif', 'modify' => false, 'form_show' => 'textfield');
$_ENV['categories']['outgoing']['process_limit_date_use'] = array('type_form' => 'radio', 'mandatory' => true, 'label' => _PROCESS_LIMIT_DATE_USE, 'table' => 'none',  'values' => array('Y', 'N'), 'modify' => false);
$_ENV['categories']['outgoing']['other_cases']['process_limit_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'label' => _PROCESS_LIMIT_DATE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/date_limit.gif', 'modify' => false, 'form_show' => 'date');
$_ENV['categories']['outgoing']['type_contact'] = array( 'type_form' => 'radio', 'mandatory' => true, 'label' => _DEST_TYPE, 'table' => 'none', 'values' => array('internal', 'external'), 'modify' => false);
$_ENV['categories']['outgoing']['other_cases']['contact'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _DEST, 'table' => 'coll_ext', 'special' => 'dest_user_id,dest_contact_id', 'modify' => false);


///////////////////////////// INTERNAL ////////////////////////////////////////////////
$_ENV['categories']['internal'] = array();
$_ENV['categories']['internal']['img_cat'] =  $_SESSION['config']['businessappurl'].'img/cat_doc_internal.gif';
$_ENV['categories']['internal']['other_cases'] = array();
$_ENV['categories']['internal']['priority'] = array('type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _PRIORITY, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['internal']['type_id'] = array('type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _DOCTYPE_MAIL, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif',  'modify' => true, 'form_show' => 'select');
$_ENV['categories']['internal']['doc_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'mandatory' => true, 'label' => _MAIL_DATE, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/date_arr.gif', 'modify' => true, 'form_show' => 'date');
$_ENV['categories']['internal']['nature_id'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _NATURE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/nature_send.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['internal']['subject'] = array('type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _SUBJECT, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/object.gif', 'modify' => true, 'form_show' => 'textfield');
$_ENV['categories']['internal']['process_limit_date_use'] = array('type_form' => 'radio', 'mandatory' => true, 'label' => _PROCESS_LIMIT_DATE_USE, 'table' => 'none',  'values' => array('Y', 'N'), 'modify' => false);
$_ENV['categories']['internal']['other_cases']['process_limit_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'label' => _PROCESS_LIMIT_DATE, 'table' => 'coll_ext', 'img' => $_SESSION['config']['businessappurl'].'img/date_limit.gif', 'modify' => false);
$_ENV['categories']['internal']['type_contact'] = array( 'type_form' => 'radio', 'mandatory' => true, 'label' => _DEST_TYPE, 'table' => 'none', 'values' => array('internal', 'external'), 'modify' => false);
$_ENV['categories']['internal']['other_cases']['contact'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _DEST, 'table' => 'coll_ext', 'special' => 'dest_user_id,dest_contact_id', 'modify' => false);

/////////////////////////////MARKET DOCUMENT////////////////////////////////////////////////
$_ENV['categories']['market_document'] = array();
$_ENV['categories']['market_document']['img_cat'] = $_SESSION['config']['businessappurl'].'img/cat_doc_market.gif';
$_ENV['categories']['market_document']['other_cases'] = array();
$_ENV['categories']['market_document']['type_id'] = array('type_form' => 'integer', 'type_field' => 'integer', 'mandatory' => true, 'label' => _DOCTYPE, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/mini_type.gif', 'modify' => true, 'form_show' => 'select');
$_ENV['categories']['market_document']['doc_date'] = array( 'type_form' => 'date', 'type_field' => 'date', 'mandatory' => true, 'label' => _DOC_DATE, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/small_calend.gif', 'modify' => true, 'form_show' => 'date');
$_ENV['categories']['market_document']['subject'] = array('type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _SUBJECT, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/object.gif', 'modify' => true, 'form_show' => 'textfield');
$_ENV['categories']['market_document']['author'] = array('type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _AUTHOR, 'table' => 'res', 'img' => $_SESSION['config']['businessappurl'].'img/author.gif', 'modify' => true, 'form_show' => 'textfield');


/////////////////////////////MODULES SPECIFIC////////////////////////////////////////////////
$core = new core_tools();
if($core->is_module_loaded('entities'))
{
	//Entities module (incoming)
	$_ENV['categories']['incoming']['destination'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _DEPARTMENT_DEST, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'entities/img/manage_entities_b_small.gif', 'modify' => false, 'form_show' => 'select');
	$_ENV['categories']['incoming']['other_cases']['diff_list'] = array( 'type' => 'special', 'mandatory' => true, 'label' => _DIFF_LIST,'table' => 'special');

	// Entities module (outgoing)
	$_ENV['categories']['outgoing']['destination'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _DEPARTMENT_EXP, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'entities/img/manage_entities_b_small.gif', 'modify' => false, 'form_show' => 'select');
	$_ENV['categories']['outgoing']['other_cases']['diff_list'] = array( 'type' => 'special', 'mandatory' => true, 'label' => _DIFF_LIST,'table' => 'special');

	// Entities module (internal)
	$_ENV['categories']['internal']['destination'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _DEPARTMENT_DEST, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'entities/img/manage_entities_b_small.gif', 'modify' => false, 'form_show' => 'select');
	$_ENV['categories']['internal']['other_cases']['diff_list'] = array( 'type' => 'special', 'mandatory' => true, 'label' => _DIFF_LIST,'table' => 'special');
}
if($core->is_module_loaded('physical_archive'))
{
	//Physical Archive (incoming)
	$_ENV['categories']['incoming']['other_cases']['arbox_id'] = array( 'type_form' => 'interger', 'type_field' => 'integer', 'mandatory' => true, 'label' => _BOX_ID, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'physical_archive/img/pa_boxes.gif', 'modify' => true, 'form_show' => 'select');
	// Physical Archive (outgoing)
	$_ENV['categories']['outgoing']['other_cases']['arbox_id'] = array( 'type_form' => 'interger', 'type_field' => 'integer', 'mandatory' => true, 'label' => _BOX_ID, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'physical_archive/img/pa_boxes.gif', 'modify' => true, 'form_show' => 'select');
	// Physical Archive (internal)
	$_ENV['categories']['internal']['other_cases']['arbox_id'] = array( 'type_form' => 'interger', 'type_field' => 'integer', 'mandatory' => true, 'label' => _BOX_ID, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'physical_archive/img/pa_boxes.gif', 'modify' => true, 'form_show' => 'select');
	// Physical Archive (market_document)
	$_ENV['categories']['market_document']['other_cases']['arbox_id'] = array( 'type_form' => 'interger', 'type_field' => 'integer', 'mandatory' => true, 'label' => _BOX_ID, 'table' => 'res', 'img' => $_SESSION['urltomodules'].'physical_archive/img/pa_boxes.gif', 'modify' => true, 'form_show' => 'select');
}

if($core->is_module_loaded('folder'))
{
	//Folder (incoming)
	$_ENV['categories']['incoming']['other_cases']['market'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => false, 'label' => _MARKET, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/market.gif', 'modify' => true, 'form_show' => 'autocomplete');
	$_ENV['categories']['incoming']['other_cases']['project'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => false, 'label' => _PROJECT, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/doc_project.gif', 'modify' => true, 'form_show' => 'autocomplete');
	//Folder (outgoing)
	$_ENV['categories']['outgoing']['other_cases']['market'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => false, 'label' => _MARKET, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/market.gif', 'modify' => true, 'form_show' => 'autocomplete');
	$_ENV['categories']['outgoing']['other_cases']['project'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _PROJECT, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/doc_project.gif', 'modify' => true, 'form_show' => 'autocomplete');
	//Folder (internal)
	$_ENV['categories']['internal']['other_cases']['market'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => false, 'label' => _MARKET, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/market.gif', 'modify' => true, 'form_show' => 'autocomplete');
	$_ENV['categories']['internal']['other_cases']['project'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => false, 'label' => _PROJECT, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/doc_project.gif', 'modify' => true, 'form_show' => 'autocomplete');
	//Folder (market_document)
	$_ENV['categories']['market_document']['other_cases']['market'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => false, 'label' => _MARKET, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/market.gif', 'modify' => true, 'form_show' => 'autocomplete');
	$_ENV['categories']['market_document']['other_cases']['project'] = array( 'type_form' => 'string', 'type_field' => 'string', 'mandatory' => true, 'label' => _PROJECT, 'table' => 'none', 'img' => $_SESSION['config']['businessappurl'].'img/doc_project.gif', 'modify' => true, 'form_show' => 'autocomplete');
}


/************************* END *************************************************************/

/**
* Gets the index of a given res_id in an array
*
* @param $coll_id string  Collection identifier
* @param $res_id string  Resource identifier
* @param $mode string  'full' : fills the array with maximum data (id, value, show_value,etc...), 'minimal' the array contains only the value or 'form" the array contains the data to be displayed in a form
* @param $params array optional parameters
* 			$params['img_'.field_id] = true (gets the img url in the 'full' mode array for the field_id), false (no img for the field_id )
* @return Array Structure different in 'full' mode or in 'minimal' mode
* 			mode 'full' : $data[field_id] field_id exemple: 'priority'
* 										['value'] : value of the field in the database
* 										['show_value'] : value to display
* 										['label'] : label to display
* 										['display'] : type of display (only 'textinput' and 'textarea' for the moment)
* 										['img'] : url of an icon to display (optional)
* 			mode 'minimal' : $data[field_id] = value of the field in the database
* 			mode 'form' : $data[field_id] field_id exemple: 'priority'
* 										['value'] : value of the field in the database
* 										['show_value'] : value to display
* 										['label'] : label to display
* 										['display'] : type of display (only 'textinput' and 'textarea' for the moment)
* 										['img'] : url of an icon to display (optional)
* 										['readonly'] : true or false
*										['field_type'] : keyword 'date' = date input field with calendar
 * 							 							 		 'textfield' = text input field
 * 							 									 'select' = select field
*										['select'] : array of options items (only if field_type = 'select')
*												[$i]['id'] : option value
* 													['label'] : option text
*/
function get_general_data($coll_id, $res_id, $mode, $params = array())
{
	require_once($_SESSION['pathtocoreclass']."class_security.php");
	$sec =new security();
	$view = $sec->retrieve_view_from_coll_id($coll_id);
	if(empty($view))
	{
		$view = $table;
	}
	$db = new dbquery();
	$db->connect();
	$db2 = new dbquery();
	$db2->connect();
	$db->query('select category_id from '.$view.' where res_id = '.$res_id);
	$res = $db->fetch_object();
	$cat_id = $res->category_id;
	$fields = '';
	$data = array();
	$arr = array();

	// First we load the category_id
	if($mode == 'full' || 'form')
	{
		if($params['img_category_id'] == true)
		{
			$data['category_id'] = array( 'value' => $res->category_id, 'show_value' => $_SESSION['mail_categories'][$res->category_id], 'label' => _CATEGORY, 'display' => 'textinput', 'img' =>  $_SESSION['config']['businessappurl'].'img/picto_change.gif');
		}
		else
		{
			$data['category_id'] = array( 'value' => $res->category_id, 'show_value' => $_SESSION['mail_categories'][$res->category_id], 'label' => _CATEGORY, 'display' => 'textinput');
		}
	}
	else
	{
		$data['category_id'] =  $res->category_id;
	}
	//Then we browse the $_ENV['categories'] array to get the other indexes
	foreach(array_keys($_ENV['categories'][$cat_id]) as $field)
	{
		// Normal cases : fields are put in a string to make a query
		if($field <> 'process_limit_date_use' && $field <> 'other_cases' && $field <> 'type_contact' && $field <> 'img_cat')
		{
			$fields .= 	$field.',';
			if(($mode == 'full' || $mode == 'form') && (!isset($params['show_'.$field]) || $params['show_'.$field] == true))
			{
				if($params['img_'.$field] == true)
				{
					$data[$field] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id][$field]['label'], 'display' => 'textinput', 'img' =>$_ENV['categories'][$cat_id][$field]['img']);
				}
				else
				{
					$data[$field] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id][$field]['label'], 'display' => 'textinput');
				}
				array_push($arr, $field);
				if($field == 'subject')
				{
					$data[$field]['display'] = 'textarea';
				}
				$data[$field]['readonly'] = true;
				if($mode == 'form' && $_ENV['categories'][$cat_id][$field]['modify'])
				{
					$data[$field]['readonly'] = false;
					$data[$field]['field_type'] = $_ENV['categories'][$cat_id][$field]['form_show'];
					if($data[$field]['field_type'] == 'select')
					{
						$data[$field]['select']= array();
						if($field == 'type_id')
						{
							require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_types.php");
							$type = new types();
							$data[$field]['select'] = $type->getArrayStructTypes($coll_id);
						}
						else if($field == 'destination')
						{
							//require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_entities.php");
							// TO DO : get the entities list
						}
						else if($field == 'nature_id')
						{
							foreach(array_keys($_SESSION['mail_natures']) as $nature)
							{
								array_push($data[$field]['select'], array('ID' => $nature, 'LABEL' => $_SESSION['mail_natures'][$nature]));
							}
						}
						else if($field == 'priority')
						{
							foreach(array_keys($_SESSION['mail_priorities']) as $prio)
							{
								array_push($data[$field]['select'], array('ID' => $prio, 'LABEL' => $_SESSION['mail_priorities'][$prio]));
							}
						}
					}
				}
			}
			else if($mode == 'minimal' && (!isset($params['show_'.$field]) || $params['show_'.$field] == true))
			{
				$data[$field] = '';
				array_push($arr, $field);
			}
		}
	}
	// Special cases :  fields are put in a string to make a query
	// Process limit date
	if(isset($_ENV['categories'][$cat_id]['other_cases']['process_limit_date'] ) && count($_ENV['categories'][$cat_id]['other_cases']['process_limit_date']) > 0 && (!isset($params['show_process_limit_date']) || $params['show_process_limit_date'] == true))
	{
		$fields .= 'process_limit_date,';
		if($mode == 'full' || 'form')
		{
			if($params['img_process_limit_date'] == true)
			{
				$data['process_limit_date'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['process_limit_date']['label'], 'display' => 'textinput', 'img' => $_ENV['categories'][$cat_id]['other_cases']['process_limit_date']['img'] );
			}
			else
			{
				$data['process_limit_date'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['process_limit_date']['label'], 'display' => 'textinput' );
			}
			$data['process_limit_date']['readonly'] = true;
			if($mode == 'form' && $_ENV['categories'][$cat_id]['process_limit_date']['modify'])
			{
				$data['process_limit_date']['field_type'] = $_ENV['categories'][$cat_id]['process_limit_date']['form_show'];
				$data['process_limit_date']['readonly'] = false;
			}
		}
		else
		{
			$data['process_limit_date'] = '';
			$data['process_limit_date_use'] = false;
		}
		array_push($arr, 'process_limit_date');
	}
	// Contact
	if(isset($_ENV['categories'][$cat_id]['other_cases']['contact'] ) && count($_ENV['categories'][$cat_id]['other_cases']['contact']) > 0 && (!isset($params['show_contact']) || $params['show_contact'] == true))
	{
		$fields .= $_ENV['categories'][$cat_id]['other_cases']['contact']['special'].',';
		if(preg_match('/,/', $_ENV['categories'][$cat_id]['other_cases']['contact']['special'] ))
		{
			$arr_tmp = preg_split('/,/', $_ENV['categories'][$cat_id]['other_cases']['contact']['special']);
		}
		else
		{
			$arr_tmp = array($_ENV['categories'][$cat_id]['other_cases']['contact']['special']);
		}
		for($i=0;$i<count($arr_tmp);$i++)
		{
			if($mode == 'full' || $mode == 'form')
			{
				$data[$arr_tmp[$i]] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['contact']['label'], 'display' => 'textinput' );
				$data[$arr_tmp[$i]]['readonly'] = true;
				if($mode == 'form' && $_ENV['categories'][$cat_id][$arr_tmp[$i]]['modify'])
				{
					$data[$arr_tmp[$i]]['field_type'] = $_ENV['categories'][$cat_id][$arr_tmp[$i]]['form_show'];
					$data[$arr_tmp[$i]]['readonly'] = false;
				}
			}
			array_push($arr, $arr_tmp[$i]);
		}

		if($mode == 'minimal')
		{
			$data['contact'] = '';
		}

	}
	// Market
	if(isset($_ENV['categories'][$cat_id]['other_cases']['market'] ) && count($_ENV['categories'][$cat_id]['other_cases']['market']) > 0  && (!isset($params['show_market']) || $params['show_market'] == true))
	{
		//echo 'market ';
		$fields .= 'folders_system_id,';
		if($mode == 'full' || $mode == 'form')
		{
			if($params['img_project'] == true)
			{
				$data['project'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['project']['label'], 'display' => 'textinput', 'img' => $_ENV['categories'][$cat_id]['other_cases']['project']['img']);
			}
			else
			{
				$data['project'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['project']['label'], 'display' => 'textinput');
			}
			if($params['img_market'] == true)
			{
				$data['market'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['market']['label'], 'display' => 'textinput', 'img' => $_ENV['categories'][$cat_id]['other_cases']['market']['img']);
			}
			else
			{
				$data['market'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['market']['label'], 'display' => 'textinput');
			}
			$data['market']['readonly'] = true;
			if($mode == 'form' && $_ENV['categories'][$cat_id]['other_cases']['market']['modify'])
			{
				$data['market']['field_type'] = $_ENV['categories'][$cat_id]['other_cases']['market']['form_show'];
				$data['market']['readonly'] = false;
			}
			$data['project']['readonly'] = true;
			if($mode == 'form' && $_ENV['categories'][$cat_id]['other_cases']['project']['modify'])
			{
				$data['project']['field_type'] = $_ENV['categories'][$cat_id]['other_cases']['project']['form_show'];
				$data['project']['readonly'] = false;
			}
		}
		else
		{
			$data['market'] = '';
			$data['project'] = '';
		}
		array_push($arr, 'market');
	}
	// Project
	if(isset($_ENV['categories'][$cat_id]['other_cases']['project'] ) && count($_ENV['categories'][$cat_id]['other_cases']['project']) > 0 && (!isset($params['show_project']) || $params['show_project'] == true) )
	{
		//echo 'project';
		if(!isset($_ENV['categories'][$cat_id]['other_cases']['market'] ) || count($_ENV['categories'][$cat_id]['other_cases']['market']) == 0)
		{

			$fields .= 'folders_system_id,';
			array_push($arr, 'project');
		}
		if($mode == 'full' || $mode == 'form')
		{
			if($params['img_project'] == true)
			{
				$data['project'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['project']['label'], 'display' => 'textinput', 'img' => $_ENV['categories'][$cat_id]['other_cases']['project']['img']);
			}
			else
			{
				$data['project'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['project']['label'], 'display' => 'textinput');
			}
			$data['project']['readonly'] = true;
			if($mode == 'form' && $_ENV['categories'][$cat_id]['other_cases']['project']['modify'])
			{
				$data['project']['field_type'] = $_ENV['categories'][$cat_id]['other_cases']['project']['form_show'];
				$data['project']['readonly'] = false;
			}
		}
		else
		{
			$data['project'] = '';
		}
	}
	// Arboxes
	if(isset($_ENV['categories'][$cat_id]['other_cases']['arbox_id'] ) && count($_ENV['categories'][$cat_id]['other_cases']['arbox_id']) > 0 && (!isset($params['show_arbox_id']) || $params['show_arbox_id'] == true))
	{
		$fields .= 'arbox_id,';
		if($mode == 'full' || $mode == 'form')
		{
			if($params['img_arbox_id'] == true)
			{
				$data['arbox_id'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['label'], 'display' => 'textinput', 'img' =>  $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['img']);
			}
			else
			{
				$data['arbox_id'] = array( 'value' => '', 'show_value' => '',  'label' => $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['label'], 'display' => 'textinput' );
			}
			$data['arbox_id']['readonly'] = true;
			if($mode == 'form' && $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['modify'])
			{
				$data['arbox_id']['field_type'] = $_ENV['categories'][$cat_id]['other_cases']['arbox_id']['form_show'];
				$data['arbox_id']['readonly'] = false;
				$data['arbox_id']['select'] = array();
				$db->query("select arbox_id, title from ".$_SESSION['tablename']['ar_boxes']." where status ='NEW' order by title");
				while($res = $db->fetch_object())
				{
					array_push($data['arbox_id']['select'], array('ID' => $res->arbox_id, 'LABEL' => $res->title.' ('.$res->arbox_id.')'));
				}
			}
		}
		else
		{
			$data['arbox_id'] = '';
		}
		array_push($arr, 'arbox_id');
	}

	if($mode == 'full')
	{
		$fields = preg_replace('/,$/', ',type_label', $fields);
	}
	else
	{
		$fields = preg_replace('/,$/', '', $fields);
	}
	// Query
	$db->query("select category_id,".$fields." from ".$view." where res_id = ".$res_id);
	//$db->show();
	//$db->show_array($arr);
	$line = $db->fetch_object();
	// We fill the array with the query result
	for($i=0; $i < count($arr);$i++)
	{
		if($mode == 'full' || $mode == 'form')
		{
			// Normal Cases
			$data[$arr[$i]]['value'] = $line->$arr[$i];
			$data[$arr[$i]]['show_value'] = $line->$arr[$i];
			if($_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'date')
			{
				$data[$arr[$i]]['show_value'] = $db->format_date_db($line->$arr[$i], false);
			}
			elseif($_ENV['categories'][$cat_id]['other_cases'][$arr[$i]]['type_field'] == 'date')
			{
				$data[$arr[$i]]['show_value'] = $db->format_date_db($line->$arr[$i], false);
			}
			elseif($_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'string')
			{
				$data[$arr[$i]]['show_value'] = $db->show_string($line->$arr[$i], true );
			}
			// special cases :
			if($arr[$i] == 'priority')
			{
				$data[$arr[$i]]['show_value'] = $_SESSION['mail_priorities'][$line->$arr[$i]];
			}
			elseif($arr[$i] == 'nature_id')
			{
				$data[$arr[$i]]['show_value'] = $_SESSION['mail_natures'][$line->$arr[$i]];
			}
			elseif($arr[$i] == 'type_id')
			{
				$data[$arr[$i]]['show_value'] = $db->show_string($line->type_label);
			}
			// Arboxe
			elseif($arr[$i] == 'arbox_id')
			{
				if(isset($line->arbox_id) && !empty($line->arbox_id))
				{
					$db2->query('select title from '.$_SESSION['tablename']['ar_boxes']." where arbox_id = ".$line->arbox_id."");

					$res = $db2->fetch_object();
					$data[$arr[$i]]['show_value'] = $db->show_string($res->title.' ('.$line->arbox_id.')');
				}
			}
			// Contact
			elseif($arr[$i] == 'dest_user_id' || $arr[$i] == 'exp_user_id')
			{
				if(!empty($line->$arr[$i]))
				{
					$db2->query('select lastname, firstname from '.$_SESSION['tablename']['users']." where user_id = '".$line->$arr[$i]."'");
					$res = $db2->fetch_object();
					$data[$arr[$i]]['show_value'] = $res->lastname.', '.$res->firstname.' ('.$line->$arr[$i].')';
					$data[$arr[$i]]['addon'] = '<a href="#" id="contact_card" title="'._CONTACT_CARD.'" onclick="window.open(\''.$_SESSION ['config']['businessappurl'].'user_info.php?id='.$line->$arr[$i].'\', \'contact_info\', \'height=450, width=600,scrollbars=yes,resizable=yes\');" ><img src="'.$_SESSION['config']['businessappurl'].'img/my_contacts_off.gif" alt="'._CONTACT_CARD.'" /></a>';
				}
				else
				{
					unset($data[$arr[$i]]);
				}
			}
			elseif($arr[$i] == 'dest_contact_id' || $arr[$i] == 'exp_contact_id')
			{
				if(!empty($line->$arr[$i]))
				{
					$db2->query('select is_corporate_person, lastname, firstname, society from '.$_SESSION['tablename']['contacts']." where enabled = 'Y' and contact_id = ".$line->$arr[$i]."");
					$res = $db2->fetch_object();
					if($res->is_corporate_person == 'Y')
					{
						$data[$arr[$i]]['show_value'] = $res->society;
					}
					else
					{
						$data[$arr[$i]]['show_value'] = $res->lastname.', '.$res->firstname;
						if(!empty($res->society))
						{
							$data[$arr[$i]]['show_value'] .= ' ('.$res->society.')';
						}
					}
					$data[$arr[$i]]['addon'] = '<a href="#" id="contact_card" title="'._CONTACT_CARD.'" onclick="window.open(\''.$_SESSION ['config']['businessappurl'].'contact_info.php?mode=view&id='.$line->$arr[$i].'\', \'contact_info\', \'height=600, width=600,scrollbars=yes,resizable=yes\');" ><img src="'.$_SESSION['config']['businessappurl'].'img/my_contacts_off.gif" alt="'._CONTACT_CARD.'" /></a>';
				}
				else
				{
					unset($data[$arr[$i]]);
				}
			}
			// Folder : market
			elseif($arr[$i] == 'market' &&  isset($line->folders_system_id) && !empty($line->folders_system_id))
			{
				$db2->query('select folder_name, subject, folders_system_id, parent_id from '.$_SESSION['tablename']['fold_folders']." where status <> 'DEL' and folders_system_id = ".$line->folders_system_id." and folder_level = 2");


				if($db2->nb_result() > 0)
				{
					$res = $db2->fetch_object();
					$data['market']['show_value'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
					$folder_id = $res->parent_id;
					if(isset($folder_id) && !empty($folder_id))
					{
						$db2->query('select folder_name, subject, folders_system_id from '.$_SESSION['tablename']['fold_folders']." where status <> 'DEL' and folders_system_id = ".$folder_id." and folder_level = 1");
					//	$db2->show();
						$res = $db2->fetch_object();
						$data['project']['show_value'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
						//$db2->show_array($data['project']);
					}
				}
			}
			// Folder : project
			elseif($arr[$i] == 'project' && $line->folders_system_id <> ''  && isset($line->folders_system_id) && empty($data['project']['show_value']))
			{
				$db2->query('select folder_name, subject, folders_system_id, parent_id from '.$_SESSION['tablename']['fold_folders']." where status <> 'DEL' and folders_system_id = ".$line->folders_system_id." and folder_level = 1");

				if($db2->nb_result() > 0)
				{
					$res = $db2->fetch_object();
					$data['project']['show_value'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
				}
			}
		}
		else // 'mimimal' mode
		{
			// Normal cases
			$data[$arr[$i]] = $line->$arr[$i];
			if($_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'date')
			{
				$data[$arr[$i]] = $db->format_date_db($line->$arr[$i], false);
			}
			elseif($_ENV['categories'][$cat_id]['other_cases'][$arr[$i]]['type_field'] == 'date')
			{
				$data[$arr[$i]] = $db->format_date_db($line->$arr[$i], false);
			}
			elseif($_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'string')
			{
				$data[$arr[$i]] = $db->show_string($line->$arr[$i], true);
			}
			// special cases :
			// Contact
			if($arr[$i] == 'dest_user_id' || $arr[$i] == 'exp_user_id')
			{
				$data['type_contact'] = 'internal';
				if(!empty($line->$arr[$i]))
				{
					$db2->query('select lastname, firstname from '.$_SESSION['tablename']['users']." where user_id = '".$line->$arr[$i]."'");
					$res = $db2->fetch_object();
					$data['contact']= $res->lastname.', '.$res->firstname.' ('.$line->$arr[$i].')';
				}
				unset($data[$arr[$i]]);

			}
			elseif($arr[$i] == 'dest_contact_id' || $arr[$i] == 'exp_contact_id')
			{
				$data['type_contact'] = 'external';
				if(!empty($line->$arr[$i]))
				{
					$db2->query('select is_corporate_person, lastname, firstname, society from '.$_SESSION['tablename']['contacts']." where enabled = 'Y' and contact_id = ".$line->$arr[$i]."");
					$res = $db2->fetch_object();
					if($res->is_corporate_person == 'Y')
					{
						$data['contact'] = $res->society.' ('.$line->$arr[$i].')';
					}
					else
					{
						if(!empty($res->society))
						{
							$data['contact'] = $res->society.', '.$res->lastname.' '.$res->firstname.' ('.$line->$arr[$i].')';
						}
						else
						{
							$data['contact']= $res->lastname.', '.$res->firstname.' ('.$line->$arr[$i].')';
						}
					}
				}
				unset($data[$arr[$i]]);
			}
			// Folder : market
			elseif($arr[$i] == 'market' &&  isset($line->folders_system_id) && !empty($line->folders_system_id))
			{
				$db2->query('select folder_name, subject, folders_system_id, parent_id, folder_level from '.$_SESSION['tablename']['fold_folders']." where status <> 'DEL' and folders_system_id = ".$line->folders_system_id." ");
				if($db2->nb_result() > 0)
				{
					$res = $db2->fetch_object();
					if($res->folder_level == 2)
					{
						$data['market'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
						$folder_id = $res->parent_id;
						if(isset($folder_id) && !empty($folder_id))
						{
							$db2->query('select folder_name, subject, folders_system_id from '.$_SESSION['tablename']['fold_folders']." where status <> 'DEL' and folders_system_id = ".$folder_id." and folder_level = 1");
							$res = $db2->fetch_object();
							$data['project'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
						}
					}
					else
					{
						$data['project'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
					}
				}

			}
			// Folder : project
			elseif($arr[$i] == 'project' && $line->folders_system_id <> ''  && isset($line->folders_system_id) && empty($data['project']))
			{
				$db2->query('select folder_name, subject, folders_system_id, parent_id from '.$_SESSION['tablename']['fold_folders']." where status <> 'DEL' and folders_system_id = ".$line->folders_system_id." and folder_level = 1");
				//$db2->show();
				$res = $db2->fetch_object();
				$data['project'] = $res->folder_name.', '.$res->subject.' ('.$res->folders_system_id.')';
			}
		}
	}

	if($mode == 'minimal' && isset($data['process_limit_date']) && !empty($data['process_limit_date']))
	{
		$data['process_limit_date_use'] = true;
	}
	return $data;
}

/**
 * Returns the icon for the given category or the default icon
 *
 * @param $cat_id string Category identifiert
 * @return string Icon Url
 **/
function get_img_cat($cat_id)
{
	$default =  $_SESSION['config']['businessappurl'].'img/picto_delete.gif';
	if(empty($cat_id))
	{
		
		return $default;
	}
	else
	{
		if(!empty($_ENV['categories'][$cat_id]['img_cat']))
		{
			return $_ENV['categories'][$cat_id]['img_cat'];
		}
		else
		{
			return $default;
		}
	}
}
?>
