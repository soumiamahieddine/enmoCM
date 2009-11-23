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
* @brief  Form to choose a group in the user management (iframe included in the user management)
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->load_lang();

require_once( "apps".DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroup_content.php");
$func = new functions();

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

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_USER_GROUPS_TITLE);
?>
<body id="iframe">
<div class="block">
<form name="usergroup_content" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php" >
<input type="hidden" name="display" value="true" />
<input type="hidden" name="admin" value="users" />
<input type="hidden" name="page" value="ugc_form" />
 <h2 class="tit"> <?php  echo _USER_GROUPS_TITLE; ?> :</h2>
<?php

	if(empty($_SESSION['m_admin']['users']['groups'])   )
	{
		echo _USER_BELONGS_NO_GROUP.".<br/>";
		echo _CHOOSE_ONE_GROUP.".<br/>";
	}
	else
	{
		for($theline = 0; $theline < count($_SESSION['m_admin']['users']['groups']) ; $theline++)
		{
				if( $_SESSION['m_admin']['users']['groups'][$theline]['PRIMARY'] == 'Y')
				{
					?><img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/arrow_primary.gif" alt="<?php  echo _PRIMARY_GROUP;?>" title="<?php  echo _PRIMARY_GROUP;?>" /> <?php
				}
				else
				{
					echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				?>
				<input type="checkbox"  class="check" name="groups[]" value="<?php  echo  $_SESSION['m_admin']['users']['groups'][$theline]['GROUP_ID']; ?>" ><?php  echo $_SESSION['m_admin']['users']['groups'][$theline]['LABEL'] ; ?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><?php  echo $_SESSION['m_admin']['users']['groups'][$theline]['ROLE']; ?></i><br/></input>
				<?php
		}
		 ?> <br/><input class="button" type="submit" name="removeGroup" value="<?php  echo _DELETE_GROUPS; ?>" /><br/><br/>
<?php 	}

	if (count($_SESSION['m_admin']['users']['groups']) < $_SESSION['m_admin']['nbgroups']  || empty($_SESSION['m_admin']['users']['groups']))
	{
	?>
		<input class="button" type="button" name="addGroup" onClick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=add_usergroup_content','add','toolbar=no,status=no,width=400,height=150,left=500,top=300,scrollbars=no,top=no,location=no,resizable=yes,menubar=no')" value="<?php  echo _ADD_TO_GROUP; ?>" />
	<?php
	}

	?>
	<br/><br/>
	<?php  if (count($_SESSION['m_admin']['users']['groups']) > 0)
	{
	?>
		<input type="submit" class="button" name="setPrimary" value="<?php  echo _CHOOSE_PRIMARY_GROUP; ?>" />
	<?php
	}
	?>
	</form>
	</div>
</body>
</html>
