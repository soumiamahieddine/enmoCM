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
    $func = new functions();
    if (!isset($docserverId)
        || empty($docserverId)
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

//////////////
$messageController->loadMessageFile(
    'apps/maarch_entreprise/admin/docservers/xml/docservers_Messages.xml'
);

if (!is_dir($dataObject->path_template)) {
    $this->messages[] = $messageController->createMessage(
        'docservers.error.path_template.unapproachable'
    );
} else {
    if (!is_writable($dataObject->path_template)
        || !is_readable($dataObject->path_template)
    ) {
        $this->messages[] = $messageController->createMessage(
            'docservers.error.path_template.no_rights'
        );
    }
}

if (!adrPriorityNumberControl(
    $dataObject->docserver_id, 
    $dataObject->docserver_type_id, 
    $dataObject->adr_priority_number)
) {
    $this->messages[] = $messageController->createMessage(
        'docservers.error.adr_priority_number.duplicate',
        false,
        array(
            $dataObject->adr_priority_number
        )
    );
}

if (!priorityNumberControl(
    $dataObject->docserver_id, 
    $dataObject->docserver_type_id, 
    $dataObject->priority_number)
) {
    $this->messages[] = $messageController->createMessage(
        'docservers.error.priority_number.duplicate',
        false,
        array(
            $dataObject->priority_number
        )
    );
}
