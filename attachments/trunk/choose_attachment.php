<?php

/*
*   Copyright 2008-2014 Maarch
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
* @brief  Frame to choose a file to index
*
* @file choose_attachment.php
* @date $date$
* @version $Revision$
*/

$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$core_tools->load_html();
$core_tools->load_header('', true, false);
$upFileOK = false;
?>
    <body>
    <?php
    $_SESSION['upfile']['error'] = 0;
    if (isset($_FILES['file']['error']) && $_FILES['file']['error'] == 1) {
        $_SESSION['upfile']['error'] = $_FILES['file']['error'];
        if ($_SESSION['upfile']['error'] == 1) {
 			$_SESSION['error'] = 'La taille du fichier telecharge excede la valeur de upload_max_filesize';
        }
    } elseif (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['error'] <> 1) {
    	$_SESSION['upfile']['tmp_name'] = $_FILES['file']['tmp_name'];
        $extension = explode(".",$_FILES['file']['name']);
        $count_level = count($extension)-1;
        $the_ext = $extension[$count_level];
        $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.' . strtolower($the_ext);
            $_SESSION['upfile']['fileNameOnTmp'] = $fileNameOnTmp;
        $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
        //$md5 = md5_file($_FILES['file']['tmp_name']);
        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                $_SESSION['error'] = _FILE_NOT_SEND . ". " . _TRY_AGAIN
                    . ". " . _MORE_INFOS . " (<a href=\"mailto:"
                    . $_SESSION['config']['adminmail'] . "\">"
                    . $_SESSION['config']['adminname'] . "</a>)";
        } elseif (!@move_uploaded_file($_FILES['file']['tmp_name'], $filePathOnTmp)) {
            $_SESSION['error'] = _FILE_NOT_SEND . ". " . _TRY_AGAIN . ". "
                . _MORE_INFOS . " (<a href=\"mailto:"
                . $_SESSION['config']['adminmail'] . "\">"
                . $_SESSION['config']['adminname'] . "</a>)";
        } else {
            $_SESSION['upfile']['size'] = $_FILES['file']['size'];
            $_SESSION['upfile']['mime'] = $_FILES['file']['type'];
            $_SESSION['upfile']['local_path'] = $filePathOnTmp;
            //$_SESSION['upfile']['name'] = $_FILES['file']['name'];
            $_SESSION['upfile']['name'] = $fileNameOnTmp;
            $_SESSION['upfile']['format'] = $the_ext;
            require_once 'core/docservers_tools.php';
            $arrayIsAllowed = array();
            $arrayIsAllowed = Ds_isFileTypeAllowed($_SESSION['upfile']['local_path']);
            if ($arrayIsAllowed['status'] == false) {
                $_SESSION['error'] = _WRONG_FILE_TYPE . ' ' . $arrayIsAllowed['mime_type'];
                $_SESSION['upfile'] = array();
            }
            $upFileOK = true;
        }
    }
    ?>
    <form name="select_file_form" id="select_file_form" method="get" enctype="multipart/form-data" action="<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?display=true&module=attachments&page=choose_attachment" class="forms">
        <input type="hidden" name="display" value="true" />
        <input type="hidden" name="dir" value="indexing_searching" />
        <input type="hidden" name="page" value="choose_attachment" />
        <p>
        <!-- window.parent.$('title').value = this.value.substring(0,this.value.indexOf('.')); -->
            <input type="file" name="file" id="file" onchange="window.parent.$('file_loaded').setStyle({display: 'inline'});$('with_file').value='false';this.form.method = 'post';this.form.submit();" value="<?php
                if (isset($_SESSION['file_path'])) {
                    echo $_SESSION['file_path'];
                } ?>" style="width:200px" />
        </p>
        <p style="display:none">
            <div align="center">
            	<input type="radio" name="with_file" id="with_file" value="false" onclick="this.form.method = 'post';this.form.submit();" />
            </div>
        </p>
    </form>
    <?php $core_tools->load_js();?>
    </body>
</html>
