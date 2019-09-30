<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Contains all the function to manage the doctypes
 *
 * @author dev@maarch.org
*
* @package  Maarch
* @version 2.0
* @since 10/2005
* @license GPL
*
*/

require_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    ."class_security.php";
require_once 'core/core_tables.php';
require_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_history.php";

abstract class types_Abstract extends database
{

    /**
    * Return in an array all enabled doctypes for a given collection
    *
    * @param string $collId Collection identifier
    */
    public function getArrayTypes($collId)
    {
        $types = array();
        if (empty($collId)) {
            return $types;
        }

        $db = new Database();
        $stmt = $db->query(
            "SELECT type_id, description FROM " . DOCTYPES_TABLE
            . " WHERE coll_id = ? and enabled = 'Y' "
            . "order by description",
            array($collId)
        );
        while ($res = $stmt->fetchObject()) {
            array_push(
                $types,
                array(
                    'ID' => $res->type_id,
                    'LABEL' => $this->show_string($res->description),
                )
            );
        }
        return $types;
    }

    /**
    * Returns in an array all enabled doctypes for a given collection with the
    * structure
    *
    * @param string $collId Collection identifier
    */
    public function getArrayStructTypes($collId)
    {
        $db = new Database();
        $level1 = array();
        $stmt = $db->query(
            "SELECT d.type_id, d.description, d.doctypes_first_level_id, "
            . "d.doctypes_second_level_id, dsl.doctypes_second_level_label, "
            . "dfl.doctypes_first_level_label, dfl.css_style as style_level1, "
            . " dsl.css_style as style_level2 FROM " . DOCTYPES_TABLE . " d, "
            . $_SESSION['tablename']['doctypes_second_level'] . " dsl, "
            . $_SESSION['tablename']['doctypes_first_level']
            . " dfl WHERE d.enabled = 'Y' "
            . "and d.doctypes_second_level_id = dsl.doctypes_second_level_id "
            . "and d.doctypes_first_level_id = dfl.doctypes_first_level_id "
            . "and dsl.enabled = 'Y' and dfl.enabled = 'Y' "
            . "order by dfl.doctypes_first_level_label,"
            . "dsl.doctypes_second_level_label, d.description "
        );
        $lastLevel1 = '';
        $nbLevel1 = 0;
        $lastLevel2 = '';
        $nbLevel2 = 0;
        while ($res = $stmt->fetchObject()) {
            //var_dump($res);
            if ($lastLevel1 <> $res->doctypes_first_level_id) {
                array_push(
                    $level1,
                    array(
                        'id' => $res->doctypes_first_level_id,
                        'label' => $this->show_string($res->doctypes_first_level_label),
                        'style' => $res->style_level1,
                        'level2' => array(
                            array(
                                'id' => $res->doctypes_second_level_id,
                                'label' => $this->show_string($res->doctypes_second_level_label),
                                'style' => $res->style_level2,
                                'types' => array(
                                    array(
                                        'id' => $res->type_id,
                                        'label' => $this->show_string($res->description)
                                    )
                                )
                            )
                        )
                    )
                );
                $lastLevel1 = $res->doctypes_first_level_id;
                $nbLevel1 ++;
                $lastLevel2 = $res->doctypes_second_level_id;
                $nbLevel2 = 1;
            } elseif ($lastLevel2 <> $res->doctypes_second_level_id) {
                array_push(
                    $level1[$nbLevel1 - 1]['level2'],
                    array(
                        'id' => $res->doctypes_second_level_id,
                        'label' => $this->show_string($res->doctypes_second_level_label),
                        'style' => $res->style_level2,
                        'types' => array(
                            array(
                                'id' => $res->type_id,
                                'label' => $this->show_string($res->description)
                            )
                        )
                    )
                );
                $lastLevel2 = $res->doctypes_second_level_id;
                $nbLevel2 ++;
            } else {
                array_push(
                    $level1[$nbLevel1 - 1]['level2'][$nbLevel2 - 1]['types'],
                    array(
                        'id' => $res->type_id,
                        'label' => $this->show_string($res->description)
                    )
                );
            }
            //$this->show_array($level1);
        }
        return $level1;
    }

