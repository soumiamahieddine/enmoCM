<?php
/**
* File : filling_res.php
*
* Frame to show the indexed files in a folder (show_folder.php)
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Loïc Vinet  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$security = new security();
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
$func = new functions();
$table_name = $security->retrieve_table_from_coll($_SESSION['current_foldertype_coll_id']);
$table_view = $security->retrieve_view_from_coll_id($_SESSION['current_foldertype_coll_id']);
$foldertype_id = $_SESSION['folder_search']['foldertype_id'];
$missing_res = array();

$core_tools->load_html();
//here we building the header
$core_tools->load_header( );
?>
<body id="filling_res_frame">
<?php
$_SESSION['array_struct_final'] = array();
$db = new dbquery();
$db->connect();
$array_struct = array();
$array_level_1 = array();
$array_level_2 = array();
$array_level_3 = array();
$array_level_4 = array();
$list=new list_show();
for($i=0;$i<count($_SESSION['user']['security']);$i++)
{
	if($_SESSION['user']['security'][$i]['coll_id'] == $_SESSION['current_foldertype_coll_id'])
	{
		$coll_id_test = $_SESSION['user']['security'][$i]['coll_id'];
		$where_clause = $_SESSION['user']['security'][$i]['where'];
		break;
	}
}
if(empty($coll_id_test))
{
	echo _NO_COLLECTION_ACCESS_FOR_THIS_USER;
}
else
{
	if($where_clause <> "")
	{
		$where_clause = " and (".$where_clause.")";
	}
	$db->query("select distinct doctypes_first_level_id, doctypes_first_level_label, doctypes_second_level_id, doctypes_second_level_label, type_id, type_label, res_id from  ".$table_view." where folders_system_id = '".$_SESSION['current_folder_id']."' and type_id <> 0 and doctypes_first_level_id <> 0 and doctypes_second_level_id <> 0 and status<>'DEL' ".$where_clause." order by doctypes_first_level_label, doctypes_second_level_label, type_label, res_id ");
	//$db->show();
	$count_doc = 0;
	while($res = $db->fetch_object())
	{
		array_push($array_struct, array("level_1_id" => $res->doctypes_first_level_id, "level_1_label" => $func->show_string($res->doctypes_first_level_label), "level_2_id" => $res->doctypes_second_level_id, "level_2_label" => $func->show_string($res->doctypes_second_level_label), "level_3_id" => $res->type_id, "level_3_label" => $func->show_string($res->type_label), "level_4_id" => $res->res_id, "level_4_label" => $res->res_id));
		$count_doc++;
	}
	if($count_doc >0)
	{
		//$func->show_array($array_struct);
		$_SESSION['array_struct_final']['level_1'][$array_struct[0]['level_1_id']]['label'] = $array_struct[0]['level_1_label'];
		$_SESSION['array_struct_final']['level_1'][$array_struct[0]['level_1_id']]['level_2'][$array_struct[0]['level_2_id']]['label'] = $array_struct[0]['level_2_label'];
		$_SESSION['array_struct_final']['level_1'][$array_struct[0]['level_1_id']]['level_2'][$array_struct[0]['level_2_id']]['level_3'][$array_struct[0]['level_3_id']]['label'] = $array_struct[0]['level_3_label'];
		$_SESSION['array_struct_final']['level_1'][$array_struct[0]['level_1_id']]['level_2'][$array_struct[0]['level_2_id']]['level_3'][$array_struct[0]['level_3_id']]['level_4'][$array_struct[0]['level_4_id']]['label'] = $array_struct[0]['level_4_label'];

		$level1 = array();
		$_SESSION['where_list_doc'] = "";
		?>
		<script language="javascript">
			function view_doc(id)
			{
				var eleframe1 = window.top.document.getElementById('view_doc');
				eleframe1.src = '<?php  echo $_SESSION['urltomodules']?>folder/list_doc.php?listid='+id;
			}
		</script>
		<div align="left">
			<?php
			function is_new_element($array, $level, $element_id, $element_label, $level_2, $element_id_2, $element_label_2, $level_3, $element_id_3, $element_label_3, $level_4, $element_id_4, $element_label_4)
			{
				//echo "is_array : ".is_array($_SESSION['array_struct_final'])."<br>";
				foreach(array_keys($_SESSION['array_struct_final'][$level]) as $value)
				{
					if($value == $element_id)
					{
						//echo "exist : ".$element_label."<br>";
					}
					else
					{
						//echo "new element : ".$element_label."<br>";
						$_SESSION['array_struct_final'][$level][$element_id]['label'] = $element_label;
						//echo "----".$element_label."<br>";
						break;
					}
				}
				foreach(array_keys($_SESSION['array_struct_final'][$level][$element_id]) as $value)
				{
					if($value == $element_id_2)
					{
						//echo "exist : ".$element_label."<br>";
					}
					else
					{
						//echo "new element : ".$element_label."<br>";
						$_SESSION['array_struct_final'][$level][$element_id][$level_2][$element_id_2]['label'] = $element_label_2;
						//echo "--------".$element_label2."<br>";
						break;
					}
				}
				foreach(array_keys($_SESSION['array_struct_final'][$level][$element_id][$level_2][$element_id_2]) as $value)
				{
					if($value == $element_id_3)
					{
						//echo "exist : ".$element_label."<br>";
					}
					else
					{
						//echo "new element : ".$element_label."<br>";
						$_SESSION['array_struct_final'][$level][$element_id][$level_2][$element_id_2][$level_3][$element_id_3]['label'] = $element_label_3;
						//echo "----------------".$element_label3."<br>";
						break;
					}
				}
				foreach(array_keys($_SESSION['array_struct_final'][$level][$element_id][$level_2][$element_id_2][$level_3][$element_id_3]) as $value)
				{
					if($value == $element_id_4)
					{
						//echo "exist : ".$element_label."<br>";
					}
					else
					{
						//echo "new element : ".$element_label."<br>";
						$_SESSION['array_struct_final'][$level][$element_id][$level_2][$element_id_2][$level_3][$element_id_3][$level_4][$element_id_4]['label'] = $element_label_4;
						//echo "--------------------------------".$element_label4."<br>";
						break;
					}
				}
			}
			for($i=1;$i<count($array_struct);$i++)
			{
				is_new_element($array_struct, "level_1", $array_struct[$i]['level_1_id'], $array_struct[$i]['level_1_label'], "level_2", $array_struct[$i]['level_2_id'], $array_struct[$i]['level_2_label'], "level_3", $array_struct[$i]['level_3_id'], $array_struct[$i]['level_3_label'], "level_4", $array_struct[$i]['level_4_id'], $array_struct[$i]['level_4_label']);
			}
			//$func->show_array($_SESSION['array_struct_final']);
			foreach(array_keys($_SESSION['array_struct_final']['level_1']) as $value_1)
			{
				$res_id_list = "";
				foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2']) as $value_2)
				{
					foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3']) as $value_3)
					{
						foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4']) as $value_4)
						{
							$res_id_list .= $_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4'][$value_4]['label'].",";

						}
					}
				}
				?>
				<div onClick="change2(<?php  echo $value_1;?>)" id="h2<?php  echo $value_1;?>" class="categorie">
					<?php  echo "<a href=javascript:view_doc('".$res_id_list."');>"?><img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/folderopen.gif" alt="" />&nbsp;<b><?php  echo $_SESSION['array_struct_final']['level_1'][$value_1]['label'];?></b></a>
					<span class="lb1-details">&nbsp;</span>
				</div>
				<br>
				<div class="desc" id="desc<?php  echo $value_1;?>" >
					<div class="ref-unit">
						<?php
						$res_id_list = "";
						foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2']) as $value_2)
						{
							$res_id_list = "";
							foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3']) as $value_3)
							{
								foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4']) as $value_4)
								{
									$res_id_list .= $_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4'][$value_4]['label'].",";

								}
							}
							//echo $res_id_list;
							?>
							<div onClick="change2(<?php  echo $value_2;?>)" id="h2<?php  echo $value_2;?>" class="categorie">
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo "<a href=javascript:view_doc('".$res_id_list."');>"?><img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/folderopen.gif" alt="" />&nbsp;<b><?php  echo $_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['label'];?></b></a>
								<span class="lb1-details">&nbsp;</span>
							</div>
							<br>
							<div class="desc" id="desc<?php  echo $value_2;?>" >
								<div class="ref-unit">
									<?php
									$res_id_list = "";
									foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3']) as $value_3)
									{
										$res_id_list = "";
										foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4']) as $value_4)
										{
											$res_id_list = $_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4'][$value_4]['label'].",";
											echo 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<a href=javascript:view_doc('".$res_id_list."');><img src='".$_SESSION['config']['businessappurl'].$_SESSION['config']['img']."/page.gif'alt='' />".$_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4'][$value_4]['label']." - ".$_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['label']."</a><br>";
										}

										?>

												<?php
												foreach(array_keys($_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4']) as $value_4)
												{
													echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
													<a href=javascript:view_doc('".$res_id_list."');><img src='".$_SESSION['config']['businessappurl'].$_SESSION['config']['img']."/page.gif'alt='' />".$_SESSION['array_struct_final']['level_1'][$value_1]['level_2'][$value_2]['level_3'][$value_3]['level_4'][$value_4]['label']."</a><br>";
												}
												?>
											</div>
										</div>-->
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	<?php
	}
	else
	{
		echo _FOLDERHASNODOC.".<br>";
	}
}
?>
</body>
</html>