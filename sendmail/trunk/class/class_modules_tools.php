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
* @brief    modules tools Class for sendmail, 
*           contains all the functions to handle sendmail
*
* @file     class_modules_tools.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  sendmail
*/

// Loads the required class
try {
    require_once("core/class/class_db.php");
    require_once ("core/class/class_security.php");
    require_once("modules/sendmail/sendmail_tables.php");
} catch (Exception $e){
    echo $e->getMessage().' // ';
}


class sendmail extends dbquery
{
	/**
	* Build Maarch module tables into sessions vars with a xml configuration
	* file
	*/
	public function build_modules_tables() {
		if (file_exists(
		    $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
		    . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
		    . DIRECTORY_SEPARATOR . "sendmail" . DIRECTORY_SEPARATOR . "xml"
		    . DIRECTORY_SEPARATOR . "config.xml"
		)
		) {
			$path = $_SESSION['config']['corepath'] . 'custom'
			      . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
			      . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR
			      . "sendmail" . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
			      . "config.xml";
		} else {
			$path = "modules" . DIRECTORY_SEPARATOR . "sendmail"
			      . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
			      . "config.xml";
		}
		$xmlconfig = simplexml_load_file($path);
        $_SESSION['sendmail'] = array();
        
        //Lang file
        include_once 'modules' . DIRECTORY_SEPARATOR . 'sendmail'
            . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
            . $_SESSION['config']['lang'] . '.php';
        
        //Status
        $_SESSION['sendmail']['status'] = array();
        if (count($xmlconfig->STATUS ) > 0) {
            foreach($xmlconfig->STATUS as $status) {
                foreach($status->STS as $STS) {
                    $label = (string)$STS->LABEL;
                    if (!empty($label) && defined($label) && constant($label) <> NULL) {
                        $label = constant($label);
                    }
                    $_SESSION['sendmail']['status'][(string)$STS->ID]['id'] = (string)$STS->ID;
                    $_SESSION['sendmail']['status'][(string)$STS->ID]['label'] = $label;
                    $_SESSION['sendmail']['status'][(string)$STS->ID]['img'] = (string)$STS->IMG;
                }
            }
        }
        
        //History
		$hist = $xmlconfig->HISTORY;
		$_SESSION['history']['mailadd'] = (string) $hist->mailadd;
		$_SESSION['history']['mailup'] = (string) $hist->mailup;
		$_SESSION['history']['maildel'] = (string) $hist->maildel;
	}
	
    public function countUserEmails($id, $coll_id) {
        $nbr = 0;
        $db = new dbquery();
        $db->connect();
        $db->query("select email_id from "
                . EMAILS_TABLE 
                . " where res_id = " . $id 
                . " and coll_id ='"
                . $coll_id . "' and user_id = '" 
                . $_SESSION['user']['UserId'] . "'");
        // $db->show(); 
        $nbr = $db->nb_result(); 
         
        return $nbr;
    }
    
    public function CheckEmailAdress($adress) {
        $error = '';
        if (!empty($adress)) {
            
            $adressArray = explode(',', trim($adress));
            for($i=0; $i < count($adressArray); $i++) {
                if (!empty($adressArray[$i])) {
                    $this->wash($adressArray[$i], 'mail', _MAIL.": ".$adressArray[$i], 'yes', 0, 255);
                    if (!empty($_SESSION['error'])) {
                        $error .= $_SESSION['error'];$_SESSION['error']='';
                    }
                }
            }
            $error = str_replace("<br />", "#", $error);
        }
        return $error;
    }
    
    public function haveJoinedFiles($id) {
    
        $db = new dbquery();
        $db->connect();
        $db->query("select email_id from "
                . EMAILS_TABLE 
                . " where email_id = " . $id 
                . " and (is_res_master_attached ='Y' or"
                . " res_attachment_id_list <> '' or" 
                . " note_id_list <> '')");
        // $db->show(); 
        if ($db->nb_result() > 0)
            return true;
        else
            return false;
    }
    
