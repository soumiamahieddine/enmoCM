<?php
/*
*   Copyright 2008-2015 Maarch and Document Image Solutions
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
* @brief Contains the functions to manage visa and notice workflow.
*
* @file
* @author Nicolas Couture <couture@docimsol.com>
* @date $date$
* @version $Revision$
* @ingroup visa
*/

class visa extends dbquery
{
	/***
	* Build Maarch module tables into sessions vars with a xml configuration file
	*
	*
	*/
	public function build_modules_tables() {
		if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
            . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR . "xml"
            . DIRECTORY_SEPARATOR . "config.xml"
        )
        ) {
            $configPath = $_SESSION['config']['corepath'] . 'custom'
                        . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                        . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR
                        . "visa" . DIRECTORY_SEPARATOR . "xml"
                        . DIRECTORY_SEPARATOR . "config.xml";
        } else {
            $configPath = "modules" . DIRECTORY_SEPARATOR . "visa"
                        . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
                        . "config.xml";
        }
		
		$xmlconfig = simplexml_load_file($configPath);
		$conf = $xmlconfig->CONFIG;
		$_SESSION['modules_loaded']['visa']['exeSign'] = (string) $conf->exeSign;
		$_SESSION['modules_loaded']['visa']['reason'] = (string) $conf->reason;
		$_SESSION['modules_loaded']['visa']['location'] = (string) $conf->location;
		$_SESSION['modules_loaded']['visa']['licence_number'] = (string) $conf->licence_number;
		
		$routing_template = (string) $conf->routing_template;
		
		if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
            . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR . "Bordereau_visa_modele.pdf"
        )
        ) {
            $routing_template = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
            . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR . "Bordereau_visa_modele.pdf";
        }

		$_SESSION['modules_loaded']['visa']['routing_template'] = $routing_template;
	}
	
	public function getWorkflow($res_id, $coll_id, $typeList){
		require_once('modules/entities/class/class_manage_listdiff.php');
        $listdiff = new diffusion_list();
        $roles = $listdiff->list_difflist_roles();
        $circuit = $listdiff->get_listinstance($res_id, false, $coll_id, $typeList);
		if (isset($circuit['copy'])) unset($circuit['copy']);
		return $circuit;
	}
	
	public function saveWorkflow($res_id, $coll_id, $workflow, $typeList){
		require_once('modules/entities/class/class_manage_listdiff.php');
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
	
	public function saveModelWorkflow($id_list, $workflow, $typeList, $title){
		require_once('modules/entities/class/class_manage_listdiff.php');
		$diff_list = new diffusion_list();

		
		$diff_list->save_listmodel(
            $workflow, 
			$typeList,
			$id_list,
			$title
        );    
	}
	
	public function deleteWorkflow($res_id, $coll_id){
		$this->connect();
		$this->query("DELETE FROM visa_circuit WHERE res_id=$res_id AND coll_id='$coll_id'");
	}
	
	public function nbVisa($res_id, $coll_id){
		$this->connect();
		$this->query("SELECT listinstance_id from listinstance WHERE res_id=$res_id and coll_id = '$coll_id' and item_mode = 'visa'");
		return $this->nb_result();
	}
	
	public function getCurrentStep($res_id, $coll_id, $listDiffType){
		$this->connect();
		
		$this->query("SELECT sequence, item_mode from listinstance WHERE res_id=$res_id and coll_id = '$coll_id' and difflist_type = '$listDiffType' and process_date ISNULL ORDER BY listinstance_id ASC LIMIT 1");
		
		$res=$this->fetch_object();
		if ($res->item_mode == 'sign'){
			return $this->nbVisa($res_id, $coll_id);
		}
		return $res->sequence;
	}
	
	public function getUsersVis(){
		$requete_users = "SELECT users.user_id, users.firstname, users.lastname from users, usergroup_content WHERE users.user_id = usergroup_content.user_id AND group_id IN (SELECT group_id FROM usergroups_services WHERE service_id = 'visa_documents') ORDER BY users.lastname";
		$db_users = new dbquery();
		$db_users->connect();
		$db_users->query($requete_users);
		$tab_users = array();
		
		
		while($res = $db_users->fetch_object()){
			array_push($tab_users,array('id'=>$res->user_id, 'firstname'=>$res->firstname,'lastname'=>$res->lastname));
		}
		return $tab_users;
	}
	
	public function allUserVised($res_id, $coll_id, $typeList){
		$circuit = $this->getWorkflow($res_id, $coll_id, 'VISA_CIRCUIT');
		if (isset($circuit['visa'])) {
			foreach($circuit['visa']['users'] as $seq=>$step){
				if ($step['process_date'] == ''){
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function getConsigne($res_id, $coll_id, $userId){
		$circuit = $this->getWorkflow($res_id, $coll_id, 'VISA_CIRCUIT');
		if (isset($circuit['visa'])) {
			foreach($circuit['visa']['users'] as $seq=>$step){
				if ($step['user_id'] == $userId){
					return $step['process_comment'];
				}
			}
		}
		foreach($circuit['sign']['users'] as $seq=>$step){
			if ($step['user_id'] == $userId){
				return $step['process_comment'];
			}
		}
		return '';
	}
	
	public function getList($res_id, $coll_id, $bool_modif=false, $typeList){
		$core_tools =new core_tools();
		if ( $typeList == 'VISA_CIRCUIT'){
			$id_tab="tab_visaSetWorkflow";
			$id_form="form_visaSetWorkflow";
		}
		else{
			$id_tab="tab_avisSetWorkflow";
			$id_form="form_avisSetWorkflow";
		}
				
		$circuit = $this->getWorkflow($res_id, $coll_id, $typeList);
		if (!isset($circuit['visa']['users']) && !isset($circuit['sign']['users']) && !$core_tools->test_service('config_visa_workflow', 'visa', false)){
			$str .= '<div class="errorVisa" id="divErrorVisa" name="divErrorVisa">'._EMPTY_USER_LIST.'</div>';
		}
		else{
			require_once('modules/entities/class/class_manage_listdiff.php');
			$diff_list = new diffusion_list();
			$listModels = $diff_list->select_listmodels($typeList);
		
		$str .= '<div align="center">';
		//$str .= '<pre>'.print_r($listModels,true).'</pre>';
		
		$str .= '<div class="errorVisa" id="divErrorVisa" name="divErrorVisa"></div>';
		
		if (!empty($listModels) && $bool_modif){
		$str .= '<select name="modelList" id="modelList" onchange="load_listmodel_visa(this.options[this.selectedIndex], \''.$typeList.'\', \''.$id_tab.'\');">';
		$str .= '<option value="">Sélectionnez un modèle</option>';
		foreach($listModels as $lm){
			$str .= '<option value="'.$lm['object_id'].'">'.$lm['title'].'</option>';
		}
		$str .= '</select>';
		}
		$str .= '<table class="listing spec detailtabricatordebug" cellspacing="0" border="0" id="'.$id_tab.'">';
		$str .= '<thead><tr>';
		$str .= '<th style="width:30%;" align="left" valign="bottom"><span>Visa</span></th>';
		if ($bool_modif){
			$str .= '<th style="width:5%;"></th>';
			$str .= '<th style="width:5%;"></th>';
			$str .= '<th style="width:5%;"></th>';
			$str .= '<th style="width:5%;"></th>';
			$str .= '<th style="width:45%;" align="left" valign="bottom"><span>Consigne</span></th>';
		}
		else{
			$str .= '<th style="width:55%;" align="left" valign="bottom"><span>Consigne</span></th>';
			$str .= '<th style="width:10%;" align="left" valign="bottom"><span>Etat</span></th>';
		}
		$str .= '</tr></thead>';
		$str .= '<tbody>';
		$color = "";
		
		if ($typeList == 'VISA_CIRCUIT'){
			if (!isset($circuit['visa']['users']) && !isset($circuit['sign']['users'])){
				$j=0;
				$str .= '<tr class="col" id="lineVisaWorkflow_'.$j.'">';
				$str .= '<td>';
				if ($bool_modif){
					$tab_users = $this->getUsersVis();
					$str .= '<select id="conseiller_'.$j.'" name="conseiller_'.$j.'" >';
					$str .= '<option value="" >Sélectionnez un utilisateur</option>';
					foreach($tab_users as $user){
						$str .= '<option value="'.$user['id'].'" >'.$user['lastname'].', '.$user['firstname'].'</option>';
					}
					$str .= '</select>';
				}
				$str .= '</td>';
				$str .= '<td><a href="javascript://" id="down_'.$j.'" name="down_'.$j.'" style="visibility:hidden;" onclick="deplacerLigne(0,1,\''.$id_tab.'\')" ><i class="fa fa-arrow-down fa-2x"></i></a></td>';
				$str .= '<td><a href="javascript://" id="up_'.$j.'" name="up_'.$j.'" style="visibility:hidden;" ><i class="fa fa-arrow-up fa-2x"></i></a></td>';
				$str .= '<td><a href="javascript://" onclick="delRow(this.parentNode.parentNode.rowIndex,\''.$id_tab.'\')" id="suppr_'.$j.'" name="suppr_'.$j.'" style="visibility:hidden;" ><i class="fa fa-user-times fa-2x"></i></a></td>';
				$str .= '<td><a href="javascript://" style="visibility:visible;"  id="add_'.$j.'" name="add_'.$j.'" onclick="addRow(\''.$id_tab.'\')" ><i class="fa fa-user-plus fa-2x"></i></a></td>';
				$str .= '<td><input type="text" id="consigne_'.$j.'" name="consigne_'.$j.'" style="width:100%;"/></td>';
				$str .= '</tr>';
			}
			else{
				if (isset($circuit['visa']['users'])){
					foreach($circuit['visa']['users'] as $seq=>$step){
						if($color == ' class="col"') {
							$color = '';
						} else {
							$color = ' class="col"';
						}
						
						$str .= '<tr ' . $color . '>';
						//$str .= '<td>' . $seq+1 . '</td>';
						if ($bool_modif){
							$str .= '<td>';
							$tab_users = $this->getUsersVis();
							$str .= '<select id="conseiller_'.$seq.'" name="conseiller_'.$seq.'" >';
							$str .= '<option value="" >Sélectionnez un utilisateur</option>';
							foreach($tab_users as $user){
								$selected = " ";
								if ($user['id'] == $step['user_id'])
									$selected = " selected";
								$str .= '<option value="'.$user['id'].'" '.$selected.'>'.$user['lastname'].', '.$user['firstname'].'</option>';
							}
							$str .= '</select>';
							
							$str .= '</td>';
							$up = ' style="visibility:visible"';
							$down = ' style="visibility:visible"';
							$add = ' style="visibility:hidden"';
							if ($seq == 0){
								$up = ' style="visibility:hidden"';
							}
							
							//$str .= '<td><img src="static.php?filename=DownUser.png&module=visa" '.$down.' id="down_'.$seq.'" name="down_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2,\''.$id_tab.'\')" /></td>';
							$str .= '<td><a href="javascript://"  '.$down.' id="down_'.$seq.'" name="down_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2,\''.$id_tab.'\')" ><i class="fa fa-arrow-down fa-2x"></i></a></td>';
							//$str .= '<td><img src="static.php?filename=UpUser.png&module=visa" '.$up.' id="up_'.$seq.'" name="up_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1,\''.$id_tab.'\')" /></td>';
							$str .= '<td><a href="javascript://"   '.$up.' id="up_'.$seq.'" name="up_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1,\''.$id_tab.'\')" ><i class="fa fa-arrow-up fa-2x"></i></a></td>';
							$str .= '<td><a href="javascript://" onclick="delRow(this.parentNode.parentNode.rowIndex,\''.$id_tab.'\')" id="suppr_'.$j.'" name="suppr_'.$j.'" style="visibility:visible;" ><i class="fa fa-user-times fa-2x"></i></a></td>';
							$str .= '<td><a href="javascript://" '.$add.'  id="add_'.$seq.'" name="add_'.$seq.'" onclick="addRow(\''.$id_tab.'\')" ><i class="fa fa-user-plus fa-2x"></i></a></td>';
							$str .= '<td><input type="text" id="consigne_'.$seq.'" name="consigne_'.$seq.'" value="'.$step['process_comment'].'" style="width:100%;"/></td>';							
						}
						else{
							$str .= '<td>'.$step['firstname'].' '.$step['lastname'];
							$str .= '</td>';
							$str .= '<td>'.$step['process_comment'].'</td>';	
							if ($step['process_date'] != '') $str .= '<td><i class="fa fa-check fa-2x"></i></td>';		
							elseif ($step['user_id'] == $_SESSION['user']['UserId']) $str .= '<td><i class="fa fa-spinner fa-2x"></i></td>';		
							else $str .= '<td></td>';		
						}
						$str .= '</tr>';
					}
				}
					//ajout signataire
					
					$seq = count ($circuit['visa']['users']);
					
					if($color == ' class="col"') {
						$color = '';
					} else {
						$color = ' class="col"';
					}
					
					$str .= '<tr ' . $color . '>';
					//$str .= '<td>' . $seq+1 . '</td>';
					if ($bool_modif){
						$str .= '<td>';
						$tab_users = $this->getUsersVis();
						$str .= '<select id="conseiller_'.$seq.'" name="conseiller_'.$seq.'" >';
						$str .= '<option value="" >Sélectionnez un utilisateur</option>';
						foreach($tab_users as $user){
							$selected = " ";
							if ($user['id'] == $circuit['sign']['users'][0]['user_id'])
								$selected = " selected";
							$str .= '<option value="'.$user['id'].'" '.$selected.'>'.$user['lastname'].', '.$user['firstname'].'</option>';
						}
						$str .= '</select>';
						
						$str .= '</td>';
						$up = ' style="visibility:visible"';
						$down = ' style="visibility:hidden"';
						$add = ' style="visibility:visible"';
											
						//$str .= '<td><img src="static.php?filename=DownUser.png&module=visa" '.$down.' id="down_'.$seq.'" name="down_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2,\''.$id_tab.'\')" /></td>';
						//$str .= '<td><img src="static.php?filename=UpUser.png&module=visa" '.$up.' id="up_'.$seq.'" name="up_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1,\''.$id_tab.'\')" /></td>';
						$str .= '<td><a href="javascript://"  '.$down.' id="down_'.$seq.'" name="down_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex+2,\''.$id_tab.'\')" ><i class="fa fa-arrow-down fa-2x"></i></a></td>';
						$str .= '<td><a href="javascript://"   '.$up.' id="up_'.$seq.'" name="up_'.$seq.'" onclick="deplacerLigne(this.parentNode.parentNode.rowIndex, this.parentNode.parentNode.rowIndex-1,\''.$id_tab.'\')" ><i class="fa fa-arrow-up fa-2x"></i></a></td>';
						$str .= '<td><a href="javascript://" onclick="delRow(this.parentNode.parentNode.rowIndex,\''.$id_tab.'\')" id="suppr_'.$j.'" name="suppr_'.$j.'" style="visibility:visible;" ><i class="fa fa-user-times fa-2x"></i></a></td>';
						$str .= '<td><a href="javascript://" '.$add.'  id="add_'.$seq.'" name="add_'.$seq.'" onclick="addRow(\''.$id_tab.'\')" ><i class="fa fa-user-plus fa-2x"></i></a></td>';
						$str .= '<td><input type="text" id="consigne_'.$seq.'" name="consigne_'.$seq.'" value="'.$circuit['sign']['users'][0]['process_comment'].'" style="width:100%;"/></td>';							
					}
					else{
						$str .= '<td>'.$circuit['sign']['users'][0]['firstname'].' '.$circuit['sign']['users'][0]['lastname'];
						$str .= '</td>';
						$str .= '<td>'.$circuit['sign']['users'][0]['process_comment'].'</td>';	
						if ($circuit['sign']['users'][0]['process_date'] != '') $str .= '<td><i class="fa fa-check fa-2x"></i></td>';		
						elseif ($circuit['sign']['users'][0]['user_id'] == $_SESSION['user']['UserId']) $str .= '<td><i class="fa fa-spinner fa-2x"></i></td>';		
						else $str .= '<td></td>';		
					}
					$str .= '</tr>';
			}
		}
		
		$str .= '</tbody>';
		$str .= '</table>';
		if ($bool_modif){
			$str .= '<input type="button" name="send" id="send" value="Sauvegarder" class="button" onclick="saveVisaWorkflow(\''.$res_id.'\', \''.$coll_id.'\', \''.$id_tab.'\');" /> ';
			$str .= '<input type="button" name="save" id="save" value="Enregistrer comme modèle" class="button" onclick="$(\'modalSaveVisaModel\').style.display = \'block\';" />';
			
		
			
			$str .= '<div id="modalSaveVisaModel" >';
			$str .= '<h3>Sauvegarder le circuit de visa</h3>';
			$str .= '<input type="hidden" value="'.$typeList . '_' . strtoupper(base_convert(date('U'), 10, 36)).'" name="objectId_input" id="objectId_input"/><br/>';
			$str .= '<label for="titleModel">Titre</label> ';
			$str .= '<input type="text" name="titleModel" id="titleModel"/><br/>';
			$str .= '<input type="button" name="saveModel" id="saveModel" value="'._VALIDATE.'" class="button" onclick="saveVisaModel(\''.$id_tab.'\');" /> ';
			$str .= '<input type="button" name="cancelModel" id="cancelModel" value="'._CANCEL.'" class="button" onclick="$(\'modalSaveVisaModel\').style.display = \'none\';" />';
			$str .= '</div>';
		}
		$str .= '</div>';
		}
		return $str;
	}
}

/* EXEMPLE TAB VISA_CIRCUIT

Array
(
    [coll_id] => letterbox_coll
    [res_id] => 190
    [difflist_type] => entity_id
    [sign] => Array
        (
            [users] => Array
                (
                    [0] => Array
                        (
                            [user_id] => sgros
                            [lastname] => GROS
                            [firstname] => Sébastien
                            [entity_id] => CHEFCABINET
                            [entity_label] => Chefferie
                            [visible] => Y
                            [viewed] => 0
                            [difflist_type] => VISA_CIRCUIT
                            [process_date] => 
                            [process_comment] => 
                        )

                )

        )

    [visa] => Array
        (
            [users] => Array
                (
                    [0] => Array
                        (
                            [user_id] => sbes
                            [lastname] => BES
                            [firstname] => Stéphanie
                            [entity_id] => CHEFCABINET
                            [entity_label] => Chefferie
                            [visible] => Y
                            [viewed] => 0
                            [difflist_type] => VISA_CIRCUIT
                            [process_date] => 
                            [process_comment] => 
                        )

                    [1] => Array
                        (
                            [user_id] => fbenrabia
                            [lastname] => BENRABIA
                            [firstname] => Fadela
                            [entity_id] => POLESOCIAL
                            [entity_label] => Pôle social
                            [visible] => Y
                            [viewed] => 0
                            [difflist_type] => VISA_CIRCUIT
                            [process_date] => 
                            [process_comment] => 
                        )

                    [2] => Array
                        (
                            [user_id] => bpont
                            [lastname] => PONT
                            [firstname] => Brieuc
                            [entity_id] => POLEAFFAIRESETRANGERES
                            [entity_label] => Pôle affaires étrangères
                            [visible] => Y
                            [viewed] => 0
                            [difflist_type] => VISA_CIRCUIT
                            [process_date] => 
                            [process_comment] => 
                        )

                )

        )

)

*/
?>