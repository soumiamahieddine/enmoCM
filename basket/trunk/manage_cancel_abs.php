<?php
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();

if(isset($_REQUEST['submit']))
{
	$db = new dbquery();
	$db->connect();
//	$db->query("update ".$_SESSION['tablename']['users']." set status = 'OK' where user_id = '".$_SESSION['m_admin']['users']['UserId']."'");
	require_once($_SESSION['pathtomodules'].'basket'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$bask = new basket();
	$bask->cancel_abs($_SESSION['m_admin']['users']['UserId']);

}
?>
<script >window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=users_up&admin=users&id=<?php echo $_SESSION['m_admin']['users']['UserId'];?>';</script>
