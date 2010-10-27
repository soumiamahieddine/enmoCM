<?php

/*
*   Copyright 2010 Maarch
*
*  	This file is part of Maarch Framework.
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
* @brief Maarch web service root
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

//include of classes
include_once("core".DIRECTORY_SEPARATOR."init.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_portal.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_core_tools.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."web_service".DIRECTORY_SEPARATOR."class_web_service.php");
//load Maarch session vars
$portal = new portal();
$portal->unset_session();
$portal->build_config();
$coreTools = new core_tools();
$_SESSION["custom_override_id"] = $coreTools->get_custom_id();
$coreTools->build_core_config("core".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml");
$_SESSION["config"]["app_id"] = $_SESSION["businessapps"][0]["appid"];
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION["businessapps"][0]["appid"].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
$businessAppTools = new business_app_tools();
$businessAppTools->build_business_app_config();
$coreTools->load_modules_config($_SESSION['modules']);
//load webservice engine
$webService = new webService();
//http Authentication
if($webService->authentication()) {
	$business = new business_app_tools();
	$_SESSION['user']['UserId'] = $_SERVER["PHP_AUTH_USER"];
	$business->load_app_var_session();
	//retrieve Maarch web service catalog
	$webService->WSCoreCatalog();
	$webService->WSAppsCatalog();
	$webService->WSModulesCatalog();
	$webService->WScustomCatalog();
	//launch webservice engine
	$webService->launchWs();
} else {
	header("WWW-Authenticate: Basic realm=\"Maarch WebServer Engine\"");
	if (preg_match("/Microsoft/", $_SERVER["SERVER_SOFTWARE"])) {
		header("Status: 401 Unauthorized");
		exit();
	} else {
		header("HTTP/1.0 401 Unauthorized");
		echo "Access denied";
		exit();
	}
}
?>
