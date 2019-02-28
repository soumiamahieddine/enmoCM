<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

*
* @brief view_baskets
*
* @author dev <dev@maarch.org>
* @ingroup baskets
*/
$_SESSION['FILE'] = array();
if (isset($_REQUEST['extension'])) {
    $_SESSION['origin'] = "scan";
    $_SESSION['FILE']['extension'] = $_REQUEST['extension'];
    $_SESSION['upfile']['size'] = $_REQUEST['taille_fichier'];
    $_SESSION['upfile']['mime'] = "application/pdf";
    $_SESSION['upfile']['local_path'] = $_SESSION['config']['tmppath'] . "tmp_file_".$_REQUEST['md5'].".pdf";
    $_SESSION['upfile']['name'] = "tmp_file_".$_REQUEST['md5'].".pdf";
    $_SESSION['upfile']['md5'] = $_REQUEST['md5'];
    $_SESSION['upfile']['format'] = 'pdf';
} else {
    $_SESSION['origin'] = "";
    $_SESSION['upfile'] = array();
}
//file size
if (isset($_REQUEST['taille_fichier'])) {
    $_SESSION['FILE']['taille_fichier'] = $_REQUEST['taille_fichier'];
    $_SESSION['upfile']['size'] = $_REQUEST['taille_fichier'];
}
//file temporary path
if (isset($_REQUEST['Ftp_File'])) {
    $_SESSION['FILE']['index_type'] = $_REQUEST['index_type'];
    $_SESSION['FILE']['Ftp_File'] = $_REQUEST['Ftp_File'];
}
//fingerprint of the file
if (isset($_REQUEST['md5'])) {
    $_SESSION['FILE']['md5'] = $_REQUEST['md5'];
}
//scan user
if (isset($_REQUEST['tmp_file'])) {
    $_SESSION['FILE']['tmp_file'] = $_REQUEST['tmp_file'];
}

if (! isset($_REQUEST['noinit'])) {
    $_SESSION['current_basket'] = array();
}
require_once "modules/basket/class/class_modules_tools.php";


$bask = new basket();

if (isset($_REQUEST['baskets']) && ! empty($_REQUEST['baskets'])) {
    $_SESSION['tmpbasket']['status'] = "all";
    $bask->load_current_basket(trim($_REQUEST['baskets']), trim($_REQUEST['groupId']));
}

if (empty($_GET['resId'])) {
    $_GET['resId'] = "'none'";
}

if (empty($_GET['defaultAction'])) {
    $_GET['defaultAction'] = $_SESSION['current_basket']['default_action'];
}

$_SESSION['urlV2Basket'] = $_GET;
echo '<script language="javascript">';
if (!empty($_GET['backToBasket'])) {
    echo 'triggerAngular(\'#/basketList/users/'.$_GET['userId'].'/groups/'.$_GET['groupIdSer'].'/baskets/'.$_GET['basketId'].'\');';
} else if (!empty($_GET['signatureBookMode'])) {
    echo 'triggerAngular(\'#/signatureBook/users/'.$_GET['userId'].'/groups/'.$_GET['groupIdSer'].'/baskets/'.$_GET['basketId'].'/resources/'.$_GET['resId'].'\');';
} else {
    echo 'action_send_first_request(\''
        . $_SESSION['config']['businessappurl']
        . 'index.php?display=true&page=manage_action&module=core\''
        . ', \'page\''
        . ',' . $_GET['defaultAction']
        . ',' . $_GET['resId']
        . ',\'' . $_SESSION['current_basket']['table'] . '\''
        . ',\'basket\''
        . ',\'' . $_SESSION['current_basket']['coll_id'] . '\');';
}
echo '</script>';
