<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief List of users for autocompletion
*
*
* @file
* @author  Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$db = new dbquery();
$db->connect();
$listArray = array();
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$db->query("select lastname, firstname, user_id from ".$_SESSION['tablename']['users']." where lastname ilike '%".$db->protect_string_db($_REQUEST['what'])."%' and enabled = 'Y' order by lastname");
}
else
{
	$db->query("select lastname, firstname, user_id from ".$_SESSION['tablename']['users']." where lastname like '%".$db->protect_string_db($_REQUEST['what'])."%' and enabled = 'Y' order by lastname");
}
while($line = $db->fetch_object())
{
	array_push($listArray, $db->show_string($line->lastname)." ".$db->show_string($line->firstname)." (user:".$line->user_id.")");
	//array_push($listArray, "user : [".$db->show_string($line->lastname)." ".$db->show_string($line->firstname))."]";
}
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$db->query("select is_corporate_person, society, lastname, firstname, contact_id from ".$_SESSION['tablename']['contacts']." where (lastname ilike '%".$db->protect_string_db($_REQUEST['what'])."%' or firstname ilike '".$db->protect_string_db($_REQUEST['what'])."%' or society ilike '%".$db->protect_string_db($_REQUEST['what'])."%') and enabled = 'Y'");
	//$db->show();
}
else
{
	$db->query("select is_corporate_person, society, lastname, firstname, contact_id from ".$_SESSION['tablename']['contacts']." where (lastname like '%".$db->protect_string_db($_REQUEST['what'])."%' or firstname like '".$db->protect_string_db($_REQUEST['what'])."%' or society like '%".$db->protect_string_db($_REQUEST['what'])."%') and enabled = 'Y'");
}
//$db->show();
while($line = $db->fetch_object())
{
	if($line->is_corporate_person == "Y")
	{
		array_push($listArray, $db->show_string($line->society)." ".$db->show_string($line->lastname)." ".$db->show_string($line->firstname)." (contact:".$line->contact_id.")");
	}
	else
	{
		array_push($listArray, $db->show_string($line->lastname)." ".$db->show_string($line->firstname)." (contact:".$line->contact_id.")");
	}
}
echo "<ul>\n";
$authViewList = 0;
foreach($listArray as $what)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
	echo "<li>".$what."</li>\n";
	if($flagAuthView)
	{
		echo "<li>...</li>\n";
		break;
	}
		$authViewList++;
}
echo "</ul>";
