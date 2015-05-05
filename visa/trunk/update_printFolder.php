<?php
/* FONCTIONS */


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
function getNotes($res_id){
	$db = new dbquery();
    $db->connect();
	$req_notes = "select * from notes where identifier = '".$res_id."'";
	$db->query($req_notes);

	$tab_notes = array();
	 while ($notes = $db->fetch_object()) {
		$note = "Note de ".$notes->user_id.", le ".$notes->date_note." : ".$notes->note_text;
		//array_push($tab_notes, $note);
		array_push($tab_notes, array('id_note'=>$notes->id,'user_id'=>$notes->user_id,'date_note'=>$notes->date_note,'note_text'=>$notes->note_text));
	}
	
	return $tab_notes;
}
function get_attach_path($res_id, $coll_id)
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
	$db->query("select filename, path,title,res_id,status,typist,creation_date,format,attachment_type from res_view_attachments where status <> 'DEL' AND status IN ('TRA','SIGN') AND res_id_master = " . $res_id . " order by attachment_type, creation_date asc");
	$array_attach = array();
	$cpt_rep = 0;
	while ($res2 = $db->fetch_object()){
		$filename=$res2->filename;
		$path = preg_replace('/#/', DIRECTORY_SEPARATOR, $res2->path);
		$filename_pdf = str_replace(pathinfo($filename, PATHINFO_EXTENSION), "pdf",$filename);
		$array_attach[$cpt_rep]['path'] = $docserver_path.$path.$filename_pdf;
		$array_attach[$cpt_rep]['title'] = $res2->title;
		$array_attach[$cpt_rep]['res_id'] = $res2->res_id;
		$array_attach[$cpt_rep]['attachment_type'] = $res2->attachment_type;
		$array_attach[$cpt_rep]['format'] = $res2->format;
		$array_attach[$cpt_rep]['typist'] = $res2->typist;
		$date = explode(" ",$res2->creation_date);
		$array_attach[$cpt_rep]['date'] = $date[0];
		$cpt_rep++;
	}
    return $array_attach;
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
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

$core =new core_tools();

$data = get_general_data($coll_id, $res_id, 'minimal');
			
/* Partie centrale */

// AVANCEMENT
$avancement_html = '';

$avancement_html .= '<h2>'. _WF .'</h2>';
$avancement_html .= '<iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_workflow_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full&small=true" name="workflow_history_document" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="workflow_history_document"></iframe>';
$avancement_html .= '<br/>';
$avancement_html .= '<br/>';

$avancement_html .= '<span style="cursor: pointer;" onmouseover="this.style.cursor=\\\'pointer\\\';" onclick="new Effect.toggle(\\\'history_document\\\', \\\'blind\\\', {delay:0.2});whatIsTheDivStatus(\\\'history_document\\\', \\\'divStatus_all_history_div\\\');return false;">';
$avancement_html .= '<span id="divStatus_all_history_div" style="color:#1C99C5;"><<</span>';
$avancement_html .= '<b>&nbsp;'. _ALL_HISTORY .'</b>';
$avancement_html .= '</span>';

$avancement_html .= '<iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full&small=true" name="history_document" width="100%" height="620px;" align="left" scrolling="yes" frameborder="0" id="history_document" style="display:none;"></iframe>';



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



//PJ

