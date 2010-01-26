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
* @defgroup basket Basket Module
*/

/**
* @brief   Module Basket :  Module Tools Class
*
* <ul>
* <li>Set the session variables needed to run the basket module</li>
* <li>Loads the baskets for the current user</li>
* <li>Manage the current basket with its actions (if any)</li>
*</ul>
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/

/**
* @brief   Module Basket : Module Tools Class
*
* <ul>
* <li>Loads the tables used by the baskets</li>
* <li>Set the session variables needed to run the basket module</li>
* <li>Loads the baskets for the current user</li>
* <li>Manage the current basket with its actions (if any)</li>
*</ul>
*
* @ingroup basket
*/
class basket extends dbquery
{
	/**
	* Loads basket  tables into sessions vars from the basket/xml/config.xml
	* Loads basket log setting into sessions vars from the basket/xml/config.xml
	*/
	public function build_modules_tables()
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		$xmlconfig = simplexml_load_file($path);

		$CONFIG = $xmlconfig->CONFIG;

		$_SESSION['config']['basket_reserving_time'] = (string) $CONFIG->reserving_time;

		// Loads the tables of the module basket  into session ($_SESSION['tablename'] array)
		$TABLENAME =  $xmlconfig->TABLENAME ;
		$_SESSION['tablename']['bask_baskets'] = (string) $TABLENAME->bask_baskets;
		$_SESSION['tablename']['bask_groupbasket'] = (string) $TABLENAME->bask_groupbasket;
		$_SESSION['tablename']['bask_users_abs'] = (string) $TABLENAME->bask_users_abs;
		$_SESSION['tablename']['bask_actions_groupbaskets'] = (string) $TABLENAME->bask_actions_groupbaskets;

