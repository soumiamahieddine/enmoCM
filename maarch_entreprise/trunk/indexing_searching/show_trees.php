<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
//require_once("class/class_folder.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR.'class_business_app_tools.php');
$appTools = new business_app_tools();
$core_tools = new core_tools();
$core_tools->load_lang();
$sec = new security();
$func = new functions();
$db = new dbquery();
$db->connect();
$dbTmp = new dbquery();
$dbTmp->connect();
$db1 = new dbquery();
$db1->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$db4 = new dbquery();
$db4->connect();
$nb_trees = count($_SESSION['user']['trees']);
$core_tools->load_html();
$core_tools->load_header();
//Définition de la collection en dur
//print_r($_REQUEST);exit;
$res_view = $_SESSION['user']['security'][0]['view'];
$coll_id = $_SESSION['user']['security'][0]['coll_id'];
$where_clause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
if(trim($where_clause) == "")
{
	$where_clause = "1=1";
}
?>
<body>
<?php
$label = _SEARCH_ADV_RESULT;
if(!empty($_SESSION['nc']))
{
	$_REQUEST['num_folder'] = $_SESSION['nc']['num_folder'];
	$_REQUEST['name_folder'] = $_SESSION['nc']['name_folder'];
}
unset($_SESSION['nc']);
unset($_SESSION['chosen_num_folder']);
unset($_SESSION['chosen_name_folder']);
if(!empty($_REQUEST['project']) && empty($_REQUEST['market']))
{
	if(substr($_REQUEST['project'], strlen($_REQUEST['project']) -1, strlen($_REQUEST['project'])) == ")")
	{
		$folderSystemId = str_replace(')', '', substr($_REQUEST['project'], strrpos($_REQUEST['project'],'(')+1));
	}
}
if(!empty($_REQUEST['market']))
{
	if(substr($_REQUEST['market'], strlen($_REQUEST['market']) -1, strlen($_REQUEST['market'])) == ")")
	{
		$folderSystemId = str_replace(')', '', substr($_REQUEST['market'], strrpos($_REQUEST['market'],'(')+1));
	}
}
//echo $folderSystemId;exit;
if($folderSystemId <> '')
{
	$_SESSION['chosen_name_folder'] = $folderSystemId;
	$dbTmp->query("select distinct folder_id, folder_name, subject, folder_level, folders_system_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$folderSystemId);
	//$dbTmp->show();
	while($resTmp = $dbTmp->fetch_object())
	{
		if($resTmp->folder_level == '1')
		{
			$db->query("select distinct folder_id, folder_name, subject, folder_level, folders_system_id from ".$_SESSION['tablename']['fold_folders']." where parent_id = ".$resTmp->folders_system_id." or folders_system_id = ".$folderSystemId." ");
			$flagProject = true;
			//$db->show();
		}
		else
		{
			$db->query("select distinct folder_id, folder_name, subject, folder_level, folders_system_id, parent_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$folderSystemId);
			//$db->show();
		}
	}
}
//$actual_custom_result = $db->fetch_object();
//$actual_custom_t1 = $actual_custom_result->folder_id;
if(isset($_SESSION['chosen_name_folder']) && !empty($_SESSION['chosen_name_folder']))
{
	?>
	<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/prototype.js"></script>
	<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/scriptaculous.js"></script>
	<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>Tree.js"></script>
	<?php
	//exit;
	$search_customer_results = array();
	while($res = $db->fetch_object())
	{
		$actual_custom_t1 = $res->folder_id;
		//echo $actual_custom_t1."<br>";
		if($flagProject)
		{
			$dbTmp->query("select folder_name, subject from ".$_SESSION['tablename']['fold_folders']." where folder_name = '".$_REQUEST['name_folder']."'");
			$resTmp = $dbTmp->fetch_object();
			$idProject = $resTmp->folder_name;
			$labelProject = $resTmp->subject;
		}
		else
		{
			$dbTmp->query("select folder_name, subject from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$res->parent_id."");
			$resTmp = $dbTmp->fetch_object();
			$idProject = $resTmp->folder_name;
			$labelProject = $resTmp->subject;
		}
		$db4->query("select count(res_id) as cptresult from ".$_SESSION['collections'][0]['view']." where folder_id = '".$actual_custom_t1."' and (".$where_clause.")");
		$rescpt4 = $db4->fetch_object();
		if($rescpt4->cptresult > 150)
		{
			$error = "<br><br><p align='center' style='color:#FFC200;'>Passez SVP par la recherche avanc&eacute;e ou la recherche de dossiers, le nombre de documents demand&eacute; est trop important (".$rescpt4->cptresult.")</p>";
			break;
		}
		$f_level = array();
		$db1->query("select distinct doctypes_first_level_id, doctypes_first_level_label from ".$_SESSION['collections'][0]['view']." where folder_id = '".$actual_custom_t1."' and (".$where_clause.")");
		while($res1 = $db1->fetch_object())
		{
			$s_level = array();
			$db2->query("select distinct doctypes_second_level_id, doctypes_second_level_label from ".$_SESSION['collections'][0]['view']." where (doctypes_first_level_id = ".$res1->doctypes_first_level_id." and folder_id = '".$actual_custom_t1."') and (".$where_clause.")");
			//$db2->show();
			//echo $res1->doctypes_first_level_label."<br>";
			while($res2 = $db2->fetch_object())
			{
				$doctypes = array();
				//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$res2->doctypes_second_level_label."<br>";
				$db3->query("select distinct type_id, type_label as description from ".$_SESSION['collections'][0]['view']." where (doctypes_first_level_id = ".$res1->doctypes_first_level_id." and doctypes_second_level_id = ".$res2->doctypes_second_level_id." and folder_id = '".$actual_custom_t1."') and (".$where_clause.")");
				//$db3->show();
				while($res3 = $db3->fetch_object())
				{
					//Dépot des documents
					$results = array();
					//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$res3->description."<br>";
					$cptDoc=0;
					$db4->query("select res_id, doc_date, folder_name, identifier, subject from ".$_SESSION['collections'][0]['view']." where (type_id = ".$res3->type_id." and folder_id = '".$actual_custom_t1."') and (".$where_clause.") order by doc_date desc");
					//$db4->show();
					while($res4 = $db4->fetch_object())
					{
						/*$foundDoc = false;
						if($_REQUEST['name_folder'] <> "" && $res4->folder_name == $_REQUEST['name_folder'])
						{
							//$foundDoc = true;
						}
						if($foundDoc)
						{
							$directResId = $res4->res_id;
						}*/
						array_push($results, array('res_id' => $res4->res_id, 'doc_date' => $res4->doc_date, 'name_folder' => $res4->folder_name, 'num_ref' => $res4->identifier, 'found_doc' => $foundDoc, 'subject' => $func->show_string($res4->subject)));
						$cptDoc++;
					}
					if($cptDoc == 0)
					{
						//array_push($doctypes, array('type_id' => $res3->type_id, 'description' => $func->show_string($res3->description), "results" => $results, "no_doc" => true ));
					}
					else
					{
						array_push($doctypes, array('type_id' => $res3->type_id, 'description' => $func->show_string($res3->description), "results" => $results, "no_doc" => false ));
					}
				}
				array_push($s_level, array('doctypes_second_level_id' => $res2->doctypes_second_level_id, 'doctypes_second_level_label' => $func->show_string($res2->doctypes_second_level_label), 'doctypes' => $doctypes));
			}
			//$func->show_array($s_level);
			array_push($f_level, array('doctypes_first_level_id' => $res1->doctypes_first_level_id, 'doctypes_first_level_label' => $func->show_string($res1->doctypes_first_level_label), 'second_level' => $s_level));
		}
		array_push($search_customer_results, array('folder_id' => $res->folder_id,'folder_name' => $res->folder_name, 'folder_subject' => $res->subject, 'content' => $f_level));
	}
	//$core_tools->show_array($search_customer_results);
	if($idProject <> "")
	{
		echo "<b>&nbsp;<i>".$labelProject." (".$idProject.")</i></b>";
	}
	?>
	<script type="text/javascript">
		function funcOpen(branch, response) {
			// Ici tu peux traiter le retour et retourner true si
			// tu veux insérer les enfants, false si tu veux pas
			//MyClick(branch);
			return true;
		}

		/*
		function MyClick (branch)
		 {
			if(branch.struct.script != '' && branch.struct.script != 'default')
			{
				var parents = [];
				parents = branch.getParents();
				 var str = '';
				for(var i=0; i < (parents.length -1) ;i++)
				{
					str = str + '&parent_id[]=' + parents[i].getId();
				}
				window.top.frames['show_res_trees'].location.href='<?php  // echo $_SESSION['urltomodules']."autofoldering/";?>show_res_trees.php?script='+branch.struct.script+'&id='+branch.getId()+"&tree_id="+branch.getAncestor().getId()+str;
			}
		}
		*/

		function myClick(branch) {
			//window.top.frames['view'].location.href='<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id="; ?>'+branch.getId());;
			//window.top.frames['view'].location.href='<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id="; ?>'+branch.getId());
			window.top.frames['view'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/little_details_invoices.php?id='+branch.getId();
			//alert(branch.getId());
			//branch.setText('<b>'+branch.getText()+'</b>');
		}

		function MyOpen(branch)
		{
			if(branch.struct.script != '' && branch.struct.script != 'default')
			{
				var parents = [];
				parents = branch.getParents();
				var str = '';
				for(var i=0; i < (parents.length -1) ;i++)
				{
					str = str + '&parent_id[]=' + parents[i].getId();
				}
				var str_children  = '';
				var children = branch.getChildren();
				for(var i=0; i < (children.length -1) ;i++)
				{
					str_children = str_children + '&children_id[]=' + children[i].getId();
				}
				window.top.frames['show_res_trees'].location.href='<?php  echo $_SESSION['config']['businessappurl']."indexing_searching/";?>show_res_trees.php?script='+branch.struct.script+'&id='+branch.getId()+"&tree_id="+branch.getAncestor().getId()+str+str_children;
			}
			return true;
		}

		function MyClose(branch)
		{
			var parents = branch.getParents();
			var branch_id = branch.getId();
			//alert(branch_id);
			if(current_branch_id != null)
			{
				var branch2 = tree.getBranchById(current_branch_id);
				if(current_branch_id == branch_id )
				{
					window.top.frames['show_res_trees'].location.href='<?php  echo $_SESSION['config']['businessappurl']."indexing_searching/";?>show_res_trees.php';
					current_branch_id = branch.getNextOpenedBranch;
					//current_branch_id = null;
				}
				else if(branch2 && branch2.isChild(branch_id))
				{
					window.top.frames['show_res_trees'].location.href='<?php  echo $_SESSION['config']['businessappurl']."indexing_searching/";?>show_res_trees.php';
					current_branch_id = branch.getNextOpenedBranch;
					//current_branch_id = null;
				}
			}
			branch.collapse();
			branch.openIt(false);
		}

		function MyBeforeOpen(branch, opened)
		{
			if(opened == true)
			{
				MyClose(branch);
			}
			else
			{
				current_branch_id = branch.getId();
				MyOpen(branch);
				return true;
			}
		}

		function myMouseOver (branch)
		{
			document.body.style.cursor='pointer';
		}

		function myMouseOut (branch)
		{
			document.body.style.cursor='auto';
		}

		var tree = null;
		var current_branch_id = null;

		function TafelTreeInit ()
		{
			var struct = [
							<?php
							for($i=0;$i<count($search_customer_results);$i++)
							{
								?>
								{
									'id':'<?php  echo $search_customer_results[$i]['folder_id'];?>',
									'txt':'<b><?php  echo addslashes($search_customer_results[$i]['folder_subject'])."</b><br><small>(".$search_customer_results[$i]['folder_name'].")</small>";?>',
									'items':[
												<?php
												for($j=0;$j<count($search_customer_results[$i]['content']);$j++)
												{
													?>
													{
														'id':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['doctypes_first_level_id']);?>',
														'txt':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['doctypes_first_level_label']);?>',
														'items':[
																	<?php
																	for($k=0;$k<count($search_customer_results[$i]['content'][$j]['second_level']);$k++)
																	{
																		?>
																		{
																			'id':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_id']);?>',
																			'txt':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_label']);?>',
																			'items':[
																						<?php
																						for($l=0;$l<count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes']);$l++)
																						{
																							?>
																							{
																								<?php
																								if($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['no_doc'])
																								{
																									/*
																									?>
																									'txt':'<span style="font-style:italic;"><small><small>(<?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['description']);?>)</small></small></span>',
																									'img':'empty.gif',
																									<?php
																									*/
																								}
																								else
																								{
																									?>
																									'txt':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['description']);?>',
																									<?php
																								}
																								?>

																								'items':[
																											<?php
																											for($m=0;$m<count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results']);$m++)
																											{
																												?>
																												{
																													'id':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id']);?>',
																													<?php
																													if($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['found_doc'])
																													{
																														$beginStr = "<b>";
																														$endStr = "</b>";
																													}
																													else
																													{
																														$beginStr = "";
																														$endStr = "";
																													}
																													if(trim($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['name_folder']) <> "" && $search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['type_id'] == "3")
																													{
																														echo "'txt':'".addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['doc_date'])." ".$beginStr.addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['name_folder']).$endStr."',";
																													}
																													else
																													{
																														if($beginStr.addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['subject'] == ""))
																														{
																															echo "'txt':'".$beginStr.addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id']).$endStr."',";
																														}
																														else
																														{
																															//echo "'txt':'".$beginStr.addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['doc_date']).$endStr."',";
																															echo "'txt':'".$beginStr.addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['subject']).$endStr." <small>(".$search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id'].")</small>',";
																														}
																													}
																													?>
																													'imgBase' : '<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>imgs/',
																													//'onbeforeopen' : MyBeforeOpen,
																													//'script' : 'window.opener.frames["view"].location.href="<? echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id']);?>" '
																													//'onclick' : 'javascript:window.top.frames["view"].location.href="<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id=".addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id']);?>" '
																													"onclick" : myClick,
																													"onmouseover" : myMouseOver,
																													"onmouseout" : myMouseOut
																												}
																												<?php
																												if ($m <> count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results']) - 1)
																													echo ',';
																											}
																											?>
																										]
																							}
																							<?php
																							if ($l <> count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes']) - 1)
																							echo ',';
																						} ?>
																					]
																		}
																		<?php
																		if($k <> count($search_customer_results[$i]['content'][$j]['second_level']) - 1)
																		echo ',';
																	}
																	?>
																]
													}
													<?php
													if ($j <> count($search_customer_results[$i]['content']) - 1)
														echo ',';
												}
												?>
											]
								}
								<?php
								if ($i <> count($search_customer_results) - 1)
									echo ',';
							}
							?>
						];
			tree = new TafelTree('trees_div', struct, {
				'generate' : true,
				'imgBase' : '<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>imgs/',
				'defaultImg' : 'page.gif',
				//'defaultImg' : 'folder.gif',
				'defaultImgOpen' : 'folderopen.gif',
				'defaultImgClose' : 'folder.gif',
				'onOpenPopulate' : [funcOpen, 'get_tree_children.php?IdTree=<?php  echo $_SESSION['chosen_tree'];?>']
			});

			//open all branches
			tree.expend();
		};
	</script>
	<?php
	if($directResId <> "")
	{
		?>
		<script language="javascript">
			window.top.frames['view'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/little_details_invoices.php?id=<?php echo $directResId;?>';
		</script>
		<?php
	}
	else
	{
		?>
		<script language="javascript">
			window.top.frames['view'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/little_details_invoices.php';
		</script>
		<?php
	}
	?>
	<div id="trees_div"></div>
	<?php
	if($actual_custom_t1 == "")
	{
		echo '<br><br><p align="center" style="color:#FFC200;"><b>'._NO_RESULTS.'</b></p>';
		echo '<br><br><p align="center"><b>'._TO_SEARCH_DEFINE_A_SEARCH_ADV.'</b></p>';
	}
	if($error <> "")
	{
		echo $error;
	}
}
else
{
	echo '<br><br><p align="center"><b>'._TO_SEARCH_DEFINE_A_SEARCH_ADV.'</b></p>';
}
?>
</body>
</html>
