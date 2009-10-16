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
* @brief   Pop up to redirect mail to a specific user when the default user is missing
*
* Opens a modal box, displays the form to redirect, checks the form and manages it.
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @author  Loic Vinet
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

$func = new functions();
$users = array();
$what = "A";
$where = "";

// Skip missing user from list
$db_abs = new dbquery();
$db_abs->connect();
$db_abs->query("select distinct user_abs from ".$_SESSION['tablename']['bask_users_abs']);
$j=0;
while($line = $db_abs->fetch_object())
{
	$user_abs[$j] = $line->user_abs;
	$j++;
}

if(isset($_GET['what']) && !empty($_GET['what']))
{
	$this_user = $_SESSION['user']['UserId'];
	if ($_GET['specified_user'] <> "")
	{
		$this_user = $_GET['specified_user'];
	}

	$what = addslashes($func->wash($_GET['what'], "no", "", "no"));
	if($_GET['service'] ==  "true")
	{
		if($what == "all")
		{
			$where = " where user_id <> '".$this_user."' order by department, lastname ";
		}
		else
		{
			if($_SESSION('config']['databasetype'] == 'POSTGRESQL')
			{
				$where = "where user_id <> '".$this_user."' and department ilike '".strtolower($what)."%' or department ilike '".strtoupper($what)."%' order by department, lastname  ";
			}
			else
			{
				$where = "where user_id <> '".$this_user."' and department like '".strtolower($what)."%' or department like '".strtoupper($what)."%' order by department, lastname  ";
			}
		}
	}
	else
	{
		if($what == "all")
		{
			$where = " where user_id <> '".$this_user."' order by  lastname ";
		}
		else
		{
			if($_SESSION('config']['databasetype'] == 'POSTGRESQL')
			{
				$where = "where user_id <> '".$this_user."' and lastname ilike '".strtolower($what)."%' or lastname ilike '".strtoupper($what)."%' order by  lastname";
			}
			else
			{
				$where = "where user_id <> '".$this_user."' and lastname like '".strtolower($what)."%' or lastname like '".strtoupper($what)."%' order by  lastname";
			}
		}
	}
}
$db = new dbquery();
$db->connect();
$db->query("select user_id, firstname, lastname, department, mail from ".$_SESSION['tablename']['users']." ".$where);

$i=1;
while($line = $db->fetch_object())
{
	$users[$i] = array("ID" => $line->user_id, "PRENOM" => $line->firstname, "NOM" => $line->lastname, "DEP" => $line->department, "MAIL" => $line->mail);
	$i++;
}

$new_user = "";
if($_GET['action'] == "choose" && !empty($_GET['ind']))
{
	$new_user = $_GET['ind'];
	$id_new_user = $_GET['ind2'];
}

