<?php
/*
listmodel_type_id : $('listmodel_type_id').value,
listmodel_type_label : $('listmodel_type_label').value,
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core/core_tables.php");
$request = new request();
$request->connect();

$mode = $_REQUEST['mode']; 
$id = $request->protect_string_db($_REQUEST['id']); 
$description = $request->protect_string_db($_REQUEST['description']);
$param_value_string = $_REQUEST['param_value_string'];
$param_value_int = $_REQUEST['param_value_int'];
$param_value_date = $_REQUEST['param_value_date'];

$type = $_REQUEST['type'];

switch($type) {
case 'string':
    $column = 'param_value_string';
    $value = (string)$request->protect_string_db($param_value_string);
    break;
    
case 'int':
    $column = 'param_value_int';
    $value = (integer)$param_value_int;
    break;

case 'date':
    $column = 'param_value_date';
    $value = $param_value_date;
    break;
}


# If no error, proceed
switch($_REQUEST['mode']) {
case 'add':
    $res = $request->query(
        "insert into " . PARAM_TABLE
            . " (id, description, ".$column.")"
            . " values (" 
                . "'" . $id . "',"
                . "'" . $description .  "', "
                . "'" . $value .  "'"
            .")"
    );
    break;
    
case 'up':
    $res = $request->query(
        "update " . PARAM_TABLE 
        . " set "
            . "description = '" . $description .  "', "
            . $column. " = '" . $value . "' "
        . "where id = '" . $id . "'"
    );
    break;
    
case 'del':
    $res = $request->query(
        "delete from " . PARAM_TABLE 
        . " where id = '" . $id . "'"
    );
    break;

}