<?php
/**
* File : little_details_invoices.php
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*/

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_header();

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_docserver.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
/****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
if($_SESSION['indexation'] == true)
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=details_invoices&dir=indexing_searching';
$page_label = _DETAILS;
$page_id = "is_details";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$users = new history();
$security = new security();
$func = new functions();
$request= new request;
$s_id = "";
$_SESSION['req'] ='details_invoices';
$is_view= false;
$_SESSION['indexing'] = array();
if($_GET['status'] == "empty")
{
	?><p align="center"><img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=bg_home_home.gif'; ?>" alt="Maarch" /></p> <?php
}
else
{
	if(isset($_SESSION['collection_id_choice']) && !empty($_SESSION['collection_id_choice']))
	{
		$table = $security->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
		$is_view = true;
		if(empty($table))
		{
			$table = $security->retrieve_table_from_coll($_SESSION['collection_id_choice']);
			$is_view = false;
		}
	}
	elseif(isset($_SESSION['collection_choice']) && !empty($_SESSION['collection_choice']))
	{
		$table = $_SESSION['collection_choice'];
		$_SESSION['collection_id_choice'] = $security->retrieve_coll_id_from_table($_SESSION['collection_choice']);
	}
	elseif((isset($_SESSION['indexing2']['ind_coll']) && !empty($_SESSION['indexing2']['ind_coll']))|| ($_SESSION['indexing2']['ind_coll'] == 0 && isset($_SESSION['indexing2']['ind_coll'])))
	{
		if(isset($_SESSION['collections'][$_SESSION['indexing2']['ind_coll']]['view']) && !empty($_SESSION['collections'][$_SESSION['indexing2']['ind_coll']]['view']))
		{
			$table = $_SESSION['collections'][$_SESSION['indexing2']['ind_coll']]['view'];
			$is_view = true;
		}
		else
		{
			$table = $_SESSION['collections'][$_SESSION['indexing2']['ind_coll']]['table'];
		}
		$_SESSION['collection_id_choice'] = $_SESSION['collections'][$_SESSION['indexing2']['ind_coll']]['id'];
	}
	elseif((isset($_SESSION['searching']['ind_coll']) && !empty($_SESSION['searching']['ind_coll']))|| ($_SESSION['searching']['ind_coll'] == 0 && isset($_SESSION['searching']['ind_coll'])))
	{
		if(isset($_SESSION['collections'][$_SESSION['searching']['ind_coll']]['view']) && !empty($_SESSION['collections'][$_SESSION['searching']['ind_coll']]['view']))
		{
			$table = $_SESSION['collections'][$_SESSION['searching']['ind_coll']]['view'];
			$is_view = true;
		}
		else
		{
			$table = $_SESSION['collections'][$_SESSION['searching']['ind_coll']]['table'];
		}
		$_SESSION['collection_id_choice'] = $_SESSION['collections'][$_SESSION['searching']['ind_coll']]['id'];
	}
	else
	{
		$table = $_SESSION['collections'][0]['view'];
		$is_view = true;
		$_SESSION['collection_id_choice'] = $security->retrieve_coll_id_from_table($_SESSION['collection_choice']);
	}
	$_SESSION['id_to_view'] = "";

	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$_SESSION['id_to_view'] = $_GET['id'];
	}
	if(isset($_POST['up_res_id']) && !empty($_POST['up_res_id']))
	{
		$_GET['id'] = $_POST['up_res_id'];
	}
	if(isset($_SESSION['detail_id']) && !empty($_SESSION['detail_id']) && $_GET['origin'] =="waiting_list")
	{
		$s_id =$_SESSION['detail_id'];
	}
	if(isset($_GET['id']) && !empty($_GET['id']))
	{
		$s_id = addslashes($func->wash($_GET['id'], "num", _THE_DOC));
	}
	else if(isset($_SESSION['scan_doc_id']) && !empty($_SESSION['scan_doc_id']))
	{
		$s_id =$_SESSION['scan_doc_id'];
		$_SESSION['scan_doc_id'] = "";
	}
	$_SESSION['doc_id'] = $s_id;
	if($_SESSION['origin'] <> "basket")
	{
		$right = $security->test_right_doc($_SESSION['collection_id_choice'], $s_id);
	}
	else
	{
		$right = true;
	}
	if(!$right && $s_id <> "")
	{
		?>
	    <script language="javascript" type="text/javascript">
	    window.top.location.href = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=no_right';
	    </script>
	    <?php
		exit();
	}
	if($s_id == "")
	{
		echo '<br><br><center><h2 style="color:#FFC200;">'._NO_RESULTS.'</h2></center>';
		exit;
	}
	if(isset($s_id) && !empty($s_id) && $_SESSION['history']['resview'] == "true")
	{
		$users->add($table, $s_id ,"VIEW", _VIEW_DOC_NUM.$s_id, $_SESSION['config']['databasetype'],'apps');
	}
	$modify_doc = $security->collection_user_right($_SESSION['collection_id_choice'], "can_update");
	if(empty($_SESSION['error']) || $_SESSION['indexation'])
	{
		$connexion_invoices = new dbquery();
		$connexion_invoices->connect();
		$connexion_invoices->query("select type_id, type_label, format, typist, creation_date, fingerprint, filesize, res_id, work_batch, status, page_count, doc_date, identifier, description, source, doc_language from ".$table." where res_id = ".$s_id."");
		//$connexion_invoices->show();
	}
	?>
	<div id="" class="clearfix">
	<?php
	if((!empty($_SESSION['error']) && ! ($_SESSION['indexation'] ))  )
	{
		?>
		<div class="error">
			<br />
			<br />
			<br />
			<?php  echo $_SESSION['error'];  $_SESSION['error'] = "";?>
			<br />
			<br />
			<br />
		</div>
		<?php
	}
	else
	{
		if($connexion_invoices->nb_result() == 0)
		{
			?>
			<div align="center">
				<br />
				<br />
				<?php  echo _NO_DOCUMENT_CORRESPOND_TO_IDENTIFIER; ?>.
				<br />
				<br />
				<br />
			</div>
			<?php
		}
		else
		{
			$details = $connexion_invoices->fetch_object();
			$title = $details->title;
			//$description = $details->description;
			$typist = $details->typist;
			$format = $details->format;
			$filesize = $details->filesize;
			$creation_date = $details->creation_date;
			//echo $creation_date;exit;
			$doc_date = $details->doc_date;
			$fingerprint = $details->fingerprint;
			$work_batch = $details->work_batch;
			$ref = $details->identifier;
			$tmp = "";
			$type = $details->type_id;
			$type_label = $details->type_label;
			$type_id = $details->type_id;
			$_SESSION['type'] = $type_id;
			$res_id = $details->res_id;
			$status = $details->status;
			$page_count = $details->page_count;
			$identifier = $details->identifier;
			//$doc_date = $connexion_invoices->format_date_db($details->doc_date, false);
			//echo "doc_date ".$doc_date;exit;
			$description = $details->description;
			$source = $details->source;
			$doc_language = $details->doc_language;
			if(!empty($type))
			{
				$connexion_invoices->query("select description, coll_id from ".$_SESSION['tablename']['doctypes']." where type_id = ".$type);
				$line_sql = $connexion_invoices->fetch_object();
				$type = $line_sql->description;
				$tmp =  $line_sql->coll_id;
				for($i=0; $i < count($_SESSION['ressources']); $i++)
				{
					if($_SESSION['ressources'][$i]['tablename'] == $tmp)
					{
						$table = $_SESSION['ressources'][$i]['comment'];
						break;
					}
				}
			}
			?>
			<div align="center">
			<?php
			if($type_id <> "0" && $type_id <> "")
			{
					$connexion_invoices->query("select * from ".$_SESSION['tablename']['doctypes']." where type_id = ".$type_id);
					$res = $connexion_invoices->fetch_array();
					$desc = str_replace("\\","",$res['description']);
					$type_id = $res['type_id'];
					$is_master = $res['is_master'];
					if($is_master == "Y")
					{
						$doctypes_second_level_id = $res['doctypes_second_level_id'];
						$_SESSION['multidoc'] = true;
					}
					require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
					$indexing_searching = new indexing_searching_app();
					//$indexing_searching->retrieve_index($res,$_SESSION['collection_id_choice'] );
					?>
					<form method="post" name="index_doc" action="index.php?page=details_invoices&dir=indexing_searching&id=<?php  echo $_SESSION['id_to_view']; ?>" class="forms">
						<div class="block">
							<p align="left">
									<h3 align="left" onclick="new Effect.toggle('desc3', 'blind');" onmouseover="document.body.style.cursor='pointer';" onmouseout="document.body.style.cursor='auto';" id="h23" class="categorie">
										<a href="#"><?php echo _SHOW_DETAILS_DOC; ?></a>
									</h3>

							</p>
						</div>
						<div class="desc block_light admin" id="desc3" style="display:none">
							<div class="ref-unit">
								<?php echo _MENU." : "; ?>
								<a href="<?php  echo $_SESSION['config']['businessappurl'];?>indexing_searching/view.php?id=<?php  echo $s_id; ?>" target="_blank"><b><?php  echo _VIEW_DOC_FULL; ?></b> </a>
										|
								<a href="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=details&dir=indexing_searching&id=<?php  echo $s_id; ?>" target="_blank"><b><?php  echo _DETAILS_DOC_FULL; ?> </b></a>
								<hr/>
								<p>
									<label>
									<?php echo _NUM_GED." : "; ?>
									</label>
									<input type="text" name="resId" id="resId" value="<?php  echo $s_id;?>" />
								</p>
								<p>
									<label>
									<?php echo _PIECE_TYPE." : "; ?>
									</label>
									<input type="text" name="typeLabel" id="typeLabel" value="<?php  echo $func->show_string($type_label);?>" />
								</p>
								<?php
								$db_invoices = new dbquery();
								$db_invoices->connect();
								for($cpt6=0;$cpt6<=count($_SESSION['index_to_use']);$cpt6++)
								{
									if($_SESSION['index_to_use'][$cpt6]['label'] <> "")
									{
										$field = $_SESSION['index_to_use'][$cpt6]['column'];
										if($is_view)
										{
											$field = "doc_".$field;
										}
										$connexion_invoices->query("select ".$field." from ".$table." where res_id = ".$_SESSION['id_to_view']);
										$res_mastertype = $connexion_invoices->fetch_array();
										//$connexion_invoices->show_array($res_mastertype);
										$_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']] = $res_mastertype[$field];
										if($_SESSION['index_to_use'][$cpt6]['date'])
										{
											$_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']] = $func->format_date_db($_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']], false);
										}
										?>
										<p>
											<label for="<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>">
												<?php
												if($_SESSION['index_to_use'][$cpt6]['mandatory'])
												{
													echo "<b>".$_SESSION['index_to_use'][$cpt6]['label']."</b> : ";
												}
												else
												{
													echo $_SESSION['index_to_use'][$cpt6]['label']." : ";
												}
												?>
											</label>
											<input type="text" name="<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" id="<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" value="<?php  echo $_SESSION['indexing'][$_SESSION['index_to_use'][$cpt6]['column']];?>" <?php  if($_SESSION['field_error'][$_SESSION['index_to_use'][$cpt6]['column']]){?>style="background-color:#FF0000"<?php  }?> <?php  if(!$modify_doc){?> class="readonly" readonly="readonly" <?php  } ?>  <?php  if($_SESSION['index_to_use'][$cpt6]['date']){?> onclick='showCalender(this)'<?php  }?>/>
											<?php
											if($_SESSION['index_to_use'][$cpt6]['mandatory'] && $modify_doc)
											{
												?>
												<input type="hidden" name="mandatory_<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" id="mandatory_<?php  echo $_SESSION['index_to_use'][$cpt6]['column'];?>" value="true" />
												<?php
											}
											?>
										</p>
										<?php
									}
								}
								?>
							</div>
						</div>
					</form>
				<iframe name="view" id="view" width="100%" height="700" frameborder="0" scrolling="no" src="<?php  echo $_SESSION['config']['businessappurl']."index.php?display=true&dir=indexing_searching&page=view&id=".$s_id;?>"></iframe>
				<?php
			}
			else
			{
				echo _DOC_NOT_QUALIFIED."<br/>";
				if($security->collection_user_right($_SESSION['collection_id_choice'], "can_delete"))
				{
					?>
					<form method="post" name="index_doc" action="index.php?page=details&dir=indexing_searching&id=<?php  echo $_SESSION['id_to_view']; ?>" class="forms">
						<input type="submit" class="button"  value="<?php  echo _DELETE_THE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));"/>
					</form>
					<?php
				}
			}
			if(!empty($_SESSION['error_page']))
			{
				?>
				<script language="javascript" type="text/javascript">
					alert("<?php  echo $func->wash_html($_SESSION['error_page']);?>");
					<?php
					if(isset($_POST['delete_doc']))
					{
						?>
						window.location.href = 'index.php';
						<?php
					}
					?>
				</script>
				<?php
				$_SESSION['error'] = "";
				$_SESSION['error_page'] = "";
			}
			?>
			</div>
			<?php
		}
	}
}
?>
</div>
