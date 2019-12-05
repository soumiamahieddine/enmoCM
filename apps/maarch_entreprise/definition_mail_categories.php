<?php
/*
*    Copyright 2008-2016 Maarch
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
 *
 * @author Claire Figueras <dev@maarch.org>
 * @date $date$
 *
 * @version $Revision$
 * @ingroup apps
 */

///////////////////// Pattern to check dates
$_ENV['date_pattern'] = '/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/';

/*
 * Categories are described in a global variable : $_ENV['categories']
 * $_ENV['categories'][category_id]
 *      ['img_cat'] : url of the icon category
 *      [id_field] // id_field must be an identifier of a field in the form and in the database
 *          ['type_form'] = Form type field: 'integer', 'date', 'string', 'yes_no', 'special'
 *          ['type_field'] = Database type field :'integer', 'date', 'string', 'yes_no', 'special'
 *          ['mandatory'] = true or false
 *          ['label'] = label of the field
 *          ['img'] = Image url (optional)
 *          ['table'] = keyword : 'res' = field in the res_x like table
 *                                'coll_ext' = field in collection ext table (ex : mlb_ext_coll)
 *                                'none' = field in no table, only in form for special functionnality
 *                                'special' = field in other table, handled in the code
 *          ['modify'] = true or false (optional) : can we modify this field (used in details.php)
 *          ['form_show'] = keyword (used with modify = true)
 *                           'select' = displayed in a select item
 *                           'textfield' = displayed in text input
 *                           'date' = displayed in a date input (with calendar activated)
 *      ['other_cases'] // particular cases handled in code
 *            [identifier] // keyword handled in the code
 *                  ['type_form'] = Form type field:'integer', 'date', 'string', 'yes_no', 'special'
 *                  ['type_field'] = Databse type field :'integer', 'date', 'string', 'yes_no', 'special'
 *                  ['mandatory'] = true or false
 *                  ['label'] = label of the field
 *                  ['table'] = keyword : 'res' = field in the res_x like table
 *                                        'coll_ext' = field in collection ext table (ex : mlb_ext_coll)
 *                                        'none' = field in no table, only in form for special functionnality
 *                                        'special' = field in another table, handled in the code
 *                  ['modify'] = true or false (optional) : can we modify this field (used in details.php)
 *                  ['form_show'] = keyword (used with modify = true)
 *                              'select' = displayed in a select item
 *                              'textfield' = keyword : 'date' = date input field
 *                              'date' = displayed in a date input (with calendar activated)
 *
 */

/**************************** Categories descriptions******************************/
$_ENV['categories'] = array();

