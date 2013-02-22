<?php
/*
listmodel_type_id : $('listmodel_type_id').value,
listmodel_type_label : $('listmodel_type_label').value,
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");
$request = new request();
$request->connect();

$listmodel_type_id    = $request->protect_string_db($_REQUEST['listmodel_type_id']); 
$listmodel_type_label = $request->protect_string_db($_REQUEST['listmodel_type_label']);

# Controls
$errors = false;
if(!empty($listmodel_type_id)) {
    $request->query(
        "select count(1) as nb from " . ENT_LISTMODEL_TYPES
            . " where listmodel_type_id = '" . $listmodel_type_id . "'"
    );
    $res = $request->fetch_object();
    $nb = $res->nb;
    if($_REQUEST['mode'] == 'add' && $nb == 1)
        $errors .= "<br/>" . _LISTMODEL_TYPE_ID_ALREADY_USED;
    if($_REQUEST['mode'] == 'up' && $nb == 0)
        $errors .= "<br/>" . _LISTMODEL_TYPE_ID_IS_UNKNOWN;
} else {
        $errors .= "<br/>" . _LISTMODEL_TYPE_ID_IS_MANDATORY;
}


if(empty($listmodel_type_label))
    $errors .= "<br/>" . _LISTMODEL_TYPE_LABEL_IS_MANDATORY;


# If errors detected, return html
if($errors) {
    echo $errors;
    return;
}

# If no error, proceed
switch($_REQUEST['mode']) {
case 'add':
    $res = $request->query(
        "insert into " . ENT_LISTMODEL_TYPES
            . " (listmodel_type_id, listmodel_type_label)"
            . " values (" 
                . "'" . $listmodel_type_id . "',"
                . "'" . $listmodel_type_label .  "')"
    );
    
    break;
case 'up':
    $res = $request->query(
        "update " . ENT_LISTMODEL_TYPES 
            . " set "
                . " listmodel_type_label = '" . $listmodel_type_label . "'"
            . " where listmodel_type_id = '" . $listmodel_type_id . "'"
    );
    break;

}