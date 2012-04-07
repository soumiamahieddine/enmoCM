<?php

include_once 'apps/maarch_entreprise/tools/tbs/tbs_class_php5.php' ;
include_once 'apps/maarch_entreprise/tools/tbs/tbs_plugin_opentbs.php';

//$template = 'demo_response_open_document.odt';
$template = $filePathOnTmp;

global $CONTACT_TITLE;
global $CONTACT_FIRSTNAME;
global $CONTACT_LASTNAME;
global $CONTACT_SOCIETY;
global $CONTACT_ADRS_NUM;
global $CONTACT_ADRS_STREET;
global $CONTACT_ADRS_PC;
global $CONTACT_ADRS_TOWN;
global $DESTINATION;
global $USER_FIRSTNAME;
global $USER_LASTNAME;
global $SUBJECT;
global $DOC_DATE;

$CONTACT_TITLE = 'Monsieur';
$CONTACT_FIRSTNAME = 'Robert';
$CONTACT_LASTNAME = 'Duverne';
$CONTACT_SOCIETY = 'PIONS';
$CONTACT_ADRS_NUM = '21';
$CONTACT_ADRS_STREET = 'Cours Mirabeau';
$CONTACT_ADRS_PC = '13500';
$CONTACT_ADRS_TOWN = 'Aix En Provence';
$DESTINATION = 'Direction des Eaux';
$USER_FIRSTNAME = 'Peter';
$USER_LASTNAME = 'Parker';
$SUBJECT = 'Fuite d\'eau dans le gymnase Municipale';
$DOC_DATE = '12/01/2012';

$TBS = new clsTinyButStrong; // new instance of TBS
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

$suffix = (isset($_POST['suffix']) && (trim($_POST['suffix'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['suffix']) : '';
$debug = (isset($_POST['debug'])) ? intval($_POST['debug']) : 0;

if (!file_exists($template)) exit('File does not exist.');

// Load the template
$TBS->LoadTemplate($template);

if ($debug==2) { // debug mode 2
    $TBS->Plugin(OPENTBS_DEBUG_XML_CURRENT);
    exit;
} elseif ($debug==1) { // debug mode 1
    $TBS->Plugin(OPENTBS_DEBUG_INFO);
    exit;
}

$fileName = $_SESSION['config']['tmppath'] . 'merged_' . basename($template);
$TBS->Show(OPENTBS_FILE, $fileName);

$filePathOnTmp = $fileName;
//createXML('ERROR', $_SESSION['config']['tmppath'] . 'merged_' . basename($template));
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
