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
* @brief  Form to manage the group security (iframe included in group management form)
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');

require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");

$func = new functions();
$sec= new security();
$_SESSION['doctypes_choosen'] = array();

$_SESSION['m_admin']['collection_choice'] = "coll_1";


if(isset($_REQUEST['setRights']))
{
	$sec->init_rights_session();
	if(count($_REQUEST['rights_insert'])>0 )
	{
		$tab = array();
		for ($i=0; $i<count($_REQUEST['rights_insert']); $i++)
		{
			array_push($tab,$_REQUEST['rights_insert'][$i]);
		}
		$sec->set_rights_session($tab, 'CAN_INSERT');
	}
	if (count($_REQUEST['rights_update']) > 0)
	{
		$tab2 = array();
		for ($j=0; $j<count($_REQUEST['rights_update']); $j++)
		{
			array_push($tab2,$_REQUEST['rights_update'][$j]);
		}
		$sec->set_rights_session($tab2, 'CAN_UPDATE');
	}
	if ( count($_REQUEST['rights_delete']) > 0 )
	{
		$tab2 = array();
		for ($j=0; $j<count($_REQUEST['rights_delete']); $j++)
		{
			array_push($tab2,$_REQUEST['rights_delete'][$j]);
		}
		$sec->set_rights_session($tab2, 'CAN_DELETE');
	}
	$_SESSION['m_admin']['load_security'] = false;
}
if(isset($_REQUEST['modifyAccess']))
{
	$_SESSION['m_admin']['init'] = true;
	if(count($_REQUEST['security'])>0)
	{
		?>
		<script type="text/javascript" language="javascript">window.open('<?php  echo $_SESSION['config']['businessappurl'];?>admin/groups/add_grant.php?collection=<?php  echo $_REQUEST['security'][0];?>','modify','toolbar=no,status=no,width=800,height=450,left=150,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');</script>
		<?php
	}
}
if(isset($_REQUEST['removeAccess']))
{
	$tab = array();
	if(count($_REQUEST['security'])>0)
	{
		for($i=0; $i<count($_REQUEST['security']); $i++)
		{
			array_push($tab,$_REQUEST['security'][$i]);
		}
		$sec->remove_security($tab);
	}
	else
	{

	}
	$_SESSION['m_admin']['load_security'] = false;
}
//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_MANAGE_RIGHTS);
?>
<body id="iframe">
<div class="block" >
<?php  //$func->show_array($_SESSION['m_admin']['groups']['security']);
?>
<h2 class="tit"><small><?php  echo _MANAGE_RIGHTS;?> : </small></h2>
<form name="security_form" method="get">
	<?php
	if(count($_SESSION['m_admin']['groups']['security']) < 1 )
	{
		echo _THE_GROUP." "._HAS_NO_SECURITY.".<br/>";
		echo _DEFINE_A_GRANT."<br/>";
	}
	else
	{
		?>
		<table width="100%" border = "0">
			<tr >
				<td width="20">&nbsp;</td>
				<td width="90" class="column"><?php  echo _COLLECTION;?></td>
				<td width="250" class="column"><?php  echo _WHERE_CLAUSE;?></td>
				<td width="60" class="column"><?php  echo _INSERT; ?></td>
				<td width="90" class="column"><?php  echo _UPDATE; ?></td>
				<td width="90" class="column"><?php  echo _DELETE_SHORT; ?></td>
			</tr>
		<?php
		for($i=0; $i < count($_SESSION['m_admin']['groups']['security']); $i++)
		{
			if($_SESSION['m_admin']['groups']['security'][$i] <> "")
			{
				?>
				<tr>
					<td width="20"><input type="checkbox"  class="check" name="security[]" value="<?php  echo $_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']; ?>" /></td>
					<td width="90"><?php  echo $_SESSION['collections'][$_SESSION['m_admin']['groups']['security'][$i]['IND_COLL_SESSION']]['label']; ?></td>
					<td width="250"><?php  echo $func->cut_string(stripslashes($func->show_string($_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'])),50); ?></td>
					<td width="60"><input type="checkbox"  class="check" name="rights_insert[]" value="<?php  echo $_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']; ?>" <?php  if($_SESSION['m_admin']['groups']['security'][$i]['CAN_INSERT'] == 'Y') { echo 'checked="checked"';} ?> disabled="disabled" /></td>
					<td width="90"><input type="checkbox"  class="check" name="rights_update[]" value="<?php  echo $_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']; ?>" <?php  if($_SESSION['m_admin']['groups']['security'][$i]['CAN_UPDATE'] == 'Y') { echo 'checked="checked"';} ?>disabled="disabled" /></td>
					<td width="90"><input type="checkbox"  class="check" name="rights_delete[]" value="<?php  echo $_SESSION['m_admin']['groups']['security'][$i]['COLL_ID']; ?>" <?php  if($_SESSION['m_admin']['groups']['security'][$i]['CAN_DELETE'] == 'Y') { echo 'checked="checked"';} ?> disabled="disabled"/></td>
				</tr>
				<?php
			}
		}
		?>
			<tr><td height="20">&nbsp;</td></tr>
		</table>
		<?php
	}
	if (count($_SESSION['m_admin']['groups']['security']) > 0)
	{
		?>
		<input type="submit" name="modifyAccess" value="<?php  echo _MODIFY_ACCESS; ?>" class="button"/>
		<input type="submit" name="removeAccess" value="<?php  echo _REMOVE_ACCESS; ?>" class="button"/>
		<?php
	}
	if (count($_SESSION['collections']) > count($_SESSION['m_admin']['groups']['security']))
	{
		?>
		<input type="button" name="addGrant" class="button" onClick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>admin/groups/add_grant.php','add','toolbar=no,status=no,width=800,height=450,left=150,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');" value="<?php  echo _ADD_GRANT; ?>" />
		<?php
	}
	/*
	if (count($_SESSION['m_admin']['groups']['security']) > 0)
	 {
	 ?>
		<input type="submit" name="setRights" class="button" value="<?php  echo _UPDATE_RIGHTS; ?>" />
	<?php
	}
	*/
	?>
	<br/><br/>
</form>
</div>

</body>
</html>
