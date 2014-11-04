<?php
/**
* Contacts Class
*
* Contains all the specific functions to manage Contacts
*
* @version 2.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Loïc Vinet  <dev@maarch.org>
*
*/

/**
* Class Contacts : Contains all the specific functions to manage Contacts
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @version 2.0
*/

class contacts_v2 extends dbquery
{
    /**
    * Return the contacts data in sessions vars
    *
    * @param string $mode add or up
    */
    public function contactinfo($mode)
    {
        // return the user information in sessions vars
        $func = new functions();
        $_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] =
            $_REQUEST['is_corporate'];
        if ($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y') {
            $_SESSION['m_admin']['contact']['SOCIETY'] = $func->wash(
                $_REQUEST['society'], 'no', _STRUCTURE_ORGANISM . ' ', 'yes', 0, 255
            );
            $_SESSION['m_admin']['contact']['LASTNAME'] = '';
            $_SESSION['m_admin']['contact']['FIRSTNAME'] = '';
            $_SESSION['m_admin']['contact']['FUNCTION'] = '';
            $_SESSION['m_admin']['contact']['TITLE'] = '';
        } else {
            $_SESSION['m_admin']['contact']['LASTNAME'] = $func->wash(
                $_REQUEST['lastname'], 'no', _LASTNAME, 'yes', 0, 255
            );
            $_SESSION['m_admin']['contact']['FIRSTNAME'] = $func->wash(
                $_REQUEST['firstname'], 'no', _FIRSTNAME, 'yes', 0, 255
            );
            if ($_REQUEST['society'] <> '') {
                $_SESSION['m_admin']['contact']['SOCIETY'] = $func->wash(
                    $_REQUEST['society'], 'no', _STRUCTURE_ORGANISM . ' ', 'yes', 0, 255
                );
            } else {
                $_SESSION['m_admin']['contact']['SOCIETY'] = '';
            }
            if ($_REQUEST['function'] <> '') {
                $_SESSION['m_admin']['contact']['FUNCTION'] = $func->wash(
                    $_REQUEST['function'], 'no', _FUNCTION . ' ', 'yes', 0, 255
                );
            } else {
                $_SESSION['m_admin']['contact']['FUNCTION'] = '';
            }
            if ($_REQUEST['title'] <> '') {
                $_SESSION['m_admin']['contact']['TITLE'] = $func->wash(
                    $_REQUEST['title'], 'no', _TITLE2 . ' ', 'yes', 0, 255
                );
            } else {
                $_SESSION['m_admin']['contact']['TITLE'] = '';
            }
        }
        if ($_REQUEST['society_short'] <> '') {
            $_SESSION['m_admin']['contact']['SOCIETY_SHORT'] = $func->wash(
                $_REQUEST['society_short'], 'no', _SOCIETY_SHORT . ' ', 'yes', 0, 32
            );
        } else {
            $_SESSION['m_admin']['contact']['SOCIETY_SHORT'] = '';
        }

        $_SESSION['m_admin']['contact']['CONTACT_TYPE'] = $func->wash(
            $_REQUEST['contact_type'], 'no', _CONTACT_TYPE . ' ', 'yes', 0, 255
        );

        if ($_REQUEST['comp_data'] <> '') {
            $_SESSION['m_admin']['contact']['OTHER_DATA'] = $func->wash(
                $_REQUEST['comp_data'], 'no', _COMP_DATA . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['contact']['OTHER_DATA'] = '';
        }

        if (isset($_REQUEST['owner']) && $_REQUEST['owner'] <> '') {
            if (preg_match('/\((.|\s|\d|\h|\w)+\)$/i', $_REQUEST['owner']) == 0) {
                $_SESSION['error'] = _CREATE_BY . ' ' . _WRONG_FORMAT . '.<br/>'
                                   . _USE_AUTOCOMPLETION;
            } else {
                $_SESSION['m_admin']['contact']['OWNER'] = str_replace(
                    ')', '', substr($_REQUEST['owner'],
                    strrpos($_REQUEST['owner'],'(')+1)
                );
                $_SESSION['m_admin']['contact']['OWNER'] = $func->wash(
                    $_SESSION['m_admin']['contact']['OWNER'], 'no',
                    _CREATE_BY . ' ', 'yes', 0, 32
                );
            }
        } else {
            $_SESSION['m_admin']['contact']['OWNER'] = '';
        }

        $_SESSION['m_admin']['contact']['order'] = $_REQUEST['order'];
        $_SESSION['m_admin']['contact']['order_field'] = $_REQUEST['order_field'];
        $_SESSION['m_admin']['contact']['what'] = $_REQUEST['what'];
        $_SESSION['m_admin']['contact']['start'] = $_REQUEST['start'];
    }

    public function is_exists($mode, $mycontact){
        $query = $this->query_contact_exists($mode);
        $this->query($query);
        if($this->nb_result() > 0){
            if($mode <> 'up'){
                $_SESSION['error'] = _THE_CONTACT.' '._ALREADY_EXISTS;
            }

            if($mycontact == 'iframe'){
                $path_contacts_confirm = $_SESSION['config']['businessappurl'] . 'index.php?display=false&page=contacts_v2_confirm';
            } else {
                $path_contacts_confirm = $_SESSION['config']['businessappurl'] . 'index.php?page=contacts_v2_confirm';
            }
            header(
                'location: ' . $path_contacts_confirm.'&mode='.$mode.'&mycontact='.$mycontact
            );
            exit;
        }
    }

