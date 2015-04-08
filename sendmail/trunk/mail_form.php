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
require_once "modules" . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
    
$core_tools     = new core_tools();
$request        = new request();
$sec            = new security();
$is             = new indexing_searching_app();
$users_tools    = new class_users();
$sendmail_tools = new sendmail();
$db             = new dbquery();

$parameters = '';

if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
} else {
    echo _ERROR_IN_SENDMAIL_FORM_GENERATION;
    exit;
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

//Keep the origin
$origin = '';
if (isset($_REQUEST['origin']) && !empty($_REQUEST['origin'])) {
    //
    $origin = $_REQUEST['origin'];
}

//Path to actual script
$path_to_script = $_SESSION['config']['businessappurl']
    ."index.php?display=true&module=sendmail&page=sendmail_ajax_content&identifier="
    .$identifier."&origin=".$origin.$parameters;
    
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header('', true, false);           
?><body><?php
$core_tools->load_js(); 
//ADD
if ($mode == 'add') {
    $content .= '<div class="block">';
    $content .= '<form name="formEmail" id="formEmail" method="post" action="#">';
    $content .= '<input type="hidden" value="'.$identifier.'" name="identifier" id="identifier">';
    $content .= '<input type="hidden" value="Y" name="is_html" id="is_html">';
    $content .= '<table border="0" align="left" width="100%" cellspacing="5">';
    $content .= '<tr>';
    $content .= '<td colspan="3" nowrap><b>'._NEW_EMAIL.' '.strtolower(_FROM_SHORT).': </b>'
        .$_SESSION['user']['FirstName'].' '.$_SESSION['user']['LastName']
        .' ('.$_SESSION['user']['Mail'].')<br/></td>';
    $content .= '</tr>';
    $content .= '<tr>';
    $content .= '<td>'._EMAIL.'</label></td>';
    $content .= '<td colspan="2"><input type="text" name="email" id="email" value="" class="emailSelect" />';
    $content .= '<div id="adressList" class="autocomplete"></div>';
    $content .= '<script type="text/javascript">addEmailAdress(\'email\', \'adressList\', \''
        .$_SESSION['config']['businessappurl']
        .'index.php?display=true&module=sendmail&page=adresss_autocomletion\', \'what\', \'2\');</script>';
    $content .= '<select name="target" id="target">'
        .'<option id="target_target_to" value="to">'._SEND_TO_SHORT.'</option>'
        .'<option id="target_cc" value="cc">'._COPY_TO_SHORT.'</option>'
        .'<option id="target_cci" value="cci">'._COPY_TO_INVISIBLE_SHORT.'</option>'
        .'</select>';
    $content .=' <input type="button" name="add" value="&nbsp;'._ADD
                    .'&nbsp;" id="valid" class="button" onclick="updateAdress(\''.$path_to_script
                    .'&mode=adress\', \'add\', document.getElementById(\'email\').value, '
                    .'document.getElementById(\'target\').value, false, \''.(addslashes(_EMAIL_WRONG_FORMAT)).'\');" />&nbsp;';
    $content .= '</td>';
    $content .= '</tr>';
    $content .= '<tr>';
    $content .= '<td align="right" nowrap width="10%"><span class="red_asterisk"><i class="fa fa-star"></i></span><label>'
        ._SEND_TO_SHORT.'</label></td>';
    $content .= '<td width="90%" colspan="2"><div name="to" id="to" class="emailInput">'
        .'<div id="loading_to" style="display:none;"><i class="fa fa-spinner fa-spin" title="loading..."></div></div></td>';
    $content .= '</tr>';
    $content .= '<tr><td colspan="3"><a href="javascript://" '
		.'onclick="new Effect.toggle(\'tr_cc\', \'blind\', {delay:0.2});'
		.'new Effect.toggle(\'tr_cci\', \'blind\', {delay:0.2});">'
		._SHOW_OTHER_COPY_FIELDS.'</a></td></tr>';
    $content .= '<tr id="tr_cc" style="display:none">';
    $content .= '<td align="right" nowrap><label>'._COPY_TO_SHORT.'</label></td>';
    $content .= '<td colspan="2"><div name="cc" id="cc" class="emailInput">'
        .'<div id="loading_cc" style="display:none;"><i class="fa fa-spinner fa-spin" title="loading..."></div></div></td>';
    $content .= '</tr>';
    $content .= '<tr id="tr_cci" style="display:none">';
    $content .= '<td align="right" nowrap><label>'._COPY_TO_INVISIBLE_SHORT.'</label></td>';
    $content .= '<td colspan="2"><div name="cci" id="cci" class="emailInput">'
        .'<div id="loading_cci" style="display:none;"><i class="fa fa-spinner fa-spin" title="loading..."></div></div></td>';
    $content .= '</tr>';
    $content .= '<tr>';
    $content .= '<td align="right" nowrap><span class="red_asterisk"><i class="fa fa-star"></i></span><label>'._EMAIL_OBJECT.' </label></td>';
    $content .= '<td colspan="2"><input name="object" id="object" class="emailInput" type="text" value="" /></td>';
    $content .= '</tr>';
    $content .= '</table><br />';
    $content .='<hr />';
    $content .= '<h4 onclick="new Effect.toggle(\'joined_files\', \'blind\', {delay:0.2});'
        . 'whatIsTheDivStatus(\'joined_files\', \'divStatus_joined_files\');" '
        . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
    $content .= ' <span id="divStatus_joined_files" style="color:#1C99C5;"><<</span>&nbsp;' 
        . _JOINED_FILES;
    $content .= '</h4>';
    
    $all_joined_files = "\n \n";
    $content .= '<div id="joined_files" style="display:none">';
    //Document
    $joined_files = $sendmail_tools->getJoinedFiles($collId, $table, $identifier);
    if (count($joined_files) >0) {
        $content .='<br/>';
        $content .=_DOC;
        for($i=0; $i < count($joined_files); $i++) {
            //Get data
            $id = $joined_files[$i]['id']; 
            $description = $joined_files[$i]['label'];
            $format = $joined_files[$i]['format'];
            $format = $joined_files[$i]['format'];
            $mime_type = $is->get_mime_type($joined_files[$i]['format']);
            $filesize = $joined_files[$i]['filesize']/1024;
            ($filesize > 1)? $filesize = ceil($filesize).' Ko' :  $filesize = round($filesize,2).' Octets';
			//Show data
			$version = '';
            if($joined_files[$i]['is_version'] === true){
				//Version
				$version = ' - '._VERSION.' '.$joined_files[$i]['version'] ;
				//Contents
				$content .= "<li alt=\"".$description
					. "\" title=\"".$description
					. "\"><input type=\"checkbox\" id=\"join_file_".$id
					. "_V".$joined_files[$i]['version']."\" name=\"join_version[]\""
					. " class=\"check\" value=\""
					. $id."\" checked=\"checked\">"
					. $description." <em>(".$mime_type.")</em> ".$filesize.$version."</li>";
			} else {
				$content .= "<li alt=\"".$description
					. "\" title=\"".$description
					. "\"><input type=\"checkbox\" id=\"join_file_".$id."\" name=\"join_file[]\""
					. " class=\"check\" value=\""
					. $id."\" checked=\"checked\">"
					. $description." <em>(".$mime_type.")</em> ".$filesize."</li>";
            }

			$filename = $sendmail_tools->createFilename($description.$version, $format);
            $all_joined_files .= $description.': '.$filename.PHP_EOL;
        }
    }
    
    //Attachments
    if ($core_tools->is_module_loaded('attachments')) {
        $attachment_files = $sendmail_tools->getJoinedFiles($collId, $table, $identifier, true);
        if (count($attachment_files) >0) {
            $content .='<br/>';
            $content .=_ATTACHMENTS;
            for($i=0; $i < count($attachment_files); $i++) {
                //Get data
                $id = $attachment_files[$i]['id']; 
                $description = $attachment_files[$i]['label'];
                $format = $attachment_files[$i]['format'];
                $mime_type = $is->get_mime_type($joined_files[$i]['format']);
                $filesize = $attachment_files[$i]['filesize']/1024;
                ($filesize > 1)? $filesize = ceil($filesize).' Ko' :  $filesize = $filesize.' Octets';
                
                $content .= "<li alt=\"".$description
                    . "\" title=\"".$description
                    . "\"><input type=\"checkbox\" id=\"join_file_".$id."\" name=\"join_attachment[]\""
                    . " class=\"check\" value=\""
                    . $id."\">"
                   . $description." <em>(".$mime_type.")</em> ".$filesize."</li>";
                   
				$filename = $sendmail_tools->createFilename($description, $format);
                // $all_joined_files .= $description.': '.$filename.PHP_EOL;
            }
        }
    }
    
    //Notes            
    if ($core_tools->is_module_loaded('notes')) {
        require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
            . "class" . DIRECTORY_SEPARATOR
            . "class_modules_tools.php";
        $notes_tools    = new notes();
        $user_notes = $notes_tools->getUserNotes($identifier, $collId);
        if (count($user_notes) >0) {
            $content .='<br/>';
            $content .=_NOTES;
            for($i=0; $i < count($user_notes); $i++) {
                //Get data
                $id = $user_notes[$i]['id']; 
                $noteShort = $request->cut_string($user_notes[$i]['label'], 50);
                $note = $user_notes[$i]['label'];
                $userArray = $users_tools->get_user($user_notes[$i]['author']);
                $date = $request->dateformat($user_notes[$i]['date']);
                
                $content .= "<li alt=\"".$note
                    . "\" title=\"".$note
                    . "\"><input type=\"checkbox\" id=\"note_".$id."\" name=\"notes[]\""
                    . " class=\"check\" value=\""
                    . $id."\">"
                    . $noteShort." (".$userArray['firstname']." ".$userArray['lastname'].") ".$date."</li>"; 
            }
            
            // $all_joined_files .= _NOTES.": notes_".$identifier."_".date(dmY).".html\n";
        }
    }
    $content .= '</div>';
    $content .='<hr />';
    $content .= '<tr>';
    $content .= '<td><label style="padding-right:10px">' . _Label_ADD_TEMPLATE_MAIL . '</label></td>';
    $content .= '<select name="templateMail" id="templateMail" style="width:200px" '
                . 'onchange="addTemplateToEmail($(\'templateMail\').value, \''
                            . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
                            . '&module=templates&page=templates_ajax_content_for_mails&id=' . $_REQUEST['identifier'] . '\');">';

    $content .= '<option value="">' . _ADD_TEMPLATE_MAIL . '</option>';
    $db->connect();
    $db->query("select template_id, template_label, template_content from templates where template_target = 'sendmail'");
    while ( $result=$db->fetch_object()) {
        $content .= "<option value='" . $result->template_id ."'>" . $result->template_label . "</option>";
    }
    $content .='</select>';
    $content .= '</tr></br></br>';

    //Body
    $displayHtml = 'block';
    $displayRaw = 'none';
    $content .='<script type="text/javascript">var mode="html";</script>';
     //Show/hide html VS raw mode
    $content .= '<a href="javascript://" onclick="switchMode(\'show\');"><em>'._HTML_OR_RAW.'</em></a>';
    
    //load tinyMCE editor
    ob_start();
    include('modules/sendmail/load_editor.php');
    $content .= ob_get_clean();
    ob_end_flush();
    $content .='<div id="html_mode" style="display:'.$displayHtml.'">';
    $content .= '<textarea name="body_from_html" id="body_from_html" style="width:100%" rows="15" cols="60">'
        ._DEFAULT_BODY.$sendmail_tools->rawToHtml($all_joined_files).'</textarea>';
    $content .='</div>';
    
    //raw text arera
    $content .='<div id="raw_mode" style="display:'.$displayRaw.'">';
    $content .= '<textarea name="body_from_raw" id="body_from_raw" class="emailInput" cols="60" rows="14">'
        ._DEFAULT_BODY.$sendmail_tools->htmlToRaw($all_joined_files).'</textarea>';
    $content .='</div>';
    
    //Buttons
    $content .='<hr style="margin-top:2px;" />';
    $content .='<div align="center">';
    //Send
    $content .=' <input type="button" name="valid" value="&nbsp;'._SEND_EMAIL
                .'&nbsp;" id="valid" class="button" onclick="validEmailForm(\''
                .$path_to_script.'&mode=added&for=send\', \'formEmail\');" />&nbsp;';
    //Save
    $content .=' <input type="button" name="valid" value="&nbsp;'._SAVE_EMAIL
                .'&nbsp;" id="valid" class="button" onclick="validEmailForm(\''
                .$path_to_script.'&mode=added&for=save\', \'formEmail\');" />&nbsp;';
    //Cancel
    $content .='<input type="button" name="cancel" id="cancel" class="button" value="'
                ._CANCEL.'" onclick="window.parent.destroyModal(\'form_email\');"/>';
    $content .='</div>';
    $content .= '</form>';
    $content .= '</div>';

//UPDATE OR TRANSFER
} else if ($mode == 'up' || $mode == 'transfer') {
 
    if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
        
        $id = $_REQUEST['id'];
        $emailArray = $sendmail_tools->getEmail($id);

        //Check if mail exists
        if (count($emailArray) > 0 ) {
            $content .= '<div class="block">';
            $content .= '<form name="formEmail" id="formEmail" method="post" action="#">';
            $content .= '<input type="hidden" value="'.$identifier.'" name="identifier" id="identifier">';
            $content .= '<input type="hidden" value="'.$id.'" name="id" id="id">';
            $content .= '<input type="hidden" value="'.$emailArray['isHtml'].'" name="is_html" id="is_html">';
            $content .= '<table border="0" align="left" width="100%" cellspacing="5">';
            $content .= '<tr>';
			$content .= '<td colspan="3" nowrap><b>'._EDIT_EMAIL.' '.strtolower(_FROM_SHORT).': </b>'
                .$_SESSION['user']['FirstName'].' '.$_SESSION['user']['LastName']
                .' ('.$_SESSION['user']['Mail'].')<br/></td>';
            $content .= '</tr>';
            $content .= '<tr>';
            $content .= '<td>'._EMAIL.'</label></td>';
            $content .= '<td colspan="2"><input type="text" name="email" id="email" value="" class="emailSelect" />';
            $content .= '<div id="adressList" class="autocomplete"></div>';
            $content .= '<script type="text/javascript">addEmailAdress(\'email\', \'adressList\', \''
                .$_SESSION['config']['businessappurl']
                .'index.php?display=true&module=sendmail&page=adresss_autocomletion\', \'what\', \'2\');</script>';
            $content .= '<select name="target" id="target">'
                .'<option id="target_target_to" value="to">'._SEND_TO_SHORT.'</option>'
                .'<option id="target_cc" value="cc">'._COPY_TO_SHORT.'</option>'
                .'<option id="target_cci" value="cci">'._COPY_TO_INVISIBLE_SHORT.'</option>'
                .'</select>';
            $content .=' <input type="button" name="add" value="&nbsp;'._ADD
                            .'&nbsp;" id="valid" class="button" onclick="updateAdress(\''.$path_to_script
                            .'&mode=adress\', \'add\', document.getElementById(\'email\').value, '
                            .'document.getElementById(\'target\').value, false, \''.(addslashes(_EMAIL_WRONG_FORMAT)).'\');" />&nbsp;';
            $content .= '</td>';
            $content .= '</tr>';
            //To
            if (count($emailArray['to']) > 0) {
                $_SESSION['adresses']['to'] = array();
                $_SESSION['adresses']['to'] = $emailArray['to'];
            }
            $content .= '<tr>';
            $content .= '<td align="right" nowrap width="10%"><span class="red_asterisk"><i class="fa fa-star"></i></span><label>'
                ._SEND_TO_SHORT.'</label></td>';
            $content .= '<td width="90%" colspan="2"><div name="to" id="to" class="emailInput">';
            $content .= $sendmail_tools->updateAdressInputField($path_to_script, $_SESSION['adresses'], 'to');
            $content .= '</div></td>';
            $content .= '</tr>';
            //CC
            if (count($emailArray['cc']) > 0) {
                $_SESSION['adresses']['cc'] = array();
                $_SESSION['adresses']['cc'] = $emailArray['cc'];
            }
			$content .= '<tr><td colspan="3"><a href="javascript://" '
				.'onclick="new Effect.toggle(\'tr_cc\', \'blind\', {delay:0.2});'
				.'new Effect.toggle(\'tr_cci\', \'blind\', {delay:0.2});">'
				._SHOW_OTHER_COPY_FIELDS.'</a></td></tr>';
			$content .= '<tr id="tr_cc" style="display:none">';
            $content .= '<td align="right" nowrap><label>'._COPY_TO_SHORT.'</label></td>';
            $content .= '<td colspan="2"><div name="cc" id="cc" class="emailInput">';
            $content .= $sendmail_tools->updateAdressInputField($path_to_script, $_SESSION['adresses'], 'cc');
            $content .= '</div></td>';
            $content .= '</tr>';
            //CCI
            if (count($emailArray['cci']) > 0) {
                $_SESSION['adresses']['cci'] = array();
                $_SESSION['adresses']['cci'] = $emailArray['cci'];
            }
            $content .= '<tr id="tr_cci" style="display:none">';
            $content .= '<td align="right" nowrap><label>'._COPY_TO_INVISIBLE_SHORT.'</label></td>';
            $content .= '<td colspan="2"><div name="cci" id="cci" class="emailInput">';
            $content .= $sendmail_tools->updateAdressInputField($path_to_script, $_SESSION['adresses'], 'cci');
            $content .= '</div></td>';
            $content .= '</tr>';
            //Object
            $content .= '<tr>';
            $content .= '<td align="right" nowrap><span class="red_asterisk"><i class="fa fa-star"></i></span><label>'._EMAIL_OBJECT.' </label></td>';
            $content .= '<td colspan="2"><input name="object" id="object" class="emailInput" type="text" value="'
                .(($mode == 'transfer')? 'Fw: '.$emailArray['object'] : $emailArray['object']).'" /></td>';
            $content .= '</tr>';
            $content .= '</table><br />';
            $content .='<hr />';
            //Show hide joined info
            $content .= '<h4 onclick="new Effect.toggle(\'joined_files\', \'blind\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'joined_files\', \'divStatus_joined_files\');" '
                . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
            $content .= ' <span id="divStatus_joined_files" style="color:#1C99C5;"><<</span>&nbsp;' 
                . _JOINED_FILES;
            $content .= '</h4>';
            //
            $content .= '<div id="joined_files" style="display:none">';
            //Document
            $joined_files = $sendmail_tools->getJoinedFiles($collId, $table, $identifier);
            if (count($joined_files) >0) {
                $content .='<br/>';
                $content .=_DOC;
                for($i=0; $i < count($joined_files); $i++) {
                    //Get data
                    $id = $joined_files[$i]['id']; 
                    $description = $joined_files[$i]['label'];
                    $format = $joined_files[$i]['format'];
                    $format = $joined_files[$i]['format'];
                    $mime_type = $is->get_mime_type($joined_files[$i]['format']);
                    $filesize = $joined_files[$i]['filesize']/1024;
					($filesize > 1)? $filesize = ceil($filesize).' Ko' :  $filesize = round($filesize,2).' Octets';

                    //Show data
					$version = '';
					if($joined_files[$i]['is_version'] === true){
						//Checked?
						(in_array($id, $emailArray['version']))? $checked = ' checked="checked"' : $checked = '';
						//Version
						$version = ' - '._VERSION.' '.$joined_files[$i]['version'] ;
						//Content
						$content .= "<li alt=\"".$description
							. "\" title=\"".$description
							. "\"><input type=\"checkbox\" id=\"join_file_".$id
							. "_V".$joined_files[$i]['version']."\" name=\"join_version[]\""
							. " class=\"check\" value=\""
							. $id."\"".$checked.">"
							. $description." <em>(".$mime_type.")</em> ".$filesize.$version."</li>";
					} else {
						//Checked?
						($emailArray['resMasterAttached'] == 'Y')? $checked = ' checked="checked"' : $checked = '';
						//Content
						$content .= "<li alt=\"".$description
							. "\" title=\"".$description
							. "\"><input type=\"checkbox\" id=\"join_file_".$id."\" name=\"join_file[]\""
							. " class=\"check\" value=\""
							. $id."\"".$checked.">"
							. $description." <em>(".$mime_type.")</em> ".$filesize."</li>";
					}
                    //Filename
					$filename = $sendmail_tools->createFilename($description.$version, $format);
                    $all_joined_files .= $description.': '.$filename.PHP_EOL;
                }
            }
            
            //Attachments
            if ($core_tools->is_module_loaded('attachments')) {
                $attachment_files = $sendmail_tools->getJoinedFiles($collId, $table, $identifier, true);
                if (count($attachment_files) >0) {
                    $content .='<br/>';
                    $content .=_ATTACHMENTS;
                    for($i=0; $i < count($attachment_files); $i++) {
                        //Get data
                        $id = $attachment_files[$i]['id']; 
                        $description = $attachment_files[$i]['label'];
                        $format = $attachment_files[$i]['format'];
                        $mime_type = $is->get_mime_type($joined_files[$i]['format']);
                        $filesize = $attachment_files[$i]['filesize']/1024;
                        ($filesize > 1)? $filesize = ceil($filesize).' Ko' :  $filesize = $filesize.' Octets';
                        //Checked?
                        (in_array($id, $emailArray['attachments']))? $checked = ' checked="checked"' : $checked = '';
                        //Show data
                        $content .= "<li alt=\"".$description
                            . "\" title=\"".$description
                            . "\"><input type=\"checkbox\" id=\"join_file_".$id."\" name=\"join_attachment[]\""
                            . " class=\"check\" value=\""
                            . $id."\"".$checked.">"
                           . $description." <em>(".$mime_type.")</em> ".$filesize."</li>";
                        //Filename
						$filename = $sendmail_tools->createFilename($description, $format);
                        $all_joined_files .= $description.': '.$filename.PHP_EOL;
                    }
                }
            }
            
            //Notes            
            if ($core_tools->is_module_loaded('notes')) {
                require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
                    . "class" . DIRECTORY_SEPARATOR
                    . "class_modules_tools.php";
                $notes_tools    = new notes();
                $user_notes = $notes_tools->getUserNotes($identifier, $collId);
                if (count($user_notes) >0) {
                    $content .='<br/>';
                    $content .=_NOTES;
                    for($i=0; $i < count($user_notes); $i++) {
                        //Get data
                        $id = $user_notes[$i]['id']; 
                        $noteShort = $request->cut_string($user_notes[$i]['label'], 50);
                        $note = $user_notes[$i]['label'];
                        $userArray = $users_tools->get_user($user_notes[$i]['author']);
                        $date = $request->dateformat($user_notes[$i]['date']);
                        //Checked?
                        (in_array($id, $emailArray['notes']))? $checked = ' checked="checked"' : $checked = '';
                        //Show data
                        $content .= "<li alt=\"".$note
                            . "\" title=\"".$note
                            . "\"><input type=\"checkbox\" id=\"note_".$id."\" name=\"notes[]\""
                            . " class=\"check\" value=\""
                            . $id."\"".$checked.">"
                            . $noteShort." (".$userArray['firstname']." ".$userArray['lastname'].") ".$date."</li>"; 
                    }
                    //Filename
                    $filename = "notes_".$identifier."_".date(dmY).".html";
                    $all_joined_files .= _NOTES.': '.$filename.PHP_EOL;
                }
            }
            $content .= '</div>';
            $content .='<hr />';

            $content .= '<tr>';
            $content .= '<td><label style="padding-right:10px">' . _Label_ADD_TEMPLATE_MAIL . '</label></td>';
            $content .= '<select name="templateMail" id="templateMail" style="width:200px" '
                        . 'onchange="addTemplateToEmail($(\'templateMail\').value, \''
                                    . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
                                    . '&module=templates&page=templates_ajax_content_for_mails&id=' . $_REQUEST['identifier'] . '\');">';

            $content .= '<option value="">' . _ADD_TEMPLATE_MAIL . '</option>';
            $db->connect();
            $db->query("select template_id, template_label, template_content from templates where template_target = 'sendmail'");
            while ( $result=$db->fetch_object()) {
                $content .= "<option value='" . $result->template_id ."'>" . $result->template_label . "</option>";
            }
            $content .='</select>';
            $content .= '</tr></br></br>';


            //Body
            if ($emailArray['isHtml'] == 'Y') {
                $displayRaw = 'none';
                $displayHtml = 'block';
                $textAreaMode = 'html';
            } else {
                $displayRaw = 'block';
                $displayHtml = 'none';
                $textAreaMode = 'raw';
            }
            $content .='<script type="text/javascript">var mode="'.$textAreaMode.'";</script>';
            //Show/hide html VS raw mode
            $content .= '<a href="javascript://" onclick="switchMode(\'show\');"><em>'._HTML_OR_RAW.'</em></a>';
            
            //load tinyMCE editor
            ob_start();
            include('modules/sendmail/load_editor.php');
            $content .= ob_get_clean();
            ob_end_flush();
            $content .='<div id="html_mode" style="display:'.$displayHtml.'">';
            $content .= '<textarea name="body_from_html" id="body_from_html" style="width:100%" rows="15" cols="60">'
                .$sendmail_tools->rawToHtml($emailArray['body']).'</textarea>';
            $content .='</div>';
            
            //raw textarera
            $content .='<div id="raw_mode" style="display:'.$displayRaw.'">';
            $content .= '<textarea name="body_from_raw" id="body_from_raw" class="emailInput" cols="60" rows="14">'
                .$sendmail_tools->htmlToRaw($emailArray['body']).'</textarea>';
            $content .='</div>';
            
            //Buttons
            $content .='<hr style="margin-top:5px;margin-bottom:2px;" />';
            $content .='<div align="center">';
            
            if ($emailArray['status'] <> 'S') {
                //Send button
                $content .=' <input type="button" name="valid" value="&nbsp;'._SEND_EMAIL
                    .'&nbsp;" id="valid" class="button" onclick="validEmailForm(\''
                    .$path_to_script.'&mode=updated&for=send\', \'formEmail\');" />&nbsp;';
                //Save button    
                $content .=' <input type="button" name="valid" value="&nbsp;'._SAVE_EMAIL
                    .'&nbsp;" id="valid" class="button" onclick="validEmailForm(\''
                    .$path_to_script.'&mode=updated&for=save\', \'formEmail\');" />&nbsp;';                     
                //Delete button    
                $content .=' <input type="button" name="valid" value="&nbsp;'._REMOVE_EMAIL
                    .'&nbsp;" id="valid" class="button" onclick="if(confirm(\''
                    ._REALLY_DELETE.': '.$request->cut_string($emailArray['object'], 50)
                    .' ?\')) validEmailForm(\''.$path_to_script
                    .'&mode=del\', \'formEmail\');" />&nbsp;';
            } else {
                //Re-send button
                $content .=' <input type="button" name="valid" value="&nbsp;'._RESEND_EMAIL
                    .'&nbsp;" id="valid" class="button" onclick="validEmailForm(\''
                    .$path_to_script.'&mode=added&for=send\', \'formEmail\');" />&nbsp;';
                //Save copy button
                $content .=' <input type="button" name="valid" value="&nbsp;'._SAVE_COPY_EMAIL
                    .'&nbsp;" id="valid" class="button" onclick="validEmailForm(\''
                    .$path_to_script.'&mode=added&for=save\', \'formEmail\');" />&nbsp;';    
            }
            
            //Cancel button
            $content .='<input type="button" name="cancel" id="cancel" class="button" value="'
                        ._CANCEL.'" onclick="window.parent.destroyModal(\'form_email\');"/>';
            $content .='</div>';
            $content .= '</form>';
            $content .= '</div>';
        } else {
            $content = $request->wash_html($id.': '._EMAIL_DONT_EXIST.'!','NONE');
        }
    } else {
        $content = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
    }
} else if ($mode == 'read') {
    if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
        
        $id = $_REQUEST['id'];
        $emailArray = $sendmail_tools->getEmail($id, false);

        //Check if mail exists
        if (count($emailArray) > 0 ) {
			$usermailArray = $users_tools->get_user($emailArray['userId']);
		
            $content .= '<div class="block">';
            $content .= '<table border="0" align="left" width="100%" cellspacing="5">';
            $content .= '<tr>';
/*
			$content .= '<td colspan="3" nowrap><b>'._READ_EMAIL.' '.strtolower(_FROM_SHORT).': </b>'
                .$_SESSION['user']['FirstName'].' '.$_SESSION['user']['LastName']
                .' ('.$_SESSION['user']['Mail'].')<br/></td>';
*/
			$content .= '<td colspan="3" nowrap><b>'._READ_EMAIL.' '.strtolower(_FROM_SHORT).': </b>'
                .$usermailArray['firstname'].' '.$usermailArray['lastname']
                .' ('.$usermailArray['mail'].')<br/></td>';
            $content .= '</tr>';
            //To
            if (count($emailArray['to']) > 0) {
                $_SESSION['adresses']['to'] = array();
                $_SESSION['adresses']['to'] = $emailArray['to'];
            }
            $content .= '<tr>';
            $content .= '<td align="right" nowrap width="10%"><span class="red_asterisk"><i class="fa fa-star"></i></span><label>'
                ._SEND_TO_SHORT.'</label></td>';
            $content .= '<td width="90%" colspan="2"><div name="to" id="to" class="emailInput">';
            $content .= $sendmail_tools->updateAdressInputField($path_to_script, $_SESSION['adresses'], 'to', true);
            $content .= '</div></td>';
            $content .= '</tr>';
            //CC
            if (count($emailArray['cc']) > 0) {
                $_SESSION['adresses']['cc'] = array();
                $_SESSION['adresses']['cc'] = $emailArray['cc'];
            }
            $content .= '<tr>';
            $content .= '<td align="right" nowrap><label>'._COPY_TO_SHORT.'</label></td>';
            $content .= '<td colspan="2"><div name="cc" id="cc" class="emailInput">';
            $content .= $sendmail_tools->updateAdressInputField($path_to_script, $_SESSION['adresses'], 'cc', true);
            $content .= '</div></td>';
            $content .= '</tr>';
            //CCI
            if (count($emailArray['cci']) > 0) {
                $_SESSION['adresses']['cci'] = array();
                $_SESSION['adresses']['cci'] = $emailArray['cci'];
            }
            $content .= '<tr>';
            $content .= '<td align="right" nowrap><label>'._COPY_TO_INVISIBLE_SHORT.'</label></td>';
            $content .= '<td colspan="2"><div name="cci" id="cci" class="emailInput">';
            $content .= $sendmail_tools->updateAdressInputField($path_to_script, $_SESSION['adresses'], 'cci', true);
            $content .= '</div></td>';
            $content .= '</tr>';   
            //Object
            $content .= '<tr>';
            $content .= '<td align="right" nowrap><span class="red_asterisk"><i class="fa fa-star"></i></span><label>'._EMAIL_OBJECT.' </label></td>';
            $content .= '<td colspan="2"><div name="object" id="object" class="emailInput">'
                .$emailArray['object'].'</div></td>';
            $content .= '</tr>';
            $content .= '</table><br />';

            $content .='<hr />';
            //Show hide joined info
            $content .= '<h4 onclick="new Effect.toggle(\'joined_files\', \'blind\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'joined_files\', \'divStatus_joined_files\');" '
                . 'class="categorie" style="width:90%;" onmouseover="this.style.cursor=\'pointer\';">';
            $content .= ' <span id="divStatus_joined_files" style="color:#1C99C5;"><<</span>&nbsp;' 
                . _JOINED_FILES;
            $content .= '</h4>';
            //
            $content .= '<div id="joined_files" style="display:none">';
            //Document
            $joined_files = $sendmail_tools->getJoinedFiles($collId, $table, $identifier);
            if (count($joined_files) >0) {
                $content .='<br/>';
                $content .=_DOC;
                for($i=0; $i < count($joined_files); $i++) {
                    //Get data
                    $id = $joined_files[$i]['id']; 
                    $description = $joined_files[$i]['label'];
                    $format = $joined_files[$i]['format'];
                    $format = $joined_files[$i]['format'];
                    $mime_type = $is->get_mime_type($joined_files[$i]['format']);
                    $filesize = $joined_files[$i]['filesize']/1024;
                    ($filesize > 1)? $filesize = ceil($filesize).' Ko' :  $filesize = $filesize.' Octets';
                    //Checked?
                    ($emailArray['resMasterAttached'] == 'Y')? $checked = ' checked="checked"' : $checked = '';
                    //Show data
                    $content .= "<li alt=\"".$description
                        . "\" title=\"".$description
                        . "\"><input type=\"checkbox\" id=\"join_file_".$id."\" name=\"join_file[]\""
                        . " class=\"check\" value=\""
                        . $id."\"".$checked." disabled=\"disabled\">"
                        . $description." <em>(".$mime_type.")</em> ".$filesize."</li>"; 
                }
            }
            
            //Attachments
            if ($core_tools->is_module_loaded('attachments')) {
                $attachment_files = $sendmail_tools->getJoinedFiles($collId, $table, $identifier, true);
                if (count($attachment_files) >0) {
                    $content .='<br/>';
                    $content .=_ATTACHMENTS;
                    for($i=0; $i < count($attachment_files); $i++) {
                        //Get data
                        $id = $attachment_files[$i]['id']; 
                        $description = $attachment_files[$i]['label'];
                        $format = $attachment_files[$i]['format'];
                        $mime_type = $is->get_mime_type($joined_files[$i]['format']);
                        $filesize = $attachment_files[$i]['filesize']/1024;
                        ($filesize > 1)? $filesize = ceil($filesize).' Ko' :  $filesize = $filesize.' Octets';
                        //Checked?
                        (in_array($id, $emailArray['attachments']))? $checked = ' checked="checked"' : $checked = '';
                        //Show data
                        $content .= "<li alt=\"".$description
                            . "\" title=\"".$description
                            . "\"><input type=\"checkbox\" id=\"join_file_".$id."\" name=\"join_attachment[]\""
                            . " class=\"check\" value=\""
                            . $id."\"".$checked." disabled=\"disabled\">"
                           . $description." <em>(".$mime_type.")</em> ".$filesize."</li>";
                    }
                }
            }
            
            //Notes            
            if ($core_tools->is_module_loaded('notes')) {
                require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
                    . "class" . DIRECTORY_SEPARATOR
                    . "class_modules_tools.php";
                $notes_tools    = new notes();
                $user_notes = $notes_tools->getUserNotes($identifier, $collId);
                if (count($user_notes) >0) {
                    $content .='<br/>';
                    $content .=_NOTES;
                    for($i=0; $i < count($user_notes); $i++) {
                        //Get data
                        $id = $user_notes[$i]['id']; 
                        $noteShort = $request->cut_string($user_notes[$i]['label'], 50);
                        $note = $user_notes[$i]['label'];
                        $userArray = $users_tools->get_user($user_notes[$i]['author']);
                        $date = $request->dateformat($user_notes[$i]['date']);
                        //Checked?
                        (in_array($id, $emailArray['notes']))? $checked = ' checked="checked"' : $checked = '';
                        //Show data
                        $content .= "<li alt=\"".$note
                            . "\" title=\"".$note
                            . "\"><input type=\"checkbox\" id=\"note_".$id."\" name=\"notes[]\""
                            . " class=\"check\" value=\""
                            . $id."\"".$checked." disabled=\"disabled\">"
                            . $noteShort." (".$userArray['firstname']." ".$userArray['lastname'].") ".$date."</li>"; 
                    }
                }
            }
            $content .= '</div>';
            $content .='<hr />';
            //Body (html or raw mode)
            if ($emailArray['isHtml'] == 'Y') { 
                $content .='<script type="text/javascript">var mode="html";</script>';
                //load tinyMCE editor
                ob_start();
                include('modules/sendmail/load_editor.php');
                $content .= ob_get_clean();
                ob_end_flush();
                $content .='<div id="html_mode" style="display:block">';
                $content .= '<textarea name="body_from_html" id="body_from_html" style="width:100%" '
                    .'rows="15" cols="60" readonly="readonly">'
                    .$sendmail_tools->rawToHtml($emailArray['body']).'</textarea>';
                $content .='</div>';
            } else {
                $content .='<script type="text/javascript">var mode="raw";</script>';
                //raw textarera
                $content .='<div id="raw_mode" style="display:block">';
                $content .= '<textarea name="body_from_raw" id="body_from_raw" class="emailInput" '
                    .'cols="60" rows="14" readonly="readonly">'
                    .$sendmail_tools->htmlToRaw($emailArray['body']).'</textarea>';
                $content .='</div>';
            }
                        
            //Buttons
            $content .='<hr style="margin-top:2px;" />';
            $content .='<div align="center">';
            //Close button
            $content .='<input type="button" name="cancel" id="cancel" class="button" value="'
                        ._CLOSE.'" onclick="window.parent.destroyModal(\'form_email\');"/>';
            $content .='</div>';
            $content .= '</div>';
        } else {
            $content = $request->wash_html($id.': '._EMAIL_DONT_EXIST.'!','NONE');
        }
    } else {
        $content = $request->wash_html(_ID.' '._IS_EMPTY.'!','NONE');
    }
}
echo $content;

?></body></html>
