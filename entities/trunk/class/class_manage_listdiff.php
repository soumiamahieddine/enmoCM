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
require("modules/entities/entities_tables.php");
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
    * @param string $entity_id Entity identifier
    * @param array $coll_id Collection identifier ('letterbox_coll' by default)
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
    public function get_listmodel_from_entity($entity_id, $coll_id = 'letterbox_coll')
    {
        $listmodel = array();
        $listmodel['dest'] = array();
        $listmodel['copy'] = array();
        $listmodel['copy']['users'] = array();
        $listmodel['copy']['entities'] = array();
        if(empty($entity_id))
        {
            return $listmodel;
        }
        $entity_id = $this->protect_string_db($entity_id);
        $this->connect();
        $this->query("select  l.item_id, u.firstname, u.lastname, e.entity_id, e.entity_label  from ".$_SESSION['tablename']['ent_listmodels']." l, ".$_SESSION['tablename']['users']." u, ".ENT_ENTITIES." e, ".ENT_USERS_ENTITIES." ue where l.coll_id = '".$this->protect_string_db(trim($coll_id))."' and l.listmodel_type = 'DOC' and l.item_mode = 'dest' and l.item_type = 'user_id' and l.object_type = 'entity_id' and l.sequence = 0 and l.object_id = '".$this->protect_string_db(trim($entity_id))."' and l.item_id = u.user_id and u.user_id = ue.user_id and e.entity_id = ue.entity_id and ue.primary_entity = 'Y'");

        $res = $this->fetch_object();
        $listmodel['dest']['user_id'] = $this->show_str($res->item_id);
        $listmodel['dest']['lastname'] = $this->show_str($res->lastname);
        $listmodel['dest']['firstname'] = $this->show_str($res->firstname);
        $listmodel['dest']['entity_id'] = $this->show_str($res->entity_id);
        $listmodel['dest']['entity_label'] = $this->show_str($res->entity_label);

        $this->query("select  l.item_id, u.firstname, u.lastname, e.entity_id, e.entity_label  from ".$_SESSION['tablename']['ent_listmodels']." l, ".$_SESSION['tablename']['users']." u, ".ENT_ENTITIES." e, ".ENT_USERS_ENTITIES." ue where l.coll_id = '".$this->protect_string_db(trim($coll_id))."' and l.listmodel_type = 'DOC' and l.item_mode = 'cc' and l.item_type = 'user_id' and l.object_type = 'entity_id' and l.object_id = '".$this->protect_string_db(trim($entity_id))."' and l.item_id = u.user_id and l.item_id = ue.user_id and e.entity_id = ue.entity_id and ue.primary_entity='Y' order by u.lastname ");

        while($res = $this->fetch_object())
        {
            array_push($listmodel['copy']['users'], array('user_id' =>  $this->show_string($res->item_id), 'lastname' => $this->show_string($res->lastname), 'firstname' => $this->show_string($res->firstname), 'entity_id' =>  $this->show_string($res->entity_id), 'entity_label' => $this->show_string($res->entity_label)));
        }

        $this->query("select  l.item_id,  e.entity_label  from ".$_SESSION['tablename']['ent_listmodels']." l, ".ENT_ENTITIES." e where l.coll_id = '".$this->protect_string_db(trim($coll_id))."' and l.listmodel_type = 'DOC' and l.item_mode = 'cc' and l.item_type = 'entity_id' and l.object_type = 'entity_id' and l.object_id = '".$this->protect_string_db(trim($entity_id))."' and l.item_id = e.entity_id order by e.entity_label ");

        while($res = $this->fetch_object())
        {
            array_push($listmodel['copy']['entities'], array('entity_id' =>  $this->show_string($res->item_id), 'entity_label' => $this->show_string($res->entity_label)));
        }

        return $listmodel;
    }

    /**
    * Loads a diffusion list into database (listinstance or listmodel table)
    *
    * @param array $diff_list['dest] : Data of the dest_user
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
    * @param string $list_type List type, 'DOC' by default
    * @param string $object_type Object type, 'entity_id' by default
    **/
    function load_list_db($diff_list, $params, $list_type = 'DOC', $object_type = 'entity_id')
    {
        //print_r($diff_list);exit;
        $this->connect();
        //print_r($_SESSION['m_admin']['entity']['listmodel']);
        //echo "<br>";
        //print_r($params);
        if($params['mode'] == 'listmodel' && isset($params['object_id']) && !empty($params['object_id']))
        {
            $this->query("delete from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and object_type = '".$this->protect_string_db(trim($object_type))."' and object_id = '".$this->protect_string_db(trim($params['object_id']))."' and listmodel_type = '".$this->protect_string_db(trim($list_type))."'");
            //$this->show();
            if(isset($diff_list['dest']['user_id']) && !empty($diff_list['dest']['user_id']))
            {
                if($diff_list['dest']['viewed'] <> "")
                {
                    $this->query("insert into ".$params['table']." (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, viewed) values ('".$this->protect_string_db(trim($params['coll_id']))."', '".$this->protect_string_db(trim($params['object_id']))."' , '".$this->protect_string_db(trim($object_type))."', 0, '".$this->protect_string_db(trim($diff_list['dest']['user_id']))."', 'user_id' , 'dest', '".$this->protect_string_db(trim($list_type))."', ".$diff_list['dest']['viewed'].")");
                }
                else
                {
                    $this->query("insert into ".$params['table']." (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type ) values ('".$this->protect_string_db(trim($params['coll_id']))."', '".$this->protect_string_db(trim($params['object_id']))."' , '".$this->protect_string_db(trim($object_type))."', 0, '".$this->protect_string_db(trim($diff_list['dest']['user_id']))."', 'user_id' , 'dest', '".$this->protect_string_db(trim($list_type))."')");
                }
                //$this->show();
                for($i=0;$i<count($diff_list['copy']['users']);$i++)
                {
                    if($diff_list['copy']['users'][$i]['viewed'] <> "")
                    {
                        $this->query("insert into ".$params['table']." (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, viewed) values ('".$this->protect_string_db(trim($params['coll_id']))."', '".$this->protect_string_db(trim($params['object_id']))."' , '".$this->protect_string_db(trim($object_type))."', ".$i.", '".$this->protect_string_db(trim($diff_list['copy']['users'][$i]['user_id']))."', 'user_id' , 'cc', '".$this->protect_string_db(trim($list_type))."', ".$diff_list['copy']['users'][$i]['viewed'].")");
                    }
                    else
                    {
                        $this->query("insert into ".$params['table']." (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type ) values ('".$this->protect_string_db(trim($params['coll_id']))."', '".$this->protect_string_db(trim($params['object_id']))."' , '".$this->protect_string_db(trim($object_type))."', ".$i.", '".$this->protect_string_db(trim($diff_list['copy']['users'][$i]['user_id']))."', 'user_id' , 'cc', '".$this->protect_string_db(trim($list_type))."')");
                    }
                    //$this->show();
                }
                for($i=0;$i<count($diff_list['copy']['entities']);$i++)
                {
                    $this->query("insert into ".$params['table']." (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type ) values ('".$this->protect_string_db(trim($params['coll_id']))."', '".$this->protect_string_db(trim($params['object_id']))."' , '".$this->protect_string_db(trim($object_type))."', ".$i.", '".$this->protect_string_db(trim($diff_list['copy']['entities'][$i]['entity_id']))."', 'entity_id' , 'cc', '".$this->protect_string_db(trim($list_type))."')");
                    //$this->show();
                }
            }
        }
        else if($params['mode'] == 'listinstance')
        {
            $creator_user = '';
            $creator_entity = '';
            if(!isset($params['concat_list']))
            {
                $concat = false;
            }
            else
            {
                $concat = $params['concat_list'];
            }
            if(!isset($params['only_cc']))
            {
                $only_cc = false;
            }
            else
            {
                $only_cc = $params['only_cc'];
            }
            if(isset($params['user_id']) && !empty($params['user_id']))
            {
                require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
                $ent = new entity();
                $creator_user = $params['user_id'];
                $primary = $ent->get_primary_entity($creator_user);
                $creator_entity = $primary['ID'];
            }
            // If not in concat mode, deletes all copies
            if(!$concat)
            {
                $this->query("delete from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."'  and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and res_id = ".$params['res_id']." and item_mode = 'cc'");
                //$this->show();
            }
            if(isset($diff_list['dest']['user_id']) && !empty($diff_list['dest']['user_id']) && !$only_cc)
            {
                // If dest_user is set, deletes the dest_user (concat or not concat)
                $this->query("delete from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."'  and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and res_id = ".$params['res_id']." and item_mode = 'dest'");
                //$this->show();
                if($concat)
                {
                    // Deletes the dest user if he is in copy to avoid duplicate entry
                    $this->query("delete from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."'  and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and res_id = ".trim($params['res_id'])." and item_mode = 'cc' and item_type = 'user_id' and item_id = '".$this->protect_string_db(trim($diff_list['dest']['user_id']))."'");
                    //$this->show();
                }
                if($diff_list['dest']['viewed'] <> "")
                {
                    $this->query("insert into ".$params['table']." (coll_id, res_id, listinstance_type,  sequence, item_id, item_type, item_mode, added_by_user, added_by_entity, viewed) values ('".$this->protect_string_db(trim($params['coll_id']))."', ".$params['res_id']." , '".$this->protect_string_db(trim($list_type))."', 0, '".$this->protect_string_db(trim($diff_list['dest']['user_id']))."', 'user_id' , 'dest', '".$this->protect_string_db(trim($creator_user))."', '".$this->protect_string_db(trim($creator_entity))."', ".$diff_list['dest']['viewed']." )");
                }
                else
                {
                    $this->query("insert into ".$params['table']." (coll_id, res_id, listinstance_type,  sequence, item_id, item_type, item_mode, added_by_user, added_by_entity  ) values ('".$this->protect_string_db(trim($params['coll_id']))."', ".$params['res_id']." , '".$this->protect_string_db(trim($list_type))."', 0, '".$this->protect_string_db(trim($diff_list['dest']['user_id']))."', 'user_id' , 'dest', '".$this->protect_string_db(trim($creator_user))."', '".$this->protect_string_db(trim($creator_entity))."' )");
                }
                //$this->show();
            }
            $max_seq = 0;
            if($concat)
            {
                $this->query("select max(sequence) as max_seq from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and res_id = ".$params['res_id']." and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and item_type = 'user_id' and item_mode= 'cc'");
                //$this->show();
                $res = $this->fetch_object();
                if($res->max_seq > -1)
                {
                    $max_seq = (int) $res->max_seq + 1;
                }
            }
            for($i=0;$i<count($diff_list['copy']['users']);$i++)
            {
                $insert = true;
                if($concat)
                {
                    $this->query("select res_id from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and res_id = ".$params['res_id']." and listinstance_type = '".$this->protect_string_db(trim($list_type))."'  and item_id = '".$this->protect_string_db(trim($diff_list['copy']['users'][$i]['user_id']))."' and item_type = 'user_id' and item_mode= 'cc'");
                    //$this->show();
                    if($this->nb_result() == 0)
                    {
                        $insert = true;
                    }
                    else
                    {
                        $insert = false;
                    }
                }
                if($insert && $diff_list['dest']['user_id'] <> $diff_list['copy']['users'][$i]['user_id'])
                {
                    $seq = $i + $max_seq;
                    if($diff_list['copy']['users'][$i]['viewed'] <> "")
                    {
                        $this->query("insert into ".$params['table']." (coll_id, res_id, listinstance_type,  sequence, item_id, item_type, item_mode, added_by_user, added_by_entity, viewed) values ('".$this->protect_string_db(trim($params['coll_id']))."', ".$params['res_id']." , '".$this->protect_string_db(trim($list_type))."', ".$seq.", '".$this->protect_string_db(trim($diff_list['copy']['users'][$i]['user_id']))."', 'user_id' , 'cc', '".$this->protect_string_db(trim($creator_user))."', '".$this->protect_string_db(trim($creator_entity))."', ".$diff_list['copy']['users'][$i]['viewed']." )");
                    }
                    else
                    {
                        $this->query("insert into ".$params['table']." (coll_id, res_id, listinstance_type,  sequence, item_id, item_type, item_mode, added_by_user, added_by_entity ) values ('".$this->protect_string_db(trim($params['coll_id']))."', ".$params['res_id']." , '".$this->protect_string_db(trim($list_type))."', ".$seq.", '".$this->protect_string_db(trim($diff_list['copy']['users'][$i]['user_id']))."', 'user_id' , 'cc', '".$this->protect_string_db(trim($creator_user))."', '".$this->protect_string_db(trim($creator_entity))."' )");
                    }
                    //$this->show();
                }
            }
            //found copies to delete
            $this->query("select res_id, item_id from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and res_id = ".$params['res_id']." and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and item_mode= 'cc'");
            //$this->show();
            while($resToDelete = $this->fetch_object())
            {
                $toDelete = true;
                for($cptCopies=0;$cptCopies<count($diff_list['copy']['users']);$cptCopies++)
                {
                    if($resToDelete->item_id == $diff_list['copy']['users'][$cptCopies]['user_id'])
                    {
                        $toDelete = false;
                    }
                }
                if($toDelete)
                {
                    //echo $toDelete." ".$resToDelete->item_id;
                    $this->query("delete from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."'  and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and res_id = ".trim($params['res_id'])." and item_mode = 'cc' and item_id = '".$resToDelete->item_id."'");
                    //$this->show();
                }

            }
            $max_seq = 0;
            if($concat)
            {
                $this->query("select max(sequence) as max_seq from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and res_id = ".$params['res_id']." and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and item_type = 'entity_id' and item_mode= 'cc'");
                //$this->show();
                $res = $this->fetch_object();
                if($res->max_seq > -1)
                {
                    $max_seq = (int) $res->max_seq + 1;
                }
            }
            for($i=0; $i<count($diff_list['copy']['entities']);$i++)
            {
                $insert = true;
                if($concat)
                {
                    $this->query("select res_id from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and res_id = ".$params['res_id']." and listinstance_type = '".$this->protect_string_db(trim($list_type))."'  and item_id = '".$this->protect_string_db(trim($diff_list['copy']['entities'][$i]['entity_id']))."' and item_type = 'entity_id' and item_mode= 'cc'");
                    //$this->show();
                    if($this->nb_result() == 0)
                    {
                        $insert = true;
                    }
                    else
                    {
                        $insert = false;
                    }
                }
                if($insert)
                {
                    $seq = $i + $max_seq;
                    $this->query("insert into ".$params['table']." (coll_id, res_id, listinstance_type,  sequence, item_id, item_type, item_mode ,added_by_user, added_by_entity  ) values ('".$this->protect_string_db(trim($params['coll_id']))."', ".$params['res_id']." ,'".$this->protect_string_db(trim($list_type))."', ".$seq.", '".$this->protect_string_db(trim($diff_list['copy']['entities'][$i]['entity_id']))."', 'entity_id' , 'cc',  '".$creator_user."', '".$creator_entity."')");
                    //$this->show();
                }
                //$this->show();
            }
        }
        if($params['mode'] == 'listinstance')
        {
            // Deletes the dest user if he is in copy to avoid duplicate entry
            $this->query("select item_id from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."' and res_id = ".$params['res_id']." and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and item_type = 'user_id' and item_mode= 'dest'");
            //$this->show();
            $result = $this->fetch_object();
            $itemId = $result->item_id;
            $this->query("delete from ".$params['table']." where coll_id = '".$this->protect_string_db(trim($params['coll_id']))."'  and listinstance_type = '".$this->protect_string_db(trim($list_type))."' and res_id = ".trim($params['res_id'])." and item_mode = 'cc' and item_type = 'user_id' and item_id = '".$itemId."'");
            //$this->show();
        }
        //exit;
    }

    /**
    * Gets a diffusion list for a given resource identifier
    *
    * @param string $res_id Resource identifier
    * @param string $coll_id Collection identifier, 'letterbox_coll' by default
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
    public function get_listinstance($res_id, $mode_cc = false, $coll_id = 'letterbox_coll')
    {
        $listinstance = array();
        $listinstance['dest'] = array();
        $listinstance['copy'] = array();
        $listinstance['copy']['users'] = array();
        $listinstance['copy']['entities'] = array();
        if(empty($res_id) || empty($coll_id))
        {
            return $listinstance;
        }

        $this->connect();
        if(!$mode_cc)
        {
            $this->query("select  l.item_id, u.firstname, u.lastname, e.entity_id, e.entity_label  from ".$_SESSION['tablename']['ent_listinstance']." l, ".$_SESSION['tablename']['users']." u, ".ENT_ENTITIES." e, ".ENT_USERS_ENTITIES." ue where l.coll_id = '".$this->protect_string_db(trim($coll_id))."' and l.listinstance_type = 'DOC' and l.item_mode = 'dest' and l.item_type = 'user_id' and l.sequence = 0 and l.item_id = u.user_id and u.user_id = ue.user_id and e.entity_id = ue.entity_id and ue.primary_entity = 'Y' and l.res_id = ".$res_id);

            $res = $this->fetch_object();
            $listinstance['dest']['user_id'] = $this->show_string($res->item_id);
            $listinstance['dest']['lastname'] = $this->show_string($res->lastname);
            $listinstance['dest']['firstname'] = $this->show_string($res->firstname);
            $listinstance['dest']['entity_id'] = $this->show_string($res->entity_id);
            $listinstance['dest']['entity_label'] = $this->show_string($res->entity_label);
        }
        $this->query("select  l.item_id, u.firstname, u.lastname, e.entity_id, e.entity_label  from ".$_SESSION['tablename']['ent_listinstance']." l, ".$_SESSION['tablename']['users']." u, ".ENT_ENTITIES." e, ".ENT_USERS_ENTITIES." ue where l.coll_id = '".$coll_id."' and l.listinstance_type = 'DOC' and l.item_mode = 'cc' and l.item_type = 'user_id'  and l.item_id = u.user_id and l.item_id = ue.user_id and ue.user_id=u.user_id and e.entity_id = ue.entity_id and l.res_id = ".$res_id." and ue.primary_entity = 'Y' order by u.lastname ");
        //$this->show();
        while($res = $this->fetch_object())
        {
            array_push($listinstance['copy']['users'], array('user_id' =>  $this->show_string($res->item_id), 'lastname' => $this->show_string($res->lastname), 'firstname' => $this->show_string($res->firstname), 'entity_id' =>  $this->show_string($res->entity_id), 'entity_label' => $this->show_string($res->entity_label)));
        }

        $this->query("select  l.item_id,  e.entity_label  from ".$_SESSION['tablename']['ent_listinstance']." l, ".ENT_ENTITIES." e where l.coll_id = 'letterbox_coll' and l.listinstance_type = 'DOC' and l.item_mode = 'cc' and l.item_type = 'entity_id' and l.item_id = e.entity_id and l.res_id = ".$res_id." order by e.entity_label ");

        while($res = $this->fetch_object())
        {
            array_push($listinstance['copy']['entities'], array('entity_id' =>  $this->show_string($res->item_id), 'entity_label' => $this->show_string($res->entity_label)));
        }

        return $listinstance;
    }

    public function set_main_dest($dest, $coll_id, $res_id, $listinstance_type = 'DOC', $item_type = 'user_id', $viewed)
    {
        $this->connect();
        $this->query("select item_id from ".$_SESSION['tablename']['ent_listinstance']." where res_id = ".$res_id." and coll_id = '".$this->protect_string_db($coll_id)."' and listinstance_type = '".$this->protect_string_db($listinstance_type)."' and sequence = 0 and item_type = '".$this->protect_string_db($item_type)."' and item_mode = 'dest'");
        if($this->nb_result() == 1)
        {
            $this->query("update ".$_SESSION['tablename']['ent_listinstance']." set item_id = '".$this->protect_string_db($dest)."', viewed = ".$viewed." where res_id = ".$res_id." and coll_id = '".$this->protect_string_db($coll_id)."' and listinstance_type = '".$this->protect_string_db($listinstance_type)."' and sequence = 0 and item_type = '".$this->protect_string_db($item_type)."' and item_mode = 'dest'");
        }
        else
        {
            $this->query("insert into ".$_SESSION['tablename']['ent_listinstance']." (coll_id, res_id, listinstance_type, item_id, item_type, item_mode, sequence,  added_by_user, added_by_entity, viewed) values ('".$this->protect_string_db($coll_id)."', ".$res_id.", '".$this->protect_string_db($listinstance_type)."', '".$this->protect_string_db($dest)."', '".$this->protect_string_db($item_type)."', 'dest', 0, '".$_SESSION['user']['UserId']."','".$_SESSION['primaryentity']['id']."', ".$viewed.");");
        }
    }
}
?>
