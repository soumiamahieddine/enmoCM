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

/*
* Deprecated file : to adapt to the new basket modifications
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."basket".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_admin_entity.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");

if($_SESSION['origin'] <> "valid")
{
?>
<script language="javascript" type="text/javascript">
var frame = window.parent.document.getElementById('diffusion_list');
frame.height = '1px';
</script>
<?php
}
else
{
	$core_tools = new core_tools();
	$sec = new security();
	$connexion = new dbquery();
	$connexion->connect();
	$func = new functions();
	$core_tools->load_lang();
	$core_tools->load_html();
	$core_tools->load_header();
	if($_SESSION['origin'] == "qualify" ||$_SESSION['origin'] == "valid"  )
	{
		$res_id = $_SESSION['res_id_to_qualify'];
	}
	else
	{
		$res_id = $_SESSION['id_to_view'];
		$_SESSION['current_basket'] = array();
	}
	$time = $core_tools->get_session_time_expire();
	$view ="";
	if(isset($_SESSION['collection_id_choice']) && !empty($_SESSION['collection_id_choice']))
	{
		$view = $sec->retrieve_user_view_from_coll_id($_SESSION['collection_id_choice']);
		if(!$view)
		{
			$view = $sec->retrieve_user_coll_table2($_SESSION['collection_id_choice']);
		}
		$table = $sec->retrieve_user_coll_table2($_SESSION['collection_id_choice']);
		$coll_id = $_SESSION['collection_id_choice'];
	}
	else
	{
		if(isset($_SESSION['collections'][0]['view'])&& !empty($_SESSION['collections'][0]['view']))
		{
			$view = $_SESSION['collections'][0]['view'];
		}
		else
		{
			$view = $_SESSION['collections'][0]['table'];
		}
		$table = $_SESSION['collections'][0]['table'];
		$coll_id = $_SESSION['collections'][0]['coll_id'];
	}
	$connexion->query("select res_id, destination from ".$view." where res_id = ".$res_id);
	if($connexion->nb_result() == 0)
	{
		echo "<br><center>"._THE_DOC." "._EXISTS_OR_RIGHT."&hellip;</center>";
	}
	else
	{
		if(!$_SESSION["popup_suite"])
		{
			$line = $connexion->fetch_object();
			$destination = $line->destination;
			$connexion->query("select * from ".$_SESSION['tablename']['bask_entity']." where entity_id = '".$destination."' and enabled= 'Y' order by entity_label");
			if($connexion->nb_result() == 0)
			{
				echo "<br><center>"._ENTITY_MISSING."</center>";
				$state = false;
			}
			else
			{
				if(!isset($_REQUEST['valid4']))
				{
					$_SESSION['m_admin']['entity']['entityId'] = $destination;
					$line = $connexion->fetch_object();
					$_SESSION['m_admin']['entity']['entitylabel'] = $func->show_string($line->entity_label);
					$_SESSION['m_admin']['entity']['listmodel'] = array();
					$connexion2 = new dbquery;
					$connexion2->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listinstance']." where res_id = ".$res_id." and coll_id ='".$coll_id."' order by sequence asc ");
					$count_list = 0;
					while($line = $connexion2->fetch_object())
					{
						$_SESSION['m_admin']['entity']['listmodel'][$line->sequence]['USER_ID'] = $line->user_id;
						$count_list++;
					}
					if($count_list == 0)
					{
						$connexion2->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listmodels']." where id = '".$_SESSION['m_admin']['entity']['entityId']."' order by sequence asc ");
						while($line = $connexion2->fetch_object())
						{
							$_SESSION['m_admin']['entity']['listmodel'][$line->sequence]['USER_ID'] = $line->user_id;
						}
					}
					for($diff_i=0; $diff_i<count($_SESSION['m_admin']['entity']['listmodel']); $diff_i++)
					{
						$connexion->query("select firstname, lastname, department, mail from ".$_SESSION['tablename']['users']." where user_id='".$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['USER_ID']."'");
						$line = $connexion->fetch_object();
						$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['FIRSTNAME'] = $line->firstname;
						$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['LASTNAME'] = $line->lastname;
						$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['MAIL'] = $line->mail;
						$connexion->query("select entity_label from ".$_SESSION['tablename']['bask_entity']." where entity_id='".$line->department."'");
						$line = $connexion->fetch_object();
						$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['DEPARTMENT_LABEL'] = $line->entity_label;
					}
				}
			}
		}
		else
		{
			$_SESSION['show_diff'] = true;
			$connexion->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$coll_id."' and res_id = ".$res_id."");
			for($i=0; $i<count($_SESSION['m_admin']['entity']['listmodel']); $i++)
			{
				$connexion->query("insert into ".$_SESSION['tablename']['bask_listinstance']." (coll_id, res_id, sequence, user_id) values('".$coll_id."',".$res_id.",".$i." ,'".$_SESSION['m_admin']['entity']['listmodel'][$i]['USER_ID']."')");
			}
		}
	}
	//redirection to sender entity
	 /*if(isset($_REQUEST['valid1']))
	{
		$connexion->query("update ".$table." set destination = '".$_REQUEST['service']."', status = 'COU' where res_id = ".$res_id);
		$connexion->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$coll_id."' and res_id = ".$res_id."");
		$conn = new dbquery();
		$conn->connect();
		$connexion->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listmodels']." where id = '".$_REQUEST['service']."' order by sequence asc ");
		$diff_i=0;
		while($line = $connexion->fetch_object())
		{
			$conn->query("insert into ".$_SESSION['tablename']['bask_listinstance']." values('".$coll_id."','".$res_id."',".$line->sequence.",'".$line->user_id."','DOC')");
			$diff_i++;
		}
		$_SESSION['current_foldertype'] = "";
		//$_SESSION['error'] = _DOC_REDIRECT_TO_SENDER_ENTITY." ".$_REQUEST['service'];
		if($_SESSION['history']['redirection'] == 'true')
		{
			require_once($_SESSION['pathtocoreclass']."class_history.php");
			$hist = new history();
			$hist->add($table, $res_id,"RED", _REDIRECT_TO_SENDER_ENTITY, $_SESSION['config']['databasetype'], "basket");
		}
		if($_SESSION['origin'] == "qualify")
		{
			?>
			<script language="javascript" type="text/javascript">window.top.opener.top.location.reload();window.top.close();</script>
			<?php
		}
		else
		{
			?>
			<script language="javascript" type="text/javascript">
				window.top.opener.top.location = '<?php echo $_SESSION['config']['businessappurl'].'index.php';?>';
				window.top.close();
			</script>
			<?php
		}
	}*/
	$erreur = "";
	//redirection to another entity
	if(isset($_REQUEST['valid2']))
	{
		if(isset($_REQUEST['service']) && !empty($_REQUEST['service']))
		{
			$destination = $_REQUEST['service'];
			$_SESSION['validation_service'] = $_REQUEST['service'];
			$_SESSION['validation_user'] = '';
			$connexion->query("update ".$table." set destination = '".$_REQUEST['service']."' where res_id = ".$res_id);
			$connexion->query("select * from ".$_SESSION['tablename']['bask_entity']." where entity_id = '".$_REQUEST['service']."'");
			$line = $connexion->fetch_object();
			$_SESSION['m_admin']['entity']['entitylabel'] = $func->show_string($line->entity_label);
			$connexion->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$coll_id."' and res_id = ".$res_id."");
			$conn = new dbquery();
			$conn->connect();
			$connexion->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listmodels']." where id = '".$_REQUEST['service']."' order by sequence asc ");
			$diff_i=0;
			while($line = $connexion->fetch_object())
			{
				$conn->query("insert into ".$_SESSION['tablename']['bask_listinstance']." values('".$coll_id."','".$res_id."',".$line->sequence.",'".$line->user_id."','DOC')");
				$diff_i++;
			}
			$_SESSION['current_foldertype'] = "";
			if($_SESSION['history']['redirection'] == 'true')
			{
				require_once($_SESSION['pathtocoreclass']."class_history.php");
				$hist = new history();
				$hist->add($table, $res_id,"RED",  _DOC_REDIRECT_TO_ENTITY." ".$_REQUEST['service'], $_SESSION['config']['databasetype'], "basket");
			}

			$_SESSION['show_diff'] = true;
			?>
			<script language="javascript" type="text/javascript">
				var eleframe1 = window.opener.parent.frames['myframe'].document.getElementById('diffusion_list');
				eleframe1.src = '<?php=$_SESSION['urltomodules']?>basket/frame_diffusion_list_qualify.php';
				window.top.close();
			</script>
				<?php
		}
		else
		{
			$_SESSION['validation_service'] = '';
			$erreur .= _CHOOSE_DEP."!<br/>";
		}
	}
	//redirection to an user
	if(isset($_REQUEST['valid3']))
	{
		if(isset($_REQUEST['user']) && !empty($_REQUEST['user']))
		{
			$_SESSION['validation_user'] = $_REQUEST['user'];
			$_SESSION['validation_service'] = '';
			$tmp = array();
			$connexion->query("update ".$table." set dest_user = '".$_REQUEST['user']."' where res_id = ".$res_id);
			$connexion->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$coll_id."' and res_id = ".$res_id."");
			$conn = new dbquery();
			$conn->connect();
			$conn->query("insert into ".$_SESSION['tablename']['bask_listinstance']." values('".$coll_id."','".$res_id."',0,'".$_REQUEST['user']."','DOC')");
			$_SESSION['current_foldertype'] = "";
			if($_SESSION['history']['redirection'] == 'true')
			{
				require_once($_SESSION['pathtocoreclass']."class_history.php");
				$hist = new history();
				$hist->add($table, $res_id,"RED",  _DOC_REDIRECT_TO_USER." ".$_REQUEST['user'], $_SESSION['config']['databasetype'], "basket");
			}

			$_SESSION['show_diff'] = true;
			?>
			<script language="javascript" type="text/javascript">
				var eleframe1 = window.opener.parent.frames['myframe'].document.getElementById('diffusion_list');
				eleframe1.src = '<?php=$_SESSION['urltomodules']?>basket/frame_diffusion_list_qualify.php';
				window.top.close();
			</script>
				<?php
		}
		else
		{
			$_SESSION['validation_user'] = '';
			$erreur .= _CHOOSE_USER."!<br/>";
		}
	}
	if(!$_SESSION["popup_suite"])
	{
		$connexion->connect();
		$connexion->query("select * from ".$_SESSION['tablename']['bask_entity']." where entity_id = '".$destination."' and enabled= 'Y' order by entity_label");
		if(!isset($_REQUEST['valid4']))
		{
			$_SESSION['m_admin']['entity']['entityId'] = $destination;
			$line = $connexion->fetch_object();
			$_SESSION['m_admin']['entity']['entitylabel'] = $func->show_string($line->entity_label);
			$_SESSION['m_admin']['entity']['listmodel'] = array();
			$connexion2 = new dbquery;
			$connexion2->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listinstance']." where res_id = ".$res_id." and coll_id ='".$coll_id."' order by sequence asc ");
			$count_list = 0;
			while($line = $connexion2->fetch_object())
			{
				$_SESSION['m_admin']['entity']['listmodel'][$line->sequence]['USER_ID'] = $line->user_id;
				$count_list++;
			}
			if($count_list == 0)
			{
				$connexion2->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listmodels']." where id = '".$_SESSION['m_admin']['entity']['entityId']."' order by sequence asc ");
				while($line = $connexion2->fetch_object())
				{
					$_SESSION['m_admin']['entity']['listmodel'][$line->sequence]['USER_ID'] = $line->user_id;
				}
			}
			for($diff_i=0; $diff_i<count($_SESSION['m_admin']['entity']['listmodel']); $diff_i++)
			{
				$connexion->query("select firstname, lastname, department, mail from ".$_SESSION['tablename']['users']." where user_id='".$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['USER_ID']."'");
				$line = $connexion->fetch_object();
				$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['FIRSTNAME'] = $line->firstname;
				$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['LASTNAME'] = $line->lastname;
				$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['MAIL'] = $line->mail;
				$connexion->query("select entity_label from ".$_SESSION['tablename']['bask_entity']." where entity_id='".$line->department."'");
				$line = $connexion->fetch_object();
				$_SESSION['m_admin']['entity']['listmodel'][$diff_i]['DEPARTMENT_LABEL'] = $line->entity_label;
			}
		}
	}
	else
	{
		$_SESSION["popup_suite"] = false;
	}
	?>
