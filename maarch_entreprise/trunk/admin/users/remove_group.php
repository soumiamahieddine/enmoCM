<?php

if(isset($_REQUEST['usergroups']) && $_REQUEST['usergroups'] >= 0)
{
	$group_ind = explode('#', $_REQUEST['usergroups']);
	unset($group_ind[count($group_ind) -1]);
	print_r($group_ind);
	print_r($_SESSION['m_admin']['users']['groups']);
	for($i=0;$i<count($group_ind);$i++)
	{
		for($j=0; $j<count($_SESSION['m_admin']['users']['groups']);$j++)
		{
			if(!empty($group_ind[$i]) && trim($group_ind[$i]) == trim($_SESSION['m_admin']['users']['groups'][$j]['GROUP_ID']))
			{
				unset($_SESSION['m_admin']['users']['groups'][$j]);
				break;
			}
		}
	}
	array_unique($_SESSION['m_admin']['users']['groups']);
	print_r($_SESSION['m_admin']['users']['groups']);
	
	$_SESSION['m_admin']['load_group'] = false;
	echo "{ status : 0 }";
}
else
{
	echo "{ status : 1 }";
}
?>
