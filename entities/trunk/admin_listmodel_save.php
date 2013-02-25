<?php
# AJAX save listmodel from admin_listmodel

require_once("modules/entities/class/class_manage_listdiff.php");
$difflist = new diffusion_list();

switch($_REQUEST['mode']) {
case 'up':
case 'add':
    $difflist->save_listmodel(
        $_SESSION['m_admin']['entity']['listmodel'], 
        $collId = $_REQUEST['collId'],
        $listType = 'DOC', 
        $objectType = $_REQUEST['objectType'],
        $objectId = $_REQUEST['objectId']
    );
    break;
    
case 'del':
    $difflist->delete_listmodel(
        $collId = $_REQUEST['collId'],
        $listType = 'DOC', 
        $objectType = $_REQUEST['objectType'],
        $objectId = $_REQUEST['objectId']
    );
    break;
}