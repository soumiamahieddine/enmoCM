<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once '../../core/init.php';
require_once 'install/class/Class_Install.php';

$Class_Install = new Install();
$listLang = $Class_Install->loadLang();

//retrieve required dependencies for Maarch labs
$dependPath = 'dependencies';
if(!file_exists('dependencies.zip')) {
    file_put_contents(
        'dependencies.zip', 
        fopen("https://labs.maarch.org/maarch/LibsExtMaarchCourrier/repository/archive.zip?ref=v17.06", 'r')
    );
}

if (!file_exists('dependencies.zip')) {
    echo '{"status":1, "' . _DEPENDENCIES_NOT_DOWNLOADED . '"}';
    exit();
}

umask(0022);
if (!is_dir($dependPath)) {
    mkdir($dependPath, 0770);
}

$zip = new ZipArchive;
if ($zip->open('dependencies.zip') === TRUE) {
    $zip->extractTo($dependPath);
    $zip->close();
} else {
    $return['status'] = 0;
    $return['text'] = _CANNOT_EXTRACT;

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}
//exit;
//$phar = new PharData('dependencies.zip');
//$phar->extractTo($dependPath, null, true);

$fileTab = scandir($dependPath);
$nbFiles = count($fileTab);
for ($n = 0; $n < count($fileTab); $n++) {
    $currentFileName = array();
    if (
        $fileTab[$n] <> '.' 
        && $fileTab[$n] <> '..'
        && is_dir($dependPath . '/' . $fileTab[$n])
    ) {
        $finalDependPath = $dependPath . '/' . $fileTab[$n];
    }
}

if (!$Class_Install->copy_dir(
        $finalDependPath . DIRECTORY_SEPARATOR,
        $_SESSION['config']['corepath']
    )
) {
    $return['status'] = 0;
    $return['text'] = _CAN_NOT_COPY_TO . ':' . $_SESSION['config']['corepath'];

    $jsonReturn = json_encode($return);

    echo $jsonReturn;
    exit;
}

if (!file_exists('vendor/') && !file_exists('node_modules/')) {
    echo '{"status":1, "message" : "3 ' . _DEPENDENCIES_NOT_EXTRACTED . ' (3)"}';
} else {
    include_once 'core/docservers_tools.php';
    Ds_washTmp($dependPath);
    unlink('dependencies.zip');
    echo '{"status":0}';
}

