<?php
/**
* File : change_doctype.php
*
* Script called by an ajax object to process the document type change during
* indexing (index_mlb.php)
*
* @package  maarch
* @version 1
* @since 10/2005
* @license GPL v3
* @author  Cyril Vazquez  <dev@maarch.org>
*/
require_once 'modules/entities/class/class_manage_listdiff.php';

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$diffList = new diffusion_list();

$objectType = $_REQUEST['objectType'];
$objectId = $_REQUEST['objectId'];
$origin = $_REQUEST['origin'];

// Get listmodel_parameters
$_SESSION[$origin]['difflist_type'] = $diffList->get_difflist_type($objectType);

// Fill session with listmodel
$_SESSION[$origin]['diff_list'] = $diffList->get_listmodel($objectType, $objectId);

$roles = $diffList->list_difflist_roles();
$difflist = $_SESSION[$origin]['diff_list'];

$content = '';
if (! $onlyCC) {
    if (isset($_SESSION['validStep']) && $_SESSION['validStep'] == 'ok') {
        $content .= "";
    } 
}

# Get content from buffer of difflist_display 
ob_start();
require_once 'modules/entities/difflist_display.php';
$content .= str_replace(array("\r", "\n", "\t"), array("", "", ""), ob_get_contents());
ob_end_clean();

$labelButton = _MODIFY_LIST;
$arg = '&mode=up';

if ($onlyCC) {
    $arg .= '&only_cc';
}
$content_standard = '<center><b>' . _DIFF_LIST . '</b> | ';
$content_standard .= '<span class="button" >';
$content_standard .= '<img src="' . $_SESSION['config']['businessappurl']
         . 'static.php?filename=modif_liste.png&module=entities" alt="" />'
         . '<a href="javascript://" onclick="window.open(\''
         . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
         . '&module=entities&page=manage_listinstance&origin=' . $origin . $arg
         . '\', \'\', \'scrollbars=yes,menubar=no,toolbar=no,status=no,'
         . 'resizable=yes,width=1280,height=800,location=no\');"><small>'
         . $labelButton . '</small></a>';
$content_standard .= '</span></center>';

echo "{status : 0, div_content : '" . addslashes($content_standard . $content . '<br>') 
    . "', div_content_action : '" . addslashes($content) . "'}";
exit();
