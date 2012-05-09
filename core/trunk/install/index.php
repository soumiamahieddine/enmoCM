<?php
//MODEL
    include_once '../core/init.php';
    require_once('install/class/Class_Install.php');
    $Class_Install = new Install;

//CONTROLLER
    if ((!isset($_REQUEST['step']) || empty($_REQUEST['step'])) && isset($_SESSION['inInstall'])) {
        header("Location: error.php?error=noStep");
        exit;
    }

    $step = $_REQUEST['step'];
    if (empty($step)) {
        $step = 'language';
    }

    if (!file_exists('install/controller/'.$step.'_controller.php')) {
        header("Location: error.php?error=badStep");
        exit;
    }

    $Class_Install->setPreviousStep($step);

    require_once('install/controller/'.$step.'_controller.php');

//VIEW
    require_once('install/view/principal_view.php');
