<?php

/* Affichage */
if($mode == 'list'){
	$list = new list_show();
    $list->admin_list($tab,$i,$title,'id',
		'status_management_controler&mode=list','status','id',true,
		$page_name_up, $page_name_val, $page_name_ban, $page_name_del,
		$page_name_add, $label_add, FALSE, FALSE, _ALL_STATUS, _STATUS_SING,
		$_SESSION['config']['businessappurl'] 
		. 'static.php?filename=manage_status_b.gif', false, true, false, true, 
		$what, true, $autoCompletionArray);
}
elseif($mode == 'up' || $mode == 'add'){
    ?><h1><img src="<?php 
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=manage_status_b.gif" alt="" />
    <?php
        if($mode == 'up'){
            echo _MODIFY_STATUS;
        }
        elseif($mode == "add"){
            echo _ADD_STATUS;
        }?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
        <br /><br />
    <?php
    if($state == false){
        echo '<br /><br /><br /><br />' . _THE_STATUS . ' ' . _UNKNOWN
        . '<br /><br /><br /><br />';
    }
    else{?>
        <form name="frmstatus" id="frmstatus" method="post" action="<?php 
        echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
        . '&admin=status&page=status_management_controler&mode='
        . $mode;?>" class="forms addforms">
            <input type="hidden" name="display" value="true" />
            <input type="hidden" name="admin" value="status" />
            <input type="hidden" name="page" value="status_management_controler" />
            <input type="hidden" name="mode" value="<?php echo $mode;?>" />

            <input type="hidden" name="order" id="order" value="<?php 
				echo $_REQUEST['order'];?>" />
            <input type="hidden" name="order_field" id="order_field" value="<?php 
				echo $_REQUEST['order_field'];?>" />
            <input type="hidden" name="what" id="what" value="<?php 
				echo $_REQUEST['what'];?>" />
            <input type="hidden" name="start" id="start" value="<?php 
				echo $_REQUEST['start'];?>" />
            <input type="hidden" name="is_system" id="is_system" value="<?php 
				echo $_SESSION['m_admin']['status']['IS_SYSTEM'];?>" />
            <p>
                <label for="status_id"><?php echo _ID; ?> : </label>
                <input name="status_id" type="text"  id="status_id" value="<?php
					echo functions::show_str($_SESSION['m_admin']['status']['ID']
					); ?>" <?php
					if($mode == "up"){ 
						echo 'readonly="readonly" class="readonly"';
					}?>/>
            </p>
            <p>
                <label for="label"><?php echo _DESC; ?> : </label>
                <input name="label" type="text"  id="label" value="<?php 
					echo functions::show_str($_SESSION['m_admin']['status']['LABEL']
					); ?>"/>
            </p>
            <p>
                <label ><?php echo _CAN_BE_SEARCHED; ?> : </label>
                <input type="radio"  class="check" name="can_be_searched" value="Y"<?php
                 if($_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] == 'Y'){
					 ?> checked="checked"<?php 
					} ?> /><?php echo _YES;?>
                <input type="radio" name="can_be_searched" class="check" value="N"
                <?php
					if($_SESSION['m_admin']['status']['CAN_BE_SEARCHED'] == 'N'){
						?> checked="checked"<?php
					} ?> /><?php echo _NO;?>
            </p>
            <p>
                <label ><?php echo _CAN_BE_MODIFIED; ?> : </label>
                <input type="radio"  class="check" name="can_be_modified" value="Y" 
				<?php 
					if($_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] == 'Y'){
						?> checked="checked"<?php 
					} ?> /><?php echo _YES;?>
                <input type="radio" name="can_be_modified" class="check"  value="N" 
                <?php 
					if($_SESSION['m_admin']['status']['CAN_BE_MODIFIED'] == 'N'){
						?> checked="checked"<?php 
					} ?> /><?php echo _NO;?>
            </p>

             <p class="buttons">
                <?php
            if($mode == 'up'){?>
                <input class="button" type="submit" name="status_submit" value="
                <?php echo _MODIFY_STATUS; ?>" />
                <?php
            }
			elseif($mode == 'add')
            {?>
                <input type="submit" class="button"  name="status_submit" value="
                <?php echo _ADD_STATUS; ?>" />
                <?php
            }
                ?>
            <input type="button" class="button"  name="cancel" value="<?php
             echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php
              echo $_SESSION['config']['businessappurl'];
              ?>index.php?page=status_management_controler&amp;mode=list&amp;admin=status';"/>
            </p>
        </form >
    <?php
    }
}