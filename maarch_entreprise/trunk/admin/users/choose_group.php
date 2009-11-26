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
* @brief Process the add_usergroup_content.php form
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

if(!empty($_REQUEST['groupe']) && isset($_REQUEST['groupe']))
{
	require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroup_content.php");
	$ugc = new usergroup_content();
	$ugc->connect();

	$ugc->query("select group_desc from ".$_SESSION['tablename']['usergroups']." where group_id = '".$_REQUEST['groupe']."'");
	$res = $ugc->fetch_object();
	$ugc->add_usertmp_to_group_session( $_REQUEST['groupe'], $_REQUEST['role'], $res->group_desc);
}
else
{
	$_SESSION['error'] = _NO_GROUP_SELECTED."!";
	exit;
}
	?>
<script language="javascript">
window.parent.opener.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=ugc_form';self.close();
</script>

