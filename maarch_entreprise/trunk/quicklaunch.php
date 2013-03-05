<?php

/*
*   Copyright 2008-2013 Maarch
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
* @brief  Access to the baskets
*
*
* @file
* @author Loic Vinet <dev@maarch.org>
* @author Claire Figueras <dev@maarch.org>
* @author Lauren Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

require_once('core/class/class_request.php');
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->test_service('quicklaunch', "apps");
?>
<div id="welcome_box_left_quick_lunch">
    <!-- SEARCH ON LETTERBOX COLLECTION -->
    <?php 
    if (isset($_SESSION['user']['security']['letterbox_coll']) || $_SESSION['user']['UserId'] == 'superadmin') {
        ?> 
        <form name="choose_query" id="choose_query"  method="post" action="<?php 
            echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_result';
                ?>" class="<?php echo $class_for_form;?>" >
            <div class="block">
                <table>
                    <tr>
                        <td width="40%">
                            <em><?php echo _GLOBAL_SEARCH; ?></em>
                        </td> 
                        <td>
                            <input id = "text" name = "welcome" id="welcome" size="27" autocomplete = "off">
                            <input type="hidden" name="meta[]" value="welcome#welcome#welcome" />
                            <input class="button_search_adv_text" type="submit"  value="<?php echo _SEARCH; ?>" name ="Submit" >
                            <img src = "<?php echo $_SESSION['config']['businessappurl'];
                                ?>static.php?filename=picto_menu_search_small.gif" alt="<?php 
                                    echo _HELP_GLOBAL_SEARCH; ?>" title="<?php echo _HELP_GLOBAL_SEARCH; ?>" />
                        </td>
                    <tr/>
                </table>
            </div>
        </form>
        <div class="blank_space">&nbsp;</div>
        <?php
    }
    ?>
    <!-- SEARCH ON BUSINESS COLLECTION -->
    <?php 
    if (isset($_SESSION['user']['security']['business_coll']) || $_SESSION['user']['UserId'] == 'superadmin') {
        ?> 
        <form name="choose_query" id="choose_query"  method="post" action="<?php 
            echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_result_business';
                ?>" class="<?php echo $class_for_form;?>" >
            <div class="block">
                <table>
                    <tr>
                        <td width="40%">
                            <em><?php echo _GLOBAL_SEARCH_BUSINESS; ?></em>
                        </td> 
                        <td>
                            <input id = "text" name = "welcome" id="welcome" size="27" autocomplete = "off">
                            <input type="hidden" name="meta[]" value="welcome#welcome#welcome" />
                            <input class="button_search_adv_text" type="submit"  value="<?php echo _SEARCH; ?>" name ="Submit" >
                            <img src = "<?php echo $_SESSION['config']['businessappurl'];
                                ?>static.php?filename=picto_menu_search_small.gif" alt="<?php 
                                    echo _HELP_GLOBAL_SEARCH; ?>" title="<?php echo _HELP_GLOBAL_SEARCH; ?>" />
                        </td>
                    <tr/>
                </table>
            </div>
        </form>
        <div class="blank_space">&nbsp;</div>
        <?php
    }
    $nb_max = 0;
    $menu_array = $_SESSION['menu'];
    $tag = false;
    foreach ($menu_array as $element) {
        if ($nb_max > 3) {
            echo '<br />';
        }
        if ($_SESSION['user']['UserId'] == 'superadmin' && !$tag) {
            $tag = true;
            echo '<div class="quiclaunch_div bighome_userinfo"><a href="index.php?page=admin&amp;reinit=true"><span>'._ADMIN.'</span></a></div>';
            $nb_max++;
            $displayed_admin = true;
        }
        if ($element['id'] == 'physical_archive' && $element['show'] == true && (!isset($displayed_physical_archive) || isset($displayed_physical_archive) && $displayed_physical_archive <> true)) {
            echo '<div class="quiclaunch_div bighome_physical_archive"><a href="index.php?page=boxes&amp;module=physical_archive&amp;reinit=true"><span>'._PHYSICAL_ARCHIVE.'</span></a></div>';
            $nb_max++;
            $displayed_physical_archive = true;
        }
        if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'index_mlb' && $element['show'] == true &&
            (!isset($displayed_index_mlb) || $displayed_index_mlb <> true))) {
            echo '<div class="quiclaunch_div bighome_indexing"><a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=IndexingBasket"><span>'._INDEXING_MLB.'</span></a></div>';
            $nb_max++;
            $displayed_index_mlb = true;
        }
        if ($element['id'] == 'adv_search_mlb' && $element['show'] == true &&
            (!isset($displayed_adv_search_mlb) || isset($displayed_index_mlb) && $displayed_adv_search_mlb <> true)) {
            echo '<div class="quiclaunch_div bighome_search_adv"><a href="index.php?page=search_adv&amp;dir=indexing_searching&amp;reinit=true"><span>'._ADV_SEARCH_MLB.'</span></a></div>';
            $nb_max++;
            $displayed_adv_search_mlb = true;
        }
        //business collection
        if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'index_business' && $element['show'] == true &&
            (!isset($displayed_index_business) || $displayed_index_business <> true))) {
            echo '<div class="quiclaunch_div bighome_indexing"><a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=businessIndexation"><span>'._INDEXING_BUSINESS.'</span></a></div>';
            $nb_max++;
            $displayed_index_business = true;
        }
        if ($element['id'] == 'adv_search_business' && $element['show'] == true &&
            (!isset($displayed_adv_search_business) || isset($displayed_index_business) && $displayed_adv_search_business <> true)) {
            echo '<div class="quiclaunch_div bighome_search_adv"><a href="index.php?page=search_adv_business&amp;dir=indexing_searching&amp;reinit=true"><span>'._ADV_SEARCH_BUSINESS.'</span></a></div>';
            $nb_max++;
            $displayed_adv_search_business = true;
        }
        //rm collection
         if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'index_rm' && $element['show'] == true &&
            (!isset($displayed_index_rm) || $displayed_index_rm <> true))) {
            echo '<div class="quiclaunch_div bighome_indexing"><a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=INDEXARCHIVE"><span>'._INDEXING_RM.'</span></a></div>';
            $nb_max++;
            $displayed_index_rm = true;
        }
        if ($element['id'] == 'adv_search_rm' && $element['show'] == true &&
            (!isset($displayed_adv_search_rm) || isset($displayed_index_rm) && $displayed_adv_search_rm <> true)) {
            echo '<div class="quiclaunch_div bighome_search_adv"><a href="index.php?page=search_adv_rm&amp;module=records_management&amp;reinit=true"><span>'._ADV_SEARCH_RM.'</span></a></div>';
            $nb_max++;
            $displayed_adv_search_rm = true;
        }
    }
    if ($nb_max <3) {
        ?>
        <div class="quiclaunch_div bighome_userinfo">
            <a href="index.php?page=modify_user&amp;admin=users&amp;reinit=true">
                <span><?php echo _MY_INFO; ?></span>
            </a>
        </div>
        <?php
    }
    ?>
    <div class="blank_space">&nbsp;</div>
</div>
