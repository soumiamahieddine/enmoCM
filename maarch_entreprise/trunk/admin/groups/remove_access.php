<?php

if(isset($_REQUEST['security']))
{
	include_once('../../../../core/init.php');

	require_once("core/class/class_functions.php");
	require_once("core/class/class_db.php");
	require_once("core/class/class_security.php");
	
	$sec = new security();

	$tmp_array= array();
	if(count($_REQUEST['security'])>0)
	{
		for($i=0; $i<count($_REQUEST['security']); $i++)
		{
			array_push($tmp_array,$_REQUEST['security'][$i]);
		}
		$_SESSION['m_admin']['groups']['security'] = $sec->remove_security($tmp_array, $_SESSION['m_admin']['groups']['security']);
	}
	$_SESSION['m_admin']['load_security'] = false;
	echo "{ status : 0 }";
}
else
{
	echo "{ status : 1 }";
}
?>
