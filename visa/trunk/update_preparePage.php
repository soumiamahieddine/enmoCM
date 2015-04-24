<?php
/* FONCTIONS */

function getHistoryActions($res_id){
	$db=new dbquery();
	$db->connect();
	$db->query("SELECT * from history where record_id='$res_id' and event_type LIKE 'ACTION#%'");
	$tab_histo = array();
	while($res = $db->fetch_object()){
		array_push($tab_histo, $res);
	}
	return $tab_histo;
}

function get_rep_path($res_id, $coll_id)
{
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docservers_controler.php");
	$docserverControler = new docservers_controler();
    $sec =new security();
    $view = $sec->retrieve_view_from_coll_id($coll_id);
    if(empty($view))
    {
        $view = $sec->retrieve_table_from_coll($coll_id);
    }
    $db = new dbquery();
    $db->connect();

    $db->query("select docserver_id, path, filename from ".$view." where res_id = ".$res_id);
    $res = $db->fetch_object();
    $docserver_id = $res->docserver_id;
	
	
	$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver_id."'");
    $res = $db->fetch_object();
    $docserver_path = $res->path_template;
	$db->query("select filename, path,title,res_id,res_id_version  from res_view_attachments where res_id_master = " . $res_id . " AND status <> 'OBS' AND status <> 'DEL' and attachment_type IN ('response_project','signed_response') order by creation_date asc");
	$array_reponses = array();
	$cpt_rep = 0;
	while ($res2 = $db->fetch_object()){
		$filename=$res2->filename;
		$path = preg_replace('/#/', DIRECTORY_SEPARATOR, $res2->path);
		$filename_pdf = str_replace(pathinfo($filename, PATHINFO_EXTENSION), "pdf",$filename);
		if (is_file($docserver_path.$path.$filename_pdf)){
			$array_reponses[$cpt_rep]['path'] = $docserver_path.$path.$filename_pdf;
			$array_reponses[$cpt_rep]['title'] = $res2->title;
			if ($res2->res_id_version == 0){
				$array_reponses[$cpt_rep]['res_id'] = $res2->res_id;
				$array_reponses[$cpt_rep]['is_version'] = 0;
			}
			else{
				$array_reponses[$cpt_rep]['res_id'] = $res2->res_id_version;
				$array_reponses[$cpt_rep]['is_version'] = 1;
			}
			$cpt_rep++;
		}
	}
    return $array_reponses;
}

function getInfosAction($action_id){
	$db=new dbquery();
	$db->connect();
	$db->query("SELECT id_status, status.label_status from actions,status where actions.id_status = status.id and actions.id=$action_id");
	$action = array();
	$res = $db->fetch_object();
	$action['status'] = $res->id_status;
	$action['label_status'] = $res->label_status;
	$db->query("SELECT label_action from actions where actions.id=$action_id");
	$res = $db->fetch_object();
	$action['label'] = $res->label_action;
	return $action;
}

function getInfosUser($user_id){
	$db=new dbquery();
	$db->connect();
	$db->query("SELECT firstname, lastname, group_id, entity_id from users u, usergroup_content uc, users_entities ue where u.user_id = '$user_id' AND uc.user_id = u.user_id AND ue.user_id = u.user_id AND ue.primary_entity='Y' AND uc.primary_group = 'Y' ");
	$user = array();
	$res = $db->fetch_object();
	$user['prenom'] = $res->firstname;
	$user['nom'] = $res->lastname;
	$user['groupe'] = $res->group_id;
	$user['entite'] = $res->entity_id;
	return $user;
}
/*************/

$res_id = $_REQUEST['res_id'];
$coll_id = $_REQUEST['coll_id'];

require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');

$core =new core_tools();

$data = get_general_data($coll_id, $res_id, 'minimal');
			
/* Partie centrale */

// AVANCEMENT
$avancement_html = '';
$avancement_html .= '<h2>Workflow</h2>';
$visa = new visa();
$workflow = $visa->getWorkflow($res_id, $coll_id, 'VISA_CIRCUIT');
$current_step = $visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');