$countAttachments = "select res_id from res_view_attachments where status NOT IN ('DEL','OBS') and res_id_master = " . $res_id . " and coll_id = '" . $coll_id . "'";
		$dbAttach = new dbquery();
		$dbAttach->query($countAttachments);
		if ($dbAttach->nb_result() > 0) {
			$nb_attach = ' (' . ($dbAttach->nb_result()). ')';
		}
	
		$pj_html_dt =  _ATTACHED_DOC .$nb_attach;
		
		$pj_html_dd = '';
		
		if ($core->is_module_loaded('attachments')) {
        require 'modules/templates/class/templates_controler.php';
        $templatesControler = new templates_controler();
        $templates = array();
        $templates = $templatesControler->getAllTemplatesForProcess($curdest);
        $_SESSION['destination_entity'] = $curdest;
        //var_dump($templates);
        $pj_html_dd .= '<div id="list_answers_div" onmouseover="this.style.cursor=\\\'pointer\\\';">';
            $pj_html_dd .= '<div class="block" style="margin-top:-2px;">';
                $pj_html_dd .= '<div id="processframe" name="processframe">';
                    $pj_html_dd .= '<center><h2>' . _PJ . ', ' . _ATTACHEMENTS . '</h2></center>';
					
                    $req = new request;
                    $req->connect();
                    $req->query("select res_id from ".$_SESSION['tablename']['attach_res_attachments']
                        . " where (status = 'A_TRA' or status = 'TRA') and res_id_master = " . $res_id . " and coll_id = '" . $coll_id . "'");
                    //$req->show();
                    $nb_attach = 0;
                    if ($req->nb_result() > 0) {
                        $nb_attach = $req->nb_result();
                    }
                    $pj_html_dd .= '<div class="ref-unit">';
                    $pj_html_dd .= '<center>';
                    if ($core->is_module_loaded('templates')) {
                        $pj_html_dd .= '<input type="button" name="attach" id="attach" class="button" value="'
                            . _CREATE_PJ
                            .'" onclick="showAttachmentsForm(\\\'' . $_SESSION['config']['businessappurl']
                            . 'index.php?display=true&module=attachments&page=attachments_content\\\')" />';
                    }
                    $pj_html_dd .= '</center><iframe name="list_attach" id="list_attach" src="'
                    . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&resId='.$res_id.'" '
                    . 'frameborder="0" width="100%" height="600px"></iframe>';
                    $pj_html_dd .= '</div>';
                $pj_html_dd .= '</div>';
            $pj_html_dd .= '</div>';
            $pj_html_dd .= '<hr />';
        $pj_html_dd .= '</div>';
    }
	
	
		
		
/* Partie droite */
$right_html = '';

