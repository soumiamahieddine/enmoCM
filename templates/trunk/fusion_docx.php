<?php

include_once('tbs_class_php5.php');
include_once('tbs_plugin_opentbs.php');

$_POST['tpl'] = 'modules/templates/templates/styles/another_test.docx';
//$_POST['yourname'] = 'Laurent Giovannoni';

global $yourname;
global $company;

$company = 'MAARCH';

$TBS = new clsTinyButStrong; // new instance of TBS
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

$suffix = (isset($_POST['suffix']) && (trim($_POST['suffix'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['suffix']) : '';
$debug = (isset($_POST['debug'])) ? intval($_POST['debug']) : 0;

// Retrieve the template to open
$template = (isset($_POST['tpl'])) ? $_POST['tpl'] : '';

if (!file_exists($template)) exit("File does not exist.");

// Retrieve the user name to display
$yourname = (isset($_POST['yourname'])) ? $_POST['yourname'] : '';
$yourname = trim(''.$yourname);
if ($yourname=='') $yourname = "(no name)";

//echo $yourname;

// Load the template
$TBS->LoadTemplate($template);

if ($debug==2) { // debug mode 2
    $TBS->Plugin(OPENTBS_DEBUG_XML_CURRENT);
    exit;
} elseif ($debug==1) { // debug mode 1
    $TBS->Plugin(OPENTBS_DEBUG_INFO);
    exit;
}

// Define the name of the output file
$file_name = str_replace('.','_'.date('Y-m-d').'.',$template);

// Output as a download file (some automatic fields are merged here)
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

