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
	
	
	$db = new dbquery();
    $db->connect();
	
	$query = "select priority_number, docserver_id from "
		   . _DOCSERVERS_TABLE_NAME . " where is_readonly = 'N' and "
		   . " enabled = 'Y' and coll_id = '" . $coll_id
		   . "' and docserver_type_id = 'TNL' order by priority_number";
		   
	$db->query($query);
	$docserverId = $db->fetch_object()->docserver_id;
			
	$docserver = $docserversControler->get($docserverId);
	
	
	$db->query("SELECT tnl_path, tnl_filename FROM $table WHERE res_id = $res_id");
	$data = $db->fetch_object();
	
	$tnlPath = str_replace("#", DIRECTORY_SEPARATOR , $data->tnl_path);
	$tnlFilename = $data->tnl_filename;
	
	$path=$docserver->path_template . DIRECTORY_SEPARATOR . $tnlPath . $tnlFilename;
	$path = str_replace("//","/",$path);
	
		if (!is_file($path)){
			$path = 'modules'. DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . 'no_thumb.png';
		}
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
	
?>