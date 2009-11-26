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
* @brief Frame to edit the where clause in expert mode
*
* @file
* @author  Laurent Giovannoni  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core/class/class_security.php");
$core_tools = new core_tools();
$db = new dbquery();
$func = new functions();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');
$_SESSION['m_admin']['doctypes'] = array();
$db->connect();
$db->query("select * from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' and coll_id = '".$_SESSION['m_admin']['group']['coll_id']."' order by description");
while($line = $db->fetch_object())
{
	array_push($_SESSION['m_admin']['doctypes'], array( "ID" => $line->type_id, "COMMENT" => $func->show_string($line->description)));
}
$func = new functions();
$sec= new security();
if($core_tools->is_module_loaded("basket"))
{
	if(isset($_REQUEST['services']) && count($_REQUEST['services']) > 0)
	{
		if(isset($_SESSION['entities_choosen']))
		{
			for($i=0; $i < count($_REQUEST['services']); $i++)
			{
				if(!in_array(trim($_REQUEST['services'][$i]), $_SESSION['entities_choosen']))
				{
					array_push($_SESSION['entities_choosen'], trim($_REQUEST['services'][$i]));
				}
			}
		}
	}
	else if(isset($_REQUEST['serviceslist']) && count($_REQUEST['serviceslist']) > 0)
	{
		for($i=0; $i < count($_SESSION['entities_choosen']); $i++)
		{
			for($j=0; $j < count($_REQUEST['serviceslist']); $j++)
			{
				if(trim($_REQUEST['serviceslist'][$j]) == trim($_SESSION['entities_choosen'][$i]))
				{
					unset($_SESSION['entities_choosen'][$i]);
				}
			}
		}
		$_SESSION['entities_choosen'] = array_values($_SESSION['entities_choosen']);
	}
	elseif(isset($_REQUEST['services']) && count($_REQUEST['services']) <= 0)
	{
		$_SESSION['entities_choosen'] = array();
	}
}

