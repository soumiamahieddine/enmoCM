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
* @brief   Contains all the functions to manage diffusion list
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup entities
*/
require_once 'modules/entities/entities_tables.php';
require_once 'core/core_tables.php';
/**
* @brief   Contains all the functions to manage diffusion list
*
* <ul>
* <li>Loads diffusion list from an model linked to an entity</li>
* <li>Updates the listinstance or listmodel table in the database</li>
* <li>Gets a diffusion list for a given resource identifier</li>
*</ul>
*
* @ingroup entities
*/
class diffusion_list extends dbquery
{
    /**
    * Gets the diffusion list model for a given entity
    *
    * @param string $entityId Entity identifier
    * @param array $collId Collection identifier ('letterbox_coll' by default)
    * @return array $listmodel['dest] : Data of the dest_user
    *                                ['user_id'] : identifier of the dest_user
    *                                ['lastname'] : Lastname of the dest_user
    *                                ['firstname'] : firstname of the dest_user
    *                                ['entity_id'] : entity identifier of the dest_user
    *                                ['entity_label'] : entity label of the dest_user
    *                         ['copy'] : Data of the copies
    *                                ['users'][$i] : Users in copy data
    *                                       ['user_id'] : identifier of the user in copy
    *                                       ['lastname'] : Lastname of the user in copy
    *                                       ['firstname'] : firstname of the user in copy
    *                                       ['entity_id'] : entity identifier of the user in copy
    *                                       ['entity_label'] : entity label of the user in copy
    *                                ['entities'][$i] : Entities in copy data
    *                                       ['entity_id'] : entity identifier of the entity in copy
    *                                       ['entity_label'] : entity label of the entity in copy
    */
    public function get_listmodel_from_entity($entityId, $collId = 'letterbox_coll')
    {
        $listmodel = array();
        $listmodel['dest'] = array();
        $listmodel['copy'] = array();
        $listmodel['copy']['users'] = array();
        $listmodel['copy']['entities'] = array();
        if (empty($entityId)) {
            return $listmodel;
        }
        $entityId = $this->protect_string_db($entityId);
        $this->connect();
        $this->query(
            "select l.item_id, u.firstname, u.lastname, e.entity_id, "
            . "e.entity_label  from " . ENT_LISTMODELS . " l, " . USERS_TABLE
            . " u, " . ENT_ENTITIES . " e, " . ENT_USERS_ENTITIES
            . " ue where l.coll_id = '" . $this->protect_string_db(trim($collId))
            . "' and l.listmodel_type = 'DOC' and l.item_mode = 'dest' "
            . "and l.item_type = 'user_id' and l.object_type = 'entity_id' "
            . "and l.sequence = 0 and l.object_id = '"
            . $this->protect_string_db(trim($entityId)) . "' "
            . "and l.item_id = u.user_id and u.user_id = ue.user_id "
            . "and e.entity_id = ue.entity_id and ue.primary_entity = 'Y'"
        );

        $res = $this->fetch_object();

        if ($this->nb_result() > 0 && isset($res)) {
            $listmodel['dest']['user_id'] = $this->show_str($res->item_id);
            $listmodel['dest']['lastname'] = $this->show_str($res->lastname);
            $listmodel['dest']['firstname'] = $this->show_str($res->firstname);
            $listmodel['dest']['entity_id'] = $this->show_str($res->entity_id);
            $listmodel['dest']['entity_label'] = $this->show_str($res->entity_label);
        }
        $this->query(
            "select  l.item_id, u.firstname, u.lastname, e.entity_id, "
            . "e.entity_label from " . ENT_LISTMODELS . " l, " . USERS_TABLE
            . " u, " . ENT_ENTITIES . " e, " . ENT_USERS_ENTITIES
            . " ue where l.coll_id = '" . $this->protect_string_db(trim($collId))
            . "' and l.listmodel_type = 'DOC' and l.item_mode = 'cc' "
            . "and l.item_type = 'user_id' and l.object_type = 'entity_id' "
            . "and l.object_id = '" . $this->protect_string_db(trim($entityId))
            . "' and l.item_id = u.user_id and l.item_id = ue.user_id "
            . "and e.entity_id = ue.entity_id and ue.primary_entity='Y' "
            . "order by u.lastname "
        );

        while ($res = $this->fetch_object()) {
            array_push(
                $listmodel['copy']['users'],
                array(
                    'user_id' => $this->show_string($res->item_id),
                    'lastname' => $this->show_string($res->lastname),
                    'firstname' => $this->show_string($res->firstname),
                    'entity_id' => $this->show_string($res->entity_id),
                    'entity_label' => $this->show_string($res->entity_label)
                )
            );
        }

        $this->query(
            "select l.item_id,  e.entity_label from " . ENT_LISTMODELS . " l, "
            . ENT_ENTITIES . " e where l.coll_id = '"
            . $this->protect_string_db(trim($collId)) . "' "
            . "and l.listmodel_type = 'DOC' and l.item_mode = 'cc' "
            . "and l.item_type = 'entity_id' and l.object_type = 'entity_id' "
            . "and l.object_id = '" . $this->protect_string_db(trim($entityId))
            . "' and l.item_id = e.entity_id order by e.entity_label "
        );

        while ($res = $this->fetch_object()) {
            array_push(
                $listmodel['copy']['entities'],
                array(
                    'entity_id' => $this->show_string($res->item_id),
                    'entity_label' => $this->show_string($res->entity_label)
                )
            );
        }
        return $listmodel;
    }

