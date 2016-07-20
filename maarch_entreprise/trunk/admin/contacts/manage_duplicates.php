<?php
/*
*   Copyright 2008-2013 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  manage contacts duplicates
*
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR  . 'class' . DIRECTORY_SEPARATOR 
    . 'class_business_app_tools.php';
$admin = new core_tools();
$admin->test_admin('admin_contacts', 'apps');
$func = new functions();
$db = new Database();

$business = new business_app_tools();

$_SESSION['m_admin'] = array();
/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
$level = "";
if (
    isset($_REQUEST['level'])
    && (
        $_REQUEST['level'] == 2
        || $_REQUEST['level'] == 3
        || $_REQUEST['level'] == 4
        || $_REQUEST['level'] == 1
    )
) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl']
    . 'index.php?page=manage_duplicates&admin=contacts';
$page_label = _MANAGE_DUPLICATES;
$page_id = "manage_duplicates";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$color = array('#DFE3E0','#EAF2F1','#CDDCDA','#A8BCB9');
function randomColor($lastColor)
{
    if ($lastColor <> 0) {
        $val = rand(0, $lastColor - 1);
    } else {
        $val = 3;
    }
    return $val;
}

echo '<h1><i class="fa fa-magic fa-2x"></i>&nbsp;' 
        . _MANAGE_DUPLICATES 
    . '</h1>';

echo '<div id="inner_content">';
echo '<div class="block" style="text-align:center;">';
//TODO: ENABLE THIS FUNCTION FOR ALL COLLECTION USING CONTACTS

//update NULL to ''
$db->query("UPDATE contacts_v2 SET user_id='' WHERE user_id IS NULL");

//duplicates by society
$selectDuplicatesBySociety = "SELECT tab1.contact_id, tab1.society
FROM contacts_v2 tab1, contacts_v2  tab2
WHERE lower(translate(tab1.society,'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ-','aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr ')) LIKE lower(translate(tab2.society,'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ-','aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr '))
AND (tab2.society IS NOT NULL AND tab2.society <> '')
  AND (tab1.society IS NOT NULL AND tab1.society <> '')
  AND tab1.contact_id<>tab2.contact_id
GROUP BY tab1.contact_id,tab1.society
ORDER BY tab1.society ASC LIMIT 500";

$htmlTabSoc = '<form name="manage_duplicate_society" action="#" onsubmit="return linkDuplicate(\'manage_duplicate_society\')" method="post">';
$htmlTabSoc .= '<table style="width:100%;">';
$htmlTabSoc .= '<CAPTION>' . _DUPLICATES_BY_SOCIETY . '</CAPTION>';
$htmlTabSoc .= '<tr>';
$htmlTabSoc .= '<th style="width:60px">&nbsp;</th>';
$htmlTabSoc .= '<th style="width:200px">' . _ID . '</th>';
$htmlTabSoc .= '<th>' . _STRUCTURE_ORGANISM . '</th>';
$htmlTabSoc .= '<th style="width:60px">&nbsp;</th>';
$htmlTabSoc .= '</tr>';

$tabSoc = array();
$socCompare = '';
$colorToUse = '';
$colorNumber = '2';
$stmt = $db->query($selectDuplicatesBySociety);

$cptSoc = 0;
while($lineDoublSoc = $stmt->fetchObject()) {

    //$stmt2 = $db->query("SELECT id FROM contact_addresses WHERE contact_id = ?", array($lineDoublSoc->contact_id));
    //$result_address = $stmt2->fetchObject();

    if ($lineDoublSoc->contact_id <> ''/* && $result_address->id <> '' */) {
        $cptSoc++;

        if ($socCompare == $lineDoublSoc->lowsoc) {
            //echo 'doublon<br>';
        } else {
            //echo 'new doublon<br>';
            $colorNumber = randomColor($colorNumber);
            $colorToUse = $color[$colorNumber];
        }
        $corporatePeople = ($lineDoublSoc->is_corporate_person == "Y")? _YES : _NO;
        $socCompare = $lineDoublSoc->lowsoc;
        $htmlTabSoc .= '<tr style="background-color: #ffffff;" id="tr_' . $lineDoublSoc->contact_id . '">';
        $htmlTabSoc .= '<td><input type="checkbox" id="fusion_id_'.$lineDoublSoc->contact_id.'" name="fusion_id" value="'.$lineDoublSoc->contact_id.'"/> <input type="radio" id="master_fusion_id_'.$lineDoublSoc->contact_id.'" name="master_fusion_id" value="'.$lineDoublSoc->contact_id.'"/></td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->contact_id . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->society . '</td>';
        $htmlTabSoc .= '<td><i onclick="loadDocList('
            . $lineDoublSoc->contact_id . ');" class="fa fa-search fa-2x" title="'._IS_ATTACHED_TO_DOC.'" title="'
            . _IS_ATTACHED_TO_DOC . '" style="cursor: pointer;"></i></td>';
        $htmlTabSoc .= '</tr>';
        $htmlTabSoc .= '<tr id="deleteContactDiv_' . $lineDoublSoc->contact_id
            . '" name="deleteContactDiv_' . $lineDoublSoc->contact_id
            . '" style="display: none; border-bottom: solid 1px black; '
            . 'background-color: #FFF;">';
        $htmlTabSoc .= '<td style="background-color: white;" colspan="9">';
        $htmlTabSoc .= '<div id="divDeleteContact_' . $lineDoublSoc->contact_id
            . '" align="center" style="color: grey;" width="100%">';
        $htmlTabSoc .= '<img width="10%" style="background-color: white; '
            . 'margin: 0; padding: 0;" src="static.php?filename=loading_big.gif">';
        $htmlTabSoc .= '</div>';
        $htmlTabSoc .= '</td>';
        $htmlTabSoc .= '</tr>';
        $htmlTabSoc .= '<tr id="docList_' . $lineDoublSoc->contact_id
            . '" name="docList_' . $lineDoublSoc->contact_id
            . '" style="display: none; border-bottom: solid 1px black; '
            . 'background-color: #FFF;">';
        $htmlTabSoc .= '<td style="background-color: white;" colspan="9">';
        $htmlTabSoc .= '<div id="divDocList_' . $lineDoublSoc->contact_id
            . '" align="center" style="color: grey;" width="100%">';
        $htmlTabSoc .= '<img width="10%" style="background-color: white; '
            . 'margin: 0; padding: 0;" src="static.php?filename=loading_big.gif">';
        $htmlTabSoc .= '</div>';
        $htmlTabSoc .= '</td>';
        $htmlTabSoc .= '</tr>';
    }
}
//$func->show_array($tabSoc);
$htmlTabSoc .= '</table>';
$htmlTabSoc .= '<input type="submit" value="fusionner!" class="button"/>';
$htmlTabSoc .= '</form>';
if ($cptSoc == 0) {
    echo _NO_SOCIETY_DUPLICATES . '<br>';
} else {
    echo $htmlTabSoc;
}
?><br><?php
/***********************************************************************/
//duplicates by name
$selectDuplicatesByName = "SELECT tab1.title, tab1.contact_id, tab1.lastname, tab1.firstname
FROM contacts_v2 tab1, contacts_v2  tab2
WHERE lower(translate(tab1.lastname,'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ-','aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr ')) LIKE lower(translate(tab2.lastname,'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ-','aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr '))
AND lower(translate(tab1.firstname,'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ-','aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr ')) LIKE lower(translate(tab2.firstname,'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ-','aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr '))
  AND tab1.contact_id<>tab2.contact_id
  AND (tab2.lastname IS NOT NULL AND tab2.lastname <> '')
  AND (tab1.lastname IS NOT NULL AND tab1.lastname <> '')
