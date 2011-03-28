<?php
/**
* modules tools Class for templates
*
*  Contains all the functions to load modules tables for template
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
*
*/

class templates extends dbquery
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Build Maarch module tables into sessions vars with a xml configuration file
	*/
	public function build_modules_tables()
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
		{
			$path_config = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		else
		{
			$path_config = "modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		$xmlconfig = simplexml_load_file($path_config);
		foreach($xmlconfig->TABLENAME as $TABLENAME)
		{
			$_SESSION['tablename']['temp_templates'] = (string) $TABLENAME->temp_templates;
			$_SESSION['tablename']['temp_templates_doctype_ext'] = (string) $TABLENAME->temp_templates_doctype_ext;
			$_SESSION['tablename']['temp_templates_association'] = (string) $TABLENAME->temp_templates_association;
		}
		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['templateadd'] = (string) $HISTORY->templateadd;
		$_SESSION['history']['templateup'] = (string) $HISTORY->templateup;
		$_SESSION['history']['templatedel'] = (string) $HISTORY->templatedel;
	}


	public function getAllItemsLinkedToModel($template_id, $field ='')
	{
		$items = array();
		if(empty($template_id))
		{
			return $items;
		}
		$this->connect();

		if(empty($field))
		{
			$this->query("select distinct what from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$template_id);
			while($res = $this->fetch_object())
			{
				$items[$res->what] = array();
			}
			foreach(array_keys($items) as $key)
			{
				$this->query("select value_field from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$template_id." and what = '".$key."'");
				$items[$key] = array();
				while($res = $this->fetch_object())
				{
					array_push($items[$key], $res->value_field);
				}
			}
		}
		else
		{
			$items[$field] = array();
			$this->query("select value_field from ".$_SESSION['tablename']['temp_templates_association']." where template_id = ".$template_id." and what = '".$field."'");
			while($res = $this->fetch_object())
			{
				array_push($items[$field], $res->value_field);
			}
		}
		return $items;
	}

	public function getModelsFromResid($res_id, $coll_id, $field ='')
	{
		$templates = array();
		if(empty($res_id) || empty($coll_id))
		{
			return $templates;
		}
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		$sec = new security();
		$table = $sec->retrieve_table_from_coll($coll_id);
		if(empty($table))
		{
			return $templates;
		}
		$templates_str = '';

		$this->connect();
		if(empty($field))
		{
			$db = new dbquery();
			$db->connect();
			$this->query("select distinct what from ".$_SESSION['tablename']['temp_templates_association']);
			while($res = $this->fetch_object())
			{
				$db->query("select ".$res->what." as what from ".$table." where res_id = ".$res_id );
				$line = $db->fetch_object();
				$what = $line->what;
				$db->query("select ma.template_id, m.label, ma. from ".$_SESSION['tablename']['temp_templates_association']." ma, ".$_SESSION['tablename']['temp_templates']." m where m.id = ma.template_id and ma.value_field = '".$what."'");
				$line = $db->fetch_object();
				array_push($templates, array('ID' => $line->template_id, 'LABEL' => $line->label));
				$templates_str .= $line->template_id.", ";
			}
		}
		else
		{
			$this->query("select ".$field." as what from ".$table." where res_id = ".$res_id );
			$line = $this->fetch_object();
			$what = $line->what;
			$this->query("select ma.template_id, m.label, ma. from ".$_SESSION['tablename']['temp_templates_association']." ma, ".$_SESSION['tablename']['temp_templates']." m where m.id = ma.template_id and ma.value_field = '".$what."'");
			$line = $this->fetch_object();
			array_push($templates, array('ID' => $line->template_id, 'LABEL' => $line->label));
			$templates_str = $line->template_id." ";
		}

		/*if(!empty($templates_str))
		{
			$templates_str = preg_replace('/, $/', '', $templates_str);
			$this->query("select id, label from ".$_SESSION['tablename']['temp_templates'].". where id not in (".$templates_str.")");
		}
		while($res = $this->fetch_object())
		{
			array_push($templates, array('ID' => $res->template_id, 'LABEL' => $res->label));
		}*/
		return $templates;
	}

	/**
	* replace the defined fields in the instance of the template
	*
	* @param string  $content template content
	*/
	public function fields_replace($content, $res_id = '', $coll_id = '')
	{
		if(!empty($res_id) && !empty($coll_id))
		{
			require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
			require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_business_app_tools.php');
			$sec = new security();
			$business = new business_app_tools();
			$this->connect();
			$table = $sec->retrieve_view_from_coll_id($coll_id);
			$params = array();
			$params['coll_view'] = array();
			$params['users'] = array();
			$params['users']['table'] = $_SESSION['tablename']['users'];
			$params['users']['where'] = '';
			$params['contacts'] = array();
			$params['contacts']['table'] = $_SESSION['tablename']['contacts'];
			$params['contacts']['where'] = '';
			$params['entities'] = array();
			$params['entities']['where'] = '';
			$params['entities']['table'] = $_SESSION['tablename']['ent_entities'];
			$params['coll_view']['table'] = $table;
			$params['coll_view']['where'] = " where res_id = ".$res_id;

			$this->query("select exp_contact_id, exp_user_id, dest_user_id, dest_contact_id, destination from ".$params['coll_view']['table']." ".$params['coll_view']['where']);

			$res = $this->fetch_object();
			if(isset($res->exp_contact_id) && !empty($res->exp_contact_id))
			{
				$params['contacts']['where'] = " where contact_id = ".$res->exp_contact_id;
			}
			else if(isset($res->dest_contact_id) && !empty($res->dest_contact_id))
			{
				$params['contacts']['where'] = " where contact_id = ".$res->dest_contact_id;
			}

			if(isset($res->exp_user_id) && !empty($res->exp_user_id))
			{
				$params['users']['where'] = " where user_id = '".$res->exp_user_id."'";
			}
			else if(isset($res->dest_user_id) && !empty($res->dest_user_id))
			{
				$params['users']['where'] = " where user_id = '".$res->dest_user_id."'";
			}

			if(isset($res->destination) && !empty($res->destination))
			{
				$params['entities']['where'] = " where entity_id = '".$res->destination."'";
			}

			if($table <> '')
			{
				if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."mapping_file.xml"))
				{
					$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."mapping_file.xml";
				}
				else
				{
					$path = "modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."mapping_file.xml";
				}
				$xml = simplexml_load_file($path);

				$items = array();
				foreach($xml->item as $item)
				{
					$field = (string) $item->field;
					$var_name = (string) $item->var_name;
					$used_table = '';
					$used_where = '';
					if(isset($item->table))
					{
						$tmp_table = (string) $item->table;
						if($tmp_table == 'coll_view')
						{
							$used_table = $params['coll_view']['table'];
							$used_where = $params['coll_view']['where'];
						}
						else
						{
							$used_table = $params[$tmp_table]['table'];
							$used_where = $params[$tmp_table]['where'];
						}
					}
					$type = '';
					if(isset($item->type))
					{
						$type = (string) $item->type;
					}
					if(!empty($field) && !empty($tmp_table) && !empty($used_where))
					{
						$this->query("select ".$field." as field from ".$used_table." ".$used_where);
						$res = $this->fetch_object();
						$value = $res->field;
						if($var_name == '[CAT_ID]')
						{
							$value = $_SESSION['mail_categories'][$value];
						}
						elseif($var_name == '[NATURE]')
						{
							$value = $_SESSION['mail_natures'][$value];
						}
						elseif($var_name == '[CONTACT_TITLE]')
						{
							$value = $business->get_label_title($value);
						}
						elseif($type == 'string')
						{
							$value = $this->show_string($value);
						}
						else if($type == 'date')
						{
							$value = $this->format_date_db($value, false);
						}
						array_push($items, array('var_name' =>  $var_name, 'value' => $value));
					}
					else
					{
						switch($var_name)
						{
							case "[NOW]" :
								$value = date('j-m-Y');
								break;
							case "[CURRENT_USER_FIRSTNAME]" :
								$value = $_SESSION['user']['FirstName'];
								break;
							case "[CURRENT_USER_LASTNAME]" :
								$value = $_SESSION['user']['LastName'];
								break;
							case "[CURRENT_USER_PHONE]" :
								$value = $_SESSION['user']['Phone'];
								break;
							case "[CURRENT_USER_EMAIL]" :
								$value = $_SESSION['user']['Mail'];
								break;
							default :
								$value = '';
						}
						//if($value<> '')
						//{
							array_push($items, array('var_name' =>  $var_name, 'value' => $value));
						//}
					}
				}
			}
		}

		for($i=0; $i< count($items);$i++)
		{
			$content = str_replace($items[$i]['var_name'], $items[$i]['value'], $content);
		}

		return $content;
	}
}
?>
