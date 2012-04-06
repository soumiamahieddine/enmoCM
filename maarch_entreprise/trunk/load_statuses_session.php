<?php
require_once 'core/class/StatusControler.php';

$sts = new Maarch_Core_Class_StatusControler();
$_SESSION['m_admin']['statuses'] = array();
$_SESSION['m_admin']['statuses'] = $sts->getAllInfos();
