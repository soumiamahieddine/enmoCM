<?php

if (
    file_exists('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR. '..' 
                    . DIRECTORY_SEPARATOR . 'core'. DIRECTORY_SEPARATOR . 'init.php'
    )
) {
    include_once '../../../../core/init.php';
} else {
    include_once '../../core/init.php';
}

if (
    file_exists('custom'.DIRECTORY_SEPARATOR. $_SESSION['custom_override_id']
                . DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR . 'content_management'
                . DIRECTORY_SEPARATOR . 'applet_controller.php'
    )
) {
    $path = 'custom/'. $_SESSION['custom_override_id'] .'/modules/content_management/applet_controller.php';
} else {
    $path = 'modules/content_management/applet_controller.php';
}

//ONLY FOR THE TESTS
/*
$_REQUEST['objectType'] = 'resource';
$_REQUEST['objectTable'] = 'res_letterbox';
$_REQUEST['objectId'] = 104;
*/

/*
echo '<pre>';
print_r($_REQUEST);
print_r($_SESSION);
echo '</pre>';
exit;
*/

$_SESSION['cm']['resMaster'] = '';
$_SESSION['cm']['reservationId'] = '';

require_once 'core/class/class_functions.php';
require_once 'core/class/class_core_tools.php';
require_once 'core/class/class_db.php';
require_once 'core/class/class_request.php';
require_once 'core/class/class_security.php';
require_once 'core/class/class_resource.php';
require_once 'core/class/docservers_controler.php';
require_once 'modules/content_management/class/class_content_manager_tools.php';

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_js();
$function = new functions();
$sec = new security();
$cM = new content_management_tools();
$cMFeatures = array();
$cMFeatures = $cM->getCmParameters();

/* Values for objectType :
 * - resource
 * - attachment
 * - attachmentFromTemplate
 * - template
 * - templateStyle
*/
if (isset($_REQUEST['objectType'])) {
    $objectType = $_REQUEST['objectType'];
} else {
    $objectType = '';
}

if ($objectType == 'templateStyle') {
    $_REQUEST['objectId'] = $_SESSION['m_admin']['templates']['current_style'];
}

if (isset($_REQUEST['objectTable'])) {
    $objectTable = $_REQUEST['objectTable'];
} else {
    $objectTable = '';
}
if (isset($_REQUEST['objectId'])) {
    $objectId = $_REQUEST['objectId'];
} else {
    $objectId = '';
}
if ($_REQUEST['resMaster'] <> '') {
    $_SESSION['cm']['resMaster'] = $_REQUEST['resMaster'];
    $reservationObjectId = $_SESSION['cm']['resMaster'];
} else {
    $reservationObjectId = $objectId;
}
if ($objectType == '' || $objectTable == '' || $objectId == '') {
    $_SESSION['error'] = _PARAM_MISSING_FOR_MAARCHCM_APPLET . ' ' 
    . $objectType . ' ' . $objectTable . ' ' . $objectId;
    //echo $_SESSION['error'];exit;
    header('location: ' . $_SESSION['config']['businessappurl'] 
        . 'index.php'
    );
    exit();
}

/*
echo 'objectType : ' . $objectType . '<br>';
echo 'objectTable : ' . $objectTable . '<br>';
echo 'objectId : ' . $objectId . '<br>';
*/

//no reservation for templateStyle object
if ($objectType <> 'templateStyle') {
    //reservation test
    $cM->deleteExpiredCM();
    $reservedBy = array();
    $reservedBy = $cM->isReservedBy(
        $objectTable,
        $reservationObjectId
    );
    if (
        $reservedBy['status'] == 'ok' 
        && $reservedBy['user_id'] != $_SESSION['user']['UserId']
    ) {
        if ($reservedBy['fullname'] <> 'empty') {
            $_SESSION['error'] = _ALREADY_RESERVED . ' ' . _BY . ' : ' 
                . $reservedBy['fullname'];
        } else {
            $_SESSION['error'] = _RESPONSE_ALREADY_RESERVED;
        }
        header('location: ' . $_SESSION['config']['businessappurl'] 
            . 'index.php'
        );
        exit();
    } else {
        $_SESSION['cm']['reservationId'] = $cM->reserveObject(
            $objectTable,
            $reservationObjectId,
            $_SESSION['user']['UserId']
        );
    }
}

//init error session
$_SESSION['error'] = '';

?>
<div id="maarchcmdiv">
    <h3><?php echo _MAARCH_CM_APPLET;?></h3>
    <br><?php echo _DONT_CLOSE;?>
    <img alt="<?php echo _LOADING;?>" src="<?php echo 
        $_SESSION['config']['businessappurl'];
        ?>static.php?filename=loading_big.gif" border="0" alt="" width="200px" height="200px" />
    <div id="maarchcm_error" class="error"></div>
    <applet ARCHIVE="<?php 
            echo $_SESSION['config']['coreurl'];?>modules/content_management/dist/maarchCM.jar" 
        code="maarchcm.MaarchCM" name="maarchcmapplet" id="maarchcmapplet" 
        WIDTH="1" HEIGHT="1" version = "1.6">
        <param name="url" value="<?php 
            echo $_SESSION['config']['coreurl'].$path;
        ?>">
        <param name="objectType" value="<?php echo $objectType;?>">
        <param name="objectTable" value="<?php echo $objectTable;?>">
        <param name="objectId" value="<?php echo $objectId;?>">
        <param name="userMaarch" value="<?php 
            echo $cMFeatures['CONFIG']['userMaarchOnClient'];
        ?>">
        <param name="userMaarchPwd" value="<?php 
            echo $cMFeatures['CONFIG']['userPwdMaarchOnClient'];
        ?>">
        <param name="psExecMode" value="<?php echo $cMFeatures['CONFIG']['psExecMode'];?>">
        <param name="mayscript" value="mayscript" />
    </applet>
</div>
<p class="buttons">
    <input type="button" name="cancel" value="<?php 
        echo _CLOSE;
        ?>" class="button" onclick="destroyModal('CMApplet');"/>
</p>
