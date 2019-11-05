<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   get_chrono_attachment
* @author  dev <dev@maarch.org>
* @ingroup attachments
*/

require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_chrono.php';
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");

$core = new core_tools();
$core->test_user();
$db = new Database();

$index = $_REQUEST['index'];

//RETRIEVE CATEGORY OF DOCUMENT
$stmt = $db->query("SELECT category_id FROM res_letterbox WHERE res_id = ? ", array($_SESSION['doc_id']));
$resMaster = $stmt->fetchObject();
$category_id = $resMaster->category_id;

$nb_attachment = 0;

// Check if reponse project was already attached to this outgoing document.
if ($category_id == "incoming" || $category_id == 'attachment' || (isset($_POST['type_id']) && $_POST['type_id'] == 'attachment')) {
    if (isset($_SESSION['save_chrono_number']) && $_SESSION['save_chrono_number'][$index] <> "") {
        echo "{status: 1, chronoNB: '".$_SESSION['save_chrono_number'][$index]."'}";
    } else {
        //GENERATE NEW CHRONO
        $chronoX = new chrono();
        $myVars = array(
            'category_id' => 'outgoing',
            'entity_id' => $_SESSION['user']['primaryentity']['id']
        );

        $myChrono = $chronoX->generate_chrono('outgoing', $myVars);
        $_SESSION['save_chrono_number'][$index] = $myChrono;
        echo "{status: 1, chronoNB: '".functions::xssafe($myChrono)."'}";
    }
}