		// Loads the log setting of the module basket  into session ($_SESSION['history'] array)
		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['basketup'] = (string) $HISTORY->basketup;
		$_SESSION['history']['basketadd'] = (string) $HISTORY->basketadd;
		$_SESSION['history']['basketdel'] = (string) $HISTORY->basketdel;
		$_SESSION['history']['basketval'] = (string) $HISTORY->basketval;
		$_SESSION['history']['basketban'] = (string) $HISTORY->basketban;
		$_SESSION['history']['userabs'] = (string) $HISTORY->userabs;
	}

	/**
	* Load into session vars all the basket specific vars : calls private methods
	*/
	public function load_module_var_session()
	{
		$this->load_activity_user();
		$this->load_baskets_pages();
		$this->load_basket();
		$this->load_basket_abs();
	}

	/**
	* Return the url of the basket result page  given an basket identifier.
	*
	* @param   $basket_id_page  string  Basket results page identifier
	* @param   $mode_page   string "frame" or "no_frame"
	* @return string url of the basket results page or empty string in error case
	*/
	public function retrieve_path_page($basket_id_page, $mode)
	{
		// Gets the indice of the $basket_id_page in the $_SESSION['basket_page'] to access all the informations on this page
		$path = '';
		$ind = -1;
		for($i=0; $i<count($_SESSION['basket_page']);$i++)
		{
			if(trim($_SESSION['basket_page'][$i]['ID'] ) == trim($basket_id_page))
			{
				$ind = $i;
				break;
			}
		}
		// If the page identifier is not found return an empty string
		if($ind == -1)
		{
			return '';
		}
		else // building the url
		{
			// The page is in the apps
			if(strtoupper($_SESSION['basket_page'][$ind]['ORIGIN']) == "APPS")
			{
				if(strtoupper($mode) == 'NO_FRAME')
				{
					$path = $_SESSION['config']['businessappurl']."index.php?page=".$_SESSION['basket_page'][$ind]['NAME'];
				}
				else if(strtoupper($mode) == 'FRAME')
				{
					$path = $_SESSION['config']['businessappurl'].$_SESSION['basket_page'][$ind]['NAME'].".php";
				}
				elseif(strtoupper($mode) == 'INCLUDE')
				{
					$path = "apps/".$_SESSION['config']['app_id']."/".$_SESSION['basket_page'][$ind]['NAME'].".php";
				}
				else
				{
					return '';
				}
			}// The page is in a module
			elseif(strtoupper($_SESSION['basket_page'][$ind]['ORIGIN']) == "MODULE")
			{
				$core_tools = new core_tools();
				// Error : The module name is empty or the module is not loaded
				if(empty($_SESSION['basket_page'][$ind]['MODULE']) || !$core_tools->is_module_loaded($_SESSION['basket_page'][$ind]['MODULE']))
				{
					return '';
				}
				else
				{
					if(strtoupper($mode) == 'NO_FRAME')
					{
						$path = $_SESSION['config']['businessappurl']."index.php?page=".$_SESSION['basket_page'][$ind]['NAME']."&module=".$_SESSION['basket_page'][$ind]['MODULE'];
					}
					else if(strtoupper($mode) == 'FRAME')
					{
						//$path = $_SESSION['urltomodules'].$_SESSION['basket_page'][$ind]['MODULE']."/".$_SESSION['basket_page'][$ind]['NAME'].".php";
						$path = $_SESSION['config']['businessappurl']."index.php?display=true&module=".$_SESSION['basket_page'][$ind]['MODULE']."&page=".$_SESSION['basket_page'][$ind]['NAME'];

					}
					elseif(strtoupper($mode) == 'INCLUDE')
					{
						//$path = $_SESSION['pathtomodules'].$_SESSION['basket_page'][$ind]['MODULE'].DIRECTORY_SEPARATOR.$_SESSION['basket_page'][$ind]['NAME'].".php";
						$path = 'modules'.DIRECTORY_SEPARATOR.$_SESSION['basket_page'][$ind]['MODULE'].DIRECTORY_SEPARATOR.$_SESSION['basket_page'][$ind]['NAME'].".php";
					}
					else
					{
						return '';
					}
				}
			} // Error
			else
			{
				return '';
			}
		}
		return $path;
	}

	/**
	* Loads in session ($_SESSION['basket_page'] array) the informations on the baskets results page from the basket/xml/basketpage.xml
	*
	*/
	private function load_baskets_pages()
	{
		$_SESSION['basket_page'] = array();
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."basketpage.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."basketpage.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."basketpage.xml";
		}
		$xmlfile = simplexml_load_file($path);
		$path_lang = "modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		$i =0;
		foreach($xmlfile->BASKETPAGE as $BASKETPAGE)
		{
			$tmp = (string)$BASKETPAGE->LABEL;
			// the label of the page comes from the module basket languages files
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$desc =  $tmp2;
			}
			else
			{
				$desc = $tmp;
			}
			$_SESSION['basket_page'][$i] = array("ID" => (string) $BASKETPAGE->ID, "LABEL" => $desc, "NAME" => (string) $BASKETPAGE->NAME, "ORIGIN" => (string) $BASKETPAGE->ORIGIN, "MODULE" => (string) $BASKETPAGE->MODULE);
			$i++;
		}
	}

	/**
	* Loads the baskets datas into session variables
	*
	*/
	public function load_basket()
	{
		$_SESSION['user']['baskets'] = array();

		$db = new dbquery();
		$db->connect();
		$db->query("select gb.basket_id from ".$_SESSION['tablename']['bask_groupbasket']." gb, ".$_SESSION['tablename']['bask_baskets']." b where gb.group_id = '".$_SESSION['user']['primarygroup']."' and gb.basket_id = b.basket_id order by b.basket_name ");
		//$db->show();
		while($res = $db->fetch_object())
		{
			$tmp = $this->get_baskets_data($res->basket_id, $_SESSION['user']['UserId']);
			//$this->show_array($tmp);
			array_push($_SESSION['user']['baskets'], $tmp );
		}
		//$this->show_array($_SESSION['user']['baskets']);

	}

	public function load_basket_abs()
	{
		$db = new dbquery();
		$db->connect();
		$db->query("select system_id, basket_id from ".$_SESSION['tablename']['bask_users_abs']." where new_user = '".$_SESSION['user']['UserId']."' ");
		//$db->show();
		while($res = $db->fetch_object())
		{
			array_push($_SESSION['user']['baskets'], $this->get_abs_baskets_data($res->basket_id, $_SESSION['user']['UserId'], $res->system_id));
		}
	//	$this->show_array($_SESSION['user']['baskets']);
		//exit();
	}

	/**
	* Get the actions for a group in a basket.
	*
	* @param   $basket_id   string  Basket identifier
	* @param   $group_id string  Users group identifier
	* @return array actions
	*/
	private function get_actions_from_groupbaket($basket_id, $group_id)
	{
		$actions = array();
		$this->connect();

		$this->query("select agb.id_action, agb.where_clause, agb.used_in_basketlist, agb.used_in_action_page, a.label_action, a.id_status, a.action_page
		from ".$_SESSION['tablename']['actions']." a, ".$_SESSION['tablename']['bask_actions_groupbaskets']." agb where a.id = agb.id_action and agb.group_id = '".$group_id."'
		and agb.basket_id = '".$basket_id."' and a.enabled = 'Y' and agb.default_action_list ='N'");
		$core = new core_tools();
		while($res = $this->fetch_object())
		{
			array_push($actions, array('ID' => $res->id_action, 'LABEL' => $res->label_action, 'WHERE' => $res->where_clause,
			'MASS_USE' => $res->used_in_basketlist, 'PAGE_USE' => $res->used_in_action_page, 'ID_STATUS' => $res->id_status,
			'ACTION_PAGE' => $res->action_page));
		}
		return $actions;
	}

	/**
	* Get the default action in a basket for a group
	*
	* @param  $basket_id   string  Basket identifier
	* @param   $group_id  string  Users group identifier
	* @return string action identifier or empty string in error case
	*/
	private function get_default_action($basket_id, $group_id)
	{
		$this->connect();
		$this->query("select agb.id_action
		from ".$_SESSION['tablename']['actions']." a, ".$_SESSION['tablename']['bask_actions_groupbaskets']." agb where a.id = agb.id_action and agb.group_id = '".$group_id."'
		and agb.basket_id = '".$basket_id."' and a.enabled = 'Y' and agb.default_action_list ='Y'");

		if($this->nb_result()<1)
		{
			return '';
		}
		else
		{
			$res = $this->fetch_object();
			return $res->id_action;
		}
	}


	/**
	* Make a given basket the current basket (using $_SESSION['current_basket'] array)
	*
	* @param   $id_basket   string Basket identifier
	*/
	public function load_current_basket($id_basket)
	{
		$_SESSION['current_basket'] = array();
		$_SESSION['current_basket']['id'] = trim($id_basket);
		$ind = -1;
		for($i=0; $i < count($_SESSION['user']['baskets']); $i++)
		{
			if($_SESSION['user']['baskets'][$i]['id'] == $_SESSION['current_basket']['id'])
			{
				$ind = $i;
				break;
			}
		}
		if($ind > -1)
		{
			$_SESSION['current_basket']['table'] = $_SESSION['user']['baskets'][$ind]['table'];
			$_SESSION['current_basket']['view'] = $_SESSION['user']['baskets'][$ind]['view'];
			$_SESSION['current_basket']['coll_id'] = $_SESSION['user']['baskets'][$ind]['coll_id'];
			$_SESSION['current_basket']['page_frame'] = $_SESSION['user']['baskets'][$ind]['page_frame'];
			$_SESSION['current_basket']['page_no_frame'] = $_SESSION['user']['baskets'][$ind]['page_no_frame'];
			$_SESSION['current_basket']['page_include'] = $_SESSION['user']['baskets'][$ind]['page_include'];
			$_SESSION['current_basket']['default_action'] =	$_SESSION['user']['baskets'][$ind]['default_action'];
			$_SESSION['current_basket']['label'] = $_SESSION['user']['baskets'][$ind]['name'];
			$_SESSION['current_basket']['clause'] = $_SESSION['user']['baskets'][$ind]['clause'];
			$_SESSION['current_basket']['actions'] = $_SESSION['user']['baskets'][$ind]['actions'];
		}
		$_SESSION['current_basket']['redirect_services'] =  $_SESSION['user']['baskets'][$ind]['redirect_services'];
		$_SESSION['current_basket']['redirect_users'] =  $_SESSION['user']['baskets'][$ind]['redirect_users'];
		$_SESSION['current_basket']['basket_owner'] = $_SESSION['user']['baskets'][$ind]['basket_owner'];
		$_SESSION['current_basket']['abs_basket'] = $_SESSION['user']['baskets'][$ind]['abs_basket'];
	}

	/**
	* Loads status from users and create var when he's missing.
	*
	*/
	private function load_activity_user()
	{
		$the_user = $_SESSION['user']['UserId'];
		$this->connect();
		$this->query("SELECT status from ".$_SESSION['tablename']['users']." where user_id='".$the_user."'");
		$line = $this-> fetch_object();

		if ($line->status == 'ABS')
		{
			$_SESSION['abs_user_status'] = true;
		}
		else
		{
			$_SESSION['abs_user_status'] = false;
		}
	}

	public function translates_actions_to_json($actions = array())
	{
		$actions_json = '{';

		if(count($actions) > 0)
		{
			for($i=0; $i<count($actions);$i++)
			{
				$actions_json .= "'".$actions[$i]['ID']."' : { 'where' : '".addslashes($actions[$i]['WHERE'])."',";
				$actions_json .= "'id_status' : '".$actions[$i]['ID_STATUS']."', 'confirm' : '".$actions[$i]['CONFIRM']."', ";
				$actions_json .= "'id_action_page' : '".$actions[$i]['ACTION_PAGE']."'}, ";
			}
			$actions_json = preg_replace('/, $/', '}', $actions_json);
		}

		if($actions_json == '{')
		{
			$actions_json = '{}';
		}
		return $actions_json;
	}
	/**
	* Builds the basket results list (using class_list_show.php method)
	*
	* @param   $param_list  array  Parameters array used to display the result list
	* @param   $actions actions  Array to be displayed in the list
	* @param   $line_txt  string String to be displayed at the bottom of the list to describe the default action
	*/
	public function basket_list_doc($param_list, $actions, $line_txt)
	{
		//$this->show_array($param_list);
		$action_form = '';
		$bool_check_form = false;
		$method = '';
		$actions_list = array();
	//	$actions_json = '{';
		// Browses the actions array to build the jason string that will be used to display the actions in the list
		if(count($actions) > 0)
		{
			for($i=0; $i<count($actions);$i++)
			{
				if($actions[$i]['MASS_USE'] == 'Y')
				{
					array_push($actions_list, array('VALUE' => $actions[$i]['ID'], 'LABEL' => addslashes($actions[$i]['LABEL'])));
				}
			}

		}

		$actions_json = $this->translates_actions_to_json($actions);

		if(count($actions_list) > 0)
		{
			$action_form = $_SESSION['config']['businessappurl']."index.php?display=true&page=manage_action&module=core";
			$bool_check_form = true;
			$method = 'get';
		}

		$do_action = false;
		if(!empty($_SESSION['current_basket']['default_action']))
		{
			$do_action = true;
		}
	
		$list = new list_show();
		if(!isset( $param_list['link_in_line']))
		{
			 $param_list['link_in_line'] = false;
		}
		if(!isset( $param_list['template']))
		{
			 $param_list['template'] = false;
		}
		if(!isset( $param_list['template_list']))
		{
			 $param_list['template_list'] = array();
		}
		if(!isset( $param_list['actual_template']))
		{
			 $param_list['actual_template'] = '';
		}

		if(!isset( $param_list['bool_export']))
		{
			 $param_list['bool_export'] = false;
		}

		$str = '';
		// Displays the list using list_doc method from class_list_shows
		$str .= $list->list_doc($param_list['values'],count($param_list['values']),$param_list['title'],$param_list['what'],$param_list['page_name'],
		$param_list['key'],$param_list['detail_destination'],$param_list['view_doc'],false,$method,
		$action_form ,'', $param_list['bool_details'], $param_list['bool_order'], $param_list['bool_frame'], $param_list['bool_export'], false, false ,
		true, $bool_check_form, '', $param_list['module'],false, '', '', $param_list['css'], $param_list['comp_link'], $param_list['link_in_line'], true, $actions_list,
		$param_list['hidden_fields'], $actions_json,$do_action , $_SESSION['current_basket']['default_action'], $param_list['open_details_popup'], $param_list['do_actions_arr'],  $param_list['template'], $param_list['template_list'], $param_list['actual_template'],  true);

		// Displays the text line if needed
		if(count($param_list['values']) > 0 && ($param_list['link_in_line'] || $do_action ) )
		{
			$str .= "<em>".$line_txt."</em>";
		}
		if(!isset( $param_list['mode_string']) || $param_list['mode_string'] == false)
		{
			echo $str;
		}
		else
		{
			return $str;
		}
	}

	/**
	* Returns the actions for the current basket for a given mode.
	* The mode can be "MASS_USE" or "PAGE_USE".
	*
	* @param   $res_id  string  Resource identifier (used in PAGE_USE mode to test the action where_clause)
	* @param   $coll_id  string Collection identifier (used in PAGE_USE mode to test the action where_clause)
	* @param   $mode  string  "PAGE_USE" or "MASS_USE"
	* @return array  Actions to be displayed
	*/
	public function get_actions_from_current_basket($res_id, $coll_id, $mode, $test_where = true)
	{
		$arr = array();
		// If parameters error return an empty array
		if(empty($res_id) || empty($coll_id) || (strtoupper($mode) <> 'MASS_USE' && strtoupper($mode) <> 'PAGE_USE'))
		{
			return $arr;
		}
		else
		{
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
			$sec =new security();
			$this->connect();
			$table = $sec->retrieve_view_from_coll_id($coll_id);
			if(empty($table))
			{
				$table = $sec->retrieve_table_from_coll_id($coll_id);
			}
			// If the view and the table of the collection is empty, return an empty array
			if(empty($table))
			{
				return $arr;
			}
			// If mode "PAGE_USE", add the action 'end_action' to validate the current action
			if($mode == 'PAGE_USE')
			{
				array_push($arr, array('VALUE' => 'end_action', 'LABEL' => _SAVE_CHANGES));
			}
			// Browsing the current basket actions to build the actions array
			for($i=0; $i<count($_SESSION['current_basket']['actions']);$i++)
			{
				// If in mode "PAGE_USE", testing the action where clause on the res_id before adding the action
				if(strtoupper($mode) == 'PAGE_USE' && $_SESSION['current_basket']['actions'][$i]['PAGE_USE'] == 'Y' && $test_where)
				{
					$where = ' where res_id = '.$res_id;
					if(!empty($_SESSION['current_basket']['actions'][$i]['WHERE']))
					{
						$where = $where.' and '.$_SESSION['current_basket']['actions'][$i]['WHERE'];
					}
					$this->query('select res_id from '.$table." ".$where);
					if($this->nb_result()> 0)
					{
						array_push($arr, array('VALUE' => $_SESSION['current_basket']['actions'][$i]['ID'], 'LABEL' => $_SESSION['current_basket']['actions'][$i]['LABEL']));
					}
				}
				elseif(strtoupper($mode) == 'PAGE_USE' && $_SESSION['current_basket']['actions'][$i]['PAGE_USE'] == 'Y' && !$test_where)
				{
					array_push($arr, array('VALUE' => $_SESSION['current_basket']['actions'][$i]['ID'], 'LABEL' => $_SESSION['current_basket']['actions'][$i]['LABEL']));
				}
				// If "MASS_USE" adding the actions in the array
				elseif(strtoupper($mode) == 'MASS_USE' && $_SESSION['current_basket']['actions'][$i]['MASS_USE'] == 'Y')
				{
					array_push($arr, array('VALUE' => $_SESSION['current_basket']['actions'][$i]['ID'], 'LABEL' => $_SESSION['current_basket']['actions'][$i]['LABEL']));
				}
			}
			return $arr;
		}
	}

	/**
	* Returns in an array the baskets of a given user  (Including the redirected baskets)
	*
	* @param  $user_id string Owner of the baskets (identifier)
	*/
	public function get_baskets($user_id)
	{
		$this->connect();
		$this->query("select b.basket_id, b.basket_name from ".$_SESSION['tablename']['bask_baskets']." b, ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['bask_groupbasket']." gb, ".$_SESSION['tablename']['usergroups']." u where uc.user_id = '".$user_id."' and uc.primary_group = 'Y' and gb.group_id = uc.group_id and b.basket_id = gb.basket_id and u.group_id = gb.group_id and u.enabled = 'Y' ");

		//$this->show();
		$tab = array();
		while($res = $this->fetch_object())
		{
			array_push($tab, array('id' => $res->basket_id, 'name' => $res->basket_name, 'is_virtual' => 'N', 'basket_owner' =>'', 'abs_basket' => false));
		}
		return array_merge($tab, $this->get_abs_baskets($user_id));

	}

	/**
	* Returns in an array the redirected baskets of a given user
	*
	* @param  $user_id string Owner of the baskets (identifier)
	*/
	public function get_abs_baskets($user_id)
	{
		$this->connect();
		$this->query("select basket_id, is_virtual, basket_owner from ".$_SESSION['tablename']['bask_users_abs']." mu where user_abs = '".$user_id."'");
		$db = new dbquery();
		$db->connect();
		$tab = array();
		while( $res = $this->fetch_object())
		{
			$basket_id = $res->basket_id;
			$basket_owner = $res->basket_owner;
			$is_virtual = $res->is_virtual;
			$db->query("select basket_name from ".$_SESSION['tablename']['bask_baskets']." where basket_id ='".$basket_id."'");
			$res2 = $db->fetch_object();
			$basket_name = $res2->basket_name;
			if($is_virtual == 'Y' && $basket_owner <>  '')
			{
				$db->query("select firstname, lastname from ".$_SESSION['tablename']['users']." where user_id = '".$basket_owner."'");
				$res2 = $db->fetch_object();
				$user_name = $res2->firstname.' '.$res2->lastname;
				$basket_name .= "(".$user_name.")";
			}
			else
			{
				$basket_owner = $user_id;
			}
			array_push($tab, array('id' => $basket_id, 'name' => $basket_name, 'is_virtual' => $is_virtual, 'basket_owner' => $basket_owner, 'abs_basket' => true));
		}
		return $tab;
	}

	/**
	* Returns in an array all the data of a basket for a user (checks if the basket is a redirected one and then if already a virtual one)
	*
	* @param  $basket_id string Basket identifier
	* @param  $user_id string User identifier
	*/
	public function get_baskets_data($basket_id, $user_id)
	{
		$tab = array();
		$this->connect();
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		$sec = new security();

		$this->query("select basket_id, coll_id, basket_name, basket_desc, basket_clause, is_generic from ".$_SESSION['tablename']['bask_baskets']." where basket_id = '".$this->protect_string_db($basket_id)."' and enabled = 'Y'");

		$res = $this->fetch_object();
		$tab['id'] = $res->basket_id;
		$tab['coll_id'] = $res->coll_id;
		$tab['table'] = $sec->retrieve_table_from_coll($tab['coll_id']);
		$tab['view'] = $sec->retrieve_view_from_coll_id($tab['coll_id']);
		$tab['is_generic'] = $res->is_generic;

		$tab['desc'] = $this->show_string($res->basket_desc);
		$tab['name'] = $this->show_string($res->basket_name);

		$tab['clause'] = $res->basket_clause;

		$is_virtual = 'N';
		$basket_owner = '';
		$abs_basket = false;

		/// TO DO : Test if tmp_user is empty
		if($user_id <> $_SESSION['user']['UserId'])
		{
			$this->query("select group_id from ".$_SESSION['tablename']['usergroup_content']." where primary_group = 'Y' and user_id = '".$user_id."'");
			$res = $this->fetch_object();
			$primary_group = $res->group_id;
		}
		else
		{
			$primary_group = $_SESSION['user']['primarygroup'];
		}
		$this->query("select sequence, can_redirect, can_delete, can_insert, result_page, redirect_basketlist, redirect_grouplist from ".$_SESSION['tablename']['bask_groupbasket']." where group_id = '".$primary_group."' and basket_id = '".$basket_id."' ");
		$res = $this->fetch_object();

		$basket_id_page = $res->result_page;
		$tab['id_page'] = $basket_id_page;
		// Retrieves the basket url (frame and no_frame modes)
		$basket_path_page_no_frame = $this->retrieve_path_page($basket_id_page,'no_frame');
		$basket_path_page_frame = $this->retrieve_path_page($basket_id_page,'frame');
		$basket_path_page_include = $this->retrieve_path_page($basket_id_page,'include');
		$tab['page_no_frame'] = $basket_path_page_no_frame;
		$tab['page_frame'] = $basket_path_page_frame;
		$tab['page_include'] = $basket_path_page_include;
		// Gets actions of the basket
		$tab['default_action'] = $this->get_default_action($basket_id,$primary_group );
		$tab['actions'] = $this->get_actions_from_groupbaket($basket_id,$primary_group );

		$tab['abs_basket'] = $abs_basket;
		$tab['is_virtual'] = $is_virtual;
		$tab['basket_owner'] = $basket_owner;

		//$tab['redirect_services'] = trim(stripslashes($res->redirect_basketlist));
		//$tab['redirect_users'] = trim(stripslashes($res->redirect_grouplist));
		$tab['clause'] = $sec->process_security_where_clause($tab['clause'], $user_id);
		$tab['clause'] = str_replace('where', '',$tab['clause'] );

		return $tab;

	}

	/**
	* Returns in an array all the data of a basket for a user (checks if the basket is a redirected one and then if already a virtual one)
	*
	* @param  $basket_id string Basket identifier
	* @param  $user_id string User identifier
	*/
	public function get_abs_baskets_data($basket_id, $user_id, $system_id)
	{
		$tab = array();
		$this->connect();
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		$sec = new security();

		$this->query("select basket_id, coll_id, basket_name, basket_desc, basket_clause from ".$_SESSION['tablename']['bask_baskets']." where basket_id = '".$basket_id."' and enabled = 'Y'");

		$res = $this->fetch_object();
		$tab['id'] = $res->basket_id;
		$tab['coll_id'] = $res->coll_id;
		$tab['table'] = $sec->retrieve_table_from_coll($tab['coll_id']);
		$tab['view'] = $sec->retrieve_view_from_coll_id($tab['coll_id']);
		$tab['is_generic'] = 'NO';

		$tab['desc'] = $res->basket_desc;
		$tab['name'] = $res->basket_name;
		$tab['clause'] = $res->basket_clause;

		$this->query("select user_abs, is_virtual, basket_owner from ".$_SESSION['tablename']['bask_users_abs']." where basket_id = '".$basket_id."' and new_user = '".$user_id."' and system_id = ".$system_id);

		$abs_basket = true;
		$res = $this->fetch_object();
		$is_virtual = $res->is_virtual;
		$basket_owner = $res->basket_owner;
		$user_abs = $res->user_abs;

		if(empty($basket_owner))
		{
			$basket_owner = $user_abs;
		}
		if($is_virtual == 'N')
		{
			$tmp_user = $user_abs;
			$this->query("select firstname, lastname from ".$_SESSION['tablename']['users']." where user_id ='".$user_abs."'");
			$res = $this->fetch_object();
			$name_user_abs = $res->firstname.' '.$res->lastname;
			$tab['name'] .= " (".$name_user_abs.")";
			$tab['desc'] .= " (".$name_user_abs.")";
			$tab['id'] .= "_".$user_abs;
		}
		else
		{
			$tmp_user = $basket_owner;  /// TO DO : test if basket_owner empty
			$this->query("select firstname, lastname from ".$_SESSION['tablename']['users']." where user_id ='".$basket_owner."'");
			$res = $this->fetch_object();
			$name_basket_owner = $res->firstname.' '.$res->lastname;
			$tab['name'] .= " (".$name_basket_owner.")";
			$tab['desc'] .= " (".$name_basket_owner.")";
			$tab['id'] .= "_".$basket_owner;
		}
		/// TO DO : Test if tmp_user is empty
		if($tmp_user <> $_SESSION['user']['UserId'])
		{
			$this->query("select group_id from ".$_SESSION['tablename']['usergroup_content']." where primary_group = 'Y' and user_id = '".$tmp_user."'");
			$res = $this->fetch_object();
			$primary_group = $res->group_id;
		}
		else
		{
			$primary_group = $_SESSION['user']['primarygroup'];
		}
		$this->query("select  sequence, can_redirect, can_delete, can_insert, result_page, redirect_basketlist, redirect_grouplist from ".$_SESSION['tablename']['bask_groupbasket']." where group_id = '".$primary_group."' and basket_id = '".$basket_id."' ");
		$res = $this->fetch_object();

		$basket_id_page = $res->result_page;
		$tab['id_page'] = $basket_id_page;
		// Retrieves the basket url (frame and no_frame modes)
		$basket_path_page_no_frame = $this->retrieve_path_page($basket_id_page,'no_frame');
		$basket_path_page_frame = $this->retrieve_path_page($basket_id_page,'frame');
		$basket_path_page_include = $this->retrieve_path_page($basket_id_page,'include');
		$tab['page_no_frame'] = $basket_path_page_no_frame;
		$tab['page_frame'] = $basket_path_page_frame;
		$tab['page_include'] = $basket_path_page_include;
		// Gets actions of the basket
		$tab['default_action'] = $this->get_default_action($basket_id,$primary_group );
		$tab['actions'] = $this->get_actions_from_groupbaket($basket_id,$primary_group );

		$tab['is_virtual'] = $is_virtual;
		$tab['basket_owner'] = $basket_owner;
		$tab['redirect_services'] = trim(stripslashes($res->redirect_basketlist));
		$tab['redirect_users'] = trim(stripslashes($res->redirect_grouplist));
		$tab['abs_basket'] = $abs_basket;

		$tab['clause'] = $sec->process_security_where_clause($tab['clause'], $basket_owner);
		$tab['clause'] = str_replace('where', '',$tab['clause'] );

		return $tab;

	}

	/**
	* Returns the number of baskets of a given user (Including the redirected baskets)
	*
	* @param  $user_id string Owner of the baskets (identifier)
	*/
	public function get_numbers_of_baskets($user_id)
	{
		if($user_id == $_SESSION['user']['UserId'])
		{
			return count($_SESSION['user']['baskets']);
		}
		else
		{
			$this->connect();
			$this->query("SELECT gb.basket_id  FROM ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['bask_groupbasket']." gb WHERE uc.user_id = '".$user_id."' AND uc.primary_group = 'Y' AND uc.group_id = gb.group_id");
			$nb = $this->nb_result();
			$this->query("select basket_id from ".$_SESSION['tablename']['bask_users_abs']." mu where new_user = '".$user_id."'");

			return $nb+$this->nb_result();
		}
	}

	/**
	* Returns in a string the form to redirect baskets to users during leaving
	*
	* @param  $result array Array of the baskets to redirect
	* @param  $nb_total integer Number of baskets to redirect
	* @param  $user_id string Owner of the baskets (identifier)
	* @param  $used_css string CSS to use in displaying
	*/
	public function redirect_my_baskets_list($result, $nb_total, $user_id, $used_css = 'listing spec')
	{

		$nb_show = $_SESSION['config']['nblinetoshow'];
		if($nb_total > 0)
		{
			ob_start();
			?><h2><?php echo _REDIRECT_MY_BASKETS;?></h2><div align="center"><form name="redirect_my_baskets_to" id="redirect_my_baskets_to" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=basket&page=manage_redirect_my_basket"><input type="hidden" name="display" id="display" value="true" /><input type="hidden" name="page" id="page" value="manage_redirect_my_basket" /><input type="hidden" name="module" id="module" value="basket" /><input type="hidden" name="baskets_owner" id="baskets_owner" value="<?php echo $user_id;?>" /><table border="0" cellspacing="0" class="<?php echo $used_css;?>"><thead><tr><th><?php echo _ID; ?></th><th><?php echo _NAME; ?></th><th><?php echo _REDIRECT_TO; ?></th></tr></thead><tbody><?php
			$color = "";
			for($theline = 0; $theline < $nb_total ; $theline++)
			{
				if($color == ' class="col"')
				{
					$color = '';
				}
				else
				{
					$color = ' class="col"';
				}
				?><tr <?php echo $color; ?>><td> <?php echo $result[$theline]['id'];  ?></td><td><?php echo $result[$theline]['name'];  ?></td><td><input type="hidden" name="basket_<?php echo $theline;?>" id="basket_<?php echo $theline;?>" value="<?php echo $result[$theline]['id'];?>" /><input type="hidden" name="virtual_<?php echo $theline;?>" id="virtual_<?php echo $theline;?>" value="<?php if( $result[$theline]['abs_basket'] == true){ echo 'Y';}else{ echo 'N';} ?>"/><input type="hidden" name="originalowner_<?php echo $theline;?>" id="originalowner_<?php echo $theline;?>" value="<?php echo $result[$theline]['basket_owner'];  ?>" /><input type="text" id="user_<?php echo $theline;?>" name="user_<?php echo $theline;?>" class="users_to redirect" /><span id="indicator_<?php echo $theline;?>" style="display: none"><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=loading.gif" alt="Working..." /></span><div id="options_<?php echo $theline;?>" class="autocomplete"></div></td></tr><?php
			}
			?></tbody></table><p class="buttons"><input type="button" onclick="test_form();" name="valid" value="<?php echo _VALIDATE;?>" class="button"/> <input type="button" name="cancel" value="<?php echo _CANCEL;?>" onclick="destroyModal('modal_redirect');" class="button"/></p></form></div><?php

			 $content = ob_get_clean();
		}
		else
		{
			ob_start();
			?><h2><?php echo _ABS_MODE;?></h2><div align="center"><form name="abs_mode" id="abs_mode" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=basket&page=manage_abs_mode"><input type="hidden" name="display" value="true"/><input type="hidden" name="module" value="basket"/><input type="hidden" name="page" value="manage_abs_mode"/><input type="hidden" name="user_id" value="<?php echo $user_id ;?>"/><p><?php echo _REALLY_ABS_MODE;?></p><input type="submit" name="submit" value="<?php echo _VALIDATE;?>" class="button" /> <input type="button" name="cancel" value="<?php echo _CANCEL;?>" onclick="destroyModal('modal_redirect');" class="button" /></form></div><?php
			$content = ob_get_clean();
		}
		 return $content;
	}

	/**
	* Cancel leaving for a user
	*
	* @param  $user_id string user identifier
	*
	*/
	public function cancel_abs($user_id)
	{
		$this->connect();
		$db = new dbquery();
		$db->connect();
		$this->query("delete from ".$_SESSION['tablename']['bask_users_abs']." where is_virtual = 'Y' and basket_owner = '".$this->protect_string_db($user_id)."'");
		//Then we search all the virtual baskets assigned to the user
		$this->query("select basket_owner, basket_id from ".$_SESSION['tablename']['bask_users_abs']." where is_virtual='Y' and user_abs = '".$this->protect_string_db($user_id)."'" );
		// and delete this baskets if they were reassigned to someone else
		$i=0;
		while($res = $this->fetch_object())
		{
			$db->query("delete from ".$_SESSION['tablename']['bask_users_abs']." where is_virtual ='Y' and basket_id = '".$this->protect_string_db($res->basket_id)."' and basket_owner = '".$this->protect_string_db($res->basket_owner)."'");
			//$this->show();
			$i++;
		}
		// then we delete all baskets where the user was the missing user
		$this->query("DELETE  from ".$_SESSION['tablename']['bask_users_abs']." WHERE user_abs='".$this->protect_string_db($user_id)."'");
		$this->query("update ".$_SESSION['tablename']['users']." set status = 'OK' where user_id = '".$user_id."'");
	}

	public function check_reserved_time($res_id, $coll_id)
	{
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$table = $sec->retrieve_table_from_coll($coll_id);
		$db = new dbquery();
		if(!empty($table) && !empty($res_id))
		{
			$db->connect();
			$db->query("select video_time, video_user, destination from ".$table." where res_id = ".$res_id);
			$res = $db->fetch_object();
			$timestamp = $res->video_time;
			$video_user = $res-> video_user;
			$dest = $res->destination;
			//if($status == 'RSV' && ($timestamp  - mktime( date("H") , date("i")  , date("s") , date("m") , date("d") , date("Y")) < 0 ))
			if(trim($video_user)<> '' && ($timestamp  - mktime( date("H") , date("i")  , date("s") , date("m") , date("d") , date("Y")) < 0 ))
			{
				$db->query("update ".$table." set video_user = '' where res_id = ".$res_id);
				return false;
			}
			// Reserved time not yet expired
  			else
  			{
				if($video_user == $_SESSION['user']['UserId'] || empty($video_user))
				{
  					return true;
				}
				else
				{
					return false;
				}
  			}
		}
		else
		{
			return false;
		}
	}

	public function reserve_doc( $user_id, $res_id, $coll_id, $delay = 60)
	{
		if(empty($user_id) || empty($res_id) || empty($coll_id))
		{
			return false;
		}
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$table = $sec->retrieve_table_from_coll($coll_id);
		if(empty($table))
		{
			return false;
		}
		$this->connect();
		$this->query("select video_user, video_time from ".$table." where res_id = ".$res_id);

		if($this->nb_result() == 0)
		{
			return false;
		}
		$res = $this->fetch_object();
		$user = $res->video_user;

		if($delay > 1)
		{
			$delay_str = "+".$delay." minutes ";
		}
		else if( $delay == 1)
		{
			$delay_str = "+1 minute ";
		}
		else
		{
			return false;
		}

		if($user <> $user_id && !empty($user))
		{
			return false;
		}
		else
		{
			$this->query("update ".$table." set video_time = ".strtotime($delay_str).", video_user = '".$user_id."' where res_id = ".$res_id);
			return true;
		}
	}
}
?>
