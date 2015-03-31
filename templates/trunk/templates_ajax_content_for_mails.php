<?php

require_once 'modules/templates/class/templates_controler.php';
require_once 'core/class/class_security.php';
$templateController = new templates_controler();

if ((! isset($_REQUEST['templateId']) || empty($_REQUEST['templateId']))) {
    $error = _TEMPLATE_ID . ' ' . _EMPTY;
    echo "{status : 1, error_txt : '" . addslashes($error) . "'}";
    exit();
}

$sec = new security();
$res_view = $sec->retrieve_view_from_coll_id('letterbox_coll');

$params = array(
	'res_id' => $_GET['id'],
	'coll_id'=> "letterbox_coll",
	'res_view'=> $res_view
	);

$template = $templateController->get($_REQUEST['templateId']);
$template->template_content =  $templateController->merge($_REQUEST['templateId'], $params, 'content');
$template->template_content = str_replace("\r\n", "\n", $template->template_content);
$template->template_content = str_replace("\r", "\n", $template->template_content);
$template->template_content = str_replace("\n", "\\n ", $template->template_content);

echo "{status : 0, content : '" . addslashes($template->template_content) . "'}";
exit();