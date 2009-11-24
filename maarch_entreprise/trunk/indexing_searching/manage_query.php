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
* @brief  Script used by an Ajax object to manage saved queries(create, modify and delete)
*
* @file manage_query.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

$core_tools = new core_tools();
$core_tools->load_lang();
$db = new dbquery();
$req = new request();
 if($_POST['action'] == "creation")
 {
	 $func_date = $req->current_datetime();
	if(isset($_POST['name']) && !empty($_POST['name']))
	{
		$name = preg_replace('/[\'"]/', '', $_POST['name']);
		$db->connect();
		$db->query("select query_id from ".$_SESSION['tablename']['saved_queries']." where user_id ='".$db->protect_string_db($_SESSION['user']['UserId'])."' and query_name='".$db->protect_string_db($_POST['name'])."'");

		if($db->nb_result() < 1)
		{
			$db->query("insert into ".$_SESSION['tablename']['saved_queries']." (user_id, query_name, creation_date, created_by, query_type, query_txt)
		values ('".$db->protect_string_db($_SESSION['user']['UserId'])."', '".$db->protect_string_db($_POST['name'])."', ". $func_date.",'".$db->protect_string_db($_SESSION['user']['UserId'])."', 'my_search', '".$db->protect_string_db($_SESSION['current_search_query'])."' )", true);
		}
		else
		{
			$res = $db->fetch_object();
			$id = $res->query_id;
			$db->query("update ".$_SESSION['tablename']['saved_queries']." set query_txt = '".$db->protect_string_db($_SESSION['current_search_query'])."', last_modification_date = ". $func_date." where user_id ='".$db->protect_string_db($_SESSION['user']['UserId'])."' and query_name='".$db->protect_string_db($_POST['name'])."'", true);
		}
		if(!$db->query )
		{
			echo "{status : 2}";
			exit();
		}
		else
		{
			echo "{status : 0}";
			exit();
		}
	}
	else
	{
		echo "{status : 3}";
	}
 }
 else if($_POST['action'] == "load")
 {
	if(isset($_POST['id']) && !empty($_POST['id']))
	{
		$db->connect();
		$db->query("select query_txt from ".$_SESSION['tablename']['saved_queries']." where query_id = ".$_POST['id'], true);
	}
	if(!$db->query )
	{
		echo "{'status' : 2, 'query':'".$db->show()."'}";
	}
	else
	{
		$res = $db->fetch_object();
		echo "{'status' : 0, 'query':".$res->query_txt."}";
	}
 }
 else if($_POST['action'] == "delete")
 {
	if(isset($_POST['id']) && !empty($_POST['id']))
	{
		$db->connect();
		$db->query("delete from ".$_SESSION['tablename']['saved_queries']." where query_id = ".$_POST['id'], true);
	}
	if(!$db->query )
	{
		echo "{'status' : 2}";
	}
	else
	{
		echo "{'status' : 0}";
	}
 }
 else
 {
	echo "{status : 1}";
 }
?>
