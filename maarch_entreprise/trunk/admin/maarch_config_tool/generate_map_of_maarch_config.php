<?php
/**
* File : generate_map_of_maarch_config.php
*
*
* @package  Maarch v3
* @version 3.0
* @since 06/2006
* @license GPL
* @author  Laurent Giovannoni  	<dev@maarch.org>
*/
session_name('maarch_v3');

$_SESSION['modules_calling_scripts'] = array();
$_SESSION['apps_calling_scripts'] = array();
function create_reports_file_php($report_text)
{
	$modules_services = $_SESSION['config']['businessapppath'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."maarch_config_tool".DIRECTORY_SEPARATOR."modules_services_config.php";
	echo $modules_services;
	if(file_exists($modules_services))
	{
		if(file_exists($modules_services.'_old'))
		{
			unlink ($modules_services."_old");
		}
		rename($modules_services, $modules_services.'_old');
	}
	$modules_services_opened = fopen($modules_services, "a");
	fwrite($modules_services_opened, $report_text);
	fclose($modules_services_opened);
}

function create_reports_file_html($report_text)
{
	$modules_services = $_SESSION['config']['businessapppath'].DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."maarch_config_tool".DIRECTORY_SEPARATOR."modules_services_config.html";
	echo $modules_services;
	if(file_exists($modules_services))
	{
		if(file_exists($modules_services.'_old'))
		{
			unlink ($modules_services."_old");
		}
		rename($modules_services, $modules_services.'_old');
	}
	$modules_services_opened = fopen($modules_services, "a");
	fwrite($modules_services_opened, $report_text);
	fclose($modules_services_opened);
}

function load_calling_scripts_of_apps_services($app_services)
{
	$count_calling_script = 0;
	for($i=0;$i<count($app_services);$i++)
	{
		for($k=0;$k<count($app_services[$i]['whereamiused']);$k++)
		{
			$_SESSION['apps_calling_scripts'][$count_calling_script] = str_replace("index.php?page=", "", $app_services[$i]['whereamiused'][$k]['page']);
			$count_calling_script++;
		}
		for($k=0;$k<count($app_services[$i]['processinbackground']);$k++)
		{
			$_SESSION['apps_calling_scripts'][$count_calling_script] = str_replace("index.php?page=", "", $app_services[$i]['processinbackground'][$k]['page']);
			$count_calling_script++;
		}
	}
	$_SESSION['apps_calling_scripts'] = array_unique($_SESSION['apps_calling_scripts']);
	sort($_SESSION['apps_calling_scripts']);
}

function load_calling_scripts_of_modules_services($modules_services)
{
	$count_calling_script = 0;
	foreach(array_keys($modules_services) as $value)
	{
		for($i=0;$i<count($modules_services[$value]);$i++)
		{
			for($k=0;$k<count($modules_services[$value][$i]['whereamiused']);$k++)
			{
				$_SESSION['modules_calling_scripts'][$count_calling_script] = str_replace("index.php?page=", "", $modules_services[$value][$i]['whereamiused'][$k]['page']);
				$count_calling_script++;
			}
			for($k=0;$k<count($modules_services[$value][$i]['processinbackground']);$k++)
			{
				$_SESSION['modules_calling_scripts'][$count_calling_script] = str_replace("index.php?page=", "", $modules_services[$value][$i]['processinbackground'][$k]['page']);
				$count_calling_script++;
			}
		}
	}
	$_SESSION['modules_calling_scripts'] = array_unique($_SESSION['modules_calling_scripts']);
	sort($_SESSION['modules_calling_scripts']);
}

function load_service_config_of_calling_scripts_modules($modules_services, $whereami, $report_modules_text)
{
	echo "\n\n**************************".$whereami."****************************\n\n";
	$report_modules_text .= "<table border='1' width='800px' style='background-color:#a9dbd8;'>";
	$report_modules_text .= "<tr>";
	$report_modules_text .= "<td align='center' style='color:#007583;'><b>".$whereami."</b></td>";
	$report_modules_text .= "</tr>";
	$report_modules_text .= "</table>";
	foreach(array_keys($modules_services) as $value)
	{
		for($i=0;$i<count($modules_services[$value]);$i++)
		{
			for($k=0;$k<count($modules_services[$value][$i]['whereamiused']);$k++)
			{
				if($modules_services[$value][$i]['whereamiused'][$k]['page'] == $whereami || $modules_services[$value][$i]['whereamiused'][$k]['page'] == "index.php?page=".$whereami)
				{
					$report_modules_text .= "<table border='1' width='800px'>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='160' style='color:blue;'><b>service id</b></td>";
					$report_modules_text .= "<td width='160' style='color:blue;'><b>type</b></td>";
					$report_modules_text .= "<td width='160' style='color:blue;'><b>nature</b></td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td>".$modules_services[$value][$i]['id']."</td>";
					$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['servicetype']."</td>";
					$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['nature']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "\nservice id : ".$modules_services[$value][$i]['id']."\n";
					echo "type : ".$modules_services[$value][$i]['servicetype']."\n";
					echo "nature : ".$modules_services[$value][$i]['whereamiused'][$k]['nature']."\n";
					if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "frame")
					{
						$report_modules_text .= "<table border='1' width='800px'><tr>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>frame width</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>frame height</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>frame border</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>frame scrolling</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>frame src</b></td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "<tr>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['width']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['height']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['border']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['scrolling']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['servicepage']."</td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "</table>";
						echo "frame width : ".$modules_services[$value][$i]['whereamiused'][$k]['width']."\n";
						echo "frame height : ".$modules_services[$value][$i]['whereamiused'][$k]['height']."\n";
						echo "frame border : ".$modules_services[$value][$i]['whereamiused'][$k]['border']."\n";
						echo "frame scrolling : ".$modules_services[$value][$i]['whereamiused'][$k]['scrolling']."\n";
						echo "frame src : ".$value."/".$modules_services[$value][$i]['servicepage']."\n";
					}
					if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "tab")
					{
						$report_modules_text .= "<table border='1' width='800px'><tr>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>tab order</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>tab label</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>tab src</b></td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "<tr>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['tab_order']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['tab_label']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['servicepage']."</td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "</table>";
						echo "tab order : ".$modules_services[$value][$i]['whereamiused'][$k]['tab_order']."\n";
						echo "tab label : ".$modules_services[$value][$i]['whereamiused'][$k]['tab_label']."\n";
						echo "tab src : ".$value."/".$modules_services[$value][$i]['servicepage']."\n";
					}
					if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "popup")
					{
						$report_modules_text .= "<table border='1' width='800px'><tr>";
						$report_modules_text .= "<td width='160'><b>popup link</b></td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['servicepage']."</td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "</table>";
						echo "popup link : ".$value."/".$modules_services[$value][$i]['servicepage']."\n";
					}
					if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "button")
					{
						$report_modules_text .= "<table border='1' width='800px'><tr>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>button link</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>page width</b></td>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>page height</b></td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "<tr>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['servicepage']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['width']."</td>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['whereamiused'][$k]['height']."</td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "</table>";
						echo "button link : ".$value."/".$modules_services[$value][$i]['servicepage']."\n";
						echo "page width : ".$modules_services[$value][$i]['whereamiused'][$k]['width']."\n";
						echo "page height : ".$modules_services[$value][$i]['whereamiused'][$k]['height']."\n";
					}
					if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "include")
					{
						$report_modules_text .= "<table border='1' width='800px'><tr>";
						$report_modules_text .= "<td width='160' style='color:green;'><b>include link</b></td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "<tr>";
						$report_modules_text .= "<td width='160'>".$modules_services[$value][$i]['servicepage']."</td>";
						$report_modules_text .= "</tr>";
						$report_modules_text .= "</table>";
						echo "include link : ".$value."/".$modules_services[$value][$i]['servicepage']."\n";
					}
					if($modules_services[$value][$i]['whereamiused'][$k]['nature'] == "listelement")
					{
						//
					}
				}
			}
			for($k=0;$k<count($modules_services[$value][$i]['processinbackground']);$k++)
			{
				if($modules_services[$value][$i]['processinbackground'][$k]['page'] == $whereami || $modules_services[$value][$i]['processinbackground'][$k]['page'] == "index.php?page=".$whereami)
				{
					$report_modules_text .= "<table border='1' width='800px'>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='50' style='color:brown;'><b>processorder</b></td>";
					$report_modules_text .= "<td width='300' style='color:brown;'><b>preprocess</b></td>";
					$report_modules_text .= "<td width='300' style='color:brown;'><b>postprocess</b></td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='50'>".$modules_services[$value][$i]['processinbackground'][$k]['processorder']."</td>";
					$report_modules_text .= "<td width='300'>".$modules_services[$value][$i]['processinbackground'][$k]['preprocess']."</td>";
					$report_modules_text .= "<td width='300'>".$modules_services[$value][$i]['processinbackground'][$k]['postprocess']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "\nprocessorder : ".$modules_services[$value][$i]['processinbackground'][$k]['processorder']."\n";
					echo "preprocess : ".$modules_services[$value][$i]['processinbackground'][$k]['preprocess']."\n";
					echo "postprocess : ".$modules_services[$value][$i]['processinbackground'][$k]['postprocess']."\n";
				}
			}
		}
	}
	$report_modules_text .= "</table><br><br><br>";
	return $report_modules_text;
}