if($_POST['redirect_abs'])
{
	$this_user = $_SESSION['user']['UserId'];
	if ($_POST['specified_user'] <> "")
	{
		$this_user = $_POST['specified_user'];
	}
	$db->query("INSERT INTO ".$_SESSION['tablename']['bask_users_abs']." ( user_abs , new_user ) VALUES ('".$this_user."', '".$_REQUEST['user']."');");

	//Manage Abs when user is already missing
	$db->query("UPDATE ".$_SESSION['tablename']['bask_users_abs']." SET new_user =  '".$_REQUEST['user']."' WHERE new_user ='".$this_user."'");
	if($_SESSION['history']['userabs'] == "true")
	{
		require_once($_SESSION['pathtocoreclass']."class_history.php");
		$history = new history();
		$history->add($_SESSION['tablename']['bask_users_abs'],$this_user,"ABS",_REDIRECT_MAIL_OF." ".$this_user." "._TOWARD." ".$_REQUEST['user'].".", $_SESSION['config']['databasetype'], 'basket');
	}
	?>
	 <script language="javascript"> window.opener.location.reload(); self.close();
	 <?php if($this_user == $_SESSION['user']['UserId'])
	 {?>
	 window.opener.top.location.href = '<?php echo $_SESSION['config']['businessappurl'];?>logout.php?coreurl=<?php echo $_SESSION['config']['coreurl'];?>';
     <?php } ?>
     </script>
	<?php
}
$time = $core_tools->get_session_time_expire();
$core_tools->load_html();
$core_tools->load_header(_REDIRECTION);
?>
<body onLoad="setTimeout(window.close, <?php echo $time;?>*60*1000);">
<?php
	$link = $_SESSION['urltomodules']."basket/redirect_abs.php";
	if ($_GET['specified_user'] <> "")
	{
		$spec_user = "&specified_user=".$_GET['specified_user'];
	}
	?>
	<br/>
	<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
		<tr height="30">
			<td width="210" align="center" style="font-weight:bold ">
				<div align="right"><?php echo _SORT_BY.' '._USER;?> : &nbsp;</div>
			</td>
			<td width="688" align="center" style="font-weight:bold "><div align="left">
				<a class="alphabet" href="<?php echo $link;?>?what=A<?php echo $spec_user?>">A</a>
				<a class="alphabet" href="<?php echo $link;?>?what=B<?php echo $spec_user?>">B</a>
				<a class="alphabet" href="<?php echo $link;?>?what=C<?php echo $spec_user?>">C</a>
				<a class="alphabet" href="<?php echo $link;?>?what=D<?php echo $spec_user?>">D</a>
				<a class="alphabet" href="<?php echo $link;?>?what=E<?php echo $spec_user?>">E</a>
				<a class="alphabet" href="<?php echo $link;?>?what=F<?php echo $spec_user?>">F</a>
				<a class="alphabet" href="<?php echo $link;?>?what=G<?php echo $spec_user?>">G</a>
				<a class="alphabet" href="<?php echo $link;?>?what=H<?php echo $spec_user?>">H</a>
				<a class="alphabet" href="<?php echo $link;?>?what=I<?php echo $spec_user?>">I</a>
				<a class="alphabet" href="<?php echo $link;?>?what=J<?php echo $spec_user?>">J</a>
				<a class="alphabet" href="<?php echo $link;?>?what=K<?php echo $spec_user?>">K</a>
				<a class="alphabet" href="<?php echo $link;?>?what=L<?php echo $spec_user?>">L</a>
				<a class="alphabet" href="<?php echo $link;?>?what=M<?php echo $spec_user?>">M</a>
				<a class="alphabet" href="<?php echo $link;?>?what=N<?php echo $spec_user?>">N</a>
				<a class="alphabet" href="<?php echo $link;?>?what=O<?php echo $spec_user?>">O</a>
				<a class="alphabet" href="<?php echo $link;?>?what=P<?php echo $spec_user?>">P</a>
				<a class="alphabet" href="<?php echo $link;?>?what=Q<?php echo $spec_user?>">Q</a>
				<a class="alphabet" href="<?php echo $link;?>?what=R<?php echo $spec_user?>">R</a>
				<a class="alphabet" href="<?php echo $link;?>?what=S<?php echo $spec_user?>">S</a>
				<a class="alphabet" href="<?php echo $link;?>?what=T<?php echo $spec_user?>">T</a>
				<a class="alphabet" href="<?php echo $link;?>?what=U<?php echo $spec_user?>">U</a>
				<a class="alphabet" href="<?php echo $link;?>?what=V<?php echo $spec_user?>">V</a>
				<a class="alphabet" href="<?php echo $link;?>?what=W<?php echo $spec_user?>">W</a>
				<a class="alphabet"  href="<?php echo $link;?>?what=X<?php echo $spec_user?>">X</a>
				<a class="alphabet" href="<?php echo $link;?>?what=Y<?php echo $spec_user?>">Y</a>
				<a class="alphabet" href="<?php echo $link;?>?what=Z<?php echo $spec_user?>">Z</a>
				- <a class="alphabet" href="<?php echo $link; ?>?what=all<?php echo $spec_user?>"><?php echo _ALL_USERS; ?></a></div>
			</td>
		</tr>
	</table>
	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr height="30">
            <td width="210" align="center" style="font-weight:bold ">
				<div align="right"><?php echo _SORT_BY.' '._DEPARTMENT;?> : &nbsp;</div>
			</td>
            <td width="698" align="center" style="font-weight:bold ">
				<div align="left">
					<a class="alphabet" href="<?php echo $link;?>?what=A&service=true<?php echo $spec_user?>">A</a>
					<a class="alphabet" href="<?php echo $link;?>?what=B&service=true<?php echo $spec_user?>">B</a>
					<a class="alphabet" href="<?php echo $link;?>?what=C&service=true<?php echo $spec_user?>">C</a>
					<a class="alphabet" href="<?php echo $link;?>?what=D&service=true<?php echo $spec_user?>">D</a>
					<a class="alphabet" href="<?php echo $link;?>?what=E&service=true<?php echo $spec_user?>">E</a>
					<a class="alphabet" href="<?php echo $link;?>?what=F&service=true<?php echo $spec_user?>">F</a>
					<a class="alphabet" href="<?php echo $link;?>?what=G&service=true<?php echo $spec_user?>">G</a>
					<a class="alphabet" href="<?php echo $link;?>?what=H&service=true<?php echo $spec_user?>">H</a>
					<a class="alphabet" href="<?php echo $link;?>?what=I&service=true<?php echo $spec_user?>">I</a>
					<a class="alphabet" href="<?php echo $link;?>?what=J&service=true<?php echo $spec_user?>">J</a>
					<a class="alphabet" href="<?php echo $link;?>?what=K&service=true<?php echo $spec_user?>">K</a>
					<a class="alphabet" href="<?php echo $link;?>?what=L&service=true<?php echo $spec_user?>">L</a>
					<a class="alphabet" href="<?php echo $link;?>?what=M&service=true<?php echo $spec_user?>">M</a>
					<a class="alphabet" href="<?php echo $link;?>?what=N&service=true<?php echo $spec_user?>">N</a>
					<a class="alphabet" href="<?php echo $link;?>?what=O&service=true<?php echo $spec_user?>">O</a>
					<a class="alphabet" href="<?php echo $link;?>?what=P&service=true<?php echo $spec_user?>">P</a>
					<a class="alphabet" href="<?php echo $link;?>?what=Q&service=true<?php echo $spec_user?>">Q</a>
					<a class="alphabet" href="<?php echo $link;?>?what=R&service=true<?php echo $spec_user?>">R</a>
					<a class="alphabet" href="<?php echo $link;?>?what=S&service=true<?php echo $spec_user?>">S</a>
					<a class="alphabet" href="<?php echo $link;?>?what=T&service=true<?php echo $spec_user?>">T</a>
					<a class="alphabet" href="<?php echo $link;?>?what=U&service=true<?php echo $spec_user?>">U</a>
					<a class="alphabet" href="<?php echo $link;?>?what=V&service=true<?php echo $spec_user?>">V</a>
					<a class="alphabet" href="<?php echo $link;?>?what=W&service=true<?php echo $spec_user?>">W</a>
					<a class="alphabet"  href="<?php echo $link;?>?what=X&service=true<?php echo $spec_user?>">X</a>
					<a class="alphabet" href="<?php echo $link;?>?what=Y&service=true<?php echo $spec_user?>">Y</a>
				    <a class="alphabet" href="<?php echo $link;?>?what=Z&service=true<?php echo $spec_user?>">Z</a> -
					<a class="alphabet" href="<?php echo $link; ?>?what=all"<?php echo $spec_user?>><?php echo _ALL_ENTITIES ; ?></a>
				</div>
			</td>
        </tr>
    </table>
	<br/>
	<br/>

	<h2 class="tit"><?php echo _USERS_LIST;?></h2>
	<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
		<thead>
			<tr>
				<th><?php echo _ID;?></th>
				<th ><?php echo _LASTNAME;?> </th>
				<th ><?php echo _FIRSTNAME;?></th>
				<th><?php echo _DEPARTMENT;?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$color = ' class="col"';
		for($j=1; $j <= count($users); $j++)
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
			<tr <?php echo $color; ?> >
				<td><?php echo $users[$j]['ID'];?></td>
				<td><?php echo $users[$j]['NOM']; ?></td>
				<td><?php echo $users[$j]['PRENOM']; ?></td>
				<td><?php echo $users[$j]['DEP'];?></td>
				<td>
                <?php
				$is_abs=false;
				for ($n=0; $n<=count($user_abs); $n++)
				{
					if ($users[$j]['ID'] == $user_abs[$n])
					{
						$is_abs = true;
					}
				}
				if ($is_abs==true)
				{
					echo _MISSING;
				}
				else
				{
				?>
					<a href="<?php echo $_SESSION['urltomodules']."basket/";?>redirect_abs.php?what=<?php echo $what;?>&action=choose&ind=<?php echo $j; ?>&ind2=<?php echo  $users[$j]['ID']; ?>&specified_user=<?php echo $_GET['specified_user']; ?>" class="change"><?php echo _ADD;?></a></td>
			<?php  } ?>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
	<br/>
	<br/>
	<h2 class="tit" ><?php echo _REDIRECT_TO;?> : </h2>
	<br/>
	<form name="redirect_form" method="post" action="<?php echo $_SESSION['urltomodules'].'basket/redirect_abs.php';?>">
		<?php if(empty($new_user) )
		{
			?>
			<div align="center" ><?php echo _CHOOSE_PERSON_TO_REDIRECT;?>.<br>
			<?php echo _CLICK_ON_THE_LINE_OR_ICON ;?> <img src="<?php echo $_SESSION['config']['img'];?>/picto_change.gif"  /> <?php echo _TO_SELECT_USER;?>.
			</div><br/>
			<div align="center"><input name="cancel" type="button" value="<?php echo _CANCEL;?>" onClick="self.close();" align="middle" class="button" /></div>
			<?php
		}
		else
		{
			?>
			<table cellpadding="0" cellspacing="0" border="0" class="listing spec" >
				<thead>
					<tr>
						<th><?php echo _ID;?></th>
						<th ><?php echo _LASTNAME;?> </th>
						<th ><?php echo _FIRSTNAME;?></th>
						<th><?php echo _DEPARTMENT;?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo $users[$new_user]['ID'];?></td>
						<td><?php echo $users[$new_user]['NOM'];?></td>
						<td><?php echo $users[$new_user]['PRENOM'];?></td>
						<td><?php echo $users[$new_user]['DEP'];?></td>
					</tr>
				</tbody>
			</table>
			<br/>
			<input name="user" type="hidden" value="<?php echo $users[$new_user]['ID']; ?>">
			<input name="specified_user" type="hidden" value="<?php echo $_GET['specified_user']; ?>" />
			<div align="center"><input name="redirect_abs" type="submit" value="<?php echo _REDIRECT;?>" align="middle" class="button" />
				<input name="cancel" type="button" value="<?php echo _CANCEL;?>" onClick="self.close();" align="middle" class="button" />
			</div>
			<?php
		}
		?>
	</form>
</body>
</html>
