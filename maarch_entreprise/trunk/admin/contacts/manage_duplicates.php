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
$db = new dbquery();
$db->connect();
$db2 = new dbquery();
$db2->connect();
$business = new business_app_tools();

//delete old contacts with enabled = 'N'
// $db->query("delete from contacts where enabled = 'N'");

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

echo '<h1><img src="' 
        . $_SESSION['config']['businessappurl'] 
        . 'static.php?filename=manage_duplicates.png" alt="" />' 
        . _MANAGE_DUPLICATES 
    . '</h1>';
echo '<center>';
//TODO: ENABLE THIS FUNCTION FOR ALL COLLECTION USING CONTACTS

//update NULL to ''
$db->query("UPDATE contacts_v2 SET user_id='' WHERE user_id IS NULL");

//duplicates by society
$selectDuplicatesBySociety = "SELECT contact_id, user_id, society, lower(society) as lowsoc, society_short,"
    . "is_corporate_person, lastname, firstname "
    // . "address_num, address_street, address_town "
    . "from contacts_v2 "
    . "WHERE lower(society) in ("
    . "SELECT lower(society) FROM contacts_v2 GROUP BY lower(society), user_id "
    . "     HAVING Count(lower(society)) > 1 and lower(society) <> '' ) "
    . "order by lower(society)";
$htmlTabSoc = '<table>';
$htmlTabSoc .= '<CAPTION>' . _DUPLICATES_BY_SOCIETY . '</CAPTION>';
$htmlTabSoc .= '<tr>';
$htmlTabSoc .= '<th>&nbsp;</th>';
$htmlTabSoc .= '<th>' . _ID . '</th>';
$htmlTabSoc .= '<th>' . _IS_PRIVATE . '</th>';
$htmlTabSoc .= '<th>' . _SOCIETY . '</th>';
$htmlTabSoc .= '<th>' . _SOCIETY_SHORT . '</th>';
$htmlTabSoc .= '<th>' . _IS_CORPORATE_PERSON . '</th>';
$htmlTabSoc .= '<th>' . _LASTNAME . '</th>';
$htmlTabSoc .= '<th>' . _FIRSTNAME . '</th>';
$htmlTabSoc .= '<th>' . _ADDRESS . '</th>';
$htmlTabSoc .= '<th>&nbsp;</th>';
$htmlTabSoc .= '</tr>';

