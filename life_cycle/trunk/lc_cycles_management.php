<?php
/* View */
if($mode == "list"){
	list_show::admin_list(
					$lc_cycles_list['tab'], 
					count($lc_cycles_list['tab']), 
					$lc_cycles_list['title'], 
					'cycle_id',
					'lc_cycles_management_controler&mode=list',
					'life_cycle','cycle_id', 
					true, 
					$lc_cycles_list['page_name_up'], 
					$lc_cycles_list['page_name_val'], 
					$lc_cycles_list['page_name_ban'], 
					$lc_cycles_list['page_name_del'], 
					$lc_cycles_list['page_name_add'], 
					$lc_cycles_list['label_add'], 
					false, 
					false, 
					_ALL_LC_CYCLES, 
					_LC_CYCLE, 
					$_SESSION['config']['businessappurl'].'static.php?filename=manage_lc_b.gif&module=life_cycle', 
					true, 
					true, 
					false, 
					true, 
					$lc_cycles_list['what'], 
					true, 
					$lc_cycles_list['autoCompletionArray']
				);
}
elseif($mode == "up" || $mode == "add"){
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_lc_b.gif&module=life_cycle" alt="" />
		<?php
		if($mode == "add"){
			echo _LC_CYCLE_ADDITION;
		}
		elseif($mode == "up"){
			echo _LC_CYCLE_MODIFICATION;
		}
		?>
	</h1>
	<div id="inner_content" class="clearfix" align="center">
		<br><br>
		<?php
		if($state == false){
			echo "<br /><br />"._THE_LC_CYCLE." "._UNKOWN."<br /><br /><br /><br />";
		} else {
			?>
			<form name="formdocserver" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=lc_cycles_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="lc_cycles_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<?php if($mode == "up") {
					?>
					<p>
					 	<label for="policy_id"><?php echo _POLICY_ID; ?> : </label>
						<input name="policy_id" type="text"  id="policy_id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['policy_id']); ?>" readonly='readonly' class='readonly'/>
					</p>
					<?php
				} else {
					?>
					<p>
					 	<label for="policy_id"><?php echo _POLICY_ID; ?> : </label>
						<select name="policy_id" id="policy_id">
							<option value=""><?php echo _POLICY_ID;?></option>
							<?php
							for($cptPolicies=0;$cptPolicies<count($policiesArray);$cptPolicies++) {
								?>
								<option value="<?php echo $policiesArray[$cptPolicies];?>" <?php if($_SESSION['m_admin']['lc_cycles']['policy_id'] == $policiesArray[$cptPolicies]) { echo 'selected="selected"';}?>><?php echo $policiesArray[$cptPolicies];?></option>
								<?php
							}
							?>
						</select>
					</p>
					<?
				}
				?>
				<p>
				 	<label for="id"><?php echo _CYCLE_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['cycle_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="cycle_desc"><?php echo _CYCLE_DESC; ?> : </label>
					<textarea name="cycle_desc" type="text"  id="cycle_desc" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['cycle_desc']); ?>" /><?php echo $_SESSION['m_admin']['lc_cycles']['cycle_desc'] ?></textarea>
				</p>
				<p>
				 	<label for="sequence_number"><?php echo _SEQUENCE_NUMBER; ?> : </label>
					<input name="sequence_number" type="text"  id="sequence_number" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['sequence_number']); ?>" />
				</p>
				<p>
				 	<label for="where_clause"><?php echo _WHERE_CLAUSE; ?> : </label>
					<input name="where_clause" type="text"  id="where_clause" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['where_clause']); ?>" />
				</p>
				<p>
				 	<label for="validation_mode"><?php echo _VALIDATION_MODE; ?> : </label>
					<input name="validation_mode" type="text"  id="validation_mode" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['validation_mode']); ?>" />
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
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=lc_cycles_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}
?>
