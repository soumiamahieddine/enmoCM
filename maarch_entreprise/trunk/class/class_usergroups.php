<?php
/**
*  Usergroups class
*
* Contains all the functions to manage the groups
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class usergroups: contains all the functions and forms to manage the usergroups
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package  Maarch PeopleBox 1.0
* @version 2.1
*/

class usergroups extends dbquery
{
	/**
	* Redefinition of the user object constructor : configure the SQL argument order by
	*/
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Form for the management of the groups.
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	* @param 	string  $id  group identifier (empty by default)
	*/
	public function formgroups($mode,$id = "")
	{
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		$sec = new security();
		$func = new functions();
		$core_tools = new core_tools();
		$state = true;
		$tab = array();
		$users = array();
		$baskets = array();
		
		if($mode == "up")
		{
			$_SESSION['m_admin']['mode'] = "up";
			if(empty($_SESSION['error']))
			{
				$this->connect();
				$this->query("select * from ".$_SESSION['tablename']['usergroups']." where group_id = '".$id."' and enabled = 'Y'");

				if($this->nb_result() == 0)
				{
					$_SESSION['error'] = _GROUP.' '._UNKNOWN;
					$state = false;
				}
				else
				{
					$line = $this->fetch_object();
					$_SESSION['m_admin']['groups']['GroupId'] = $line->group_id;
					$_SESSION['m_admin']['groups']['desc'] = $this->show_string($line->group_desc);
					$this->query("select * from ".$_SESSION['tablename']['security']." where group_id = '".$id."'");
					$i=0;
					while($line = $this->fetch_object())
					{
						$_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'] = $this->show_string($line->coll_id);
						$_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'] = $this->show_string($line->where_clause);
						$i++;
					}
				}

				if (! isset($_SESSION['m_admin']['load_security']) || $_SESSION['m_admin']['load_security'] == true)
				{
					$_SESSION['m_admin']['groups']['security']=$sec->load_security_group($id);
					$_SESSION['m_admin']['load_security'] = false ;
				}

				if (! isset($_SESSION['m_admin']['load_services']) || $_SESSION['m_admin']['load_services'] == true)
				{
					$sec->load_services_group($id);
					$_SESSION['m_admin']['load_services'] = false ;
				}
				
				if($core_tools->is_module_loaded('entities'))
				{
					$this->query("select  u.lastname , u.firstname  , u.user_id , e.entity_label
								from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['users']." u, ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['ent_entities']." e where ue.user_id = u.user_id AND ue.entity_id = e.entity_id AND uc.user_id = u.user_id AND u.enabled = 'Y' AND uc.group_id = '".$id."' and ue.primary_entity = 'Y' order by u.lastname asc");

					while($res = $this->fetch_object())
					{
						array_push($users, array( 'ID' => $res->user_id, 'LASTNAME' => $res->lastname, 'FIRSTNAME' => $res->firstname, 'DEPARTMENT' => $res->entity_label));
					}
				}
				else
				{
					$this->query("select  u.department, u.lastname , u.firstname  , u.user_id 
								from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['users']." u where uc.user_id = u.user_id AND u.enabled = 'Y' AND uc.group_id = '".$group."' order by u.lastname asc");

					while($res = $this->fetch_object())
					{
						array_push($users, array( 'ID' => $res->user_id, 'LASTNAME' => $res->lastname, 'FIRSTNAME' => $res->firstname, 'DEPARTMENT' => $res->department));
					}
				}
				if($core_tools->is_module_loaded('basket'))
				{
					$this->query("select b.basket_name, b.basket_id, b.basket_desc, b.coll_id
										from ".$_SESSION['tablename']['bask_baskets']." b, ".$_SESSION['tablename']['bask_groupbasket']." bg where b.basket_id = bg.basket_id AND bg.group_id = '".$id."' order by b.basket_name asc");

					while($res = $this->fetch_object())
					{
						array_push($baskets, array( 'ID' => $res->basket_id, 'NAME' => $res->basket_name, 'DESC' => $res->basket_desc, 'COLL_ID' => $res->coll_id));
					}
				}
			}
		}
		elseif($mode == "add")
		{
			$_SESSION['m_admin']['mode'] = "add";
			if ($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] ))
			{
				$sec->init_session();
			}
		}

		?>
        <h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_groupe_b.gif" alt="" />
		<?php
			if($mode == "add")
			{
				echo _GROUP_ADDITION;
			}
			elseif($mode == "up")
			{
				echo _GROUP_MODIFICATION;
			}
			?>
			</h1>

			<?php
			if($state == false)
			{
				echo "<br /><br /><br /><br />"._GROUP.' '._UNKNOWN."<br /><br /><br /><br />";
			}
			else
			{
				?>
				<div id="inner_content" class="clearfix">
					<div id="group_box" class="bloc" >
						<?php
						if($mode == "up")
						{
						?><div onclick="new Effect.toggle('users_list', 'blind', {delay:0.2});return false;" >
							&nbsp;<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=membres_groupe_b.gif" alt="" /><i><?php  echo _SEE_GROUP_MEMBERS;?></i> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="" />
							<span class="lb1-details">&nbsp;</span></div>
							<div class="desc" id="users_list" style="display:none;">
								<div class="ref-unit">
									<table cellpadding="0" cellspacing="0" border="0" class="listingsmall">
										<thead>
											<tr>
												<th><?php  echo _LASTNAME;?></th>
												<th ><?php  echo _FIRSTNAME;?></th>
												<th  ><?php  echo _ENTITY;?></th>
												<th></th>
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
												   <td width="25%"><?php  echo $users[$i]['LASTNAME'];?></td>
													  <td width="25%"><?php  echo $users[$i]['FIRSTNAME'];?></td>
												   <td><?php  echo $users[$i]['DEPARTMENT']; ?></td>
												   <td ><?php 
													if($core_tools->test_service('admin_users', 'apps', false))
													{?>
												   <a class="change" href="<?php echo $_SESSION['config']['businessappurl'].'index.php?page=users_up&admin=users&id='.$users[$i]['ID']; ?>" alt="<?php echo _GO_MANAGE_USER;?>" title="<?php echo _GO_MANAGE_USER;?>"><i><?php echo _GO_MANAGE_USER;?></i></a><?php }?></td>
										</tr>
												<?php
										}
									?>
									</tbody>
									</table>
									<br/>
								</div>
							</div>
							
						<?php
							if($core_tools->is_module_loaded('basket'))
							{?>
								<div onclick="new Effect.toggle('baskets_list2', 'blind', {delay:0.2});return false;" >
							&nbsp;<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=membres_groupe_b.gif" alt="" /><i><?php  echo _SEE_BASKETS_RELATED;?></i> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="" />
							<span class="lb1-details">&nbsp;</span></div>
							<div class="desc" id="baskets_list2" style="display:none;">
								<div class="ref-unit">
									<table cellpadding="0" cellspacing="0" border="0" class="listingsmall">
										<thead>
											<tr>
												<th><?php  echo NAME;?></th>
												<th ><?php  echo DESC;?></th>
												<th></th>
											</tr>
										</thead>

									<tbody>
										 <?php
									$color = ' class="col"';
									 for($i=0;$i<count($baskets);$i++)
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
												   <td width="30%"><?php  echo $baskets[$i]['NAME'];?></td>
												  <td width="50%"><?php  echo $baskets[$i]['DESC'];?></td>
												   <td >
												   <?php if($core_tools->test_service('admin_baskets', 'basket', false))
													{?>
												    <a class="change" href="<?php echo $_SESSION['config']['businessappurl'].'index.php?page=basket_up&module=basket&id='.$baskets[$i]['ID']; ?>" alt="<?php echo _GO_MANAGE_BASKET;?>" title="<?php echo _GO_MANAGE_BASKET;?>"><i><?php echo _GO_MANAGE_BASKET;?></i></a>
												   <?php } ?> 
												    </td>
										</tr>
												<?php
										}
									?>
									</tbody>
									</table>
									<br/>
									<br/>
								</div>
							</div>
						<?php	}
						}
						?><div id="access"></div>
						<!--<iframe name="group_form" id="group_form" class="frameform4" src="<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&admin=groups&page=groups_form';?>" frameborder="0" scrolling="auto"></iframe>-->
					</div>
					<form name="formgroup" method="post"  class="forms" action="<?php  if($mode == "up") { echo $_SESSION['config']['businessappurl']."index.php?display=true&admin=groups&page=group_up_db"; } elseif($mode == "add") { echo "index.php?display=true&admin=groups&page=group_add_db"; } ?>" >
					<input type="hidden" name="display" value="value" />
					<input type="hidden" name="admin" value="groups" />
					<?php  if($mode == "up") {?>
						<input type="hidden" name="page" value="group_up" />
					<?php }
					elseif($mode == "add")
					{?>
						<input type="hidden" name="page" value="group_add" />
			<?php	}?>
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
						<table border="0" align="center" width="540px">
							<tr>
								<td width = "200px" align="right">
									<?php  echo _GROUP; ?> :
								</td>
								<td align="left">
									<?php  if($mode == "up") { echo $func->show($_SESSION['m_admin']['groups']['GroupId']); } ?>
									<input name="GroupId" type="<?php  if($mode == "up") { ?>hidden<?php  } elseif($mode == "add") { ?>text<?php  } ?>" id="GroupId" value="<?php  echo $func->show($_SESSION['m_admin']['groups']['GroupId']); ?>" />
									<input type="hidden"  name="id" value="<?php  echo $id; ?>" />
								</td>
							</tr>
							<tr>
								<td align="right">
									<?php  echo _DESC; ?> :
								</td>
								<td align="left">
									<input name="desc" id="desc" class="text" type="text" value="<?php  echo $_SESSION['m_admin']['groups']['desc']; ?>"  alt="<?php  echo $_SESSION['m_admin']['groups']['desc']; ?>" title="<?php  echo $_SESSION['m_admin']['groups']['desc']; ?>"/>
								</td>
							</tr>
						</table>
						<br><center><i><?php  echo _AVAILABLE_SERVICES;?> :</i></center>
						<?php
						//$this->show_array($_SESSION['enabled_services']);
						$enabled_services_sort_by_parent = array();
						$j=0;
						for($i=0; $i<count($_SESSION['enabled_services']);$i++)
						{
							if( $_SESSION['enabled_services'][$i]['system'] == false)
							{
								if($_SESSION['enabled_services'][$i]['parent'] <> $_SESSION['enabled_services'][$i - 1]['parent'])
								{
									$j=0;
								}
								$enabled_services_sort_by_parent[$_SESSION['enabled_services'][$i]['parent']][$j] = $_SESSION['enabled_services'][$i];
							$j++;
							}
						}
						//$this->show_array($enabled_services_sort_by_parent);

