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
* @brief   Pop up : Create or modify a diffusion list
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
	array_push($users, array("Id" => $line->user_id, "FirstName" => $line->firstname, "Name" => $line->lastname, "DepId" => $line->department, "MAIL" => $line->mail));
}
$id = "";
$desc ="";
if(isset($_GET['action']) && $_GET['action'] == "add" )
{
	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$id = $_GET['id'];
		$find = false;
		for($i=0; $i < count($_SESSION['diff']); $i++)
		{
			if($id == $_SESSION['diff'][$i]['UserID'])
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
			array_push($_SESSION['diff'], array('UserID' => $id,
																		'FirstName' =>$line->firstname,
																		'LastName' =>$line->lastname,
																		'Service' =>$line->department,
																		'Mail' =>$line->mail,
																		'sequence' =>$i+1 ,
																		));
		}
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "remove")
{
	$rang = $_GET['rang'];
	unset( $_SESSION['diff'][$rang] );
	$_SESSION['diff'] = array_values($_SESSION['diff']);
	for($i=0; $i < count($_SESSION['diff']); $i++)
	{
		$_SESSION['diff'][$i]['sequence'] = $i+1;
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "up")
{
	$rang = $_GET['rang'];
	$temp = $_SESSION['diff'][$rang-1];
	$_SESSION['diff'][$rang-1] = $_SESSION['diff'][$rang];
	$_SESSION['diff'][$rang] = $temp;

	for($i=0; $i < count($_SESSION['diff']); $i++)
	{
		$_SESSION['diff'][$i]['sequence'] = $i+1;
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "down")
{
	$rang = $_GET['rang'];
	$temp = $_SESSION['diff'][$rang+1];
	$_SESSION['diff'][$rang+1] = $_SESSION['diff'][$rang];
	$_SESSION['diff'][$rang] = $temp;

	for($i=0; $i < count($_SESSION['diff']); $i++)
	{
		$_SESSION['diff'][$i]['sequence'] = $i+1;
	}
}
?>
<body>
		<?php
		$link = "popup_listmodel_action.php";
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
					<th><?php echo _LastName;?> </th>
					<th><?php echo _FirstName;?></th>
					<th><?php echo _Service;?></th>
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
				<td><?php echo $users[$j]['Id'];?></td>
				<td><?php echo $users[$j]['Name']; ?></td>
				<td><?php echo $users[$j]['FirstName']; ?></td>
				<td><?php echo $users[$j]['DepId'];?></td>
				<?php
				if($_GET['service'] ==  "true")
				{
					?>
					<td class="action"><a href="popup_listmodel_action.php?what=<?php echo $what;?>&service=true&action=add&id=<?php echo $users[$j]['Id'];?>" class="change"><?php echo _ADD;?></a></td>
					<?php
				}
				else
				{
					?>
					<td class="action"><a href="popup_listmodel_action.php?what=<?php echo $what;?>&action=add&id=<?php echo $users[$j]['Id'];?>" class="change"><?php echo _ADD;?></a></td>
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
		if(count($_SESSION['diff']) > 0)
		{
			?>
			<h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
			<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
				<thead>
					<tr>
						<th><?php echo _LastName;?> </th>
						<th><?php echo _FirstName;?></th>
						<th><?php echo _Service;?></th>
						<th colspan="2">&nbsp;</th>
					</tr>
				</thead>
			 <tr>
			 	<td><?php echo $_SESSION['diff'][0]['LastName'];?></td>
				<td><?php echo $_SESSION['diff'][0]['FirstName'];?></td>
				<td><?php echo $_SESSION['diff'][0]['Service']; ?></td>
				<td class="action"><a href="popup_listmodel_action.php?what=<?php echo $what;?>&action=remove&rang=0" class="delete"><?php echo _DELETE;?></a></td>
				<td class="action"><a href="popup_listmodel_action.php?what=<?php echo $what;?>&action=down&rang=0" class="down"><?php echo _DOWN;?></a></td>
		 </tr>
	</table>
	<br/><?php
	if(count($_SESSION['diff']) > 1)
	{
		?>
		<h2 class="sstit"><?php echo _TO_CC;?></h2>
		<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
            <thead>
                <tr>
                    <th><?php echo _LastName;?></th>
                    <th><?php echo _FirstName;?></th>
                    <th><?php echo _Service;?></th>
                    <th colspan="2">&nbsp;</th>
                </tr>
            </thead>
			<?php
			$color = ' class="col"';
			for($i=1;$i<count($_SESSION['diff']);$i++)
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
					<td><?php echo  $_SESSION['diff'][$i]['LastName'];?></td>
					<td><?php echo $_SESSION['diff'][$i]['FirstName'];?></td>
					<td><?php echo $_SESSION['diff'][$i]['Service']; ?></td>
					<td class="action"><a href="popup_listmodel_action.php?what=<?php echo $what;?>&action=remove&rang=<?php echo $i;?>" class="delete"><?php echo _DELETE;?></a></td>
					<td class="action"><?php if( $i+1 < count($_SESSION['diff'])) { ?><a href="popup_listmodel_action.php?what=<?php echo $what;?>&action=down&rang=<?php echo $i;?>" class="down"><?php echo _DOWN;?></a><?php } else { echo "&nbsp;"; }?></td>
					<td class="action"><a href="popup_listmodel_action.php?what=<?php echo $what;?>&action=up&rang=<?php echo $i;?>" class="up"><?php echo _UP;?></a></td>
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
	<form name="pop_diff" method="post">
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
