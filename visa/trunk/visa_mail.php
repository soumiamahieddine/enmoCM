<?php

/**
* @brief   Action : Viser le courrier
*
* Ouverture, dans une fenêtre séparée en deux, d'un document entrant (+ ses informations) d'une part
* et de ses projets de réponses d'autre part. Possibilité de modifier les réponses, écrire des notes 
* et envoyer des mails rapidement
*
* @file visa_mail
* @author Nicolas Couture <couture@docimsol.com>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

/**
* $confirm  bool false
*/
$confirm = false;
/**
* $etapes  array Contains only one etap : form
*/
$etapes = array('form');
/**
* $frm_width  Width of the modal (empty)
*/
$frm_width='';
/**
* $frm_height  Height of the modal (empty)
*/
$frm_height = '';
/**
* $mode_form  Mode of the modal : fullscreen
*/
$mode_form = 'fullscreen';

include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');

require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

function writeLogIndex($EventInfo)
{
    $logFileOpened = fopen($_SESSION['config']['logdir']."visa_mail.log", 'a');
    fwrite($logFileOpened, '[' . date('d') . '/' . date('m') . '/' . date('Y')
        . ' ' . date('H') . ':' . date('i') . ':' . date('s') . '] ' . $EventInfo
        . "\r\n"
    );
    fclose($logFileOpened);
}


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
function check_category($coll_id, $res_id)
{
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    $sec =new security();
    $view = $sec->retrieve_view_from_coll_id($coll_id);

    $db = new dbquery();
    $db->connect();
    $db->query("select category_id from ".$view." where res_id = ".$res_id);
    $res = $db->fetch_object();

    if(!isset($res->category_id))
    {
        $ind_coll = $sec->get_ind_collection($coll_id);
        $table_ext = $_SESSION['collections'][$ind_coll]['extensions'][0];
        $db->query("insert into ".$table_ext." (res_id, category_id) VALUES (".$res_id.", '".$_SESSION['coll_categories']['letterbox_coll']['default_category']."')");
        //$db->show();
    }
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
	$db->query("select filename, path,title,res_id  from res_view_attachments where res_id_master = " . $res_id . " AND status <> 'OBS' AND status <> 'DEL' and attachment_type = 'response_project' order by creation_date asc");
	$array_reponses = array();
	$cpt_rep = 0;
	while ($res2 = $db->fetch_object()){
		$filename=$res2->filename;
		$path = preg_replace('/#/', DIRECTORY_SEPARATOR, $res2->path);
		$filename_pdf = str_replace(pathinfo($filename, PATHINFO_EXTENSION), "pdf",$filename);
		if (is_file($docserver_path.$path.$filename_pdf)){
		
		$array_reponses[$cpt_rep]['path'] = $docserver_path.$path.$filename_pdf;
		$array_reponses[$cpt_rep]['title'] = $res2->title;
		$array_reponses[$cpt_rep]['res_id'] = $res2->res_id;
		$cpt_rep++;
		}
	}
    return $array_reponses;
}

