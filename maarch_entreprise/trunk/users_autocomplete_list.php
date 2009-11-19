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

include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");

$req = new request();
$req->connect();

$select = array();
$select[$_SESSION['tablename']['users']]= array('lastname', 'firstname', 'user_id');
if($_SESSION['config']['databasetype'] == "POSTGRESQL")
{
	$where = " (lastname ilike '".$req->protect_string_db($_REQUEST['Input'])."%' or firstname ilike '".$req->protect_string_db($_REQUEST['Input'])."%' or user_id ilike '".$req->protect_string_db($_REQUEST['Input'])."%') and status = 'OK' and enabled = 'Y'";
}
else
{
	$where = " (lastname like '".$req->protect_string_db($_REQUEST['Input'])."%' or firstname like '".$req->protect_string_db($_REQUEST['Input'])."%' or user_id like '".$req->protect_string_db($_REQUEST['Input'])."%') and status = 'OK' and enabled = 'Y'";
}

$other = 'order by lastname, firstname';

$res = $req->select($select, $where, $other, $_SESSION['config']['databasetype'], 11,false,"","","", false);

echo "<ul>\n";
for($i=0; $i< min(count($res), 10)  ;$i++)
{
	echo "<li>".$req->show_string($res[$i][0]['value']).', '.$req->show_string($res[$i][1]['value']).' ('.$res[$i][2]['value'].")</li>\n";
}
if(count($res) == 11)
{
	echo "<li>...</li>\n";
}
echo "</ul>";
