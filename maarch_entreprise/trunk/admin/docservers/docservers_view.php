<?php
/* View */
if ($params['mode'] == 'list') {
    echo $listContent;
} elseif ($params['mode'] == 'update' || $params['mode'] == 'add') {
    $func = new functions();
    ?>
    <h1><img src="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=favicon.png" alt="" />
        <?php
    if ($params['mode'] == 'create') {
        echo _DOCSERVER_ADDITION;
    } elseif ($params['mode'] == 'update') {
        echo _DOCSERVER_MODIFICATION;
    }
    ?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
        <br><br>
        <form name="formdocserver" method="post" class="forms" action="<?php
        echo $_SESSION['config']['businessappurl'] . "index.php?display=true&"
            . "page=docservers_management_controler&admin=docservers&mode="
            . $params['mode'];
        ?>">
        <?php
        //load hidden standard fields 
        echo $adminPageController->loadHiddenFields($params);
        ?>
        <input type="hidden" name="size_limit_hidden"  value="<?php
        if (isset($_SESSION['m_admin']['docservers']['size_limit_number'])) {
            echo $_SESSION['m_admin']['docservers']['size_limit_number'];
        }
        ?>" id="size_limit_hidden"/>
        <input type="hidden" name="actual_size_hidden"  value="<?php
        if (isset($_SESSION['m_admin']['docservers']['actual_size_number'])) {
            echo $_SESSION['m_admin']['docservers']['actual_size_number'];
        }
        ?>" id="actual_size_hidden"/>
        <p>
            <label for="id"><?php echo _DOCSERVER_ID; ?> (*): </label>
            <input name="id" type="text"  id="id" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['docserver_id'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['docserver_id']
            );
        }
        ?>" <?php
        if ($params['mode'] == 'update') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
        </p>
        <p>
            <label for="docserver_type_id"><?php echo _DOCSERVER_TYPES;?> (*): </label>
            <?php
            if (isset($_SESSION['m_admin']['docservers']['docserver_type_id'])
                    && $_SESSION['m_admin']['docservers']['docserver_type_id'] == 'TEMPLATES'
            ) {
                ?>
                <input name="docserver_type_id" type="text"  id="docserver_type_id" value="TEMPLATES" readonly="readonly" class="readonly"/>
                <?php
            } else {
                for ($cptTypes = 0; $cptTypes < count($docserverTypesArray);
                    $cptTypes ++
                ) {
                    if (isset($_SESSION['m_admin']['docservers']['docserver_type_id'])
                        && $_SESSION['m_admin']['docservers']['docserver_type_id'] == $docserverTypesArray[$cptTypes]
                    ) {
                        $docserverTypeTxt = $docserverTypesArray[$cptTypes];
                    }
                }
                if (isset($_SESSION['m_admin']['docservers']['link_exists']) 
                    && $_SESSION['m_admin']['docservers']['link_exists']
                ) {
                    ?>
                    <input type="hidden" name="docserver_type_id" value="<?php 
                        echo $_SESSION['m_admin']['docservers']['docserver_type_id'];?>" />
                    <input name="docserver_type_id_txt" type="text"  id="docserver_type_id_txt" value="<?php
                        echo $docserverTypeTxt;
                    ?>" readonly class="readonly"/>
                    <?php
                } else {
                    ?>
                    <select name="docserver_type_id" id="docserver_type_id">
                        <option value=""><?php echo _DOCSERVER_TYPES;?></option>
                        <?php
                    for ($cptTypes = 0; $cptTypes < count($docserverTypesArray);
                        $cptTypes ++
                    ) {
                        ?>
                        <option value="<?php echo $docserverTypesArray[$cptTypes];?>" <?php
                        if (isset($_SESSION['m_admin']['docservers']['docserver_type_id'])
                            && $_SESSION['m_admin']['docservers']['docserver_type_id'] == $docserverTypesArray[$cptTypes]
                        ) {
                            echo 'selected="selected"';
                        }
                        ?>><?php echo $docserverTypesArray[$cptTypes];?></option>
                        <?php
                    }
                    ?>
                    </select>
                    <?php
                }
            }
            ?>
        </p>
        <p>
            <label for="device_label"><?php echo _DEVICE_LABEL; ?> (*): </label>
            <input name="device_label" type="text"  id="device_label" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['device_label'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['device_label']
            );
        }
        ?>"/>
        </p>
        <p>
            <label><?php echo _IS_READONLY; ?> : </label>
            <input type="radio" class="check" name="is_readonly" value="true" <?php
        if (isset($_SESSION['m_admin']['docservers']['is_readonly'])
            && $_SESSION['m_admin']['docservers']['is_readonly'] == "Y"
        ) {
            ?> checked="checked"<?php
        }
        ?> /><?php echo _YES;?>
            <input type="radio" class="check" name="is_readonly" value="false" <?php
        if (!isset($_SESSION['m_admin']['docservers']['is_readonly'])
            || (!($_SESSION['m_admin']['docservers']['is_readonly'] == "Y")
            || $_SESSION['m_admin']['docservers']['is_readonly'] == '')
        ) {
            ?> checked="checked"<?php
        }
        ?> /><?php echo _NO;?>
        </p>
        <p>
            <label for="size_format"><?php echo _SIZE_FORMAT; ?> : </label>
            <select name="size_format" id="size_format" onChange="javascript:convertSize();">
                <option value="GB"><?php echo _GB;?></option>
                <option value="TB"><?php echo _TB;?></option>
                <option value="MB"><?php echo _MB;?></option>
            </select>
        </p>
        <p>
            <label for="size_limit_number"><?php echo _SIZE_LIMIT; ?> : </label>
            <input name="size_limit_number" type="text" id="size_limit_number" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['size_limit_number'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['size_limit_number']
            );
        }
        ?>" onChange="javascript:saveSizeInBytes();"/>
        </p>
        <?php
        if ($params['mode'] == 'update') {
            ?>
            <p>
                <label for="actual_size_number"><?php
            echo _ACTUAL_SIZE;
            ?> : </label>
                <input name="actual_size_number" type="text" id="actual_size_number" value="<?php
            if (isset($_SESSION['m_admin']['docservers']['actual_size_number'])) {
                echo $func->show_str(
                    $_SESSION['m_admin']['docservers']['actual_size_number']
                );
            }
            ?>" readonly class="readonly"/>
            </p>
            <p>
                <label for="percentage_full"><?php
            echo _PERCENTAGE_FULL;
            ?> : </label>
                <input name="percentage_full" type="text" id="percentage_full" value="<?php
            if (isset($_SESSION['m_admin']['docservers']['actual_size_number'])
                && isset($_SESSION['m_admin']['docservers']['size_limit_number'])
                && ($_SESSION['m_admin']['docservers']['actual_size_number'] <> 0
                && $_SESSION['m_admin']['docservers']['size_limit_number'] <> 0)
            ) {
                echo $func->show_str(
                    (100 * $_SESSION['m_admin']['docservers']['actual_size_number']) / $_SESSION['m_admin']['docservers']['size_limit_number']
                );
            }
            ?>%" readonly class="readonly"/>
            </p>
            <?php
        }
        ?>
        <p>
            <label for="path_template"><?php echo _PATH_TEMPLATE; ?> : </label>
            <input name="path_template" type="text"  id="path_template" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['path_template'])) {
            echo $_SESSION['m_admin']['docservers']['path_template'];
        }
        ?>"/>
        </p>
        <!--<p>
            <label for="ext_docserver_info"><?php
        echo _EXT_DOCSERVER_INFO;
        ?> : </label>
            <input name="ext_docserver_info" type="text"  id="ext_docserver_info" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['ext_docserver_info'])) {
            echo $func->show_str($_SESSION['m_admin']['docservers']['ext_docserver_info']);
        }
        ?>"/>
        </p>
        <p>
            <label for="chain_before"><?php echo _CHAIN_BEFORE; ?> : </label>
            <input name="chain_before" type="text"  id="chain_before" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['chain_before'])) {
            echo $func->show_str($_SESSION['m_admin']['docservers']['chain_before']);
        }
        ?>"/>
        </p>
        <p>
            <label for="chain_after"><?php echo _CHAIN_AFTER; ?> : </label>
            <input name="chain_after" type="text"  id="chain_after" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['chain_after'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['chain_after']
            );
        }
        ?>"/>
        </p>
        <p>
            <label for="closing_date"><?php echo _CLOSING_DATE; ?> : </label>
            <input name="closing_date" type="text"  id="closing_date" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['closing_date'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['closing_date']
            );
        }
        ?>"/>
        </p>-->
        <!--<p>
            <label for="oais_mode"><?php echo _OAIS_MODE; ?> : </label>
            <select name="oais_mode" id="oais_mode">
                <option value=""><?php echo _CHOOSE_OAIS_MODE;?></option>
        <?php
        for ($cptOaisMode = 0; $cptOaisMode < count(
            $_SESSION['docserversFeatures']['DOCSERVERS']['OAIS']['MODE']
        ); $cptOaisMode ++
        ) {
            ?>
            <option value="<?php
            echo $_SESSION['docserversFeatures']['DOCSERVERS']['OAIS']['MODE'][$cptOaisMode];
            ?>" <?php
            if (isset($_SESSION['m_admin']['docservers']['oais_mode'])
                && $_SESSION['m_admin']['docservers']['oais_mode'] == $_SESSION['docserversFeatures']['DOCSERVERS']['OAIS']['MODE'][$cptOaisMode]
            ) {
                echo 'selected="selected"';
            }
            ?>><?php
            echo $_SESSION['docserversFeatures']['DOCSERVERS']['OAIS']['MODE'][$cptOaisMode];
            ?></option>
            <?php
        }
        ?>
            </select>
        </p>
        <p>
            <label for="sign_mode"><?php echo _SIGN_MODE; ?> : </label>
            <select name="sign_mode" id="sign_mode">
                <option value=""><?php echo _CHOOSE_SIGN_MODE;?></option>
        <?php
        for ($cptSignMode = 0; $cptSignMode < count(
            $_SESSION['docserversFeatures']['DOCSERVERS']['SIGN']['MODE']
        ); $cptSignMode ++
        ) {
            ?>
            <option value="<?php
            echo $_SESSION['docserversFeatures']['DOCSERVERS']['SIGN']['MODE'][$cptSignMode];
            ?>" <?php
            if (isset($_SESSION['m_admin']['docservers']['sign_mode'])
                && $_SESSION['m_admin']['docservers']['sign_mode'] == $_SESSION['docserversFeatures']['DOCSERVERS']['SIGN']['MODE'][$cptSignMode]
            ) {
                echo 'selected="selected"';
            }
            ?>><?php
            echo $_SESSION['docserversFeatures']['DOCSERVERS']['SIGN']['MODE'][$cptSignMode];
            ?></option>
            <?php
        }
        ?>
            </select>
        </p>
        <p>
            <label for="compress_mode"><?php echo _COMPRESS_MODE; ?> : </label>
            <select name="compress_mode" id="compress_mode">
                <option value=""><?php echo _CHOOSE_COMPRESS_MODE;?></option>
        <?php
        for ($cptCompressMode = 0; $cptCompressMode < count(
            $_SESSION['docserversFeatures']['DOCSERVERS']['COMPRESS']['MODE']
        ); $cptCompressMode ++
        ) {
            ?>
            <option value="<?php
            echo $_SESSION['docserversFeatures']['DOCSERVERS']['COMPRESS']['MODE'][$cptCompressMode];
            ?>" <?php
            if (isset($_SESSION['m_admin']['docservers']['compress_mode'])
                && $_SESSION['m_admin']['docservers']['compress_mode'] == $_SESSION['docserversFeatures']['DOCSERVERS']['COMPRESS']['MODE'][$cptCompressMode]
                ) {
                    echo 'selected="selected"';
            }
            ?>><?php
            echo $_SESSION['docserversFeatures']['DOCSERVERS']['COMPRESS']['MODE'][$cptCompressMode];
            ?></option>
            <?php
        }
        ?>
            </select>
        </p>-->
        <p>
            <label for="coll_id"><?php echo _COLLECTION; ?> (*): </label>
            <?php
            for ($cptCollection = 0; $cptCollection < count(
                $_SESSION['collections']
            ); $cptCollection ++
            ) {
                if (isset($_SESSION['m_admin']['docservers']['coll_id'])
                    && $_SESSION['m_admin']['docservers']['coll_id'] == $_SESSION['collections'][$cptCollection]['id']
                ) {
                    $collTxt = $_SESSION['collections'][$cptCollection]['id'] . " : "
                    . $_SESSION['collections'][$cptCollection]['label'];
                }
            }
            if (isset($_SESSION['m_admin']['docservers']['link_exists']) 
                && $_SESSION['m_admin']['docservers']['link_exists']
            ) {
                ?>
                <input type="hidden" name="coll_id" value="<?php 
                    echo $_SESSION['m_admin']['docservers']['coll_id'];?>" />
                <input name="coll_id_txt" type="text"  id="coll_id_txt" value="<?php
                    echo $collTxt;
                ?>" readonly class="readonly"/>
                <?php
            } else {
                if (isset($_SESSION['m_admin']['docservers']['coll_id'])
                    && $_SESSION['m_admin']['docservers']['coll_id'] == 'templates'
                ) {
                    ?>
                    <input name="coll_id" type="text"  id="coll_id" value="templates" readonly="readonly" class="readonly"/>
                    <?php
                } else {
                    ?>
                <select name="coll_id" id="coll_id">
                    <option value=""><?php echo _CHOOSE_COLLECTION;?></option>
                    <?php
                    for ($cptCollection = 0; $cptCollection < count(
                        $_SESSION['collections']
                    ); $cptCollection ++
                    ) {
                        ?>
                        <option value="<?php
                        echo $_SESSION['collections'][$cptCollection]['id'];
                        ?>" <?php
                        if (isset($_SESSION['m_admin']['docservers']['coll_id'])
                            && $_SESSION['m_admin']['docservers']['coll_id'] == $_SESSION['collections'][$cptCollection]['id']
                        ) {
                            echo 'selected="selected"';
                        }
                        ?>><?php
                        echo $_SESSION['collections'][$cptCollection]['id'] . " : "
                                . $_SESSION['collections'][$cptCollection]['label'];
                        ?></option>
                        <?php
                    }
                    ?>
                </select>
                    <?php
                }
            }
            ?>
        </p>
        <p>
            <label for="priority_number"><?php echo _PRIORITY; ?> : </label>
            <input name="priority_number" type="text"  id="priority_number" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['priority_number'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['priority_number']
            );
        }
        ?>"/>
        </p>
        <p>
            <label for="docserver_location_id"><?php
        echo _DOCSERVER_LOCATIONS;
        ?> (*): </label>
            <select name="docserver_location_id" id="docserver_location_id">
                <option value=""><?php echo _DOCSERVER_LOCATIONS;?></option>
        <?php
        for ($cptLocation = 0; $cptLocation < count($docserverLocationsArray);
        $cptLocation ++
        ) {
            ?>
            <option value="<?php
            echo $docserverLocationsArray[$cptLocation];
            ?>" <?php
            if (isset($_SESSION['m_admin']['docservers']['docserver_location_id'])
                && $_SESSION['m_admin']['docservers']['docserver_location_id'] == $docserverLocationsArray[$cptLocation]
            ) {
                echo 'selected="selected"';
            }
            ?>><?php echo $docserverLocationsArray[$cptLocation];?></option>
            <?php
        }
        ?>
           </select>
        </p>
        <p>
           <label for="adr_priority_number"><?php
        echo _ADR_PRIORITY;
        ?> : (*)</label>
            <input name="adr_priority_number" type="text"  id="adr_priority_number" value="<?php
        if (isset($_SESSION['m_admin']['docservers']['adr_priority_number'])) {
            echo $func->show_str(
                $_SESSION['m_admin']['docservers']['adr_priority_number']
            );
        }
        ?>"/>
        </p>
        <p class="buttons">
        <?php
        if ($params['mode'] == 'update') {
            ?>
            <input class="button" type="submit" name="submit" value="<?php
            echo _MODIFY;
            ?>" />
            <?php
        } elseif ($params['mode'] == 'create') {
            ?>
            <input type="submit" class="button"  name="submit" value="<?php
            echo _ADD;
            ?>" />
            <?php
        }
        ?>
            <input type="button" class="button"  name="cancel" value="<?php
        echo _CANCEL;
        ?>" onClick="javascript:window.location.href='<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?page=docservers_management_controler&amp;admin=docservers&amp;mode=list';"/>
        </p>
        </form>
        <script type="text/javascript">
            //on load in GB
            $('size_limit_number').value = $('size_limit_number').value / (1000 * 1000 * 1000)
            $('actual_size_number').value = $('actual_size_number').value / (1000 * 1000 * 1000)
        </script>
    </div>
    <?php
}
