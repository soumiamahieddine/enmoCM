<?php
//MODEL
    require_once('class/Class_Install.php');
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

    if (!file_exists('controller/'.$step.'_controller.php')) {
        header("Location: error.php?error=badStep");
        exit;
    }

    $Class_Install->setPreviousStep($step);

    require_once('controller/'.$step.'_controller.php');

//VIEW
    require_once('view/principal_view.php');
