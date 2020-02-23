<?php
/*
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/*
 * @brief avis_controler_Abstract
 * @author dev@maarch.org
 * @ingroup avis
 *
 */
abstract class avis_controler_Abstract
{
    public function getAvis($resId)
    {
        //define avis limit date
        $db = new Database();

        $query = "SELECT notes.user_id,notes.note_text, opinion_limit_date FROM notes,res_letterbox WHERE identifier = ? AND note_text LIKE '[POUR AVIS]%' AND notes.identifier = res_letterbox.res_id";

        $stmt = $db->query($query, array($resId));

        $avis = $stmt->fetchObject();

        return $avis;
    }

    //####################################
    //# send avis
    //####################################

    public function getList($res_id, $coll_id, $bool_modif = false, $typeList, $isAvisStep = false, $fromDetail = '')
    {
        $circuit = $this->getWorkflow($res_id, $coll_id, $typeList);

        $str = '<div class="error" id="divErrorAvis" onclick="this.hide();"></div>';
        $str .= '<div class="info" id="divInfoAvis" onclick="this.hide();"></div>';

        //AVIS USER LIST
        if ($bool_modif == true) {
            $str .= '<select data-placeholder="'._ADD_AVIS_ROLE.'" id="avisUserList" onchange="addAvisUser();">';
            $str .= '<option value="" ></option>';

            $tab_userentities = $this->getEntityAvis();
            $tab_users = $this->getUsersAvis();
            //Order by parent entity
            foreach ($tab_userentities as $key => $value) {
                $str .= '<optgroup label="'.$tab_userentities[$key]['entity_id'].'">';
                foreach ($tab_users as $user) {
                    if ($tab_userentities[$key]['entity_id'] == $user['entity_id']) {
                        $selected = ' ';
                        if ($user['id'] == $step['user_id']) {
                            $selected = ' selected';
                        }
                        $str .= '<option value="'.$user['id'].'" '.$selected.'>'.$user['lastname'].' '.$user['firstname'].'</option>';
                    }
                }
                $str .= '</optgroup>';
            }
            $str .= '</select>';
            $str .= '<script>';
            $str .= '$j("#avisUserList").chosen({width: "250px", disable_search_threshold: 10});';
            $str .= '</script>';


            $str .= ' <select data-placeholder="'._ADD_AVIS_MODEL.'" name="modelList" id="modelList" onchange="loadAvisModelUsers();">';
            $str .= '<option value=""></option>';
            $str .= '</select>';

            $str .= '<script>';
            $str .= '$j("#modelList").chosen({width: "250px", disable_search_threshold: 10});';
            $str .= '</script>';
            $str .= '<br/><br/>';
        }

        $str .= '<div id="avis_content">';
        //AVIS USER IN DOCUMENT
        $i = 1;
        $lastUserAvis = true;

        if (empty($circuit['avis']['users']) || !is_array($circuit['avis']['users']) || count($circuit['avis']['users']) == 0) {
            $str .= '<div id="emptyAvis"><strong><em>'._EMPTY_AVIS_WORKFLOW.'</em></strong></div>';
        } else {
            $str .= '<div id="emptyAvis" style="display:none;"><strong><em>'._EMPTY_AVIS_WORKFLOW.'</em></strong></div>';
            if (count($circuit['avis']['users']) > 0) {
                foreach ($circuit['avis']['users'] as $it => $info_userAvis) {
                    if (empty($info_userAvis['process_date'])) {
                        if ($lastUserAvis == true && $isAvisStep == true) {
                            $vised = ' currentAvis';
                            $modif = 'false';
                            $disabled = '';
                            $link_vis = 'arrow-right ';
                            $del_vis = '<div class="delete_avis"></div>';
                            if ($info_userAvis['user_id'] != $_SESSION['user']['UserId']) {
                                //$info_vised = '<p style="color:red;">Vous donnez votre avis à la place de ' . $info_userAvis['firstname'] . ' ' . $info_userAvis['lastname'] . '!</p>';
                                $dropZone = '';
                            } else {
                                //$info_vised = 'Vous êtes l\'actuel conseiller';
                                $dropZone = '';
                            }
                        } else {
                            $dropZone = '<i class="fa fa-exchange-alt fa-2x fa-rotate-90" aria-hidden="true" title="'._DRAG_N_DROP_CHANGE_ORDER.'" style="cursor: pointer"></i>';
                            $vised = '';
                            if ($bool_modif == true) {
                                $modif = 'true';
                                $del_vis = '<i class="fa fa-trash-alt" aria-hidden="true" onclick="delAvisUser(this.parentElement.parentElement);" title="'._DELETE.'"></i>';
                                $disabled = '';
                            } else {
                                $dropZone = '';
                                $modif = 'false';
                                $del_vis = '';
                                $disabled = ' disabled="disabled"';
                            }

                            $info_vised = '';
                            $link_vis = 'hourglass-half';
                        }

                        $lastUserAvis = false;
                    } else {
                        $lastUserAvis = true;
                        $modif = 'false';
                        $vised = ' vised';
                        $link_vis = 'check';
                        $disabled = ' disabled="disabled"';
                        $info_vised = '<br/><sub>avis donné le : '.functions::format_date_db($info_userAvis['process_date'], '', '', true).'</sub>';
                        $del_vis = '';
                    }
                    //AVIS USER LINE CIRCUIT
                    $str .= '<div class="droptarget'.$vised.'" id="avis_'.$i.'" draggable="'.$modif.'">';
                    $str .= '<span class="avisUserStatus">';
                    $str .= '<i class="fa fa-'.$link_vis.'" aria-hidden="true"></i>';
                    $str .= '</span>';
                    $str .= '<span class="avisUserInfo">';
                    $str .= '<sup class="avisUserPos nbResZero">'.$i.'</sup>&nbsp;&nbsp;';
                    $str .= '<i class="fa fa-user fa-2x" aria-hidden="true"></i> '.$info_userAvis['lastname'].' '.$info_userAvis['firstname'].' <sup class="nbRes">'.$info_userAvis['entity_id'].'</sup>'.$info_vised;
                    $str .= '</span>';
                    $str .= '<span class="avisUserAction">';
                    $str .= $del_vis;
                    $str .= '</span>';
                    $str .= '<span class="avisUserConsigne">';
                    $str .= '<input class="userId" type="hidden" value="'.$info_userAvis['user_id'].'"/><input class="avisDate" type="hidden" value="'.$info_userAvis['process_date'].'"/><input'.$disabled.' class="consigne" type="text" value="'.$info_userAvis['process_comment'].'"/>';
                    $str .= '</span>';
                    $str .= '<span id="dropZone">';
                    $str .= $dropZone;
                    $str .= '</span>';
                    $str .= '</div>';

                    ++$i;
                }
            }
        }

        $str .= '</div>';

        if ($bool_modif == true) {
            //SAVE AVIS CIRCUIT
            $str .= '<input type="button" name="send" id="send" value="'._SAVE_CHANGES.'" class="button" ';
            $str .= 'onclick="updateAvisWorkflow('.$res_id.');" /> ';

            //SAVE AS MODEL
            $str .= '<input type="button" name="save" id="save" value="Enregistrer comme modèle" class="button" onclick="$(\'modalSaveAvisModel\').style.display = \'block\';" />';
            $str .= '<div id="modalSaveAvisModel" >';
            $str .= '<h3>'._SAVE_POSITION.' '._AVIS_WORKFLOW.'</h3><br/>';
            $str .= '<label for="titleModel">'._TITLE.'</label> ';
            $str .= '<input type="text" name="titleModel" id="titleModel"/><br/>';
            $str .= '<input type="button" name="saveModel" id="saveModel" value="'._VALIDATE.'" class="button" onclick="saveAvisWorkflowAsModel();" /> ';
            $str .= '<input type="button" name="cancelModel" id="cancelModel" value="'._CANCEL.'" class="button" onclick="$(\'modalSaveAvisModel\').style.display = \'none\';" />';
            $str .= '</div>';
        }
        $str .= '<script>initDragNDropAvis();</script>';

        return $str;
    }

