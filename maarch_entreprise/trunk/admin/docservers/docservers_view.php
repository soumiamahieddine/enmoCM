<?php
/* View */
if ($params['mode'] == 'list') {
    ?>
    <h1><img src="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=admin_docservers.png" alt="" />
    <?php
    echo _DOCSERVERS_LIST. ' : ' . count($dataObjectList->$params['objectName']) 
        . ' ' . _DOCSERVERS;
    ?>
    </h1>
    <?php
    echo $listContent;
} elseif ($params['mode'] == 'create' || $params['mode'] == 'read' || $params['mode'] == 'update') {
    $func = new functions();
    ?>
    <h1><img src="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=admin_docservers.png" alt="" />
        <?php
    if ($params['mode'] == 'create') {
        echo _DOCSERVER_ADDITION;
    } elseif ($params['mode'] == 'read') {
        echo _DOCSERVER_READ;
    } elseif ($params['mode'] == 'update') {
        echo _DOCSERVER_MODIFICATION;
    }
    ?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
        <br><br>
        <form name="formdocserver" method="post" class="forms" action="<?php
        echo $_SESSION['config']['businessappurl'] . "index.php?display=true&"
            . "page=docservers_page_controller&objectName=" . $params['objectName'] . "&admin=docservers&mode="
            . $params['mode'] . '&objectId='.$params['objectId'];
        ?>">
        <?php
        //load hidden standard fields 
        echo loadHiddenFields($params);
        ?>
        <input type="hidden" name="size_limit_hidden"  value="<?php
        if (isset($dataObject->size_limit_number)) {
            echo $dataObject->size_limit_number;
        }
        ?>" id="size_limit_hidden"/>
        <input type="hidden" name="actual_size_hidden"  value="<?php
        if (isset($dataObject->actual_size_number)) {
            echo $dataObject->actual_size_number;
        }
        ?>" id="actual_size_hidden"/>
        <p>
            <label for="docserver_id"><?php echo _DOCSERVER_ID; ?> (*): </label>
            <input name="docserver_id" type="text"  id="docserver_id" value="<?php
        if (isset($dataObject->docserver_id)) {
            echo $func->show_str(
                $dataObject->docserver_id
            );
        }
        ?>" <?php
        if ($params['mode'] == 'update' || $params['mode'] == 'read') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
        </p>
        <p>
            <label for="docserver_type_id"><?php echo _DOCSERVER_TYPES;?> (*): </label>
            <?php
            if ((isset($dataObject->docserver_type_id)
                    && $dataObject->docserver_type_id == 'TEMPLATES')
                || $params['mode'] == 'read'
            ) {
                ?>
                <input name="docserver_type_id" type="text"  id="docserver_type_id" value="TEMPLATES" readonly="readonly" class="readonly"/>
                <?php
            } else {
                for ($cptTypes = 0; $cptTypes < count($docserverTypesArray);
                    $cptTypes ++
                ) {
                    if (isset($dataObject->docserver_type_id)
                        && $dataObject->docserver_type_id == $docserverTypesArray[$cptTypes]
                    ) {
                        $docserverTypeTxt = $docserverTypesArray[$cptTypes];
                    }
                }
                if (isset($dataObject->link_exists) 
                    && $dataObject->link_exists
                ) {
                    ?>
                    <input type="hidden" name="docserver_type_id" value="<?php 
                        echo $dataObject->docserver_type_id;?>" />
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
                        if (isset($dataObject->docserver_type_id)
                            && $dataObject->docserver_type_id == $docserverTypesArray[$cptTypes]
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
        if (isset($dataObject->device_label)) {
            echo $func->show_str(
                $dataObject->device_label
            );
        }
        ?>" <?php
        if ($params['mode'] == 'read') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
        </p>
        <p>
            <label><?php echo _IS_READONLY; ?> : </label>
            <input type="radio" class="check" name="is_readonly" value="true" <?php
        if (isset($dataObject->is_readonly)
            && $dataObject->is_readonly == "Y"
        ) {
            ?> checked="checked"<?php
        }
        ?> /><?php echo _YES;?>
            <input type="radio" class="check" name="is_readonly" value="false" <?php
        if (!isset($dataObject->is_readonly)
            || (!($dataObject->is_readonly == "Y")
            || $dataObject->is_readonly == '')
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
        if (isset($dataObject->size_limit_number)) {
            echo $func->show_str(
                $dataObject->size_limit_number
            );
        }
        ?>" onChange="javascript:saveSizeInBytes();" <?php
        if ($params['mode'] == 'read') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
        </p>
        <?php
        if ($params['mode'] == 'update' || $params['mode'] == 'read') {
            ?>
            <p>
                <label for="actual_size_number"><?php
            echo _ACTUAL_SIZE;
            ?> : </label>
                <input name="actual_size_number" type="text" id="actual_size_number" value="<?php
            if (isset($dataObject->actual_size_number)) {
                echo $func->show_str(
                    $dataObject->actual_size_number
                );
            }
            ?>" readonly class="readonly" />
            </p>
            <p>
                <label for="percentage_full"><?php
            echo _PERCENTAGE_FULL;
            ?> : </label>
                <input name="percentage_full" type="text" id="percentage_full" value="<?php
            if (isset($dataObject->actual_size_number)
                && isset($dataObject->size_limit_number)
                && ($dataObject->actual_size_number <> 0
                && $dataObject->size_limit_number <> 0)
            ) {
                echo $func->show_str(
                    (100 * $dataObject->actual_size_number) / $dataObject->size_limit_number
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
        if (isset($dataObject->path_template)) {
            echo $dataObject->path_template;
        }
        ?>" <?php
        if ($params['mode'] == 'read') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
        </p>
        <p>
            <label for="coll_id_txt"><?php echo _COLLECTION; ?> (*): </label>
            <?php
            for ($cptCollection = 0; $cptCollection < count(
                $_SESSION['collections']
            ); $cptCollection ++
            ) {
                if (isset($dataObject->coll_id)
                    && $dataObject->coll_id == $_SESSION['collections'][$cptCollection]['id']
                ) {
                    $collTxt = $_SESSION['collections'][$cptCollection]['id'] . " : "
                    . $_SESSION['collections'][$cptCollection]['label'];
                }
            }
            if ((isset($dataObject->link_exists) 
                && $dataObject->link_exists)
                || $params['mode'] == 'read'
            ) {
                ?>
                <input type="hidden" name="coll_id" value="<?php 
                    echo $dataObject->coll_id;?>" />
                <input name="coll_id_txt" type="text"  id="coll_id_txt" value="<?php
                    echo $collTxt;
                ?>" readonly class="readonly"/>
                <?php
            } else {
                if (isset($dataObject->coll_id)
                    && $dataObject->coll_id == 'templates'
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
                        if (isset($dataObject->coll_id)
                            && $dataObject->coll_id == $_SESSION['collections'][$cptCollection]['id']
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
        if (isset($dataObject->priority_number)) {
            echo $func->show_str(
                $dataObject->priority_number
            );
        }
        ?>" <?php
        if ($params['mode'] == 'read') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
        </p>
        <p>
            <label for="docserver_location_id"><?php
        echo _DOCSERVER_LOCATIONS;
        ?> (*): </label>
        <?php
        if ($params['mode'] == 'read') {
                ?>
                <input name="docserver_location_id" type="text"  id="docserver_location_id" value="<?php
                echo $dataObject->docserver_location_id;
                ?>" readonly="readonly" class="readonly"/>
                <?php
            } else {
                ?>
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
                        if (isset($dataObject->docserver_location_id)
                            && $dataObject->docserver_location_id == $docserverLocationsArray[$cptLocation]
                        ) {
                            echo 'selected="selected"';
                        }
                        ?>><?php echo $docserverLocationsArray[$cptLocation];?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php
            }
            ?>
        </p>
        <p>
           <label for="adr_priority_number"><?php
        echo _ADR_PRIORITY;
        ?> : (*)</label>
            <input name="adr_priority_number" type="text"  id="adr_priority_number" value="<?php
        if (isset($dataObject->adr_priority_number)) {
            echo $func->show_str(
                $dataObject->adr_priority_number
            );
        }
        ?>" <?php
        if ($params['mode'] == 'read') {
            echo " readonly='readonly' class='readonly'";
        }
        ?>/>
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
            ?>index.php?page=<?php 
                echo $params['pageName'];?>&amp;admin=<?php 
                echo $params['objectName'];?>&amp;mode=list&amp;objectName=<?php 
                echo $params['objectName'];?>&amp;order=<?php 
                echo $params['order'];?>&amp;orderField=<?php 
                echo $params['orderField'];?>&amp;what=<?php 
                echo $params['what'];?>';"/>
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
