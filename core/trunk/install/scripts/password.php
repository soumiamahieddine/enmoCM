<?php
//MODEL
    include_once '../../core/init.php';
    require_once('install/class/Class_Install.php');
    $Class_Install = new Install;

//CONTROLLER
    if (!isset($_REQUEST['newSuperadminPass']) || empty($_REQUEST['newSuperadminPass'])) {
        header("Location: ../error.php?error=badForm"); exit;
    }

    $Class_Install->setSuperadminPass(
        $_REQUEST['newSuperadminPass']
    );

    header("Location: ../index.php?step=resume");