    /**
    * Loads a diffusion list into database (listinstance or listmodel table)
    *
    * @param array $diffList['dest] : Data of the dest_user
    *                                ['user_id'] : identifier of the dest_user
    *                                ['lastname'] : Lastname of the dest_user
    *                                ['firstname'] : firstname of the dest_user
    *                                ['entity_id'] : entity identifier of the dest_user
    *                                ['entity_label'] : entity label of the dest_user
    *                         ['copy'] : Data of the copies
    *                                ['users'][$i] : Users in copy data
    *                                       ['user_id'] : identifier of the user in copy
    *                                       ['lastname'] : Lastname of the user in copy
    *                                       ['firstname'] : firstname of the user in copy
    *                                       ['entity_id'] : entity identifier of the user in copy
    *                                       ['entity_label'] : entity label of the user in copy
    *                                ['entities'][$i] : Entities in copy data
    *                                       ['entity_id'] : entity identifier of the entity in copy
    *                                       ['entity_label'] : entity label of the entity in copy
    * @param array $params['mode'] : 'listmodel' or 'listinstance' (mandatory)
    *                     ['table'] : table to update (mandatory)
    *                     ['object_id'] : Object identifier linked to the diffusion list, entity identifier
    *                               (mandatory if mode =  'listmodel')
    *                     ['coll_id'] : Collection identifier (mandatory if mode = 'listinstance')
    *                     ['res_id'] : Resource identifier (mandatory if mode = 'listinstance')
    *                     ['user_id'] : User identifier of the person who add an item in the list
    *                     ['concat_list'] : True or false (can be set only in 'listinstance' mode )
    * @param string $listType List type, 'DOC' by default
    * @param string $objectType Object type, 'entity_id' by default
    **/
    function load_list_db($diffList, $params, $listType = 'DOC', $objectType = 'entity_id')
    {
        //print_r($diffList);exit;
        $this->connect();
        
        require_once 'core/class/class_history.php';
        $hist = new history();
        
        //print_r($_SESSION['m_admin']['entity']['listmodel']);
        //echo "<br>";
        //print_r($params);
        if ($params['mode'] == 'listmodel' && isset($params['object_id'])
            && ! empty($params['object_id'])
        ) {
            $this->query(
                "delete from " . $params['table'] . " where coll_id = '"
                . $this->protect_string_db(trim($params['coll_id'])) . "' "
                . "and object_type = '"
                . $this->protect_string_db(trim($objectType)) . "' "
                . "and object_id = '"
                . $this->protect_string_db(trim($params['object_id'])) . "' "
                . "and listmodel_type = '"
                . $this->protect_string_db(trim($listType)) . "'"
            );
            //$this->show();
            if (isset($diffList['dest']['user_id'])
                && !empty($diffList['dest']['user_id'])
            ) {
                if ($diffList['dest']['viewed'] <> "") {
                    $this->query(
                        "insert into " . $params['table']
                        . " (coll_id, object_id, object_type, sequence, "
                        . "item_id, item_type, item_mode, listmodel_type, "
                        . "viewed) values ('"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "', '"
                        . $this->protect_string_db(trim($params['object_id']))
                        . "' , '" . $this->protect_string_db(trim($objectType))
                        . "', 0, '"
                        . $this->protect_string_db(
                            trim($diffList['dest']['user_id'])
                        ) . "', 'user_id' , 'dest', '"
                        . $this->protect_string_db(trim($listType)) . "', "
                        . $diffList['dest']['viewed'] . ")"
                    );
                } else {
                    $this->query(
                        "insert into " . $params['table'] . " (coll_id, "
                        . "object_id, object_type, sequence, item_id, "
                        . "item_type, item_mode, listmodel_type ) values ('"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "', '"
                        . $this->protect_string_db(trim($params['object_id']))
                        . "' , '" . $this->protect_string_db(trim($objectType))
                        . "', 0, '"
                        . $this->protect_string_db(trim($diffList['dest']['user_id']))
                        . "', 'user_id' , 'dest', '"
                        . $this->protect_string_db(trim($listType)) . "')"
                    );
                }
                //$this->show();
                for ($i = 0; $i < count($diffList['copy']['users']); $i ++) {
                    if ($diffList['copy']['users'][$i]['viewed'] <> "") {
                        $this->query(
                            "insert into " . $params['table'] . " (coll_id, "
                            . "object_id, object_type, sequence, item_id, "
                            . "item_type, item_mode, listmodel_type, viewed) "
                            . "values ('"
                            . $this->protect_string_db(trim($params['coll_id']))
                            . "', '"
                            . $this->protect_string_db(
                                trim($params['object_id'])
                            ) . "' , '"
                            . $this->protect_string_db(trim($objectType))
                            . "', " . $i . ", '"
                            . $this->protect_string_db(
                                trim($diffList['copy']['users'][$i]['user_id'])
                            ) . "', 'user_id' , 'cc', '"
                            . $this->protect_string_db(trim($listType)) . "', "
                            . $diffList['copy']['users'][$i]['viewed'] . ")"
                        );
                    } else {
                        $this->query(
                            "insert into " . $params['table'] . " (coll_id, "
                            . "object_id, object_type, sequence, item_id, "
                            . "item_type, item_mode, listmodel_type ) values ('"
                            . $this->protect_string_db(trim($params['coll_id']))
                            . "', '"
                            . $this->protect_string_db(trim($params['object_id']))
                            . "' , '"
                            . $this->protect_string_db(trim($objectType))
                            . "', " . $i . ", '"
                            . $this->protect_string_db(
                                trim($diffList['copy']['users'][$i]['user_id'])
                            ) . "', 'user_id' , 'cc', '"
                            . $this->protect_string_db(trim($listType)) . "')"
                        );
                    }
                    //$this->show();
                }
                for ($i = 0; $i < count($diffList['copy']['entities']); $i ++) {
                    $this->query(
                        "insert into " . $params['table'] . " (coll_id, "
                        . "object_id, object_type, sequence, item_id, "
                        . "item_type, item_mode, listmodel_type ) values ('"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "', '"
                        . $this->protect_string_db(trim($params['object_id']))
                        . "' , '" . $this->protect_string_db(trim($objectType))
                        . "', " . $i . ", '"
                        . $this->protect_string_db(
                            trim($diffList['copy']['entities'][$i]['entity_id'])
                        ) . "', 'entity_id' , 'cc', '"
                        . $this->protect_string_db(trim($listType)) . "')"
                    );
                    //$this->show();
                }
            }
        } 
        else if ($params['mode'] == 'listinstance') {
            $creatorUser = '';
            $creatorEntity = '';
            if (! isset($params['concat_list'])) {
                $concat = false;
            } else {
                $concat = $params['concat_list'];
            }
            if (! isset($params['only_cc'])) {
                $onlyCc = false;
            } else {
                $onlyCc = $params['only_cc'];
            }
            if (isset($params['user_id']) && ! empty($params['user_id'])) {
                require_once 'modules' . DIRECTORY_SEPARATOR . 'entities'
                    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
                    . 'class_manage_entities.php';
                $ent = new entity();
                $creatorUser = $params['user_id'];
                $primary = $ent->get_primary_entity($creatorUser);
                $creatorEntity = $primary['ID'];
            }
            // If not in concat mode, deletes all copies
            if (! $concat) {
                $this->query(
                    "delete from " . $params['table'] . " where coll_id = '"
                    . $this->protect_string_db(trim($params['coll_id'])) . "' "
                    . " and listinstance_type = '"
                    . $this->protect_string_db(trim($listType)) . "'"
                    . " and res_id = " . $params['res_id']
                    . " and item_mode = 'cc'"
                );
                //$this->show();
            }
            if (isset($diffList['dest']['user_id'])
                && ! empty($diffList['dest']['user_id']) && ! $onlyCc
            ) {
                // If dest_user is set, deletes the dest_user (concat or not concat)
                $this->query(
                    "delete from " . $params['table'] . " where coll_id = '"
                    . $this->protect_string_db(trim($params['coll_id'])) . "'"
                    . " and listinstance_type = '"
                    . $this->protect_string_db(trim($listType)) . "'"
                    . " and res_id = " . $params['res_id']
                    . " and item_mode = 'dest'"
                );
                //$this->show();
                if ($concat) {
                    // Deletes the dest user if he is in copy to avoid duplicate entry
                    $this->query(
                        "delete from " . $params['table'] . " where coll_id = '"
                        . $this->protect_string_db(trim($params['coll_id']))."'"
                        . " and listinstance_type = '"
                        . $this->protect_string_db(trim($listType)) . "' "
                        . "and res_id = " . trim($params['res_id'])
                        . " and item_mode = 'cc' and item_type = 'user_id' "
                        . "and item_id = '"
                        . $this->protect_string_db(
                            trim($diffList['dest']['user_id'])
                        ) . "'"
                    );
                    //$this->show();
                }
                if (isset($diffList['dest']['viewed'])
                    && $diffList['dest']['viewed'] <> ""
                ) {
                    $this->query(
                        "insert into " . $params['table'] . " (coll_id, res_id,"
                        . " listinstance_type, sequence, item_id, item_type, "
                        . "item_mode, added_by_user, added_by_entity, viewed) "
                        . "values ('"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "', " . $params['res_id'] . " , '"
                        . $this->protect_string_db(trim($listType)) . "', 0, '"
                        . $this->protect_string_db(
                            trim($diffList['dest']['user_id'])
                        ) . "', 'user_id' , 'dest', '"
                        . $this->protect_string_db(trim($creatorUser)) . "', '"
                        . $this->protect_string_db(trim($creatorEntity)) . "', "
                        . $diffList['dest']['viewed'] . " )"
                    );
                } else {
                    $this->query(
                        "insert into " . $params['table'] . " (coll_id, res_id,"
                        . " listinstance_type, sequence, item_id, item_type, "
                        . "item_mode, added_by_user, added_by_entity ) values "
                        . "('"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "', " . $params['res_id'] . " , '"
                        . $this->protect_string_db(trim($listType)) . "', 0, '"
                        . $this->protect_string_db(
                            trim($diffList['dest']['user_id'])
                        ) . "', 'user_id' , 'dest', '"
                        . $this->protect_string_db(trim($creatorUser)) . "', '"
                        . $this->protect_string_db(trim($creatorEntity)) . "')"
                    );
                }
                $listinstance_id = $this->last_insert_id('listinstance_id_seq');
                $hist->add(
                    $params['table'],
                    $listinstance_id,
                    'ADD',
                    'diffdestuser',
                    'Diffusion of document '.$params['res_id'],
                    $_SESSION['config']['databasetype'],
                    'apps'
                );                
                //$this->show();
            }
            $maxSeq = 0;
            if ($concat) {
                $this->query(
                    "select max(sequence) as max_seq from " . $params['table']
                    . " where coll_id = '"
                    . $this->protect_string_db(trim($params['coll_id']))
                    . "' and res_id = " . $params['res_id']
                    . " and listinstance_type = '"
                    . $this->protect_string_db(trim($listType))
                    . "' and item_type = 'user_id' and item_mode= 'cc'"
                );
                //$this->show();
                $res = $this->fetch_object();
                if ($res->max_seq > - 1) {
                    $maxSeq = (int) $res->max_seq + 1;
                }
            }
            for ($i = 0; $i < count($diffList['copy']['users']); $i ++) {
                $insert = true;
                if ($concat) {
                    $this->query(
                        "select res_id from " . $params['table']
                        . " where coll_id = '"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "' and res_id = " . $params['res_id']
                        . " and listinstance_type = '"
                        . $this->protect_string_db(trim($listType))
                        . "'  and item_id = '"
                        . $this->protect_string_db(
                            trim($diffList['copy']['users'][$i]['user_id'])
                        ) . "' and item_type = 'user_id' and item_mode= 'cc'"
                    );
                    //$this->show();
                    if ($this->nb_result() == 0) {
                        $insert = true;
                    } else {
                        $insert = false;
                    }
                }
                if ($insert
                    && $diffList['dest']['user_id'] <> $diffList['copy']['users'][$i]['user_id']
                ) {
                    $seq = $i + $maxSeq;
                    if (isset($diffList['copy']['users'][$i]['viewed'])
                        && $diffList['copy']['users'][$i]['viewed'] <> ""
                    ) {
                        $this->query(
                            "insert into " . $params['table'] . " (coll_id, "
                            . "res_id, listinstance_type,  sequence, item_id, "
                            . "item_type, item_mode, added_by_user, "
                            . "added_by_entity, viewed) values ('"
                            . $this->protect_string_db(trim($params['coll_id']))
                            . "', " . $params['res_id'] . " , '"
                            . $this->protect_string_db(trim($listType)) . "', "
                            . $seq . ", '"
                            . $this->protect_string_db(
                                trim($diffList['copy']['users'][$i]['user_id'])
                            ) . "', 'user_id' , 'cc', '"
                            . $this->protect_string_db(trim($creatorUser))
                            . "', '"
                            . $this->protect_string_db(trim($creatorEntity))
                            . "', " . $diffList['copy']['users'][$i]['viewed']
                            . " )"
                        );
                    } else {
                        $this->query(
                            "insert into " . $params['table'] . " (coll_id, "
                            . "res_id, listinstance_type, sequence, item_id, "
                            . "item_type, item_mode, added_by_user, "
                            . "added_by_entity ) values ('"
                            . $this->protect_string_db(trim($params['coll_id']))
                            . "', " . $params['res_id'] . " , '"
                            . $this->protect_string_db(trim($listType)) . "', "
                            . $seq . ", '"
                            . $this->protect_string_db(
                                trim($diffList['copy']['users'][$i]['user_id'])
                            ) . "', 'user_id' , 'cc', '"
                            . $this->protect_string_db(trim($creatorUser))
                            . "', '"
                            . $this->protect_string_db(trim($creatorEntity))
                            . "' )"
                        );
                    }
                    $listinstance_id = $this->last_insert_id('listinstance_id_seq');      
                    $hist->add(
                        $params['table'],
                        $listinstance_id,
                        'ADD',
                        'diffcopyuser',
                        'Diffusion of document '.$params['res_id'],
                        $_SESSION['config']['databasetype'],
                        'apps'
                    ); 
                    //$this->show();
                }
            }
            //found copies to delete if alreay in copy
            /*$this->query(
                "select res_id, item_id from " . $params['table']
                . " where coll_id = '"
                . $this->protect_string_db(trim($params['coll_id'])) ."' "
                . "and res_id = " . $params['res_id'] . " "
                . "and listinstance_type = '"
                . $this->protect_string_db(trim($listType)) . "' "
                . "and item_mode= 'cc'"
            );
            //$this->show();
            while ($resToDelete = $this->fetch_object()) {
                $toDelete = true;
                for ($cptCopies = 0; $cptCopies < count(
                    $diffList['copy']['users']
                ); $cptCopies ++
                ) {
                    if ($resToDelete->item_id == $diffList['copy']['users'][$cptCopies]['user_id']) {
                        $toDelete = false;
                    }
                }
                if ($toDelete) {
                    //echo $toDelete." ".$resToDelete->item_id;
                    $this->query(
                        "delete from " . $params['table'] . " where coll_id = '"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "' and listinstance_type = '"
                        . $this->protect_string_db(trim($listType)) . "' "
                        . "and res_id = " . trim($params['res_id']) . " "
                        . "and item_mode = 'cc' and item_id = '"
                        . $resToDelete->item_id . "'"
                    );
                    //$this->show();
                }
            }*/
            $maxSeq = 0;
            if ($concat) {
                $this->query(
                    "select max(sequence) as max_seq from " . $params['table']
                    . " where coll_id = '"
                    . $this->protect_string_db(trim($params['coll_id']))
                    . "' and res_id = " . $params['res_id']
                    . " and listinstance_type = '"
                    . $this->protect_string_db(trim($listType))
                    . "' and item_type = 'entity_id' and item_mode= 'cc'"
                );
                //$this->show();
                $res = $this->fetch_object();
                if ($res->max_seq > - 1) {
                    $maxSeq = (int) $res->max_seq + 1;
                }
            }
            for ($i = 0; $i < count($diffList['copy']['entities']); $i ++) {
                $insert = true;
                if ($concat) {
                    $this->query(
                        "select res_id from " . $params['table']
                        . " where coll_id = '"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "' and res_id = " . $params['res_id']
                        . " and listinstance_type = '"
                        . $this->protect_string_db(trim($listType))
                        . "' and item_id = '"
                        . $this->protect_string_db(
                            trim($diffList['copy']['entities'][$i]['entity_id'])
                        ) . "' and item_type = 'entity_id' and item_mode= 'cc'"
                    );
                    //$this->show();
                    if ($this->nb_result() == 0) {
                        $insert = true;
                    } else {
                        $insert = false;
                    }
                }
                if ($insert) {
                    $seq = $i + $maxSeq;
                    $this->query(
                        "insert into " . $params['table'] . " (coll_id, res_id,"
                        . " listinstance_type, sequence, item_id, item_type, "
                        . "item_mode ,added_by_user, added_by_entity) values ('"
                        . $this->protect_string_db(trim($params['coll_id']))
                        . "', " . $params['res_id'] . " ,'"
                        . $this->protect_string_db(trim($listType)) . "', "
                        . $seq . ", '"
                        . $this->protect_string_db(
                            trim($diffList['copy']['entities'][$i]['entity_id'])
                        ) . "', 'entity_id' , 'cc',  '" . $creatorUser . "', '"
                        . $creatorEntity . "')"
                    );
                    //$this->show();
                    $listinstance_id = $this->last_insert_id('listinstance_id_seq');      
                    $hist->add(
                        $params['table'],
                        $listinstance_id,
                        'ADD',
                        'diffcopyentity',
                        'Diffusion of document '.$params['res_id'],
                        $_SESSION['config']['databasetype'],
                        'apps'
                    ); 
                }
                //$this->show();
            }
        }
        if ($params['mode'] == 'listinstance') {
            // Deletes the dest user if he is in copy to avoid duplicate entry
            $this->query(
                "select item_id from " . $params['table'] . " where coll_id = '"
                . $this->protect_string_db(trim($params['coll_id']))
                . "' and res_id = " . $params['res_id']
                . " and listinstance_type = '"
                . $this->protect_string_db(trim($listType))
                . "' and item_type = 'user_id' and item_mode= 'dest'"
            );
            //$this->show();
            $result = $this->fetch_object();
            $itemId = $result->item_id;
            $this->query(
                "delete from " . $params['table'] . " where coll_id = '"
                . $this->protect_string_db(trim($params['coll_id']))
                . "' and listinstance_type = '"
                . $this->protect_string_db(trim($listType))
                . "' and res_id = " . trim($params['res_id'])
                . " and item_mode = 'cc' and item_type = 'user_id' "
                . "and item_id = '" . $itemId . "'"
            );
            //$this->show();
        }
        //exit;
    }

