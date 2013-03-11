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
* @file     sendmail_ajax_content.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  sendmail
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
    
$core_tools     = new core_tools();
$request        = new request();
$sec            = new security();
$is             = new indexing_searching_app();
$users_tools    = new class_users();
$sendmail_tools = new sendmail();

function _parse($text) {
    //...
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);

    //
    $text = str_replace("\n", "\\n ", $text);
    return $text;
}
function _parse_error($text) {
    //...
    $text = str_replace("#", "\\n ", $text);
    return $text;
}
    
$core_tools->load_lang();
$request->connect();

$status = 0;
$error = $content = $js = $parameters = '';

$labels_array = array();

if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
} else {
    $error = _ERROR_IN_SENDMAIL_FORM_GENERATION;
    $status = 1;
}

//Identifier of the element wich is noted
$identifier = '';
if (isset($_REQUEST['identifier']) && ! empty($_REQUEST['identifier'])) {
    $identifier = trim($_REQUEST['identifier']);
}

//Collection
if (isset($_REQUEST['coll_id']) && ! empty($_REQUEST['coll_id'])) {
    $collId = trim($_REQUEST['coll_id']);
    $parameters .= '&coll_id='.$_REQUEST['coll_id'];
    $view = $sec->retrieve_view_from_coll_id($collId);
    $table = $sec->retrieve_table_from_coll($collId);
}

//Keep some origin parameters
if (isset($_REQUEST['size']) && !empty($_REQUEST['size'])) $parameters .= '&size='.$_REQUEST['size'];
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $parameters .= '&order='.$_REQUEST['order'];
    if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) $parameters .= '&order_field='.$_REQUEST['order_field'];
}
if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) $parameters .= '&what='.$_REQUEST['what'];
if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) $parameters .= '&template='.$_REQUEST['template'];
if (isset($_REQUEST['start']) && !empty($_REQUEST['start'])) $parameters .= '&start='.$_REQUEST['start'];

//Keep the origin to reload the origin list
$list_origin = $origin = '';
if (isset($_REQUEST['origin']) && !empty($_REQUEST['origin'])) {
    //
    $origin = $_REQUEST['origin'];

    if ($_REQUEST['origin'] == "document") {
        //From document
        $list_origin = "window.parent.loadList('".$_SESSION['config']['businessappurl']
                ."index.php?display=true&module=sendmail&page=sendmail&identifier="
                .$identifier."&origin=document".$parameters."', 'divList', true);";
    } elseif ($_REQUEST['origin'] == "folder") {
        
        //From folders
        $collId = 'folders';
        $table = $_SESSION['tablename']['fold_folders'];
        $list_origin = "window.parent.loadList('".$_SESSION['config']['businessappurl']
                    ."index.php?display=true&module=sendmail&page=sendmail&identifier="
                    .$identifier."&origin=folder".$parameters."', 'divList', true);";
    }
}

//Path to actual script
$path_to_script = $_SESSION['config']['businessappurl']
            ."index.php?display=true&module=sendmail&page=sendmail_ajax_content&identifier="
            .$identifier."&origin=".$origin.$parameters;

