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
    $where .= " (folder_level = 1 or folder_level = 2) and (lower(folder_name) like lower('%"
		.$req->protect_string_db($_REQUEST['Input'])."%') or lower(folder_id) like lower('%"
		.$req->protect_string_db($_REQUEST['Input'])."%') ) and (status <> 'DEL' or status <> 'FOLDDEL')";
    //Order
    $order = 'order by subject, folder_name';

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
