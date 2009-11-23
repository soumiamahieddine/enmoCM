<?php
/**
* List Class
*
*  Contains all the function to make list whith results
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Loïc Vinet  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*
*/


/**
*  List Class : Contains all the function to make list whith results
*
* @author  Claire Figueras  <dev@maarch.org>
* @author  Loïc Vinet  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @license GPL
*
*/
class folders_show extends functions
{

	/**
	* Show the tree of folder
	*
	* @param array $result array of the tree folder
	* @param string $link link to the form page
	*/
	public function folder_tree($result,$link)
	{
		$second_level=0;
		$third_level=0;

		echo "<div  >";
		//echo $_GET['second_level'];
		for ($i=0;$i<count($result);$i++)
		{
			foreach(array_keys($result[$i]) as $value)
			{
				if($value == "first_level_id")
				{
					?><img src="<?php  echo $_SESSION['config']['img'];?>/dossiers2.gif" align="middle" alt="" />
				<span class='selected'>
					<?php  echo $result[$i]['first_level_label'];?><br />
					</span>
					<div class='dir_second_level'>
                    <?php
					for ($j=0;$j<count($result[$i]['level2']['second_level_id']);$j++)
					{
						if($_GET['second_level'] == $result[$i]['level2']['second_level_id'][$second_level])
						{
							?><span class="selected">
							<a href="index.php?page=<?php  echo $link;?>"><img src="<?php  echo $_SESSION['config']['img'];?>/dir_open.gif" border="0" align='middle' alt="" /> 		<?php  echo $result[$i]['level2']['second_level_label'][$second_level];?></a><br/>
							</span>
							<div class='dir_third_level'>
							<?php  for($k=0;$k<count($result[$i]['level2'][$second_level]['level3']['type_id']);$k++)
							{
								?>
								<a href="index.php?page=<?php  echo $link;?>&amp;type_id=<?php  echo $result[$i]['level2'][$second_level]['level3']['type_id'][$third_level];?>&amp;second_level=<?php  echo $result[$i]['level2']['second_level_id'][$second_level];?>&amp;coll_id=<?php  echo $result[$i]['level2'][$second_level]['level3']['coll_id'][$third_level] ?>"><img src="<?php  echo $_SESSION['config']['img'];?>/arrow_primary.gif" border="0" align="middle" alt="" /> <?php  echo $result[$i]['level2'][$second_level]['level3']['type_label'][$third_level];?></a><br/> <?php
								$third_level++;
							}
							$second_level++;
							echo "</div>";
						}
						else
						{
							for($k=0;$k<count($result[$i]['level2'][$second_level]['level3']['type_id']);$k++)
							{
								$third_level++;
							}
							?><a href="index.php?page=<?php  echo $link;?>&amp;second_level=<?php  echo $result[$i]['level2']['second_level_id'][$second_level];?>"><img src="<?php  echo $_SESSION['config']['img'];?>/dir_close.gif" align="top" border="0" alt="" /> <?php  echo $result[$i]['level2']['second_level_label'][$second_level];?></a><br/><?php
							$second_level++;
						}
					}
					echo "</div>";
				}
			}
		}
		echo "</div>";
	}

