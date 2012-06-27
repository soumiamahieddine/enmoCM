<?php
function updateObject($request, $object)
{
    foreach($object as $key => $value) {
        if (isset($request[$key])) {
            $object->$key = $request[$key];
        }
    }
}

require_once 'core/class/class_core_tools.php';
$coreTools = new core_tools();
$coreTools->load_lang();

require_once('core/tests/class/DataObjectController.php');
$DataObjectController = new DataObjectController();
$DataObjectController->loadSchema($_REQUEST['schemaPathAjax']);


//specifique test


$dataObject = $DataObjectController->unserialize(
    $_SESSION['m_admin'][$_REQUEST['objectNameAjax']]
);

updateObject($_REQUEST, $dataObject);

$validateObject = $DataObjectController->validate(
    $dataObject
);

if ($validateObject) {
    $DataObjectController->save($dataObject);
    $return['status'] = 1;
} else {
    $failFields = array();
    foreach($DataObjectController->getValidationErrors() as $error) {
        $errors[] = $error->message;
        $fail = explode('\'', $error->message);
        array_push($failFields, $fail[1]);
    }
    
    $messages = '<br /><br /><table cellspacing="0" cellpadding="5" width="70%" align="center">';
    for ($i=0; $i<count($errors); $i++) {
        $messages .= '<tr>';
            $messages .= '<td style="text-align: left;">';
                $messages .= '<b>';
                    $messages .= $errors[$i];
                $messages .= '</b>';
            $messages .= '</td>';
        $messages .= '</tr>'; 
    }
    $messages .= '</table><br />';
    
    $return['status']   = 0;
    $return['failFields'] = $failFields;
    $return['messages'] = $messages;
}

echo json_encode($return);

