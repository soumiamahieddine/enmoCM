<?php
# AJAX Script to validate listmodel header values
# and load into session if needed

require_once 'core/class/class_core_tools.php';
$core = new core_tools();
$core->load_lang();

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");
$request = new request();
$request->connect();

$mode = $_REQUEST['mode'];
$objectType = $_REQUEST['objectType'];
$objectId = $_REQUEST['objectId'];
$collId = $_REQUEST['collId'];
$listmodelType = $_REQUEST['listmodelType'];
$description = $_REQUEST['description'];

$return = "";

if($objectId == '' || $objectType == '')
    $return .= _SELECT_OBJECT_TYPE_AND_ID . "<br/>";
    
if($collId == '')
    $return .= _SELECT_COLL_ID . "<br/>";

if($description == '')
    $return .= _ENTER_DESCRIPTION . "<br/>";
    
if($mode == 'add' && $objectId && $objectType && $collId) {
    $request->query(
        "select count(1) as nb from " . ENT_LISTMODELS
        . " where object_type = '" . $objectType . "'"
        . " and object_id = '" . $objectId . "'"
        . " and coll_id = '" . $collId . "'"
    );
    $res = $request->fetch_object();
    if($res->nb > 0)
        $return .= _LISTMODEL_ID_ALREADY_USED . "<br/>";
}

# Load into session
$_SESSION['m_admin']['entity']['listmodel_info']['object_type'] = $objectType;
$_SESSION['m_admin']['entity']['listmodel_info']['object_id'] = $objectId;
$_SESSION['m_admin']['entity']['listmodel_info']['coll_id'] = $collId;
$_SESSION['m_admin']['entity']['listmodel_info']['listmodel_type'] = $listmodelType;
$_SESSION['m_admin']['entity']['listmodel_info']['description'] = $description;

# Return messages
echo $return;