$tabSoc = array();
$socCompare = '';
$colorToUse = '';
$colorNumber = '2';
$db->query($selectDuplicatesBySociety);
$cptSoc = 0;
while($lineDoublSoc = $db->fetch_object()) {
    if ($lineDoublSoc->contact_id <> '') {
        $cptSoc++;
        if($lineDoublSoc->user_id <> '') {
			$is_private = 'Y';
		}
		else
			$is_private = 'N';
        //USE AJAX REQUEST TO KNOW IF RES ATTACHED
        /*$selectResAttached = "select res_id from res_view_letterbox where "
            . "exp_contact_id = " . $lineDoublSoc->contact_id . " or "
            . "dest_contact_id = " . $lineDoublSoc->contact_id . " order by res_id";*/
        //$db2->query($selectResAttached);
        /*array_push($tabSoc, array('contact_id' => $lineDoublSoc->contact_id,
            'society' => $lineDoublSoc->society,
            'lastname' => $lineDoublSoc->lastname,
            'firstname' => $lineDoublSoc->firstname,
            'address_num' => $lineDoublSoc->address_num,
            'address_street' => $lineDoublSoc->address_street,
            'address_town' => $lineDoublSoc->address_town,
            )
        );*/
        if ($socCompare == $lineDoublSoc->lowsoc) {
            //echo 'doublon<br>';
        } else {
            //echo 'new doublon<br>';
            $colorNumber = randomColor($colorNumber);
            $colorToUse = $color[$colorNumber];
        }
        $socCompare = $lineDoublSoc->lowsoc;
        $htmlTabSoc .= '<tr style="background-color: ' 
            . $colorToUse . ';" id="tr_' . $lineDoublSoc->contact_id . '">';
        $htmlTabSoc .= '<td><img src="'
            . $_SESSION['config']['businessappurl']
            . 'static.php?filename=view_folder.gif" title="'
            . _IS_ATTACHED_TO_DOC . '" onclick="loadDocList('
            . $lineDoublSoc->contact_id . ');" style="cursor: pointer;"/></td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->contact_id . '</td>';
        $htmlTabSoc .= '<td>' . $is_private. '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->society . '</td>';
        $htmlTabSoc .= '<td align="center">' . $lineDoublSoc->society_short . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->is_corporate_person . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->lastname . '</td>';
        $htmlTabSoc .= '<td>' . $lineDoublSoc->firstname . '</td>';
        $htmlTabSoc .= '<td><img onclick="loadDeleteContactDiv('
            . $lineDoublSoc->contact_id . ', \'' 
            . addslashes($lineDoublSoc->society) . '\', \'\');" src="'
            . $_SESSION['config']['businessappurl']
            . 'static.php?filename=picto_delete.gif" title="'
            . _DELETE . ' ' . $lineDoublSoc->contact_id . ' '
            . $lineDoublSoc->society . ' ?" style="cursor: pointer;" /></td>';
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
/***********************************************************************/
//duplicates by name
$selectDuplicatesByName = "SELECT contact_id, lower(lastname||' '||firstname) as lastname_firstname, society, society_short,"
    . "is_corporate_person, lastname, firstname, title "
    . "from contacts_v2 "
    . "WHERE lower(lastname||' '||firstname) in ("
    . "SELECT lower(lastname||' '||firstname) as lastname_firstname FROM contacts_v2 GROUP BY lastname_firstname, contact_type "
    . "     HAVING Count(lower(lastname||' '||firstname)) > 1 and lower(lastname||' '||firstname) <> ' ') "
    . "order by lower(lastname||' '||firstname)";
$htmlTabName = '<table>';
$htmlTabName .= '<CAPTION>' . _DUPLICATES_BY_NAME . '</CAPTION>';
$htmlTabName .= '<tr>';
$htmlTabName .= '<th>&nbsp;</th>';
$htmlTabName .= '<th>' . _ID . '</th>';
$htmlTabName .= '<th>' . _TITLE2 . '</th>';
$htmlTabName .= '<th>' . _LASTNAME . '</th>';
$htmlTabName .= '<th>' . _FIRSTNAME . '</th>';
$htmlTabName .= '<th>' . _SOCIETY . '</th>';
$htmlTabName .= '<th>' . _SOCIETY_SHORT . '</th>';
$htmlTabName .= '<th>' . _IS_CORPORATE_PERSON . '</th>';
$htmlTabName .= '<th>' . _ADDRESS . '</th>';
$htmlTabName .= '<th>&nbsp;</th>';
$htmlTabName .= '</tr>';
$tabName = array();
$nameCompare = '';
$colorToUse = '';
$colorNumber = '2';
$db->query($selectDuplicatesByName);
$cptName = 0;
while($lineDoublName = $db->fetch_object()) {
    if ($lineDoublName->contact_id <> '') {
        $cptName++;
        //USE AJAX REQUEST TO KNOW IF RES ATTACHED
        /*$selectResAttached = "select res_id from res_view_letterbox where "
            . "exp_contact_id = " . $lineDoublName->contact_id . " or "
            . "dest_contact_id = " . $lineDoublName->contact_id . " order by res_id";*/
        //$db2->query($selectResAttached);
        /*array_push($tabName, array('contact_id' => $lineDoublName->contact_id,
            'society' => $lineDoublName->society,
            'lastname' => $lineDoublName->lastname,
            'firstname' => $lineDoublName->firstname,
            'address_num' => $lineDoublName->address_num,
            'address_street' => $lineDoublName->address_street,
            'address_town' => $lineDoublName->address_town,
            )
        );*/
        if ($nameCompare == $lineDoublName->lastname_firstname) {
            //echo 'doublon<br>';
        } else {
            //echo 'new doublon<br>';
            $colorNumber = randomColor($colorNumber);
            $colorToUse = $color[$colorNumber];
        }
        $nameCompare = $lineDoublName->lastname_firstname;
        $htmlTabName .= '<tr style="background-color: ' 
            . $colorToUse . ';" id="tr_' . $lineDoublName->contact_id . '">';
        $htmlTabName .= '<td><img src="'
            . $_SESSION['config']['businessappurl']
            . 'static.php?filename=view_folder.gif" title="'
            . _IS_ATTACHED_TO_DOC . '" onclick="loadDocList('
            . $lineDoublName->contact_id . ');" style="cursor: pointer;"/></td>';
        $htmlTabName .= '<td>' . $lineDoublName->contact_id . '</td>';
        $htmlTabName .= '<td>' . $business->get_label_title($lineDoublName->title) . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->lastname . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->firstname . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->society . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->society_short . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->is_corporate_person . '</td>';
        $htmlTabName .= '<td>' . $lineDoublName->address_num;
        $htmlTabName .= ' ' . $lineDoublName->address_street;
        $htmlTabName .= ' ' . $lineDoublName->address_town . '</td>';
        $htmlTabName .= '<td><img onclick="loadDeleteContactDiv('
            . $lineDoublName->contact_id . ', \'\', \'' 
            . addslashes($lineDoublName->lastname_firstname) . '\');" src="'
            . $_SESSION['config']['businessappurl']
            . 'static.php?filename=picto_delete.gif" title="'
            . _DELETE . ' ' . $lineDoublName->contact_id . ' '
            . $lineDoublName->society . ' ?" style="cursor: pointer;" /></td>';
        $htmlTabName .= '</tr>';
        $htmlTabName .= '<tr id="deleteContactDiv_' . $lineDoublName->contact_id
            . '" name="deleteContactDiv_' . $lineDoublName->contact_id
            . '" style="display: none; border-bottom: solid 1px black; '
            . 'background-color: #FFF;">';
        $htmlTabName .= '<td style="background-color: white;" colspan="8">';
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
        $htmlTabName .= '<td style="background-color: white;" colspan="9">';
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
    echo _NO_NAME_DUPLICATES . '<br>';
} else {
    echo $htmlTabName;
}
echo '</center>';
