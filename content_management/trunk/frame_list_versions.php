<?php
require_once 'core/class/class_request.php';
require_once 'core/class/class_security.php';
require_once 'apps/' . $_SESSION['config']['app_id']
    . '/class/class_list_show.php';
$core = new core_tools();
$core->load_lang();
$security = new security();
$versionTable = $security->retrieve_version_table_from_coll_id(
    $_REQUEST['collId']
);
$selectVersions[$versionTable] = array();
array_push(
    $selectVersions[$versionTable], 
    'res_id', 
    'typist',
    'creation_date'
);
$whereClause = " res_id_master = " . $_REQUEST['resMasterId'] . " ";
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

for ($indVersion = 0;$indVersion < count($tabVersions);$indVersion ++ ) {
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
        }
    }
}
$core->load_html();
//here we building the header
$core->load_header('', true, false);
?>
<body id="<?php echo $body;?>">
    <?php
    $title = '';
    $listVersions = new list_show();
    $listVersions->list_simple(
        $tabVersions, 
        count($tabVersions), 
        $title, 
        'res_id', 
        'res_id', 
        true, 
        $_SESSION['config']['businessappurl'] 
            . 'index.php?display=true&amp;dir=indexing_searching&amp;page=view_resource_controler&versionTable=' . $versionTable, 
        $css
    );
    $core->load_js();
    ?>
</body>
</html>
