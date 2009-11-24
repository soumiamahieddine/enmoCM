<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$db = new dbquery();
$db->connect();
$fold = new folder();
$_SESSION['folder_index_to_use'] = array();
if(isset($_SESSION['foldertype'])&&!empty($_SESSION['foldertype']))
{

		$db->query("select * from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$_SESSION['foldertype']);
		$res = $db->fetch_array();
		$desc = $db->show_string($res['foldertype_label']);
		$fold->retrieve_index($res);
		//$func->show_array($_SESSION['index_to_use']);
	}

$core_tools->load_html();
$core_tools->load_header();

?>
<body >

<br/>
<?php  if(isset($_SESSION['foldertype']) && !empty($_SESSION['foldertype']))
{?>
 <div id="create_folder">
     	<form name="create_folder_frm" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=create_folder" class="forms addforms">
			<input type="hidden" name="display"  value="true" />
			<input type="hidden" name="module"  value="folder" />
			<input type="hidden" name="page"  value="create_folder" />
        <?php
        for($i=0;$i<=count($_SESSION['folder_index_to_use']);$i++)
		{
			if($_SESSION['folder_index_to_use'][$i]['label'] <> "" )
			{
				?>
				<p>
					<label>
						<?php
						if($_SESSION['folder_index_to_use'][$i]['mandatory'])
						{
							echo "<b>".$_SESSION['folder_index_to_use'][$i]['label']."</b> : ";
						}
						else
						{
							echo $_SESSION['folder_index_to_use'][$i]['label']." : ";
						}
						?>
                    </label>
                    <?php

					if((isset($_SESSION['folder_index_to_use'][$i]['foreign_key']) && !empty($_SESSION['folder_index_to_use'][$i]['foreign_key']) && isset($_SESSION['folder_index_to_use'][$i]['foreign_label']) && !empty($_SESSION['folder_index_to_use'][$i]['foreign_label']) && isset($_SESSION['folder_index_to_use'][$i]['tablename']) && !empty($_SESSION['folder_index_to_use'][$i]['tablename'])) || (isset($_SESSION['folder_index_to_use'][$i]['values']) && count($_SESSION['folder_index_to_use'][$i]['values']) > 0))
					{
					?>
                    	<select name="<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>" id="<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>">
                        <option value=""><?php  echo _CHOOSE;?></option>
                        <?php
						if(isset($_SESSION['folder_index_to_use'][$i]['values']) && count($_SESSION['folder_index_to_use'][$i]['values']) > 0)
						{
							for($k=0; $k < count($_SESSION['folder_index_to_use'][$i]['values']); $k++)
							{
							?>
                            	<option value="<?php  echo $_SESSION['folder_index_to_use'][$i]['values'][$k]['label'];?>"><?php  echo $_SESSION['folder_index_to_use'][$i]['values'][$k]['label'];?></option>
                            <?php
							}
						}
						else
						{
							$query = "select ".$_SESSION['folder_index_to_use'][$i]['foreign_key'].", ".$_SESSION['folder_index_to_use'][$i]['foreign_label']." from ".$_SESSION['folder_index_to_use'][$i]['tablename'];

							if(isset($_SESSION['folder_index_to_use'][$i]['where']) && !empty($_SESSION['folder_index_to_use'][$i]['where']))
							{
								$query .= " where ".$_SESSION['folder_index_to_use'][$i]['where'];
							}
							if(isset($_SESSION['folder_index_to_use'][$i]['order']) && !empty($_SESSION['folder_index_to_use'][$i]['order']))
							{
								$query .= ' '.$_SESSION['folder_index_to_use'][$i]['order'];
							}

							$db->query($query);

							while($res = $db->fetch_object())
							{
							?>
                            <option value="<?php  echo $res->$_SESSION['folder_index_to_use'][$i]['foreign_key'];?>"><?php  echo $db->show_string($res->$_SESSION['folder_index_to_use'][$i]['foreign_label']);?></option>
                            <?php
							}
						}
						?>
                        </select>
                    <?php
					}
					else
					{
						if($_SESSION['folder_index_to_use'][$i]['date'])
						{
						?>
							<img src="<?php  echo $_SESSION['config']['businessappurl'];?>img/calendar.jpg" alt="" name="for_<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>" id='for_<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>' onclick='showCalender(this)' />
						<?php
                   		 }
						?>
						<input type="text" name="<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>" id="<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>"  <?php  if($_SESSION['field_error'][$_SESSION['folder_index_to_use'][$i]['column']]){?>style="background-color:#FF0000"<?php  }?> <?php  if($_SESSION['folder_index_to_use'][$i]['date'])
						{ ?>class="medium"<?php  } ?> value="<?php  echo $_SESSION['create_folder'][$_SESSION['folder_index_to_use'][$i]['column']];?>"/>
                    <?php
					}
					if($_SESSION['folder_index_to_use'][$i]['mandatory'])
					{
						?>
                        <input type="hidden" name="mandatory_<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>" id="mandatory_<?php  echo $_SESSION['folder_index_to_use'][$i]['column'];?>" value="true" />
                        <?php
					}
					?>
				</p>
				<p>&nbsp;</p>
				<?php
			}
		}?>
     	<p class="buttons">

            <input type="submit" class="button"  value="<?php  echo _VALIDATE;?>" name="submit" />
         </p>
     	</form>
      </div>
<?php
}
else
{
	echo _CHOOSE_FOLDERTYPE;
}
?>
</body>
</html>
