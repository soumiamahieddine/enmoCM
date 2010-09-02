<?php
/* View */
if($mode == "list"){
	list_show::admin_list(
					$docserver_locations_list['tab'], 
					count($docserver_locations_list['tab']), 
					$docserver_locations_list['title'], 
					'docserver_locations_id',
					'docserver_locations_management_controler&mode=list',
					'life_cycle','docserver_locations_id', 
					true, 
					$docserver_locations_list['page_name_up'], 
					$docserver_locations_list['page_name_val'], 
					$docserver_locations_list['page_name_ban'], 
					$docserver_locations_list['page_name_del'], 
					$docserver_locations_list['page_name_add'], 
					$docserver_locations_list['label_add'], 
					false, 
					false, 
					_ALL_DOCSERVER_LOCATIONS, 
					_DOCSERVER_LOCATION, 
					$_SESSION['config']['businessappurl'].'static.php?filename=manage_docserver_locations.gif&module=life_cycle', 
					true, 
					true, 
					false, 
					true, 
					$docserver_locations_list['what'], 
					true, 
					$docserver_locations_list['autoCompletionArray']
				);
}
elseif($mode == "up" || $mode == "add"){
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_docserver_b.gif" alt="" />
		<?php
		if($mode == "add"){
			echo _DOCSERVER_LOCATION_ADDITION;
		}
		elseif($mode == "up"){
			echo _DOCSERVER_LOCATION_MODIFICATION;
		}
		?>
	</h1>
	<div id="inner_content" class="clearfix" align="center">
		<br><br>
		<?php
		if($state == false){
			echo "<br /><br />"._THE_DOCSERVER_LOCATION." "._UNKOWN."<br /><br /><br /><br />";
		}
		else{
			?>
			<form name="formdocserver" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=docserver_locations_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="docserver_locations_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<p>
				 	<label for="id"><?php echo _DOCSERVER_LOCATION_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['docserver_locations']['docserver_locations_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="ipv4"><?php echo _IPV4; ?> : </label>
					<input name="ipv4" type="text"  id="ipv4" value="<?php echo functions::show($_SESSION['m_admin']['docserver_locations']['ipv4']); ?>"/>
				</p>
	           	<p>
				 	<label for="ipv6"><?php echo _IPV6; ?> : </label>
					<input name="ipv6" type="text"  id="ipv6" value="<?php echo functions::show($_SESSION['m_admin']['docserver_locations']['ipv6']); ?>"/>
				</p>
				<p>
				 	<label for="net_domain"><?php echo _NET_DOMAIN; ?> : </label>
					<input name="net_domain" type="text"  id="net_domain" value="<?php echo functions::show($_SESSION['m_admin']['docserver_locations']['net_domain']); ?>"/>
				</p>
				<p>
				 	<label for="mask"><?php echo _MASK; ?> : </label>
					<input name="mask" type="text"  id="mask" value="<?php echo functions::show($_SESSION['m_admin']['docserver_locations']['mask']); ?>"/>
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
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=docserver_locations_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}
?>
