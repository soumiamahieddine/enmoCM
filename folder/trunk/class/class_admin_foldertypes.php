<?php
class foldertype extends dbquery
{
	/**
	* Load data from the foldertypes_doctypes table in the session ( $_SESSION['m_admin']['foldertype']['doctypes']  array)
	*
	* @param 	string  $id  foldertype identifier
	*/

	private function load_doctypes($id)
	{
		$this->connect();
		$_SESSION['m_admin']['foldertype']['structures'] = array();
		$this->query("select doctypes_first_level_id from ".$_SESSION['tablename']['fold_foldertypes_doctypes_level1']." where foldertype_id = ".$id);
		while($res = $this->fetch_object())
		{
			array_push($_SESSION['m_admin']['foldertype']['structures'], $res->doctypes_first_level_id);
		}
		$_SESSION['m_admin']['doctypes'] = array();
		for($i=0;$i< count($_SESSION['m_admin']['foldertype']['structures']);$i++)
		{
			$tmp = array();
			$this->query("select d.description, d.type_id from ".$_SESSION['tablename']['doctypes']." d where d.doctypes_first_level_id = ".$_SESSION['m_admin']['foldertype']['structures'][$i]);
			while($res = $this->fetch_object())
			{
				$type_id = $res->type_id;
				if(!in_array($type_id, $tmp))
				{
					array_push($tmp, $type_id);
					array_push($_SESSION['m_admin']['doctypes'], array('ID' => $type_id, 'COMMENT' => $this->show_string($res->description)));
				}
			}
		}

		$_SESSION['m_admin']['foldertype']['doctypes'] = array();
		$this->query("select d.description, fd.doctype_id from ".$_SESSION['tablename']['fold_foldertypes_doctypes']." fd, ".$_SESSION['tablename']['doctypes']." d where d.type_id = fd.doctype_id and fd.foldertype_id = ".$id." order by d.description ");
		while($res = $this->fetch_object())
		{
			//array_push($_SESSION['m_admin']['foldertype']['doctypes'], array('id'=>$res->doctype_id, 'label' => $res->description));
			array_push($_SESSION['m_admin']['foldertype']['doctypes'], $res->doctype_id);
		}
		$_SESSION['m_admin']['load_doctypes'] = false;
	}


