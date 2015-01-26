<?php

require_once 'modules/templates/class/templates_controler.php';
$templateController = new templates_controler();

if ((! isset($_REQUEST['templateId']) || empty($_REQUEST['templateId']))) {
    $error = _TEMPLATE_ID . ' ' . _EMPTY;
    echo "{status : 1, error_txt : '" . addslashes($error) . "'}";
    exit();
}

$template = $templateController->get($_REQUEST['templateId']);

$template->template_content = str_replace("\r\n", "\n", $template->template_content);
$template->template_content = str_replace("\r", "\n", $template->template_content);
$template->template_content = str_replace("\n", "\\n ", $template->template_content);

echo "{status : 0, content : '" . addslashes($template->template_content) . "'}";
exit();

