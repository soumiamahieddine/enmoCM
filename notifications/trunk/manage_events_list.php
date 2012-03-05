<?php
/* Affichage */
if ($mode == 'list') {
    $list = new list_show();
    $list->admin_list(
        $eventsList['tab'],
        count($eventsList['tab']),
        $eventsList['title'],
        'system_id',
        'manage_events_list_controller&mode=list',
        'notifications','system_id',
        true,
        $eventsList['page_name_up'],
        $eventsList['page_name_val'],
        $eventsList['page_name_ban'],
        $eventsList['page_name_del'],
        $eventsList['page_name_add'],
        $eventsList['label_add'],
        false,
        false,
        _ALL_EVENTS,
        _EVENT,
        $_SESSION['config']['businessappurl']
        . 'static.php?filename=manage_users_b.gif',
        true,
        true,
        false,
        true,
        $eventsList['what'],
        true,
        $eventsList['autoCompletionArray']
    );
} elseif ($mode == 'up' || $mode == 'add') {
    ?><h1><img src="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=manage_status_b.gif" alt="" />
    <?php
        if ($mode == 'up') {
            echo _MODIFY_EVENT;
        } elseif ($mode == 'add') {
            echo _ADD_EVENT;
        }?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
        <br /><br />
    <?php
    if ($state == false) {
        echo '<br /><br /><br /><br />' . _THIS_EVENT . ' ' . _IS_UNKNOWN
        . '<br /><br /><br /><br />';
    } else {?>
    <form name="frmevent" id="frmevent" method="post" action="<?php
        echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
        . '&amp;module=notifications&amp;page=manage_events_list_controller&amp;mode='
        . $mode;?>" class="forms addforms">
        <input type="hidden" name="display" value="true" />
        <input type="hidden" name="admin" value="notifications" />
        <input type="hidden" name="page" value="manage_event_list_controler" />
        <input type="hidden" name="mode" value="<?php echo $mode;?>" />
        
        <input type="hidden" name="system_id" id="system_id" value="<?php echo $_SESSION['m_admin']['event']['system_id'];?>" />

        <input type="hidden" name="order" id="order" value="<?php
            echo $_REQUEST['order'];?>" />
        <input type="hidden" name="order_field" id="order_field" value="<?php
            echo $_REQUEST['order_field'];?>" />
        <input type="hidden" name="what" id="what" value="<?php
            echo $_REQUEST['what'];?>" />
        <input type="hidden" name="start" id="start" value="<?php
            echo $_REQUEST['start'];?>" />
       
		
		<p>
            <label for="label"><?php echo _NAME; ?> : </label>
            <input name="notification_id" type="text"  id="notification_id" value="<?php
                echo functions::show_str(
                    $_SESSION['m_admin']['event']['notification_id']
                ); ?>"/>
        </p>
        <p>
            <label for="label"><?php echo _DESC; ?> : </label>
            <input name="description" type="text"  id="description" value="<?php
                echo functions::show_str(
                    $_SESSION['m_admin']['event']['description']
                ); ?>"/>
        </p>
		<p>
			<label for="label"><?php echo _EVENT; ?> : </label>
			<select name="value_field" id="value_field">
				<optgroup label="<?php echo _ACTIONS; ?>">
				<option value=""><?php echo _EVENT;?></option>
				<?php
				foreach($actions_list as $this_action){
					?><option value="<?php echo $this_action->id;?>" 
					<?php 
					if($_SESSION['m_admin']['event']['value_field'] 
						== $this_action->id) { 
						echo 'selected="selected"';
					}?>><?php echo $this_action->label_action;
					?></option><?php
				}
				
				//Récupération des éléments systèmes
				?></optgroup><?php
				$newarray = array_keys($_SESSION['notif_events']);
				?><optgroup label="<?php echo _SYSTEM; ?>"><?php
				foreach($_SESSION['notif_events'] as $event_type_id => $event_type_label){
					?><option value="<?php echo $event_type_id;?>" 
					<?php 
					if($_SESSION['m_admin']['event']['value_field'] 
						== $event_type_id) { 
						echo 'selected="selected"';
					}?>><?php echo $event_type_label;
					?></option><?php
				}
				?>
				</optgroup>
			</select>
		</p>
		
		
		<p>
			<label for="label"><?php echo _TEMPLATE; ?> : </label>
			<select name="template_id" id="template_id">
				<option value=""><?php echo _TEMPLATE;?></option>
				<?php
				foreach($templates_list as $template){
					?><option value="<?php echo $template['id'];?>" 
					<?php 
					if($_SESSION['m_admin']['event']['template_id'] 
						== $template['id']) { 
						echo 'selected="selected"';
					}?>><?php echo $template['label'];
					?></option><?php
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="status"><?php echo _DIFFUSION_TYPE; ?> : </label>
			<select name="diffusion_type" 
					id="status" onchange="change_diff_type_box(this.options[this.selectedIndex].value,'<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&module=notifications&page=load_diffusiontype_formcontent',
					'diff_type_div','notifications',
					'');">
					
				<option value=""><?php echo _DIFFUSION_TYPE;?></option>
				<?php
				foreach($diffusion_types as $this_diffusion){
					?><option value="<?php echo $this_diffusion->id;?>" 
					<?php 
					if(trim($_SESSION['m_admin']['event']['diffusion_type']) 
						== trim($this_diffusion->id)) { 
						echo 'selected="selected"';
					}?>><?php echo $this_diffusion->label;
					?></option><?php
				}
				?>
			</select>
		</p>
				
		<div id="diff_type_div" 
			class="scroll_div" 
			style="height:200px; 
					width:600px;
					border: 1px solid;">
					
					
		
		</div>
		
		
		
		<p>
		<div id="diff_list_div" class="scroll_div" '
                . 'style="height:200px; border: 1px solid;"></div>
		</p>
		
		<p>
			<label for="status"><?php echo _DIFFUSION_CONTENT; ?> : </label>
			<select name="diffusion_content" 
					id="status" onchange="change_diff_type_box(this.options[this.selectedIndex].value,'<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&module=notifications&page=load_diffusiontype_formcontent',
					'diff_type_div','notifications',
					'');">
					
				<option value=""><?php echo _DIFFUSION_CONTENT;?></option>
				<?php
				foreach($diffusion_contents as $this_content){
					?><option value="<?php echo $this_content->id;?>" 
					<?php 
					if(trim($_SESSION['m_admin']['event']['diffusion_content']) 
						== trim($this_content->id)) { 
						echo 'selected="selected"';
					}?>><?php echo $this_content->label;
					?></option><?php
				}
				?>
			</select>
		</p>
	
		 <p>
            <label ><?php echo _ATTACH_MAIL_FILE; ?> : </label>
            <input type="radio"  class="check" name="is_attached" value="Y"
            <?php
            if ($_SESSION['m_admin']['event']['is_attached'] == 'Y') {
                ?> checked="checked"<?php
            } ?> /><?php echo _YES;?>
            <input type="radio" name="is_attached" class="check"  value="N"
            <?php
            if ($_SESSION['m_admin']['event']['is_attached'] == 'N' || !$_SESSION['m_admin']['event']['is_attached']) {
               ?> checked="checked"<?php
            } ?> /><?php echo _NO;?>
        </p>
		
        <p class="buttons">
            <?php
        if ($mode == 'up') {?>
            <input class="button" type="submit" name="event_submit" style="width:190px;" value=
			"<?php echo _MODIFY_EVENT; ?>" />
            <?php
        } elseif ($mode == 'add') {?>
            <input type="submit" class="button"  name="event_submit" value=
			"<?php echo _ADD; ?>" />
            <?php
        }
        ?>
        <input type="button" class="button"  name="cancel" value="<?php
         echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php
         echo $_SESSION['config']['businessappurl'];
		 ?>index.php?page=manage_events_list_controller&amp;mode=list&amp;module=notifications'"/>

	</p>


	


     </form >
<?php
    }
   ?></div><?php
   
   	 
	if ($_SESSION['m_admin']['event']['diffusion_type'] <> '')
	{
		/*First Launch */
		?>
		<script language="javascript">
		change_diff_type_box(
			'<?php echo $_SESSION['m_admin']['event']['diffusion_type']; ?>',
			'<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&module=notifications&page=load_diffusiontype_formcontent',
			'diff_type_div',
			'notifications',
			'');
		</script>
		<?php
		if ($_SESSION['m_admin']['event']['diffusion_type'] <> '')
		{
			//Loading Extra Javascript : 
			require_once 'modules' . DIRECTORY_SEPARATOR . 'notifications' . DIRECTORY_SEPARATOR
    . 'class' . DIRECTORY_SEPARATOR . 'diffusion_type_controler.php';
			$Type = new diffusion_type_controler();
			
			$dType = $Type -> getDiffusionType($_SESSION['m_admin']['event']['diffusion_type']);
			include_once ($dType->script);
			?>
			<script language="javascript">
			loadDiffusionProperties(
				'<?php echo $_SESSION['m_admin']['event']['diffusion_type']; ?>',
				'<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&module=notifications&page=load_diffusionproperties_formcontent'
				);
			</script>
			<?php
			//getExtraProperties(); //Lancement du javascript adequat
		}
	}
}
