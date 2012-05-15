<?php
/*
*    Copyright 2008,2012 Maarch
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
* Module : Tags
* 
* This module is used to store ressources with any keywords
* V: 1.0
*
* @file
* @author Loic Vinet
* @date $date$
* @version $Revision$
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
