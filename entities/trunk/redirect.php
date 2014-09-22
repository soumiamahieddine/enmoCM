<?php
$confirm = false;
$etapes = array('form');
$frm_width='355px';
$frm_height = '800px';
require("modules/entities/entities_tables.php");
require_once("modules/entities/class/EntityControler.php");
require_once('modules/entities/class/class_manage_entities.php');;


 function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
 {
    $ent = new entity();
    $entity_ctrl = new EntityControler();
    $services = array();
    $servicesCompare = array();
    $db = new dbquery();
    $db->connect();
    $labelAction = '';
    if ($id_action <> '') {
        $db->query("select label_action from actions where id = " . $id_action);
        $resAction = $db->fetch_object();
        $labelAction = $db->show_string($resAction->label_action);
    }
    
    //print_r($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']);
    preg_match("'^ ,'",$_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities'], $out);
    if(count($out[0]) == 1) {
        $_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities'] = substr($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities'], 2, strlen($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']));
    }
    //print_r($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']);
    if(!empty($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']))
    {
        $db->query("select entity_id, entity_label from ".ENT_ENTITIES." where entity_id in (".$_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities'].") and enabled= 'Y' order by entity_label");
        while($res = $db->fetch_object())
        {
            array_push($services, array( 'ID' => $res->entity_id, 'LABEL' => $db->show_string($res->entity_label)));
            array_push($servicesCompare, $res->entity_id);
        }
    }
    $users = array();
    if(!empty($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['users_entities']) )
    {
        $db->query("select distinct ue.user_id, u.lastname, u.firstname from ".ENT_USERS_ENTITIES." ue, ".$_SESSION['tablename']['users']." u where ue.entity_id in (".$_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['users_entities'].") and u.user_id = ue.user_id and (u.status = 'OK' or u.status = 'ABS') order by u.lastname asc");
        while($res = $db->fetch_object())
        {
            array_push($users, array( 'ID' => $res->user_id, 'NOM' => $db->show_string($res->lastname), "PRENOM" => $db->show_string($res->firstname)));
        }
    }

    $frm_str = '<div id="frm_error_'.$id_action.'" class="error"></div>';
    if ($labelAction <> '') {
        $frm_str .= '<h2 class="title">' . $labelAction . ' ' . _NUM;
    } else {
        $frm_str .= '<h2 class="title">'._REDIRECT_MAIL.' '._NUM;
    }
    $values_str = '';
    for($i=0; $i < count($values);$i++)
    {
        $values_str .= $values[$i].', ';
    }
    $values_str = preg_replace('/, $/', '', $values_str);
    $frm_str .= $values_str;
    $frm_str .= '</h2><br/><br/>';
    if(!empty($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['entities']))
    {
        $EntitiesIdExclusion = array();
        $entities = $entity_ctrl->getAllEntities();
        $countEntities = count($entities);
        //var_dump($entities);
        for ($cptAllEnt = 0;$cptAllEnt<$countEntities;$cptAllEnt++) {
            if (!is_integer(array_search($entities[$cptAllEnt]->__get('entity_id'), $servicesCompare))) {
                array_push($EntitiesIdExclusion, $entities[$cptAllEnt]->__get('entity_id'));
            }
        }
        
        $allEntitiesTree= array();
        $allEntitiesTree = $ent->getShortEntityTreeAdvanced(
            $allEntitiesTree, 'all', '', $EntitiesIdExclusion, 'all'
        );
        //var_dump($allEntitiesTree);
        $frm_str .= '<hr />';
        $frm_str .='<div id="form2">';
        $frm_str .= '<form name="frm_redirect_dep" id="frm_redirect_dep" method="post" class="forms" action="#">';
        $frm_str .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
                $frm_str .='<p>';
                    $frm_str .= '<label><b>'._REDIRECT_TO_OTHER_DEP.' :</b></label>';
                    $frm_str .= '<select name="department" id="department" onchange="change_entity(this.options[this.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=load_listinstance'.'\', \'diff_list_div_redirect\', \'redirect\');">';
                        $frm_str .='<option value="">'._CHOOSE_DEPARTMENT.'</option>';
                       /*for($i=0; $i < count($services); $i++)
                       {
                            $frm_str .='<option value="'.$services[$i]['ID'].'" >'.$db->show_string($services[$i]['LABEL']).'</option>';
                       }*/
                       $countAllEntities = count($allEntitiesTree);
                        for ($cptEntities = 0;$cptEntities < $countAllEntities;$cptEntities++) {
                            if (!$allEntitiesTree[$cptEntities]['KEYWORD']) {
                                $frm_str .= '<option data-object_type="entity_id" value="' . $allEntitiesTree[$cptEntities]['ID'] . '"';
                                if ($allEntitiesTree[$cptEntities]['DISABLED']) {
                                    $frm_str .= ' disabled="disabled" class="disabled_entity"';
                                } else {
                                     //$frm_str .= ' style="font-weight:bold;"';
                                }
                                $frm_str .=  '>' 
                                    .  $ent->show_string($allEntitiesTree[$cptEntities]['SHORT_LABEL']) 
                                    . '</option>';
                            }
                        }
                    $frm_str .='</select>';
                    $frm_str .=' <input type="button" name="redirect_dep" value="'._REDIRECT.'" id="redirect_dep" class="button" onclick="valid_action_form( \'frm_redirect_dep\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$values_str.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');" />';

                $frm_str .= '<div id="diff_list_div_redirect" class="scroll_div" style="height:450px;display:none;"></div>';
                $frm_str .='</p>';
            $frm_str .='</form>';
        $frm_str .='</div>';
    }
    if(!empty($_SESSION['user']['redirect_groupbasket'][$_SESSION['current_basket']['id']][$id_action]['users_entities']))
    {
        $frm_str .='<hr />';
            $frm_str .='<div id="form3">';
                $frm_str .= '<form name="frm_redirect_user" id="frm_redirect_user" method="post" class="forms" action="#">';
                $frm_str .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
                $frm_str .='<p>';
                    $frm_str .='<label><b>'._REDIRECT_TO_USER.' :</b></label>';
                    $frm_str .='<select name="user" id="user">';
                        $frm_str .='<option value="">'._CHOOSE_USER2.'</option>';
                        for($i=0; $i < count($users); $i++)
                       {
                        $frm_str .='<option value="'.$users[$i]['ID'].'">'.$users[$i]['NOM'].' '.$users[$i]['PRENOM'].'</option>';
                       }
                    $frm_str .='</select>';
                    $frm_str .=' <input type="button" name="redirect_user" id="redirect_user" value="'
                        ._REDIRECT
                        . '" class="button" onclick="valid_action_form( \'frm_redirect_user\', \''
                        . $path_manage_action . '\', \'' . $id_action . '\', \'' . $values_str . '\', \'' . $table . '\', \'' . $module . '\', \'' . $coll_id . '\', \'' . $mode . '\');"  />';
                $frm_str .='</p>';
            $frm_str .='</form>';
        $frm_str .='</div>';
    }
    $frm_str .='<hr />';
    $frm_str .='<div align="center">';
            $frm_str .='<input type="button" name="cancel" id="cancel" class="button"  value="'._CANCEL.'" onclick="destroyModal(\'modal_'.$id_action.'\');"/>';
    $frm_str .='</div>';
    return addslashes($frm_str);
 }

 function check_form($form_id,$values)
 {
    if($form_id == 'frm_redirect_dep')
    {
        $dep = get_value_fields($values, 'department');
        if($dep == '')
        {
            $_SESSION['action_error'] = _MUST_CHOOSE_DEP;
            return false;
        }
        else
        {
            return true;
        }
    }
    else if($form_id == 'frm_redirect_user')
    {
        $user = get_value_fields($values, 'user');
        if($user == '')
        {
            $_SESSION['action_error'] = _MUST_CHOOSE_USER;
            return false;
        }
        else
        {
            return true;
        }
    }
    else
    {
        $_SESSION['action_error'] = _FORM_ERROR;
        return false;
    }
 }

function manage_form($arr_id, $history, $id_action, $label_action, $status, $coll_id, $table, $values_form )
{
    /*
        Redirect to dep:
        $values_form = array (size=3)
          0 => 
            array (size=2)
              'ID' => string 'chosen_action' (length=13)
              'VALUE' => string 'end_action' (length=10)
          1 => 
            array (size=2)
              'ID' => string 'department' (length=10)
              'VALUE' => string 'DGA' (length=3)
          2 => 
            array (size=2)
              'ID' => string 'redirect_dep' (length=12)
              'VALUE' => string 'Rediriger' (length=9)
        
        Redirect to user:
        $values_form = array (size=3)
          0 => 
            array (size=2)
              'ID' => string 'chosen_action' (length=13)
              'VALUE' => string 'end_action' (length=10)
          1 => 
            array (size=2)
              'ID' => string 'user' (length=4)
              'VALUE' => string 'aackermann' (length=10)
          2 => 
            array (size=2)
              'ID' => string 'redirect_user' (length=13)
              'VALUE' => string 'Rediriger' (length=9)
    
    */
    
    if(empty($values_form) || count($arr_id) < 1) 
        return false;
    
    require_once('modules/entities/class/class_manage_listdiff.php');
    $diffList = new diffusion_list();
    
    $db = new dbquery();
    $db->connect();
    
    $formValues = array();
    for($i=0; $i<count($values_form); $i++) {
        $formValue = $values_form[$i];
        $id = $formValue['ID'];
        $value = $formValue['VALUE'];
        $formValues[$id] = $value;
    }
    
    # 1 : Redirect to user :
    #   - create new listinstance from scratch with only dest user
    #   - do not change destination
    if(isset($formValues['redirect_user'])) {
        $userId = $formValues['user'];
        
        # Select new_dest user info
        $db->query(
            "select u.user_id, u.firstname, u.lastname, e.entity_id, e.entity_label "
            . "FROM " . $_SESSION['tablename']['users'] . " u, " . ENT_ENTITIES . " e, "
            . ENT_USERS_ENTITIES . " ue WHERE u.status <> 'DEL' and u.enabled = 'Y' and"
            . " e.entity_id = ue.entity_id and u.user_id = ue.user_id and"
            . " e.enabled = 'Y' and ue.primary_entity='Y' and u.user_id = '" . $userId . "'"
        );
        $user = $db->fetch_object();
        
        # Create new listinstance
        $_SESSION['redirect']['diff_list'] = array();
        $_SESSION['redirect']['diff_list']['difflist_type'] = 'entity_id';
        $_SESSION['redirect']['diff_list']['dest']['users'][0] =    
            array(
                'user_id' => $userId,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'entity_id' => $user->entity_id,
                'viewed' => 0,
                'visible' => 'Y',
                'difflist_type' => 'entity_id'
            );
        
        $message = _REDIRECT_TO_USER_OK . ' ' . $userId;
    }
    # 2 : Redirect to departement (+ dest user)
    #   - listinstance has laready been loaded when selecting entity
    #   - get entity_id that will update destination
    elseif(isset($formValues['redirect_dep'])) {
        $entityId = $formValues['department'];
        $message = _REDIRECT_TO_DEP_OK . " " . $entityId;
    }
    
    # 1 + 2 :
    #   - update dest_user
    #   - move former dest user to copy if requested
    #   - finally save listinstance 
    for($i=0; $i<count($arr_id); $i++) {
        $res_id = $arr_id[$i];
        # update dest_user
        $new_dest = $_SESSION['redirect']['diff_list']['dest']['users'][0]['user_id'];
        if($new_dest) {
            $db->query("update ".$table." set dest_user = '".$new_dest."' where res_id = ".$res_id);
            # If new dest was in other roles, get number of views
            $db->query(
                "select viewed"
                . " from " . $_SESSION['tablename']['ent_listinstance'] 
                . " where coll_id = '". $coll_id ."' and res_id = " . $res_id . " and item_type = 'user_id' and item_id = '" . $new_dest . "'"
            );
            $res = $db->fetch_object();
            $viewed = $res->viewed;
            $_SESSION['redirect']['diff_list']['dest']['users'][0]['viewed'] = (integer)$viewed;
        }
        
        # Update destination if needed
        if($entityId)
            $db->query("update ".$table." set destination = '".$entityId."' where res_id = ".$res_id); 
		
		# Put all existing copies in copy
		# Get old copies for users
		$db->query(
			"select * "
			. " from " . $_SESSION['tablename']['ent_listinstance'] 
			. " where coll_id = '". $coll_id ."' and res_id = " . $res_id . " and item_type = 'user_id' and item_mode = 'cc'"
		);
		if (!is_array($_SESSION['redirect']['diff_list']['copy'])) {
			$_SESSION['redirect']['diff_list']['copy'] = array();
		}
		if (!is_array($_SESSION['redirect']['diff_list']['copy']['users'])) {
			$_SESSION['redirect']['diff_list']['copy']['users'] = array();
		}
		if (!is_array($_SESSION['redirect']['diff_list']['copy']['entities'])) {
			$_SESSION['redirect']['diff_list']['copy']['entities'] = array();
		}
		while ($old_copiesU = $db->fetch_object()) {
			$found = false;
			for ($cptU=0;$cptU<count($_SESSION['redirect']['diff_list']['copy']['users']);$cptU++) {
				if ($_SESSION['redirect']['diff_list']['copy']['users'][$cptU]['user_id'] == $old_copiesU->item_id) {
					//echo $_SESSION['redirect']['diff_list']['copy']['users'][$cptU]['user_id'] . " found" . PHP_EOL;
					$found = true;
					break;
				}
			}
			//if not found, add the old copy in the new diff list
			if (!$found) {
				//echo $old_copiesU->item_id . " not found" . PHP_EOL;
				array_push(
					$_SESSION['redirect']['diff_list']['copy']['users'], 
					array(
						'user_id' => $old_copiesU->item_id, 
						'viewed' => (integer)$old_copiesU->viewed,
						'visible' => 'Y',
						'difflist_type' => $_SESSION['redirect']['diff_list']['difflist_type']
					)
				);
			}
		}
		# Get old copies for entities
		$db->query(
			"select * "
			. " from " . $_SESSION['tablename']['ent_listinstance'] 
			. " where coll_id = '". $coll_id ."' and res_id = " . $res_id . " and item_type = 'entity_id' and item_mode = 'cc'"
		);
		while ($old_copiesE = $db->fetch_object()) {
			$found = false;
			for ($cptE=0;$cptE<count($_SESSION['redirect']['diff_list']['copy']['entities']);$cptE++) {
				if ($_SESSION['redirect']['diff_list']['copy']['entities'][$cptE]['entity_id'] == $old_copiesE->item_id) {
					$found = true;
					break;
				}
			}
			//if not found, add the old copy in the new diff list
			if (!$found) {
				array_push(
					$_SESSION['redirect']['diff_list']['copy']['entities'], 
					array(
						'entity_id' => $old_copiesE->item_id, 
						'visible' => 'Y',
					)
				);
			}
		}
        
        # If feature activated, put old dest in copy
        if($_SESSION['features']['dest_to_copy_during_redirection'] == 'true') {
            # Get old dest
            $db->query(
                "select * "
                . " from " . $_SESSION['tablename']['ent_listinstance'] 
                . " where coll_id = '". $coll_id ."' and res_id = " . $res_id . " and item_type = 'user_id' and item_mode = 'dest'"
            );
            //$db->show();
            //exit();
            $old_dest = $db->fetch_object();
            
            if($old_dest && isset($_SESSION['redirect']['diff_list']['copy']['users'])) {
                # try to find old dest in copies already
                $found = false;
                for($ci=0; $ci<count($_SESSION['redirect']['diff_list']['copy']['users']);$ci++) {
                    
                    # If in copies before, add number of views as dest to number of views as copy
					if($_SESSION['redirect']['diff_list']['copy']['users'][$ci]['user_id'] == $old_dest->item_id) {
                        $found = true;
                        $_SESSION['redirect']['diff_list']['copy']['users'][$ci]['viewed'] = 
                            $_SESSION['redirect']['diff_list']['copy']['users'][$ci]['viewed'] + (integer)$old_dest->viewed;
                        break;
                    }
                }
                
                //re-built session without dest in copy
                $tab=array();
                for($ci=0; $ci<count($_SESSION['redirect']['diff_list']['copy']['users']);$ci++) {
                    if($_SESSION['redirect']['diff_list']['copy']['users'][$ci]['user_id'] != $new_dest){
                    array_push(
                        $tab, 
                        array(
						'user_id' => $_SESSION['redirect']['diff_list']['copy']['users'][$ci]['user_id'], 
						'viewed' => (integer)$_SESSION['redirect']['diff_list']['copy']['users'][$ci]['viewed'],
						'visible' => 'Y',
						'difflist_type' => $_SESSION['redirect']['diff_list']['copy']['users'][$ci]['viewed']
                        )
                    );
                    }
                }
                $_SESSION['redirect']['diff_list']['copy']['users']=$tab;
                
                if(!$found) {
                    array_push(
                        $_SESSION['redirect']['diff_list']['copy']['users'], 
                        array(
						'user_id' => $old_dest->item_id, 
						'viewed' => (integer)$old_dest->viewed,
						'visible' => 'Y',
						'difflist_type' => $_SESSION['redirect']['diff_list']['difflist_type']
                        )
                    );
                }
            }
        }
        # Save listinstance
        $diffList->save_listinstance(
            $_SESSION['redirect']['diff_list'], 
            $_SESSION['redirect']['diff_list']['difflist_type'],
            $coll_id, 
            $res_id, 
            $_SESSION['user']['UserId']
        );           
    }
    
    # Pb with action chain : main action page is saved after this. 
    #   if process, $_SESSION['process']['diff_list'] will override this one
    
    $_SESSION['process']['diff_list'] = $_SESSION['redirect']['diff_list'];
    $_SESSION['action_error'] = $message;
    return array('result' => implode('#', $arr_id), 'history_msg' => $message);
    
    #
    # OLD SCRIPT
    #
    $list = new diffusion_list();
    $arr_list = '';

    for($j=0; $j<count($values_form); $j++)
    {
        $queryEntityLabel = "SELECT entity_label FROM entities WHERE entity_id='".$values_form[$j]['VALUE']."'";
        $db->query($queryEntityLabel);
        while ($entityLabel = $db->fetch_object()) {
            $zeEntityLabel = $entityLabel->entity_label;
        }
        $msg = _TO." : ".$db->protect_string_db($zeEntityLabel)." (".$db->protect_string_db($values_form[$j]['VALUE']).")";
        if($values_form[$j]['ID'] == "department")
        {
            for($i=0; $i < count($arr_id); $i++)
            {
                $arr_list .= $arr_id[$i].'#';
                $db2 = new dbquery();
                $db2->connect();
                $db->query("update ".$table." set destination = '".$db->protect_string_db($values_form[$j]['VALUE'])."' where res_id = ".$arr_id[$i]);
                if(isset($_SESSION['redirect']['diff_list']['dest']['users'][0]['user_id']) && !empty($_SESSION['redirect']['diff_list']['dest']['users'][0]['user_id']))
                {
                    $db->query("update ".$table." set dest_user = '".$db->protect_string_db($_SESSION['redirect']['diff_list']['dest']['user_id'])."' where res_id = ".$arr_id[$i]);
                }
                $newDestViewed = 0;
                // Récupère le nombre de fois où le futur destinataire principal a vu le document
                $db->query("select viewed from ".$_SESSION['tablename']['ent_listinstance']." where coll_id = '".$db->protect_string_db($coll_id)."' and res_id = ".$arr_id[$i]." and item_type = 'user_id' and item_id = '".$_SESSION['redirect']['diff_list']['dest']['users'][0]['user_id']."'");
                //$db->show();
                $res = $db->fetch_object();
                if($res->viewed <> "")
                {
                    $_SESSION['redirect']['diff_list']['dest']['users'][0]['viewed'] = $res->viewed;
                    $newDestViewed = $res->viewed;
                }
                if($_SESSION['features']['dest_to_copy_during_redirection'] == 'true')
                {
                    $lastDestViewed = 0;
                    // Récupère le nombre de fois où l'ancien destinataire principal a vu le document
                    $db->query("select viewed from ".$_SESSION['tablename']['ent_listinstance']." where coll_id = '".$db->protect_string_db($coll_id)."' and res_id = ".$arr_id[$i]." and item_type = 'user_id' and item_mode = 'dest'");
                    //$db->show();
                    $res = $db->fetch_object();
                    if($res->viewed <> "")
                    {
                        $lastDestViewed = $res->viewed;
                    }
                    for($cptCopy=0;$cptCopy<count($_SESSION['redirect']['diff_list']['copy']['users']);$cptCopy++)
                    {
                        if($_SESSION['redirect']['diff_list']['copy']['users'][$cptCopy]['user_id'] == $_SESSION['user']['UserId'])
                        {
                            $_SESSION['redirect']['diff_list']['copy']['users'][$cptCopy]['viewed'] = $lastDestViewed;
                        }
                    }
                    array_push($_SESSION['redirect']['diff_list']['copy']['users'], array('user_id' => $_SESSION['user']['UserId'], 'lastname' => $_SESSION['user']['LastName'], 'firstname' => $_SESSION['user']['FirstName'], 'entity_id' => $_SESSION['user']['primaryentity']['id'], 'viewed' => $lastDestViewed));
                }
                $params = array('mode'=> 'listinstance', 'table' => $_SESSION['tablename']['ent_listinstance'], 'coll_id' => $coll_id, 'res_id' => $arr_id[$i], 'user_id' => $_SESSION['user']['UserId'], 'concat_list' => true);
                //print_r($_SESSION['redirect']['diff_list']);exit();
                $list->load_list_db($_SESSION['redirect']['diff_list'], $params);
            }
            $_SESSION['action_error'] = _REDIRECT_TO_DEP_OK;

            return array('result' => $arr_list, 'history_msg' => $msg );
        }
        elseif($values_form[$j]['ID'] == "user")
        {
            for($i=0;$i<count($arr_id);$i++)
            {
                // Update listinstance
                $difflist['dest'] = array();
                $difflist['copy'] = array();
                $difflist['copy']['users'] = array();
                $difflist['copy']['entities'] = array();
                $difflist['dest']['users'][0]['user_id'] = $values_form[$j]['VALUE'];
                $arr_list .= $arr_id[$i].'#';
                // Récupère le nombre de fois où le futur destinataire principal a vu le document
                $db->query("select viewed from ".$_SESSION['tablename']['ent_listinstance']." where coll_id = '".$db->protect_string_db($coll_id)."' and res_id = ".$arr_id[$i]." and item_type = 'user_id' and item_id = '".$difflist['dest']['users'][0]['user_id']."'");
                //$db->show();
                $res = $db->fetch_object();
                $newDestViewed = 0;
                if($res->viewed <> "")
                {
                    $difflist['dest']['users'][0]['viewed'] = $res->viewed;
                    $newDestViewed = $res->viewed;
                }
                // Récupère le nombre de fois où l'ancien destinataire principal a vu le document
                $db->query("select viewed from ".$_SESSION['tablename']['ent_listinstance']." where coll_id = '".$db->protect_string_db($coll_id)."' and res_id = ".$arr_id[$i]." and item_type = 'user_id' and item_mode = 'dest'");
                //$db->show();
                $res = $db->fetch_object();
                $lastDestViewed = 0;
                if($res->viewed <> "")
                {
                    $lastDestViewed = $res->viewed;
                }
                // Update dest_user in res table
                $db->query("update ".$table." set dest_user = '".$db->protect_string_db($values_form[$j]['VALUE'])."' where res_id = ".$arr_id[$i]);
                $list->set_main_dest($values_form[$j]['VALUE'], $coll_id, $arr_id[$i], 'DOC', 'user_id', $newDestViewed);
                if($_SESSION['features']['dest_to_copy_during_redirection'] == 'true')
                {
                    array_push($difflist['copy']['users'],array('user_id' => $_SESSION['user']['UserId'], 'lastname' => $_SESSION['user']['LastName'], 'firstname' => $_SESSION['user']['FirstName'], 'entity_id' => $_SESSION['user']['primaryentity']['id'], 'viewed' => $lastDestViewed));
                }
                $params = array('mode'=> 'listinstance', 'table' => $_SESSION['tablename']['ent_listinstance'], 'coll_id' => $coll_id, 'res_id' => $arr_id[$i], 'user_id' => $_SESSION['user']['UserId'], 'concat_list' => true);
                $list->load_list_db($difflist, $params);
            }
            $_SESSION['action_error'] = _REDIRECT_TO_USER_OK;
            return array('result' => $arr_list, 'history_msg' => $msg);
        }
    }
    return false;
}

function manage_unlock($arr_id, $history, $id_action, $label_action, $status, $coll_id, $table)
{
    $db = new dbquery();
    $db->connect();
    for($i=0; $i<count($arr_id );$i++)
    {
        $req = $db->query("update ".$table. " set video_user = '', video_time = 0 where res_id = ".$arr_id[$i], true);

        if(!$req)
        {
            $_SESSION['action_error'] = _SQL_ERROR;
            return false;
        }
    }
    return true;
 }

 /**
 * Get the value of a given field in the values returned by the form
 *
 * @param $values Array Values of the form to check
 * @param $field String the field
 * @return String the value, false if the field is not found
 **/
function get_value_fields($values, $field)
{
    for($i=0; $i<count($values);$i++)
    {
        if($values[$i]['ID'] == $field)
        {
            return  $values[$i]['VALUE'];
        }
    }
    return false;
}

?>
