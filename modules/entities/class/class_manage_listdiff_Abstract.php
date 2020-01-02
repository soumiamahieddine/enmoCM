<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

*
* @brief   class_manage_listdiff_Abstract : Contains all the functions to manage diffusion list
*
* @author  dev <dev@maarch.org>
* @ingroup entities
*/
require_once 'modules/entities/entities_tables.php';
require_once 'core/core_tables.php';

abstract class diffusion_list_Abstract extends functions
{
    //**************************************************************************
    // LISTINSTANCE
    // Management of diffusion lists for documents and folders
    //**************************************************************************
    // Legacy load_list_db (for custom calls)
    public function load_list_db(
        $diffList,
        $params,
        $listType = 'DOC',
        $objectType = 'entity_id'
    ) {
        $collId = trim($params['coll_id']);
        $resId = $params['res_id'];

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            require_once 'modules/entities/class/class_manage_entities.php';
            $ent = new entity();
            $creatorUser = trim($params['user_id']);
            $primary = $ent->get_primary_entity($creatorUser);
            $creatorEntity = trim($primary['ID']);
        } else {
            $creatorUser = '';
            $creatorEntity = '';
        }

        $objectType = trim($objectType);

        if (isset($params['fromQualif']) && $params['fromQualif'] == true) {
            $fromQualif = true;
        } else {
            $fromQualif = false;
        }

