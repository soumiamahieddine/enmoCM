<?php
/*
*
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
* @brief   Displays the diffusion list of a document
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
include('core/init.php');

require_once("core/class/class_db.php");
require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");
require_once("core/class/class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();
$core_tools2 = new core_tools();
$conn = new dbquery();
$conn -> connect();
$func = new functions;
$list = new list_show();
?>
<h2 onclick="change(22)" id="h222" class="categorie">
	<img src="<?php echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/plus.png" alt="" />&nbsp;<b><?php echo _DIFFUSION_DISTRIBUTION;?> :</b>
	<span class="lb1-details">&nbsp;</span>
</h2>
<br>
<div class="desc" id="desc22" style="display:none">
	<div class="ref-unit">
		<p>
			<?php
			if(!empty($_SESSION['id_to_view']) && !empty($_SESSION['id_to_view']))
	        {
				$select_diff[$_SESSION['tablename']['users']] = array();
				array_push($select_diff[$_SESSION['tablename']['users']],"user_id","lastname","firstname", 'department');
				$select_diff[$_SESSION['tablename']['bask_listinstance']] = array();
				array_push($select_diff[$_SESSION['tablename']['bask_listinstance']], "user_id");
				$select_diff[$_SESSION['tablename']['bask_entity']] = array();
				array_push($select_diff[$_SESSION['tablename']['bask_entity']], "entity_label");
				$where = $_SESSION['tablename']['bask_listinstance'].".res_id = ".$_SESSION['id_to_view']."  and  ".$_SESSION['tablename']['bask_listinstance'].".coll_id='".$_SESSION['collection_id_choice']."' and ".$_SESSION['tablename']['bask_listinstance'].".sequence = 0 and ".$_SESSION['tablename']['bask_listinstance'].".user_id <> '' and ".$_SESSION['tablename']['bask_listinstance'].".type_listinstance='DOC' and ".$_SESSION['tablename']['bask_entity'].".entity_id = ".$_SESSION['tablename']['users'].".department and ".$_SESSION['tablename']['bask_listinstance'].".user_id = ".$_SESSION['tablename']['users'].".user_id" ;
				$request= new request;
				$tab=$request->select($select_diff,$where,"order by ".$_SESSION['tablename']['bask_listinstance'].".sequence asc",$_SESSION['config']['databasetype'], "500" );
				for ($ind_diff1=0;$ind_diff1<count($tab);$ind_diff1++)
				{
		            for ($ind_diff2=0;$ind_diff2<count($tab[$ind_diff1]);$ind_diff2++)
		            {
		                foreach(array_keys($tab[$ind_diff1][$ind_diff2]) as $value)
		                {
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="user_id")
							{
		                        $tab[$ind_diff1][$ind_diff2]["user_id"]=$tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]= _ID;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="18";
	                            $tab[$ind_diff1][$ind_diff2]["label_align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=false;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="lastname")
		                    {
		                        $tab[$ind_diff1][$ind_diff2]['value']=$request->show_string($tab[$ind_diff1][$ind_diff2]['value']);
		                        $tab[$ind_diff1][$ind_diff2]["lastname"]=$tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_LASTNAME;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=true;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="firstname")
		                    {
								$tab[$ind_diff1][$ind_diff2]["firstname"]= $tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_FIRSTNAME;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=true;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="department")
		                    {
								$tab[$ind_diff1][$ind_diff2]["department"]= $tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_DEPARTMENT;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=false;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="entity_label")
		                    {
		                        $tab[$ind_diff1][$ind_diff2]["entity_label"]= $tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_DEPARTMENT;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=true;
		                    }
		                }
		            }
				}
				$title = '';
				$list_diff = new list_show();
				echo "<p class='sstit'>"._RECIPIENT."</p>";
				$list_diff->list_simple($tab, count($tab), $title,'id','id', false, '','listing small');

				$select_diff[$_SESSION['tablename']['users']] = array();
				array_push($select_diff[$_SESSION['tablename']['users']],"user_id","lastname","firstname", 'department');
				$select_diff[$_SESSION['tablename']['bask_listinstance']] = array();
				array_push($select_diff[$_SESSION['tablename']['bask_listinstance']], "user_id");
				$select_diff[$_SESSION['tablename']['bask_entity']] = array();
				array_push($select_diff[$_SESSION['tablename']['bask_entity']], "entity_label");
				$where = $_SESSION['tablename']['bask_listinstance'].".res_id = ".$_SESSION['id_to_view']."  and  ".$_SESSION['tablename']['bask_listinstance'].".coll_id='".$_SESSION['collection_id_choice']."' and ".$_SESSION['tablename']['bask_listinstance'].".sequence > 0 and ".$_SESSION['tablename']['bask_listinstance'].".user_id <> '' and ".$_SESSION['tablename']['bask_listinstance'].".type_listinstance='DOC' and ".$_SESSION['tablename']['bask_entity'].".entity_id = ".$_SESSION['tablename']['users'].".department and ".$_SESSION['tablename']['bask_listinstance'].".user_id = ".$_SESSION['tablename']['users'].".user_id" ;
				$request= new request;
				$tab=$request->select($select_diff,$where,"order by ".$_SESSION['tablename']['bask_listinstance'].".sequence asc",$_SESSION['config']['databasetype'], "500" );
				for ($ind_diff1=0;$ind_diff1<count($tab);$ind_diff1++)
				{
		            for ($ind_diff2=0;$ind_diff2<count($tab[$ind_diff1]);$ind_diff2++)
		            {
		                foreach(array_keys($tab[$ind_diff1][$ind_diff2]) as $value)
		                {
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="user_id")
		                    {
		                        $tab[$ind_diff1][$ind_diff2]["user_id"]=$tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]= _ID;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="18";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=false;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="lastname")
		                    {
								$tab[$ind_diff1][$ind_diff2]['value']=$request->show_string($tab[$ind_diff1][$ind_diff2]['value']);
		                        $tab[$ind_diff1][$ind_diff2]["lastname"]=$tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_LASTNAME;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="left";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=true;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="firstname")
		                    {
								$tab[$ind_diff1][$ind_diff2]["firstname"]= $tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_FIRSTNAME;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=true;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="department")
		                    {
		                        $tab[$ind_diff1][$ind_diff2]["department"]= $tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_DEPARTMENT;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=false;
		                    }
		                    if($tab[$ind_diff1][$ind_diff2][$value]=="entity_label")
		                    {
								$tab[$ind_diff1][$ind_diff2]["entity_label"]= $tab[$ind_diff1][$ind_diff2]['value'];
		                        $tab[$ind_diff1][$ind_diff2]["label"]=_DEPARTMENT;
		                        $tab[$ind_diff1][$ind_diff2]["size"]="10";
		                        $tab[$ind_diff1][$ind_diff2]["label_align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["align"]="center";
		                        $tab[$ind_diff1][$ind_diff2]["valign"]="bottom";
		                        $tab[$ind_diff1][$ind_diff2]["show"]=true;
		                    }
		                }
		            }
				}
				$title = '';
				$list_diff = new list_show();
				echo "<br><p class='sstit'>"._TO_CC."</p>";
				$list_diff->list_simple($tab, count($tab), $title,'id','id', false, '','listing small');
			} ?>
		</p>
	</div>
</div>
<?php $select = array();?>
<hr />