if(isset($_REQUEST['doctypes']) && count($_REQUEST['doctypes']) > 0)
{
	//$_SESSION['doctypes_choosen'] = array();
	if(isset($_SESSION['doctypes_choosen']))
	{
		for($i=0; $i < count($_REQUEST['doctypes']); $i++)
		{
			if(!in_array(trim($_REQUEST['doctypes'][$i]), $_SESSION['doctypes_choosen']))
			{
				array_push($_SESSION['doctypes_choosen'], trim($_REQUEST['doctypes'][$i]));
			}
		}
	}
}
elseif(isset($_REQUEST['doctypeslist']) && count($_REQUEST['doctypeslist']) > 0)
{
	for($i=0; $i < count($_SESSION['doctypes_choosen']); $i++)
	{
		for($j=0; $j < count($_REQUEST['doctypeslist']); $j++)
		{
			if(trim($_REQUEST['doctypeslist'][$j]) == trim($_SESSION['doctypes_choosen'][$i]))
			{
				unset($_SESSION['doctypes_choosen'][$i]);
			}
		}
	}
	$_SESSION['doctypes_choosen'] = array_values($_SESSION['doctypes_choosen']);
}
elseif(isset($_REQUEST['doctypes']) && count($_REQUEST['doctypes']) <= 0)
{

}
if($core_tools->is_module_loaded("basket"))
{
	if(isset($_SESSION['entities_choosen']))
	{
		$_SESSION['entities_choosen_where_clause'] = implode($_SESSION['entities_choosen'],'\',\'');
		$_SESSION['entities_choosen_where_clause'] = " DESTINATION IN ('".$_SESSION['entities_choosen_where_clause']."')";
		$_SESSION['entities_choosen_where_clause'] = str_replace("'',", "", $_SESSION['entities_choosen_where_clause']);
	}
}
if(isset($_SESSION['doctypes_choosen']))
{
	$_SESSION['doctypes_choosen_where_clause'] = implode($_SESSION['doctypes_choosen'],'\',\'');
	$_SESSION['doctypes_choosen_where_clause'] = " TYPE_ID IN ('".$_SESSION['doctypes_choosen_where_clause']."')";
	$_SESSION['doctypes_choosen_where_clause'] = str_replace("'',", "", $_SESSION['doctypes_choosen_where_clause']);
}

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_MANAGE_RIGHTS);
?>
<body id="iframe">
<br/>
<center><?php  echo _ASSISTANT_MODE; ?></center>
<form name="choose_services" id="choose_services" method="post" action="frame_expert_mode.php">
	<table>
		<tr>
			<?php
			if($core_tools->is_module_loaded("basket"))
			{
			?>
				<td>
					<table align="left" border="0" width="100%">
						<tr>
							<td valign="top" width="48%"><center><?php  echo _ENTITIES_LIST;?></center></td>
							<td width="5%">&nbsp;</td>
							<td valign="top" width="47%"><center><?php  echo _SELECTED_ENTITIES;?></center></td>
						</tr>
						<tr>
						 <td width="45%" align="center" valign="top">
							<select name="serviceslist[]" class="multiple_list_entities" ondblclick='moveclick(document.choose_services.elements["serviceslist[]"],document.choose_services.elements["services[]"]);this.form.submit();' multiple="multiple">
							<?php
							for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
							{
								$state_services = false;
								for($j=0;$j<count($_SESSION['entities_choosen']);$j++)
								{
									if(trim($_SESSION['m_admin']['entities'][$i]['ID']) == trim($_SESSION['entities_choosen'][$j]))
									{
										$state_services = true;
									}
								}
								if($state_services == false)
								{
									?>
									<option value="<?php  echo $_SESSION['m_admin']['entities'][$i]['ID']; ?>"><?php  echo $_SESSION['m_admin']['entities'][$i]['COMMENT']; ?></option>
									<?php
								}
							}
							?>
							</select>
							<br/><br/>
							<a href='javascript:selectall(document.forms["choose_services"].elements["serviceslist[]"]);' class="choice"><?php  echo _SELECT_ALL; ?></a>
						</td>
						<td width="10%" align="center">
							<input type="button" class="button" value="<?php  echo _ADD; ?>" onclick='Move(document.choose_services.elements["serviceslist[]"],document.choose_services.elements["services[]"]);this.form.submit();' align="middle"/>
							<br />
							<br />
							<input type="button" class="button"  value="<?php  echo _REMOVE; ?>" onclick='Move(document.choose_services.elements["services[]"],document.choose_services.elements["serviceslist[]"]);this.form.submit();' align="middle"/>
						</td>
						<td width="45%" align="center" valign="top">
							<select name="services[]" class="multiple_list_entities" width="100" ondblclick='moveclick(document.choose_services.elements["services[]"],document.choose_services.elements["serviceslist"]);this.form.submit();' multiple="multiple" >
								<?php
								for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
								{
									$state_services = false;
									for($j=0;$j<count($_SESSION['entities_choosen']);$j++)
									{
										if(trim($_SESSION['m_admin']['entities'][$i]['ID']) == trim($_SESSION['entities_choosen'][$j]))
										{
											$state_services = true;
										}
									}
									if($state_services == true)
									{
										?>
										<option value="<?php  echo $_SESSION['m_admin']['entities'][$i]['ID']; ?>" ><?php  echo $_SESSION['m_admin']['entities'][$i]['COMMENT']; ?></option>
										<?php
									}
								}
								?>
							</select>
							<br/><br/>
							<a href='javascript:selectall(document.forms["choose_services"].elements["services[]"]);' class="choice">
							<?php  echo _SELECT_ALL; ?></a></td>
						</tr>
						<tr>
							<td height="10">&nbsp;</td>
						</tr>
					</table>
				</td>
			<?php
			}
			?>
			<td>
				<table align="left" border="0" width="100%">
					<tr>
						<td valign="top" width="48%"><center><?php  echo _DOCTYPES_LIST_SHORT;?></center></td>
						<td width="5%">&nbsp;</td>
						<td valign="top" width="47%"><center><?php  echo _SELECTED_DOCTYPES;?></center></td>
					</tr>
					<tr>
					 <td width="45%" align="center" valign="top">
						<select name="doctypeslist[]" class="multiple_list_doctypes" ondblclick='moveclick(document.choose_services.elements["doctypeslist[]"],document.choose_services.elements["doctypes[]"]);this.form.submit();' multiple="multiple">
						<?php
						for($i=0;$i<count($_SESSION['m_admin']['doctypes']);$i++)
						{
							$state_doctypes = false;
							for($j=0;$j<count($_SESSION['doctypes_choosen']);$j++)
							{
								if(trim($_SESSION['m_admin']['doctypes'][$i]['ID']) == trim($_SESSION['doctypes_choosen'][$j]))
								{
									$state_doctypes = true;
								}
							}
							if($state_doctypes == false)
							{
								?>
								<option value="<?php  echo $_SESSION['m_admin']['doctypes'][$i]['ID']; ?>"><?php  echo $_SESSION['m_admin']['doctypes'][$i]['COMMENT']; ?></option>
								<?php
							}
						}
						?>
						</select>
						<br/><br/>
						<a href='javascript:selectall(document.forms["choose_services"].elements["doctypeslist[]"]);' class="choice"><?php  echo _SELECT_ALL; ?></a>
					</td>
					<td width="10%" align="center">
						<input type="button" class="button" value="<?php  echo _ADD; ?>" onclick='Move(document.choose_services.elements["doctypeslist[]"],document.choose_services.elements["doctypes[]"]);this.form.submit();' align="middle"/>
						<br />
						<br />
						<input type="button" class="button"  value="<?php  echo _REMOVE; ?>" onclick='Move(document.choose_services.elements["doctypes[]"],document.choose_services.elements["doctypeslist[]"]);this.form.submit();' align="middle"/>
					</td>
					<td width="45%" align="center" valign="top">
						<select name="doctypes[]" class="multiple_list_doctypes" ondblclick='moveclick(document.choose_services.elements["doctypes[]"],document.choose_services.elements["doctypeslist"]);this.form.submit();' multiple="multiple" >
							<?php
							for($i=0;$i<count($_SESSION['m_admin']['doctypes']);$i++)
							{
								$state_doctypes = false;
								for($j=0;$j<count($_SESSION['doctypes_choosen']);$j++)
								{
									if(trim($_SESSION['m_admin']['doctypes'][$i]['ID']) == trim($_SESSION['doctypes_choosen'][$j]))
									{
										$state_doctypes = true;
									}
								}
								if($state_doctypes == true)
								{
									?>
									<option value="<?php  echo $_SESSION['m_admin']['doctypes'][$i]['ID']; ?>" ><?php  echo $_SESSION['m_admin']['doctypes'][$i]['COMMENT']; ?></option>
									<?php
								}
							}
							?>
						</select>
						<br/><br/>
						<a href='javascript:selectall(document.forms["choose_services"].elements["doctypes[]"]);' class="choice">
						<?php  echo _SELECT_ALL; ?></a></td>
					</tr>
					<tr>
						<td height="10">&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
</body>
</html>
