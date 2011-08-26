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
* @brief   Contains all the functions to use a maarch portal
*
* @file
* @author  Laurent Giovannoni  <dev@maarch.org>
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

require_once 'class_functions.php';

/**
* @brief   Contains all the functions to use a maarch portal
*
* @ingroup core
*/
class portal extends functions
{

	/**
	* Loads Maarch portal configuration into sessions  from an xml configuration file (core/xml/config.xml)
	*/
	public function build_config() {
		$xmlconfig = simplexml_load_file(dirname(__FILE__) 
                   . DIRECTORY_SEPARATOR . '..'
                   . DIRECTORY_SEPARATOR . 'xml' 
                   . DIRECTORY_SEPARATOR . 'config.xml');
		foreach($xmlconfig->CONFIG as $CONFIG) {
			$_SESSION['config']['corename'] = (string) $CONFIG->corename;
			$_SESSION['config']['corepath'] = (string) $CONFIG->corepath;
			$_SESSION['config']['tmppath'] = (string) $CONFIG->tmppath;
			$_SESSION['config']['unixserver'] = (string) $CONFIG->unixserver;
			$_SESSION['config']['defaultpage'] = (string) $CONFIG->defaultpage;
			$_SESSION['config']['defaultlang'] = (string) $CONFIG->defaultlanguage;
			if(isset($CONFIG->default_timezone) && !empty($CONFIG->default_timezone)) {
				$_SESSION['config']['default_timezone'] = (string) $CONFIG->default_timezone;
			} else {
				$_SESSION['config']['default_timezone'] = 'Europe/Paris';
			}
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
				$protocol = "https";
			else
				$protocol = "http";
			
			if($_SERVER['SERVER_PORT'] <> 443 && $protocol == "https") {
				$server_port = ":".$_SERVER['SERVER_PORT'];
			} elseif ($_SERVER['SERVER_PORT'] <> 80 && $protocol == "http") {
				$server_port = ":".$_SERVER['SERVER_PORT'];
			} else {
				$server_port = '';
			}
			if(isset($_SERVER['HTTP_X_FORWARDED_HOST']) && $_SERVER['HTTP_X_FORWARDED_HOST'] <> "") {
					$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
			}
			else {
					$host = $_SERVER['HTTP_HOST'];
			}
			$tmp = $host;
			if(!preg_match('/:[0-9]+$/', $host)) {
				$tmp =$host.$server_port;
			}
            
            if (isset($_SERVER['HTTP_X_BASE_URL']) 
                && $_SERVER['HTTP_X_BASE_URL'] <> ""
            ) {
                $uri = str_replace($_SERVER['HTTP_X_BASE_URL'], '', $_SERVER['SCRIPT_NAME']);
            } else {
                $uri = $_SERVER['SCRIPT_NAME'];
            }
            
            if (($appsInUri = strpos($uri, 'apps')) !== false)  {
                $uri = substr($uri, 0, $appsInUri);
            }
            $uri = str_replace('index.php', '', $uri);
            
            $_SESSION['config']['coreurl'] = $protocol . "://" . $tmp
                                           . $uri;
		}
		$i=0;
		foreach($xmlconfig->BUSINESSAPPS as $BUSINESSAPPS) {
			$_SESSION['businessapps'][$i] = array("appid" => (string) $BUSINESSAPPS->appid,	"comment" => (string) $BUSINESSAPPS->comment);
			$i++;
		}
	}

	/**
	* Unset session variabless
	*/
	public function unset_session() {
		unset($_SESSION['config']);
		unset($_SESSION['businessapps']);
	}
}
?>
