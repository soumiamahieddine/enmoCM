<?php

/**
* @brief   Action : Viser le courrier
*
* Ouverture, dans une fenêtre séparée en deux, d'un document entrant (+ ses informations) d'une part
* et du formulaire de construction du dossier d'autre part. Une fois que les différentes cases sont 
* cochées, il y a la construction du dossier lui même qui est faite en plusieurs étapes : 
* - Construction de l'intercalaire faisant l'inventaire des pièces (format PDF)
* - Elaboration du bordereau de transmission (s'il est à imprimer) (format PDF)
* - Elaboration d'un fichier PDF avec les notes à imprimer
* - Conversion des pièces jointes à imprimer ( /!\ à travailler )
* - Concaténation des différentes pièces pour créer le dossier
*
* @file imprim_dossier
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
require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_request.php");
require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_security.php");
	
require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_resource.php");
require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";

	
$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";


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
	$db->query("select filename, path,title,res_id,status,typist,creation_date,format,attachment_type from res_view_attachments where status <> 'DEL' AND status = 'TRA' AND res_id_master = " . $res_id . " order by status, creation_date asc");
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

function get_attach_path_id($id_attach, $id_doc, $coll_id)
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

    $db->query("select docserver_id, path, filename from ".$view." where res_id = ".$id_doc);
    $res = $db->fetch_object();
    $docserver_id = $res->docserver_id;
	
	
	$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver_id."'");
    $res = $db->fetch_object();
    $docserver_path = $res->path_template;
	$db->query("select filename, path,title,res_id,status,typist,creation_date  from res_attachments where res_id = " . $id_attach );
	$res2 = $db->fetch_object();
	$filename=$res2->filename;
	$path = preg_replace('/#/', DIRECTORY_SEPARATOR, $res2->path);
	$path = str_replace ("//","/",$path);
	$filename_pdf = str_replace(pathinfo($filename, PATHINFO_EXTENSION), "pdf",$filename);
	return $docserver_path.$path.$filename_pdf;
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
function getNote($id_note){
	$note = array();
	$requete = "SELECT * from notes where id = $id_note";
	$db = new dbquery();
	$db->connect();
	$db->query($requete);
	$res=$db->fetch_object();
	$note['user'] = $res->user_id;
	$note['text'] = $res->note_text;
	$date = explode("-",$res->date_note);
	$note['date'] = $date[2]."/".$date[1]."/".$date[0];
	
	return $note;
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



function createPdfNotes($list_notes){
	
	define('FPDF_FONTPATH','modules/visa/class/font/');
	//above line is import to define, otherwise it gives an error : Could not include font metric file
	require('modules/visa/class/fpdf.php');
	require('modules/visa/class/fpdi.php');
	class ChargePdf extends FPDI
	{
	function LoadData($tab)
	{
		// Lecture des lignes du fichier
		$data = array();
		/*foreach($lines as $line)
			$data[] = explode(';',trim($line));*/
		for ($i = 0; $i < count($tab); $i++){
			$note = getNote($tab[$i]);
			$user = getInfosUser($note['user']);
			
			$data[] = array(utf8_decode($user['prenom']).' '.utf8_decode($user['nom']),$note['date'],utf8_decode($note['text']));
		}
		return $data;
	}

	var $widths;
	var $aligns;

	function SetWidths($w)
	{
		$this->widths=$w;
	}

	function SetAligns($a)
	{
		$this->aligns=$a;
	}

	function Row($data)
	{
		//Calcule la hauteur de la ligne
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=5*$nb;
		$this->CheckPageBreak($h);
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];	
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			$x=$this->GetX();$y=$this->GetY();
			$this->Rect($x,$y,$w,$h);
			$this->MultiCell($w,5,$data[$i],0,$a);
			$this->SetXY($x+$w,$y);
		}
		$this->Ln($h);
	}

	function CheckPageBreak($h)
	{
		if($this->GetY()+$h>$this->PageBreakTrigger)$this->AddPage($this->CurOrientation);
	}

	function NbLines($w,$txt)
	{
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")	$nb--;
		$sep=-1;$i=0;$j=0;$l=0;$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;$sep=-1;$j=$i;$l=0;$nl++;
				continue;
			}
			if($c==' ')	$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)	$i++;
				}
				else
					$i=$sep+1;$sep=-1;$j=$i;$l=0;$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}
	}

	$pdf = new ChargePdf();
	$pdf->addPage();
	$data = $pdf->LoadData($list_notes);
	$header = array('Nom', 'Consigne', 'Date');
	$pdf->SetFont('Arial','B',12);
	$pdf->SetY(30);
	//$pdf->Table($header, $data);
	$pdf->SetWidths(array(40,30,100));
	$pdf->SetAligns(array('C','C','C'));
	$pdf->Row(array('Nom', 'Date', 'Note'));
	$pdf->SetAligns(array('L','C','L'));
	$pdf->SetFont('Arial','',10);
	foreach($data as $d){
		$pdf->Row($d);
	}
	
	$outputFile = $_SESSION['config']['tmppath'] . DIRECTORY_SEPARATOR . "listNotes".$_SESSION['user']['UserId'].".pdf";
	$pdf->Output($outputFile, 'F');
	return $outputFile;
}

