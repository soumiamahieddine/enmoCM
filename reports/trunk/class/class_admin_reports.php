<?php
/*
*   Copyright 2008,2009 Maarch
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
* @brief   Module reports :  Administration of the reports
*
* Forms and process to link reports to groups
*
* @file
* @author Yves Christian KPAKPO <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup reports
*/

class admin_reports extends dbquery
{

	/**
	* Loads the reports of a user group in session variables.
	*
	* @param  $group_id string User group identifier
	*/
	public function load_reports_group($group_id)
	{
		$_SESSION['m_admin']['reports']['groups']=array();

		$this->connect();
		$this->query("select report_id from ".$_SESSION['tablename']['usergroups_reports'] ." where group_id = '".$group_id."'");
		//$this->show();
		if($this->nb_result() != 0)
		{
			while($value = $this->fetch_object())
			{
				array_push($_SESSION['m_admin']['reports']['groups'],$value->report_id);
			}
		}
		//$_SESSION['m_admin']['load_reports'] = false;
	}


	/**
	* Loads into database the reports for a user group
	*
	* @param  $reports array Array os reports
	* @param  $group string User group identifier
	*/
	public function load_reports_db($reports, $group)
	{
		$this->connect();
		$this->query("delete from ".$_SESSION['tablename']['usergroups_reports']." where group_id = '".$group."'");
		for($i=0; $i<count($reports);$i++)
		{
			$this->query("insert into ".$_SESSION['tablename']['usergroups_reports']." values ('".$group."', '".$reports[$i]."')");
		}

		if($_SESSION['history']['usergroupsreportsadd'] == "true")
		{
			require_once("core/class/class_history.php");
			$hist = new history();
			$hist->add($_SESSION['tablename']['usergroups_reports'], $group,"ADD",_GROUP_REPORTS_ADDED." : ".$group, $_SESSION['config']['databasetype']);
		}

		unset($_SESSION['m_admin']);

		$_SESSION['error'] =  _GROUP_REPORTS_ADDED;
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=admin_reports&module=reports");
		exit();
	}

	/**
	* Returns all the reports for the superadmin in an array
	*
	*/
	private function get_all_reports()
	{
		require_once('class_modules_tools.php');
		$rep = new reports();
		$enabled_reports = $rep->get_reports_from_xml();
		$reports = array();
		foreach(array_keys($enabled_reports)as $key)
		{
			$reports[$key] = true;
		}
		return $reports;
	}

	/**
	* Loads into session all the reports for a user
	*
	* @param  $user_id  string User identifier
	*/
	public function load_user_reports($user_id, $from)
	{
		$reports = array();
		if($user_id == "superadmin")
		{
			$reports = $this->get_all_reports();
		}
		else
		{
			require_once('class_modules_tools.php');
			$rep = new reports();
			$enabled_reports = $rep->get_reports_from_xml();
			$this->connect();
			//$_SESSION['user']['reports'] = array();
			require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_usergroups.php");
			$group = new usergroups();

			foreach(array_keys($enabled_reports)as $key)
			{
					$this->query("select group_id from ".$_SESSION['tablename']['usergroups_reports']." where report_id = '".$key."'");
					$find = false;
					while($res = $this->fetch_object())
					{
						if($group->in_group($user_id, $res->group_id) == true)
						{
							$find = true;
							break;
						}
					}
					if($find == true)
					{
						$reports[$key] = true;
/*
						if ((!empty($from) && $from == "menu") && $enabled_reports[$i]['menu']=="true")
						{
							array_push($reports, array('id' => $enabled_reports[$i]['id'],
														'label' => $enabled_reports[$i]['label'],
														'desc' => $enabled_reports[$i]['desc'],
														'url' =>$enabled_reports[$i]['url'],
														'menu' =>$enabled_reports[$i]['menu'],
														'module' => $enabled_reports[$i]['module'],
														'module_label' => $enabled_reports[$i]['module_label']));
						}
						elseif((!isset($from) || empty($from) || $from <> "menu") && $enabled_reports[$i]['menu']== "false")
						{

						array_push($reports, array('id' => $enabled_reports[$i]['id'],
													'label' => $enabled_reports[$i]['label'],
													'desc' =>$enabled_reports[$i]['desc'],
													'url' =>$enabled_reports[$i]['url'],
													'menu' =>$enabled_reports[$i]['menu'],
													'module' => $enabled_reports[$i]['module'],
													'module_label' => $enabled_reports[$i]['module_label']));

						}
*/
						//print_r($reports);
					}
					else
					{
						//$_SESSION['user']['reports'][$_SESSION['enabled_reports'][$i]['id']] = false;
						//$reports[$_SESSION['enabled_reports'][$i]['id']] = false;
					}

			}
		}
		return $reports;
	}

