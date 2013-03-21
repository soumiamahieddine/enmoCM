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
    #**************************************************************************
    # LISTMODELS
    # Administration and use of diffusion list templates
    #**************************************************************************
    public function select_listmodels(
        $objectType='entity_id'
    ) {
        $listmodels = array();
        $this->connect();
        
        $query = 
            "SELECT distinct object_type, object_id, description"
            . " FROM " . ENT_LISTMODELS
            . " WHERE object_type = '".$objectType."'" 
            . " GROUP BY object_type, object_id, description " 
            . " ORDER BY object_type ASC, object_id ASC";

        $this->query($query);
        
        while ($listmodel = $this->fetch_array()) {
            if($listmodel['description'] == '')
                $listmodel['description'] = $listmodel['object_id'];
                
            $listmodels[] = $listmodel;
        }
        
        return $listmodels;
    }
        
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
    public function get_listmodel(
        $objectType='entity_id', 
        $objectId
    ) {
        $objectId = $this->protect_string_db($objectId);
        $objectType = $this->protect_string_db($objectType);
        
        $this->connect();
        $roles = $this->list_difflist_roles();
        $listmodel = array();
        
        if (empty($objectId) || empty($objectType)) 
            return $listmodel;
        
        # Load header
        $query = 
            "SELECT distinct object_type, object_id, description"
            . " FROM " . ENT_LISTMODELS
            . " WHERE object_type = '".$objectType."'" 
                . "and object_id = '" . $objectId . "'"
            . " GROUP BY object_type, object_id, description";

        $this->query($query);
        
        $listmodel = $this->fetch_assoc();
        
        # Load list
        foreach($roles as $role_id => $role_label) {
            if($role_id == 'copy')
                $item_mode = 'cc';
            else 
                $item_mode = $role_id;
            
            # Users
            $this->query(
                "SELECT l.item_id, l.item_mode, u.firstname, u.lastname, e.entity_id, e.entity_label, l.visible "
                . " FROM " . ENT_LISTMODELS . " l "
                    . " JOIN " . USERS_TABLE . " u ON l.item_id = u.user_id " 
                    . " JOIN " . ENT_USERS_ENTITIES . " ue ON u.user_id = ue.user_id " 
                    . " JOIN " . ENT_ENTITIES . " e ON ue.entity_id = e.entity_id"
                . " WHERE "
                    . "ue.primary_entity = 'Y' "
                    . "and l.item_mode = '".$item_mode."' "
                    . "and l.item_type = 'user_id' " 
                    . "and l.object_type = '". $objectType ."' "
                    . "and l.object_id = '" . $objectId . "'"
                . "ORDER BY l.sequence"
            );

            while ($user = $this->fetch_object()) {
                if(!isset($listmodel[$role_id]))
                    $listmodel[$role_id] = array();
                if(!isset($listmodel[$role_id]['users']))
                    $listmodel[$role_id]['users'] = array();
                                
                array_push(
                    $listmodel[$role_id]['users'],
                    array(
                        'user_id' => $this->show_string($user->item_id),
                        'lastname' => $this->show_string($user->lastname),
                        'firstname' => $this->show_string($user->firstname),
                        'entity_id' => $this->show_string($user->entity_id),
                        'entity_label' => $this->show_string($user->entity_label),
                        'visible' => $user->visible
                    )
                );
            }
            
            # Entities
            $this->query(
                "SELECT l.item_id, e.entity_label, l.item_mode, l.visible "
                . "FROM " . ENT_LISTMODELS . " l "
                    . "JOIN " . ENT_ENTITIES . " e ON l.item_id = e.entity_id "
                . "WHERE "
                    . " l.item_mode = '".$item_mode."' "
                    . "and l.item_type = 'entity_id' "
                    . "and l.object_type = '" . $objectType . "' "
                    . "and l.object_id = '" . $objectId . "' "
                . "ORDER BY l.sequence "
            );

            while ($entity = $this->fetch_object()) {
                if(!isset($listmodel[$role_id]))
                    $listmodel[$role_id] = array();
                if(!isset($listmodel[$role_id]['entities']))
                    $listmodel[$role_id]['entities'] = array();
                    
                array_push(
                    $listmodel[$role_id]['entities'],
                    array(
                        'entity_id' => $this->show_string($entity->item_id),
                        'entity_label' => $this->show_string($entity->entity_label),
                        'visible' => $entity->visible
                    )
                );
            }
        }
        return $listmodel;
    }
    
    public function save_listmodel(
        $diffList, 
        $objectType = 'entity_id',
        $objectId,
        $description = false
    ) {
        $this->connect();
        $roles = $this->list_difflist_roles();
        
        require_once 'core/class/class_history.php';
        $hist = new history();
        
        $objectType = $this->protect_string_db(trim($objectType));
        $objectId = $this->protect_string_db(trim($objectId));
        $description = $this->protect_string_db(trim($description));
        
        # Delete all and replace full list
        #**********************************************************************
        $this->query(
            "delete from " . ENT_LISTMODELS 
            . " where "
                . "object_type = '" . $objectType . "' "
                . "and object_id = '" . $objectId . "' "
        );
        foreach($roles as $role_id => $role_label) {
            if($role_id == 'copy')
                $item_mode = 'cc';
            else 
                $item_mode = $role_id;
            
            # users
            #**********************************************************************
            for ($i=0, $l=count($diffList[$role_id]['users']);
                $i<$l; 
                $i++
            ) {
                $user = $diffList[$role_id]['users'][$i];
                $this->query(
                    "insert into " . ENT_LISTMODELS
                        . " (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible ) "
                    . " values ("
                        . "'any', "
                        . "'" . $objectId . "' , " 
                        . "'" . $objectType . "', "
                        . $i . ", "
                        . "'" . $user['user_id'] . "', "
                        . "'user_id', "
                        . "'".$item_mode."', "
                        . "null, "
                        . "'" . $description . "',"
                        . "'" . $user['visible']. "'"
                    . ")"
                );
            }
            # Entities
            #**********************************************************************
            for ($i=0, $l=count($diffList[$role_id]['entities']); $i<$l ; $i++) {
                $entity = $diffList[$role_id]['entities'][$i];
                $this->query(
                    "insert into " . ENT_LISTMODELS
                        . " (coll_id, object_id, object_type, sequence, item_id, item_type, item_mode, listmodel_type, description, visible ) "
                    . " values ("
                        . "'any', "
                        . "'" . $objectId . "' , " 
                        . "'" . $objectType . "', "
                        . $i . ", "
                        . "'" . $entity['entity_id'] . "', "
                        . "'entity_id', "
                        . "'".$item_mode."', "
                        . "null, "
                        . "'" . $description . "', "
                        . "'" . $entity['visible'] . "'"
                    . ")"
                );
            }
        }
    }
    
    public function delete_listmodel(
        $objectType = 'entity_id',
        $objectId
    ) {
        $this->connect();
       
        $objectType = $this->protect_string_db(trim($objectType));
        $objectId = $this->protect_string_db(trim($objectId));

        # Delete all and replace full list
        #**********************************************************************
        $this->query(
            "delete from " . ENT_LISTMODELS 
            . " where "
                . "object_type = '" . $objectType . "' "
                . "and object_id = '" . $objectId . "' "
        );
    
    }
    
    #**************************************************************************
    # LISTINSTANCE
    # Management of diffusion lists for documents and folders
    #**************************************************************************
    # Legacy load_list_db (for custom calls)
    function load_list_db(
        $diffList, 
        $params, 
        $listType = 'DOC', 
        $objectType = 'entity_id'
    ) {
        $collId = $this->protect_string_db(trim($params['coll_id']));
        $resId = $params['res_id'];
        
        if (isset($params['user_id']) && ! empty($params['user_id'])) {
            require_once 'modules/entities/class/class_manage_entities.php';
            $ent = new entity();
            $creatorUser = $this->protect_string_db(trim($params['user_id']));
            $primary = $ent->get_primary_entity($creatorUser);
            $creatorEntity = $this->protect_string_db(trim($primary['ID']));
        } else {
            $creatorUser = '';
            $creatorEntity = '';
        }
        
        $objectType = $this->protect_string_db(trim($objectType));
        
        $this->save_listinstance(
            $diffList,
            $objectType,
            $collId,
            $resId,
            $creatorUser,
            $creatorEntity
        );
        
    }
    
    function save_listinstance(
        $diffList,
        $difflistType = 'entity_id',
        $collId,
        $resId,
        $creatorUser = "",
        $creatorEntity = ""
    ) {
        $this->connect();

        require_once 'core/class/class_history.php';
        $hist = new history();
        
        # Delete previous listinstance
        $this->query(
            "DELETE FROM " . ENT_LISTINSTANCE
            . " WHERE coll_id = '" . $collId . "'"
                . " AND res_id = " . $resId
        );
        
        $roles = $this->list_difflist_roles();
        foreach($roles as $role_id => $role_label) {
            # Special value 'copy', item_mode = cc
            if($role_id == 'copy')
                $item_mode = 'cc';
            else 
                $item_mode = $role_id;
            
            for ($i=0, $l=count($diffList[$role_id]['users']);
                $i<$l;
                $i++
            ) {
                $userId = $this->protect_string_db(trim($diffList[$role_id]['users'][$i]['user_id']));
                $visible = $diffList[$role_id]['users'][$i]['visible'];
                $viewed = (integer)$diffList[$role_id]['users'][$i]['viewed'];
                $this->query(
                    "insert into " . ENT_LISTINSTANCE
                        . " (coll_id, res_id, listinstance_type, sequence, item_id, item_type, item_mode, added_by_user, added_by_entity, visible, viewed, difflist_type) "
                    . "values ("
                        . "'" . $collId . "', "
                        . $resId . ", "
                        . "null, "
                        . $i . ", "
                        . "'" . $userId . "', " 
                        . "'user_id' , "
                        . "'".$item_mode."', "
                        . "'" . $creatorUser . "', "
                        . "'" . $creatorEntity. "', "
                        . "'" . $visible . "', "
                        . $viewed . ", "
                        . "'" . $difflistType . "'"
                    . " )"
                );
                
                # History
                $listinstance_id = $this->last_insert_id('listinstance_id_seq');      
                $hist->add(
                    ENT_LISTINSTANCE,
                    $listinstance_id,
                    'ADD',
                    'diff'.$role_id.'user',
                    'Diffusion of document '.$resId.' to '. $userId . ' as ' . $role_id,
                    $_SESSION['config']['databasetype'],
                    'entities'
                ); 
            }
            
            # CUSTOM ENTITY ROLES
            for ($i=0, $l=count($diffList[$role_id]['entities']);
                $i<$l;
                $i++
            ) {
                $entityId = $this->protect_string_db(trim($diffList[$role_id]['entities'][$i]['entity_id']));
                $visible = $diffList[$role_id]['entities'][$i]['visible'];
                $viewed = (integer)$diffList[$role_id]['entities'][$i]['viewed'];
                
                $this->query(
                    "insert into " . ENT_LISTINSTANCE
                        . " (coll_id, res_id, listinstance_type, sequence, item_id, item_type, item_mode, added_by_user, added_by_entity, visible, viewed, difflist_type) "
                    . "values ("
                        . "'" . $collId . "', "
                        . $resId . ", "
                        . "null, "
                        . $i . ", "
                        . "'" . $entityId . "', " 
                        . "'entity_id' , "
                        . "'".$item_mode."', "
                        . "'" . $creatorUser . "', "
                        . "'" . $creatorEntity . "', "
                        . "'" . $visible . "',"
                        . $viewed. ", "
                        . "'" . $difflistType . "'"
                    . " )"
                );
                
                # History
                $listinstance_id = $this->last_insert_id('listinstance_id_seq');      
                $hist->add(
                    ENT_LISTINSTANCE,
                    $listinstance_id,
                    'ADD',
                    'diff'.$role_id.'user',
                    'Diffusion of document '.$resId.' to '. $entityId . ' as ' . $role_id,
                    $_SESSION['config']['databasetype'],
                    'entities'
                ); 

            }
        }
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
    public function get_listinstance(
        $resId, 
        $modeCc = false, 
        $collId = 'letterbox_coll'
    ) {
        $this->connect();
        $roles = $this->list_difflist_roles();
        
        $listinstance = array();       
        
        if (empty($resId) || empty($collId)) {
            return $listinstance;
        }
        
        # Load header
        $query = 
            "SELECT distinct coll_id, res_id, difflist_type"
            . " FROM " . ENT_LISTINSTANCE
            . " WHERE coll_id = '" . $collId . "' "
                . "and res_id = " . $resId
            . " GROUP BY coll_id, res_id, difflist_type";

        $this->query($query);

        $listinstance = $this->fetch_assoc();
        if($listinstance['difflist_type'] == "")
            $listinstance['difflist_type'] = 'entity_id';
        
        # DEST USER
        /*if (! $modeCc) {
            $this->query(
                "select l.item_id, u.firstname, u.lastname, e.entity_id, "
                . "e.entity_label, l.visible, l.viewed from " . ENT_LISTINSTANCE . " l, "
                . USERS_TABLE . " u, " . ENT_ENTITIES . " e, "
                . ENT_USERS_ENTITIES . " ue "
                . " where l.coll_id = '" . $collId . "' "
                ." and l.item_mode = 'dest' "
                . "and l.item_type = 'user_id' and l.sequence = 0 "
                . "and l.item_id = u.user_id and u.user_id = ue.user_id "
                . "and e.entity_id = ue.entity_id and ue.primary_entity = 'Y' "
                . "and l.res_id = " . $resId
            );

            $res = $this->fetch_object();
           
            $listinstance['dest'] = array(
                'user_id' => $this->show_string($res->item_id),
                'lastname' => $this->show_string($res->lastname),
                'firstname' => $this->show_string($res->firstname),
                'entity_id' => $this->show_string($res->entity_id),
                'entity_label' => $this->show_string($res->entity_label),
                'visible' => $this->show_string($res->visible),
                'viewed' => $this->show_string($res->viewed)
            );
        }*/
        
        # OTHER ROLES USERS
        #**********************************************************************
        $this->query(
            "select l.item_id, u.firstname, u.lastname, e.entity_id, "
            . "e.entity_label, l.visible, l.viewed, l.item_mode, l.difflist_type from "
            . ENT_LISTINSTANCE . " l, " . USERS_TABLE
            . " u, " . ENT_ENTITIES . " e, " . ENT_USERS_ENTITIES
            . " ue where l.coll_id = '" . $collId . "' "
            . " and l.item_type = 'user_id' and l.item_id = u.user_id "
            . " and l.item_id = ue.user_id and ue.user_id=u.user_id "
            . " and e.entity_id = ue.entity_id and l.res_id = " . $resId
            . " and ue.primary_entity = 'Y' order by l.sequence "
        );
        //$this->show();
        while ($res = $this->fetch_object()) {
            if($res->item_mode == 'cc') 
                $role_id = 'copy';
            else 
                $role_id = $res->item_mode;
                
            if(!isset($listinstance[$role_id]['users']))
                $listinstance[$role_id]['users'] = array();
            array_push(
                $listinstance[$role_id]['users'],
                array(
                    'user_id' => $this->show_string($res->item_id),
                    'lastname' => $this->show_string($res->lastname),
                    'firstname' => $this->show_string($res->firstname),
                    'entity_id' => $this->show_string($res->entity_id),
                    'entity_label' => $this->show_string($res->entity_label),
                    'visible' => $this->show_string($res->visible),
                    'viewed' => $this->show_string($res->viewed),
                    'difflist_type' => $this->show_string($res->difflist_type)
                )
            );
        }

        # OTHER ROLES ENTITIES
        #**********************************************************************
        $this->query(
            "select l.item_id,  e.entity_label, l.visible, l.viewed, l.item_mode, l.difflist_type from " . ENT_LISTINSTANCE
            . " l, " . ENT_ENTITIES . " e where l.coll_id =  '" . $collId . "' "
            . "and l.item_type = 'entity_id' and l.item_id = e.entity_id "
            . "and l.res_id = " . $resId . " order by l.sequence "
        );

        while ($res = $this->fetch_object()) {
            if($res->item_mode == 'cc') 
                $role_id = 'copy';
            else 
                $role_id = $res->item_mode;
                
            if(!isset($listinstance[$role_id]['entities']))
                $listinstance[$role_id]['entities'] = array();
            array_push(
                $listinstance[$role_id]['entities'],
                array(
                    'entity_id' => $this->show_string($res->item_id),
                    'entity_label' => $this->show_string($res->entity_label),
                    'visible' => $this->show_string($res->visible),
                    'viewed' => $this->show_string($res->viewed),
                    'difflist_type' => $this->show_string($res->difflist_type)
                )
            );
        }
        
        return $listinstance;
    }

    public function set_main_dest(
        $dest, 
        $collId, 
        $resId,
        $listinstanceType = 'DOC', 
        $itemType = 'user_id', 
        $viewed
    ) {
        $this->connect();
        $this->query(
            "select item_id from " . ENT_LISTINSTANCE . " where res_id = "
            . $resId." and coll_id = '" . $this->protect_string_db($collId)
            . "' and sequence = 0 and item_type = '"
            . $this->protect_string_db($itemType) . "' and item_mode = 'dest'"
        );
        if ($this->nb_result() == 1) {
            $this->query(
                "update " . ENT_LISTINSTANCE . " set item_id = '"
                . $this->protect_string_db($dest) . "', viewed = " . $viewed
                . " where res_id = " . $resId . " and coll_id = '"
                . $this->protect_string_db($collId)
                . "' and sequence = 0 and item_type = '"
                . $this->protect_string_db($itemType)
                . "' and item_mode = 'dest'"
            );
        } else {
            $this->query(
                "insert into " . ENT_LISTINSTANCE . " (coll_id, res_id, "
                . "item_id, item_type, item_mode, sequence, "
                . "added_by_user, added_by_entity, viewed) values ('"
                . $this->protect_string_db($collId) . "', " . $resId . ", '"
                . $this->protect_string_db($dest) . "', '"
                . $this->protect_string_db($itemType) . "', 'dest', 0, '"
                . $_SESSION['user']['UserId'] . "','"
                . $_SESSION['primaryentity']['id'] . "', " . $viewed . ")"
            );
        }
    }
    
    #**************************************************************************
    # DIFFLIST_ROLES
    # Administration and management of roles
    #************************************************************************** 
    #  Get list of available roles for list models and diffusion lists definition
    public function list_difflist_roles() 
    {
        $roles = array();
        
        require_once 'core' . DIRECTORY_SEPARATOR . 'core_tables.php';
        
        $query = 
            "SELECT distinct ug.group_id, ug.group_desc "
            . " FROM " . USERGROUPS_TABLE . " ug "
            . " LEFT JOIN " . USERGROUP_CONTENT_TABLE . " ugc "
                . " ON ug.group_id = ugc.group_id "
            . " WHERE ug.enabled = 'Y'";
        
        if($user_id)
            $query .= " AND ugc.user_id = '".$user_id."'";
        
        $query .= " GROUP BY ug.group_id, ug.group_desc";
        $query .= " ORDER BY ug.group_id ASC";
        
        $this->connect();
        $this->query($query);
        
        $roles['dest'] = _DEST_USER;
        $roles['copy'] = _TO_CC;
        
        while($usergroup = $this->fetch_object()) {
            $group_id = $usergroup->group_id;
            $roles[$group_id] = $usergroup->group_desc;
        }
        
        return $roles;
    }
    
    #**************************************************************************
    # DIFFLIST_TYPES
    # Administration and management of types of list
    #**************************************************************************
    #  Get list of available list model types / labels
    public function list_difflist_types()
    {
        $this->connect();
        $this->query('select * from ' . ENT_DIFFLIST_TYPES);
        
        $types = array();
                        
        while ($type = $this->fetch_object()) { 
            $types[(string) $type->difflist_type_id] = $type->difflist_type_label;
        }
        return $types;
    }
    
    # Get given listmodel type object
    public function get_difflist_type(
        $difflist_type_id
    ) {       
        $this->connect();
        $this->query(
            'SELECT * FROM ' . ENT_DIFFLIST_TYPES
            . " WHERE difflist_type_id = '".$difflist_type_id."'" 
        );
        
        $difflist_type = $this->fetch_object();
       
        return $difflist_type;
    }
    
    public function get_difflist_type_roles(
        $difflist_type
    ) {
        $roles = array();
        
        $this->connect();
        
        $role_ids = explode(' ', $difflist_type->difflist_type_roles);

        for($i=0, $l=count($role_ids);
            $i<$l;
            $i++
        ) {
            $role_id = $role_ids[$i];
            switch($role_id) {
            case 'dest' :
                $role_label = _DEST_USER;
                break;
            case 'copy' :
                $role_label = _TO_CC;
                break;
                
            default:
                $this->query(
                    "SELECT group_desc FROM " . USERGROUPS_TABLE
                    . " WHERE group_id = '" . $role_id . "'"
                );
                $group = $this->fetch_object();
                $role_label = $group->group_desc;
            }
            $roles[$role_id] = $role_label;
        }
        return $roles;
        
    }
    
    public function insert_difflist_type(
        $difflist_type_id,
        $difflist_type_label,
        $difflist_type_roles,
        $allow_entities
    ) {
        $this->connect();
        $this->query(
            "insert into " . ENT_DIFFLIST_TYPES
                . " (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities)"
                . " values (" 
                    . "'" . $difflist_type_id . "',"
                    . "'" . $difflist_type_label .  "',"
                    . "'" . $difflist_type_roles . "',"
                    . "'" . $allow_entities . "'"
                    . ")"
        );
    }
    
    public function update_difflist_type(
        $difflist_type_id,
        $difflist_type_label,
        $difflist_type_roles,
        $allow_entities
    ) {
        $this->connect();
        $this->query(
            "update " . ENT_DIFFLIST_TYPES 
                . " set "
                    . " difflist_type_label = '" . $difflist_type_label . "',"
                    . " difflist_type_roles = '" . $difflist_type_roles . "',"
                    . " allow_entities = '" . $allow_entities . "'"
                . " where difflist_type_id = '" . $difflist_type_id . "'"
        );
    }
    
    public function delete_difflist_type(
        $difflist_type_id
    ) {
        $this->connect();
        $this->query(
            'DELETE FROM ' . ENT_DIFFLIST_TYPES
            . " WHERE difflist_type_id = '".$difflist_type_id."'" 
        );
    }
        
    #**************************************************************************
    # GROUPBASKET_DIFFLIST_TYPES
    # Types of lists available for a given group in basket
    #**************************************************************************   
    #  Get list of available list model types for a given groupbasket
    public function list_groupbasket_difflist_types(
        $group_id,
        $basket_id,
        $action_id
    ) {
        $types = array();
        $this->connect();
        $this->query(
            "select difflist_type_id from " . ENT_GROUPBASKET_DIFFLIST_TYPES
            . " where group_id = '".$group_id."'" 
                . " and basket_id = '".$basket_id."'"
                . " and action_id = ".$action_id
        );
        
        $types = array();
                
        while ($type = $this->fetch_object()) { 
            $types[] = (string) $type->difflist_type_id;
        }
        return $types;
    }

    
}
