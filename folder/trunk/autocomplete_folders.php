<?php
/**
* File : autocomplete_folders.php
*
* Autocompletion list on folder or subfolder
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*/
require('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php');
$req = new request();
$req->connect();

//If no mode
// if($_REQUEST['mode']<> 'folder' && $_REQUEST['mode'] <> 'subfolder') {
	// exit();
// } else {
    // $mode = $_REQUEST['mode'];
// }

//Build query
$select = array();
    //Table
    $table = $_SESSION['tablename']['fold_folders'];
    //Fields
    $select[$table]= array( 'folder_id', 'folder_name',  'folders_system_id');
    //Where
    $where = '';
    // if($mode == 'subfolder') {
        // $where = " folder_level = 2 and ";
    // } else {
        // $where = " folder_level = 1 and ";
    // }

	$category_id = $_SESSION['category_id_session'];
	
	$db = new dbquery();
	$db->connect();
	
	if($category_id != null and $category_id != ''){
	
		$db->query("select doctypes_first_level_id from doctypes where type_id = ".$category_id);

		$res = $db->fetch_object();
		
		$db->query("select foldertype_id from foldertypes_doctypes_level1 where doctypes_first_level_id = ".$res->doctypes_first_level_id);
		$res = $db->fetch_object();
	
		$where .= " (foldertype_id = ".$res->foldertype_id.") and (lower(folder_name) like lower('%"
			.$req->protect_string_db($_REQUEST['Input'])."%') or lower(folder_id) like lower('%"
			.$req->protect_string_db($_REQUEST['Input'])."%') ) and (status <> 'DEL' or status <> 'FOLDDEL')";
		//Order
		$order = 'order by folders_system_id, folder_name';
	}else{
	
		$db->query("select doctypes_first_level_id from doctypes");
		$doctypes_1 = '';
		while($res = $db->fetch_object()){
			$doctypes_1 .= $res->doctypes_first_level_id.",";
		}
		$doctypes_1 .= 0 ;
		$db->query("select foldertype_id from foldertypes_doctypes_level1 where doctypes_first_level_id in ( ".$doctypes_1.")");
		$wh = '';
		while($res = $db->fetch_object()){
			$wh .= $res->foldertype_id.",";
		}
		$wh .= 0 ;
		$where .= " (foldertype_id in (".$wh.")) and (lower(folder_name) like lower('%"
		.$req->protect_string_db($_REQUEST['Input'])."%') or lower(folder_id) like lower('%"
		.$req->protect_string_db($_REQUEST['Input'])."%') ) and (status <> 'DEL' or status <> 'FOLDDEL')";
		//Order
		$order = 'order by folders_system_id, folder_name';
	}
	
//Query
$res = $req->select($select, $where, $order, $_SESSION['config']['databasetype'], 11,false,"","","", false);

//Autocompletion output
echo "<ul>\n";
for($i=0; $i< min(count($res), 10)  ;$i++) {
	echo "<li>".$req->show_string($res[$i][0]['value']).', '.$req->show_string($res[$i][1]['value']).' ('.$res[$i][2]['value'].")</li>\n";
}

//Show only ten item
if(count($res) == 11) {
		echo "<li>...</li>\n";
}
echo "</ul>";
