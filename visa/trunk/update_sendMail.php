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


//PJ

$countAttachments = "select res_id from "
            . $_SESSION['tablename']['attach_res_attachments']
            . " where status NOT IN ('DEL','OBS') and res_id_master = " . $res_id . " and coll_id = '" . $coll_id . "'";
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
	
	
		$frm_str .= '</dd>';
	
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


if ($core->test_service('sendmail', 'sendmail', false) === true) {
		require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
		$sendmail_tools    = new sendmail();
		 //Count mails
		$nbr_emails = $sendmail_tools->countUserEmails($res_id, $coll_id);
		if ($nbr_emails > 0 ) $nbr_emails = ' ('.$nbr_emails.')';  else $nbr_emails = '';
	   
		
		$right_html .= '<dt>' . _SENDED_EMAILS.$nbr_emails .'</dt><dd>';
		//Emails iframe
		$right_html .=  $core->execute_modules_services(
			$_SESSION['modules_services'], 'details', 'frame', 'sendmail', 'sendmail'
		);
		
		
		$right_html .= '</dd>';
	}
	
	
	//Onglet Circuit 
	$right_html .= '<dt id="onglet_circuit">'._VISA_WORKFLOW.'</dt><dd id="page_circuit">';
	$right_html .= '<h2>'._VISA_WORKFLOW.'</h2>';
	
	$modifVisaWorkflow = false;
    if ($core->test_service('config_visa_workflow', 'visa', false)) {
        $modifVisaWorkflow = true;
    }
	$visa = new visa();
	
	$right_html .= '<div class="error" id="divError" name="divError"></div>';
	$right_html .= '<div style="text-align:center;">';
	$right_html .= $visa->getList($res_id, $coll_id, $modifVisaWorkflow, 'VISA_CIRCUIT');
                
	$right_html .= '</div><br>';
	/* Historique diffusion visa */
	$right_html .= '<br/>'; 
		$right_html .= '<br/>';                
		$right_html .= '<span class="diff_list_visa_history" style="width: 90%; cursor: pointer;" onmouseover="this.style.cursor=\\\'pointer\\\';" onclick="new Effect.toggle(\\\'diff_list_visa_history_div\\\', \\\'blind\\\', {delay:0.2});whatIsTheDivStatus(\\\'diff_list_visa_history_div\\\', \\\'divStatus_diff_list_visa_history_div\\\');return false;">';
			$right_html .= '<span id="divStatus_diff_list_visa_history_div" style="color:#1C99C5;"><<</span>';
			$right_html .= '<b>&nbsp;<small>'._DIFF_LIST_VISA_HISTORY.'</small></b>';
		$right_html .= '</span>';

		$right_html .= '<div id="diff_list_visa_history_div" style="display:none">';

			$s_id = $res_id;
			$return_mode = true;
			$diffListType = 'VISA_CIRCUIT';
			require_once('modules/entities/difflist_visa_history_display.php');
						
	$right_html .= '</div>';
	$right_html .= '</dd>';
$valid_but = 'valid_action_form( \\\'index_file\\\', \\\'index.php?display=true&page=manage_action&module=core\\\', \\\''.$_REQUEST['action'].'\\\', \\\''.$res_id.'\\\', \\\'res_letterbox\\\', \\\'null\\\', \\\''.$coll_id.'\\\', \\\'page\\\');';
echo "{status : 3,notes_dt:'".$notes_html_dt."',notes_dd:'".$notes_html_dd."',pj_dt:'".$pj_html_dt."',pj_dd:'".$pj_html_dd."',avancement:'".$avancement_html."',right_html:'".$right_html."',valid_button:'".$valid_but."'}";
exit();
?>