switch ($mode) {
    case 'up':
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $parameters .= '&id='.$_REQUEST['id'];
        } else {
            $error = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
            $status = 1;
             //Close the modal
            $js =  "window.parent.destroyModal('form_email');"; 
            break;
        }
    case 'add':
        if (empty($identifier)) {
            $error = $request->wash_html(_IDENTIFIER.' '._IS_EMPTY.'!','NONE');
            $status = 1;
             //Close the modal
            $js =  "window.parent.destroyModal('form_email');"; 
        } else {
            
            $content .='<iframe name="form_mail" id="form_mail" src="'
                . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&module=sendmail&page=mail_form&identifier='
                . $identifier.'&origin=document&coll_id='.$collId.'&mode='.$mode.$parameters.'" '
                . 'frameborder="0" scrolling="no" width="99%" style="height:540px;padding:0px;"></iframe>';
        }
    break;
        
    case 'added':
        if (empty($identifier)) {
            $error = $request->wash_html(_IDENTIFIER.' '._IS_EMPTY.'!','NONE');
            $status = 1;
        } else {
            if (!empty($_REQUEST['to'])) {
                if (!empty($_REQUEST['object'])) {
                    // print_r($_REQUEST);
                    
                    //Check adress for to
                    $_REQUEST['to'] =  str_replace(";", ",", $_REQUEST['to']);
                    $error = $sendmail_tools->CheckEmailAdress($_REQUEST['to']);
                    
                    if (empty($error)) {
                        
                        //Check adress for cc
                        $_REQUEST['cc'] =  str_replace(";", ",", $_REQUEST['cc']);
                        $error = $sendmail_tools->CheckEmailAdress($_REQUEST['cc']);
                        
                        if (empty($error)) {
                        
                            //Check adress for cci
                            $_REQUEST['cci'] =  str_replace(";", ",", $_REQUEST['cci']);
                            $error = $sendmail_tools->CheckEmailAdress($_REQUEST['cci']);
                            
                            if (empty($error)) {
                            
                                //Data
                                $collId = $request->protect_string_db($_REQUEST['coll_id']);
                                $to =  $request->protect_string_db($_REQUEST['to']);
                                $cc = $request->protect_string_db($_REQUEST['cc']);
                                $cci = $request->protect_string_db($_REQUEST['cci']);
                                $object = $request->protect_string_db($_REQUEST['object']);
                                (isset($_REQUEST['join_file']) 
                                    && count($_REQUEST['join_file']) > 0
                                )? $res_master_attached = 'Y' : $res_master_attached = 'N';
                                if (isset($_REQUEST['join_attachment']) && count($_REQUEST['join_attachment']) > 0) {
                                    $attachment_list = join(',', $_REQUEST['join_attachment']);
                                }
                                if (isset($_REQUEST['notes']) && count($_REQUEST['notes']) > 0) {
                                    $note_list = join(',', $_REQUEST['notes']);
                                }
                                $date = $request->current_datetime();
                                $userId = $request->protect_string_db($_SESSION['user']['UserId']);
                                (!empty($_REQUEST['is_html']) && $_REQUEST['is_html'] == 'Y')? $isHtml = 'Y' : $isHtml = 'N';
                                //Body content
                                if ($isHtml == 'Y') {
                                    $body = $request->protect_string_db($_REQUEST['body_from_html']);
                                } else {
                                     $body = $request->protect_string_db($_REQUEST['body_from_raw']);
                                }
                                
                                //Status
                                if ($_REQUEST['for'] == 'save') {
                                    $email_status = 'D';
                                } else if ($_REQUEST['for'] == 'send'){
                                    $email_status = 'W';
                                    
                                    //Inserer les informations dans la table notif_event_stack ???
                                   /* $request->query("INSERT INTO " . _NOTIF_EVENT_STACK_TABLE_NAME . "("
                                        . "notification_sid, table_name,  record_id, user_id, event_info, event_date)("
                                        . $notification_id.", '" . EMAILS_TABLE . "', '" . $id . "'"._SENDMAIL." (".$id
                                        . ")', '" . $userId . "', '', ".$date.")";
                                    );*/
                                }
                                
                                //Query                 
                                $request->query(
                                    "INSERT INTO " . EMAILS_TABLE . "(coll_id, res_id, user_id, to_list, cc_list,
                                    cci_list, email_object, email_body, is_res_master_attached,
                                    res_attachment_id_list, note_id_list, is_html, email_status, creation_date) VALUES (
                                    '" . $collId. "', '" .$identifier. "', '" .$userId. "', '" .$to. "', '" .$cc. "',
                                    '" .$cci . "', '" .$object. "', '" .$body. "', '" .$res_master_attached . "',
                                    '" . $attachment_list . "', '" . $note_list . "', '" . $isHtml . "', '" . $email_status . "', " . $date . ")"
                                );
                                
                                //Last insert ID from sequence
                                $id = $request->last_insert_id('app_emails_email_id_seq');
                                
                                //History
                                if ($_SESSION['history']['mailadd']) {
                                    $hist = new history();
                                    if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "folder") {
                                        $hist->add(
                                            $table, $identifier, "UP", 'folderup', _MAIL_ADDED . _ON_FOLDER_NUM
                                            . $identifier . ' (' . $id . ') : "' . $request->cut_string($object, 254) .'"',
                                            $_SESSION['config']['databasetype'], 'sendmail'
                                        );
                                    } else if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "document") {
                                        $hist->add(
                                            $view, $identifier, "UP", 'resup',  _MAIL_ADDED . _ON_DOC_NUM
                                            . $identifier . ' (' . $id . ') : "' . $request->cut_string($object, 254) .'"',
                                            $_SESSION['config']['databasetype'], 'sendmail'
                                        );
                                    }

                                    $hist->add(
                                        EMAILS_TABLE, $id, "ADD", 'mailadd', _MAIL_ADDED . ' (' . $id . ')',
                                        $_SESSION['config']['databasetype'], 'sendmail'
                                    );
                                }
                                
                                //Reload and show message
                                $js =  $list_origin."window.parent.top.$('main_info').innerHTML = '"._MAIL_ADDED."';"; 
                            } else {
                                $status = 1;
                            }
                        } else {
                            $status = 1;
                        }
                    } else {
                        $status = 1;
                    }
                } else {
                    $error = $request->wash_html(_OBJECT.' '._IS_EMPTY.'!','NONE');
                    $status = 1;
                }
            } else {
                $error = $request->wash_html(_SEND_TO.' '._IS_EMPTY.'!','NONE');
                $status = 1;
            }
        }
    break;
    
    case 'updated':
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            //Email ID
            $id = $_REQUEST['id'];
            
            //Res ID
            if (empty($identifier)) {
                $error = $request->wash_html(_IDENTIFIER.' '._IS_EMPTY.'!','NONE');
                $status = 1;
            } else {
                if (!empty($_REQUEST['to'])) {
                    if (!empty($_REQUEST['object'])) {
                        // print_r($_REQUEST);
                        
                        //Check adress for to
                        $_REQUEST['to'] =  str_replace(";", ",", $_REQUEST['to']);
                        $error = $sendmail_tools->CheckEmailAdress($_REQUEST['to']);
                        
                        if (empty($error)) {
                            
                            //Check adress for cc
                            $_REQUEST['cc'] =  str_replace(";", ",", $_REQUEST['cc']);
                            $error = $sendmail_tools->CheckEmailAdress($_REQUEST['cc']);
                            
                            if (empty($error)) {
                            
                                //Check adress for cci
                                $_REQUEST['cci'] =  str_replace(";", ",", $_REQUEST['cci']);
                                $error = $sendmail_tools->CheckEmailAdress($_REQUEST['cci']);
                                
                                if (empty($error)) {
                                
                                    //Data
                                    $collId = $request->protect_string_db($_REQUEST['coll_id']);
                                    $to =  $request->protect_string_db($_REQUEST['to']);
                                    $cc = $request->protect_string_db($_REQUEST['cc']);
                                    $cci = $request->protect_string_db($_REQUEST['cci']);
                                    $object = $request->protect_string_db($_REQUEST['object']);
                                    (isset($_REQUEST['join_file']) 
                                        && count($_REQUEST['join_file']) > 0
                                    )? $res_master_attached = 'Y' : $res_master_attached = 'N';
                                    if (isset($_REQUEST['join_attachment']) && count($_REQUEST['join_attachment']) > 0) {
                                        $attachment_list = join(',', $_REQUEST['join_attachment']);
                                    }
                                    if (isset($_REQUEST['notes']) && count($_REQUEST['notes']) > 0) {
                                        $note_list = join(',', $_REQUEST['notes']);
                                    }
                                    $date = $request->current_datetime();
                                    $userId = $request->protect_string_db($_SESSION['user']['UserId']);
                                    (!empty($_REQUEST['is_html']) && $_REQUEST['is_html'] == 'Y')? $isHtml = 'Y' : $isHtml = 'N';
                                    //Body content
                                    if ($isHtml == 'Y') {
                                        $body = $request->protect_string_db($_REQUEST['body_from_html']);
                                    } else {
                                         $body = $request->protect_string_db($_REQUEST['body_from_raw']);
                                    }
                                    //Status
                                    if ($_REQUEST['for'] == 'save') {
                                        $email_status = 'D';
                                    } else if ($_REQUEST['for'] == 'send'){
                                        $email_status = 'W';
                                    }
                                    
                                    //Query                 
                                    $request->query(
                                        "UPDATE " . EMAILS_TABLE . " SET to_list = '" . $to . "', cc_list = '" 
                                            . $cc . "', cci_list = '" . $cci . "', email_object = '" 
                                            . $object . "', email_body = '" . $body . "', is_res_master_attached = '" 
                                            . $res_master_attached . "', res_attachment_id_list = '" 
                                            . $attachment_list . "', note_id_list = '" . $note_list 
                                            . "', is_html = '" . $isHtml . "', email_status = '" 
                                            . $email_status . "' where email_id = ". $id 
                                            ." and res_id =  '" . $identifier . "' and user_id = '" . $userId . "'"
                                    );
                                    
                                    //History
                                    if ($_SESSION['history']['mailup']) {
                                        $hist = new history();
                                        if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "folder") {
                                            $hist->add(
                                                $table, $identifier, "UP", 'folderup', _MAIL_UPDATED . _ON_FOLDER_NUM
                                                . $identifier . ' (' . $id . ') : "' . $request->cut_string($object, 254) .'"',
                                                $_SESSION['config']['databasetype'], 'sendmail'
                                            );
                                        } else if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "document") {
                                            $hist->add(
                                                $view, $identifier, "UP", 'resup',  _MAIL_UPDATED . _ON_DOC_NUM
                                                . $identifier . ' (' . $id . ') : "' . $request->cut_string($object, 254) .'"',
                                                $_SESSION['config']['databasetype'], 'sendmail'
                                            );
                                        }

                                        $hist->add(
                                            EMAILS_TABLE, $id, "UP", 'mailup', _MAIL_UPDATED . ' (' . $id . ')',
                                            $_SESSION['config']['databasetype'], 'sendmail'
                                        );
                                    }
                                    
                                    //Reload and show message
                                    $js =  $list_origin."window.parent.top.$('main_info').innerHTML = '"._MAIL_UPDATED."';"; 
                                } else {
                                    $status = 1;
                                }
                            } else {
                                $status = 1;
                            }
                        } else {
                            $status = 1;
                        }
                    } else {
                        $error = $request->wash_html(_OBJECT.' '._IS_EMPTY.'!','NONE');
                        $status = 1;
                    }
                } else {
                    $error = $request->wash_html(_SEND_TO.' '._IS_EMPTY.'!','NONE');
                    $status = 1;
                }
            }
            
        } else {
            $error = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
            $status = 1;
             //Close the modal
            $js =  "window.parent.destroyModal('form_email');"; 
        }
    break;
    
    case 'del':
        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            
            //Keep object
            $object = $request->protect_string_db($_REQUEST['object']);
            
            //Delete mail
            $request->query("delete from " . EMAILS_TABLE . " where email_id = " . $id);
            
            //Delete email from stack too ???
            $request->query("delete from " . _NOTIF_EVENT_STACK_TABLE_NAME 
                . " where table_name = '" . EMAILS_TABLE . "' and record_id = '" . $id . "'");
            
            //History
            if ($_SESSION['history']['mailadd']) {
                $hist = new history();
                if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "folder") {
                    $hist->add(
                        $table, $identifier, "UP", 'folderup', _MAIL_REMOVED . _ON_FOLDER_NUM
                        . $identifier . ' (' . $id . ') : "' . $request->cut_string($object, 254) .'"',
                        $_SESSION['config']['databasetype'], 'sendmail'
                    );
                } else if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == "document") {
                    $hist->add(
                        $view, $identifier, "UP", 'resup',  _MAIL_REMOVED . _ON_DOC_NUM
                        . $identifier . ' (' . $id . ') : "' . $request->cut_string($object, 254) .'"',
                        $_SESSION['config']['databasetype'], 'sendmail'
                    );
                }

                $hist->add(
                    EMAILS_TABLE, $id, "DEL", 'maildel', _MAIL_REMOVED . ' (' . $id . ') : "' 
                    . $request->cut_string($object, 254) .'"',
                    $_SESSION['config']['databasetype'], 'sendmail'
                );
            }
            
            //Reload and show message
            $js =  $list_origin."window.parent.top.$('main_info').innerHTML = '"._MAIL_REMOVED."';";
            
        } else {
            $error = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
            $status = 1;
            //Close the modal
            $js =  "window.parent.destroyModal('form_email');"; 
        }
    break;
}

echo "{status : " . $status . ", content : '" . addslashes(_parse($content)) . "', error : '" . addslashes(_parse_error($error)) . "', exec_js : '".addslashes($js)."'}";
exit ();
?>