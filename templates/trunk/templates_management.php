<?php
/* View */
$func = new functions();
if ($mode == 'list') {
    $listShow = new list_show();
    $listShow->admin_list(
        $templates_list['tab'],
        count($templates_list['tab']),
        $templates_list['title'],
        'template_id',
        'templates_management_controler&mode=list',
        'templates','template_id',
        true,
        $templates_list['page_name_up'],
        '',
        '',
        $templates_list['page_name_del'],
        $templates_list['page_name_add'],
        $templates_list['label_add'],
        false,
        false,
        _ALL_TEMPLATES,
        _TEMPLATES,
        $_SESSION['config']['businessappurl'] 
            . 'static.php?filename=manage_lc_b.gif&module=life_cycle',
        true,
        true,
        false,
        true,
        $templates_list['what'],
        true,
        $templates_list['autoCompletionArray']
    );
} elseif ($mode == 'up' || $mode == 'add') {
    /*echo '<pre>';
    print_r($_SESSION['m_admin']['templatesStyles']);
    echo '</pre>';*/
    include('modules/templates/load_editor.php');
    ?>
    <h1><img src="<?php 
        echo $_SESSION['config']['businessappurl'];
            ?>static.php?filename=manage_lc_b.gif&module=life_cycle" alt="" />
        <?php
        if ($mode == 'add') {
            echo _TEMPLATE_ADDITION;
        }
        elseif ($mode == 'up') {
            echo _TEMPLATE_UPDATE;
        }
        ?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
        <br/><br/>
        <?php
        if ($state == false) {
            echo '<br /><br />' . _THE_TEMPLATE . ' ' . _UNKOWN 
                . '<br /><br /><br /><br />';
        } else {
            ?>
            <form id="adminform" method="post" class="forms" action="<?php 
                echo $_SESSION['config']['businessappurl'] 
                . 'index.php?display=true&page=templates_management_controler&module=templates&mode=' 
                . $mode;
            ?>">
                <input type="hidden" name="display" value="value" />
                <input type="hidden" name="module" value="templates" />
                <input type="hidden" name="page" value="templates_management_controler" />
                <input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
                <input type="hidden" name="order" id="order" value="<?php 
                    if (isset($_REQUEST['order'])) echo $_REQUEST['order'];
                ?>" />
                <input type="hidden" name="order_field" id="order_field" value="<?php 
                    if (isset($_REQUEST['order_field'])) echo $_REQUEST['order_field'];
                ?>" />
                <input type="hidden" name="what" id="what" value="<?php 
                    if (isset($_REQUEST['what'])) echo $_REQUEST['what'];
                ?>" />
                <input type="hidden" name="start" id="start" value="<?php 
                    if (isset($_REQUEST['start'])) echo $_REQUEST['start'];
                ?>" />
                <?php
                if (
                    $mode == 'up' 
                    && $_SESSION['m_admin']['templates']['template_type'] == 'OFFICE'
                ) {
                    ?>
                    <input type="hidden" name="template_path" id="template_path" value="<?php
                        echo $_SESSION['m_admin']['templates']['template_path'];
                    ?>" />
                    <input type="hidden" name="template_file_name" id="template_file_name" value="<?php
                        echo $_SESSION['m_admin']['templates']['template_file_name'];
                    ?>" />
                    <?php
                }
                if ($mode == 'up') {
                    ?>
                    <p>
                        <label for="id"><?php echo _TEMPLATE_ID; ?> : </label>
                        <input name="id" type="text" id="id" value="<?php 
                            if (isset($_SESSION['m_admin']['templates']['template_id'])) {
                                echo $func->show_str($_SESSION['m_admin']['templates']['template_id']); 
                            }
                            ?>" readonly='readonly' class='readonly'/>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <label for="template_label"><?php echo _TEMPLATE_LABEL; ?> : </label>
                    <input name="template_label" type="text" id="template_label" value="<?php 
                        if (isset($_SESSION['m_admin']['templates']['template_label'])) {
                            echo $func->show_str($_SESSION['m_admin']['templates']['template_label']); 
                        }
                        ?>" />
                </p>
                <p>
                    <label for="template_comment"><?php echo _TEMPLATE_COMMENT; ?> : </label>
                    <textarea name="template_comment" type="text"  id="template_comment" value="<?php 
                        if (isset($_SESSION['m_admin']['templates']['template_comment'])) {
                            echo $func->show_str($_SESSION['m_admin']['templates']['template_comment']); 
                        }
                        ?>" /><?php 
                        if (isset($_SESSION['m_admin']['templates']['template_comment'])) {
                            echo $_SESSION['m_admin']['templates']['template_comment'];
                        }
                    ?></textarea>
                </p>
                <p>
                    <label><?php echo _TEMPLATE_TYPE;?> :</label>
                    <input type="radio" name="template_type" value="OFFICE" 
                        onClick="javascript:show_special_form('office_div', 'html_div');" <?php 
                        echo $checkedOFFICE;?>/> <?php echo _OFFICE;?>
                    <input type="radio" name="template_type" value="HTML" 
                        onClick="javascript:show_special_form('html_div', 'office_div');" <?php 
                        echo $checkedHTML;?>/> <?php echo _HTML;?>
                </p>
                <div id="html_div" name="html_div">
                    <p>
                        <label for="template_content">
                            <?php echo _TEMPLATE_CONTENT; ?> HTML : 
                        </label><br/><br/>
                        <textarea name="template_content" style="width:100%" rows="15" cols="60" id="template_content" value="<?php 
                            if (isset($_SESSION['m_admin']['templates']['template_content'])) {
                                echo $func->show_str($_SESSION['m_admin']['templates']['template_content']); 
                            }
                            ?>" /><?php 
                            if (isset($_SESSION['m_admin']['templates']['template_content'])) {
                                echo $_SESSION['m_admin']['templates']['template_content'];
                            }
                        ?></textarea>
                    </p>
                </div>
                <div id="office_div" name="office_div">
                    <p>
                        <label for="template_style"><?php echo _TEMPLATE_STYLE; ?> : </label>
                        <?php 
                        if ($mode == 'up') {
                            ?>
                            <input name="template_style" type="text" id="template_style" value="<?php 
                                if (isset($_SESSION['m_admin']['templates']['template_style'])) {
                                    echo $func->show_str($_SESSION['m_admin']['templates']['template_style']); 
                                }
                                ?>" readonly='readonly' class='readonly' />
                            <?php
                        } else {
                            ?>
                            <select name="template_style" id="template_style" onChange="javascript:changeStyle($('template_style'), '<?php
                                echo $_SESSION['config']['coreurl'] . 'modules/templates/change_template_style.php';?>');">
                                <?php
                                // if user don't choose a style
                                if (!isset($_SESSION['m_admin']['templates']['current_style'])) {
                                    $_SESSION['m_admin']['templates']['current_style']
                                        = $_SESSION['m_admin']['templatesStyles'][0]['filePath'];
                                }
                                for (
                                    $cptStyle = 0;
                                    $cptStyle < count($_SESSION['m_admin']['templatesStyles']);
                                    $cptStyle ++
                                ) {
                                    ?>
                                    <option value="<?php
                                        echo $_SESSION['m_admin']['templatesStyles'][$cptStyle]['fileExt'] . ': ';
                                        echo $_SESSION['m_admin']['templatesStyles'][$cptStyle]['fileName'];
                                    ?>" <?php
                                    if (isset($_SESSION['m_admin']['templates']['template_style'])
                                        && $_SESSION['m_admin']['templates']['template_style'] 
                                            == $_SESSION['m_admin']['templatesStyles'][$cptStyle]['fileName']
                                    ) {
                                        echo 'selected="selected"';
                                    }
                                    ?>><?php 
                                        echo $_SESSION['m_admin']['templatesStyles'][$cptStyle]['fileExt'] . ': ';
                                        echo $_SESSION['m_admin']['templatesStyles'][$cptStyle]['fileName'];
                                    ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <?php
                        }
                        ?>
                    </p>
                    <?php
                    if ($mode == 'add') {
                        $objectType = 'templateStyle';
                    } else {
                        $objectType = 'template';
                        $objectId = $_SESSION['m_admin']['templates']['template_id'];
                    }
                    $objectTable = _TEMPLATES_TABLE_NAME;
                    ?>
                    <p>
                        <label><?php echo _EDIT_TEMPLATE;?> :</label>
                        <div style="text-align:center;">
                            <a href="<?php 
                                echo $_SESSION['config']['coreurl'];
                                ?>modules/content_management/applet_launcher.php?objectType=<?php 
                                    echo $objectType;
                                ?>&objectId=<?php 
                                    echo $objectId;
                                ?>&objectTable=<?php
                                    echo $objectTable;
                                ?>" target="_blank">
                                <img alt="<?php echo _EDIT_TEMPLATE;?>" src="<?php echo 
                                    $_SESSION['config']['businessappurl'];
                                    ?>static.php?filename=modif_note.png&module=notes" border="0" alt="" />
                            </a>
                        </div>
                    </p>
                </div>
                    <table align="center" width="100%" id="template_entities" >
                    <tr>
                        <td colspan="3"><?php  echo _CHOOSE_ENTITY_TEMPLATE;?> :</td>
                    </tr>
                    <tr>
                        <td width="40%" align="center">
                            <select name="entitieslist[]" id="entitieslist" size="7" 
                            ondblclick='moveclick($(entitieslist), $(entities_chosen));' multiple="multiple" >
                            <?php
                            for ($i=0;$i<count($_SESSION['m_admin']['templatesEntitiesOrg']);$i++) {
                                $state_entity = false;
                                for ($j=0;$j<count($_SESSION['m_admin']['templatesEntities']['destination']);$j++) {
                                    if (
                                        $_SESSION['m_admin']['templatesEntitiesOrg'][$i]->entity_id 
                                        == $_SESSION['m_admin']['templatesEntities']['destination'][$j]
                                    ) {
                                        $state_entity = true;
                                    }
                                }
                                if ($state_entity == false) {
                                    ?>
                                    <option value="<?php 
                                        echo $_SESSION['m_admin']['templatesEntitiesOrg'][$i]->entity_id;
                                        ?>"><?php 
                                        echo $_SESSION['m_admin']['templatesEntitiesOrg'][$i]->entity_label;
                                        ?></option>
                                    <?php
                                }
                            }
                            ?>
                            </select>
                            <br/>
                            <!--<em><a href='javascript:selectall($(entitieslist));'><?php 
                                echo _SELECT_ALL;
                                ?></a></em>-->
                        </td>
                        <td width="20%" align="center">
                            <input type="button" class="button" value="<?php 
                                echo _ADD; 
                                ?> &gt;&gt;" onclick='Move($(entitieslist), $(entities_chosen));' />
                            <br />
                            <br />
                            <input type="button" class="button" value="&lt;&lt; <?php 
                                echo _REMOVE;
                                ?>" onclick='Move($(entities_chosen), $(entitieslist));' />
                        </td>
                        <td width="40%" align="center">
                            <select name="entities_chosen[]" id="entities_chosen" size="7" 
                            ondblclick='moveclick($(entities_chosen), $(entitieslist));' multiple="multiple">
                            <?php
                            for ($i=0;$i<count($_SESSION['m_admin']['templatesEntitiesOrg']);$i++) {
                                $state_entity = false;
                                for ($j=0;$j<count($_SESSION['m_admin']['templatesEntities']['destination']);$j++) {
                                    if (
                                        $_SESSION['m_admin']['templatesEntitiesOrg'][$i]->entity_id 
                                        == $_SESSION['m_admin']['templatesEntities']['destination'][$j]
                                    ) {
                                        $state_entity = true;
                                    }
                                }
                                if ($state_entity == true) {
                                    ?>
                                    <option value="<?php 
                                        echo $_SESSION['m_admin']['templatesEntitiesOrg'][$i]->entity_id;
                                        ?>" selected="selected" ><?php 
                                        echo $_SESSION['m_admin']['templatesEntitiesOrg'][$i]->entity_label; 
                                        ?></option>
                                    <?php
                                }
                            }
                            ?>
                            </select>
                            <br/>
                            <!--<em><a href="javascript:selectall($(entities_chosen));" >
                            <?php echo _SELECT_ALL; ?></a></em>-->
                        </td>
                    </tr>
                </table>

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
                        ?>index.php?page=templates_management_controler&amp;module=templates&amp;mode=list';"/>
                </p>
            </form>
            <?php
            if($_SESSION['m_admin']['templates']['template_type'] <> 'HTML') {
                ?>
                <script>
                    show_special_form('office_div', 'html_div');
                </script>
                <?php
            } else {
                ?>
                <script>
                    show_special_form('html_div', 'office_div');
                </script>
                <?php
            }
        }
        ?>
    </div>
    <?php
}
