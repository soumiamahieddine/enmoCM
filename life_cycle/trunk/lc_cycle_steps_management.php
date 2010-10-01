<?php
/* View */
if($mode == "list"){
	list_show::admin_list(
					$lc_cycle_steps_list['tab'], 
					count($lc_cycle_steps_list['tab']), 
					$lc_cycle_steps_list['title'], 
					'cycle_step_id',
					'lc_cycle_steps_management_controler&mode=list',
					'life_cycle','cycle_step_id', 
					true, 
					$lc_cycle_steps_list['page_name_up'], 
					$lc_cycle_steps_list['page_name_val'], 
					$lc_cycle_steps_list['page_name_ban'], 
					$lc_cycle_steps_list['page_name_del'], 
					$lc_cycle_steps_list['page_name_add'], 
					$lc_cycle_steps_list['label_add'], 
					false, 
					false, 
					_ALL_LC_CYCLE_STEPS, 
					_LC_CYCLE_STEP, 
					$_SESSION['config']['businessappurl'].'static.php?filename=manage_lc_cycle_steps.gif&module=life_cycle', 
					true, 
					true, 
					false, 
					true, 
					$lc_cycle_steps_list['what'], 
					true, 
					$lc_cycle_steps_list['autoCompletionArray']
				);
}
elseif($mode == "up" || $mode == "add"){
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_docserver_b.gif" alt="" />
		<?php
		if($mode == "add"){
			echo _LC_CYCLE_STEP_ADDITION;
		}
		elseif($mode == "up"){
			echo _LC_CYCLE_STEP_MODIFICATION;
		}
		?>
	</h1>
	<div id="inner_content" class="clearfix" align="center">
		<br><br>
		<?php
		if($state == false){
			echo "<br /><br />"._THE_LC_CYCLE_STEP." "._UNKOWN."<br /><br /><br /><br />";
		}
		else{
			?>
			<form name="formdocserver" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=lc_cycle_steps_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="lc_cycle_steps_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<?php if($mode == "up") {
					?>
					<p>
					 	<label for="policy_id"><?php echo _POLICY_ID; ?> : </label>
						<input name="policy_id" type="text"  id="policy_id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['policy_id']); ?>" readonly='readonly' class='readonly'/>
					</p>
					<?
				} else {
					?>
					<p>
					 	<label for="policy_id"><?php echo _POLICY_ID; ?> : </label>
						<select name="policy_id" id="policy_id">
							<option value=""><?php echo _POLICY_ID;?></option>
							<?php
							for($cptPolicies=0;$cptPolicies<count($policiesArray);$cptPolicies++){
								?>
								<option value="<?php echo $policiesArray[$cptPolicies];?>" <?php if($_SESSION['m_admin']['lc_cycle_steps']['policy_id'] == $policiesArray[$cptPolicies]) { echo 'selected="selected"';}?>><?php echo $policiesArray[$cptPolicies];?></option>
								<?php
							}
							?>
						</select>
					</p>
					<?
				}
				?>
				<p>
				 	<label for="id"><?php echo _CYCLE_STEP_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['cycle_step_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="cycle_step_desc"><?php echo _CYCLE_STEP_DESC; ?> : </label>
					<input name="cycle_step_desc" type="text"  id="cycle_step_desc" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['cycle_step_desc']); ?>" />
				</p>
				<p>
				 	<label for="cycle_id"><?php echo _CYCLE_ID; ?> : </label>
					<input name="cycle_id" type="text"  id="cycle_id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['cycle_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="docserver_type_id"><?php echo _DOCSERVER_TYPE_ID; ?> : </label>
					<input name="docserver_type_id" type="text"  id="docserver_type_id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['docserver_type_id']); ?>" />
				</p>
				<p>
	                <label><?php echo _IS_ALLOW_FAILURE; ?> : </label>
	                <input type="radio" class="check" name="is_allow_failure" value="true" <?php if($_SESSION['m_admin']['docservers']['is_allow_failure']){?> checked="checked"<?php } ?> /><?php echo _YES;?>
	                <input type="radio" class="check" name="is_allow_failure" value="false" <?php if(!$_SESSION['m_admin']['docservers']['is_allow_failure'] || $_SESSION['m_admin']['docservers']['is_allow_failure'] == ''){?> checked="checked"<?php } ?> /><?php echo _NO;?>
	            </p>
				<p>
					<label for="step_operation"><?php echo _STEP_OPERATION; ?> : </label>
					<select name="step_operation" id="step_operation">
						<?php
						for($cptStepOperation=0;$cptStepOperation<count($_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE']);$cptStepOperation++){
							?>
							<option value="<?php echo $_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE'][$cptStepOperation];?>" <?php if($_SESSION['m_admin']['docserver_types']['step_operation'] == $_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE'][$cptStepOperation]) { echo 'selected="selected"';}?>><?php echo $_SESSION['lifeCycleFeatures']['LIFE_CYCLE']['PROCESS']['MODE'][$cptStepOperation];?></option>
						<?php
						}
						?>
					</select>
				</p>
	            <p>
				 	<label for="sequence_number"><?php echo _SEQUENCE_NUMBER; ?> : </label>
					<input name="sequence_number" type="text"  id="sequence_number" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['sequence_number']); ?>" />
				</p>
	            <p>
	                <label><?php echo _IS_MUST_COMPLETE; ?> : </label>
	                <input type="radio" class="check" name="is_must_complete" value="true" <?php if($_SESSION['m_admin']['docservers']['is_must_complete']){?> checked="checked"<?php } ?> /><?php echo _YES;?>
	                <input type="radio" class="check" name="is_must_complete" value="false" <?php if(!$_SESSION['m_admin']['docservers']['is_must_complete'] || $_SESSION['m_admin']['docservers']['is_must_complete'] == ''){?> checked="checked"<?php } ?> /><?php echo _NO;?>
	            </p>
				<p>
				 	<label for="preprocess_script"><?php echo _PREPROCESS_SCRIPT; ?> : </label>
					<input name="preprocess_script" type="text"  id="preprocess_script" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['preprocess_script']); ?>" />
				</p>
				<p>
				 	<label for="postprocess_script"><?php echo _POSTPROCESS_SCRIPT; ?> : </label>
					<input name="postprocess_script" type="text"  id="postprocess_script" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycle_steps']['postprocess_script']); ?>" />
				</p>
				
				
				
				<p class="buttons">
					<?php
					if($mode == "up"){
						?>
						<input class="button" type="submit" name="submit" value="<?php echo _MODIFY; ?>" />
						<?php
					}
					elseif($mode == "add"){
						?>
						<input type="submit" class="button"  name="submit" value="<?php echo _ADD; ?>" />
						<?php
					}
					?>
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=lc_cycle_steps_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}
?>
