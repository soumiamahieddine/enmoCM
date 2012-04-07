<?php

require_once 'core/class/class_core_tools.php';
require_once 'core/class/class_db.php';
require_once 'modules/attachments/attachments_tables.php';

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$db = new dbquery();
$db->connect();

if (!empty($_REQUEST['id']) && !empty($_REQUEST['collId'])) {
    $id = $_REQUEST['id'];
    $_SESSION['cm']['collId'] = $_REQUEST['collId'];
    $db->query("select res_id, format from " 
        . RES_ATTACHMENTS_TABLE . " where res_id = " . $id);
    $db->show();
    if ($db->nb_result() < 1) {
        echo _FILE . ' ' . _UNKNOWN.".<br/>";
    } else {
        $line = $db->fetch_object();
        if ($line->format <> '' && strtoupper($line->format) == 'MAARCH') {
            // if HTML format
            header('location: ' . $_SESSION['config']['businessappurl'] 
                . 'index.php?display=true'
                . '&module=templates&page=generate_attachment&mode=up&id=' . $id
            );
        } else {
            // if OFFICE format
            header('location: ' .$_SESSION['config']['coreurl'] 
                . 'modules/content_management/applet_launcher.php?objectType=attachment&objectId=' 
                . $id . '&objectTable=' . RES_ATTACHMENTS_TABLE
            );
        }
    }
} else {
    echo _ATTACHMENT_ID_AND_COLL_ID_REQUIRED;
}
exit;
