<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Show the tree
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
//include('core/init.php');

//require_once("core/class/class_functions.php");
//require_once("core/class/class_db.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
//require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$db = new dbquery();
$db->connect();
$db1 = new dbquery();
$db1->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$db4 = new dbquery();
$db4->connect();
$nb_trees = count($_SESSION['doctypes_chosen_tree']);
$core_tools->load_html();
$core_tools->load_header();
$f_level = array();
?>
<body>
<?php
if($nb_trees < 1)
{
	echo _NO_DEFINED_TREES;
}
else
{
	if(isset($_SESSION['doctypes_chosen_tree']) && !empty($_SESSION['doctypes_chosen_tree']))
	{
		?>
		<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/prototype.js"></script>
		<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/scriptaculous.js"></script>
		<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>Tree.js"></script>
		<?php
		$search_customer_results = array();
		$f_level = array();
		$db1->query("select d.doctypes_first_level_id, d.doctypes_first_level_label from ".$_SESSION['tablename']['fold_foldertypes_doctypes_level1']." g, ".$_SESSION['tablename']['doctypes_first_level']." d where g.foldertype_id = '".$_SESSION['doctypes_chosen_tree']."' and g.doctypes_first_level_id = d.doctypes_first_level_id and d.enabled = 'Y' order by d.doctypes_first_level_label");
		while($res1 = $db1->fetch_object())
		{
			$s_level = array();
			$db2->query("select doctypes_second_level_id, doctypes_second_level_label from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_first_level_id = ".$res1->doctypes_first_level_id." and enabled = 'Y'");
			while($res2 = $db2->fetch_object())
			{
				$doctypes = array();
				$db3->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where doctypes_first_level_id = ".$res1->doctypes_first_level_id." and doctypes_second_level_id = ".$res2->doctypes_second_level_id." and enabled = 'Y' ");
				while($res3 = $db3->fetch_object())
				{
					$results = array();
					array_push($doctypes, array('type_id' => $res3->type_id, 'description' => $func->show_string($res3->description), "results" => $results));
				}
				array_push($s_level, array('doctypes_second_level_id' => $res2->doctypes_second_level_id, 'doctypes_second_level_label' => $func->show_string($res2->doctypes_second_level_label, true), 'doctypes' => $doctypes));
			}
			array_push($f_level, array('doctypes_first_level_id' => $res1->doctypes_first_level_id, 'doctypes_first_level_label' => $func->show_string($res1->doctypes_first_level_label, true), 'second_level' => $s_level));
		}
		for($i=0;$i<count($_SESSION['tree_foldertypes']);$i++)
		{
			if($_SESSION['tree_foldertypes'][$i]['ID'] == $_SESSION['doctypes_chosen_tree'])
			$fLabel = $_SESSION['tree_foldertypes'][$i]['LABEL'];
		}
		array_push($search_customer_results, array('folder_id' => $fLabel, 'content' => $f_level));
		?>
		<script type="text/javascript">
			function funcOpen(branch, response) {
				// Ici tu peux traiter le retour et retourner true si
				// tu veux insérer les enfants, false si tu veux pas
				//MyClick(branch);
				return true;
			}

			function myClick(branch) {
				//window.top.frames['view'].location.href='<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id="; ?>'+branch.getId());;
				//window.top.frames['view'].location.href='<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id="; ?>'+branch.getId());
				//window.top.frames['view'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/little_details_invoices.php?id='+branch.getId();
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
				}
				return true;
			}

			function MyClose(branch)
			{
				var parents = branch.getParents();
				var branch_id = branch.getId();
				if(current_branch_id != null)
				{
					var branch2 = tree.getBranchById(current_branch_id);
					if(current_branch_id == branch_id )
					{
						current_branch_id = branch.getNextOpenedBranch;
					}
					else if(branch2 && branch2.isChild(branch_id))
					{
						current_branch_id = branch.getNextOpenedBranch;
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
										'txt':'<b><?php  echo $search_customer_results[$i]['folder_id'];?></b>',
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
																									?>
																									'txt':'<span style="font-style:italic;"><small><small><?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['description']);?></small></small></span>',
																									'img':'empty.gif'
																								}
																								<?php
																								if($l <> count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes']) - 1)
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
														if($j <> count($search_customer_results[$i]['content']) - 1)
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
					'defaultImg' : 'folder.gif',
					//'defaultImg' : 'page.gif',
					'defaultImgOpen' : 'folderopen.gif',
					'defaultImgClose' : 'folder.gif',
					'onOpenPopulate' : [funcOpen, 'get_tree_children.php?IdTree=<?php  echo $_SESSION['chosen_tree'];?>']
				});

				//open all branches
				tree.expend();
			};
		</script>
		<div id="trees_div"></div>
		<?php
	}
}
?>
</body>
</html>
