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

$core_tools = new core_tools();

$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
include('apps/maarch_entreprise/security_bitmask.php');
include('core/manage_bitmask.php');

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
		<script type="text/javascript" language="javascript">window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=groups&page=add_grant&collection=<?php  echo $_REQUEST['security'][0];?>','modify','toolbar=no,status=no,width=850,height=650,left=150,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');</script>
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
$core_tools->load_header(_MANAGE_RIGHTS, true, false);
?>
<body id="iframe">
<div class="block" >
<?php //$func->show_array($_ENV['security_bitmask']); //$func->show_array($_SESSION['m_admin']['groups']['security']);
?>
<h2 class="tit"><small><?php  echo _MANAGE_RIGHTS;?> : </small></h2>
<form name="security_form" method="get" >
<input type="hidden" name="display" value="true" />
<input type="hidden" name="admin" value="groups" />
<input type="hidden" name="page" value="groups_form" />
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
				<td width="5%">&nbsp;</td>
				<td class="column"><?php  echo _COLLECTION;?></td>
				<td class="column"><?php  echo _WHERE_TARGET;?></td>
				<td class="column"><?php  echo _WHERE_CLAUSE;?></td>
				<td class="column"><?php  echo _TASKS; ?></td>
				<td class="column"><?php  echo _SINCE; ?></td>
				<td class="column"><?php  echo _FOR; ?></td>
			</tr>
		<?php
		
		foreach(array_keys($_SESSION['m_admin']['groups']['security']) as $coll)
		{
			for($i=0; $i < count($_SESSION['m_admin']['groups']['security'][$coll]); $i++)
			{
				if($_SESSION['m_admin']['groups']['security'][$coll][$i] <> "")
				{
					?>
					<tr>
						<td width="5%"><input type="checkbox"  class="check" name="security[]" value="<?php  echo $_SESSION['m_admin']['groups']['security'][$coll][$i]['COLL_ID']; ?>" /></td>
						<td ><?php  echo $_SESSION['collections'][$_SESSION['m_admin']['groups']['security'][$coll][$i]['IND_COLL_SESSION']]['label']; ?></td>
						<td><?php 
						if( $_SESSION['m_admin']['groups']['security'][$coll][$i]['WHERE_TARGET'] == 'DOC')
						{
							echo _DOCS;
						}
						elseif($_SESSION['m_admin']['groups']['security'][$coll][$i]['WHERE_TARGET'] == 'CLASS')
						{
							echo _CLASS_SCHEME;
						}
						else
						{
							echo _ALL;
						}?></td>
						<td ><?php  echo $func->cut_string(stripslashes($func->show_string($_SESSION['m_admin']['groups']['security'][$coll][$i]['WHERE_CLAUSE'])),50); ?></td>
						<td><div onclick="new Effect.toggle('tasks_list', 'blind', {delay:0.2});return false;" >
								&nbsp;<i><?php  echo _SEE_TASKS;?></i> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="<?php _SEE_TASKS;?>" />
								<span class="lb1-details">&nbsp;</span></div>
								<div class="desc" id="tasks_list" style="display:none;">
									<div class="ref-unit">
										<ul align="right">
										<?php  for($k=0;$k<count($_ENV['security_bitmask']); $k++)
											{
												echo "<li>".$_ENV['security_bitmask'][$k]['LABEL'].'<img ';
												
												if(check_right($_SESSION['m_admin']['groups']['security'][$coll][$i]['RIGHTS_BITMASK'] , $_ENV['security_bitmask'][$k]['ID']))
												{
													echo 'src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_stat_enabled.gif" alt="'._ENABLED.'"';
												}
												else
												{
													echo 'src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_stat_disabled.gif" alt="'._ENABLED.'"';
												}
												echo " /></li>";
											} ?>
										</ul>
									</div>
								</div>
						</td>
						<td><?php echo $func->format_date($_SESSION['m_admin']['groups']['security'][$coll][$i]['START_DATE']);?></td>
						<td><?php echo $func->format_date($_SESSION['m_admin']['groups']['security'][$coll][$i]['STOP_DATE']);?></td>
					</tr>
					<?php
				}
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
		<input type="button" name="addGrant" class="button" onClick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=groups&page=add_grant','add','toolbar=no,status=no,width=850,height=650,left=150,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');" value="<?php  echo _ADD_GRANT; ?>" />
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
<?php $core_tools->load_js();?>
</body>
</html>
