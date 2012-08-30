<?php

/*
*   Copyright 2008-2012 Maarch
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
* @brief   Contains data structure used to get the proper index for a given category, to checks data and to loads in db (indexing, process, validation, details, ...) and the function to access it
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

///////////////////// Pattern to check dates
$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

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
$_ENV['categories'] = array ();
///////////////////////////// RM_ARCHIVE ////////////////////////////////////////////////
$_ENV['categories']['rm_archive'] = array ();
$_ENV['categories']['rm_archive']['img_cat'] = $_SESSION['config']['businessappurl'] 
    . 'static.php?filename=cat_doc_incoming.gif';
$_ENV['categories']['rm_archive']['other_cases'] = array ();

$_ENV['categories']['rm_archive']['originating_entity_id'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _ENTITE_PRODUCTRICE,
    'table' => 'coll_ext',
    'img' => $_SESSION['config']['businessappurl'] 
        . 'static.php?module=entities&filename=manage_entities_b_small.gif',
    'modify' => false,
    'form_show' => 'select'
);
$_ENV['categories']['rm_archive']['originating_user_id'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SERVICE_PRODUCTEUR,
    'table' => 'coll_ext',
    'img' => $_SESSION['config']['businessappurl'] 
        . 'static.php?module=entities&filename=manage_entities_b_small.gif',
    'modify' => false,
    'form_show' => 'select'
);
$_ENV['categories']['rm_archive']['destination'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _SERVICE_VERSANT,
    'table' => 'res',
    'img' => $_SESSION['config']['businessappurl'] 
        . 'static.php?module=entities&filename=manage_entities_b_small.gif',
    'modify' => false,
    'form_show' => 'select'
);
$_ENV['categories']['rm_archive']['type_id'] = array (
    'type_form' => 'integer',
    'type_field' => 'integer',
    'mandatory' => true,
    'label' => _TYPE_DARCHIVE,
    'table' => 'res',
    'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=mini_type.gif',
    'modify' => false,
    'form_show' => 'select'
);
$_ENV['categories']['rm_archive']['appraisal_code'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _REGLE_GESTION,
    'table' => 'coll_ext',
    'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=contrat_mini.gif',
    'modify' => false,
    'form_show' => 'select'
);
$_ENV['categories']['rm_archive']['appraisal_duration'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _DUA,
    'table' => 'coll_ext',
    'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=date_arr.gif',
    'modify' => false,
    'form_show' => 'textfield'
);

$_ENV['categories']['rm_archive']['doc_date'] = array (
    'type_form' => 'date',
    'type_field' => 'date',
    'mandatory' => false,
    'label' => _DATE_DE_LA_PIECE,
    'table' => 'res',
    'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=date_arr.gif',
    'modify' => false,
    'form_show' => 'date'
);
$_ENV['categories']['rm_archive']['other_cases']['project'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => false,
    'label' => _PROJECT,
    'table' => 'none',
    'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=doc_project.gif',
    'modify' => true,
    'form_show' => 'autocomplete'
);
$_ENV['categories']['rm_archive']['item_name'] = array (
    'type_form' => 'string',
    'type_field' => 'string',
    'mandatory' => true,
    'label' => _INTITULE_ANALYSE,
    'table' => 'coll_ext',
    'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=object.gif',
    'modify' => true,
    'form_show' => 'textarea'
);
/************************* END *************************************************************/

