<?php
/**
* core tools Class
*
*  Contains all the functions to load core and others
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Laurent Giovannoni  <dev@maarch.org>
*
*/

class business_app_tools extends dbquery
{
	function __construct()
	{
		parent::__construct();
	}
	/**
	* Build Maarch business app configuration into sessions vars with a xml configuration file
	*/
	public function build_business_app_config()
	{
		// build Maarch business app configuration into sessions vars
		$_SESSION['showmenu']='oui';
	
		$core = new core_tools();
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml'))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml';
		}
		else
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'config.xml';
		}
		$xmlconfig = simplexml_load_file($path);
		if( $xmlconfig <> false)
		{
			$CONFIG = $xmlconfig->CONFIG;
			$_SESSION['config']['businessappname'] = (string) $CONFIG->businessappname;
			//$_SESSION['config']['businessapppath'] = (string) $CONFIG->businessapppath;
			//##############
			if ($_SERVER['SERVER_PORT'] <> 443 && $protocol == "https")		
			{
				$server_port = ":".$_SERVER['SERVER_PORT'];
			}
			elseif ($_SERVER['SERVER_PORT'] <> 80 && $protocol == "http")
			{
				$server_port = ":".$_SERVER['SERVER_PORT'];
			}
			else
			{
					$server_port = "";
			}	
				
			if ($_SERVER['HTTPS'] == "on")
				$protocol = "https";
			else
				$protocol = "http";
				
				
			//##############
			if($_SERVER['HTTP_X_FORWARDED_HOST'] <> "")
			{
					$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
			}
			else
			{
					$host = $_SERVER['HTTP_HOST'];
			}

			$_SESSION['config']['businessappurl'] = $protocol."://".$host.$server_port.str_replace('index.php','',$_SERVER['SCRIPT_NAME']);
			$_SESSION['config']['databaseserver'] = (string) $CONFIG->databaseserver;
			$_SESSION['config']['databaseserverport'] = (string) $CONFIG->databaseserverport;
			$_SESSION['config']['databasetype'] = (string) $CONFIG->databasetype;
			$_SESSION['config']['databasename'] = (string) $CONFIG->databasename;
			$_SESSION['config']['databaseschema'] = (string) $CONFIG->databaseschema;
			$_SESSION['config']['databaseuser'] = (string) $CONFIG->databaseuser;
			$_SESSION['config']['databasepassword'] = (string) $CONFIG->databasepassword;
			$_SESSION['config']['databasesearchlimit'] = (string) $CONFIG->databasesearchlimit;
			$_SESSION['config']['nblinetoshow'] = (string) $CONFIG->nblinetoshow;
			$_SESSION['config']['limitcharsearch'] = (string) $CONFIG->limitcharsearch;
			$_SESSION['config']['lang'] = (string) $CONFIG->lang;
			$_SESSION['config']['adminmail'] = (string) $CONFIG->adminmail;
			$_SESSION['config']['adminname'] = (string) $CONFIG->adminname;
			$_SESSION['config']['debug'] = (string) $CONFIG->debug;
			$_SESSION['config']['applicationname'] = (string) $CONFIG->applicationname;
			$_SESSION['config']['defaultPage'] = (string) $CONFIG->defaultPage;
			$_SESSION['config']['exportdirectory'] = (string) $CONFIG->exportdirectory;
			$_SESSION['config']['tmppath'] = (string) $CONFIG->tmppath;
			$_SESSION['config']['cookietime'] = (string) $CONFIG->CookieTime;
			$_SESSION['config']['ldap'] = (string) $CONFIG->ldap;
			//$_SESSION['config']['databaseworkspace'] = (string) $CONFIG->databaseworkspace;

			$TABLENAME =  $xmlconfig->TABLENAME ;
			$_SESSION['tablename']['doctypes_first_level'] = (string) $TABLENAME->doctypes_first_level;
			$_SESSION['tablename']['doctypes_second_level'] = (string) $TABLENAME->doctypes_second_level;
			$_SESSION['tablename']['mlb_doctype_ext'] = (string) $TABLENAME->mlb_doctype_ext;
			$_SESSION['tablename']['doctypes_indexes'] = (string) $TABLENAME->doctypes_indexes;
			$_SESSION['tablename']['saved_queries'] = (string) $TABLENAME->saved_queries;
			$_SESSION['tablename']['contacts'] = (string) $TABLENAME->contacts;
			$i=0;

			$_SESSION['collections'] = array();
			foreach($xmlconfig->COLLECTION as $col)
			{
				$tmp = (string) $col->label;
				$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
				if($tmp2 <> false)
				{
					$tmp = $tmp2;
				}
				$extensions = $col->extensions;
				$tab = array();
				foreach($extensions->table as $table)
				{
					array_push($tab,(string)$table);
				}
				if(isset($col->table) && !empty($col->table))
				{
					$_SESSION['collections'][$i] = array("id" => (string) $col->id, "label" => (string) $tmp, "table" => (string) $col->table,"view" => (string) $col->view, "index_file" => (string) $col->index_file, "script_add" => (string) $col->script_add, "script_search" => (string) $col->script_search, "script_search_result" => (string) $col->script_search_result, "script_details"=> (string) $col->script_details, "path_to_lucene_index"=> (string) $col->path_to_lucene_index, "extensions" => $tab);
					$i++;
				}
				else
				{
					$_SESSION['collections'][$i] = array("id" => (string) $col->id, "label" => (string) $tmp, "view" => (string) $col->view, "index_file" => (string) $col->index_file, "script_add" => (string) $col->script_add, "script_search" => (string) $col->script_search, "script_search_result" => (string) $col->script_search_result, "script_details"=> (string) $col->script_details, "path_to_lucene_index"=> (string) $col->path_to_lucene_index, "extensions" => $tab);
				}
			}
			$HISTORY = $xmlconfig->HISTORY;
			$_SESSION['history']['usersdel'] = (string) $HISTORY->usersdel;
			$_SESSION['history']['usersban'] = (string) $HISTORY->usersban;
			$_SESSION['history']['usersadd'] = (string) $HISTORY->usersadd;
			$_SESSION['history']['usersup'] = (string) $HISTORY->usersup;
			$_SESSION['history']['usersval'] = (string) $HISTORY->usersval;
			$_SESSION['history']['doctypesdel'] = (string) $HISTORY->doctypesdel;
			$_SESSION['history']['doctypesadd'] = (string) $HISTORY->doctypesadd;
			$_SESSION['history']['doctypesup'] = (string) $HISTORY->doctypesup;
			$_SESSION['history']['doctypesval'] = (string) $HISTORY->doctypesval;
			$_SESSION['history']['doctypesprop'] = (string) $HISTORY->doctypesprop;
			$_SESSION['history']['usergroupsdel'] = (string) $HISTORY->usergroupsdel;
			$_SESSION['history']['usergroupsban'] = (string) $HISTORY->usergroupsban;
			$_SESSION['history']['usergroupsadd'] = (string) $HISTORY->usergroupsadd;
			$_SESSION['history']['usergroupsup'] = (string) $HISTORY->usergroupsup;
			$_SESSION['history']['usergroupsval'] = (string) $HISTORY->usergroupsval;
			$_SESSION['history']['structuredel'] = (string) $HISTORY->structuredel;
			$_SESSION['history']['structureadd'] = (string) $HISTORY->structureadd;
			$_SESSION['history']['structureup'] = (string) $HISTORY->structureup;
			$_SESSION['history']['subfolderdel'] = (string) $HISTORY->subfolderdel;
			$_SESSION['history']['subfolderadd'] = (string) $HISTORY->subfolderadd;
			$_SESSION['history']['subfolderup'] = (string) $HISTORY->subfolderup;
			$_SESSION['history']['resadd'] = (string) $HISTORY->resadd;
			$_SESSION['history']['resup'] = (string) $HISTORY->resup;
			$_SESSION['history']['resdel'] = (string) $HISTORY->resdel;
			$_SESSION['history']['resview'] = (string) $HISTORY->resview;
			$_SESSION['history']['userlogin'] = (string) $HISTORY->userlogin;
			$_SESSION['history']['userlogout'] = (string) $HISTORY->userlogout;
			$_SESSION['history']['actionadd'] = (string) $HISTORY->actionadd;
			$_SESSION['history']['actionup'] = (string) $HISTORY->actionup;
			$_SESSION['history']['actiondel'] = (string) $HISTORY->actiondel;
			$_SESSION['history']['contactadd'] = (string) $HISTORY->contactadd;
			$_SESSION['history']['contactup'] = (string) $HISTORY->contactup;
			$_SESSION['history']['contactdel'] = (string) $HISTORY->contactdel;
			$_SESSION['history']['statusadd'] = (string) $HISTORY->statusadd;
			$_SESSION['history']['statusup'] = (string) $HISTORY->statusup;
			$_SESSION['history']['statusdel'] = (string) $HISTORY->statusdel;
			$_SESSION['history_keywords'] = array();
			foreach($xmlconfig->KEYWORDS as $keyword)
			{
				$tmp = (string) $keyword->label;
				$tmp2 = $this->retrieve_constant_lang($tmp, 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].".php");
				if($tmp2 <> false)
				{
					$tmp = $tmp2;
				}
				array_push($_SESSION['history_keywords'], array('id' =>(string) $keyword->id,'label' =>$tmp));
			}


			$i=0;
			foreach($xmlconfig->MODULES as $MODULES)
			{

				$_SESSION['modules'][$i] = array("moduleid" => (string) $MODULES->moduleid
				//,"comment" => (string) $MODULES->comment
				);
				$i++;
			}

			$this->load_actions_pages();
		}
	}

	/**
	* Load actions in session
	*/
	private function load_actions_pages()
	{
		if(isset($_SESSION['config']['corepath']) && isset($_SESSION['config']['app_id']) && isset($_SESSION['config']['lang']))
		{
			$core = new core_tools();
			if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."actions_pages.xml"))
			{
				$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."actions_pages.xml";
			}
			else
			{
				$path = "core".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."actions_pages.xml";
			}
			$xmlfile = simplexml_load_file($path);
			$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';

			$i =0;
			foreach($xmlfile->ACTIONPAGE as $ACTIONPAGE)
			{
				$tmp = (string) $ACTIONPAGE->LABEL;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$label = $tmp2;
				}
				else
				{
					$label = $tmp;
				}
				$_SESSION['actions_pages'][$i] = array("ID" => (string) $ACTIONPAGE->ID, "LABEL" => $label,"NAME" => (string) $ACTIONPAGE->NAME, "ORIGIN" => (string) $ACTIONPAGE->ORIGIN,"MODULE" => (string) $ACTIONPAGE->MODULE);
				$i++;

			}
		}
	}

	private function load_letterbox_var()
	{
		$core = new core_tools();
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml";
		}
		else
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml";
		}
		$xmlfile = simplexml_load_file($path);
		$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';

		$categories = $xmlfile->categories;
		$_SESSION['mail_categories'] = array();
		foreach($categories->category as $cat )
		{
			$tmp = (string) $cat->label;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}
			$_SESSION['mail_categories'][(string)$cat->id] = $label;
		}
		$_SESSION['default_category'] = (string) $categories->default_category;

		$_SESSION['mail_natures'] = array();
		$mail_natures = $xmlfile->mail_natures;
		foreach($mail_natures->nature as $nature )
		{
			$tmp = (string) $nature->label;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}
			$_SESSION['mail_natures'][(string)$nature->id] = $label;
		}
		$_SESSION['default_mail_nature'] = (string) $mail_natures->default_nature;

		$_SESSION['mail_priorities'] = array();
		$mail_priorities = $xmlfile->priorities;
		$i=0;
		foreach($mail_priorities->priority as $priority )
		{
			$tmp = (string) $priority;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}
			$_SESSION['mail_priorities'][$i] = $label;
			$i++;
		}
		$_SESSION['default_mail_priority'] = (string) $mail_priorities->default_priority;
	}

	public function load_features($xml_features)
	{
		$_SESSION['features'] = array();
		//Defines all features by  default at 'false'
		$_SESSION['features']['add_copy_in_process'] = "false";
		$_SESSION['features']['personal_contact'] = "false";
		$_SESSION['features']['search_notes'] = "false";
		$_SESSION['features']['dest_to_copy_during_redirection'] = "false";

		$xmlfeatures = simplexml_load_file($xml_features);
		if ($xmlfeatures)
		{
			foreach($xmlfeatures->FEATURES as $FEATURES)
			{
				$_SESSION['features']['add_copy_in_process'] = (string) $FEATURES->add_copy_in_process;
				$_SESSION['features']['personal_contact'] = (string) $FEATURES->personal_contact;
				$_SESSION['features']['search_notes'] = (string) $FEATURES->search_notes;
				$_SESSION['features']['dest_to_copy_during_redirection'] = (string) $FEATURES->dest_to_copy_during_redirection;
			}
		}
	}

	/**
	* Loads current folder identifier in session
	*
	*/
	private function load_current_folder()
	{
		$this->connect();

		$this->query("select custom_t1 from ".$_SESSION['tablename']['users']." where user_id = '".$_SESSION['user']['UserId']."'");

		$res = $this->fetch_object();

		$_SESSION['current_folder_id'] = $res->custom_t1;

	}

	/**
	* Loads app specific vars in session
	*
	*/
	public function load_app_var_session()
	{
		$this->load_current_folder();
		$this->load_letterbox_var();
		$this->load_features('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'features.xml');
	}

	/**
	* Return a specific path or false
	*
	*/
	public function insert_app_page($name)
	{
		if($name == "structures" || $name=="structures_list_by_name" || $name == "structure_up" || $name == "structure_del")
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."architecture".DIRECTORY_SEPARATOR."structures".DIRECTORY_SEPARATOR.$name.'.php';
			return $path;
		}
		elseif($name == "subfolders" || $name=="subfolders_list_by_name" || $name == "subfolder_up" || $name == "subfolder_del")
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."architecture".DIRECTORY_SEPARATOR."subfolders".DIRECTORY_SEPARATOR.$name.'.php';
			return $path;
		}
		elseif($name == "types" || $name == "types_up" || $name == "types_up_db" || $name == "types_add" || $name == "types_del" || $name == 'get_index' || $name == "choose_index" || $name == "choose_coll" || $name == "types_list_by_name")
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."architecture".DIRECTORY_SEPARATOR."types".DIRECTORY_SEPARATOR.$name.'.php';
			
			return $path;
		}
		else
		{
			return false;
		}
	}

	public function get_titles()
	{
		$core = new core_tools();
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml";
		}
		else
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml";
		}
		$xmlfile = simplexml_load_file($path);
		$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';

		$res_titles = array();
		$titles = $xmlfile->titles;
		foreach($titles->title as $title )
		{
			$tmp = (string) $title->label;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}

			$res_titles[(string)$title->id] = $label;
		}

		asort($res_titles, SORT_LOCALE_STRING);
		$default_title = (string) $titles->default_title;
		return array('titles' => $res_titles, 'default_title' => $default_title);
	}


	public function get_label_title($id_title)
	{
		$core = new core_tools();
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml";
		}
		else
		{
			$path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."entreprise.xml";
		}
		$xmlfile = simplexml_load_file($path);
		$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		$titles = $xmlfile->titles;
		foreach($titles->title as $title )
		{
			if($id_title == (string)$title->id)
			{
				$tmp = (string) $title->label;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$label = $tmp2;
				}
				else
				{
					$label = $tmp;
				}
				return $label;
			}
		}
		return '';
	}

}
?>
