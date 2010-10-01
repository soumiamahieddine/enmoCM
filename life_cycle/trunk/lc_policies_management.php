<?php
/* View */
if($mode == "list"){
	list_show::admin_list(
					$lc_policies_list['tab'], 
					count($lc_policies_list['tab']), 
					$lc_policies_list['title'], 
					'policy_id',
					'lc_policies_management_controler&mode=list',
					'life_cycle','policy_id', 
					true, 
					$lc_policies_list['page_name_up'], 
					$lc_policies_list['page_name_val'], 
					$lc_policies_list['page_name_ban'], 
					$lc_policies_list['page_name_del'], 
					$lc_policies_list['page_name_add'], 
					$lc_policies_list['label_add'], 
					false, 
					false, 
					_ALL_LC_POLICIES, 
					_LC_POLICY, 
					$_SESSION['config']['businessappurl'].'static.php?filename=manage_lc_policies.gif&module=life_cycle', 
					true, 
					true, 
					false, 
					true, 
					$lc_policies_list['what'], 
					true, 
					$lc_policies_list['autoCompletionArray']
				);
} elseif($mode == "up" || $mode == "add") {
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_docserver_b.gif" alt="" />
		<?php
		if($mode == "add") {
			echo _LC_POLICY_ADDITION;
		}
		elseif($mode == "up") {
			echo _LC_POLICY_MODIFICATION;
		}
		?>
	</h1>
	<div id="inner_content" class="clearfix" align="center">
		<br/><br/>
		<?php
		if($state == false) {
			echo "<br /><br />"._THE_LC_POLICY." "._UNKOWN."<br /><br /><br /><br />";
		} else {
			?>
			<form id="adminform" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=lc_policies_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="lc_policies_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<p>
				 	<label for="id"><?php echo _LC_POLICY_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['lc_policies']['policy_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="policy_name"><?php echo _LC_POLICY_NAME; ?> : </label>
					<input name="policy_name" type="text"  id="policy_name" value="<?php echo functions::show($_SESSION['m_admin']['lc_policies']['policy_name']); ?>" />
				</p>
				<p>
				 	<label for="policy_desc"><?php echo _POLICY_DESC; ?> : </label>
					<textarea name="policy_desc" type="text"  id="policy_desc" value="<?php echo functions::show($_SESSION['m_admin']['lc_policies']['policy_desc']); ?>" /><?php echo $_SESSION['m_admin']['lc_policies']['policy_desc'] ?></textarea>
				</p>
				<p class="buttons">
					<?php
					if($mode == "up") {
						?>
						<input class="button" type="submit" name="submit" value="<?php echo _MODIFY; ?>" />
						<?php
					} elseif($mode == "add") {
						?>
						<input type="submit" class="button"  name="submit" value="<?php echo _ADD; ?>" />
						<?php
					}
					?>
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=lc_policies_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}
?>
