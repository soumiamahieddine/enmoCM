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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$db = new dbquery();
$db->connect();

$db->query("select folder_id, folder_name, folders_system_id from ".$_SESSION['tablename']['fold_folders']." where lower(folder_name) like lower('".$_REQUEST['folder']."%') order by folder_name");

//$db->show();
$folders = array();
while($line = $db->fetch_object())
{
	array_push($folders, $line->folder_name." (".$line->folder_id.")");
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
