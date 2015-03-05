<?php
/* Affichage */
if ($mode == 'list') {
    $list = new list_show();
    $list->admin_list(
        $statusList['tab'],
        count($statusList['tab']),
        $statusList['title'],
        'id',
        'status_management_controler&mode=list',
        'status','id',
        true,
        $statusList['page_name_up'],
        $statusList['page_name_val'],
        $statusList['page_name_ban'],
        $statusList['page_name_del'],
        $statusList['page_name_add'],
        $statusList['label_add'],
        false,
        false,
        _ALL_STATUS,
        _STATUS,
        'check-circle',
        false,
        true,
        false,
        true,
        $statusList['what'],
        true,
        $statusList['autoCompletionArray']
    );
} elseif ($mode == 'up' || $mode == 'add') {
    ?><h1><i class="fa fa-check-circle fa-2x"></i>
    <?php
        if ($mode == 'up') {
            echo _MODIFY_STATUS;
        } elseif ($mode == 'add') {
            echo _ADD_STATUS;
        }?>
    </h1>
    <div id="inner_content" class="clearfix" align="center">
    <?php
    if ($state == false) {
        echo '<br /><br /><br /><br />' . _THE_STATUS . ' ' . _UNKNOWN
        . '<br /><br /><br /><br />';
    } else {?>
    <div class="block">
    <form name="frmstatus" id="frmstatus" method="post" action="<?php
        echo $_SESSION['config']['businessappurl'] . 'index.php?display=true'
        . '&amp;admin=status&amp;page=status_management_controler&amp;mode='
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
            echo $_SESSION['m_admin']['status']['is_system'];?>" />
        <p>
            <label for="status_id"><?php echo _ID; ?> : </label>
            <input name="status_id" type="text"  id="status_id" value="<?php
                echo functions::show_str(
                    $_SESSION['m_admin']['status']['id']
                ); ?>" <?php
                if ($mode == 'up') {
                    echo 'readonly="readonly" class="readonly"';
                }?>/>
        </p>
        <p>
            <label for="label"><?php echo _DESC; ?> : </label>
            <input name="label" type="text"  id="label" value="<?php
                echo functions::show_str(
                    $_SESSION['m_admin']['status']['label_status']
                ); ?>"/>
        </p>
        <p>
            <label ><?php echo _CAN_BE_SEARCHED; ?> : </label>
        <input type="radio"  class="check" name="can_be_searched" value="Y"<?php
             if ($_SESSION['m_admin']['status']['can_be_searched'] == 'Y') {
                 ?> checked="checked"<?php
             } ?> /><?php echo _YES;?>
            <input type="radio" name="can_be_searched" class="check" value="N"
            <?php
            if ($_SESSION['m_admin']['status']['can_be_searched'] == 'N') {
               ?> checked="checked"<?php
            } ?> /><?php echo _NO;?>
        </p>
        <p>
            <label ><?php echo _CAN_BE_MODIFIED; ?> : </label>
            <input type="radio"  class="check" name="can_be_modified" value="Y"
            <?php
            if ($_SESSION['m_admin']['status']['can_be_modified'] == 'Y') {
                ?> checked="checked"<?php
            } ?> /><?php echo _YES;?>
            <input type="radio" name="can_be_modified" class="check"  value="N"
            <?php
            if ($_SESSION['m_admin']['status']['can_be_modified'] == 'N') {
               ?> checked="checked"<?php
            } ?> /><?php echo _NO;?>
			
			<input name="img_filename" type="hidden"  id="img_filename" value="<?php
                echo functions::show_str(
                    $_SESSION['m_admin']['status']['img_filename']
                ); ?>" <?php
                /* if ($mode == 'up') {
                    echo 'readonly="readonly" class="readonly"';
                } */?>/>
        </p>
        <?php
        $core_tools = new core_tools();
        if ($core_tools->is_module_loaded('folder')) {
        ?>
            <p>
                <label ><?php echo _IS_FOLDER_STATUS; ?> : </label>
                <input type="radio"  class="check" name="is_folder_status" value="Y"
                <?php
                if ($_SESSION['m_admin']['status']['is_folder_status'] == 'Y') {
                    ?> checked="checked"<?php
                } ?> /><?php echo _YES;?>
                <input type="radio" name="is_folder_status" class="check"  value="N"
                <?php
                if ($_SESSION['m_admin']['status']['is_folder_status'] == 'N') {
                   ?> checked="checked"<?php
                } ?> /><?php echo _NO;?>
            </p>
        <?php } ?>
        <p class="buttons">

            <input type="submit" class="button"  name="status_submit" value="<?php echo _VALIDATE; ?>" />

        <input type="button" class="button"  name="cancel" value="<?php
         echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php
         echo $_SESSION['config']['businessappurl'];
?>index.php?page=status_management_controler&amp;mode=list&amp;admin=status';"/>
        </p>
     </form >
     </div>
<?php
    }
   ?></div><?php
}