function load_service_config_of_calling_scripts_apps($apps_services, $whereami, $report_modules_text)
{
	echo "\n\n**************************".$whereami."****************************\n\n";
	$report_modules_text .= "<table border='1' width='800px' style='background-color:#a9dbd8;'>";
	$report_modules_text .= "<tr>";
	$report_modules_text .= "<td align='center' style='color:#007583;'><b>".$whereami."</b></td>";
	$report_modules_text .= "</tr>";
	$report_modules_text .= "</table>";
	for($i=0;$i<count($apps_services);$i++)
	{
		for($k=0;$k<count($apps_services[$i]['whereamiused']);$k++)
		{
			if($apps_services[$i]['whereamiused'][$k]['page'] == $whereami || $apps_services[$i]['whereamiused'][$k]['page'] == "index.php?page=".$whereami)
			{
				$report_modules_text .= "<table border='1' width='800px'>";
				$report_modules_text .= "<tr>";
				$report_modules_text .= "<td width='160' style='color:blue;'><b>service id</b></td>";
				$report_modules_text .= "<td width='160' style='color:blue;'><b>type</b></td>";
				$report_modules_text .= "<td width='160' style='color:blue;'><b>nature</b></td>";
				$report_modules_text .= "</tr>";
				$report_modules_text .= "<tr>";
				$report_modules_text .= "<td>".$apps_services[$i]['id']."</td>";
				$report_modules_text .= "<td width='160'>".$apps_services[$i]['servicetype']."</td>";
				$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['nature']."</td>";
				$report_modules_text .= "</tr>";
				$report_modules_text .= "</table>";
				echo "\nservice id : ".$apps_services[$i]['id']."\n";
				echo "type : ".$apps_services[$i]['servicetype']."\n";
				echo "nature : ".$apps_services[$i]['whereamiused'][$k]['nature']."\n";
				if($apps_services[$i]['whereamiused'][$k]['nature'] == "frame")
				{
					$report_modules_text .= "<table border='1' width='800px'><tr>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>frame width</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>frame height</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>frame border</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>frame scrolling</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>frame src</b></td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['width']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['height']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['border']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['scrolling']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['servicepage']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "frame width : ".$apps_services[$i]['whereamiused'][$k]['width']."\n";
					echo "frame height : ".$apps_services[$i]['whereamiused'][$k]['height']."\n";
					echo "frame border : ".$apps_services[$i]['whereamiused'][$k]['border']."\n";
					echo "frame scrolling : ".$apps_services[$i]['whereamiused'][$k]['scrolling']."\n";
					echo "frame src : ".$value."/".$apps_services[$i]['servicepage']."\n";
				}
				if($apps_services[$i]['whereamiused'][$k]['nature'] == "tab")
				{
					$report_modules_text .= "<table border='1' width='800px'><tr>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>tab order</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>tab label</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>tab src</b></td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['tab_order']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['tab_label']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['servicepage']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "tab order : ".$apps_services[$i]['whereamiused'][$k]['tab_order']."\n";
					echo "tab label : ".$apps_services[$i]['whereamiused'][$k]['tab_label']."\n";
					echo "tab src : ".$value."/".$apps_services[$i]['servicepage']."\n";
				}
				if($apps_services[$i]['whereamiused'][$k]['nature'] == "popup")
				{
					$report_modules_text .= "<table border='1' width='800px'><tr>";
					$report_modules_text .= "<td width='160'><b>popup link</b></td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['servicepage']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "popup link : ".$value."/".$apps_services[$i]['servicepage']."\n";
				}
				if($apps_services[$i]['whereamiused'][$k]['nature'] == "button")
				{
					$report_modules_text .= "<table border='1' width='800px'><tr>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>button link</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>page width</b></td>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>page height</b></td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['servicepage']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['width']."</td>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['whereamiused'][$k]['height']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "button link : ".$value."/".$apps_services[$i]['servicepage']."\n";
					echo "page width : ".$apps_services[$i]['whereamiused'][$k]['width']."\n";
					echo "page height : ".$apps_services[$i]['whereamiused'][$k]['height']."\n";
				}
				if($apps_services[$i]['whereamiused'][$k]['nature'] == "include")
				{
					$report_modules_text .= "<table border='1' width='800px'><tr>";
					$report_modules_text .= "<td width='160' style='color:green;'><b>include link</b></td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "<tr>";
					$report_modules_text .= "<td width='160'>".$apps_services[$i]['servicepage']."</td>";
					$report_modules_text .= "</tr>";
					$report_modules_text .= "</table>";
					echo "include link : ".$value."/".$apps_services[$i]['servicepage']."\n";
				}
				if($apps_services[$i]['whereamiused'][$k]['nature'] == "listelement")
				{
					//
				}
			}
		}
		for($k=0;$k<count($apps_services[$i]['processinbackground']);$k++)
		{
			if($apps_services[$i]['processinbackground'][$k]['page'] == $whereami || $apps_services[$i]['processinbackground'][$k]['page'] == "index.php?page=".$whereami)
			{
				$report_modules_text .= "<table border='1' width='800px'>";
				$report_modules_text .= "<tr>";
				$report_modules_text .= "<tr>";
				$report_modules_text .= "<td width='50' style='color:brown;'><b>processorder</b></td>";
				$report_modules_text .= "<td width='300' style='color:brown;'><b>preprocess</b></td>";
				$report_modules_text .= "<td width='300' style='color:brown;'><b>postprocess</b></td>";
				$report_modules_text .= "</tr>";
				$report_modules_text .= "<tr>";
				$report_modules_text .= "<td width='50'>".$apps_services[$i]['processinbackground'][$k]['processorder']."</td>";
				$report_modules_text .= "<td width='300'>".$apps_services[$i]['processinbackground'][$k]['preprocess']."</td>";
				$report_modules_text .= "<td width='300'>".$apps_services[$i]['processinbackground'][$k]['postprocess']."</td>";
				$report_modules_text .= "</tr>";
				$report_modules_text .= "</table>";
				echo "\nprocessorder : ".$apps_services[$i]['processinbackground'][$k]['processorder']."\n";
				echo "preprocess : ".$apps_services[$i]['processinbackground'][$k]['preprocess']."\n";
				echo "postprocess : ".$apps_services[$i]['processinbackground'][$k]['postprocess']."\n";
			}
		}
	}
	$report_modules_text .= "</table><br><br><br>";
	return $report_modules_text;
}

