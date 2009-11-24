<?php
/**
* File : folders_list_by_id.php
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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$db = new dbquery();
$db->connect();
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$db->query("select folder_id from ".$_SESSION['tablename']['fold_folders']." where folder_id ilike '".$_REQUEST['Input']."%' order by folder_id");
}
else
{
	$db->query("select folder_id from ".$_SESSION['tablename']['fold_folders']." where folder_id like '".$_REQUEST['Input']."%' order by folder_id");
}
//$db->show();
$folders = array();
while($line = $db->fetch_object())
{
	array_push($folders, $line->folder_id);
}

echo "<ul>";
$authViewList = 0;
foreach($folders as $folder)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
    if(stripos($folder, $_REQUEST['Input']) === 0)
    {
        echo "<li>".$folder."</li>";
		if($flagAuthView)
		{
			echo "<li>...</li>";
			break;
		}
		$authViewList++;
    }
}
echo "</ul>";

