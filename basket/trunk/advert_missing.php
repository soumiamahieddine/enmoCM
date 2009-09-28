<?php

session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");


if ($_POST['value'] == "submit")
{
	$db = new dbquery();
	$db->connect();
	$db2 = new dbquery();
	$db2->connect();
	require_once($_SESSION['pathtomodules'].'basket'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');

	$bask = new basket();
	$bask->cancel_abs($_SESSION['user']['UserId']);

	$_SESSION['abs_user_status'] = false;
	if($_SESSION['history']['userabs'] == "true")
	{
		require_once($_SESSION['pathtocoreclass']."class_history.php");
		$history = new history();
		$history->connect();
		$history->query("select firstname, lastname from ".$_SESSION['tablename']['users']." where user_id = '".$this_user."'");
		$res = $history->fetch_object();
		$history->add($_SESSION['tablename']['users'],$this_user,"RET",$res->firstname." ".$res->lastname.' '._BACK_FROM_VACATION, $_SESSION['config']['databasetype']);
	}
	?>
		 <script language="javascript"> window.location.href="<?php echo $_SESSION['config']['businessappurl'];?>index.php";</script>
	<?php
	exit();
}
?>
<h1 ><img src="<?php echo $_SESSION['config']['img'];?>/picto_help_b.gif"  align="middle" /><?php echo _MISSING_ADVERT_TITLE; ?></h1>
<div id="inner_content" class="clearfix">
<h2 class="tit" align="center"><?php echo_MISSING_ADVERT_01; ?></h2>
<p align="center"><?php echo _MISSING_ADVERT_02; ?> </p>
<p align="center"><?php echo _MISSING_CHOOSE; ?></p>

<form name="redirect_form" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=advert_missing&module=basket">
	<p align="center">
    <input name="value" type="hidden" value="submit">
    <input name="cancel" type="submit"  value="<?php echo _CONTINUE; ?>" align="middle" class="button" />
    <input name="cancel" type="button" value="<?php echo _CANCEL;?>" onclick="window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>logout.php?coreurl=<?php echo $_SESSION['config']['coreurl'];?>';" align="middle" class="button" />
    </p>
</form>
</div>
