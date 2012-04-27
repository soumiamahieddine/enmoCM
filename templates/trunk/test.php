<?php

// SET TRUE IN DEBUG MODE
$debug = true;

include_once 'apps/maarch_entreprise/tools/tbs/tbs_class_php5.php';
include_once 'apps/maarch_entreprise/tools/tbs/tbs_plugin_opentbs.php';

require_once 'modules/templates/class/templates_controler.php';
require_once 'modules/templates/datasources/attachment.php';

$templates_controler = new templates_controler();
$template = $filePathOnTmp;

// new instance of TBS
$TBS = new clsTinyButStrong;

if ($debug) {
    //FOR THE TESTS ONLY !
    $template = 'modules/templates/demo_document_msoffice.docx';
    $_SESSION['cm']['resMaster'] = 134;
    $_SESSION['cm']['collId'] = 'letterbox_coll';
    $TBS->NoErr = false;
}

/*$fusionVars = $templates_controler->fieldsReplace(
    '', $_SESSION['cm']['resMaster'], $_SESSION['cm']['collId'], true
);*/

/*
for ($cpt=0;$cpt<count($fusionVars);$cpt++) {
    $var_name = str_replace('[', '', $fusionVars[$cpt]['var_name']);
    $var_name = str_replace(']', '', $var_name);
    if ($debug) {
        echo $var_name . ':' . $fusionVars[$cpt]['value'] . '<br>';
    }
    global $$var_name;
    if ($fusionVars[$cpt]['value'] == '') {
        $fusionVars[$cpt]['value'] = ' ';
    }
    $$var_name = utf8_decode($fusionVars[$cpt]['value']);
    $$var_name = html_entity_decode($$var_name);
}*/

// load OpenTBS plugin
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

$suffix = (isset($_POST['suffix']) && (trim($_POST['suffix'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['suffix']) : '';
$debug = (isset($_POST['debug'])) ? intval($_POST['debug']) : 0;

if (!file_exists($template)) exit('File does not exist.');

$datasources = array();

$datasources = getDatasource(112, 'res_view_letterbox', 'letterbox_coll');

// Load the template
$TBS->LoadTemplate($template);

foreach ($datasources as $name => $datasource) {
    $TBS->MergeBlock($name, $datasources[$name]);
}
// debug mode 2
if ($debug==2) {
    $TBS->Plugin(OPENTBS_DEBUG_XML_CURRENT);
    exit;
} elseif ($debug==1) {
    // debug mode 1
    $TBS->Plugin(OPENTBS_DEBUG_INFO);
    exit;
}

$fileName = $_SESSION['config']['tmppath'] . 'merged_' . basename($template);

//$TBS->Show(OPENTBS_FILE, $fileName);
$TBS->Show(OPENTBS_STRING);

$myContent = $TBS->Source;

echo $myContent;

$filePathOnTmp = $fileName;
// Output as a download file (some automatic fields are merged here)
/*
if ($debug==3) { // debug mode 3
    $TBS->Plugin(OPENTBS_DEBUG_XML_SHOW);
} elseif ($suffix==='') {
    // download
    $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
} else {
    // save as file
    $file_name = str_replace('.','_'.$suffix.'.',$file_name);
    $TBS->Show(OPENTBS_FILE+TBS_EXIT, $file_name);
}
*/
