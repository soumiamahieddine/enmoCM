<?php
/*
*    Copyright 2008 - 2011 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Returns in a json structure all allowed first branches of a tree for
* the current user (Ajax)
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/
require_once "core/class/class_security.php";
require_once "core/class/class_core_tools.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
. DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
. 'class_business_app_tools.php';
$appTools = new business_app_tools();
$core = new core_tools();
$core->load_lang();
$sec = new security();
$func = new functions();
$db = new dbquery();
$db->connect();
$dbTmp = new dbquery();
$dbTmp->connect();
$db1 = new dbquery();
$db1->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$db4 = new dbquery();
$db4->connect();
$whereClause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
?>
<script type="text/javascript">
	function hideshow(which){
		if (!document.getElementById)
			return
		if (which.style.display=="block")
			which.style.display="none"
		else
			which.style.display="block"
	}
</script>
<style type="text/css">li{cursor: pointer;}li.folder{padding-top: 10px;padding-bottom: 10px;}span.folder{float:left;margin-top:5px;}ul.doc a{padding:5px;}ul.doc a:hover{background-color: #BAD1E2;border-radius:2px;}</style>
<?php
$db->connect();
$subject = $_REQUEST['project'];
$pattern = '/\([0-9]*\)/';
preg_match($pattern, substr($subject,3), $matches, PREG_OFFSET_CAPTURE);
$fold_id=str_replace("(", "", $matches[0][0]);
$fold_id=str_replace(")", "", $fold_id);
//print_r($fold_id);
//var_dump($matches[0]);die();
if($matches[0] != ''){
	$db->query(
		"select folders_system_id, folder_name, parent_id from folders WHERE foldertype_id not in (100) AND parent_id=0 AND folders_system_id IN (".$fold_id.") order by folder_id asc "
		);

}else{
	$db->query(
		"select folders_system_id, folder_name, parent_id from folders WHERE foldertype_id not in (100) AND parent_id=0 order by folder_id asc "
		);

}


$categories = array();
$html.="<ul class='folder' id='folder_tree_content'>";
while($row = $db->fetch_array()) {
	$db2->query(
		"select count(*) as total from res_view_letterbox WHERE folders_system_id in ('".$row['folders_system_id']."') AND (".$whereClause.") AND status NOT IN ('DEL')"
		);
	$row2 = $db2->fetch_array();

	$folders_system_id=$row['folders_system_id'];
	$html.="<span onclick='get_folders(".$folders_system_id.")' class='folder'><img src=\"". $_SESSION['config']['businessappurl']. "static.php?filename=folder.gif\" class=\"mt_fclosed\" alt=\"\" id='".$row['folders_system_id']."_img'></span><li id='".$row['folders_system_id']."' class='folder'>";
	$html.="<span onclick='get_folder_docs(".$folders_system_id.")'>".$row['folder_name']." <b>(".$row2['total'].")</b></span>";
	$html.="</li>";
}
$html.="</ul>";
echo $html;
?>
