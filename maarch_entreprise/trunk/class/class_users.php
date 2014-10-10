<?php
/**
* User Class
*
*  Contains all the functions to manage users
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class users: Contains all the functions and forms to manage users
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package  Maarch PeopleBox 1.0
* @version 2.1
*/

require_once 'core/core_tables.php';

class class_users extends dbquery
{
    /**
    * Redefinition of the user object constructor : configure the SQL argument
    *  order by
    */
    public function __construct()
    {
        parent::__construct();
    }


    /**
    * Treats the information returned by the form of change_info_user().
    *
    */
    public function user_modif()
    {
        $_SESSION['user']['FirstName'] = $this->wash(
            $_POST['FirstName'], 'no', _FIRSTNAME
        );
        $_SESSION['user']['LastName'] = $this->wash(
            $_POST['LastName'], 'no', _LASTNAME
        );
        $_SESSION['user']['pass1'] = $this->wash(
            $_POST['pass1'], 'no', _FIRST_PSW
        );
        
        if ($_SESSION['config']['ldap'] != "true") {
            $_SESSION['user']['pass2'] = $this->wash(
                $_POST['pass2'], 'no', _SECOND_PSW
            );
        }

        if ($_SESSION['user']['pass1'] <> $_SESSION['user']['pass2'] && $_SESSION['config']['ldap'] != "true") {
            $this->add_error(_WRONG_SECOND_PSW, '');
        }

        if (isset($_POST['Phone']) && ! empty($_POST['Phone'])) {
            $_SESSION['user']['Phone']  = $_POST['Phone'];
        }

        if (isset($_POST['Fonction']) && ! empty($_POST['Fonction'])) {
            $_SESSION['user']['Fonction']  = $_POST['Fonction'];
        }

        if (isset($_POST['Department']) && ! empty($_POST['Department'])) {
            $_SESSION['user']['department']  = $_POST['Department'];
        }

        if (isset($_POST['Mail']) && ! empty($_POST['Mail'])) {
            $_SESSION['user']['Mail']  = $_POST['Mail'];
        }
        if (empty($_SESSION['error'])) {
            $firstname = $this->protect_string_db(
                $_SESSION['user']['FirstName']
            );
            $lastname = $this->protect_string_db($_SESSION['user']['LastName']);
            $department = $this->protect_string_db(
                $_SESSION['user']['department']
            );
            $this->connect();
            $this->query(
                "update " . USERS_TABLE . " set password = '"
                . md5($_SESSION['user']['pass1']) . "', firstname = '"
                . $firstname . "', lastname = '" . $lastname . "', phone = '"
                . $_SESSION['user']['Phone'] . "', mail = '"
                . $_SESSION['user']['Mail'] . "' , department = '" . $department
                . "' where user_id = '" . $_SESSION['user']['UserId'] . "'"
            );


            if ($_SESSION['history']['usersup'] == 'true') {
                require_once 'core' . DIRECTORY_SEPARATOR . 'class'
                    . DIRECTORY_SEPARATOR . 'class_history.php';
                $hist = new history();
                $hist->add(
                    USERS_TABLE, $_SESSION['user']['UserId'], 'UP','usersup',
                    _USER_UPDATE . ' : ' . $_SESSION['user']['LastName'] . ' '
                    . $_SESSION['user']['FirstName'],
                    $_SESSION['config']['databasetype']
                );
            }

            $_SESSION['error'] = _USER_UPDATED;
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php'
            );
            exit();
        } else {
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?page=modify_user&admin=users'
            );
            exit();
        }
    }

    /**
    * Form for the management of the current user.
    *
    */
    public function change_info_user()
    {
        $core = new core_tools();
        ?>
        <h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_user_b.gif" alt="" /> <?php  echo _MY_INFO; ?></h1>

        <div id="inner_content" class="clearfix">
            <div id="user_box" >
                <div class="block">
                 <h2 class="tit"><?php  echo _USER_GROUPS_TITLE;?> : </h2>
                     <ul id="my_profil">
                      <?php
            $this->connect();
            $this->query(
                "SELECT u.group_desc FROM " . USERGROUP_CONTENT_TABLE . " uc, "
                . USERGROUPS_TABLE ." u where uc.user_id ='"
                . $_SESSION['user']['UserId'] . "' and uc.group_id = u.group_id"
                . " order by u.group_desc"
            );

            if ($this->nb_result() < 1) {
                echo _USER_BELONGS_NO_GROUP . ".";
            } else {
                while ($line = $this->fetch_object()) {
                    echo "<li>" . $line->group_desc . " </li>";
                }
            }
                         ?>
                         </ul>
                         <?php if($core->is_module_loaded("entities") )
                        {?>
                         <h2 class="tit"><?php  echo _USER_ENTITIES_TITLE;?> : </h2>
                            <ul id="my_profil">
                         <?php
                            $this->query("SELECT e.entity_label FROM ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['ent_entities']." e
                            where ue.user_id ='".$_SESSION['user']['UserId']."' and ue.entity_id = e.entity_id order by e.entity_label");

                            if($this->nb_result() < 1)
                            {
                                echo _USER_BELONGS_NO_ENTITY.".";
                            }
                            else
                            {
                                while($line = $this->fetch_object())
                                {

                                 echo "<li>".$line->entity_label." </li>";
                                }
                            }
                         ?>
                         </ul>
                         <?php }?>
                     </div>
                     <div class="block_end">&nbsp;</div>
                     </div>

                        <form name="frmuser" id="frmuser" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=user_modif" class="forms addforms">
                            <input type="hidden" name="display" value="true" />
                            <input type="hidden" name="admin" value="users" />
                            <input type="hidden" name="page" value="user_modif" />
                        <div class="">
                    <p>
                        <label><?php  echo _ID; ?> : </label>
                        <input name="UserId"  type="text" id="UserId" value="<?php  echo $_SESSION['user']['UserId']; ?>"  readonly="readonly" />
                        <input type="hidden"  name="id" value="<?php  echo $_SESSION['user']['UserId']; ?>" />
                    </p>
                    <p <?php if($_SESSION['config']['ldap'] == "true"){echo 'style="display:none"';} ?> >
                        <label for="pass1"><?php  echo _PASSWORD; ?> : </label>
                        <input name="pass1"  type="password" id="pass1"  value="" />
                    </p>
                    <p <?php if($_SESSION['config']['ldap'] == "true"){echo 'style="display:none"';} ?> >
                        <label for="pass2"><?php  echo _REENTER_PSW; ?> : </label>
                        <input name="pass2"  type="password" id="pass2" value="" />
                    </p>
                    <p>
                        <label for="LastName"><?php  echo _LASTNAME; ?> : </label>
                        <input name="LastName"   type="text" id="LastName" size="45" value="<?php  echo $this->show_string($_SESSION['user']['LastName']); ?>" />
                    </p>
                    <p>
                        <label for="FirstName"><?php  echo _FIRSTNAME; ?> : </label>
                        <input name="FirstName"  type="text" id="FirstName" size="45" value="<?php  echo $this->show_string($_SESSION['user']['FirstName']); ?>" />
                     </p>
                     <?php if(!$core->is_module_loaded("entities") )
                        {?>
                      <p>
                        <label for="Department"><?php  echo _DEPARTMENT;?> : </label>
                            <input name="Department" id="Department" type="text"  disabled size="45" value="<?php  echo $this->show_string($_SESSION['user']['department']); ?>" />
                        </p>
                        <?php }?>
                      <p>
                        <label for="Phone"><?php  echo _PHONE_NUMBER; ?> : </label>
                        <input name="Phone"  type="text" id="Phone" value="<?php  echo $_SESSION['user']['Phone']; ?>" />
                      </p>
                     <p>
                        <label for="Mail"><?php  echo _MAIL; ?> : </label>
                        <input name="Mail"  type="text" id="Mail" size="45" value="<?php  echo $_SESSION['user']['Mail']; ?>" />
                      </p>

                      <p class="buttons">
                            <input type="submit" name="Submit" value="<?php  echo _VALIDATE; ?>" class="button" />
                            <input type="button" name="cancel" value="<?php  echo _CANCEL; ?>" class="button" onclick="javascript:window.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php';" />
                    </p>
                    </div>

                    </form>
                    <div class="blank_space"></div>
                <?php

            //  require_once("core/class/class_core_tools.php");
                $core = new core_tools;
                echo $core->execute_modules_services($_SESSION['modules_services'], 'modify_user.php', "include");
                ?>
        </div>

    <?php

    }
    
    /**
    * Return a array of user informations
    *
    */
    public function get_user($user_id) {
        if (!empty($user_id)) {
            $this->connect();
            $this->query(
                "select user_id, firstname, lastname, mail, phone, status from " 
                . USERS_TABLE . " where user_id = '" . $user_id . "'"
            );
            if ($this->nb_result() >0) {
                $line = $this->fetch_object();
                $user = array(
                        'id' => $line->user_id,
                        'firstname' => $this->show_string($line->firstname),
                        'lastname' => $this->show_string($line->lastname),
                        'mail' => $line->mail,
                        'phone' => $line->phone,
                        'status' => $line->status
                    );
                return $user;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>
