<?php

if(isset($_REQUEST['removeGroup']) && !empty($_REQUEST['removeGroup']))
{
	if(count($_REQUEST['groups'])>0)
	{
		$tab = array();
    	for ($i=0; $i<count($_REQUEST['groups']); $i++)
		{
			array_push($tab,$_REQUEST['groups'][$i]);
 		}
		$ugc = new usergroup_content();
		$ugc->remove_session($tab);
   	}
	$_SESSION['m_admin']['load_group'] = false;

}

if(isset($_REQUEST['setPrimary']))
{
	if(count($_REQUEST['groups'])>0)
	{
    		$ugc = new usergroup_content();
			$ugc->erase_primary_group_session();
			$ugc->set_primary_group_session($_REQUEST['groups'][0]);
   	}

	$_SESSION['m_admin']['load_group'] = false;

}
?>
