<?php
$_REQUEST['docserverRoot'] = str_replace("/", DIRECTORY_SEPARATOR, $_REQUEST['docserverRoot']);

$checkDocserverRoot = $Class_Install->checkDocserverRoot(
    $_REQUEST['docserverRoot']
);

if ($checkDocserverRoot !== true) {
    $return['status'] = 0;
    $return['text'] = $checkDocserverRoot;

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}

if (!$Class_Install->createDocservers($_REQUEST['docserverRoot'])) {
    $return['status'] = 0;
    $return['text'] = _CAN_NOT_CREATE_SUB_DOCSERVERS;

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}

$updateDocserversDB = $Class_Install->updateDocserversDB(
    $_REQUEST['docserverRoot']
);

$return['status'] = 1;
$return['text'] = '';

$jsonReturn = json_encode($return);

echo $jsonReturn;
exit;
