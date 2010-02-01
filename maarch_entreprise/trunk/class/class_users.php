<?php
/**
* User Class
*
*  Contains all the functions to manage users
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class users: Contains all the functions and forms to manage users
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package  Maarch PeopleBox 1.0
* @version 2.1
*/

class users extends dbquery
{
	/**
	* Redefinition of the user object constructor : configure the SQL argument order by
	*/
	function __construct()
	{
		parent::__construct();
	}

	/**
	* To allow administrator to admin users
	*
	* @param integer $id user identifier
	* @param string $mode allow, ban or del
	*/
	public function adminuser($id,$mode)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];

		$core = new core_tools();
		// To allow administrator to admin users
		if(!empty($_SESSION['error']))
		{

			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit();
		}
		else
		{
			$this->connect();
			$this->query("select user_id, firstname, lastname from ".$_SESSION['tablename']['users']." where user_id = '".$id."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _USER.' '._UNKNOWN;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
			else
			{
				$info = $this->fetch_object();
				$theuser = $this->show_string($info->LastName." ".$info->FirstName);

				if($mode == "allow")
				{
					$this->query("Update ".$_SESSION['tablename']['users']." set enabled = 'Y' where user_id = '".$id."'");
					if($_SESSION['history']['usersval'] == "true")
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");

						$hist = new history();
						$hist->add($_SESSION['tablename']['users'], $id,"VAL",_USER_AUTORIZATION." ".$theuser, $_SESSION['config']['databasetype']);
					}
					$_SESSION['error'] = _AUTORIZED_USER;

				}
				elseif($mode == "ban")
				{
					$this->query("Update ".$_SESSION['tablename']['users']." set enabled = 'N' where user_id = '".$id."'");
					if($_SESSION['history']['usersban'] == "true")
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");

						$hist = new history();
						$hist->add($_SESSION['tablename']['users'], $id,"BAN",_USER_SUSPENSION." : ".$theuser, $_SESSION['config']['databasetype']);
					}
					$_SESSION['error'] = _SUSPENDED_USER;
				}
				elseif($mode == "del" )
				{
					$this->query("delete from ".$_SESSION['tablename']['users']."  where user_id = '".$id."'");
					if($core->is_module_loaded('basket'))
					{
						$this->query("delete from ".$_SESSION['tablename']['bask_users_abs']." where user_abs = '".$id."' or new_user = '".$id."' or basket_owner = '".$id."'");
					}
					if($_SESSION['history']['usersdel'])
					{
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['users'], $id,"DEL",_USER_DELETION." : ".$theuser, $_SESSION['config']['databasetype']);
					}
					$_SESSION['error'] = _DELETED_USER;
				}

				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}

	/**
	* Treats the information returned by the form of change_info_user().
	*
	*/
	public function user_modif()
	{
		$_SESSION['user']['FirstName'] = $this->wash($_POST['FirstName'], "no", _FIRSTNAME);
		$_SESSION['user']['LastName'] = $this->wash($_POST['LastName'], "no", _LASTNAME);
		$_SESSION['user']['pass1'] = $this->wash($_POST['pass1'], "no", _FIRST_PSW);
		$_SESSION['user']['pass2'] = $this->wash($_POST['pass2'], "no", _SECOND_PSW);

		if($_SESSION['user']['pass1'] <> $_SESSION['user']['pass2'])
		{
			$this->add_error(_WRONG_SECOND_PSW, '');
		}

		if(isset($_POST['Phone']) && !empty($_POST['Phone']))
		{
			$_SESSION['user']['Phone']  = $_POST['Phone'];
		}

		if(isset($_POST['Fonction']) && !empty($_POST['Fonction']))
		{
			$_SESSION['user']['Fonction']  = $_POST['Fonction'];
		}

		if(isset($_POST['Department']) && !empty($_POST['Department']))
		{
			$_SESSION['user']['department']  = $_POST['Department'];
		}

		if(isset($_POST['Mail']) && !empty($_POST['Mail']))
		{
			$_SESSION['user']['Mail']  = $_POST['Mail'];
		}
		if(empty($_SESSION['error']))
		{
			$tmp_fn = $this->protect_string_db($_SESSION['user']['FirstName']);
			$tmp_ln = $this->protect_string_db($_SESSION['user']['LastName']);
			$tmp_dep = $this->protect_string_db($_SESSION['user']['department']);
			$this->connect();
			$this->query("update ".$_SESSION['tablename']['users']." set password = '".md5($_SESSION['user']['pass1'])."', firstname = '".$_SESSION['user']['FirstName']."', lastname = '".$_SESSION['user']['LastName']."', phone = '".$_SESSION['user']['Phone']."', mail = '".$_SESSION['user']['Mail']."' , department = '".$_SESSION['user']['department']."' where user_id = '".$_SESSION['user']['UserId']."'");


			if($_SESSION['history']['usersup'] == "true")
			{
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$hist = new history();
				$hist->add($_SESSION['tablename']['users'], $_SESSION['user']['UserId'],"UP",_USER_UPDATE." : ".$_SESSION['user']['LastName']." ".$_SESSION['user']['FirstName'], $_SESSION['config']['databasetype']);
			}

			$_SESSION['error'] = _USER_UPDATED;
			header("location: ".$_SESSION['config']['businessappurl']."index.php");
			exit();
		}
		else
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=modify_user&admin=users");
			exit();
		}
	}

	/**
	* Form for the management of the current user.
	*
	*/
	public function change_info_user()
	{
		//require_once('core/class/class_core_tools.php');
		$core_tools = new core_tools();
		?>
		<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_user_b.gif" alt="" /> <?php  echo _MY_INFO; ?></h1>

		<div id="inner_content" class="clearfix">
			<div id="user_box" >
				<div class="block">
                 <h2 class="tit"><?php  echo _USER_GROUPS_TITLE;?> : </h2>
					 <ul id="my_profil">
                      <?php
						 	$this->connect();
							$this->query("SELECT u.group_desc FROM ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['usergroups']." u
							where uc.user_id ='".$_SESSION['user']['UserId']."' and uc.group_id = u.group_id order by u.group_desc");

							if($this->nb_result() < 1)
							{
								echo _USER_BELONGS_NO_GROUP.".";
							}
							else
							{
								while($line = $this->fetch_object())
								{

								 echo "<li>".$line->group_desc." </li>";
								}
							}
						 ?>
						 </ul>
						 <?php if($core_tools->is_module_loaded("entities") )
						{?>
						 <h2 class="tit"><?php  echo _USER_ENTITIES_TITLE;?> : </h2>
							<ul id="my_profil">
						 <?php
							$this->query("SELECT e.entity_label FROM ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['ent_entities']." e
							where ue.user_id ='".$_SESSION['user']['UserId']."' and ue.entity_id = e.entity_id order by e.entity_label");

							if($this->nb_result() < 1)
							{
								echo _USER_BELONGS_NO_ENTITY.".";
							}
							else
							{
								while($line = $this->fetch_object())
								{

								 echo "<li>".$line->entity_label." </li>";
								}
							}
						 ?>
						 </ul>
						 <?php }?>
                     </div>
                     <div class="block_end">&nbsp;</div>
                     </div>

                     	<form name="frmuser" id="frmuser" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=user_modif" class="forms addforms">
							<input type="hidden" name="display" value="true" />
							<input type="hidden" name="admin" value="users" />
							<input type="hidden" name="page" value="user_modif" />
						<div class="">
					<p>
						<label><?php  echo _ID; ?> : </label>
						<input name="UserId"  type="text" id="UserId" value="<?php  echo $_SESSION['user']['UserId']; ?>"  readonly="readonly" />
					   	<input type="hidden"  name="id" value="<?php  echo $_SESSION['user']['UserId']; ?>" />
					 </p>
					 <p>
					 	<label for="pass1"><?php  echo _PASSWORD; ?> : </label>
						<input name="pass1"  type="password" id="pass1"  value="" />
					</p>
					<p>
						<label for="pass2"><?php  echo _REENTER_PSW; ?> : </label>
						<input name="pass2"  type="password" id="pass2" value="" />
					 </p>
					  <p>
						<label for="LastName"><?php  echo _LASTNAME; ?> : </label>
						<input name="LastName"   type="text" id="LastName" size="45" value="<?php  echo $this->show_string($_SESSION['user']['LastName']); ?>" />
					</p>
					<p>
					  	<label for="FirstName"><?php  echo _FIRSTNAME; ?> : </label>
						<input name="FirstName"  type="text" id="FirstName" size="45" value="<?php  echo $this->show_string($_SESSION['user']['FirstName']); ?>" />
					 </p>
					 <?php if(!$core_tools->is_module_loaded("entities") )
						{?>
					  <p>
						<label for="Department"><?php  echo _DEPARTMENT;?> : </label>
							<input name="Department" id="Department" type="text"  disabled size="45" value="<?php  echo $this->show_string($_SESSION['user']['department']); ?>" />
						</p>
						<?php }?>
					  <p>
						<label for="Phone"><?php  echo _PHONE_NUMBER; ?> : </label>
						<input name="Phone"  type="text" id="Phone" value="<?php  echo $_SESSION['user']['Phone']; ?>" />
					  </p>
					 <p>
						<label for="Mail"><?php  echo _MAIL; ?> : </label>
						<input name="Mail"  type="text" id="Mail" size="45" value="<?php  echo $_SESSION['user']['Mail']; ?>" />
					  </p>

					  <p class="buttons">
							<input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button" />
							<input type="button" name="cancel" value="<?php  echo _CANCEL; ?>" class="button" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';" />
					</p>
					</div>

					</form>
					<div class="blank_space"></div>
				<?php

			//	require_once("core/class/class_core_tools.php");
				$core_tools = new core_tools;
				echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'modify_user.php', "include");
				?>
		</div>

	<?php

	}

	/**
	* Form to add or modify users
	*
	* @param string $mode up or add
	* @param integer $id user identifier, empty by default
	*/
	public function formuser($mode,$id = "")
	{
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroup_content.php");
		// the form to add or modify users
		$core = new core_tools();
		$state = true;
		$ugc = new usergroup_content();

		if(empty($_SESSION['error']))
		{
			$this->connect();
			$this->query("select count(*) as total from ".$_SESSION['tablename']['usergroups']." where enabled ='Y'");
			$nb_total_1 = $this->fetch_object();
			$_SESSION['m_admin']['nbgroups']  = $nb_total_1->total;
		}
		if($mode == "up")
		{
			$_SESSION['m_admin']['mode'] = "up";
			if(empty($_SESSION['error']))
			{
				$this->connect();
				$this->query("select * from ".$_SESSION['tablename']['users']." where user_id = '".$id."'");

				if($this->nb_result() == 0)
				{
					$_SESSION['error'] = _USER.' '._UNKNOWN;
					$state = false;
				}
				else
				{
					$line = $this->fetch_object();

					$_SESSION['m_admin']['users']['UserId'] = $line->user_id;
					$_SESSION['m_admin']['users']['FirstName'] = $this->show_string($line->firstname);
					$_SESSION['m_admin']['users']['LastName'] = $this->show_string($line->lastname);
					$_SESSION['m_admin']['users']['Phone'] = $line->phone;
					$_SESSION['m_admin']['users']['Mail'] = $line->mail;
					$_SESSION['m_admin']['users']['Department'] = $this->show_string($line->department);
					$_SESSION['m_admin']['users']['Enabled'] = $line->enabled;
					$_SESSION['m_admin']['users']['Status'] = $line->status;
				}

				if (($_SESSION['m_admin']['load_group'] == true || ! isset($_SESSION['m_admin']['load_group'] )) && $_SESSION['m_admin']['users']['UserId'] <> "superadmin")
				{
					$ugc->load_group_session($_SESSION['m_admin']['users']['UserId']);
				}
			}
		}
		elseif($mode == "add" )
		{
			$_SESSION['m_admin']['mode'] = "add";
			if ($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] ))
			{
				$ugc->init_session();
			}
		}

				if($mode == "add")
				{
					echo '<h1><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_user_b.gif" alt="" />'._USER_ADDITION.'</h1>';
				}
				elseif($mode == "up")
				{
					echo '<h1><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_user_b.gif" alt="" /> '._USER_MODIFICATION.'</h1>';
				}
				$_SESSION['service_tag'] = 'user_init';
				echo $core->execute_modules_services($_SESSION['modules_services'], 'user_up_init', "include");
				$_SESSION['service_tag'] = 'formuser';
				echo $core->execute_modules_services($_SESSION['modules_services'], 'formuser', "include");

				?>
                    <div id="add_box">
                    <p>
                    <?php  if($_SESSION['m_admin']['users']['UserId'] <> "superadmin")
					{?>
                    	<!--<iframe name="usergroups_content" id="usergroups_content" class="frameform2" src="<?php  echo $_SESSION['config']['businessappurl'].'admin/users/ugc_form.php';?>" frameborder="0"></iframe>-->
                    	<iframe name="usergroups_content" id="usergroups_content" class="frameform2" src="<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&admin=users&page=ugc_form';?>" frameborder="0"></iframe>
                     <?php  } ?>
                    </p>
				</div>
			<?php
			if($state == false)
			{
				echo "<br /><br /><br /><br />"._USER.' '._UNKNOWN."<br /><br /><br /><br />";
			}
			else
			{
				?>
				<form name="frmuser" id="frmuser" method="post" action="<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&admin=users&page=<?php  if($mode == "up") { echo "users_up_db";}elseif($mode=="add"){echo"users_add_db";}?>" class="forms addforms" style="width:300px">
					<input type="hidden" name="display" value="true" />
					<input type="hidden" name="admin" value="users" />
					<?php  if($mode == "up") {?>
					<input type="hidden" name="page" value="users_up_db" />
					<?php }
					elseif($mode == "add") { ?>
					<input type="hidden" name="page" value="users_add_db" />
					<?php } ?>
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<p>
					<label for="UserId"><?php  echo _ID; ?> :</label>
					<?php  if($mode == "up") { echo $this->show_string($_SESSION['m_admin']['users']['UserId']); }else{ echo '<br/>'; } ?><input name="UserId"  type="<?php  if($mode == "up") { ?>hidden<?php  } elseif($mode == "add") { ?>text<?php  } ?>" id="UserId" value="<?php  echo $this->show_string($_SESSION['m_admin']['users']['UserId']); ?>" /><span class="red_asterisk">*</span>

					<input type="hidden"  name="id" id="id" value="<?php  echo $id; ?>" />
				</p>
                <p>
					<label for="LastName"><?php  echo _LASTNAME; ?> :</label><br/>
					<input name="LastName" id="LastName"  type="text" value="<?php  echo $this->show_string($_SESSION['m_admin']['users']['LastName']); ?>" /><span class="red_asterisk">*</span>
				</p>
				<p>
					<label for="FirstName"><?php  echo _FIRSTNAME; ?> :</label><br/>
					<input name="FirstName" id="FirstName"  type="text" value="<?php  echo $this->show_string($_SESSION['m_admin']['users']['FirstName']); ?>" /><span class="red_asterisk">*</span>
				</p>
				<?php
				//require_once("core/class/class_core_tools.php");
				?>
				<p>
					<label for="Phone"><?php  echo _PHONE_NUMBER; ?> :</label><br/>
					<input name="Phone" id="Phone"  type="text" value="<?php  echo $_SESSION['m_admin']['users']['Phone']; ?>" />
				</p>
				<p>
					<label for="Mail"><?php  echo _MAIL; ?> :</label><br/>
					<input name="Mail" id="Mail"  type="text" value="<?php  echo $_SESSION['m_admin']['users']['Mail']; ?>" /><span class="red_asterisk">*</span>
				</p>
					<p class="buttons">
									<?php
						if($mode == "up")
						{
						?>

						<input type="button" name="reset_pwd" value="<?php  echo _RESET.' '._PASSWORD; ?>" class="button" onclick="window.open('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=psw_changed', '', 'toolbar=no,status=yes,width=400,height=150,left=500,top=300,scrollbars=no,top=no,location=no,resize=yes,menubar=no')" />
						<?php
						}
						elseif($mode == "add")
						{
						?>


						<?php
						}
						?><br/>
						<input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button"/>
						 <input type="button" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=users&amp;admin=users';"/>
								</p>
			</form>
				 <?php
				 //require_once("core/class/class_core_tools.php");
				$core_tools = new core_tools;
				echo $core_tools->execute_modules_services($_SESSION['modules_services'], 'users_up.php', "include");
				 ?>
					</div>

			<?php
			}

	}

	/**
	* Return the user information in sessions vars
	*
	* @param string $mode add or up
	*/
	public function usersinfo($mode)
	{
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroup_content.php");
		// return the user information in sessions vars

		if($mode == "add")
		{
			$_SESSION['m_admin']['users']['UserId'] = $this->wash($_POST['UserId'], "no", _THE_ID, 'yes', 0, 32);
			$_SESSION['m_admin']['users']['pass'] = md5('maarch');

		}

		if($mode == "up")
		{
			$_SESSION['m_admin']['users']['UserId'] = $this->wash($_POST['id'], "no", _THE_ID, 'yes', 0, 32);
		}

		$_SESSION['m_admin']['users']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['users']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['users']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['users']['start'] = $_REQUEST['start'];

		$_SESSION['m_admin']['users']['FirstName'] = $this->wash($_POST['FirstName'], "no", _THE_FIRSTNAME, 'yes', 0, 255);
		$_SESSION['m_admin']['users']['LastName'] = $this->wash($_POST['LastName'], "no", _THE_LASTNAME, 'yes', 0, 255);

		if(isset($_POST['Department']) && !empty($_POST['Department']))
		{
			$_SESSION['m_admin']['users']['Department']  = $this->wash($_POST['Department'], "no", _DEPARTMENT, 'yes', 0, 50);

		}

		if(isset($_POST['Phone']) && !empty($_POST['Phone']))
		{
			$_SESSION['m_admin']['users']['Phone']  = $this->wash($_POST['Phone'], "no", _PHONE, 'yes', 0, 15);
		}

		if(isset($_POST['Mail']) && !empty($_POST['Mail']))
		{
			$_SESSION['m_admin']['users']['Mail']  = $this->wash($_POST['Mail'], "mail", _MAIL, 'yes', 0, 255);
		}

		if($_SESSION['m_admin']['users']['UserId'] <> "superadmin")
		{
			$ugc = new usergroup_content();

			$primary_set = false;
			for($i=0; $i < count($_SESSION['m_admin']['users']['groups']);$i++)
			{
				if($_SESSION['m_admin']['users']['groups'][$i]['PRIMARY'] == 'Y')
				{
					$primary_set = true;
					break;
				}
			}

			if ($primary_set == false)
			{
				$ugc->add_error(_NOTE2, "");
			}
		}

		$_SESSION['service_tag'] = 'user_check';
		$core = new core_tools();
		echo $core->execute_modules_services($_SESSION['modules_services'], 'user_check', "include");

	}

	/**
	* Add ou modify users in the database
	*
	* @param string $mode up or add
	*/
	public function addupusers($mode)
	{
		// add ou modify users in the database
		$this->usersinfo($mode);
		$core = new core_tools();
		$order = $_SESSION['m_admin']['users']['order'];
		$order_field = $_SESSION['m_admin']['users']['order_field'];
		$what = $_SESSION['m_admin']['users']['what'];
		$start = $_SESSION['m_admin']['users']['start'];
		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['users']['UserId']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_up&id=".$_SESSION['m_admin']['users']['UserId']."&admin=users");
					exit();
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
				}
			}
			elseif($mode == "add")
			{
				$_SESSION['m_admin']['load_group'] = false;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_add&admin=users");
				exit();
			}
		}
		else
		{
			$this->connect();

			if($mode == "add")
			{
				
				if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
					$query = "select user_id from ".$_SESSION['tablename']['users']." where user_Id ilike '".$_SESSION['m_admin']['users']['UserId']."'";
				else
					$query = "select user_id from ".$_SESSION['tablename']['users']." where user_Id like '".$_SESSION['m_admin']['users']['UserId']."'";
				
 				$this->query($query);

				if($this->nb_result() > 0)
				{
					$_SESSION['error'] = _THE_USER." ".$_SESSION['m_admin']['users']['UserId']." "._ALREADY_EXISTS."<br />";

					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_add&admin=users");
					exit();
				}
				else
				{
				$cookie_date = '';
				//if($_SESSION['config']['databasetype'] == "POSTGRESQL" || $_SESSION['config']['databasetype'] == "MYSQL")
				//{
				$cookie_date = 'NULL';
				//}
				$tmp_fn = $this->protect_string_db($_SESSION['m_admin']['users']['FirstName']);
				$tmp_ln = $this->protect_string_db($_SESSION['m_admin']['users']['LastName']);
				$tmp_dep = $this->protect_string_db($_SESSION['m_admin']['users']['Department']);

					$this->query("INSERT INTO ".$_SESSION['tablename']['users']." (  user_id , password , firstname , lastname , phone , mail , department , cookie_key , cookie_date , enabled ) values ( '".$_SESSION['m_admin']['users']['UserId']."', '".$_SESSION['m_admin']['users']['pass']."', '".$tmp_fn."', '".$tmp_ln."', '".$_SESSION['m_admin']['users']['Phone']."', '".$_SESSION['m_admin']['users']['Mail']."', '".$tmp_dep."', '', ".$cookie_date.", 'Y')");


					require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroup_content.php");
					$ugc=new usergroup_content();
					$ugc->load_db();

					if($_SESSION['history']['usersadd'] == "true")
					{
						$tmp_h = $this->protect_string_db(_USER_ADDED." : ".$_SESSION['m_admin']['users']['LastName']." ".$_SESSION['m_admin']['users']['FirstName']);
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['UserId'],"ADD",$tmp_h, $_SESSION['config']['databasetype']);
					}
					$_SESSION['service_tag'] = 'users_add_db';
					echo $core->execute_modules_services($_SESSION['modules_services'], 'users_add_db.php', "include");
					$this->clearuserinfos();

					$_SESSION['error'] = _USER_ADDED;
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
				}
			}
			elseif($mode == "up")
			{
				$tmp_fn = $this->protect_string_db($_SESSION['m_admin']['users']['FirstName']);
				$tmp_ln = $this->protect_string_db($_SESSION['m_admin']['users']['LastName']);
				$tmp_dep = $this->protect_string_db($_SESSION['m_admin']['users']['Department']);
					$this->query("update ".$_SESSION['tablename']['users']." set firstname = '".$tmp_fn."', lastname = '".$tmp_ln."', phone = '".$_SESSION['m_admin']['users']['Phone']."', mail = '".$_SESSION['m_admin']['users']['Mail']."' , department = '".$tmp_dep."' where user_id = '".$_SESSION['m_admin']['users']['UserId']."'");

					if($_SESSION['m_admin']['users']['UserId'] <> "superadmin")
					{
						require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_usergroup_content.php");
						$ugc=new usergroup_content();
						$ugc->load_db();
					}
					if($_SESSION['history']['usersup'] == "true")
					{
						$tmp_h = $this->protect_string_db(_USER_UPDATE." : ".$_SESSION['m_admin']['users']['LastName']." ".$_SESSION['m_admin']['users']['FirstName']." (".$_SESSION['m_admin']['users']['UserId'].")");
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['UserId'],"UP",$tmp_h, $_SESSION['config']['databasetype']);
					}

					if( $_SESSION['m_admin']['users']['UserId'] == $_SESSION['user']['UserId'] && $_SESSION['user']['UserId'] <> "superadmin" )
					{
						$_SESSION['user']['groups'] = array();
						$_SESSION['user']['security'] = array();
						require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
						$sec = new security();
						$tmp = $sec->load_groups($_SESSION['user']['UserId']);
						$_SESSION['user']['groups'] = $tmp['groups'];
						$_SESSION['user']['primarygroup'] = $tmp['primarygroup'];

						$tmp = $sec->load_security($_SESSION['user']['UserId']);
						$_SESSION['user']['collections'] = $tmp['collections'];
						$_SESSION['user']['security'] = $tmp['security'];
					}
					$_SESSION['service_tag'] = 'users_up_db';
					echo $core->execute_modules_services($_SESSION['modules_services'], 'users_up_db.php', "include");
					$this->clearuserinfos();

					$_SESSION['error'] = _USER_UPDATED;
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users&admin=users&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
			}
		}
	}

	/**
	* Clear the users add or modification vars
	*/
	private function clearuserinfos()
	{
		// clear the users add or modification vars
		unset($_SESSION['m_admin']);
	}
}
?>
