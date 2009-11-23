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
* @brief Modify the user in the database after the form
*
*
* @file
* @author  Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
//include('core/init.php');

//require_once("core/class/class_functions.php");
//require("core/class/class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_admin('admin_users', 'apps');
//here we loading the lang vars
$core_tools->load_lang();
//require_once("core/class/class_db.php");
require("apps".DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_users.php");

$users = new users();
$users->addupusers("up");
?>
