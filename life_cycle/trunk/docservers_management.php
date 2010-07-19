<?php
//functions::show_array($_REQUEST);
if($mode == "list")
{
	list_show::admin_list($tab, $i, $title, 'docerver_id','docservers_management_controler&mode=list','life_cycle', 'docserver_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_DOSERVERS, _DOCSERVER, $_SESSION['config']['businessappurl'].'static.php?module=life_cycle&filename=manage_lc_b.gif', true, true, false, true, "", true, $autoCompletionArray);
}
elseif($mode == "up" || $mode == "add")
{
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_docserver_b.gif" alt="" />
		<?php
		if($mode == "add")
		{
			echo _DOCSERVER_ADDITION;
		}
		elseif($mode == "up")
		{
			echo _DOCSERVER_MODIFICATION;
		}
		?>
	</h1>
	<script language="javascript">
		function convertSize()
		{
			if(!isNaN($('size_limit').value))
			{
				if($('size_format').value == "MB")
				{
					$('size_limit').value = $('size_limit_hidden').value / (1000 * 1000);
					$('actual_size').value = $('actual_size_hidden').value / (1000 * 1000);
				}
				if($('size_format').value == "GB")
				{
					$('size_limit').value = $('size_limit_hidden').value / (1000 * 1000 * 1000);
					$('actual_size').value = $('actual_size_hidden').value / (1000 * 1000 * 1000);
				}
				if($('size_format').value == "TB")
				{
					$('size_limit').value = $('size_limit_hidden').value / (1000 * 1000 * 1000 * 1000);
					$('actual_size').value = $('actual_size_hidden').value / (1000 * 1000 * 1000 * 1000);
				}
			}
			else
			{
				window.alert('WRONG FORMAT');
			}
		}
		
		function saveSizeInBytes()
		{
			if(!isNaN($('size_limit').value))
			{
				//$('size_limit_hidden').value = $('size_limit').value;
				if($('size_format').value == "MB")
				{
					$('size_limit_hidden').value = $('size_limit').value * (1000 * 1000);
				}
				if($('size_format').value == "GB")
				{
					$('size_limit_hidden').value = $('size_limit').value * (1000 * 1000 * 1000);
				}
				if($('size_format').value == "TB")
				{
					$('size_limit_hidden').value = $('size_limit').value * (1000 * 1000 * 1000 * 1000);
				}
			}
			else
			{
				window.alert('WRONG FORMAT');
			}
		}
	</script>
	<div id="inner_content" class="clearfix" align="center">
		<br><br>
		<?php
		if($state == false)
		{
			echo "<br /><br />"._THE_DOCSERVER." "._UNKOWN."<br /><br /><br /><br />";
		}
		else
		{
			//functions::show_array($docserver);
			?>
			<form name="formdocserver" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=docservers_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="docservers_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<input type="hidden" name="size_limit_hidden" id="size_limit_hidden" value="<?php echo $_SESSION['m_admin']['docservers']['size_limit'];?>"/>
				<input type="hidden" name="actual_size_hidden" id="actual_size_hidden" value="<?php echo $_SESSION['m_admin']['docservers']['actual_size'];?>"/>
				<p>
				 	<label for="id"><?php echo _DOCSERVER_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['docserver_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="device_type"><?php echo _DEVICE_TYPE; ?> : </label>
					<input name="device_type" type="text"  id="device_type" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['device_type']); ?>"/>
				</p>
				<p>
				 	<label for="device_label"><?php echo _DEVICE_LABEL; ?> : </label>
					<input name="device_label" type="text"  id="device_label" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['device_label']); ?>"/>
				</p>
				<p>
	                <label><?php echo _IS_READONLY; ?> : </label>
	                <input type="radio" class="check" name="is_readonly" value="Y" <?php if($_SESSION['m_admin']['docservers']['is_readonly'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
	                <input type="radio" class="check" name="is_readonly" value="N" <?php if($_SESSION['m_admin']['docservers']['is_readonly'] == 'N' || $_SESSION['m_admin']['docservers']['is_readonly'] == ''){?> checked="checked"<?php } ?> /><?php echo _NO;?>
	            </p>
	            <p>
				 	<label for="size_format"><?php echo _SIZE_FORMAT; ?> : </label>
					<select name="size_format" id="size_format" onchange="javascript:convertSize();">
						<option value="MB"><?php echo _MB;?></option>
						<option value="GB"><?php echo _GB;?></option>
						<option value="TB"><?php echo _TB;?></option>
					</select>
				</p>
	            <p>
				 	<label for="size_limit"><?php echo _SIZE_LIMIT; ?> : </label>
					<input name="size_limit" type="text" id="size_limit" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['size_limit']); ?>" onchange="javascript:saveSizeInBytes();"/>
				</p>
				<?php
				if($mode == "up")
				{
					?>
					<p>
					 	<label for="actual_size"><?php echo _ACTUAL_SIZE; ?> : </label>
						<input name="actual_size" type="text" id="actual_size" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['actual_size']); ?>" readonly="readonly" class="readonly"/>
					</p>
					<?php
				}
				?>
				<p>
				 	<label for="path_template"><?php echo _PATH_TEMPLATE; ?> : </label>
					<input name="path_template" type="text"  id="path_template" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['path_template']); ?>"/>
				</p>
				<!--<p>
				 	<label for="ext_docserver_info"><?php echo _EXT_DOCSERVER_INFO; ?> : </label>
					<input name="ext_docserver_info" type="text"  id="ext_docserver_info" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['ext_docserver_info']); ?>"/>
				</p>
				<p>
				 	<label for="chain_before"><?php echo _CHAIN_BEFORE; ?> : </label>
					<input name="chain_before" type="text"  id="chain_before" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['chain_before']); ?>"/>
				</p>
				<p>
				 	<label for="chain_after"><?php echo _CHAIN_AFTER; ?> : </label>
					<input name="chain_after" type="text"  id="chain_after" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['chain_after']); ?>"/>
				</p>
				<p>
				 	<label for="closing_date"><?php echo _CLOSING_DATE; ?> : </label>
					<input name="closing_date" type="text"  id="closing_date" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['closing_date']); ?>"/>
				</p>-->
				<p>
				 	<label for="priority"><?php echo _PRIORITY; ?> : </label>
					<input name="priority" type="text"  id="priority" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['priority']); ?>"/>
				</p>
				<p>
					<label for="oais_mode"><?php echo _OAIS_MODE; ?> : </label>
					<select name="oais_mode" id="oais_mode">
						<option value=""><?php echo _CHOOSE_OAIS_MODE;?></option>
						<?php
						for($cptOaisMode=0;$cptOaisMode<count($_SESSION['lifeCycleFeatures']['DOCSERVERS']['OAIS']['MODE']);$cptOaisMode++)
						{
							?>
							<option value="<?php echo $_SESSION['lifeCycleFeatures']['DOCSERVERS']['OAIS']['MODE'][$cptOaisMode];?>" <?php if($_SESSION['m_admin']['docservers']['oais_mode'] == $_SESSION['lifeCycleFeatures']['DOCSERVERS']['OAIS']['MODE'][$cptOaisMode]) { echo 'selected="selected"';}?>><?php echo $_SESSION['lifeCycleFeatures']['DOCSERVERS']['OAIS']['MODE'][$cptOaisMode];?></option>
							<?php
						}
						?>
					</select>
				</p>
				<p>
					<label for="sign_mode"><?php echo _SIGN_MODE; ?> : </label>
					<select name="sign_mode" id="sign_mode">
						<option value=""><?php echo _CHOOSE_SIGN_MODE;?></option>
						<?php
						for($cptSignMode=0;$cptSignMode<count($_SESSION['lifeCycleFeatures']['DOCSERVERS']['SIGN']['MODE']);$cptSignMode++)
						{
							?>
							<option value="<?php echo $_SESSION['lifeCycleFeatures']['DOCSERVERS']['SIGN']['MODE'][$cptSignMode];?>" <?php if($_SESSION['m_admin']['docservers']['sign_mode'] == $_SESSION['lifeCycleFeatures']['DOCSERVERS']['SIGN']['MODE'][$cptSignMode]) { echo 'selected="selected"';}?>><?php echo $_SESSION['lifeCycleFeatures']['DOCSERVERS']['SIGN']['MODE'][$cptSignMode];?></option>
							<?php
						}
						?>
					</select>
				</p>
				<p>
					<label for="compress_mode"><?php echo _COMPRESS_MODE; ?> : </label>
					<select name="compress_mode" id="compress_mode">
						<option value=""><?php echo _CHOOSE_COMPRESS_MODE;?></option>
						<?php
						for($cptCompressMode=0;$cptCompressMode<count($_SESSION['lifeCycleFeatures']['DOCSERVERS']['COMPRESS']['MODE']);$cptCompressMode++)
						{
							?>
							<option value="<?php echo $_SESSION['lifeCycleFeatures']['DOCSERVERS']['COMPRESS']['MODE'][$cptCompressMode];?>" <?php if($_SESSION['m_admin']['docservers']['compress_mode'] == $_SESSION['lifeCycleFeatures']['DOCSERVERS']['COMPRESS']['MODE'][$cptCompressMode]) { echo 'selected="selected"';}?>><?php echo $_SESSION['lifeCycleFeatures']['DOCSERVERS']['COMPRESS']['MODE'][$cptCompressMode];?></option>
							<?php
						}
						?>
					</select>
				</p>
				<p>
				 	<label for="docserver_locations_docserver_location_id"><?php echo _DOCSERVER_LOCATION; ?> : </label>
					<input name="docserver_locations_docserver_location_id" type="text"  id="docserver_locations_docserver_location_id" value="<?php echo functions::show($_SESSION['m_admin']['docservers']['docserver_locations_docserver_location_id']); ?>"/>
				</p>
				<p>
					<label for="coll_id"><?php echo _COLLECTION; ?> : </label>
					<select name="coll_id" id="coll_id">
						<option value=""><?php echo _CHOOSE__COLLECTION;?></option>
						<?php
						for($cptCollection=0;$cptCollection<count($_SESSION['collections']);$cptCollection++)
						{
							?>
							<option value="<?php echo $_SESSION['collections'][$cptCollection]['id'];?>" <?php if($_SESSION['m_admin']['docservers']['coll_id'] == $_SESSION['collections'][$cptCollection]['id']) { echo 'selected="selected"';}?>><?php echo $_SESSION['collections'][$cptCollection]['id']." : ".$_SESSION['collections'][$cptCollection]['label'];?></option>
							<?php
						}
						?>
					</select>
				</p>
				<p class="buttons">
					<?php
					if($mode == "up")
					{
						?>
						<input class="button" type="submit" name="docserver_submit" value="<?php echo _MODIFY; ?>" />
						<?php
					}
					elseif($mode == "add")
					{
						?>
						<input type="submit" class="button"  name="docserver_submit" value="<?php echo _ADD; ?>" />
						<?php
					}
					?>
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=docservers_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<script language="javascript">
				//on load
				$('size_limit').value = $('size_limit').value / (1000 * 1000)
				$('actual_size').value = $('actual_size').value / (1000 * 1000)
			</script>
			<?php
		}
		?>
	</div>
	<?php
}
?>