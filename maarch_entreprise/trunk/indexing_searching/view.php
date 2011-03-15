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

/**
* @brief  View a document
*
* @file view.php
* @author Claire Figueras <dev@maarch.org>
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_request.php");
require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_security.php");
require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_resource.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$function = new functions();
$sec = new security();
//1:test the request
if (isset($_REQUEST['id'])) {
    $s_id = $_REQUEST['id'];
} else {
    $s_id = "";
}
if ($s_id == '') {
    $_SESSION['error'] = _THE_DOC . ' ' . _IS_EMPTY;
    header("location: " . $_SESSION['config']['businessappurl'] . "index.php");
    exit();
} else {
    //2:retrieve the view
    $table = "";
    if (isset($_SESSION['collection_id_choice']) 
        && !empty($_SESSION['collection_id_choice'])
    ) {
        $table = $sec->retrieve_view_from_coll_id(
            $_SESSION['collection_id_choice']
        );
        if (!$table) {
            $table = $sec->retrieve_table_from_coll(
                $_SESSION['collection_id_choice']
            );
        }
    } else {
        if (isset($_SESSION['collections'][0]['view']) 
            && !empty($_SESSION['collections'][0]['view'])
        ) {
            $table = $_SESSION['collections'][0]['view'];
        } else {
            $table = $_SESSION['collections'][0]['table'];
        }
    }
    //3:retrieve the where clause
    $whereBis = " and 1=1";
    if ($_SESSION['origin'] <> "basket" && $_SESSION['origin'] <> "workflow") {
        if (isset(
            $_SESSION['user']['security'][$_SESSION['collection_id_choice']])
        ) {
            $whereBis = " and( " . $_SESSION['user']['security']
                [$_SESSION['collection_id_choice']]['DOC']['where'] . " ) ";
        } else {
            $whereBis = " and 1=-1";
        }
    }
    //4:retrieve the adr of the resource
    $adr = array();
    $resource = new resource();
    $adr = $resource->getResourceAdr($table, $s_id, $whereBis);
    if ($adr['error']) {
        header("location: " . $_SESSION['config']['businessappurl'] 
               . "index.php?page=no_right");
        exit();
    } else {
        $docserver = $adr['docserver_id'];
        $path = $adr['path'];
        $filename = $adr['filename'];
        $format = $adr['format'];
        $fingerprint = $adr['fingerprint'];
        $fingerprint_from_db = $adr['fingerprint'];
        $offset_doc = $adr['offset_doc'];
        //5:retrieve infos of the docserver
        require_once("core" . DIRECTORY_SEPARATOR . "class" 
            . DIRECTORY_SEPARATOR . "docservers_controler.php");
        require_once("core"  .  DIRECTORY_SEPARATOR  . "DocserversTools.inc");
        $docserverControler = new docservers_controler();
        $docserverObject = $docserverControler->get($docserver);
        $docserver = $docserverObject->path_template;
        $file = $docserver . $path . $filename;
        $file = str_replace("#", DIRECTORY_SEPARATOR, $file);
        $fingerprint_from_docserver = @md5_file($file);
        //echo md5_file($file) . "<br>";
        //echo filesize($file) . "<br>";
        $adr['path_to_file'] = $file;
        //6:retrieve infos of the docserver type
        require_once("core" . DIRECTORY_SEPARATOR . "class" 
            . DIRECTORY_SEPARATOR . "docserver_types_controler.php");
        $docserverTypeControler = new docserver_types_controler();
        $docserverTypeObject = $docserverTypeControler->get(
            $docserverObject->docserver_type_id
        );
        if ($docserverTypeObject->is_container && $offset_doc == "") {
            $core_tools->load_html();
            $core_tools->load_header('', true, false);
            echo '<body>';
            echo '<br/><div class="error">' 
                . _PB_WITH_OFFSET_OF_THE_DOC_IN_THE_CONTAINER . '</div>';
            echo '</body></html>';
            exit;
        }
        //7:manage compressed resource
        if ($docserverTypeObject->is_compressed) {
            $extract = array();
            $extract = Ds_extractArchive($adr);
            if ($extract['error'] <> "") {
                $core_tools->load_html();
                $core_tools->load_header('', true, false);
                echo '<body>';
                echo '<br/><div class="error">' . $extract['error'] . '</div>';
                echo '</body></html>';
                exit;
            } else {
                $file = $extract['path'];
                $mime_type = $extract['mime_type'];
                $format = $extract['format'];
            }
        }
        //var_dump($extract);
        //8:manage view of the file
        $use_tiny_mce = false;
        if (strtolower($format) == 'maarch' 
            && $core_tools->is_module_loaded('templates')
        ) {
            $type_state = true;
            $use_tiny_mce = true;
        } else {
            require_once('apps' . DIRECTORY_SEPARATOR 
                . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR 
                . 'class' . DIRECTORY_SEPARATOR 
                . "class_indexing_searching_app.php");
            $is = new indexing_searching_app();
            $type_state = $is->is_filetype_allowed($format);
        }
        if ($fingerprint_from_db == $fingerprint_from_docserver) {
            if ($type_state <> false) {
                if ($_SESSION['history']['resview'] == "true") {
                    require_once("core" . DIRECTORY_SEPARATOR 
                        . "class" . DIRECTORY_SEPARATOR . "class_history.php");
                    $users = new history();
                    $users->add(
                        $table, 
                        $s_id, 
                        "VIEW", 
                        _VIEW_DOC_NUM . "" . $s_id, 
                        $_SESSION['config']['databasetype'], 
                        'indexing_searching'
                    );
                }
                //count number of viewed in listinstance for the user
                if ($core_tools->is_module_loaded('entities')) {
                    require_once("modules" . DIRECTORY_SEPARATOR . "entities" 
                        . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
                        . "class_manage_entities.php");
                    $ent = new entity();
                    $ent->increaseListinstanceViewed($s_id);
                }
                if ((!$use_tiny_mce || strtolower($format) <> 'maarch') 
                    && $mime_type == ""
                ) {
                    $mime_type = $is->get_mime_type($format);
                }
                // ***************************************
                // Begin contribution of Mathieu DONZEL
                // ***************************************
                if (strtolower($format) == "pdf") {
                    $Arguments = "#navpanes=0";
                    if (isset($_SESSION['search']['plain_text'])) {
                        if (strlen($_SESSION['search']['plain_text']) > 0) {
                            $Arguments .= "#search=" 
                                . $_SESSION['search']['plain_text'] . "";
                        }
                    }
                    $fileDestinationTmpName = 'tmp_file_' . rand() .  '_' 
                        .  $fingerprint  . '_' . $_SESSION['user']['UserId'] 
                        . '.' . $format;
                    @copy($file, $_SESSION['config']['tmppath'] 
                          . DIRECTORY_SEPARATOR . $fileDestinationTmpName);
                    if (file_exists($_SESSION['config']['corepath'] . 'custom' 
                        . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id'] 
                        . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR 
                        . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR 
                        . 'tmp' . DIRECTORY_SEPARATOR . $fileDestinationTmpName)
                   ) {
                        echo "<iframe frameborder=\"0\" scrolling=\"no\"" 
                        . "width=\"100%\" HEIGHT=\"100%\" src=\"" 
                        . $_SESSION['config']['coreurl'] . '/custom/' 
                        . $_SESSION['custom_override_id'] . '/apps/' 
                        . $_SESSION['config']['app_id'] . '/tmp/' 
                        . $fileDestinationTmpName . "$Arguments\">" 
                        . _FRAME_ARE_NOT_AVAILABLE_FOR_YOUR_BROWSER 
                        . "</iframe>";
                    } else {
                        echo "<iframe frameborder=\"0\" scrolling=\"no\" "
                        . "width=\"100%\" HEIGHT=\"100%\" src=\"" 
                        . $_SESSION['config']['businessappurl'] . "/tmp/" 
                        . $fileDestinationTmpName . "$Arguments\">" 
                        . _FRAME_ARE_NOT_AVAILABLE_FOR_YOUR_BROWSER 
                        . "</iframe>";
                    }
                } elseif ($use_tiny_mce && strtolower($format) == 'maarch') {
                    $myfile = fopen($file, "r");
                    $data = fread($myfile, filesize($file));
                    fclose($myfile);
                    $content = stripslashes($data);
                    $core_tools->load_html();
                    $core_tools->load_header();
                    ?>
                    <!--<body id="validation_page" 
                    onload="javascript:moveTo(0,0);
                    resizeTo(screen.width, screen.height);">-->
                    <body id="validation_page">
                        <div id="template_content" style="width:100%;"-->
                        <?php  echo $content;?>
                        </div>
                    </body>
                    </html> 
                    <?php

                } else {
                    // ***************************************
                    // End contribution ofMathieu DONZEL
                    // ***************************************
                    header("Pragma: public");
                    header("Expires: 0");
                    header(
                        "Cache-Control: must-revalidate, "
                        . "post-check=0, pre-check=0"
                    );
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Type: " . $mime_type);
                    header("Content-Disposition: inline; filename=" 
                           . basename('maarch.' . $format) . ";");
                    header("Content-Transfer-Encoding: binary");
                    readfile($file);
                    exit();
                }
            } else {
                $core_tools->load_html();
                $core_tools->load_header('', true, false);
                echo '<body>';
                echo '<br/><div class="error">' . _DOCTYPE . ' ' 
                    . _UNKNOWN . '</div>';
                echo '</body></html>';
                exit();
            }
        } else {
            $core_tools->load_html();
            $core_tools->load_header('', true, false);
            echo '<body>';
            echo '<br/><div class="error">' 
                . _PB_WITH_FINGERPRINT_OF_DOCUMENT . '</div>';
            echo '</body></html>';
        }
        if (file_exists($extract['tmpArchive'])) {
            Ds_washTmp($extract['tmpArchive']);
        }
    }
}

