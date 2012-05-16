<?php

/*
*   Copyright 2008-2011 Maarch
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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->test_service('quicklaunch', "apps");
?>
<div id="welcome_box_left_quick_lunch">
    
    <form name="choose_query" id="choose_query"  method="post" action="<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_result';?>" class="<?php echo $class_for_form;?>" >
    <!--<form name="frmsearch2" action="<?php  { echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_result';}?>"  id="frmsearch2" class="<?php echo $class_for_form; ?>">-->
      <div class="block">
        <!--<b><?php echo _QUICKLAUNCH; ?> : </b><br/>-->
       <tr>
       <td>
        <em><?php echo _GLOBAL_SEARCH; ?></em>
         </td>  
		<input id = "text" name = "welcome" id="welcome" size="40" autocomplete = "off">
		<input type="hidden" name="meta[]" value="welcome#welcome#welcome" />
		<input class="button_search_adv_text" type="submit"  value="<?php echo _SEARCH; ?>" name ="Submit" >
		<img src = "<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_menu_search_small.gif" alt="<?php echo _HELP_GLOBAL_SEARCH; ?>" title="<?php echo _HELP_GLOBAL_SEARCH; ?>" />
		
		<tr/>
	</div>   
    </form>
    <div class="blank_space">&nbsp;</div>
    <?php
    $nb_max = 0;
    $menu_array = $_SESSION['menu'];
    $tag = false;
    foreach ($menu_array as $element) {
        if ($nb_max < 3) {
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
                echo '<div class="quiclaunch_div bighome_search_adv"><a href="index.php?page=search_adv&amp;dir=indexing_searching&amp;reinit=true"><span>'._ADV_SEARCH_TITLE.'</span></a></div>';
                $nb_max++;
                $displayed_adv_search_mlb = true;
            }
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