$conf = $argv[1];
$path_server = $argv[2];
$xmlconfig = simplexml_load_file($conf);
$CONFIG = $xmlconfig->CONFIG;
$_SESSION['config']['businessapppath'] = (string) $CONFIG->businessapppath;
$_SESSION['config']['businessappurl'] = (string) $CONFIG->businessappurl;
$_SESSION['config']['img'] = (string) $CONFIG->img;
$_SESSION['config']['lang'] = (string) $CONFIG->lang;
$i=0;
foreach($xmlconfig->MODULES as $MODULES)
{
	$_SESSION['modules'][$i] = array("moduleid" => (string) $MODULES->moduleid);
	$i++;
}
/*
if(strtoupper(substr(PHP_OS, 0, 3)) != "WIN" && strtoupper(substr(PHP_OS, 0, 3)) != "WINNT")
{
	$_SESSION['slash_env'] = "/";
}
else
{
	$_SESSION['slash_env'] = "\\";
*/
}
if(!preg_match("/[/\\]$/",$path_server))
{
	$path_server = $path_server.DIRECTORY_SEPARATOR;
}
$_SESSION['history_keywords'] = array();
$_SESSION['pathtocore'] = $path_server.DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;
$_SESSION['pathtocoreclass'] = $path_server.DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR;
$_SESSION['pathtomodules'] = $path_server.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR;
require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
$core_tools = new core_tools();
$core_tools->load_app_services();
$core_tools->load_modules_config($_SESSION['modules'], true);
$core_tools->load_modules_services($_SESSION['modules']);
$core_tools->load_lang();
//$core_tools->show_array($_SESSION['config']);
//$core_tools->show_array($_SESSION['modules']);
//$core_tools->show_array($_SESSION['app_services']);
//$core_tools->show_array($_SESSION['modules_services']);
load_calling_scripts_of_modules_services($_SESSION['modules_services']);
//$core_tools->show_array($_SESSION['modules_calling_scripts']);
$report_modules_text = "<h1><img src='".$_SESSION['config']['businessappurl']."static.php?filename=picto_admin_b.gif' alt='' />"._XML_PARAM_SERVICE."</h1><div id='inner_content' class='clearfix'>";
$report_modules_text .= "<center><h3>"._MODULES_SERVICES."</h3></center><br>";
for($i=0;$i<count($_SESSION['modules_calling_scripts']);$i++)
{
	$report_modules_text = load_service_config_of_calling_scripts_modules($_SESSION['modules_services'], $_SESSION['modules_calling_scripts'][$i], $report_modules_text);
}
$report_modules_text .= "<center><h3>"._APPS_SERVICES."</h3></center><br>";
load_calling_scripts_of_apps_services($_SESSION['app_services']);
$core_tools->show_array($_SESSION['apps_calling_scripts']);
for($i=0;$i<count($_SESSION['apps_calling_scripts']);$i++)
{
	$report_modules_text = load_service_config_of_calling_scripts_apps($_SESSION['app_services'], $_SESSION['apps_calling_scripts'][$i], $report_modules_text);
}
$report_modules_text .= "</div>";
create_reports_file_php($report_modules_text);
create_reports_file_html($report_modules_text);
exit;
?>
