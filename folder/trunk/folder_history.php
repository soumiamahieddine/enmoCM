<?php
/*
*
*    Copyright 2008,2012 Maarch
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
* @brief    Displays folder history
*
* @file     folder_history.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  folder
*/

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_lists.php";
            
$core_tools = new core_tools();
$request    = new request();
$list       = new lists();

//
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header('', true, false);

?><body><?php
echo '<h2>' . _HISTORY . '</h2>';

$core_tools->load_js();

//Load list
if (isset($_SESSION['current_folder_id']) 
	&& ! empty($_SESSION['current_folder_id'])
) {
    $target = $_SESSION['config']['businessappurl'].'index.php?module=folder&page=history_list&id='.$_SESSION['current_folder_id'];
    $listContent = $list->loadList($target);
    echo $listContent;
}
?>
</body>
</html>