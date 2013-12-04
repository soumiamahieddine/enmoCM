<?php
/**
* File : contact_info.php
*
* Pop up used to view info of a contact
*
* @package  Maarch Framework 3.0
* @version 3.0
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni <dev@maarch.org>
* @author  Claire Figueras <dev@maarch.org>
*/

require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
	. DIRECTORY_SEPARATOR  . 'class' . DIRECTORY_SEPARATOR 
	. 'class_business_app_tools.php';
$core = new core_tools();
$business = new business_app_tools();
$core->load_lang();
$core->load_html();
$core->load_header('', true, false);
$func = new functions();
$db = new dbquery();
$db->connect();
$tmp = $business->get_titles();
$titles = $tmp['titles'];
$defaultTitle = $tmp['default_title'];
if ($_REQUEST['id'] == "" && $_REQUEST['mode'] == 'view') {
    echo '<script type="text/javascript">window.resizeTo(300, 150);</script>';
    echo '<br/><br/><center>'._YOU_MUST_SELECT_CONTACT.'</center><br/><br/><div align="center">
        <input name="close" type="button" value="'._CLOSE.'"  onclick="self.close();" class="button" />
        </div>';
    exit();
}
if (!empty($_REQUEST['submit'])) {
    $contact['ID'] = $_REQUEST['contact_id'];

    $contact['IS_CORPORATE_PERSON'] = $_REQUEST['is_corporate'];

    if ($contact['IS_CORPORATE_PERSON'] == 'Y') {
        $contact['SOCIETY'] = $func->wash(
        	$_REQUEST['society'], "no", _SOCIETY." "
        );
        $contact['LASTNAME'] = '';
    } else {
        $contact['LASTNAME'] = $func->wash(
        	$_REQUEST['lastname'], "no", _LASTNAME
        );
        if ($_REQUEST['society'] <> '') {
            $contact['SOCIETY'] = $func->wash(
            	$_REQUEST['society'], "no", _SOCIETY." "
            );
        } else {
            $contact['SOCIETY'] = '';
        }
    }
    if ($_REQUEST['title'] <> '') {
        $contact['TITLE'] = $func->wash(
        	$_REQUEST['title'], "no", _TITLE2." "
        );
    } else {
        $contact['TITLE'] = '';
    }
    if ($_REQUEST['firstname'] <> '') {
        $contact['FIRSTNAME'] = $func->wash(
        	$_REQUEST['firstname'], "no", _FIRSTNAME." "
        );
    } else {
        $contact['FIRSTNAME'] = '';
    }
    if ($_REQUEST['function'] <> '') {
        $contact['FUNCTION'] = $func->wash(
        	$_REQUEST['function'], "no", _FUNCTION." "
        );
    } else {
        $contact['FUNCTION'] = '';
    }
    if ($_REQUEST['num'] <> '') {
        $contact['ADD_NUM'] = $func->wash($_REQUEST['num'], "no", _NUM." ");
    } else {
        $contact['ADD_NUM'] = '';
    }
    if ($_REQUEST['street'] <> '') {
        $contact['ADD_STREET'] = $func->wash(
        	$_REQUEST['street'], "no", _STREET." "
        );
    } else {
        $contact['ADD_STREET'] = '';
    }
    if ($_REQUEST['add_comp'] <> '') {
        $contact['ADD_COMP'] = $func->wash(
        	$_REQUEST['add_comp'], "no", ADD_COMP." "
        );
    } else {
        $contact['ADD_COMP'] = '';
    }
    if ($_REQUEST['town'] <> '') {
        $contact['ADD_TOWN'] = $func->wash($_REQUEST['town'], "no", _TOWN." ");
    } else {
        $contact['ADD_TOWN'] = '';
    }
    if ($_REQUEST['cp'] <> '') {
        $contact['ADD_CP'] = $func->wash($_REQUEST['cp'], "no", _POSTAL_CODE);
    } else {
        $contact['ADD_CP'] = '';
    }
    if ($_REQUEST['country'] <> '') {
        $contact['ADD_COUNTRY'] = $func->wash(
        	$_REQUEST['country'], "no", _COUNTRY
        );
    } else {
        $contact['ADD_COUNTRY'] = '';
    }
    if ($_REQUEST['phone'] <> '') {
        $contact['PHONE'] = $func->wash($_REQUEST['phone'], "no", _PHONE);
    } else {
        $contact['PHONE'] = '';
    }
    if ($_REQUEST['mail'] <> '') {
        $contact['MAIL'] = $func->wash($_REQUEST['mail'], "mail", _MAIL);
    } else {
        $contact['MAIL'] = '';
    }
    if ($_REQUEST['comp_data'] <> '') {
        $contact['OTHER_DATA'] = $func->wash(
        	$_REQUEST['comp_data'], "no", _COMP_DATA
        );
    } else {
        $contact['OTHER_DATA'] = '';
    }
    if ($_REQUEST['contact_type'] <> '') {
        $contact['CONTACT_TYPE'] = $_REQUEST['contact_type'];
    } else {
        $contact['CONTACT_TYPE'] = 'letter';
    }
    if ($_REQUEST['is_private'] <> '') {
        $contact['IS_PRIVATE'] = $_REQUEST['is_private'];
    } else {
        $contact['IS_PRIVATE'] = 'N';
    }
    
    if (!empty($_SESSION['error'])) {
        //
    } else {
        if(isset($contact['ID'])) {
            $db->query(
                "UPDATE " . $_SESSION['tablename']['contacts'] . " SET "
                . "lastname = '".$func->protect_string_db($contact['LASTNAME'])."', "
                . "firstname = '".$func->protect_string_db($contact['FIRSTNAME'])."', "
                . "society = '".$func->protect_string_db($contact['SOCIETY'])."', "
                . "function = '".$func->protect_string_db($contact['FUNCTION'])."', "
                . "phone = '".$func->protect_string_db($contact['PHONE'])."', "
                . "email = '".$func->protect_string_db($contact['MAIL'])."', "
                . "address_num = '".$func->protect_string_db($contact['ADD_NUM'])."', "
                . "address_street = '".$func->protect_string_db($contact['ADD_STREET'])."', "
                . "address_complement = '".$func->protect_string_db($contact['ADD_COMP'])."', "
                . "address_town = '".$func->protect_string_db($contact['ADD_TOWN'])."', "
                . "address_postal_code = '".$func->protect_string_db($contact['ADD_CP'])."', "
                . "address_country = '".$func->protect_string_db($contact['ADD_COUNTRY'])."', "
                . "other_data = '".$func->protect_string_db($contact['OTHER_DATA'])."', "
                . "title = '".$func->protect_string_db($contact['TITLE'])."', "
                . "is_corporate_person = '".$func->protect_string_db($contact['IS_CORPORATE_PERSON'])."', "
                . "user_id = '".$func->protect_string_db($_SESSION['user']['UserId'])."', "
                . "is_private = '".$func->protect_string_db($contact['IS_PRIVATE'])."'"
                . " WHERE contact_id = " . $contact['ID']);
        } else {
            if ($contact['IS_CORPORATE_PERSON'] == 'Y') {
                $db->query(
                    "INSERT INTO " . $_SESSION['tablename']['contacts']
                    . " (society, phone, email, address_num, address_street, "
                    . "address_complement, address_town, address_postal_code, "
                    . "address_country, other_data, is_corporate_person, user_id, is_private)"
                    . " values ('" . $func->protect_string_db($contact['SOCIETY'])
                    . "', '" . $func->protect_string_db($contact['PHONE']) . "', '"
                    . $func->protect_string_db($contact['MAIL']) . "', '"
                    . $func->protect_string_db($contact['ADD_NUM']) . "','"
                    . $func->protect_string_db($contact['ADD_STREET']) . "', '"
                    . $func->protect_string_db($contact['ADD_COMP']) . "', '"
                    . $func->protect_string_db($contact['ADD_TOWN']) . "', '"
                    . $func->protect_string_db($contact['ADD_CP']) . "', '"
                    . $func->protect_string_db($contact['ADD_COUNTRY']) . "', '"
                    . $func->protect_string_db($contact['OTHER_DATA']) . "', '"
                    . $func->protect_string_db($contact['IS_CORPORATE_PERSON']) . "', '" 
                    . $func->protect_string_db($_SESSION['user']['UserId']) . "', '" 
                    . $func->protect_string_db($contact['IS_PRIVATE']). "')"
                );
            } else {
                $db->query(
                    "INSERT INTO " . $_SESSION['tablename']['contacts']
                    . " (lastname , firstname , society , function , phone , email,"
                    . " address_num, address_street, address_complement, "
                    . "address_town, address_postal_code, address_country,"
                    . " other_data, title, is_corporate_person, user_id, contact_type, is_private) values ('"
                    . $func->protect_string_db($contact['LASTNAME']) . "', '"
                    . $func->protect_string_db($contact['FIRSTNAME']) . "', '"
                    . $func->protect_string_db($contact['SOCIETY']) . "', '"
                    . $func->protect_string_db($contact['FUNCTION']) . "', '"
                    . $func->protect_string_db($contact['PHONE']) . "', '"
                    . $func->protect_string_db($contact['MAIL']) . "', '"
                    . $func->protect_string_db($contact['ADD_NUM']) . "','"
                    . $func->protect_string_db($contact['ADD_STREET']) . "', '"
                    . $func->protect_string_db($contact['ADD_COMP']) . "', '"
                    . $func->protect_string_db($contact['ADD_TOWN']) . "',  '"
                    . $func->protect_string_db($contact['ADD_CP']) . "','"
                    . $func->protect_string_db($contact['ADD_COUNTRY']) . "','"
                    . $func->protect_string_db($contact['OTHER_DATA']) . "','"
                    . $func->protect_string_db($contact['TITLE']) . "','"
                    . $func->protect_string_db($contact['IS_CORPORATE_PERSON']) . "','" 
                    . $func->protect_string_db($_SESSION['user']['UserId']) . "', '" 
                    . $func->protect_string_db($contact['CONTACT_TYPE']). "', '"
                    . $func->protect_string_db($contact['IS_PRIVATE']). "')"
                );
            }
        }
        if ($contact['IS_CORPORATE_PERSON'] == 'N') {
            $db->query(
            	"select contact_id, lastname, firstname, society from "
            	. $_SESSION['tablename']['contacts'] . " where lastname = '"
            	. $func->protect_string_db($contact['LASTNAME'])
            	. "' and firstname = '" . $func->protect_string_db($contact['FIRSTNAME']) . "' and enabled = 'Y' order by contact_id desc"
            );
            $res = $db->fetch_object();
            if (empty($res->society)) {
                $value_contact = $res->lastname.', '.$res->firstname.' ('.$res->contact_id.')';
            }
            else
            {
                $value_contact = $res->society.', '.$res->lastname.' '.$res->firstname.' ('.$res->contact_id.')';
            }
        }
        else
        {
            $db->query("select contact_id, society from ".$_SESSION['tablename']['contacts']." where society = '".$func->protect_string_db($contact['SOCIETY'])."' and enabled = 'Y' order by contact_id desc");
            $res = $db->fetch_object();
            $value_contact = $res->society.' ('.$res->contact_id.')';
        }
    ?>
    <script type="text/javascript">
    var contact = window.opener.$('contact');
    if(contact)
    {
        contact.value = '<?php echo $value_contact;?>';
         window.opener.$('contact_card').style.visiblity = 'visible';
    }
    self.close();
    </script>
    <?php
		echo "EXIT!";
        exit();
    }
}