    public function query_contact_exists($mode){
        $this->connect();
        $query = '';
        if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'N'){
            $query = "select contact_id, contact_type, society, firstname, lastname from ".$_SESSION['tablename']['contacts_v2']." 
                where lower(firstname) = lower('".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])."')
                  and lower(lastname) = lower('".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])."')";

        } else if ($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){
            $query = "select contact_id, contact_type, society, firstname, lastname from ".$_SESSION['tablename']['contacts_v2']." 
                where lower(society) = lower('".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])."')";

        }
        if ($mode == 'up'){
            $query .= " and contact_id <> " . $_SESSION['m_admin']['contact']['ID'];
        }
        return $query;    
    }


    /**
    * Add ou modify contact in the database
    *
    * @param string $mode up or add
    */
    public function addupcontact($mode, $admin = true, $confirm = 'N', $mycontact = 'N')
    {
        // add ou modify users in the database
        if($confirm == 'N'){
            $this->contactinfo($mode);
        }
        if (empty($_SESSION['error']) && $confirm == 'N') {
            $this->is_exists($mode, $mycontact);
        }
        $order = $_SESSION['m_admin']['contact']['order'];
        $order_field = $_SESSION['m_admin']['contact']['order_field'];
        $what = $_SESSION['m_admin']['contact']['what'];
        $start = $_SESSION['m_admin']['contact']['start'];

        $path_contacts = $_SESSION['config']['businessappurl']
                       . 'index.php?page=contacts_v2&order='
                       . $order . '&order_field=' . $order_field . '&start='
                       . $start . '&what=' . $what;
        $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                  . 'index.php?page=contacts_v2_add';
        $path_contacts_up_errors = $_SESSION['config']['businessappurl']
                                 . 'index.php?page=contacts_v2_up';
        if (! $admin) {
            $path_contacts = $_SESSION['config']['businessappurl']
                           . 'index.php?page=my_contacts&dir=my_contacts&load&order='
                           . $order . '&order_field=' . $order_field . '&start='
                           . $start . '&what=' . $what;
            $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                      . 'index.php?page=my_contact_add&dir='
                                      . 'my_contacts&load';
            $path_contacts_up_errors = $_SESSION['config']['businessappurl']
                                     . 'index.php?page=my_contact_up&dir='
                                     . 'my_contacts&load';
        }
        if ($mycontact == 'iframe') {
            if ($mode == 'add') {
                $path_contacts = $_SESSION['config']['businessappurl']
                                          . 'index.php?display=false&dir=my_contacts&page=create_address_iframe';
                $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                          . 'index.php?display=false&dir=my_contacts&page=create_contact_iframe';
            } else if ($mode == 'up') {
                $path_contacts =  $_SESSION['config']['businessappurl']
                                        . 'index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid='.$_SESSION['contact']['current_contact_id'].'&addressid='.$_SESSION['contact']['current_address_id'].'&created=Y';
                $path_contacts_up_errors = $_SESSION['config']['businessappurl']
                                        . 'index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid='.$_SESSION['contact']['current_contact_id'].'&addressid='.$_SESSION['contact']['current_address_id'];
            }
        }
        if (! empty($_SESSION['error'])) {
            if ($mode == 'up') {
                if (! empty($_SESSION['m_admin']['contact']['ID'])) {
                    header(
                        'location: ' . $path_contacts_up_errors . '&id='
                        . $_SESSION['m_admin']['contact']['ID']
                    );
                    exit;
                } else {
                    header('location: ' . $path_contacts);
                    exit;
                }
            }
            if ($mode == 'add') {
                header('location: ' . $path_contacts_add_errors);
                exit;
            }
        } else {
            $this->connect();
            if ($mode == 'add') {
                if($_SESSION['user']['UserId'] == 'superadmin'){
                    $entity_id = 'SUPERADMIN';
                } else {
                    $entity_id = $_SESSION['user']['primaryentity']['id'];
                }
                $query = 'INSERT INTO ' . $_SESSION['tablename']['contacts_v2']
                       . ' ( contact_type, lastname , firstname , society , society_short, function , '
                       . 'other_data,'
                       . " title, is_corporate_person, user_id, entity_id, creation_date) VALUES (  "
                         . $_SESSION['m_admin']['contact']['CONTACT_TYPE']                          
                         . ", '" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['LASTNAME']
                       ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['FIRSTNAME']
                       ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['SOCIETY']
                       ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['SOCIETY_SHORT']
                       ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['FUNCTION']
                       ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['OTHER_DATA']
                       ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['TITLE']
                       ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON']                                   
                       ) . "','" . $this->protect_string_db(
                            $_SESSION['user']['UserId']
                       ) . "','" . $this->protect_string_db(
                            $entity_id
                       ) . "', current_timestamp)";
                $this->query($query);
                if($_SESSION['history']['contactadd'])
                {
                    $this->query("select contact_id, creation_date from ".$_SESSION['tablename']['contacts_v2']
                        ." where lastname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])
                        ."' and firstname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])
                        ."' and society = '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])
                        ."' and function = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])
                        ."' and is_corporate_person = '".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])
                        ."' order by creation_date desc");
                    $res = $this->fetch_object();
                    $id = $res->contact_id;
                    if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y')
                    {
                        $msg =  _CONTACT_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY']);
                    }
                    else
                    {
                        $msg =  _CONTACT_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'].' '.$_SESSION['m_admin']['contact']['FIRSTNAME']);
                    }
                    require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
                    $hist = new history();
                    $hist->add($_SESSION['tablename']['contacts_v2'], $id,"ADD",'contacts_v2_add',$msg, $_SESSION['config']['databasetype']);
                }
                if($mycontact = 'iframe'){
                    $this->query("select contact_id, creation_date from ".$_SESSION['tablename']['contacts_v2']
                        ." where lastname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])
                        ."' and firstname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])
                        ."' and society = '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])
                        ."' and function = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])
                        ."' and is_corporate_person = '".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])
                        ."' order by creation_date desc");
                    $res = $this->fetch_object();
                    $id = $res->contact_id;
                    $_SESSION['contact']['current_contact_id'] = $id;
                }
                $this->clearcontactinfos();
                $_SESSION['info'] = _CONTACT_ADDED;
                header("location: ".$path_contacts);
                exit;
            }
            elseif($mode == "up")
            {
                $query = "update ".$_SESSION['tablename']['contacts_v2']." set update_date = current_timestamp, contact_type = ".$_SESSION['m_admin']['contact']['CONTACT_TYPE'].", lastname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'])."', firstname = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FIRSTNAME'])."',society = '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY'])."',society_short = '".$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY_SHORT'])."',function = '".$this->protect_string_db($_SESSION['m_admin']['contact']['FUNCTION'])."', other_data = '".$this->protect_string_db($_SESSION['m_admin']['contact']['OTHER_DATA'])."', title = '".$this->protect_string_db($_SESSION['m_admin']['contact']['TITLE'])."', is_corporate_person = '".$this->protect_string_db($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'])."'";
                // if($admin)
                // {
                //     $query .= ", user_id = '".$this->protect_string_db($_SESSION['m_admin']['contact']['OWNER'])."'";
                // }
                $query .=" where contact_id = '".$_SESSION['m_admin']['contact']['ID']."'";
                if(!$admin)
                {
                    //$query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
                }
                $this->query($query);
                if($_SESSION['history']['contactup'])
                {
                    if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y')
                    {
                        $msg =  _CONTACT_MODIFIED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['SOCIETY']);
                    }
                    else
                    {
                        $msg =  _CONTACT_MODIFIED.' : '.$this->protect_string_db($_SESSION['m_admin']['contact']['LASTNAME'].' '.$_SESSION['m_admin']['contact']['FIRSTNAME']);
                    }
                    require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
                    $hist = new history();
                    $hist->add($_SESSION['tablename']['contacts_v2'], $_SESSION['m_admin']['contact']['ID'],"UP",'contacts_v2_up',$msg, $_SESSION['config']['databasetype']);
                }
                $this->clearcontactinfos();
                $_SESSION['info'] = _CONTACT_MODIFIED;
                if (isset($_REQUEST['fromContactTree'])) {
                    ?><script>self.close();</script><?php
                } else {
                    header("location: ".$path_contacts);
                    exit();                    
                }
            }
        }
    }

    /**
    * Form to modify a contact v2
    *
    * @param  $string $mode up or add
    * @param int  $id  $id of the contact to change
    */
    public function formcontact($mode,$id = "", $admin = true, $iframe = false)
    {
        if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
        {
            $browser_ie = true;
            $display_value = 'block';
        }
        elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"]) )
        {
            $browser_ie = true;
            $display_value = 'block';
        }
        else
        {
            $browser_ie = false;
            $display_value = 'table-row';
        }
        $func = new functions();
        $state = true;
        if(!isset($_SESSION['m_admin']['contact']))
        {
            $this->clearcontactinfos();
        }
        if( $mode <> "add")
        {
            $this->connect();
            $query = "select * from ".$_SESSION['tablename']['contacts_v2']." where contact_id = ".$id;
            if(!$admin)
            {
                //$query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
            }
            $this->query($query);

            if($this->nb_result() == 0)
            {
                $_SESSION['error'] = _THE_CONTACT.' '._ALREADY_EXISTS;
                $state = false;
            }
            else
            {
                $_SESSION['m_admin']['contact'] = array();
                $line = $this->fetch_object();
                $_SESSION['m_admin']['contact']['ID'] = $line->contact_id;
                $_SESSION['m_admin']['contact']['TITLE'] = $this->show_string($line->title);
                $_SESSION['m_admin']['contact']['LASTNAME'] = $this->show_string($line->lastname);
                $_SESSION['m_admin']['contact']['FIRSTNAME'] = $this->show_string($line->firstname);
                $_SESSION['m_admin']['contact']['SOCIETY'] = $this->show_string($line->society);
                $_SESSION['m_admin']['contact']['SOCIETY_SHORT'] = $this->show_string($line->society_short);
                $_SESSION['m_admin']['contact']['FUNCTION'] = $this->show_string($line->function);
                $_SESSION['m_admin']['contact']['OTHER_DATA'] = $this->show_string($line->other_data);
                $_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] = $this->show_string($line->is_corporate_person);
                $_SESSION['m_admin']['contact']['CONTACT_TYPE'] = $line->contact_type;
                $_SESSION['m_admin']['contact']['OWNER'] = $line->user_id;
                if($admin && !empty($_SESSION['m_admin']['contact']['OWNER']))
                {
                    $this->query("select lastname, firstname from ".$_SESSION['tablename']['users']." where user_id = '".$_SESSION['m_admin']['contact']['OWNER']."'");
                    $res = $this->fetch_object();
                    $_SESSION['m_admin']['contact']['OWNER'] = $res->lastname.', '.$res->firstname.' ('.$_SESSION['m_admin']['contact']['OWNER'].')';
                }
            }
        }
        else if($mode == 'add' && !isset($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON']))
        {
            $_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] = 'Y';
        }
        require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
        $business = new business_app_tools();
        $tmp = $business->get_titles();
        $titles = $tmp['titles'];

        $contact_types = array();
        $this->connect();
        $this->query("SELECT id, label FROM ".$_SESSION['tablename']['contact_types']." ORDER BY label");
        while($res = $this->fetch_object()){
            $contact_types[$res->id] = $this->show_string($res->label); 
        }

        ?>
        <h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif" alt="" />
            <?php
            if($mode == "up") {
                echo '&nbsp;' . _MODIFY_CONTACT;
            }
            elseif($mode == "add") {
                echo '&nbsp;' . _ADD_NEW_CONTACT;
            }
            elseif($mode == "view") {
                echo '&nbsp;' . _VIEW;
            }
            ?>
        </h1>
        <div id="inner_content_contact" class="clearfix" align="center">
            <?php
            if($state == false)
            {
                echo "<br /><br /><br /><br />"._THE_CONTACT." "._UNKOWN."<br /><br /><br /><br />";
            }
            else
            {
                $action = $_SESSION['config']['businessappurl']."index.php?display=true&page=contacts_v2_up_db";
                if(!$admin)
                {
                    $action = $_SESSION['config']['businessappurl']."index.php?display=true&dir=my_contacts&page=my_contact_up_db";
                    if($iframe){
                        $action = $_SESSION['config']['businessappurl']."index.php?display=true&dir=my_contacts&page=my_contact_up_db&mycontact=iframe";
                    }
                }
                ?>
                <form name="frmcontact" id="frmcontact" method="post" action="<?php echo $action;?>" class="forms">
                    <input type="hidden" name="display"  value="true" />
                    <?php if(!$admin)
                    {?>
                        <input type="hidden" name="dir"  value="my_contacts" />
                        <input type="hidden" name="page"  value="my_contact_up_db" />
                <?php   }
                    else
                    {?>
                        <input type="hidden" name="admin"  value="contacts_v2" />
                        <input type="hidden" name="page"  value="contacts_v2_up_db" />
                <?php if (isset($_REQUEST['fromContactTree'])){
                        ?><input type="hidden" name="fromContactTree" value="yes" /><?php
                    }
                   }?>
                    <input type="hidden" name="order" id="order" value="<?php if(isset($_REQUEST['order'])) {echo $_REQUEST['order'];}?>" />
                    <input type="hidden" name="order_field" id="order_field" value="<?php if(isset($_REQUEST['order_field'])) { echo $_REQUEST['order_field'];}?>" />
                    <input type="hidden" name="what" id="what" value="<?php if(isset($_REQUEST['what'])){echo $_REQUEST['what'];}?>" />
                    <input type="hidden" name="start" id="start" value="<?php if(isset($_REQUEST['start'])){ echo $_REQUEST['start'];}?>" />
                <table width="65%" id="frmcontact_table">
                    <tr>
                        <td><?php echo _IS_CORPORATE_PERSON; ?> :</td>
                        <td>&nbsp;</td>
                        <td class="indexing_field">
                            <input type="radio"  class="check" name="is_corporate"  value="Y" <?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){?> checked="checked"<?php } ?>/ onclick="javascript:show_admin_contacts( true, '<?php echo $display_value;?>');"><?php echo _YES;?>
                            <input type="radio"  class="check" name="is_corporate" value="N" <?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'N'){?> checked="checked"<?php } ?> onclick="javascript:show_admin_contacts( false, '<?php echo $display_value;?>');"/><?php echo _NO;?>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                <?php if($admin && $mode == "up")
                {
                    ?>
                    <tr>
                        <td>
                            <label for="owner"><?php echo _CREATE_BY; ?> : </label>
                        </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><input disabled name="owner" type="text"  id="owner" value="<?php echo $func->show_str($_SESSION['m_admin']['contact']['OWNER']); ?>"/><div id="show_user" class="autocomplete"></div>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php
                }?>
                    <tr id="contact_types_tr" >
                        <td><label for="contact_types"><?php echo _CONTACT_TYPE; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field">
                            <select name="contact_type" id="contact_type" 
                                <?php if($mode == "add"){ 
                                    ?> onchange="getContacts('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=my_contacts&page=getContacts', this.options[this.selectedIndex].value, 'view');" <?php 
                                } ?>
                                >
                                <option value=""><?php echo _CHOOSE_CONTACT_TYPES;?></option>
                                <?php
                                    foreach(array_keys($contact_types) as $key) {
                                        ?><option value="<?php echo $key;?>" <?php

                                        if(isset($_SESSION['m_admin']['contact']['CONTACT_TYPE']) && $key == $_SESSION['m_admin']['contact']['CONTACT_TYPE'] )
                                        {
                                            echo 'selected="selected"';
                                        }
                                        ?>><?php echo $contact_types[$key];?>
                                        </option><?php
                                    }?>
                            </select></td>
                        <td><span class="red_asterisk" style="visibility:visible;" id="contact_types_mandatory">*</span></td>
                    </tr>
                    <tr id="contacts_created_tr" style="display:none">
                        <td><?php echo _CONTACT_ALREADY_CREATED; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field">
                            <select id="contacts_created">
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="society"><?php echo _STRUCTURE_ORGANISM; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><input name="society" type="text"  id="society" value="<?php if(isset($_SESSION['m_admin']['contact']['SOCIETY'])){ echo $func->show_str($_SESSION['m_admin']['contact']['SOCIETY']); }?>"/></td>
                        <td class="indexing_field" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'N'){ echo 'none';}else{ echo $display_value;}?>"><span class="red_asterisk" style="visibility:visible;" id="society_mandatory">*</span></td>
                    </tr>
                    <tr>
                        <td><?php echo _SOCIETY_SHORT; ?> :</td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><input name="society_short" type="text"  id="society_short" value="<?php if(isset($_SESSION['m_admin']['contact']['SOCIETY_SHORT'])){ echo $func->show_str($_SESSION['m_admin']['contact']['SOCIETY_SHORT']); }?>"/></td>
                    </tr>
                    <tr id="title_p" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><label for="title"><?php echo _TITLE2; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><select name="title" id="title" >
                            <option value=""><?php echo _CHOOSE_TITLE;?></option>
                            <?php
                            foreach(array_keys($titles) as $key)
                            {
                                ?><option value="<?php echo $key;?>" <?php

                                if((!isset($_SESSION['m_admin']['contact']['TITLE']) || empty($_SESSION['m_admin']['contact']['TITLE']))&& $key == $_SESSION['default_mail_title'])
                                {
                                     echo 'selected="selected"';
                                }
                                elseif(isset($_SESSION['m_admin']['contact']['TITLE']) && $key == $_SESSION['m_admin']['contact']['TITLE'] )
                                {
                                    echo 'selected="selected"';
                                }
                                ?>><?php echo $titles[$key];?></option><?php
                            }?>
                        </select></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="lastname_p" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><label for="lastname"><?php echo _LASTNAME; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><input name="lastname" type="text" id="lastname" value="<?php if(isset($_SESSION['m_admin']['contact']['LASTNAME'])){ echo $func->show_str($_SESSION['m_admin']['contact']['LASTNAME']);} ?>"/></td>
                        <td><span id="lastname_mandatory" class="red_asterisk" style="visibility:none;">*</span></td>
                    </tr>
                    <tr id="firstname_p" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><label for="firstname"><?php echo _FIRSTNAME; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><input name="firstname" type="text" id="firstname" value="<?php if(isset($_SESSION['m_admin']['contact']['FIRSTNAME'])){ echo $func->show_str($_SESSION['m_admin']['contact']['FIRSTNAME']);} ?>"/></td>
                        <td><span id="firstname_mandatory" class="red_asterisk" style="visibility:none;">*</span></td>
                    </tr>
                    <tr id="function_p" style="display:<?php if(isset($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON']) && $_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><label for="function"><?php echo _FUNCTION; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><input name="function" type="text" id="function" value="<?php if(isset($_SESSION['m_admin']['contact']['FUNCTION'])){echo $func->show_str($_SESSION['m_admin']['contact']['FUNCTION']);} ?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><?php echo _COMP_DATA; ?>&nbsp;:</td>
                        <td>&nbsp;</td>
                        <td class="indexing_field"><textarea name="comp_data" id="comp_data"><?php if(isset($_SESSION['m_admin']['contact']['OTHER_DATA'])){echo $func->show_str($_SESSION['m_admin']['contact']['OTHER_DATA']); }?></textarea></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
                        <input name="mode" type="hidden" value="<?php echo $mode; ?>" />
                        <br/>
                        <em>(<?php echo _YOU_SHOULD_ADD_AN_ADDRESS;?>)</em>
                    <p class="buttons">
                    <?php

                    if($mode == "up") { ?>
                        <input class="button" type="submit" name="Submit" value="<?php echo _MODIFY_CONTACT; ?>" />
                        <?php
                    }
                    elseif($mode == "add")
                    {
                        ?>
                        <input type="submit" class="button"  name="Submit" value="<?php echo _ADD_CONTACT; ?>" />
                        <?php
                    }
                    $cancel_target = $_SESSION['config']['businessappurl'].'index.php?page=contacts_v2';
                    if(!$admin) {
                        $cancel_target = $_SESSION['config']['businessappurl'].'index.php?page=my_contacts&amp;dir=my_contacts&amp;load';
                    }
                    if($iframe) { ?>    
                        <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="new Effect.BlindUp(parent.document.getElementById('create_contact_div'));new Effect.BlindUp(parent.document.getElementById('info_contact_div'));return false;" />
                    <?php
                    } else {
                        if ($mode == 'view') { ?>
                            <input type="button" class="button"  name="cancel" value="<?php echo _BACK_TO_RESULTS_LIST; ?>" onclick="history.go(-1);" />
                    <?php } else {
                                if (isset($_SESSION['fromContactTree']) && $_SESSION['fromContactTree'] == "yes"){
                                    ?><input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="self.close();" /><?php
                                    $_SESSION['fromContactTree'] = "";
                                } else {?>
                                    <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $cancel_target;?>';" />                 
                    <?php       }
                            }           
                    }
                    ?>
                    </p>
                </form>
            <?php
                if($mode=="up" && $admin)
                {
                    ?><script type="text/javascript">launch_autocompleter('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=users_autocomplete_list', 'owner', 'show_user');</script><?php
                }
            }
            ?>
        </div>
    <?php
    }

    public function chooseContact(){
        $this->connect();
        ?>
        <h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif" alt="" />
            <?php
                echo '&nbsp;' . _ADD_ADDRESS_TO_CONTACT;
            ?>
        </h1>
        <br/>
            <span style="margin-left:30px;">
                <?php echo '&nbsp;'. _ADD_ADDRESS_TO_CONTACT_DESC; ?>
            </span>
            <br/>
            <br/>
                <form class="forms" method="post" style="margin-left:30px;">
                    <table width="60%">
                        <tr>
                            <td><?php echo '&nbsp;'. _TYPE_OF_THE_CONTACT; ?></td>
                            <td>
                                <select id="contact_type_selected" onchange="getContacts('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=my_contacts&page=getContacts', this.options[this.selectedIndex].value, 'set');">
                                    <option value="all"><?php echo _ALL;?></option>
                                    <?php
                                        $this->query("SELECT id, label FROM contact_types ORDER BY label");
                                        while ($res_label = $this->fetch_object()){
                                            ?><option value="<?php echo $res_label->id;?>"><?php echo $res_label->label;?></option>
                                        <?php
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo '&nbsp;'. _WHICH_CONTACT; ?></td>
                            <td>                                
                                <select id="contactSelect">
                                    <option value=""><?php echo _CHOOSE_A_CONTACT;?></option>
                                    <?php
                                        $this->query("SELECT contact_id, society, firstname, lastname, is_corporate_person FROM contacts_v2 ORDER BY is_corporate_person desc, society, lastname");
                                        while ($res_contact = $this->fetch_object()){
                                            ?><option value="<?php echo $res_contact->contact_id;?>"><?php
                                            if ($res_contact->is_corporate_person == "Y") {
                                                echo $res_contact->society;
                                            } else if ($res_contact->is_corporate_person == "N") {
                                                echo $res_contact->lastname .' '. $res_contact->firstname;
                                            } ?>
                                            </option>
                                        <?php
                                        }
                                    ?>
                                </select>
                                <span class="red_asterisk">*</span>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <input class="button" type="button" value="<?php echo _CHOOSE_THIS_CONTACT; ?>" onclick="putInSessionContact('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=my_contacts&page=put_in_session');" />
                            </td>
                        </tr>
                    </table>
                </form>
            <!-- <input id="contactid" type="hidden"/> -->
            <?php

    }

    /**
    * Clear the session variables of the edmit 's administration
    *
    */
    private function clearcontactinfos()
    {
        // clear the session variable
        unset($_SESSION['m_admin']);
    }

    /**
    * Clear the session variables of the edmit 's administration
    *
    */
    private function clearaddressinfos()
    {
        // clear the session variable
        unset($_SESSION['m_admin']['address']);
    }
    
    /**
     * Loads all the contacts
     * 
     * @param 
     */
    public function loadContacts()
    {
        $contacts = array();
        $this->connect();
        $this->query('select * from '.$_SESSION['tablename']['contacts']);
        while($res = $this->fetch_object())
        {
            if($res->lastname <> '')
                array_push($contacts, "'".$res->lastname.", ".$res->firstname."[".$res->contact_id."]'");
            else
                array_push($contacts, "'".$res->society."[".$res->contact_id."]'");
        }
  
        return $contacts;
    }


    /**
    * delete a contact in the database
    *
    * @param string $id contact identifier
    */
    public function delcontact($id, $admin = true)
    {
        $element_found = false;
        $nb_docs = 0;
        $tables = array();
        $_SESSION['m_admin']['contact'] = array();
        $this->connect();
        $order = $_REQUEST['order'];
        $order_field = $_REQUEST['order_field'];
        $start = $_REQUEST['start'];
        $what = $_REQUEST['what'];
        $path_contacts = $_SESSION['config']['businessappurl']."index.php?page=contacts_v2&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
        if(!$admin)
        {
            $path_contacts = $_SESSION['config']['businessappurl']."index.php?page=my_contacts&dir=my_contacts&load&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
        }
        
        if(!empty($id))
        {
            $this->query("select res_id from ".$_SESSION['collections'][0]['view'] 
                . " where exp_contact_id = '".$this->protect_string_db($id) 
                . "' or dest_contact_id = '".$this->protect_string_db($id) . "'");
            // $this->show();
            if($this->nb_result() > 0)$nb_docs = $nb_docs + $this->nb_result();

                $this->query("select contact_id from contacts_res where contact_id = '". $this->protect_string_db($id)."'");
                if($this->nb_result() > 0)$nb_docs = $nb_docs + $this->nb_result();
/*            $this->query("select res_id from mlb_coll_ext 
                            where address_id in 
                                (select distinct id from ".$_SESSION['tablename']['contact_addresses'] 
                                . " where contact_id = '".$this->protect_string_db($id)."')"
                    );
            // $this->show();
            if($this->nb_result() > 0)$nb_docs_address = $nb_docs_address + $this->nb_result();*/
                         
            if ($nb_docs == 0)
            {
                $this->connect();
                $query = "select contact_id from ".$_SESSION['tablename']['contacts_v2']." where contact_id = ".$id;
                if(!$admin)
                {
                    $query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
                }
                $this->query($query);
                if($this->nb_result() == 0)
                {
                    $_SESSION['error'] = _CONTACT.' '._UNKNOWN;
                }
                else
                {
                    $res = $this->fetch_object();
                    $this->query("delete from " . $_SESSION['tablename']['contacts_v2'] . " where contact_id = " . $id);
                    $this->query("delete from " . $_SESSION['tablename']['contact_addresses'] . " where contact_id = " . $id);
                    if($_SESSION['history']['contactdel'])
                    {
                        require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
                        $hist = new history();
                        $hist->add($_SESSION['tablename']['contacts_v2'], $id,"DEL","contactdel",_CONTACT_DELETED.' : '.$id, $_SESSION['config']['databasetype']);
                        $hist->add($_SESSION['tablename']['contact_addresses'], $id,"DEL","contact_addresses_del", _ADDRESS_DEL." ".strtolower(_NUM).$id."", $_SESSION['config']['databasetype']);
                    }
                    $_SESSION['info'] = _CONTACT_DELETED;
                }
            }
            else
            {
                ?> 
                <br>
                <div id="main_error">
                    <b><?php
                        echo _WARNING_MESSAGE_DEL_CONTACT;
                    ?></b>
                </div>
                <br>
                <br>
                
                <h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_contact_b.gif" alt="" />
                    <?php echo _CONTACT_DELETION;?>
                </h1>
                
                <form name="entity_del" id="entity_del" method="post" class="forms">
                    <input type="hidden" value="<?php echo $id;?>" name="id">
                    <h2 class="tit"><?php echo _CONTACT_REAFFECT." : <i>".$label."</i>";?></h2>
                    <?php
                    if($nb_docs > 0)
                    {
                        echo "<br><b> - ".$nb_docs."</b> "._DOC_SENDED_BY_CONTACT;
                        
                        ?>
                        <br>
                        <br>
                        <input type="hidden" value="documents" name="documents">
                            <td>
                                <label for="contact_list"><?php echo _NEW_CONTACT; ?> : </label>
                            </td>
                            <td class="indexing_field">
                                <input name="contact_list" type="text"  id="contact_list" value=""/>
                                <div id="show_contact" class="autocomplete">
                                    <script type="text/javascript">
                                        initList_hidden_input('contact_list', 'show_contact', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contacts_v2_list_by_name&id=<?php echo $id; ?>', 'what', '2', 'contact');
                                    </script>
                                </div>
                                <input type="hidden" id="contact" name="contact" />
                            </td>
                        <br>
                        <br>
                            <td>
                                <label for="address_list"><?php echo _NEW_ADDRESS; ?> : </label>
                            </td>
                            <td class="indexing_field">
                                <input name="address_list" type="text"  id="address_list" value=""/>
                                <div id="show_address" class="autocomplete">
                                    <script type="text/javascript">
                                        initList_hidden_input_before('address_list', 'show_address', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_addresses_list_by_name', 'what', '2', 'address', 'idContact', 'contact');
                                    </script>
                                </div>
                                <input type="hidden" id="address" name="address" />
                            </td>
                            
                        <br/>                     
                        <br/>
                        <p class="buttons">
                            <input type="submit" value="<?php echo _DEL_AND_REAFFECT;?>" name="valid" class="button" onclick="return(confirm('<?php  echo _REALLY_DELETE;  if(isset($page_name) && $page_name == "users"){ echo $complete_name;} elseif(isset($admin_id)){ echo " ".$admin_id; }?> ?\n\r\n\r<?php  echo _DEFINITIVE_ACTION; ?>'));"/>
                            <input type="button" value="<?php echo _CANCEL;?>" onclick="window.location.href='<?php echo $path_contacts;?>';" class="button" />
                        </p>
                      <?php
                    }
                    ?>
                </form>
                <?php
                exit;
            }
        } else {
            $_SESSION['error'] = _CONTACT.' '._EMPTY;
        }
        
        ?>
        <script type="text/javascript">
            window.location.href="<?php echo $path_contacts;?>";
        </script>
        <?php
        exit;
    }

    function get_contact_information($res_id, $category_id,$view )
    //Get contact full information for each case: incoming document or outgoing document
    {
        $stopthis=false;
        $column_title = false;
        $column_value = false;
        $column_join = false;


        $this->connect();
        //For this 3 cases, we need to create a different string

        if ($category_id == 'incoming')
        {

            $prefix = "<b>"._TO_CONTACT_C."</b>";
            $this->query("SELECT exp_user_id, exp_contact_id from ".$view." WHERE res_id = ".$res_id);

            $compar = $this->fetch_object();


            if ($compar->exp_user_id <> '')
            {

                $column_title = "user_id";
                $column_value = $compar->exp_user_id;
                $column_join = $_SESSION['tablename']['users'];
            }
            elseif ($compar->exp_contact_id <> '')
            {

                $column_title = "contact_id";
                $column_value = $compar->exp_contact_id;
                $column_join = $_SESSION['tablename']['contacts'];
            }
            else
            {
                $stopthis = true;
            }
        }
        elseif ($category_id == 'outgoing'  || $category_id == 'internal')
        {
                $prefix = "<b>"._FOR_CONTACT_C."</b>";

                $this->query("SELECT dest_user_id, dest_contact_id from ".$view." WHERE res_id = ".$res_id);

                $compar = $this->fetch_object();
                if ($compar->dest_user_id <> '')
                {

                    $column_title = "user_id";
                    $column_value = $compar->dest_user_id;
                    $column_join = $_SESSION['tablename']['users'];
                }
                elseif ($compar->dest_contact_id <> '')
                {

                    $column_title = "contact_id";
                    $column_value = $compar->dest_contact_id;
                    $column_join = $_SESSION['tablename']['contacts'];
                }
                else
                {
                    $stopthis = true;
                }
        }
        else
        {
             $stopthis = true;
             $prefix = '';
        }
        if($stopthis == true)
        {
            return false;
        }

        //If we need to find a contact, get the society first
        if ($column_join == $_SESSION['tablename']['contacts'])
            $fields = 'c.firstname, c.lastname, c.society ';
        elseif ($column_join == $_SESSION['tablename']['users'])
            $fields = 'c.firstname, c.lastname';
        else
            $fields = '';

        //Launching request to restore full contact string
        $this->query("SELECT ".$fields." from ".$column_join." c    where ".$column_title." = '".$column_value."'");

        $final = $this->fetch_object();

        $firstname = $final->firstname;
        $lastname = $final->lastname;
        if ($final->society <> '')
        {
            if ($firstname =='' && $lastname == '')
            {
                $society = $final->society;
            }
            else
            {
                $society = " (".$final->society.") ";
            }
        }
        else
            $society = "";

        $the_contact =$prefix." ".$firstname." ".$lastname." ".$society;
        return $the_contact;

    }

    function get_contact_information_from_view($category_id, $contact_lastname="", $contact_firstname="", $contact_society="", $user_lastname="", $user_firstname="")
    {
        if ($category_id == 'incoming')
        {
            $prefix = "<b>"._TO_CONTACT_C."</b>";
        }
        elseif ($category_id == 'outgoing'  || $category_id == 'internal')
        {
            $prefix = "<b>"._FOR_CONTACT_C."</b>";
        }
        else {
            $prefix = '';
        }
        if($contact_lastname <> "")
        {
            $lastname = $contact_lastname;
            $firstname = $contact_firstname;
        }
        else
        {
            $lastname = $user_lastname;
            $firstname = $user_firstname;
        }
        if($contact_society <> "")
        {
            if ($firstname =='' && $lastname == '')
            {
                $society = $contact_society;
            }
            else
            {
                $society = " (".$contact_society.") ";
            }
        }
        else
            $society = "";
        $the_contact =$prefix." ".$firstname." ".$lastname." ".$society;
        return $the_contact;
    }

    /**
    * Form to modify or add an address v2
    *
    * @param  $string $mode up or add
    * @param int  $id  $id of the contact to change
    */
    public function formaddress($mode,$id = "", $admin = true, $iframe = "")
    {
        if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"]))
        {
            $browser_ie = true;
            $display_value = 'block';
        }
        elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $_SERVER["HTTP_USER_AGENT"]) )
        {
            $browser_ie = true;
            $display_value = 'block';
        }
        else
        {
            $browser_ie = false;
            $display_value = 'table-row';
        }
        $func = new functions();
        $state = true;
        if(!isset($_SESSION['m_admin']['address']) && !isset($_SESSION['m_admin']['contact']))
        {
            $this->clearcontactinfos();
        }
        if( $mode <> "add")
        {
            $this->connect();
            $query = "select * from ".$_SESSION['tablename']['contact_addresses']." where id = ".$id;
            $core_tools = new core_tools();
            if(!$admin && !$core_tools->test_service('update_contacts', 'apps', false))
            {
                $query .= " and user_id = '".$this->protect_string_db($_SESSION['user']['UserId'])."'";
            }
            $this->query($query);

            if($this->nb_result() == 0)
            {
                $_SESSION['error'] = _THE_ADDRESS.' '._ALREADY_EXISTS;
                $state = false;
            }
            else
            {
                $_SESSION['m_admin']['address'] = array();
                $line = $this->fetch_object();
                $_SESSION['m_admin']['address']['ID'] = $line->id;
                $_SESSION['m_admin']['address']['CONTACT_ID'] = $line->contact_id;
                $_SESSION['m_admin']['address']['TITLE'] = $this->show_string($line->title);
                $_SESSION['m_admin']['address']['LASTNAME'] = $this->show_string($line->lastname);
                $_SESSION['m_admin']['address']['FIRSTNAME'] = $this->show_string($line->firstname);
                $_SESSION['m_admin']['address']['FUNCTION'] = $this->show_string($line->function);
                $_SESSION['m_admin']['address']['OTHER_DATA'] = $this->show_string($line->other_data);
                $_SESSION['m_admin']['address']['OWNER'] = $line->user_id;
                $_SESSION['m_admin']['address']['DEPARTEMENT'] = $this->show_string($line->departement);
                $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] = $line->contact_purpose_id;
                $_SESSION['m_admin']['address']['OCCUPANCY'] = $this->show_string($line->occupancy);
                $_SESSION['m_admin']['address']['ADD_NUM'] = $this->show_string($line->address_num);
                $_SESSION['m_admin']['address']['ADD_STREET'] = $this->show_string($line->address_street);
                $_SESSION['m_admin']['address']['ADD_COMP'] = $this->show_string($line->address_complement);
                $_SESSION['m_admin']['address']['ADD_TOWN'] = $this->show_string($line->address_town);
                $_SESSION['m_admin']['address']['ADD_CP'] = $this->show_string($line->address_postal_code);
                $_SESSION['m_admin']['address']['ADD_COUNTRY'] = $this->show_string($line->address_country);
                $_SESSION['m_admin']['address']['PHONE'] = $this->show_string($line->phone);
                $_SESSION['m_admin']['address']['MAIL'] = $this->show_string($line->email);
                $_SESSION['m_admin']['address']['WEBSITE'] = $this->show_string($line->website);
                $_SESSION['m_admin']['address']['IS_PRIVATE'] = $this->show_string($line->is_private);
                $_SESSION['m_admin']['address']['SALUTATION_HEADER'] = $this->show_string($line->salutation_header);
                $_SESSION['m_admin']['address']['SALUTATION_FOOTER'] = $this->show_string($line->salutation_footer);
                if($admin && !empty($_SESSION['m_admin']['address']['OWNER']))
                {
                    $this->query("select lastname, firstname from ".$_SESSION['tablename']['users']." where user_id = '".$_SESSION['m_admin']['address']['OWNER']."'");
                    $res = $this->fetch_object();
                    $_SESSION['m_admin']['address']['OWNER'] = $res->lastname.', '.$res->firstname.' ('.$_SESSION['m_admin']['address']['OWNER'].')';
                }
            }
        }
        else if($mode == 'add' && !isset($_SESSION['m_admin']['address']['IS_PRIVATE']))
        {
            $_SESSION['m_admin']['address']['IS_PRIVATE'] = 'N';
        }
        require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
        $business = new business_app_tools();
        $tmp = $business->get_titles();
        $titles = $tmp['titles'];

        $contact_purposes = array();
        $this->connect();
        $this->query("SELECT id, label FROM ".$_SESSION['tablename']['contact_purposes']);
        while($res = $this->fetch_object()){
            $contact_purposes[$res->id] = $this->show_string($res->label); 
        }

        ?>
        <h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_add_b.gif" alt="" />
            <?php
            if($mode == "up")
            {
                echo _MODIFY_ADDRESS;
            }
            elseif($mode == "add")
            {
                echo _ADDITION_ADDRESS;
            }
            ?>
        </h1>
        <div id="inner_content_contact" class="clearfix" align="center">
            <?php
            if($state == false)
            {
                echo "<br /><br /><br /><br />"._THE_ADDRESS." "._UNKOWN."<br /><br /><br /><br />";
            }
            else
            {
                $this->get_contact_form();
                $action = $_SESSION['config']['businessappurl']."index.php?display=true&page=contact_addresses_up_db";
                if(!$admin)
                {
                    $action = $_SESSION['config']['businessappurl']."index.php?display=true&page=contact_addresses_up_db&mycontact=Y";
                }
                if($iframe == "iframe"){
                    $action = $_SESSION['config']['businessappurl']."index.php?display=false&page=contact_addresses_up_db&mycontact=iframe";
                } else if($iframe == "iframe_add_up") {
                    $action = $_SESSION['config']['businessappurl']."index.php?display=false&page=contact_addresses_up_db&mycontact=iframe_add_up";
                }
                ?>
                <form name="frmcontact" id="frmcontact" method="post" action="<?php echo $action;?>" class="forms">
                    <input type="hidden" name="display"  value="true" />
                    <?php if(!$admin)
                    {?>
                        <input type="hidden" name="dir"  value="my_contacts" />
                        <input type="hidden" name="page"  value="my_contact_up_db" />
                <?php   }
                    else
                    {?>
                        <input type="hidden" name="admin"  value="contacts_v2_up" />
                        <input type="hidden" name="page"  value="contact_addresses_up_db" />
                <?php   }?>
                    <input type="hidden" name="order" id="order" value="<?php if(isset($_REQUEST['order'])) {echo $_REQUEST['order'];}?>" />
                    <input type="hidden" name="order_field" id="order_field" value="<?php if(isset($_REQUEST['order_field'])) { echo $_REQUEST['order_field'];}?>" />
                    <input type="hidden" name="what" id="what" value="<?php if(isset($_REQUEST['what'])){echo $_REQUEST['what'];}?>" />
                    <input type="hidden" name="start" id="start" value="<?php if(isset($_REQUEST['start'])){ echo $_REQUEST['start'];}?>" />
                <table width="65%">
                    <tr align="left">
                        <td colspan="4" onclick="new Effect.toggle('address_div', 'blind', {delay:0.2});
                        whatIsTheDivStatus('address_div', 'divStatus_address_div');"><label>
                            <span id="divStatus_address_div" style="color:#1C99C5;">>></span>&nbsp;
                            <b><?php echo _ADDRESS;?> </b></label>
                        </td>
                    </tr>
                </table>
                <div id="address_div"  style="display:inline">
                    <table width="65%" id="frmaddress_table1">
                        <tr id="contact_purposes_tr" >
                            <td><label for="contact_purposes"><?php echo _CONTACT_PURPOSE; ?>&nbsp;:&nbsp;</label>
<!--                                 <a href="#" id="create_contact" title="<?php echo _NEW_CONTACT_PURPOSE_ADDED; ?>" 
                                    onclick="javascript:window.open('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=false&page=contact_purposes_up&mode=popup','', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=550,height=250');" style="display:inline;" >
                                    <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=modif_liste.png" alt="<?php echo _NEW_CONTACT_PURPOSE_ADDED; ?>"/>
                                </a> -->
                            </td>
                            <td>&nbsp;</td>
                            <td class="indexing_field">
                                                        <!-- <select name="contact_purposes" id="contact_purposes" >
                                                            <option value=""><?php echo _CHOOSE_CONTACT_PURPOSES;?></option>
                                                            <?php
                                                            foreach(array_keys($contact_purposes) as $key)
                                                            {
                                                                ?><option value="<?php echo $key;?>" <?php

                                                                if(isset($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID']) && $key == $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] )
                                                                {
                                                                    echo 'selected="selected"';
                                                                }
                                                                ?>><?php echo $contact_purposes[$key];?></option><?php
                                                            }?>
                                                        </select> -->
                                <input name="new_id" id="new_id" onblur="purposeCheck();";
                                    <?php if(isset($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID']) && $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] <> '')
                                        {
                                            echo 'value="'.$this->get_label_contact($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'],$_SESSION['tablename']['contact_purposes']).'"';
                                        } 
                                    ?>
                                />
                                <div id="show_contact" class="autocomplete">
                                    <script type="text/javascript">
                                        initList_hidden_input('new_id', 'show_contact', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_purposes_list_by_name&id=<?php echo $id; ?>', 'what', '2', 'contact_purposes');
                                    </script>
                                </div>
                                <input type="hidden" id="contact_purposes" name="contact_purposes"                             
                                    <?php if(isset($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID']) && $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] <> '')
                                        {
                                            echo 'value="'.$_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'].'"';
                                        } 
                                    ?>
                                />
                            </td>
                            <td class="indexing_field"><span class="red_asterisk" style="visibility:visible;" id="contact_purposes_mandatory">*</span></td>
                        </tr> 
                        <tr id="purpose_to_create" style="display:none">
                            <td colspan="4">
                                <em><?php echo _CONTACT_PURPOSE_WILL_BE_CREATED;?></em>
                            </td>
                        </tr>
                        
                        <tr id="departement_p" style="display:<?php if($_SESSION['m_admin']['address']['DEPARTEMENT'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                            <td><label for="departement"><?php echo _SERVICE; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="departement" type="text"  id="departement" value="<?php if(isset($_SESSION['m_admin']['address']['DEPARTEMENT'])){ echo $func->show_str($_SESSION['m_admin']['address']['DEPARTEMENT']);} ?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr id="title_p" style="display:<?php if($_SESSION['m_admin']['address']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                            <td><label for="title"><?php echo _TITLE2; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><select name="title" id="title" >
                                <option value=""><?php echo _CHOOSE_TITLE;?></option>
                                <?php
                                foreach(array_keys($titles) as $key)
                                {
                                    ?><option value="<?php echo $key;?>" <?php

                                    if((!isset($_SESSION['m_admin']['address']['TITLE']) || empty($_SESSION['m_admin']['address']['TITLE']))&& $key == $_SESSION['default_mail_title'])
                                    {
                                         echo 'selected="selected"';
                                    }
                                    elseif(isset($_SESSION['m_admin']['address']['TITLE']) && $key == $_SESSION['m_admin']['address']['TITLE'] )
                                    {
                                        echo 'selected="selected"';
                                    }
                                    ?>><?php echo $titles[$key];?></option><?php
                                }?>
                            </select></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr id="lastname_p" >
                            <td><label for="lastname"><?php echo _LASTNAME; ?> : </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="lastname" type="text"  id="lastname" value="<?php if(isset($_SESSION['m_admin']['address']['LASTNAME'])){ echo $func->show_str($_SESSION['m_admin']['address']['LASTNAME']);} ?>"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr id="firstname_p" >
                            <td><label for="firstname"><?php echo _FIRSTNAME; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="firstname" type="text"  id="firstname" value="<?php if(isset($_SESSION['m_admin']['address']['FIRSTNAME'])){ echo $func->show_str($_SESSION['m_admin']['address']['FIRSTNAME']);} ?>"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr id="function_p" >
                            <td><label for="function"><?php echo _FUNCTION; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="function" type="text"  id="function" value="<?php if(isset($_SESSION['m_admin']['address']['FUNCTION'])){echo $func->show_str($_SESSION['m_admin']['address']['FUNCTION']);} ?>"/></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><?php echo _OCCUPANCY; ?> : </td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="occupancy" type="text"  id="occupancy" value="<?php if(isset($_SESSION['m_admin']['address']['OCCUPANCY'])){echo $func->show_str($_SESSION['m_admin']['address']['OCCUPANCY']); }?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="num"><?php echo _NUM; ?> : </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="num" type="text"  id="num" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_NUM'])){echo $func->show_str($_SESSION['m_admin']['address']['ADD_NUM']); }?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="street"><?php echo _STREET; ?> : </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="street" type="text"  id="street" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_STREET'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_STREET']); }?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="add_comp"><?php echo _COMPLEMENT; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="add_comp" type="text"  id="add_comp" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_COMP'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_COMP']); }?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="cp"><?php echo _POSTAL_CODE; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="cp" type="text" id="cp" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_CP'])){echo $func->show_str($_SESSION['m_admin']['address']['ADD_CP']); }?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="town"><?php echo _TOWN; ?> : </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="town" type="text" id="town" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_TOWN'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_TOWN']);} ?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="country"><?php echo _COUNTRY; ?> : </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="country" type="text"  id="country" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_COUNTRY'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_COUNTRY']); }?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr >
                            <td><label for="phone"><?php echo _PHONE; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="phone" type="text"  id="phone" value="<?php if(isset($_SESSION['m_admin']['address']['PHONE'])){echo $func->show_str($_SESSION['m_admin']['address']['PHONE']);} ?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="mail"><?php echo _MAIL; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="mail" type="text" id="mail" value="<?php if(isset($_SESSION['m_admin']['address']['MAIL'])){ echo $func->show_str($_SESSION['m_admin']['address']['MAIL']);} ?>"/></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><label for="website"><?php echo _WEBSITE; ?>&nbsp;: </label></td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><input name="website" type="text" id="website" value="<?php if(isset($_SESSION['m_admin']['address']['WEBSITE'])){ echo $func->show_str($_SESSION['m_admin']['address']['WEBSITE']);} ?>"/></td>
                            <td>&nbsp;</td>
                        </tr>   
                        <tr>
                            <td><?php echo _COMP_DATA; ?>&nbsp;: </td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><textarea name="comp_data"   id="comp_data"><?php if(isset($_SESSION['m_admin']['address']['OTHER_DATA'])){echo $func->show_str($_SESSION['m_admin']['address']['OTHER_DATA']); }?></textarea></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><?php echo _IS_PRIVATE; ?>&nbsp;: </td>
                            <td>&nbsp;</td>
                            <td class="indexing_field">
                                <input type="radio"  class="check" name="is_private" value="Y" <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
                                <input type="radio"  class="check" name="is_private" value="N" <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'N' OR $_SESSION['m_admin']['address']['IS_PRIVATE'] <> 'Y'){?> checked="checked"<?php } ?> /><?php echo _NO;?>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="4"><?php echo _HELP_PRIVATE; ?></td>
                        </tr>
                    </table>
                </div>
                    <table width="65%">
                        <tr align="left">
                            <td colspan="4" onclick="new Effect.toggle('salutation_div', 'blind', {delay:0.2});
                        whatIsTheDivStatus('salutation_div', 'divStatus_salutation_div');"><label>
                            <span id="divStatus_salutation_div" style="color:#1C99C5;">>></span>&nbsp;<b><?php echo _SALUTATION;?> </b></label></td>
                        </tr>
                    </table>
                <div id="salutation_div">
                    <table width="65%" id="frmaddress_table2">
                        <tr>
                            <td><?php echo _SALUTATION_HEADER; ?> : </td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><textarea name="salutation_header" id="salutation_header"><?php if(isset($_SESSION['m_admin']['address']['SALUTATION_HEADER'])){echo $func->show_str($_SESSION['m_admin']['address']['SALUTATION_HEADER']); }?></textarea></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                        <tr>
                            <td><?php echo _SALUTATION_FOOTER; ?> : </td>
                            <td>&nbsp;</td>
                            <td class="indexing_field"><textarea name="salutation_footer" id="salutation_footer"><?php if(isset($_SESSION['m_admin']['address']['SALUTATION_FOOTER'])){echo $func->show_str($_SESSION['m_admin']['address']['SALUTATION_FOOTER']); }?></textarea></td>
                            <td class="indexing_field"><span class="blue_asterisk" style="visibility:visible;">*</span></td>
                        </tr>
                    </table>
                </div>
                        <input name="mode" type="hidden" value="<?php echo $mode; ?>" />
                    <p class="buttons">
                    <?php

                    if($mode == "up")
                    {
                        ?>
                        <input class="button" type="submit" name="Submit" value="<?php echo _EDIT_ADDRESS; ?>" />
                        <?php
                    }
                    elseif($mode == "add")
                    {
                        ?>
                        <input type="submit" class="button"  name="Submit" value="<?php echo _ADD_ADDRESS; ?>" />
                        <?php
                    }
                    $cancel_target = $_SESSION['config']['businessappurl'].'index.php?page=contacts_v2_up';
                    if(!$admin)
                    {
                        $cancel_target = $_SESSION['config']['businessappurl'].'index.php?page=my_contact_up&amp;dir=my_contacts&amp;load';
                    }
                    if($iframe == 'iframe')
                    {
                        $cancel_target = $_SESSION['config']['businessappurl'].'index.php?display=false&page=create_contact_iframe&dir=my_contacts';
                    } else if($iframe == 'iframe_add_up'){
                        $cancel_target = $_SESSION['config']['businessappurl'].'index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid='.$_SESSION['contact']['current_contact_id'].'&addressid='.$_SESSION['contact']['current_address_id'];
                    }
                    ?>
                    <input type="button" class="button"  name="cancel" value="<?php echo _CANCEL; ?>" onclick="javascript:window.location.href='<?php echo $cancel_target;?>';" />
                    </p>
                </form>
            <?php
                if($mode=="up" && $admin)
                {
                    ?><script type="text/javascript">launch_autocompleter('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=users_autocomplete_list', 'owner', 'show_user');</script><?php
                }
            }
            ?>
        </div>
    <?php
    }

    /**
    * Add ou modify address in the database
    *
    * @param string $mode up or add
    */
    public function addupaddress($mode, $admin = true, $iframe = false)
    {
        // add ou modify users in the database
        $this->addressinfo($mode);
        $order = $_SESSION['m_admin']['address']['order'];
        $order_field = $_SESSION['m_admin']['address']['order_field'];
        $what = $_SESSION['m_admin']['address']['what'];
        $start = $_SESSION['m_admin']['address']['start'];

        $path_contacts = $_SESSION['config']['businessappurl']
                       . 'index.php?page=contacts_v2_up&order='
                       . $order . '&order_field=' . $order_field . '&start='
                       . $start . '&what=' . $what;
        $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                  . 'index.php?page=contact_addresses_add';
        $path_contacts_up_errors = $_SESSION['config']['businessappurl']
                                 . 'index.php?page=contact_addresses_up';
        if (! $admin) {
            $path_contacts = $_SESSION['config']['businessappurl']
                           . 'index.php?dir=my_contacts&page=my_contact_up&load&order='
                           . $order . '&order_field=' . $order_field . '&start='
                           . $start . '&what=' . $what;
            $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                      . 'index.php?page=contact_addresses_add&mycontact=Y';
            $path_contacts_up_errors = $_SESSION['config']['businessappurl']
                                     . 'index.php?page=contact_addresses_up&mycontact=Y';
        }
        if ($iframe) {
            if($mode == 'add') {
                if($iframe == 1){
                    $path_contacts = $_SESSION['config']['businessappurl']
                                              . 'index.php?display=false&dir=my_contacts&page=create_contact_iframe&created=Y';
                    $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                              . 'index.php?display=false&dir=my_contacts&page=create_address_iframe';
                } else if($iframe == 2) {
                    $path_contacts = $_SESSION['config']['businessappurl']
                                          . 'index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid='.$_SESSION['contact']['current_contact_id'].'&addressid='.$_SESSION['contact']['current_address_id'];
                    $path_contacts_add_errors = $_SESSION['config']['businessappurl']
                                              . 'index.php?display=false&dir=my_contacts&page=create_address_iframe&iframe=iframe_up_add';
                }
            } else if($mode == 'up') {
                $path_contacts = $_SESSION['config']['businessappurl']
                                          . 'index.php?display=false&dir=my_contacts&page=info_contact_iframe&contactid='.$_SESSION['contact']['current_contact_id'].'&addressid='.$_SESSION['contact']['current_address_id'];
                $path_contacts_up_errors = $_SESSION['config']['businessappurl']
                                          . 'index.php?display=false&dir=my_contacts&page=update_address_iframe';
            }

        }
        if (! empty($_SESSION['error'])) {
            if ($mode == 'up') {
                if (! empty($_SESSION['m_admin']['address']['ID'])) {
                    header(
                        'location: ' . $path_contacts_up_errors . '&id='
                        . $_SESSION['m_admin']['address']['ID']
                    );
                    exit;
                } else {
                    header('location: ' . $path_contacts);
                    exit;
                }
            }
            if ($mode == 'add') {
                header('location: ' . $path_contacts_add_errors);
                exit;
            }
        } else {
            $this->connect();
            if ($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] == "") {
                $this->query("INSERT INTO contact_purposes (label) VALUES ('".$this->protect_string_db($_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'])."')");
                $this->query("SELECT id FROM contact_purposes WHERE label = '".$this->protect_string_db($_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'])."'");
                $res_purpose = $this->fetch_object();
                $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] = $res_purpose->id;
            } else if($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] <> "" && $_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'] <> ""){
                $this->query("SELECT id FROM contact_purposes WHERE label = '".$this->protect_string_db($_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'])."'");
                $res_purpose = $this->fetch_object();
                if ($res_purpose->id != $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID']) {
                    $this->query("INSERT INTO contact_purposes (label) VALUES ('".$this->protect_string_db($_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'])."')");
                    $this->query("SELECT id FROM contact_purposes WHERE label = '".$this->protect_string_db($_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'])."'");
                    $res_purpose = $this->fetch_object();
                    $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] = $res_purpose->id;
                }
            }
            if ($mode == 'add') {
                if($_SESSION['user']['UserId'] == 'superadmin'){
                    $entity_id = 'SUPERADMIN';
                } else {
                    $entity_id = $_SESSION['user']['primaryentity']['id'];
                }
                $query = 'INSERT INTO ' . $_SESSION['tablename']['contact_addresses']
                        . ' (  contact_id, contact_purpose_id, departement, lastname , firstname , function , '
                        . 'phone , email , address_num, address_street, '
                        . 'address_complement, address_town, '
                        . 'address_postal_code, address_country, other_data,'
                        . " title, is_private, website, occupancy, user_id, entity_id, salutation_header, salutation_footer) VALUES (  "
                        .   $_SESSION['contact']['current_contact_id']
                        . ", " .  $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID']
                        . ", '" . $this->protect_string_db(
                           $_SESSION['m_admin']['address']['DEPARTEMENT']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['LASTNAME']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['FIRSTNAME']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['FUNCTION']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['PHONE']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['MAIL']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['ADD_NUM']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['ADD_STREET']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['ADD_COMP']
                        ) . "', '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['ADD_TOWN']
                        ) . "',  '" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['ADD_CP']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['ADD_COUNTRY']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['OTHER_DATA']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['TITLE']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['IS_PRIVATE']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['WEBSITE']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['OCCUPANCY']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['user']['UserId']
                        ) . "','" . $this->protect_string_db(
                            $entity_id
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['SALUTATION_HEADER']
                        ) . "','" . $this->protect_string_db(
                            $_SESSION['m_admin']['address']['SALUTATION_FOOTER']
                        ) . "' )";

                $this->query($query);
                if($_SESSION['history']['addressadd'])
                {
                    $this->query("select id from ".$_SESSION['tablename']['contact_addresses']." where lastname = '".$this->protect_string_db($_SESSION['m_admin']['address']['LASTNAME'])."' and firstname = '".$this->protect_string_db($_SESSION['m_admin']['address']['FIRSTNAME'])."' and society = '".$this->protect_string_db($_SESSION['m_admin']['address']['SOCIETY'])."' and function = '".$this->protect_string_db($_SESSION['m_admin']['address']['FUNCTION'])."' and is_corporate_person = '".$this->protect_string_db($_SESSION['m_admin']['address']['IS_CORPORATE_PERSON'])."'");
                    $res = $this->fetch_object();
                    $id = $res->contact_id;
                    if($_SESSION['m_admin']['address']['IS_CORPORATE_PERSON'] == 'Y')
                    {
                        $msg =  _ADDRESS_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['address']['SOCIETY']);
                    }
                    else
                    {
                        $msg =  _ADDRESS_ADDED.' : '.$this->protect_string_db($_SESSION['m_admin']['address']['LASTNAME'].' '.$_SESSION['m_admin']['address']['FIRSTNAME']);
                    }
                    require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
                    $hist = new history();
                    $hist->add($_SESSION['tablename']['contact_addresses'], $id,"ADD",'contact_addresses_add',$msg, $_SESSION['config']['databasetype']);
                }

                if($iframe){
                    $this->clearcontactinfos();
                }

                $this->clearaddressinfos();
                $_SESSION['info'] = _ADDRESS_ADDED;
                header("location: ".$path_contacts);
                exit;
            }
            elseif($mode == "up")
            {
                $query = "update ".$_SESSION['tablename']['contact_addresses']." 
                      set contact_purpose_id = '".$_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID']."'
                        , departement = '".$this->protect_string_db($_SESSION['m_admin']['address']['DEPARTEMENT'])."'
                        , firstname = '".$this->protect_string_db($_SESSION['m_admin']['address']['FIRSTNAME'])."'
                        , lastname = '".$this->protect_string_db($_SESSION['m_admin']['address']['LASTNAME'])."'
                        , title = '".$this->protect_string_db($_SESSION['m_admin']['address']['TITLE'])."'
                        , function = '".$this->protect_string_db($_SESSION['m_admin']['address']['FUNCTION'])."'
                        , phone = '".$this->protect_string_db($_SESSION['m_admin']['address']['PHONE'])."'
                        , email = '".$this->protect_string_db($_SESSION['m_admin']['address']['MAIL'])."'
                        , occupancy = '".$this->protect_string_db($_SESSION['m_admin']['address']['OCCUPANCY'])."'
                        , address_num = '".$this->protect_string_db($_SESSION['m_admin']['address']['ADD_NUM'])."'
                        , address_street = '".$this->protect_string_db($_SESSION['m_admin']['address']['ADD_STREET'])."'
                        , address_complement = '".$this->protect_string_db($_SESSION['m_admin']['address']['ADD_COMP'])."'
                        , address_town = '".$this->protect_string_db($_SESSION['m_admin']['address']['ADD_TOWN'])."'
                        , address_postal_code = '".$this->protect_string_db($_SESSION['m_admin']['address']['ADD_CP'])."'
                        , address_country = '".$this->protect_string_db($_SESSION['m_admin']['address']['ADD_COUNTRY'])."'
                        , website = '".$this->protect_string_db($_SESSION['m_admin']['address']['WEBSITE'])."'
                        , other_data = '".$this->protect_string_db($_SESSION['m_admin']['address']['OTHER_DATA'])."'
                        , is_private = '".$this->protect_string_db($_SESSION['m_admin']['address']['IS_PRIVATE'])."'
                        , salutation_header = '".$this->protect_string_db($_SESSION['m_admin']['address']['SALUTATION_HEADER'])."'
                        , salutation_footer = '".$this->protect_string_db($_SESSION['m_admin']['address']['SALUTATION_FOOTER'])."'";

                $query .=" where id = ".$_SESSION['m_admin']['address']['ID'];

                $this->query($query);
                if($_SESSION['history']['contactup'])
                {
                    $msg =  _ADDRESS_EDITED.' : '.$this->protect_string_db($_SESSION['m_admin']['address']['SOCIETY']).' '.$this->protect_string_db($_SESSION['m_admin']['address']['LASTNAME'].' '.$_SESSION['m_admin']['address']['FIRSTNAME']);
                    require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_history.php');
                    $hist = new history();
                    $hist->add($_SESSION['tablename']['contacts_v2'], $_SESSION['m_admin']['address']['ID'],"UP",'contacts_v2_up',$msg, $_SESSION['config']['databasetype']);
                }
                $this->clearcontactinfos();
                $_SESSION['info'] = _ADDRESS_EDITED;
                header("location: ".$path_contacts);
                exit();
            }
        }
    }

    /**
    * Return the address data in sessions vars
    *
    * @param string $mode add or up
    */
    public function addressinfo($mode)
    {
        // return the user information in sessions vars
        $func = new functions();
        if ($_REQUEST['title'] <> '') {
            $_SESSION['m_admin']['address']['TITLE'] = $func->wash(
                $_REQUEST['title'], 'no', _TITLE2 . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['TITLE'] = '';
        }

        if ($_REQUEST['contact_purposes'] <> '') {
            $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] = $func->wash(
                $_REQUEST['contact_purposes'], 'no', _CONTACT_PURPOSE . ' ', 'yes', 0, 255
            );
        }  else {
            $_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'] = '';
        }

        $_SESSION['m_admin']['address']['CONTACT_PURPOSE_NAME'] = $func->wash(
            $_REQUEST['new_id'], 'no', _CONTACT_PURPOSE . ' ', 'yes', 0, 255
        );


        if ($_REQUEST['departement'] <> '') {
            $_SESSION['m_admin']['address']['DEPARTEMENT'] = $func->wash(
                $_REQUEST['departement'], 'no', _DEPARTEMENT . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['DEPARTEMENT'] = '';
        }

        if ($_REQUEST['lastname'] <> '') {
            $_SESSION['m_admin']['address']['LASTNAME'] = $func->wash(
                $_REQUEST['lastname'], 'no', _LASTNAME . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['LASTNAME'] = '';
        }

        if ($_REQUEST['firstname'] <> '') {
            $_SESSION['m_admin']['address']['FIRSTNAME'] = $func->wash(
                $_REQUEST['firstname'], 'no', _FIRSTNAME . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['FIRSTNAME'] = '';
        }

        if ($_REQUEST['function'] <> '') {
            $_SESSION['m_admin']['address']['FUNCTION'] = $func->wash(
                $_REQUEST['function'], 'no', _FUNCTION . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['FUNCTION'] = '';
        }

        if ($_REQUEST['num'] <> '') {
            $_SESSION['m_admin']['address']['ADD_NUM'] = $func->wash(
                $_REQUEST['num'], 'no', _NUM . ' ', 'yes', 0, 32
            );
        } else {
            $_SESSION['m_admin']['address']['ADD_NUM'] = '';
        }

        if ($_REQUEST['street'] <> '') {
            $_SESSION['m_admin']['address']['ADD_STREET'] = $func->wash(
                $_REQUEST['street'], 'no', _STREET . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['ADD_STREET'] = '';
        }

        if ($_REQUEST['add_comp'] <> '') {
            $_SESSION['m_admin']['address']['ADD_COMP'] = $func->wash(
                $_REQUEST['add_comp'], 'no', ADD_COMP . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['ADD_COMP'] = '';
        }

        if ($_REQUEST['town'] <> '') {
            $_SESSION['m_admin']['address']['ADD_TOWN'] = $func->wash(
                $_REQUEST['town'], 'no', _TOWN . ' ', 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['ADD_TOWN'] = '';
        }
        if ($_REQUEST['cp'] <> '') {
            $_SESSION['m_admin']['address']['ADD_CP'] = $func->wash(
                $_REQUEST['cp'], 'no', _POSTAL_CODE, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['ADD_CP'] = '';
        }
        if ($_REQUEST['country'] <> '') {
            $_SESSION['m_admin']['address']['ADD_COUNTRY'] = $func->wash(
                $_REQUEST['country'], 'no', _COUNTRY, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['ADD_COUNTRY'] = '';
        }
        if ($_REQUEST['phone'] <> '') {
            $_SESSION['m_admin']['address']['PHONE'] = $func->wash(
                $_REQUEST['phone'], 'num', _PHONE, 'yes', 0, 20
            );
        } else {
            $_SESSION['m_admin']['address']['PHONE'] = '';
        }
        if ($_REQUEST['mail'] <> '') {
            $_SESSION['m_admin']['address']['MAIL'] = $func->wash(
                $_REQUEST['mail'], 'mail', _MAIL, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['MAIL'] = '';
        }
        if ($_REQUEST['comp_data'] <> '') {
            $_SESSION['m_admin']['address']['OTHER_DATA'] = $func->wash(
                $_REQUEST['comp_data'], 'no', _COMP_DATA, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['OTHER_DATA'] = '';
        }
        if ($_REQUEST['website'] <> '') {
            $_SESSION['m_admin']['address']['WEBSITE'] = $func->wash(
                $_REQUEST['website'], 'no', _WEBSITE, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['WEBSITE'] = '';
        }
        if ($_REQUEST['occupancy'] <> '') {
            $_SESSION['m_admin']['address']['OCCUPANCY'] = $func->wash(
                $_REQUEST['occupancy'], 'no', _OCCUPANCY, 'yes', 0, 1024
            );
        } else {
            $_SESSION['m_admin']['address']['occupancy'] = '';
        }
        if ($_REQUEST['salutation_header'] <> '') {
            $_SESSION['m_admin']['address']['SALUTATION_HEADER'] = $func->wash(
                $_REQUEST['salutation_header'], 'no', _SALUTATION_HEADER, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['SALUTATION_HEADER'] = '';
        }
        if ($_REQUEST['salutation_footer'] <> '') {
            $_SESSION['m_admin']['address']['SALUTATION_FOOTER'] = $func->wash(
                $_REQUEST['salutation_footer'], 'no', _SALUTATION_FOOTER, 'yes', 0, 255
            );
        } else {
            $_SESSION['m_admin']['address']['SALUTATION_FOOTER'] = '';
        }
         $_SESSION['m_admin']['address']['IS_PRIVATE'] =
            $_REQUEST['is_private'];

        if (isset($_REQUEST['owner']) && $_REQUEST['owner'] <> '') {
            if (preg_match('/\((.|\s|\d|\h|\w)+\)$/i', $_REQUEST['owner']) == 0) {
                $_SESSION['error'] = _OWNER . ' ' . _WRONG_FORMAT . '.<br/>'
                                   . _USE_AUTOCOMPLETION;
            } else {
                $_SESSION['m_admin']['address']['OWNER'] = str_replace(
                    ')', '', substr($_REQUEST['owner'],
                    strrpos($_REQUEST['owner'],'(')+1)
                );
                $_SESSION['m_admin']['address']['OWNER'] = $func->wash(
                    $_SESSION['m_admin']['address']['OWNER'], 'no',
                    _OWNER . ' ', 'yes', 0, 32
                );
            }
        } else {
            $_SESSION['m_admin']['address']['OWNER'] = '';
        }

        $_SESSION['m_admin']['address']['order'] = $_REQUEST['order'];
        $_SESSION['m_admin']['address']['order_field'] = $_REQUEST['order_field'];
        $_SESSION['m_admin']['address']['what'] = $_REQUEST['what'];
        $_SESSION['m_admin']['address']['start'] = $_REQUEST['start'];
    }

    /**
    * Return the label from an id
    *
    * @param int $contact_type_id
    * @param string $table
    */
    public function get_label_contact($contact_type_id, $table){
        $this->connect();
        $this->query('select label from '.$table . ' where id = '.$contact_type_id);
        $res = $this->fetch_object();
        return $this->show_string($res->label);
    }

    public function type_purpose_address_del($id, $admin = true, $tablename, $mode='contact_type', $deleted_sentence, $warning_sentence, $title, $reaffect_sentence, $new_sentence, $choose_sentence, $page_return, $page_del, $name){
        $nb_elements = 0;
        $this->connect();
        $order = $_REQUEST['order'];
        $order_field = $_REQUEST['order_field'];
        $start = $_REQUEST['start'];
        $what = $_REQUEST['what'];
        $path = $_SESSION['config']['businessappurl']."index.php?page=".$page_return."&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
        $path_del = $_SESSION['config']['businessappurl']."index.php?page=".$page_del."&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
        if(!$admin)
        {
            if ($mode == 'contact_address'){
                $path = $_SESSION['config']['businessappurl']."index.php?page=my_contact_up&dir=my_contacts&load&order=".$order."&order_field=".$order_field."&start=".$start."&what=".$what;
            }
        }
        
        if(!empty($id))
        {
            if ($mode == 'contact_type') {
                $this->query("select contact_id from ".$_SESSION['tablename']['contacts_v2'] 
                . " where contact_type = ". $id );
            } else if ($mode == 'contact_purpose'){
                $this->query("select id from ".$_SESSION['tablename']['contact_addresses']
                    . " where contact_purpose_id = ". $id );
            } else if ($mode == 'contact_address'){
                $this->query("select address_id from mlb_coll_ext where address_id = ". $id );
            }
            
            if($this->nb_result() > 0)$nb_elements = $nb_elements + $this->nb_result();
            // $this->show(); 
            if ($mode == 'contact_address'){
                $this->query("select address_id from contacts_res where address_id = ". $id );
                if($this->nb_result() > 0)$nb_elements = $nb_elements + $this->nb_result();
            }
                         
            if ($nb_elements == 0)
            {
                $this->query("DELETE FROM ".$tablename." where id = ".$id);

                if($_SESSION['history'][$page_del] == "true")
                {
                    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
                    $users = new history();
                    $users->add($tablename, $id,"DEL",$page_del, $title." ".strtolower(_NUM).$id."", $_SESSION['config']['databasetype']);
                }

                $_SESSION['error'] = $deleted_sentence;

                unset($_SESSION['m_admin']);
                ?>
                    <script type="text/javascript">
                        window.location.href="<?php echo $path;?>";
                    </script>   
                <?php

            }
            else
            {

                ?> 
                <br>
                <div id="main_error">
                    <b><?php
                        echo $warning_sentence;                       
                    ?></b>
                </div>
                <br>
                <br>
                
                <h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_contact_b.gif" alt="" />
                    <?php
                        echo $title; 
                    ?>
                </h1>
                
                <form name="contact_type_del" id="contact_type_del" method="post" class="forms" action="<?php echo $path_del?>">
                    <input type="hidden" value="<?php echo $id;?>" name="id">
                    <h2 class="tit"><?php echo $reaffect_sentence; " : <i>".$label."</i>";?></h2>
                    <?php
                    if($nb_elements > 0)
                    {
                        if ($mode == 'contact_type') {
                            echo "<br><b> - ".$nb_elements."</b> "._CONTACTS;
                        } else if ($mode == 'contact_purpose'){
                            echo "<br><b> - ".$nb_elements."</b> "._ADDRESSES;
                        } else if ($mode == 'contact_address'){
                            echo "<br><b> - ".$nb_elements."</b> "._DOC_S;
                        }                                              
                    ?>
                        <br>
                        <br>
                        <td>
                            <label for="contact"><?php echo $new_sentence; ?> : </label>
                        </td>
                        <td class="indexing_field">
                            <?php 
                            if($mode == 'contact_address'){
                                ?> <input name="new_id" id="new_id" value=""/>
                                    <div id="show_contact" class="autocomplete">
                                        <script type="text/javascript">
                                            initList_hidden_input('new_id', 'show_contact', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_addresses_list_by_name&id=<?php echo $id; ?>', 'what', '2', 'new');
                                        </script>
                                    </div>
                                    <input type="hidden" id="new" name="new" />
                                <?php
                            } else if($mode == 'contact_purpose'){
                                ?> <input name="new_id" id="new_id" value=""/>
                                    <div id="show_contact" class="autocomplete">
                                        <script type="text/javascript">
                                            initList_hidden_input('new_id', 'show_contact', '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_purposes_list_by_name&id=<?php echo $id; ?>', 'what', '2', 'new');
                                        </script>
                                    </div>
                                    <input type="hidden" id="new" name="new" />
                                <?php
                            }else{
                                $this->query("select id, label from ".$tablename." where id <> ".$id);

                                while ($res = $this->fetch_object()) {
                                    $array[$res->id] = $this->protect_string_db($res->label);
                                }
                            ?>
                                <select name="new" id="new">
                                    <option value=""><?php echo $choose_sentence; ?></option>
                                    <?php
                                    foreach($array as $key => $label){
                                        ?><option value="<?php echo $key;?>">
                                            <?php echo $label;
                                        ?></option><?php
                                    }
                                    ?>
                                </select>
                            <?php 
                            } ?>
                        </td>
                            
                        <br/>
                      
                    <br/>
                    <p class="buttons">
                        <input type="submit" value="<?php echo _DEL_AND_REAFFECT;?>" name="valid" class="button" onclick="return(confirm('<?php  echo _REALLY_DELETE;  if(isset($page_name) && $page_name == "users"){ echo $complete_name;} elseif(isset($admin_id)){ echo " ".$admin_id; }?> ?\n\r\n\r<?php  echo _DEFINITIVE_ACTION; ?>'));"/>
                        <input type="button" value="<?php echo _CANCEL;?>" onclick="window.location.href='<?php echo $path;?>';" class="button" />
                    </p>
                      <?php
                    }
                    ?>
                </form>
                <?php
                exit;
            }
        } else {
            $_SESSION['error'] = $name.' '._EMPTY;
        }
        
        ?>
        <script type="text/javascript">
            window.location.href="<?php echo $path;?>";
        </script>
        <?php
        exit;        
    }

    /**
    * Contact form with every field disabled
    *
    */
    public function get_contact_form(){

        $func = new functions();
        $business = new business_app_tools();
        ?>
        <form class="forms">
            <table width="65%">
                <tr align="left">
                    <td colspan="4" onclick="new Effect.toggle('info_contact_div', 'blind', {delay:0.2});
                    whatIsTheDivStatus('info_contact_div', 'divStatus_contact_div');"><label>
                        <span id="divStatus_contact_div" style="color:#1C99C5;">>></span>&nbsp;<b><?php echo _CONTACT;?></b></label>
                    </td>
                </tr>
            </table>
            <div id="info_contact_div" style="display:inline">
                <table width="65%" >
                    <tr >
                        <td><?php echo _IS_CORPORATE_PERSON; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right">
                            <input disabled type="radio"  class="check" name="is_corporate"  value="Y" <?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
                            <input disabled type="radio"  class="check" name="is_corporate" value="N" <?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'N'){?> checked="checked"<?php } ?> /><?php echo _NO;?>
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr id="contact_types_tr" >
                        <td><?php echo _CONTACT_TYPE; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="contact_types" type="text"  id="contact_types" value="<?php if(isset($_SESSION['m_admin']['contact']['CONTACT_TYPE'])){ echo $this->get_label_contact($_SESSION['m_admin']['contact']['CONTACT_TYPE'], $_SESSION['tablename']['contact_types']); }?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><?php echo _STRUCTURE_ORGANISM; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="society" type="text"  id="society" value="<?php if(isset($_SESSION['m_admin']['contact']['SOCIETY'])){ echo $func->show_str($_SESSION['m_admin']['contact']['SOCIETY']); }?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><?php echo _SOCIETY_SHORT; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="society_short" type="text"  id="society_short" value="<?php if(isset($_SESSION['m_admin']['contact']['SOCIETY_SHORT'])){ echo $func->show_str($_SESSION['m_admin']['contact']['SOCIETY_SHORT']); }?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="title_p" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><?php echo _TITLE2; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="title" type="text"  id="title" value="<?php if(isset($_SESSION['m_admin']['contact']['TITLE'])){ echo $business->get_label_title($_SESSION['m_admin']['contact']['TITLE']); }?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="lastname_p" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><?php echo _LASTNAME; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="lastname" type="text"  id="lastname" value="<?php if(isset($_SESSION['m_admin']['contact']['LASTNAME'])){ echo $func->show_str($_SESSION['m_admin']['contact']['LASTNAME']);} ?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="firstname_p" style="display:<?php if($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><?php echo _FIRSTNAME; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="firstname" type="text"  id="firstname" value="<?php if(isset($_SESSION['m_admin']['contact']['FIRSTNAME'])){ echo $func->show_str($_SESSION['m_admin']['contact']['FIRSTNAME']);} ?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr id="function_p" style="display:<?php if(isset($_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON']) && $_SESSION['m_admin']['contact']['IS_CORPORATE_PERSON'] == 'Y'){ echo 'none';}else{ echo $display_value;}?>">
                        <td><?php echo _FUNCTION; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="function" type="text"  id="function" value="<?php if(isset($_SESSION['m_admin']['contact']['FUNCTION'])){echo $func->show_str($_SESSION['m_admin']['contact']['FUNCTION']);} ?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><?php echo _COMP_DATA; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><textarea disabled name="comp_data"   id="comp_data"><?php if(isset($_SESSION['m_admin']['contact']['OTHER_DATA'])){echo $func->show_str($_SESSION['m_admin']['contact']['OTHER_DATA']); }?></textarea></td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
        </form>
        <?php
    }

    public function get_address_form(){
        $func = new functions();
        $business = new business_app_tools();
        ?>
        <form class="forms">
            <table width="65%">
                <tr align="left">
                    <td colspan="4" onclick="new Effect.toggle('address_div', 'blind', {delay:0.2});
                    whatIsTheDivStatus('address_div', 'divStatus_address_div');"><label>
                        <span id="divStatus_address_div" style="color:#1C99C5;">>></span>&nbsp;<b><?php echo _ADDRESS;?></b></label>
                    </td>
                </tr>
            </table>
            <div id="address_div"  style="display:inline">
                <table width="65%" >
        <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'N'){ ?>
                    <tr id="contact_purposes_tr" >
                        <td><label for="contact_purposes"><?php echo _CONTACT_PURPOSE; ?>&nbsp;: </label>
                        </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right">
                            <input disabled name="new_id" id="new_id" value="<?php echo $this->get_label_contact($_SESSION['m_admin']['address']['CONTACT_PURPOSE_ID'], $_SESSION['tablename']['contact_purposes']);?>"/>
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;</td>
                    </tr>                    
                    <tr id="departement_p" >
                        <td><label for="departement"><?php echo _SERVICE; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="departement" type="text"  id="departement" value="<?php if(isset($_SESSION['m_admin']['address']['DEPARTEMENT'])){ echo $func->show_str($_SESSION['m_admin']['address']['DEPARTEMENT']);} ?>"/></td>
                    </tr>
            <?php } ?>                     
                    <tr id="title_p" >
                        <td><?php echo _TITLE2; ?> : </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled disabled name="title" type="text"  id="title" value="<?php if(isset($_SESSION['m_admin']['contact']['TITLE'])){ echo $business->get_label_title($_SESSION['m_admin']['contact']['TITLE']); }?>"/></td>
                    </tr>
                    <tr id="lastname_p" >
                        <td><label for="lastname"><?php echo _LASTNAME; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="lastname" type="text"  id="lastname" value="<?php if(isset($_SESSION['m_admin']['address']['LASTNAME'])){ echo $func->show_str($_SESSION['m_admin']['address']['LASTNAME']);} ?>"/></td>
                    </tr>
                    <tr id="firstname_p" >
                        <td><label for="firstname"><?php echo _FIRSTNAME; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="firstname" type="text"  id="firstname" value="<?php if(isset($_SESSION['m_admin']['address']['FIRSTNAME'])){ echo $func->show_str($_SESSION['m_admin']['address']['FIRSTNAME']);} ?>"/></td>
                    </tr>
                    <tr id="function_p" >
                        <td><label for="function"><?php echo _FUNCTION; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="function" type="text"  id="function" value="<?php if(isset($_SESSION['m_admin']['address']['FUNCTION'])){echo $func->show_str($_SESSION['m_admin']['address']['FUNCTION']);} ?>"/></td>
                    </tr>
        <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'N'){ ?>
                    <tr>
                        <td><?php echo _OCCUPANCY; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="occupancy" type="text"  id="occupancy" value="<?php if(isset($_SESSION['m_admin']['address']['OCCUPANCY'])){echo $func->show_str($_SESSION['m_admin']['address']['OCCUPANCY']); }?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="num"><?php echo _NUM; ?> : </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="num" type="text"  id="num" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_NUM'])){echo $func->show_str($_SESSION['m_admin']['address']['ADD_NUM']); }?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="street"><?php echo _STREET; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="street" type="text"  id="street" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_STREET'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_STREET']); }?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="add_comp"><?php echo _COMPLEMENT; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="add_comp" type="text"  id="add_comp" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_COMP'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_COMP']); }?>"/></td>
                    </tr>
                    <tr>
                        <td><?php echo _POSTAL_CODE; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="cp" type="text" id="cp" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_CP'])){echo $func->show_str($_SESSION['m_admin']['address']['ADD_CP']); }?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="town"><?php echo _TOWN; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="town" type="text" id="town" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_TOWN'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_TOWN']);} ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="country"><?php echo _COUNTRY; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="country" type="text"  id="country" value="<?php if(isset($_SESSION['m_admin']['address']['ADD_COUNTRY'])){ echo $func->show_str($_SESSION['m_admin']['address']['ADD_COUNTRY']); }?>"/></td>
                    </tr>
                    <tr >
                        <td><label for="phone"><?php echo _PHONE; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="phone" type="text"  id="phone" value="<?php if(isset($_SESSION['m_admin']['address']['PHONE'])){echo $func->show_str($_SESSION['m_admin']['address']['PHONE']);} ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="mail"><?php echo _MAIL; ?>&nbsp;: </label></td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="mail" type="text" id="mail" value="<?php if(isset($_SESSION['m_admin']['address']['MAIL'])){ echo $func->show_str($_SESSION['m_admin']['address']['MAIL']);} ?>"/></td>
                    </tr>
            <?php } ?>            
                    <tr>
                        <td><?php echo _WEBSITE; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><input disabled name="website" type="text" id="website" value="<?php if(isset($_SESSION['m_admin']['address']['WEBSITE'])){ echo $func->show_str($_SESSION['m_admin']['address']['WEBSITE']);} ?>"/></td>
                    </tr>   
                    <tr>
                        <td><?php echo _COMP_DATA; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><textarea disabled name="comp_data"   id="comp_data"><?php if(isset($_SESSION['m_admin']['address']['OTHER_DATA'])){echo $func->show_str($_SESSION['m_admin']['address']['OTHER_DATA']); }?></textarea></td>
                    </tr>
                    <tr>
                        <td><?php echo _IS_PRIVATE; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right">
                            <input type="radio" disabled class="check" name="is_private" value="Y" <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'Y'){?> checked="checked"<?php } ?> /><?php echo _YES;?>
                            <input type="radio" disabled class="check" name="is_private" value="N" <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'N' OR $_SESSION['m_admin']['address']['IS_PRIVATE'] <> 'Y'){?> checked="checked"<?php } ?> /><?php echo _NO;?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php if($_SESSION['m_admin']['address']['IS_PRIVATE'] == 'N'){ ?>
                <table width="65%">
                    <tr align="left">
                        <td colspan="4" onclick="new Effect.toggle('salutation_div', 'blind', {delay:0.2});
                    whatIsTheDivStatus('salutation_div', 'divStatus_salutation_div');"><label>
                        <span id="divStatus_salutation_div" style="color:#1C99C5;">>></span>&nbsp;<b><?php echo _SALUTATION; ?></b></label></td>
                    </tr>
                </table>
            <div id="salutation_div" style="display:inline">
                <table width="65%">
                    <tr>
                        <td><?php echo _SALUTATION_HEADER; ?>&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><textarea disabled name="salutation_header" id="salutation_header"><?php if(isset($_SESSION['m_admin']['address']['SALUTATION_HEADER'])){echo $func->show_str($_SESSION['m_admin']['address']['SALUTATION_HEADER']); }?></textarea></td>
                        <td>&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td><?php echo _SALUTATION_FOOTER; ?>&nbsp;: </td>
                        <td>&nbsp;</td>
                        <td class="indexing_field" align="right"><textarea disabled name="salutation_footer" id="salutation_footer"><?php if(isset($_SESSION['m_admin']['address']['SALUTATION_FOOTER'])){echo $func->show_str($_SESSION['m_admin']['address']['SALUTATION_FOOTER']); }?></textarea></td>
                    </tr>
                </table>
            </div>
            <?php } ?>
        </form>
    <?php
    }

}
?>