$right_html .= '<dt>Dossier</dt><dd style="overflow-x: hidden;">';
	
	$right_html .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
	$right_html .= '<h2>Contenu du dossier de réponse</h2>';
	
	$right_html .= '<p><b>Requérent</b> : '.$data['contact'].'</p>';
	$right_html .= '<p><b>Objet</b> : '.$data['subject'].'</p>';
	$right_html .= '<hr/>';
	
	
	$tab_attach_file = get_attach_path($res_id, $coll_id);
	$right_html .= '<table style="width:99%;">';
	$right_html .= '<thead><tr><th style="width:25%;"></th><th style="width:40%;">Titre</th><th style="width:20%;">Rédacteur</th><th style="width:10%;">Date</th><th style="width:5%;"></th></tr></thead>';
	$right_html .= '<tbody>';
	if ($data['category_id']['value'] == "incoming"){
	$right_html .= '<tr><td><h3>+ Courrier entrant</h3></td><td></td><td></td><td></td><td></td></tr>';
	$right_html .= '<tr><td></td><td>'.$data['subject'].'</td><td>'.$data['contact'].'</td><td>'.$data['doc_date'].'</td><td><input id="contenu_dossier" type="checkbox" name="dossier[]" value="initial_'.$res_id.'" checked></input></td></tr>';	
	}
	else{
		$right_html .= '<tr><td><h3>+ Courrier sortant</h3></td><td></td><td></td><td></td><td></td></tr>';
		$right_html .= '<tr><td></td><td>'.$data['subject'].'</td><td>'.$typist.'</td><td>'.$data['doc_date'].'</td><td><input id="contenu_dossier" type="checkbox" name="dossier[]" value="initial_'.$res_id.'" checked></input></td></tr>';	
	}
	$currentStat = "";

	$bordereauExists = false;
	for ($i=0; $i<count($tab_attach_file);$i++){
		$auteur = getInfosUser($tab_attach_file[$i]['typist']);
		if ($tab_attach_file[$i]['attachment_type'] != $currentStat){
			
			if ($tab_attach_file[$i]['attachment_type'] == "response_project"){
				$right_html .= '<tr><td><h3>+ Réponse(s) effectuée(s)</h3></td><td></td><td></td><td></td><td></td></tr>';			
				$checked = " checked";				
			}
			if ($tab_attach_file[$i]['attachment_type'] == "signed_response"){
				$frm_str .= '<tr><td><h3>+ Réponse(s) effectuée(s)</h3></td><td></td><td></td><td></td><td></td></tr>';			
				$checked = " checked";				
			}
			if ($tab_attach_file[$i]['attachment_type'] == "simple_attachment"){
				$right_html .= '<tr><td><h3>+ Pièce(s) jointe(s)</h3></td><td></td><td></td><td></td><td></td></tr>';	
				$checked = " ";	
			}
			if ($tab_attach_file[$i]['attachment_type'] == "routing"){
				$right_html .= '<tr><td><h3>+ Fiche de circulation</h3></td><td></td><td></td><td></td><td></td></tr>';	
				$checked = " checked ";
				$bordereauExists = true;
			}
				
			$currentStat = $tab_attach_file[$i]['attachment_type'];
		}
		
		if ($tab_attach_file[$i]['attachment_type'] == "simple_attachment" && $tab_attach_file[$i]['format'] != "pdf") $disabled = " disabled title=\"Il n'est pas possible d'imprimer une pièce d'un autre format que pdf\" ";	
		else  $disabled = " ";	
		
		$right_html .= '<tr><td></td><td><a href="index.php?display=true&module=attachments&page=view_attachment&id='.$tab_attach_file[$i]['res_id'].'&res_id_master='.$res_id.'" target="_blank">'.$tab_attach_file[$i]['title'].'</a></td><td>'.$auteur['prenom'].' '.$auteur['nom'].'</td><td>'.$tab_attach_file[$i]['date'].'</td><td><input type="checkbox" id="contenu_dossier" name="dossier[]" value="attach_'.$tab_attach_file[$i]['res_id'].'" '.$checked.' '.$disabled.'></input></td></tr>';	
	}
	
	if (!$bordereauExists){
		$right_html .= "<tr><td><h3 id=\"tit_bord\" onclick=\"window.open('".$_SESSION['config']['businessappurl']."/index.php?display=true&module=content_management&page=applet_popup_launcher&objectType=bordereauFromTemplate&objectId=113&objectTable=res_letterbox&resMaster=".$res_id."', '', 'height=301, width=301,scrollbars=no,resizable=no,directories=no,toolbar=no');\" ". "onmouseover=\"this.style.cursor='pointer';\" >+ Générer la fiche de circulation</h3></td><td></td><td></td><td></td><td></td></tr>";	
		$right_html .= '<tr id="line_bord"><td></td><td></td><td></td><td></td><td><input type="checkbox" name="dossier[]" value="attach_" checked></input></td></tr>';	
	}
	
	$tab_notes = getNotes($res_id);
	if (count($tab_notes) > 0){
		$right_html .= '<tr><td><h3>+ Notes</h3></td><td></td><td></td><td></td><td></td></tr>';	
		
		foreach($tab_notes as $note){
			$auteur = getInfosUser($note['user_id']);
			$right_html .= '<tr><td></td><td>'.$note['note_text'].'</td><td>'.$auteur['prenom'].' '.$auteur['nom'].'</td><td>'.$note['date_note'].'</td><td><input type="checkbox" id="contenu_dossier" name="dossier[]" value="note_'.$note['id_note'].'"  ></input></td></tr>';	
		}
	}
	
	
	$right_html .= '</tbody>';
	$right_html .= '</table>';
	$right_html .= '<hr/>';
	$right_html .= '</dd>';

	$right_html = str_replace("'", "\\'",$right_html);
//Onglet Circuit 
	$right_html .= '<dt id="onglet_circuit">Circuit de visa</dt><dd id="page_circuit" style="overflow-x: hidden;">';
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


$valid_but = 'valid_action_form( \\\'index_file\\\', \\\'index.php?display=true&page=manage_action&module=core\\\', \\\''.$_REQUEST['action'].'\\\', \\\''.$res_id.'\\\', \\\'res_letterbox\\\', \\\'null\\\', \\\''.$coll_id.'\\\', \\\'page\\\');';
echo "{status : 4,notes_dt:'".$notes_html_dt."',notes_dd:'".$notes_html_dd."',pj_dt:'".$pj_html_dt."',pj_dd:'".$pj_html_dd."',avancement:'".$avancement_html."',right_html:'".$right_html."',valid_button:'".$valid_but."'}";
exit();
?>