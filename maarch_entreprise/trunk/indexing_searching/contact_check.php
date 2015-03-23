<?php
/*
*
*   Copyright 2014 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief    Script to return ajax result
*
* @author   Alex ORLUC <dev@maarch.org>
* @date     $date$
* @version  $Revision$
*/

if(empty($_POST['contact_id'])){
	echo "success";
} else {
	$db = new dbquery();
	$db->connect();

	if (is_numeric($_POST['contact_id'])) {
		$where = "contact_id = ".$_POST['contact_id']." AND address_id = ".$_POST['address_id']." AND creation_date >= (select CURRENT_DATE + integer '-".$_SESSION['check_days_before']."')";
		$query = "SELECT res_id FROM res_view_letterbox WHERE ".$where;
	} else {
		$where = "(exp_user_id = '".$_POST['contact_id']."' OR dest_user_id = '".$_POST['contact_id']."') AND creation_date >= (select CURRENT_DATE + integer '-".$_SESSION['check_days_before']."')";
		$query = "SELECT res_id FROM res_view_letterbox WHERE ".$where;
	}

	if($_POST['res_id'] != "none"){
		$query .= " AND res_id NOT IN (".$_POST['res_id'].")";
		$_SESSION['excludeId'] = $_POST['res_id'];
	}

	$query .= "  ORDER by creation_date DESC limit 1";
	$db->query($query);
	// $db->show();

	if ($db->nb_result() > 0){
		$_SESSION['where_from_contact_check'] = " AND (".$where.")";
		echo "fail";
	} else {
		echo "success";
	}
}