$tab_histo = getHistoryActions($res_id);
$avancement_html .= '<table class="listing spec detailtabricatordebug" cellspacing="0" border="0" id="tab_visaWorkflow">';
$avancement_html .= '<thead><tr>';
$avancement_html .= '<th style="width:15%;" align="left" valign="bottom"><span>Date</span></th>';
$avancement_html .= '<th style="width:25%;" align="left" valign="bottom"><span>Action</span></th>';
$avancement_html .= '<th style="width:20%;" align="left" valign="bottom"><span>Profil</span></th>';
$avancement_html .= '<th style="width:20%;" align="left" valign="bottom"><span>Service</span></th>';
$avancement_html .= '<th style="width:20%;" align="left" valign="bottom"><span>Acteur</span></th>';
$avancement_html .= '</tr></thead><tbody>';
$color = "";
//$visaEnCours = false;
foreach($tab_histo as $action){
	$act = getInfosAction($action->event_id);
	$us = getInfosUser($action->user_id);
	if (($act['status'] != "")/* && $action->event_id != 401 && $action->event_id != 405*/){
	if($color == ' class="col"') {
		$color = '';
	} else {
		$color = ' class="col"';
	}
	$date = $action->event_date;
	$date = explode(" ",$date);
	$date = explode("-",$date[0]);
	$avancement_html .= '<tr ' . $color . '>';
	$avancement_html .= '<td>'.$date[2]."/".$date[1]."/".$date[0].'</td>';
	$avancement_html .= '<td>'.$act['label'].'</td>';
	$avancement_html .= '<td>'.$us['groupe'].'</td>';
	$avancement_html .= '<td>'.$us['entite'].'</td>';
	$avancement_html .= '<td>'.$us['prenom'].' '.$us['nom'].'</td>';
	$avancement_html .= '</tr>';
	}
}
$avancement_html .= '</tbody></table><br/>';
$avancement_html .= '<h2 onmouseover="this.style.cursor=\\\'pointer\\\';" onclick="new Effect.toggle(\\\'frame_histo_div\\\', \\\'blind\\\', {delay:0.2}); whatIsTheDivStatus(\\\'frame_histo_div\\\', \\\'frame_histo_div_status\\\');return false;">';
$avancement_html .= ' <span id="frame_histo_div_status" style="color:#1C99C5;"><<</span>';
$avancement_html .= ' Historique complet</h2>';
$avancement_html .= '<div id="frame_histo_div" style="display:none" >';
$avancement_html .= '<iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full" name="history_document" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="history_document"></iframe>';
$avancement_html .= '</div>';


require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$sec =new security();

	
//NOTES	
if ($core->is_module_loaded('notes')){
	require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
						. "class" . DIRECTORY_SEPARATOR
						. "class_modules_tools.php";
	$notes_tools    = new notes();
					
	//Count notes
	$nbr_notes = $notes_tools->countUserNotes($res_id, $coll_id);
	if ($nbr_notes > 0 ) $nbr_notes = ' ('.$nbr_notes.')';  else $nbr_notes = '';
	//Notes iframe
	$notes_html_dt =  _NOTES.$nbr_notes;
	$notes_html_dd = '<h2>'. _NOTES .'</h2><iframe name="list_notes_doc" id="list_notes_doc" src="'. $_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=notes&identifier='. $res_id .'&origin=document&coll_id='.$coll_id.'&load&size=full" frameborder="0" scrolling="no" width="99%" height="570px"></iframe> ';	
}

/* Partie droite */
$right_html = '';

//CIRCUIT 
$right_html = '';


