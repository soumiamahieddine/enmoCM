<?php

include_once '../core/init.php';

if (!$_REQUEST['ajax']) {
    header('Location: install/index.php');
    exit;
}

require_once('install/class/Class_Install.php');
$Class_Install = new Install;

if (!file_exists('install/scripts/'.$_REQUEST['script'].'.php')) {
    $return['status'] = 0;
    $return['text'] = 'Le script n\'existe pas';

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}

require_once('install/scripts/'.$_REQUEST['script'].'.php');
