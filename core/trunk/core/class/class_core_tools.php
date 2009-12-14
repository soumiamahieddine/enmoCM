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
* @defgroup core Framework core
*/

/**
* @brief   Contains all the functions to load core and modules
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Contains all the functions to load core and modules
*
* <ul>
* <li>Loads core tables into session</li>
* <li>Loads modules settings into session</li>
* <li>Builds the application menu</li>
*  <li>Management and building the framework</li>
*  <li>Modules services loading</li>
*  <li>Execution of the module services </li>
*</ul>
*
* @ingroup core
*/
class core_tools extends functions
{
	/**
	* Load Maarch core configuration into sessions vars from the core/xml/config.xml file
	*/
	public function build_core_config($pathtoxmlcore)
	{
		$xmlconfig = simplexml_load_file($pathtoxmlcore);

		// Loads  core tables into session ($_SESSION['tablename'] array)
		$TABLENAME = $xmlconfig->TABLENAME ;
		$_SESSION['tablename']['actions'] = (string) $TABLENAME->actions;
		$_SESSION['tablename']['authors'] = (string) $TABLENAME->authors;
		$_SESSION['tablename']['docservers'] = (string) $TABLENAME->docservers;
		$_SESSION['tablename']['doctypes'] = (string) $TABLENAME->doctypes;
		$_SESSION['tablename']['ext_docserver'] = (string) $TABLENAME->extdocserver;
		$_SESSION['tablename']['fulltext'] = (string) $TABLENAME->fulltext;
		$_SESSION['tablename']['groupsecurity'] = (string) $TABLENAME->groupsecurity;
		$_SESSION['tablename']['history'] = (string) $TABLENAME->history;
		$_SESSION['tablename']['history_batch'] = (string) $TABLENAME->history_batch;
		$_SESSION['tablename']['param'] = (string) $TABLENAME->param;
		$_SESSION['tablename']['resgroups'] = (string) $TABLENAME->resgroups;
		$_SESSION['tablename']['resgroup_content'] = (string) $TABLENAME->resgroup_content;
		$_SESSION['tablename']['security'] = (string) $TABLENAME->security;
		$_SESSION['tablename']['status'] = (string) $TABLENAME->status;
		$_SESSION['tablename']['usergroups'] = (string) $TABLENAME->usergroups;
		$_SESSION['tablename']['usergroup_content'] = (string) $TABLENAME->usergroupcontent;
		$_SESSION['tablename']['usergroup_services'] = (string) $TABLENAME->usergroups_services;
		$_SESSION['tablename']['users'] = (string) $TABLENAME->users;
	}

