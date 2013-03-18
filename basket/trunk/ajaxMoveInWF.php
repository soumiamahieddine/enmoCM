<?php

$core_tools = new core_tools();
$dbActions= new dbquery();
$dbActions->connect();
require_once('modules/basket/class/class_modules_tools.php');
$b = new basket();

//WF COMPUTING
 $myTurnInTheWF = false;
//is it my turn in the WF ?
$myTurnInTheWF = $b->isItMyTurnInTheWF(
    $_REQUEST['userId'],
    $_REQUEST['resId'],
    $_REQUEST['collId'],
    $_REQUEST['role']
);

if ($myTurnInTheWF) {
    $b->moveInTheWF(
        $_REQUEST['way'],
        $_REQUEST['collId'],
        $_REQUEST['resId'],
        $_REQUEST['role'],
        $_REQUEST['userId']
    );
    require_once('modules/entities/class/class_manage_listdiff.php');
    $listdiff = new diffusion_list();
    $_SESSION['process']['diff_list'] = $listdiff->get_listinstance(
        $_REQUEST['resId'], 
        false, 
        $_REQUEST['collId']
    );
    if ($_REQUEST['way'] == 'forward') {
        $histText = _FORWARD_IN_THE_WF;
    } else {
        $histText = _BACK_IN_THE_WF;
    }
    require_once('core/class/class_security.php');
    $sec = new security();
    $view = $sec->retrieve_view_from_coll_id($_REQUEST['collId']);
    require_once('core/class/class_history.php');
    $hist = new history();
    $hist->add(
        $view, 
        $_REQUEST['resId'], 
        'UP', 
        'stepWF' . $_REQUEST['way'],
        _DOC_NUM . $_REQUEST['resId'] . ' ' . $histText . ' ' . $_REQUEST['role'], 
        $_SESSION['config']['databasetype'], 
        'apps'
    );
    echo "{status:0}";
    exit();
} else  {
    $_SESSION['error'] = _ITS_NOT_MY_TURN_IN_THE_WF;
    echo "{status:1, error_txt:'" . _ITS_NOT_MY_TURN_IN_THE_WF . "'}";
    exit();
}

