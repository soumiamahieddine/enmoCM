<?php
class entity extends dbquery
{
	/**
	* Form for the management of the entities.
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	* @param 	string  $id  entity identifier (empty by default)
	*/
	public function formentity($mode, $id = '')
	{
		$core_tools = new core_tools();
		$state = true;
		if($mode == "up")
		{
			$_SESSION['service_tag'] = 'entity_up';
			echo '<h1><img src="'.$_SESSION['urltomodules'].'entities/img/manage_entities_b.gif" alt="" /> '._ENTITY_MODIFICATION.'</h1>';
			if(empty($_SESSION['error']))
			{
				$this->connect();
				$this->query('select * from '.$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($id))."'");
				if($this->nb_result() == 0)
				{
					$_SESSION['error'] = _ENTITY_MISSING;
					$state = false;
				}
				else
				{
					$_SESSION['m_admin']['entity']['entityId'] = $this->show_string($id);
					$line = $this->fetch_object();
					$_SESSION['m_admin']['entity']['label'] = $this->show_string($line->entity_label);
					$_SESSION['m_admin']['entity']['short_label'] = $this->show_string($line->short_label);
					$_SESSION['m_admin']['entity']['enabled'] = $this->show_string($line->enabled);
					$_SESSION['m_admin']['entity']['adrs1'] = $this->show_string($line->adrs_1);
					$_SESSION['m_admin']['entity']['adrs2'] = $this->show_string($line->adrs_2);
					$_SESSION['m_admin']['entity']['adrs3'] = $this->show_string($line->adrs_3);
					$_SESSION['m_admin']['entity']['zcode'] = $this->show_string($line->zipcode);
					$_SESSION['m_admin']['entity']['city'] = $this->show_string($line->city);
					$_SESSION['m_admin']['entity']['country'] = $this->show_string($line->country);
					$_SESSION['m_admin']['entity']['email'] = $this->show_string($line->email);
					$_SESSION['m_admin']['entity']['business'] = $this->show_string($line->business_id);
					$_SESSION['m_admin']['entity']['parent'] = $this->show_string($line->parent_entity_id);
					$_SESSION['m_admin']['entity']['type'] = $this->show_string($line->entity_type);
				}
			}
			//$core_tools->execute_modules_services($_SESSION['modules_services'], 'entity_up', "include");
			//$core_tools->execute_app_services($_SESSION['app_services'], 'entity_up', 'include');
		}
		elseif($mode == 'add')
		{
			$_SESSION['service_tag'] = 'entity_add';
			echo '<h1><img src="'.$_SESSION['urltomodules'].'entities/img/manage_entities_b.gif" alt="" /> '._ENTITY_ADDITION.'</h1>';
			if($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] ))
			{
				//$this->init_session();
			}
			//$core_tools->execute_modules_services($_SESSION['modules_services'], 'entity_add', "include");
			//$core_tools->execute_app_services($_SESSION['app_services'], 'entity_add', 'include');
		}
		$_SESSION['service_tag_form'] = 'formentity';
		$except[] = $_SESSION['m_admin']['entity']['entityId'];
		$entities = array();
		if($_SESSION['user']['UserId'] == 'superadmin')
		{
			$entities = $this->getShortEntityTree($entities,'all', '', $except );
		}
		else
		{
			$entities = $this->getShortEntityTree($entities,$_SESSION['user']['entities'],  '' , $except);
		}
		?>
		<div id="inner_content" class="clearfix">
			<?php
			$core_tools->execute_modules_services($_SESSION['modules_services'], 'formentity', "include");
			$core_tools->execute_app_services($_SESSION['app_services'], 'formentity', 'include');
			if($state == false)
			{
				$_SESSION['error'] = _ENTITY_UNKNOWN;
				echo '<div class="error">'.$_SESSION['error'].'</div>';
			}
			else
			{
				?>
				<form name="formentity" id="formentity" method="post" action="<?php  if($mode == 'up') { echo $_SESSION['urltomodules'].'entities/entity_up_db.php'; } elseif($mode == 'add') { echo $_SESSION['urltomodules'].'entities/entity_add_db.php'; } ?>" class="forms">
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
					<?php
					if($mode == 'up')
					{
						?>
						<p>
							<label><?php  echo _ID;?> : </label>
							<input name="entityId" id="entityId" type="text" value="<?php  echo $_SESSION['m_admin']['entity']['entityId']; ?>" readonly="readonly" class="readonly" /><span class="red_asterisk">*</span>
							<input type="hidden"  name="id" value="<?php  echo $id; ?>" />
							<input type="hidden"  name="mode" value="<?php  echo $mode; ?>" />
						</p>
						<?php
					}
					else
					{
						?>
						<p>
							<label><?php  echo _ID;?> : </label>
							<input name="entityId" id="entityId" type="text" value="<?php  echo $_SESSION['m_admin']['entity']['entityId']; ?>" /><span class="red_asterisk">*</span>
						</p>
						<?php
					}
					?>
					<p>
						<label><?php  echo _ENTITY_LABEL; ?> : </label>
						<input name="label"  type="text" id="label" value="<?php  echo $_SESSION['m_admin']['entity']['label']; ?>" /><span class="red_asterisk">*</span>
					</p>
					<p>
						<label><?php  echo _SHORT_LABEL; ?> : </label>
						<input name="short_label"  type="text" id="short_label" value="<?php  echo $_SESSION['m_admin']['entity']['short_label']; ?>" /><span class="red_asterisk">*</span>
					</p>
					<p>
						<label><?php  echo _ENTITY_ADR_1; ?> : </label>
						<input name="adrs1"  type="text" id="adrs1" value="<?php  echo $_SESSION['m_admin']['entity']['adrs1']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_ADR_2; ?> : </label>
						<input name="adrs2"  type="text" id="adrs2" value="<?php  echo $_SESSION['m_admin']['entity']['adrs2']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_ADR_3; ?> : </label>
						<input name="adrs3"  type="text" id="adrs3" value="<?php  echo $_SESSION['m_admin']['entity']['adrs3']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_ZIPCODE; ?> : </label>
						<input name="zcode"  type="text" id="zcode" value="<?php  echo $_SESSION['m_admin']['entity']['zcode']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_CITY; ?> : </label>
						<input name="city"  type="text" id="city" value="<?php  echo $_SESSION['m_admin']['entity']['city']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_COUNTRY; ?> : </label>
						<input name="country"  type="text" id="country" value="<?php  echo $_SESSION['m_admin']['entity']['country']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_EMAIL; ?> : </label>
						<input name="email"  type="text" id="email" value="<?php  echo $_SESSION['m_admin']['entity']['email']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_BUSINESS; ?> : </label>
						<input name="business"  type="text" id="business" value="<?php  echo $_SESSION['m_admin']['entity']['business']; ?>" />
					</p>
					<p>
						<label><?php  echo _ENTITY_TYPE;
						require_once('modules/entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');

						$ent = new entities;

						if($_SESSION['user']['UserId'] =="superadmin")
						{
							$entity_type = "all";
						}
						else
						{
							$entity_type = $this->get_entity_type_level($_SESSION['user']['primaryentity']['id']);
						}

						$typelist = $this->load_entities_types_for_user($entity_type);
						 ?> : </label>
						<select name="type" id="type">
							<option value="" ><?php  echo _CHOOSE_ENTITY_TYPE; ?></option>
							<?php
								for ($i = 0; $i < count($typelist); $i++)
								{
							?>
									<option value="<?php  echo $typelist[$i]['id']; ?>" <?php  if ($_SESSION['m_admin']['entity']['type'] == $typelist[$i]['id']){ echo 'selected="selected"'; } ?> ><?php  echo $typelist[$i]['label']; ?></option>
							<?php
								}
							?>
						</select><span class="red_asterisk">*</span>
					</p>
					<p>
						<label for="parent_id"><?php  echo _ENTITY_PARENT;?> : </label>
						<select name="parententity"  size="5">
							<option value=""><?php  echo _CHOOSE_ENTITY_PARENT;?></option>
							<?php
							for($i=0; $i<count($entities);$i++)
							{
								?>
								<option value="<?php  echo $entities[$i]['ID'];?>" <?php  if($entities[$i]['ID']== $_SESSION['m_admin']['entity']['parent']){ echo 'selected="selected"'; }?> ><?php  echo $entities[$i]['LABEL'];?></option><?php
							}
							?>
						</select><span class="red_asterisk" >*</span>
					</p>

					<p class="buttons">
						<input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button" />
						<input type="button" name="cancel" value="<?php  echo _CANCEL; ?>" class="button"  onclick="javascript:window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=manage_entities&amp;module=entities';"/>
					</p>
				</form>
				<?php
			}
		?>
		</div>
		<?php
	}

	/**
	* Checks if an entity has children
	*
	* @param string $id entity identifier
	*/
	public function havechild($id)
	{
		$this->connect();

		$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($id))."'");

		if($this->nb_result() == 0){ return false; }
		else{ return true; }
	}

	/**
	* Checks if an entity is related with a user
	*
	* @param string $id entity identifier
	*/
	public function isRelated($id)
	{
		$this->connect();

		$this->query('select ue.entity_id from '.$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['users']." u where ue.user_id = u.user_id and ue.entity_id = '".$this->protect_string_db(trim($id))."'");

		if($this->nb_result() == 0){ return false; }
		else{ return true; }
	}


	/**
	* Inits the session variables related to the entities administration.
	*/
	public function init_session()
	{
		unset($_SESSION['m_admin']);
	}

	/**
	* Returns entity label
	*
	* @param string $entity_id entity selected
	*/
	public function getentitylabel($entity_id)
	{
		$labelreturn = false;

		$this->connect();

		$this->query("select entity_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($entity_id))."'");
		//$this->show();
		if($this->nb_result() > 0)
		{
			$line = $this->fetch_object();
			return $line->entity_label;
		}
		else
		{
			return $labelreturn;
		}
	}
	/**
	* Returns entity label
	*
	* @param string $entity_id entity selected
	*/
	public function getentityshortlabel($entity_id)
	{
		$labelreturn = false;

		$this->connect();

		$this->query("select short_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($entity_id))."'");
		//$this->show();
		if($this->nb_result() > 0)
		{
			$line = $this->fetch_object();
			return $line->short_label;
		}
		else
		{
			return $labelreturn;
		}
	}


	/**
	* check whether an entity exists and is enabled
	*
	* @param string $entity_id identifier of the entity
	*/
	public function isEnabledEntity($entity_id)
	{
		$this->connect();
		$this->query('select entity_id, entity_label, short_label from '.$_SESSION['tablename']['ent_entities']." where enabled = 'Y' and entity_id = '".$this->protect_string_db(trim($entity_id))."'");
		if($this->nb_result() > 0)
		{
			$line = $this->fetch_object();
			return $line;
		}
		else
		{
			return false;
		}
	}

	/**
	* Gets all children of an entity in an array
	*
	* @param string $parent the root of the tree
	* @param string $selected identifier of the selected entity
	* @param string $tabspace margin of separation of tree's branches
	* @param array  $except array of entity_id ( elements of the tree that should not appear )
	*/

	public function getEntityChildrenTree($entities, $parent = '', $tabspace = '', $except = array(), $where = '')
	{
		$this->connect();
		$this->query('select entity_id, entity_label, short_label from '.$_SESSION['tablename']['ent_entities']." where enabled = 'Y' and parent_entity_id = '".$this->protect_string_db(trim($parent))."'".$where);

		if($this->nb_result() > 0)
		{
			$espace = $tabspace.'&emsp;';

			while($line = $this->fetch_object())
			{
				if (!in_array($line->entity_id, $except))
				{
					 array_push($entities, array('ID' =>$line->entity_id, 'LABEL' =>  $espace.$this->show_string($line->entity_label), 'SHORT_LABEL' =>$espace.$this->show_string($line->short_label), 'KEYWORD' => false));

					$db2 = new entity();
					$db2->connect();
					$db2->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where enabled = 'Y' and parent_entity_id = '".$this->protect_string_db(trim($line->entity_id))."'".$where);
					$tmp = array();
					if($db2->nb_result() > 0)
					{
						$tmp = $db2->getEntityChildrenTree($tmp,$line->entity_id,  $espace, $except);
						$entities = array_merge($entities, $tmp);
					}
				}
			}
		}
		return $entities;
	}


	/**
	* Gets all entities in an array
	*
	* @param string $parent the root of the tree
	* @param string $selected identifier of the selected entity
	* @param string $tabspace margin of separation of tree's branches
	* @param array  $except array of entity_id ( elements of the tree that should not appear )
	*/
	public function getShortEntityTree($entities, $parent = 'all',  $tabspace = '', $except = array(), $entity_type = '', $root=true)
	{
		$tab_entity_type = array();
		$my_tab_entity_type = array();
		$where = '';
		if($entity_type == '')
		{
			if($_SESSION['user']['UserId'] == 'superadmin')
			{
				$entity_type = "all";
			}
			else
			{
				$entity_type = $this->get_entity_type_level($_SESSION['user']['primaryentity']['id']);
			}
		}
		$tab_entity_type = $this->load_entities_types_for_user($entity_type);

		foreach($tab_entity_type as $theType)
		{
			$my_tab_entity_type[] = "'".$theType['id']."'";
		}

		if (count($my_tab_entity_type)>0)
		{
			$where = " and entity_type in(".join(",", $my_tab_entity_type).")";
		}

		if(is_array($parent))
		{
			for ($i=0; $i < count($parent); $i++)
			{
				$tmp = array();
				if($entity = $this->isEnabledEntity($parent[$i]['ENTITY_ID']))
				{
					if ($root)
					{
						array_push($entities, array('ID' =>$parent[$i]['ENTITY_ID'], 'LABEL' => $this->show_string($parent[$i]['ENTITY_LABEL']), 'KEYWORD' => false));
					}

					$tmp = $this->getEntityChildrenTree($tmp, $parent[$i]['ENTITY_ID'], $tabspace, $except, $where);
					$entities = array_merge($entities, $tmp);
				}
			}
		}
		elseif($parent == 'all')
		{
			 $entities = $this->getEntityChildrenTree($entities,'',  $tabspace, $except, $where);
		}
		return $entities;
	}



	/**
	* Get array of identifiers of all entity's children of an entity (that are related to a user)
	*
	* @param string $parent the root of the tree
	*/
	public function getTabChildrenId($parent = '', $where = '', $immediate_children_only = false)
	{
		static $tab_children_id = array();

		$this->connect();

		$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($parent))."'".$where);

		if($this->nb_result() > 0)
		{
			while($line = $this->fetch_object())
			{
				$tab_children_id[] = "'".$this->protect_string_db(trim($line->entity_id))."'";

				if($immediate_children_only == false)
				{
					$db2 = new entity();
					$db2->connect();
					$db2->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($line->entity_id))."'".$where);
					if($db2->nb_result() > 0)
					{
						$db2->getTabChildrenId($line->entity_id, $where);
					}
				}
			}
		}
		return $tab_children_id;
	}

	/**
	* Get array of identifiers of all entities that are related to a user
	*
	* @param string $parent the root of the tree
	*/
	public function get_all_entities_id_user($parent = 'all')
	{
		$tab_entity_type = array();
		$my_tab_entity_type = array();
		$tab_all_id = array();
		$where = '';

		if($_SESSION['user']['UserId'] == 'superadmin')
		{
			$entity_type = "all";
		}
		else
		{
			$entity_type = $this->get_entity_type_level($_SESSION['user']['primaryentity']['id']);
		}

		$tab_entity_type = $this->load_entities_types_for_user($entity_type);

		foreach($tab_entity_type as $theType)
		{
			$my_tab_entity_type[] = "'".$theType['id']."'";
		}

		if (count($my_tab_entity_type)>0)
		{
			$where = " and entity_type in(".join(",", $my_tab_entity_type).")";
		}

		if(is_array($parent))
		{
			for ($i=0; $i < count($parent); $i++)
			{
				if($entity = $this->isEnabledEntity($parent[$i]['ENTITY_ID']))
				{
					$tab_all_id[] = "'".$entity->entity_id."'";
					$tab_all_id = array_merge($tab_all_id, $this->getTabChildrenId($parent[$i]['ENTITY_ID'], $where));
				}
			}
		}
		elseif($parent == 'all')
		{
			$tab_all_id = $this->getTabChildrenId('', $where);
		}

		return $tab_all_id;
	}

	/**
	* Get array of identifiers of all entities that are related to a user
	*
	* @param string $parent the root of the tree
	*/
	public function get_entities_of_user($user_id,$parent = 'all')
	{
		$entities = array();
		$db = new dbquery();
		$db->connect();
		$db->query("select e.entity_id,e.entity_label,e.short_label, ue.primary_entity, ue.user_role from ".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['ent_users_entities']." ue where ue.entity_id = e.entity_id and ue.user_id = '".$db->protect_string_db(trim($user_id))."' order by e.entity_label");
		while($res = $db->fetch_object())
		{
			array_push($entities, array('ID' => $res->entity_id, 'LABEL' => $res->entity_label, 'SHORT_LABEL' => $res->short_label,'PRIMARY' => $res->entity_label, 'ROLE' => $res->user_role ));
		}
		return $entities;
	}

	/**
	* Allows or denies an entity and its children
	*
	* @param string $id entity identifier
	* @param string $mode ban or allow
	*/
	public function allowbanentity($id, $mode)
	{
		static $count = 1;

		if($mode == 'ban'){
			$action  = 'N';
			$histKey = 'BAN';
			$histLabel = _ENTITY_SUSPENSION;
			$hist = 'entityban';
			$msgError = _ENTITY_SUSPENDED;
		}
		else{
			$action  = 'Y';
			$HistKey = 'VAL';
			$HistLabel = _ENTITY_AUTORIZATION;
			$hist = 'entityval';
			$msgError = _ENTITY_AUTORIZED;
		}
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];

		$this->connect();
		$this->query('Update '.$_SESSION['tablename']['ent_entities']." set enabled = '".$this->protect_string_db(trim($action))."' where entity_id = '".$this->protect_string_db(trim($id))."'");

		if($_SESSION['history'][$hist] == "true")
		{
			require_once("core/class/class_history.php");
			$users = new history();
			$users->add($_SESSION['tablename']['ent_entities'], $id, $histKey, $histLabel." : ".$id, $_SESSION['config']['databasetype']);
		}
		$this->connect();
		$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($id))."'");

		if($this->nb_result() > 0)
		{
			while($line = $this->fetch_object())
			{
				$db2 = new entity();
				$db2->connect();

				$db2->query('Update '.$_SESSION['tablename']['ent_entities']." set enabled = '".$this->protect_string_db(trim($action))."' where entity_id = '".$this->protect_string_db(trim($line->entity_id))."'");
				if($_SESSION['history'][hist] == "true")
				{
					require_once("core/class/class_history.php");
					$users = new history();
					$users->add($_SESSION['tablename']['ent_entities'], $line->entity_id, $HistKey, $HistLabel." : ".$line->entity_id, $_SESSION['config']['databasetype']);
				}

				$count++;

				$db2->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($line->entity_id))."'");
				if($db2->nb_result() > 0)
				{
					$db2->allowbanentity($line->entity_id, $mode);
				}
			}
		}
		$_SESSION['error'] = $count.' '.$msgError;
	}


	/**
	* Allow, Denied or Delete an entity in the database
	*
	* @param string $id entity identifier
	* @param string $mode allow, ban or del
	*/
	public function adminentity($id, $mode)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		if(!empty($_SESSION['error']))
		{
			header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=manage_entities&module=entities&order='.$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit();
		}
		else
		{
			$this->connect();

			$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($id))."'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _ENTITY.' '._UNKNWON;
				header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=manage_entities&module=entities&order='.$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit;
			}
			else
			{
				if($mode == 'allow')
				{
					$this->allowbanentity($id, $mode);

				}
				elseif($mode == 'ban')
				{
					$this->allowbanentity($id, $mode);
				}
				elseif($mode == 'del' )
				{
					if($this->havechild($id))
					{
						$_SESSION['error'] = _ENTITY_HAVE_CHILD;
					}
					elseif($this->isRelated($id))
					{
						$_SESSION['error'] = _ENTITY_IS_RELATED;
					}
					else{
						$this->query("delete from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($id))."'");
						if($_SESSION['history']['entitydel'] == "true")
						{
							require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
							$users = new history();
							$users->add($_SESSION['tablename']['ent_entities'], $id,'DEL',_ENTITY_DELETION." : ".$this->protect_string_db(trim($id)), $_SESSION['config']['databasetype']);
						}
						$_SESSION['error'] = _ENTITY_DELETED;
					}
				}
				header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=manage_entities&module=entities&order='.$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}

	/**
	* Treats the information returned by the form of formentity()
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	*/
	public function entityinfo($mode)
	{
		//require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR..'class_core_tools.php');
		$core = new core_tools();
		if($mode == 'up')
		{
			if(empty($_REQUEST['id']) || !isset($_REQUEST['id']))
			{
				$_SESSION['error'].= _ID_MISSING."<br/>";
			}
			else
			{
				$_SESSION['m_admin']['entity']['entityId']  = $this->wash($_REQUEST['id'], "alphanum", _THE_ID, 'yes', 0, 32);
			}
		}

		if($mode == 'add')
		{
			if(empty($_REQUEST['entityId']) || !isset($_REQUEST['entityId']))
			{
				$_SESSION['error'].= _ID_MISSING."<br/>";
			}
			else
			{
				$_SESSION['m_admin']['entity']['entityId']  = $this->wash($_REQUEST['entityId'], "alphanum", _THE_ID, 'yes', 0, 32);
			}
		}
		$_SESSION['m_admin']['entity']['mode'] = $mode;
		if(isset($_REQUEST['label']) && !empty($_REQUEST['label']))
		{
			$_SESSION['m_admin']['entity']['label'] = $this->wash($_REQUEST['label'], "no", _ENTITY_LABEL, 'yes', 0, 255);
		}
		else
		{
			$_SESSION['error'].= _LABEL_MISSING."<br/>";
		}
		if(isset($_REQUEST['short_label']) && !empty($_REQUEST['short_label']))
		{
			$_SESSION['m_admin']['entity']['short_label'] = $this->wash($_REQUEST['short_label'], "no", _SHORT_LABEL, 'yes', 0, 255);
		}
		else
		{
			$_SESSION['error'].= _SHORT_LABEL_MISSING."<br/>";
		}

		if(isset($_REQUEST['adrs1']) && !empty($_REQUEST['adrs1']))
		{
			$_SESSION['m_admin']['entity']['adrs1'] = $this->wash($_REQUEST['adrs1'], "no", _ENTITY_ADR_1, 'yes', 0, 255);
		}

		if(isset($_REQUEST['adrs2']) && !empty($_REQUEST['adrs2']))
		{
			$_SESSION['m_admin']['entity']['adrs2'] = $this->wash($_REQUEST['adrs2'], "no", _ENTITY_ADR_2, 'yes', 0, 255);
		}

		if(isset($_REQUEST['adrs3']) && !empty($_REQUEST['adrs3']))
		{
			$_SESSION['m_admin']['entity']['adrs3'] = $this->wash($_REQUEST['adrs3'], "no", _ENTITY_ADR_3, 'yes', 0, 255);
		}

		if(isset($_REQUEST['zcode']) && !empty($_REQUEST['zcode']))
		{
			$_SESSION['m_admin']['entity']['zcode'] = $this->wash($_REQUEST['zcode'], "no", _ENTITY_ZIPCODE, 'yes', 0, 32);
		}

		if(isset($_REQUEST['city']) && !empty($_REQUEST['city']))
		{
			$_SESSION['m_admin']['entity']['city'] =  $this->wash($_REQUEST['city'], "no", _ENTITY_CITY, 'yes', 0, 255);
		}

		if(isset($_REQUEST['country']) && !empty($_REQUEST['country']))
		{
			$_SESSION['m_admin']['entity']['country'] =$this->wash($_REQUEST['country'], "no", _ENTITY_COUNTRY, 'yes', 0, 255);
		}

		if(isset($_REQUEST['email']) && !empty($_REQUEST['email']))
		{
			$_SESSION['m_admin']['entity']['email'] = $this->wash($_REQUEST['email'], "mail", _ENTITY_EMAIL, 'yes', 0, 255);
		}

		if(isset($_REQUEST['business']) && !empty($_REQUEST['business']))
		{
			$_SESSION['m_admin']['entity']['business'] = $this->wash($_REQUEST['business'], "no", _ENTITY_BUSINESS, 'yes', 0, 32);
		}

		if(isset($_REQUEST['type']) && !empty($_REQUEST['type']))
		{
			$_SESSION['m_admin']['entity']['type'] =  $this->wash($_REQUEST['type'], "no", _ENTITY_TYPE, 'yes', 0, 64);
		}
		else
		{
			$_SESSION['error'].= _TYPE_MISSING.'<br/>';
		}
		$_SESSION['service_tag'] = 'entity_check';
		$core->execute_modules_services($_SESSION['modules_services'], 'entity_check', "include");
		$core->execute_app_services($_SESSION['app_services'], 'entity_check', 'include');
		$_SESSION['service_tag'] = '';
		$_SESSION['m_admin']['entity']['parent'] = '';
		if(isset($_REQUEST['parententity']) && !empty($_REQUEST['parententity']))
		{
			$_SESSION['m_admin']['entity']['parent'] = $_REQUEST['parententity'];
		}
		$_SESSION['m_admin']['init'] = false;

		$_SESSION['m_admin']['entity']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['entity']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['entity']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['entity']['start'] = $_REQUEST['start'];
	}


	/**
	* Add ou modify entity in the database
	*
	* @param string $mode up or add
	*/
	public function addupentity($mode)
	{
		//require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_core_tools.php');
		$core = new core_tools();
		// add ou modify entity in the database
		$this->entityinfo($mode);
		$order = $_SESSION['m_admin']['entity']['order'];
		$order_field = $_SESSION['m_admin']['entity']['order_field'];
		$what = $_SESSION['m_admin']['entity']['what'];
		$start = $_SESSION['m_admin']['entity']['start'];
		if(!empty($_SESSION['error']))
		{
			if($mode == 'up')
			{
				if(!empty($_SESSION['m_admin']['entity']['entityId'] ))
				{
					header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=entity_up&id='.$_SESSION['m_admin']['entity']['entityId'] .'&module=entities');
					exit();
				}
				else
				{
					header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=manage_entities&module=entities&order='.$order.'&order_field='.$order_field.'&start='.$start.'&what='.$what);
					exit();
				}
			}
			elseif($mode == 'add')
			{
				header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=entity_add&module=entities');
				exit();
			}
		}
		else
		{
			$this->connect();
			if($mode == 'add')
			{
				$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($_SESSION['m_admin']['entity']['entityId'])) ."'");
				if($this->nb_result() > 0)
				{
					$_SESSION['error'] = $_SESSION['m_admin']['entity']['entityId'] .' '._ALREADY_EXISTS.'<br />';
					header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=entity_add&module=entities');
					exit();
				}
				else
				{
					$this->query('INSERT INTO '.$_SESSION['tablename']['ent_entities']." (entity_id, entity_label, short_label, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('".$_SESSION['m_admin']['entity']['entityId']."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['label'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['short_label'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['adrs1'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['adrs2'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['adrs3'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['zcode'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['city'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['country'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['email'])."', '".$this->protect_string_db($_SESSION['m_admin']['entity']['business'])."', '".$_SESSION['m_admin']['entity']['parent']."', '".$_SESSION['m_admin']['entity']['type']."')");
					$_SESSION['service_tag'] = 'entity_add_db';
					$core->execute_modules_services($_SESSION['modules_services'], 'entity_add_db', "include");
					$core->execute_app_services($_SESSION['app_services'], 'entity_add_db', 'include');
					$_SESSION['service_tag'] = '';

					if($_SESSION['history']['entityadd'] == "true")
					{
						require("core/class/class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['ent_entities'], $_SESSION['m_admin']['entity']['entityId'] ,"ADD",_ADD_ENTITY." : ".$_SESSION['m_admin']['entity']['entityId'] , $_SESSION['config']['databasetype'], 'entities');
					}
					$this->clearentityinfos();
					$_SESSION['error'] = _ENTITY_ADDITION;
					unset($_SESSION['m_admin']);
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=manage_entities&module=entities&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
				}
			}
			elseif($mode == 'up')
			{
				$this->query('UPDATE '.$_SESSION['tablename']['ent_entities']." set entity_label = '".$this->protect_string_db($_SESSION['m_admin']['entity']['label'])."' , short_label = '".$this->protect_string_db($_SESSION['m_admin']['entity']['short_label'])."' , adrs_1 = '".$this->protect_string_db($_SESSION['m_admin']['entity']['adrs1'])."', adrs_2 = '".$this->protect_string_db($_SESSION['m_admin']['entity']['adrs2'])."', adrs_3 = '".$this->protect_string_db($_SESSION['m_admin']['entity']['adrs3'])."', zipcode = '".$this->protect_string_db($_SESSION['m_admin']['entity']['zcode'])."', city = '".$this->protect_string_db($_SESSION['m_admin']['entity']['city'])."', country = '".$this->protect_string_db($_SESSION['m_admin']['entity']['country'])."', email = '".$this->protect_string_db($_SESSION['m_admin']['entity']['email'])."', business_id = '".$this->protect_string_db($_SESSION['m_admin']['entity']['business'])."', parent_entity_id = '".$_SESSION['m_admin']['entity']['parent']."', entity_type = '".$_SESSION['m_admin']['entity']['type']."' where entity_id = '".$_SESSION['m_admin']['entity']['entityId'] ."'");
					$_SESSION['service_tag'] = 'entity_up_db';
					$core->execute_modules_services($_SESSION['modules_services'], 'entity_up_db', "include");
					$core->execute_app_services($_SESSION['app_services'], 'entity_up_db', 'include');
					$_SESSION['service_tag'] = '';

				if($_SESSION['history']['entityup'] == "true")
				{
					require('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
					$hist = new history();
					$hist->add($_SESSION['tablename']['ent_entities'], $_SESSION['m_admin']['entity']['entityId'] ,'UP',_ENTITY_UPDATED.' : '.$_SESSION['m_admin']['entity']['entityId'] , $_SESSION['config']['databasetype'], 'folder');
				}
				$this->clearentityinfos();
				$_SESSION['error'] = _ENTITY_MODIFICATION;
				unset($_SESSION['m_admin']);
				header('location: '.$_SESSION['config']['businessappurl'].'index.php?page=manage_entities&module=entities&order='.$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}


	/**
	* Clean the $_SESSION['m_admin']['entity'] array
	*/
	private function clearentityinfos()
	{
		// clear the users add or modification vars
		unset($_SESSION['m_admin']);
	}

	/**
	* Get array of all entity_type that a user has acces
	*
	* @param string $level the entity_type's level of the primary entity of a user
	*/
	public function load_entities_types_for_user($level="all")
	{
		$entypes = array();
		$type = array();
		foreach($_SESSION['entities_types'] as $type)
		{
			if ($level == 'root')
			{
				if($type['level'] == 'node')
				{
					$entypes[] = $type;
				}
			}
			elseif($level =="node")
			{
				if($type['level'] == 'none')
				{
					$entypes[] = $type;
				}
			}
			elseif($level =="all")
			{
				$entypes[] = $type;
			}
		}
		return $entypes;
	}

	/**
	* Get the entity_type_level of an entity
	*
	* @param string $entity_id identifier of the entity
	*/
	public function get_entity_type_level($entity_id)
	{
		$type_level = "";
		$found_type_level = false;
		$this->connect();
		$this->query('select entity_id, entity_label, short_label, entity_type from '.$_SESSION['tablename']['ent_entities']." where entity_id  = '".$this->protect_string_db(trim($entity_id))."'");
		//$this->show();
		$line = $this->fetch_object();
		$entity_type = $line->entity_type;
		$xmltypeentity = simplexml_load_file("modules/entities/xml/typentity.xml");
		//echo "<br>";
		foreach($xmltypeentity->TYPE as $ENTITYTYPE)
		{
			//echo $ENTITYTYPE->id." <>? ".$entity_type." (".$ENTITYTYPE->typelevel.")<br>";
			if($ENTITYTYPE->id == $entity_type)
			{
				//echo $ENTITYTYPE->id." = ".$entity_type." (".$ENTITYTYPE->typelevel.")<br>";
				$type_level =  (string) $ENTITYTYPE->typelevel;
				$found_type_level = true;
				break;
			}
		}
		if ($found_type_level)
		{
			return $type_level;
		}
		else
		{
			return $found_type_level;
		}
	}

	/**
	* Return the parent of an entity
	*
	* @param string $entity_id identifier of the entity
	*/
	public function getParentEntityId($entity_id)
	{
		if(!empty($entity_id))
		{
			$this->connect();
			$this->query("select parent_entity_id from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$this->protect_string_db(trim($entity_id))."'");
			$res = $this->fetch_object();
			return $res->parent_entity_id;
		}
		else
		{
			return '';
		}
	}

	public function getTabSisterEntityId($entity_id)
	{
		$sisters = array();
		if(!empty($entity_id))
		{
			$parent = $this->getParentEntityId($entity_id);
			$this->connect();
			if(!empty($parent))
			{
				$this->query('select entity_id from '.$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$this->protect_string_db(trim($parent))."' and entity_id <> '".$this->protect_string_db(trim($entity_id))."'");
				while($res = $this->fetch_object())
				{
					array_push($sisters, "'".$res->entity_id."'");
				}
			}
		}
		return $sisters;
	}

	public function is_user_in_entity($user_id, $entity_id)
	{
		if($_SESSION['user']['UserId'] == $user_id)
		{
			for($i=0; $i<count($_SESSION['user']['entities']);$i++)
			{
				if($_SESSION['user']['entities'][$i]['ENTITY_ID'] == $entity_id)
				{
					return true;
				}
			}
			return false;
		}
		else
		{
			$this->connect();
			$this->query("select entity_id from ".$_SESSION['tablename']['ent_users_entities']." where user_id = '".$this->protect_string_db(trim($user_id))." and entity_id = '".$this->protect_string_db(trim($entity_id))."'");
			if($this->nb_result() == 1)
			{
				return true	;
			}
			else
			{
				return false;
			}
		}
	}

	public function get_primary_entity($user_id)
	{
		if(empty($user_id))
		{
			return false;
		}
		$this->connect();
		$this->query("select ue.entity_id, ue.user_role, e.entity_label, e.short_label from ".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['ent_users_entities']." ue where ue.user_id = '".$this->protect_string_db(trim($user_id))."' and ue.entity_id = e.entity_id and ue.primary_entity = 'Y'");
		$res = $this->fetch_object();
		return array('ID' => $res->entity_id, 'LABEL' => $res->entity_label, 'SHORT_LABEL' => $res->short_label, 'ROLE' => $res->user_role);
	}
}
?>
