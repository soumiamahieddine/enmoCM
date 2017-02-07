<?php
/*
*    Copyright 2008-2017 Maarch
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
 * Ce fichier a placer dans custom/[CUSTOM]/define_local.php permet de mettre des definitions spécifique au serveur
 **/
if ( !empty($_SESSION['custom_override_id']) && file_exists("{$_SESSION['config']['corepath']}custom/{$_SESSION['custom_override_id']}/define_local.php") ) {
	require_once "{$_SESSION['config']['corepath']}custom/{$_SESSION['custom_override_id']}/define_local.php";
}

/**
 * Ce fichier a mettre en paralléle dans le custom, permet de mettre des definitions spécifique au custom (exemple langue ou remplacement de define de ce fichier de base)
 **/
require_once 'apps/maarch_entreprise/define_custom.php';

if ( ! defined('DEBUG') ) {
	define('DEBUG', false);
}
if ( DEBUG ) {
	if (!ini_get('display_errors')) {
	    ini_set('display_errors', 1);
	}
	if ( defined('E_ERROR_REPORTING') ) {
		error_reporting(E_ERROR_REPORTING);
	} else {
		error_reporting(E_ALL);
	}
}
if ( @$_SERVER['HTTP_USER_AGENT'] == 'TestU' ) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL & ~ E_NOTICE);
}

if ( ! defined('HEADER_USER_UID_SALT') ) {
	define('HEADER_USER_UID_SALT', '%s');
}

// BaseUrl for Web side :
if ( ! defined('URL_IMG') ){
	define('URL_IMG', '');
}

function return_bytes ($size_str)
{
    switch (substr ($size_str, -1))
    {
        case 'M': case 'm': return (int)$size_str * 1048576;
        case 'K': case 'k': return (int)$size_str * 1024;
        case 'G': case 'g': return (int)$size_str * 1073741824;
        default: return $size_str;
    }
}
// hom many file may we can upload :
if ( ! defined('UPLOAD_FILE_LIMIT_COUNT') ){
	define('UPLOAD_FILE_LIMIT_COUNT', 100);
}
if ( ! defined('UPLOAD_FILES_MAX_SIZE') ) {
	define('UPLOAD_FILES_MAX_SIZE', min(
		return_bytes(ini_get('upload_max_filesize')),
		return_bytes(ini_get('post_max_size'))
		));
}
if ( ! defined('UPLOAD_FILE_MAX_SIZE') ) {
	define('UPLOAD_FILE_MAX_SIZE', min(
		UPLOAD_FILES_MAX_SIZE,
		return_bytes(ini_get('upload_max_filesize')),
		return_bytes(ini_get('post_max_size'))
		));
}

if ( ! defined('HEADER_USER_UID') ) {
	define('HEADER_USER_UID', 'UID');
}


if ( ! defined('DEFAULT_PAGE') ) {
    define('DEFAULT_PAGE', 'index.php');
}
