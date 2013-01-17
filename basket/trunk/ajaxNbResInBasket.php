<?php
$db = new dbquery();
$db->connect();
$basketId = trim(str_replace(
    'nb_', 
    '',
    $_REQUEST['id_basket']
));

for ($i=0;$i<count($_SESSION['user']['baskets']);$i++) {
    if ($_SESSION['user']['baskets'][$i]['id'] == $basketId) {
        if (!empty($_SESSION['user']['baskets'][$i]['table'])) {
            if (trim($_SESSION['user']['baskets'][$i]['clause']) <> '') {
                $db->query('select * from '
                    . $_SESSION['user']['baskets'][$i]['view']
                    . ' where ' . $_SESSION['user']['baskets'][$i]['clause'], true);
                $nb = $db->nb_result();
            }
        }
    }
}



echo "{status : 1, nb : '" . $nb ."', idSpan : '" . $_REQUEST['id_basket'] . "'}";
exit;
$sessionTemplateContent = trim(str_replace(
    "\n", 
    "",
    $sessionTemplateContent
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
