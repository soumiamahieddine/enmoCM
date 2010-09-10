<?php

/* Affichage */
if($mode == "list")
{
	list_show::admin_list(
					$groups_list['tab'], 
					count($groups_list['tab']), 
					$groups_list['title'], 
					'group_id',
					'usergroups_management_controler&mode=list',
					'groups','group_id', 
					true, 
					$groups_list['page_name_up'], 
					$groups_list['page_name_val'], 
					$groups_list['page_name_ban'], 
					$groups_list['page_name_del'], 
					$groups_list['page_name_add'], 
					$groups_list['label_add'], 
					false, 
					false, 
					_ALL_GROUPS, 
					_GROUP, 
					$_SESSION['config']['businessappurl'].'static.php?filename=manage_scheme.gif&module=moreq', 
					false, 
					true, 
					false, 
					true, 
					$groups_list['what'], 
					true, 
					$groups_list['autoCompletionArray']
				);
}
elseif($mode == "up" || $mode == "add")
{
	?><script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=usergroups_management.js"></script>
	<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_groupe_b.gif" alt="" />
	<?php
	if($mode == "add")
	{
		echo _GROUP_ADDITION;
	}
	elseif($mode == "up")
	{
		echo _GROUP_MODIFICATION;
	}
	?>
	</h1>

	<?php
	if($state == false)
	{
		echo "<br /><br /><br /><br />"._GROUP.' '._UNKNOWN."<br /><br /><br /><br />";
	}
	else
	{
	?>
	<div id="inner_content" class="clearfix">
		<div id="group_box" class="bloc" >
			<?php
			if($mode == "up")
			{
			?><div onclick="new Effect.toggle('users_list', 'blind', {delay:0.2});return false;" >
				&nbsp;<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=membres_groupe_b.gif" alt="" /><i><?php  echo _SEE_GROUP_MEMBERS;?></i> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="" />
				<span class="lb1-details">&nbsp;</span></div>
				<div class="desc" id="users_list" style="display:none;">
					<div class="ref-unit">
						<table cellpadding="0" cellspacing="0" border="0" class="listingsmall">
							<thead>
								<tr>
									<th><?php  echo _LASTNAME;?></th>
									<th ><?php  echo _FIRSTNAME;?></th>
									<!--<th  ><?php  echo _ENTITY;?></th>-->
									<th></th>
								</tr>
							</thead>

						<tbody>
							 <?php
						$color = ' class="col"';
						 for($i=0;$i<count($users);$i++)
							{
								if($color == ' class="col"')
								{
									$color = '';
								}
								else
								{
									$color = ' class="col"';
								}
									?>
							 <tr <?php  echo $color; ?> >
									   <td width="25%"><?php  echo $users[$i]->__get('lastname');?></td>
										  <td width="25%"><?php  echo $users[$i]->__get('firstname');?></td>
									 <!--  <td><?php  //echo $users[$i]['DEPARTMENT']; ?></td>-->
									   <td ><?php 
										if(core_tools::test_service('admin_users', 'apps', false))
										{?>
									   <a class="change" href="<?php echo $_SESSION['config']['businessappurl'].'index.php?page=users_up&admin=users&id='.$users[$i]->__get('user_id'); ?>" alt="<?php echo _GO_MANAGE_USER;?>" title="<?php echo _GO_MANAGE_USER;?>"><i><?php echo _GO_MANAGE_USER;?></i></a><?php }?></td>
							</tr>
									<?php
							}
						?>
						</tbody>
						</table>
						<br/>
					</div>
				</div>
				
			<?php
				if($basket_loaded)
				{?>
					<div onclick="new Effect.toggle('baskets_list2', 'blind', {delay:0.2});return false;" >
				&nbsp;<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=membres_groupe_b.gif" alt="" /><i><?php  echo _SEE_BASKETS_RELATED;?></i> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="" />
				<span class="lb1-details">&nbsp;</span></div>
				<div class="desc" id="baskets_list2" style="display:none;">
					<div class="ref-unit">
						<table cellpadding="0" cellspacing="0" border="0" class="listingsmall">
							<thead>
								<tr>
									<th><?php  echo NAME;?></th>
									<th ><?php  echo DESC;?></th>
									<th></th>
								</tr>
							</thead>

						<tbody>
							 <?php
						$color = ' class="col"';
						 for($i=0;$i<count($baskets);$i++)
							{
								if($color == ' class="col"')
								{
									$color = '';
								}
								else
								{
									$color = ' class="col"';
								}
									?>
							 <tr <?php  echo $color; ?> >
									   <td width="30%"><?php  echo $baskets[$i]->__get('basket_name');?></td>
									  <td width="50%"><?php  echo $baskets[$i]->__get('basket_desc');?></td>
									   <td >
									   <?php if(core_tools::test_service('admin_baskets', 'basket', false))
										{?>
										<a class="change" href="<?php echo $_SESSION['config']['businessappurl'].'index.php?page=basket_up&module=basket&id='.$baskets[$i]->__get('basket_id'); ?>" alt="<?php echo _GO_MANAGE_BASKET;?>" title="<?php echo _GO_MANAGE_BASKET;?>"><i><?php echo _GO_MANAGE_BASKET;?></i></a>
									   <?php } ?> 
										</td>
							</tr>
									<?php
							}
						?>
						</tbody>
						</table>
						<br/>
						<br/>
					</div>
				</div>
			<?php	}
			}
			?><div id="access"></div>
		</div>
		<form name="formgroup" method="post"  class="forms" action="<?php echo  $_SESSION['config']['businessappurl']."index.php?display=true&admin=groups&page=usergroups_management_controler&mode=".$mode ?>" >
		<input type="hidden" name="display" value="value" />
		<input type="hidden" name="admin" value="groups" />
		<input type="hidden" name="page" value="usergroups_management_controler" />
		<input type="hidden" name="mode" value="<?php echo $mode;?>" />
		
		<input type="hidden" name="order" id="order" value="<?php echo $_REQUEST['order'];?>" />
		<input type="hidden" name="order_field" id="order_field" value="<?php echo $_REQUEST['order_field'];?>" />
		<input type="hidden" name="what" id="what" value="<?php echo $_REQUEST['what'];?>" />
		<input type="hidden" name="start" id="start" value="<?php echo $_REQUEST['start'];?>" />
		
			<table border="0" align="center" width="540px">
				<tr>
					<td width = "200px" align="right">
						<?php  echo _GROUP; ?> :
					</td>
					<td align="left">
						<?php  if($mode == "up") { echo functions::show($_SESSION['m_admin']['groups']['group_id']); } ?>
						<input name="group_id" type="<?php  if($mode == "up") { ?>hidden<?php  } elseif($mode == "add") { ?>text<?php  } ?>" id="group_id" value="<?php  echo $_SESSION['m_admin']['groups']['group_id']; ?>" />
						<input type="hidden"  name="id" value="<?php  echo $group_id; ?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
						<?php  echo _DESC; ?> :
					</td>
					<td align="left">
						<input name="desc" id="desc" class="text" type="text" value="<?php  echo $_SESSION['m_admin']['groups']['group_desc']; ?>"  alt="<?php  echo $_SESSION['m_admin']['groups']['desc']; ?>" title="<?php  echo $_SESSION['m_admin']['groups']['group_desc']; ?>"/>
					</td>
				</tr>
			</table>
			<br><center><i><?php  echo _AVAILABLE_SERVICES;?> :</i></center>
			<?php
			//functions::show_array($_SESSION['enabled_services']);
			$enabled_services_sort_by_parent = array();
			$j=0;
			for($i=0; $i<count($_SESSION['enabled_services']);$i++)
			{
				if( $_SESSION['enabled_services'][$i]['system'] == false)
				{
					if($_SESSION['enabled_services'][$i]['parent'] <> $_SESSION['enabled_services'][$i - 1]['parent'])
					{
						$j=0;
					}
					$enabled_services_sort_by_parent[$_SESSION['enabled_services'][$i]['parent']][$j] = $_SESSION['enabled_services'][$i];
					$j++;
				}
			}
			//functions::show_array($enabled_services_sort_by_parent);

			$_SESSION['cpt']=0;
			foreach(array_keys($enabled_services_sort_by_parent) as $value)
			{
				if($value == 'application')
				{
					$label = _APPS_COMMENT;
				}
				elseif($value == 'core')
				{
					$label = _CORE_COMMENT;
				}
				else
				{
					$label = $_SESSION['modules_loaded'][$value]['comment'];
				}
				//$this->show_array($enabled_services_sort_by_parent[$value]);
				//$this->show_array($_SESSION['m_admin']['groups']['services']);
				//echo $_SESSION['cpt']."<br>";

				if(count($enabled_services_sort_by_parent[$value]) > 0)
				{
				?>

					<h5 onclick="change(<?php  echo $_SESSION['cpt'];?>)" id="h2<?php  echo $_SESSION['cpt'];?>" class="categorie">
						<img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=plus.png" alt="" />&nbsp;<b><?php  echo $label ;?></b>
						<span class="lb1-details">&nbsp;</span>
					</h5>
					<br/>
					<div class="desc block_light admin" id="desc<?php  echo $_SESSION['cpt'];?>" style="display:none">
						<div class="ref-unit">
							<table>
							<?php
							for($i=0; $i<count($enabled_services_sort_by_parent[$value]); $i++)
							{
								if($enabled_services_sort_by_parent[$value][$i]['system'] <> true)
								{
								?>
								<tr>
									<td width="800px" align="right" title="<?php  echo $enabled_services_sort_by_parent[$value][$i]['comment'];?>">
										<?php  echo $enabled_services_sort_by_parent[$value][$i]['label'];?> <?php  if(  $enabled_services_sort_by_parent[$value][$i]['type'] == "admin") {?>(<?php echo _ADMIN;?>) <?php  }elseif($enabled_services_sort_by_parent[$value][$i]['type'] == "menu"){?>(<?php echo _MENU;?>)<?php } ?>  :
									</td>
									<td width="50px" align="left">
										<input type="checkbox"  class="check" name="services[]" value="<?php  echo $enabled_services_sort_by_parent[$value][$i]['id'];?>" <?php  if(in_array(trim($enabled_services_sort_by_parent[$value][$i]['id']),$_SESSION['m_admin']['groups']['services']) || $mode == "add"){ echo 'checked="checked"';}?>  />
									</td>
								</tr>
								<?php
								}
							}
							?>
						</table>
					</div>
				</div>
			<?php }

				$_SESSION['cpt']++;
			}
			?>
			<p class="buttons">
				<input type="submit" name="group_submit" id="group_submit" value="<?php  echo _VALIDATE; ?>" class="button"/>
				 <input type="button" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=usergroups_management_controler&amp;mode=list&amp;admin=groups';"/>
			</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		</form>
	</div>
	<script type="text/javascript">updateContent('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=groups_form&admin=groups', 'access');</script>
	<?php 
	}
}