function getDossier($values_form, $typeView){
	$dossier = array();
	$list_notes = array();
	foreach($values_form as $v){
		if ($v['ID'] == "contenu_dossier_uni" && $typeView=="uni"){
			$val = explode("_",$v['VALUE']);
			array_push($dossier,array('type'=>$val[0],'id'=>$val[1]));
			if ($val[0] == "note"){
				array_push($list_notes,$val[1]);
			}
		}
		elseif ($v['ID'] == "contenu_dossier_split" && $typeView=="split"){
			$val = explode("_",$v['VALUE']);
			array_push($dossier,array('type'=>$val[0],'id'=>$val[1]));
			if ($val[0] == "note"){
				array_push($list_notes,$val[1]);
			}
		}
	}
	if (count($list_notes) > 0){
		$path_file_notes = createPdfNotes($list_notes);
		array_push($dossier,array('type'=>'pdf_notes','path'=>$path_file_notes));
	}
	
	return $dossier;
}

function getPaths($dossier, $id_doc){
	//$paths = array();
	//foreach ($dossier as $d){
	for ($i = 0; $i < count($dossier); $i++){
		$id = $dossier[$i]['id'];
		if ($dossier[$i]['type'] == "initial"){
			$db = new dbquery();
			$db->connect();
			$resource = new resource();
			$whereClause = ' and 1=1';
			$adrTable = $_SESSION['collections'][0]['adr'];
			/* TEST VERSION */
			$sec = new security();
			$versionTable = $sec->retrieve_version_table_from_coll_id(
				'letterbox_coll'
			);
			$selectVersions = "select res_id from " 
            . $versionTable . " where res_id_master = " 
            . $id . " and status <> 'DEL' order by res_id desc";
			$dbVersions = new dbquery();
			$dbVersions->connect();
			$dbVersions->query($selectVersions);
			$lineLastVersion = $dbVersions->fetch_object();
			$lastVersion = $lineLastVersion->res_id;
			if ($lastVersion <> ''){
				$adr = $resource->getResourceAdr(
				$versionTable,
				$lastVersion,
				$whereClause,
				''
				);
			}
			else {
				$adr = $resource->getResourceAdr(
				"res_view_letterbox",
				$id,
				$whereClause,
				$adrTable
				);
			}
			/****************/
			//writeLogIndex(print_r($adr,true));
			
			$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$adr[0][0]['docserver_id']."'");
			$res = $db->fetch_object();
			$docserver_path = $res->path_template;
			
			if (isset($adr[0][0]['visu_filename']) && $adr[0][0]['visu_filename'] != "") $dossier[$i]['path'] = str_replace("//","/",$docserver_path . str_replace("#", DIRECTORY_SEPARATOR , $adr[0][0]['path']) . $adr[0][0]['visu_filename']);
			else $dossier[$i]['path'] = str_replace("//","/",$docserver_path . str_replace("#", DIRECTORY_SEPARATOR , $adr[0][0]['path']) . $adr[0][0]['filename']);
			
		}
		elseif ($dossier[$i]['type'] == "attach"){
			//writeLogIndex("Attach num $id");
			$path = get_attach_path_id($id, $id_doc, "letterbox_coll");
			//writeLogIndex(print_r($path,true));
			$dossier[$i]['path'] = $path;
		}
		elseif ($dossier[$i]['type'] == "note"){
		
		}
	}
	return $dossier;
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
	
    $_SESSION['doc_id'] = $res_id;
    $frm_str = '';
    //require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
    require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
    require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_types.php");
    //require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

    $sec =new security();
    $core =new core_tools();
    $b = new basket();
    $type = new types();
    $business = new business_app_tools();

	if ($core->is_module_loaded('entities')) {
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
	$db->query("select alt_identifier, typist from " 
		. $view 
		. " where res_id = " . $res_id);
	
	$resChrono = $db->fetch_object();
	$typist = $resChrono->typist;
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
	$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=logo_action_18.png"  align="middle" alt="" />'._IMPRIM_DOSSIER.' '._NUM.$res_id;
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
	
		$frm_str .= '<dt id="onglet_doc">'._ENTRANT.'</dt><dd id="page_doc"><iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&coll_id='.$coll_id.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';
		
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
	$workflow = $visa->getVisaWorkflow($res_id, $coll_id);
	$current_step = $visa->getCurrentVisaStep($res_id, $table);
	
	$tab_histo = getHistoryActions($res_id);
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
            if ($core->test_service('add_links', 'apps', false)) {
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
	
	$frm_str .= '<dt>Dossier</dt><dd>';
	
	$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
	$frm_str .= '<h2>Contenu du dossier de réponse</h2>';
	
	if ($data['category_id']['value'] == "incoming")$frm_str .= '<p><b>Requérent</b> : '.$data['exp_contact_id']['show_value'].'</p>';
	else $frm_str .= '<p><b>Requérent</b> : '.$data['dest_contact_id']['show_value'].'</p>';
	$frm_str .= '<p><b>Objet</b> : '.$data['subject']['show_value'].'</p>';
	$frm_str .= '<hr/>';
	
	/*$circuit_visa = new visa();
	$frm_str .= $circuit_visa->getList($res_id, $coll_id, true);*/
	$tab_attach_file = get_attach_path($res_id, $coll_id);
	$frm_str .= '<table style="width:100%;">';
	$frm_str .= '<thead><tr><th style="width:25%;"></th><th style="width:40%;">Titre</th><th style="width:20%;">Rédacteur</th><th style="width:10%;">Date</th><th style="width:5%;"></th></tr></thead>';
	$frm_str .= '<tbody>';
	if ($data['category_id']['value'] == "incoming"){
	$frm_str .= '<tr><td><h3>+ Courrier entrant</h3></td><td></td><td></td><td></td><td></td></tr>';
	$frm_str .= '<tr><td></td><td>'.$data['subject']['show_value'].'</td><td>'.$data['exp_contact_id']['show_value'].'</td><td>'.$data['doc_date']['show_value'].'</td><td><input id="contenu_dossier_split" type="checkbox" name="dossier[]" value="initial_'.$res_id.'" checked></input></td></tr>';	
	}
	else{
		$frm_str .= '<tr><td><h3>+ Courrier sortant</h3></td><td></td><td></td><td></td><td></td></tr>';
		$frm_str .= '<tr><td></td><td>'.$data['subject']['show_value'].'</td><td>'.$typist.'</td><td>'.$data['doc_date']['show_value'].'</td><td><input id="contenu_dossier_split" type="checkbox" name="dossier[]" value="initial_'.$res_id.'" checked></input></td></tr>';	
	}
	$currentStat = "";
	//$frm_str .= print_r($tab_attach_file,true);
	$bordereauExists = false;
	for ($i=0; $i<count($tab_attach_file);$i++){
		$auteur = getInfosUser($tab_attach_file[$i]['typist']);
		if ($tab_attach_file[$i]['attachment_type'] != $currentStat){
			
			if ($tab_attach_file[$i]['attachment_type'] == "response_project"){
				$frm_str .= '<tr><td><h3>+ Réponse(s) effectuée(s)</h3></td><td></td><td></td><td></td><td></td></tr>';			
				$checked = " checked";				
			}
			if ($tab_attach_file[$i]['attachment_type'] == "simple_attachment"){
				$frm_str .= '<tr><td><h3>+ Pièce(s) jointe(s)</h3></td><td></td><td></td><td></td><td></td></tr>';	
				$checked = " ";	
			}
			if ($tab_attach_file[$i]['attachment_type'] == "routing"){
				$frm_str .= '<tr><td><h3>+ Fiche de circulation</h3></td><td></td><td></td><td></td><td></td></tr>';	
				$checked = " checked ";
				$bordereauExists = true;
			}
				
			$currentStat = $tab_attach_file[$i]['attachment_type'];
		}
		
		if ($tab_attach_file[$i]['attachment_type'] == "simple_attachment" && $tab_attach_file[$i]['format'] != "pdf") $disabled = " disabled title=\"Il n'est pas possible d'imprimer une pièce d'un autre format que pdf\" ";	
		else  $disabled = " ";	
		
		$frm_str .= '<tr><td></td><td><a href="index.php?display=true&module=attachments&page=view_attachment&id='.$tab_attach_file[$i]['res_id'].'&res_id_master='.$res_id.'" target="_blank">'.$tab_attach_file[$i]['title'].'</a></td><td>'.$auteur['prenom'].' '.$auteur['nom'].'</td><td>'.$tab_attach_file[$i]['date'].'</td><td><input type="checkbox" id="contenu_dossier_uni" name="dossier[]" value="attach_'.$tab_attach_file[$i]['res_id'].'" '.$checked.' '.$disabled.'></input></td></tr>';	
	}
	
	if (!$bordereauExists){
		$frm_str .= "<tr><td><h3 id=\"tit_bord\" onclick=\"window.open('".$_SESSION['config']['businessappurl']."/index.php?display=true&module=content_management&page=applet_popup_launcher&objectType=bordereauFromTemplate&objectId=113&objectTable=res_letterbox&resMaster=".$res_id."', '', 'height=301, width=301,scrollbars=no,resizable=no,directories=no,toolbar=no');\" ". "onmouseover=\"this.style.cursor='pointer';\" >+ Générer la fiche de circulation</h3></td><td></td><td></td><td></td><td></td></tr>";	
		$frm_str .= '<tr id="line_bord"><td></td><td></td><td></td><td></td><td><input type="checkbox" name="dossier[]" value="attach_" checked></input></td></tr>';	
	}
	
	$tab_notes = getNotes($res_id);
	if (count($tab_notes) > 0){
		$frm_str .= '<tr><td><h3>+ Notes</h3></td><td></td><td></td><td></td><td></td></tr>';	
		
		foreach($tab_notes as $note){
			$auteur = getInfosUser($note['user_id']);
			$frm_str .= '<tr><td></td><td>'.$note['note_text'].'</td><td>'.$auteur['prenom'].' '.$auteur['nom'].'</td><td>'.$note['date_note'].'</td><td><input type="checkbox" id="contenu_dossier_uni" name="dossier[]" value="note_'.$note['id_note'].'"  ></input></td></tr>';	
		}
	}
	
	
	$frm_str .= '</tbody>';
	$frm_str .= '</table>';
	$frm_str .= '<hr/>';
	$frm_str .= '</dd>';
	
	//Onglet préparation du circuit de visa
	$frm_str .= '<dt>Circuit de visa</dt><dd>';
	
	$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
	$circuit_visa = new visa();
	$frm_str .= $circuit_visa->getList($res_id, $coll_id, false);
	
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
	$frm_str .= '<h1 class="tit" id="action_title"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=logo_action_18.png"  align="middle" alt="" />'._IMPRIM_DOSSIER.' '._NUM.$res_id;
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
		$frm_str .= '<dt>Dossier</dt><dd>';
	
	$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
	$frm_str .= '<h2>Contenu du dossier de réponse</h2>';
	
	if ($data['category_id']['value'] == "incoming") $frm_str .= '<p><b>Requérent</b> : '.$data['exp_contact_id']['show_value'].'</p>';
	else $frm_str .= '<p><b>Requérent</b> : '.$data['dest_contact_id']['show_value'].'</p>';
	$frm_str .= '<p><b>Objet</b> : '.$data['subject']['show_value'].'</p>';
	$frm_str .= '<hr/>';
	
	/*$circuit_visa = new visa();
	$frm_str .= $circuit_visa->getList($res_id, $coll_id, true);*/
	$tab_attach_file = get_attach_path($res_id, $coll_id);
	$frm_str .= '<table style="width:100%;">';
	$frm_str .= '<thead><tr><th style="width:25%;"></th><th style="width:40%;">Titre</th><th style="width:20%;">Rédacteur</th><th style="width:10%;">Date</th><th style="width:5%;"></th></tr></thead>';
	$frm_str .= '<tbody>';
	if ($data['category_id']['value'] == "incoming"){
	$frm_str .= '<tr><td><h3>+ Courrier entrant</h3></td><td></td><td></td><td></td><td></td></tr>';
	$frm_str .= '<tr><td></td><td>'.$data['subject']['show_value'].'</td><td>'.$data['exp_contact_id']['show_value'].'</td><td>'.$data['doc_date']['show_value'].'</td><td><input id="contenu_dossier_uni" type="checkbox" name="dossier[]" value="initial_'.$res_id.'" checked></input></td></tr>';	
	}
	else {
		$frm_str .= '<tr><td><h3>+ Courrier sortant</h3></td><td></td><td></td><td></td><td></td></tr>';
	$frm_str .= '<tr><td></td><td>'.$data['subject']['show_value'].'</td><td>'.$typist.'</td><td>'.$data['doc_date']['show_value'].'</td><td><input id="contenu_dossier_uni" type="checkbox" name="dossier[]" value="initial_'.$res_id.'" checked></input></td></tr>';	
	}
	$currentStat = "";
	//$frm_str .= print_r($tab_attach_file,true);
	$bordereauExists = false;
	for ($i=0; $i<count($tab_attach_file);$i++){
		$auteur = getInfosUser($tab_attach_file[$i]['typist']);
		if ($tab_attach_file[$i]['attachment_type'] != $currentStat){
			
			if ($tab_attach_file[$i]['attachment_type'] == "response_project"){
				$frm_str .= '<tr><td><h3>+ Réponse(s) effectuée(s)</h3></td><td></td><td></td><td></td><td></td></tr>';			
				$checked = " checked";				
			}
			if ($tab_attach_file[$i]['attachment_type'] == "simple_attachment"){
				$frm_str .= '<tr><td><h3>+ Pièce(s) jointe(s)</h3></td><td></td><td></td><td></td><td></td></tr>';	
				$checked = " ";	
			}
			if ($tab_attach_file[$i]['attachment_type'] == "routing"){
				$frm_str .= '<tr><td><h3>+ Fiche de circulation</h3></td><td></td><td></td><td></td><td></td></tr>';	
				$checked = " checked ";
				$bordereauExists = true;
			}
				
			$currentStat = $tab_attach_file[$i]['attachment_type'];
		}
		
		if ($tab_attach_file[$i]['attachment_type'] == "simple_attachment" && $tab_attach_file[$i]['format'] != "pdf") $disabled = " disabled title=\"Il n'est pas possible d'imprimer une pièce d'un autre format que pdf\" ";	
		else  $disabled = " ";	
		
		$frm_str .= '<tr><td></td><td><a href="index.php?display=true&module=attachments&page=view_attachment&id='.$tab_attach_file[$i]['res_id'].'" target="_blank">'.$tab_attach_file[$i]['title'].'</a></td><td>'.$auteur['prenom'].' '.$auteur['nom'].'</td><td>'.$tab_attach_file[$i]['date'].'</td><td><input type="checkbox" id="contenu_dossier_uni" name="dossier[]" value="attach_'.$tab_attach_file[$i]['res_id'].'" '.$checked.' '.$disabled.'></input></td></tr>';	
	}
	
	if (!$bordereauExists){
		$frm_str .= "<tr><td><h3 id=\"tit_bord\" onclick=\"window.open('".$_SESSION['config']['businessappurl']."/index.php?display=true&module=content_management&page=applet_popup_launcher&objectType=bordereauFromTemplate&objectId=113&objectTable=res_letterbox&resMaster=".$res_id."', '', 'height=301, width=301,scrollbars=no,resizable=no,directories=no,toolbar=no');\" ". "onmouseover=\"this.style.cursor='pointer';\" >+ Générer la fiche de circulation</h3></td><td></td><td></td><td></td><td></td></tr>";	
		$frm_str .= '<tr id="line_bord"><td></td><td></td><td></td><td></td><td><input type="checkbox" name="dossier[]" value="attach_" checked></input></td></tr>';	
	}
	
	$tab_notes = getNotes($res_id);
	if (count($tab_notes) > 0){
		$frm_str .= '<tr><td><h3>+ Notes</h3></td><td></td><td></td><td></td><td></td></tr>';	
		
		foreach($tab_notes as $note){
			$auteur = getInfosUser($note['user_id']);
			$frm_str .= '<tr><td></td><td>'.$note['note_text'].'</td><td>'.$auteur['prenom'].' '.$auteur['nom'].'</td><td>'.$note['date_note'].'</td><td><input type="checkbox" id="contenu_dossier_uni" name="dossier[]" value="note_'.$note['id_note'].'"  ></input></td></tr>';	
		}
	}
	
	$frm_str .= '</tbody>';
	$frm_str .= '</table>';
	$frm_str .= '<hr/>';
	$frm_str .= '</dd>';
	
	//Onglet préparation du circuit de visa
	$frm_str .= '<dt>Circuit de visa</dt><dd>';
	
	$frm_str .= '<div id="frm_error_'.$id_action.'" class="indexing_error"></div>';		
	$circuit_visa = new visa();
	$frm_str .= $circuit_visa->getList($res_id, $coll_id, false);
	
	$frm_str .= '</dd>';
	
		$frm_str .= '<dt id="onglet_doc">'._ENTRANT.'</dt><dd id="page_doc"><iframe src="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&coll_id='.$coll_id.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';
		
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
	$workflow = $visa->getVisaWorkflow($res_id, $coll_id);
	$current_step = $visa->getCurrentVisaStep($res_id, $table);
	
	$tab_histo = getHistoryActions($res_id);
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
            if ($core->test_service('add_links', 'apps', false)) {
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
		
		
		/*$frm_str .= '<td style="width:5%">';	
		$frm_str .= '<a href="javascript://" onclick="javascript:switchViewTab(2);"><img src="static.php?filename=splitView.png" title="Passer en côte-à-côte"/></a>';
		$frm_str .= '</td>';*/
		
		
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

function concat_files($dossier, $res_id){
	require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docservers_controler.php";
	require_once "core".DIRECTORY_SEPARATOR."docservers_tools.php";
	require_once 'core/class/docserver_types_controler.php';

	$cmd = "pdftk ";
	foreach($dossier as $d){
		if (isset($d['path']))
			$cmd .= $d['path']." ";
	}
	
	
	
	$docserv_control = new docservers_controler();
	$docserverTypeControler = new docserver_types_controler();
	
	$docserv = $docserv_control->getDocserverToInsert("letterbox_coll");
	$docserverTypeObject = $docserverTypeControler->get($docserv->docserver_type_id);
	$storeFolder = $docserv->path_template;
	$pathOnDocserver = Ds_createPathOnDocServer(
            $storeFolder
        );
	$storeFolder = str_replace("//", "/", $pathOnDocserver['destinationDir']);
	$pathondocserver = $storeFolder;
	$fname_tab = $docserv_control->getNextFileNameInDocserver($pathondocserver);	
	$outputFile = array();
	$outputFile['filepath'] = $fname_tab['destinationDir'].$fname_tab['fileDestinationName'].".pdf";
	
	$path_bdd=explode("/",$fname_tab['destinationDir']);
	$cmd .= " cat output ".$outputFile['filepath'];
	$outputFile['path'] = $path_bdd[count($path_bdd)-4]."#".$path_bdd[count($path_bdd)-3] . "#" . $path_bdd[count($path_bdd)-2]."#";
	$outputFile['filename'] = $fname_tab['fileDestinationName'].".pdf";
	exec($cmd);
	
	ajout_bdd($outputFile,$res_id);
	return $outputFile;
}

function ajout_bdd($dossier_imp, $res_id){
	$db = new dbquery();
	$db->connect();
	$title = "Dossier du document n°".$res_id;

	
	$fingerprint = hash_file(strtolower("SHA256"), $dossier_imp['filepath']);
	$filesize = filesize ($dossier_imp['filepath']);
	$date = date("Y-m-d H:i:s").".000";

	$req =  "INSERT INTO res_attachments (title, type_id, format, typist, creation_date, identifier, docserver_id, path, filename, fingerprint, filesize, status, coll_id,res_id_master) VALUES ('".$title."', '0', 'pdf', '".$_SESSION['user']['UserId']."', '".$date."', '1', 'FASTHD_MAN', '".$dossier_imp['path']."', '".$dossier_imp['filename']."', '".$fingerprint."', '".$filesize."', 'DOSIMP', 'letterbox_coll','".$res_id."');";

	$db->query($req, false, true);
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
	
	$dir_field_split = explode('-',$dir_field);
	
	$dir_user = $dir_field_split[0];
	$dir_ent = $dir_field_split[1];
	
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	$sec = new security();
	$table = $sec->retrieve_table_from_coll($coll_id);
	
	/*$circuit_visa = new visa();
	$circuit_visa->saveWorkflow($res_id, $coll_id, get_circuit($values_form));*/
	$dossier = getDossier($values_form, $type_view);
	$dossier = getPaths($dossier, $res_id);
	$output = concat_files($dossier,$res_id);
	//writeLogIndex($output);
    return array(
		'result' => $res_id.'#',
		'history_msg' => '',
        'page_result' => $_SESSION['config']['businessappurl']
                         . 'index.php?page=view_parapheur_controller&dir=indexing_searching'
                         . '&res_id=' . $res_id
	);
}