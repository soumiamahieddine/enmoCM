<?php
$core_path = $_SESSION['config']['corepath'];
$core_path = str_replace("\\", "/", $core_path);
define('FPDF_FONTPATH',$core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/font/');
//above line is import to define, otherwise it gives an error : Could not include font metric file
require($core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/fpdf.php');
require($core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/fpdi.php');
require($core_path.'apps/maarch_entreprise/tools/pdfb/fpdf_1_7/php-barcode.php');


  
  function ajout_bdd($bordereau, $res_id){
	$db = new dbquery();
	$db->connect();
	
	$fingerprint = hash_file(strtolower("SHA256"), $bordereau['filepath']);
	$filesize = filesize ($bordereau['filepath']);

	$req =  "UPDATE res_attachments SET path='".$bordereau['path']."', filename='".$bordereau['filename']."', fingerprint='".$fingerprint."', filesize='".$filesize."' WHERE res_id_master = $res_id and attachment_type='routing' ";
	
	$db->query($req, false, true);
}

function getOutputName(){
	require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docservers_controler.php";
	require_once "core".DIRECTORY_SEPARATOR."docservers_tools.php";
	require_once 'core/class/docserver_types_controler.php';

	
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
	$outputFile['path'] = $path_bdd[count($path_bdd)-4]."#".$path_bdd[count($path_bdd)-3] . "#" . $path_bdd[count($path_bdd)-2]."#";
	$outputFile['filename'] = $fname_tab['fileDestinationName'].".pdf";
	

	return $outputFile;
}

  // -------------------------------------------------- //
  //                      USEFUL
  // -------------------------------------------------- //
  
  class eFPDF extends FPDI{
    function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
    {
        $font_angle+=90+$txt_angle;
        $txt_angle*=M_PI/180;
        $font_angle*=M_PI/180;
    
        $txt_dx=cos($txt_angle);
        $txt_dy=sin($txt_angle);
        $font_dx=cos($font_angle);
        $font_dy=sin($font_angle);
    
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }
  }

  // -------------------------------------------------- //
  //                  PROPERTIES
  // -------------------------------------------------- //
  
  if(isset($argv[1]) && isset($argv[2])){
		$path = $argv[1];
		$resId = $argv[2];
		$code     = $argv[3];
	}
	else{
		$path = $_REQUEST['path'];
		$resId = $_REQUEST['res_id'];
		$code     = $_REQUEST['code'];
	}
	

  $fontSize = 12;
  $marge    = 5;   // between barcode and hri in pixel
  $x        = 100;  // barcode center
  $y        = 40;  // barcode center
  $height   = 35;   // barcode height in 1D ; module size in 2D
  $width    = 1;    // barcode height in 1D ; not use in 2D
  $angle    = 0;   // rotation in degrees
  
  $type     = 'code128';
  $black    = '000000'; // color in hexa
  
  
  // -------------------------------------------------- //
  //            ALLOCATE FPDF RESSOURCE
  // -------------------------------------------------- //
    

  $pdf = new eFPDF('P', 'pt');
  $pageCount = $pdf->setSourceFile($path);
  $tplIdx = $pdf->importPage(1);
  $pdf->addPage();
  $pdf->useTemplate($tplIdx);
  
  // -------------------------------------------------- //
  //                      BARCODE
  // -------------------------------------------------- //
  
  $data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);
  
  // -------------------------------------------------- //
  //                      HRI
  // -------------------------------------------------- //
  
  $pdf->SetFont('Arial','B',$fontSize);
  $pdf->SetTextColor(0, 0, 0);
  $len = $pdf->GetStringWidth($data['hri']);
  Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + $marge, $angle, $xt, $yt);
  $pdf->TextWithRotation($x + $xt, $y + $yt, $data['hri'], $angle);
  
	$out = getOutputName();
	$pdf->Output($out['filepath'], 'F');
	ajout_bdd($out,$resId);
	echo "{status : 1,path:'".$out['filepath']."'}";
	exit();
?>