<body id="iframe" onLoad="setTimeout(window.close, <?php echo $time;?>*60*1000);">
<script language="javascript" type="text/javascript">
function resize_frame_diffusion()
{
	var frame = window.parent.document.getElementById('diffusion_list');
	var div1 = window.document.getElementById('desc55');
	if(div1.style.overflow == "visible" && div1.style.display !="none")
	{
		frame.height = '50px';
	}
	else
	{
		frame.height = '400px';
	}
}
</script>

<br/>
<center>
	<h2 onClick="change(55<?php if($_SESSION['show_diff'] == true){ echo ',false'; }?>);resize_frame_diffusion();" id="h255" class="tit">
		<img src="<?php echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/<?php if($_SESSION['show_diff'] == true){ echo "moins";}else{echo "plus";}?>.png" alt="" />&nbsp;
		<b><?php echo _DIFFUSION_DISTRIBUTION;?></b>
		<span class="lb1-details">&nbsp;</span>
	</h2>
	<div class="desc" id="desc55" style=" display:<?php if($_SESSION['show_diff'] == true){echo "block";$_SESSION['show_diff'] = false;}else{ echo "none";}?>">
		<div class="ref-unit">
			<?php
			$services = array();
			$users = array();
			$connexion = new dbquery();
			$connexion->connect();
			$connexion2 = new dbquery();
			$connexion2->connect();
			if(!empty($_SESSION['current_basket']['redirect_services']) && $_SESSION['current_basket']['can_redirect'] == 'Y')
			{
				$connexion->query("select * from ".$_SESSION['tablename']['bask_entity']." where entity_id in (".$_SESSION['current_basket']['redirect_services'].") and entity_id in (select id from listmodels) and enabled = 'Y' order by entity_label");
				while($res = $connexion->fetch_object())
				{
					array_push($services, array('ID' => $res->entity_id, 'LABEL' => $res->entity_label));
				}
			}
			if(!empty($_SESSION['current_basket']['redirect_users']) && $_SESSION['current_basket']['can_redirect'] == 'Y')
			{
				$connexion2 = new dbquery();
				$connexion2->connect();
				$connexion->query("select distinct uc.user_id, u.lastname from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['users']." u where group_id in (".$_SESSION['current_basket']['redirect_users'].") and u.user_id = uc.user_id order by u.lastname asc");
				while($res = $connexion->fetch_object())
				{
					$connexion2->query("select lastname, firstname from ".$_SESSION['tablename']['users']." where user_id = '".$res->user_id."' and user_id <> ''");
					$res2 = $connexion2->fetch_object();
					array_push($users, array( 'ID' => $res->user_id, 'NOM' => $res2->lastname, "PRENOM" => $res2->firstname, "SERVICE" => $res->department));
				}
			}
			$connexion->query("select initiator from ".$view." where res_id = ".$res_id);
			$line = $connexion->fetch_object();
			$initiator_entity = $line->initiator;
			if($_SESSION['current_basket']['can_redirect'] == 'Y')
			{
				?>
				<!--<form name="red_form1" id="red_form1" method="REQUEST" class="forms">
					<p>
						<label><?php //echo _LETTER_SERVICE_REDIRECT;?> :</label>
						<select name="service" id="service" readonly>
							<option value="<?php //echo $initiator_entity;?>" checked><?php //echo $initiator_entity;?></option>
						</select>
						<input type="submit" name="valid1" id="valid1" value="<?php //echo _REDIRECT;?>" class="button" />
					</p>
				</form>-->
                <h3><?php echo _DISTRIBUTE_MAIL;?></h3><?php
					if(!empty($_SESSION['current_basket']['redirect_services']))
					{
						?>
						<form name="red_form2" id="red_form2" method="get" class="forms">
							<p>
								<label> <?php echo _REDIRECT_TO_OTHER_DEP;?> :</label>
								<select name="service" id="service">
									<option value=""><?php echo _BLANK;?></option>
									<?php
									for($diff_i=0;$diff_i<count($services); $diff_i++)
									{
										?>
										<option value="<?php echo $services[$diff_i]['ID'];?>" <?php if($_SESSION['validation_service'] == $services[$diff_i]['ID']){echo 'selected="selected"';}?>><?php echo $services[$diff_i]['LABEL'];?></option>
										<?php
									}
									?>
								</select>
								<input type="submit" name="valid2" value="<?php echo _REDIRECT;?>" id="valid2" class="button" />
							</p>
						</form>
						<?php
					}

					if(!empty($_SESSION['current_basket']['redirect_users']))
					{
						?>
						<div id="form3">
							<form name="red_form3" id="red_form3" method="get" class="forms">
								<p>
									<label><?php echo _REDIRECT_TO_USER;?> :</label>
									<select name="user" id="user">
										<option value=""><?php echo _BLANK;?></option>
											<?php
											for($diff_i=0; $diff_i < count($users); $diff_i++)
											{
												?>
												<option value="<?php echo $users[$diff_i]['ID'];?>" <?php if($_SESSION['validation_user']  == $users[$diff_i]['ID']) { echo 'selected="selected"';;}?>><?php echo $users[$diff_i]['NOM'].' '.$users[$diff_i]['PRENOM'];?></option>
												<?php
											}
											?>
									</select>
									<input type="submit" name="valid3" id="valid3" value="<?php echo _REDIRECT;?>" class="button" />
								</p>
							</form>
						</div>
						<?php
					}
			}
			else
			{
				if( $_SESSION['origin'] == "valid" ) //$_SESSION['origin'] == "qualify" ||
				{
					?>
					<div><?php echo _NO_REDIRECT_RIGHT;?>.</div>
					<?php
				}
				/*	?>
				<form name="red_form1" id="red_form1" method="REQUEST" class="forms">
					<p>
						<label><?php echo _LETTER_SERVICE_REDIRECT;?> :</label>
						<select name="service" id="service" readonly>
							<option value="<?php echo $initiator_entity;?>" checked><?php echo $initiator_entity;?></option>
						</select>
						<input type="submit" name="valid1" id="valid1" value="<?php echo _REDIRECT;?>" class="button" />
					</p>
				</form>
				<?php */
			}
			?>
			<br/>
			<?php
			//BEGIN
			if(isset($_SESSION['m_admin']['entity']['listmodel']) && count($_SESSION['m_admin']['entity']['listmodel']) > 0)
			{
				?>
			<!--	<h2 onClick="change(56)" id="h256" class="tit">
					<img src="<?php echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/plus.png" alt="" />&nbsp;
					<b>-->
						<h3><?php echo _LINKED_DIFF_LIST;?> : <?php echo $_SESSION['m_admin']['entity']['entitylabel'];?></h3><!--
					</b>
					<span class="lb1-details">&nbsp;</span>
				</h2>
				<div class="desc" id="desc56">
					<div class="ref-unit">-->

					<p class="sstit"><?php echo _RECIPIENT;?></p>
						<form name="red_form4" id="red_form4" method="REQUEST">
							<table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec">
								<thead>
									<tr>
										<th><?php echo _LASTNAME;?></th>
										<th><?php echo _FIRSTNAME;?></th>
										<th><?php echo _DEPARTMENT;?></th>
									</tr>
								</thead>
								<tr class="col">
									<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['FIRSTNAME'];?></td>
									<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['LASTNAME'];?></td>
									<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['DEPARTMENT_LABEL'];?></td>
								</tr>
							</table>
							<br/>
							<?php
							if(count($_SESSION['m_admin']['entity']['listmodel']) > 1)
							{
								?>
								<p class="sstit"><?php echo _TO_CC;?></p>
								<table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec">
									<thead>
										<tr>
											<th><?php echo _LASTNAME;?></th>
											<th><?php echo _FIRSTNAME;?></th>
											<th><?php echo _DEPARTMENT;?></th>
										</tr>
									</thead>
								<?php
								$color = ' class="col"';
								for($diff_i=1;$diff_i<count($_SESSION['m_admin']['entity']['listmodel']);$diff_i++)
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
										<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$diff_i]['FIRSTNAME'];?></td>
										<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$diff_i]['LASTNAME'];?></td>
										<td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$diff_i]['DEPARTMENT_LABEL']; ?></td>
									</tr>
									<?php
								}
								?>
								</table>
						<?php
						}
						?>
						<p class="buttons">
							<p>
								<!--<input type="submit" name="valid4" id="valid4" value="<?php //echo _VALID_LIST;?>" class="button" />-->
								<!--<input type="button" onClick="javascript:valid_list();" class="button" value="<?php //echo _VALID_LIST;?>" />-->
								<input type="button" onClick="window.open('popup_listmodel_creation_qualify.php?what=A', '', 'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=900,height=850,location=no');" class="button" value="<?php echo _MODIFY_LIST;?>" />
							</p>
						</p>
					</form>
				<?php
			}
			else
			{
				$_SESSION['m_admin']['entity']['listmodel'] = array();
				?>
		<!--		<h2 onClick="change(57)" id="h257" class="tit">
					<img src="<?php echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/plus.png" alt="" />&nbsp;
					<b>-->
				<h3>	<?php echo _NO_LINKED_DIFF_LIST;?>.</h3>
                <!--
                </b>
					<span class="lb1-details">&nbsp;</span>
				</h2>
				<div class="desc" id="desc57">
					<div class="ref-unit">-->
						<p class="buttons">
						   <p>
								<input type="button" onClick="window.open('popup_listmodel_creation_qualify.php?what=A', '', 'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=900,height=850,location=no');" class="button" value="<?php echo _CREATE_LIST;?>" />
							</p>
						</p>
			<!--		</div>
				</div>-->
				<?php
			}
			?>
		</center>
        </div>
	</div>
</body>
</html>
		<?php

}
