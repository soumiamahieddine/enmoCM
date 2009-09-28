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
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");

$_SESSION['error'] = "";
if(!empty($_SESSION['m_admin']['group']['coll_id']) && isset($_SESSION['m_admin']['group']['coll_id']))
{
	$insert = 'N';
	if(isset($_REQUEST['insert']) && count($_REQUEST['insert']) > 0)
	{
		 $insert = 'Y';
	}
	$update = 'N';
	if (isset($_REQUEST['update']) && count($_REQUEST['update']) > 0)
	{
		 $update = 'Y';
	}
	$delete = 'N';
	if(isset($_REQUEST['delete']) && count($_REQUEST['delete']) > 0)
	{
		 $delete = 'Y';
	}
	$comment = "";
	if(isset($_REQUEST['comment']))
	{
		$comment = $_REQUEST['comment'];
	}
	$mode = '';
	if (isset($_REQUEST['mode']))
	{
		$mode = $_REQUEST['mode'];
	}
	$sec = new security();
	$sec->add_grouptmp_session($_SESSION['m_admin']['group']['coll_id'], $_REQUEST['where'], $comment, $insert, $update, $delete, $mode);
}
else
{
}
?>
<script language="javascript">
window.opener.top.frames['group_form'].location.href='groups_form.php';self.close();
</script>