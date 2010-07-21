<?php
//functions::show_array($_REQUEST);
if($mode == "list")
{
	list_show::admin_list($tab, $i, $title, 'docerver_location_id','docservers_locations_management_controler&mode=list','life_cycle', 'docserver_location_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_DOSERVERS_LOCATIONS, _DOCSERVER_LOCATION, $_SESSION['config']['businessappurl'].'static.php?module=life_cycle&filename=manage_lc_b.gif', true, true, false, true, "", true, $autoCompletionArray);
}
elseif($mode == "up" || $mode == "add")
{
	?>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_docserver_location_b.gif" alt="" />
		<?php
		if($mode == "add")
		{
			echo _DOCSERVER_LOCATION_ADDITION;
		}
		elseif($mode == "up")
		{
			echo _DOCSERVER_LOCATION_MODIFICATION;
		}
		?>
	</h1>
	<div id="inner_content" class="clearfix" align="center">
		<br><br>
		<?php
		if($state == false)
		{
			echo "<br /><br />"._THE_DOCSERVER_LOCATION." "._UNKOWN."<br /><br /><br /><br />";
		}
		else
		{
			//functions::show_array($docserverLocation);
			?>
			<form name="formdocserverlocation" method="post" class="forms" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&page=docservers_locations_management_controler&module=life_cycle&mode=".$mode;?>">
				<input type="hidden" name="display" value="value" />
				<input type="hidden" name="module" value="life_cycle" />
				<input type="hidden" name="page" value="docservers_locations_management_controler" />
				<input type="hidden" name="mode" id="mode" value="<?php echo $mode;?>" />
				<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
				<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
				<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
				<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
				<p>
				 	<label for="id"><?php echo _DOCSERVER_LOCATION_ID; ?> : </label>
					<input name="id" type="text"  id="id" value="<?php echo functions::show($_SESSION['m_admin']['docservers_locations']['docserver_location_id']); ?>" <?php if($mode == "up") echo " readonly='readonly' class='readonly'";?>/>
				</p>
				<p>
				 	<label for="ipv4_filter"><?php echo _IPV4_FILTER; ?> : </label>
					<input name="ipv4_filter" type="text"  id="ipv4_filter" value="<?php echo functions::show($_SESSION['m_admin']['docservers_locations']['ipv4_filter']); ?>"/>
				</p>
				<p>
				 	<label for="ipv6_filter"><?php echo _IPV6_FILTER; ?> : </label>
					<input name="ipv6_filter" type="text"  id="ipv6_filter" value="<?php echo functions::show($_SESSION['m_admin']['docservers_locations']['ipv6_filter']); ?>"/>
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
	               <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=docservers_locations_management_controler&amp;module=life_cycle&amp;mode=list';"/>
				</p>
			</form>
			<?php
		}
		?>
	</div>
	<?php
}
?>