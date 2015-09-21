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
$selectDuplicatesBySociety = "SELECT contacts_v2.contact_id, contacts_v2.user_id, society, lower(society) as lowsoc, society_short,
    is_corporate_person, contact_addresses.title,contact_addresses.lastname, contact_addresses.firstname, address_num||' '||address_street||' '||address_postal_code||' '||address_town as address 
    from contacts_v2, contact_addresses 
    WHERE contacts_v2.contact_id = contact_addresses.contact_id AND lower(society) in (
    SELECT lower(society) FROM contacts_v2 GROUP BY lower(society) 
    HAVING Count(lower(society)) > 1 and lower(society) <> '' ) AND is_corporate_person = 'Y'
    order by lower(society)";
$htmlTabSoc = '<table style="width:100%;">';
$htmlTabSoc .= '<CAPTION>' . _DUPLICATES_BY_SOCIETY . '</CAPTION>';
$htmlTabSoc .= '<tr>';
$htmlTabSoc .= '<th style="width:60px">&nbsp;</th>';
$htmlTabSoc .= '<th style="width:200px">' . _ID . '</th>';
$htmlTabSoc .= '<th>' . _STRUCTURE_ORGANISM . '</th>';
$htmlTabSoc .= '<th>' . _SOCIETY_SHORT . '</th>';
$htmlTabSoc .= '<th>' . _ADDRESS . '</th>';
$htmlTabSoc .= '<th>' . _TITLE2 . '</th>';
$htmlTabSoc .= '<th>' . _LASTNAME . '</th>';
$htmlTabSoc .= '<th>' . _FIRSTNAME . '</th>';
$htmlTabSoc .= '<th>&nbsp;</th>';
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
        $htmlTabSoc .= '<td><i onclick="loadDocList('
            . $lineDoublSoc->contact_id . ');" class="fa fa-search fa-2x" title="'._IS_ATTACHED_TO_DOC.'" title="'
            . _IS_ATTACHED_TO_DOC . '" style="cursor: pointer;"></i></td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->contact_id . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->society . '</td>';
        $htmlTabSoc .= '<td align="center">' . $lineDoublSoc->society_short . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->address . '</td>';
        $htmlTabSoc .= '<td>' . $business->get_label_title($lineDoublSoc->title) . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->lastname . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->firstname . '</td>';
        $htmlTabSoc .= '<td><i onclick="loadDeleteContactDiv('
            . $lineDoublSoc->contact_id . ', \'' 
            . addslashes($lineDoublSoc->society) . '\', \'\');" class="fa fa-close fa-2x" title="'._DELETE.'" title="'
            . _DELETE . ' ' . $lineDoublSoc->contact_id . ' '
            . $lineDoublSoc->society . ' ?" style="cursor: pointer;"></i></td>';
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
if ($cptSoc == 0) {
    echo _NO_SOCIETY_DUPLICATES . '<br>';
} else {
    echo $htmlTabSoc;
}
?><br><?php
/***********************************************************************/
//duplicates by name
$selectDuplicatesByName = "SELECT contacts_v2.contact_id, lower(contacts_v2.lastname||' '||contacts_v2.firstname) as lastname_firstname, society, society_short,
    is_corporate_person, contacts_v2.lastname, contacts_v2.firstname, contacts_v2.title, address_num||' '||address_street||' '||address_postal_code||' '||address_town as address
    from contacts_v2, contact_addresses
    WHERE contacts_v2.contact_id = contact_addresses.contact_id AND is_corporate_person = 'N' AND lower(contacts_v2.lastname||' '||contacts_v2.firstname) in (
    SELECT lower(lastname||' '||firstname) as lastname_firstname FROM contacts_v2 GROUP BY lastname_firstname 
    HAVING Count(lower(lastname||' '||firstname)) > 1 and lower(lastname||' '||firstname) <> ' ') 
    order by lower(contacts_v2.lastname||' '||contacts_v2.firstname)";
$htmlTabName = '<table style="width:100%;">';
$htmlTabName .= '<CAPTION>' . _DUPLICATES_BY_NAME . '</CAPTION>';
$htmlTabName .= '<tr>';
$htmlTabName .= '<th style="width:60px">&nbsp;</th>';
$htmlTabName .= '<th style="width:200px">' . _ID . '</th>';
$htmlTabName .= '<th>' . _TITLE2 . '</th>';
$htmlTabName .= '<th>' . _LASTNAME . '</th>';
$htmlTabName .= '<th>' . _FIRSTNAME . '</th>';
$htmlTabName .= '<th>' . _STRUCTURE_ORGANISM . '</th>';
$htmlTabName .= '<th>' . _SOCIETY_SHORT . '</th>';
$htmlTabName .= '<th>' . _ADDRESS . '</th>';
$htmlTabName .= '<th style="width:50px">&nbsp;</th>';
$htmlTabName .= '</tr>';
$tabName = array();
$nameCompare = '';
$colorToUse = '';
$colorNumber = '2';
$stmt = $db->query($selectDuplicatesByName);
$cptName = 0;
while($lineDoublName = $stmt->fetchObject()) {

    $stmt2 = $db->query("SELECT id FROM contact_addresses WHERE contact_id = ? ", array($lineDoublName->contact_id));
    $result_address = $stmt2->fetchObject();

    if ($lineDoublName->contact_id <> '' && $result_address->id <> '') {
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
        $htmlTabName .= '<td><i onclick="loadDocList('
            . $lineDoublName->contact_id . ');" class="fa fa-search fa-2x" title="'._IS_ATTACHED_TO_DOC.'" title="'
            . _IS_ATTACHED_TO_DOC . '" style="cursor: pointer;"></i></td>';
        $htmlTabName .= '<td>' . $lineDoublName->contact_id . '</td>';
        $htmlTabName .= '<td>' . $business->get_label_title($lineDoublName->title) . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->lastname . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->firstname . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->society . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->society_short . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->address;
        $htmlTabName .= '</td>';
        $htmlTabName .= '<td><i onclick="loadDeleteContactDiv('
            . $lineDoublName->contact_id . ', \'\', \'' 
            . addslashes($lineDoublName->lastname_firstname) . '\');" class="fa fa-close fa-2x" title="'._DELETE . ' ' . $lineDoublName->contact_id . ' '
            . $lineDoublName->society . ' ?" title="'
            . _DELETE . ' ' . $lineDoublName->contact_id . ' '
            . $lineDoublName->society . ' ?" style="cursor: pointer;"></i></td>';
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
if ($cptName == 0) {
    echo '<hr/>'; 
    echo _NO_NAME_DUPLICATES . '<br>';
} else {
    echo $htmlTabName;
}
echo '</div>';
echo '</div>';