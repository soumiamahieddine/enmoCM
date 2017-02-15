<?php

require_once 'apps/maarch_entreprise/services/Table.php';

class thumbnails
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
		
		$db = new Database();
		
		$query = "select priority_number, docserver_id from "
			   . _DOCSERVERS_TABLE_NAME . " where is_readonly = 'N' and "
			   . " enabled = 'Y' and coll_id = ? and docserver_type_id = 'TNL' order by priority_number";
			   
		$stmt = $db->query($query, array($coll_id));
		$docserverId = $stmt->fetchObject()->docserver_id;
				
		$docserver = $docserversControler->get($docserverId);
		
		
		$query = "select category_id from mlb_coll_ext"
			   . " where res_id = ?";
			   
		$stmt = $db->query($query, array($res_id));

		$catId = $stmt->fetchObject()->category_id;

		$query = "select count(*) as total from res_view_attachments"
			   . " where res_id_master = ? AND status NOT IN ('DEL','OBS','TMP') AND attachment_type = ?";
			   
		$stmt = $db->query($query, array($res_id,'outgoing_mail'));

		$isOutgoingPj = $stmt->fetchObject()->total;

		if($catId == 'outgoing' && $isOutgoingPj > 0){
			$stmt = $db->query("SELECT tnl_path, tnl_filename FROM res_attachments WHERE res_id_master = ? AND status NOT IN ('DEL','OBS','TMP') AND type_id = '1'", array($res_id));
		}else{
			$stmt = $db->query("SELECT tnl_path, tnl_filename FROM $table WHERE res_id = ?", array($res_id));
		}

		$data = $stmt->fetchObject();
		
		$tnlPath = str_replace("#", DIRECTORY_SEPARATOR , $data->tnl_path);
		$tnlFilename = $data->tnl_filename;
		
		$path=$docserver->path_template . DIRECTORY_SEPARATOR . $tnlPath . $tnlFilename;
		$path = str_replace("//","/",$path);
		
		return $path;
	}

	/**
	 * Retrieve the path of source file to process
	 * @param array $aArgs
	 * @return string
	 */
	public function getTnlPathWithColl(array $aArgs = []) {
		if (empty($aArgs['resId'])) {
			throw new \Exception('resId empty');
		}
		if (empty($aArgs['collId'])) {
			throw new \Exception('collId empty');
		}

		$resId = $aArgs['resId'];
		$collId = $aArgs['collId'];

		for ($i=0;$i < count($_SESSION['collections']);$i++) {
			if ($_SESSION['collections'][$i]['id'] == $collId) {
				$resTable = $_SESSION['collections'][$i]['table'];
			}
		}
		if (empty($resTable)) {
			return false;
		}

		$oRowSet = Apps_Table_Service::select([
			'select'    => ['path_template'],
			'table'     => ['docservers'],
			'where'     => ['docserver_id = ?'],
			'data'      => ['TNL']
		]);

		if (empty($oRowSet[0]['path_template'])) {
			throw new \Exception('TNL docserver path empty');
		}

		$docserverPath = $oRowSet[0]['path_template'];

		$oRowSet = Apps_Table_Service::select([
			'select'    => ['tnl_path', 'tnl_filename'],
			'table'     => [$resTable],
			'where'     => ['res_id = ?'],
			'data'      => [$resId]
		]);

		if (empty($oRowSet)) {
			return false;
		}

		$path          = '';
		$filename      = '';
		if (!empty($oRowSet[0]['tnl_path'])) {
			$path = $oRowSet[0]['tnl_path'];
		}
		if (!empty($oRowSet[0]['tnl_filename'])) {
			$filename = $oRowSet[0]['tnl_filename'];
		}
		$sourceFilePath = $docserverPath . $path . $filename;
		$sourceFilePath = str_replace('#', DIRECTORY_SEPARATOR, $sourceFilePath);

		return $sourceFilePath;
	}

}

