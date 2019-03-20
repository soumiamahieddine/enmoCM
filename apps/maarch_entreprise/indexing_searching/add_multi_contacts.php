<?php
/*
*
*    Copyright 2013 Maarch
*
*  This file is part of Maarch Framework.
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
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief    Script to return ajax result
*
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
*/

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php";
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_indexing_searching_app.php';
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_users.php';
require_once 'modules/sendmail/sendmail_tables.php';
require_once 'modules/notifications/notifications_tables_definition.php';
require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_multicontacts.php";
    
$core_tools     = new core_tools();
$request        = new request();
$sec            = new security();
$is             = new indexing_searching_app();
$users_tools    = new class_users();
$sendmail_tools = new sendmail();
$multicontacts  = new multicontacts();
$db             = new Database();

function _parse($text)
{
    $text = str_replace("\r\n", PHP_EOL, $text);
    $text = str_replace("\r", PHP_EOL, $text);
    $text = str_replace(PHP_EOL, "\\n ", $text);
    return $text;
}
function _parse_error($text)
{
    $text = str_replace("###", "\\n ", $text);
    return $text;
}
    
$core_tools->load_lang();

$status = 0;
$error = $content = $js = $parameters = '';

$labels_array = array();

if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
} else {
    $error = _ERROR_IN_SENDMAIL_FORM_GENERATION;
    $status = 1;
}

//Path to actual script
$path_to_script = $_SESSION['config']['businessappurl']
        ."index.php?display=true&dir=indexing_searching&page=add_multi_contacts&coll_id=".$collId;
            
switch ($mode) {
    case 'adress':
        if (isset($_REQUEST['for']) && isset($_REQUEST['field']) && isset($_REQUEST['contact'])) {
            //
            if (isset($_REQUEST['contactid']) && !empty($_REQUEST['contactid'])) {
                //Clean up email
                $email = trim($_REQUEST['contact']);
                //Reset session adresses if necessary
                if (!isset($_SESSION['adresses'][$_REQUEST['field']])) {
                    $_SESSION['adresses'][$_REQUEST['field']] = array();
                }
                if (!isset($_SESSION['adresses']['contactid'])) {
                    $_SESSION['adresses']['contactid'] = array();
                }
                if (!isset($_SESSION['adresses']['addressid'])) {
                    $_SESSION['adresses']['addressid'] = array();
                }
                //For ADD
                if ($_REQUEST['for'] == 'add') {
                    if ($_REQUEST['addressid'] == '0' && is_numeric($_REQUEST['contactid'])) {
                        $listContacts = \Contact\models\ContactGroupModel::getListById([
                            'id' => $_REQUEST['contactid'],
                            'select' => ['contact_addresses_id']
                        ]);
                        foreach ($listContacts as $contact) {
                            $email = '';
                            if (in_array($contact['contact_addresses_id'], $_SESSION['adresses']['addressid'])) {
                                continue;
                            }
                            $contactInfos = \Contact\models\ContactModel::getByAddressId([
                                'addressId' => $contact['contact_addresses_id'],
                                'select' => ['contact_id', 'firstname', 'lastname', 'address_num', 'address_street', 'address_postal_code', 'address_town']
                            ]);
                            $contactSociety = \Contact\models\ContactModel::getById(['id' => $contactInfos['contact_id'], 'select' => ['society','firstname', 'lastname']]);
                            if (empty($contactSociety['society'])) {
                                $contactAddress = implode(' ', [$contactInfos['address_num'], $contactInfos['address_street'], $contactInfos['address_postal_code'], $contactInfos['address_town']]);
                                $email = trim($contactSociety['firstname'].' '.$contactSociety['lastname'].' - '.$contactAddress);
                            } else {
                                $contactAddress = implode(' ', [$contactInfos['firstname'], $contactInfos['lastname'].',', $contactInfos['address_num'], $contactInfos['address_street'], $contactInfos['address_postal_code'], $contactInfos['address_town']]);
                                $email = trim($contactSociety['society'].' - '.$contactAddress);
                            }
                            
                            array_push($_SESSION['adresses'][$_REQUEST['field']], $email);
                            array_push($_SESSION['adresses']['contactid'], $contactInfos['contact_id']);
                            array_push($_SESSION['adresses']['addressid'], $contact['contact_addresses_id']);
                        }
                    } else {
                        array_push($_SESSION['adresses'][$_REQUEST['field']], $email);
                        array_push($_SESSION['adresses']['contactid'], $_REQUEST['contactid']);
                        array_push($_SESSION['adresses']['addressid'], $_REQUEST['addressid']);
                    }
                    //For DEL
                } elseif ($_REQUEST['for'] == 'del') {
                    //unset adress in array
                    //unset($_SESSION['adresses'][$_REQUEST['field']][$_REQUEST['index']]);
                    array_splice($_SESSION['adresses'][$_REQUEST['field']], $_REQUEST['index'], 1);
                    array_splice($_SESSION['adresses']['contactid'], $_REQUEST['index'], 1);
                    array_splice($_SESSION['adresses']['addressid'], $_REQUEST['index'], 1);
                    //If no adresse for field, unset the entire sub-array
                    if (count($_SESSION['adresses'][$_REQUEST['field']]) == 0) {
                        unset($_SESSION['adresses'][$_REQUEST['field']]);
                        unset($_SESSION['adresses']['contactid']);
                        unset($_SESSION['adresses']['addressid']);
                        //array_splice($_SESSION['adresses'], 0);
                    }
                }
                //Get content
                $content = $multicontacts->updateContactsInputField($path_to_script, $_SESSION['adresses'], $_REQUEST['field']);
            } else {
                $error = $request->wash_html(_SENDER.' '._IS_EMPTY.'!', 'NONE');
                $status = 1;
            }
        } else {
            $error = $request->wash_html(_UNKNOW_ERROR.'!', 'NONE');
            $status = 1;
        }
    break;
}

echo "{status : " . $status . ", content : '" . addslashes(_parse($content)) . "', error : '" . addslashes(_parse_error($error)) . "', exec_js : '".addslashes($js)."'}";
exit();