    /**
    * Returns in an array all indexes possible for a given collection
    *
    * @param string $collId Collection identifier
    * @return array $indexes[$i]
    *                   ['column'] : database field of the index
    *                   ['label'] : Index label
    *                   ['type'] : Index type ('date', 'string', 'integer' or 'float')
    *                   ['img'] : url to the image index
    */
    public function get_all_indexes($collId)
    {
        $sec = new security();
        $db = new Database();
        $indColl = $sec->get_ind_collection($collId);
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
            . $_SESSION['collections'][$indColl]['index_file']
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "xml"
                  . DIRECTORY_SEPARATOR
                  . $_SESSION['collections'][$indColl]['index_file'];
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
                  . $_SESSION['collections'][$indColl]['index_file'];
        }

        $xmlfile = simplexml_load_file($path);

        $pathLang = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['lang'] . '.php';
        $indexes = array();
        foreach ($xmlfile->INDEX as $item) {
            $label = (string) $item->label;
            if (!empty($label) && defined($label) && constant($label) <> null) {
                $label = constant($label);
            }
            $img = (string) $item->img;
            if (isset($item->default_value) && ! empty($item->default_value)) {
                $default = (string) $item->default_value;
                if (!empty($default) && defined($default)
                    && constant($default) <> null
                ) {
                    $default = constant($default);
                }
            } else {
                $default = false;
            }
            if (isset($item->values_list)) {
                $values = array();
                $list = $item->values_list ;
                foreach ($list->value as $val) {
                    $labelVal = (string) $val->label;
                    if (!empty($labelVal) && defined($labelVal)
                        && constant($labelVal) <> null
                    ) {
                        $labelVal = constant($labelVal);
                    }
                   
                    array_push(
                        $values,
                        array(
                            'id' => (string) $val->id,
                            'label' => $labelVal,
                        )
                    );
                }
                $tmpArr = array(
                    'column' => (string) $item->column,
                    'label' => $label,
                    'type' => (string) $item->type,
                    'img' => $img,
                    'type_field' => 'select',
                    'values' => $values,
                    'default_value' => $default
                );
            } elseif (isset($item->table)) {
                $values = array();
                $tableXml = $item->table;
                //$this->show_array($tableXml);
                $tableName = (string) $tableXml->table_name;
                $foreignKey = (string) $tableXml->foreign_key;
                $foreignLabel = (string) $tableXml->foreign_label;
                $whereClause = (string) $tableXml->where_clause;
                $order = (string) $tableXml->order;
                $query = "select " . $foreignKey . ", " . $foreignLabel
                       . " from " . $tableName;
                if (isset($whereClause) && ! empty($whereClause)) {
                    $query .= " where " . $whereClause;
                }
                if (isset($order) && ! empty($order)) {
                    $query .= ' '.$order;
                }
                
                $stmt = $db->query($query);
                while ($res = $stmt->fetch()) {
                    array_push(
                         $values,
                         array(
                             'id' => (string) $res[0],
                             'label' => (string) $res[1],
                         )
                     );
                }
                $tmpArr = array(
                    'column' => (string) $item->column,
                    'label' => $label,
                    'type' => (string) $item->type,
                    'img' => $img,
                    'type_field' => 'select',
                    'values' => $values,
                    'default_value' => $default,
                );
            } else {
                $tmpArr = array(
                    'column' => (string) $item->column,
                    'label' => $label,
                    'type' => (string) $item->type,
                    'img' => $img,
                    'type_field' => 'input',
                    'default_value' => $default,
                );
            }
            //$this->show_array($tmpArr);
            array_push($indexes, $tmpArr);
        }
        return $indexes;
    }

    /**
    * Returns in an array all indexes for a doctype
    *
    * @param string $typeId Document type identifier
    * @param string $collId Collection identifier
    * @param string $mode Mode 'full' or 'minimal', 'full' by default
    * @return array array of the indexes, depends on the chosen mode :
    *       1) mode = 'full' : $indexes[field_name] :  the key is the field name in the database
    *                                       ['label'] : Index label
    *                                       ['type'] : Index type ('date', 'string', 'integer' or 'float')
    *                                       ['img'] : url to the image index
    *       2) mode = 'minimal' : $indexes[$i] = field name in the database
    */
    public function get_indexes($typeId, $collId, $mode='full')
    {
        $fields = array();
        $db = new Database();
        if (!empty($typeId)) {
            $stmt = $db->query(
                "SELECT field_name FROM " . DOCTYPES_INDEXES_TABLE
                . " WHERE coll_id = ? and type_id = ?",
                array($collId, $typeId)
            );
        } else {
            return array();
        }

        while ($res = $stmt->fetchObject()) {
            array_push($fields, $res->field_name);
        }
        if ($mode == 'minimal') {
            return $fields;
        }

        $indexes = array();
        $sec = new security();
        $indColl = $sec->get_ind_collection($collId);
        if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
            . $_SESSION['collections'][$indColl]['index_file']
        )
        ) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
                  . "xml" . DIRECTORY_SEPARATOR
                  . $_SESSION['collections'][$indColl]['index_file'];
        } else {
            $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
                  . $_SESSION['collections'][$indColl]['index_file'];
        }

        $xmlfile = simplexml_load_file($path);
        $pathLang = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
                  . $_SESSION['config']['lang'] . '.php';
        foreach ($xmlfile->INDEX as $item) {
            $label = (string) $item->label;
            if (!empty($label) && defined($label)
                && constant($label) <> null
            ) {
                $label = constant($label);
            }
           
            $col = (string) $item->column;
            $img = (string) $item->img;
            if (isset($item->default_value) && ! empty($item->default_value)) {
                $default = (string) $item->default_value;
                if (!empty($default) && defined($default)
                    && constant($default) <> null
                ) {
                    $default = constant($default);
                }
            } else {
                $default = false;
            }
            if (in_array($col, $fields)) {
                if (isset($item->values_list)) {
                    $values = array();
                    $list = $item->values_list ;
                    foreach ($list->value as $val) {
                        $labelVal = (string) $val->label;
                        if (!empty($labelVal) && defined($labelVal)
                            && constant($labelVal) <> null
                        ) {
                            $labelVal = constant($labelVal);
                        }
                       
                        array_push(
                            $values,
                            array(
                                'id' => (string) $val->id,
                                'label' => $labelVal,
                            )
                        );
                    }
                    $indexes[$col] = array(
                        'label'         => $label,
                        'type'          => (string) $item->type,
                        'img'           => $img,
                        'type_field'    => 'select',
                        'values'        => $values,
                        'default_value' => $default,
                        'origin'        => 'document',
                        'only_detail'   => $item->only_detail
                    );
                } elseif (isset($item->table)) {
                    $values = array();
                    $tableXml = $item->table;
                    //$this->show_array($tableXml);
                    $tableName = (string) $tableXml->table_name;
                    $foreignKey = (string) $tableXml->foreign_key;
                    $foreignLabel = (string) $tableXml->foreign_label;
                    $whereClause = (string) $tableXml->where_clause;
                    $order = (string) $tableXml->order;
                    $query = "select " . $foreignKey . ", " . $foreignLabel
                           . " from " . $tableName;
                    if (isset($whereClause) && ! empty($whereClause)) {
                        $query .= " where " . $whereClause;
                    }
                    if (isset($order) && ! empty($order)) {
                        $query .= ' '.$order;
                    }
                    
                    $stmt = $db->query($query);
                    while ($res = $stmt->fetchObject()) {
                        array_push(
                             $values,
                             array(
                                 'id' => (string) $res->{$foreignKey},
                                 'label' => $res->{$foreignLabel},
                             )
                         );
                    }
                    $indexes[$col] = array(
                        'label'         => $label,
                        'type'          => (string) $item->type,
                        'img'           => $img,
                        'type_field'    => 'select',
                        'values'        => $values,
                        'default_value' => $default,
                        'origin'        => 'document',
                        'only_detail'   => $item->only_detail
                    );
                } else {
                    $indexes[$col] = array(
                        'label'         => $label,
                        'type'          => (string) $item->type,
                        'img'           => $img,
                        'type_field'    => 'input',
                        'default_value' => $default,
                        'origin'        => 'document',
                        'only_detail'   => $item->only_detail
                    );
                }
            }
        }

        foreach (array_keys($indexes) as $key) {
            if (is_array($indexes[$key])) {
                $indexes[$key]['label']         = functions::xssafe($indexes[$key]['label']);
                $indexes[$key]['type']          = functions::xssafe($indexes[$key]['type']);
                $indexes[$key]['img']           = functions::xssafe($indexes[$key]['img']);
                $indexes[$key]['type_field']    = functions::xssafe($indexes[$key]['type_field']);
                $indexes[$key]['default_value'] = functions::xssafe($indexes[$key]['default_value']);
                $indexes[$key]['origin']        = functions::xssafe($indexes[$key]['origin']);
                $indexes[$key]['only_detail']   = functions::xssafe($indexes[$key]['only_detail']);
                if (is_array($indexes[$key]['values'])) {
                    for ($cpt=0;$cpt<count($indexes[$key]['values']);$cpt++) {
                        $indexes[$key]['values'][$cpt]['id'] = functions::xssafe($indexes[$key]['values'][$cpt]['id']);
                        $indexes[$key]['values'][$cpt]['label'] = functions::xssafe($indexes[$key]['values'][$cpt]['label']);
                    }
                    $indexes[$key]['type_field'] = functions::xssafe($indexes[$key]['type_field']);
                }
            }
        }
        return $indexes;
    }

    /**
    * Returns in an array all manadatory indexes possible for a given type
    *
    * @param string $typeId Document type identifier
    * @param string $collId Collection identifier
    * @return array Array of the manadatory indexes, $indexes[$i] = field name
    * in the db
    */
    public function get_mandatory_indexes($typeId, $collId)
    {
        $fields = array();
        $db = new Database();
        $stmt = $db->query(
            "SELECT field_name FROM " . DOCTYPES_INDEXES_TABLE
            . " WHERE coll_id = ? and type_id = ? and mandatory = 'Y'",
            array($collId, $typeId)
        );

        while ($res = $stmt->fetchObject()) {
            array_push($fields, $res->field_name);
        }
        return $fields;
    }

    /**
    * Checks validity of indexes
    *
    * @param string $typeId Document type identifier
    * @param string $collId Collection identifier
    * @param array $values Values to check
    * @return bool true if checks is ok, false if an error occurs
    */
    public function check_indexes($typeId, $collId, $values)
    {
        $sec = new security();
        $indColl = $sec->get_ind_collection($collId);
        $indexes = $this->get_indexes($typeId, $collId);
        $mandatoryIndexes = $this->get_mandatory_indexes($typeId, $collId);

        // Checks the manadatory indexes
        for ($i = 0; $i < count($mandatoryIndexes); $i ++) {
            if ((empty($values[$mandatoryIndexes[$i]])
                || $values[$mandatoryIndexes[$i]] == '')
            ) {
                $_SESSION['error'] .= $indexes[$mandatoryIndexes[$i]]['label']
                                   . _IS_EMPTY;
            }
        }

        // Checks type indexes
        $datePattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
        foreach (array_keys($values) as $key) {
            //var_dump($values);
            //exit;
            if ($indexes[$key]['type'] == 'date' && ! empty($values[$key])) {
                if (preg_match($datePattern, $values[$key]) == 0) {
                    $_SESSION['error'] .= $indexes[$key]['label']
                                       . _WRONG_FORMAT;
                    return false;
                }
            } elseif ($indexes[$key]['type'] == 'string'
                && trim($values[$key]) <> ''
            ) {
                $fieldValue = $this->wash(
                    $values[$key],
                    "no",
                    $indexes[$key]['label']
                );
            } elseif ($indexes[$key]['type'] == 'float'
                && preg_match("/^[0-9.,]+$/", $values[$key]) == 1
            ) {
                $values[$key] = str_replace(",", ".", $values[$key]);
                $fieldValue = $this->wash(
                    $values[$key],
                        "float",
                        $indexes[$key]['label']
                );
            } elseif ($indexes[$key]['type'] == 'integer'
                && preg_match("/^[0-9]+$/", $values[$key]) == 1
            ) {
                $fieldValue = $this->wash(
                    $values[$key],
                    "num",
                    $indexes[$key]['label']
                );
            } elseif (!empty($values[$key])) {
                $_SESSION['error'] .= $indexes[$key]['label']
                                       . _WRONG_FORMAT;
                return false;
            }

            if (isset($indexes[$key]['values'])
                && count($indexes[$key]['values']) > 0
            ) {
                $found = false;
                for ($i = 0; $i < count($indexes[$key]['values']); $i++) {
                    if ($values[$key] == $indexes[$key]['values'][$i]['id']) {
                        $found = true;
                        break;
                    }
                }
                if (! $found && $values[$key] <> "") {
                    $_SESSION['error'] .= $indexes[$key]['label'] . " : "
                                       . _ITEM_NOT_IN_LIST . "";
                    return false;
                }
            }
        }
        if (! empty($_SESSION['error'])) {
            return false;
        } else {
            return true;
        }
    }


    /**
    * Returns a string to use in an sql update query
    *
    * @param string $typeId Document type identifier
    * @param string $collId Collection identifier
    * @param array $values Values to update
    * @return string Part of the update sql query
    */
    public function get_sql_update($typeId, $collId, $values)
    {
        $indexes = $this->get_indexes($typeId, $collId);

        $req = '';
        foreach (array_keys($values)as $key) {
            if ($indexes[$key]['type'] == 'date' && ! empty($values[$key])) {
                $req .= ", " . $key . " = '"
                     . $this->format_date_db($values[$key]) . "'";
            } elseif ($indexes[$key]['type'] == 'string'
                && ! empty($values[$key])
            ) {
                $req .= ", " . $key . " = '"
                     . $this->protect_string_db($values[$key]) . "'";
            } elseif ($indexes[$key]['type'] == 'float'
                && ! empty($values[$key])
            ) {
                $req .= ", " . $key . " = " . $values[$key] . "";
            } elseif ($indexes[$key]['type'] == 'integer'
                && ! empty($values[$key])
            ) {
                $req .= ", " . $key . " = " . $values[$key] . "";
            }
        }
        return $req;
    }

    /**
    * Returns an array used to insert data in the database
    *
    * @param string $typeId Document type identifier
    * @param string $collId Collection identifier
    * @param array $values Values to update
    * @param array $data Return array
    * @return array
    */
    public function fill_data_array($typeId, $collId, $values, $data = array())
    {
        $indexes = $this->get_indexes($typeId, $collId);

        foreach (array_keys($values) as $key) {
            if ($indexes[$key]['type'] == 'date' && ! empty($values[$key])) {
                array_push(
                    $data,
                    array(
                        'column' => $key,
                        'value' => $this->format_date_db($values[$key]),
                        'type' => "date",
                    )
                );
            } elseif ($indexes[$key]['type'] == 'string'
                && trim($values[$key]) <> ''
            ) {
                array_push(
                    $data,
                    array(
                        'column' => $key,
                        'value' => $values[$key],
                        'type' => "string",
                    )
                );
            } elseif ($indexes[$key]['type'] == 'float'
                && preg_match("/^[0-9.,]+$/", $values[$key]) == 1
            ) {
                $values[$key] = str_replace(",", ".", $values[$key]);
                array_push(
                    $data,
                    array(
                        'column' => $key,
                        'value' => $values[$key],
                        'type' => "float",
                    )
                );
            } elseif ($indexes[$key]['type'] == 'integer'
                && preg_match("/^[0-9]+$/", $values[$key]) == 1
            ) {
                array_push(
                    $data,
                    array(
                        'column' => $key,
                        'value' => $values[$key],
                        'type' => "integer",
                    )
                );
            }
        }
        return $data;
    }

    /**
    * Inits in the database the indexes for a given res id to null
    *
    * @param string $collId Collection identifier
    * @param string $resId Resource identifier
    */
    public function inits_opt_indexes($collId, $resId)
    {
        $sec = new security();
        $table = $sec->retrieve_table_from_coll($collId);
        $db = new Database();

        $indexes = $this->get_all_indexes($collId);
        if (count($indexes) > 0) {
            $query = "UPDATE " . $table . " set ";
            for ($i = 0; $i < count($indexes); $i ++) {
                $query .= $indexes[$i]['column'] . " = NULL, ";
            }
            $query = preg_replace('/, $/', ' where res_id = ?', $query);
            $db->query($query, array($resId));
        }
    }


    /**
    * Makes the search checks for a given index, and builds the where query and
    *  json
    *
    * @param array $indexes Array of the possible indexes (used to check)
    * @param string $fieldName Field name, index identifier
    * @param string $val Value to check
    * @return array ['json_txt'] : json used in the search
    *               ['where'] : where query
    */
    public function search_checks($indexes, $fieldName, $val)
    {
        $func = new functions();
        $whereRequest = '';
        $jsonTxt = '';
        if (! empty($val)) {
            $datePattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
            for ($j = 0; $j < count($indexes); $j ++) {
                $column = $indexes[$j]['column'] ;
                if (preg_match('/^doc_/', $fieldName)) {
                    $column = 'doc_' . $column;
                }
                // type == 'string or others'
                if ($indexes[$j]['column'] == $fieldName
                    || 'doc_' . $indexes[$j]['column'] == $fieldName
                ) {
                    if ($indexes[$j]['type'] == 'float' || $indexes[$j]['type'] == 'integer') {
                        $jsonTxt .= " '" . $fieldName . "' : ['"
                             . addslashes(trim($val)) . "'],";
                        $whereRequest .= " (" . $column . ") = ('"
                                      . $val . "') and ";
                    } else {
                        $jsonTxt .= " '" . $fieldName . "' : ['"
                             . addslashes(trim($val)) . "'],";
                        $whereRequest .= " lower(" . $column . ") like lower('%"
                                      . $this->protect_string_db($val) . "%') and ";
                    }
                    break;
                } elseif (($indexes[$j]['column'] . '_from' == $fieldName
                    || $indexes[$j]['column'] . '_to' == $fieldName
                    || 'doc_' . $indexes[$j]['column'] . '_from' == $fieldName
                    || 'doc_' . $indexes[$j]['column'] . '_to' == $fieldName)
                        && ! empty($val)
                ) { // type == 'date'
                    if (preg_match($datePattern, $val) == false) {
                        $_SESSION['error'] .= _WRONG_DATE_FORMAT . ' : ' . $val;
                    } else {
                        if ($indexes[$j]['column'] . '_from' == $fieldName
                            || 'doc_' . $indexes[$j]['column'] . '_from' == $fieldName
                        ) {
                            $whereRequest .= " (" . $column . " >= '"
                                          . $this->format_date_db($val) . "') and ";
                        } else {
                            $whereRequest .= " (" . $column . " <= '"
                                          . $this->format_date_db($val) . "') and ";
                        }
                        $jsonTxt .= " '" . $fieldName . "' : ['" . trim($val)
                                 . "'],";
                    }
                    break;
                } elseif ($indexes[$j]['column'] . '_min' == $fieldName
                    || 'doc_' . $indexes[$j]['column'] . '_min' == $fieldName
                ) {
                    if ($indexes[$j]['type'] == 'integer'
                        || $indexes[$j]['type'] == 'float'
                    ) {
                        if ($indexes[$j]['type'] == 'integer') {
                            $valCheck = $func->wash(
                                $val,
                                "num",
                                $indexes[$j]['label'],
                                "no"
                            );
                        } else {
                            $valCheck = $func->wash(
                                $val,
                                "float",
                                $indexes[$j]['label'],
                                "no"
                            );
                        }
                        if (empty($_SESSION['error'])) {
                            $whereRequest .= " (" . $column . " >= " . $valCheck
                                          . ") and ";
                            $jsonTxt .= " '" . $fieldName . "' : ['" . $valCheck
                                     . "'],";
                        }
                    }
                    break;
                } elseif ($indexes[$j]['column'] . '_max' == $fieldName
                    || 'doc_' . $indexes[$j]['column'] . '_max' == $fieldName
                ) {
                    if ($indexes[$j]['type'] == 'integer'
                        || $indexes[$j]['type'] == 'float'
                    ) {
                        if ($indexes[$j]['type'] == 'integer') {
                            $valCheck = $func->wash(
                                $val,
                                "num",
                                $indexes[$j]['label'],
                                "no"
                            );
                        } else {
                            $valCheck = $func->wash(
                                $val,
                                "float",
                                $indexes[$j]['label'],
                                "no"
                            );
                        }
                        if (empty($_SESSION['error'])) {
                            $whereRequest .= " (" . $column . " <= " . $valCheck
                                          . ") and ";
                            $jsonTxt .= " '" . $fieldName . "' : ['" . $valCheck
                                     . "'],";
                        }
                    }
                    break;
                }
            }
        }
        return array(
            'json_txt' => $jsonTxt,
            'where' => $whereRequest,
        );
    }
}
