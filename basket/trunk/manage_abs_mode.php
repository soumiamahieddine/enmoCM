<?php
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();

if(isset($_REQUEST['submit']) && isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']))
{
	$db = new dbquery();
	$db->connect();

	require_once($_SESSION['pathtomodules'].'basket'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$db->query("update ".$_SESSION['tablename']['users']." set status = 'ABS' where user_id = '".$db->protect_string_db($_REQUEST['user_id'])."'");


}
if($_REQUEST['user_id'] == $_SESSION['user']['UserId'])
{
?>
<script >window.top.location='<?php echo $_SESSION['config']['businessappurl'];?>logout.php?coreurl=<?php echo $_SESSION['config']['coreurl'];?>';</script>
<?php
}
else
{
?>	<script language="javascript">window.top.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=users&admin=users';</script>	<?php
}?>
