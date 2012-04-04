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
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once 'modules' . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR
    . 'class' . DIRECTORY_SEPARATOR . 'class_manage_listdiff.php';

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$diffList = new diffusion_list();

if ((! isset($_REQUEST['id_entity']) || empty($_REQUEST['id_entity']))
    && $_REQUEST['load_from_model'] == 'true'
) {
    if ($_REQUEST['mandatory'] <> 'none') {
        $_SESSION['error'] = _ENTITY_ID . ' ' . _IS_EMPTY;
    }
    $_SESSION['indexing']['diff_list'] = array();
    echo "{status : 1, error_txt : '" . addslashes($_SESSION['error']) . "'}";
    exit();
}

if (empty($_REQUEST['origin'])) {
    $_SESSION['error'] = _ORIGIN . ' ' . _UNKNOWN;
    echo "{status : 2, error_txt : '" . addslashes($_SESSION['error']) . "'}";
    exit();
}
$onlyCC = false;
if (isset($_REQUEST['only_cc'])) {
    $onlyCC = true;
}

$origin = $_REQUEST['origin'];
if ($_REQUEST['load_from_model'] == 'true') {
    $_SESSION[$origin]['diff_list'] = $diffList->get_listmodel_from_entity(
        $_REQUEST['id_entity']
    );
}

$content = '';
if (! $onlyCC) {
    if (isset($_SESSION['validStep']) && $_SESSION['validStep'] == 'ok') {
        $content .= "";
    } else {
        $content .= '<h2>' . _LINKED_DIFF_LIST . ' : </h2>';
    }
}

if (isset($_SESSION[$origin]['diff_list']['dest']['user_id'])
    && ! empty($_SESSION[$origin]['diff_list']['dest']['user_id'])
) {
    if (! $onlyCC) {
        $content .= '<p class="sstit">' . _RECIPIENT . '</p>';
        $content .= '<table cellpadding="0" cellspacing="0" border="0" class="listing spec detailtabricatordebug">';
        $content .= '<tr class="col">';
        $content .= '<td><img src="' . $_SESSION['config']['businessappurl']
                 . 'static.php?filename=manage_users_entities_b_small.gif'
                 . '&module=entities" alt="' . _USER . '" title="'
                 . _USER . '" /></td>';
        $content .= '<td >' . $_SESSION[$origin]['diff_list']['dest']['firstname']
                 . '</td>';
        $content .= '<td >' . $_SESSION[$origin]['diff_list']['dest']['lastname']
                 .'</td>';
        $content .= '<td>' . $_SESSION[$origin]['diff_list']['dest']['entity_label']
                 .'</td>';
        $content .= '</tr>';
        $content .= '</table><br/>';
    }
    if (count($_SESSION[$origin]['diff_list']['copy']['users']) > 0
        || count($_SESSION[$origin]['diff_list']['copy']['entities']) > 0
    ) {
        if (! $onlyCC) {
            $content .= '<p class="sstit">' . _TO_CC . '</p>';
        }
        $content .= '<table cellpadding="0" cellspacing="0" border="0" class="listing spec detailtabricatordebug">';
        $color = ' class="col"';
        for ($i = 0; $i < count(
            $_SESSION[$origin]['diff_list']['copy']['entities']
        ); $i ++
        ) {
            if ($color == ' class="col"') {
                $color = '';
            } else {
                $color = ' class="col"';
            }
            $content .= '<tr ' . $color . ' >';
            $content .= '<td><img src="' . $_SESSION['config']['businessappurl']
                     . 'static.php?filename=manage_entities_b_small.gif&module='
                     . 'entities" alt="' . _ENTITY . '" title="' . _ENTITY
                     . '" /></td>';
            $content .= '<td >' . $_SESSION[$origin]['diff_list']['copy']['entities'][$i]['entity_id']
                     .'</td>';
            $content .= '<td colspan="2">'
                     . $_SESSION[$origin]['diff_list']['copy']['entities'][$i]['entity_label']
                     .'</td>';
            $content .= '</tr>';
        }
        for ($i = 0; $i < count(
            $_SESSION[$origin]['diff_list']['copy']['users']
        ); $i ++
        ) {
            if ($color == ' class="col"') {
                $color = '';
            } else {
                $color = ' class="col"';
            }
            $content .= '<tr ' . $color . ' >';
            $content .= '<td><img src="' . $_SESSION['config']['businessappurl']
                     . 'static.php?filename=manage_users_entities_b_small.gif'
                     . '&module=entities" alt="' . _USER . '" title="' . _USER
                     . '" /></td>';
            $content .= '<td >'
                     . $_SESSION[$origin]['diff_list']['copy']['users'][$i]['firstname']
                     . '</td>';
            $content .= '<td >'
                     . $_SESSION[$origin]['diff_list']['copy']['users'][$i]['lastname']
                     . '</td>';
            $content .= '<td>'
                     . $_SESSION[$origin]['diff_list']['copy']['users'][$i]['entity_label']
                     . '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';
    }
    $labelButton = _MODIFY_LIST;
    $arg = '&mode=up';
} else {
    $content .= '<p>' . _NO_DIFF_LIST_ASSOCIATED . '</p>';
    $labelButton = _CREATE_LIST;
    $arg = '&mode=add';
}
if ($onlyCC) {
    $arg .= '&only_cc';
}
$content_standard = '<center><h2>' . _DIFF_LIST . '</h2></center>';
$content_standard .= '<p class="button" >';
$content_standard .= '<img src="' . $_SESSION['config']['businessappurl']
         . 'static.php?filename=modif_liste.png&module=entities" alt="" />'
         . '<a href="javascript://" onclick="window.open(\''
         . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
         . '&module=entities&page=manage_listinstance&origin=' . $origin . $arg
         . '\', \'\', \'scrollbars=yes,menubar=no,toolbar=no,status=no,'
         . 'resizable=yes,width=1024,height=650,location=no\');">'
         . $labelButton . '</a>';
$content_standard .= '</p>';

echo "{status : 0, div_content : '" . addslashes($content_standard . $content . '<br>') 
    . "', div_content_action : '" . addslashes($content) . "'}";
exit();