/**
* Gets the index of a given res_id in an array
*
* @param $coll_id string  Collection identifier
* @param $res_id string  Resource identifier
* @param $mode string  'full' : fills the array with maximum data (id, value, show_value,etc...), 'minimal' the array contains only the value or 'form" the array contains the data to be displayed in a form
* @param $params array optional parameters
*           $params['img_'.field_id] = true (gets the img url in the 'full' mode array for the field_id), false (no img for the field_id )
* @return Array Structure different in 'full' mode, 'form' and 'minimal' mode
*           mode 'full' : $data[field_id] field_id exemple: 'priority'
*                                       ['value'] : value of the field in the database
*                                       ['show_value'] : value to display
*                                       ['label'] : label to display
*                                       ['display'] : type of display (only 'textinput' and 'textarea' for the moment)
*                                       ['img'] : url of an icon to display (optional)
*           mode 'minimal' : $data[field_id] = value of the field in the database
*           mode 'form' : $data[field_id] field_id exemple: 'priority'
*                                       ['value'] : value of the field in the database
*                                       ['show_value'] : value to display
*                                       ['label'] : label to display
*                                       ['display'] : type of display (only 'textinput' and 'textarea' for the moment)
*                                       ['img'] : url of an icon to display (optional)
*                                       ['readonly'] : true or false
*                                       ['field_type'] : keyword 'date' = date input field with calendar
 *                                                               'textfield' = text input field
 *                                                               'select' = select field
*                                       ['select'] : array of options items (only if field_type = 'select')
*                                               [$i]['id'] : option value
*                                                   ['label'] : option text
*/
function get_general_data($coll_id, $res_id, $mode, $params = array ()) {
    require_once ('core/class/class_security.php');
    $sec = new security();
    $view = $sec->retrieve_view_from_coll_id($coll_id);
    if (empty ($view)) {
        $view = $sec->retrieve_table_from_coll($coll_id);
    }
    $db = new dbquery();
    $db->connect();
    $db2 = new dbquery();
    $db2->connect();
    $cat_id = 'rm_archive';
    $fields = '';
    $data = array ();
    $arr = array ();

    // First we load the category_id
    if ($mode == 'full' || 'form') {
        if ($params['img_category_id'] == true) {
            $data['category_id'] = array (
                'value' => $cat_id,
                'show_value' => $_SESSION['mail_categories'][$cat_id],
                'label' => _CATEGORY,
                'display' => 'textinput',
                'img' => $_SESSION['config']['businessappurl'] . 'static.php?filename=picto_change.gif'
            );
        } else {
            $data['category_id'] = array (
                'value' => $cat_id,
                'show_value' => $_SESSION['mail_categories'][$cat_id],
                'label' => _CATEGORY,
                'display' => 'textinput'
            );
        }
    } else {
        $data['category_id'] = $cat_id;
    }
    if (!isset ($cat_id) || empty ($cat_id)) {
        $cat_id = 'empty';
    }
    //Then we browse the $_ENV['categories'] array to get the other indexes
    foreach (array_keys($_ENV['categories'][$cat_id]) as $field) {
        // Normal cases : fields are put in a string to make a query
        if ($field <> 'process_limit_date_use' && $field <> 'other_cases' && $field <> 'type_contact' && $field <> 'img_cat') {
            $fields .= $field . ',';
            if (($mode == 'full' || $mode == 'form') && (!isset ($params['show_' . $field]) || $params['show_' . $field] == true)) {
                if ($params['img_' . $field] == true) {
                    $data[$field] = array (
                        'value' => '',
                        'show_value' => '',
                        'label' => $_ENV['categories'][$cat_id][$field]['label'],
                        'display' => 'textinput',
                        'img' => $_ENV['categories'][$cat_id][$field]['img']
                    );
                } else {
                    $data[$field] = array (
                        'value' => '',
                        'show_value' => '',
                        'label' => $_ENV['categories'][$cat_id][$field]['label'],
                        'display' => 'textinput'
                    );
                }
                array_push($arr, $field);
                if ($field == 'item_name') {
                    $data[$field]['display'] = 'textarea';
                }
                $data[$field]['readonly'] = true;
                if ($mode == 'form' && $_ENV['categories'][$cat_id][$field]['modify']) {
                    $data[$field]['readonly'] = false;
                    $data[$field]['field_type'] = $_ENV['categories'][$cat_id][$field]['form_show'];
                    if ($data[$field]['field_type'] == 'select') {
                        $data[$field]['select'] = array ();
                        if ($field == 'type_id') {
                            require_once ("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] 
                                . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_types.php");
                            $type = new types();
                            if ($_SESSION['features']['show_types_tree'] == 'true') {
                                $data[$field]['select'] = $type->getArrayStructTypes($coll_id);
                            } else {
                                $data[$field]['select'] = $type->getArrayTypes($coll_id);
                            }
                        } else
                            if ($field == 'destination') {
                                // TO DO : get the entities list
                            } else
                                if ($field == 'nature_id') {
                                    foreach (array_keys($_SESSION['mail_natures']) as $nature) {
                                        array_push($data[$field]['select'], array (
                                            'ID' => $nature,
                                            'LABEL' => $_SESSION['mail_natures'][$nature]
                                        ));
                                    }
                                } else
                                    if ($field == 'priority') {
                                        foreach (array_keys($_SESSION['mail_priorities']) as $prio) {
                                            array_push($data[$field]['select'], array (
                                                'ID' => $prio,
                                                'LABEL' => $_SESSION['mail_priorities'][$prio]
                                            ));
                                        }
                                    }
                    }
                }
            } else
                if ($mode == 'minimal' && (!isset ($params['show_' . $field]) || $params['show_' . $field] == true)) {
                    $data[$field] = '';
                    array_push($arr, $field);
                }
        }
    }
    // Special cases :  fields are put in a string to make a query
    
    // Project
    if (isset ($_ENV['categories'][$cat_id]['other_cases']['project']) && count($_ENV['categories'][$cat_id]['other_cases']['project']) > 0 && (!isset ($params['show_project']) || $params['show_project'] == true)) {
        //echo 'project';
        if (!isset ($_ENV['categories'][$cat_id]['other_cases']['market']) || count($_ENV['categories'][$cat_id]['other_cases']['market']) == 0) {

            $fields .= 'folders_system_id,';
            //array_push($arr, 'project');
        }
        array_push($arr, 'project');
        if ($mode == 'full' || $mode == 'form') {
            if ($params['img_project'] == true) {
                $data['project'] = array(
                    'value' => '',
                    'show_value' => '',
                    'label' => $_ENV['categories'][$cat_id]['other_cases']['project']['label'],
                    'display' => 'textinput',
                    'img' => $_ENV['categories'][$cat_id]['other_cases']['project']['img']
                );
            } else {
                $data['project'] = array(
                    'value' => '',
                    'show_value' => '',
                    'label' => $_ENV['categories'][$cat_id]['other_cases']['project']['label'],
                    'display' => 'textinput'
                );
            }
            $data['project']['readonly'] = true;
            if ($mode == 'form' && $_ENV['categories'][$cat_id]['other_cases']['project']['modify']) {
                $data['project']['field_type'] = $_ENV['categories'][$cat_id]['other_cases']['project']['form_show'];
                $data['project']['readonly'] = false;
            }
        } else {
            $data['project'] = '';
        }
    }

    if ($mode == 'full' || $mode == 'form') {
        $fields = preg_replace('/,$/', ',type_label', $fields);
    } else {
        $fields = preg_replace('/,$/', '', $fields);
    }
    
    //Query
    $db->query("select " . $fields . " from " . $view . " where res_id = " . $res_id);
    //$db->show();
    $line = $db->fetch_object();
    // We fill the array with the query result
    for ($i = 0; $i < count($arr); $i++) {
        if ($mode == 'full' || $mode == 'form') {
            // Normal Cases
            if (isset($line-> $arr[$i])) {
                $data[$arr[$i]]['value'] = $line-> $arr[$i];
            }
            if ($arr[$i] <> 'project') {
                $data[$arr[$i]]['show_value'] = $db->show_string($data[$arr[$i]]['value']);
            }
            if (isset($_ENV['categories'][$cat_id][$arr[$i]]['type_field'])
                && $_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'date'
            ) {
                $data[$arr[$i]]['show_value'] = $db->format_date_db($line-> $arr[$i], false);
            } else if (isset($_ENV['categories'][$cat_id]['other_cases'][$arr[$i]]['type_field'])
                && $_ENV['categories'][$cat_id]['other_cases'][$arr[$i]]['type_field'] == 'date'
            ) {
                $data[$arr[$i]]['show_value'] = $db->format_date_db($line-> $arr[$i], false);
            } else if (isset($_ENV['categories'][$cat_id][$arr[$i]]['type_field'])
                && $_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'string'
            ) {
                $data[$arr[$i]]['show_value'] = $db->show_string($line-> $arr[$i], true);
            }
            if ($arr[$i] == 'destination') {
                $db2->query('select entity_label from ' . $_SESSION['tablename']['ent_entities'] 
                    . " where entity_id = '" . $line-> $arr[$i] . "'");
                if ($db2->nb_result() == 1) {
                    $res2 = $db2->fetch_object();
                    $data[$arr[$i]]['show_value'] = $db->show_string($res2->entity_label, true);
                }
            } elseif ($arr[$i] == 'type_id') {
                $data[$arr[$i]]['show_value'] = $db->show_string($line->type_label);
            }
            // Folder : project
            elseif ($arr[$i] == 'project' && $line->folders_system_id <> '' 
                && isset ($line->folders_system_id) && empty ($data['market']['show_value'])) {
                $db2->query('select folder_name, subject, folders_system_id, parent_id from ' 
                    . $_SESSION['tablename']['fold_folders'] . " where status <> 'DEL' and folders_system_id = " 
                    . $line->folders_system_id . " and folder_level = 1");

                if ($db2->nb_result() > 0) {
                    $res = $db2->fetch_object();
                    $data['project']['show_value'] = $res->folder_name . ', ' . $res->subject . ' (' . $res->folders_system_id . ')';
                }
            }
        } else {
            // Normal cases
            $data[$arr[$i]] = $line-> $arr[$i];
            if ($_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'date') {
                $data[$arr[$i]] = $db->format_date_db($line-> $arr[$i], false);
            } elseif ($_ENV['categories'][$cat_id]['other_cases'][$arr[$i]]['type_field'] == 'date') {
                $data[$arr[$i]] = $db->format_date_db($line-> $arr[$i], false);
            } elseif ($_ENV['categories'][$cat_id][$arr[$i]]['type_field'] == 'string') {
                $data[$arr[$i]] = $db->show_string($line-> $arr[$i], true);
            }
            // special cases :
            // Folder : project
            elseif ($arr[$i] == 'project' && $line->folders_system_id <> '' && isset ($line->folders_system_id) && empty ($data['market'])) {
                $db2->query('select folder_name, subject, folders_system_id, parent_id from ' 
                    . $_SESSION['tablename']['fold_folders'] . " where status <> 'DEL' and folders_system_id = " 
                    . $line->folders_system_id . " and folder_level = 1");
                //$db2->show();
                $res = $db2->fetch_object();
                $data['project'] = $res->folder_name . ', ' . $res->subject . ' (' . $res->folders_system_id . ')';
            }
        }
    }
    return $data;
}

/**
 * Returns the icon for the given category or the default icon
 *
 * @param $cat_id string Category identifiert
 * @return string Icon Url
 **/
function get_img_cat($cat_id) {
    $default = $_SESSION['config']['businessappurl'] . 'static.php?filename=picto_delete.gif';
    if (empty ($cat_id)) {

        return $default;
    } else {
        if (!empty ($_ENV['categories'][$cat_id]['img_cat'])) {
            return $_ENV['categories'][$cat_id]['img_cat'];
        } else {
            return $default;
        }
    }
}