						$_SESSION['cpt']=0;
						foreach(array_keys($enabled_services_sort_by_parent) as $value)
						{
							if($value == 'application')
							{
								$label = _APPS_COMMENT;
							}
							elseif($value == 'core')
							{
								$label = _CORE_COMMENT;
							}
							else
							{
								$label = $_SESSION['modules_loaded'][$value]['comment'];
							}
							//$this->show_array($enabled_services_sort_by_parent[$value]);
							//$this->show_array($_SESSION['m_admin']['groups']['services']);
							//echo $_SESSION['cpt']."<br>";

							if(count($enabled_services_sort_by_parent[$value]) > 0)
							{
							?>

								<h5 onclick="change(<?php  echo $_SESSION['cpt'];?>)" id="h2<?php  echo $_SESSION['cpt'];?>" class="categorie">
									<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="" />&nbsp;<b><?php  echo $label ;?></b>
									<span class="lb1-details">&nbsp;</span>
								</h5>
								<br/>
								<div class="desc block_light admin" id="desc<?php  echo $_SESSION['cpt'];?>" style="display:none">
									<div class="ref-unit">
										<table>
										<?php
										for($i=0; $i<count($enabled_services_sort_by_parent[$value]); $i++)
										{
											if($enabled_services_sort_by_parent[$value][$i]['system'] == false)
											{
											?>
											<tr>
												<td width="800px" align="right" title="<?php  echo $enabled_services_sort_by_parent[$value][$i]['comment'];?>">
													<?php  echo $enabled_services_sort_by_parent[$value][$i]['label'];?> <?php  if(  $enabled_services_sort_by_parent[$value][$i]['type'] == "admin") {?>(<?php echo _ADMIN;?>) <?php  }elseif($enabled_services_sort_by_parent[$value][$i]['type'] == "menu"){?>(<?php echo _MENU;?>)<?php } ?>  :
												</td>
												<td width="50px" align="left">
													<input type="checkbox"  class="check" name="services[]" value="<?php  echo $enabled_services_sort_by_parent[$value][$i]['id'];?>" <?php  if(in_array(trim($enabled_services_sort_by_parent[$value][$i]['id']),$_SESSION['m_admin']['groups']['services']) || $mode == "add"){ echo 'checked="checked"';}?>  />
												</td>
											</tr>
											<?php
											}
										}
										?>
									</table>
								</div>
							</div>
						<?php }

							$_SESSION['cpt']++;
						}
						?>
						<p class="buttons">
							<?php
							if($mode == "up")
							{
							?>
							<input id="groupbutton" type="submit"  name="Submit" value="<?php  echo _VALIDATE; ?>" class="button"/>
							<?php
							}
							elseif($mode == "add")
							{
							?>
							<input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button"/>
							<?php
							}
							?>
							 <input type="button" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=groups&amp;admin=groups';"/>
						</p>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
					</form>
				</div>
				<script>updateContent('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=groups_form&admin=groups', 'access');</script>
			<?php
			}
	}

	/**
	* Treats the information returned by the form of formgroups()
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	*/
	public function groupsinfo($mode)
	{
		$func = new functions();

		if($mode == "add")
		{
			$_SESSION['m_admin']['groups']['GroupId'] = $func->wash($_POST['GroupId'], "alphanum", _THE_GROUP, 'yes', 0, 32);
		}

		if($mode == "up")
		{
			$_SESSION['m_admin']['groups']['GroupId'] = $func->wash($_POST['id'], "alphanum", _THE_GROUP, 'yes', 0, 32);
		}

		if (isset($_POST['desc']) && !empty($_POST['desc']))
		{
			$_SESSION['m_admin']['groups']['desc'] = $func->wash($_POST['desc'], "no", _GROUP_DESC, 'yes', 0, 255);
		}

		if (count($_SESSION['m_admin']['groups']['security']) < 1  && count($_REQUEST['services']) < 1)
		{
			$func->add_error(_THE_GROUP.' '._NO_SECURITY_AND_NO_SERVICES, "");
		}
		$_SESSION['m_admin']['groups']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['groups']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['groups']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['groups']['start'] = $_REQUEST['start'];
	}

	/**
	* Add ou modify groups in the database
	*
	* @param string $mode up or add
	*/
	public function addupgroups($mode)
	{
		// add ou modify users in the database
		$this->groupsinfo($mode);
		$order = $_SESSION['m_admin']['groups']['order'];
		$order_field = $_SESSION['m_admin']['groups']['order_field'];
		$what = $_SESSION['m_admin']['groups']['what'];
		$start = $_SESSION['m_admin']['groups']['start'];
/*
		if($_SESSION['m_admin']['groups']['admin'] == "");
		{
			$_SESSION['m_admin']['groups']['admin'] = "N";
		}
		if($_SESSION['m_admin']['groups']['stagiaire'] == "");
		{
			$_SESSION['m_admin']['groups']['stagiaire'] = "N";
		}
		if($_SESSION['m_admin']['groups']['view'] == "");
		{
			$_SESSION['m_admin']['groups']['view'] = "N";
		}
		if($_SESSION['m_admin']['groups']['stats'] == "");
		{
			$_SESSION['m_admin']['groups']['stats'] = "N";
		}
		if($_SESSION['m_admin']['groups']['del'] == "");
		{
			$_SESSION['m_admin']['groups']['del'] = "N";
		}
*/
		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['groups']['GroupId']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=group_up&id=".$_SESSION['m_admin']['groups']['GroupId']."&admin=groups");
					exit;
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit;
				}
			}
			elseif($mode == "add")
			{
				$_SESSION['m_admin']['load_group'] = false;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=group_add&admin=groups");
				exit;
			}
		}
		else
		{
			$this->connect();
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
			$sec = new security();
			if($mode == "add")
			{
				$this->query("select group_id from ".$_SESSION['tablename']['usergroups']." where group_id= '".$_SESSION['m_admin']['groups']['GroupId']."'");

				if($this->nb_result() > 0)
				{
					$_SESSION['error'] = $_SESSION['m_admin']['groups']['GroupId']." "._ALREADY_EXISTS."<br />";
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=group_add&admin=groups");
					exit();
				}
				else
				{
					//$syntax = true;
				//	$syntax = $sec->where_test();

/*
					if($syntax <> true)
					{
					 	$_SESSION['error'] .= " : "._SYNTAX_ERROR_WHERE_CLAUSE."." ;
						header("location: ".$_SESSION['config']['businessappurl']."index.php?page=group_add&admin=groups");
						exit();
					}
					else
					{
*/
						$tmp = $this->protect_string_db($_SESSION['m_admin']['groups']['desc']);
						$this->query("insert into ".$_SESSION['tablename']['usergroups']." (group_id , group_desc , enabled) values ('".$_SESSION['m_admin']['groups']['GroupId']."'," ." '".$tmp."','Y')");

						$sec->load_access_db();
						$sec->load_services_db($_REQUEST['services'],$_SESSION['m_admin']['groups']['GroupId']);

						if($_SESSION['history']['usergroupsadd'] == "true")
						{
							require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
							$users = new history();
							$users->add($_SESSION['tablename']['usergroups'], $_SESSION['m_admin']['groups']['GroupId'],"ADD",_GROUP_ADDED." : ".$_SESSION['m_admin']['groups']['GroupId'], $_SESSION['config']['databasetype']);
						}
						$this->cleargroupinfos();
						$_SESSION['error'] =  _GROUP_ADDED;
						header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
						exit();
				//	}
				}
			}
			elseif($mode == "up")
			{
					$this->query("UPDATE ".$_SESSION['tablename']['usergroups']." set group_desc = '".$this->protect_string_db($_SESSION['m_admin']['groups']['desc'])."' , administrator = '".$_SESSION['m_admin']['groups']['admin']."'," ." custom_right1 = '".$_SESSION['m_admin']['groups']['stagiaire']."', custom_right2 = '".$_SESSION['m_admin']['groups']['view']."', custom_right3 = '".$_SESSION['m_admin']['groups']['stats']."'" .", custom_right4 = '".$_SESSION['m_admin']['groups']['del']."' where group_id = '".$_SESSION['m_admin']['groups']['GroupId']."'");
					$tmp = $this->protect_string_db($_SESSION['m_admin']['groups']['desc']);
					$this->query("UPDATE ".$_SESSION['tablename']['usergroups']." set group_desc = '".$this->protect_string_db($tmp)."'  where group_id = '".$_SESSION['m_admin']['groups']['GroupId']."'");
					

/*
					if($sec->where_test() == false)
					 {
					 	$_SESSION['error'] .= " : "._SYNTAX_ERROR_WHERE_CLAUSE."." ;
						header("location: ".$_SESSION['config']['businessappurl']."index.php?page=group_up&admin=groups&id=".$_SESSION['m_admin']['groups']['GroupId']);
						exit();
					}
					else
					{
*/
						$sec->load_access_db();
						$sec->load_services_db($_REQUEST['services'],$_SESSION['m_admin']['groups']['GroupId']);
						if($_SESSION['history']['usergroupsup'] == "true")
						{
							require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
							$users = new history();
							$users->add($_SESSION['tablename']['usergroups'], $_SESSION['m_admin']['groups']['GroupId'],"UP",_GROUP_UPDATE." : ".$_SESSION['m_admin']['groups']['GroupId'], $_SESSION['config']['databasetype']);
						}

						if($this->in_group($_SESSION['user']['UserId'], $_SESSION['m_admin']['groups']['GroupId']) )
						{
							$_SESSION['user']['groups'] = array();
							$_SESSION['user']['security'] = array();
							//$sec->load_groups($_SESSION['user']['UserId']);
							$tmp = $sec->load_groups($_SESSION['user']['UserId']);
							$_SESSION['user']['groups'] = $tmp['groups'];
							$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

							$tmp = $sec->load_security($_SESSION['user']['UserId']);
							$_SESSION['user']['collections'] = $tmp['collections'];
							$_SESSION['user']['security'] = $tmp['security'];
						//	$sec->load_security();
							$_SESSION['user']['services'] = $sec->load_user_services($_SESSION['user']['UserId']);
						}
						$this->cleargroupinfos();
						$_SESSION['error'] = _GROUP_UPDATED;
						header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
						exit();
					}
				//}
		}
	}

	/**
	* Tests if the user belong to the group
	*
	* @param string $user user identifier
	* @param string $group group identifier
	*/
	public function in_group($user, $group)
	{
		$this->connect();
		$this->query("select user_id from ".$_SESSION['tablename']['usergroup_content']." where user_id ='".$user."' and group_id = '".$group."'");

		if($this->nb_result() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Clear the $_SESSION['m_admin']['groups'] variable
	*
	*/
	private function cleargroupinfos()
		{
			// clear the users add or modification vars
			unset($_SESSION['m_admin']);

		}

	/**
	* Add ou modify groups in the database
	*
	* @param string $id group identifier
	* @param string $mode up or add
	*/
	public function admingroup($id,$mode)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit();
		}
		else
		{
			$this->connect();
			$this->query("select group_id from ".$_SESSION['tablename']['usergroups']." where group_id = '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _GROUP.' '._UNKNWON;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
			else
			{
				if($mode == "allow")
				{
					$this->query("Update ".$_SESSION['tablename']['usergroups']." set enabled = 'Y' where group_id = '".$id."'");
					if($_SESSION['history']['usergroupsval'] == "true")
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['usergroups'], $id,"VAL",_GROUP_AUTORIZATION." : ".$id, $_SESSION['config']['databasetype']);
					}
					$_SESSION['error'] = _AUTORIZED_GROUP;

					if($this->in_group($_SESSION['user']['UserId'], $id))
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
						$_SESSION['user']['groups'] = array();
						$_SESSION['user']['security'] = array();
						$sec = new security();
						$tmp = $sec->load_groups($_SESSION['user']['UserId']);
						$_SESSION['user']['groups'] = $tmp['groups'];
						$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

						$tmp = $sec->load_security($_SESSION['user']['UserId']);
						$_SESSION['user']['collections'] = $tmp['collections'];
						$_SESSION['user']['security'] = $tmp['security'];
					}

				}
				elseif($mode == "ban")
				{
					$this->query("Update ".$_SESSION['tablename']['usergroups']." set enabled = 'N' where group_id = '".$id."'");
					if($_SESSION['history']['usergroupsban'] == "true")
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['usergroups'], $id,"BAN",_GROUP_SUSPENSION." : ".$id, $_SESSION['config']['databasetype']);
					}
					$_SESSION['error'] = _SUSPENDED_GROUP;

					if($this->in_group($_SESSION['user']['UserId'], $id))
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
						$_SESSION['user']['groups'] = array();
						$_SESSION['user']['security'] = array();
						$sec = new security();
						$tmp = $sec->load_groups($_SESSION['user']['UserId']);
						$_SESSION['user']['groups'] = $tmp['groups'];
						$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

						$tmp = $sec->load_security($_SESSION['user']['UserId']);
						$_SESSION['user']['collections'] = $tmp['collections'];
						$_SESSION['user']['security'] = $tmp['security'];
					}
				}
				elseif($mode == "del" )
				{
					$this->query("delete from ".$_SESSION['tablename']['usergroups']."  where group_id = '".$id."'");
					$this->query("delete from ".$_SESSION['tablename']['usergroup_content']."  where group_id = '".$id."'");
					$this->query("delete from ".$_SESSION['tablename']['security']."  where group_id = '".$id."'");
					$this->query("delete from ".$_SESSION['tablename']['usergroup_services']."  where group_id = '".$id."'");
					if($_SESSION['history']['usergroupsdel'] == "true")
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['usergroups'], $id,"DEL",_GROUP_DELETION." : ".$id, $_SESSION['config']['databasetype']);
					}
					$_SESSION['error'] = _DELETED_GROUP;

					if($this->in_group($_SESSION['user']['UserId'], $id))
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
						$_SESSION['user']['groups'] = array();
						$_SESSION['user']['security'] = array();
						$sec = new security();
						$tmp = $sec->load_groups($_SESSION['user']['UserId']);
						$_SESSION['user']['groups'] = $tmp['groups'];
						$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

						$tmp = $sec->load_security($_SESSION['user']['UserId']);
						$_SESSION['user']['collections'] = $tmp['collections'];
						$_SESSION['user']['security'] = $tmp['security'];

					}
				}

				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=groups&admin=groups&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}

}

?>
