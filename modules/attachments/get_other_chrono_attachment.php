<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
* @brief   Other chrono for attachments
*
* Open a modal box to displays the indexing form, make the form checks and loads
* the result in database. Used by the core (manage_action.php page).
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_chrono.php';
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");

$core = new core_tools();
$core->test_user();
$db = new Database();

$array_attachment_types_get_chrono = "'".implode("','", $_SESSION['attachment_types_get_chrono'][$_REQUEST['type_id']])."'";
$stmt = $db->query("SELECT distinct identifier FROM res_view_attachments WHERE res_id_master = ? and attachment_type IN (".$array_attachment_types_get_chrono.") and status <> 'DEL' and status <> 'OBS'", array($_SESSION['doc_id']));

$listIdentifier = array();

while ($res = $stmt->fetchObject()) {
    array_push($listIdentifier, $res->identifier);
}

$stmt = $db->query("SELECT category_id, alt_identifier FROM mlb_coll_ext WHERE res_id = ? ", array($_SESSION['doc_id']));
$res = $stmt->fetchObject();

$category_id = $res->category_id;

if ($category_id == "outgoing" && $_SESSION['attachment_types_get_chrono'][$_REQUEST['type_id']] == "response_project") {
    array_push($listIdentifier, $res->alt_identifier);
}

$countIdentifier = count($listIdentifier);
$listChrono .= '<option value="">S&eacute;lectionner le num&eacute;ro chrono</option>';

for ($cptsIdentifier = 0; $cptsIdentifier < $countIdentifier; $cptsIdentifier++) {
    $listChrono .= '<option value="'.functions::show_string($listIdentifier[$cptsIdentifier]).'">'
        .  functions::show_string($listIdentifier[$cptsIdentifier])
    . '</option>';
}

echo "{status: 1, chronoList: '".$listChrono."'}";
