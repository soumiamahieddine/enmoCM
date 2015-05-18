<?php

$core_path = $_SESSION['config']['corepath'];
$core_path = str_replace("\\", "/", $core_path);
define('FPDF_FONTPATH',$core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/font/');
//above line is import to define, otherwise it gives an error : Could not include font metric file
require($core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/fpdf.php');
require($core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/fpdi.php');
require($core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/php-barcode.php');

include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');


require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
if(isset($argv[1])){
	$res_id = $argv[1];
}
else{
	$res_id = $_REQUEST['res_id'];
}

function bordExists($res_id, $coll_id){
	$db = new dbquery();
    $db->connect();
	$db->query("select filename, path,title,res_id  from res_attachments where res_id_master = " . $res_id . " AND status <> 'DEL' and attachment_type = 'routing' order by creation_date asc");
	if ($db->nb_result() > 0) return true;
	return false;
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


function ajout_bdd($fnameTmp, $res_id){
	$db = new dbquery();
	$db->connect();
	$collId = 'letterbox_coll';

	require_once 'modules/attachments/attachments_tables.php';
	require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docservers_controler.php";
	require_once "core".DIRECTORY_SEPARATOR."docservers_tools.php";
	require_once 'core/class/docserver_types_controler.php';

	
	$docserv_control = new docservers_controler();
	$docserverTypeControler = new docserver_types_controler();
	
	$docserver = $docserv_control->getDocserverToInsert($collId);
	
	if (empty($docserver)) {
    $_SESSION['error'] = _DOCSERVER_ERROR . ' : '
        . _NO_AVAILABLE_DOCSERVER . '. ' . _MORE_INFOS;
	} else {
		
		$newSize = $docserv_control->checkSize(
			$docserver, $filesize
		);
		if ($newSize == 0) {
        $_SESSION['error'] = _DOCSERVER_ERROR . ' : '
            . _NOT_ENOUGH_DISK_SPACE . '. ' . _MORE_INFOS . '.';
		} else {
			$title = "Fiche de circulation du document n°".$res_id;
			
			$fileInfos = array(
            'tmpDir'      => $_SESSION['config']['tmppath'],
            'size'        => filesize ($_SESSION['config']['tmppath'].$fnameTmp),
            'format'      => 'pdf',
            'tmpFileName' => $fnameTmp,
			);

			$storeResult = array();
			$storeResult = $docserv_control->storeResourceOnDocserver(
				$collId, $fileInfos
			);
			
			if (isset($storeResult['error']) && $storeResult['error'] <> '') {
				$_SESSION['error'] = $storeResult['error'];
			} else {
				require_once "core/class/class_request.php";
				$req = new request();
				$req->connect();
				
				$resAttach = new resource();
				$_SESSION['data'] = array();
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'typist',
						'value' => $_SESSION['user']['UserId'],
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'format',
						'value' => 'pdf',
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'docserver_id',
						'value' => $storeResult['docserver_id'],
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'status',
						'value' => 'TRA',
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'offset_doc',
						'value' => ' ',
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'logical_adr',
						'value' => ' ',
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'title',
						'value' => $req->protect_string_db($title),
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'relation',
						'value' => 1,
						'type' => 'integer',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'coll_id',
						'value' => $collId,
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'res_id_master',
						'value' => $res_id,
						'type' => 'integer',
					)
				);
				
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'type_id',
						'value' => 0,
						'type' => 'int',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'identifier',
						'value' => '1',
						'type' => 'string',
					)
				);
				array_push(
					$_SESSION['data'],
					array(
						'column' => 'attachment_type',
						'value' => 'routing',
						'type' => 'string',
					)
				);

				$id = $resAttach->load_into_db(
					RES_ATTACHMENTS_TABLE,
					$storeResult['destination_dir'],
					$storeResult['file_destination_name'] ,
					$storeResult['path_template'],
					$storeResult['docserver_id'], 
					$_SESSION['data'],
					$_SESSION['config']['databasetype']
				);
				
				$complete_path = $storeResult['path_template'].str_replace('#',DIRECTORY_SEPARATOR,$storeResult['destination_dir']).$storeResult['file_destination_name'];
				
				if ($id == false) {
					$_SESSION['error'] = $resAttach->get_error();
				} else {
					if ($_SESSION['history']['attachadd'] == "true") {
						$hist = new history();
						$sec = new security();
						$view = $sec->retrieve_view_from_coll_id(
							$collId
						);
						$hist->add(
							$view, $res_id, 'ADD', 'attachadd',
							ucfirst(_DOC_NUM) . $id . ' '
							. _NEW_ATTACH_ADDED . ' ' . _TO_MASTER_DOCUMENT
							. $res_id,
							$_SESSION['config']['databasetype'],
							'apps'
						);
						$hist->add(
							RES_ATTACHMENTS_TABLE, $id, 'ADD','attachadd',
							$_SESSION['error'] 
							. _NEW_ATTACHMENT,
							$_SESSION['config']['databasetype'],
							'attachments'
						);
					}
				}
				
				return $complete_path;
			}
			
			return 'null';
			/*if (!bordExists($res_id, 'letterbox_coll')){
				$req =  "INSERT INTO res_attachments (title, type_id, format, typist, creation_date, identifier, docserver_id, path, filename, fingerprint, filesize, status, coll_id,res_id_master,attachment_type, relation) VALUES ('".$title."', '0', 'pdf', '".$_SESSION['user']['UserId']."', '".$date."', '1', '".$docserver->docserver_id."', '".$bordereau['path']."', '".$bordereau['filename']."', '".$fingerprint."', '".$filesize."', 'TRA', 'letterbox_coll','".$res_id."','routing', 1);";
				$db->query($req, false, true);
			}
			else {
				$req =  "UPDATE res_attachments SET path='".$bordereau['path']."', filename='".$bordereau['filename']."', fingerprint='".$fingerprint."', filesize='".$filesize."' WHERE res_id_master = $res_id and attachment_type='routing' ";
				$db->query($req, false, true);
			}*/
		}
	}
}

class ChargePdf extends FPDI
{
	function LoadData($tab)
	{
		// Lecture des lignes du fichier
		$data = array();
		
		
		if (isset($tab['visa']['users'])){
			foreach($tab['visa']['users'] as $seq=>$step){
				if (isset($step['process_date']) && $step['process_date'] != ''){
					$date_visa = explode(" ",$step['process_date']);
					$date = explode("-",$date_visa[0]);
					$data[] = array(utf8_decode($step['firstname']).' '.utf8_decode($step['lastname']), utf8_decode($step['process_comment']),$date[2]."/".$date[1]."/".$date[0],utf8_decode('Visé'));
				}
				else {
					$data[] = array(utf8_decode($step['firstname']).' '.utf8_decode($step['lastname']), utf8_decode($step['process_comment']),'','');
				}
			}
		}
		if (isset($tab['sign']['users'])){
			foreach($tab['sign']['users'] as $seq=>$step){
				if (isset($step['process_date']) && $step['process_date'] != ''){
					$date_visa = explode(" ",$step['process_date']);
					$data[] = array(utf8_decode($step['firstname']).' '.utf8_decode($step['lastname']), utf8_decode($step['process_comment']),$date_visa[0],utf8_decode('Visé'));
				}
				else {
					$data[] = array(utf8_decode($step['firstname']).' '.utf8_decode($step['lastname']), utf8_decode($step['process_comment']),'','');
				}
			}
		}
		// Ajout de 2 lignes vides
		for ($j = 0; $j < 2; $j++){
			$data[] = array('','','','');
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



$data = get_general_data('letterbox_coll', $res_id, 'full');

$pageCount = $pdf->setSourceFile($_SESSION['modules_loaded']['visa']['routing_template']);
$tplIdx = $pdf->importPage(1);
$pdf->addPage();
$pdf->useTemplate($tplIdx);

$db = new dbquery();
$db->connect();
$db->query("select alt_identifier, identifier from res_view_letterbox where res_id = " . $res_id);
$res = $db->fetch_object();
$resChrono = $res->alt_identifier;
$barcode = 'MIVI12345678';
	
	
$pdf->SetY(10);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,14,utf8_decode($data['destination']['show_value']), 0, 0, 'C');

$pdf->SetY(36);
$pdf->SetX(43);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,12,'BORD_'.str_replace("/","_",$resChrono), 0);

$pdf->SetX(122);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,12,date('d/m/Y'), 0);


$pdf->SetY(50);
$pdf->SetX(45);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,12,utf8_decode($data['exp_contact_id']['show_value']), 0);


$pdf->SetY(55);
$pdf->SetX(45);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,12,utf8_decode($data['subject']['show_value']), 0);


$visa_circuit = new visa();
$workflow = $visa_circuit->getWorkflow($res_id, 'letterbox_coll', 'VISA_CIRCUIT');

$data = $pdf->LoadData($workflow);
$header = array('Nom', 'Consigne', 'Date', 'Visa');
$pdf->SetFont('Arial','B',12);
$pdf->SetY(75);
//$pdf->Table($header, $data);
$pdf->SetWidths(array(60,80,30,15));
$pdf->SetAligns(array('C','C','C','C'));
$pdf->Row(array('Nom', 'Consigne', 'Date', 'Visa'));
$pdf->SetAligns(array('L','L','C','C'));
$pdf->SetFont('Arial','',10);
foreach($data as $d){
	$pdf->Row($d);
}

  
$tmpFileName = 'tmp_file_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.pdf';
$filePathOnTmp = $_SESSION['config']['tmppath'] . $tmpFileName;
$pdf->Output($filePathOnTmp, 'F');

$filepath_out = ajout_bdd($tmpFileName,$res_id);
echo "{status : 1,path:'".$filepath_out."',code:'".$barcode."'}";
exit();
?>