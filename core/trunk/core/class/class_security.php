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
* @brief   Contains all the functions to manage the users groups security and connexion to the application
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   contains all the functions to manage the users groups security through session variables
*
*<ul>
*  <li>Management of application connexion</li>
*  <li>Management of user rigths</li>
*</ul>
* @ingroup core
*/

//Requires to launch history functions
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");

class security extends dbquery
{

	/**
	* Loads the security parameters for a group into session variables.
	*
	* @param  $group_id string User group identifier
	*/
	public function load_security_group($group_id)
	{
		$tab = array();
		$this->connect();
		$this->query("select * from ".$_SESSION['tablename']['security'] ." where group_id = '".$group_id."'");

		if($this->nb_result() == 0)
		{
			$_SESSION['m_admin']['groups']['security'] = array();
		}
		else
		{
			$_SESSION['m_admin']['groups']['security'] = array();

			while($res = $this->fetch_object())
			{
				$ind = $this->get_ind_collection($res->coll_id);
				array_push($_SESSION['m_admin']['groups']['security'],array("GROUP_ID" => $res->group_id,"COLL_ID" => $res->coll_id, "IND_COLL_SESSION" => $ind, "WHERE_CLAUSE" => $res->where_clause, "MAARCH_COMMENT" => $res->commment ,"CAN_INSERT" => $res->can_insert ,"CAN_UPDATE" => $res->can_update, "CAN_DELETE" => $res->can_delete));
			}
		}
		$_SESSION['m_admin']['load_security'] = false;
	}

	/**
	* Gets the indice of the collection in the  $_SESSION['collections'] array
	*
	* @param  $coll_id string  Collection identifier
	* @return integer Indice of the collection in the $_SESSION['collections'] or -1 if not found
	*/
	public function get_ind_collection($coll_id)
	{
		for($i=0;$i< count($_SESSION['collections']); $i++)
		{
			if(trim($_SESSION['collections'][$i]['id']) == trim($coll_id))
			{
				return $i;
			}
		}
		return -1;
	}

	/**
	* Loads the services of a user group in session variables.
	*
	* @param  $group_id string User group identifier
	*/
	public function load_services_group($group_id)
	{
		$this->connect();
		$this->query("select service_id from ".$_SESSION['tablename']['usergroup_services'] ." where group_id = '".$group_id."'");
		if($this->nb_result() == 0)
		{
			$_SESSION['m_admin']['groups']['services'] = array();
		}
		else
		{
			$_SESSION['m_admin']['groups']['services']=array();
			while($value = $this->fetch_object())
			{
				array_push($_SESSION['m_admin']['groups']['services'],trim($value->service_id));
			}
		}
		$_SESSION['m_admin']['load_services'] = false;
	}

	/**
	* Inits the session variables related to the user group administration.
	*
	*/
	public function init_session()
	{
		$_SESSION['m_admin']['groups'] = array();
		$_SESSION['m_admin']['groups']['GroupId'] = "";
		$_SESSION['m_admin']['groups']['desc'] = "";
		$_SESSION['m_admin']['groups']['security'] = array();
		$_SESSION['m_admin']['groups']['services'] = array();
		$_SESSION['m_admin']['init'] = false;
	}

	/**
	* Inits to ‘N’ (no) the rights in the session variables related to the user group administration.
	*
	*/
	public function init_rights_session()
	{
		for($i=0; $i < count($_SESSION['m_admin']['groups']['security']); $i++)
		{
			$_SESSION['m_admin']['groups']['security'][$i]['CAN_INSERT'] = 'N';
			$_SESSION['m_admin']['groups']['security'][$i]['CAN_UPDATE'] = 'N';
			$_SESSION['m_admin']['groups']['security'][$i]['CAN_DELETE'] = 'N';
		}
	}