if ($_REQUEST['id'] == '') {
    $_REQUEST['id'] = 0;
}

$readonly = true;
$is_private = false;
$is_personal = false;
$is_owner = false;
$db->query("select * from ".$_SESSION['tablename']['contacts']." where contact_id = ".$_REQUEST['id']."  ");
if($db->nb_result() == 0) {
    $_SESSION['error'] = _CONTACT.' '._NOT_EXISTS;
    $state = false;
}
else
{
    $contact_info = array();
    $line = $db->fetch_object();
    
    if($line->is_private == 'Y') { 
        $is_private = true;
    }
    
    if($line->user_id != '') {
        $is_personal = true;
        if($line->user_id == $_SESSION['user']['UserId']) {
            $is_owner = true;
        }
    }
    
    $contact_info['ID'] = $line->contact_id;
    $contact_info['IS_CORPORATE_PERSON'] = $line->is_corporate_person;
    $contact_info['TITLE'] = $line->title;
    $contact_info['TITLE_LABEL'] = $business->get_label_title($line->title);
    $contact_info['LASTNAME'] = $func->show_string($line->lastname);
    $contact_info['FIRSTNAME'] = $func->show_string($line->firstname);
    $contact_info['SOCIETY'] = $func->show_string($line->society);
    $contact_info['FUNCTION'] = $func->show_string($line->function);
    $contact_info['IS_PRIVATE'] = $func->show_string($line->is_private);
    $contact['CONTACT_TYPE'] = $func->show_string($line->contact_type);
    if(!$is_private || $is_owner) { 
        $contact_info['ADD_NUM'] = $func->show_string($line->address_num);
        $contact_info['ADD_STREET'] = $func->show_string($line->address_street);
        $contact_info['ADD_COMP'] = $func->show_string($line->address_complement);
        $contact_info['ADD_TOWN'] = $func->show_string($line->address_town);
        $contact_info['ADD_CP'] = $func->show_string($line->address_postal_code);
        $contact_info['ADD_COUNTRY'] = $func->show_string($line->address_country);
        $contact_info['PHONE'] = $func->show_string($line->phone);
        $contact_info['MAIL'] = $func->show_string($line->email);
        $contact_info['OTHER_DATA'] = $func->show_string($line->other_data);
    } 
}