//Onglet Circuit 
	$right_html .= '<dt id="onglet_circuit">Circuit de visa</dt><dd id="page_circuit">';
	$right_html .= '<h2>Circuit de visa</h2>';
	
	$modifVisaWorkflow = false;
    if ($core->test_service('config_visa_workflow', 'visa', false)) {
        $modifVisaWorkflow = true;
    }
	$visa = new visa();
	
	$right_html .= '<div class="error" id="divError" name="divError"></div>';
	$right_html .= '<div style="text-align:center;">';
	$right_html .= str_replace("'", "\\'", $visa->getList($res_id, $coll_id, $modifVisaWorkflow, 'VISA_CIRCUIT'));
                
	$right_html .= '</div><br>';

	$right_html .= '<br/>'; 
		$right_html .= '<br/>';                
		$right_html .= '<span class="diff_list_visa_history" style="width: 90%; cursor: pointer;" onmouseover="this.style.cursor=\\\'pointer\\\';" onclick="new Effect.toggle(\\\'diff_list_visa_history_div\\\', \\\'blind\\\', {delay:0.2});whatIsTheDivStatus(\\\'diff_list_visa_history_div\\\', \\\'divStatus_diff_list_visa_history_div\\\');return false;">';
			$right_html .= '<span id="divStatus_diff_list_visa_history_div" style="color:#1C99C5;"><<</span>';
			$right_html .= '<b>&nbsp;<small>Historique du circuit de visa</small></b>';
		$right_html .= '</span>';

		$right_html .= '<div id="diff_list_visa_history_div" style="display:none">';

			$s_id = $res_id;
			$return_mode = true;
			$diffListType = 'VISA_CIRCUIT';
			require_once('modules/entities/difflist_visa_history_display.php');
						
	$right_html .= '</div>';
	$right_html .= '</dd>';