    /**
    * Gets a diffusion list for a given resource identifier
    *
    * @param string $resId Resource identifier
    * @param string $collId Collection identifier, 'letterbox_coll' by default
    * @return array $listinstance['dest] : Data of the dest_user
    *                                ['user_id'] : identifier of the dest_user
    *                                ['lastname'] : Lastname of the dest_user
    *                                ['firstname'] : firstname of the dest_user
    *                                ['entity_id'] : entity identifier of the dest_user
    *                                ['entity_label'] : entity label of the dest_user
    *                         ['copy'] : Data of the copies
    *                                ['users'][$i] : Users in copy data
    *                                       ['user_id'] : identifier of the user in copy
    *                                       ['lastname'] : Lastname of the user in copy
    *                                       ['firstname'] : firstname of the user in copy
    *                                       ['entity_id'] : entity identifier of the user in copy
    *                                       ['entity_label'] : entity label of the user in copy
    *                                ['entities'][$i] : Entities in copy data
    *                                       ['entity_id'] : entity identifier of the entity in copy
    *                                       ['entity_label'] : entity label of the entity in copy
    **/
    public function get_listinstance($resId, $modeCc = false, $collId = 'letterbox_coll')
    {
        $listinstance = array();
        $listinstance['dest'] = array();
        $listinstance['copy'] = array();
        $listinstance['copy']['users'] = array();
        $listinstance['copy']['entities'] = array();
        if (empty($resId) || empty($collId)) {
            return $listinstance;
        }

        $this->connect();
        if (! $modeCc) {
            $this->query(
                "select l.item_id, u.firstname, u.lastname, e.entity_id, "
                . "e.entity_label from " . ENT_LISTINSTANCE . " l, "
                . USERS_TABLE . " u, " . ENT_ENTITIES . " e, "
                . ENT_USERS_ENTITIES . " ue where l.coll_id = '"
                . $this->protect_string_db(trim($collId))
                . "' and l.listinstance_type = 'DOC' and l.item_mode = 'dest' "
                . "and l.item_type = 'user_id' and l.sequence = 0 "
                . "and l.item_id = u.user_id and u.user_id = ue.user_id "
                . "and e.entity_id = ue.entity_id and ue.primary_entity = 'Y' "
                . "and l.res_id = " . $resId
            );

            $res = $this->fetch_object();
            $listinstance['dest']['user_id'] = $this->show_string(
                $res->item_id
            );
            $listinstance['dest']['lastname'] = $this->show_string($res->lastname);
            $listinstance['dest']['firstname'] = $this->show_string($res->firstname);
            $listinstance['dest']['entity_id'] = $this->show_string($res->entity_id);
            $listinstance['dest']['entity_label'] = $this->show_string($res->entity_label);
        }
        $this->query(
            "select l.item_id, u.firstname, u.lastname, e.entity_id, "
            . "e.entity_label from " . ENT_LISTINSTANCE . " l, " . USERS_TABLE
            . " u, " . ENT_ENTITIES . " e, " . ENT_USERS_ENTITIES
            . " ue where l.coll_id = '" . $collId
            . "' and l.listinstance_type = 'DOC' and l.item_mode = 'cc' "
            . "and l.item_type = 'user_id'  and l.item_id = u.user_id "
            . "and l.item_id = ue.user_id and ue.user_id=u.user_id "
            . "and e.entity_id = ue.entity_id and l.res_id = " . $resId
            . " and ue.primary_entity = 'Y' order by u.lastname "
        );
        //$this->show();
        while ($res = $this->fetch_object()) {
            array_push(
                $listinstance['copy']['users'],
                array(
                    'user_id' => $this->show_string($res->item_id),
                    'lastname' => $this->show_string($res->lastname),
                    'firstname' => $this->show_string($res->firstname),
                    'entity_id' => $this->show_string($res->entity_id),
                    'entity_label' => $this->show_string($res->entity_label)
                )
            );
        }

        $this->query(
            "select l.item_id,  e.entity_label from " . ENT_LISTINSTANCE
            . " l, " . ENT_ENTITIES . " e where l.coll_id = 'letterbox_coll' "
            . "and l.listinstance_type = 'DOC' and l.item_mode = 'cc' "
            . "and l.item_type = 'entity_id' and l.item_id = e.entity_id "
            . "and l.res_id = " . $resId . " order by e.entity_label "
        );

        while ($res = $this->fetch_object()) {
            array_push(
                $listinstance['copy']['entities'],
                array(
                    'entity_id' => $this->show_string($res->item_id),
                    'entity_label' => $this->show_string($res->entity_label)
                )
            );
        }

        return $listinstance;
    }

