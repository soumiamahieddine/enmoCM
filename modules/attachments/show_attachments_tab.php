<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   show_attachments_tab
* @author  dev <dev@maarch.org>
* @ingroup attachments
*/

require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_security.php';

$res_id = $_REQUEST["resId"];
$coll_id = $_REQUEST["collId"];

$security = new security();
$right = $security->test_right_doc($coll_id, $res_id);

if (!$right) {
    exit(_NO_RIGHT_TXT);
}

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header('', true, false);
$core_tools->load_js();

$frm_str .= '<div class="ref-unit">';
$frm_str .= '<center>';
$frm_str .= '</center><iframe name="list_attach" id="list_attach" src="'
        . $_SESSION['config']['businessappurl']
        . 'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type_exclude=converted_pdf,print_folder" '
        . 'frameborder="0" width="100%" height="550px"></iframe>';
$frm_str .= '</div>';

echo $frm_str;
