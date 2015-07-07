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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$core_tools = new core_tools();
$core_tools->load_lang();
 $core_tools->load_html();
//here we building the header
$core_tools->load_header( '', true, false );

if($_SESSION['m_admin']['mode'] == "up" && $_SESSION['m_admin']['init'] == true)
{
	$_SESSION['m_admin']['chosen_foldertypes'] = array();
	$_SESSION['m_admin']['chosen_foldertypes'] = $_SESSION['m_admin']['loaded_foldertypes'];
	$_SESSION['m_admin']['init'] = false;
}
elseif($_SESSION['m_admin']['mode'] == "add")
{
	$_SESSION['m_admin']['chosen_foldertypes'] = array();
}

if(isset($_REQUEST['foldertypes']) && count($_REQUEST['foldertypes']) > 0)
{
		for($i=0; $i < count($_REQUEST['foldertypes']); $i++)
		{
			if(!in_array(trim($_REQUEST['foldertypes'][$i]), $_SESSION['m_admin']['chosen_foldertypes']))
			{
				array_push($_SESSION['m_admin']['chosen_foldertypes'], trim($_REQUEST['foldertypes'][$i]));
			}
		}
		$_SESSION['m_admin']['loaded_foldertypes'] = $_SESSION['m_admin']['chosen_foldertypes'];
}
else if(isset($_REQUEST['foldertypeslist']) && count($_REQUEST['foldertypeslist']) > 0)
{
	for($i=0; $i < count($_SESSION['m_admin']['chosen_foldertypes']); $i++)
	{

		for($j=0; $j < count($_REQUEST['foldertypeslist']); $j++)
		{
			if(trim($_REQUEST['foldertypeslist'][$j]) == trim($_SESSION['m_admin']['chosen_foldertypes'][$i]))
			{
				unset($_SESSION['m_admin']['chosen_foldertypes'][$i]);
			}
		}
	}
	$_SESSION['m_admin']['chosen_foldertypes'] = array_values($_SESSION['m_admin']['chosen_foldertypes']);
	$_SESSION['m_admin']['loaded_foldertypes'] = $_SESSION['m_admin']['chosen_foldertypes'];
}
elseif(isset($_REQUEST['foldertypes']) && count($_REQUEST['foldertypes']) <= 0)
{

	$_SESSION['m_admin']['chosen_foldertypes'] = array();
	$_SESSION['m_admin']['loaded_foldertypes'] = $_SESSION['m_admin']['chosen_foldertypes'];
}
?>
<body>
<form name="choose_foldertypes" id="choose_foldertypes" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=choose_foldertypes">
	<input type="hidden" name="display" value="true" />
    <input type="hidden" name="page" value="choose_foldertypes" />
		<table align="left" border="0" width="100%">
		<tr>
			<td valign="top" width="48%"><b class="tit"><?php echo _FOLDERTYPES_LIST;?></b></td>
			<td width="5%" >&nbsp;</td>
			<td valign="top" width="47%"><b class="tit"><?php echo _SELECTED_FOLDERTYPES;?></b></td>
		</tr>

		<tr>
		 <td width="45%" align="center" valign="top">
			<select name="foldertypeslist[]" class="multiple_list" ondblclick='moveclick(document.choose_foldertypes.elements["foldertypeslist[]"],document.choose_foldertypes.elements["foldertypes[]"]);this.form.submit();' multiple="multiple">
			<?php
			for($i=0;$i<count($_SESSION['m_admin']['foldertypes']);$i++)
			{
			$state_foldertypes = false;

			for($j=0;$j<count($_SESSION['m_admin']['chosen_foldertypes']);$j++)
			{
				if(trim($_SESSION['m_admin']['foldertypes'][$i]['id']) == trim($_SESSION['m_admin']['chosen_foldertypes'][$j]))
				{
					$state_foldertypes = true;
				}
			}

			if($state_foldertypes == false)
			{
				?>
				<option value="<?php functions::xecho($_SESSION['m_admin']['foldertypes'][$i]['id']);?>" alt="<?php functions::xecho($_SESSION['m_admin']['foldertypes'][$i]['label']);?>" title="<?php functions::xecho($_SESSION['m_admin']['foldertypes'][$i]['label']);?>"><?php functions::xecho($_SESSION['m_admin']['foldertypes'][$i]['label']);?></option>
				<?php
			}
		}
		?>
    </select>
	<br/><br/>
	<a href='javascript:selectall(document.forms["choose_foldertypes"].elements["foldertypeslist[]"]);' class="choice"><?php echo _SELECT_ALL;?></a></td>
    <td width="10%" align="center">
	<input type="button" class="button" value="<?php echo _ADD;?>" onclick='Move(document.choose_foldertypes.elements["foldertypeslist[]"],document.choose_foldertypes.elements["foldertypes[]"]);this.form.submit();' align="middle"/>
	<br />
	<br />
	<input type="button" class="button"  value="<?php echo _REMOVE;?>" onclick='Move(document.choose_foldertypes.elements["foldertypes[]"],document.choose_foldertypes.elements["foldertypeslist[]"]);this.form.submit();' align="middle"/>
	</td>
    <td width="45%" align="center" valign="top">
	<select name="foldertypes[]" class="multiple_list" ondblclick='moveclick(document.choose_foldertypes.elements["foldertypes[]"],document.choose_foldertypes.elements["foldertypeslist"])this.form.submit();' multiple="multiple" >
		<?php
		for($i=0;$i<count($_SESSION['m_admin']['foldertypes']);$i++)
		{
			$state_foldertypes = false;

			for($j=0;$j<count($_SESSION['m_admin']['chosen_foldertypes']);$j++)
			{
				if(trim($_SESSION['m_admin']['foldertypes'][$i]['id']) == trim($_SESSION['m_admin']['chosen_foldertypes'][$j]))
				{
					$state_foldertypes = true;
				}
			}


			if($state_foldertypes == true)
			{
				?>
				<option value="<?php functions::xecho($_SESSION['m_admin']['foldertypes'][$i]['id']);?>" ><?php functions::xecho($_SESSION['m_admin']['foldertypes'][$i]['label']);?></option>
				<?php
			}
		}
		?>
    </select>
	<br/><br/>
	<a href='javascript:selectall(document.forms["choose_foldertypes"].elements["foldertypes[]"]);' class="choice">
	<?php echo _SELECT_ALL;?></a></td>
	</tr>
	<tr> <td height="10">&nbsp;</td></tr>
		</table>
		</form>
<?php $core_tools->load_js();?>
</body>
</html>