        $this->save_listinstance(
            $diffList,
            $objectType,
            $collId,
            $resId,
            $creatorUser,
            $creatorEntity,
            $fromQualif
        );
    }

    public function save_listinstance(
        $diffList,
        $difflistType = 'entity_id',
        $collId,
        $resId,
        $creatorUser = '',
        $creatorEntity = '',
        $fromQualif = false
    ) {
        // Fix : superadmin can edit visa workflow in detail
        if ($creatorUser == 'superadmin') {
            $creatorEntity = '';
        }

        $oldListInst = $this->get_listinstance($resId, false, $difflistType);
        /*echo 'old<br/>';
        var_dump($oldListInst);
        echo 'new<br/>';
        var_dump($diffList);*/

        require_once 'core/class/class_history.php';
        $hist = new history();

        $roles = $this->list_difflist_roles();
        $db = new Database();
        $stmt = $db->query('SELECT listinstance_id FROM listinstance WHERE res_id = ? and difflist_type = ?', array($resId, $difflistType));

        $diffUser = false;
        $diffCopyUsers = false;
        $diffCopyEntities = false;

        foreach ($roles as $role_id => $role_label) {
            if ($stmt->rowCount() > 0 && (isset($oldListInst[$role_id]) || isset($diffList[$role_id]))) {
                //compare old and new difflist
                if (!empty($oldListInst[$role_id]['users'])) {
                    for ($iOld = 0; $iOld < count($oldListInst[$role_id]['users']); ++$iOld) {
                        if ($oldListInst[$role_id]['users'][$iOld]['user_id'] != $diffList[$role_id]['users'][$iOld]['user_id']) {
                            $diffUser = true;
                            break;
                        }
                    }
                }

                //USELESS ?
                if (!$diffUser && isset($oldListInst[$role_id]['users']) && !empty($oldListInst[$role_id]['users'])) {
                    if (count($oldListInst[$role_id]['users']) != count($diffList[$role_id]['users'])) {
                        $diffCopyUsers = true;
                    }
                }

                if (!$diffUser && !$diffCopyEntities && isset($oldListInst[$role_id]['entities']) && !empty($oldListInst[$role_id]['entities'])) {
                    if (count($oldListInst[$role_id]['entities']) != count($diffList[$role_id]['entities'])) {
                        $diffCopyEntities = true;
                    }
                    for ($iOld = 0; $iOld < count($oldListInst[$role_id]['entities']); ++$iOld) {
                        foreach ($oldListInst[$role_id]['entities'][$iOld] as $key => $value) {
                            if ($value != $diffList[$role_id]['entities'][$iOld][$key]) {
                                $diffCopyEntities = true;
                                break;
                            }
                        }
                    }
                }

                // check add/remove all copy entities
                if ((isset($diffList[$role_id]['entities']) && !isset($oldListInst[$role_id]['entities'])) || (!isset($diffList[$role_id]['entities']) && isset($oldListInst[$role_id]['entities']))) {
                    $diffCopyUsers = true;
                }
            }
        }

        if ($diffUser || $diffCopyUsers || $diffCopyEntities) {
            $this->save_listinstance_history($collId, $resId, $difflistType);
        }

        //Delete previous listinstance
        $stmt = $db->query(
            'DELETE FROM '.ENT_LISTINSTANCE
            .' WHERE res_id = ? AND difflist_type = ?',
            array($resId, $difflistType)
        );

        $roles = $this->list_difflist_roles();
        foreach ($roles as $role_id => $role_label) {
            // Special value 'copy', item_mode = cc
            if ($role_id == 'copy') {
                $item_mode = 'cc';
            } else {
                $item_mode = $role_id;
            }

            $cptUsers = 0;
            if (!empty($diffList[$role_id]['users']) && is_array($diffList[$role_id]['users'])) {
                $cptUsers = count($diffList[$role_id]['users']);
            }
            for ($i = 0; $i < $cptUsers; ++$i) {
                $userFound = false;
                $userId = trim($diffList[$role_id]['users'][$i]['user_id']);
                $processComment = trim($diffList[$role_id]['users'][$i]['process_comment']);
                $processDate = trim($diffList[$role_id]['users'][$i]['process_date']);
                $viewed = (int) $diffList[$role_id]['users'][$i]['viewed'];

                $signatory = ($diffList[$role_id]['users'][$i]['signatory'] ? 'true' : 'false');
                $requested_signature = ($diffList[$role_id]['users'][$i]['requested_signature'] ? 'true' : 'false');

                $cptOldUsers = 0;
                if (!empty($oldListInst[$role_id]['users']) && is_array($oldListInst[$role_id]['users'])) {
                    $cptOldUsers = count($oldListInst[$role_id]['users']);
                }
                for ($h = 0; $h < $cptOldUsers; ++$h) {
                    if ($userId == $oldListInst[$role_id]['users'][$h]['user_id']) {
                        $userFound = true;
                        break;
                    } else {
                        //else
                    }
                }
                //Modification du dest_user dans la table res_letterbox
                if ($role_id == 'dest') {
                    $resEntities = \User\models\UserEntityModel::get(['select' => ['entity_id', 'primary_entity'], 'where' => ['user_id = ?'], 'data' => [$userId]]);
                    $mailDestination = \Resource\models\ResModel::getById(['select' => ['destination'], 'resId' => $resId]);

                    $entityFound = false;
                    $primaryEntity = '';
                    foreach ($resEntities as $key => $value) {
                        if ($mailDestination['destination'] == $value['entity_id']) {
                            $entityFound = true;
                        }
                        if ($value['primary_entity'] == 'Y') {
                            $primaryEntity = $value['entity_id'];
                        }
                    }
                    if ($entityFound) {
                        $stmt = $db->query('update res_letterbox set dest_user = ? where res_id = ?', array($userId, $resId));
                    } else {
                        $stmt = $db->query('update res_letterbox set dest_user = ?, destination = ? where res_id = ?', array($userId, $primaryEntity, $resId));
                    }
                }

                if ($processDate != '') {
                    $stmt = $db->query(
                        'insert into '.ENT_LISTINSTANCE
                            .' (res_id, sequence, item_id, item_type, item_mode, added_by_user, viewed, difflist_type, process_comment, process_date, signatory, requested_signature) '
                        .'values ('
                            .'?, ?, '
                            ."?, "
                            .'?, '
                            ."'user_id' , "
                            .'?, '
                            .'?, '
                            .'?, ?, '
                            .'?, '
                            .'?, '
                            .'?, '
                            .'?, '
                            .'?'
                        .' )',
                        array($resId, $i, $userId, $item_mode, $creatorUser, $viewed, $difflistType, $processComment, $processDate, $signatory, $requested_signature)
                    );
                } else {
                    $stmt = $db->query(
                        'insert into '.ENT_LISTINSTANCE
                            .' (res_id, sequence, item_id, item_type, item_mode, added_by_user, viewed, difflist_type, process_comment, signatory, requested_signature) '
                        .'values ('
                            .'?, ?, '
                            .'?, '
                            ."'user_id' , "
                            .'?, '
                            .'?, '
                            .'?, ?, '
                            .'?, '
                            .'?, '
                            .'?'
                        .' )',
                        array($resId, $i, $userId, $item_mode, $creatorUser, $viewed, $difflistType, $processComment, $signatory, $requested_signature)
                    );
                }

                if (!$userFound || $fromQualif) {
                    //History
                    $listinstance_id = $db->lastInsertId('listinstance_id_seq');
                    $hist->add(
                        ENT_LISTINSTANCE,
                        $listinstance_id,
                        'ADD',
                        'diff'.$role_id.'user',
                        'Diffusion du document '.$resId.' à '.$userId.' en tant que "'.$role_id.'"',
                        $_SESSION['config']['databasetype'],
                        'entities'
                    );
                }
            }

            //CUSTOM ENTITY ROLES
            $cptEntities = 0;
            if (!empty($diffList[$role_id]['entities']) && is_array($diffList[$role_id]['entities'])) {
                $cptEntities = count($diffList[$role_id]['entities']);
            }
            for ($j = 0; $j < $cptEntities; ++$j) {
                $entityFound = false;
                $entityId = trim($diffList[$role_id]['entities'][$j]['entity_id']);
                $viewed = (int) $diffList[$role_id]['entities'][$j]['viewed'];
                $cptOldEntities = 0;
                if (is_array($oldListInst[$role_id]['entities'])) {
                    $cptOldEntities = count($oldListInst[$role_id]['entities']);
                }
                for ($g = 0; $g < $cptOldEntities; ++$g) {
                    if ($entityId == $oldListInst[$role_id]['entities'][$g]['entity_id']) {
                        $entityFound = true;
                        break;
                    } else {
                        //else
                    }
                }

                $stmt = $db->query(
                    'INSERT INTO '.ENT_LISTINSTANCE
                        .' (res_id, sequence, item_id, item_type, item_mode, added_by_user, viewed, difflist_type) '
                    .'VALUES ('
                        .'?, ?, '
                        .'?, '
                        ."'entity_id' , "
                        .'?, '
                        .'?, '
                        .'?, ?'
                    .' )',
                    array($resId, $j, $entityId, $item_mode, $creatorUser, $viewed, $difflistType)
                );

                if (!$entityFound || $fromQualif) {
                    //History
                    $listinstance_id = $db->lastInsertId('listinstance_id_seq');
                    $hist->add(
                        ENT_LISTINSTANCE,
                        $listinstance_id,
                        'ADD',
                        'diff'.$role_id.'user',
                        'Diffusion of document '.$resId.' to '.$entityId.' as '.$role_id,
                        $_SESSION['config']['databasetype'],
                        'entities'
                    );
                }
            }
        }
    }

    public function save_listinstance_history($coll_id, $res_id, $difflistType)
    {
        $user = \User\models\UserModel::getByLogin(['select' => ['id'], 'login' => $_SESSION['user']['UserId']]);

        $db = new Database();
        $db->query('INSERT INTO listinstance_history (coll_id, res_id, user_id, updated_date) VALUES (?, ?, ?, current_timestamp)', array('letterbox_coll', $res_id, $user['id']));
        $listinstance_history_id = $db->lastInsertId('listinstance_history_id_seq');

        $stmt = $db->query('SELECT * FROM listinstance WHERE res_id = ? and difflist_type = ?', array($res_id, $difflistType));

        while ($resListinstance = $stmt->fetchObject()) {
            $stmt2 = $db->query(
                'INSERT INTO listinstance_history_details 
                    (listinstance_history_id, coll_id, res_id, listinstance_type, sequence, item_id, item_type, item_mode, added_by_user, viewed, difflist_type, process_comment) '
                .'VALUES (?,?, ?, '
                    ."'DOC', ?, "
                    .'?, '
                    .'? , '
                    .'?, '
                    .'?, '
                    .'?,?, '
                    .'?, '
                    .'?'
                .' )',
                array($listinstance_history_id, 'letterbox_coll', $res_id, $resListinstance->sequence, $resListinstance->item_id, $resListinstance->item_type, $resListinstance->item_mode, $resListinstance->added_by_user, $resListinstance->viewed, $resListinstance->difflist_type, $resListinstance->process_comment)
            );
        }
    }

    /**
     * Gets a diffusion list for a given resource identifier.
     *
     * @param string $resId  Resource identifier
     * @param string $collId Collection identifier, 'letterbox_coll' by default
     *
     * @return array $listinstance['dest] : Data of the dest_user
     *               ['user_id'] : identifier of the dest_user
     *               ['lastname'] : Lastname of the dest_user
     *               ['firstname'] : firstname of the dest_user
     *               ['entity_id'] : entity identifier of the dest_user
     *               ['entity_label'] : entity label of the dest_user
     *               ['copy'] : Data of the copies
     *               ['users'][$i] : Users in copy data
     *               ['user_id'] : identifier of the user in copy
     *               ['lastname'] : Lastname of the user in copy
     *               ['firstname'] : firstname of the user in copy
     *               ['entity_id'] : entity identifier of the user in copy
     *               ['entity_label'] : entity label of the user in copy
     *               ['entities'][$i] : Entities in copy data
     *               ['entity_id'] : entity identifier of the entity in copy
     *               ['entity_label'] : entity label of the entity in copy
     **/
    public function get_listinstance(
        $resId,
        $modeCc = false,
        $typeList = 'entity_id'
    ) {
        $db = new Database();
        $roles = $this->list_difflist_roles();

        $listinstance = array();

        if (empty($resId)) {
            return $listinstance;
        }

        // Load header
        $query =
            'SELECT distinct res_id, difflist_type'
            .' FROM '.ENT_LISTINSTANCE
            .' WHERE res_id = ? GROUP BY res_id, difflist_type';
        $stmt = $db->query($query, array($resId));

        $listinstance = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($listinstance['difflist_type'] == '') {
            $listinstance['difflist_type'] = 'entity_id';
        }

        // DEST USER
        /*if (! $modeCc) {
            $stmt = $db->query(
                "select l.item_id, u.firstname, u.lastname, e.entity_id, "
                . "e.entity_label, l.viewed from " . ENT_LISTINSTANCE . " l, "
                . USERS_TABLE . " u, " . ENT_ENTITIES . " e, "
                . ENT_USERS_ENTITIES . " ue "
                . " where l.item_mode = 'dest' "
                . "and l.item_type = 'user_id' and l.sequence = 0 "
                . "and l.item_id = u.user_id and u.user_id = ue.user_id "
                . "and e.entity_id = ue.entity_id and ue.primary_entity = 'Y' "
                . "and l.res_id = " . $resId
            );

            $res = $stmt->fetchObject();

            $listinstance['dest'] = array(
                'user_id' => $this->show_string($res->item_id),
                'lastname' => $this->show_string($res->lastname),
                'firstname' => $this->show_string($res->firstname),
                'entity_id' => $this->show_string($res->entity_id),
                'entity_label' => $this->show_string($res->entity_label),
                'viewed' => $this->show_string($res->viewed)
            );
        }*/

        // OTHER ROLES USERS
        //**********************************************************************
        $stmt = $db->query(
            'select l.listinstance_id ,l.item_id, u.firstname, u.lastname, e.entity_id, e.entity_label,'
            .' l.viewed, l.item_mode, l.difflist_type, l.process_date, l.process_comment, l.signatory, l.requested_signature from '
            .ENT_LISTINSTANCE.' l, '.USERS_TABLE
            .' u, '.ENT_ENTITIES.' e, '.ENT_USERS_ENTITIES
            ." ue where l.item_type = 'user_id' and l.item_id = u.user_id "
            .' and l.item_id = ue.user_id and ue.user_id=u.user_id '
            ." and e.entity_id = ue.entity_id and l.difflist_type = ? and l.res_id = ? and ue.primary_entity = 'Y' order by l.sequence ",
            array($typeList, $resId)
        );
        //$this->show();
        while ($res = $stmt->fetchObject()) {
            if ($res->item_mode == 'cc') {
                $role_id = 'copy';
            } else {
                $role_id = $res->item_mode;
            }

            if (!isset($listinstance[$role_id]['users'])) {
                $listinstance[$role_id]['users'] = [];
            }
            $listinstance[$role_id]['users'][] = [
                'listinstance_id' => $res->listinstance_id,
                'user_id' => functions::show_string($res->item_id),
                'lastname' => functions::show_string($res->lastname),
                'firstname' => functions::show_string($res->firstname),
                'entity_id' => functions::show_string($res->entity_id),
                'entity_label' => functions::show_string($res->entity_label),
                'viewed' => functions::show_string($res->viewed),
                'difflist_type' => functions::show_string($res->difflist_type),
                'process_date' => functions::show_string($res->process_date),
                'process_comment' => functions::show_string($res->process_comment),
                'signatory' => (empty($res->signatory) ? false : true),
                'requested_signature' => (empty($res->requested_signature) ? false : true),
            ];
        }

        // OTHER ROLES ENTITIES
        //**********************************************************************
        $stmt = $db->query(
            'select l.item_id,  e.entity_label, l.viewed, l.item_mode, l.difflist_type, l.process_date, l.process_comment  from '.ENT_LISTINSTANCE
            .' l, '.ENT_ENTITIES.' e where l.difflist_type = ? '
            ."and l.item_type = 'entity_id' and l.item_id = e.entity_id "
            .'and l.res_id = ? order by l.sequence ',
            array($typeList, $resId)
        );

        while ($res = $stmt->fetchObject()) {
            if ($res->item_mode == 'cc') {
                $role_id = 'copy';
            } else {
                $role_id = $res->item_mode;
            }

            if (!isset($listinstance[$role_id]['entities'])) {
                $listinstance[$role_id]['entities'] = array();
            }
            array_push(
                $listinstance[$role_id]['entities'],
                array(
                     'entity_id' => functions::show_string($res->item_id),
                     'entity_label' => functions::show_string($res->entity_label),
                     'viewed' => functions::show_string($res->viewed),
                     'difflist_type' => functions::show_string($res->difflist_type),
                     'process_date' => functions::show_string($res->process_date),
                     'process_comment' => functions::show_string($res->process_comment),
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
        $db = new Database();
        $stmt = $db->query(
            'select item_id from '.ENT_LISTINSTANCE." where res_id = ? and sequence = 0 and item_type = ? and item_mode = 'dest'",
            array($resId, $itemType)
        );
        if ($stmt->rowCount() == 1) {
            $stmt = $db->query(
                'update '.ENT_LISTINSTANCE." set item_id = ?, viewed = ? where res_id = ? and sequence = 0 and item_type = ? and item_mode = 'dest'",
                array($dest, $viewed, $resId, $itemType)
            );
        } else {
            $stmt = $db->query(
                'insert into '.ENT_LISTINSTANCE.' (res_id, '
                .'item_id, item_type, item_mode, sequence, '
                ."added_by_user, viewed) values (?, ?, ?, ?, 'dest', 0, ?, ?)",
                array($resId, $dest, $itemType, $_SESSION['user']['UserId'], $viewed)
            );
        }
    }

    //**************************************************************************
    // DIFFLIST_ROLES
    // Administration and management of roles
    //**************************************************************************
    //  Get list of available roles for list models and diffusion lists definition
    public function list_difflist_roles()
    {
        $roles = array();

        //load roles difflist
        if (file_exists(
            $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR
            .$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'
            .DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR
            .'roles.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR
            .$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'
            .DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR
            .'roles.xml';
        } else {
            $path = 'modules'.DIRECTORY_SEPARATOR.'entities'
                  .DIRECTORY_SEPARATOR.'xml'
                  .DIRECTORY_SEPARATOR.'roles.xml';
        }

        $xmlroles = simplexml_load_file($path);

        foreach ($xmlroles->ROLES as $value) {
            foreach ($value as $role) {
                $id = (string) $role->id;
                $label = (string) $role->label;
                if (!empty($label) && defined($label) && constant($label) != null) {
                    $label = constant($label);
                }
                $roles[$id] = $label;
            }
        }

        return $roles;
    }

    public function list_difflist_roles_to_keep($resId, $collId, $objectType, $newDiffList)
    {
        $roles = array();

        //load roles difflist
        if (file_exists(
            $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR
            .$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'
            .DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR
            .'roles.xml'
        )
        ) {
            $path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR
            .$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'
            .DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR
            .'roles.xml';
        } else {
            $path = 'modules'.DIRECTORY_SEPARATOR.'entities'
                  .DIRECTORY_SEPARATOR.'xml'
                  .DIRECTORY_SEPARATOR.'roles.xml';
        }

        $xmlroles = simplexml_load_file($path);

        foreach ($xmlroles->ROLES as $value) {
            foreach ($value as $role) {
                $id = (string) $role->id;
                $keep_string = (string) $role->keep_in_diffusion_list;
                $keep = $keep_string === 'true' ? true : false;
                $roles[$id] = $keep;
            }
        }

        $aCurrentDiffList = diffusion_list::get_listinstance($resId, false, $objectType);
        if (!empty($aCurrentDiffList)) {
            // On regarde les roles a conserver
            foreach ($aCurrentDiffList as $roleId => $listDiffItemId) {
                if ($roles[$roleId]) {
                    $aRolesToKeep[$roleId] = $listDiffItemId;
                }
            }

            if (!empty($aRolesToKeep['dest']['users'][0]['user_id']) && !empty($newDiffList['dest']['users'][0]['user_id'])) {
                if ($newDiffList['dest']['users'][0]['user_id'] != $aRolesToKeep['dest']['users'][0]['user_id']) {
                    // On passe le dest en copie car il ne peut y avoir qu'un dest.
                    $aRolesToKeep['copy']['users'][] = $aRolesToKeep['dest']['users'][0];
                }
                unset($aRolesToKeep['dest']);
            }

            // Si c'est parametre, on reinjecte les anciens roles dans la nouvelle liste de diffusion
            if (!empty($aRolesToKeep)) {
                foreach ($aRolesToKeep as $roleId => $listDiffItemId) {
                    // On injecte le user s il n'existe pas encore
                    if (isset($listDiffItemId['users'])) {
                        if (!isset($newDiffList[$roleId]['users']) && isset($listDiffItemId['users'])) {
                            $newDiffList[$roleId]['users'] = [];
                        }
                        foreach ($listDiffItemId['users'] as $value) {
                            if (!diffusion_list::in_array_r($value['user_id'], $newDiffList[$roleId]['users'], true)) {
                                $newDiffList[$roleId]['users'][] = $value;
                            }
                        }
                    }

                    // On injecte l entite si elle n'existe pas encore
                    if (isset($listDiffItemId['entities'])) {
                        if (!isset($newDiffList[$roleId]['entities']) && isset($listDiffItemId['entities'])) {
                            $newDiffList[$roleId]['entities'] = [];
                        }
                        foreach ($listDiffItemId['entities'] as $value) {
                            if (!diffusion_list::in_array_r($value['entity_id'], $newDiffList[$roleId]['entities'], true)) {
                                $newDiffList[$roleId]['entities'][] = $value;
                            }
                        }
                    }
                }
            }
        }

        return $newDiffList;
    }

    public function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && diffusion_list::in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    //**************************************************************************
    // DIFFLIST_TYPES
    // Administration and management of types of list
    //**************************************************************************
    //  Get list of available list types / labels
    public function list_difflist_types()
    {
        $db = new Database();
        $stmt = $db->query('select * from '.ENT_DIFFLIST_TYPES);

        $types = array();

        while ($type = $stmt->fetchObject()) {
            $types[(string) $type->difflist_type_id] = $type->difflist_type_label;
        }

        return $types;
    }

    // Get given listmodel type object
    public function get_difflist_type(
        $difflist_type_id
    ) {
        $db = new Database();
        $stmt = $db->query(
            'SELECT * FROM '.ENT_DIFFLIST_TYPES
            .' WHERE difflist_type_id = ?',
            array($difflist_type_id)
        );

        $difflist_type = $stmt->fetchObject();

        return $difflist_type;
    }

    public function get_difflist_type_roles(
        $difflist_type
    ) {
        $roles = array();

        $db = new Database();

        $role_ids = explode(' ', $difflist_type->difflist_type_roles);

        for ($i = 0, $l = count($role_ids);
            $i < $l;
            ++$i
        ) {
            $role_id = $role_ids[$i];
            $role_label = diffusion_list::get_difflist_type_roles_label($role_id);
            $roles[$role_id] = $role_label;
        }

        return $roles;
    }

    public function get_difflist_type_roles_label(
        $role_id
    ) {
        $roles = diffusion_list::list_difflist_roles();

        foreach ($roles as $id => $label) {
            if ($role_id == $id) {
                $role_label = $label;
            }
        }

        return $role_label;
    }

    public function insert_difflist_type(
        $difflist_type_id,
        $difflist_type_label,
        $difflist_type_roles,
        $allow_entities
    ) {
        $db = new Database();
        $stmt = $db->query(
            'insert into '.ENT_DIFFLIST_TYPES
                .' (difflist_type_id, difflist_type_label, difflist_type_roles, allow_entities)'
                .' values ('
                    .'?,'
                    .'?,'
                    .'?,'
                    .'?'
                    .')',
            array($difflist_type_id, $difflist_type_label, $difflist_type_roles, $allow_entities)
        );
    }

    public function update_difflist_type(
        $difflist_type_id,
        $difflist_type_label,
        $difflist_type_roles,
        $allow_entities
    ) {
        $db = new Database();
        $stmt = $db->query(
            'update '.ENT_DIFFLIST_TYPES
                .' set '
                    .' difflist_type_label = ?,'
                    .' difflist_type_roles = ?,'
                    .' allow_entities = ?'
                .' where difflist_type_id = ?',
            array($difflist_type_label, $difflist_type_roles, $allow_entities, $difflist_type_id)
        );
    }

    public function delete_difflist_type(
        $difflist_type_id
    ) {
        $db = new Database();
        $stmt = $db->query(
            'DELETE FROM '.ENT_DIFFLIST_TYPES
            .' WHERE difflist_type_id = ?',
            array($difflist_type_id)
        );
    }
}
