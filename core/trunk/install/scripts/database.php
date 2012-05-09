<?php


$_SESSION['config']['databaseserver'] =  $_REQUEST['databaseserver'];
$_SESSION['config']['databaseserverport'] = $_REQUEST['databaseserverport'];
$_SESSION['config']['databaseuser'] = $_REQUEST['databaseuser'];
$_SESSION['config']['databasepassword'] = $_REQUEST['databasepassword'];
$_SESSION['config']['databasename'] = $_REQUEST['databasename'];
$_SESSION['config']['databasetype'] = $_REQUEST['databasetype'];

$checkDatabaseParameters = $Class_Install->checkDatabaseParameters(
    $_REQUEST['databaseserver'],
    $_REQUEST['databaseserverport'],
    $_REQUEST['databaseuser'],
    $_REQUEST['databasepassword'],
    $_REQUEST['databasename'],
    $_REQUEST['databasetype']
);

if (!$checkDatabaseParameters) {
    $return['status'] = 1;
    $return['text'] = 'CheckDatabaseParameters fail';

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}

$return['status'] = 1;
$return['text'] = 'checkDatabaseParameters OK';

$jsonReturn = json_encode($return);

echo $jsonReturn;
exit;
