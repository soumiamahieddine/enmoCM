<?php
/* View */
if($mode == "list"){
	list_show::admin_list(
					$lc_cycles_list['tab'], 
					count($lc_cycles_list['tab']), 
					$lc_cycles_list['title'], 
					'lc_cycles_id',
					'lc_cycles_management_controler&mode=list',
					'life_cycle','lc_cycles_id', 
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
					$_SESSION['config']['businessappurl'].'static.php?filename=manage_lc_cycles.gif&module=life_cycle', 
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
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_docserver_b.gif" alt="" />
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
		}
		else{
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
				<p>
				 	<label for="id"><?php echo _LC_CYCLE_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['lc_cycles_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="lc_policies_id"><?php echo _LC_POLICIES_ID; ?> : </label>
					<input name="lc_policies_id" type="text"  id="lc_policies_id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['lc_cycles_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="cycle"><?php echo _CYCLE_DESC; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['lc_cycles_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="id"><?php echo _SEQUENCE_NUMBER; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['lc_cycles']['lc_cycles_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
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
