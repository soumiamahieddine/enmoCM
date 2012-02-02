<?php
require_once('core/class/ExportControler.php');
$export = new ExportControler();
$_SESSION['last_select_query'] = '';

if (!empty($_SESSION['error'])) {
	
	?>
    <script language="javascript" >
		window.opener.location.reload();
		window.close();
	</script>
    <?php
	
} else {

	//header('Pragma: public');
	//header('Expires: 0');
	//header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	//header('Cache-Control: public');
	//header('Content-Description: File Transfer');
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: inline; filename=export_maarch.csv;');
	//header('Content-Transfer-Encoding: binary');
	$pathToCsv = $_SESSION['config']['tmppath'] . $_SESSION['export']['filename'];
	readfile($pathToCsv);
	unlink($pathToCsv);
	exit;
	
}