///////////////////////////// INCOMING ////////////////////////////////////////////////
$_ENV['categories']['incoming'] = array();
$_ENV['categories']['incoming']['img_cat'] = '<i class="fa fa-arrow-right fa-2x"></i>';
$_ENV['categories']['incoming']['other_cases'] = array();
$_ENV['categories']['incoming']['priority'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _PRIORITY,
    'table' => 'res',
    'img' => 'exclamation',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['incoming']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE_MAIL,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['incoming']['doc_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _MAIL_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['incoming']['admission_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _RECEIVING_DATE,
    'table' => 'coll_ext',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['incoming']['departure_date'] = array (
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => false,
    'label' => _EXP_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date'
);
// $_ENV['categories']['incoming']['description'] = array (
//     'type_form' => 'string',
//     'type_field' => 'string',
//     'mandatory' => false,
//     'label' => _OTHERS_INFORMATIONS,
//     'table' => 'res',
//     'img' => 'info-circle',
//     'modify' => true,
//     'form_show' => 'textarea'
// );
$_ENV['categories']['incoming']['subject'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SUBJECT,
    'table' => 'res',
    'img' => 'info',
    'modify' => true,
    'form_show' => 'textarea',
);
$_ENV['categories']['incoming']['process_limit_date_use'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => _PROCESS_LIMIT_DATE_USE,
    'table' => 'none',
    'values' => array(
        'Y',
        'N',
    ),
    'modify' => false,
);
$_ENV['categories']['incoming']['other_cases']['process_limit_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'label' => _PROCESS_LIMIT_DATE,
    'table' => 'coll_ext',
    'img' => 'bell',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['incoming']['type_contact'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => _SHIPPER_TYPE,
    'table' => 'none',
    'values' => array(
        'internal',
        'external',
        'multi_external',
    ),
    'modify' => false,
);
$_ENV['categories']['incoming']['other_cases']['contact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SHIPPER,
    'table' => 'coll_ext',
    'special' => 'exp_user_id,exp_contact_id,is_multicontacts',
    'modify' => true,
    'img' => 'book',
    'form_show' => 'textfield',
);
$_ENV['categories']['incoming']['other_cases']['resourceContact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _DEST,
    'table' => 'res',
    'special' => '',
    'modify' => true,
    'img' => 'book',
    'form_show' => 'textfield',
);
$_ENV['categories']['incoming']['department_number_id'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _DEPARTMENT_NUMBER,
    'table' => 'res',
    'img' => 'road',
    'modify' => false,
    'form_show' => 'textfield'
);
$_ENV['categories']['incoming']['barcode'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _BARCODE,
    'table' => 'res',
    'img' => 'barcode',
    'modify' => false,
    'form_show' => 'textfield',
);
$_ENV['categories']['incoming']['confidentiality'] = array(
    'type_form' => 'radio',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _CONFIDENTIALITY,
    'table' => 'res',
    'values' => array(
        'Y',
        'N',
    ),
    'img' => 'exclamation-triangle',
    'modify' => true,
);

///////////////////////////// OUTGOING ////////////////////////////////////////////////
$_ENV['categories']['outgoing'] = array();
$_ENV['categories']['outgoing']['img_cat'] = '<i class="fa fa-arrow-left fa-2x"></i>';
$_ENV['categories']['outgoing']['other_cases'] = array();
$_ENV['categories']['outgoing']['priority'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _PRIORITY,
    'table' => 'res',
    'img' => 'exclamation',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['outgoing']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE_MAIL,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['outgoing']['doc_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _MAIL_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['outgoing']['departure_date'] = array (
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => false,
    'label' => _EXP_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date'
);
// $_ENV['categories']['outgoing']['description'] = array (
//     'type_form' => 'string',
//     'type_field' => 'string',
//     'mandatory' => false,
//     'label' => _OTHERS_INFORMATIONS,
//     'table' => 'res',
//     'img' => 'info-circle',
//     'modify' => true,
//     'form_show' => 'textarea'
// );
$_ENV['categories']['outgoing']['department_number_id'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _DEPARTMENT_NUMBER,
    'table' => 'res',
    'img' => 'road',
    'modify' => false,
    'form_show' => 'textfield'
);
$_ENV['categories']['outgoing']['barcode'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _BARCODE,
    'table' => 'res',
    'img' => 'barcode',
    'modify' => false,
    'form_show' => 'textfield',
);
$_ENV['categories']['outgoing']['subject'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SUBJECT,
    'table' => 'res',
    'img' => 'info',
    'modify' => true,
    'form_show' => 'textarea',
);
$_ENV['categories']['outgoing']['process_limit_date_use'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => _PROCESS_LIMIT_DATE_USE,
    'table' => 'none',
    'values' => array(
        'Y',
        'N',
    ),
    'modify' => false,
);
$_ENV['categories']['outgoing']['other_cases']['process_limit_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'label' => _PROCESS_LIMIT_DATE,
    'table' => 'coll_ext',
    'img' => 'bell',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['outgoing']['type_contact'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => _DEST_TYPE,
    'table' => 'none',
    'values' => array(
        'internal',
        'external',
        'multi_external',
    ),
    'modify' => false,
);
$_ENV['categories']['outgoing']['other_cases']['contact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _DEST,
    'table' => 'coll_ext',
    'special' => 'dest_user_id,dest_contact_id,is_multicontacts',
    'img' => 'book',
    'modify' => true,
    'img' => 'book',
    'form_show' => 'textfield',
);
$_ENV['categories']['outgoing']['other_cases']['resourceContact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SENDER,
    'table' => 'res',
    'special' => '',
    'modify' => true,
    'img' => 'book',
    'form_show' => 'textfield',
);
$_ENV['categories']['outgoing']['confidentiality'] = array(
    'type_form' => 'radio',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _CONFIDENTIALITY,
    'table' => 'res',
    'values' => array(
        'Y',
        'N',
    ),
    'img' => 'exclamation-triangle',
    'modify' => true,
);

///////////////////////////// INTERNAL ////////////////////////////////////////////////
$_ENV['categories']['internal'] = array();
$_ENV['categories']['internal']['img_cat'] = '<i class="fa fa-arrow-down fa-2x"></i>';
$_ENV['categories']['internal']['other_cases'] = array();
$_ENV['categories']['internal']['priority'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _PRIORITY,
    'table' => 'res',
    'img' => 'exclamation',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['internal']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE_MAIL,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['internal']['doc_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _MAIL_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['internal']['subject'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SUBJECT,
    'table' => 'res',
    'img' => 'info',
    'modify' => true,
    'form_show' => 'textarea',
);
$_ENV['categories']['internal']['process_limit_date_use'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => _PROCESS_LIMIT_DATE_USE,
    'table' => 'none',
    'values' => array(
        'Y',
        'N',
    ),
    'modify' => false,
);
$_ENV['categories']['internal']['other_cases']['process_limit_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'label' => _PROCESS_LIMIT_DATE,
    'table' => 'coll_ext',
    'img' => 'bell',
    'modify' => false,
);
$_ENV['categories']['internal']['type_contact'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => _SHIPPER_TYPE,
    'table' => 'none',
    'values' => array(
        'internal',
        'external',
        'multi_external'
    ),
    'modify' => false,
);
$_ENV['categories']['internal']['other_cases']['contact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SHIPPER,
    'table' => 'coll_ext',
    'special' => 'exp_user_id,exp_contact_id,is_multicontacts',
    'modify' => true,
    'img' => 'book',
    'form_show' => 'textfield',
);
$_ENV['categories']['internal']['other_cases']['resourceContact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _DEST,
    'table' => 'res',
    'special' => '',
    'modify' => true,
    'img' => 'book',
    'form_show' => 'textfield',
);

///////////////////////////// RECONCILIATION ////////////////////////////////////////////////

$_ENV['categories']['attachment'] = array();
$_ENV['categories']['attachment']['img_cat'] = '<i class="fa fa-paperclip fa-2x"></i>';
$_ENV['categories']['attachment']['other_cases'] = array();
$_ENV['categories']['attachment']['other_cases']['chrono_number'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => false,
    'label' => _CHRONO_NUMBER,
    'table' => 'none',
    'img' => 'compass',
    'modify' => false,
    'form_show' => 'textfield',
);
$_ENV['categories']['attachment']['other_cases']['contact'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _DEST,
    'table' => 'coll_ext',
    'special' => 'dest_user_id,dest_contact_id',
    'img' => 'book',
    'modify' => false
);
$_ENV['categories']['attachment']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE_MAIL,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['attachment']['destination'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _DEPARTMENT_EXP,
    'table' => 'res',
    'img' => 'sitemap',
    'modify' => false,
    'form_show' => 'textarea',
);
$_ENV['categories']['attachment']['other_cases']['contact'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _DEST,
    'table' => 'coll_ext',
    'special' => 'dest_user_id,dest_contact_id,is_multicontacts',
    'img' => 'book',
    'modify' => false
);
///////////////////////////// GED DOC ////////////////////////////////////////////////
$_ENV['categories']['ged_doc'] = array();
$_ENV['categories']['ged_doc']['img_cat'] = '<i class="fa fa-arrow-right fa-2x"></i>';
$_ENV['categories']['ged_doc']['other_cases'] = array();
$_ENV['categories']['ged_doc']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['ged_doc']['doc_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _DOC_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);

$_ENV['categories']['ged_doc']['subject'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SUBJECT,
    'table' => 'res',
    'img' => 'info',
    'modify' => true,
    'form_show' => 'textarea',
);
$_ENV['categories']['ged_doc']['type_contact'] = array(
    'type_form' => 'radio',
    'mandatory' => true,
    'label' => 'type de contact',
    'table' => 'none',
    'values' => array(
        'internal',
        'external',
    ),
    'modify' => false,
);
$_ENV['categories']['ged_doc']['other_cases']['contact'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _AUTHOR_DOC,
    'table' => 'coll_ext',
    'special' => 'exp_user_id,exp_contact_id',
    'modify' => false,
    'img' => 'book',
    'form_show' => 'textfield',
);

$_ENV['categories']['ged_doc']['confidentiality'] = array(
    'type_form' => 'radio',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _CONFIDENTIALITY,
    'table' => 'res',
    'values' => array(
        'Y',
        'N',
    ),
    'img' => 'exclamation-triangle',
    'modify' => true,
);

/////////////////////////////FOLDER DOCUMENT////////////////////////////////////////////////
$_ENV['categories']['folder_document'] = array();
$_ENV['categories']['folder_document']['img_cat'] = '<i class="fa fa-folder fa-2x"></i>';
$_ENV['categories']['folder_document']['other_cases'] = array();
$_ENV['categories']['folder_document']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['folder_document']['doc_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _DOC_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['folder_document']['subject'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SUBJECT,
    'table' => 'res',
    'img' => 'info',
    'modify' => true,
    'form_show' => 'textarea',
);
$_ENV['categories']['folder_document']['author'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _AUTHOR,
    'table' => 'res',
    'img' => 'edit',
    'modify' => true,
    'form_show' => 'textfield',
);

/////////////////////////////EMPTY////////////////////////////////////////////////
$_ENV['categories']['empty'] = array();
$_ENV['categories']['empty']['img_cat'] = '<i class="fa fa-circle fa-2x"></i>';
$_ENV['categories']['empty']['other_cases'] = array();
$_ENV['categories']['empty']['type_id'] = array(
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _DOCTYPE,
    'table' => 'res',
    'img' => 'file',
    'modify' => true,
    'form_show' => 'select',
);
$_ENV['categories']['empty']['doc_date'] = array(
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => true,
    'label' => _DOC_DATE,
    'table' => 'res',
    'img' => 'calendar',
    'modify' => true,
    'form_show' => 'date',
);
$_ENV['categories']['empty']['title'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _INVOICE_NUMBER,
    'table' => 'res',
    'img' => 'info',
    'modify' => true,
    'form_show' => 'textfield',
);
$_ENV['categories']['empty']['identifier'] = array(
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _INVOICE_NUMBER,
    'table' => 'res',
    'img' => 'compass',
    'modify' => true,
    'form_show' => 'textfield',
);

/////////////////////////////MODULES SPECIFIC////////////////////////////////////////////////
$core = new core_tools();
if ($core->is_module_loaded('entities')) {
    //Entities module (incoming)
    $_ENV['categories']['incoming']['destination'] = array(
        'type_form' => 'string',
        'type_field' => 'string',
        'mandatory' => true,
        'label' => _DEPARTMENT_DEST,
        'table' => 'res',
        'img' => 'sitemap',
        'modify' => false,
        'form_show' => 'textarea',
    );
    $_ENV['categories']['incoming']['other_cases']['diff_list'] = array(
        'type' => 'special',
        'mandatory' => true,
        'label' => _DIFF_LIST,
        'table' => 'special',
    );

    // Entities module (outgoing)
    $_ENV['categories']['outgoing']['destination'] = array(
        'type_form' => 'string',
        'type_field' => 'string',
        'mandatory' => true,
        'label' => _DEPARTMENT_EXP,
        'table' => 'res',
        'img' => 'sitemap',
        'modify' => false,
        'form_show' => 'textarea',
    );
    $_ENV['categories']['outgoing']['other_cases']['diff_list'] = array(
        'type' => 'special',
        'mandatory' => true,
        'label' => _DIFF_LIST,
        'table' => 'special',
    );

    // Entities module (internal)
    $_ENV['categories']['internal']['destination'] = array(
        'type_form' => 'string',
        'type_field' => 'string',
        'mandatory' => true,
        'label' => _DEPARTMENT_DEST,
        'table' => 'res',
        'img' => 'sitemap',
        'modify' => false,
        'form_show' => 'textarea',
    );
    $_ENV['categories']['internal']['other_cases']['diff_list'] = array(
        'type' => 'special',
        'mandatory' => true,
        'label' => _DIFF_LIST,
        'table' => 'special',
    );

    //Entities module (ged doc)
    $_ENV['categories']['ged_doc']['destination'] = array(
        'type_form' => 'string',
        'type_field' => 'string',
        'mandatory' => true,
        'label' => _DEPARTMENT_OWNER,
        'table' => 'res',
        'img' => 'sitemap',
        'modify' => false,
        'form_show' => 'textarea',
    );

    $_ENV['categories']['ged_doc']['destination'] = array(
        'type_form' => 'string',
        'type_field' => 'string',
        'mandatory' => true,
        'label' => _DEPARTMENT_OWNER,
        'table' => 'res',
        'img' => 'sitemap',
        'modify' => false,
        'form_show' => 'textarea',
    );
}

/************************* END *************************************************************/


/**
 * Returns the icon for the given category or the default icon.
 *
 * @param $cat_id string Category identifiert
 *
 * @return string Icon Url
 **/
function get_img_cat($cat_id)
{
    $default = '<i class="fa fa-times fa-2x"></i>';
    if (empty($cat_id)) {
        return $default;
    } else {
        if (!empty($_ENV['categories'][$cat_id]['img_cat'])) {
            return $_ENV['categories'][$cat_id]['img_cat'];
        } else {
            return $default;
        }
    }
}
