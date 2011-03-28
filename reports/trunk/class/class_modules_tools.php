<?php
/**
* modules tools Class for Advanced Physical Archive
*
*  Contains all the functions to load modules tables for Advanced Physical Archive
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Yves Christian KPAKPO  <dev@maarch.org>
*
*/

class reports  extends dbquery
{

	/**
	* Build Maarch module tables into sessions vars with a xml configuration file
	*/
	public function build_modules_tables()
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		$xmlconfig = simplexml_load_file($path);

		foreach($xmlconfig->TABLENAME as $TABLENAME)
		{
			$_SESSION['tablename']['usergroups_reports'] = (string) $TABLENAME->usergroups_reports;
		}

		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['usergroupsreportsadd'] = (string) $HISTORY->usergroupsreportsadd;
		$_SESSION['history']['viewreport'] = (string) $HISTORY->viewreport;
		$_SESSION['history']['printreport'] = (string) $HISTORY->printreport;
	}


	function get_reports_from_xml($id_report = '', $only_enabled = true)
	{
		$reports = array();
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."reports.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."reports.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."reports".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."reports.xml";
		}
		$xmlfile = simplexml_load_file($path);
		$i =0;
		foreach($xmlfile->REPORT as $report)
		{

			$id = (string)$report->ID;
			$enabled = (string)$report->ENABLED;
			$menu = (string)$report->IN_MENU_REPORTS;
			$url = (string)$report->URL;
			$origin = (string)$report->ORIGIN;

			if ($origin == "apps")
			{
				$module = "application";
				$path_lang = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
				$tmp = $this->retrieve_constant_lang(_APPS_COMMENT, $path_lang);
				if($tmp != false)
				{
					$moduleLabel = $tmp;
				}
				else
				{
					$moduleLabel = _APPS_COMMENT;
				}
				$label = (string)$report->LABEL;
				$label = $this->retrieve_constant_lang($label, $path_lang);
				$desc = (string)$report->DESCRIPTION;
				$desc = $this->retrieve_constant_lang($desc, $path_lang);
				$url = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=reports&page='.$url;
			}
			else
			{
				$module = (string)$report->MODULE;
				$moduleLabel = (string)$report->MODULE_LABEL;
				$path_lang = 'modules'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
				$tmp = $this->retrieve_constant_lang($moduleLabel, $path_lang);
				if($tmp != false)
				{
					$moduleLabel = $tmp;
				}
				$label = (string)$report->LABEL;
				$label = $this->retrieve_constant_lang($label, $path_lang);
				$desc = (string)$report->DESCRIPTION;
				$desc = $this->retrieve_constant_lang($desc, $path_lang);
				$url = $_SESSION['config']['businessappurl'].'index.php?display=true&module='.$module.'&page='.$url;
			}
			if(($enabled == 'true' && $only_enabled) || !$only_enabled)
			{
				if(!empty($id_report) && $id == $id_report)
				{
					$reports[$id] = array('id' => $id, 'label' => $label, 'desc' => $desc, 'enabled' => $enabled, 'menu' => $menu, 'url' => $url, 'module' => $module, 'module_label' => $moduleLabel);
					break;
				}
				elseif(empty($id_report))
				{
					$reports[$id] = array('id' => $id, 'label' => $label, 'desc' => $desc, 'enabled' => $enabled, 'menu' => $menu, 'url' => $url, 'module' => $module, 'module_label' => $moduleLabel);
					$i++;
				}
			}
		}
		return $reports;
	}

	public function get_arguments_for_report($str_to_analyse, $arg_to_find)
	{
		$str = $str_to_analyse;
		$arr_tmp = preg_split('/\$\$/', $str);
		for($i=0; $i<count($arr_tmp);$i++)
		{
			$arr_tmp2 = preg_split('/#/', $arr_tmp[$i]);
			if($arr_tmp2[0] == $arg_to_find)
			{
				return $arr_tmp2[1];
			}
		}
		return '';
	}
}
?>
