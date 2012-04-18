<?php
/**
* File : autocomplete_folders.php
*
* Autocompletion list on market or project
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Loïc Vinet  <dev@maarch.org>
*/
require_once("core/class/class_db.php");
require_once("modules/tags/tags_tables_definition.php");

$table = _TAG_TABLE_NAME;



if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$where .= " (tag_label ilike '%".addslashes($_REQUEST['Input'])."%' or tag_label ilike '%".addslashes($_REQUEST['Input'])."%' ) ";
	$limit = " limit 10";
}
else
{
	$where .= " (tag_label like '%".addslashes($_REQUEST['Input'])."%' or tag_label like '%".addslashes($_REQUEST['Input'])."%' ) ";
	$limit = "";
}
$other = 'order by tag_label';

$db = new dbquery();
$db->connect();
$db->query(
    	"select distinct tag_label as label from " ._TAG_TABLE_NAME
        . " where ".$where." ".
        $other." ".$limit
	);

echo "<ul>\n";
$imax = 0;
while($result=$db->fetch_object())
{
	$imax++;
	if ($imax > 9){
		echo "<li>...</li>\n";
		break;
	}
	echo "<li>".$db->show_string($result->label)."</li>\n";
}
echo "</ul>";