	/**
	* construct the folders tree
	*
	*/
	public function construct_tree()
	{
		$sun= new functions();
		$conn = new dbquery();
		$conn->connect();
		$conn2 = new dbquery();
		$conn2->connect();

		$query="select ".$_SESSION['tablename']['doctypes'].".coll_id, ".$_SESSION['tablename']['doctypes'].".type_id as type_id, ".$_SESSION['tablename']['doctypes'].".description as type_description, ".$_SESSION['tablename']['doctypes'].".doctypes_first_level_id as first_level_id, ".$_SESSION['tablename']['doctypes'].".doctypes_second_level_id as second_level_id,
		".$_SESSION['tablename']['doctypes_first_level'].".doctypes_first_level_label as first_level_label, ".$_SESSION['tablename']['doctypes_second_level'].".doctypes_second_level_label as second_level_label
		from ".$_SESSION['tablename']['doctypes']."
		left join ".$_SESSION['tablename']['doctypes_first_level']." on ".$_SESSION['tablename']['doctypes'].".doctypes_first_level_id = ".$_SESSION['tablename']['doctypes_first_level'].".doctypes_first_level_id
		left join ".$_SESSION['tablename']['doctypes_second_level']." on ".$_SESSION['tablename']['doctypes'].".doctypes_second_level_id = ".$_SESSION['tablename']['doctypes_second_level'].".doctypes_second_level_id
		where ".$_SESSION['tablename']['doctypes'].".enabled = 'Y' and ".$_SESSION['tablename']['doctypes_second_level'].".enabled = 'Y' and ".$_SESSION['tablename']['doctypes_first_level'].".enabled = 'Y' order by ".$_SESSION['tablename']['doctypes'].".doctypes_first_level_id, ".$_SESSION['tablename']['doctypes'].".doctypes_second_level_id, ".$_SESSION['tablename']['doctypes'].".description";

		$conn->query($query);
		//$conn->show();

		$tab_result = array();
		$tab_tree = array();
		$i=0;
		while ($value = $conn->fetch_object())
		{
			//echo $value->type_description."<br/>";
			$tab_result[$i]['type_id'] = $value->type_id;

			$tab_result[$i]['type_description'] = $this->show_string($value->type_description);
			$tab_result[$i]['first_level_id'] = $value->first_level_id;
			$tab_result[$i]['first_level_label'] = $this->show_string($value->first_level_label);
			$tab_result[$i]['second_level_id'] = $value->second_level_id;
			$tab_result[$i]['second_level_label'] = $this->show_string($value->second_level_label);
			$i++;
		}

		$query="select doctypes_first_level_id, doctypes_first_level_label from ".$_SESSION['tablename']['doctypes_first_level']." where enabled = 'Y' order by doctypes_first_level_label";
		$conn->query($query);
		$i=0;
		while ($value = $conn->fetch_object())
		{
			$tab_tree[$i]['first_level_id'] = $value->doctypes_first_level_id;
			$tab_tree[$i]['first_level_label'] = $this->show_string($value->doctypes_first_level_label);
			$tab_tree[$i]['level2']=array();
			$i++;
		}
		$j=0;
		$k=0;
		for ($i=0;$i<count($tab_tree);$i++)
		{
			foreach(array_keys($tab_tree[$i]) as $value)
			{
				if($value == "first_level_id")
				{
					$query="select doctypes_second_level_id, doctypes_second_level_label from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_first_level_id =".$tab_tree[$i]['first_level_id']." and enabled = 'Y' order by doctypes_second_level_label";
					$conn->query($query);
					while ($val = $conn->fetch_object())
					{
						$tab_tree[$i]['level2']['second_level_id'][$j]=$val->doctypes_second_level_id;
						$tab_tree[$i]['level2']['second_level_label'][$j]=$this->show_string($val->doctypes_second_level_label);
						$tab_tree[$i]['level2'][$j]['level3']=array();
						$query2="select type_id, description, coll_id from ".$_SESSION['tablename']['doctypes']." where doctypes_first_level_id =".$tab_tree[$i]['first_level_id']." and doctypes_second_level_id=".$tab_tree[$i]['level2']['second_level_id'][$j]." and enabled = 'Y' order by description";
						$conn2->query($query2);
						while ($val2 = $conn2->fetch_object())
						{
							$tab_tree[$i]['level2'][$j]['level3']['type_id'][$k]=$val2->type_id;							$tab_tree[$i]['level2'][$j]['level3']['coll_id'][$k]=$val2->coll_id;
							$tab_tree[$i]['level2'][$j]['level3']['type_label'][$k]=$this->show_string($val2->description);
							$k++;
						}
						$j++;
					}
				}
			}
		}
		//$this->show_array($tab_tree);
		$this->folder_tree($tab_tree,"view_folder&amp;module=folder");
	}



