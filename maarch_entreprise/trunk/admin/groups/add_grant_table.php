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
* @brief Process the add_grant.php form, update the session variables with the new grant data
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
include('apps/maarch_entreprise/security_bitmask.php');
include('core/manage_bitmask.php');
$core_tools = new core_tools();
$core_tools->load_js();
$_SESSION['error'] = "";
if(!empty($_SESSION['m_admin']['group']['coll_id']) && isset($_SESSION['m_admin']['group']['coll_id']))
{
	$comment = "";
	if(isset($_REQUEST['comment']))
	{
		$comment = $_REQUEST['comment'];
	}
	$target = 'ALL';
	if(isset($_REQUEST['target']))
	{
		$target = $_REQUEST['target'];
	}
	$where = '';
	if(isset($_REQUEST['where']))
	{
		$where = $_REQUEST['where'];
	}
	$mode = '';
	if (isset($_REQUEST['mode']))
	{
		$mode = $_REQUEST['mode'];
	}
	$start_date = '';
	if (isset($_REQUEST['start_date']))
	{
		$start_date = $_REQUEST['start_date'];
	}
	$stop_date = '';
	if (isset($_REQUEST['stop_date']))
	{
		$stop_date = $_REQUEST['stop_date'];
	}
	
	$bitmask = 0; 
	if(isset($_REQUEST['rights_bitmask']) && count($_REQUESt['rights_bitmask']) > 0)
	{
		for($i=0; $i<count($_REQUEST['rights_bitmask']);$i++)
		{
			$bitmask = set_right($bitmask, $_REQUEST['rights_bitmask'][$i]);
		}
	}
	$sec = new security();
	$sec->add_grouptmp_session($_SESSION['m_admin']['group']['coll_id'], $where , $target, $bitmask, $comment, $mode, $start_date, $stop_date);
}
else
{
}
?>
<script language="javascript">
updateContent('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=groups_form&admin=groups', window.opener.$('access'));self.close();
//window.opener.top.frames['group_form'].location.href='<?php $_SESSION['config']['businessappurl'];?>index.php?display&page=groups_form&admin=groups';self.close();
</script>
<?php exit();?>