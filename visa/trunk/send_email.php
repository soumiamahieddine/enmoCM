<?php

/**
* @brief   Action : Préparation du circuit de visa
*
* Ouverture, dans une fenêtre séparée en deux, d'un document entrant (+ ses informations) d'une part
* et de ses projets de réponses d'autre part. L'action a effectuée est de préparer un circuit de visa
* pour le reste des opérations. Ce circuit est stockée dans la BDD (table circuit_visa)
*
* @file
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
    $logFileOpened = fopen($_SESSION['config']['logdir']."send_email.log", 'a');
    fwrite($logFileOpened, '[' . date('d') . '/' . date('m') . '/' . date('Y')
        . ' ' . date('H') . ':' . date('i') . ':' . date('s') . '] ' . $EventInfo
        . "\r\n"
    );
    fclose($logFileOpened);
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
	$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=logo_action_18.png"  align="middle" alt="" />'._SEND_MAIL.' '._NUM.$resChrono->alt_identifier;
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
	
		if ($data['category_id']['value'] == "incoming") $frm_str .= '<dt id="onglet_entrant">'._ENTRANT.'</dt><dd>';
		else $frm_str .= '<dt id="onglet_entrant">'._SPONTANEOUS.'</dt><dd>';
		$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&coll_id='.$coll_id.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';
		
		$frm_str .= '</dd>';
		
	
	$frm_str .= '<dt>Détails</dt><dd>';
	
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
		$frm_str .= '<dt>'. _NOTES.$nbr_notes .'</dt><dd><h2>'. _NOTES .'</h2><iframe name="list_notes_doc" id="list_notes_doc" src="'. $_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=notes&identifier='. $res_id .'&origin=document&coll_id='.$coll_id.'&load&size=full" frameborder="0" scrolling="no" width="99%" height="570px"></iframe></dd> ';	
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
	
		$frm_str .= '<dt>'. _DONE_ANSWERS .$nb_rep.'</dt><dd><iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&no_modify&view_only&mode=normal&status=NEW&resId='.$res_id.'" name="list_answ" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="list_answ"></iframe></dd>';
	
	
		$countAttachments = "select res_id, creation_date, title, format from " 
			. $_SESSION['tablename']['attach_res_attachments'] 
			. " where res_id_master = " . $_SESSION['doc_id'] 
			. " and coll_id ='" . $_SESSION['collection_id_choice'] 
			. "' and status <> 'DEL' and status='PJ' ";
		$req->query($countAttachments);
		if ($req->nb_result() > 0) {
			$nb_attach = ' (' . ($req->nb_result()). ')';
		}
	
		$frm_str .= '<dt>'. _ATTACHED_DOC .$nb_attach.'</dt><dd><iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&no_modify&view_only&mode=normal&status=PJ&resId='.$res_id.'" name="list_answ" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="list_answ"></iframe></dd>';
		
	}
		
	/*if ($core->test_service('sendmail', 'sendmail', false) === true) {
		require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
		$sendmail_tools    = new sendmail();
		 //Count mails
		$nbr_emails = $sendmail_tools->countUserEmails($res_id, $coll_id);
		if ($nbr_emails > 0 ) $nbr_emails = ' ('.$nbr_emails.')';  else $nbr_emails = '';
	   
		
		$frm_str .= '<dt>' . _SENDED_EMAILS.$nbr_emails .'</dt><dd>';
		//Emails iframe
		$frm_str .=  $core->execute_modules_services(
			$_SESSION['modules_services'], 'details', 'frame', 'sendmail', 'sendmail'
		);
		
		$frm_str .= '</dd>';
	}*/
	
	$frm_str .= '<dt id="onglet_avancement">Avancement</dt><dd id="page_avancement">';
	$frm_str .= '<h2>Workflow</h2>';
	$visa = new visa();
	$workflow = $visa->getVisaWorkflow($res_id, $coll_id);
	$current_step = $visa->getCurrentVisaStep($res_id, $table);
	
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
	
	$frm_str .= '</dl>';
	$frm_str .= '</div>';
		
	$frm_str .= '<div class="toolsSplit">';	
		$frm_str .= '<table style="width:45%;">';	
		
		$frm_str .= '<tr>';
		$frm_str .= '<td style="width:5%">';	
		if ($prevFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:previousDoc();"><img src="static.php?filename=FlecheGaucheBleue.png"/></a>';
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:20%">';	
		$nb_docs_tot = count($tab_docs)-1;
		$frm_str .= 'Page '.$tab_docs['cur_cpt'].' sur '.$nb_docs_tot;
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:5%">';	
		if ($nextFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:nextDoc();"><img src="static.php?filename=FlecheDroiteBleue.png"/></a>';
		$frm_str .= '</td>';
		
		/*$frm_str .= '<td style="width:5%">';	
		$frm_str .= '<a href="javascript://" onclick="javascript:switchViewTab(1);"><img src="static.php?filename=splitView.png" title="Rassembler les vues"/></a>';
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
	//Onglet préparation du circuit de visa
	if ($core->test_service('sendmail', 'sendmail', false) === true) {
		require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
		$sendmail_tools    = new sendmail();
		 //Count mails
		$nbr_emails = $sendmail_tools->countUserEmails($res_id, $coll_id);
		if ($nbr_emails > 0 ) $nbr_emails = ' ('.$nbr_emails.')';  else $nbr_emails = '';
	   
		
		$frm_str .= '<dt>' . _SENDED_EMAILS.$nbr_emails .'</dt><dd>';
		//Emails iframe
		$frm_str .=  $core->execute_modules_services(
			$_SESSION['modules_services'], 'details', 'frame', 'sendmail', 'sendmail'
		);
		
		$frm_str .= '</dd>';
	}
	
	
	
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
			$frm_str .= '<input type="button" name="send" id="send" value="'._VALIDATE.'" class="button" onclick="valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
		}
		
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
	$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=logo_action_18.png"  align="middle" alt="" />'._SEND_MAIL.' '._NUM.$resChrono->alt_identifier;
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
	//Onglet préparation du circuit de visa
	if ($core->test_service('sendmail', 'sendmail', false) === true) {
		require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
		$sendmail_tools    = new sendmail();
		 //Count mails
		$nbr_emails = $sendmail_tools->countUserEmails($res_id, $coll_id);
		if ($nbr_emails > 0 ) $nbr_emails = ' ('.$nbr_emails.')';  else $nbr_emails = '';
	   
		
		$frm_str .= '<dt>' . _SENDED_EMAILS.$nbr_emails .'</dt><dd>';
		//Emails iframe
		$frm_str .=  $core->execute_modules_services(
			$_SESSION['modules_services'], 'details', 'frame', 'sendmail', 'sendmail'
		);
		
		$frm_str .= '</dd>';
	}
	
	if ($data['category_id']['value'] == "incoming") $frm_str .= '<dt id="onglet_entrant">'._ENTRANT.'</dt><dd>';
		else $frm_str .= '<dt id="onglet_entrant">'._SPONTANEOUS.'</dt><dd>';
		$frm_str .= '<iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&coll_id='.$coll_id.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';
	
	$frm_str .= '</dd>';
		
	
	$frm_str .= '<dt>Détails</dt><dd>';
	
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
		$frm_str .= '<dt>'. _NOTES.$nbr_notes .'</dt><dd><h2>'. _NOTES .'</h2><iframe name="list_notes_doc" id="list_notes_doc" src="'. $_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=notes&identifier='. $res_id .'&origin=document&coll_id='.$coll_id.'&load&size=full" frameborder="0" scrolling="no" width="99%" height="570px"></iframe></dd> ';	
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
	
		$frm_str .= '<dt>'. _DONE_ANSWERS .$nb_rep.'</dt><dd><iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&no_modify&view_only&mode=normal&status=NEW&resId='.$res_id.'" name="list_answ" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="list_answ"></iframe></dd>';
	
	
		$countAttachments = "select res_id, creation_date, title, format from " 
			. $_SESSION['tablename']['attach_res_attachments'] 
			. " where res_id_master = " . $_SESSION['doc_id'] 
			. " and coll_id ='" . $_SESSION['collection_id_choice'] 
			. "' and status <> 'DEL' and status='PJ' ";
		$req->query($countAttachments);
		if ($req->nb_result() > 0) {
			$nb_attach = ' (' . ($req->nb_result()). ')';
		}
	
		$frm_str .= '<dt>'. _ATTACHED_DOC .$nb_attach.'</dt><dd><iframe src="' . $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&no_modify&view_only&mode=normal&status=PJ&resId='.$res_id.'" name="list_answ" width="100%" height="590px" align="left" scrolling="no" frameborder="0" id="list_answ"></iframe></dd>';
		
	}
		
	
	
	$frm_str .= '<dt id="onglet_avancement">Avancement</dt><dd id="page_avancement">';
	$frm_str .= '<h2>Workflow</h2>';
	$visa = new visa();
	$workflow = $visa->getVisaWorkflow($res_id, $coll_id);
	$current_step = $visa->getCurrentVisaStep($res_id, $table);
	
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
	
	$frm_str .= '</dl>';
	$frm_str .= '</div>';
		
	$frm_str .= '<div class="toolsSplit">';	
		$frm_str .= '<table style="width:90%;">';	
		
		$frm_str .= '<tr>';
		$frm_str .= '<td style="width:5%">';	
		if ($prevFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:previousDoc();"><img src="static.php?filename=FlecheGaucheBleue.png"/></a>';
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:20%">';	
		$nb_docs_tot = count($tab_docs)-1;
		$frm_str .= 'Page '.$tab_docs['cur_cpt'].' sur '.$nb_docs_tot;
		$frm_str .= '</td>';
		
		$frm_str .= '<td style="width:5%">';	
		if ($nextFileExists)
			$frm_str .= '<a href="javascript://" onclick="javascript:nextDoc();"><img src="static.php?filename=FlecheDroiteBleue.png"/></a>';
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
			$frm_str .= '<input type="button" name="send" id="send" value="'._VALIDATE.'" class="button" onclick="valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
		}
		
		$frm_str .= '</td>';
		
		$frm_str .= '</tr>';	
		$frm_str .= '</table>';	
		$frm_str .= '</div>';
	$frm_str .= '</div>';        
    $frm_str .= '</div>';  
	/*** Extra javascript ***/
	
	//showEmailForm(\'index.php?display=true&module=sendmail&page=sendmail_ajax_content&mode=add&identifier='.$res_id.'&origin=document&coll_id=letterbox_coll&size=medium\', \'\', \'\', \'sendmail_iframe\');
	$frm_str .= '<script type="text/javascript">launchTabri();window.scrollTo(0,0);showEmailForm(\'index.php?display=true&module=sendmail&page=sendmail_ajax_content&mode=add&identifier='.$res_id.'&origin=document&coll_id=letterbox_coll&size=medium\', \'\', \'\', \'sendmail_iframe\'); ';
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

function get_circuit($values){
	$tab_circuit = array();
	foreach($values as $key=>$val){
		$vals = explode("_",$val['ID']);
		if (isset($vals[0]) && $vals[0] == "conseiller"){
			array_push($tab_circuit,array('sequence'=>$vals[1], 'vis_user'=>$val['VALUE'], 'consigne'=>get_value_fields($values, 'consigne_'.$vals[1])));
		}
	}	
	return $tab_circuit;
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
	writeLogIndex("Action choisie = ".$action_chosen);
	//writeLogIndex($action_chosen);
	$dir_field_split = explode('-',$dir_field);
	
	$dir_user = $dir_field_split[0];
	$dir_ent = $dir_field_split[1];
	
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	$sec = new security();
	$table = $sec->retrieve_table_from_coll($coll_id);
	
	/*$circuit_visa = new visa();
	$circuit_visa->saveWorkflow($res_id, $coll_id, get_circuit($values_form));*/
   
    return array('result' => $res_id.'#', 'history_msg' => '');
}