    public function getWorkflow($res_id, $coll_id, $typeList)
    {
        require_once 'modules/entities/class/class_manage_listdiff.php';
        $listdiff = new diffusion_list();
        $roles = $listdiff->list_difflist_roles();
        $circuitAvis = $listdiff->get_listinstance($res_id, false, $typeList);
        if (isset($circuitAvis['copy'])) {
            unset($circuitAvis['copy']);
        }

        return $circuitAvis;
    }

    public function getEntityAvis()
    {
        $db = new Database();

        $stmt = $db->query("SELECT distinct(entities.entity_id) from users, usergroup_content, users_entities,entities WHERE users_entities.user_id = users.user_id and 
            users_entities.primary_entity = 'Y' and users.user_id = usergroup_content.user_id AND entities.entity_id = users_entities.entity_id AND group_id IN 
            (SELECT group_id FROM usergroups_services WHERE service_id = ?)  
            order by entities.entity_id", array('avis_documents'));

        $tab_userentities = array();

        while ($res = $stmt->fetchObject()) {
            array_push($tab_userentities, array('entity_id' => $res->entity_id));
        }
        //print_r($tab_userentities);
        return $tab_userentities;
    }

    public function getUsersAvis($group_id = null)
    {
        $db = new Database();

        if ($group_id != null) {
            $stmt = $db->query("SELECT users.user_id, users.firstname, users.lastname, usergroup_content.group_id,entities.entity_id from users, usergroup_content, users_entities,entities WHERE users_entities.user_id = users.user_id and users.status <> 'DEL' and 
                users_entities.primary_entity = 'Y' and users.user_id = usergroup_content.user_id AND entities.entity_id = users_entities.entity_id AND group_id IN 
                (SELECT group_id FROM usergroups_services WHERE service_id = ? AND group_id = ?)  order by users.lastname", array('avis_documents', $group_id));
        } else {
            $stmt = $db->query("SELECT users.user_id, users.firstname, users.lastname, usergroup_content.group_id,entities.entity_id from users, usergroup_content, users_entities,entities WHERE users_entities.user_id = users.user_id and users.status <> 'DEL' and 
                users_entities.primary_entity = 'Y' and users.user_id = usergroup_content.user_id AND entities.entity_id = users_entities.entity_id AND group_id IN 
                (SELECT group_id FROM usergroups_services WHERE service_id = ?)  
                order by users.lastname", array('avis_documents'));
        }

        $tab_users = array();

        while ($res = $stmt->fetchObject()) {
            array_push($tab_users, array('id' => $res->user_id, 'firstname' => $res->firstname, 'lastname' => $res->lastname, 'group_id' => $res->group_id, 'entity_id' => $res->entity_id));
        }

        return $tab_users;
    }

    public function saveModelWorkflow($id_list, $workflow, $typeList, $title)
    {
    }

    public function saveWorkflow($res_id, $coll_id, $workflow, $typeList)
    {
        require_once 'modules/entities/class/class_manage_listdiff.php';
        $diff_list = new diffusion_list();

        $diff_list->save_listinstance(
                $workflow,
            $typeList,
            $coll_id,
            $res_id,
            $_SESSION['user']['UserId'],
            $_SESSION['user']['primaryentity']['id']
        );
    }

    public function nbAvis($res_id, $coll_id)
    {
        $db = new Database();
        $stmt = $db->query("SELECT listinstance_id from listinstance WHERE res_id= ? and coll_id = ? and item_mode = ? and difflist_type = 'AVIS_CIRCUIT'", array($res_id, $coll_id, 'avis'));

        return $stmt->rowCount();
    }
}
