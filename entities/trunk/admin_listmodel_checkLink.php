<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");
$request = new request();
$request->connect();

$objectType = $_REQUEST['objectType'];
$objectId = $_REQUEST['objectId'];
$collId = $_REQUEST['collId'];

# Check if type/id already used
if(!empty($objectId)) {
    $request->query(
        "select count(1) as nb from " . ENT_LISTMODELS
        . " where object_type = '" . $objectType . "'"
        . " and object_id = '" . $objectId . "'"
        . " and coll_id = '" . $collId . "'"
    );
    $res = $request->fetch_object();
    echo (string)$res->nb;
    return;
}
echo "0";
