<?php

class thumbnails extends dbquery
{
	/*function __construct()
	{
		parent::__construct();
	}*/

	public function build_modules_tables()
	{
		if (file_exists(
            $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
            . DIRECTORY_SEPARATOR . "thumbnails" . DIRECTORY_SEPARATOR . "xml"
            . DIRECTORY_SEPARATOR . "config.xml"
        )
        ) {
            $configPath = $_SESSION['config']['corepath'] . 'custom'
                        . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                        . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR
                        . "thumbnails" . DIRECTORY_SEPARATOR . "xml"
                        . DIRECTORY_SEPARATOR . "config.xml";
        } else {
            $configPath = "modules" . DIRECTORY_SEPARATOR . "thumbnails"
                        . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
                        . "config.xml";
        }
		
		$xmlconfig = simplexml_load_file($configPath);
		$conf = $xmlconfig->CONFIG;
		
	}
	
	public function getPathTnl($res_id, $coll_id){
		require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
		. "class_security.php");
		require_once 'core/class/docservers_controler.php';
		$docserversControler = new docservers_controler();
		$sec = new security();
	
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
		
		return $path;
	}

}

