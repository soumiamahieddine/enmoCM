<?php
/*
*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief   Displays the invoices list in the following baskets (default setting): NewInvoicesSup10000, RejectedInvoices, ValidatedInvoices
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules".DIRECTORY_SEPARATOR."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once("modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_entities.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$security = new security();
$core_tools = new core_tools();
$request = new request();
$db = new dbquery();
$db2 = new dbquery();
$db->connect();
$db2->connect();
$ent = new entity();
$bask = new basket();
$_SESSION['auth_dep'] = array();
if(!empty($_REQUEST['clear']))
{
	$_SESSION['auth_dep'] = array();
}
if(!empty($_REQUEST['id']))
{
	$bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}
?>
<?php
if(!empty($_SESSION['current_basket']['view']))
{
	$table = $_SESSION['current_basket']['view'];
}
else
{
	$table = $_SESSION['current_basket']['table'];
}
if(!empty($_REQUEST['entity_id']))
{
	$_SESSION['auth_dep']['bask_chosen_entity'] = $_REQUEST['entity_id'];
}
if(!empty($_REQUEST['isViewed']))
{
	$_SESSION['auth_dep']['bask_chosen_viewed'] = $_REQUEST['isViewed'];
}
if(!empty($_REQUEST['bask_chosen_category']))
{
	$_SESSION['auth_dep']['bask_chosen_category'] = $_REQUEST['category_id'];
}
if(!empty($_REQUEST['status_id']))
{
	$_SESSION['auth_dep']['bask_chosen_status'] = $_REQUEST['status_id'];
}
if(!empty($_REQUEST['contact_id']))
{
	$_SESSION['auth_dep']['bask_chosen_contact'] = $_REQUEST['contact_id'];
}
$entity = '';
$str = '';
$entities = array();
$where_tmp = '';
$where = $_SESSION['current_basket']['clause'];
$where = str_replace("and status <> 'VAL'", " ", $where);
if(!empty($where))
{
	$where_tmp = ' where '.$where;
}
if($_SESSION['current_basket']['id'] == "DepartmentBasket")
{
	$db->query("select distinct(".$table.".destination) as entity_id, count(distinct ".$table.".res_id) as total, e.entity_label from ".$table." join ".$_SESSION['tablename']['ent_entities']." e on e.entity_id = ".$table.".destination ".$where_tmp." group by e.entity_label, ".$table.".destination order by e.entity_label");
}
else
{
	$db->query("select distinct(".$table.".destination) as entity_id, count(distinct ".$table.".res_id) as total, e.entity_label from ".$table." join ".$_SESSION['tablename']['ent_entities']." e on e.entity_id = ".$table.".destination ".$where_tmp." group by e.entity_label, ".$table.".destination order by e.entity_label");
}
while($res = $db->fetch_object())
{
	$db2->query("select entity_label, short_label from ".$_SESSION['tablename']['ent_entities']." e where e.entity_id = '".$res->entity_id."'");
	$res2 = $db2->fetch_object();
	array_push($entities, array('ID' => $res->entity_id, 'LABEL' => $res2->entity_label, 'SHORT_LABEL' => $res2->short_label, 'IN_ENTITY' => $ent->is_user_in_entity($_SESSION['user']['UserId'], $res->entity_id), 'TOTAL' => $res->total));
}
$db->query("select * from ".$_SESSION['tablename']['status']." order by label_status");
$arr_status = array();
while($res = $db->fetch_object())
{
	array_push($arr_status , array('id' => $res->id, 'label' => $res->label_status, 'is_system' => $res->is_system, 'img_filename' => $res->img_filename,
	'module' => $res->module, 'can_be_searched' => $res->can_be_searched, 'can_be_modified' => $res->can_be_modified));
}
array_push($arr_status , array('id' => 'late', 'label' => _LATE));
?>
<div align="center">
	<script type="text/javascript">
		function change_list_entity(id_entity, path_script)
		{
			//Defines template allowed for this list
			<?php
			if(!$_REQUEST['template'])
			{
				?>
				var templateVal = 'copy_list_extend'; <?php
			}
			?>
			<?php
			if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
			{
				?>
				var templateVal = ''; <?php
			}
			?>
			<?php
			if($_REQUEST['template'])
			{
				?>
				var templateVal = '<?php echo $_REQUEST['template']; ?>'; <?php
			}
			?>
			//###################
			//console.log(id_entity);
			var startVal = '<?php echo $_REQUEST['start'];?>';
			var orderVal = '<?php echo $_REQUEST['order'];?>';
			var order_fieldVal = '<?php echo $_REQUEST['order_field'];?>';
			if(id_entity && path_script)
			{
				new Ajax.Request(path_script,
				{
					method:'post',
					parameters: {entity_id : id_entity, start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},
					onSuccess: function(answer){
						//console.log(answer.responseText);
						var item = $('list_doc');
						if(item != null)
						{
							item.update(answer.responseText);
						}
					},
					onFailure: function(){ }
				});
			}
		}

		function change_list_category(id_category, path_script)
		{
			//Defines template allowed for this list
			<?php
			if(!$_REQUEST['template'])
			{
				?>
				var templateVal = 'copy_list_extend'; <?php
			}
			?>
			<?php if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
			{
				?>
				var templateVal = ''; <?php
			}
			?>
			<?php
			if($_REQUEST['template'])
			{
				?>
				var templateVal = '<?php echo $_REQUEST['template']; ?>'; <?php
			}
			?>
			//###################
			//console.log(id_category);
			var startVal = '<?php echo $_REQUEST['start'];?>';
			var orderVal = '<?php echo $_REQUEST['order'];?>';
			var order_fieldVal = '<?php echo $_REQUEST['order_field'];?>';
			if(id_category && path_script)
			{
				new Ajax.Request(path_script,
				{
					method:'post',
					parameters: {category_id : id_category, start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},
					onSuccess: function(answer){
						//console.log(answer.responseText);
						var item = $('list_doc');
						if(item != null)
						{
							item.update(answer.responseText);
						}
					},
					onFailure: function(){ }
				});
			}
		}
		
		function change_list_viewed(viewed, path_script)
		{
			//Defines template allowed for this list
			<?php
			if(!$_REQUEST['template'])
			{
				?>
				var templateVal = 'copy_list_extend'; <?php
			}
			?>
			<?php
			if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
			{
				?>
				var templateVal = ''; <?php
			}
			?>
			<?php
			if($_REQUEST['template'])
			{
				?>
				var templateVal = '<?php echo $_REQUEST['template']; ?>'; <?php
			}
			?>
			//###################
			//console.log(viewed);
			var startVal = '<?php echo $_REQUEST['start'];?>';
			var orderVal = '<?php echo $_REQUEST['order'];?>';
			var order_fieldVal = '<?php echo $_REQUEST['order_field'];?>';
			if(viewed && path_script)
			{
				new Ajax.Request(path_script,
				{
					method:'post',
					parameters: {isViewed : viewed, start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},
					onSuccess: function(answer){
						//console.log(answer.responseText);
						var item = $('list_doc');
						if(item != null)
						{
							item.update(answer.responseText);
						}
					},
					onFailure: function(){ }
				});
			}
		}
		
		function change_list_status(id_status, path_script)
		{
			//Defines template allowed for this list
			<?php
			if(!$_REQUEST['template'])
			{
				?>
				var templateVal = 'copy_list_extend'; <?php
			}
			?>
			<?php if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
			{
				?>
				var templateVal = ''; <?php
			}
			?>
			<?php
			if($_REQUEST['template'])
			{
				?>
				var templateVal = '<?php echo $_REQUEST['template']; ?>'; <?php
			}
			?>
			//###################
			//console.log(id_status);
			var startVal = '<?php echo $_REQUEST['start'];?>';
			var orderVal = '<?php echo $_REQUEST['order'];?>';
			var order_fieldVal = '<?php echo $_REQUEST['order_field'];?>';
			if(id_status && path_script)
			{
				new Ajax.Request(path_script,
				{
					method:'post',
					parameters: {status_id : id_status, start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},
					onSuccess: function(answer){
						//console.log(answer.responseText);
						var item = $('list_doc');
						if(item != null)
						{
							item.update(answer.responseText);
						}
					},
					onFailure: function(){ }
				});
			}
		}

		function change_contact(id_contact, path_script)
		{
			//Defines template allowed for this list
			<?php
			if(!$_REQUEST['template'])
			{
				?>
				var templateVal = 'copy_list_extend'; <?php
			}
			?>
			<?php
			if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
			{
				?>
				var templateVal = ''; <?php
			}
			?>
			<?php
			if($_REQUEST['template'])
			{
				?>
				var templateVal = '<?php echo $_REQUEST['template']; ?>'; <?php
			}
			?>
			//###################
			//console.log(id_contact);
			var startVal = '<?php echo $_REQUEST['start'];?>';
			var orderVal = '<?php echo $_REQUEST['order'];?>';
			var order_fieldVal = '<?php echo $_REQUEST['order_field'];?>';
			if(id_contact && path_script)
			{
				new Ajax.Request(path_script,
				{
					method:'post',
					parameters: {contact_id : id_contact, start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},
					onSuccess: function(answer){
						//console.log(answer.responseText);
						var item = $('list_doc');
						if(item != null)
						{
							item.update(answer.responseText);
						}
					},
					onFailure: function(){ }
				});
			}
		}
	</script>
	<?php
		if(empty($_SESSION['auth_dep']['bask_chosen_entity']) && empty($_SESSION['auth_dep']['bask_chosen_category']) && empty($_SESSION['auth_dep']['bask_chosen_status']) && empty($_SESSION['auth_dep']['bask_chosen_contact']))
		{
			?>
			<script type="text/javascript">
				//Defines template allowed for this list
				<?php if(!$_REQUEST['template'])
				{
					?>
					var templateVal = 'copy_list_extend'; <?php
				}
				?>
				<?php if(isset($_REQUEST['template']) && empty($_REQUEST['template']))
				{
					?>
					var templateVal = ''; <?php
				}
				?>
				<?php
				if($_REQUEST['template'])
				{
					?>
					var templateVal = '<?php echo $_REQUEST['template']; ?>'; <?php
				}
				?>
				//###################
				var startVal = '<?php echo $_REQUEST['start'];?>';
				var orderVal = '<?php echo $_REQUEST['order'];?>';
				var order_fieldVal = '<?php echo $_REQUEST['order_field'];?>';
				new Ajax.Request('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list',
				{
					method:'post',
					parameters: {start : startVal, order : orderVal, order_field : order_fieldVal, template : templateVal},
					onSuccess: function(answer){
						//console.log(answer.responseText);
						var item = $('list_doc');
						if(item != null)
						{
							item.update(answer.responseText);
						}
					},
					onFailure: function(){ }
				});
			</script>
			<?php
		}
	?>
	<form name="filter_by_entity" action="#" method="post">
		<?php echo _FILTER_BY;?> :
		<select name="entity" id="entity" onchange="change_list_entity(this.options[this.selectedIndex].value, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');">
			<option value="none"><?php echo _CHOOSE_ENTITY;?></option>
			<?php
			for($i=0;$i<count($entities);$i++)
			{
				?>
				<option value="<?php echo $entities[$i]['ID'];?>"<?php if($_SESSION['auth_dep']['bask_chosen_entity'] == $entities[$i]['ID']){echo ' selected="selected"';}?><?php if($entities[$i]['IN_ENTITY']){echo ' style="font-weight:bold;"';}?>><?php echo $entities[$i]['SHORT_LABEL'].' ('.$entities[$i]['TOTAL'].')';?></option>
				<?php
			}
			?>
		</select>
		<select name="category" id="category" onchange="change_list_category(this.options[this.selectedIndex].value, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');">
			<option value="none"><?php echo _CHOOSE_CATEGORY;?></option>
			<?php
			foreach(array_keys($_SESSION['mail_categories']) as $value)
			{
				?>
				<option value="<?php echo $value;?>"<?php if($_SESSION['auth_dep']['bask_chosen_category'] == $value){echo ' selected="selected"';}?>><?php echo $_SESSION['mail_categories'][$value];?></option>
				<?php
			}
			?>
		</select>
		<?php
		if($_SESSION['current_basket']['id'] == "DepartmentBasket")
		{
			?>
			<select name="status" id="status" onchange="change_list_status(this.options[this.selectedIndex].value, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');">
				<option value="none"><?php echo _CHOOSE_STATUS;?></option>
				<?php
				for($cptStatus=0;$cptStatus<count($arr_status);$cptStatus++)
				{
					?>
					<option value="<?php echo $arr_status[$cptStatus]['id'];?>"<?php if($_SESSION['auth_dep']['bask_chosen_status'] == $arr_status[$cptStatus]['id']){echo ' selected="selected"';}?>><?php echo $arr_status[$cptStatus]['label'];?></option>
					<?php
				}
				?>
			</select>
			<?php
		}
		?>
		<select name="isViewed" id="isViewed" onchange="change_list_viewed(this.options[this.selectedIndex].value, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');">
			<option value="none"><?php echo _VIEWED;?></option>
			<option value="yes" <?php if($_SESSION['auth_dep']['bask_chosen_viewed'] == "yes"){echo ' selected="selected"';}?>><?php echo _YES;?></option>
			<option value="no"  <?php if($_SESSION['auth_dep']['bask_chosen_viewed'] == "no" || $_SESSION['auth_dep']['bask_chosen_viewed'] == ""){echo ' selected="selected"';}?>><?php echo _NO;?></option>
		</select>
		<input type="text" name="contact_id" id="contact_id"
			value="<?php
					if(!empty($_SESSION['auth_dep']['bask_chosen_contact']))
					{
						echo $_SESSION['auth_dep']['bask_chosen_contact'];
					}
					else
					{
						echo '['._CONTACT.']';
					}
					?>"
					<?php
					if(empty($_SESSION['auth_dep']['bask_chosen_contact']))
					{
						?>
						onfocus="if(this.value=='[<?php echo _CONTACT;?>]'){this.value='';}"
						<?php
					}
					?> size="40" onKeyPress="if(event.keyCode == 9)change_contact(this.value, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');" onBlur="change_contact(this.value, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');"  />
		<div id="contactListByName" class="autocomplete"></div>
		<script type="text/javascript">
			initList('contact_id', 'contactListByName', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_list_by_name', 'what', '2');
		</script>
		<input type="button" class="button" value="<?php echo _CLEAR_SEARCH;?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl']."index.php?page=view_baskets&module=basket&baskets=".$_SESSION['current_basket']['id']."&clear=ok";?>';">
	</form>
	<?php
	if(isset($_SESSION['auth_dep']['bask_chosen_entity']) && !empty($_SESSION['auth_dep']['bask_chosen_entity']))
	{
		?>
		<script type="text/javascript">
			change_list_entity('<?php echo $_SESSION['auth_dep']['bask_chosen_entity'];?>', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');
		</script>
		<?php
	}
	if(isset($_SESSION['auth_dep']['bask_chosen_category']) && !empty($_SESSION['auth_dep']['bask_chosen_category']))
	{
		?>
		<script type="text/javascript">
			change_list_category('<?php echo $_SESSION['auth_dep']['bask_chosen_category'];?>', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');
		</script>
		<?php
	}
	if(isset($_SESSION['auth_dep']['bask_chosen_status']) && !empty($_SESSION['auth_dep']['bask_chosen_status']))
	{
		?>
		<script type="text/javascript">
			change_list_status('<?php echo $_SESSION['auth_dep']['bask_chosen_status'];?>', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');
		</script>
		<?php
	}
	if(isset($_SESSION['auth_dep']['bask_chosen_contact']) && !empty($_SESSION['auth_dep']['bask_chosen_contact']))
	{
		?>
		<script type="text/javascript">
			change_contact('<?php echo $_SESSION['auth_dep']['bask_chosen_contact'];?>', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=manage_filter_count_list');
		</script>
		<?php
	}
	?>
</div>
<div id="list_doc"><?php echo $str;?></div>
