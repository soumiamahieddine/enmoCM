<?php

/*
*   Copyright 2008-2015 Maarch
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

/*
* @brief  Lists the baskets for the current user (service)
*
*
* @file
* @author Loic Vinet <dev@maarch.org>
* @author Claire Figueras <dev@maarch.org>
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/

require_once('core/class/class_request.php');
require_once('modules/basket/class/class_modules_tools.php');
$core_tools = new core_tools();
$core_tools->test_user();

$_SESSION['basket_used'] = '';
if (!isset($_REQUEST['noinit'])) {
    $_SESSION['current_basket'] = array();
}
/************/
$bask = new basket();
$db = new Database();

?>

<div id="welcome_box_left_baskets">
    <br />
    <?php
    if ($core_tools->test_service('display_basket_list', 'basket', false)) {
        // Refresh personnal basket info
        $bask->load_module_var_session($_SESSION['user']); ?>
    <div class="blank_space">&nbsp;</div>
    <?php
    }
?>
</div>