	/**
	* Show all the folder data (used in the salary sheet and view folder)
	*
	* @param array $folder_array array containing folder data
	* @param string $link link to the form result
	* @param string $path_trombi path to the photo
	*/
	public function view_folder_info_details($folder_array,$link,$path_trombi = '')
	{
		if ($_SESSION['user']['services']['modify_folder'] )
		{
			$up = true;
		 }
		 else
		 {
		 	$up=false;
		 }
		$db = new dbquery();
		$db->connect();
		 ?>
         	<form name="view_folder_detail" method='post' action='<?php  echo $link;?>' class="folder_forms" ><?php
		if($up)
		{
		?><input type="hidden" value="up" name="mode" />
		<input type="hidden" value="true" name="folder_index" />
		<?php
		}?>

			<?php
					if($folder_array['complete'] == "Y")
					{
						$complete = _FOLDER.' '.strtolower(_COMPLETE);
					}
					else
					{
						$complete = _FOLDER.' '.strtolower(_INCOMPLETE);
					}
					?><br/>
                    <div align="center">
                    <span>
                    	<label><?php  echo _MATRICULE;?> :</label>
                        <input type="text" readonly="readonly" value="<?php  echo $folder_array['folder_id'];?>" class="readonly" />
                        &nbsp;&nbsp;&nbsp;
                          <label><?php  echo _FOLDERTYPE;?> :</label>
                        <input type="text" readonly="readonly" value="<?php  echo $folder_array['foldertype_label'];?>" class="readonly" />
                        </span>
                        <span >
                        <br/><small>(
                        <?php  echo $complete;?>)</small>
                        </span>



                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span id="link_right" >
                    	<?php  if($_SESSION['origin'] == "view_folder")
						{?><a href="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=show_folder&amp;module=folder&amp;field=<?php  echo $_SESSION['current_folder_id'];?>"><img src="<?php  echo $_SESSION['config']['img'];?>/s_sheet_c.gif" width="20px" height="25px"
                    	alt="logo"/><?php  echo _VIEW_SALARY_SHEET;?></a>
                        <?php  } ?>
                    </span>
                  </div>
                    <br/>


            <table border="0" width="100%">
		<?php
			//$this->show_array( $folder_array);
			for($i=0; $i < count($folder_array['index']);$i++)
			{
				 if ($i%2 != 1 || $i==0) // pair
				 {
   					echo '<tr>'	;
				}
				?>
				<td width="24%" align="left" >
					<span>
						<?php
						if($folder_array['index'][$i]['mandatory'])
						{
							echo "<b>".$folder_array['index'][$i]['label']."</b> : ";
						}
						else
						{
							echo $folder_array['index'][$i]['label']." : ";
						}
						?>
                    </span>
                  </td>
                  <td width="25%" align="left">
                    <?php
			if($up==true)
			{

					if((isset($folder_array['index'][$i]['foreign_key']) && !empty($folder_array['index'][$i]['foreign_key']) && isset($folder_array['index'][$i]['foreign_label']) && !empty($folder_array['index'][$i]['foreign_label']) && isset($folder_array['index'][$i]['tablename']) && !empty($folder_array['index'][$i]['tablename'])) || (isset($folder_array['index'][$i]['values']) && count($folder_array['index'][$i]['values']) > 0))
					{
					?>
                    	<select name="<?php  echo $folder_array['index'][$i]['column'];?>" id="<?php  echo $folder_array['index'][$i]['column'];?>">
                        <option value=""><?php  echo _CHOOSE;?></option>
                        <?php
						if(isset($folder_array['index'][$i]['values']) && count($folder_array['index'][$i]['values']) > 0)
						{
							for($k=0; $k < count($folder_array['index'][$i]['values']); $k++)
							{
							?>
                            	<option value="<?php  echo $folder_array['index'][$i]['values'][$k]['label'];?>" <?php  if($folder_array['index'][$i]['values'][$k]['label'] == $folder_array['index'][$i]['value']){ echo 'selected="selected"'; } ?>><?php  echo $folder_array['index'][$i]['values'][$k]['label'];?></option>
                            <?php
							}
						}
						else
						{
							$query = "select ".$folder_array['index'][$i]['foreign_key'].", ".$folder_array['index'][$i]['foreign_label']." from ".$folder_array['index'][$i]['tablename'];

							if(isset($folder_array['index'][$i]['where']) && !empty($folder_array['index'][$i]['where']))
							{
								$query .= " where ".$folder_array['index'][$i]['where'];
							}
							if(isset($folder_array['index'][$i]['order']) && !empty($folder_array['index'][$i]['order']))
							{
								$query .= ' '.$folder_array['index'][$i]['order'];
							}

							$db->query($query);

							while($res = $db->fetch_object())
							{
							?>
                            <option value="<?php  echo $res->$folder_array['index'][$i]['foreign_key'];?>" <?php  if($res->$folder_array['index'][$i]['foreign_key'] == $folder_array['index'][$i]['value']){ echo 'selected="selected"';}?>><?php  echo $res->$folder_array['index'][$i]['foreign_label'];?></option>
                            <?php
							}
						}
						?>
                        </select>
                    <?php
					}
					else
					{
						if($folder_array['index'][$i]['date'])
						{
						/*?>
							<img src="<?php  echo $_SESSION['config']['businessappurl'];?>img/calendar.jpg" alt="" name="for_<?php  echo $folder_array['index'][$i]['column'];?>" id='for_<?php  echo $folder_array['index'][$i]['column'];?>' onclick='showCalender(this)' />
						<?php */
                   		 }
						?>
						<input type="text" name="<?php  echo $folder_array['index'][$i]['column'];?>" id="<?php  echo $folder_array['index'][$i]['column'];?>" <?php  if($_SESSION['field_error'][$folder_array['index'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?> <?php  if($folder_array['index'][$i]['date'])
						{ echo 'class="medium"'; } ?> value="<?php
						if($folder_array['index'][$i]['date'])
						{
							echo $this->format_date_db($folder_array['index'][$i]['value']);
						}
						else
						{
							echo $folder_array['index'][$i]['value'];
						}
						echo '"';
						if($folder_array['index'][$i]['date'])
						{
						?>
						onclick="showCalender(this);"
						<?php  }?>
						/>
                    <?php
					}
					if($folder_array['index'][$i]['mandatory'])
					{
						?>
                        <input type="hidden" name="mandatory_<?php  echo $folder_array['index'][$i]['column'];?>" id="mandatory_<?php  echo $folder_array['index'][$i]['column'];?>" value="true" />
                        <?php
					}
			}
			else
			{
				?><input type="text" name="<?php  echo $folder_array['index'][$i]['column'];?>" id="<?php  echo $folder_array['index'][$i]['column'];?>" <?php  if($folder_array['index'][$i]['date'])
						{ echo 'class="medium"'; } ?> value="<?php  echo $folder_array['index'][$i]['value'] ?>" readonly="readonly" class="readonly" /><?php
			}

					?>
				</td>
				<?php
				if ($i%2 == 1 && $i!=0) // impair
				 {
   					echo '</tr>'	;
				}
				else
				{

					if($i+1 == count($folder_array['index']))
					{
						echo '<td width="2" colspan="3">&nbsp;</td></tr>';
					}
					else
					{
						echo  '<td width="2">&nbsp;</td>';
					}
				}
			}
		?></table>
		<div align="right">
		<?php
       	if($up==true)
		{ ?>
			<input type="submit" class="button" value="<?php  echo _UPDATE_FOLDER;?>" />
		<?php  }
		//require_once($_SESSION['pathtocoreclass'].'class_core_tools.php');
		$ct = new core_tools();
		$ct->execute_modules_services($_SESSION['modules_services'], "index.php?page=".$_SESSION['origin'], '', 'delete_folder', 'folder');
				?>
         </div>
		</form><?php
	}

	/**
	* Show the folder info (used in the view_folder_out page)
	*
	* @param array $folder_array array containing folder data
	* @param string $link link to the form result
	* @param string $path_trombi path to the photo
	*/
/*	public function view_folder_out($folder_array,$link,$path_trombi = '')
	{
		$db = new dbquery();
		$db->connect();
		?>
        <div id="folder_out_form">
        <form class="forms2" method="post" action="<?php  echo $link;?>" name="view_folder_out">
		<div class="leftpart2" >
			<p><?php
			 if(!empty($path_trombi))
			{
			if(file_exists($path_trombi.$folder_array['PHOTO']))
			{
				$file_trombi = $path_trombi.$folder_array['PHOTO'];
			}
			else
			{
				$file_trombi = $path_trombi."standard.bmp";
			}
			?><img src='".$file_trombi."' alt="" />
        <?php  } ?>
            <label><?php  echo _MATRICULE;?></label><span class="colon">: </span>
            <input type="text" readonly="readonly" name="matricule" class="readonly" value="<?php  echo $folder_array['FOLDER_ID'];?>" />
			</p>

        </div>
        <div class="leftpart">
        	<p>
                <label><?php  echo _LASTNAME;?> </label><span class="colon">: </span>
                <input type='text' name='ins_last_name' value="<?php  echo $folder_array['NOM'];?>" readonly="readonly" class="readonly" />
                <input type='hidden' name='flag_ins' value='true' />
                            <input type='hidden' name='ins_folder_system_id' value="<?php  echo $folder_array['SYSTEM_ID'];?>" />
                            <input type='hidden' name='ins_folder_id' value="<?php  echo $folder_array['FOLDER_ID'];?>"/>
			</p>

        </div>

        <div class="rightpart">
            <p>
                <label><?php  echo _FIRSTNAME;?></label><span class="colon">: </span>
                 <input type='text' name='ins_first_name' value="<?php  echo $folder_array['PRENOM'];?>" readonly="readonly" class="readonly" />
            </p>
        </div>
		<p><em><?php  echo _FOLDER_OUT_PERSON;?></em></p>

 		<div class="leftpart">
            <p >
                <label><?php  echo _LASTNAME;?></label><span class="colon">: </span>
                 <input type='text' name='ins_last_name_in' />
            </p>
             <p>
                <label><?php  echo _FILE_OUT_DATE2;?></label><span class="colon">: </span>
                 <img src="<?php  echo $_SESSION['config']['img'];?>/calendar.jpg" alt='' name='for_date' id='for_ins_retrait_date' onclick='showCalender(this);' />&nbsp;<input type='text' name='ins_retrait_date' size='15' id='ins_retrait_date' />
            </p>


        </div>
          <div class="rightpart">
            <p>
                <label><?php  echo _FIRSTNAME;?></label><span class="colon">: </span>
                 <input type='text' name='ins_first_name_in' />
            </p>
            <p>
                <label><?php  echo _FOLDER_OUT_RETURN_DATE;?></label><span class="colon">: </span>
                 <img src="<?php  echo $_SESSION['config']['img'];?>/calendar.jpg" alt='' name='for_date' id='for_ins_restitution_date' onclick='showCalender(this);' />&nbsp;<input type='text' name='ins_restitution_date' size='15' id='ins_restitution_date' />
            </p>
        </div>
        <div class="leftpart2" >
         <p>
                <label><?php  echo _FOLDER_OUT_MOTIVE;?> :</label>
                <table border="0">
                	<tr align="left">
                    	<td><input type='radio' name='ins_motif' value='1' class="radiobutton" /><?php  echo _MOTIVE1;?></td>
                    </tr>
                    <tr align="left">
                    	<td><input type='radio' name='ins_motif' value='2' class="radiobutton"  /><?php  echo _MOTIVE2;?></td>
                    </tr>
                    <tr align="left">
                    	<td><input type='radio' name='ins_motif' value='3' class="radiobutton"  /><?php  echo _MOTIVE3;?></td>
                    </tr>
                </table>
                 <br />
                            	<br/>
                                <br />
            </p>
        </div>
          <p class="buttons">
          	<input type='submit' value='<?php  echo _SAVE_SHEET;?>' class="button" />
          </p>


   </form>
   </div><?php
	}
	*/
}
?>
