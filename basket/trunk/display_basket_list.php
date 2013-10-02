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
if (!isset($_REQUEST['noinit'])) {
    $_SESSION['current_basket'] = array();
}
/************/
$bask = new basket();
$db = new dbquery();
$db->connect();

?>
<!--<div id="welcome_box_right">-->

<div id="welcome_box_left_baskets">
<br />
<?php
if ($core_tools->test_service('display_basket_list','basket', false)) {
        if (
            isset($_SESSION['user']['baskets'])
            && count($_SESSION['user']['baskets']) > 0
        ) {
            $collWithUserBaskets = array();
            $count = count($_SESSION['collections']);
            for ($cptColl=0;$cptColl<$count;$cptColl++) {
                if ($bask->isUserHasBasketInCollection($_SESSION['collections'][$cptColl]['id'])) {
                    array_push(
                        $collWithUserBaskets, 
                        array(
                            'coll_id' => $_SESSION['collections'][$cptColl]['id'],
                            'coll_label' => $_SESSION['collections'][$cptColl]['label']
                        )
                    );
                }
            }
            //$core_tools->show_array($collWithUserBaskets);
            ?>
            <div class="block">
                <h2><?php echo _MY_BASKETS; ?> : </h2>
            </div>
            <br />
            <ul class="basket_elem">
            <?php
            $countColl = count($collWithUserBaskets);
            for ($cpt=0;$cpt<$countColl;$cpt++) {
                echo '<h4><img src="' . $_SESSION['config']['businessappurl']
                    . 'static.php?filename=box.gif" alt=""/>&nbsp;'
                    . $collWithUserBaskets[$cpt]['coll_label'] . '</h4>';
                $abs_basket = false;
                for ($i=0;$i<count($_SESSION['user']['baskets']);$i++) {
                    if ($_SESSION['user']['baskets'][$i]['is_visible'] === 'Y' 
                        && $_SESSION['user']['baskets'][$i]['id_page'] <> 'redirect_to_action'
                        && $_SESSION['user']['baskets'][$i]['coll_id'] == $collWithUserBaskets[$cpt]['coll_id'] 
                    ) {
                        if (
                            $_SESSION['user']['baskets'][$i]['abs_basket'] == true
                            && !$abs_basket
                        ) {
                            echo '</ul><h3>' . _OTHER_BASKETS
                                . ' :</h3><ul class="basket_elem">';
                            $abs_basket = true;
                        }
                        $nb = '';
                        if (
                            preg_match('/^CopyMailBasket/', $_SESSION['user']['baskets'][$i]['id'])
                            && !empty($_SESSION['user']['baskets'][$i]['view'])
                        ) {
                            $db->query('select res_id from '
                                . $_SESSION['user']['baskets'][$i]['view']
                                . ' where ' . $_SESSION['user']['baskets'][$i]['clause']
                            );
                            $nb = $db->nb_result();
                        }
                        if ($nb <> 0) {
                            $nb = '(' . $nb . ')';
                        } else {
                            $nb = '';
                        }
                        if ($_SESSION['user']['baskets'][$i]['id_page'] <> 'redirect_to_action') {
                            if (
                                $core_tools->is_module_loaded('folder') 
                                && $_SESSION['user']['baskets'][$i]['is_folder_basket'] == 'Y'
                            ) {
                                echo '<li><a href="'
                                    . $_SESSION['config']['businessappurl']
                                    . 'index.php?page=view_baskets&amp;module=basket&amp;baskets='
                                    . $_SESSION['user']['baskets'][$i]['id']
                                    . '"><img src="' . $_SESSION['config']['businessappurl']
                                    . 'static.php?filename=basket_folders_b.gif&amp;module=folder" alt=""/> '
                                    . $_SESSION['user']['baskets'][$i]['name']
                                    . '  <b><span id="nb_' . $_SESSION['user']['baskets'][$i]['id'] 
                                    . '" name="nb_' . $_SESSION['user']['baskets'][$i]['id']
                                    . '"><img src="' . $_SESSION['config']['businessappurl']
                                    . 'static.php?filename=loading.gif" width="14" height="14" alt="loading" title="loading"/>'
                                    . '</span></b></a></li>';
                            } else {
                                echo '<li><a href="'
                                    . $_SESSION['config']['businessappurl']
                                    . 'index.php?page=view_baskets&amp;module=basket&amp;baskets='
                                    . $_SESSION['user']['baskets'][$i]['id']
                                    . '"><img src="' . $_SESSION['config']['businessappurl']
                                    . 'static.php?filename=manage_baskets_off.gif&amp;module=basket" alt=""/> '
                                    . $_SESSION['user']['baskets'][$i]['name']
                                    . '  <b><span id="nb_' . $_SESSION['user']['baskets'][$i]['id'] 
                                    . '" name="nb_' . $_SESSION['user']['baskets'][$i]['id']
                                    . '"><img src="' . $_SESSION['config']['businessappurl']
                                    . 'static.php?filename=loading.gif" width="14" height="14" alt="loading" title="loading"/>'
                                    . '</span></b></a></li>';
                            }
                        }
                    }
                }
                echo '<hr />';
            }
            ?>
            </ul>
            <?php
        }
        ?>
    <div class="blank_space">&nbsp;</div>
    <?php
}
?>
</div>

<script language="javascript">
    var basketsSpan = $('welcome_box_left_baskets').select('span');
    //console.log(basketsSpan);
    var path_manage_script = '<?php echo $_SESSION["config"]["businessappurl"];?>'
        + 'index.php?display=true&module=basket&page=ajaxNbResInBasket';
    for (i=0;i<basketsSpan.length;i++) {
        //console.log(basketsSpan[i]);
        
        new Ajax.Request(path_manage_script,
        {
            method:'post',
            parameters: { id_basket : basketsSpan[i].id},
            onSuccess: function(answer)
            {
                eval('response = '+answer.responseText);
                $(response.idSpan).innerHTML = '<b>(' + response.nb + ')</b>';
            }
        });
    }
</script>
