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
                "SELECT field_name FROM doctypes_indexes WHERE coll_id = ? and type_id = ?",
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
            "SELECT field_name FROM doctypes_indexes WHERE coll_id = ? and type_id = ? and mandatory = 'Y'",
            array($collId, $typeId)
        );

        while ($res = $stmt->fetchObject()) {
            array_push($fields, $res->field_name);
        }
        return $fields;
    }
}