GROUP BY tab1.title, tab1.contact_id,tab1.lastname, tab1.firstname 
ORDER BY tab1.lastname ASC LIMIT 500
";
$htmlTabName = '<form name="manage_duplicate_person" action="#" onsubmit="return linkDuplicate(\'manage_duplicate_person\')" method="post">';
$htmlTabName .= '<table style="width:100%;">';
$htmlTabName .= '<CAPTION>' . _DUPLICATES_BY_NAME . '</CAPTION>';
$htmlTabName .= '<tr>';
$htmlTabName .= '<th style="width:60px">&nbsp;</th>';
$htmlTabName .= '<th style="width:200px">' . _ID . '</th>';
$htmlTabName .= '<th>' . _TITLE2 . '</th>';
$htmlTabName .= '<th>' . _LASTNAME . '</th>';
$htmlTabName .= '<th>' . _FIRSTNAME . '</th>';
$htmlTabName .= '<th style="width:50px">&nbsp;</th>';
$htmlTabName .= '</tr>';
$tabName = array();
$nameCompare = '';
$colorToUse = '';
$colorNumber = '2';
$stmt = $db->query($selectDuplicatesByName);
$cptName = 0;
while($lineDoublName = $stmt->fetchObject()) {

    if ($lineDoublName->contact_id <> '') {
        $cptName++;

        if ($nameCompare == $lineDoublName->lastname_firstname) {
            //echo 'doublon<br>';
        } else {
            //echo 'new doublon<br>';
            $colorNumber = randomColor($colorNumber);
            $colorToUse = $color[$colorNumber];
        }

        $corporatePeople = ($lineDoublName->is_corporate_person == "Y")? _YES : _NO;

        $nameCompare = $lineDoublName->lastname_firstname;
        $htmlTabName .= '<tr style="background-color: #ffffff;" id="tr_' . $lineDoublName->contact_id . '">';
        $htmlTabName .= '<td><input type="checkbox" id="fusion_id_'.$lineDoublName->contact_id.'" name="fusion_id" value="'.$lineDoublName->contact_id.'"/> <input type="radio" id="master_fusion_id_'.$lineDoublName->contact_id.'" name="master_fusion_id" value="'.$lineDoublName->contact_id.'"/></td>';
        $htmlTabName .= '<td>' . $lineDoublName->contact_id . '</td>';
        $htmlTabName .= '<td>' . $business->get_label_title($lineDoublName->title) . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->lastname . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->firstname . '</td>';
        $htmlTabName .= '<td><i onclick="loadDocList('
            . $lineDoublName->contact_id . ');" class="fa fa-search fa-2x" title="'._IS_ATTACHED_TO_DOC.'" title="'
            . _IS_ATTACHED_TO_DOC . '" style="cursor: pointer;"></i></td>';
        $htmlTabName .= '</tr>';
        $htmlTabName .= '<tr id="deleteContactDiv_' . $lineDoublName->contact_id
            . '" name="deleteContactDiv_' . $lineDoublName->contact_id
            . '" style="display: none; border-bottom: solid 1px black; '
            . 'background-color: #FFF;">';
        $htmlTabName .= '<td style="background-color: white;" colspan="10">';
        $htmlTabName .= '<div id="divDeleteContact_' . $lineDoublName->contact_id
            . '" align="center" style="color: grey;" width="100%">';
        $htmlTabName .= '<img width="10%" style="background-color: white; '
            . 'margin: 0; padding: 0;" src="static.php?filename=loading_big.gif">';
        $htmlTabName .= '</div>';
        $htmlTabName .= '</td>';
        $htmlTabName .= '</tr>';
        $htmlTabName .= '<tr id="docList_' . $lineDoublName->contact_id
            . '" name="docList_' . $lineDoublName->contact_id
            . '" style="display: none; border-bottom: solid 1px black; '
            . 'background-color: #FFF;">';
        $htmlTabName .= '<td style="background-color: white;" colspan="10">';
        $htmlTabName .= '<div id="divDocList_' . $lineDoublName->contact_id
            . '" align="center" style="color: grey;" width="100%">';
        $htmlTabName .= '<img width="10%" style="background-color: white; '
            . 'margin: 0; padding: 0;" src="static.php?filename=loading_big.gif">';
        $htmlTabName .= '</div>';
        $htmlTabName .= '</td>';
        $htmlTabName .= '</tr>';
    }
}
//$func->show_array($tabName);
$htmlTabName .= '</table>';
$htmlTabName .= '<input type="submit" value="fusionner!" class="button"/>';
$htmlTabName .= '</form>';
if ($cptName == 0) {
    echo '<hr/>'; 
    echo _NO_NAME_DUPLICATES . '<br>';
} else {
    echo $htmlTabName;
}
echo '</div>';
echo '</div>';