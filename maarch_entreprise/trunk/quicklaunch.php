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
    <!-- QUICK SEARCH ON COLLECTION -->
    <?php
    if (count($_SESSION['user']['security']) > 0 || $_SESSION['user']['UserId'] == 'superadmin') {
        ?>
        <form name="choose_query" id="choose_query"  method="post" action="" class="<?php echo $class_for_form;?>" >
            <div class="block">
                <h2><?php echo _QUICK_SEARCH;?> :</h2>
            </div>
            <br />
            <table>
                <tr>
                    <td style="text-align:right;width:35%" >
                        <select id="collection" name="collection" onchange="updateActionForm(this.options[this.selectedIndex].value);">
                            <?php
                            foreach ($_SESSION['user']['security'] as $key => $value) {
                                if ($key == 'letterbox_coll' || $key == 'business_coll' || $key == 'rm_coll' || $key == 'res_coll') {
                                    echo '<option id="' . $key . '" value="' . $key . '">' . $value['DOC']['label_coll'] .'</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        &nbsp;
                        <input id = "text" name = "welcome" id="welcome" size="42" autocomplete = "off">
                        <input type="hidden" name="meta[]" value="baskets_clause#baskets_clause#select_simple" />
                        <input type="hidden" name="meta[]" value="welcome#welcome#welcome" />
                        <input type="hidden" name="baskets_clause" value="true" />
                        <input class="button_search_adv_text" type="submit"  value="<?php echo _SEARCH; ?>" name ="Submit" >
                        <img src = "<?php echo $_SESSION['config']['businessappurl'];
                            ?>static.php?filename=picto_menu_search_small.gif" alt="<?php
                                echo _HELP_GLOBAL_SEARCH; ?>" title="<?php echo _HELP_GLOBAL_SEARCH; ?>" />
                    </td>
                <tr/>
            </table>
        </form>
        <script language="javascript">
            if ($('collection').value) {
                updateActionForm($('collection').value);
            }
            function updateActionForm(collId)
            {
                var targetAction;
                if (collId == 'letterbox_coll') {
                    targetAction = 'index.php?display=true&dir=indexing_searching&page=search_adv_result';
                } else if (collId == 'business_coll') {
                     targetAction = 'index.php?display=true&dir=indexing_searching&page=search_adv_result_business';
                } else if (collId == 'rm_coll') {
                     targetAction = 'index.php?display=true&module=records_management&page=search_adv_result_rm';
                } else if (collId == 'res_coll') {
                     targetAction = 'index.php?display=true&dir=indexing_searching&page=search_adv_result_invoices';
                } else {
                    window.alert('no global search for this collection');
                }
                //console.log(targetAction);
                $('choose_query').action = targetAction;
            }
        </script>
        <?php
    }
    ?>
</div>

<div id="welcome_box_right">
<div class="block">
    <h2><?php echo _QUICKLAUNCH;?> :</h2>
</div>
<br />
<?php
    $nb_max = 0;
    $menu_array = $_SESSION['menu'];
    $tag = false;
    foreach ($menu_array as $element) {
        if ($nb_max > 3) {
            echo '<br />';
        }
        if ($_SESSION['user']['UserId'] == 'superadmin' && !$tag) {
            $tag = true;
            echo '<a href="index.php?page=admin&amp;reinit=true"><div class="quiclaunch_div bighome_userinfo"><span>'._ADMIN.'</span></div></a>';
            $nb_max++;
            $displayed_admin = true;
        }
        if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'index_mlb' && $element['show'] == true &&
            (!isset($displayed_index_mlb) || $displayed_index_mlb <> true))) {
            echo '<a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=IndexingBasket"><div class="quiclaunch_div bighome_indexing"><span>'
                ._INDEXING_MLB.'</span></div></a>';
            $nb_max++;
            $displayed_index_mlb = true;
        }
        if ($element['id'] == 'adv_search_mlb' && $element['show'] == true &&
            (!isset($displayed_adv_search_mlb) || isset($displayed_index_mlb) && $displayed_adv_search_mlb <> true)) {
            echo '<a href="index.php?page=search_adv&amp;dir=indexing_searching&amp;reinit=true"><div class="quiclaunch_div bighome_search_adv"><span>'
                ._ADV_SEARCH_MLB.'</span></div></a>';
            $nb_max++;
            $displayed_adv_search_mlb = true;
        }
        //business collection
        if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'index_business' && $element['show'] == true &&
            (!isset($displayed_index_business) || $displayed_index_business <> true))) {
            echo '<a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=BusinessIndexation"><div class="quiclaunch_div bighome_indexing"><span>'
                ._INDEXING_BUSINESS.'</span></div></a>';
            $nb_max++;
            $displayed_index_business = true;
        }
        if ($element['id'] == 'adv_search_business' && $element['show'] == true &&
            (!isset($displayed_adv_search_business) || isset($displayed_index_business) && $displayed_adv_search_business <> true)) {
            echo '<a href="index.php?page=search_adv_business&amp;dir=indexing_searching&amp;reinit=true"><div class="quiclaunch_div bighome_search_adv"><span>' ._ADV_SEARCH_BUSINESS.'</span></div></a>';
            $nb_max++;
            $displayed_adv_search_business = true;
        }
        //rm collection
         if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'ArchiveTransferCreation' && $element['show'] == true &&
            (!isset($displayed_create_io) || $displayed_create_io <> true))) {
            echo '<a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=BRCreation"><div class="quiclaunch_div bighome_createio"><span>'
                ._ARCHIVE_TRANSFER_CREATE.'</span></div></a>';
            $nb_max++;
            $displayed_create_io = true;
        }
         if ($_SESSION['user']['UserId'] <> 'superadmin' && ($element['id'] == 'index_rm' && $element['show'] == true &&
            (!isset($displayed_index_rm) || $displayed_index_rm <> true))) {
            echo '<a href="index.php?page=view_baskets&amp;module=basket&amp;baskets=INDEXARCHIVE"><div class="quiclaunch_div bighome_indexing"><span>'
                ._INDEXING_RM.'</span></div></a>';
            $nb_max++;
            $displayed_index_rm = true;
        }
        if ($element['id'] == 'adv_search_rm' && $element['show'] == true &&
            (!isset($displayed_adv_search_rm) || isset($displayed_index_rm) && $displayed_adv_search_rm <> true)) {
            echo '<a href="index.php?page=search_adv_rm&amp;module=records_management&amp;reinit=true"><div class="quiclaunch_div bighome_search_adv"><span>'
            ._ADV_SEARCH_RM.'</span></div></a>';
            $nb_max++;
            $displayed_adv_search_rm = true;
        }
        //cold collection
        if ($element['id'] == 'adv_search_invoices' && $element['show'] == true &&
            (!isset($displayed_adv_search_invoice) || isset($displayed_adv_search_invoice) && $displayed_adv_search_invoice <> true)) {
            echo '<a href="index.php?page=search_adv_invoices&amp;dir=indexing_searching&amp;reinit=true"><div class="quiclaunch_div bighome_search_adv"><span>' ._ADV_SEARCH_INVOICES.'</span></div></a>';
            $nb_max++;
            $displayed_adv_search_invoice = true;
        }
        //physical archive
        if ($element['id'] == 'physical_archive' && $element['show'] == true && (!isset($displayed_physical_archive) || isset($displayed_physical_archive) && $displayed_physical_archive <> true)) {
            echo '<a href="index.php?page=boxes&amp;module=physical_archive&amp;reinit=true"><div class="quiclaunch_div bighome_physical_archive"><span>'
                ._PHYSICAL_ARCHIVE.'</span></div></a>';
            $nb_max++;
            $displayed_physical_archive = true;
        }
    }
    ?>
    <a href="index.php?page=modify_user&amp;admin=users&amp;reinit=true">
        <div class="quiclaunch_div bighome_userinfo">
            <span><?php echo _MY_INFO; ?></span>
        </div>
    </a>
</div>
