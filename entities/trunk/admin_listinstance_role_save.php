<?php
/*
mode : $('mode').value,
role_id : $('role_id').value,
role_label : $('role_label').value,
list_label : $('list_label').value,
list_img : $('list_img').value,
allow_entities : $('allow_entities').checked
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");
$request = new request();
$request->connect();

$role_id        = $request->protect_string_db($_REQUEST['role_id']); 
$role_label     = $request->protect_string_db($_REQUEST['role_label']);
$list_label     = $request->protect_string_db($_REQUEST['list_label']);
$workflow_mode  = $request->protect_string_db($_REQUEST['workflow_mode']);
$list_img       = $request->protect_string_db($_REQUEST['list_img']);
if($_REQUEST['allow_entities'] == 'true')
    $allow_entities = 'Y';
else if($_REQUEST['allow_entities'] == 'false')
    $allow_entities = 'N';

# Controls
$errors = false;
if(!empty($role_id)) {
    $request->query(
        "select count(1) as nb from " . ENT_LISTINSTANCE_ROLES
            . " where role_id = '" . $role_id . "'"
    );
    $res = $request->fetch_object();
    $nb = $res->nb;
    if($_REQUEST['mode'] == 'add' && $nb == 1)
        $errors .= "<br/>" . _LISTINSTANCE_ROLE_ID_ALREADY_USED;
    if($_REQUEST['mode'] == 'up' && $nb == 0)
        $errors .= "<br/>" . _LISTINSTANCE_ROLE_ID_UNKNOWN;
} else {
        $errors .= "<br/>" . _LISTINSTANCE_ROLE_ID_IS_MANDATORY;
}


if(empty($role_label))
    $errors .= "<br/>" . _LISTINSTANCE_ROLE_LABEL_IS_MANDATORY;
if(empty($list_label))
    $errors .= "<br/>" . _LISTINSTANCE_ROLE_LIST_LABEL_IS_MANDATORY; 

# If errors detected, return html
if($errors) {
    echo $errors;
    return;
}

# If no error, proceed
switch($_REQUEST['mode']) {
case 'add':
    $res = $request->query(
        "insert into " . ENT_LISTINSTANCE_ROLES
            . " (role_id, role_label, list_label, workflow_mode, list_img, allow_entities)"
            . " values (" 
                . "'" . $role_id . "',"
                . "'" . $role_label . "',"
                . "'" . $list_label . "',"
                . "'" . $workflow_mode . "',"
                . "'" . $list_img . "',"
                . "'" . $allow_entities. "')"
    );
    
    break;
case 'up':
    $res = $request->query(
        "update " . ENT_LISTINSTANCE_ROLES 
            . " set "
                . " role_label = '" . $role_label . "',"
                . " list_label = '" . $list_label . "',"
                . " list_img = '" . $list_img . "',"
                . " workflow_mode = '" . $workflow_mode . "',"
                . " allow_entities = '" . $allow_entities. "'"
            . " where role_id = '" . $role_id . "'"
    );
    break;

}