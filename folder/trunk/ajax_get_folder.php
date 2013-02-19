<?php
/**
* File : ajax_get_project.php
*
* Script called by an ajax object to get the project id  given a market id (index_mlb.php)
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*/

$db = new dbquery();
$db->connect();
$core = new core_tools();
$core->load_lang();

if(!isset($_REQUEST['id_subfolder']) || empty($_REQUEST['id_subfolder']))
{
	//$_SESSION['error'] = _SUBFOLDER.' '._IS_EMPTY;
	echo "{status : 1, error_txt : '".addslashes( _SUBFOLDER.' '._IS_EMPTY)."'}";
	exit();
}
$db->query('select parent_id from '.$_SESSION['tablename']['fold_folders'].' where folders_system_id = '.$_REQUEST['id_subfolder']);

if($db->nb_result() < 1)
{
	//$_SESSION['error'] = _NO_SUBFOLDER;
	echo "{status : 1, error_txt : '".addslashes(_NO_SUBFOLDER)."'}";
	exit();
}
$res = $db->fetch_object();
$parent_id = $res->parent_id;
$db->query('select folder_name, subject, folders_system_id from '.$_SESSION['tablename']['fold_folders'].' where folders_system_id = '.$parent_id);

if($db->nb_result() < 1)
{
	//$_SESSION['error'] =_NO_FOLDER;
	echo "{status : 1, error_txt : '".addslashes(_NO_FOLDER)."'}";
	exit();
}
$res = $db->fetch_object();
echo "{status : 0, value : '".$db->show_string($res->folder_name).', '.$db->show_string($res->subject).' ('.$db->show_string($res->folders_system_id).')'."'}";
exit();
?>
