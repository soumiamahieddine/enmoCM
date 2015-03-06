<?php

/* Affichage */
if($mode == "list")
{
    $list = new list_show();
    $list->admin_list(
                    $users_list['tab'],
                    count($users_list['tab']),
                    $users_list['title'],
                    'user_id',
                    'users_management_controler&mode=list',
                    'users','user_id',
                    true,
                    $users_list['page_name_up'],
                    $users_list['page_name_val'],
                    $users_list['page_name_ban'],
                    $users_list['page_name_del'],
                    $users_list['page_name_add'],
                    $users_list['label_add'],
                    false,
                    false,
                    _ALL_USERS,
                    _USER,
                    'user',
                    false,
                    true,
                    false,
                    true,
                    $users_list['what'],
                    true,
                    $users_list['autoCompletionArray']
                );
}
elseif($mode == "up" || $mode == "add")
{  
    ?><script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=users_management.js"></script><?php
    if($mode == "add")
    {
        echo '<h1><i class="fa fa-user fa-2x"></i> '._USER_ADDITION.'</h1>';
    }
    elseif($mode == "up")
    {
        echo '<h1><i class="fa fa-user fa-2x"></i> '._USER_MODIFICATION.'</h1>';
    }
    echo '<br/>';
    $_SESSION['service_tag'] = 'formuser';
    core_tools::execute_modules_services($_SESSION['modules_services'], 'formuser', "include");
    ?>
    <div id="ugc"></div>
    <?php
    if($state == false)
        echo "<br /><br /><br /><br />"._USER.' '._UNKNOWN."<br /><br /><br /><br />";
    else
    {?>
        <form  id="frmuser" class="block" method="post" action="<?php echo $_SESSION['config']['businessappurl']; ?>index.php?display=true&amp;admin=users&amp;page=users_management_controler&amp;mode=<?php echo $mode;?>" class="forms addforms" style="width:55%;height:330px;">
            <div class="content" style="width:330px;margin:auto;">
            <input type="hidden" name="display" value="true" />
            <input type="hidden" name="admin" value="users" />
            <input type="hidden" name="page" value="users_management_controler" />
            <input type="hidden" name="mode" value="<?php echo $mode;?>" />

            <input type="hidden" name="order" id="order" value="<?php if(isset($_REQUEST['order'])){echo $_REQUEST['order'];}?>" />
            <input type="hidden" name="order_field" id="order_field" value="<?php if(isset($_REQUEST['order_field'])){echo $_REQUEST['order_field'];}?>" />
            <input type="hidden" name="what" id="what" value="<?php if(isset($_REQUEST['what'])){echo $_REQUEST['what'];}?>" />
            <input type="hidden" name="start" id="start" value="<?php if(isset($_REQUEST['start'])){ echo $_REQUEST['start'];}?>" />
            <p>
                <label for="user_id"><?php  echo _ID; ?> :</label>
                    <?php  if($mode == "up" && isset($_SESSION['m_admin']['users']['user_id'])) { echo functions::show_string($_SESSION['m_admin']['users']['user_id']); }else{ echo '<br/>'; } ?><input name="user_id"  type="<?php  if($mode == "up") { ?>hidden<?php  } elseif($mode == "add") { ?>text<?php  } ?>" id="user_id" value="<?php  if(isset($_SESSION['m_admin']['users']['user_id'])) {echo functions::show_string($_SESSION['m_admin']['users']['user_id']);} ?>" />
                    <span class="red_asterisk"><?php  if($mode != "up"){?>*<?php } ?></span>
                    <!--<input type="hidden"  name="id" id="id" value="<?php  echo $id; ?>" />-->
            </p>
            <p>
                <label for="LastName"><?php  echo _LASTNAME; ?> :</label><br/>
                <input name="LastName" id="LastName" style="width: 95%;" type="text" value="<?php if(isset($_SESSION['m_admin']['users']['lastname'])){echo functions::show_string($_SESSION['m_admin']['users']['lastname']);} ?>" />
                <span class="red_asterisk">*</span>
            </p>
            <p>
                <label for="FirstName"><?php  echo _FIRSTNAME; ?> :</label><br/>
                <input name="FirstName" style="width: 95%;" id="FirstName"  type="text" value="<?php if(isset($_SESSION['m_admin']['users']['firstname'])){ echo functions::show_string($_SESSION['m_admin']['users']['firstname']); }?>" />
                <span class="red_asterisk">*</span>
            </p>
            <p>
                <?php  echo _PHONE_NUMBER; ?> :<br/>
                <input name="Phone" id="Phone" style="width: 95%;" type="text" value="<?php if(isset($_SESSION['m_admin']['users']['phone'])){ echo $_SESSION['m_admin']['users']['phone']; }?>" />
            </p>
            <p>
                <label for="Mail"><?php  echo _MAIL; ?> :</label><br/>
                <input name="Mail" id="Mail" style="width: 95%;" type="text" value="<?php if(isset($_SESSION['m_admin']['users']['mail'])){ echo $_SESSION['m_admin']['users']['mail']; }?>" />
                <span class="red_asterisk">*</span>
            </p>
            <p>
                <?php  echo _LOGIN_MODE; ?>&nbsp;:<br/>
                <?php
                echo '<select name="LoginMode" style="width: 95%;"  id="LoginMode">';

                    foreach($_SESSION['login_method_memory'] as $METHOD)
                    {
                        if($METHOD['ACTIVATED'] == 'true')
                        {
                            $vala = '';
                            if ($_SESSION['m_admin']['users']['loginmode'] == $METHOD['ID'])
                                $vala = 'selected="selected"';

                            echo '<option value="'.$METHOD['ID'].'" '.$vala.'  >'.constant($METHOD['BRUT_LABEL']).'</option>';
                        }
                    }

                echo '</select>';
                ?>
                <span class="red_asterisk">*</span>
            </p>
            <p class="buttons">
            <?php
                if($mode == "up" && $_SESSION['config']['ldap'] != "true")
                {
                    ?>
                    <input type="button" name="reset_pwd" value="<?php  echo _RESET.' '._PASSWORD; ?>" class="button" onclick="displayModal('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&amp;admin=users&amp;page=psw_changed', 'pwd_changed', 40, 150);"  />
                    <?php
                }
                ?><br/>
                <input type="submit" name="user_submit" id="user_submit" value="<?php  echo _VALIDATE; ?>" class="button"/>
                 <input type="button" class="button"  name="cancel" value="<?php  echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=users_management_controler&amp;mode=list&amp;admin=users';"/>
            </p>
            </div>
        </form>
             <?php
             if($mode == "up")
                core_tools::execute_modules_services($_SESSION['modules_services'], 'users_up.php', "include");?>

    <script type="text/javascript">updateContent('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=ugc_form&admin=users', 'ugc');</script>
    <?php
    }
}

