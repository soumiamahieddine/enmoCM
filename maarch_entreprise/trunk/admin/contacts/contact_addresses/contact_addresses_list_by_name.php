<?php
/*
*    Copyright 2014 Maarch
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
*
* @brief List of structures for autocompletion
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR."maarch_entreprise".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");
$contact = new contacts_v2();
$db = new dbquery();
$db->connect();
$query = "select id, lastname as tag, firstname, contact_purpose_id from ".$_SESSION['tablename']['contact_addresses']." 
			where (lower(lastname) like lower('%".$db->protect_string_db($_REQUEST['what'])."%')
			or lower(firstname) like lower('%".$db->protect_string_db($_REQUEST['what'])."%')
			or lower(address_town) like lower('%".$db->protect_string_db($_REQUEST['what'])."%'))";

if(isset($_GET['id']) &&  $_GET['id'] <> ''){
	$query .= ' and id <> '.$_GET['id'].' and contact_id = '.$_SESSION['contact']['current_contact_id'];
} else if (isset($_REQUEST['idContact']) &&  $_REQUEST['idContact'] <> ''){
	$query .= ' and contact_id = '.$_REQUEST['idContact'];
}

$query .= " order by lastname";
$db->query($query);
// $db->show();

$listArray = array();
while($line = $db->fetch_object())
{
	$listArray[$line->id] = $contact->get_label_contact($line->contact_purpose_id, $_SESSION['tablename']['contact_purposes']) . ' : ' . $line->tag . ' '. $line->firstname;
}
echo "<ul>\n";
$authViewList = 0;

foreach($listArray as $key => $what)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
    echo "<li id=".$key.">".$what."</li>\n";
	if($flagAuthView)
	{
		echo "<li id=".$key.">...</li>\n";
		break;
	}
	$authViewList++;
}
echo "</ul>";
