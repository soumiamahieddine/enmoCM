<?php
include_once '../core/init.php';

//write semaphore installed.lck

$inF = fopen('installed.lck','w');
fclose($inF);

unset($_SESSION);
$_SESSION = array();
session_unset();
session_destroy();
header('Location: ../');
exit;
