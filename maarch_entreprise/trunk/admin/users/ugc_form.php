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
core_tools::load_lang();
?>
<div class="block">
<form name="usergroup_content" method="get" action="#" >
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
					?><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=arrow_primary.gif" alt="<?php  echo _PRIMARY_GROUP;?>" title="<?php  echo _PRIMARY_GROUP;?>" /> <?php
				}
				else
				{
					echo "&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				?>
				<input type="checkbox"  class="check" name="groups[]" value="<?php  echo  $_SESSION['m_admin']['users']['groups'][$theline]['GROUP_ID']; ?>" ><?php  echo $_SESSION['m_admin']['users']['groups'][$theline]['LABEL'] ; ?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><?php  echo $_SESSION['m_admin']['users']['groups'][$theline]['ROLE']; ?></i><br/></input>
				<?php
		}
		 ?> <br/><input class="button" type="button" name="removeUsergroup" id="removeUsergroup" value="<?php  echo _DELETE_GROUPS; ?>" onclick="removeGroup('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=remove_group', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=ugc_form')"/><br/><br/>
<?php 	}

	if (count($_SESSION['m_admin']['users']['groups']) < $_SESSION['m_admin']['nbgroups']  || empty($_SESSION['m_admin']['users']['groups']))
	{
	?>
		<input class="button" type="button" name="addGroup" id="addGroup" onclick="displayModal('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=add_usergroup_content', 'add_ugc', 600, 150);" value="<?php  echo _ADD_TO_GROUP; ?>" />
	<?php
	}

	?>
	<br/><br/>
	<?php  if (count($_SESSION['m_admin']['users']['groups']) > 0)
	{
	?>
		<input type="submit" class="button" name="setPrimary" id="setPrimary" value="<?php  echo _CHOOSE_PRIMARY_GROUP; ?>"  onclick="setPrimaryGroup('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=set_primary_group', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=ugc_form');"/>
	<?php
	}
	?>
	</form>
	</div>
