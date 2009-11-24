<?php /**
* File : manage_listinstance.php
*
* Pop up used to create and modify diffusion lists instances
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/


function cmp_entity($a, $b)
{
   	return strcmp($a["entity_label"], $b["entity_label"]);
}
function cmp_users($a, $b)
{
   	return strcmp($a["lastname"], $b["lastname"]);
}

$core_tools = new core_tools();
$core_tools->load_lang();

$func = new functions();

if(isset($_POST['what_users']) && !empty($_POST['what_users']) )
{
	$_GET['what_users'] = $_POST['what_users'];
}
if(isset($_POST['what_services']) && !empty($_POST['what_services']) )
{
	$_GET['what_services'] = $_POST['what_services'];
}

$users = array();
$entities = array();
$where_users = "";
$where_entities = "";
$orderby_users = "";
$orderby_entities = "";
$where_entities_users = '';
$what = "";
$only_cc = false;
if(isset($_REQUEST['only_cc']))
{
	$only_cc = true;
}
if(isset($_GET['what_users']) && !empty($_GET['what_users']) )
{
	$what_users = $func->protect_string_db($func->wash($_GET['what_users'], "no", "", "no"));

	if($SESSION['config']['databasetype'] == 'POSTGRESQL')
	{
		$where_users .= " and (u.lastname ilike '%".strtolower($what_users)."%'
				or u.lastname ilike '%".strtoupper($what_users)."%'
				or u.firstname ilike '%".strtolower($what_users)."%'
				or u.firstname ilike '%".strtoupper($what_users)."%'
				or u.user_id ilike '%".strtolower($what_users)."%'
				or u.user_id ilike '%".strtoupper($what_users)."%')";
	}
	else
	{
		$where_users .= " and (u.lastname like '%".strtolower($what_users)."%'
				or u.lastname like '%".strtoupper($what_users)."%'
				or u.firstname like '%".strtolower($what_users)."%'
				or u.firstname like '%".strtoupper($what_users)."%'
				or u.user_id like '%".strtolower($what_users)."%'
				or u.user_id like '%".strtoupper($what_users)."%')";
	}
	$orderby_users = " order by u.user_id asc, u.lastname asc, u.firstname asc, e.entity_label asc";

	if($SESSION['config']['databasetype'] == 'POSTGRESQL')
	{
		$where_entities_users .= " and (u.lastname ilike '%".strtolower($what_users)."%'
				or u.lastname ilike '%".strtoupper($what_users)."%'
				or u.firstname ilike '%".strtolower($what_users)."%'
				or u.firstname ilike '%".strtoupper($what_users)."%'
				or u.user_id ilike '%".strtolower($what_users)."%'
				or u.user_id ilike '%".strtoupper($what_users)."%')";
	}
	else
	{
		$where_entities_users .= " and (u.lastname like '%".strtolower($what_users)."%'
				or u.lastname like '%".strtoupper($what_users)."%'
				or u.firstname like '%".strtolower($what_users)."%'
				or u.firstname like '%".strtoupper($what_users)."%'
				or u.user_id like '%".strtolower($what_users)."%'
				or u.user_id like '%".strtoupper($what_users)."%')";
	}
	$orderby_entities = " order by e.entity_label asc";
}
if(isset($_GET['what_services']) && !empty($_GET['what_services']) )
{
	$what_services = addslashes($func->wash($_GET['what_services'], "no", "", "no"));
	if($SESSION['config']['databasetype'] == 'POSTGRESQL')
	{
		$where_users .= " and (e.entity_label ilike '%".strtolower($what_services)."%' or e.entity_id ilike '%".strtoupper($what_services)."%')";
		$where_entities .= " and (e.entity_label ilike '%".strtolower($what_services)."%' or e.entity_id ilike '%".strtolower($what_services)."%' )";
	}
	else
	{
		$where_users .= " and (e.entity_label like '%".strtolower($what_services)."%' or e.entity_id like '%".strtoupper($what_services)."%')";
		$where_entities .= " and (e.entity_label like '%".strtolower($what_services)."%' or e.entity_id like '%".strtolower($what_services)."%' )";
	}
	$orderby_users = " order by e.entity_label asc, u.user_id asc, u.lastname asc, u.firstname asc";
	$orderby_entities = " order by e.entity_label asc";
}

$db = new dbquery();
$db->connect();
$db->query("select u.user_id, u.firstname, u.lastname,e.entity_id,  e.entity_label
FROM ".$_SESSION['tablename']['users']." u, ".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['ent_users_entities']." ue
WHERE u.status <> 'DEL' and u.enabled = 'Y' and  e.entity_id = ue.entity_id and u.user_id = ue.user_id and e.enabled = 'Y' ".$where_users.$orderby_users);

$i=0;
while($line = $db->fetch_object())
{
	array_push($users, array("ID" => $db->show_string($line->user_id), "PRENOM" => $db->show_string($line->firstname), "NOM" => $db->show_string($line->lastname), "DEP_ID" => $db->show_string($line->entity_id),  "DEP" =>$db->show_string($line->entity_label)));
}

if($where_entities_users  == '')
{
	$db->query("select e.entity_id,  e.entity_label
FROM  ".$_SESSION['tablename']['ent_entities']." e
WHERE e.enabled = 'Y' ".$where_entities.$orderby_entities);
}
else
{
	$db->query("select e.entity_id,  e.entity_label
FROM ".$_SESSION['tablename']['users']." u, ".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['ent_users_entities']." ue
WHERE u.status <> 'DEL' and u.enabled = 'Y' and  e.entity_id = ue.entity_id and u.user_id = ue.user_id and e.enabled = 'Y' ".$where_entities_users.$orderby_users);
}
$i=0;
while($line = $db->fetch_object())
{
	array_push($entities, array("ID" => $db->show_string($line->entity_id), "DEP" =>$db->show_string($line->entity_label)));
}
$origin = $_REQUEST['origin'];
$id = "";
$desc ="";
if(!isset($_SESSION[$origin]['diff_list']['copy']['users']))
{
	$_SESSION[$origin]['diff_list']['copy']['users'] = array();
}
if(!isset($_SESSION[$origin]['diff_list']['copy']['entities']))
{
	$_SESSION[$origin]['diff_list']['copy']['entities'] = array();
}
if(isset($_GET['action']) && $_GET['action'] == "add_entity" )
{

	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$id = $_GET['id'];
		$find = false;
		for($i=0; $i < count($_SESSION[$origin]['diff_list']['copy']['entities']); $i++)
		{
			if($id == $_SESSION[$origin]['diff_list']['copy']['entities'][$i]['entity_id'])
			{
				$find = true;
				break;
			}
		}

		if( $find == false)
		{
			$db->query("SELECT  e.entity_id,  e.entity_label FROM ".$_SESSION['tablename']['ent_entities']." e WHERE   e.entity_id = '".$db->protect_string_db($id)."'");
			$line = $db->fetch_object();
			array_push($_SESSION[$origin]['diff_list']['copy']['entities'], array(  'entity_id' => $db->show_string($id),'entity_label' =>$db->show_string($line->entity_label)));
		}

		usort($_SESSION[$origin]['diff_list']['copy']['entities'], "cmp_entity");
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "add_user" )
{

	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$id = $_GET['id'];
		$find = false;

		if($id == $_SESSION[$origin]['diff_list']['dest']['user_id'])
		{
			$find = true;
		}
		elseif( empty( $_SESSION[$origin]['diff_list']['dest']['user_id']) || !isset( $_SESSION[$origin]['diff_list']['dest']['user_id']))
		{
			$db->query("SELECT u.firstname, u.lastname, u.department, e.entity_id,  e.entity_label FROM ".$_SESSION['tablename']['users']." u,  ".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['ent_users_entities']." ue WHERE  u.user_id='".$db->protect_string_db($id)."' and  e.entity_id = ue.entity_id and u.user_id = ue.user_id");
			$line = $db->fetch_object();
			$_SESSION[$origin]['diff_list']['dest']['user_id'] = $db->show_string($id);
			$_SESSION[$origin]['diff_list']['dest']['firstname'] = $db->show_string($line->firstname);
			$_SESSION[$origin]['diff_list']['dest']['lastname'] = $db->show_string($line->lastname);
			$_SESSION[$origin]['diff_list']['dest']['entity_id'] = $db->show_string($line->entity_id);
			$_SESSION[$origin]['diff_list']['dest']['entity_label'] = $db->show_string($line->entity_label);
		}
		else
		{
			for($i=0; $i < count($_SESSION[$origin]['diff_list']['copy']['users']); $i++)
			{
				if($id == $_SESSION[$origin]['diff_list']['copy']['users'][$i]['user_id'])
				{
					$find = true;
					break;
				}
			}

			if( $find == false)
			{
				$db->query("SELECT u.firstname, u.lastname, u.department, e.entity_id,  e.entity_label FROM ".$_SESSION['tablename']['users']." u,  ".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['ent_users_entities']." ue WHERE  u.user_id='".$db->protect_string_db($id)."' and  e.entity_id = ue.entity_id and u.user_id = ue.user_id");
				$line = $db->fetch_object();
				array_push($_SESSION[$origin]['diff_list']['copy']['users'], array(  'user_id' => $db->show_string($id),
															'firstname' =>$db->show_string($line->firstname),
															'lastname' =>$db->show_string($line->lastname),
															'entity_id' =>$db->show_string($line->entity_id),
															'entity_label' =>$db->show_string($line->entity_label),
												));
			}
		}
		usort($_SESSION[$origin]['diff_list']['copy']['users'], "cmp_users");
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "remove_dest" )
{
	unset( $_SESSION[$origin]['diff_list']['dest'] );
}
else if(isset($_GET['action']) && $_GET['action'] == "remove_entity" )
{
	$rank = $_GET['rank'];
	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$id = $_GET['id'];
		if($_SESSION[$origin]['diff_list']['copy']['entities'][$rank]['entity_id'] == $id)
		{
			unset($_SESSION[$origin]['diff_list']['copy']['entities'][$rank] );
			$_SESSION[$origin]['diff_list']['copy']['entities'] = array_values($_SESSION[$origin]['diff_list']['copy']['entities']);
		}
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "remove_user" )
{
	$rank = $_GET['rank'];
	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$id = $_GET['id'];
		if($_SESSION[$origin]['diff_list']['copy']['users'][$rank]['user_id'] == $id)
		{
			unset($_SESSION[$origin]['diff_list']['copy']['users'][$rank] );
			$_SESSION[$origin]['diff_list']['copy']['users'] = array_values($_SESSION[$origin]['diff_list']['copy']['users']);
		}
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "dest_to_copy" )
{
	if(isset($_SESSION[$origin]['diff_list']['dest']['user_id']) && !empty($_SESSION[$origin]['diff_list']['dest']['user_id']))
	{
		array_push($_SESSION[$origin]['diff_list']['copy']['users'], array(  'user_id' => $_SESSION[$origin]['diff_list']['dest']['user_id'],
															'firstname' =>$_SESSION[$origin]['diff_list']['dest']['firstname'],
															'lastname' =>$_SESSION[$origin]['diff_list']['dest']['lastname'],
															'entity_id' =>$_SESSION[$origin]['diff_list']['dest']['entity_id'],
															'entity_label' =>$_SESSION[$origin]['diff_list']['dest']['entity_label'],
												));
		unset( $_SESSION[$origin]['diff_list']['dest'] );
		usort($_SESSION[$origin]['diff_list']['copy']['users'], "cmp_users");
	}
}
else if(isset($_GET['action']) && $_GET['action'] == "copy_to_dest" )
{
	if(isset($_SESSION[$origin]['diff_list']['dest']['user_id']) && !empty($_SESSION[$origin]['diff_list']['dest']['user_id']))
	{
		array_push($_SESSION[$origin]['diff_list']['copy']['users'], array(  'user_id' => $_SESSION[$origin]['diff_list']['dest']['user_id'],
															'firstname' =>$_SESSION[$origin]['diff_list']['dest']['firstname'],
															'lastname' =>$_SESSION[$origin]['diff_list']['dest']['lastname'],
															'entity_id' =>$_SESSION[$origin]['diff_list']['dest']['entity_id'],
															'entity_label' =>$_SESSION[$origin]['diff_list']['dest']['entity_label'],
												));
		unset( $_SESSION[$origin]['diff_list']['dest'] );

	}
	$rank = $_GET['rank'];
	if(isset($_SESSION[$origin]['diff_list']['copy']['users'][$rank]['user_id']) && !empty($_SESSION[$origin]['diff_list']['copy']['users'][$rank]['user_id']))
	{
		$_SESSION[$origin]['diff_list']['dest']['user_id'] = $_SESSION[$origin]['diff_list']['copy']['users'][$rank]['user_id'];
		$_SESSION[$origin]['diff_list']['dest']['firstname'] = $_SESSION[$origin]['diff_list']['copy']['users'][$rank]['firstname'];
		$_SESSION[$origin]['diff_list']['dest']['lastname'] = $_SESSION[$origin]['diff_list']['copy']['users'][$rank]['lastname'];
		$_SESSION[$origin]['diff_list']['dest']['entity_id'] = $_SESSION[$origin]['diff_list']['copy']['users'][$rank]['entity_id'];
		$_SESSION[$origin]['diff_list']['dest']['entity_label'] = $_SESSION[$origin]['diff_list']['copy']['users'][$rank]['entity_label'];
		unset( $_SESSION[$origin]['diff_list']['copy']['users'][$rank]);
		$_SESSION[$origin]['diff_list']['copy']['users'] = array_values($_SESSION[$origin]['diff_list']['copy']['users']);
	}
	usort($_SESSION[$origin]['diff_list']['copy']['users'], "cmp_users");
}

$core_tools->load_html();
$core_tools->load_header(_USER_ENTITIES_TITLE);
$time = $core_tools->get_session_time_expire();
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
{
	$browser_ie = true;
	$display_value = 'block';
}
elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT) )
{
	$browser_ie = true;
	$display_value = 'block';
}
else
{
	$browser_ie = false;
	$display_value = ''.$display_value.'';
}
?>
<body onLoad="setTimeout(window.close, <?php echo $time;?>*60*1000);">
<?php //$db->show_array($_SESSION[$origin]['diff_list']);?>
	<?php $link = $_SESSION['config']['businessappurl']."index.php?display=true&module=entities&page=manage_listinstance&origin=".$origin;
	if($only_cc)
	{
		$link .= '&only_cc';
	}
	//print_r($_SESSION['process']['diff_list'] );
		?>
		<br/></br>
		<div align="center">
		<h2 class="tit"><?php echo _SEARCH_DIFF_LIST ?></h2>
		<form action="#" name="search_diff_list" method="" id="search_diff_list" >
			<input type="hidden" name="display" value="true" />
			<input type="hidden" name="module" value="entities" />
			<input type="hidden" name="page" value="manage_listinstance" />
		<input type="hidden" name="origin" id="origin" value="<?php echo $_REQUEST['origin'];?>" />
		<table cellpadding="2" cellspacing="2" border="0">
			<tr>
				<th>
					<label for="what_users" class="bold"><?php echo _LASTNAME;?> / <?php echo _FIRSTNAME;?> / <?php echo _ID; ?></label>
				</th>
				<th>
					<input name="what_users" id="what_users" type="text" <?php if(isset($_GET["what_users"])) echo "value ='".$_GET["what_users"]."'"; ?> />
				</th>
			</tr>
			<tr>
				<th>
					<label for="what_services" class="bold"><?php echo _DEPARTMENT;?></label>
				</th>
				<th>
					<input name="what_services" id="what_services" type="text" <?php if(isset($_GET["what_services"])) echo "value ='".$_GET["what_services"]."'"; ?>/>
				</th>
			</tr>
		</table>
		</form>
		<script type="text/javascript">repost('<? echo $link;?>',new Array('diff_list'),new Array('what_users','what_services'),'keyup',250);</script>
		<br/></br><br/></br><br/></br>
		</div>
		<?php if((isset($_GET['what_users']) && !empty($_GET['what_users'])) || (isset($_GET['what_services']) && !empty($_GET['what_services'])) || !empty($_SESSION[$origin]['diff_list']['dest']['user_id']	) )
		{ ?>

		<div id="diff_list" align="center">
		<h2 class="tit"><?php echo _DIFFUSION_LIST;?></h2>

		<?php if(isset($_SESSION[$origin]['diff_list']['dest']['user_id']) && !empty($_SESSION[$origin]['diff_list']['dest']['user_id']) && !$only_cc)
		{
			?>
			<h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
			<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
			 <tr >
			 <td><img src="<?php echo $_SESSION['urltomodules'].'entities/img/manage_users_entities_b.gif';?>" alt="<? echo _USER;?>" title="<? echo _USER;?>" /></td>
			 	 <td ><?php echo $_SESSION[$origin]['diff_list']['dest']['lastname'];?></td>
				 <td ><?php echo $_SESSION[$origin]['diff_list']['dest']['firstname'];?></td>
				<td><?php echo $_SESSION[$origin]['diff_list']['dest']['entity_label']; ?></td>
				<td class="action_entities"><a href="<?php echo $link;?>&action=remove_dest" class="delete"><?php echo _DELETE;?></a></td>
				<td class="action_entities"><a href="<?php echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=dest_to_copy" class="down"><?php echo _TO_CC;?></a></td>
		 </tr>
	</table>
	<?php } ?>
	<br/>
	<?php if( count($_SESSION[$origin]['diff_list']['copy']['users']) > 0 || count($_SESSION[$origin]['diff_list']['copy']['entities']) > 0)
		{
			?>
			<h2 class="sstit"><?php echo _TO_CC;?></h2>
			<table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec">
	 <?php $color = ' class="col"';
			for($i=0;$i<count($_SESSION[$origin]['diff_list']['copy']['entities']);$i++)
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
					<td><img src="<?php echo $_SESSION['urltomodules'].'entities/img/manage_entities_b.gif';?>" alt="<? echo _ENTITY;?>" title="<? echo _ENTITY;?>" /></td>
					<td ><?php echo $_SESSION[$origin]['diff_list']['copy']['entities'][$i]['entity_id'];?></td>
					<td colspan="2"><?php echo $_SESSION[$origin]['diff_list']['copy']['entities'][$i]['entity_label'];?></td>
					<td class="action_entities"><a href="<? echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=remove_entity&rank=<?php echo $i;?>&id=<?php echo $_SESSION[$origin]['diff_list']['copy']['entities'][$i]['entity_id'];?>" class="delete"><?php echo _DELETE;?></a></td>

					<td  >&nbsp;</td>
			</tr>
		<?php }
		for($i=0;$i<count($_SESSION[$origin]['diff_list']['copy']['users']);$i++)
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
				<td><img src="<?php echo $_SESSION['urltomodules'].'entities/img/manage_users_entities_b.gif';?>" alt="<? echo _USER;?>" title="<? echo _USER;?>" /></td>
				<td ><?php echo $_SESSION[$origin]['diff_list']['copy']['users'][$i]['lastname'];?></td>
				<td ><?php echo $_SESSION[$origin]['diff_list']['copy']['users'][$i]['firstname'];?></td>
				<td><?php echo $_SESSION[$origin]['diff_list']['copy']['users'][$i]['entity_label']; ?></td>
				<td class="action_entities"><a href="<? echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=remove_user&rank=<?php echo $i;?>&id=<?php echo $_SESSION[$origin]['diff_list']['copy']['users'][$i]['user_id'];?>" class="delete"><?php echo _DELETE;?></a></td>
				<td class="action_entities"><?php if(!$only_cc){?><a href="<? echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=copy_to_dest&rank=<?php echo $i;?>" class="up"><?php echo _TO_DEST;?></a><?php }?>&nbsp;</td>
			</tr>
	<?php }
			?>
			</table>
			<br/>

<?php   }
		else
		{
			?>
			<h2 class="sstit"><?php echo _NO_LINKED_DIFF_LIST;?></h2>
<?php   }
		?>
		<br/>
		<form name="pop_diff" method="post" >
	<div align="center">
	<?php
	if(isset($_SESSION[$origin]['diff_list']['dest']['user_id']) && !empty($_SESSION[$origin]['diff_list']['dest']['user_id']))
		{?>
	<input align="middle" type="button" value="<?php echo _VALIDATE;?>" class="button" name="valid" onclick="change_diff_list('<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=load_listinstance&origin='.$origin; if($only_cc){echo '&only_cc';}?>', <?php echo "'".$display_value."'"; if($_REQUEST['origin'] == 'redirect'){echo ",'diff_list_div_redirect'";}?>);" />
	<?php } ?>
	<input align="middle" type="button" value="<?php echo _CANCEL;?>"  onclick="self.close();" class="button"/>

	</div>
	</form>
	<br/>
	<br/>
	<hr align="center" color="#6633CC" size="5" width="60%">
	<br/>

	<div align="center">
		<h2 class="tit"><?php echo _ENTITIES_LIST;?></h2>

			<table cellpadding="0" cellspacing="0" border="0" class="listing spec">
			<thead>
				<tr>
					<th><?php echo _ID;?></th>
					<th><?php echo _DEPARTMENT;?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>

			<?php $color = ' class="col"';
			for($j=0; $j < count($entities); $j++)
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
					<td><?php echo $entities[$j]['ID'];?></td>
					<td><?php echo $entities[$j]['DEP']; ?></td>
					<td class="action_entities"><a href="<? echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=add_entity&id=<?php echo $entities[$j]['ID'];?>" class="change"><?php echo _ADD_CC;?></a></td>
			</tr>
			<?php }
			?>
		</table>
	</div>
	<div align="center">
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

			<?php $color = ' class="col"';
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
				<td><?php echo $users[$j]['NOM']; ?></td>
				<td><?php echo $users[$j]['PRENOM']; ?></td>
				<td><?php echo $users[$j]['DEP'];?></td>
				<td class="action_entities"><a href="<? echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=add_user&id=<?php echo $users[$j]['ID'];?>" class="change"><?php echo _ADD;?></a></td>
			</tr>
			<?php }
			?>
		</table>
	</div>
</div>

<?php }
else
{
?>
<div id="diff_list" align="center">
	<input  type="button" value="<?php echo _CANCEL;?>" class="button"  onclick="self.close();"/>
	</div>
  <?php  }
		?>

<br/>
</body>
</html>
