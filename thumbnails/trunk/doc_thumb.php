<?php
	require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_security.php");
	require_once 'core/class/docservers_controler.php';
	$docserversControler = new docservers_controler();
	$sec = new security();
	$res_id = $_REQUEST['res_id'];
	$coll_id = $_REQUEST['coll_id'];
	
	$table = "";
    if (isset($coll_id) 
        && !empty($coll_id)
    ) {
       $table = $sec->retrieve_table_from_coll(
			$coll_id
		);
    } else {
        $table = $_SESSION['collections'][0]['table'];
    }
	
	
	$docserver = $docserversControler->get($_SESSION['modules_loaded']['thumbnails']['docserver_id']);
	
	$db = new dbquery();
    $db->connect();
	
	$db->query("SELECT tnl_path, tnl_filename FROM $table WHERE res_id = $res_id");
	$data = $db->fetch_object();
	
	$tnlPath = str_replace("#", DIRECTORY_SEPARATOR , $data->tnl_path);
	$tnlFilename = $data->tnl_filename;
	
	$path=$docserver->path_template . DIRECTORY_SEPARATOR . $tnlPath . $tnlFilename;
	$path = str_replace("//","/",$path);
	
	if (is_file($path)){
		$mime_type = 'image/png';	
		$date = mktime(0,0,0,date("m" ) + 2  ,date("d" ) ,date("Y" )  );
		$date = date("D, d M Y H:i:s", $date);
		$time = 30*12*60*60;
		header("Pragma: public");
		header("Expires: ".$date." GMT");
		header("Cache-Control: max-age=".$time.", must-revalidate");
		header("Content-Description: File Transfer");
		header("Content-Type: ".$mime_type);
		header("Content-Disposition: inline; filename=".$tnlFilename.";");
		header("Content-Transfer-Encoding: binary");
		readfile($path);
			
		exit();
	}
	else {
		echo "La miniature n'a pas encore été générée";
	}
?>