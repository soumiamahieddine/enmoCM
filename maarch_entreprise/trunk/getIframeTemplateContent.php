<?php
$sessionTemplateContent = utf8_encode(html_entity_decode(strip_tags($_SESSION['template_content'])));
$requestTemplateContent = utf8_encode(html_entity_decode(strip_tags($_REQUEST['template_content'])));

$sessionTemplateContent = trim(str_replace(
    "\n", 
    "",
    $sessionTemplateContent
));
$sessionTemplateContent = trim(str_replace(
    " ", 
    "",
    $sessionTemplateContent
));
$sessionTemplateContent = trim(str_replace(
    "\r", 
    "",
    $sessionTemplateContent
));
$sessionTemplateContent = trim(str_replace(
    "\t", 
    "",
    $sessionTemplateContent
));
$requestTemplateContent = trim(str_replace(
    "\n", 
    "", 
    $requestTemplateContent
));
$requestTemplateContent = trim(str_replace(
    " ", 
    "", 
    $requestTemplateContent
));
$requestTemplateContent = trim(str_replace(
    "\r", 
    "", 
    $requestTemplateContent
));
$requestTemplateContent = trim(str_replace(
    "\t", 
    "", 
    $requestTemplateContent
));


/*echo $sessionTemplateContent;
echo "
";
echo $requestTemplateContent;
exit;*/

if ($sessionTemplateContent == $requestTemplateContent) {
    $_SESSION['template_content'] = '';
    echo "{status : '1, responseText : same content ! '}";
} else {
    $_SESSION['template_content'] = $_REQUEST['template_content'];
    $_SESSION['template_content'] = str_replace('[dates]', date('d-m-Y'), $_SESSION['template_content']);
    $_SESSION['template_content'] = str_replace('[time]', date('G:i:s'), $_SESSION['template_content']);
    echo "{status : '0, responseText : " . addslashes($_REQUEST['template_content']) . "'}";
}

exit;