    public function getJoinedFiles($id, $coll_id, $from_res_attachment=false) {
        $joinedFiles = array();
        $db = new dbquery();
        $db->connect();
        if ($from_res_attachment === false) {
            $sec = new security();
            $table = $sec->retrieve_table_from_coll($coll_id);
            $db->query(
                "select res_id, description, subject, title, format, filesize from "
                . $table . " where res_id = " . $id 
                . " and status <> 'DEL'");
        } else {
            $db->query(
                "select res_id, description, subject, title, format, filesize, res_id_master from " 
                .  $_SESSION['tablename']['attach_res_attachments']
                . " where res_id_master = " . $id . " and coll_id ='"
                . $coll_id . "' and status <> 'DEL'");
        }
        // $db->show(); 
        
        while($res = $db->fetch_object()) {
            $label = '';
            //Tile, or subject or description
            if (strlen(trim($res->title)) > 0)
                $label = $res->title;
            elseif (strlen(trim($res->subject)) > 0)
                $label = $res->subject;
            elseif (strlen(trim($res->description)) > 0)
                $label = $res->description;
                
            array_push($joinedFiles,
                        array('id' => $res->res_id, //ID
                              'label' => $this->show_string($label), //Label
                              'format' => $res->format, //Format 
                              'filesize' => $res->filesize //Filesize
                            )
            );
        }

        return $joinedFiles;
    }
    
    public function rawToHtml($text) {
        //...
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        //
        $text = str_replace("\n", "<br />", $text);
        //
        return $text;
    }
    
    public function htmlToRaw($text) {
        //
        $text = str_replace("<br>", "\n", $text);
        $text = str_replace("<br/>", "\n", $text);
        $text = str_replace("<br />", "\n", $text);
        //
        return $text;
    }
    
    public function getEmail($id, $owner=true) {
        $email = array();
        if (!empty($id)) {
            $this->connect();
            if ( $owner=== true) {
                $where = " and user_id = '" . $_SESSION['user']['UserId'] . "'";
            } else {
                $where = "";
            }
            
            $this->query("select * from "
                            . EMAILS_TABLE 
                            . " where email_id = " . $id
                            . $where);
            //
            if ($this->nb_result() > 0) {
                $res = $this->fetch_object();
                $email['id'] = $res->email_id;
                $email['collId'] = $res->coll_id;
                $email['resId'] = $res->res_id;
                $email['userId'] = $res->user_id;
                $email['to'] = array();
                if (!empty($res->to_list)) {
                    $email['to'] = explode(',', $res->to_list);
                }
                $email['cc'] = array();
                if (!empty($res->cc_list)) {
                    $email['cc'] = explode(',', $res->cc_list);
                }
                $email['cci'] = array();
                if (!empty($res->cci_list)) {
                    $email['cci'] = explode(',', $res->cci_list);
                }               
                $email['attachments'] = array();
                if (!empty($res->res_attachment_id_list)) {
                    $email['attachments'] = explode(',', $res->res_attachment_id_list);
                }
                $email['notes'] = array();
                if (!empty($res->note_id_list)) {
                    $email['notes'] = explode(',', $res->note_id_list);
                }
                $email['object'] = $this->show_string($res->email_object);
                $email['body'] = $this->show_string($res->email_body);
                $email['resMasterAttached'] = $res->is_res_master_attached;
                $email['isHtml'] = $res->is_html;
                $email['status'] = $res->email_status;
                $email['creationDate'] = $this->format_date_db($res->creation_date);
                $email['sendDate'] = $this->format_date_db($res->send_date);
            }
        }
        
        return $email;
    }
    
    public function updateAdressInputField($ajaxPath, $adressArray, $inputField, $readOnly=false) {
        $content = '';
        //Init with loading div
        $content .= '<div id="loading_'.$inputField.'" style="display:none;"><img src="'
            . $_SESSION['config']['businessappurl']
            . 'static.php?filename=loading.gif" width="12" '
            . 'height="12" style="vertical-align: middle;" alt='
            . '"loading..." title="loading..."></div>';
        // $content .=  print_r($adressArray, true);
        //Get info from session array and display tag
        if (isset($adressArray[$inputField]) && count($adressArray[$inputField]) > 0) {
            foreach($adressArray[$inputField] as $key => $adress)	{
                if (!empty($adress)) {
                    $content .= '<div class="email_element" id="'.$key.'_'.$adress.'">'.$adress;
                    if ($readOnly === false) {
                        $content .= '&nbsp;<div class="email_delete_button" id="'.$key.'"'
                            . 'onclick="updateAdress(\''.$ajaxPath
                            .'&mode=adress\', \'del\', \''.$adress.'\', \''
                            .$inputField.'\', this.id);" alt="'._DELETE.'" title="'
                            ._DELETE.'">x</div>';
                    }
                    $content .= '</div>';
                }
            }
        }
        
        return $content;
    }
    
    public function createNotesFile($notesArray) {
    
        if (count($notesArray) > 0) {
        
        } else { 
            return false;
        }
    
    }
}

