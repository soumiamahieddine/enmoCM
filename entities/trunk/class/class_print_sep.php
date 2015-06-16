<?php 
/**
* File : class_print_sep.php
*
* Frame able to list boxes in physical archives modules
*
* @package  Maarch  3.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Loic Vinet <dev@maarch.org>

*/
require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
class print_sep extends FPDI
{	
  	function addlot($arbox_id)
	{
 		$data =  array();
		$request = new request();
		$db = new dbquery();
		$db ->connect();
		//$db->query(" select max(CAST(title as integer)) as nb from ".$_SESSION['tablename']['ar_lots']." where arbox_id = '".$arbox_id."' ");
		$db->query("select max(arbatch_id) as nb, title from ".$_SESSION['tablename']['ar_lots']." where arbox_id = '".$arbox_id."' group by title");
		$res = $db->fetch_object();
		if($res->nb == "")
		{
			$count_lot = 1;
		}
		else
		{
			$count_lot = $res->nb +1;
		}
		
		$current_date = $db->current_datetime();
		
		//Find the folder number
		$folder_number = $_SESSION['current_folder_id'];
		//Restore the folder label
		$db->query(" select folder_id, folder_name from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = '".$folder_number."' ");
		$folder_title = $db->fetch_object();
		array_push($data, array('column' => "title", 'value' => ($count_lot), "type" => "string"));
		array_push($data, array('column' => "status", 'value' => "NEW", "type" => "string"));
		array_push($data, array('column' => "custom_t3", 'value' => $_SESSION['user']['UserId'], "type" => "string"));
		array_push($data, array('column' => "creation_date", 'value' => $current_date, "type" => ""));
		array_push($data, array('column' => "custom_d4", 'value' => $current_date, "type" => ""));
		array_push($data, array('column' => "arbox_id", 'value' => $arbox_id, "type" => ""));
		array_push($data, array('column' => "custom_t5", 'value' => $folder_title->folder_id, "type" => "string"));
		array_push($data, array('column' => "custom_t7", 'value' => $folder_title->folder_name, "type" => "string"));
		if(!$request->insert($_SESSION['tablename']['ar_lots'], $data, $_SESSION['config']['databasetype']))
		{
			$request->show();
			echo _INDEXING_LOT_ERROR."<br/>";
			return false;	
		}
		else
		{
			$db->query(" select arbatch_id from ".$_SESSION['tablename']['ar_lots']." where title = '".$count_lot."' and arbox_id = '".$arbox_id."' ");
			$line = $db->fetch_object();
			return $line -> arbatch_id;
		}
		$_SESSION['error'] = _NEW_LOT_INDEXING;
  	}
}
