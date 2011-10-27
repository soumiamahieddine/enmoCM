<?php
/* View */
if ($mode == "list") {
	$listShow = new list_show();
    $listShow->admin_list(
        $docserverLocationsList['tab'], count($docserverLocationsList['tab']),
        $docserverLocationsList['title'], 'docserver_location_id',
        'docserver_locations_management_controler&mode=list',
        'docservers', 'docserver_location_id', true,
        $docserverLocationsList['page_name_up'],
        $docserverLocationsList['page_name_val'],
        $docserverLocationsList['page_name_ban'],
        $docserverLocationsList['page_name_del'],
        $docserverLocationsList['page_name_add'],
        $docserverLocationsList['label_add'], false, false,
        _ALL_DOCSERVER_LOCATIONS, _DOCSERVER_LOCATION,
        $_SESSION['config']['businessappurl']
        . 'static.php?filename=favicon.png', false, true, false, true,
        $docserverLocationsList['what'], true,
        $docserverLocationsList['autoCompletionArray']
    );
} elseif ($mode == "up" || $mode == "add") {
	$coreTools = new core_tools();
	$func = new functions();
    ?>
    <h1><img src="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=favicon.png" alt="" />
        <?php
    if ($mode == "add") {
        echo _DOCSERVER_LOCATION_ADDITION;
    } elseif ($mode == "up") {
        echo _DOCSERVER_LOCATION_MODIFICATION;
    }
        ?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
        <br><br>
        <?php
    if ($state == false) {
        echo "<br /><br />" . _THE_DOCSERVER_LOCATION . " " . _UNKOWN
            . "<br /><br /><br /><br />";
    } else {
        ?>
        <div id="inner_content" class="clearfix">
        <?php
        if ($mode == "up") {
            if (count($docservers) > 0) {
                ?><div onclick="new Effect.toggle('users_list', 'blind', {delay:0.2});return false;" >
                &nbsp;<img src="<?php
                echo $_SESSION['config']['businessappurl'];
                ?>static.php?filename=manage_doctypes_b.gif" alt="" /><i><?php
                echo _SEE_DOCSERVERS_LOCATION;
                ?></i> <img src="<?php
                echo $_SESSION['config']['businessappurl'];
                ?>static.php?filename=plus.png" alt="" />
                    <span class="lb1-details">&nbsp;</span>
                </div>
                <div class="desc" id="users_list" style="display:none;">
                    <div class="ref-unit">
                        <table cellpadding="0" cellspacing="0" border="0" class="listingsmall" summary="">
                            <thead>
                                <tr>
                                    <th><?php  echo _DOCSERVER_ID;?></th>
                                    <th ><?php  echo _DEVICE_LABEL;?></th>
                                    <th ><?php  echo _DOCSERVER_TYPE_ID;?></th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                $color = ' class="col"';

                for ($i = 0; $i < count($docservers); $i ++) {
                    if ($color == ' class="col"') {
                        $color = '';
                    } else {
                        $color = ' class="col"';
                    }
                    ?>
                    <tr <?php  echo $color; ?> >
                        <td style="width:25%;"><?php
                    echo $docservers[$i]->__get('docserver_id');
                    ?></td>
                    <td style="width:25%;"><?php
                    echo $docservers[$i]->__get('device_label');
                    ?></td>
                    <td style="width:25%;"><?php
                    echo $docservers[$i]->__get('docserver_type_id');
                    ?></td>
                    <td ><?php
                    if ($coreTools->test_service('admin_docservers', 'apps', false)
                    ) {
                        ?>
                        <a class="change" href="<?php
                        echo $_SESSION['config']['businessappurl']
                            . 'index.php?page=docservers_management_controler'
                            . '&amp;mode=up&amp;admin=docservers&amp;id='
                            . $docservers[$i]->__get('docserver_id');
                        ?>"  title="<?php echo _GO_MANAGE_DOCSERVER;?>"><i><?php
                        echo _GO_MANAGE_DOCSERVER;
                        ?></i></a><?php
                    }
                    ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
                </table>
                <br/>
                </div>
                </div>
                <?php
            }
        }
        ?>
        <br/><br/>
        <form name="formdocserver" method="post" class="forms" action="<?php
        echo $_SESSION['config']['businessappurl'] . "index.php?display=true"
            . "&page=docserver_locations_management_controler&admin=docservers"
            . "&mode=" . $mode;
        ?>">
            <input type="hidden" name="display" value="value" />
            <input type="hidden" name="admin" value="docservers" />
            <input type="hidden" name="page" value="docserver_locations_management_controler" />
            <input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
            <input type="hidden" name="order" id="order" value="<?php
        if (isset($_REQUEST['order'])) {
            echo $_REQUEST['order'];
        }
        ?>" />
            <input type="hidden" name="order_field" id="order_field" value="<?php
        if (isset($_REQUEST['order_field'])) {
            echo $_REQUEST['order_field'];
        }
        ?>" />
            <input type="hidden" name="what" id="what" value="<?php
        if (isset($_REQUEST['what'])) {
            echo $_REQUEST['what'];
        }
        ?>" />
            <input type="hidden" name="start" id="start" value="<?php
        if (isset($_REQUEST['start'])) {
            echo $_REQUEST['start'];
        }
        ?>" />
        <p>
            <label for="id"><?php echo _DOCSERVER_LOCATION_ID; ?> : </label>
            <input name="id" type="text"  id="id" value="<?php
        if (isset(
            $_SESSION['m_admin']['docserver_locations']['docserver_location_id']
        )
        ) {
            echo $func->show_str(
                $_SESSION['m_admin']['docserver_locations']['docserver_location_id']
            );
        }
        ?>" <?php
        if ($mode == "up") {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/><span class="red_asterisk">*</span>
        </p>
        <p class = "bulle">
            <label for="ipv4"><?php echo _IPV4; ?> <small>(e.g: 127.0.0.1)</small> : </label>
            <input name="ipv4" type="text"  id="ipv4" value="<?php
        if (isset($_SESSION['m_admin']['docserver_locations']['ipv4'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docserver_locations']['ipv4']
            );
        }
        ?>"/>
            <span class="red_asterisk">*</span>

        </p>
        <p class="bulle">
            <label for="ipv6"><?php echo _IPV6; ?> <small>(e.g: 2001:db8:0:85a3::ac1f:8001)</small> : </label>
            <input name="ipv6" type="text"  id="ipv6" value="<?php
        if (isset($_SESSION['m_admin']['docserver_locations']['ipv6'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docserver_locations']['ipv6']
            );
        }
        ?>"/>
            <span>&nbsp;</span>
        </p>
        <p>
            <label for="net_domain"><?php echo _NET_DOMAIN; ?> : </label>
            <input name="net_domain" type="text"  id="net_domain" value="<?php
        if (isset($_SESSION['m_admin']['docserver_locations']['net_domain'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docserver_locations']['net_domain']
            );
        }
        ?>"/>
            <span>&nbsp;</span>
        </p>
        <p class="bulle">
            <label for="mask"><?php echo _MASK; ?> <small>(e.g: 255.255.255.0)</small> : </label>
            <input name="mask" type="text"  id="mask" value="<?php
        if (isset($_SESSION['m_admin']['docserver_locations']['mask'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docserver_locations']['mask']
            );
        }
        ?>"/>
            <span>&nbsp;</span>
        </p>
        <p>
            <label for="net_link"><?php echo _NET_LINK; ?> : </label>
            <input name="net_link" type="text"  id="net_link" value="<?php
        if (isset($_SESSION['m_admin']['docserver_locations']['net_link'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docserver_locations']['net_link']
            );
        }
        ?>"/>
            <span>&nbsp;</span>
        </p>
        <p class="buttons">
        <?php
        if ($mode == "up") {
            ?>
            <input class="button" type="submit" name="submit" value="<?php
            echo _MODIFY;
            ?>" />
            <?php
        } elseif ($mode == "add") {
            ?>
            <input type="submit" class="button"  name="submit" value="<?php
            echo _ADD;
            ?>" />
            <?php
        }
        ?>
            <input type="button" class="button"  name="cancel" value="<?php
        echo _CANCEL;
        ?>" onclick="javascript:window.location.href='<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?page=docserver_locations_management_controler&amp;admin=docservers&amp;mode=list';"/>
        </p>
        </form>
        <?php
    }
    ?>
    </div>
   <?php
}