	/**
	* Form for the management of the foldertype.
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	* @param 	string  $id  foldertype identifier (empty by default)
	*/
	public function formfoldertype($mode,$id = "")
	{
		$func = new functions();
		$state = true;
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		$sec = new security();
		$_SESSION['m_admin']['foldertype']['COLL_ID']= "";
		if($mode == "up")
		{
			$_SESSION['m_admin']['mode'] = "up";
			if(empty($_SESSION['error']))
			{
				$this->connect();
				$this->query("select * from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$id);
				if($this->nb_result() == 0)
				{
					$_SESSION['error'] = _FOLDERTYPE_MISSING;
					$state = false;
				}
				else
				{
					$_SESSION['m_admin']['foldertype']['foldertypeId'] = $id;
					$line = $this->fetch_object();
					$_SESSION['m_admin']['foldertype']['desc'] = $this->show_string($line->foldertype_label);
					$_SESSION['m_admin']['foldertype']['comment'] = $this->show_string($line->maarch_comment);

					$_SESSION['m_admin']['foldertype']['indexes'] = $this->get_indexes($id,  'minimal');
					$_SESSION['m_admin']['foldertype']['mandatory_indexes'] = $this->get_mandatory_indexes($id);

					if (!isset($_SESSION['m_admin']['load_doctypes']) || $_SESSION['m_admin']['load_doctypes'] == true)
					{
						$this->load_doctypes($id);
						$_SESSION['m_admin']['load_doctypes'] = false;
					}

					$_SESSION['m_admin']['foldertype']['COLL_ID'] = $line->coll_id;
					$table_view = $sec->retrieve_view_from_coll_id($_SESSION['m_admin']['foldertype']['COLL_ID']);
					$this->query("select count(*) as total_doc from ".$table_view." where foldertype_id = ".$_SESSION['m_admin']['foldertype']['foldertypeId']);
					//$this->show();
					$line = $this->fetch_object();
					$total_doc = $line->total_doc;
				}
			}
		}
		else
		{
			$_SESSION['m_admin']['foldertype']['indexes'] = array();
			$_SESSION['m_admin']['foldertype']['mandatory_indexes'] = array();
		}
		//$this->show_array($_SESSION['m_admin']);
		if($mode == "add")
		{
			echo '<h1><img src="'.$_SESSION['urltomodules'].'folder/img/manage_foldertypes_b.gif" alt="" /> '._FOLDERTYPE_ADDITION.'</h1>';
		}
		elseif($mode == "up")
		{
			echo '<h1><img src="'.$_SESSION['urltomodules'].'folder/img/manage_foldertypes_b.gif" alt="" /> '._FOLDERTYPE_MODIFICATION.'</h1>';
		}
		?>
		<div id="inner_content" class="clearfix">
			<?php
			if($state == false)
			{
				echo "<br /><br /><br /><br />"._FOLDERTYPE.' '._UNKNOWN."<br /><br /><br /><br />";
			}
			else
			{
				$this->connect();
				$db2 = new dbquery();
				$db2->connect();
				$db3 = new dbquery();
				$db3->connect();
				?>
				<form name="formfoldertype" id="formfoldertype" method="post" action="<?php  if($mode == "up") { echo $_SESSION['config']['businessappurl']."index.php?display=true&module=folder&page=foldertype_up_db"; } elseif($mode == "add") { echo $_SESSION['config']['businessappurl']."index.php?display=true&module=folder&page=foldertype_add_db"; } ?>" class="forms">
					<input type="hidden" name="display" value="true" />
					<input type="hidden" name="module" value="folder" />
					<?php  if($mode == "up") {?>
						<input type="hidden" name="page" value="foldertype_up_db" />
					<?php }
					elseif($mode == "add") {?>
						<input type="hidden" name="page" value="foldertype_add_db" />
					<?php } ?>
					<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
					<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
					<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
					<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
					<?php
					if($mode == "up")
					{
						?>
						<p>
							<label><?php  echo _ID;?> : </label>
							<input name="foldertypeId" id="foldertypeId" type="text" value="<?php  echo $func->show($_SESSION['m_admin']['foldertype']['foldertypeId']); ?>" <?php  if($mode == "up") { echo 'readonly="readonly" class="readonly"';} ?> />
							<input type="hidden"  name="id" value="<?php  echo $id; ?>" />
							<input type="hidden"  name="mode" value="<?php  echo $mode; ?>" />
						</p>
						<?php
					}

					if($mode == "up")
					{
						if($total_doc > 0)
						{
							?>
							<p>
								<label><?php  echo _COLLECTION; ?> : </label>
								<input name="collection_show" id="collection_show" type="text" value="<?php  echo $func->show($sec->retrieve_coll_label_from_coll_id($_SESSION['m_admin']['foldertype']['COLL_ID'])); ?>" readonly="readonly" class="readonly" />
								<input name="collection" id="collection" type="hidden" value="<?php  echo $_SESSION['m_admin']['foldertype']['COLL_ID']; ?>" />
							</p>
							<p align="center">
								<?php
								echo _CANTCHANGECOLL." ".$total_doc." "._DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION;
								?>
							</p>
							<?php
						}
						else
						{
							?>
							<p>
								<label for="collection"><?php  echo _COLLECTION;?> : </label>
								<select name="collection" id="collection">
									<option value="" ><?php  echo _CHOOSE_COLLECTION;?></option>
								<?php  for($i=0; $i<count($_SESSION['collections']);$i++)
								{
								?>
									<option value="<?php  echo $_SESSION['collections'][$i]['id'];?>" <?php  if($_SESSION['m_admin']['foldertype']['COLL_ID'] == $_SESSION['collections'][$i]['id']){ echo 'selected="selected"';}?> ><?php  echo $_SESSION['collections'][$i]['label'];?></option>
								<?php
								}
								?>
							 	</select>
                   			 </p>
							<?php
						}
					}
					else
					{
					?>
						<p>
							<label for="collection"><?php  echo _COLLECTION;?> : </label>
							<select name="collection" id="collection" >
								<option value="" ><?php  echo _CHOOSE_COLLECTION;?></option>
							<?php  for($i=0; $i<count($_SESSION['collections']);$i++)
							{
							?>
								<option value="<?php  echo $_SESSION['collections'][$i]['id'];?>" <?php  if($_SESSION['m_admin']['foldertype']['COLL_ID'] == $_SESSION['collections'][$i]['id']){ echo 'selected="selected"';}?> ><?php  echo $_SESSION['collections'][$i]['label'];?></option>
							<?php
							}
							?>
							</select>
						 </p>
						<?php
					}
					?>
					<p>
						<label><?php  echo _DESC; ?> : </label>
						<input name="desc"  type="text" id="desc" value="<?php  echo $func->show($_SESSION['m_admin']['foldertype']['desc']); ?>" />
					</p>
					<p>
						<label><?php  echo _COMMENTS; ?> : </label>
						<textarea  cols="30" rows="4"  name="comment"  id="comment" ><?php  echo $func->show($_SESSION['m_admin']['foldertype']['comment']); ?></textarea>
					</p>
				<?php
				$indexes = $this->get_all_indexes();

				if(count($indexes) > 0)
				{?>
				<div  class="block" align="center" >
				<table>
        			<tr>
            			<th width="500px"><?php echo _FIELD;?></th>
           				<th align="center" width="100px"><?php echo _USED;?></th>
            			<th align="center" width="100px"><?php echo _MANDATORY;?></th>
        			</tr>
					<?php
					for($i=0;$i<count($indexes);$i++)
					{?>
						<tr>
							<td width="150px"><?php echo $indexes[$i]['label'];?></td>
							<td align="center">
							<input name="fields[]" id="field_<?php echo $indexes[$i]['column'];?>" type="checkbox" class="check" value="<?php echo $indexes[$i]['column'];?>" <?php
							if (in_array($indexes[$i]['column'], $_SESSION['m_admin']['foldertype']['indexes']))
							{
								echo 'checked="checked"';
							}?>  /></td>
							<td align="center" width="100px">
								<input name="mandatory_fields[]" id="mandatory_field_<?php echo $indexes[$i]['column'];?>" type="checkbox" class="check" value="<?php echo $indexes[$i]['column'];?>" <?php
						if (in_array($indexes[$i]['column'], $_SESSION['m_admin']['foldertype']['mandatory_indexes']) && in_array($indexes[$i]['column'], $_SESSION['m_admin']['foldertype']['indexes']))
						{
							echo ' checked="checked"';
						}?> onclick="$('field_<?php echo $indexes[$i]['column'];?>').checked=true;" /></td>
						</tr>
			<?php 	} ?>
    			</table>

					</div>
					<div  class="block_end"></div>
			<?php } ?>
					<div align="center">
					 <p><h3><?php  echo _MANDATORY_DOCTYPES_COMP;?> : </h3></p><br/>
					<iframe name="doctypes_frame" src="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=choose_doctypes" frameborder="0" width="900px" height="250px" scrolling="no"></iframe>
					</div>
					<p class="buttons">
						<input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button" />
						<input type="button" name="cancel" value="<?php  echo _CANCEL; ?>" class="button"  onclick="javascript:window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=foldertypes&amp;module=folder';"/>
					</p>
				</form>
				<?php
			}
		?>
		</div>
		<?php
	}

	/**
	* Processes data returned by formgroups()
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	*/
	private function foldertypeinfo($mode)
	{
		$func = new functions();

		if($mode == "up")
		{
			if(empty($_REQUEST['id']) || !isset($_REQUEST['id']))
			{
				$_SESSION['error'].= _ID_MISSING."<br/>";
			}
			else
			{
				$_SESSION['m_admin']['foldertype']['foldertypeId']  = $func->wash($_REQUEST['id'], "alphanum", _THE_ID);
			}
		}
		if(isset($_REQUEST['desc']) && !empty($_REQUEST['desc']))
		{
			$_SESSION['m_admin']['foldertype']['desc'] = $func->wash($_REQUEST['desc'], "no", _THE_DESC);
		}
		else
		{
			$_SESSION['error'].= _DESC_MISSING."<br/>";
		}
		if(isset($_REQUEST['collection']) && !empty($_REQUEST['collection']))
		{
			$_SESSION['m_admin']['foldertype']['COLL_ID'] = $func->wash($_REQUEST['collection'], "no", _COLLECTION);
		}
		else
		{
			$_SESSION['error'].= _COLLECTION.' '._MISSING."<br/>";
		}
		if(isset($_REQUEST['comment']) && !empty($_REQUEST['comment']))
		{
			$_SESSION['m_admin']['foldertype']['comment'] = $_REQUEST['comment'];
		}
		$_SESSION['m_admin']['foldertype']['indexes'] = array();
		$_SESSION['m_admin']['foldertype']['mandatory_indexes'] = array();
		for($i=0; $i<count($_REQUEST['fields']);$i++)
		{
			array_push($_SESSION['m_admin']['foldertype']['indexes'], $_REQUEST['fields'][$i]);
		}
		for($i=0; $i<count($_REQUEST['mandatory_fields']);$i++)
		{
			if(!in_array($_REQUEST['mandatory_fields'][$i], $_SESSION['m_admin']['foldertype']['indexes']))
			{
				$_SESSION['error'].= _IF_CHECKS_MANDATORY_MUST_CHECK_USE;
			}
			array_push($_SESSION['m_admin']['foldertype']['mandatory_indexes'], $_REQUEST['mandatory_fields'][$i]);
		}

		$_SESSION['m_admin']['foldertype']['order'] = $_REQUEST['order'];
		$_SESSION['m_admin']['foldertype']['order_field'] = $_REQUEST['order_field'];
		$_SESSION['m_admin']['foldertype']['what'] = $_REQUEST['what'];
		$_SESSION['m_admin']['foldertype']['start'] = $_REQUEST['start'];
	}

	/**
	* Add ou modify foldertype in the database
	*
	* @param string $mode up or add
	*/
	public function addupfoldertype($mode)
	{
		// add ou modify basket in the database
		$this->foldertypeinfo($mode);
		$order = $_SESSION['m_admin']['foldertype']['order'];
		$order_field = $_SESSION['m_admin']['foldertype']['order_field'];
		$what = $_SESSION['m_admin']['foldertype']['what'];
		$start = $_SESSION['m_admin']['foldertype']['start'];

		if(!empty($_SESSION['error']))
		{
			if($mode == "up")
			{
				if(!empty($_SESSION['m_admin']['foldertype']['foldertypeId'] ))
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertype_up&id=".$_SESSION['m_admin']['foldertype']['foldertypeId'] ."&module=folder");
					exit();
				}
				else
				{
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
				}
			}
			elseif($mode == "add")
			{
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertype_add&module=folder");
				exit();
			}
		}
		else
		{

			if($mode == "add")
			{
				$this->connect();
				$this->query("select foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_label= '".$_SESSION['m_admin']['foldertype']['desc'] ."'");
				if($this->nb_result() > 0)
				{
					$_SESSION['error'] = $_SESSION['m_admin']['foldertype']['desc'] ." "._ALREADY_EXISTS."<br />";
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertype_add&module=folder");
					exit();
				}
				else
				{
					$this->connect();
					//echo $_SESSION['m_admin']['foldertype']['custom_query_insert_colums'];
					$this->query("INSERT INTO ".$_SESSION['tablename']['fold_foldertypes']." (foldertype_label, maarch_comment, coll_id) VALUES ('".$this->show_string($_SESSION['m_admin']['foldertype']['desc'])."', '".$this->show_string($_SESSION['m_admin']['foldertype']['comment'])."',  '".$_SESSION['m_admin']['foldertype']['COLL_ID']."');");
					$this->query('select foldertype_id from '.$_SESSION['tablename']['fold_foldertypes']." where foldertype_label = '".$this->show_string($_SESSION['m_admin']['foldertype']['desc'])."' and maarch_comment = '".$this->show_string($_SESSION['m_admin']['foldertype']['comment'])."';");
					$res = $this->fetch_object();
					$_SESSION['m_admin']['foldertype']['foldertypeId'] = $res->foldertype_id;
					$this->load_db();

					for($i=0; $i<count($_SESSION['m_admin']['foldertype']['indexes']);$i++)
					{
						$mandatory = 'N';
						if(in_array($_SESSION['m_admin']['foldertype']['indexes'][$i], $_SESSION['m_admin']['foldertype']['mandatory_indexes'] ))
						{
							$mandatory = 'Y';
						}
						$this->query("insert into ".$_SESSION['tablename']['fold_foldertypes_indexes']." (foldertype_id, field_name, mandatory) values(".$_SESSION['m_admin']['foldertype']['foldertypeId'].", '".$_SESSION['m_admin']['foldertype']['indexes'][$i]."', '".$mandatory."')");
					}

					if($_SESSION['history']['foldertypeadd'] == "true")
					{
						require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['fold_foldertypes'], $_SESSION['m_admin']['foldertype']['foldertypeId'] ,"ADD",_FOLDERTYPE_ADDED." : ".$_SESSION['m_admin']['foldertype']['foldertypeId'] , $_SESSION['config']['databasetype'], 'folder');
					}
					$this->clearfoldertypeinfos();
					$_SESSION['error'] = _FOLDERTYPE_ADDED;
					unset($_SESSION['m_admin']);
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
					exit();
				}
			}
			elseif($mode == "up")
			{
				$this->connect();
				$this->query("UPDATE ".$_SESSION['tablename']['fold_foldertypes']." set foldertype_label = '".$this->show_string($_SESSION['m_admin']['foldertype']['desc'])."' , maarch_comment = '".$this->show_string($_SESSION['m_admin']['foldertype']['comment'])."' , coll_id = '".$_SESSION['m_admin']['foldertype']['COLL_ID']."' where foldertype_id= '".$_SESSION['m_admin']['foldertype']['foldertypeId'] ."'");
				$this->load_db();

				$this->query("delete from ".$_SESSION['tablename']['fold_foldertypes_indexes']." where foldertype_id = ".$_SESSION['m_admin']['foldertype']['foldertypeId']);
				//$this->show_array($_SESSION['m_admin']['foldertype']['indexes']);

				for($i=0; $i<count($_SESSION['m_admin']['foldertype']['indexes']);$i++)
				{
					$mandatory = 'N';
					if(in_array($_SESSION['m_admin']['foldertype']['indexes'][$i], $_SESSION['m_admin']['foldertype']['mandatory_indexes'] ))
					{
						$mandatory = 'Y';
					}
					$this->query("insert into ".$_SESSION['tablename']['fold_foldertypes_indexes']." ( foldertype_id, field_name, mandatory) values( ".$_SESSION['m_admin']['foldertype']['foldertypeId'].", '".$_SESSION['m_admin']['foldertype']['indexes'][$i]."', '".$mandatory."')");
				}
				if($_SESSION['history']['foldertypeup'] == "true")
				{
					require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					$hist = new history();
					$hist->add($_SESSION['tablename']['fold_foldertypes'], $_SESSION['m_admin']['foldertype']['foldertypeId'] ,"UP",_FOLDERTYPE_UPDATE." : ".$_SESSION['m_admin']['foldertype']['foldertypeId'] , $_SESSION['config']['databasetype'], 'folder');
				}
				$this->clearfoldertypeinfos();
				$_SESSION['error'] = _FOLDERTYPE_UPDATE;
				unset($_SESSION['m_admin']);
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}

	/**
	* Clear the session variable for the foldertypes
	*/
	private function clearfoldertypeinfos()
	{
		unset($_SESSION['m_admin']);
	}

	/**
	* Load the foldertype data in the database
	*/
	private function load_db()
	{
		$this->connect();
		$this->query("DELETE FROM ".$_SESSION['tablename']['fold_foldertypes_doctypes'] ." where foldertype_id= ".$_SESSION['m_admin']['foldertype']['foldertypeId'] ."");
		//$this->show();
		for($i=0; $i < count($_SESSION['m_admin']['foldertype']['doctypes'] ); $i++)
		{
			$this->query("insert into ".$_SESSION['tablename']['fold_foldertypes_doctypes']." values (".$_SESSION['m_admin']['foldertype']['foldertypeId'].", ".$_SESSION['m_admin']['foldertype']['doctypes'][$i].")");
		}
	}

	/**
	*  delete foldertype in the database
	*
	* @param string $id foldertype identifier
	* @param string $mode allow, ban or del
	*/
	public function adminfoldertype($id,$mode)
	{
		$order = $_REQUEST['order'];
		$order_field = $_REQUEST['order_field'];
		$start = $_REQUEST['start'];
		$what = $_REQUEST['what'];
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
			exit();
		}
		else
		{
			$this->connect();
			$this->query("select foldertype_id from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id= ".$id);
			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _FOLDERTYPE_MISSING;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
			else
			{
				$info = $this->fetch_object();
				if($mode == "del" )
				{
					$this->query("delete from ".$_SESSION['tablename']['fold_foldertypes']."  where foldertype_id = ".$id."");
					$this->query("delete from ".$_SESSION['tablename']['fold_foldertypes_doctypes']."  where foldertype_id = ".$id."");
					$this->query("delete from ".$_SESSION['tablename']['fold_foldertypes_indexes']."  where foldertype_id = ".$id."");

					if($_SESSION['history']['foldertypedel'] == "true")
					{
						require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['fold_foldertypes'], $id,"DEL",_FOLDERTYPE_DELETION." : ".$id, $_SESSION['config']['databasetype'],  'folder');
					}
					$_SESSION['error'] = _FOLDERTYPE_DELETION;
				}
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what);
				exit();
			}
		}
	}

	/**
	* Returns in an array all indexes possible
	*
	* @return array $indexes[$i]
	* 					['column'] : database field of the index
	* 					['label'] : Index label
	* 					['type'] : Index type ('date', 'string', 'integer' or 'float')
	* 					['img'] : url to the image index
	*/
	public function get_all_indexes()
	{

		$xmlfile = simplexml_load_file('modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'folder_index.xml');
		$path_lang = 'modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		$indexes = array();
		foreach($xmlfile->INDEX as $item)
		{
			$tmp = (string) $item->label;
			//echo $tmp;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}

			$img = (STRING) $item->img;
			if(isset($item->default_value) && !empty($item->default_value))
			{
				$tmp = (string) $item->default_value;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$default = $tmp2;
				}
				else
				{
					$default= $tmp;
				}
			}
			else
			{
				$default = false;
			}
			if(isset($item->values_list))
			{
				$values = array();
				$list = $item->values_list ;
				foreach($list->value as $val)
				{
					$tmp = (string) $val->label;
					$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
					if($tmp2 <> false)
					{
						$label_val = $tmp2;
					}
					else
					{
						$label_val = $tmp;
					}
					array_push($values, array('id' => (string) $val->id, 'label' => $label_val));
				}
				$arr_tmp = array('column' => (STRING) $item->column, 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['urltomodules'].'folder/img/'.$img, 'type_field' => 'select', 'values' => $values, 'default_value' => $default);
			}
			else
			{
				$arr_tmp = array('column' => (STRING) $item->column, 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['urltomodules'].'folder/img/'.$img, 'type_field' => 'input', 'default_value' => $default);
			}
			array_push($indexes, $arr_tmp);
		}
		return $indexes;
	}

	/**
	* Returns in an array all indexes for a doctype
	*
	* @param string $foldertype_id Document type identifier
	* @param string $mode Mode 'full' or 'minimal', 'full' by default
	* @return array array of the indexes, depends on the chosen mode :
	*  		1) mode = 'full' : $indexes[field_name] :  the key is the field name in the database
	* 										['label'] : Index label
	* 										['type'] : Index type ('date', 'string', 'integer' or 'float')
	* 										['img'] : url to the image index
	* 		2) mode = 'minimal' : $indexes[$i] = field name in the database
	*/
	public function get_indexes($foldertype_id, $mode= 'full')
	{
		$fields = array();
		$this->connect();
		$this->query("select field_name from ".$_SESSION['tablename']['fold_foldertypes_indexes']." where  foldertype_id = ".$foldertype_id);
		//$this->show();

		while($res = $this->fetch_object())
		{
			array_push($fields,$res->field_name );
		}
		if($mode == 'minimal')
		{
			return $fields;
		}

		$indexes = array();
		$xmlfile = simplexml_load_file('modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'folder_index.xml');
		$path_lang = 'modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		foreach($xmlfile->INDEX as $item)
		{
			$tmp = (string) $item->label;
			$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang );
			if($tmp2 <> false)
			{
				$label = $tmp2;
			}
			else
			{
				$label = $tmp;
			}
			$col = (STRING) $item->column;
			$img = (STRING) $item->img;
			if(isset($item->default_value) && !empty($item->default_value))
			{
				$tmp = (string) $item->default_value;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$default = $tmp2;
				}
				else
				{
					$default= $tmp;
				}
			}
			else
			{
				$default = false;
			}
			if(in_array($col, $fields))
			{
				if(isset($item->values_list))
				{
					$values = array();
					$list = $item->values_list ;
					foreach($list->value as $val)
					{
						$tmp = (string) $val->label;
						$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
						if($tmp2 <> false)
						{
							$label_val = $tmp2;
						}
						else
						{
							$label_val = $tmp;
						}
						array_push($values, array('id' => (string) $val->id, 'label' => $label_val));
					}
					$indexes[$col] = array( 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['urltomodules'].'folder/img/'.$img, 'type_field' => 'select', 'values' => $values, 'default_value' => $default);
				}
				else
				{
					$indexes[$col] = array( 'label' => $label, 'type' => (STRING) $item->type, 'img' => $_SESSION['urltomodules'].'folder/img/'.$img, 'type_field' => 'input', 'default_value' => $default);
				}
			}
		}
		return $indexes;
	}

	/**
	* Returns in an array all manadatory indexes possible for a given type
	*
	* @param string $foldertype_id Document type identifier
	* @return array Array of the manadatory indexes, $indexes[$i] = field name in the db
	*/
	public function get_mandatory_indexes($foldertype_id)
	{
		$fields = array();
		$this->connect();
		$this->query("select field_name from ".$_SESSION['tablename']['fold_foldertypes_indexes']." where foldertype_id = ".$foldertype_id." and mandatory = 'Y'");

		while($res = $this->fetch_object())
		{
			array_push($fields,$res->field_name );
		}
		return $fields;
	}

	/**
	* Checks validity of indexes
	*
	* @param string $foldertype_id Folder type identifier
	* @param array $values Values to check
	* @return bool true if checks is ok, false if an error occurs
	*/
	public function check_indexes($foldertype_id, $values)
	{
		if(empty($foldertype_id))
		{
			return false;
		}

		// Checks the manadatory indexes
		$indexes = $this->get_indexes($foldertype_id);
		$mandatory_indexes = $this->get_mandatory_indexes($foldertype_id);

		for($i=0; $i<count($mandatory_indexes);$i++)
		{
			if( empty($values[$mandatory_indexes[$i]]))  // Pb 0
			{
				$_SESSION['error'] .= $indexes[$mandatory_indexes[$i]]['label'].' '._IS_EMPTY;
				return false;
			}
		}

		// Checks type indexes
		$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
		foreach(array_keys($values)as $key)
		{
			if(!empty($_SESSION['error']))
			{
				//echo 'error '.$_SESSION['error'];
				return false;
			}
			if($indexes[$key]['type'] == 'date' && !empty($values[$key]))
			{
				if(preg_match( $date_pattern,$values[$key])== 0)
				{
					$_SESSION['error'] .= $indexes[$key]['label']." "._WRONG_FORMAT.".<br/>";
					return false;
				}
			}
			else if($indexes[$key]['type'] == 'string'  && !empty($values[$key]))
			{
				$field_value = $this->wash($values[$key],"no",$indexes[$key]['label']);
			}
			else if($indexes[$key]['type'] == 'float' && !empty($values[$key]) )
			{
				$field_value = $this->wash($values[$key],"float",$indexes[$key]['label']);
			}
			else if($indexes[$key]['type'] == 'integer' && !empty($values[$key]) )
			{
				$field_value = $this->wash($values[$key],"num",$indexes[$key]['label']);
			}

			if(isset($indexes[$key]['values']) && count($indexes[$key]['values']) > 0)
			{
				$found = false;
				for($i=0; $i < count($indexes[$key]['values']); $i++)
				{
					if($values[$key] == $indexes[$key]['values'][$i]['id'])
					{
						$found = true;
						break;
					}
				}
				if(!$found)
				{
					$_SESSION['error'] .= 	$indexes[$key]['label']." : "._ITEM_NOT_IN_LIST.".<br/>";
					return false;
				}
			}
		}

		return true;
	}

	/**
	* Returns a string to use in an sql update query
	*
	* @param string $foldertype_id Folder type identifierer
	* @param array $values Values to update
	* @return string Part of the update sql query
	*/
	public function get_sql_update($foldertype_id, $values)
	{
		$indexes = $this->get_indexes($foldertype_id, $coll_id);

		$req = '';
		foreach(array_keys($values)as $key)
		{
			if($indexes[$key]['type'] == 'date' && !empty($values[$key]))
			{
				$req .= ", ".$key." = '".$this->format_date_db($values[$key])."'";
			}
			else if($indexes[$key]['type'] == 'string' && !empty($values[$key]))
			{
				$req .= ", ".$key." = '".$this->protect_string_db($values[$key])."'";
			}
			else if($indexes[$key]['type'] == 'float' && !empty($values[$key]))
			{
				$req .= ", ".$key." = ".$values[$key]."";
			}
			else if($indexes[$key]['type'] == 'integer' && !empty($values[$key]))
			{
				$req .= ", ".$key." = ".$values[$key]."";
			}
		}
		return $req;
	}

	/**
	* Returns an array used to insert data in the database
	*
	* @param string $foldertype_id Folder type identifier
	* @param array $values Values to update
	* @param array $data Return array
	* @return array
	*/
	public function fill_data_array($foldertype_id, $values, $data = array())
	{
		$indexes = $this->get_indexes($foldertype_id);

		foreach(array_keys($values)as $key)
		{
			if($indexes[$key]['type'] == 'date' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $this->format_date_db($values[$key]), 'type' => "date"));
			}
			else if($indexes[$key]['type'] == 'string' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $this->protect_string_db($values[$key]), 'type' => "string"));
			}
			else if($indexes[$key]['type'] == 'float' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $values[$key], 'type' => "float"));
			}
			else if($indexes[$key]['type'] == 'integer' && !empty($values[$key]))
			{
				array_push($data, array('column' => $key, 'value' => $values[$key], 'type' => "integer"));
			}
		}
		return $data;
	}

	/**
	* Inits in the database the indexes for a given folder id to null
	*
	* @param string $folder_sys_id Folder identifier
	*/
	public function inits_opt_indexes($folder_sys_id)
	{
		$indexes = $this->get_all_indexes( );
		$query = "update ".$_SESSION['tablename']['fold_folders']." set ";
		for($i=0; $i<count($indexes);$i++)
		{
			$query .= $indexes[$i]['column']." = NULL, ";
		}
		$query = preg_replace('/, $/', ' where folders_system_id = '.$folder_sys_id, $query);

		$this->connect();
		$this->query($query);
	}


	public function search_checks($indexes, $field_name, $val )
	{
		$where_request = '';
		$date_pattern = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
		foreach(array_keys($indexes) as $key)
		{
			if($key == $field_name) // type == 'string'
			{
				if(!empty($val))
				{
					if($_SESSION['config']['databasetype'] == "POSTGRESQL")
					{
						$where_request .= " ".$_SESSION['tablename']['fold_folders'].".".$key." ilike '%".$this->protect_string_db($val)."%' and ";
					}
					else
					{
						$where_request .= " ".$_SESSION['tablename']['fold_folders'].".".$key." like '%".$this->protect_string_db($val)."%' and ";
					}
				}
				break;
			}
			else if($key.'_from' == $field_name || $key.'_to' == $field_name)
			{ // type == 'date'
				if( preg_match($date_pattern,$val)==false )
				{
					$_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$val;
				}
				else
				{
					$where_request .= " (".$_SESSION['tablename']['fold_folders'].".".$key." >= '".$this->format_date_db($val)."') and ";
				}
				break;
			}
			else if($key.'_min' == $field_name || $key.'_max' == $field_name )
			{
				if($indexes[$key]['type'] == 'integer' || $indexes[$key]['type'] == 'float')
				{
					if($indexes[$key]['type'] == 'integer')
					{
						$val_check = $this->wash($val,"num",$indexes[$key]['label'],"no");
					}
					else
					{
						$val_check = $this->wash($val,"float",$indexes[$key]['label'],"no");
					}
					if(empty($_SESSION['error']))
					{
						$where_request .= " (".$_SESSION['tablename']['fold_folders'].".".$key." >= ".$val_check.") and ";
					}
				}
				break;
			}
		}
		return  $where_request;
	}

}
