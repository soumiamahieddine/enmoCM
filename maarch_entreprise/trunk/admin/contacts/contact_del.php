<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief  Delete contact
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_admin('admin_contacts', 'apps');
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts.php");


 /****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}

$page_path = $_SESSION['config']['businessappurl'].'index.php?page=contact_del&admin=contacts';
$page_label = _DELETION;
$page_id = "contact_del";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
$func = new functions();
$db = new dbquery();
$db->connect();
$contact = new contacts();

if(isset($_GET['id']))
{
    $s_id = addslashes($func->wash($_GET['id'], "alphanum", _CONTACT));
}
else
{
    $s_id = "";
}
if(isset($_REQUEST['valid']))
{
        
	if(!empty($_REQUEST['contact']))
    {	

		if (preg_match('/\([0-9]+\)$/', $_REQUEST['contact']) == 0) 
		{
			$_SESSION['error'] = _CONTACT. ' ' . _WRONG_FORMAT . '.<br/>'
                                   . _USE_AUTOCOMPLETION;
            
			$contact->delcontact($s_id);
			exit;
        } 
        else
        {
			$contactTmp = str_replace(')', '', substr($_REQUEST['contact'], strrpos($_REQUEST['contact'],'(')+1));
			echo $contactTmp;
			$find1 = strpos($contactTmp, ':');
			$find2 =  $find1 + 1;
			$contact_type = substr($contactTmp, 0, $find1);
			$new_contact = substr($contactTmp, $find2, strlen($contactTmp));
		
			for($i=0;$i<count($_SESSION['collections']);$i++)
			{
				if(isset($_SESSION['collections'][$i]['table']) && !empty($_SESSION['collections'][$i]['view']))
				{
					$db->query("update ".$_SESSION['collections'][$i]['extensions'][$i]." set exp_contact_id = '".$db->protect_string_db($new_contact)."' where exp_contact_id = '".$db->protect_string_db($s_id)."'");
				}
			}
		}
	}
	elseif(empty($_REQUEST['contact']))
    {
        $_SESSION['error'] .= _CONTACT_MANDATORY_FOR_REDIRECTION."<br>";
        //$documents = false;
    }
}
else
{

	//$contact = new contacts();
	$contact->delcontact($s_id);
}
?>
