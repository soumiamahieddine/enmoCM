<?php 
require('SOAP/Client.php');
$proxy = array('user'=>$_REQUEST['proxy1'],'pass'=>$_REQUEST['proxy2']);
$wsdl = new SOAP_WSDL('http://127.0.0.1/maarch_entreprise/ws_server.php?WSDL', $proxy, false);
$client = $wsdl->getProxy();
/*************** view Maarch document *********************/
$fileContentArray = array();
$fileContentArray = $client->viewResource((integer) $_REQUEST['id'], $_REQUEST['table']);
if($fileContentArray->status == "ok") {
	$fileContent = base64_decode($fileContentArray->file_content);
	$Fnm = "local.".strtolower($fileContentArray->ext);
	$inF = fopen($Fnm, "w");
	fwrite($inF, $fileContent);
	fclose($inF);
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: ".strtolower($fileContentArray->mime_type));
	header("Content-Disposition: inline; filename=".basename('maarch.'.strtolower($fileContentArray->ext)).";");
	header("Content-Transfer-Encoding: binary");
	readfile($Fnm);
	exit();
} else {
	echo $fileContentArray->error;
}

?>
