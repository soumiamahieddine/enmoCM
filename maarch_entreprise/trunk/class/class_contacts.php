<?
/**
* Contacts Class
*
* Contains all the specific functions to manage Contacts
*
* @version 2.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Loïc Vinet  <dev@maarch.org>
*
*/

/**
* Class Contacts : Contains all the specific functions to manage Contacts
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @version 2.0
*/

class contacts extends dbquery
{
	/**
	* Return the contacts data in sessions vars
	*
	* @param string $mode add or up
	*/
	public function contactinfo($mode)
	{
		// return the user information in sessions vars
		$func = new functions();
		$_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON']  = $_REQUEST['is_corporate'];
		if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y')
		{
			$_SESSION['m_admin']['contact']['SOCIETY'] = $func->wash($_REQUEST['society'], "no", _SOCIETY." ", 'yes', 0, 255);
			$_SESSION['m_admin']['contact']['LASTNAME'] = '';
		}
		else
		{
			$_SESSION['m_admin']['contact']['LASTNAME'] = $func->wash($_REQUEST['lastname'], "no", _LASTNAME, 'yes', 0, 255);
			if ($_REQUEST['society'] <> '')
			{
				$_SESSION['m_admin']['contact']['SOCIETY'] = $func->wash($_REQUEST['society'], "no", _SOCIETY." ", 'yes', 0, 255);
			}
			else
			{
				$_SESSION['m_admin']['contact']['SOCIETY'] = '';
			}
		}
		if ($_REQUEST['title'] <> '')
		{
			$_SESSION['m_admin']['contact']['TITLE'] = $func->wash($_REQUEST['title'], "no", _TITLE2." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['TITLE'] = '';
		}

		if ($_REQUEST['firstname'] <> '')
		{
			$_SESSION['m_admin']['contact']['FIRSTNAME'] = $func->wash($_REQUEST['firstname'], "no", _FIRSTNAME." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['FIRSTNAME'] = '';
		}

		if ($_REQUEST['function'] <> '')
		{
			$_SESSION['m_admin']['contact']['FUNCTION'] = $func->wash($_REQUEST['function'], "no", _FUNCTION." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['FUNCTION'] = '';
		}

		if ($_REQUEST['num'] <> '')
		{
			$_SESSION['m_admin']['contact']['ADD_NUM'] = $func->wash($_REQUEST['num'], "no", _NUM." ", 'yes', 0, 32);
		}
		else
		{
			$_SESSION['m_admin']['contact']['ADD_NUM'] = '';
		}

		if ($_REQUEST['street'] <> '')
		{
			$_SESSION['m_admin']['contact']['ADD_STREET'] = $func->wash($_REQUEST['street'], "no", _STREET." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['ADD_STREET'] = '';
		}

		if ($_REQUEST['add_comp'] <> '')
		{
			$_SESSION['m_admin']['contact']['ADD_COMP'] = $func->wash($_REQUEST['add_comp'], "no", ADD_COMP." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['ADD_COMP'] = '';
		}

		if ($_REQUEST['town'] <> '')
		{
			$_SESSION['m_admin']['contact']['ADD_TOWN'] = $func->wash($_REQUEST['town'], "no", _TOWN." ", 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['ADD_TOWN'] = '';
		}
		if ($_REQUEST['cp'] <> '')
		{
			$_SESSION['m_admin']['contact']['ADD_CP'] = $func->wash($_REQUEST['cp'], "no", _POSTAL_CODE, 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['ADD_CP'] = '';
		}
		if ($_REQUEST['country'] <> '')
		{
			$_SESSION['m_admin']['contact']['ADD_COUNTRY'] = $func->wash($_REQUEST['country'], "no", _COUNTRY, 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['ADD_COUNTRY'] = '';
		}
		if ($_REQUEST['phone'] <> '')
		{
			$_SESSION['m_admin']['contact']['PHONE'] = $func->wash($_REQUEST['phone'], "num", _PHONE, 'yes', 0, 20);
		}
		else
		{
			$_SESSION['m_admin']['contact']['PHONE'] = '';
		}
		if ($_REQUEST['mail'] <> '')
		{
			$_SESSION['m_admin']['contact']['MAIL'] = $func->wash($_REQUEST['mail'], "mail", _MAIL, 'yes', 0, 255);
		}
		else
		{
			$_SESSION['m_admin']['contact']['MAIL'] = '';
		}
		if ($_REQUEST['comp_data'] <> '')
		{
			$_SESSION['m_admin']['contact']['OTHER_DATA'] = $func->wash($_REQUEST['comp_data'], "no", _COMP_DATA);
		}
		else
		{
			$_SESSION['m_admin']['contact']['OTHER_DATA'] = '';
		}

		if ($_REQUEST['owner'] <> '')
		{
			if(preg_match('/\((\s|\d|\h|\w)+\)$/i', $_REQUEST['owner']) == 0)
			{
				$_SESSION['error'] = _OWNER." "._WRONG_FORMAT.".<br/>"._USE_AUTOCOMPLETION;
			}
			else
			{
				$_SESSION['m_admin']['contact']['OWNER'] = str_replace(')', '', substr($_REQUEST['owner'], strrpos($_REQUEST['owner'],'(')+1));
				$_SESSION['m_admin']['contact']['OWNER'] = $func->wash($_SESSION['m_admin']['contact']['OWNER'], "no", _OWNER." ", 'yes', 0, 32);
			}
		}
		else
		{
			$_SESSION['m_admin']['contact']['OWNER'] = '';
		}

		$_SESSION['m_admin']['contact']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['contact']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['contact']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['contact']['start'] = $_REQUEST['start'];
	}

	/**
	* Add ou modify contact in the database
	*
	* @param string $mode up or add
	*/
	public function addupcontact($mode, $admin = true)
	{
		// add ou modify users in the database
		$this->contactinfo($mode);
		$order = $_SESSION['m_admin']['contact']['order'];
		$order_field = $_SESSION['m_admin']['contact']['order_field'];
		$what = $_SESSION['m_admin']['contact']['what'];
		$start = $_SESSION['m_admin']['contact']['start'];

		$path_contacts = $_SESSION['config']['businessappurl']."index.php?page=contacts&admin=contacts&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
		$path_contacts_add_errors = $_SESSION['config']['businessappurl']."index.php?page=contact_add&admin=contacts";
		$path_contacts_up_errors = $_SESSION['config']['businessappurl']."index.php?page=contact_up&admin=contacts";
		if(!$admin)
		{
			$path_contacts = $_SESSION['config']['businessappurl']."index.php?page=my_contacts&dir=my_contacts&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
			$path_contacts_add_errors = $_SESSION['config']['businessappurl']."index.php?page=my_contact_add&dir=my_contacts";
			$path_contacts_up_errors = $_SESSION['config']['businessappurl']."index.php?page=my_contact_up&dir=my_contacts";
		}
		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['contact']['ID']))
				{
					header("location: ".$path_contacts_up_errors."&id=".$_SESSION['m_admin']['contact']['ID']);
					exit;
				}
				else
				{
					header("location: ".$path_contacts);
					exit;
				}
			}
			if($mode == "add")
			{
				header("location: ".$path_contacts_add_errors);
				exit;
			}
		}
		else
		{
			$this->connect();
			if($mode == "add")
			{
				if($admin)
				{
					$query = "INSERT INTO ".$_SESSION['tablename']['contacts']." (  lastname , firstname , society , function , phone , email , address_num, address_street, address_complement, address_town, address_postal_code, address_country, other_data, title, is_corporate_person) VALUES (  '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['PHONE'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['MAIL'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_NUM'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_STREET'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COMP'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_TOWN'])."',  '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_CP'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COUNTRY'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['OTHER_DATA'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['TITLE'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])."' )";
				}
				else
				{
					$query = "INSERT INTO ".$_SESSION['tablename']['contacts']." (  lastname , firstname , society , function , phone , email , address_num, address_street, address_complement, address_town, address_postal_code, address_country, other_data, title, is_corporate_person, user_id) VALUES (  '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['PHONE'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['MAIL'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_NUM'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_STREET'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COMP'])."', '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_TOWN'])."',  '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_CP'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COUNTRY'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['OTHER_DATA'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['TITLE'])."','".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])."', '".$this->protect_string_db($_SESSION['user']['UserId'])."')";
				}
				$this->query($query);
				if($_SESSION['history']['contactadd'])
				{
					$this->query("select contact_id from ".$_SESSION['tablename']['contacts']." where lastname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])."' and firstname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])."' and society = '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])."' and function = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])."' and phone = '".$this->protect_string_db($_SESSION['m_admin']['contact']['PHONE'])."' and email = '".$this->protect_string_db($_SESSION['m_admin']['contact']['MAIL'])."' and address_num = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_NUM'])."' and address_street = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_STREET'])."' and address_complement = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COMP'])."' and address_town = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_TOWN'])."' and address_postal_code = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_CP'])."' and address_country = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COUNTRY'])."' and other_data = '".$this->protect_string_db($_SESSION['m_admin']['contact']['OTHER_DATA'])."' and title = '".$this->protect_string_db($_SESSION['m_admin']['contact']['TITLE'])."' and is_corporate_person = '".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])."'");
					$res = $this->fetch_object();
					$id = $res->contact_id;
					if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y')
					{
						$msg = 	_CONTACT_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY']);
					}
					else
					{
						$msg = 	_CONTACT_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'].' '.$_SESSION['m_admin']['contact']['FIRSTNAME']);
					}
					require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['contacts'], $id,"ADD",$msg, $_SESSION['config']['databasetype']);
				}
				$this->clearcontactinfos();
				$_SESSION['error'] = _CONTACT_ADDED;
				header("location: ".$path_contacts);
				exit;
			}
			elseif($mode == "up")
			{
				$query = "update ".$_SESSION['tablename']['contacts']." set lastname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])."', firstname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])."',society = '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])."',function = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])."',phone = '".$this->protect_string_db($_SESSION['m_admin']['contact']['PHONE'])."', email = '".$this->protect_string_db($_SESSION['m_admin']['contact']['MAIL'])."', address_num = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_NUM'])."', address_street = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_STREET'])."', address_complement = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COMP'])."', address_town = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_TOWN'])."', address_postal_code = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_CP'])."', address_country = '".$this->protect_string_db($_SESSION['m_admin']['contact']['ADD_COUNTRY'])."', other_data = '".$this->protect_string_db($_SESSION['m_admin']['contact']['OTHER_DATA'])."', title = '".$this->protect_string_db($_SESSION['m_admin']['contact']['TITLE'])."', is_corporate_person = '".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])."'";
				if($admin)
				{
					$query .= ", user_id = '".$this->protect_string_db($_SESSION['m_admin']['contact']['OWNER'])."'";
				}
				$query .=" where contact_id = '".$_SESSION['m_admin']['contact']['ID']."' and enabled = 'Y'";
				if(!$admin)
				{
					$query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
				}
				$this->query($query);
				if($_SESSION['history']['contactup'])
				{
					if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y')
					{
						$msg = 	_CONTACT_MODIFIED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY']);
					}
					else
					{
						$msg = 	_CONTACT_MODIFIED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'].' '.$_SESSION['m_admin']['contact']['FIRSTNAME']);
					}
					require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['contacts'], $_SESSION['m_admin']['contact']['ID'],"UP",$msg, $_SESSION['config']['databasetype']);
				}
				$this->clearcontactinfos();
				$_SESSION['error'] = _CONTACT_MODIFIED;
				header("location: ".$path_contacts);
				exit();
			}
		}
	}

	/**
	* Form to modify a contact
	*
	* @param  $string $mode up or add
	* @param int  $id  $id of the contact to change
	*/
	public function formcontact($mode,$id = "", $admin = true)
	{
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
			$display_value = 'table-row';
		}
		$func = new functions();
		$state = true;
		if(!isset($_SESSION['m_admin']['contact']))
		{
			$this->clearcontactinfos();
		}
		if( $mode <> "add")
		{
			$this->connect();
			$query = "select * from ".$_SESSION['tablename']['contacts']." where contact_id = ".$id." and enabled = 'Y'";
			if(!$admin)
			{
				$query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
			}
			$this->query($query);

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _THE_CONTACT.' '._ALREADY_EXISTS;
				$state = false;
			}
			else
			{
				$_SESSION['m_admin']['contact'] = array();
				$line = $this->fetch_object();
				$_SESSION['m_admin']['contact']['ID'] = $line->contact_id;
				$_SESSION['m_admin']['contact']['TITLE'] = $this->show_string($line->title);
				$_SESSION['m_admin']['contact']['LASTNAME'] = $this->show_string($line->lastname);
				$_SESSION['m_admin']['contact']['FIRSTNAME'] = $this->show_string($line->firstname);
				$_SESSION['m_admin']['contact']['SOCIETY'] = $this->show_string($line->society);
				$_SESSION['m_admin']['contact']['FUNCTION'] = $this->show_string($line->function);
				$_SESSION['m_admin']['contact']['ADD_NUM'] = $this->show_string($line->address_num);
				$_SESSION['m_admin']['contact']['ADD_STREET'] = $this->show_string($line->address_street);
				$_SESSION['m_admin']['contact']['ADD_COMP'] = $this->show_string($line->address_complement);
				$_SESSION['m_admin']['contact']['ADD_TOWN'] = $this->show_string($line->address_town);
				$_SESSION['m_admin']['contact']['ADD_CP'] = $this->show_string($line->address_postal_code);
				$_SESSION['m_admin']['contact']['ADD_COUNTRY'] = $this->show_string($line->address_country);
				$_SESSION['m_admin']['contact']['PHONE'] = $this->show_string($line->phone);
				$_SESSION['m_admin']['contact']['MAIL'] = $this->show_string($line->email);
				$_SESSION['m_admin']['contact']['OTHER_DATA'] = $this->show_string($line->other_data);
				$_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] = $this->show_string($line->is_corporate_person);
				$_SESSION['m_admin']['contact']['OWNER'] = $line->user_id;
				if(admin && !empty($_SESSION['m_admin']['contact']['OWNER']))
				{
					$this->query("select lastname, firstname from ".$_SESSION['tablename']['users']." where user_id = '".$_SESSION['m_admin']['contact']['OWNER']."'");
					$res = $this->fetch_object();
					$_SESSION['m_admin']['contact']['OWNER'] = $res->lastname.', '.$res->firstname.' ('.$_SESSION['m_admin']['contact']['OWNER'].')';
				}
			}
		}
		else if($mode == 'add')
		{
			$_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] = 'Y';
		}
		require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
		$business = new business_app_tools();
		$tmp = $business->get_titles();
		$titles = $tmp['titles'];
		?>
		<h1><img src="<? echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif" alt="" />
			<?php
			if($mode == "up")
			{
				echo _MODIFY_CONTACT;
			}
			elseif($mode == "add")
			{
				echo _ADD_CONTACT;
			}
			?>
		</h1>
		<div id="inner_content" class="clearfix" align="center">
			<?php
			if($state == false)
			{
				echo "<br /><br /><br /><br />"._THE_CONTACT." "._UNKOWN."<br /><br /><br /><br />";
			}
			else
			{
				$action = $_SESSION['config']['businessappurl']."index.php?display=true&admin=contacts&page=contact_up_db";
				if(!$admin)
				{
					$action = $_SESSION['config']['businessappurl']."index.php?display=true&dir=my_contacts&page=my_contact_up_db";
				}
				?>
				<form name="frmcontact" id="frmcontact" method="post" action="<? echo $action;?>" class="forms">
					<input type="hidden" name="display"  value="true" />
					<?php if(!$admin)
					{?>
						<input type="hidden" name="dir"  value="my_contacts" />
						<input type="hidden" name="page"  value="my_contact_up_db" />
				<?php	}
					else
					{?>
						<input type="hidden" name="admin"  value="contacts" />
						<input type="hidden" name="page"  value="contact_up_db" />
				<?php	}?>
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<table width="75%" >
				<?php if($admin && $mode == "up")
				{
					?>
					<tr>
						<td>
							<label for="owner"><?php echo _OWNER; ?> : </label>
						</td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="owner" type="text"  id="owner" value="<?php echo $func->show($_SESSION['m_admin']['contact']['OWNER']); ?>"/><div id="show_user" class="autocomplete"></div>
						</td>
						<td>&nbsp;</td>
					</tr>
					<?php
				}?>
					<tr>
						<td><label for="is_corporate"><?php echo _IS_CORPORATE_PERSON; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input type="radio"  class="check" name="is_corporate"  value="Y" <? if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){?> checked="checked"<? } ?>/ onClick="javascript:show_admin_contacts( true, '<?echo $display_value;?>');"><? echo _YES;?>
							<input type="radio"  class="check" name="is_corporate" value="N" <? if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'N'){?> checked="checked"<? } ?> onClick="javascript:show_admin_contacts( false, '<?echo $display_value;?>');"/><? echo _NO;?>
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr id="title_p" style="display:<? if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
						<td><label for="title"><?php echo _TITLE2; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><select name="title" id="title" >
							<option value=""><?php echo _CHOOSE_TITLE;?></option>
							<?php
							foreach(array_keys($titles) as $key)
							{
								?><option value="<?php echo $key;?>" <?php
								if(((!isset($_SESSION['m_admin']['contact']['TITLE']) || empty($_SESSION['m_admin']['contact']['TITLE']) ) && $key == $default_title) || ($key == $_SESSION['m_admin']['contact']['TITLE'] ))
								{
									echo 'selected="selected"';
								}
								?>><?php echo $titles[$key];?></option><?php
							}?>
						</select></td>
						<td>&nbsp;</td>
					</tr>
					<tr id="lastname_p" style="display:<? if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
						<td><label for="lastname"><?php echo _LASTNAME; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="lastname" type="text"  id="lastname" value="<?php echo $func->show($_SESSION['m_admin']['contact']['LASTNAME']); ?>"/></td>
						<td><span id="lastname_mandatory" class="red_asterisk" style="visibility:hidden;">*</span></td>
					</tr>
					<tr id="firstname_p" style="display:<? if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
						<td><label for="firstname"><?php echo _FIRSTNAME; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="firstname" type="text"  id="firstname" value="<?php echo $func->show($_SESSION['m_admin']['contact']['FIRSTNAME']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="society"><?php echo _SOCIETY; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="society" type="text"  id="society" value="<?php echo $func->show($_SESSION['m_admin']['contact']['SOCIETY']); ?>"/></td>
						<td class="indexing_field"><span class="red_asterisk" style="visibility:visible;" id="society_mandatory">*</span></td>
					</tr>
					<tr id="function_p" style="display:<? if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
						<td><label for="function"><?php echo _FUNCTION; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="function" type="text"  id="function" value="<?php echo $func->show($_SESSION['m_admin']['contact']['FUNCTION']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr >
						<td><label for="phone"><?php echo _PHONE; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="phone" type="text"  id="phone" value="<?php echo $func->show($_SESSION['m_admin']['contact']['PHONE']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="mail"><?php echo _MAIL; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="mail" type="text" id="mail" value="<?php echo $func->show($_SESSION['m_admin']['contact']['MAIL']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="4"><label><b><? echo _ADDRESS;?> </b></label></td>
					</tr>
					<tr>
						<td><label for="num"><?php echo _NUM; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="num" type="text"  id="num" value="<?php echo $func->show($_SESSION['m_admin']['contact']['ADD_NUM']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="street"><?php echo _STREET; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="street" type="text"  id="street" value="<?php echo $func->show($_SESSION['m_admin']['contact']['ADD_STREET']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="add_comp"><?php echo _COMPLEMENT; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="add_comp" type="text"  id="add_comp" value="<?php echo $func->show($_SESSION['m_admin']['contact']['ADD_COMP']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="town"><?php echo _TOWN; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="town" type="text" id="town" value="<?php echo $func->show($_SESSION['m_admin']['contact']['ADD_TOWN']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="cp"><?php echo _POSTAL_CODE; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="cp" type="text" id="cp" value="<?php echo $func->show($_SESSION['m_admin']['contact']['ADD_CP']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><label for="country"><?php echo _COUNTRY; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><input name="country" type="text"  id="country" value="<?php echo $func->show($_SESSION['m_admin']['contact']['ADD_COUNTRY']); ?>"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="4"><label><b><? echo _COMP;?> </b></label></td>
					</tr>
					<tr>
						<td><label for="comp_data"><?php echo _COMP_DATA; ?> : </label></td>
						<td>&nbsp;</td>
						<td class="indexing_field"><textarea name="comp_data"   id="comp_data"><?php echo $func->show($_SESSION['m_admin']['contact']['OTHER_DATA']); ?></textarea></td>
						<td>&nbsp;</td>
					</tr>
				</table>
						<input name="mode" type="hidden" value="<? echo $mode; ?>" />
					<p class="buttons">
					<?php

					if($mode == "up")
					{
						?>
						<input class="button" type="submit" name="Submit" value="<?php echo _MODIFY_CONTACT; ?>" />
						<?php
					}
					elseif($mode == "add")
					{
						?>
						<input type="submit" class="button"  name="Submit" value="<?php echo _ADD_CONTACT; ?>" />
						<?
					}
					$cancel_target = $_SESSION['config']['businessappurl'].'index.php?page=contacts&amp;admin=contacts';
					if(!$admin)
					{
						$cancel_target = $_SESSION['config']['businessappurl'].'index.php?page=my_contacts&amp;dir=my_contacts';
					}
					?>
					<input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $cancel_target;?>';" />
					</p>
				</form>
			<?php
				if($mode=="up" && $admin)
				{
					?><script type="text/javascript">launch_autocompleter('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=users_autocomplete_list', 'owner', 'show_user');</script><?php
				}
			}
			?>
		</div>
	<?php
	}

	/**
	* Clear the session variables of the edmit 's administration
	*
	*/
	private function clearcontactinfos()
	{
		// clear the session variable
		unset($_SESSION['m_admin']);
	}

	/**
	* delete a model in the database
	*
	* @param string $id model identifier
	*/
	public function delcontact($id, $admin = true)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		$path_contacts = $_SESSION['config']['businessappurl']."index.php?page=contacts&admin=contacts&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
		if(!$admin)
		{
			$path_contacts = $_SESSION['config']['businessappurl']."index.php?page=my_contacts&dir=my_contacts&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
		}
		if(!empty($_SESSION['error']))
		{
			header("location: ".$path_contacts);
			exit;
		}
		else
		{
			$this->connect();
			$query = "select contact_id from ".$_SESSION['tablename']['contacts']." where enabled ='Y' and contact_id = ".$id;
			if(!$admin)
			{
				$query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
			}
			$this->query($query);
			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _THE_CONTACT.' '._UNKNOWN;
				header("location: ".$path_contacts);
				exit;
			}
			else
			{
				$res = $this->fetch_object();
				$label = $res->LABEL;
				$this->query("update ".$_SESSION['tablename']['contacts']." set enabled = 'N' where contact_id = ".$id);
				if($_SESSION['history']['contactdel'])
				{
					require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['contacts'], $id,"DEL",_CONTACT_DELETED.' : '.$id, $_SESSION['config']['databasetype']);
				}
				$_SESSION['error'] = _CONTACT_DELETED;
				header("location: ".$path_contacts);
				exit;
			}
		}
	}
	
	function get_contact_information($res_id, $category_id,$view )
	//Get contact full information for each case: incoming document or outgoing document
	{
		$stopthis=false;
		$column_title = false;
		$column_value = false;
		$column_join = false;


		$this->connect();
		//For this 3 cases, we need to create a different string

		if ($category_id == 'incoming')
		{

			$prefix = "<b>"._TO_CONTACT_C."</b>";
			$this->query("SELECT exp_user_id, exp_contact_id from ".$view." WHERE res_id = ".$res_id);

			$compar = $this->fetch_object();


			if ($compar->exp_user_id <> '')
			{

				$column_title = "user_id";
				$column_value = $compar->exp_user_id;
				$column_join = $_SESSION['tablename']['users'];
			}
			elseif ($compar->exp_contact_id <> '')
			{

				$column_title = "contact_id";
				$column_value = $compar->exp_contact_id;
				$column_join = $_SESSION['tablename']['contacts'];
			}
			else
			{
				$stopthis = true;
			}
		}
		elseif ($category_id == 'outgoing'  || $category_id == 'internal')
		{
				$prefix = "<b>"._FOR_CONTACT_C."</b>";

				$this->query("SELECT dest_user_id, dest_contact_id from ".$view." WHERE res_id = ".$res_id);

				$compar = $this->fetch_object();
				if ($compar->dest_user_id <> '')
				{

					$column_title = "user_id";
					$column_value = $compar->dest_user_id;
					$column_join = $_SESSION['tablename']['users'];
				}
				elseif ($compar->dest_contact_id <> '')
				{

					$column_title = "contact_id";
					$column_value = $compar->dest_contact_id;
					$column_join = $_SESSION['tablename']['contacts'];
				}
				else
				{
					$stopthis = true;
				}
		}
		else
		{
			 $stopthis = true;
		}
		if($stopthis == true)
		{
			return false;
		}

		//If we need to find a contact, get the society first
		if ($column_join == $_SESSION['tablename']['contacts'])
			$fields = 'c.firstname, c.lastname, c.society ';
		elseif ($column_join == $_SESSION['tablename']['users'])
			$fields = 'c.firstname, c.lastname';
		else
			$fields = '';

		//Launching request to restore full contact string
		$this->query("SELECT ".$fields." from ".$column_join." c 	where ".$column_title." = '".$column_value."'");

		$final = $this->fetch_object();

		$firstname = $final->firstname;
		$lastname = $final->lastname;
		if ($final->society <> '')
		{
			if ($firstname =='' && $lastname == '')
			{
				$society = $final->society;
			}
			else
			{
				$society = " (".$final->society.") ";
			}
		}
		else
			$society = "";

		$the_contact =$prefix." ".$firstname." ".$lastname." ".$society;
		return $the_contact;

	}
	
	function get_contact_information_from_view($category_id, $contact_lastname="", $contact_firstname="", $contact_society="", $user_lastname="", $user_firstname="")
	{
		if ($category_id == 'incoming')
		{
			$prefix = "<b>"._TO_CONTACT_C."</b>";
		}
		elseif ($category_id == 'outgoing'  || $category_id == 'internal')
		{
			$prefix = "<b>"._FOR_CONTACT_C."</b>";
		}
		if($contact_lastname <> "")
		{
			$lastname = $contact_lastname;
			$firstname = $contact_firstname;
		}
		else
		{
			$lastname = $user_lastname;
			$firstname = $user_firstname;
		}
		if($contact_society <> "")
		{
			if ($firstname =='' && $lastname == '')
			{
				$society = $contact_society;
			}
			else
			{
				$society = " (".$contact_society.") ";
			}
		}
		else
			$society = "";
		$the_contact =$prefix." ".$firstname." ".$lastname." ".$society;
		return $the_contact;
	}
}
?>
