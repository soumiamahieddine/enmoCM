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


//$core_tools->load_html();
//$core_tools->load_header(_MANAGE_RIGHTS);
?>
<!--<body id="iframe">-->
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
		
		<?php
		
		foreach(array_keys($_SESSION['m_admin']['groups']['security']) as $coll)
		{
			for($i=0; $i < count($_SESSION['m_admin']['groups']['security'][$coll]); $i++)
			{
				if($_SESSION['m_admin']['groups']['security'][$coll][$i] <> "")
				{
					?>
					<tr>
						<td>
							<div align="left" id="access_<?php echo $_SESSION['m_admin']['groups']['security'][$coll][$i]['SECURITY_ID'];?>">
								<div style="float:left;">
									<input type="checkbox"  class="check" name="security[]" value="<?php  echo $_SESSION['m_admin']['groups']['security'][$coll][$i]['SECURITY_ID']; ?>" />
								</div>
								<div>
									<?php echo $func->show_string($_SESSION['m_admin']['groups']['security'][$coll][$i]['COMMENT']);?>
								</div>
								<div align="left" style="margin-left:5%;">
								
									<span ><?php echo _COLLECTION;?> : </span><span><?php echo $_SESSION['collections'][$_SESSION['m_admin']['groups']['security'][$coll][$i]['IND_COLL_SESSION']]['label']; ?></span>
									<span align="right">
									<?php if(!empty($_SESSION['m_admin']['groups']['security'][$coll][$i]['START_DATE']) )
									{
										echo _SINCE.' : '.$func->format_date_db($_SESSION['m_admin']['groups']['security'][$coll][$i]['START_DATE']);
									}
									echo '&nbsp;';
									if(!empty($_SESSION['m_admin']['groups']['security'][$coll][$i]['STOP_DATE']) )
									{
										echo _FOR.' : '.$func->format_date_db($_SESSION['m_admin']['groups']['security'][$coll][$i]['STOP_DATE']);
									}?>
									</span>
								</div>
								<div align="right" onclick="new Effect.toggle('access_info_<?php echo $_SESSION['m_admin']['groups']['security'][$coll][$i]['SECURITY_ID'];?>', 'blind', {delay:0.2});return false;" >
								 <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif" alt="<?php _MORE_INFOS;?>" title="<?php _MORE_INFOS;?>" /><span class="lb1-details">&nbsp;</span>
								</div>
								<div style="display:none;" id="access_info_<?php echo $_SESSION['m_admin']['groups']['security'][$coll][$i]['SECURITY_ID'];?>" class="access_info desc">
									<div class="ref-unit">
										<div>
										<?php echo _WHERE_CLAUSE_TARGET.' : ';
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
										}?></div>
										<div> 
											<?php echo _WHERE_CLAUSE.' : '.$func->show_string($_SESSION['m_admin']['groups']['security'][$coll][$i]['WHERE_CLAUSE']);?>
										</div>
										<div>
											<span><?php echo _TASKS;?> :</span><br/>
												<?php
												for($k=0;$k<count($_ENV['security_bitmask']); $k++)
												{
													echo '<div class="task"><img ';
													if(check_right($_SESSION['m_admin']['groups']['security'][$coll][$i]['RIGHTS_BITMASK'] , $_ENV['security_bitmask'][$k]['ID']))
													{
														echo 'src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_stat_enabled.gif" alt="'._ENABLED.'"';
													}
													else
													{
														echo 'src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_stat_disabled.gif" alt="'._DISABLED.'"';
													}
													echo ' />&nbsp;';
													echo $_ENV['security_bitmask'][$k]['LABEL'].'</div>';
												} ?>

										</div>
										<p style="clear:both;"></p>
									</div>
								</div>
							</div>
						</td>
						
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
		<input type="button" name="modifyAccess" value="<?php  echo _MODIFY_ACCESS; ?>" class="button" onclick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=groups&page=add_grant&collection=<?php  echo $_REQUEST['security'][0];?>','modify','toolbar=no,status=no,width=850,height=650,left=150,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');" />
		<input type="button" name="removeAccess" value="<?php  echo _REMOVE_ACCESS; ?>" class="button" onclick="removeAccess('apps/maarch_entreprise/admin/groups/remove_access.php', document.getElementsByName('security[]'));"/>
		<?php
	}
		?>
		<input type="button" name="addGrant" class="button" onclick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=groups&page=add_grant','add','toolbar=no,status=no,width=850,height=650,left=150,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');" value="<?php  echo _ADD_GRANT; ?>" />
	<br/><br/>
</form>
</div>
<?php // $core_tools->load_js();?>
<!--</body>
</html>-->
