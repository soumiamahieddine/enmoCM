<?php 
/**
* File : foldertype_list_by_name.php
*
* List of foldertype for autocompletion
*
* @package  Maarch Framework 3.0
* @version 3
* @since 10/2005
* @license GPL
* @author Laurent Giovannoni <dev@maarch.org>
*/
session_name('PeopleBox');    
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
$db = new dbquery();
$db->connect();
//$_REQUEST['what'] = "P";
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$db->query("select foldertype_label as tag from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_label ilike '".$_REQUEST['what']."%' order by foldertype_label");
}
else
{
	$db->query("select foldertype_label as tag from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_label like '".$_REQUEST['what']."%' order by foldertype_label");
}
//$db->show();
$listArray = array();
while($line = $db->fetch_object())
{
	array_push($listArray, $line->tag);
}
echo "<ul>\n";
$authViewList = 0;
//echo "<li>test</li>\n";
foreach($listArray as $what)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
    if(stripos($what, $_REQUEST['what']) === 0)
    {
        echo "<li>".$what."</li>\n";
		if($flagAuthView)
		{
			echo "<li>...</li>\n";
			break;
		}
		$authViewList++;
    }
}
echo "</ul>";