function getDocsBasket($curr_id){
	$db = new dbquery();
	$db->connect();
	$requete = "select res_id from ".$_SESSION['current_basket']['view']." where " . $_SESSION['current_basket']['clause'];
	$db->query($requete, true);
	$tab_docs = array();
	$cpt = 1;
	while($res = $db->fetch_object()){
		$tab_docs[$cpt] = $res->res_id;
		if ($res->res_id == $curr_id) $tab_docs['cur_cpt']=$cpt;
		$cpt++;
	}
	return $tab_docs;
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

function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
{
	
	
    if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
    {
        $browser_ie = true;
        $display_value = 'block';
    }
    elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"]) )
    {
        $browser_ie = true;
        $display_value = 'block';
    }
    else
    {
        $browser_ie = false;
        $display_value = 'table-row';
    }
    $_SESSION['req'] = "action";
    $res_id = $values[0];
	
	$tab_docs = getDocsBasket($res_id);
	//writeLogIndex(print_r($tab_docs,true));
	
    $_SESSION['doc_id'] = $res_id;
    $frm_str = '';
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
    require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_types.php");
    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

    $sec =new security();
    $core_tools =new core_tools();
    $b = new basket();
    $type = new types();
    $business = new business_app_tools();

	
	if ($core_tools->is_module_loaded('entities')) {
        require_once('modules/entities/class/class_manage_listdiff.php');
        $listdiff = new diffusion_list();
        $roles = $listdiff->list_difflist_roles();
        $_SESSION['process']['diff_list'] = $listdiff->get_listinstance($res_id, false, $coll_id);
        $_SESSION['process']['difflist_type'] = $listdiff->get_difflist_type($_SESSION['process']['diff_list']['object_type']);
    }
	
	require_once('core/class/LinkController.php');
	$Class_LinkController = new LinkController();
	$nbLink = $Class_LinkController->nbDirectLink(
		$res_id,
		$coll_id,
		'all'
	);
	
	
	$bannetteCourante = $_SESSION['current_basket']['id'];
	//en cas de délégation, le login de la personne est derrière
	if($_SESSION['current_basket']['basket_owner'] != $_SESSION['user']['UserId']){
		$bannette = explode('_',$bannetteCourante);
		$bannette = $bannette[0];
	}
	else $bannette = $bannetteCourante;
	
	$db = new dbquery();
    $db->connect();
	
	$view = $sec->retrieve_view_from_coll_id($coll_id);
	$db->query("select alt_identifier from " 
		. $view 
		. " where res_id = " . $res_id);
	$resChrono = $db->fetch_object();
	
	$data = get_general_data($coll_id, $res_id, 'minimal');
	
	$typeAffichage = $_SESSION['user']['typeview'];
	if ($typeAffichage == "split"){
		$affichageSplit = "display:block;";
		$affichageUni = "display:none;";
	}
	elseif ($typeAffichage == "uni"){
		$affichageUni = "display:block;";
		$affichageSplit = "display:none;";
	}
	
	
	//PARTIE GAUCHE
	$frm_str .= '<form name="index_file" method="post" id="index_file" action="#" class="forms " style="text-align:left;">';
	$frm_str .= '<input type="hidden" name="typeView" id="typeView" value="'.$typeAffichage.'" />';
	$frm_str .= '<div id="validleftSplit" style="width:48%;height:95%;margin-top:7px;'.$affichageSplit.'">';
    $frm_str .= '<div id="valid_div">';
	$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=logo_action_18.png"  align="middle" alt="" />'. _VISA_MAIL .' '._NUM.$resChrono->alt_identifier;
	$frm_str .= '</h1>';
	
	

	$frm_str .= '<input type="hidden" name="values" id="values" value="'.$res_id.'" />';
	$frm_str .= '<input type="hidden" name="action_id" id="action_id" value="'.$id_action.'" />';
	$frm_str .= '<input type="hidden" name="mode" id="mode" value="'.$mode.'" />';
	$frm_str .= '<input type="hidden" name="table" id="table" value="'.$table.'" />';
	$frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="'.$coll_id.'" />';
	$frm_str .= '<input type="hidden" name="module" id="module" value="'.$module.'" />';
	$frm_str .= '<input type="hidden" name="category_id" id="category_id" value="'.$data['category_id']['value'].'" />';
	$frm_str .= '<input type="hidden" name="req" id="req" value="second_request" />';
	
	
	$frm_str .= '<input type="hidden" name="prevDoc" id="prevDoc" value="';
	$prevFileExists = false;
	if (isset($tab_docs[$tab_docs['cur_cpt']-1])){
		$prevFileExists = true;
		$frm_str .= $tab_docs[$tab_docs['cur_cpt']-1];
	}
	$frm_str .= '" />';
	$frm_str .= '<input type="hidden" name="nextDoc" id="nextDoc" value="';
	$nextFileExists = false;
	if (isset($tab_docs[$tab_docs['cur_cpt']+1])){
		$nextFileExists = true;
		$frm_str .= $tab_docs[$tab_docs['cur_cpt']+1];
	}
	$frm_str .= '" />';
	
	

	$frm_str .= '<div  style="display:block">';

	$frm_str .= '<dl id="tabricator0" >';
	
		if ($data['category_id']['value'] == "incoming") $frm_str .= '<dt id="onglet_entrant">'._INCOMING.'</dt><dd>';
		else $frm_str .= '<dt id="onglet_entrant">'._SPONTANEOUS.'</dt><dd>';
		$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&coll_id='.$coll_id.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';
		
		$frm_str .= '</dd>';
		
	
	$frm_str .= '<dt id="onglet_details">Détails</dt><dd id="page_details">';
	
	$table = '';
	if (!isset($_REQUEST['coll_id']) || empty($_REQUEST['coll_id'])) {
		//$_SESSION['error'] = _COLL_ID.' '._IS_MISSING;
		$coll_id = $_SESSION['collections'][0]['id'];
		$table = $_SESSION['collections'][0]['view'];
		$is_view = true;
	} else {
		$coll_id = $_REQUEST['coll_id'];
		$table = $sec->retrieve_view_from_coll_id($coll_id);
		$is_view = true;
		if (empty($table)) {
			$table = $sec->retrieve_table_from_coll($coll_id);
			$is_view = false;
		}
	}


	$param_data = array(
                'img_category_id' => true,
                'img_priority' => true,
                'img_type_id' => true,
                'img_doc_date' => true,
                'img_admission_date' => true,
                'img_nature_id' => true,
                'img_subject' => true,
                'img_process_limit_date' => true,
                'img_author' => true,
                'img_destination' => true,
                'img_arbox_id' => true,
				/*'img_operator' => true,
                'img_city' => true,*/
                'img_folder' => true
                );
	 $db->query(
        "select status, format, typist, creation_date, fingerprint, filesize, "
        . "res_id, work_batch, page_count, is_paper, scan_date, scan_user, "
        . "scan_location, scan_wkstation, scan_batch, source, doc_language, "
        . "description, closing_date, alt_identifier, initiator, entity_label from " . $table . " where res_id = "
        . $res_id
    );
	$data = get_general_data($coll_id, $res_id, 'full', $param_data );
	//$frm_str .= '<pre>'.print_r($data,true).'</pre>';
	//writeLogIndex(print_r($data,true));
	$frm_str .= '<h2>Détails du document</h2>';
	$frm_str .= '<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">';
	$frm_str .= '<tbody>';
	$cpt_i = 0;
	foreach($data as $key=>$d){
		if ($key != 'folder'){
			if ($cpt_i%2 == 0){
				$frm_str .= '<tr class="col">';
			}
			$frm_str .= '<th class="picto" align="left"><img src="'.$data[$key]['img'].'" title="'.$data[$key]['label'].'" alt="'.$data[$key]['label'].'" /></th>';
			$frm_str .= '<td width="170px" align="left">'.$data[$key]['label'].'</td>';
			$frm_str .= '<td width="200px" align="left">'.$data[$key]['show_value'].'</td>';
			
			if ($cpt_i%2 == 1){
				$frm_str .= '</tr>';
			}
			$cpt_i++;
		}
	}
	$frm_str .= '</tbody>';
	$frm_str .= '</table>';
	$frm_str .= '</dd>';
	
	
	if ($core->is_module_loaded('notes')){
		require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
							. "class" . DIRECTORY_SEPARATOR
							. "class_modules_tools.php";
		$notes_tools    = new notes();
						
		//Count notes
		$nbr_notes = $notes_tools->countUserNotes($res_id, $coll_id);
		if ($nbr_notes > 0 ) $nbr_notes = ' ('.$nbr_notes.')';  else $nbr_notes = '';
		//Notes iframe
		$frm_str .= '<dt id="onglet_notes">'. _NOTES.$nbr_notes .'</dt><dd id="page_notes"><h2>'. _NOTES .'</h2><iframe name="list_notes_doc" id="list_notes_doc" src="'. $_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=notes&identifier='. $res_id .'&origin=document&coll_id='.$coll_id.'&load&size=full" frameborder="0" scrolling="no" width="99%" height="570px"></iframe></dd> ';	
	}
	
	
	if ($core->is_module_loaded('attachments'))
	{
		$req = new dbquery;
		$req->connect();
		
		$countAttachments = "select res_id, creation_date, title, format from " 
				. $_SESSION['tablename']['attach_res_attachments'] 
				. " where res_id_master = " . $_SESSION['doc_id'] 
				. " and coll_id ='" . $_SESSION['collection_id_choice'] 
				. "' and status <> 'DEL' and status='NEW' ";
			$req->query($countAttachments);
			if ($req->nb_result() > 0) {
				$nb_rep = ' (' . ($req->nb_result()). ')';
			}
	
		$frm_str .= '<dt id="onglet_rep">'. _DONE_ANSWERS .$nb_rep.'</dt><dd id="page_rep"><iframe name="list_attach" id="list_attach" src="'
                    . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type=response_project" '
                    . 'frameborder="0" width="100%" height="600px"></iframe></dd>';
	
	
		$countAttachments = "select res_id, creation_date, title, format from " 
			. $_SESSION['tablename']['attach_res_attachments'] 
			. " where res_id_master = " . $_SESSION['doc_id'] 
			. " and coll_id ='" . $_SESSION['collection_id_choice'] 
			. "' and status <> 'DEL' and status='PJ' ";
		$req->query($countAttachments);
		if ($req->nb_result() > 0) {
			$nb_attach = ' (' . ($req->nb_result()). ')';
		}
	
		$frm_str .= '<dt id="onglet_pj">'. _ATTACHED_DOC .$nb_attach.'</dt><dd id="page_pj"><iframe name="list_attach" id="list_attach" src="'
                    . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type_exclude=response_project" '
                    . 'frameborder="0" width="100%" height="600px"></iframe></dd>';
		
	}
		
	if ($core->test_service('sendmail', 'sendmail', false) === true) {
		require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
		$sendmail_tools    = new sendmail();
		 //Count mails
		$nbr_emails = $sendmail_tools->countUserEmails($res_id, $coll_id);
		if ($nbr_emails > 0 ) $nbr_emails = ' ('.$nbr_emails.')';  else $nbr_emails = '';
	   
		
		$frm_str .= '<dt id="onglet_mails">' . _SENDED_EMAILS.$nbr_emails .'</dt><dd id="page_mails">';
		//Emails iframe
		$frm_str .=  $core->execute_modules_services(
			$_SESSION['modules_services'], 'details', 'frame', 'sendmail', 'sendmail'
		);
		
		$frm_str .= '</dd>';
	}
	
	$frm_str .= '<dt id="onglet_avancement">Avancement</dt><dd id="page_avancement">';
	$frm_str .= '<h2>Workflow</h2>';
	$visa = new visa();
	$workflow = $visa->getWorkflow($res_id, $coll_id, 'VISA_CIRCUIT');
	$current_step = $visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');
	
	$tab_histo = getHistoryActions($res_id);
	//writeLogIndex(print_r($tab_histo,true));
	$frm_str .= '<table class="listing spec detailtabricatordebug" cellspacing="0" border="0" id="tab_visaWorkflow">';
	$frm_str .= '<thead><tr>';
	$frm_str .= '<th style="width:15%;" align="left" valign="bottom"><span>Date</span></th>';
	$frm_str .= '<th style="width:25%;" align="left" valign="bottom"><span>Action</span></th>';
	$frm_str .= '<th style="width:20%;" align="left" valign="bottom"><span>Profil</span></th>';
	$frm_str .= '<th style="width:20%;" align="left" valign="bottom"><span>Service</span></th>';
	$frm_str .= '<th style="width:20%;" align="left" valign="bottom"><span>Acteur</span></th>';
	$frm_str .= '</tr></thead><tbody>';
	$color = "";
	$visaEnCours = false;
	foreach($tab_histo as $action){
		$act = getInfosAction($action->event_id);
		$us = getInfosUser($action->user_id);
		if (($act['status'] != "" || $visaEnCours) && $action->event_id != 401 && $action->event_id != 405){
		if($color == ' class="col"') {
			$color = '';
		} else {
			$color = ' class="col"';
		}
		$date = $action->event_date;
		$date = explode(" ",$date);
		$date = explode("-",$date[0]);
		$frm_str .= '<tr ' . $color . '>';
		$frm_str .= '<td>'.$date[2]."/".$date[1]."/".$date[0].'</td>';
		//$frm_str .= '<td>'.$act['label'].' ['.$action->event_id.']</td>';
		$frm_str .= '<td>'.$act['label'].'</td>';
		if ($action->event_id == 403) $visaEnCours = true;
		$frm_str .= '<td>'.$us['groupe'].'</td>';
		$frm_str .= '<td>'.$us['entite'].'</td>';
		$frm_str .= '<td>'.$us['prenom'].' '.$us['nom'].'</td>';
		$frm_str .= '</tr>';
		}
	}
	$frm_str .= '</tbody></table><br/>';
	$frm_str .= '<h2 onmouseover="this.style.cursor=\'pointer\';" onclick="new Effect.toggle(\'frame_histo_div\', \'blind\', {delay:0.2}); whatIsTheDivStatus(\'frame_histo_div\', \'frame_histo_div_status\');return false;">';
	$frm_str .= ' <span id="frame_histo_div_status" style="color:#1C99C5;"><<</span>';
	$frm_str .= ' Historique complet</h2>';
	$frm_str .= '<div id="frame_histo_div" style="display:none" >';
	$frm_str .= '<iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full" name="history_document" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="history_document"></iframe>';
	$frm_str .= '</div>';
	$frm_str .= '</dd>';
	$frm_str .= '<dt id="onglet_listDif">Liste de diffusion</dt><dd>';
	$frm_str .= '<center><h2>' . _DIFF_LIST_COPY . '</h2></center>';
	if ($core->test_service('add_copy_in_process', 'entities', false)) {
		$frm_str .= '<a href="#" onclick="window.open(\''
			. $_SESSION['config']['businessappurl']
			. 'index.php?display=true&module=entities&page=manage_listinstance'
			. '&origin=process&only_cc\', \'\', \'scrollbars=yes,menubar=no,'
			. 'toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no\');" title="'
			. _UPDATE_LIST_DIFF
			. '"><img src="'
			. $_SESSION['config']['businessappurl']
			. 'static.php?filename=modif_liste.png" alt="'
			. _UPDATE_LIST_DIFF
			. '" />'
			. _UPDATE_LIST_DIFF
			. '</a><br/>';
	}
	# Get content from buffer of difflist_display 
	$difflist = $_SESSION['process']['diff_list'];
	
	ob_start();
	require_once 'modules/entities/difflist_display.php';
	$difflist_display .= str_replace(array("\r", "\n", "\t"), array("", "", ""), ob_get_clean());
	$frm_str .= $difflist_display;
	ob_end_flush();
	$frm_str .= '</dd>';
	$frm_str .= '<dt id="onglet_liaisons">Liaisons</dt><dd>';
	$frm_str .= '<div style="text-align: left;">';
            $frm_str .= '<h2>';
                $frm_str .= '<center>' . _LINK_TAB . '</center>';
            $frm_str .= '</h2>';
            $frm_str .= '<div id="loadLinks">';
                $nbLinkDesc = $Class_LinkController->nbDirectLink(
                    $_SESSION['doc_id'],
                    $_SESSION['collection_id_choice'],
                    'desc'
                );
                if ($nbLinkDesc > 0) {
                    $frm_str .= '<img src="static.php?filename=cat_doc_incoming.gif"/>';
                    $frm_str .= $Class_LinkController->formatMap(
                        $Class_LinkController->getMap(
                            $_SESSION['doc_id'],
                            $_SESSION['collection_id_choice'],
                            'desc'
                        ),
                        'desc'
                    );
                    $frm_str .= '<br />';
                }
                $nbLinkAsc = $Class_LinkController->nbDirectLink(
                    $_SESSION['doc_id'],
                    $_SESSION['collection_id_choice'],
                    'asc'
                );
                if ($nbLinkAsc > 0) {
                    $frm_str .= '<img src="static.php?filename=cat_doc_outgoing.gif" />';
                    $frm_str .= $Class_LinkController->formatMap(
                        $Class_LinkController->getMap(
                            $_SESSION['doc_id'],
                            $_SESSION['collection_id_choice'],
                            'asc'
                        ),
                        'asc'
                    );
                    $frm_str .= '<br />';
                }
            $frm_str .= '</div>';
            if ($core_tools->test_service('add_links', 'apps', false)) {
				//$frm_str .= '<form>';
                include 'apps/'.$_SESSION['config']['app_id'].'/add_links.php';
                $frm_str .= $Links;
            }
        $frm_str .= '</div>';
	
	$frm_str .= '</dl>';
	$frm_str .= '</div>';
		
	$frm_str .= '<div class="toolsSplit">';	
		$frm_str .= '<table style="width:45%;">';	
		
		$frm_str .= '<tr>';
		$frm_str .= '<td style="width:5%">';	
		if ($prevFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:previousDoc();"><img src="static.php?filename=FlecheGaucheBleue.png"/></a>';
		else $frm_str .= '<a href="javascript://" ><img src="static.php?filename=FlecheGaucheGrise.png"/></a>';
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:20%">';	
		$nb_docs_tot = count($tab_docs)-1;
		$frm_str .= 'Page '.$tab_docs['cur_cpt'].' sur '.$nb_docs_tot;
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:5%">';	
		if ($nextFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:nextDoc();"><img src="static.php?filename=FlecheDroiteBleue.png"/></a>';
		else $frm_str .= '<a href="javascript://" ><img src="static.php?filename=FlecheDroiteGrise.png"/></a>';
		$frm_str .= '</td>';
		
		/*$frm_str .= '<td style="width:5%">';	
		$frm_str .= '<a href="javascript://" onclick="javascript:switchViewTab(1);"><img src="static.php?filename=splitView.png" title="Assembler les vues"/></a>';
		$frm_str .= '</td>';*/
		
		
		$frm_str .= '<td style="width:5%">';	
		$frm_str .= '<a href="javascript://" onclick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();"><img src="static.php?filename=flecheRetourOrange.png" title="Annuler"/></a>';
		$frm_str .= '</td>';
		
		
		$frm_str .= '</tr>';	
		$frm_str .= '</table>';	
		$frm_str .= '</div>';
		
    $frm_str .= '</div>';        
    $frm_str .= '</div>';        
			
	//PARTIE DROITE
	$frm_str .= '<div id="validrightSplit" style="width:48%;height:95%;margin-top:45px;'.$affichageSplit.'">';
    $frm_str .= '<div id="valid_div" style="display:block;">';
	
	
	$frm_str .= '<div  style="display:block">';
	
	$frm_str .= '<dl id="tabricator1" >';
	

	
	$tab_path_rep_file = get_rep_path($res_id, $coll_id);
	for ($i=0; $i<count($tab_path_rep_file);$i++){
		$num_rep = $i+1;
		$frm_str .= '<dt onclick="updateFunctionModifRep(\''.$tab_path_rep_file[$i]['res_id'].'\', '.$num_rep.');">'.$tab_path_rep_file[$i]['title'].'</dt><dd>';
		$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_doc&path='
			. $tab_path_rep_file[$i]['path'].'" name="split_viewframevalidRep'.$num_rep.'" id="split_viewframevalidRep'.$num_rep.'"  scrolling="auto" frameborder="0" style="width:100%;height:100%;" ></iframe>';
		 $frm_str .= '</dd>';
	}
	
	/* AJOUT DE LA PARTIE DES VERSIONS POUR LE COURRIER SPONTANE */
	if ( $core->is_module_loaded('content_management') && $data['category_id']['value'] != "incoming") {
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
		$frm_str .= '<dt>' . _VERSIONS . ' (<span id="split_nbVersions">' . $extend_title_for_versions . '</span>)</dt><dd>';
		$frm_str .= '<h2>';
			$frm_str .= '<center>' . _VERSIONS . '</center>';
		$frm_str .= '</h2>';
		$frm_str .= '<div class="error" id="divError" name="divError"></div>';
		$frm_str .= '<div style="text-align:center;">';
			$frm_str .= '<a href="';
				$frm_str .=  $_SESSION['config']['businessappurl'];
				$frm_str .= 'index.php?display=true&dir=indexing_searching&page=view_resource_controler&original&id=';
				$frm_str .= $res_id;
				$frm_str .= '" target="_blank">';
				$frm_str .= '<img alt="' . _VIEW_ORIGINAL . '" src="';
				$frm_str .= $_SESSION['config']['businessappurl'];
				$frm_str .= 'static.php?filename=picto_dld.gif" border="0" alt="" />';
				$frm_str .= _VIEW_ORIGINAL . ' | ';
			$frm_str .= '</a>';
			if ($core->test_service('add_new_version_init', 'apps', false)) {
				$_SESSION['cm']['objectTable'] = $objectTable;
				$frm_str .= '<div id="split_createVersion" style="display: inline;"></div>';
			}
			$frm_str .= '<div id="split_loadVersions"></div>';
			$frm_str .= '<script language="javascript">';
				$frm_str .= 'showDiv("split_loadVersions", "split_nbVersions", "split_createVersion", "';
					$frm_str .= $_SESSION['urltomodules'];
					$frm_str .= 'content_management/list_versions.php")';
			$frm_str .= '</script>';
		$frm_str .= '</div><br>';
		$frm_str .= '</dd>';
    }
	/*************************************************************/
	
	//Onglet préparation du circuit de visa
	$frm_str .= '<dt>Circuit de visa</dt><dd>';
	
	$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
	$circuit_visa = new visa();
	$frm_str .= $circuit_visa->getList($res_id, $coll_id, $bool_modif=false, 'VISA_CIRCUIT');
	
	$frm_str .= '</dd>';
	
	$frm_str .= '</dl>';
	$frm_str .= '</div>';
	
	$frm_str .= '<div class="toolsSplit">';	
		$frm_str .= '<table style="width:100%;">';	
		
		$frm_str .= '<tr>';	
		$frm_str .= '<td>';	
		
		$frm_str .= '<b>'._ACTIONS.' : </b>';

		$actions  = $b->get_actions_from_current_basket($res_id, $coll_id, 'PAGE_USE');
		if(count($actions) > 0)
		{
			$frm_str .='<select name="chosen_action" id="split_chosen_action">';
				$frm_str .='<option value="">'._CHOOSE_ACTION.'</option>';
				for($ind_act = 0; $ind_act < count($actions);$ind_act++)
				{
					$frm_str .='<option value="'.$actions[$ind_act]['VALUE'].'"';
					if($ind_act==0)
					{
						$frm_str .= 'selected="selected"';
					}
					$frm_str .= '>'.$actions[$ind_act]['LABEL'].'</option>';
				}
			$frm_str .='</select> ';
			$table = $sec->retrieve_table_from_coll($coll_id);
			$frm_str .= '<input type="button" name="send" id="send" value="'._VALIDATE.'" class="button" onclick="if (document.getElementById(\'split_chosen_action\').value == 409) generateBordereau('.$res_id.');valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
		}
		
		//if ($workflow[$current_step]['consigne'] != "") $frm_str .= '<img src="static.php?filename=icone_consigne.png" title="'.$workflow[$current_step]['consigne'].'"/>';
		$frm_str .= '</td>';
		$frm_str .= '<input type="hidden" name="cur_rep" id="cur_rep" value="'.$tab_path_rep_file[0]['res_id'].'" >';
		$frm_str .= '<input type="hidden" name="cur_idAffich" id="cur_idAffich" value="1" >';
		
		$frm_str .= '<td style="width:5%";">';	
		$frm_str .= '<a href="javascript://" id="add_note" onclick="showNotesPage(\'tabricator0\');"><img src="static.php?filename=AddNotes.png&module=notes" title="Ajouter une note"/></a>';
		
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:5%";">';	
		$frm_str .= '<a href="javascript://" id="split_update_rep_link" onclick="';
		if($data['category_id']['value'] == "incoming") $frm_str .= 'window.open(\''.$_SESSION['config']['businessappurl'] . 'index.php?display=true&module=attachments&page=update_attachments&mode=up&collId='.$coll_id.'&id='.$tab_path_rep_file[0]['res_id'].'\',\'\',\'height=301, width=301,scrollbars=yes,resizable=yes\');';
		$frm_str .= '"><img src="static.php?filename=iconeModifReponse.png" title="Modifier la réponse"/></a>';
		
		$frm_str .= '</td>';
		$frm_str .= '</tr>';	
		$frm_str .= '</table>';	
		$frm_str .= '</div>';
	
	$frm_str .= '</div>';        
    $frm_str .= '</div>';  
	
	
	/**
	* FORMULAIRE UNIFIE
	**/
	$frm_str .= '<div id="validUnified" style="width:99%;height:95%;margin-top:7px;'.$affichageUni.'">';
	$frm_str .= '<div id="valid_div">';
	$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=logo_action_18.png"  align="middle" alt="" />'. _VISA_MAIL .' '._NUM.$resChrono->alt_identifier;
	$frm_str .= '</h1>';
	
	

	$frm_str .= '<input type="hidden" name="values" id="values" value="'.$res_id.'" />';
	$frm_str .= '<input type="hidden" name="action_id" id="action_id" value="'.$id_action.'" />';
	$frm_str .= '<input type="hidden" name="mode" id="mode" value="'.$mode.'" />';
	$frm_str .= '<input type="hidden" name="table" id="table" value="'.$table.'" />';
	$frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="'.$coll_id.'" />';
	$frm_str .= '<input type="hidden" name="module" id="module" value="'.$module.'" />';
	$frm_str .= '<input type="hidden" name="category_id" id="category_id" value="'.$data['category_id']['value'].'" />';
	$frm_str .= '<input type="hidden" name="req" id="req" value="second_request" />';
	
	
	$frm_str .= '<input type="hidden" name="prevDoc" id="prevDoc" value="';
	$prevFileExists = false;
	if (isset($tab_docs[$tab_docs['cur_cpt']-1])){
		$prevFileExists = true;
		$frm_str .= $tab_docs[$tab_docs['cur_cpt']-1];
	}
	$frm_str .= '" />';
	$frm_str .= '<input type="hidden" name="nextDoc" id="nextDoc" value="';
	$nextFileExists = false;
	if (isset($tab_docs[$tab_docs['cur_cpt']+1])){
		$nextFileExists = true;
		$frm_str .= $tab_docs[$tab_docs['cur_cpt']+1];
	}
	$frm_str .= '" />';
	
	

	$frm_str .= '<div  style="display:block">';

	$frm_str .= '<dl id="tabricator2" >';
		/* REPORT PARTIE DROITE */
		$tab_path_rep_file = get_rep_path($res_id, $coll_id);
		for ($i=0; $i<count($tab_path_rep_file);$i++){
			$num_rep = $i+1;
			$frm_str .= '<dt onclick="updateFunctionModifRep(\''.$tab_path_rep_file[$i]['res_id'].'\', '.$num_rep.');">'.$tab_path_rep_file[$i]['title'].'</dt><dd>';
			$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_doc&path='
				. $tab_path_rep_file[$i]['path'].'" name="uni_viewframevalidRep'.$num_rep.'" id="uni_viewframevalidRep'.$num_rep.'"  scrolling="auto" frameborder="0" style="width:100%;height:100%;" ></iframe>';
			 $frm_str .= '</dd>';
		}
		
		/* AJOUT DE LA PARTIE DES VERSIONS POUR LE COURRIER SPONTANE */
	if ( $core->is_module_loaded('content_management') && $data['category_id']['value'] != "incoming") {
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
		$frm_str .= '<dt>' . _VERSIONS . ' (<span id="uni_nbVersions">' . $extend_title_for_versions . '</span>)</dt><dd>';
		$frm_str .= '<h2>';
			$frm_str .= '<center>' . _VERSIONS . '</center>';
		$frm_str .= '</h2>';
		$frm_str .= '<div class="error" id="divError" name="divError"></div>';
		$frm_str .= '<div style="text-align:center;">';
			$frm_str .= '<a href="';
				$frm_str .=  $_SESSION['config']['businessappurl'];
				$frm_str .= 'index.php?display=true&dir=indexing_searching&page=view_resource_controler&original&id=';
				$frm_str .= $res_id;
				$frm_str .= '" target="_blank">';
				$frm_str .= '<img alt="' . _VIEW_ORIGINAL . '" src="';
				$frm_str .= $_SESSION['config']['businessappurl'];
				$frm_str .= 'static.php?filename=picto_dld.gif" border="0" alt="" />';
				$frm_str .= _VIEW_ORIGINAL . ' | ';
			$frm_str .= '</a>';
			if ($core->test_service('add_new_version_init', 'apps', false)) {
				$_SESSION['cm']['objectTable'] = $objectTable;
				$frm_str .= '<div id="uni_createVersion" style="display: inline;"></div>';
			}
			$frm_str .= '<div id="uni_loadVersions"></div>';
			$frm_str .= '<script language="javascript">';
				$frm_str .= 'showDiv("uni_loadVersions", "uni_nbVersions", "uni_createVersion", "';
					$frm_str .= $_SESSION['urltomodules'];
					$frm_str .= 'content_management/list_versions.php")';
			$frm_str .= '</script>';
		$frm_str .= '</div><br>';
		$frm_str .= '</dd>';
    }
	/*************************************************************/
		
		//Onglet préparation du circuit de visa
		$frm_str .= '<dt>Circuit de visa</dt><dd>';
		
		$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
		$circuit_visa = new visa();
		$frm_str .= $circuit_visa->getList($res_id, $coll_id, $bool_modif=false, 'VISA_CIRCUIT');
		
		$frm_str .= '</dd>';
		/************************/
		if ($data['category_id']['value'] == "incoming") $frm_str .= '<dt id="onglet_entrant">'._INCOMING.'</dt><dd>';
		else $frm_str .= '<dt id="onglet_entrant">'._SPONTANEOUS.'</dt><dd>';
		$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&coll_id='.$coll_id.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';
		
		$frm_str .= '</dd>';
		
	
	$frm_str .= '<dt id="onglet_details">Détails</dt><dd id="page_details">';
	
	$table = '';
	if (!isset($_REQUEST['coll_id']) || empty($_REQUEST['coll_id'])) {
		//$_SESSION['error'] = _COLL_ID.' '._IS_MISSING;
		$coll_id = $_SESSION['collections'][0]['id'];
		$table = $_SESSION['collections'][0]['view'];
		$is_view = true;
	} else {
		$coll_id = $_REQUEST['coll_id'];
		$table = $sec->retrieve_view_from_coll_id($coll_id);
		$is_view = true;
		if (empty($table)) {
			$table = $sec->retrieve_table_from_coll($coll_id);
			$is_view = false;
		}
	}


	$param_data = array(
                'img_category_id' => true,
                'img_priority' => true,
                'img_type_id' => true,
                'img_doc_date' => true,
                'img_admission_date' => true,
                'img_nature_id' => true,
                'img_subject' => true,
                'img_process_limit_date' => true,
                'img_author' => true,
                'img_destination' => true,
                'img_arbox_id' => true,
				/*'img_operator' => true,
                'img_city' => true,*/
                'img_folder' => true
                );
	 $db->query(
        "select status, format, typist, creation_date, fingerprint, filesize, "
        . "res_id, work_batch, page_count, is_paper, scan_date, scan_user, "
        . "scan_location, scan_wkstation, scan_batch, source, doc_language, "
        . "description, closing_date, alt_identifier, initiator, entity_label from " . $table . " where res_id = "
        . $res_id
    );
	$data = get_general_data($coll_id, $res_id, 'full', $param_data );
	//$frm_str .= '<pre>'.print_r($data,true).'</pre>';
	//writeLogIndex(print_r($data,true));
	$frm_str .= '<h2>Détails du document</h2>';
	$frm_str .= '<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">';
	$frm_str .= '<tbody>';
	$cpt_i = 0;
	foreach($data as $key=>$d){
		if ($key != 'folder'){
			if ($cpt_i%2 == 0){
				$frm_str .= '<tr class="col">';
			}
			$frm_str .= '<th class="picto" align="left"><img src="'.$data[$key]['img'].'" title="'.$data[$key]['label'].'" alt="'.$data[$key]['label'].'" /></th>';
			$frm_str .= '<td width="170px" align="left">'.$data[$key]['label'].'</td>';
			$frm_str .= '<td width="200px" align="left">'.$data[$key]['show_value'].'</td>';
			
			if ($cpt_i%2 == 1){
				$frm_str .= '</tr>';
			}
			$cpt_i++;
		}
	}
	$frm_str .= '</tbody>';
	$frm_str .= '</table>';
	$frm_str .= '</dd>';
	
	
	if ($core->is_module_loaded('notes')){
		require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
							. "class" . DIRECTORY_SEPARATOR
							. "class_modules_tools.php";
		$notes_tools    = new notes();
						
		//Count notes
		$nbr_notes = $notes_tools->countUserNotes($res_id, $coll_id);
		if ($nbr_notes > 0 ) $nbr_notes = ' ('.$nbr_notes.')';  else $nbr_notes = '';
		//Notes iframe
		$frm_str .= '<dt id="onglet_notes">'. _NOTES.$nbr_notes .'</dt><dd id="page_notes"><h2>'. _NOTES .'</h2><iframe name="list_notes_doc" id="list_notes_doc" src="'. $_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=notes&identifier='. $res_id .'&origin=document&coll_id='.$coll_id.'&load&size=full" frameborder="0" scrolling="no" width="99%" height="570px"></iframe></dd> ';	
	}
	
	
	if ($core->is_module_loaded('attachments'))
	{
		$req = new dbquery;
		$req->connect();
		
		$countAttachments = "select res_id, creation_date, title, format from " 
				. $_SESSION['tablename']['attach_res_attachments'] 
				. " where res_id_master = " . $_SESSION['doc_id'] 
				. " and coll_id ='" . $_SESSION['collection_id_choice'] 
				. "' and status <> 'DEL' and status='NEW' ";
			$req->query($countAttachments);
			if ($req->nb_result() > 0) {
				$nb_rep = ' (' . ($req->nb_result()). ')';
			}
	
		$frm_str .= '<dt id="onglet_rep">'. _DONE_ANSWERS .$nb_rep.'</dt><dd id="page_rep"><iframe name="list_attach" id="list_attach" src="'
                    . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type=response_project" '
                    . 'frameborder="0" width="100%" height="600px"></iframe></dd>';
	
	
		$countAttachments = "select res_id, creation_date, title, format from " 
			. $_SESSION['tablename']['attach_res_attachments'] 
			. " where res_id_master = " . $_SESSION['doc_id'] 
			. " and coll_id ='" . $_SESSION['collection_id_choice'] 
			. "' and status <> 'DEL' and status='PJ' ";
		$req->query($countAttachments);
		if ($req->nb_result() > 0) {
			$nb_attach = ' (' . ($req->nb_result()). ')';
		}
	
		$frm_str .= '<dt id="onglet_pj">'. _ATTACHED_DOC .$nb_attach.'</dt><dd id="page_pj"><iframe name="list_attach" id="list_attach" src="'
                    . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type_exclude=response_project" '
                    . 'frameborder="0" width="100%" height="600px"></iframe></dd>';
		
	}
		
	if ($core->test_service('sendmail', 'sendmail', false) === true) {
		require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
		$sendmail_tools    = new sendmail();
		 //Count mails
		$nbr_emails = $sendmail_tools->countUserEmails($res_id, $coll_id);
		if ($nbr_emails > 0 ) $nbr_emails = ' ('.$nbr_emails.')';  else $nbr_emails = '';
	   
		
		$frm_str .= '<dt id="onglet_mails">' . _SENDED_EMAILS.$nbr_emails .'</dt><dd id="page_mails">';
		//Emails iframe
		$frm_str .=  $core->execute_modules_services(
			$_SESSION['modules_services'], 'details', 'frame', 'sendmail', 'sendmail'
		);
		
		$frm_str .= '</dd>';
	}
	
	$frm_str .= '<dt id="onglet_avancement">Avancement</dt><dd id="page_avancement">';
	$frm_str .= '<h2>Workflow</h2>';
	$visa = new visa();
	$workflow = $visa->getWorkflow($res_id, $coll_id, 'VISA_CIRCUIT');
	$current_step = $visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');
	
	$tab_histo = getHistoryActions($res_id);
	//writeLogIndex(print_r($tab_histo,true));
	$frm_str .= '<table class="listing spec detailtabricatordebug" cellspacing="0" border="0" id="tab_visaWorkflow">';
	$frm_str .= '<thead><tr>';
	$frm_str .= '<th style="width:15%;" align="left" valign="bottom"><span>Date</span></th>';
	$frm_str .= '<th style="width:25%;" align="left" valign="bottom"><span>Action</span></th>';
	$frm_str .= '<th style="width:20%;" align="left" valign="bottom"><span>Profil</span></th>';
	$frm_str .= '<th style="width:20%;" align="left" valign="bottom"><span>Service</span></th>';
	$frm_str .= '<th style="width:20%;" align="left" valign="bottom"><span>Acteur</span></th>';
	$frm_str .= '</tr></thead><tbody>';
	$color = "";
	$visaEnCours = false;
	foreach($tab_histo as $action){
		$act = getInfosAction($action->event_id);
		$us = getInfosUser($action->user_id);
		if (($act['status'] != "" || $visaEnCours) && $action->event_id != 401 && $action->event_id != 405){
		if($color == ' class="col"') {
			$color = '';
		} else {
			$color = ' class="col"';
		}
		$date = $action->event_date;
		$date = explode(" ",$date);
		$date = explode("-",$date[0]);
		$frm_str .= '<tr ' . $color . '>';
		$frm_str .= '<td>'.$date[2]."/".$date[1]."/".$date[0].'</td>';
		//$frm_str .= '<td>'.$act['label'].' ['.$action->event_id.']</td>';
		$frm_str .= '<td>'.$act['label'].'</td>';
		if ($action->event_id == 403) $visaEnCours = true;
		$frm_str .= '<td>'.$us['groupe'].'</td>';
		$frm_str .= '<td>'.$us['entite'].'</td>';
		$frm_str .= '<td>'.$us['prenom'].' '.$us['nom'].'</td>';
		$frm_str .= '</tr>';
		}
	}
	$frm_str .= '</tbody></table><br/>';
	$frm_str .= '<h2 onmouseover="this.style.cursor=\'pointer\';" onclick="new Effect.toggle(\'frame_histo_div\', \'blind\', {delay:0.2}); whatIsTheDivStatus(\'frame_histo_div\', \'frame_histo_div_status\');return false;">';
	$frm_str .= ' <span id="frame_histo_div_status" style="color:#1C99C5;"><<</span>';
	$frm_str .= ' Historique complet</h2>';
	$frm_str .= '<div id="frame_histo_div" style="display:none" >';
	$frm_str .= '<iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full" name="history_document" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="history_document"></iframe>';
	$frm_str .= '</div>';
	$frm_str .= '</dd>';
	$frm_str .= '<dt id="onglet_listDif">Liste de diffusion</dt><dd>';
	$frm_str .= '<center><h2>' . _DIFF_LIST_COPY . '</h2></center>';
	if ($core->test_service('add_copy_in_process', 'entities', false)) {
		$frm_str .= '<a href="#" onclick="window.open(\''
			. $_SESSION['config']['businessappurl']
			. 'index.php?display=true&module=entities&page=manage_listinstance'
			. '&origin=process&only_cc\', \'\', \'scrollbars=yes,menubar=no,'
			. 'toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no\');" title="'
			. _UPDATE_LIST_DIFF
			. '"><img src="'
			. $_SESSION['config']['businessappurl']
			. 'static.php?filename=modif_liste.png" alt="'
			. _UPDATE_LIST_DIFF
			. '" />'
			. _UPDATE_LIST_DIFF
			. '</a><br/>';
	}
	$frm_str .= $difflist_display;
	$frm_str .= '</dd>';
	$frm_str .= '<dt id="onglet_liaisons">Liaisons</dt><dd>';
	$frm_str .= '<div style="text-align: left;">';
            $frm_str .= '<h2>';
                $frm_str .= '<center>' . _LINK_TAB . '</center>';
            $frm_str .= '</h2>';
            $frm_str .= '<div id="loadLinks">';
                $nbLinkDesc = $Class_LinkController->nbDirectLink(
                    $_SESSION['doc_id'],
                    $_SESSION['collection_id_choice'],
                    'desc'
                );
                if ($nbLinkDesc > 0) {
                    $frm_str .= '<img src="static.php?filename=cat_doc_incoming.gif"/>';
                    $frm_str .= $Class_LinkController->formatMap(
                        $Class_LinkController->getMap(
                            $_SESSION['doc_id'],
                            $_SESSION['collection_id_choice'],
                            'desc'
                        ),
                        'desc'
                    );
                    $frm_str .= '<br />';
                }
                $nbLinkAsc = $Class_LinkController->nbDirectLink(
                    $_SESSION['doc_id'],
                    $_SESSION['collection_id_choice'],
                    'asc'
                );
                if ($nbLinkAsc > 0) {
                    $frm_str .= '<img src="static.php?filename=cat_doc_outgoing.gif" />';
                    $frm_str .= $Class_LinkController->formatMap(
                        $Class_LinkController->getMap(
                            $_SESSION['doc_id'],
                            $_SESSION['collection_id_choice'],
                            'asc'
                        ),
                        'asc'
                    );
                    $frm_str .= '<br />';
                }
            $frm_str .= '</div>';
            if ($core_tools->test_service('add_links', 'apps', false)) {
				//$frm_str .= '<form>';
				$Links = "";
                include 'apps/'.$_SESSION['config']['app_id'].'/add_links.php';
                $frm_str .= $Links;
            }
        $frm_str .= '</div>';
	
	$frm_str .= '</dl>';
	$frm_str .= '</div>';
		
	$frm_str .= '<div class="toolsSplit">';	
		$frm_str .= '<table style="width:90%;">';	
		
		$frm_str .= '<tr>';
		$frm_str .= '<td style="width:5%">';	
		if ($prevFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:previousDoc();"><img src="static.php?filename=FlecheGaucheBleue.png"/></a>';
		else $frm_str .= '<a href="javascript://" ><img src="static.php?filename=FlecheGaucheGrise.png"/></a>';
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:20%">';	
		$nb_docs_tot = count($tab_docs)-1;
		$frm_str .= 'Page '.$tab_docs['cur_cpt'].' sur '.$nb_docs_tot;
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:5%">';	
		if ($nextFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:nextDoc();"><img src="static.php?filename=FlecheDroiteBleue.png"/></a>';
		else $frm_str .= '<a href="javascript://" ><img src="static.php?filename=FlecheDroiteGrise.png"/></a>';
		$frm_str .= '</td>';
		
		
		$frm_str .= '<td style="width:5%">';	
		$frm_str .= '<a href="javascript://" onclick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();"><img src="static.php?filename=flecheRetourOrange.png" title="Annuler"/></a>';
		$frm_str .= '</td>';
		
		$frm_str .= '<td>';	
		
		$frm_str .= '<b>'._ACTIONS.' : </b>';

		$actions  = $b->get_actions_from_current_basket($res_id, $coll_id, 'PAGE_USE');
		if(count($actions) > 0)
		{
			$frm_str .='<select name="chosen_action" id="uni_chosen_action">';
				$frm_str .='<option value="">'._CHOOSE_ACTION.'</option>';
				for($ind_act = 0; $ind_act < count($actions);$ind_act++)
				{
					$frm_str .='<option value="'.$actions[$ind_act]['VALUE'].'"';
					if($ind_act==0)
					{
						$frm_str .= 'selected="selected"';
					}
					$frm_str .= '>'.$actions[$ind_act]['LABEL'].'</option>';
				}
			$frm_str .='</select> ';
			$table = $sec->retrieve_table_from_coll($coll_id);
			$frm_str .= '<input type="button" name="send" id="send" value="'._VALIDATE.'" class="button" onclick="if (document.getElementById(\'uni_chosen_action\').value == 409) generateBordereau('.$res_id.');valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
		}
		
		//if ($workflow[$current_step]['consigne'] != "") $frm_str .= '<img src="static.php?filename=icone_consigne.png" title="'.$workflow[$current_step]['consigne'].'"/>';
		$frm_str .= '</td>';
		$frm_str .= '<input type="hidden" name="cur_rep" id="cur_rep" value="'.$tab_path_rep_file[0]['res_id'].'" >';
		$frm_str .= '<input type="hidden" name="cur_idAffich" id="cur_idAffich" value="1" >';
		
		$frm_str .= '<td style="width:5%";">';	
		$frm_str .= '<a href="javascript://" id="add_note" onclick="showNotesPage(\'tabricator2\');"><img src="static.php?filename=AddNotes.png&module=notes" title="Ajouter une note"/></a>';
		
		$frm_str .= '</td>';
		
		
		$frm_str .= '<td style="width:5%";">';	
		$frm_str .= '<a href="javascript://" id="uni_update_rep_link" onclick="';
		if($data['category_id']['value'] == "incoming") $frm_str .= 'window.open(\''.$_SESSION['config']['businessappurl'] . 'index.php?display=true&module=attachments&page=update_attachments&mode=up&collId='.$coll_id.'&id='.$tab_path_rep_file[0]['res_id'].'\',\'\',\'height=301, width=301,scrollbars=yes,resizable=yes\');';
		$frm_str .= '"><img src="static.php?filename=iconeModifReponse.png" title="Modifier la réponse"/></a>';
		
		$frm_str .= '</td>';
		$frm_str .= '</tr>';	
		$frm_str .= '</table>';	
		$frm_str .= '</div>';
		
    $frm_str .= '</div>';   
	$frm_str .= '</div>';  
	/**
	* FIN FORMULAIRE UNIFIE
	**/
	
	/*** Extra javascript ***/
	$frm_str .= '<script type="text/javascript">launchTabri();window.scrollTo(0,0);';
	$frm_str .='</script>';
	return addslashes($frm_str);
}

/**
 * Checks the action form
 *
 * @param $form_id String Identifier of the form to check
 * @param $values Array Values of the form
 * @return Bool true if no error, false otherwise
 **/
function check_form($form_id,$values)
{
		//writeLogIndex("GO check_form !!");

    $_SESSION['action_error'] = '';
    if(count($values) < 1 || empty($form_id))
    {
        $_SESSION['action_error'] =  _FORM_ERROR;
        return false;
    }
    else
    {
       
        return true;
    }
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


/**
 * Action of the form : update the database
 *
 * @param $arr_id Array Contains the res_id of the document to validate
 * @param $history String Log the action in history table or not
 * @param $id_action String Action identifier
 * @param $label_action String Action label
 * @param $status String  Not used here
 * @param $coll_id String Collection identifier
 * @param $table String Table
 * @param $values_form String Values of the form to load
 **/
function manage_form($arr_id, $history, $id_action, $label_action, $status,  $coll_id, $table, $values_form )
{
	//writeLogIndex("GO MANAGE !!");
	$res_id = $arr_id[0];
	$dir_field = get_value_fields($values_form, 'directeur');
	$type_view = get_value_fields($values_form, 'typeView');
	$action_chosen = get_value_fields($values_form, $type_view.'_chosen_action');
	//writeLogIndex($action_chosen);
	$dir_field_split = explode('-',$dir_field);
	
	$dir_user = $dir_field_split[0];
	$dir_ent = $dir_field_split[1];
	
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	$sec = new security();
	$table = $sec->retrieve_table_from_coll($coll_id);
	
	if ($action_chosen == 409){
		$db = new dbquery();
		$db->connect();
		$db->query("UPDATE circuit_visa SET date_visa = CURRENT_TIMESTAMP WHERE res_id = $res_id AND vis_user='".$_SESSION['user']['UserId']."'");
	}
   
    return array('result' => $res_id.'#', 'history_msg' => '');
}