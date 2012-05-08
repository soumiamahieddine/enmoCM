<?php

if (!isset($_REQUEST['languageSelect']) || empty($_REQUEST['languageSelect'])) {
    header("Location: ../error.php?error=badForm"); exit;
}

session_start();
$_SESSION['lang'] = $_REQUEST['languageSelect'];
header("Location: ../index.php?step=welcome");
