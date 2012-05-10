<?php
include_once '../core/init.php';

unset($_SESSION);
$_SESSION = array();
session_unset();
session_destroy();
header('Location: ../');
exit;
