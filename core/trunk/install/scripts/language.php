<?php
//MODEL
    include_once '../../core/init.php';

//CONTROLLER
    if (!isset($_REQUEST['languageSelect']) || empty($_REQUEST['languageSelect'])) {
        header("Location: ../error.php?error=badForm"); exit;
    }

    $_SESSION['lang'] = $_REQUEST['languageSelect'];
    header("Location: ../index.php?step=welcome");
