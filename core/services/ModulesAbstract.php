<?php

/**
*   @copyright 2017 Maarch
*
*   This file is part of Maarch Framework.
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

require_once 'core/services/Abstract.php';

class Core_ModulesAbstract_Service extends Core_Abstract_Service {
	public static function getApiMethod() {
		$aMethod = parent::getApiMethod();
		$aMethod['getList'] = 'getList';
		return $aMethod;
	}
	/**
	 * Renvoie la liste des modules
	 * @throw \Exception $e
	 * @param array $args
	 * @return array $aModules
	 **/
	public static function getList(array $args = []) {
		if ( ! file_exists('modules') ) {
			throw new \Exception('path modules not-found');
		}
		$aDir = scandir('modules');
		$aModules = [];
		foreach ($aDir as $dir) {
			if ( '.'==$dir[0]) continue;
			if ( !is_dir("modules/$dir") ) continue;
			$aModules[$dir] = $dir;
		}
		return $aModules;
	}
	/**
	 * Renvoie la liste des services
	 * @param array $args
	 * 	- require : inclue directement la definition du service si ce n'ai pas fait
	 * @return array $aModules
	 **/
	public static function getServicesList(array $args = []) {
		// Initialisation :
		$aServices = [];
		$aServices['apps'] = [];
		// Recherche dans Apps :
		foreach ([
			$_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']."/apps/maarch_entreprise/services",
			"apps/maarch_entreprise/services",
			] as $sPathModuleService) {
			if ( is_dir($sPathModuleService) ) {
				$aDir = scandir($sPathModuleService);
				foreach ($aDir as $dir) {
					if ( '.'==$dir[0]) continue;
					if ( preg_match('/svn-commit/', $dir) ) continue;
					if ( !is_file("$sPathModuleService/$dir") ) continue;
					$sService = preg_replace('/\.php$/', '', $dir);
					$sService = 'Apps_'.ucfirst($sService).'_Service';
					if ( !empty($aServices['apps'][$sService]) ) continue; // Déjà fait
					if ( !class_exists($sService) && !empty($args['require']) ) require_once "$sPathModuleService/$dir";
					$aServices['apps'][$sService] = $sService;
				}
			}
		}
		if ( empty($aServices['apps']) ) {
			unset($aServices['apps']);
		}
		// Recherche dans Core :
		$aServices['core'] = [];
		foreach ([
			$_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']."/core/services",
			"core/services",
			] as $sPathModuleService) {
			if ( is_dir($sPathModuleService) ) {
				$aDir = scandir($sPathModuleService);
				foreach ($aDir as $dir) {
					if ( '.'==$dir[0]) continue;
					if ( preg_match('/svn-commit/', $dir) ) continue;
					if ( !is_file("$sPathModuleService/$dir") ) continue;
					$sService = preg_replace('/\.php$/', '', $dir);
					$sService = 'Core_'.ucfirst($sService).'_Service';
					if ( !empty($aServices['core'][$sService]) ) continue; // Déjà fait
					if ( !class_exists($sService) && !empty($args['require']) ) require_once "$sPathModuleService/$dir";
					$aServices['core'][$sService] = $sService;
				}
			}
		}
		if ( empty($aServices['core']) ) {
			unset($aServices['core']);
		}
		// Recherche dans tous les modules :
		$aModules = self::getList();
		foreach ($aModules as $sModule) {
			// Recherche dans maarch + custom :
			foreach ([
				$_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']."/modules/{$sModule}/services",
				"modules/{$sModule}/services",
				] as $sPathModuleService) {
				if ( is_dir($sPathModuleService) ) {
					$aDir = scandir($sPathModuleService);
					foreach ($aDir as $dir) {
						if ( '.'==$dir[0]) continue;
						if ( preg_match('/svn-commit/', $dir) ) continue;
						if ( !is_file("$sPathModuleService/$dir") ) continue;
						$sService = preg_replace('/\.php$/', '', $dir);
						$sService = ucfirst($sModule).'_'.ucfirst($sService).'_Service';
						if ( !empty($aServices[$sModule][$sService]) ) continue; // Déjà fait
						if ( !class_exists($sService) && !empty($args['require']) ) require_once "$sPathModuleService/$dir";
						$aServices[$sModule][$sService] = $sService;
					}
				}
			}
			if ( empty($aServices[$sModule]) ) {
				unset($aServices[$sModule]);
			}
		}
		// Retour :
		return $aServices;
	}
}