	/**
	* Set the rights (insert or update, depending on the parameter) for the collection passed on parameters.
	*
	* @param   $coll_id string Collection identifier
	* @param  $where  string 'CAN_INSERT', 'CAN_DELETE', or 'CAN_UPDATE'
	*/
	public function set_rights_session($coll_id, $where)
	{
		for($i=0; $i < count($_SESSION['m_admin']['groups']['security']); $i++)
		{
			if( in_array($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'], $coll_id))
			{
				$_SESSION['m_admin']['groups']['security'][$i][$where] = 'Y';
			}
		}
		$tab = array();
	}

	/**
	* Removes the security rights on the collections passed in parameters.
	*
	* @param   $tab array  Collections rights array
	*/
	public function remove_security($tab)
	{
		$unset_id = array();
		for($j=0;$j<count($tab);$j++)
		{
			for($i=0;$i<count($_SESSION['m_admin']['groups']['security']);$i++)
			{
				if(trim($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']) == trim($tab[$j]))
				{
					unset($_SESSION['m_admin']['groups']['security'][$i]);
				//	array_push($_SESSION['m_admin']['groups']['security'], $i);
				}
			}
		}
		$_SESSION['m_admin']['groups']['security'] = array_values($_SESSION['m_admin']['groups']['security']);
	}

	/**
	* Adds security parameters of a group in the session variables related to the user group administration.
	*
	* @param   $coll_id string Collection identifier
	* @param   $where string Where clause : security
	* @param   $comment string Comment on the collection
	* @param   $insert string Insert right : Y/N
	* @param   $update string Update right : Y/N
	* @param   $delete string Update right : Y/N
	* @param   $mode string Mode : 'up' or 'add'
	*/
	public function add_grouptmp_session($coll_id, $where, $comment, $insert, $update, $delete, $mode)
	{
		if(empty($mode))
		{
			$_SESSION['error'] = _ERROR_SECURITY_LOADING;
		}
		elseif($mode == "up")
		{
			for($i=0;$i< count($_SESSION['m_admin']['groups']['security']);$i++)
			{
				if($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'] == $coll_id)
				{
					$_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'] = $where;
					$_SESSION['m_admin']['groups']['security'][$i]['COMMENT'] = $comment;
					$_SESSION['m_admin']['groups']['security'][$i]['CAN_INSERT'] = $insert;
					$_SESSION['m_admin']['groups']['security'][$i]['CAN_UPDATE'] = $update;
					$_SESSION['m_admin']['groups']['security'][$i]['CAN_DELETE'] = $delete;
					break;
				}
			}
		}
		else
		{
			$ind = $this->get_ind_collection($coll_id);
			$tab = array();
			$tab[0] = array("GROUP_ID" => "" , "COLL_ID" => $coll_id , "IND_COLL_SESSION" => $ind,"WHERE_CLAUSE" => $where, "MAARCH_COMMENT" => $comment ,"CAN_INSERT" => $insert ,"CAN_UPDATE" => $update, 'CAN_DELETE' => $delete);
			array_push($_SESSION['m_admin']['groups']['security'] , $tab[0]);
			$_SESSION['m_admin']['load_security'] = false;
		}
	}

	/**
	* Updates the database with the user groups security of the administration variables in session.
	*
	*/
	public function load_db()
	{
		$this->connect();
		$this->query("DELETE FROM ".$_SESSION['tablename']['security'] ." where group_id = '".$_SESSION['m_admin']['groups']['GroupId']."'");
		for($i=0; $i < count($_SESSION['m_admin']['groups']['security'] ); $i++)
		{
			if($_SESSION['m_admin']['groups']['security'][$i] <> "")
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['security']." VALUES ('".$_SESSION['m_admin']['groups']['GroupId']."', '".$_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']."', '".$this->protect_string_db($_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'])."', '".$_SESSION['m_admin']['groups']['security'][$i]['COMMENT']."', '".$_SESSION['m_admin']['groups']['security'][$i]['CAN_INSERT']."' , '".$_SESSION['m_admin']['groups']['security'][$i]['CAN_UPDATE']."', '".$_SESSION['m_admin']['groups']['security'][$i]['CAN_DELETE']."')");
			}
		}
	}

	/**
	* Tests the syntax of the where clause of all collections for a  usergroup
	*
	* @return bool True if the syntax is correct, False otherwise
	*/
	public function where_test()
	{
		$_SESSION['error'] = "";
		$this->connect();
		$where = "";
		$res2 = true;
		for($i=0; $i < count($_SESSION['m_admin']['groups']['security'] ); $i++)
		{
			if($_SESSION['m_admin']['groups']['security'][$i] <> "")
			{
				if(trim($_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE']) == '')
				{
					$where = " ";
					$_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'] = ' ';
				}
				else
				{
					$where = " ".$_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'] ;
					$where = str_replace("\\", "", $where);
					$where = $this->process_security_where_clause($where, $_SESSION['user']['UserId']);
				}
				$ind = $this->get_ind_collection($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']);
				$selectWhereTest = array();
				$selectWhereTest[$_SESSION['collections'][$ind]['view']]= array();
				array_push($selectWhereTest[$_SESSION['collections'][$ind]['view']],"res_id");
				$tabResult = array();
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
				$request = new request();
				if(str_replace(" ", "", $where) == "")
				{
					$where = "";
				}
				$where = str_replace("where", " ", $where);
				$tabResult = $request->select($selectWhereTest, $where, "", $_SESSION['config']['databasetype'], 10, false, "", "", "", true, true);
				
				if(!$tabResult )
				{
					$_SESSION['error'] .= " ".$_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'];
					$res2 = false;
					break;
				}
			}
		}
		return $res2;
	}

	/**
	* Loads data related to the user groups (group name, role, primary group or not) into session variables
	*
	*/
	public function load_groups($user_id)
	{
		$tab['groups'] = array();
		$tab['primarygroup'] = '';
		//$_SESSION['user']['groups'] = array();
		//$_SESSION['user']['primarygroup'] ="";
		$this->connect();
		if($user_id == "superadmin")
		{
			$this->query("select group_id from ".$_SESSION['tablename']['usergroups']." where enabled= 'Y'");
			if($this->nb_result() < 1)
			{
				$_SESSION['error'] = _USER_NO_GROUP.'. '._MORE_INFOS." <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
				header("location: ".$_SESSION['config']['businessappurl']."index.php");
				exit();
			}
			else
			{
				$i =0;
				while($line = $this->fetch_object())
				{

					//$_SESSION['user']['groups'][$i]['GROUP_ID'] = $line->group_id;
					$tab['groups'][$i]['GROUP_ID'] = $line->group_id;
					$tab['groups'][$i]['ROLE'] = '';
					//$_SESSION['user']['groups'][$i]['ROLE'] = '';
					$i++;
				}
			}
		}
		else
		{
			$this->query("select uc.group_id, uc.primary_group, uc.role from ".$_SESSION['tablename']['usergroup_content']." uc , ".$_SESSION['tablename']['usergroups']." u where uc.user_id ='".$user_id."' and u.group_id = uc.group_id and u.enabled= 'Y'");
			if($this->nb_result() < 1)
			{
				$_SESSION['error'] = _USER_NO_GROUP.'. '._MORE_INFOS." <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
				header("location: ".$_SESSION['config']['businessappurl']."index.php");
				exit();
			}
			else
			{
				$i =0;
				while($line = $this->fetch_object())
				{
					//$_SESSION['user']['groups'][$i]['GROUP_ID'] = $line->group_id;
					$tab['groups'][$i]['GROUP_ID'] = $line->group_id;
					if($line->primary_group == 'Y')
					{
						//$_SESSION['user']['primarygroup'] = $line->group_id;
						$tab['primarygroup'] = $line->group_id;
					}
					//$_SESSION['user']['groups'][$i]['ROLE'] = $line->role;
					$tab['groups'][$i]['ROLE'] = $line->role;
					$i++;
				}
			}
		}
		return $tab;
	}

	/**
	* Loads into session, the security parameters corresponding to the user groups.
	*
	*/
	public function load_security($user_id)
	{
		$tab['collections'] = array();
		$tab['security'] = array();

		$this->connect();

		if($user_id == "superadmin")
		{
			for($i=0; $i<count($_SESSION['collections']);$i++)
			{
				array_push($tab['security'], array('coll_id' => $_SESSION['collections'][$i]['id'], 'table'  => $_SESSION['collections'][$i]['table'], 'label_coll'  => $_SESSION['collections'][$i]['label'],'view'  => $_SESSION['collections'][$i]['view'], 'where' =>" (1=1) ", 'can_insert' => 'Y', 'can_update' => 'Y', 'can_delete' => 'Y' ));
				array_push($tab['collections'], $_SESSION['collections'][$i]['id']);
			}
		}
		else
		{
			$this->query("select s.group_id, s.coll_id, s.where_clause , s.can_insert, s.can_update,s.can_delete  from ".$_SESSION['tablename']['security']." s, ".$_SESSION['tablename']['usergroup_content']." ugc , ".$_SESSION['tablename']['usergroups']." u where ugc.user_id='".$user_id."' and ugc.group_id = s.group_id and ugc.group_id = u.group_id and u.enabled = 'Y'");
			
			$i =0;
			$can_index = false;
			$can_postindex = false;
			while($line = $this->fetch_object())
			{
				$where_clause = $line->where_clause;
				$where_clause = $this->process_security_where_clause($where_clause, $user_id);
				$where_clause = str_replace('where', '', $where_clause);
				if( ! in_array($line->coll_id, $tab['collections'] ) )
				{
					$tab['security'][$i]['coll_id'] = $line->coll_id;
					$ind = $this->get_ind_collection($line->coll_id);
					$tab['security'][$i]['table'] = $_SESSION['collections'][$ind]['table'];
					$tab['security'][$i]['label_coll'] = $_SESSION['collections'][$ind]['label'];
					$tab['security'][$i]['view'] =  $_SESSION['collections'][$ind]['view'];
					if(trim($where_clause) <> "" && $where_clause <> " "  )
					{
						$where =  "( ".$this->show_string($where_clause)." )";
					}
					else
					{
						$where = "( 1=1 )";
					}
					$tab['security'][$i]['where'] = $where;
					$tab['security'][$i]['can_insert'] = $line->can_insert;
					$tab['security'][$i]['can_update'] = $line->can_update;
					$tab['security'][$i]['can_delete'] = $line->can_delete;
					array_push($tab['collections'] , $line->coll_id);
					$i++;
				}
				else
				{
					$key = array_search($line->coll_id, $tab['collections'] );
					if(trim($where_clause) == "")
					{
						$where = "( 1=1 )";
					}
					else
					{
						$where =  "( ".$this->show_string($where_clause)." )";
					}
					$tab['security'][$key]['where'] .= " or ".$where;
					if($line->can_insert == 'Y')
					{
						$tab['security'][$key]['can_insert'] = $line->can_insert;
					}
					if($line->can_update == 'Y')
					{
						$tab['security'][$key]['can_update'] = $line->can_update;
					}
					if($line->can_delete == 'Y')
					{
						$tab['security'][$key]['can_delete'] = $line->can_update;
					}
				}
			}
		}
		return $tab;
	}

	/**
	* Logs a user
	*
	* @param  $s_login  string User login
	* @param  $pass string User password
	*/
	public function login($s_login,$pass, $method = false)
	{
		$this->connect();
		if ($method == 'activex')
		{
			if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
				$query = "select * from ".$_SESSION['tablename']['users']." where user_id ilike '".$this->protect_string_db($s_login)."' and STATUS <> 'DEL' and loginmode = 'activex'";

			else
				$query = "select * from ".$_SESSION['tablename']['users']." where user_id like '".$this->protect_string_db($s_login)."'  and STATUS <> 'DEL' and loginmode = 'activex'";
		}
		else
		{
			if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
				$query = "select * from ".$_SESSION['tablename']['users']." where user_id ilike '".$this->protect_string_db($s_login)."' and password = '".$pass."' and STATUS <> 'DEL' and loginmode = 'standard'";

			else
				$query = "select * from ".$_SESSION['tablename']['users']." where user_id like '".$this->protect_string_db($s_login)."' and password = '".$pass."' and STATUS <> 'DEL' and loginmode = 'standard'";
		}
		$this->query($query);
		
		if($this->nb_result() > 0)
		{
			$line = $this->fetch_object();
			if($line->enabled == "Y")
			{
				$_SESSION['user']['change_pass'] = $line->change_password;
				$_SESSION['user']['UserId'] = $line->user_id;
				$_SESSION['user']['FirstName'] = $line->firstname;
				$_SESSION['user']['LastName'] = $line->lastname;
				$_SESSION['user']['Phone'] = $line->phone;
				$_SESSION['user']['Mail'] = $line->mail;
				$_SESSION['user']['department'] = $line->department;
				$_SESSION['error'] =  "";
				setcookie("maarch", "UserId=".$_SESSION['user']['UserId']."&key=".$line->cookie_key,time()-3600000);
				$key = md5(time()."%".$_SESSION['user']['FirstName']."%".$_SESSION['user']['UserId']."%".$_SESSION['user']['UserId']."%".date("dmYHmi")."%");

				if ($_SESSION['config']['databasetype'] == "ORACLE")
					$this->query("update ".$_SESSION['tablename']['users']." set cookie_key = '".$key."', cookie_date = SYSDATE where user_id = '".$_SESSION['user']['UserId']."' and mail = '".$_SESSION['user']['Mail']."'");
				else
					$this->query("update ".$_SESSION['tablename']['users']." set cookie_key = '".$key."', cookie_date = '".date("Y-m-d")." ".date("H:m:i")."' where user_id = '".$_SESSION['user']['UserId']."' and mail = '".$_SESSION['user']['Mail']."'");

				setcookie("maarch", "UserId=".$_SESSION['user']['UserId']."&key=".$key,time()+($_SESSION['config']['cookietime']*1000));
				$tmp = $this->load_groups($_SESSION['user']['UserId']);
				$_SESSION['user']['groups'] = $tmp['groups'];
				$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];
				$tmp = $this->load_security($_SESSION['user']['UserId']);
				$_SESSION['user']['collections'] = $tmp['collections'];
				$_SESSION['user']['security'] = $tmp['security'];
				$this->load_enabled_services();
				require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
				$business_app_tools = new business_app_tools();
				$core_tools = new core_tools();
				$business_app_tools->load_app_var_session();
				$core_tools->load_var_session($_SESSION['modules']);
				$_SESSION['user']['services'] = $this->load_user_services($_SESSION['user']['UserId']);
				$core_tools->load_menu($_SESSION['modules']);

				if($_SESSION['history']['userlogin'] == "true")
				{
					require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					//add new instance in history table for the user's connexion
					$hist = new history();
					$ip = $_SERVER['REMOTE_ADDR'];
					$navigateur = addslashes($_SERVER['HTTP_USER_AGENT']);

					$hist->add($_SESSION['tablename']['users'],$_SESSION['user']['UserId'],"LOGIN","IP : ".$ip.", BROWSER : ".$navigateur , $_SESSION['config']['databasetype']);
				}
				
				if($_SESSION['user']['change_pass'] == 'Y')
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=change_pass");
					exit();
				}

				elseif(isset($_SESSION['requestUri']) && trim($_SESSION['requestUri']) <> ""&& !preg_match('/page=login/', $_SESSION['requestUri']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?".$_SESSION['requestUri']);
					exit();
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php");
					exit();
				}
			}
			else
			{
				$_SESSION['error'] = _SUSPENDED_ACCOUNT.'. '._MORE_INFOS." <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
				header("location: ".$_SESSION['config']['businessappurl']."index.php");
				exit();
			}
		}
		else
		{
			
			$_SESSION['error'] = _BAD_LOGIN_OR_PSW."&hellip;";
			header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_SESSION['config']['coreurl']);
			exit();
		}
	}

	/**
	* Reopens a session with the user's cookie
	*
	* @param  $s_UserId  string User identifier
	* @param  $s_key string Cookie key
	*/
	public function reopen($s_UserId,$s_key)
	{
		$this->connect();

		if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
			$query = "select * from ".$_SESSION['tablename']['users']." where user_id ilike '".$this->protect_string_db($s_UserId)."' and cookie_key = '".$s_key."' and STATUS <> 'DEL'";
		else
			$query = "select * from ".$_SESSION['tablename']['users']." where user_id like '".$this->protect_string_db($s_UserId)."' and cookie_key = '".$s_key."' and STATUS <> 'DEL'";

		$this->query($query);
		if($this->nb_result() > 0)
		{
			$line = $this->fetch_object();
			if($line->enabled == "Y")
			{
				$_SESSION['user']['UserId'] = $line->user_id;
				$_SESSION['user']['FirstName'] = $line->firstname;
				$_SESSION['user']['LastName'] = $line->lastname;
				$_SESSION['user']['Phone'] = $line->phone;
				$_SESSION['user']['Mail'] = $line->mail;
				$_SESSION['user']['department'] = $line->department;
				$_SESSION['error'] =  "";
				setcookie("maarch", "UserId=".$_SESSION['user']['UserId']."&key=".$line->cookie_key,time()-3600000);
				$key = md5(time()."%".$_SESSION['user']['FirstName']."%".$_SESSION['user']['UserId']."%".$_SESSION['user']['UserId']."%".date("dmYHmi")."%");
				$this->query("update ".$_SESSION['tablename']['users']." set cookie_key = '".$key."', cookie_date = '".date("Y-m-d")." ".date("H:m:i")."' where user_id = '".$_SESSION['user']['UserId']."' and mail = '".$_SESSION['user']['Mail']."'");
				setcookie("maarch", "UserId=".$_SESSION['user']['UserId']."&key=".$key,time()+($_SESSION['config']['cookietime']*60));

				$tmp = $this->load_groups($_SESSION['user']['UserId']);
				$_SESSION['user']['groups'] = $tmp['groups'];
				$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

				$tmp = $this->load_security($_SESSION['user']['UserId']);
				$_SESSION['user']['collections'] = $tmp['collections'];
				$_SESSION['user']['security'] = $tmp['security'];
				$this->load_enabled_services();

				require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
			
				$business_app_tools = new business_app_tools();
				$core_tools = new core_tools();
				$business_app_tools->load_app_var_session();
				$core_tools->load_var_session($_SESSION['modules']);

				$_SESSION['user']['services'] = $this->load_user_services($_SESSION['user']['UserId']);
				$core_tools->load_menu($_SESSION['modules']);
/*
				if($_SESSION['history']['userlogin'] == "true")
				{
					//add new instance in history table for the user's connexion
					$hist = new history();
					$ip = $_SERVER['REMOTE_ADDR'];
					$navigateur = addslashes($_SERVER['HTTP_USER_AGENT']);

					$hist->add($_SESSION['tablename']['users'],$_SESSION['user']['UserId'],"LOGIN","IP : ".$ip.", BROWSER : ".$navigateur , $_SESSION['config']['databasetype']);
				}
*/
				if($_SESSION['user']['change_pass'] == 'Y')
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=change_pass");
					exit();
				}
				/*if($_SESSION['origin'] == "scan")
				{
					header("location: ../../modules/indexing_searching/index_file.php");
					exit();
				}
				elseif($_SESSION['origin'] == "files")
				{
					header("location: ../../modules/indexing_searching/index_file.php");
					exit();
				}*/
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php");
					exit();
				}
			}
			else
			{
				$_SESSION['error'] = _SUSPENDED_ACCOUNT.'. '._MORE_INFOS." <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
				header("location: ".$_SESSION['config']['businessappurl']."index.php");
				exit();
			}
		}
		else
		{
			$_SESSION['error'] = _ERROR;
			header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_SESSION['config']['coreurl']);
			exit();
		}
	}

	/**
	* Loads the enabled services into session
	*
	*/
	private function load_enabled_services()
	{
		$_SESSION['enabled_services'] = array();
		for($i=0; $i<count($_SESSION['app_services']);$i++)
		{
			if($_SESSION['app_services'][$i]['enabled'] == "true")
			{
				array_push($_SESSION['enabled_services'], array('id' => $_SESSION['app_services'][$i]['id'], 'label' => $_SESSION['app_services'][$i]['name'], 'comment' =>$_SESSION['app_services'][$i]['comment'], 'type' => $_SESSION['app_services'][$i]['servicetype'],'parent' => 'application', 'system' => $_SESSION['app_services'][$i]['system_service']));
			}
		}
		foreach(array_keys($_SESSION['modules_services']) as $value)
		{
			for($i=0; $i < count($_SESSION['modules_services'][$value]); $i++)
			{
				if($_SESSION['modules_services'][$value][$i]['enabled'] == "true")
				{
					array_push($_SESSION['enabled_services'], array('id' => $_SESSION['modules_services'][$value][$i]['id'], 'label' => $_SESSION['modules_services'][$value][$i]['name'], 'comment' => $_SESSION['modules_services'][$value][$i]['comment'], 'type' => $_SESSION['modules_services'][$value][$i]['servicetype'],'parent' => $value, 'system' =>$_SESSION['modules_services'][$value][$i]['system_service'] ));
				}
			}
		}
	}

	/**
	* Loads into database the services for a user group
	*
	* @param  $services array Array os services
	* @param  $group string User group identifier
	*/
	public function load_services_db($services, $group)
	{
		$this->connect();
		$this->query("delete from ".$_SESSION['tablename']['usergroup_services']." where group_id = '".$group."'");
		for($i=0; $i<count($services);$i++)
		{
			$this->query("insert into ".$_SESSION['tablename']['usergroup_services']." values ('".$group."', '".$services[$i]."')");
		}
	}

	/**
	* Loads into session all the services for the superadmin
	*
	*/
	private function get_all_services()
	{
		$services = array();
		for($i=0; $i< count($_SESSION['enabled_services']);$i++)
		{
		//	$_SESSION['user']['services'][$_SESSION['enabled_services'][$i]['id']] = true;
			$services[$_SESSION['enabled_services'][$i]['id']] = true;
		}
		return $services;
	}

	/**
	* Loads into session all the services for a user
	*
	* @param  $user_id  string User identifier
	*/
	public function load_user_services($user_id)
	{
		$services = array();
		if($user_id == "superadmin")
		{
			$services = $this->get_all_services();
		}
		else
		{
			$this->connect();
			//$_SESSION['user']['services'] = array();
			require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroups.php");
			$group = new usergroups();
			for($i=0; $i< count($_SESSION['enabled_services']);$i++)
			{
				if($_SESSION['enabled_services'][$i]['system'] == true)
				{
					//$_SESSION['user']['services'][$_SESSION['enabled_services'][$i]['id']] = true;
					$services[$_SESSION['enabled_services'][$i]['id']] = true;
				}
				else
				{
					$this->query("select group_id from ".$_SESSION['tablename']['usergroup_services']." where service_id = '".$_SESSION['enabled_services'][$i]['id']."'");
					$find = false;
					while($res = $this->fetch_object())
					{
						if($group->in_group($user_id, $res->group_id) == true)
						{
							$find = true;
							break;
						}
					}
					if($find == true)
					{
						//$_SESSION['user']['services'][$_SESSION['enabled_services'][$i]['id']] = true;
						$services[$_SESSION['enabled_services'][$i]['id']] = true;
					}
					else
					{
						//$_SESSION['user']['services'][$_SESSION['enabled_services'][$i]['id']] = false;
						$services[$_SESSION['enabled_services'][$i]['id']] = false;
					}
				}
			}
		}
		return $services;
	}

	/******************* COLLECTION MANAGEMENT FUNCTIONS *******************/

	/**
	* Returns all collections where we can insert new documents (with tables)
	*
	* @return array Collections where inserts are allowed
	*/
	public function retrieve_insert_collections()
	{
		$arr = array();
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if(isset($_SESSION['collections'][$i]['table']) && !empty($_SESSION['collections'][$i]['table']))
			{
				array_push($arr, $_SESSION['collections'][$i]);
			}
		}
		return $arr;
	}

	/**
	* Returns a script related to a collection
	*
	* @param  $coll_id  string Collection identifier
	* @param  $script_name  string Script name "script_add", "script_search", "script_search_result", "script_details"
	* @return string Script name or empty string if not found
	*/
	public function get_script_from_coll($coll_id, $script_name)
	{
		for($i=0; $i < count($_SESSION['collections']);$i++)
		{
			if(trim($_SESSION['collections'][$i]['id']) == trim($coll_id))
			{
				return trim($_SESSION['collections'][$i][$script_name]);
			}
		}
		return '';
	}

	/**
	* Returns the collection identifier from a table
	*
	* @param  $table  string Tablename
	* @return string Collection identifier or empty string if not found
	*/
	public function retrieve_coll_id_from_table($table)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['table'] == $table)
			{
				return $_SESSION['collections'][$i]['id'];
			}
		}
		return '';
	}

	/**
	* Returns the collection table from a view
	*
	* @param  $view string View
	* @return string Collection table or empty string if not found
	*/
	public function retrieve_coll_table_from_view($view)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['view'] == $view)
			{
				return $_SESSION['collections'][$i]['table'];
			}
		}
		return '';
	}

	/**
	* Returns the collection identifier from a view
	*
	* @param  $view string View
	* @return string Collection identifier or empty string if not found
	*/
	public function retrieve_coll_id_from_view($view)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['view'] == $view)
			{
				return $_SESSION['collections'][$i]['id'];
			}
		}
		return '';
	}


	/**
	* Returns the view of a collection from the collection identifier
	*
	* @param string $coll_id  Collection identifier
	* @return string View name or empty string if not found
	*/
	public function retrieve_view_from_coll_id($coll_id)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['id'] == $coll_id)
			{
				return $_SESSION['collections'][$i]['view'];
			}
		}
		return '';
	}

	/**
	* Returns the view of a collection from the table of the collection
	*
	* @param string $table  Tablename
	* @return string View name or empty string if not found
	*/
	public function retrieve_view_from_table($table)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['table'] == $table)
			{
				return $_SESSION['collections'][$i]['view'];
			}
		}
		return '';
	}

	/**
	* Returns the table of the collection from the collection identifier
	*
	* @param string $coll_id  Collection identifier
	* @return string Table name or empty string if not found
	*/
	public function retrieve_table_from_coll($coll_id)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['id'] == $coll_id)
			{
				return $_SESSION['collections'][$i]['table'];
			}
		}
		return '';
	}

	/**
	* Returns the table of the collection from the view of the collection
	*
	* @param string $view  View
	* @return string Table name or empty string if not found
	*/
	public function retrieve_table_from_view($view)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['view'] == $view)
			{
				return $_SESSION['collections'][$i]['table'];
			}
		}
		return '';
	}

	/**
	* Returns the collection  label from the table of the collection
	*
	* @param string $table  Tablename
	* @return string Collection label or empty string if not found
	*/
	public function retrieve_coll_label_from_table($table)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['table'] == $table)
			{
				return $_SESSION['collections'][$i]['label'];
			}
		}
		return '';
	}

	/**
	* Returns the collection  label from the collection identifier
	*
	* @param string $coll_id  Collection identifier
	* @return string Collection label or empty string if not found
	*/
	public function retrieve_coll_label_from_coll_id($coll_id)
	{
		for($i=0; $i<count($_SESSION['collections']);$i++)
		{
			if($_SESSION['collections'][$i]['id'] == $coll_id)
			{
				return $_SESSION['collections'][$i]['label'];
			}
		}
		return '';
	}

	////////////////USER RELATED

	/**
	* Returns the collection identifier for the current user from the collection table (using $_SESSION['user']['security'])
	*
	* @param  $table  string Tablename
	* @return string Collection identifier or empty string if not found
	*/
	public function retrieve_user_coll_id($table)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['table'] == $table)
			{
				return $_SESSION['user']['security'][$i]['coll_id'];
			}
		}
		return false;
	}

	/**
	* Return all collections where the current user can insert new documents (with table)
	*
	*/
	/**
	* Return all collections where the current user can insert new documents (with table)
	*
	* @return array Array of all collections where the current user can insert new documents
	*/
	public function retrieve_user_insert_coll()
	{
		$arr = array();
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if(isset($_SESSION['user']['security'][$i]['table']) && !empty(	$_SESSION['user']['security'][$i]['table']) && $_SESSION['user']['security'][$i]['can_insert'] == 'Y')
			{
				$ind = $this->get_ind_collection($_SESSION['user']['security'][$i]['coll_id']);
				array_push($arr, array('coll_id'=> $_SESSION['user']['security'][$i]['coll_id'], 'label_coll' => $_SESSION['collections'][$ind]['label'] , 'table' => $_SESSION['user']['security'][$i]['table']));
			}
		}
		return $arr;
	}

	/**
	* Checks if the current user can do the action on the collection
	*
	* @param string $coll_id  Collection identifier
	* @param string $action  can_insert, can_update, can_delete
	* @return True if the user can do the action on the collection, False otherwise
	*/
	public function collection_user_right($coll_id, $action)
	{
		$func = new functions();
		$flag = false;
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if(($_SESSION['user']['security'][$i]['coll_id'] == $coll_id)  && $_SESSION['user']['security'][$i][$action] == 'Y')
			{
				$flag = true;
			}
		}
		return $flag;
	}

	/**
	* Returns where clause of the collection for the current user from the collection identifier
	*
	* @param  $coll_id string Collection identifier
	* @return string Collection where clause or empty string if not found or the where clause is empty
	*/
	public function get_where_clause_from_coll_id($coll_id)
	{
		for($i=0; $i < count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['coll_id'] == $coll_id)
			{
				return $_SESSION['user']['security'][$i]['where'];
			}
		}
		return '';
	}

	/**
	* Returns where clause of the collection for the current user from the collection view
	*
	* @param  $view string View
	* @return string Collection where clause or empty string if not found or the where clause is empty
	*/
	public function get_where_clause_from_view($view)
	{
		for($i=0; $i < count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['view'] == $view)
			{
				return $_SESSION['user']['security'][$i]['where'];
			}
		}
		return '';
	}

	/**
	* Returns the collection table for the current user from the collection view (using $_SESSION['user']['security'])
	*
	* @param  $table  string Tablename
	* @return string Table name or False if not found
	*/
	public function retrieve_user_coll_table($view)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['view'] == $view)
			{
				return $_SESSION['user']['security'][$i]['table'];
			}
		}
		return false;
	}

	/***************DO NOT USE THESE FUNCTIONS : DEPRECATED****************/
	/**
	* Returns the collection view for the current user from the collection identifier (using $_SESSION['user']['security'])
	*
	* @param  $coll_id  string Collection identifier
	* @return string View name or False if not found
	*/
	public function retrieve_user_view_from_coll_id($coll_id)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['coll_id'] == $coll_id)
			{
				return $_SESSION['user']['security'][$i]['view'];
			}
		}
		return false;
	}

	/**
	* Returns the collection view for the current user from the collection table (using $_SESSION['user']['security'])
	*
	* @param  $table  string Table name
	* @return string View name or False if not found
	*/
	public function retrieve_user_view_from_table($table)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['table'] == $table)
			{
				return $_SESSION['user']['security'][$i]['view'];
			}
		}
		return false;
	}

	/**
	* Returns the collection table for the current user from the collection identifier (using $_SESSION['user']['security'])
	*
	* @param  $coll_id  string Collection identifier
	* @return string Table name or False if not found
	*/
	public function retrieve_user_coll_table2($coll_id)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['coll_id'] == $coll_id)
			{
				return $_SESSION['user']['security'][$i]['table'];
			}
		}
		return false;
	}

	/**
	* Returns the collection label for the current user from the collection table (using $_SESSION['user']['security'])
	*
	* @param  $table  string Table name
	* @return string Collection label or False if not found
	*/
	public function retrieve_user_coll_label($table)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['table'] == $table)
			{
				return $_SESSION['user']['security'][$i]['label_coll'];
			}
		}
		return false;
	}

	/**
	* Returns the collection label for the current user from the collection identifier (using $_SESSION['user']['security'])
	*
	* @param  $coll_id  string Collection identifier
	* @return string Collection label or False if not found
	*/
	public function retrieve_user_coll_label2($coll_id)
	{
		for($i=0; $i<count($_SESSION['user']['security']);$i++)
		{
			if($_SESSION['user']['security'][$i]['coll_id'] == $coll_id)
			{
				return $_SESSION['user']['security'][$i]['label_coll'];
			}
		}
		return false;
	}
	/*********************************************/

	/**
	* Checks the right on the document of a collection for the current user
	*
	* @param  $coll_id string Collection identifier
	* @param  $s_id string Document Identifier (res_id)
	* @return bool True if the current user has the right, False otherwise
	*/
	public function test_right_doc($coll_id, $s_id)
	{
		if(empty($coll_id) || empty($s_id))
		{
			return false;
		}
		$view = $this->retrieve_view_from_coll_id($coll_id);
		if(empty($view))
		{
			$view = $this->retrieve_table_from_coll($coll_id);
		}
		$where_clause = $this->get_where_clause_from_coll_id($coll_id);

		$query = "select res_id from ".$view." where res_id = ".$s_id;

		if(!empty($where_clause))
		{
			$query .= " and (".$where_clause.") ";
		}
		$this->connect();
		$this->query($query);

		if($this->nb_result() < 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Process a where clause, using the process_where_clause methods of the modules, the core and the apps
	*
	* @param  $where_clause string Where clause to process
	* @param  $user_id string User identifier
	* @return string Proper where clause
	*/
	public function process_security_where_clause($where_clause, $user_id)
	{
		if(!empty($where_clause))
		{
			$where = ' where '.$where_clause;

			// Process with the core vars
			$where = $this->process_where_clause($where, $user_id);
	
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
			// Process with the modules vars
			foreach(array_keys($_SESSION['modules_loaded']) as $key)
			{
				$path_module_tools = $_SESSION['modules_loaded'][$key]['path']."class".DIRECTORY_SEPARATOR."class_modules_tools.php";
				require_once($path_module_tools);
				$object = new $key;
				if(method_exists($object, 'process_where_clause'))
				{
					$where = $object->process_where_clause($where, $user_id);
				}
			}
			$where = preg_replace('/, ,/', ',', $where);
			$where = preg_replace('/\( ?,/', '(', $where);
			$where = preg_replace('/, ?\)/', ')', $where);

			// Process with the apps vars
			require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_business_app_tools.php');
			$object = new business_app_tools();
			if(method_exists($object, 'process_where_clause'))
			{
				$where = $object->process_where_clause($where, $user_id);
			}
			return $where;
		}
		else
		{
			return '';
		}
	}

	/**
	* Process a where clause with the core specific vars
	*
	* @param  $where_clause string Where clause to process
	* @param  $user_id string User identifier
	* @return string Proper where clause
	*/
	public function process_where_clause($where_clause, $user_id)
	{
		$where = $where_clause;
		if(preg_match('/@user/', $where_clause))
		{
			$where = str_replace("@user","'".trim($user_id)."'", $where_clause);
		}
		return $where;
	}
}
?>
