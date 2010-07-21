<?php
//functions::show_array($_REQUEST);
if($mode == "list")
{
	list_show::admin_list($tab, $i, $title, 'cycle_id','cycles_management_controler&mode=list','life_cycle', 'cycle_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_CYCLES, _CYCLE, $_SESSION['config']['businessappurl'].'static.php?module=life_cycle&filename=manage_lc_b.gif', true, true, false, true, "", true, $autoCompletionArray);
}
elseif($mode == "up" || $mode == "add")
{
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_cycle_b.gif" alt="" />
		<?php
		if($mode == "add")
		{
			echo _CYCLE_ADDITION;
		}
		elseif($mode == "up")
		{
			echo _CYCLE_MODIFICATION;
		}
		?>
	</h1>
	<div id="inner_content" class="clearfix" align="center">
		<br><br>
		<?php
		if($state == false)
		{
			echo "<br /><br />"._THE_CYCLE." "._UNKOWN."<br /><br /><br /><br />";
		}
		else
		{
			//functions::show_array($cycle);
			?>
			<form name="formcycle" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=cycles_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="cycles_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<p>
				 	<label for="id"><?php echo _CYCLE_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['cycles']['cycle_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="cycle_label"><?php echo _CYCLE_LABEL; ?> : </label>
					<input name="cycle_label" type="text"  id="cycle_label" value="<?php echo functions::show($_SESSION['m_admin']['cycles']['cycle_label']); ?>"/>
				</p>
				<p>
					<label for="coll_id"><?php echo _COLLECTION; ?> : </label>
					<select name="coll_id" id="coll_id">
						<option value=""><?php echo _CHOOSE__COLLECTION;?></option>
						<?php
						for($cptCollection=0;$cptCollection<count($_SESSION['collections']);$cptCollection++)
						{
							?>
							<option value="<?php echo $_SESSION['collections'][$cptCollection]['id'];?>" <?php if($_SESSION['m_admin']['cycles']['coll_id'] == $_SESSION['collections'][$cptCollection]['id']) { echo 'selected="selected"';}?>><?php echo $_SESSION['collections'][$cptCollection]['id']." : ".$_SESSION['collections'][$cptCollection]['label'];?></option>
							<?php
						}
						?>
					</select>
				</p>
				<p>
					<label for="cycle_mode"><?php echo _CYCLE_MODE; ?> : </label>
					<select name="cycle_mode" id="cycle_mode">
						<option value=""><?php echo _CHOOSE_CYCLE_MODE;?></option>
						<?php
						for($cptCycleMode=0;$cptCycleMode<count($_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE']);$cptCycleMode++)
						{
							?>
							<option value="<?php echo $_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE'][$cptCycleMode];?>" <?php if($_SESSION['m_admin']['cycles']['cycle_mode'] == $_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE'][$cptCycleMode]) { echo 'selected="selected"';}?>><?php echo $_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE'][$cptCycleMode];?></option>
							<?php
						}
						?>
					</select>
				</p>
				<p>
				 	<label for="where_clause"><?php echo _WHERE_CLAUSE; ?> : </label>
					<textarea  cols="30" rows="4"  name="where_clause" id="where_clause" ><?php echo functions::show($_SESSION['m_admin']['cycles']['where_clause']); ?></textarea>
				</p>
				<p>
					<label><?php echo _IS_MUST_COMPLETE; ?> : </label>
					<input type="radio" class="check" name="is_must_complete" value="Y" <?php if($_SESSION['m_admin']['cycles']['is_must_complete'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
					<input type="radio" class="check" name="is_must_complete" value="N" <?php if($_SESSION['m_admin']['cycles']['is_must_complete'] == 'N' || $_SESSION['m_admin']['cycles']['is_must_complete'] == ''){?> checked="checked"<?php } ?> /><?php echo _NO;?>
				</p>
	            <p>
				 	<label for="preprocess_script"><?php echo _PREPROCESS_SCRIPT; ?> : </label>
					<input name="preprocess_script" type="text"  id="preprocess_script" value="<?php echo functions::show($_SESSION['m_admin']['cycles']['preprocess_script']); ?>"/>
				</p>
				<p>
				 	<label for="postprocess_script"><?php echo _POSTPROCESS_SCRIPT; ?> : </label>
					<input name="postprocess_script" type="text"  id="postprocess_script" value="<?php echo functions::show($_SESSION['m_admin']['cycles']['postprocess_script']); ?>"/>
				</p>
				<p>
					<label><?php echo _IS_VALID_BY_USER; ?> : </label>
					<input type="radio" class="check" name="is_valid_by_user" value="Y" <?php if($_SESSION['m_admin']['cycles']['is_valid_by_user'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
					<input type="radio" class="check" name="is_valid_by_user" value="N" <?php if($_SESSION['m_admin']['cycles']['is_valid_by_user'] == 'N' || $_SESSION['m_admin']['cycles']['is_valid_by_user'] == ''){?> checked="checked"<?php } ?> /><?php echo _NO;?>
				</p>
				<p>
				 	<label for="users_user_id"><?php echo _USER_ID; ?> : </label>
					<input name="users_user_id" type="text"  id="users_user_id" value="<?php echo functions::show($_SESSION['m_admin']['cycles']['users_user_id']); ?>"/>
				</p>
				<p class="buttons">
					<?php
					if($mode == "up")
					{
						?>
						<input class="button" type="submit" name="submit" value="<?php echo _MODIFY; ?>" />
						<?php
					}
					elseif($mode == "add")
					{
						?>
						<input type="submit" class="button"  name="submit" value="<?php echo _ADD; ?>" />
						<?php
					}
					?>
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=cycles_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<script type="text/javascript">
				initList('what', 'whatList', '<?php  echo $autoCompletionArray2["list_script_url"];?>', 'what', '<?php  echo $autoCompletionArray2["number_to_begin"];?>');
			</script>
			<?php
		}
		?>
	</div>
	<?php
}
?>