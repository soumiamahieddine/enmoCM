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
		require_once($_SESSION['pathtocoreclass']."class_security.php");
		$sec = new security();
		$_SESSION['m_admin']['doctypes']['COLL_ID']= "";
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
					//$_SESSION['m_admin']['foldertype']['nationality'] = $line->custom_t1;
					$_SESSION['m_admin']['foldertype']['custom_t1'] = $line->custom_t1;
					$_SESSION['m_admin']['foldertype']['custom_t2'] = $line->custom_t2;
					$_SESSION['m_admin']['foldertype']['custom_t3'] = $line->custom_t3;
					$_SESSION['m_admin']['foldertype']['custom_t4'] = $line->custom_t4;
					$_SESSION['m_admin']['foldertype']['custom_t5'] = $line->custom_t5;
					$_SESSION['m_admin']['foldertype']['custom_t6'] = $line->custom_t6;
					$_SESSION['m_admin']['foldertype']['custom_t7'] = $line->custom_t7;
					$_SESSION['m_admin']['foldertype']['custom_t8'] = $line->custom_t8;
					$_SESSION['m_admin']['foldertype']['custom_t9'] = $line->custom_t9;
					$_SESSION['m_admin']['foldertype']['custom_t10'] = $line->custom_t10;
					$_SESSION['m_admin']['foldertype']['custom_t11'] = $line->custom_t11;
					$_SESSION['m_admin']['foldertype']['custom_t12'] = $line->custom_t12;
					$_SESSION['m_admin']['foldertype']['custom_t13'] = $line->custom_t13;
					$_SESSION['m_admin']['foldertype']['custom_t14'] = $line->custom_t14;
					$_SESSION['m_admin']['foldertype']['custom_t15'] = $line->custom_t15;
					$_SESSION['m_admin']['foldertype']['custom_d1'] = $line->custom_d1;
					$_SESSION['m_admin']['foldertype']['custom_d2'] = $line->custom_d2;
					$_SESSION['m_admin']['foldertype']['custom_d3'] = $line->custom_d3;
					$_SESSION['m_admin']['foldertype']['custom_d4'] = $line->custom_d4;
					$_SESSION['m_admin']['foldertype']['custom_d5'] = $line->custom_d5;
					$_SESSION['m_admin']['foldertype']['custom_d6'] = $line->custom_d6;
					$_SESSION['m_admin']['foldertype']['custom_d7'] = $line->custom_d7;
					$_SESSION['m_admin']['foldertype']['custom_d8'] = $line->custom_d8;
					$_SESSION['m_admin']['foldertype']['custom_d9'] = $line->custom_d9;
					$_SESSION['m_admin']['foldertype']['custom_d10'] = $line->custom_d10;
					$_SESSION['m_admin']['foldertype']['custom_n1'] = $line->custom_n1;
					$_SESSION['m_admin']['foldertype']['custom_n2'] = $line->custom_n2;
					$_SESSION['m_admin']['foldertype']['custom_n3'] = $line->custom_n3;
					$_SESSION['m_admin']['foldertype']['custom_n4'] = $line->custom_n4;
					$_SESSION['m_admin']['foldertype']['custom_n5'] = $line->custom_n5;
					$_SESSION['m_admin']['foldertype']['custom_f1'] = $line->custom_f1;
					$_SESSION['m_admin']['foldertype']['custom_f2'] = $line->custom_f2;
					$_SESSION['m_admin']['foldertype']['custom_f3'] = $line->custom_f3;
					$_SESSION['m_admin']['foldertype']['custom_f4'] = $line->custom_f4;
					$_SESSION['m_admin']['foldertype']['custom_f5'] = $line->custom_f5;
					if (!isset($_SESSION['m_admin']['load_doctypes']) || $_SESSION['m_admin']['load_doctypes'] == true)
					{
						$this->load_doctypes($id);
						$_SESSION['m_admin']['load_doctypes'] = false;
					}
					$_SESSION['m_admin']['doctypes']['COLL_ID'] = $line->coll_id;

					$table_view = $sec->retrieve_view_from_coll_id($_SESSION['m_admin']['doctypes']['COLL_ID']);
					$this->query("select count(*) as total_doc from ".$table_view." where foldertype_id = ".$_SESSION['m_admin']['foldertype']['foldertypeId']);
					//$this->show();
					$line = $this->fetch_object();
					$total_doc = $line->total_doc;
				}
			}
		}
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
				<form name="formfoldertype" id="formfoldertype" method="post" action="<?php  if($mode == "up") { echo $_SESSION['urltomodules']."folder/foldertype_up_db.php"; } elseif($mode == "add") { echo $_SESSION['urltomodules']."folder/foldertype_add_db.php"; } ?>" class="forms">
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
					//echo $_SESSION['m_admin']['doctypes']['COLL_ID'];
					if($mode == "up")
					{
						if($total_doc > 0)
						{
							?>
							<p>
								<label><?php  echo _COLLECTION; ?> : </label>
								<input name="foldertypeId" id="foldertypeId" type="text" value="<?php  echo $func->show($sec->retrieve_coll_label_from_coll_id($_SESSION['m_admin']['doctypes']['COLL_ID'])); ?>" readonly />
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
								<iframe name="choose_coll" id="choose_coll" scrolling="no" width="100%" height="20" src="<?php  echo $_SESSION['urltomodules'].'folder/choose_coll.php';?>" frameborder="0"></iframe>
							</p>
							<?php
						}
					}
					else
					{
					?>
						<p>
							<iframe name="choose_coll" id="choose_coll" scrolling="no" width="100%" height="20" src="<?php  echo $_SESSION['urltomodules'].'folder/choose_coll.php';?>" frameborder="0"></iframe>
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

					<div class="block" align="center">
					<p><h3><?php  echo _INDEX_FOR_FOLDERTYPES;?> : </h3></p><br/>
					<table>
						<tr>
							<td width='150'>
								<em><?php  echo _FIELD;?></em>
							</td>
							<td align="center" width='100'>
								<em><?php  echo _USED;?></em>
							</td>
							<td align="center" width='100'>
								<em><?php  echo _MANDATORY;?></em>
							</td>
						</tr>
						<?php
						for($i=0;$i<count($_SESSION['folder_index']);$i++)
						{
							echo "<tr>";
							echo "<td width='150'>";
							echo "	".$_SESSION['folder_index'][$i]['LABEL'];
							echo "</td>";
							echo "<td align='center'>";
							?>
							<input name="field_<?php  echo $_SESSION['folder_index'][$i]['COLUMN'];?>" type="checkbox"  value="Y"
							<?php
							if ($_SESSION['m_admin']['foldertype'][$_SESSION['folder_index'][$i]['COLUMN']] == '1100000000' || $_SESSION['m_admin']['foldertype'][$_SESSION['folder_index'][$i]['COLUMN']] == '1000000000')
							{
								echo "checked=\"checked\"";
							}
							?>
							/>
							</td>
							<td align="center" width='100'>
								<input name="mandatory_<?php  echo $_SESSION['folder_index'][$i]['COLUMN'];?>" type="checkbox"  value="Y"
								<?php
								if ($_SESSION['m_admin']['foldertype'][$_SESSION['folder_index'][$i]['COLUMN']] == '1100000000')
								{
									echo "checked=\"checked\"";
								}
								?>
								/>
							</td>
						</tr>
						<?php
					}
					?>
					</table>
					</div>
					<div class="block_end"></div>
					<div align="center">
					 <p><h3><?php  echo _MANDATORY_DOCTYPES_COMP;?> : </h3></p><br/>
					<iframe name="doctypes_frame" src="<?php  echo $_SESSION['urltomodules'];?>folder/choose_doctypes.php" frameborder="0" width="700" height="320"></iframe>
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
	* Treats the information returned by the form of formgroups()
	*
	* @param 	string  $mode administrator mode (modification, suspension, authorization, delete)
	*/
	private function foldertypeinfo($mode)
	{
		$func = new functions();
		$_SESSION['m_admin']['foldertype']['custom_query_colums'] = "";
		$_SESSION['m_admin']['foldertype']['custom_query_values'] = "";
		$_SESSION['m_admin']['foldertype']['custom_query_update'] = "";
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
		if(!isset($_SESSION['m_admin']['doctypes']['COLL_ID']) && empty($_SESSION['m_admin']['doctypes']['COLL_ID']))
		{
			$_SESSION['error'].= _COLLECTION_MISSING."<br/>";
		}
		else
		{
			$_SESSION['m_admin']['doctypes']['COLL_ID'] = $func->wash($_SESSION['m_admin']['doctypes']['COLL_ID'], "no", _COLLECTION);
		}
		if(isset($_REQUEST['comment']) && !empty($_REQUEST['comment']))
		{
			$_SESSION['m_admin']['foldertype']['comment'] = $_REQUEST['comment'];
		}
		for($i=0;$i <count($_SESSION['folder_index']);$i++)
		{
			if($_REQUEST["field_".$_SESSION['folder_index'][$i]['COLUMN']] == "Y")
			{
				if($_REQUEST["mandatory_".$_SESSION['folder_index'][$i]['COLUMN']] == "Y")
				{
					$_SESSION['m_admin']['foldertype'][$_SESSION['folder_index'][$i]['COLUMN']] = "1100000000";
					$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'] .= $_SESSION['folder_index'][$i]['COLUMN'].", ";
					$_SESSION['m_admin']['foldertype']['custom_query_insert_values'] .= "'1100000000', ";
					$_SESSION['m_admin']['foldertype']['custom_query_update'] .= $_SESSION['folder_index'][$i]['COLUMN']." = "."'1100000000', ";
				}
				else
				{
					$_SESSION['m_admin']['foldertype'][$_SESSION['folder_index'][$i]['COLUMN']] = "1000000000";
					$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'] .= $_SESSION['folder_index'][$i]['COLUMN'].", ";
					$_SESSION['m_admin']['foldertype']['custom_query_insert_values'] .= "'1000000000', ";
					$_SESSION['m_admin']['foldertype']['custom_query_update'] .= $_SESSION['folder_index'][$i]['COLUMN']." = "."'1000000000', ";
				}
			}
			elseif($_REQUEST["field_".$_SESSION['folder_index'][$i]['COLUMN']] == "")
			{
				$_SESSION['m_admin']['foldertype'][$_SESSION['folder_index'][$i]['COLUMN']] = "0000000000";
				$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'] .= $_SESSION['folder_index'][$i]['COLUMN'].", ";
				$_SESSION['m_admin']['foldertype']['custom_query_insert_values'] .= "'0000000000', ";
				$_SESSION['m_admin']['foldertype']['custom_query_update'] .= $_SESSION['folder_index'][$i]['COLUMN']." = '0000000000', ";
			}
		}
		if($_SESSION['m_admin']['foldertype']['custom_query_insert_colums'] <> "")
		{
			$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'] = ", ".$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'];
			$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'] = substr($_SESSION['m_admin']['foldertype']['custom_query_insert_colums'],0,strlen($_SESSION['m_admin']['foldertype']['custom_query_insert_colums'])-2);
		}
		if($_SESSION['m_admin']['foldertype']['custom_query_insert_values'] <> "")
		{
			$_SESSION['m_admin']['foldertype']['custom_query_insert_values'] = ", ".$_SESSION['m_admin']['foldertype']['custom_query_insert_values'];
			$_SESSION['m_admin']['foldertype']['custom_query_insert_values'] = substr($_SESSION['m_admin']['foldertype']['custom_query_insert_values'],0,strlen($_SESSION['m_admin']['foldertype']['custom_query_insert_values'])-2);
		}
		if($_SESSION['m_admin']['foldertype']['custom_query_update'] <> "")
		{
			$_SESSION['m_admin']['foldertype']['custom_query_update'] = ", ".$_SESSION['m_admin']['foldertype']['custom_query_update'];
			$_SESSION['m_admin']['foldertype']['custom_query_update'] = substr($_SESSION['m_admin']['foldertype']['custom_query_update'],0,strlen($_SESSION['m_admin']['foldertype']['custom_query_update'])-2);
		}
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
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder");
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
					$this->query("INSERT INTO ".$_SESSION['tablename']['fold_foldertypes']." (foldertype_label, maarch_comment ".$_SESSION['m_admin']['foldertype']['custom_query_insert_colums'].", coll_id) VALUES ('".$this->show_string($_SESSION['m_admin']['foldertype']['desc'])."', '".$this->show_string($_SESSION['m_admin']['foldertype']['comment'])."' ".$_SESSION['m_admin']['foldertype']['custom_query_insert_values'].", '".$_SESSION['m_admin']['doctypes']['COLL_ID']."')");
					$this->query('select foldertype_id from '.$_SESSION['tablename']['fold_foldertypes']." where foldertype_label = '".$this->show_string($_SESSION['m_admin']['foldertype']['desc'])."' and maarch_comment = '".$this->show_string($_SESSION['m_admin']['foldertype']['comment'])."'");
					$res = $this->fetch_object();
					$_SESSION['m_admin']['foldertype']['foldertypeId'] = $res->foldertype_id;
					$this->load_db();
					if($_SESSION['history']['foldertypeadd'] == "true")
					{
						require($_SESSION['pathtocoreclass']."class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['fold_foldertypes'], $_SESSION['m_admin']['foldertype']['foldertypeId'] ,"ADD",_FOLDERTYPE_ADDED." : ".$_SESSION['m_admin']['foldertype']['foldertypeId'] , $_SESSION['config']['databasetype'], 'folder');
					}
					$this->clearfoldertypeinfos();
					$_SESSION['error'] = _FOLDERTYPE_ADDED;
					unset($_SESSION['m_admin']);
					header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder");
					exit();
				}
			}
			elseif($mode == "up")
			{
				$this->connect();
				$this->query("UPDATE ".$_SESSION['tablename']['fold_foldertypes']." set foldertype_label = '".$this->show_string($_SESSION['m_admin']['foldertype']['desc'])."' , maarch_comment = '".$this->show_string($_SESSION['m_admin']['foldertype']['comment'])."' ".$_SESSION['m_admin']['foldertype']['custom_query_update'].", coll_id = '".$_SESSION['m_admin']['doctypes']['COLL_ID']."' where foldertype_id= '".$_SESSION['m_admin']['foldertype']['foldertypeId'] ."'");
				$this->load_db();
				if($_SESSION['history']['foldertypeup'] == "true")
				{
					require($_SESSION['pathtocoreclass']."class_history.php");
					$hist = new history();
					$hist->add($_SESSION['tablename']['fold_foldertypes'], $_SESSION['m_admin']['foldertype']['foldertypeId'] ,"UP",_FOLDERTYPE_UPDATE." : ".$_SESSION['m_admin']['foldertype']['foldertypeId'] , $_SESSION['config']['databasetype'], 'folder');
				}
				$this->clearfoldertypeinfos();
				$_SESSION['error'] = _FOLDERTYPE_UPDATE;
				unset($_SESSION['m_admin']);
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder");
				exit();
			}
		}
	}

	/**
	* Clean the $_SESSION['m_admin']['foldertype'] array
	*/
	private function clearfoldertypeinfos()
	{
		// clear the users add or modification vars
		/*
		$_SESSION['m_admin']['foldertype'] = array();
		$_SESSION['m_admin']['foldertype']['foldertypeId']  = "";
		$_SESSION['m_admin']['foldertype']['desc'] = "";
		$_SESSION['m_admin']['foldertype']['comment'] = "";
		$_SESSION['m_admin']['foldertype']['nationality'] = "";
		$_SESSION['m_admin']['foldertype']['doctypes'] = array();*/
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
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder");
			exit();
		}
		else
		{
			$this->connect();
			$this->query("select foldertype_id from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id= ".$id);
			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _FOLDERTYPE_MISSING;
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder");
				exit();
			}
			else
			{
				$info = $this->fetch_object();
				if($mode == "del" )
				{
					$this->query("delete from ".$_SESSION['tablename']['fold_foldertypes']."  where foldertype_id = ".$id."");
					$this->query("delete from ".$_SESSION['tablename']['fold_foldertypes_doctypes']."  where foldertype_id = ".$id."");

					if($_SESSION['history']['foldertypedel'] == "true")
					{
						require($_SESSION['pathtocoreclass']."class_history.php");
						$users = new history();
						$users->add($_SESSION['tablename']['fold_foldertypes'], $id,"DEL",_FOLDERTYPE_DELETION." : ".$id, $_SESSION['config']['databasetype'],  'folder');
					}
					$_SESSION['error'] = _FOLDERTYPE_DELETION;
				}
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=foldertypes&module=folder");
				exit();
			}
		}
	}
}