if($_REQUEST['mode'] == 'update') {
    if(!$is_personal && !$is_private) {
        $readonly = false;
    } elseif($is_personal && $is_owner) {
        $readonly = false;
    }
} else if($_REQUEST['mode'] == 'add') {
    $readonly = false;
    $contact_info['IS_CORPORATE_PERSON'] == 'Y';
}

//echo "<pre>"; var_dump($contact_info); echo "</pre>";
$core->load_js();
?>
<script type="text/javascript">window.resizeTo(600, 570);</script>
<br/>

<div id="contacts" style="padding-left: 20px;">
    <!--?php echo "<pre>"; var_dump($contact_info); echo "</pre>"; ?-->
    <h2 align="center"><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=my_contacts_off.gif" alt="<?php echo _CONTACT_INFO;?>" /> <?php echo _CONTACT_INFO;?></h2>  <br/>
    <form name="frmcontact" id="frmcontact" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_info" class="forms addforms">
        <input type="hidden" name="display" value="true" />
        <input type="hidden" name="page" value="contact_info" />
        <input name="contact_id" type="hidden" id="contact_id" value="<?php echo $contact_info['ID']; ?>" />
		<input name="id" type="hidden" id="id" value="<?php echo $contact_info['ID']; ?>" />
        <div class='error'><?php echo $_SESSION['error']; ?></div>
		<p>
            <label for="is_corporate"><?php echo _IS_CORPORATE_PERSON; ?> : </label>
            <?php if($readonly){
                if($contact_info['IS_CORPORATE_PERSON'] == 'Y'){echo _YES;}else{echo _NO;}
            } else
            {?>
            <input type="radio"  class="check" name="is_corporate" value="Y" <?php if($contact_info['IS_CORPORATE_PERSON'] == 'Y') echo 'checked="checked"'; ?> onclick="javascript:show_admin_contacts(true);"/><?php echo _YES;?>
            <input type="radio"  class="check" name="is_corporate" value="N" <?php if($contact_info['IS_CORPORATE_PERSON'] == 'N') echo 'checked="checked"'; ?> onclick="javascript:show_admin_contacts(false);"/><?php echo _NO;?>
            <?php } ?>
        </p>
         <p id="title_p" style="<?php if($contact_info['IS_CORPORATE_PERSON'] == 'Y') echo 'display:none;'; else echo 'display:inline;';?>">
            <label for="title"><?php echo _TITLE2; ?> : </label>
            <?php if($readonly) {
                ?><input name="title" type="text"  id="title" value="<?php echo $func->show_str($contact_info['TITLE_LABEL']); ?>" readonly="readonly"/><?php
            } else
            {?>
            <select name="title" id="title">
                <option value=""><?php echo _CHOOSE_TITLE;?></option>
                <?php foreach(array_keys($titles) as $key)
                {
                    ?><option value="<?php echo $key;?>" <?php if($contact_info['TITLE'] == $key) echo 'selected="selected"';?> ><?php echo $titles[$key];?></option><?php
                }?>
            </select>
            <?php }?>

         </p>
         <p id="lastname_p" style="<?php if($contact_info['IS_CORPORATE_PERSON'] == 'Y') echo 'display:none;'; else echo 'display:inline;'; ?>">
            <label for="label"><?php echo _LASTNAME; ?> : </label>
            <input name="lastname" type="text"  id="lastname" value="<?php echo $func->show_str($contact_info['LASTNAME']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?> /> <?php if(!$readonly){?><span class="red_asterisk" id="lastname_mandatory" style="display:none;">*</span><?php } ?>
         </p>
         <p id="firstname_p" style="<?php if($contact_info['IS_CORPORATE_PERSON'] == 'Y') echo 'display:none;'; else echo 'display:inline ;'; ?>">
            <label for="label"><?php echo _FIRSTNAME; ?> : </label>
            <input name="firstname" type="text"  id="firstname" value="<?php echo $func->show_str($contact_info['FIRSTNAME']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
         </p>
         <p >
            <label for="label"><?php echo _SOCIETY; ?> : </label>
            <input name="society" type="text"  id="society" value="<?php echo $func->show_str($contact_info['SOCIETY']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/> <?php if(!$readonly){?><span class="red_asterisk" id="society_mandatory" style="display:inline;">*</span><?php } ?>
         </p>
          <p id="function_p" style="<?php if($contact_info['IS_CORPORATE_PERSON'] == 'Y')  echo 'display:none;'; else echo 'display:inline;'; ?>">
            <label for="label"><?php echo _FUNCTION; ?> : </label>
            <input name="function" type="text"  id="function" value="<?php echo $func->show_str($contact_info['FUNCTION']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
         </p>
         <p>
            <label for="is_private"><?php echo _IS_PRIVATE; ?> : </label>
            <?php if($readonly){
                 if($contact_info['IS_PRIVATE'] == 'Y'){echo _YES;}else{echo _NO;}
                 }
            else
            {?>
            <input type="radio"  class="check" name="is_private" value="Y" <?php if($contact_info['IS_PRIVATE'] == 'Y'){?> checked="checked"<?php }elseif(empty($contact['IS_PRIVATE'])){ ?> checked="checked"<?php } ?> /><?php echo _YES;?>
            <input type="radio"  class="check" name="is_private" value="N" <?php if($contact_info['IS_PRIVATE'] == 'N'){?> checked="checked"<?php } ?> /><?php echo _NO;
            }?>
        </p>
        <p>
            <label for="label"><?php echo _PHONE; ?> : </label>
            <input name="phone" type="text"  id="phone" value="<?php echo $func->show_str($contact_info['PHONE']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p>
            <label for="label"><?php echo _MAIL; ?> : </label>
            <input name="mail" type="text" id="mail" value="<?php echo $func->show_str($contact_info['MAIL']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p >
            <label><b><?php echo _ADDRESS;?> </b></label>
        </p>
        <br/>
        <p>
            <label for="label"><?php echo _NUM; ?> : </label>
            <input name="num" type="text"  id="num" value="<?php echo $func->show_str($contact_info['ADD_NUM']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p>
            <label for="label"><?php echo _STREET; ?> : </label>
            <input name="street" type="text"  id="street" value="<?php echo $func->show_str($contact_info['ADD_STREET']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p>
            <label for="label"><?php echo _COMPLEMENT; ?> : </label>
            <input name="add_comp" type="text"  id="add_comp" value="<?php echo $func->show_str($contact_info['ADD_COMP']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
         </p>
        <p>
            <label for="label"><?php echo _TOWN; ?> : </label>
            <input name="town" type="text" id="town" value="<?php echo $func->show_str($contact_info['ADD_TOWN']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p>
            <label for="label"><?php echo _POSTAL_CODE; ?> : </label>
            <input name="cp" type="text" id="cp" value="<?php echo $func->show_str($contact_info['ADD_CP']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p>
            <label for="label"><?php echo _COUNTRY; ?> : </label>
            <input name="country" type="text"  id="country" value="<?php echo $func->show_str($contact_info['ADD_COUNTRY']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p><label><b><?php echo _COMP;?> </b></label></p>
        <br/>
        <p>
            <label for="label"><?php echo _COMP_DATA; ?>  </label>
            <textarea name="comp_data"   id="comp_data" <?php if($readonly){ echo 'readonly="readonly"';}?>><?php echo $func->show_str($contact_info['OTHER_DATA']); ?></textarea>
        </p>
        <p>
            <label for="label"><?php echo _CONTACT_TYPE; ?>  </label>
            <input name="contact_type" type="text"  id="contact_type" value="<?php echo $func->show_str($contact['CONTACT_TYPE']); ?>" <?php if($readonly){ echo 'readonly="readonly"';}?>/>
        </p>
        <p class="buttons">
        <?php if(!$readonly)
        { ?>
        <input name="submit" type="submit" value="<?php echo _VALIDATE;?>"  class="button" />
        <?php } ?>
            <input name="close" type="button" value="<?php echo _CLOSE;?>"  onclick="self.close();" class="button" />
        </p>
    </form >
</div>
<?php if($contact_info['IS_CORPORATE_PERSON'] == 'Y')
{
    ?>
    <script type="text/javascript">show_admin_contacts(true);</script>
    <?php
}?>
</body>
</html>
