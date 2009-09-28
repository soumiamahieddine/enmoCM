<?php
/**
* File : folders_list_by_name.php
*
* List of folders for autocompletion
*
* @package  Maarch Framework 3.0
* @version 3
* @since 10/2005
* @license GPL
* @author Laurent Giovannoni <dev@maarch.org>
* @author Claire Figueras <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
$db = new dbquery();
$db->connect();

if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$db->query("select folder_name from ".$_SESSION['tablename']['fold_folders']." where folder_name ilike '".$_REQUEST['folder']."%' order by folder_name");
}
else
{
		$db->query("select folder_name from ".$_SESSION['tablename']['fold_folders']." where folder_name like '".$_REQUEST['folder']."%' order by folder_name");
}
//$db->show();
$folders = array();
while($line = $db->fetch_object())
{
	array_push($folders, $line->folder_name);
}
echo "<ul>\n";
$authViewList = 0;
foreach($folders as $folder)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
    if(stripos($folder, $_REQUEST['folder']) === 0)
    {
        echo "<li>".$folder."</li>\n";
		if($flagAuthView)
		{
			echo "<li>...</li>\n";
			break;
		}
		$authViewList++;
    }
}
echo "</ul>";