	/**
	* Load Maarch modules configuration into sessions vars from modules/module_name/xml/config.xml files
	*
	* @param $modules array  Enabled modules of the application
	*/
	public function load_modules_config($modules, $mode_batch=false)
	{
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
		// Browses enabled modules
		for($i=0;$i<count($modules);$i++)
		{
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
			{
				$path_config = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
			}
			else
			{
					$path_config = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
			}
			
			$path_lang = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
			// Reads the config.xml file of the current module
			$xmlconfig = simplexml_load_file($path_config);
			// Loads into $_SESSION['modules_loaded'] module's informations
			foreach($xmlconfig->CONFIG as $CONFIG)
			{
				$tmp = (string) $CONFIG->name;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['name'] = $tmp2;
				}
				else
				{
					$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['name'] = $tmp;
				}
				$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['path'] = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR;
				$tmp = (string) $CONFIG->comment;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['comment'] = $tmp2;
				}
				else
				{
					$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['comment'] = $tmp;
				}
				$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['fileprefix'] = (string) $CONFIG->fileprefix;
				$_SESSION['modules_loaded'][$modules[$i]['moduleid']]['loaded'] = (string) $CONFIG->loaded;
			}
			$path_module_tools = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php";
			require_once($path_module_tools);
			$modules_tools = new $modules[$i]['moduleid'];
			//Loads the tables of the module into session
			$modules_tools->build_modules_tables();
			//Loads log keywords of the module
			foreach($xmlconfig->KEYWORDS as $keyword)
			{
				$tmp = (string) $keyword->label;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$tmp = $tmp2;
				}
				$id = (string) $keyword->id;
				if(!$this->is_var_in_history_keywords_tab($id))
				{
					array_push($_SESSION['history_keywords'], array('id' =>$id,'label' =>$tmp));
				}
			}
		}
		if(!$mode_batch)
		{
			
			//Loads logs keywords of the actions
			require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_db.php");
			$db = new dbquery();
			$db->connect();
			$db->query("select id, label_action from ".$_SESSION['tablename']['actions']." where enabled = 'Y' and history = 'Y'");
			while($res = $db->fetch_object())
			{
				array_push($_SESSION['history_keywords'], array('id' =>'ACTION#'.$res->id,'label' => $this->show_string($res->label_action)));
			}
		}
	}

	/**
	* Check if the log keyword is known in the apps
	*
	* @param $id  string Log keyword to check
	* @return bool True if the keyword is found, False otherwise
	*/
	public function is_var_in_history_keywords_tab($id)
	{
		$found = false;
		for($i=0;$i<count($_SESSION['history_keywords']);$i++)
		{
			if($_SESSION['history_keywords'][$i]['id'] == $id)
			{
				$found = $_SESSION['history_keywords'][$i]['label'];
				break;
			}
		}
		return $found;
	}

	/**
	* Loads the modules specific vars into session
	*
	* @param $modules Enabled modules of the application
	*/
	public function load_var_session($modules)
	{
		for($i=0;$i<count($modules);$i++)
		{
			$path_module_tools = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php";
			require_once($path_module_tools);
			$modules_tools = new $modules[$i]['moduleid'];
			$modules_tools->load_module_var_session();
		}
	}

	/**
	* Loads language variables into session
	*/
	public function load_lang()
	{
		//Overload of language files with custom langage file
		if (isset($_SESSION['custom_override_id']) && !empty($_SESSION['custom_override_id']))
			$this->load_lang_custom_override($_SESSION['custom_override_id']);
		
		if(isset($_SESSION['config']['lang']) && file_exists($_SESSION['config']['corepath'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php'))
		{
			include($_SESSION['config']['corepath'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
		}
		else
		{
			$_SESSION['error'] = "Language file missing...<br/>";
		}
		$this->load_lang_modules($_SESSION['modules']);
		
		
	}

	/**
	* Loads language variables of each module
	*
	* @param  $modules array Enabled modules of the application
	*/
	private function load_lang_modules($modules)
	{
		for($i=0;$i<count($modules);$i++)
		{
			if(isset($_SESSION['config']['lang']) && file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php'))
			{
				$filename = ('modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php'); 
				include($filename);
			}
			else
			{
				$_SESSION['error'] .= "Language file missing for module : ".$modules[$i]['moduleid']."<br/>";
			}
		}
	}
	
	private function load_lang_custom_override($custom_id)
	{
		$pathname = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$custom_id.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		
		if (file_exists($pathname)) {
			include($pathname);
		}
		
	}

	/**
	* Loads menu items of each module and the application into session from menu.xml files
	*
	* @param $modules array Enabled modules of the application
	*/
	public function load_menu($modules)
	{
		// Browses the enabled modules array
		$k=0;
		for($i=0;$i<count($modules);$i++)
		{
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."menu.xml"))
			{
				$path_menu = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."menu.xml";
			}
			else
			{
				$path_menu = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."menu.xml";
			}
			
			// Reads the module/module_name/xml/menu.xml file  and loads into session
			$path_lang = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
			if(file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."menu.xml") || file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."menu.xml"))
			{
				$xmlconfig = simplexml_load_file($path_menu);
				foreach($xmlconfig->MENU as $MENU)
				{
					$_SESSION['menu'][$k]['id'] = (string) $MENU->id;
					if(isset($_SESSION['user']['services'][$_SESSION['menu'][$k]['id'] ]) && $_SESSION['user']['services'][$_SESSION['menu'][$k]['id'] ] == true)
					{
						$tmp = (string) $MENU->libconst;
						$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
						if($tmp2 <> false)
						{
							$_SESSION['menu'][$k]['libconst'] = $tmp2;
						}
						else
						{
							$_SESSION['menu'][$k]['libconst'] = $tmp;
						}
						$_SESSION['menu'][$k]['url'] = $_SESSION['config']['businessappurl'].(string) $MENU->url;
						if(trim((string) $MENU->target) <> "")
						{
							$tmp = preg_replace('/\/core\/$/', '/', $_SESSION['urltocore']);
							$_SESSION['menu'][$k]['url'] = $tmp. (string) $MENU->url;
							$_SESSION['menu'][$k]['target'] = (string) $MENU->target;
						}
						$_SESSION['menu'][$k]['style'] = (string) $MENU->style;
						$_SESSION['menu'][$k]['show'] = true;
					}
					else
					{
						$_SESSION['menu'][$k]['libconst'] ='';
						$_SESSION['menu'][$k]['url'] ='';
						$_SESSION['menu'][$k]['style'] = '';
						$_SESSION['menu'][$k]['show'] = false;
					}
					$k++;
				}
			}
		}
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'menu.xml'))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'menu.xml';
		}
		else
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'menu.xml';
		}
		// Reads the apps/apps_name/xml/menu.xml file  and loads into session
		$xmlconfig = simplexml_load_file($path);
		$path_lang ='apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		foreach($xmlconfig->MENU as $MENU2)
		{
			$_SESSION['menu'][$k]['id'] = (string) $MENU2->id;
			if($_SESSION['user']['services'][$_SESSION['menu'][$k]['id']] == true)  // Menu Identifier must be equal to the Service identifier
			{
				$tmp = (string) $MENU2->libconst;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$_SESSION['menu'][$k]['libconst'] = $tmp2;
				}
				else
				{
					$_SESSION['menu'][$k]['libconst'] = $tmp;
				}
				$_SESSION['menu'][$k]['url'] = $_SESSION['config']['businessappurl'].(string) $MENU2->url;
				if(trim((string) $MENU2->target) <> "")
				{
					$tmp = preg_replace('/\/core\//$', '/', $_SESSION['urltocore']);
					$_SESSION['menu'][$k]['url'] = $tmp. (string) $MENU->url;
					$_SESSION['menu'][$k]['target'] = (string) $MENU2->target;
				}
				$_SESSION['menu'][$k]['style'] = (string) $MENU2->style;
				$_SESSION['menu'][$k]['show'] = true;
			}
			else
			{
				$_SESSION['menu'][$k]['libconst'] ='';
				$_SESSION['menu'][$k]['url'] ='';
				$_SESSION['menu'][$k]['style'] = '';
				$_SESSION['menu'][$k]['show'] = false;
			}
			$k++;
		}
	}

	/**
	* Builds the application menu from the session var menu
	*
	* @param  $menu array Enabled menu items
	*/
	public function build_menu($menu)
	{
		// Browses the menu items
		for($i=0;$i<count($menu);$i++)
		{
			if($menu[$i]['show'] == true)
			{
				$tmp = preg_replace('/&module/', '&amp;module', $menu[$i]['url']);
				if(preg_match('/php$/', $tmp))
				{
					$tmp .= "?reinit=true";
				}
				else
				{
					$tmp .= "&amp;reinit=true";
				}
				?>
				<li id="<?php  echo $menu[$i]['style'];?>" onmouseover="this.className='on';" onmouseout="this.className='';"><a href="#" onclick="window.open('<?php  echo $tmp;?>', '<?php  if($menu[$i]['target'] <> ''){echo $menu[$i]['target'];}else{echo '_self';}?>');"><span><span><?php  echo trim($menu[$i]['libconst']);?></span></span></a></li>
				<?php
			}
		}

		// Menu items always displayed
		echo '<li id="account" onmouseover="this.className=\'on\';" onmouseout="this.className=\'\';">
		<a href="'.$_SESSION['config']['businessappurl'].'index.php?page=modify_user&admin=users&reinit=true"><span><span>'._MY_INFO.'</span></span></a></li>';
		echo '<li id="logout" onmouseover="this.className=\'on\';" onmouseout="this.className=\'\';">
		<a href="'.$_SESSION['config']['businessappurl'].'index.php?display=true&page=logout&coreurl='.$_SESSION['config']['coreurl'].'"><span><span>'._LOGOUT.'</span></span></a></li>';
	}

	/**
	* Loads application services into session
	*/
	public function load_app_services()
	{
		// Reads the application config.xml file
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'services.xml'))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'services.xml';
		}
		else
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'services.xml';
		}
		$xmlconfig = simplexml_load_file($path);
		$k = 0;
		$m = 0;
		// Browses the services in that file  and loads $_SESSION['app_services']
		foreach($xmlconfig->SERVICE as $SERVICE)
		{
			$_SESSION['app_services'][$k]['id'] = (string) $SERVICE->id;
			$tmp = (string) $SERVICE->name;
			$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
			if($tmp2 <> false)
			{
				$_SESSION['app_services'][$k]['name'] = $tmp2;
			}
			else
			{
				$_SESSION['app_services'][$k]['name'] = $tmp;
			}

			$tmp = (string) $SERVICE->comment;
			$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
			if($tmp2 <> false)
			{
				$_SESSION['app_services'][$k]['comment'] = $tmp2;
			}
			else
			{
				$_SESSION['app_services'][$k]['comment'] = $tmp;
			}
			if(isset($SERVICE->servicepage))
			{
				$_SESSION['app_services'][$k]['servicepage'] = (string) $SERVICE->servicepage;
				$_SESSION['app_services'][$k]['servicepage'] = preg_replace('/&admin/', '&amp;admin', $_SESSION['app_services'][$k]['servicepage']);
				$_SESSION['app_services'][$k]['servicepage'] = preg_replace('/&module/', '&amp;module', $_SESSION['app_services'][$k]['servicepage']);
			}
			$_SESSION['app_services'][$k]['servicetype'] = (string) $SERVICE->servicetype;

			if(isset($SERVICE->style))
			{
				$_SESSION['app_services'][$k]['style'] = (string) $SERVICE->style;
			}

			$system_service =  (string) $SERVICE->system_service;
			if($system_service == "false")
			{
				$_SESSION['app_services'][$k]['system_service'] = false;
			}
			else
			{
				$_SESSION['app_services'][$k]['system_service'] = true;
			}
			$_SESSION['app_services'][$k]['enabled'] = (string) $SERVICE->enabled;
			$l=0;
			foreach($SERVICE->WHEREAMIUSED as $WHEREAMIUSED)
			{
				$_SESSION['app_services'][$k]['whereamiused'][$l]['page'] = (string) $WHEREAMIUSED->page;
				$_SESSION['app_services'][$k]['whereamiused'][$l]['nature'] = (string) $WHEREAMIUSED->nature;
				if(isset($WHEREAMIUSED->button_label))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['button_label'] = (string) $WHEREAMIUSED->button_label;
				}
				if(isset($WHEREAMIUSED->tab_label))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['tab_label'] = $this->retrieve_constant_lang((string) $WHEREAMIUSED->tab_label, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');

				}
				if(isset($WHEREAMIUSED->tab_order))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['tab_order'] = (string) $WHEREAMIUSED->tab_order;
				}
				if(isset($WHEREAMIUSED->width))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['width'] = (string) $WHEREAMIUSED->width;
				}

				if(isset($WHEREAMIUSED->height))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['height'] = (string) $WHEREAMIUSED->height;
				}
				if(isset($WHEREAMIUSED->scrolling))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['scrolling'] = (string) $WHEREAMIUSED->scrolling;
				}
				if(isset($WHEREAMIUSED->border))
				{
					$_SESSION['app_services'][$k]['whereamiused'][$l]['border'] = (string) $WHEREAMIUSED->border;
				}
				$l++;
			}
			$m = 0;
			// Loads preprocess and postprocess
			foreach($SERVICE->PROCESSINBACKGROUND as $PROCESSINBACKGROUND)
			{
				$_SESSION['app_services'][$k]['processinbackground'][$m]['page'] = (string) $PROCESSINBACKGROUND->page;
				if((string) $PROCESSINBACKGROUND->preprocess <> "")
				{
					$_SESSION['app_services'][$k]['processinbackground'][$m]['preprocess'] = (string) $PROCESSINBACKGROUND->preprocess;
				}
				if((string) $PROCESSINBACKGROUND->postprocess <> "")
				{
					$_SESSION['app_services'][$k]['processinbackground'][$m]['postprocess'] = (string) $PROCESSINBACKGROUND->postprocess;
				}
				$_SESSION['app_services'][$k]['processinbackground'][$m]['processorder'] = (string) $PROCESSINBACKGROUND->processorder;
				$m++;
			}
			$k++;
		}
	}

	/**
	* Loads the services of each module into session
	*
	* @param $modules array Enabled modules of the application
	*/
	public function load_modules_services($modules)
	{
		// Browses the enabled modules array
		for($i=0;$i<count($modules);$i++)
		{
			// Reads the module config.xml file
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."services.xml"))
			{
				$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."services.xml";
			}
			else
			{
				$path = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."services.xml";
			}
			$xmlconfig = simplexml_load_file($path);
			$k = 0;
			$m = 0;
			foreach($xmlconfig->SERVICE as $SERVICE)
			{
				if((string) $SERVICE->enabled == "true")
				{
					$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['id'] = (string) $SERVICE->id;
					$tmp = (string) $SERVICE->name;
					$tmp2 = $this->retrieve_constant_lang($tmp, 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php');
					if($tmp2<> false)
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['name']=$tmp2;
					}
					else
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['name']=$tmp;
					}
					$tmp = (string) $SERVICE->comment;
					$filename = 'modules'.DIRECTORY_SEPARATOR.$modules[$i]['moduleid'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
					
					$tmp2 = $this->retrieve_constant_lang($tmp, $filename);
					if($tmp2<> false)
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['comment']=$tmp2;
					}
					else
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['comment']=$tmp;
					}
					if(isset($SERVICE->servicepage))
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['servicepage'] = (string) $SERVICE->servicepage;
					}
					$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['servicetype'] = (string) $SERVICE->servicetype;

					if(isset($SERVICE->style))
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['style'] = (string) $SERVICE->style;
					}
					$system_service =  (string) $SERVICE->system_service;
					if($system_service == "false")
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['system_service'] = false;
					}
					else
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['system_service'] = true;
					}
					$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['enabled'] = (string) $SERVICE->enabled;

					$l=0;
					foreach($SERVICE->WHEREAMIUSED as $WHEREAMIUSED)
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['page'] = (string) $WHEREAMIUSED->page;
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['nature'] = (string) $WHEREAMIUSED->nature;
						if(isset($WHEREAMIUSED->button_label))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['button_label'] = $this->retrieve_constant_lang((string) $WHEREAMIUSED->button_label, $_SESSION['modules_loaded'][$modules[$i]['moduleid']]['path'].'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
						}
						if(isset($WHEREAMIUSED->tab_label))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['tab_label'] = $this->retrieve_constant_lang((string) $WHEREAMIUSED->tab_label, $_SESSION['modules_loaded'][$modules[$i]['moduleid']]['path'].'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
						}
						if(isset($WHEREAMIUSED->tab_order))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['tab_order'] = (string) $WHEREAMIUSED->tab_order;
						}
						if(isset($WHEREAMIUSED->width))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['width'] = (string) $WHEREAMIUSED->width;
						}
						if(isset($WHEREAMIUSED->height))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['height'] = (string) $WHEREAMIUSED->height;
						}
						if(isset($WHEREAMIUSED->scrolling))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['scrolling'] = (string) $WHEREAMIUSED->scrolling;
						}
						if(isset($WHEREAMIUSED->border))
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['whereamiused'][$l]['border'] = (string) $WHEREAMIUSED->border;
						}
						$l++;
					}
					$m=0;
					foreach($SERVICE->PROCESSINBACKGROUND as $PROCESSINBACKGROUND)
					{
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['processinbackground'][$m]['page'] = (string) $PROCESSINBACKGROUND->page;
						if((string) $PROCESSINBACKGROUND->preprocess <> "")
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['processinbackground'][$m]['preprocess'] = (string) $PROCESSINBACKGROUND->preprocess;
						}
						if((string) $PROCESSINBACKGROUND->postprocess <> "")
						{
							$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['processinbackground'][$m]['postprocess'] = (string) $PROCESSINBACKGROUND->postprocess;
						}
						$_SESSION['modules_services'][$modules[$i]['moduleid']][$k]['processinbackground'][$m]['processorder'] = (string) $PROCESSINBACKGROUND->processorder;
						$m++;
					}
					$k++;
				}
			}
		}
	}

	/**
	* Executes the module' s services in the page
	*
	* @param $modules_services  array List of the module's services
	* @param $whereami  string Page where to execute the service
	* @param $servicenature string  Nature of the service (by default, the function takes all the services natures)
	* @param  $id_service string Identifier of one specific service (empty by default)
	* @param  $id_module string Identifier of one specific module (empty by default)
	*/
	public function execute_modules_services($modules_services, $whereami, $servicenature = "all", $id_service = '', $id_module = '')
	{
		$executed_services = array();
		if(!empty($id_service) && !empty($id_module))
		{
			for($i=0;$i < count($modules_services[$id_module]);$i++)
			{
				if($modules_services[$id_module][$i]['id'] == $id_service)
				{
					for($k=0; $k < count($modules_services[$id_module][$i]['whereamiused']);$k++)
					{
				 		if($modules_services[$id_module][$i]['whereamiused'][$k]['page'] == $whereami)
						{
							if($modules_services[$id_module][$i]['whereamiused'][$k]['nature'] == "frame" && $_SESSION['user']['services'][$modules_services[$id_module][$i]['id']] && !in_array($modules_services[$id_module][$i]['id'], $executed_services))
							{
								array_push($executed_services,$modules_services[$id_module][$i]['id']);
								
								?>
								<br />
								
								<iframe src='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$id_module."&page=".$modules_services[$id_module][$i]['servicepage'];?>' name="<?php  echo $modules_services[$id_module][$i]['id'];?>" id="<?php  echo $modules_services[$id_module][$i]['id'];?>" width='<?php  echo $modules_services[$id_module][$i]['whereamiused'][$k]['width'];?>' height='<?php  echo $modules_services[$id_module][$i]['whereamiused'][$k]['height'];?>' frameborder='<?php  echo $modules_services[$id_module][$i]['whereamiused'][$k]['border'];?>' scrolling='<?php  echo $modules_services[$id_module][$i]['whereamiused'][$k]['scrolling'];?>'></iframe><br /><br />
								<?php
								break;
							}
							elseif($modules_services[$id_module][$i]['whereamiused'][$k]['nature'] == "popup" && $_SESSION['user']['services'][$modules_services[$id_module][$i]['id']] && !in_array($modules_services[$id_module][$i]['id'], $executed_services))
							{
								array_push($executed_services,$modules_services[$id_module][$i]['id']);
								echo $modules_services[$id_module][$i]['name'];
								?>
								<br />
								<a href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$id_module."&page=".$modules_services[$id_module][$i]['servicepage'];?>' target='_blank'><?php  echo _ACCESS_TO_SERVICE;?></a><br /><br />
								<?php
								break;
							}
							elseif($modules_services[$id_module][$i]['whereamiused'][$k]['nature'] == "button" && $_SESSION['user']['services'][$modules_services[$id_module][$i]['id']] && !in_array($modules_services[$id_module][$i]['id'], $executed_services))
							{
								array_push($executed_services,$modules_services[$id_module][$i]['id']);
								$tmp = $modules_services[$id_module][$i]['whereamiused'][$k]['button_label'];
								$tmp2 = $this->retrieve_constant_lang($modules_services[$id_module][$i]['whereamiused'][$k]['button_label'], $_SESSION['modules_loaded'][$id_module]['path'].'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
								if($tmp2 <> false)
								{
									$tmp = $tmp2;
								}
								?>
								<input type="button" name="<?php  echo $modules_services[$id_module][$i]['id'];?>" value="<?php  echo $tmp;?>" onclick="window.open('<?php  echo   $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$id_module."&page=".$modules_services[$id_module][$i]['servicepage'];?>', '<?php  echo $modules_services[$id_module][$i]['id'];?>','width=<?php  echo $modules_services[$id_module][$i]['whereamiused'][$k]['width'];?>,height=<?php  echo $modules_services[$id_module][$i]['whereamiused'][$k]['height'];?>,scrollbars=yes,resizable=yes' );" class="button" /><br/>
								<?php
								break;
							}
							elseif($modules_services[$id_module][$i]['whereamiused'][$k]['nature'] == "include" && $_SESSION['user']['services'][$modules_services[$id_module][$i]['id']] && !in_array($modules_services[$id_module][$i]['id'], $executed_services))
							{
								array_push($executed_services,$modules_services[$id_module][$i]['id']);
								include('modules'.DIRECTORY_SEPARATOR.$id_module.DIRECTORY_SEPARATOR.$modules_services[$id_module][$i]['servicepage']);
								break;
							}
						}
					}
				}
			}
		}
		else
		{
			$tab_view = array();
			foreach(array_keys($modules_services) as $value)
			{
				for($i=0;$i<count($modules_services[$value]);$i++)
				{
					if(isset($modules_services[$value][$i]['whereamiused']))
					{
						for($k=0;$k<count($modules_services[$value][$i]['whereamiused']);$k++)
						{
							if($modules_services[$value][$i]['whereamiused'][$k]['page'] == $whereami  )
							{
								if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "frame" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']] && ($servicenature == "all" || $servicenature == "frame") && !in_array($modules_services[$value][$i]['id'], $executed_services))
								{
									array_push($executed_services,$modules_services[$value][$i]['id']);
									?>
									<br />
									<iframe src='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$id_module."&page=".$modules_services[$id_module][$i]['servicepage'];?>' name="<?php  echo $modules_services[$value][$i]['id'];?>" id="<?php  echo $modules_services[$value][$i]['id'];?>" width='<?php  echo $modules_services[$value][$i]['whereamiused'][$k]['width'];?>' height='<?php  echo $modules_services[$value][$i]['whereamiused'][$k]['height'];?>' frameborder='<?php  echo $modules_services[$value][$i]['whereamiused'][$k]['border'];?>' scrolling='<?php  echo $modules_services[$value][$i]['whereamiused'][$k]['scrolling'];?>'></iframe><br /><br />
									<?php
								}
								elseif($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "tab" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']] && ($servicenature == "tab") && !in_array($modules_services[$value][$i]['id'], $executed_services))
								{
									array_push($executed_services,$modules_services[$value][$i]['id']);
									$tab_label = $modules_services[$value][$i]['whereamiused'][$k]['tab_label'];
									$tab_order = $modules_services[$value][$i]['whereamiused'][$k]['tab_order'];
									
									$frame_src = $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$value."&page=".$modules_services[$value][$i]['servicepage'];								
									//$frame_src = $_SESSION['urltomodules'].$value."/".$modules_services[$value][$i]['servicepage'];
									$tab_view[$tab_order]['tab_label'] = $this->retrieve_constant_lang($tab_label, $_SESSION['modules_loaded'][$value]['path'].'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
									$tab_view[$tab_order]['frame_src'] = $frame_src;
								}
								elseif($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "popup" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']] && ($servicenature == "all" || $servicenature == "popup") && !in_array($modules_services[$value][$i]['id'], $executed_services))
								{
									array_push($executed_services,$modules_services[$value][$i]['id']);
									echo $modules_services[$value][$i]['name'];
									?>
									<br />
									<a href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$value."&page=".$modules_services[$value][$i]['servicepage'];?>' target='_blank'><?php  echo _ACCESS_TO_SERVICE;?></a><br /><br />
									<?php
								}
								elseif($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "button" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']]&& ($servicenature == "all" || $servicenature == "button") && !in_array($modules_services[$value][$i]['id'], $executed_services))
								{
									array_push($executed_services,$modules_services[$value][$i]['id']);
									$tmp = $modules_services[$value][$i]['whereamiused'][$k]['button_label'];
									$tmp2 = $this->retrieve_constant_lang($modules_services[$value][$i]['whereamiused'][$k]['button_label'], $_SESSION['modules_loaded'][$value]['path'].'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
									if($tmp2 <> false)
									{
										$tmp = $tmp2;
									}
									?>
									<input type="button" name="<?php  echo $modules_services[$value][$i]['id'];?>" value="<?php  echo $tmp;?>" onclick="window.open('<?php  echo  $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$id_module."&page=".$modules_services[$id_module][$i]['servicepage'];?>', '<?php  echo $modules_services[$value][$i]['id'];?>','width=<?php  echo $modules_services[$value][$i]['whereamiused'][$k]['width'];?>,height=<?php  echo $modules_services[$value][$i]['whereamiused'][$k]['height'];?>,scrollbars=yes,resizable=yes' );" class="button" /><br/>
									<?php
								}
								elseif($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "include" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']] && ($servicenature == "all" || $servicenature == "include") && !in_array($modules_services[$value][$i]['id'], $executed_services))
								{
									array_push($executed_services,$modules_services[$value][$i]['id']);
									include('modules'.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR.$modules_services[$value][$i]['servicepage']);
								}
							}
						}
					}
				}
					//print_r($executed_services);
			}
		//	$this->show_array($executed_services);
			if($servicenature == "tab")
			{
				//print_r($tab_view);
				for($u=1;$u<=count($tab_view);$u++)
				{
					if($u == 1)
					{
						?>
                        <td  class="indexingtab">
                            <a href="javascript://" onclick="opentab('myframe', '<?php  echo $tab_view[$u]['frame_src'];?>');">
								<?php  echo $tab_view[$u]['tab_label'];?>
                            </a>
                            <?php
							$_SESSION['first_tab_to_open'] = $tab_view[$u]['frame_src'];
							?>
                        </td>
						<?php
					}
					else
					{
						?>
                        <td  class="indexingtab">
                            <a href="javascript://" onclick="opentab('myframe', '<?php  echo $tab_view[$u]['frame_src'];?>');">
								<?php  echo $tab_view[$u]['tab_label'];?>
                            </a>
                        </td>
						<?php
					}
				}
			}
		}
	//	$this->show_array($executed_services);
	}


	/**
	* Loads the services of 'tab' nature in the page
	*
	* @param  $modules_services array  List of the modules services
	* @param  $whereami string Page where to execute the service
	*/
	public function load_first_tab($modules_services, $whereami)
	{
		foreach(array_keys($modules_services) as $value)
		{
			for($i=0;$i<count($modules_services[$value]);$i++)
			{
				for($k=0;$k<count($modules_services[$value][$i]['whereamiused']);$k++)
				{
					if($modules_services[$value][$i]['whereamiused'][$k]['page'] == $whereami  )
					{
						if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "tab" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']])
						{
							$tab_label = $modules_services[$value][$i]['whereamiused'][$k]['tab_label'];
							$tab_order = $modules_services[$value][$i]['whereamiused'][$k]['tab_order'];
							$frame_src = $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$value."&page=".$modules_services[$value][$i]['servicepage'];
							$tab_view[$tab_order]['tab_label'] = $this->retrieve_constant_lang($tab_label, $_SESSION['modules_loaded'][$value]['path'].'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
							$tab_view[$tab_order]['frame_src'] = $frame_src;
						}
					}
				}
			}
		}
		for($u=1;$u<=count($tab_view);$u++)
		{
			if($u == 1)
			{
				$_SESSION['first_tab_to_open'] = $tab_view[$u]['frame_src'];
			}
		}
	}

	/**
	* Executes the apps services in the page
	*
	* @param  $apps_services array  List of the application services
	* @param  $whereami string Page where to execute the service
	* @param  $servicenature string Nature of the service (by default, the function takes all the services natures)
	*/
	public function execute_app_services($app_services, $whereami, $servicenature = "all")
	{
		$executed_services = array();
		for($i=0;$i<count($app_services);$i++)
		{
			if(isset($app_services[$i]['whereamiused']))
			{
				for($k=0;$k<count($app_services[$i]['whereamiused']);$k++)
				{
					if($app_services[$i]['whereamiused'][$k]['page'] == $whereami  )
					{
						if($app_services[$i]['whereamiused'][$k]['nature'] == "frame" && $_SESSION['user']['services'][$app_services[$i]['id']] && ($servicenature == "all" || $servicenature == "frame") && !in_array($app_services[$i]['id'],$executed_services ))
						{
							array_push($executed_services,$app_services[$i]['id']);
							?>
							   <iframe src='<?php  echo  $_SESSION['config']['businessappurl'].'index.php?display=true&page='.$app_services[$i]['servicepage'];?>' name="<?php  $app_services[$i]['id'];?>" id="<?php  $app_services[$i]['id'];?>" width='<?php  echo $app_services[$i]['whereamiused'][$k]['width'];?>' height='<?php  echo $app_services[$i]['whereamiused'][$k]['height'];?>' frameborder='<?php  echo $app_services[$i]['whereamiused'][$k]['border'];?>' scrolling='<?php  echo $app_services[$i]['whereamiused'][$k]['scrolling'];?>'></iframe>
							   <?php
						}
						elseif($app_services[$i]['whereamiused'][$k]['nature'] == "popup" && $_SESSION['user']['services'][$app_services[$i]['id']] && ($servicenature == "all" || $servicenature == "popup") && !in_array($app_services[$i]['id'],$executed_services))
						{
							array_push($executed_services,$app_services[$i]['id']);
							echo $app_services[$i]['name'];
							?>
							<br />
							<a href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&page='.$app_services[$i]['servicepage'];?>' target='_blank'><?php  echo _ACCESS_TO_SERVICE;?></a><br /><br />
							 <?php
						}
						elseif($app_services[$i]['whereamiused'][$k]['nature'] == "button" && $_SESSION['user']['services'][$app_services[$i]['id']]&& ($servicenature == "all" || $servicenature == "button") && !in_array($app_services[$i]['id'],$executed_services ))
						{
							array_push($executed_services,$app_services[$i]['id']);
							$tmp = $app_services[$i]['whereamiused'][$k]['button_label'];
							$tmp2 = $this->retrieve_constant_lang($app_services[$i]['whereamiused'][$k]['button_label'], 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
							if($tmp2 <> false)
							{
								$tmp = $tmp2;
							}
							?>
							<input type="button" name="<?php  echo $app_services[$i]['id'];?>" value="<?php  echo $tmp;?>" onclick="window.open('<?php  echo  $_SESSION['config']['businessappurl'].'index.php?display=true&page='.$app_services[$i]['servicepage']; ?>', '<?php  echo $app_services[$i]['id'];?>','width=<?php  echo $app_services[$i]['whereamiused'][$k]['width'];?>,height=<?php  echo $app_services[$i]['whereamiused'][$k]['height'];?>,scrollbars=yes,resizable=yes' );" class="button" /><br/>
							<?php
						}
						elseif($app_services[$i]['whereamiused'][$k]['nature'] == "include" && $_SESSION['user']['services'][$app_services[$i]['id']] && ($servicenature == "all" || $servicenature == "include") && !in_array($app_services[$i]['id'],$executed_services))
						{
							array_push($executed_services, $app_services[$i]['id']);
							include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$app_services[$i]['servicepage']);
						}
					}
				}
			}
		}
	}

	/**
	* Loads the html declaration and doctype
	*/
	public function load_html()
	{
		?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php  echo $_SESSION['config']['lang']; ?>" lang="<?php  echo $_SESSION['config']['lang']; ?>">
        <?php
	}

	/**
	* Loads the html header
	*
	* @param  $title string Title tag value (empty by default)
	*/
	public function load_header( $title = '', $load_css = true, $load_js = true)
	{
		if(empty($title))
		{
			 $title = $_SESSION['config']['applicationname'];
		}
		?>
        <head>
            <title><?php  echo $title;?></title>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <meta http-equiv="Content-Language" content="<?php  echo $_SESSION['config']['lang'];?>" />
			<link rel="icon" type="image/png" href="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=favicon.png"/>
            <?php
            if($load_css)
            {
				$this->load_css();
			}
			if($load_js)
			{
				$this->load_js();
			}
			?>
        </head>
		<?php
	}

	/**
	* Loads the modules and aplication css
	*/
	private function load_css()
	{
		?>
        <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['businessappurl'].'static.php?filename=styles.css'; ?>" media="screen" />

        <!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['businessappurl'].'static.php?filename=style_ie.css'; ?>" media="screen" />  <![endif]-->
        <!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['businessappurl'].'static.php?filename=style_ie7.css'; ?>" media="screen" />  <![endif]-->
        <?php
		foreach(array_keys($_SESSION['modules_loaded']) as $value)
		{
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."module.css") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."module.css"))
			{
				?>
				<link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['businessappurl'].'static.php?filename=module.css&module='.$_SESSION['modules_loaded'][$value]['name']; ?>" media="screen" />
				<?php
			}
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."module_IE.css") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."module_IE.css"))
			{
				?>
				<!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['businessappurl'].'static.php?filename=module_IE.css&module='.$_SESSION['modules_loaded'][$value]['name']; ?>" media="screen" />  <![endif]-->
				<?php
			}
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."module_IE7.css") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."module_IE7.css"))
			{
				?>
				<!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['businessappurl'].'static.php?filename=module_IE7.css&module='.$_SESSION['modules_loaded'][$value]['name']; ?>" media="screen" />  <![endif]-->
				<?php
			}
		}
	}

	/**
	* Loads the javascript files of the application and modules
	*/
	private function load_js()
	{
		?>
		<script type="text/javascript" >
			var app_path = '<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=';
		</script>
		<?php
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js") || file_exists($_SESSION['config']['corepath']."apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
		{
			?>
			<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=functions.js"></script>
			<?php
		}
		foreach(array_keys($_SESSION['modules_loaded']) as $value)
		{
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js") || file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules_loaded'][$value]['name'].DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."functions.js"))
			{
				?>
				<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=functions.js&module=<?php echo $_SESSION['modules_loaded'][$value]['name'];?>"></script>
				<?php
			}
		}
	}

	/**
	* Cleans the page variable and looks if she exists or not before including her
	*
	*/
	public function insert_page()
	{
		// Cleans the page variables and looks if she exists or not before including her
		
		if(isset($_GET['page']))
		{
			$this->f_page = $this->wash($_GET['page'],"file","","yes");
		}
		else
		{
			$this->loadDefaultPage();
			return true;
		}
		
		// Page is defined in a module
		if(isset($_GET['module']) && $_GET['module'] <> "core")
		{
			if(file_exists($_SESSION['config']['corepath'].'modules'.DIRECTORY_SEPARATOR.$_GET['module'].DIRECTORY_SEPARATOR.$this->f_page.".php")
			|| file_exists($_SESSION['config']['corepath'].DIRECTORY_SEPARATOR.'clients'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].'modules'.$_GET['module'].DIRECTORY_SEPARATOR.$this->f_page.".php")
			)
			{
				require('modules'.DIRECTORY_SEPARATOR.$_GET['module'].DIRECTORY_SEPARATOR.$this->f_page.".php");
			}
			else
			{
				$this->loadDefaultPage();
			
			}
		}
		// Page is defined the core
		elseif(isset($_GET['module']) && $_GET['module'] == "core")
		{
			if(file_exists($_SESSION['config']['corepath'].'core'.DIRECTORY_SEPARATOR.$this->f_page.".php")
			|| file_exists($_SESSION['config']['corepath'].'clients'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.$this->f_page.".php")
			)
			{
				require('core'.DIRECTORY_SEPARATOR.$this->f_page.".php");
			}
			else
			{
				$this->loadDefaultPage();
			}
		}
		// Page is defined the admin directory of the application
		elseif(isset($_GET['admin']) && !empty($_GET['admin']))
		{
			if(file_exists($_SESSION['config']['corepath'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR.trim($_GET['admin']).DIRECTORY_SEPARATOR.$this->f_page.".php")
			|| file_exists($_SESSION['config']['corepath'].'clients'.$_SESSION['custom_override_id'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR.trim($_GET['admin']).DIRECTORY_SEPARATOR.$this->f_page.".php")
			)
			{
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR.trim($_GET['admin']).DIRECTORY_SEPARATOR.$this->f_page.".php");
			}
			else
			{
				$this->loadDefaultPage();
			}
		}
		elseif(isset($_GET['dir']) && !empty($_GET['dir']))
		{
			if(file_exists($_SESSION['config']['corepath'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.trim($_GET['dir']).DIRECTORY_SEPARATOR.$this->f_page.".php")
			|| file_exists($_SESSION['config']['corepath'].'clients'.$_SESSION['custom_override_id'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.trim($_GET['dir']).DIRECTORY_SEPARATOR.$this->f_page.".php")
			)
			{
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.trim($_GET['dir']).DIRECTORY_SEPARATOR.$this->f_page.".php");
			}
			else
			{
				$this->loadDefaultPage();
			}
		}
		// Page is defined in the application
		else
		{
			if(file_exists($_SESSION['config']['corepath'].'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$this->f_page.".php")
			|| file_exists($_SESSION['config']['corepath'].'clients'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$this->f_page.".php")
			)
			{

				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$this->f_page.".php");
			}
			else
			{
				
				require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
				$app = new business_app_tools();
				$path = $app->insert_app_page($this->f_page);
				if( (!$path || empty($path)) && !file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.$path) && !file_exists($_SESSION['config']['corepath'].$path))
				{
					//require($_SESSION["config"]["defaultPage"].".php");
					$this->loadDefaultPage();
				}
				else
				{
					require($path);
				}
			}
		}
		return true;
	}

	/**
	* Loads the default page
	*/
	public function loadDefaultPage()
	{
		if(trim($_SESSION['target_page']) <> "" && trim($_SESSION['target_module']) <> "")
		{
			$target = "page=".$_SESSION['target_page']."&module=".$_SESSION['target_module'];
		}
		elseif(trim($_SESSION['target_page']) <> "" && trim($_SESSION['target_admin']) <> "")
		{
			$target = "page=".$_SESSION['target_page']."&admin=".$_SESSION['target_admin'];
		}
		elseif(trim($_SESSION['target_page']) <> "" && trim($_SESSION['target_module']) == "" && trim($_SESSION['target_admin']) == "")
		{
			$target = "page=".$_SESSION['target_page'];
		}
		$_SESSION['target_page'] = "";
		$_SESSION['target_module'] = "";
		$_SESSION['target_admin'] = "";
		if(isset($target) && trim($target) <> "")
		{
			$tmpTab = array();
			$tmpTab = explode("&", $target);
			if(count($tmpTab) == 1)
			{
				$page = str_replace("page=", "", $tmpTab[0]);
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$page.".php");
			}
			elseif(count($tmpTab) == 2)
			{
				$tabPage = array();
				$tabModuleOrAdmin = array();
				$tabPage = explode("=", $tmpTab[0]);
				$tabModuleOrAdmin = explode("=", $tmpTab[1]);
				if($tabModuleOrAdmin[0] == "module")
				{
					require('modules'.DIRECTORY_SEPARATOR.$tabModuleOrAdmin[1].DIRECTORY_SEPARATOR.$tabPage[1].".php");
				}
				else
				{
					//admin case
					if($tabPage[1] == "users" || $tabPage[1] == "groups" || $tabPage[1] == "admin_archi" || $tabPage[1] == "history" || $tabPage[1] == "history_batch"
					   || $tabPage[1] == "status" || $tabPage[1] == "action" || $tabPage[1] == "xml_param_services" || $tabPage[1] == "modify_user"
					  )
					{
						require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR.$tabModuleOrAdmin[1].DIRECTORY_SEPARATOR.$tabPage[1].".php");
					}
					else
					{
						require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."welcome.php");
					}
				}
			}
			else
			{
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."welcome.php");
			}
		}
		elseif(trim($_SESSION["config"]["defaultPage"]) <> "")
		{
			$tmpTab = array();
			$tmpTab = explode("&", $_SESSION["config"]["defaultPage"]);
			if(count($tmpTab) == 1)
			{
				$page = str_replace("page=", "", $tmpTab[0]);
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$page.".php");
			}
			elseif(count($tmpTab) == 2)
			{
				$tabPage = array();
				$tabModuleOrAdmin = array();
				$tabPage = explode("=", $tmpTab[0]);
				$tabModuleOrAdmin = explode("=", $tmpTab[1]);
				if($tabModuleOrAdmin[0] == "module")
				{
					require('modules'.DIRECTORY_SEPARATOR.$tabModuleOrAdmin[1].DIRECTORY_SEPARATOR.$tabPage[1].".php");
				}
				else
				{
					require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR.$tabModuleOrAdmin[1].DIRECTORY_SEPARATOR.$tabPage[1].".php");
				}
			}
			else
			{
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."welcome.php");
			}
		}
		else
		{
			require('apps'.DIRECTORY_SEPARATOR."welcome.php");
		}
	}

	/**
	* Loads the footer
	*/
	public function load_footer()
	{
		?>
		Powered by Maarch&trade;. <?php  $this->show_page_stat(); ?>
        <?php
	}

	/**
	* Views Cookies informations, POST and SESSION variables if the mode debug is enabled in the application config
	*/
	public function view_debug()
	{
		if($_SESSION['config']['debug'] == "true")
		{
			?>
			<div id="debug">
                <h2 class="tit">Debug Mode</h2>
                <div class="debugheader">COOKIE</div>
                    <?php
                    $this->show_array($_COOKIE);
                    ?>
                	<h2 class="tit">POST</h2>
                    <?php
                    $this->show_array($_POST);
                    ?>
                	<h2 class="tit">SESSION</h2>
                    <?php
                    $this->show_array($_SESSION);
                    ?>
			</div>
			<?php
		}
	}

	/**
	* Tests if the current user is defined in the current session
	*/
	public function test_user()
	{
		if(!isset($_SESSION['user']['UserId']))
		{
			if(trim($_SERVER['argv'][0]) <> "")
			{
				header("location: reopen.php?".$_SERVER['argv'][0]);
			}
			else
			{
				header("location: reopen.php");
			}
			exit;
		}
	}

	/**
	* Tests if the module is loaded
	*
	* @param  $module_id  string Module identifier the module to test
	* @return bool True if the module is found, False otherwise
	*/
	public function is_module_loaded($module_id)
	{
		foreach(array_keys($_SESSION['modules_loaded']) as $value)
		{
			if($value == $module_id && $_SESSION['modules_loaded'][$value]['loaded'] == "true")
			{
				return true;
			}
		}
		return false;
	}


	/**
	* Retrieves the label corresponding to a service
	*
	* @param  $id_service string Service identifier
	* @return string Service Label or  _NO_LABEL_FOUND value
	*/
	public function retrieve_label_service($id_service)
	{
		for($i=0;$i<count($_SESSION['enabled_services']);$i++)
		{
			if($_SESSION['enabled_services'][$i]['id'] == $id_service)
			{
				return $_SESSION['enabled_services'][$i]['label'];
			}
		}
		return _NO_LABEL_FOUND;
	}

	/**
	* Tests if the user has admin rights on the service
	*
	* @param  $id_service string Service identifier
	* @param  $module string Module identifier or "apps"
	* @param  $redirect bool If true the user is redirected in the index page, else no redirection (True by default)
	* @return bool or redirection depending on the $redirect value
	*/
	public function test_admin($id_service, $module, $redirect = true )
	{

		// Application service
		if($module == "apps")
		{
			$system = false;
			for($i=0; $i< count($_SESSION['apps_services']); $i++)
			{
				if($_SESSION['apps_services'][$i]['system_service'])
				{
					return true;
				}
			}
		}
		// Module service
		else
		{
			if(!$this->is_module_loaded($module))
			{
				if($redirect)
				{
					$_SESSION['error'] = _SERVICE.' '._UNKNOWN.' : '.$id_service;
					?>
					<script type="text/javascript" language="javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';</script>
					<?php
					exit();
				}
				else
				{
					return false;
				}
			}
			else
			{
				$system = false;
				for($i=0; $i< count($_SESSION['modules_services'][$module]); $i++)
				{
					if($_SESSION['modules_services'][$module][$i]['id'] == $id_service)
					{
						if($_SESSION['modules_services'][$module][$i]['system_service'] == true)
						{
							return true;
						}
					}
					else
					{
						break;
					}
				}
			}
		}
		if(! isset($_SESSION['user']['services'][$id_service]) )
		{
			if($redirect)
			{
				$_SESSION['error'] = _ADMIN_SERVICE.' '._UNKNOWN;
			?>
				<script type="text/javascript" language="javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';</script>
				<?php
				exit();
			}
			else
			{
				return false;
			}
		}
		else
		{
			if( $_SESSION['user']['services'][$id_service] == false)
			{
				if($redirect)
				{
					$label = $this->retrieve_label_service($id_service);
					$_SESSION['error'] = _NO_RIGHTS_ON.' : '.$label;
					?>
					<script type="text/javascript" language="javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';</script>
					<?php
					exit();
				}
				else
				{
					return false;
				}
			}
			else
			{
				return true;
			}
		}
	}

	/**
	* Tests if the user has right on the service
	*
	* @param  $id_service string Service identifier
	* @param  $module string Module identifier or "apps"
	* @param  $redirect bool If true the user is redirected in the index page, else no redirection (True by default)
	* @return bool or redirection depending on the $redirect value
	*/
	public function test_service($id_service, $module, $redirect = true)
	{
		// Application service
		if($module == "apps")
		{
			$system = false;
			if(isset($_SESSION['apps_services']))
			{
				for($i=0; $i< count($_SESSION['apps_services']); $i++)
				{
					if($_SESSION['apps_services'][$i]['system_service'])
					{
						return true;
					}
				}
			}
		}
		// Module service
		else
		{
			if(!$this->is_module_loaded($module))
			{
				if($redirect)
				{
					$_SESSION['error'] = _SERVICE.' '._UNKNOWN.' : '.$id_service;
				?>
					<script type="text/javascript" language="javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';</script>
					<?php
					exit();

				}
				else
				{
					return false;
				}
			}
			else
			{
				$system = false;
				for($i=0; $i< count($_SESSION['modules_services'][$module]); $i++)
				{
					if($_SESSION['modules_services'][$module][$i]['id'] == $id_service)
					{
						if($_SESSION['modules_services'][$module][$i]['system_service'] == true)
						{
							return true;
						}
					}
					else
					{
						break;
					}
				}
			}
		}
		if(! isset($_SESSION['user']['services'][$id_service]) )
		{
			if($redirect)
			{
				$_SESSION['error'] = _SERVICE.' '._UNKNOWN.' : '.$id_service;
			?>
				<script type="text/javascript" language="javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';</script>
				<?php
				exit();

			}
			else
			{
				return false;
			}
		}
		else
		{
			if( $_SESSION['user']['services'][$id_service] == false)
			{
				if($redirect)
				{
					$label = $this->retrieve_label_service($id_service);
					$_SESSION['error'] = _NO_RIGHTS_ON.' : '.$label;
					?>
					<script type="text/javascript" language="javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';</script>
					<?php
					exit();
				}
				else
				{
					return false;
				}
			}
			else
			{
				return true;
			}
		}
	}

	/**
	* Gets the time of session expiration
	*
	* @return string time of session expiration
	*/
	public function get_session_time_expire()
	{
		$time = 0;
		if(ini_get('session.cache_expire') > $_SESSION['config']['cookietime'])
		{
			$time = $_SESSION['config']['cookietime'];
		}
		else
		{
			$time = ini_get('session.cache_expire');
		}
		return $time;
	}

	/**
	* Executes  services preprocess in background in the  page
	*
	* @param  $modules_services array Enabled services
	* @param  $whereami  string Page where to execute the preprocess
	*/
	public function execute_preprocess_of_services_in_background($modules_services, $whereami)
	{
		$process_view = array();
		foreach(array_keys($modules_services) as $value)
		{
			for($i=0;$i<count($modules_services[$value]);$i++)
			{
				for($k=0;$k<count($modules_services[$value][$i]['processinbackground']);$k++)
				{
					if($modules_services[$value][$i]['processinbackground'][$k]['page'] == $whereami && $modules_services[$value][$i]['processinbackground'][$k]['preprocess'] <> "")
					{
						$process_order = $modules_services[$value][$i]['processinbackground'][$k]['processorder'];
						$process_view[$process_order]['preprocess'] = 'modules'.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR.$modules_services[$value][$i]['processinbackground'][$k]['preprocess'];
						$process_view[$process_order]['id_service'] = $modules_services[$value][$i]['id'];
					}
				}
			}
		}
		sort($process_view);

		for($u=0;$u<=count($process_view);$u++)
		{
			if($process_view[$u]['preprocess'] <> "")
			{
				include($process_view[$u]['preprocess']);
			}
		}
	}

	/**
	* Executes services postprocess  in background in the page
	*
	* @param  $modules_services array Enabled services
	* @param  $whereami  string Page where execute the postprocess
	*/
	public function execute_postprocess_of_services_in_background($modules_services, $whereami)
	{
		$process_view = array();
		foreach(array_keys($modules_services) as $value)
		{
			for($i=0;$i<count($modules_services[$value]);$i++)
			{
				for($k=0;$k<count($modules_services[$value][$i]['processinbackground']);$k++)
				{
					if($modules_services[$value][$i]['processinbackground'][$k]['page'] == $whereami && $modules_services[$value][$i]['processinbackground'][$k]['postprocess'] <> "")
					{
						$process_order = $modules_services[$value][$i]['processinbackground'][$k]['processorder'];
						$process_view[$process_order]['postprocess'] = 'modules'.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR.$modules_services[$value][$i]['processinbackground'][$k]['postprocess'];
					}
				}
			}
		}
		sort($process_view);
		for($u=0;$u<=count($process_view);$u++)
		{
			if($process_view[$u]['postprocess'] <> "")
			{
				include($process_view[$u]['postprocess']);
			}
		}
	}

	/**
	* Executes application preprocess  services in background in the page
	*
	* @param  $modules_services array Enabled services
	* @param  $whereami string Page where to execute the preprocess
	*/
	public function execute_preprocess_of_apps_services_in_background($app_services, $whereami)
	{
		$process_view = array();
		for($i=0;$i<count($app_services);$i++)
		{
			for($k=0;$k<count($app_services[$i]['processinbackground']);$k++)
			{
				if($app_services[$i]['processinbackground'][$k]['page'] == $whereami && $app_services[$i]['processinbackground'][$k]['preprocess'] <> "")
				{
					$process_order = $app_services[$i]['processinbackground'][$k]['processorder'];
					$process_view[$process_order]['preprocess'] = 'apps/'.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$app_services[$i]['processinbackground'][$k]['preprocess'];
					$process_view[$process_order]['id_service'] = $app_services[$i]['id'];
				}
			}
		}
		sort($process_view);
		for($u=0;$u<=count($process_view);$u++)
		{
			if($process_view[$u]['preprocess'] <> "")
			{
				include($process_view[$u]['preprocess']);
			}
		}
	}

	/**
	* Executes the application postprocess  services in background in the page
	*
	* @param  $modules_services array Enabled services
	* @param  $whereami string Page where to execute the postprocess
	*/
	public function execute_postprocess_of_apps_services_in_background($app_services, $whereami)
	{
		$process_view = array();
		for($i=0;$i<count($app_services);$i++)
		{
			for($k=0;$k<count($app_services[$i]['processinbackground']);$k++)
			{
				if($app_services[$i]['processinbackground'][$k]['page'] == $whereami && $app_services[$i]['processinbackground'][$k]['postprocess'] <> "")
				{
					$process_order = $app_services[$i]['processinbackground'][$k]['processorder'];
					$process_view[$process_order]['postprocess'] = 'apps/'.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$app_services[$i]['processinbackground'][$k]['postprocess'];
					$process_view[$process_order]['id_service'] = $app_services[$i]['id'];
				}
			}
		}
		sort($process_view);

		for($u=0;$u<=count($process_view);$u++)
		{
			if($process_view[$u]['postprocess'] <> "")
			{
				include($process_view[$u]['postprocess']);
			}
		}
	}

	/**
	* Gets the page corresponding to the service
	*
	* @param  $id_service  string Service identifier
	* @param  $origin string Service origin : MODULE or APPS
	* @param  $id_module string Module identifier(empty by default)
	* @return Service page or False
	*/
	public function get_service_page($id_service, $origin, $id_module = '')
	{
		if(trim(strtoupper($origin)) == "MODULE")
		{
			if(empty($id_module))
			{
				$_SESSION['error'] = _ID_MODULE.' '._MISSING;
				return false;
			}
			for($i=0; $i<count($_SESSION['modules_services'][$id_module]);$i++)
			{
				if($_SESSION['modules_services'][$id_module][$i]['id'] == trim($id_service))
				{
					if(isset($_SESSION['modules_services'][$id_module][$i]['servicepage']) && !empty($_SESSION['modules_services'][$id_module][$i]['servicepage']))
					{
						return $_SESSION['modules_services'][$id_module][$i]['servicepage'];
					}
					else
					{
						$_SESSION['error'] = _SERVICE_PAGE_NOT_DEFINED_EMPTY;
						return false;
					}
				}
			}
		}
		elseif(trim(strtoupper($origin)) == "APPS")
		{
			for($i=0; $i<count($_SESSION['apps_services']);$i++)
			{
				if($_SESSION['apps_services'][$i]['id'] == trim($id_service))
				{
					if(isset($_SESSION['apps_services'][$i]['servicepage']) && !empty($_SESSION['apps_services'][$i]['servicepage']))
					{
						return $_SESSION['apps_services'][$i]['servicepage'];
					}
					else
					{
						$_SESSION['error'] = _SERVICE_PAGE_NOT_DEFINED_EMPTY;
						return false;
					}
				}
			}
		}
	}

	/**
	* Gets the path of an action
	*
	* @param  $id_service  string Action identifier
	* @return Action page or action identifier if not found
	*/
	public function get_path_action_page($action_id)
	{
		$found = false;
		$ind = -1;
		for($i=0; $i< count($_SESSION['actions_pages']); $i++)
		{
			if($_SESSION['actions_pages'][$i]['ID'] == $action_id)
			{
				$found = true;
				$ind = $i;
				break;
			}
		}
		if(!$found)
		{
			return $action_id;
		}
		else
		{
			$path = $action_id;
			if(strtoupper($_SESSION['actions_pages'][$ind]['ORIGIN']) == "APPS")
			{
				$path = "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."actions".DIRECTORY_SEPARATOR.$_SESSION['actions_pages'][$ind]['NAME'].".php";
			}
			elseif(strtoupper($_SESSION['actions_pages'][$ind]['ORIGIN']) == "MODULE")
			{
				$path = 'modules'.DIRECTORY_SEPARATOR.$_SESSION['actions_pages'][$ind]['MODULE'].DIRECTORY_SEPARATOR.$_SESSION['actions_pages'][$ind]['NAME'].".php";
			}
			return $path;
		}
	}

	/**
	* Gets the url of an action
	*
	* @param  $id_service  string Action identifier
	* @return Action url or action identifier if not found
	*/

	public function get_url_action_page($action_id)
	{
		$found = false;
		$ind = -1;
		for($i=0; $i< count($_SESSION['actions_pages']); $i++)
		{
			if($_SESSION['actions_pages'][$i]['ID'] == $action_id)
			{
				$found = true;
				$ind = $i;
				break;
			}
		}
		if(!$found)
		{
			return $action_id;
		}
		else
		{
			$path = $action_id;
			if(strtoupper($_SESSION['actions_pages'][$ind]['ORIGIN']) == "APPS")
			{
				//$path = $_SESSION['config']['businessappurl'].$_SESSION['actions_pages'][$ind]['NAME'].".php";
				$path = $_SESSION['config']['businessappurl']."index.php?display=true&page=".$_SESSION['actions_pages'][$ind]['NAME'];
			}
			elseif(strtoupper($_SESSION['actions_pages'][$ind]['ORIGIN']) == "MODULE")
			{
				//$path = $_SESSION['urltomodules'].$_SESSION['actions_pages'][$ind]['MODULE'].'/'.$_SESSION['actions_pages'][$ind]['NAME'].".php";
				$path = $_SESSION['config']['businessappurl']."index.php?display=true&page=".$_SESSION['actions_pages'][$ind]['NAME']."&module=".$_SESSION['actions_pages'][$ind]['MODULE'];
			}
			return $path;
		}
	}

	public function is_action_defined($action_id)
	{
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_db.php');
		if(empty($action_id))
		{
			return false;
		}
		$db = new dbquery();
		$db->connect();
		$db->query("select origin from ".$_SESSION['tablename']['actions']." where id = ".$action_id);
		$res = $db->fetch_object();
		$origin = $res->origin;

		if($origin == 'apps' || $origin == 'core')
		{
			return true;
		}
		for($i=0; $i<count($_SESSION['modules']);$i++)
		{
			if($_SESSION['modules'][$i]['moduleid'] == $origin)
			{
				return true;
			}
		}
		return false;
	}


	
	public function get_custom_id()
	{
		if(!file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.'custom.xml'))
		{
			return '';
		}
		
		$arr = explode('/', $_SERVER['SCRIPT_NAME']);
		$path = $arr[1];
		//echo $path;
		$xml = simplexml_load_file($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.'custom.xml');
		var_dump($xml);
		foreach($xml->custom as $custom)
		{
			if(trim($path) <> "" && isset( $custom->path) && $custom->path == trim($path))
			{
				return (string) $custom->custom_id;
			}
			if($custom->ip == $_SERVER['SERVER_ADDR'])
			{
				return (string) $custom->custom_id;
			}
			if($custom->external_domain == $_SERVER['HTTP_HOST'] xor $custom->domain == $_SERVER['HTTP_HOST'])
			{
				return (string) $custom->custom_id;
			}
		}
		return '';
	}
}
?>
