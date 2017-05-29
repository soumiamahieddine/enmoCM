<?php
require_once '../../core/init.php';
require_once 'install/class/Class_Install.php';

$Class_Install = new Install();
$listLang = $Class_Install->loadLang();

$version = $_REQUEST['version'];
//retrieve required sources for Maarch labs
$versionPath = $version;
$versionFile = $version . '.tar.gz';
if(!file_exists($dependPath)) {
    file_put_contents(
        $versionFile , 
        fopen("https://labs.maarch.org/maarch/MaarchCourrier/repository/archive.tar.gz?ref=" . $version, 'r')
    );
}

if (!file_exists($versionFile)) {
    echo '{"status":1, "' . _VERSION_NOT_DOWNLOADED . '"}';
    exit();
}

umask(0022);
if (!is_dir($versionPath)) {
    mkdir($versionPath, 0770);
}

$phar = new PharData($versionFile);
$phar->extractTo($versionPath, null, true);

$fileTab = scandir($versionPath);
$nbFiles = count($fileTab);
for ($n = 0; $n < count($fileTab); $n++) {
    $currentFileName = array();
    if (
        $fileTab[$n] <> '.' 
        && $fileTab[$n] <> '..'
        && is_dir($versionPath . '/' . $fileTab[$n])
    ) {
        $finalVersionPath = $versionPath . '/' . $fileTab[$n];
    }
}

if (empty($finalVersionPath)) {
    echo '{"status":1, "message" : "' . _VERSION_NOT_EXTRACTED . ' (1)"}';
    exit();
} else {
    //Ds_washTmp($versionPath);
    unlink($versionFile);
    echo '{"status":0, "finalVersionPath" : "' . $finalVersionPath . '"}';
}
exit;
