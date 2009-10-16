<?php
/*
*
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
* @brief   Pop up : Pop up used to create and modify model of the diffusion lists
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_entity.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_admin('admin_entities', 'basket');
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$func = new functions();

if(isset($_POST['valid']))
{
	$_SESSION["popup_suite"] = true;
	?>
	<script language="javascript">window.parent.opener.location.reload();self.close();</script>
	<?php
}

$users = array();
$what = "";
$where = "";
if(isset($_GET['what']) && !empty($_GET['what']))
{
	$what = addslashes($func->wash($_GET['what'], "no", "", "no"));
	if($_GET['service'] ==  "true")
	{
		if($what <> 'all')
		{
		$where = " and (department like '".strtolower($what)."%' or department like '".strtoupper($what)."%') order by department asc, lastname asc";
		}
		else
		{
			$where = ' order by department asc, lastname asc';
		}
	}
	else
	{
		if($what <> 'all')
		{
		$where = " and (lastname like '".strtolower($what)."%' or lastname like '".strtoupper($what)."%') order by lastname asc";
		}
		else
		{
			$where = ' order by lastname asc';
		}
	}
}
$db = new dbquery();
$db->connect();
$db->query("select user_id, firstname, lastname, department, mail from ".$_SESSION['tablename']['users']." where enabled <> 'N'".$where);

$i=0;
while($line = $db->fetch_object())
{
	array_push($users, array("ID" => $line->user_id, "FIRSTNAME" => $line->firstname, "NAME" => $line->lastname, "DEP_ID" => $line->department, "MAIL" => $line->mail));
}
$id = "";
$desc ="";
if(isset($_GET['action']) && $_GET['action'] == "add" )
{
	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$id = $_GET['id'];
		$find = false;
		for($i=0; $i < count($_SESSION['m_admin']['entity']['listmodel']); $i++)
		{
			if($id == $_SESSION['m_admin']['entity']['listmodel'][$i]['USER_ID'])
			{
				$find = true;
				break;
			}
		}
		if($find == false)
		{
			$conn = new dbquery();
			$conn->connect();
			$conn->query("select firstname, lastname, department, mail from ".$_SESSION['tablename']['users']." where user_id='".$id."'");
			$line = $conn->fetch_object();
			array_push($_SESSION['m_admin']['entity']['listmodel'], array('USER_ID' => $id,
																		'FIRSTNAME' =>$line->firstname,
																		'LASTNAME' =>$line->lastname,
																		'DEPARTMENT' =>$line->department,
																		'MAIL' =>$line->MAIL,
																		'SEQUENCE' =>$i+1 ,
																		));
		}
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "remove" )
{
	$rang = $_GET['rang'];
	unset( $_SESSION['m_admin']['entity']['listmodel'][$rang] );
	$_SESSION['m_admin']['entity']['listmodel'] = array_values($_SESSION['m_admin']['entity']['listmodel']);
	for($i=0; $i < count($_SESSION['m_admin']['entity']['listmodel']); $i++)
	{
		$_SESSION['m_admin']['entity']['listmodel'][$i]['SEQUENCE'] = $i+1;
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "up" )
{
	$rang = $_GET['rang'];
	$temp = $_SESSION['m_admin']['entity']['listmodel'][$rang-1];
	$_SESSION['m_admin']['entity']['listmodel'][$rang-1] = $_SESSION['m_admin']['entity']['listmodel'][$rang];
	$_SESSION['m_admin']['entity']['listmodel'][$rang] = $temp;

	for($i=0; $i < count($_SESSION['m_admin']['entity']['listmodel']); $i++)
	{
		$_SESSION['m_admin']['entity']['listmodel'][$i]['SEQUENCE'] = $i+1;
	}
}

else if(isset($_GET['action']) && $_GET['action'] == "down" )
{
	$rang = $_GET['rang'];
	$temp = $_SESSION['m_admin']['entity']['listmodel'][$rang+1];
	$_SESSION['m_admin']['entity']['listmodel'][$rang+1] = $_SESSION['m_admin']['entity']['listmodel'][$rang];
	$_SESSION['m_admin']['entity']['listmodel'][$rang] = $temp;

	for($i=0; $i < count($_SESSION['m_admin']['entity']['listmodel']); $i++)
	{
		$_SESSION['m_admin']['entity']['listmodel'][$i]['SEQUENCE'] = $i+1;
	}
}
?>
<body>
	<?php
		$link = "popup_listmodel_creation.php";
		?>
		<br/>
		<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
			  <tr height="30">
			  <td width="210" align="center" style="font-weight:bold ">
				  <div align="right"><?php echo _SORT_BY.' '._USER;?> : &nbsp;</div></td>
				<td width="688" align="center" style="font-weight:bold "><div align="left">
					<a class="alphabet" href="<?php echo $link;?>?what=A">A</a>
					<a class="alphabet" href="<?php echo $link;?>?what=B">B</a>
					<a class="alphabet" href="<?php echo $link;?>?what=C">C</a>
					<a class="alphabet" href="<?php echo $link;?>?what=D">D</a>
					<a class="alphabet" href="<?php echo $link;?>?what=E">E</a>
					<a class="alphabet" href="<?php echo $link;?>?what=F">F</a>
					<a class="alphabet" href="<?php echo $link;?>?what=G">G</a>
					<a class="alphabet" href="<?php echo $link;?>?what=H">H</a>
					<a class="alphabet" href="<?php echo $link;?>?what=I">I</a>
					<a class="alphabet" href="<?php echo $link;?>?what=J">J</a>
					<a class="alphabet" href="<?php echo $link;?>?what=K">K</a>
					<a class="alphabet" href="<?php echo $link;?>?what=L">L</a>
					<a class="alphabet" href="<?php echo $link;?>?what=M">M</a>
					<a class="alphabet" href="<?php echo $link;?>?what=N">N</a>
					<a class="alphabet" href="<?php echo $link;?>?what=O">O</a>
					<a class="alphabet" href="<?php echo $link;?>?what=P">P</a>
					<a class="alphabet" href="<?php echo $link;?>?what=Q">Q</a>
					<a class="alphabet" href="<?php echo $link;?>?what=R">R</a>
					<a class="alphabet" href="<?php echo $link;?>?what=S">S</a>
					<a class="alphabet" href="<?php echo $link;?>?what=T">T</a>
					<a class="alphabet" href="<?php echo $link;?>?what=U">U</a>
					<a class="alphabet" href="<?php echo $link;?>?what=V">V</a>
					<a class="alphabet" href="<?php echo $link;?>?what=W">W</a>
					<a class="alphabet"  href="<?php echo $link;?>?what=X">X</a>
					<a class="alphabet" href="<?php echo $link;?>?what=Y">Y</a>
					<a class="alphabet" href="<?php echo $link;?>?what=Z">Z</a>
					- <a class="alphabet" href="<?php echo $link; ?>?what=all"><?php echo _ALL_USERS; ?></a></div>
				</td>
			</tr>
		</table>
		<!--<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr height="30">
            <td width="210" align="center" style="font-weight:bold ">
			  <div align="right"><?php echo _SORT_BY.' '._DEPARTMENT;?> : &nbsp;</div></td>
            <td width="698" align="center" style="font-weight:bold "><div align="left">
			<a class="alphabet" href="<?php echo $link;?>?what=A&service=true">A</a>
			<a class="alphabet" href="<?php echo $link;?>?what=B&service=true">B</a>
			<a class="alphabet" href="<?php echo $link;?>?what=C&service=true">C</a>
			<a class="alphabet" href="<?php echo $link;?>?what=D&service=true">D</a>
			<a class="alphabet" href="<?php echo $link;?>?what=E&service=true">E</a>
			<a class="alphabet" href="<?php echo $link;?>?what=F&service=true">F</a>
			<a class="alphabet" href="<?php echo $link;?>?what=G&service=true">G</a>
			<a class="alphabet" href="<?php echo $link;?>?what=H&service=true">H</a>
			<a class="alphabet" href="<?php echo $link;?>?what=I&service=true">I</a>
			<a class="alphabet" href="<?php echo $link;?>?what=J&service=true">J</a>
			<a class="alphabet" href="<?php echo $link;?>?what=K&service=true">K</a>
			<a class="alphabet" href="<?php echo $link;?>?what=L&service=true">L</a>
			<a class="alphabet" href="<?php echo $link;?>?what=M&service=true">M</a>
			<a class="alphabet" href="<?php echo $link;?>?what=N&service=true">N</a>
			<a class="alphabet" href="<?php echo $link;?>?what=O&service=true">O</a>
			<a class="alphabet" href="<?php echo $link;?>?what=P&service=true">P</a>
			<a class="alphabet" href="<?php echo $link;?>?what=Q&service=true">Q</a>
			<a class="alphabet" href="<?php echo $link;?>?what=R&service=true">R</a>
			<a class="alphabet" href="<?php echo $link;?>?what=S&service=true">S</a>
			<a class="alphabet" href="<?php echo $link;?>?what=T&service=true">T</a>
			<a class="alphabet" href="<?php echo $link;?>?what=U&service=true">U</a>
			<a class="alphabet" href="<?php echo $link;?>?what=V&service=true">V</a>
			<a class="alphabet" href="<?php echo $link;?>?what=W&service=true">W</a>
			 <a class="alphabet"  href="<?php echo $link;?>?what=X&service=true">X</a>
			 <a class="alphabet" href="<?php echo $link;?>?what=Y&service=true">Y</a>
		    <a class="alphabet" href="<?php echo $link;?>?what=Z&service=true">Z</a> - <a class="alphabet" href="<?php echo $link; ?>?what=all"><?php echo _ALL_ENTITIES ; ?></a> </div></td>
          </tr>
        </table>-->
		<br/>
		<br/>
		<?php
		if(isset($_GET['what']) && !empty($_GET['what']))
		{ ?>
			<h2 class="tit"><?php echo _USERS_LIST;?></h2>

			<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
			<thead>
				<tr>
					<th><?php echo _ID;?></th>
					<th><?php echo _LASTNAME;?> </th>
					<th><?php echo _FIRSTNAME;?></th>
					<th><?php echo _DEPARTMENT;?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<?php
			$color = ' class="col"';
			for($j=0; $j < count($users); $j++)
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
			<tr <?php echo $color; ?>>
				<td><?php echo $users[$j]['ID'];?></td>
				<td><?php echo $users[$j]['NAME']; ?></td>
				<td><?php echo $users[$j]['FIRSTNAME']; ?></td>
				<td><?php echo $users[$j]['DEP'];?></td>
				<?php
				if($_GET['service'] ==  "true")
				{
					?>
					<td class="action"><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&service=true&action=add&id=<?php echo $users[$j]['ID'];?>" class="change"><?php echo _ADD;?></a></td>
					<?php
				}
				else
				{
					?>
					<td class="action"><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&action=add&id=<?php echo $users[$j]['ID'];?>" class="change"><?php echo _ADD;?></a></td>
					<?php
				}
				?>
			</tr>
			<?php
			}
			?>
		</table>
		<br/>
		<br/>
		<hr align="center" color="#6633CC" size="5" width="60%">
		<br/>
		<h2 class="tit"><?php echo _DIFFUSION_LIST;?></h2>
		<?php
		if(count($_SESSION['m_admin']['entity']['listmodel']) > 0)
		{
			?>
			<h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
			<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
				<thead>
					<tr>
						<th><?php echo _LASTNAME;?> </th>
						<th><?php echo _FIRSTNAME;?></th>
						<th><?php echo _DEPARTMENT;?></th>
						<th colspan="2">&nbsp;</th>
					</tr>
				</thead>
			 <tr>
			 	<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['LASTNAME'];?></td>
				<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['FIRSTNAME'];?></td>
				<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['DEPARTMENT']; ?></td>
				<td class="action"><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&action=remove&rang=0" class="delete"><?php echo _DELETE;?></a></td>
				<td class="action"><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&action=down&rang=0" class="down"><?php echo _DOWN;?></a></td>
		 </tr>
	</table>
	<br/><?php
	if(count($_SESSION['m_admin']['entity']['listmodel']) > 1)
	{
		?>
		<h2 class="sstit"><?php echo _TO_CC;?></h2>
		<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
            <thead>
                <tr>
                    <th><?php echo _LASTNAME;?></th>
                    <th><?php echo _FIRSTNAME;?></th>
                    <th><?php echo _DEPARTMENT;?></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
            </thead>
			<?php
			$color = ' class="col"';
			for($i=1;$i<count($_SESSION['m_admin']['entity']['listmodel']);$i++)
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
				<tr <?php echo $color; ?>>
					<td><?php echo  $_SESSION['m_admin']['entity']['listmodel'][$i]['LASTNAME'];?></td>
					<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$i]['FIRSTNAME'];?></td>
					<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$i]['DEPARTMENT']; ?></td>
					<td class="action"><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&action=remove&rang=<?php echo $i;?>" class="delete"><?php echo _DELETE;?></a></td>
					<td class="action"><?php if( $i+1 < count($_SESSION['m_admin']['entity']['listmodel'])) { ?><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&action=down&rang=<?php echo $i;?>" class="down"><?php echo _DOWN;?></a><?php } else { echo "&nbsp;"; }?></td>
					<td class="action"><a href="popup_listmodel_creation.php?what=<?php echo $what;?>&action=up&rang=<?php echo $i;?>" class="up"><?php echo _UP;?></a></td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
		}
		?>
	<br/>
	<?php
	}
	else
	{
		?>
		<h2 class="sstit"><?php echo _NO_LINKED_DIFF_LIST;?></h2>
		<?php
	}
	?>
	<br/>
	<form name="pop_diff" method="post" >
        <div align="center">
            <input align="middle" name="valid" type="submit" value="<?php echo _VALIDATE ;?>" class="button"/>
            <input align="middle" type="button" value="<?php echo _CANCEL;?>"  onclick="self.close();" class="button"/>
        </div>
	</form>
	<?php
}
else
{
	?>
 	<br/>
  	<br/>
  	<br/>
  	<br/>
   	<h2 class="tit"><?php echo _MANAGE_MODEL_LIST_TITLE;?></h2>
  	<table width="79%" border="0">
        <tr>
          <td><p align="center"><img src="<?php echo $_SESSION['config']['img'];?>/separateur_1.jpg" width="800" height="1" alt="" /><br/><?php echo _WELCOME_MODEL_LIST_TITLE;?>.<br/><br/>
              <?php echo _MODEL_LIST_EXPLANATION1;?>.</p>
            <p align="center"><?php echo _ADD_USER_TO_LIST_EXPLANATION.', '._CLICK_ON;?> : <img src="<?php echo $_SESSION['config']['img'];?>/picto_change.gif" width="21" height="21" alt="" />.</p>
            <p align="center"><?php echo _REMOVE_USER_FROM_LIST_EXPLANATION.', '._CLICK_ON;?> : <img src="<?php echo $_SESSION['config']['img'];?>/picto_delete.gif" width="19" height="19" alt="" />.</p>
            <p align="center"><?php echo _TO_MODIFY_LIST_ORDER_EXPLANATION;?> <img src="<?php echo $_SESSION['config']['img'];?>/arrow_down.gif" width="16" height="16" alt="" /> <?php echo _AND;?> <img src="<?php echo $_SESSION['config']['img'];?>/arrow_up.gif" width="16" height="16" alt=""/>. <br/><br/><img src="<?php echo $_SESSION['config']['img'];?>/separateur_1.jpg" width="800" height="1" alt=""/></p>
            </td>
        </tr>
    </table>
	<input align="middle" type="button" value="<?php echo _CANCEL;?>" class="button"  onclick="self.close();"/>
  	<?php
	}
	?>
<br/>
</body>
</html>
