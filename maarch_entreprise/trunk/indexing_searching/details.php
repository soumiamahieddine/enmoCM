<?php
/**
* File : details.php
*
* Detailed informations on an indexed document
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_docserver.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once($_SESSION['pathtocoreclass']."class_history.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_types.php");
include($_SESSION['config']['businessapppath'].'definition_mail_categories.php');
$_SESSION['doc_convert'] = array();
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=details&dir=indexing_searching&coll_id='.$_REQUEST['coll_id'].'&id='.$_REQUEST['id'];
$page_label = _DETAILS;
$page_id = "details";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$users = new history();
$security = new security();
$func = new functions();
$request= new request;
$type = new types();
$s_id = "";
$_SESSION['req'] ='details';
$_SESSION['indexing'] = array();
$is = new indexing_searching_app();
$coll_id = '';
$table = '';
if(!isset($_REQUEST['coll_id']) || empty($_REQUEST['coll_id']))
{
	//$_SESSION['error'] = _COLL_ID.' '._IS_MISSING;
	$coll_id = $_SESSION['collections'][0]['id'];
	$table = $_SESSION['collections'][0]['view'];
	$is_view = true;
}
else
{
	$coll_id = $_REQUEST['coll_id'];
	$table = $security->retrieve_view_from_coll_id($coll_id);
	$is_view = true;
	if(empty($table))
	{
		$table = $security->retrieve_table_from_coll($coll_id);
		$is_view = false;
	}
}
$_SESSION['collection_id_choice'] = $coll_id;

/*$_SESSION['id_to_view'] = "";
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
}*/
if(isset($_GET['id']) && !empty($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "num", _THE_DOC));
}
/*else if(isset($_SESSION['scan_doc_id']) && !empty($_SESSION['scan_doc_id']))
{
	$s_id =$_SESSION['scan_doc_id'];
	$_SESSION['scan_doc_id'] = "";
}*/
$_SESSION['doc_id'] = $s_id;
if($_SESSION['origin'] <> "basket")
{
	$right = $security->test_right_doc($coll_id, $s_id);
	//$_SESSION['error'] = 'coll '.$coll_id.', res_id : '.$s_id;
}
else
{
	$right = true;
}
if(!$right)
{
	?>
    <script language="javascript" type="text/javascript">
    window.top.location.href = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=no_right';
    </script>
    <?php
	exit();
}
if(isset($s_id) && !empty($s_id) && $_SESSION['history']['resview'] == "true")
{
	$users->add($table, $s_id ,"VIEW", _VIEW_DOC_NUM.$s_id, $_SESSION['config']['databasetype'],'apps');
}
$modify_doc = $security->collection_user_right($coll_id, "can_update");
$delete_doc = $security->collection_user_right($coll_id, "can_delete");

//update index with the doctype
if(isset($_POST['submit_index_doc']))
{
	$is->update_mail($_POST, "POST", $s_id, $coll_id);
}
//delete the doctype
if(isset($_POST['delete_doc']))
{
	$is ->delete_doc( $s_id, $coll_id);
	?>
		<script language="javascript" type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=search_adv&dir=indexing_searching';?>';</script>
    <?php
	exit();
}
$db = new dbquery();
$db->connect();
if(empty($_SESSION['error']) || $_SESSION['indexation'])
{
	$comp_fields = '';
	$db->query("select type_id from ".$table." where res_id = ".$s_id);
	if($db->nb_result() > 0)
	{
		$res = $db->fetch_object();
		$type_id = $res->type_id;
		$indexes = $type->get_indexes($type_id, $coll_id, 'minimal');

		for($i=0; $i<count($indexes);$i++)
		{
			if(preg_match('/^custom_/', $indexes[$i])) // In the view all custom from res table begin with doc_
			{
				$comp_field .= ', doc_'.$indexes[$i];
			}
			else
			{
				$comp_field .= ', '.$indexes[$i];
			}
		}
	}
	$case_sql_complementary = '';
	if($core_tools->is_module_loaded('cases') == true)
	{
		$case_sql_complementary = " , case_id";
	}
	$db->query("select status, format, typist, creation_date,  fingerprint,  filesize, res_id, work_batch,  page_count, is_paper, scan_date, scan_user, scan_location, scan_wkstation, scan_batch, source, doc_language, description, closing_date, alt_identifier ".$comp_field.$case_sql_complementary." from ".$table." where res_id = ".$s_id."");
	//$db->show();

}
?>
<div id="details_div" style="display:none;">
<h1 class="titdetail">
	<img src="<?php  echo $_SESSION['config']['img'];?>/picto_detail_b.gif" alt="" /><?php  echo _DETAILS." : "._DOC.' '.strtolower(_NUM); ?><?php  echo $s_id; ?> <span>(<?php  echo  $security->retrieve_coll_label_from_coll_id($coll_id); ?>)</span>
