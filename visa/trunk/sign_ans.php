<?php

require_once 'core/class/class_core_tools.php';
require_once 'core/class/class_db.php';
require_once 'modules/attachments/attachments_tables.php';

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$db = new dbquery();
$db->connect();

if (
    file_exists(
        $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
        . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'modules'
        . DIRECTORY_SEPARATOR . 'visa' . DIRECTORY_SEPARATOR . 'applet_launcher.php'
    )
) {
    $path = 'custom/'. $_SESSION['custom_override_id'] .'/modules/visa/applet_launcher.php';
} else {
    $path = 'modules/visa/applet_launcher.php';
}

if (!empty($_REQUEST['pinCode']) && $_REQUEST['pinCode'] != 'null') {
	$_SESSION['sign']['encodedPinCode'] = $_REQUEST['pinCode'];
	$_SESSION['sign']['indexKey'] = '-1';
}


if (!empty($_REQUEST['id']) && !empty($_REQUEST['collId']) && isset($_REQUEST['modeSign'])) {
    $id = $_REQUEST['id'];
    $modeSign = $_REQUEST['modeSign'];
	$tableName = 'res_view_attachments';
    if (!isset($_REQUEST['isVersion'])) $db->query("select res_id, format, res_id_master, title from ".$tableName." where attachment_type = 'response_project' and res_id = " . $id);
    else $db->query("select res_id_version, format, res_id_master, title from ".$tableName." where attachment_type = 'response_project' and res_id_version = " . $id);
	
    if ($db->nb_result() < 1) {
        echo _FILE . ' ' . _UNKNOWN.".<br/>";
    } else {
        $line = $db->fetch_object();
		$_SESSION['visa']['last_resId_signed']['res_id'] = $line->res_id_master;
		$_SESSION['visa']['last_resId_signed']['title'] = $line->title;
            $core_tools->load_html();
            $core_tools->load_header();
            //$core_tools->load_js();
            ?>
            <body>
                <div id="container">
                    <div id="content">
                        <div class="error" id="divError" name="divError"></div>
                        <script language="javascript">
                            loadAppletSign('<?php 
								echo $_SESSION['config']['coreurl'] .''.$path;
								?>?objectType=ans_project&objectId=<?php 
                                echo $id;
                                ?>&objectTable=<?php 
                                echo RES_ATTACHMENTS_TABLE;
                                ?>&modeSign=<?php 
                                echo $modeSign;
                                ?>');
                        </script>
                    </div>
                </div>
            </body>
            </html>
            <?php    
    }
} else {
    echo _ATTACHMENT_ID_AND_COLL_ID_REQUIRED;
}
exit;