    public function set_main_dest($dest, $collId, $resId,
        $listinstanceType = 'DOC', $itemType = 'user_id', $viewed)
    {
        $this->connect();
        $this->query(
            "select item_id from " . ENT_LISTINSTANCE . " where res_id = "
            . $resId." and coll_id = '" . $this->protect_string_db($collId)
            . "' and listinstance_type = '"
            . $this->protect_string_db($listinstanceType)
            . "' and sequence = 0 and item_type = '"
            . $this->protect_string_db($itemType) . "' and item_mode = 'dest'"
        );
        if ($this->nb_result() == 1) {
            $this->query(
                "update " . ENT_LISTINSTANCE . " set item_id = '"
                . $this->protect_string_db($dest) . "', viewed = " . $viewed
                . " where res_id = " . $resId . " and coll_id = '"
                . $this->protect_string_db($collId)
                . "' and listinstance_type = '"
                . $this->protect_string_db($listinstanceType)
                . "' and sequence = 0 and item_type = '"
                . $this->protect_string_db($itemType)
                . "' and item_mode = 'dest'"
            );
        } else {
            $this->query(
                "insert into " . ENT_LISTINSTANCE . " (coll_id, res_id, "
                . "listinstance_type, item_id, item_type, item_mode, sequence, "
                . "added_by_user, added_by_entity, viewed) values ('"
                . $this->protect_string_db($collId) . "', " . $resId . ", '"
                . $this->protect_string_db($listinstanceType) . "', '"
                . $this->protect_string_db($dest) . "', '"
                . $this->protect_string_db($itemType) . "', 'dest', 0, '"
                . $_SESSION['user']['UserId'] . "','"
                . $_SESSION['primaryentity']['id'] . "', " . $viewed . ");"
            );
        }
    }

}