	/**
	* Form for the management of the reports by groups
	*
	* @param 	string  $id  group identifier (empty by default)
	*/
	public function groupreports($id = "")
	{
		require_once("core/class/class_security.php");
		require_once("core/class/class_core_tools.php");
		require_once('modules/reports'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
		$rep = new reports();
		$enabled_reports = $rep->get_reports_from_xml();
		$sec = new security();
		$func = new functions();
		$core_tools = new core_tools();
		$state = true;
		$tab = array();

		//$this->show_array($enabled_reports);
		if(empty($_SESSION['error']))
		{
			$this->connect();
			$this->query("select * from ".$_SESSION['tablename']['usergroups']." where group_id = '".$id."' and enabled = 'Y'");

			if($this->nb_result() == 0)
			{
				$_SESSION['error'] = _GROUP.' '._UNKNOWN;
				$state = false;
			}
			else
			{
				$line = $this->fetch_object();
				$_SESSION['m_admin']['reports']['GroupId'] = $line->group_id;
				$_SESSION['m_admin']['reports']['desc'] = $this->show_string($line->group_desc);
			}

			$this->load_reports_group($id);

			//$this->show_array($_SESSION['m_admin']['reports']['groups']);
		}

		if($state == false)
		{
			echo "<br /><br /><br /><br />"._GROUP.' '._UNKNOWN."<br /><br /><br /><br />";
		}
		else
		{
		?>
			<div id="group_box" class="bloc">
				<a href="javascript://" onclick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>/admin/groups/liste_users.php?id=<?php  echo $id;?>&admin=groups', '', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=820,height=400')"><img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/membres_groupe_b.gif" alt="" /><i><?php  echo _SEE_GROUP_MEMBERS;?></i></a><br/><br/>
			</div>
			<form name="formgroupreport" method="post"  class="forms" action="<?php echo $_SESSION['urltomodules'];?>reports/groupreports_up_db.php">

				<br><center><i><?php  echo _AVAILABLE_REPORTS.'</i> '.$_SESSION['m_admin']['reports']['desc'];?> :</center>
				<?php
				$enabled_reports_sort_by_parent = array();
				$j=0;
				$last_val = '';
				foreach(array_keys($enabled_reports)as $key)
				{
					if($enabled_reports[$key]['module'] <> $last_val)
					{
						$j=0;
					}
					$enabled_reports_sort_by_parent[$enabled_reports[$key]['module']][$j] = $enabled_reports[$key];
					$j++;
					$last_val = $enabled_reports[$key]['module'];
				}
			//	$this->show_array($enabled_reports_sort_by_parent);
				$_SESSION['cpt']=0;
				foreach(array_keys($enabled_reports_sort_by_parent) as $value)
				{
					?>
					<h5 onclick="change(<?php  echo $_SESSION['cpt'];?>);" id="h2<?php  echo $_SESSION['cpt'];?>" class="categorie">
						<img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/plus.png" alt="" />&nbsp;<b><?php  echo $enabled_reports_sort_by_parent[$value][0]['module_label'];?></b>
						<span class="lb1-details">&nbsp;</span>
					</h5>
					<br/>
					<div class="desc block_light" id="desc<?php  echo $_SESSION['cpt'];?>" style="display:none">
						<div class="ref-unit">
							<table>
								<?php
								for($i=0; $i<count($enabled_reports_sort_by_parent[$value]); $i++)
								{
									?>
									<tr>
										<td width="800px" align="right" title="<?php  echo $enabled_reports_sort_by_parent[$value][$i]['desc'];?>">
											<?php  echo $enabled_reports_sort_by_parent[$value][$i]['label'];?> :
										</td>
										<td width="50px" align="left">
											<input type="checkbox" name="reports[]" value="<?php  echo $enabled_reports_sort_by_parent[$value][$i]['id'];?>" <?php  if(in_array($enabled_reports_sort_by_parent[$value][$i]['id'],$_SESSION['m_admin']['reports']['groups'])){ echo 'checked="checked"';}?>  />
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php
					$_SESSION['cpt']++;
				}
				?>
				<br/>
				<p class="buttons">
					<input id="groupbutton" type="submit"  name="Submit" value="<?php  echo _VALIDATE; ?>" class="button" />
					<input type="button" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=admin&reinit=true';"/>
				</p>
				<p>&nbsp;</p>
				<p>&nbsp;</p>
			</form>

		<?php
		}
	}
}
?>
