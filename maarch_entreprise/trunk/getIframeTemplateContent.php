<?php

$_SESSION['template_content'] = $_REQUEST['template_content'];

echo "{status : 'OK " . addslashes($_REQUEST['template_content']) . "'}";
exit;
