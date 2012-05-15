<?php

if ($_REQUEST['action'] == 'testConnect') {
    $_SESSION['config']['databaseserver']     = $_REQUEST['databaseserver'];
    $_SESSION['config']['databaseserverport'] = $_REQUEST['databaseserverport'];
    $_SESSION['config']['databaseuser']       = $_REQUEST['databaseuser'];
    $_SESSION['config']['databasepassword']   = $_REQUEST['databasepassword'];
    $_SESSION['config']['databasetype']       = $_REQUEST['databasetype'];

    $checkDatabaseParameters = $Class_Install->checkDatabaseParameters(
        $_REQUEST['databaseserver'],
        $_REQUEST['databaseserverport'],
        $_REQUEST['databaseuser'],
        $_REQUEST['databasepassword'],
        $_REQUEST['databasetype']
    );

    if (!$checkDatabaseParameters) {
        $return['status'] = 0;
        $return['text'] = _BAD_INFORMATIONS_FOR_CONNECTION;

        $jsonReturn = json_encode($return);

        echo $jsonReturn;
        exit;
    }

    $return['status'] = 1;
    $return['text'] = '';

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
} elseif ($_REQUEST['action'] == 'createdatabase') {

    $_SESSION['config']['databasename'] = $_REQUEST['databasename'];

    $createDatabase = $Class_Install->createDatabase(
        $_REQUEST['databasename']
    );

    if (!$createDatabase) {
        $return['status'] = 0;
        $return['text'] = _UNABLE_TO_CREATE_DATABASE;

        $jsonReturn = json_encode($return);

        echo $jsonReturn;
        exit;
    }

    $return['status'] = 1;
    $return['text'] = '';

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
} elseif ($_REQUEST['action'] == 'loadDatas') {

    $loadDatas = $Class_Install->createData(
        $_REQUEST['dataFilename'].'.sql'
    );

    if (!$loadDatas) {
        $return['status'] = 0;
        $return['text'] = _UNABLE_TO_LOAD_DATAS;

        $jsonReturn = json_encode($return);

        echo $jsonReturn;
        exit;
    }

    $return['status'] = 1;
    $return['text'] = '';

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}
