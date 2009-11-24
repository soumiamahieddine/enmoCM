<?php

/**
*  Users_entities class
*
* Contains all the functions to manage entities and users through session variables
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/

class users_entities extends dbquery
{

	public function service_load_entities($mode)
	{
		$this->connect();
		$this->query("select count(*) as total from ".$_SESSION['tablename']['ent_entities']." where enabled ='Y'");
		$nb_total_1 = $this->fetch_object();
		$_SESSION['m_admin']['nbentities']  = $nb_total_1->total;

		if($mode == 'up')
		{
			if (($_SESSION['m_admin']['load_entities'] == true || ! isset($_SESSION['m_admin']['load_entities'] )) && $_SESSION['m_admin']['users']['UserId'] <> "superadmin")
			{
				$this->load_entities_session($_SESSION['m_admin']['users']['UserId']);
			}
		}
		else
		{
			$_SESSION['m_admin']['entity'] = array();
		}
	}

	/**
	* Loads in the session variables the entities of the user passed in parameter
	*
	* @param 	string  $user_id user identifier
	*/
	public function load_entities_session($user_id)
	{
		$this->connect();
		$this->query("select  ue.entity_id, ue.primary_entity, ue.user_role, e.entity_label from ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['ent_entities']." e where ue.user_id = '".$this->protect_string_db(trim($user_id))."' and ue.entity_id = e.entity_id");
		if($this->nb_result() == 0)
		{
			$_SESSION['m_admin']['entity']['entities'] = array();
		}
		else
		{
			$entitytab = array();
			while($res = $this->fetch_object())
			{
				array_push($entitytab, array("USER_ID" => $user_id,"ENTITY_ID" => $res->entity_id, "LABEL" => $this->show_string($res->entity_label), "PRIMARY" => $res->primary_entity, "ROLE" => $this->show_string($res->user_role) ));
			}
			$_SESSION['m_admin']['entity']['entities'] = $entitytab;

		}
		$_SESSION['m_admin']['load_entities']  = false;
	}

	/**
	* Removes the entity on the tables passed in parameters for the user.
	*
	* @param array $tab
	*/
	public function remove_session($tab)
	{
		$tabtmp = array();
		for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
		{
			if( !in_array($_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID'], $tab))
			{
				array_push($tabtmp, $_SESSION['m_admin']['entity']['entities'][$i]);
			}
		}

		$_SESSION['m_admin']['entity']['entities'] = array();
		$_SESSION['m_admin']['entity']['entities'] = $tabtmp;

	}

	/**
	* No entity is the primary entity for the user.
	*
	*/
	public function erase_primary_entity_session()
	{
		for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
		{
			$_SESSION['m_admin']['entity']['entities'][$i]["PRIMARY"] = 'N';
		}

	}

	/**
	* Set the primary entity for a user in the session variables.
	*
	* @param 	string  $entity_id entity identifier
	*/
	public function set_primary_entity_session($entity_id)
	{
		for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
		{
			if ( $_SESSION['m_admin']['entity']['entities'][$i]["ENTITY_ID"] == $entity_id)
			{
				$_SESSION['m_admin']['entity']['entities'][$i]["PRIMARY"] = 'Y';
				break;
			}
		}
	}


	/**
	* Adds an entity in the session variables related to the user_entities administration
	*
	* @param 	string  $entity_id entity identifier
	* @param 	string  $role role in the entity (empty by default)
	* @param 	string  $label label of the entity
	*/
	public function add_usertmp_to_entity_session($entity_id, $role = "", $label)
	{
		$tab = array();
		$tab = array("USER_ID" => "", "ENTITY_ID" => $entity_id , "LABEL" => $this->show_string($label), "PRIMARY" => 'N', "ROLE" => $this->show_string($role) );
		array_push($_SESSION['m_admin']['entity']['entities'], $tab);
	}


	/**
	* Put in an array ($tmparray) the identifiers of all children of an entity
	*
	* @param 	string  $entity_id entity identifier
	* @param 	array  $tmparray the array who receive the children
	*/
	public function getEntityChildren($entity_id)
	{
		$this->connect();
		static $tmparray = array();

		$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($entity_id))."'");
		if($this->nb_result() > 0)
		{
			while($line = $this->fetch_object())
			{
				array_push($tmparray, $line->entity_id);
				$count++;

				$userEnt = new users_entities();
				$userEnt->connect();
				$userEnt->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($line->entity_id))."'");
				if($userEnt->nb_result() > 0)
				{
					$userEnt->getEntityChildren($line->entity_id, $tmparray);
				}
			}
		}
		return $tmparray;
	}


	/**
	* Form to add or modify users - entities relations
	*
	* @param string $mode up or add
	* @param integer $id user identifier, empty by default
	*/
	public function formuserentities($mode, $id = "")
	{
		// the form to add or modify users
		$func = new functions();

		$state = true;
		if(empty($_SESSION['error']))
		{
			$this->connect();
			$this->query("select count(*) as total from ".$_SESSION['tablename']['ent_entities']." where enabled ='Y'");
			$nb_total_1 = $this->fetch_object();
			$_SESSION['m_admin']['nbentities']  = $nb_total_1->total;
		}
		if($mode == "up")
		{
			$_SESSION['m_admin']['mode'] = "up";
			if(empty($_SESSION['error']))
			{
				$this->connect();
				$this->query("select * from ".$_SESSION['tablename']['users']." where user_id = '".$this->protect_string_db(trim($id))."'");

				if($this->nb_result() == 0)
				{
					$_SESSION['error'] = _USER.' '._UNKNOWN;
					$state = false;
				}
				else
				{
					$line = $this->fetch_object();

					$_SESSION['m_admin']['entity']['user_UserId'] = $line->user_id;
					$_SESSION['m_admin']['entity']['user_FirstName'] = $this->show_string($line->firstname);
					$_SESSION['m_admin']['entity']['user_LastName'] = $this->show_string($line->lastname);
					$_SESSION['m_admin']['entity']['user_Phone'] = $line->phone;
					$_SESSION['m_admin']['entity']['user_Mail'] = $line->mail;
					$_SESSION['m_admin']['entity']['user_Department'] = $this->show_string($line->department);
					$_SESSION['m_admin']['entity']['user_Status'] = $line->enabled;

				}

				if (($_SESSION['m_admin']['load_entities'] == true || ! isset($_SESSION['m_admin']['load_entities'] )) && $_SESSION['m_admin']['entity']['user_UserId'] <> "superadmin")
				{
					$this->load_entities_session($_SESSION['m_admin']['entity']['user_UserId']);
				}
			}
		}

		if($mode == "up")
		{
			echo '<h1><img src="'.$_SESSION['urltomodules'].'entities/img/manage_users_entities_b.gif" alt="" />'._USER_ENTITIES_ADDITION.'</h1>';
		}

		?>
		<div id="inner_content" class="clearfix">
			<div id="add_box" class="block">
				<p>
				<?php
				if($_SESSION['m_admin']['entity']['user_UserId'] <> "superadmin")
				{
				?>
					<iframe name="usersEnt" id="usersEnt" class="frameform2" src="<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=users_entities_form';?>" frameborder="0"></iframe>
				 <?php
				 }
				 ?>
				</p>
			</div>
			<?php
			if($state == false)
			{
				$_SESSION['error'] = _USER.' '._UNKNOWN;
				echo '<div class="error">'.$_SESSION['error'].'</div>';
			}
			else
			{
				?>
				<form name="frmuserent" method="post" action="<?php  if($mode == "up") { echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=users_entities_up_db'; }  ?>" class="forms addforms" >
								<p>
					<label for="UserId"><?php  echo _ID; ?> :</label>
					<?php  if($mode == "up") { echo $func->show($_SESSION['m_admin']['entity']['user_UserId']); } ?>
					<input type="hidden"  name="id" id="id" value="<?php  echo $id; ?>" />
				</p>

                <p>
					<label for="LastName"><?php  echo _LASTNAME; ?> :</label>
					<?php  echo $func->show($_SESSION['m_admin']['entity']['user_LastName']); ?>
				</p>
				<p>
					<label for="FirstName"><?php  echo _FIRSTNAME; ?> :</label>
					<?php  echo $func->show($_SESSION['m_admin']['entity']['user_FirstName']); ?>
				</p>
				<p>
					<label for="Phone"><?php  echo _PHONE_NUMBER; ?> :</label>
					<?php  echo $_SESSION['m_admin']['entity']['user_Phone']; ?>
				</p>
				<p>
					<label for="Mail"><?php  echo _MAIL; ?> :</label>
					<?php  echo $_SESSION['m_admin']['entity']['user_Mail']; ?>
				</p>
					<p class="buttons">
						<input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button"/>
						<input type="button" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=users_list&amp;module=entities';"/>
					</p>
				</form>
			</div>

			<?php
			}

	}


	/**
	* Updates the database (users_entities table) with the session variables.
	*
	*/
	public function load_db($from_module_entities_page = true)
	{
		$this->connect();

		if(!$from_module_entities_page)
		{
			$this->query("DELETE FROM ".$_SESSION['tablename']['ent_users_entities'] ." where user_id = '".$this->protect_string_db(trim($_SESSION['m_admin']['users']['UserId']))."'");
		}
		else
		{
			$this->query("DELETE FROM ".$_SESSION['tablename']['ent_users_entities'] ." where user_id = '".$this->protect_string_db(tim($_SESSION['m_admin']['entity']['user_UserId']))."'");
		}
		for($i=0; $i < count($_SESSION['m_admin']['entity']['entities'] ); $i++)
		{
			$tmp_r = $this->protect_string_db($_SESSION['m_admin']['entity']['entities'][$i]['ROLE']);
			if(!$from_module_entities_page)
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['ent_users_entities']." VALUES ('".$_SESSION['m_admin']['users']['UserId']."', '".$_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID']."', '".$tmp_r."', '".$_SESSION['m_admin']['entity']['entities'][$i]['PRIMARY']."')");
			}
			else
			{
				$this->query("INSERT INTO ".$_SESSION['tablename']['ent_users_entities']." VALUES ('".$_SESSION['m_admin']['entity']['user_UserId']."', '".$_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID']."', '".$tmp_r."', '".$_SESSION['m_admin']['entity']['entities'][$i]['PRIMARY']."')");
			}
		}

	}


	public function checks_info($mode)
	{
		$primary_set = false;
		if(!empty($_SESSION['m_admin']['entity']['entities'])   )
		{
			for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
			{
				if($_SESSION['m_admin']['entity']['entities'][$i]['PRIMARY'] == 'Y')
				{
					$primary_set = true;
					break;
				}
			}

			if ($primary_set == false)
			{
				$_SESSION['error'] = _NO_PRIMARY_ENTITY;
			}
		}
	}
	/**
	* Add ou modify users_entities in the database
	*
	* @param string $mode up or add
	*/
	public function addupusersentities($mode)
	{
		$primary_set = false;
		if(!empty($_SESSION['m_admin']['entity']['entities'])   )
		{
			for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
			{
				if($_SESSION['m_admin']['entity']['entities'][$i]['PRIMARY'] == 'Y')
				{
					$primary_set = true;
					break;
				}
			}

			if ($primary_set == false)
			{
				$_SESSION['error'] = _NO_PRIMARY_ENTITY;
			}
		}


		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['entity']['user_UserId']))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_entities_up&id=".$_SESSION['m_admin']['entity']['user_UserId']."&module=entities");
					exit();
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=users_list&module=entities");
					exit();
				}
			}

		}
		else
		{
			$this->connect();

			if($mode == "up")
			{
				if($_SESSION['m_admin']['entity']['user_UserId'] <> "superadmin")
				{
					$this->load_db();
				}
				if($_SESSION['history']['usersup'] == "true")
				{
					$tmp_h = $this->protect_string_db(_USER_UPDATE." : ".$_SESSION['m_admin']['entity']['user_LastName']." ".$_SESSION['m_admin']['entity']['user_FirstName']." (".$_SESSION['m_admin']['entity']['user_UserId'].")");
					require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					$users = new history();
					$users->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['entity']['user_UserId'],"UP",$tmp_h, $_SESSION['config']['databasetype']);
				}

				$this->clearuserinfos();

				$_SESSION['error'] = _USER_UPDATED;
				header("location: ".$_SESSION['config']['businessappurl'].'index.php?page=users_list&module=entities');
				exit();
			}

		}
	}

	/**
	* Clear the users add or modification vars
	*/
	private function clearuserinfos()
	{
		unset($_SESSION['m_admin']);
	}

}
?>
