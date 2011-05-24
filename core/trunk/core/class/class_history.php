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
* @brief   Contains all the function to manage the history table
*
*<ul>
* <li>Connexion logs and events history management</li>
*</ul>
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Contains all the function to manage the history table
*
* @ingroup core
*/
class history extends dbquery
{
	/**
	* Inserts a record in the history table
	*
	* @param  $where  string Table or view of the event
	* @param  $id integer Identifier of the event to add
	* @param  $how string Event type (Keyword)
	* @param  $what string Event description
	* @param  $databasetype string Type of the database (MYSQL, POSTGRESQL, etc...)
	* @param  $id_module string Identifier of the module concerned by the event (admin by default)
	*/
	public function add($where,$id,$how,$what, $databasetype, $id_module = 'admin', $user = '')
	{
		if($databasetype == "SQLSERVER")
		{
			$date_now = "getdate()";
		}
		else if($databasetype == "MYSQL" || $databasetype == "POSTGRESQL" )
		{
			$date_now = "now()";
		}
		elseif($databasetype == "ORACLE")
		{
			$date_now = "SYSDATE";
		}
		$remote_ip = $_SERVER['REMOTE_ADDR'];
		$what = $this->protect_string_db($what, $databasetype);
        //$what = $this->protect_string_db($what);
		$this->connect();
		if(isset($_SESSION['user']['UserId'])) {
		    $user = $_SESSION['user']['UserId'];
		}
		$this->query(
			"INSERT INTO ".$_SESSION['tablename']['history']
		    ." (table_name, record_id , event_type , user_id , event_date , "
		    . "info , id_module, remote_ip) VALUES ('".$where."', '".$id."', '"
		    .$how."', '".$user."', ".$date_now.", '".$what."', '".$id_module
		    ."' , '".$remote_ip."')"
		);
		$this->disconnect();
	}

	/**
	* Gets the label of an history keyword
	*
	* @param  $id  string Key word identifier
	* @return  string Label of the key word or empty string
	*/
	public function get_label_history_keyword($id)
	{
		if(empty($id))
		{
			return '';
		}
		else
		{
			for($i=0; $i<count($_SESSION['history_keywords']);$i++)
			{
				if($id == $_SESSION['history_keywords'][$i]['id'])
				{
					return $_SESSION['history_keywords'][$i]['label'];
				}
			}
		}
		return '';
	}
}
?>
