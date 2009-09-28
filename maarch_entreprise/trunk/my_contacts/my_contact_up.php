<?php
/**
* File : my_contact_up.php
*
* Form to modify a contact
*
* @package Maarch LetterBox 2.3
* @version 2.0
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('my_contacts', 'apps');

require_once($_SESSION['pathtocoreclass']."class_db.php");
require($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_contacts.php");

$func = new functions();

if(isset($_GET['id']))
{
	$id = addslashes($func->wash($_GET['id'], "alphanum", _THE_CONTACT));
}
else
{
	$id = "";
}
 /****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=my_contact_up&dir=my_contacts';
$page_label = _MODIFICATION;
$page_id = "my_contact_up";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/


$contact = new contacts();
$contact->formcontact("up",$id, false);
?>