$tab_path_rep_file = get_rep_path($res_id, $coll_id);
	for ($i=0; $i<count($tab_path_rep_file);$i++){
		$num_rep = $i+1;
		if (strlen($tab_path_rep_file[$i]['title']) > 20) $titleRep = substr($tab_path_rep_file[$i]['title'],0,20).'...';
		else $titleRep = $tab_path_rep_file[$i]['title'];
		$right_html .= '<dt onclick="updateFunctionModifRep(\\\''.$tab_path_rep_file[$i]['res_id'].'\\\', '.$num_rep.', '.$tab_path_rep_file[$i]['is_version'].');">'.$titleRep.'</dt><dd>';
		$right_html .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&module=visa&page=view_doc&path='
			. $tab_path_rep_file[$i]['path'].'" name="viewframevalidRep'.$num_rep.'" id="viewframevalidRep'.$num_rep.'"  scrolling="auto" frameborder="0" style="width:100%;height:100%;" ></iframe>';
		 $right_html .= '</dd>';
	}
	
	$countAttachments = "select res_id from "
            . $_SESSION['tablename']['attach_res_attachments']
            . " where status NOT IN ('DEL','OBS') and res_id_master = " . $res_id . " and coll_id = '" . $coll_id . "'";
		$dbAttach = new dbquery();
		$dbAttach->query($countAttachments);
		if ($dbAttach->nb_result() > 0) {
			$nb_attach = ' (' . ($dbAttach->nb_result()). ')';
		}
	
		$right_html .= '<dt id="onglet_pj">'. _ATTACHED_DOC .$nb_attach.'</dt><dd id="page_pj">';
		
		if ($core_tools->is_module_loaded('attachments')) {
        require 'modules/templates/class/templates_controler.php';
        $templatesControler = new templates_controler();
        $templates = array();
        $templates = $templatesControler->getAllTemplatesForProcess($curdest);
        $_SESSION['destination_entity'] = $curdest;
        //var_dump($templates);
        $right_html .= '<div id="list_answers_div" onmouseover="this.style.cursor=\\\'pointer\\\';">';
            $right_html .= '<div class="block" style="margin-top:-2px;">';
                $right_html .= '<div id="processframe" name="processframe">';
                    $right_html .= '<center><h2>' . _PJ . ', ' . _ATTACHEMENTS . '</h2></center>';
                    $req = new request;
                    $req->connect();
                    $req->query("select res_id from ".$_SESSION['tablename']['attach_res_attachments']
                        . " where (status = 'A_TRA' or status = 'TRA') and res_id_master = " . $res_id . " and coll_id = '" . $coll_id . "'");
                    //$req->show();
                    $nb_attach = 0;
                    if ($req->nb_result() > 0) {
                        $nb_attach = $req->nb_result();
                    }
                    $right_html .= '<div class="ref-unit">';
                    $right_html .= '<center>';
                    if ($core_tools->is_module_loaded('templates')) {
                        $right_html .= '<input type="button" name="attach" id="attach" class="button" value="'
                            . _CREATE_PJ
                            .'" onclick="showAttachmentsForm(\\\'' . $_SESSION['config']['businessappurl']
                            . 'index.php?display=true&module=attachments&page=attachments_content\\\')" />';
                    }
                    $right_html .= '</center><iframe name="list_attach" id="list_attach" src="'
                    . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&resId='.$res_id.'" '
                    . 'frameborder="0" width="100%" height="600px"></iframe>';
                    $right_html .= '</div>';
                $right_html .= '</div>';
            $right_html .= '</div>';
            $right_html .= '<hr />';
        $right_html .= '</div>';
    }
	
	
		$right_html .= '</dd>';
					
		if ( $core->is_module_loaded('content_management') && $data['category_id']['value'] == 'outgoing') {
        $versionTable = $sec->retrieve_version_table_from_coll_id(
            $coll_id
        );
        $selectVersions = "select res_id from "
            . $versionTable . " where res_id_master = "
            . $res_id . " and status <> 'DEL' order by res_id desc";
        $dbVersions = new dbquery();
        $dbVersions->connect();
        $dbVersions->query($selectVersions);
        $nb_versions_for_title = $dbVersions->nb_result();
        $lineLastVersion = $dbVersions->fetch_object();
        $lastVersion = $lineLastVersion->res_id;
        if ($lastVersion <> '') {
            $objectId = $lastVersion;
            $objectTable = $versionTable;
        } else {
            $objectTable = $sec->retrieve_table_from_coll(
                $coll_id
            );
            $objectId = $res_id;
            $_SESSION['cm']['objectId4List'] = $res_id;
        }
        if ($nb_versions_for_title == 0) {
            $extend_title_for_versions = '0';
        } else {
            $extend_title_for_versions = $nb_versions_for_title;
        }
        $_SESSION['cm']['resMaster'] = '';
		$right_html .= '<dt>' . _VERSIONS . ' (<span id="nbVersions">' . $extend_title_for_versions . '</span>)</dt><dd>';
		$right_html .= '<h2>';
			$right_html .= '<center>' . _VERSIONS . '</center>';
		$right_html .= '</h2>';
		$right_html .= '<div class="error" id="divError" name="divError"></div>';
		$right_html .= '<div style="text-align:center;">';
			$right_html .= '<a href="';
				$right_html .=  $_SESSION['config']['businessappurl'];
				$right_html .= 'index.php?display=true&dir=indexing_searching&page=view_resource_controler&original&id=';
				$right_html .= $res_id;
				$right_html .= '" target="_blank">';
				$right_html .= '<img alt="' . _VIEW_ORIGINAL . '" src="';
				$right_html .= $_SESSION['config']['businessappurl'];
				$right_html .= 'static.php?filename=picto_dld.gif" border="0" alt="" />';
				$right_html .= _VIEW_ORIGINAL . ' | ';
			$right_html .= '</a>';
			if ($core->test_service('add_new_version_init', 'apps', false)) {
				$_SESSION['cm']['objectTable'] = $objectTable;
				$right_html .= '<div id="createVersion" style="display: inline;"></div>';
			}
			$right_html .= '<div id="loadVersions"></div>';
			$right_html .= '<script language="javascript">';
				$right_html .= 'showDiv("loadVersions", "nbVersions", "createVersion", "';
					$right_html .= $_SESSION['urltomodules'];
					$right_html .= 'content_management/list_versions.php")';
			$right_html .= '</script>';
		$right_html .= '</div><br>';
		$right_html .= '</dd>';
    }

	$valid_but = 'if (document.getElementById(\\\'chosen_action\\\').value == 403 || document.getElementById(\\\'chosen_action\\\').value == 404 || document.getElementById(\\\'chosen_action\\\').value == 414) generateWaybill('.$res_id.');valid_action_form( \\\'index_file\\\', \\\'index.php?display=true&page=manage_action&module=core\\\', \\\''.$_REQUEST['action'].'\\\', \\\''.$res_id.'\\\', \\\'res_letterbox\\\', \\\'null\\\', \\\''.$coll_id.'\\\', \\\'page\\\');';
echo "{status : 2,notes_dt:'".$notes_html_dt."',notes_dd:'".$notes_html_dd."',avancement:'".$avancement_html."',right_html:'".$right_html."',valid_button:'".$valid_but."'}";
exit();
?>