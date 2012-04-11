<?php

require_once '../../core/init.php';
require_once 'core/class/class_functions.php';
require_once 'core/class/class_core_tools.php';
require_once 'core/class/class_db.php';
require_once 'core/class/class_request.php';
require_once 'core/class/class_security.php';
require_once 'apps/' . $_SESSION['config']['app_id']
    . '/class/class_list_show.php';
$core = new core_tools();
$core->load_lang();
$security = new security();
$versionTable = $security->retrieve_version_table_from_coll_id(
    $_SESSION['collection_id_choice']
);
$selectVersions[$versionTable] = array();
array_push(
    $selectVersions[$versionTable],
    'res_id',
    'relation',
    'typist',
    'creation_date'
);
$whereClause = " res_id_master = " . $_SESSION['doc_id']
    . " and status <> 'DEL'";
$requestVersions = new request();
$tabVersions = $requestVersions->select(
    $selectVersions,
    $whereClause,
    ' order by res_id desc',
    $_SESSION['config']['databasetype'],
    '500',
    false,
    $versionTable
);

$sizeMedium = '15';
$sizeSmall = '15';
$sizeFull = '70';
$css = 'listing spec detailtabricatordebug';
$body = '';
$cutString = 100;
$extendUrl = '&size=full';

for ($indVersion=0;$indVersion<count($tabVersions);$indVersion++) {
    for ($indVersionBis = 0;$indVersionBis < count($tabVersions[$indVersion]);$indVersionBis ++) {
        foreach (array_keys($tabVersions[$indVersion][$indVersionBis]) as $value) {
            if ($tabVersions[$indVersion][$indVersionBis][$value] == 'res_id') {
                $tabVersions[$indVersion][$indVersionBis]['res_id']
                    = $tabVersions[$indVersion][$indVersionBis]['value'];
                $tabVersions[$indVersion][$indVersionBis]['label'] = 'ID';
                $tabVersions[$indVersion][$indVersionBis]['size'] = $sizeSmall;
                $tabVersions[$indVersion][$indVersionBis]['label_align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['valign'] = 'bottom';
                $tabVersions[$indVersion][$indVersionBis]['show'] = true;
                $indVersiond = $tabVersions[$indVersion][$indVersionBis]['value'];
            }
            if ($tabVersions[$indVersion][$indVersionBis][$value] == 'typist') {
                $tabVersions[$indVersion][$indVersionBis]['label'] = _TYPIST;
                $tabVersions[$indVersion][$indVersionBis]['size'] = $sizeSmall;
                $tabVersions[$indVersion][$indVersionBis]['label_align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['valign'] = 'bottom';
                $tabVersions[$indVersion][$indVersionBis]['show'] = true;
            }
            if ($tabVersions[$indVersion][$indVersionBis][$value] == 'creation_date') {
                $tabVersions[$indVersion][$indVersionBis]['value'] = $requestVersions->format_date_db(
                    $tabVersions[$indVersion][$indVersionBis]['value']
                );
                $tabVersions[$indVersion][$indVersionBis]['label'] = _CREATION_DATE;
                $tabVersions[$indVersion][$indVersionBis]['size'] = $sizeBig;
                $tabVersions[$indVersion][$indVersionBis]['label_align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['valign'] = 'bottom';
                $tabVersions[$indVersion][$indVersionBis]['show'] = true;
            }
            if ($tabVersions[$indVersion][$indVersionBis][$value] == 'relation') {
                $tabVersions[$indVersion][$indVersionBis]['label'] = _VERSION;
                $tabVersions[$indVersion][$indVersionBis]['size'] = $sizeSmall;
                $tabVersions[$indVersion][$indVersionBis]['label_align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['align'] = 'left';
                $tabVersions[$indVersion][$indVersionBis]['valign'] = 'bottom';
                $tabVersions[$indVersion][$indVersionBis]['show'] = true;
            }
        }
    }
}

if ($tabVersions[0][0]['res_id'] == '') {
    $objectId = $_SESSION['cm']['objectId4List'];
    $_SESSION['cm']['objectId4List'] = '';
    $objectTable = $_SESSION['cm']['objectTable'];
} else {
    $objectTable = $versionTable;
    $objectId = $tabVersions[0][0]['res_id'];
}

$createDiv = '<a href="#" onClick="loadApplet(\''
    . $_SESSION['config']['coreurl']
    . 'modules/content_management/applet_launcher.php?objectType=resource&objectId='
    . $objectId
    . '&objectTable='
    . $objectTable
    . '&resMaster='
    .  $_SESSION['doc_id']
    . '\');">'
    . '<img alt="' . _CREATE_NEW_VERSION . '" src="'
    . $_SESSION['config']['businessappurl']
    . 'static.php?filename=modif_note.png&module=notes" border="0" alt="" />'
    . _CREATE_NEW_VERSION
    . '</a>';

$title = '';
$listVersions = new list_show();

$formatText = $listVersions->list_simple(
    $tabVersions,
    count($tabVersions),
    $title,
    'res_id',
    'res_id',
    true,
    $_SESSION['config']['businessappurl']
        . 'index.php?display=true&amp;dir=indexing_searching&amp;'
        . 'page=view_resource_controler&aVersion=',
    $css,
    '',
    '',
    '',
    '',
    true
);

echo "{status : 0, list : '" . addslashes($formatText) . "', nb : '"
    . count($tabVersions) . "', create : '" . addslashes($createDiv) . "'}";
exit();
