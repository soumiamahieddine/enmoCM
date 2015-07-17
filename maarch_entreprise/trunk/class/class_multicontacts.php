<?php
/*
*
*   Copyright 2012 Maarch
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
*
* @file     class_modules_tools.php
* @date     $date$
* @version  $Revision$
*/

// Loads the required class
try {
    require_once ("core/class/class_security.php");
} catch (Exception $e){
    echo $e->getMessage().' // ';
}

class multicontacts extends Dabase
{
    public function updateContactsInputField($ajaxPath, $contactsArray, $inputField, $readOnly=false) 
	{
        $content = '';
        //Init with loading div
        $content .= '<div id="loading_'.$inputField.'" style="display:none;"><img src="'
            . $_SESSION['config']['businessappurl']
            . 'static.php?filename=loading.gif" width="12" '
            . 'height="12" style="vertical-align: middle;" alt='
            . '"loading..." title="loading..."></div>';
        // $content .=  print_r($adressArray, true);
        //Get info from session array and display tag
        if (isset($contactsArray[$inputField]) && count($contactsArray[$inputField]) > 0) {
            foreach($contactsArray[$inputField] as $key => $contacts) {
                if (!empty($contacts)) {
                    $content .= '<div class="multicontact_element" id="'.$key.'_'.$contacts.'">'.$contacts;
                    if ($readOnly === false) {
                        $content .= '&nbsp;<div class="email_delete_button" id="'.$key.'"'
                            . 'onclick="updateMultiContacts(\''.$ajaxPath
                            .'&mode=adress\', \'del\', \''.$contacts.'\', \''
                            .$inputField.'\', this.id, \''.$contactsArray['addressid'][$key].'\', \''.$contactsArray['contactid'][$key].'\');" alt="'._DELETE.'" title="'
                            ._DELETE.'">x</div>';
                    }
                    $content .= '</div>';
                }
            }
        }
        return $content;
    }
}
