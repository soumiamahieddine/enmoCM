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
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$db = new dbquery();
$db->connect();
$nb_trees = count($_SESSION['doctypes_chosen_tree']);
$core_tools->load_html();
$core_tools->load_header();
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
		$where = "";
		$db->query("select d.doctypes_first_level_id, d.doctypes_first_level_label from ".$_SESSION['tablename']['fold_foldertypes_doctypes_level1']." g, ".$_SESSION['tablename']['doctypes_first_level']." d where g.foldertype_id = '".$_SESSION['doctypes_chosen_tree']."' and g.doctypes_first_level_id = d.doctypes_first_level_id order by d.doctypes_first_level_label");
		$level1 = array();
		while($res = $db->fetch_object())
		{
			array_push($level1, array('id' => $res->doctypes_first_level_id, 'tree' => $_SESSION['doctypes_chosen_tree'], 'key_value' => $res->doctypes_first_level_id, 'label_value' => $db->show_string($res->doctypes_first_level_label), 'script' => ""));
		}
		for($i=0;$i<count($_SESSION['tree_foldertypes']);$i++)
		{
			if($_SESSION['tree_foldertypes'][$i]['ID'] == $_SESSION['doctypes_chosen_tree'])
			{
				$label = "<b>".$_SESSION['tree_foldertypes'][$i]['LABEL']."</b>";
			}
		}
		?>
		<script type="text/javascript">
			function funcOpen (branch, response)
			{
				return true;
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
				branch.struct.branch_level_id = GetBranchLevelId(branch);
				return true;
			}

			function GetBranchLevelId(branch)
			{
				var branchlevel = tree.getBranchById(branch);
				return branchlevel.getLevel();
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
					else if(branch2.isChild(branch_id))
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

			var tree = null;
			var current_branch_id = null;
			function TafelTreeInit () {
				var struct = [
				{
				'id':'<?php  echo $_SESSION['doctypes_chosen_tree'];?>',
				'txt':'<?php  echo $label;?>',
				'items':[
							<?php  
							for($cpt_level1=0; $cpt_level1 < count($level1);$cpt_level1++)
							{
								?>
								{
									'id':'<?php  echo $level1[$cpt_level1]['id'];?>',
									'txt':'<?php  echo addslashes($level1[$cpt_level1]['label_value']);?>',
									'canhavechildren' : true,
									'onbeforeopen' : MyBeforeOpen,
									<?php  if(isset($level1[$cpt_level1]['script']) && !empty($level1[$cpt_level1]['script']))
									{?>
									'script' : '<?php  echo $level1[$cpt_level1]['script'];?>',
									<?php  } ?>
									'key_value' : '<?php  echo addslashes($level1[$cpt_level1]['key_value']);?>',
									'branch_level_id' : '1'
								}
								<?php 
								if(isset($level1[$cpt_level1+1]['id']) && !empty($level1[$cpt_level1+1]['id']))
								{
									echo ",";
								}
							}
							?>
						]

				}
				];
				tree = new TafelTree('trees_div', struct, {
					'generate' : true,
					'imgBase' : '<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>imgs/',
					//'defaultImg' : 'page.gif',
					'defaultImg' : 'folder.gif',
					'defaultImgOpen' : 'folderopen.gif',
					'defaultImgClose' : 'folder.gif',
					'onOpenPopulate' : [funcOpen, 'get_tree_children.php?IdTree=<?php  echo $_SESSION['doctypes_chosen_tree'];?>']
				});
			}
		</script>
		<div id="trees_div"></div>
		<?php
	}
	else
	{
		//echo "<div align='left'>&nbsp;&nbsp;&nbsp;"._CHOOSE_FOLDERTYPE."</div>";
	}
}
?>
</body>
</html>