</h1>
<div id="inner_content" class="clearfix">
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
	if($db->nb_result() == 0)
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
			$param_data = array('img_category_id' => true, 'img_priority' => true, 'img_type_id' => true, 'img_doc_date' => true, 'img_admission_date' => true, 'img_nature_id' => true, 'img_subject' => true, 'img_process_limit_date' => true, 'img_author' => true, 'img_destination' => true, 'img_arbox_id' => true, 'img_market' => true, 'img_project' => true);

			$res = $db->fetch_object();
			$typist = $res->typist;
			$format = $res->format;
			$filesize = $res->filesize;
			$creation_date = $db->format_date_db($res->creation_date, false);
			$chrono_number = $res->alt_identifier;
			$fingerprint = $res->fingerprint;
			$work_batch = $res->work_batch;
			$page_count = $res->page_count;
			$is_paper = $res->is_paper;
			$scan_date = $db->format_date_db($res->scan_date);
			$scan_user = $res->scan_user;
			$scan_location = $res->scan_location;
			$scan_wkstation = $res->scan_wkstation;
			$scan_batch = $res->scan_batch;
			$doc_language = $res->doc_language;
			$closing_date = $db->format_date_db($res->closing_date, false);
			$indexes = $type->get_indexes($type_id, $coll_id);

			if($core_tools->is_module_loaded('cases') == true)
			{
				require_once($_SESSION['pathtomodules']."cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');
				$case = new cases();
				if ($res->case_id <> '')
					$case_properties = $case->get_case_info($res->case_id);
			}

			//$db->show_array($indexes);
			foreach(array_keys($indexes) as $key)
			{
				$tmp = 'doc_'.$key;
				$indexes[$key]['value'] = $res->$tmp;
				$indexes[$key]['show_value'] = $res->$tmp;
			}
		//	$db->show_array($indexes);
			$process_data = $is->get_process_data($coll_id, $s_id);
			$status = $res->status;
			if(!empty($status))
			{
				require_once($_SESSION['pathtocoreclass']."class_manage_status.php");
				$status_obj = new manage_status();
				$res_status = $status_obj->get_status_data($status);
				if($modify_doc)
				{
					$can_be_modified = $status_obj->can_be_modified($status);
					if(!$can_be_modified)
					{
						$modify_doc = false;
					}
				}
			}
			$mode_data = 'full';
			if($modify_doc)
			{
				$mode_data = 'form';
			}
			foreach(array_keys($indexes) as $key)
			{
				$indexes[$key]['opt_index'] = true;
				if($indexes[$key]['type_field'] == 'select')
				{
					for($i=0; $i<count($indexes[$key]['values']);$i++)
					{
						if($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value'])
						{
							$indexes[$key]['show_value'] = $indexes[$key]['values'][$i]['label'] ;
							break;
						}
					}
				}
				if(!$modify_doc)
				{
					$indexes[$key]['readonly'] = true;
					$indexes[$key]['type_field'] = 'input';
				}
				else
				{
					$indexes[$key]['readonly'] = false;
				}
			}
			$data = get_general_data($coll_id, $s_id, $mode_data, $param_data );
			//$data = array_merge($data, $indexes);
			//$db->show_array($indexes);
			$detailsExport = "";
			$detailsExport = "<html lang='fr' xmlns='http://www.w3.org/1999/xhtml' xml:lang='fr'>";
			$detailsExport = "<head><title>Maarch Details</title><meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/><meta content='fr' http-equiv='Content-Language'/>";
			$detailsExport = "<link media='screen' href='http://127.0.0.1/DGGT/apps/maarch_letterbox/css/styles.css' type='text/css' rel='stylesheet'></head>";
			$detailsExport = "<body>";
			?>
			<div class="block">
				<b>
				<p id="back_list">
					<?php
					if(!$_POST['up_res_id'])
					{
						if($_SESSION['indexation'] == false)
						{
							?>
							<a href="#" onclick="history.go(-1);" class="back"><?php  echo _BACK; ?></a>
							<?php
						}
					}
					?>
				</p>

				<p id="viewdoc">
					<a href="<?php  echo $_SESSION['config']['businessappurl'];?>indexing_searching/view.php?id=<?php  echo $s_id; ?>" target="_blank"><?php  echo _VIEW_DOC; ?></a> &nbsp;| &nbsp;
				</p></b>&nbsp;
			</div>
			<br/>
			<dl id="tabricator1">
				<?php $detailsExport .= "<h1>"._DETAILLED_PROPERTIES."</h1>";?>
				<dt><?php  echo _DETAILLED_PROPERTIES;?></dt>
				<dd>
					<h2>
			            <span class="date">
							<?php $detailsExport .= "<h2>"._FILE_DATA."</h2>";?>
			            	<b><?php  echo _FILE_DATA;?></b>
			            </span>
			        </h2>
					<br/>
				<form method="post" name="index_doc" id="index_doc" action="index.php?page=details&dir=indexing_searching&id=<?php  echo $s_id; ?>">
					<?php $detailsExport .= "<table cellpadding='2' cellspacing='2' border='0' class='block forms details' width='100%'>";?>
					<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
						<?php
						$i=0;
						foreach(array_keys($data) as $key)
						{

							if($i%2 != 1 || $i==0) // pair
							{
								$detailsExport .= "<tr class='col'>";
								?>
								<tr class="col">
								<?php
							}
							$folder_id = "";
							if(($key == "market" || $key == "project") && $data[$key]['show_value'] <> "")
							{
								$folderTmp = $data[$key]['show_value'];
								$find1 = strpos($folderTmp, '(');
								$folder_id = substr($folderTmp, $find1, strlen($folderTmp));
								$folder_id = str_replace("(", "", $folder_id);
								$folder_id = str_replace(")", "", $folder_id);
							}

							$detailsExport .= "<th align='left' width='50px'>";
							?>
							<th align="left" class="picto" >
								<?php
								if(isset($data[$key]['addon']))
								{
									echo $data[$key]['addon'];
									$detailsExport .= $data[$key]['addon'];
								}
								elseif(isset($data[$key]['img']))
								{
									$detailsExport .= "<img alt='".$data[$key]['label']."' title='".$data[$key]['label']."' src='".$data[$key]['img']."'  />";
									if($folder_id <> "")
									{
										echo "<a href='".$_SESSION['config']['businessappurl']."index.php?page=show_folder&module=folder&id=".$folder_id."'>";
										?>
										<img alt="<?php echo $data[$key]['label'];?>" title="<?php echo $data[$key]['label'];?>" src="<?php echo $data[$key]['img'];?>"  /></a>
										<?php
									}
									else
									{
										?>
										<img alt="<?php echo $data[$key]['label'];?>" title="<?php echo $data[$key]['label'];?>" src="<?php echo $data[$key]['img'];?>"  /></a>
										<?php
									}
									?>

									<?php
								}
								$detailsExport .= "</th>";
								?>
							</th>
							<?php
							$detailsExport .= "<td align='left' width='200px'>";
							?>
							<td align="left" width="200px">
								<?php
								$detailsExport .= $data[$key]['label'];
								echo $data[$key]['label'];?> :
							</td>
							<?php
							$detailsExport .=  "</td>";
							$detailsExport .=  "<td>";
							?>
							<td>
								<?php
								$detailsExport .=  $data[$key]['show_value'];
							if(!isset($data[$key]['readonly']) || $data[$key]['readonly'] == true)
							{
								if($data[$key]['display'] == 'textinput')
								{
									?>
									<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" readonly="readonly" class="readonly" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
									<?php
								}
								else
								{
									?>
									<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" readonly="readonly" class="readonly" size="40" title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
									<?php
									if(isset($data[$key]['addon']))
									{
										$frm_str .= $data[$key]['addon'];
									}
								}
							}
							else
							{
								if($data[$key]['field_type'] == 'textfield')
								{
									?>
									<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
									<?php
								}
								else if($data[$key]['field_type'] == 'date')
								{
									?>
									<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" onclick="showCalender(this);" />
									<?php
								}
								else if($data[$key]['field_type'] == 'select')
								{
									?>
									<select id="<?php echo $key;?>" name="<?php echo $key;?>" <?php if($key == 'type_id'){echo 'onchange="change_doctype_details(this.options[this.options.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'indexing_searching/change_doctype_details.php\' , \''._DOCTYPE.' '._MISSING.'\');"';}?>>
									<?php
										if($key == 'type_id')
										{
											for($k=0; $k<count($data[$key]['select']);$k++)
											{
											?><option value="" class="doctype_level1"><?php echo $data[$key]['select'][$k]['label'];?></option><?
												for($j=0; $j<count($data[$key]['select'][$k]['level2']);$j++)
												{
													?><option value="" class="doctype_level2">&nbsp;&nbsp;<?php echo $data[$key]['select'][$k]['level2'][$j]['label'];?></option><?
													for($l=0; $l<count($data[$key]['select'][$k]['level2'][$j]['types']);$l++)
													{
														?><option
														<?php if($data[$key]['value'] ==$data[$key]['select'][$k]['level2'][$j]['types'][$l]['id']){ echo 'selected="selected"';}?>
														 value="<?php echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['id'];?>" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['label'];?></option><?
													}
												}
											}
										}
										else
										{
											for($k=0; $k<count($data[$key]['select']);$k++)
											{
												?><option value="<?php echo $data[$key]['select'][$k]['ID'];?>" <?php if($data[$key]['value'] == $data[$key]['select'][$k]['ID']){echo 'selected="selected"';}?>><?php echo $data[$key]['select'][$k]['LABEL'];?></option><?php
											}
										}
									?>
									</select>
									<?php
								}
								else if($data[$key]['field_type'] == 'autocomplete')
								{
									if($key == 'project')
									{
										//$('market').value='';return false;
									?><input type="text" name="project" id="project" onblur="" value="<?php echo $data['project']['show_value']; ?>" /><div id="show_project" class="autocomplete"></div><script type="text/javascript">launch_autocompleter_folders('<?php echo $_SESSION['urltomodules'];?>folder/autocomplete_folders.php?mode=project', 'project');</script>
									<?php
									}
									else if($key == 'market')
									{
									?><input type="text" name="market" id="market" onblur="fill_project('<?php echo $_SESSION['urltomodules'];?>folder/ajax_get_project.php');return false;"  value="<?php echo $data['market']['show_value']; ?>"/><div id="show_market" class="autocomplete"></div>
									<script type="text/javascript">launch_autocompleter_folders('<?php echo $_SESSION['urltomodules'];?>folder/autocomplete_folders.php?mode=market', 'market');</script>
									<?php
									}
								}
							}
								$detailsExport .=  "</td>";
								?>
							</td>
							<?php
							if($i%2 == 1 && $i!=0) // impair
							{
								$detailsExport .=  "</td>";
								?>
								</tr>
								<?php
							}
							else
							{
								if($i+1 == count($data))
								{
									$detailsExport .= "<td  colspan='2'>&nbsp;</td></tr>";
									echo '<td  colspan="2">&nbsp;</td></tr>';
								}
							}
							$i++;
						}
						$detailsExport .=  "<tr class='col'>";
						$detailsExport .=  "<th align='left' width='50px'>";
						$detailsExport .=  "<img alt='"._STATUS." : ".$res_status['LABEL']." src='".$res_status['IMG_SRC']."' />";
						$detailsExport .=  "</th>";
						$detailsExport .=  "<td align='left' width='200px'>";
						$detailsExport .=  _STATUS." : ";
						$detailsExport .=  "</td>";
						$detailsExport .=  "<td>";
						$detailsExport .=  $res_status['LABEL'];
						$detailsExport .=  "</td>";
						$detailsExport .=  "<th align='left' width='50px'>";
						$detailsExport .=  "<img alt='"._CREATION_DATE." : ".$res_status['LABEL']." src='".$_SESSION['config']['businessappurl']."img/small_calend.gif' />";
						$detailsExport .=  "</th>";
						$detailsExport .=  "</tr>";

						$detailsExport .=  "<tr class='col'>";
						$detailsExport .=  "<th align='left' width='50px'>";
						$detailsExport .=  "<img alt='".CHRONO_NUMBER." src='".$_SESSION['config']['businessappurl']."img/chrono.gif' />";
						$detailsExport .=  "</th>";
						$detailsExport .=  "<td align='left' width='200px'>";
						$detailsExport .=  _CHRONO_NUMBER." : ";
						$detailsExport .=  "</td>";
						$detailsExport .=  "<td>";
						$detailsExport .=  $chrono_number;
						$detailsExport .=  "</td>";


						?>
						<tr class="col">
							<th align="left" class="picto">
								<img alt="<?php echo _STATUS.' : '.$res_status['LABEL'];?>" src="<?php echo $res_status['IMG_SRC'];?>" title="<?php  echo $res_status['LABEL']; ?>" alt="<?php  echo $res_status['LABEL']; ?>"/>
							</th>
							<td align="left" width="200px">
								<?php  echo _STATUS; ?> :
							</td>
							<td>
								<input type="text" class="readonly" readonly="readonly" value="<?php  echo $res_status['LABEL']; ?>" size="40"  />
							</td>
						</tr>
						<tr class="col">
							<th align="left" class="picto">
								<img alt="<?php echo _CHRONO_NUMBER; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/chrono.gif" />
							</th>
							<td align="left" width="200px">
								<?php  echo _CHRONO_NUMBER; ?> :
							</td>
							<td>
								<input type="text" class="readonly" readonly="readonly" value="<?php  echo $chrono_number; ?>" size="40" title="<?php  echo $chrono_number; ?>" alt="<?php  echo $chrono_number; ?>" />
							</td>

						</tr>

					</table>
					<?php
					$detailsExport .=  "</table>";
					$detailsExport .=  "<br>";
					$detailsExport .=  "<h2>"._FILE_PROPERTIES."</h2>";
					$detailsExport .=  "<table cellpadding='2' cellspacing='2' border='0' class='block forms details' width='100%'>";
					$detailsExport .=  "<tr>";
					$detailsExport .=  "<th align='left' width='255px'>";
					$detailsExport .=  _TYPIST." : ";
					$detailsExport .=  "</th>";
					$detailsExport .=  "<td align='left' width='250px'>";
					$detailsExport .=  $typist;
					$detailsExport .=  "</td>";
					$detailsExport .=  "<th align='left' width='255px'>";
					$detailsExport .=  _SIZE." : ";
					$detailsExport .=  "</th>";
					$detailsExport .=  "<td align='left' width='250px'>";
					$detailsExport .=  $filesize." ".$_SESSION['lang']['txt_byte']." ( ".round($filesize/1024,2)."K )";
					$detailsExport .=  "</td>";
					$detailsExport .=  "</tr>";
					$detailsExport .=  "<tr>";
					$detailsExport .=  "<th align='left' width='255px'>";
					$detailsExport .=  _FORMAT." : ";
					$detailsExport .=  "</th>";
					$detailsExport .=  "<td align='left' width='250px'>";
					$detailsExport .=  $format;
					$detailsExport .=  "</td>";
					$detailsExport .=  "<th align='left' width='255px'>";
					$detailsExport .=  _CREATION_DATE." : ";
					$detailsExport .=  "</th>";
					$detailsExport .=  "<td align='left' width='250px'>";
					$detailsExport .=  $func->format_date_db($creation_date, false);
					$detailsExport .=  "</td>";
					$detailsExport .=  "</tr>";
					$detailsExport .=  "<tr>";
					$detailsExport .=  "<th align='left' width='255px'>";
					$detailsExport .=  _MD5." : ";
					$detailsExport .=  "</th>";
					$detailsExport .=  "<td align='left' width='250px'>";
					$detailsExport .=  $fingerprint;
					$detailsExport .=  "</td>";
					$detailsExport .=  "<th align='left' width='255px'>";
					$detailsExport .=  _WORK_BATCH." : ";
					$detailsExport .=  "</th>";
					$detailsExport .=  "<td align='left' width='250px'>";
					$detailsExport .=  $work_batch;
					$detailsExport .=  "</td>";
					$detailsExport .=  "</tr>";
					$detailsExport .=  "</table>";
					$detailsExport .= "<br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
					?>
					<div id="opt_indexes">
					<?php if(count($indexes) > 0)
					{
						?><br/>
						<h2>
			            <span class="date">
			            	<b><?php  echo _OPT_INDEXES;?></b>
			            </span>
			        	</h2>
						<br/>
						<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
							<?php
							$i=0;
							foreach(array_keys($indexes) as $key)
							{

								if($i%2 != 1 || $i==0) // pair
								{
									$detailsExport .= "<tr class='col'>";
									?>
									<tr class="col">
									<?php
								}
								$detailsExport .= "<th align='left' width='50px'>";
								?>
								<th align="left" class="picto" >
									<?php
									if(isset($indexes[$key]['img']))
									{
										$detailsExport .= "<img alt='".$indexes[$key]['label']."' title='".$indexes[$key]['label']."' src='".$indexes[$key]['img']."'  />";
										?>
										<img alt="<?php echo $indexes[$key]['label'];?>" title="<?php echo $indexes[$key]['label'];?>" src="<?php echo $indexes[$key]['img'];?>"  /></a>
										<?php
									}
									$detailsExport .= "</th>";
									?>
								</th>
								<?php
								$detailsExport .= "<td align='left' width='200px'>";
								?>
								<td align="left" width="200px">
									<?php
									$detailsExport .= $indexes[$key]['label'];
									echo $indexes[$key]['label'];?> :
								</td>
								<?php
								$detailsExport .=  "</td>";
								$detailsExport .=  "<td>";
								?>
								<td>
									<?php
									$detailsExport .=  $indexes[$key]['show_value'];
									if($indexes[$key]['type_field'] == 'input')
									{
										?>
										<input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $indexes[$key]['show_value'];?>" <?php if(!isset($indexes[$key]['readonly']) || $indexes[$key]['readonly'] == true){ echo 'readonly="readonly" class="readonly"';}else if($indexes[$key]['type'] == 'date'){echo 'onclick="showCalender(this);"';}?> size="40"  title="<?php  echo $indexes[$key]['show_value']; ?>" alt="<?php  echo $indexes[$key]['show_value']; ?>"   />
										<?php
									}
									else
									{?>
										<select name="<?php echo $key;?>" id="<?php echo $key;?>" >
											<option value=""><?php echo _CHOOSE;?>...</option>
											<?php
											for($i=0; $i<count($indexes[$key]['values']);$i++)
											{?>
												<option value="<?php echo $indexes[$key]['values'][$i]['id'];?>" <?php if($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value']){ echo 'selected="selected"';}?>><?php echo $indexes[$key]['values'][$i]['label'];?></option><?php
											}?>
										</select><?php
									}

									$detailsExport .=  "</td>";
									?>
								</td>
								<?php
								if($i%2 == 1 && $i!=0) // impair
								{
									$detailsExport .=  "</td>";
									?>
									</tr>
									<?php
								}
								else
								{
									if($i+1 == count($indexes))
									{
										$detailsExport .= "<td  colspan='2'>&nbsp;</td></tr>";
										echo '<td  colspan="2">&nbsp;</td></tr>';
									}
								}
								$i++;
							}
							?>
						</table>
						<?php  } ?>
					</div>
					<br/>

					<h2>
					<span class="date">
						<b><?php  echo _FILE_PROPERTIES;?></b>
					</span>
			        </h2>
					<br/>

		        	<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
						<tr>
							<th align="left" class="picto">
								<img alt="<?php echo _TYPIST; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/manage_users_entities_b_small.gif" />
							</th>
							<td align="left" width="200px"><?php  echo _TYPIST; ?> :</td>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $typist; ?>"  /></td>
							<th align="left" class="picto">
								<img alt="<?php echo _SIZE; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/weight.gif" />
							</th>
							<td align="left" width="200px"><?php  echo _SIZE; ?> :</td>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $filesize." ".$_SESSION['lang']['txt_byte']." ( ".round($filesize/1024,2)."K )"; ?>" /></td>
						</tr>
						<tr class="col">
							<th align="left" class="picto">
								<img alt="<?php echo _FORMAT; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/mini_type.gif" />
							</th>
							<td align="left"><?php  echo _FORMAT; ?> :</td>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $format; ?>" size="40"  /></td>
							<th align="left" class="picto">
								<img alt="<?php echo _CREATION_DATE; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/small_calend.gif" />
							</th>
							<td align="left"><?php  echo _CREATION_DATE; ?> :</td>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $func->format_date_db($creation_date, false); ?>"/></td>
						</tr>
						<tr>
							<th align="left" class="picto">
								<img alt="<?php echo _MD5; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/md5.gif" />
							</th>
							<td align="left"><?php  echo _MD5; ?> :</td>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $fingerprint; ?>"  title="<?php  echo $fingerprint; ?>" alt="<?php  echo $fingerprint; ?>" /></td>

							<th align="left" class="picto">
								<img alt="<?php echo _WORK_BATCH; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>img/lot.gif" />
							</th>
							<td align="left"><?php  echo _WORK_BATCH; ?> :</td>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $work_batch; ?>" title="<?php  echo $work_batch; ?>" alt="<?php  echo $work_batch; ?>" /></td>
						</tr>
						<!--
						<tr>
							<th align="left"><?php  echo _PAGECOUNT; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $page_count; ?>"  /></td>
							<th align="left"><?php  echo _ISPAPER; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $is_paper; ?>" /></td>
						</tr>
							<tr class="col">
							<th align="left"><?php  echo _SCANUSER; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_user; ?>"  /></td>
							<th align="left"><?php  echo _SCANDATE; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_date; ?>" /></td>
						</tr>
						<tr>
							<th align="left"><?php  echo _SCANWKSATION; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_wkstation; ?>" /></td>
							<th align="left"><?php  echo _SCANLOCATION; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_location; ?>" /></td>
						</tr>
						<tr class="col">
							<th align="left"><?php  echo _SCANBATCH; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_batch; ?>"  /></td>
							<th align="right"><?php  echo _SOURCE; ?> :</th>
							<td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $source; ?>" /></td>
						</tr>
						-->
					</table>
					<br/>
					<div align="center">
						<?php if($delete_doc)
						{?>
						<input type="submit" class="button"  value="<?php  echo _DELETE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));" />
						<?php }
						if($modify_doc)
						{?>
						<input type="submit" class="button"  value="<?php  echo _MODIFY_DOC;?>" name="submit_index_doc" />
						<?php  } ?>
							<input type="button" class="button" name="back_welcome" id="back_welcome" value="<?php echo _BACK_TO_WELCOME;?>" onclick="window.top.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php';" />

					</div>
					</form>
					<?php
		}
		?>
				</dd>
				<?php
				if($core_tools->is_module_loaded('entities'))
				{
					$detailsExport .= "<h2>"._DIFF_LIST."</h2>";
					?>
					<dt><?php  echo _DIFF_LIST;?></dt>
					<dd><?php
						require_once($_SESSION['pathtomodules']."entities".DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
						$diff_list = new diffusion_list();
						$_SESSION['details']['diff_list'] = array();
						$_SESSION['details']['diff_list'] = $diff_list->get_listinstance($s_id);
						//$db->show_array($_SESSION['details']['diff_list']);
						?>
						<h2>
							<span class="date">
								<b><?php  echo _DIFF_LIST;?></b>
							</span>
						</h2>
						<br/>
						<div id="diff_list_div">
							<?php
							if(isset($_SESSION['details']['diff_list']['dest']['user_id']) && !empty($_SESSION['details']['diff_list']['dest']['user_id']))
							{
								$detailsExport .= "<p class='sstit'>"._RECIPIENT."</p>";
								$detailsExport .= "<table cellpadding='0' cellspacing='0' border='0' class='listing'>";
								$detailsExport .= "<tr class='col'>";
								$detailsExport .= "<td><img src='".$_SESSION['urltomodules']."entities/img/manage_users_entities_b_small.gif' alt='"._USER."' title='"._USER."' /></td>";
								$detailsExport .= "<td>".$_SESSION['details']['diff_list']['dest']['firstname']."</td>";
								$detailsExport .= "<td>".$_SESSION['details']['diff_list']['dest']['lastname']."</td>";
								$detailsExport .= "<td>".$_SESSION['details']['diff_list']['dest']['entity_label']."</td>";
								$detailsExport .= "</tr>";
								$detailsExport .= "</table>";
								$detailsExport .= "<br>";
								?>
								<p class="sstit"><?php echo _RECIPIENT;?></p>
								<table cellpadding="0" cellspacing="0" border="0" class="listing">
									<tr class="col">
										<td><img src="<?php echo $_SESSION['urltomodules'];?>entities/img/manage_users_entities_b_small.gif" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
										<td><?php echo $_SESSION['details']['diff_list']['dest']['firstname'];?></td>
										<td><?php echo $_SESSION['details']['diff_list']['dest']['lastname'];?></td>
										<td><?php echo $_SESSION['details']['diff_list']['dest']['entity_label'];?></td>
									</tr>
								</table>
								<br/>
								<?php
							}
							if(count($_SESSION['details']['diff_list']['copy']['users']) > 0 || count($_SESSION['details']['diff_list']['copy']['entities']) > 0)
							{
								$detailsExport .= "<p class='sstit'>"._TO_CC."</p>";
								$detailsExport .= "<table cellpadding='0' cellspacing='0' border='0' class='listing'>";
								?>
								<p class="sstit"><?php echo _TO_CC;?></p>
								<table cellpadding="0" cellspacing="0" border="0" class="listing">
								<?php $color = ' class="col"';
								for($i=0;$i<count($_SESSION['details']['diff_list']['copy']['entities']);$i++)
								{
									if($color == ' class="col"')
									{
										$color = '';
									}
									else
									{
										$color = ' class="col"';
									}
									$detailsExport .= "<tr ".$color.">";
									$detailsExport .= "<td><img src='".$_SESSION['urltomodules']."entities/img/manage_entities_b_small.gif' alt='"._ENTITY."' title='"._ENTITY."' /></td>";
									$detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_id']."</td>";
									$detailsExport .= "<td colspan='2'>".$_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_label']."</td>";
									$detailsExport .= "</tr>";
									?>
									<tr <?php echo $color;?> >
										<td><img src="<?php echo $_SESSION['urltomodules'];?>entities/img/manage_entities_b_small.gif" alt="<?php echo _ENTITY;?>" title="<?php echo _ENTITY;?>" /></td>
										<td ><?php echo $_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_id'];?></td>
										<td colspan="2"><?php echo $_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_label'];?></td>
									</tr><?php
								}
								for($i=0;$i<count($_SESSION['details']['diff_list']['copy']['users']);$i++)
								{
									if($color == ' class="col"')
									{
										$color = '';
									}
									else
									{
										$color = ' class="col"';
									}
									$detailsExport .= "<tr ".$color.">";
									$detailsExport .= "<td><img src='".$_SESSION['urltomodules']."entities/img/manage_users_entities_b_small.gif' alt='"._USER."' title='"._USER."' /></td>";
									$detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['users'][$i]['firstname']."</td>";
									$detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['users'][$i]['lastname']."</td>";
									$detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['users'][$i]['entity_label']."</td>";
									$detailsExport .= "</tr>";
									?>
									<tr <?php echo $color;?> >
										<td><img src="<?php echo $_SESSION['urltomodules'];?>entities/img/manage_users_entities_b_small.gif" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
										<td ><?php echo $_SESSION['details']['diff_list']['copy']['users'][$i]['firstname'];?></td>
										<td ><?php echo $_SESSION['details']['diff_list']['copy']['users'][$i]['lastname'];?></td>
										<td><?php echo $_SESSION['details']['diff_list']['copy']['users'][$i]['entity_label'];?></td>
									</tr><?php
								}
								$detailsExport .= "</table>";
								?>
								</table>
								<?php
							}
							?>
						</div>
					</dd>
				<?php
				}
				$detailsExport .= "<h2>"._PROCESS."</h2>";
				?>
				<dt><?php  echo _PROCESS;?></dt>
				<dd>
					<div>
						<table width="100%">
							<tr>
								<td><label for="answer_types"><?php echo _ANSWER_TYPES_DONE;?> : </label></td>
								<td>
									<?php
									$detailsExport .= "<table width='100%'>";
									$detailsExport .= "<tr>";
									$detailsExport .= "<td><label for='answer_types'>"._ANSWER_TYPES_DONE." : </label></td>";
									$answer_type = "";
									if($process_data['simple_mail'] == true)
									{
										$answer_type .=  _SIMPLE_MAIL.', ';
									}
									if($process_data['registered_mail'] == true)
									{
										$answer_type .=  _REGISTERED_MAIL.', ';
									}
									if($process_data['direct_contact'] == true)
									{
										$answer_type .=  _DIRECT_CONTACT.', ';
									}
									if($process_data['email'] == true)
									{
										$answer_type .=  _EMAIL.', ';
									}
									if($process_data['fax'] == true)
									{
										$answer_type .=  _FAX.', ';
									}
									if($process_data['no_answer'] == true)
									{
										$answer_type =  _NO_ANSWER.', ';
									}
									if($process_data['other'] == true)
									{
										$answer_type .=  " ".$process_data['other_answer_desc']."".', ';
									}
									$answer_type = preg_replace('/, $/', '', $answer_type);
									$detailsExport .= $answer_type."</td></tr>";
									?>
									<input name="answer_types" type="text" readonly="readonly" class="readonly" value="<?php echo $answer_type;?>" style="width:500px;" />
								</td>
							</tr>
							<?php
							$detailsExport .= "<tr>";
							$detailsExport .= "<td><label for='process_notes'>"._PROCESS_NOTES." : </label></td>";
							$detailsExport .= $db->show_string($process_data['process_notes'])."</td></tr>";
							?>
							<tr>
								<td><label for="process_notes"><?php echo _PROCESS_NOTES;?> : </label></td>
								<td><textarea name="process_notes" id="process_notes" readonly="readonly" style="width:500px;"><?php echo $db->show_string($process_data['process_notes']);?></textarea></td>
							</tr>
							<?php
							if(isset($closing_date) && !empty($closing_date))
							{
								$detailsExport .= "<tr>";
								$detailsExport .= "<td><label for='closing_date'>"._CLOSING_DATE." : </label></td>";
								$detailsExport .= $closing_date."</td></tr>";
								?>
								<tr>
									<td><label for="closing_date"><?php echo _CLOSING_DATE;?> : </label></td>
									<td><input name="closing_date" type="text" readonly="readonly" class="readonly" value="<?php echo $closing_date;?>" /></td></td>
								</tr>
								<?php
							}
							$detailsExport .= "</table>";
							?>
						</table>
					</div>
					<?php
					if($core_tools->is_module_loaded('attachments'))
					{
						$detailsExport .= "<h3>"._ATTACHED_DOC." : </h3>";
						$selectAttachments = "select res_id, creation_date, title, format from ".$_SESSION['tablename']['attach_res_attachments']." where res_id_master = ".$_SESSION['doc_id']." and coll_id ='".$_SESSION['collection_id_choice']."' and status <> 'DEL'";
						$dbAttachments = new dbquery();
						$dbAttachments->connect();
						$dbAttachments->query($selectAttachments);
						$detailsExport .= "<table width='100%'>";
						$detailsExport .= "<tr>";
						$detailsExport .= "<td>"._ID."</td>";
						$detailsExport .= "<td>"._DATE."</td>";
						$detailsExport .= "<td>"._TITLE."</td>";
						$detailsExport .= "<td>"._FORMAT."</td>";
						$detailsExport .= "</tr>";
						while($resAttachments = $dbAttachments->fetch_object())
						{
							$detailsExport .= "<tr>";
							$detailsExport .= "<td>".$resAttachments->res_id."</td>";
							$detailsExport .= "<td>".$resAttachments->creation_date."</td>";
							$detailsExport .= "<td>".$resAttachments->title."</td>";
							$detailsExport .= "<td>".$resAttachments->format."</td>";
							$detailsExport .= "</tr>";
						}
						$detailsExport .= "</table>";
						?>
						<div>
						<label><?php echo _ATTACHED_DOC;?> : </label>
						<iframe name="list_attach" id="list_attach" src="<?php echo $_SESSION['urltomodules'];?>attachments/frame_list_attachments.php?view_only" frameborder="0" width="100%" height="300px"></iframe>
						</div>
						<?php
					}
					$detailsExport .= "<br><br><br>";
					?>
				</dd>
				<dt><?php echo _DOC_HISTORY;?></dt>
				<dd>
					<?php include($_SESSION['config']['businesapppath']."indexing_searching".DIRECTORY_SEPARATOR."hist_doc.php");?>
				</dd>
				<?php
				if($core_tools->is_module_loaded('notes'))
				{
					$selectNotes = "select id, identifier, user_id, date, note_text from ".$_SESSION['tablename']['not_notes']." where identifier = ".$s_id." and coll_id ='".$_SESSION['collection_id_choice']."' order by date desc";
					$dbNotes = new dbquery();
					$dbNotes->connect();
					$dbNotes->query($selectNotes);
					$nb_notes_for_title  = $dbNotes->nb_result();
					if ($nb_notes_for_title == 0)
					{
						$extend_title_for_notes = '';
					}
					else
					{
						$extend_title_for_notes = " (".$nb_notes_for_title.") ";
					}
					?>
					<dt><?php  echo _NOTES.$extend_title_for_notes;?></dt>
					<dd>
					<?php
						$detailsExport .= "<h3>"._NOTES." : </h3>";
						$detailsExport .= "<table width='100%'>";
						$detailsExport .= "<tr>";
						$detailsExport .= "<td>"._ID."</td>";
						$detailsExport .= "<td>"._DATE."</td>";
						$detailsExport .= "<td>"._NOTES."</td>";
						$detailsExport .= "<td>"._USER."</td>";
						$detailsExport .= "</tr>";
						while($resNotes = $dbNotes->fetch_object())
						{
							$detailsExport .= "<tr>";
							$detailsExport .= "<td>".$resNotes->id."</td>";
							$detailsExport .= "<td>".$resNotes->date."</td>";
							$detailsExport .= "<td>".$resNotes->note_text."</td>";
							$detailsExport .= "<td>".$resNotes->user_id."</td>";
							$detailsExport .= "</tr>";
						}
						$detailsExport .= "</table>";
						$select_notes[$_SESSION['tablename']['users']] = array();
						array_push($select_notes[$_SESSION['tablename']['users']],"user_id","lastname","firstname");
						$select_notes[$_SESSION['tablename']['not_notes']] = array();
						array_push($select_notes[$_SESSION['tablename']['not_notes']],"id", "date", "note_text", "user_id");
						$where_notes = " identifier = ".$s_id." ";
						$request_notes = new request;
						$tab_notes=$request_notes->select($select_notes,$where_notes,"order by ".$_SESSION['tablename']['not_notes'].".date desc",$_SESSION['config']['databasetype'], "500", true,$_SESSION['tablename']['not_notes'], $_SESSION['tablename']['users'], "user_id" );
						?>
						<div style="text-align:center;">
							<img src="<?php echo $_SESSION['urltomodules'];?>notes/img/modif_note.png" border="0" alt="" /><a href="javascript://" onclick="ouvreFenetre('<?php echo $_SESSION['urltomodules'];?>notes/note_add.php?size=full&identifier=<?php echo $s_id;?>&coll_id=<?php echo $coll_id;?>', 450, 300)" ><?php echo _ADD_NOTE;?></a>
						</div>
						<iframe name="list_notes_doc" id="list_notes_doc" src="<?php echo $_SESSION['urltomodules'];?>notes/frame_notes_doc.php?size=full" frameborder="0" width="100%" height="520px"></iframe>
					</dd>
					<?php
				}
				if($core_tools->is_module_loaded('cases') == true)
				{
					?>
					<dt><?php  echo _CASE;?></dt>
					<dd>
				<?php
						include($_SESSION['pathtomodules'].'cases'.DIRECTORY_SEPARATOR.'including_detail_cases.php');
						 if ($core_tools->test_service('join_res_case', 'cases',false) == 1)
						{
						?><div align="center">
							<input type="button" class="button" name="back_welcome" id="back_welcome" value="<?php if($res->case_id<>'') echo _MODIFY_CASE; else echo _JOIN_CASE;?>" onclick="window.open('<?php echo $_SESSION['urltomodules'];?>cases/search_adv_for_cases.php?searched_item=res_id&searched_value=<? echo $s_id;?>','', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1020,height=685');"/></div><?php
						}
						?>
					</dd>
				<?php } ?>
			</dl>
	<?php
}
?>
</div>
</div>
<script type="text/javascript">
 var item  = $('details_div');
  var tabricator1 = new Tabricator('tabricator1', 'DT');
  if(item)
  	{
	 item.style.display='block';
	}
</script>
<?php
$detailsExport .= "</body></html>";
$_SESSION['doc_convert'] = array();
$_SESSION['doc_convert']['details_result'] = $detailsExport;
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
if($core_tools->is_module_loaded("doc_converter"))
{

	require_once($_SESSION['pathtomodules']."doc_converter".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
	$doc_converter = new doc_converter();
	$doc_converter->convert_details($detailsExport);
}
?>
