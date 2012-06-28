<?php
//specific test

require_once 'core/class/class_functions.php';
require_once 'core/class/class_db.php';
require_once 'core/core_tables.php';

/**
* Check if two docservers have the same priorities
*
* @param $docserver docservers object
* @return bool true if the control is ok
*/
function adrPriorityNumberControl($docserverId, $docserverTypeId, $adrPriorityNumber)
{
    $adrPriorityNumber = (int) $adrPriorityNumber;
    $func = new functions();
    if (!isset($docserverId)
        || empty($docserverId)
        || !is_int($adrPriorityNumber)
    ) {
        return false;
    }
    $db = new dbquery();
    $db->connect();
    $query = "select adr_priority_number from " . _DOCSERVERS_TABLE_NAME
           . " where adr_priority_number = "
           . $adrPriorityNumber
           . " AND docserver_type_id = '"
           . $func->protect_string_db($docserverTypeId) . "'"
           . " AND docserver_id <> '"
           . $func->protect_string_db($docserverId) . "'";
    $db->query($query);
    if ($db->nb_result() > 0) {
        $db->disconnect();
        return false;
    }
    $db->disconnect();
    return true;
}

/**
* Check if two docservers have the same priorities
*
* @param $docserver docservers object
* @return bool true if the control is ok
*/
function priorityNumberControl($docserverId, $docserverTypeId, $priorityNumber)
{
    $priorityNumber = (int) $priorityNumber;
    $func = new functions();
    if (!isset($docserverId)
        || empty($docserverId)
        || !is_int($priorityNumber)
    ) {
        return false;
    }
    $db = new dbquery();
    $db->connect();
    $query = "select priority_number from " . _DOCSERVERS_TABLE_NAME
           . " where priority_number = "
           . $priorityNumber
           . " AND docserver_type_id = '"
           . $func->protect_string_db($docserverTypeId) . "'"
           . " AND docserver_id <> '"
           . $func->protect_string_db($docserverId) . "'";
    $db->query($query);
    if ($db->nb_result() > 0) {
        $db->disconnect();
        return false;
    }
    $db->disconnect();
    return true;
}

if (!is_dir($_REQUEST['path_template'])) {
    $errors[] = "'path_template' " . _PATH_OF_DOCSERVER_UNAPPROACHABLE;
} else {
    if (!is_writable($_REQUEST['path_template'])
        || !is_readable($_REQUEST['path_template'])
    ) {
        $errors[] = "'path_template' " 
            . _THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS;
    }
}

if (!adrPriorityNumberControl(
    $_REQUEST['docserver_id'], 
    $_REQUEST['docserver_type_id'], 
    $_REQUEST['adr_priority_number'])
) {
    $errors[] = "'adr_priority_number' " 
            . _ADR_PRIORITY . ' ' . $_REQUEST['adr_priority_number'] . ' '
            . _ALREADY_EXISTS_FOR_THIS_TYPE_OF_DOCSERVER;
}

if (!priorityNumberControl(
    $_REQUEST['docserver_id'], 
    $_REQUEST['docserver_type_id'], 
    $_REQUEST['priority_number'])
) {
    $errors[] = "'priority_number' " 
            . _PRIORITY . ' ' . $_REQUEST['priority_number'] . ' '
            . _ALREADY_EXISTS_FOR_THIS_TYPE_OF_DOCSERVER;
}
