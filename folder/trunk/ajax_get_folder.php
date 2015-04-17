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
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$core = new core_tools();
$core->load_lang();
require_once "core/class/class_security.php";
$sec = new security();
$whereClause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
if($_POST['FOLDER_TREE']){
	$folders = array();
	$db->query('select folders_system_id, folder_name, parent_id, folder_level from folders WHERE foldertype_id not in (100) AND parent_id='.$_POST["folders_system_id"].' AND status NOT IN (\'DEL\') order by folder_id asc');
	while($row=$db->fetch_array()){
	
		$db2->query(
				"select count(*) as total from res_view_letterbox WHERE folders_system_id in ('".$row['folders_system_id']."') AND (".$whereClause.") AND status NOT IN ('DEL')"
				);
		$row2 = $db2->fetch_array();
		$db3->query(
		"select count(*) as total from folders WHERE foldertype_id not in (100) AND parent_id IN (".$row['folders_system_id'].") AND status NOT IN ('DEL')"
		);
		$row3 = $db3->fetch_array();
		$folders[] = array(
			'parent_id' => $row['parent_id'],
			'folders_system_id' => $row['folders_system_id'],
			'nom_folder' => $row['folder_name'],
			'folder_level' => $row['folder_level'],
			'nb_doc' => $row2['total'],
			'nb_subfolder' => $row3['total']
		);
	}
	echo json_encode($folders);
	exit();
}else if($_POST['FOLDER_TREE_RESET']){
	$folders = array();
	$db->query('select folders_system_id, folder_name, parent_id, folder_level from folders WHERE foldertype_id not in (100) AND folders_system_id='.$_POST["folders_system_id"].' AND status NOT IN (\'DEL\') order by folder_id asc');
	while($row=$db->fetch_array()){
		$db2->query(
				"select count(*) as total from res_view_letterbox WHERE folders_system_id in ('".$_POST['folders_system_id']."') AND (".$whereClause.") AND status NOT IN ('DEL')"
				);
		$row2 = $db2->fetch_array();
		$db3->query(
		"select count(*) as total from folders WHERE foldertype_id not in (100) AND parent_id IN (".$row['folders_system_id'].")  AND status NOT IN ('DEL')"
		);
		$row3 = $db3->fetch_array();
		$folders[] = array(
			'parent_id' => $row['parent_id'],
			'folders_system_id' => $row['folders_system_id'],
			'nom_folder' => $row['folder_name'],
			'folder_level' => $row['folder_level'],
			'nb_doc' => $row2['total'],
			'nb_subfolder' => $row3['total']
		);
	}
	echo json_encode($folders);
	exit();
}else if($_POST['FOLDER_TREE_DOCS']){
	$docs = array();
	$db->query("select res_id, type_label, subject,doctypes_first_level_label,doctypes_second_level_label, folder_level from res_view_letterbox WHERE folders_system_id in ('".$_POST['folders_system_id']."') AND (".$whereClause.") AND status NOT IN ('DEL')");
	while($row=$db->fetch_array()){
		
		$docs[] = array(
			'res_id' => $row['res_id'],
			'type_label' => $row['type_label'],
			'subject' => $row['subject'],
			'doctypes_first_level_label' => $row['doctypes_first_level_label'],
			'doctypes_second_level_label' => $row['doctypes_second_level_label'],
			'folder_level' => $row['folder_level']
		);
	}
	echo json_encode($docs);
	exit();
}else if($_POST['AUTOFOLDERS_TREE']){
	$config_autofoldering = "modules/autofoldering/xml/autofoldering.xml";
	$xml=simplexml_load_file($config_autofoldering);
	//print_r($xml->tree);
	$autofolders = array();

	foreach($xml->tree as $tree) {
		//echo $tree->id;
		$autofolders[] = array(
			'id' => $tree->id,
			'desc' => $tree->desc,
			'tree_level' => 1
		);
	}
	echo json_encode($autofolders);
	exit();
}else if($_POST['AUTOFOLDER_FOLDERS']){
	$config_autofoldering = "modules/autofoldering/xml/autofoldering.xml";
	$xml=simplexml_load_file($config_autofoldering);
	$path_folder = explode(";", $_POST['path_folder']);	
	//print_r($path_folder);
	$folders = array();
	$level = $_POST['folder_level'];
	$level=$level-1;
	$level_comp=$level-1;
	foreach($xml->tree as $tree) {
		if($tree->id == $_POST['id']){
			$view = $sec->retrieve_view_from_coll_id($tree->coll_id);
				for ($i=0; $i < $level ; $i++) { 
					if($i<>0){
						$where .= " AND (".$tree->nodes->node[$i]->target_column[0]." = '".$path_folder[$i+1]."')";
					}else{
						$where = "WHERE (".$tree->nodes->node[$i]->target_column[0]." = '".$path_folder[$i+1]."')";
					}
				}
				$select=$tree->nodes->node[$level]->target_column[0];
				if($_POST['folder_level']==$tree->nodes->node->count()){
					$check_docs=true;
				}else{
					$check_docs=false;
				}
			
			break;
		}
		
	}

	if(!empty($where)){
		$where.= ' AND ('.$whereClause.')';
	}else{
		$where = 'WHERE ('.$whereClause.')';
	}

	if($level<>0){
		$order="ORDER BY ".$select." ASC";
	}
	$db->query('select DISTINCT ('.$select.') as libelle FROM '.$view.' '.$where.' '.$order);
	

	while($row=$db->fetch_array()){
		$folders[] = array(
			'libelle' => $row['libelle'],
			'folder_level' => $level + 2,
			'check_docs' => $check_docs,
		);
	}
	echo json_encode($folders);
	exit();
}else if($_POST['AUTOFOLDER_FOLDER_DOCS']){
	$config_autofoldering = "modules/autofoldering/xml/autofoldering.xml";
	$xml=simplexml_load_file($config_autofoldering);
	$path_folder = explode(";", $_POST['path_folder']);
	//print_r($path_folder);
	$docs = array();
	foreach($xml->tree as $tree) {
		if($tree->id == $_POST['id']){
			$view = $sec->retrieve_view_from_coll_id($tree->coll_id);
			for ($i=0; $i <= $tree->nodes->node->count()-1 ; $i++) { 
				if($i<>0){
					$where .= " AND (".$tree->nodes->node[$i]->target_column[0]." = '".$path_folder[$i+1]."')";
				}else{
					$where = "WHERE (".$tree->nodes->node[$i]->target_column[0]." = '".$path_folder[$i+1]."')";
				}
			}
			
			break;
		}
		
	}
	if(!empty($where)){
		$where.= ' AND ('.$whereClause.')';
	}else{
		$where= 'WHERE ('.$whereClause.')';
	}
	//echo "requete where: ".$where;exit();
	$db->query('select res_id, type_label, subject,doctypes_first_level_label,doctypes_second_level_label, folder_level FROM '.$view.' '.$where);
	//$db->show();

	while($row=$db->fetch_array()){
		$docs[] = array(
			'res_id' => $row['res_id'],
			'type_label' => $row['type_label'],
			'subject' => $row['subject'],
			'doctypes_first_level_label' => $row['doctypes_first_level_label'],
			'doctypes_second_level_label' => $row['doctypes_second_level_label'],
			'folder_level' => $row['folder_level']
		);
	}
	echo json_encode($docs);
	exit();
}else{

if(!isset($_REQUEST['id_subfolder']) || empty($_REQUEST['id_subfolder']))
{
	//$_SESSION['error'] = _SUBFOLDER.' '._IS_EMPTY;
	echo "{status : 1, error_txt : '".addslashes( _SUBFOLDER.' '._IS_EMPTY)."'}";
	exit();
}
$db->query('select parent_id from '.$_SESSION['tablename']['fold_folders'].' where folders_system_id = '.$_REQUEST['id_subfolder']. ' AND status NOT IN (\'DEL\')');

if($db->nb_result() < 1)
{
	//$_SESSION['error'] = _NO_SUBFOLDER;
	echo "{status : 1, error_txt : '".addslashes(_NO_SUBFOLDER)."'}";
	exit();
}
$res = $db->fetch_object();
$parent_id = $res->parent_id;
$db->query('select folder_name, subject, folders_system_id from '.$_SESSION['tablename']['fold_folders'].' where folders_system_id = '.$parent_id. '  AND status NOT IN (\'DEL\')');

if($db->nb_result() < 1)
{
	//$_SESSION['error'] =_NO_FOLDER;
	echo "{status : 1, error_txt : '".addslashes(_NO_FOLDER)."'}";
	exit();
}
$res = $db->fetch_object();
echo "{status : 0, value : '".$db->show_string($res->folder_name).', '.$db->show_string($res->subject).' ('.$db->show_string($res->folders_system_id).')'."'}";
exit();
}
?>
