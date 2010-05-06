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
* @brief  Pop up : show the users list of a given group
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');

$group = "";

if(isset($_GET['id']) && !empty($_GET['id']))
{
	$group = $_GET['id'];
}

$db = new dbquery();
$db->connect();
$users = array();
if($core_tools->is_module_loaded('entities'))
{
	$db->query("select  u.lastname as nom, u.firstname as prenom , u.user_id  as id, e.entity_label
				from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['users']." u, ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['ent_entities']." e where ue.user_id = u.user_id AND ue.entity_id = e.entity_id AND uc.user_id = u.user_id AND u.enabled = 'Y' AND uc.group_id = '".$group."' and ue.primary_entity = 'Y' order by u.lastname asc");

	while($res = $db->fetch_object())
	{
		array_push($users, array( 'ID' => $res->id, 'NOM' => $res->nom, 'PRENOM' => $res->prenom, 'SERVICE' => $res->entity_label));
	}
}
else
{
	$db->query("select  u.department, u.lastname as nom, u.firstname as prenom , u.user_id  as id
				from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['users']." u where uc.user_id = u.user_id AND u.enabled = 'Y' AND uc.group_id = '".$group."' order by u.lastname asc");

	while($res = $db->fetch_object())
	{
		array_push($users, array( 'ID' => $res->id, 'NOM' => $res->nom, 'PRENOM' => $res->prenom, 'SERVICE' => $res->department));
	}
}


//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_USERS_LIST_IN_GROUP.' '.$group, true, false);
$time = $core_tools->get_session_time_expire();
?>
<body onload="setTimeout(window.close, <?php  echo $time;?>*60*1000);" class="popup_content" id="users_popup" >
<h2 class="tit"><?php  echo _USERS_LIST_IN_GROUP.' '.$group;?></h2>

<table cellpadding="0" cellspacing="0" border="0" class="listing">
	<thead>
		<tr>
			<th><?php  echo _LASTNAME;?></th>
			<th ><?php  echo _FIRSTNAME;?></th>
			<th ><?php  echo _ENTITY;?></th>
		</tr>
	</thead>

<tbody>
	 <?php
$color = ' class="col"';
 for($i=0;$i<count($users);$i++)
	{
	 	if($color == ' class="col"')
		{
			$color = '';
		}
		else
		{
			$color = ' class="col"';
		}
			?>
	 <tr <?php  echo $color; ?> >
			   <td width="30%"><?php  echo $users[$i]['NOM'];?></td>
			      <td width="30%"><?php  echo $users[$i]['PRENOM'];?></td>
			   <td><?php  echo $users[$i]['SERVICE']; ?></td>
	</tr>
			<?php
	}
?>
</tbody>
</table>
<br/>
<br/>
<div align="center">
<input type="button" class="button" onclick="self.close()" value="<?php  echo _CLOSE_WINDOW;?>" align="middle">
</div>
<?php $core_tools->load_js();?>
</body>
</html>
