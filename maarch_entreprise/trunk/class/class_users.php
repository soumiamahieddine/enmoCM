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

        if ($_SESSION['config']['ldap'] != "true") {
            $_SESSION['user']['pass1'] = $this->wash(
                $_POST['pass1'], 'no', _FIRST_PSW
            );
        }

        if(!empty($_POST['Phone'])){
            $_SESSION['user']['Phone'] = $this->wash(
                $_POST['Phone'], 'phone', _PHONE, "no", "",32
            );
        }

        
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
		
		if (isset($_POST['thumbprint']) && ! empty($_POST['thumbprint'])) {
            $_SESSION['user']['thumbprint']  = trim($_POST['thumbprint']);
        }

        if (isset($_FILES['signature']['name']) && !empty($_FILES['signature']['name'])) {
            $extension = explode(".", $_FILES['signature']['name']);
            $count_level = count($extension)-1;
            $the_ext = $extension[$count_level];
            $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
                . '_' . rand() . '.' . strtolower($the_ext);
            $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
            
            if (!is_uploaded_file($_FILES['signature']['tmp_name'])) {
                    $_SESSION['error'] = _FILE_NOT_SEND . ". " . _TRY_AGAIN
                        . ". " . _MORE_INFOS . " (<a href=\"mailto:"
                        . $_SESSION['config']['adminmail'] . "\">"
                        . $_SESSION['config']['adminname'] . "</a>)";
            } elseif (!@move_uploaded_file($_FILES['signature']['tmp_name'], $filePathOnTmp)) {
                $_SESSION['error'] = _FILE_NOT_SEND . ". " . _TRY_AGAIN . ". "
                    . _MORE_INFOS . " (<a href=\"mailto:"
                    . $_SESSION['config']['adminmail'] . "\">"
                    . $_SESSION['config']['adminname'] . "</a>)";
            } else {
                require_once 'core/docservers_tools.php';
                $arrayIsAllowed = array();
                $arrayIsAllowed = Ds_isFileTypeAllowed($filePathOnTmp);
                if (strtolower($the_ext) <> 'jpg' && strtolower($the_ext) <> 'jpeg') {
                    $_SESSION['error'] = _WRONG_FILE_TYPE . ' ' . $arrayIsAllowed['mime_type'];
                    $_SESSION['upfile'] = array();
                } else if ($arrayIsAllowed['status'] == false) {
                    $_SESSION['error'] = _WRONG_FILE_TYPE . ' ' . $arrayIsAllowed['mime_type'];
                    $_SESSION['upfile'] = array();
                } else {
                    include_once 'core/class/docservers_controler.php';
                    $docservers_controler = new docservers_controler();
                    $fileTemplateInfos = array(
                        'tmpDir'      => $_SESSION['config']['tmppath'],
                        'size'        => $_FILES['signature']['size'],
                        'format'      => $the_ext,
                        'tmpFileName' => $fileNameOnTmp,
                    );
                    $storeInfos = $docservers_controler->storeResourceOnDocserver(
                        'templates',
                        $fileTemplateInfos
                    );
                    if (!file_exists(
                            $storeInfos['path_template']
                            .  str_replace("#", DIRECTORY_SEPARATOR, $storeInfos['destination_dir'])
                            . $storeInfos['file_destination_name']
                        )
                    ) {
                        $_SESSION['error'] = _FILE_NOT_EXISTS . ' : ' . $storeInfos['path_template']
                            .  str_replace("#", DIRECTORY_SEPARATOR, $storeInfos['destination_dir'])
                            . $storeInfos['file_destination_name'];
                        return false;
                    } else {
                        $_SESSION['user']['signature_path'] = $storeInfos['destination_dir'];
                        $_SESSION['user']['signature_file_name']  = $storeInfos['file_destination_name'];
                    }
                }
            }
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

            $query = "update " . USERS_TABLE . " set";

            if ($_SESSION['config']['ldap'] != "true") {
                $query .= " password = '" . md5($_SESSION['user']['pass1']) . "',";
            }

            $query .= " firstname = '"
                . $firstname . "', lastname = '" . $lastname . "', phone = '"
                . $_SESSION['user']['Phone'] . "', mail = '"
                . $_SESSION['user']['Mail'] . "' , department = '" . $department
                . "', thumbprint = '" . $_SESSION['user']['thumbprint']
                . "', signature_path = '" . $_SESSION['user']['signature_path']
                . "', signature_file_name = '" . $_SESSION['user']['signature_file_name']
                . "' where user_id = '" . $_SESSION['user']['UserId'] . "'"; 

            $this->query($query);

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
        <h1><i class="fa fa-user fa-2x" title=""></i> <?php echo _MY_INFO;?></h1>

        <div id="inner_content" class="clearfix">
            <div id="user_box" style="float:right;width:17%;">
                <div class="block" style="height:400px;">
                         <?php if($core->is_module_loaded("entities") )
                        {?>
                         <h2 class="tit"><?php echo _USER_ENTITIES_TITLE;?> : </h2>
                            <ul id="my_profil" style="height:280px;overflow:auto;">
                         <?php
                            $this->query("SELECT e.entity_label, ue.primary_entity FROM ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['ent_entities']." e
                            where ue.user_id ='".$_SESSION['user']['UserId']."' and ue.entity_id = e.entity_id order by e.entity_label");
                            if($this->nb_result() < 1)
                            {
                                echo _USER_BELONGS_NO_ENTITY.".";
                            }
                            else
                            {
                                while($line = $this->fetch_object())
                                {
                                    if($line->primary_entity == 'Y'){
                                        echo "<li style='list-style-position:inside;padding:5px;'><i class=\"fa fa-arrow-right\"></i> ".$line->entity_label." </li>";
                                    }else{
                                        echo "<li style='padding:5px;'>".$line->entity_label." </li>";
                                    }
                                }
                            }
                         ?>
                         </ul>
                         <?php }?>
                     </div>
                     <div class="block_end">&nbsp;</div>
                     </div>
                     <div id="user_box_2" style="float:right;width:17%;">
                <div class="block" style="height:400px;">
                 <h2 class="tit"><?php echo _USER_GROUPS_TITLE;?> : </h2>
                     <ul id="my_profil" style="height:280px;overflow:auto;">
                      <?php
            $this->connect();
            $this->query(
                "SELECT u.group_desc, uc.primary_group FROM " . USERGROUP_CONTENT_TABLE . " uc, "
                . USERGROUPS_TABLE ." u where uc.user_id ='"
                . $_SESSION['user']['UserId'] . "' and uc.group_id = u.group_id"
                . " order by u.group_desc"
            );

            if ($this->nb_result() < 1) {
                echo _USER_BELONGS_NO_GROUP . ".";
            } else {
                while ($line = $this->fetch_object()) {
                    if($line->primary_group == 'Y'){
                        echo "<li style='list-style-position:inside;padding:5px;'><i class=\"fa fa-arrow-right\"></i> ".$line->group_desc." </li>";
                    }else{
                        echo "<li style='padding:5px;'>".$line->group_desc." </li>";
                    }
                }
            }
                         ?>
                         </ul>
                     </div>
                     <div class="block_end">&nbsp;</div>
                     </div>
                        <div class="block" style="float:left;width:55%;height:auto;">
                        <form name="frmuser" style="margin:auto;" enctype="multipart/form-data" id="frmuser" method="post" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=users&page=user_modif" class="forms addforms">
                            <input type="hidden" name="display" value="true" />
                            <input type="hidden" name="admin" value="users" />
                            <input type="hidden" name="page" value="user_modif" />
                        <div class="">
                    <p>
                        <label><?php echo _ID;?> : </label>
                        <input name="UserId"  type="text" id="UserId" value="<?php functions::xecho($_SESSION['user']['UserId']);?>"  readonly="readonly" />
                        <input type="hidden"  name="id" value="<?php functions::xecho($_SESSION['user']['UserId']);?>" />
                    </p>
                    <p <?php if($_SESSION['config']['ldap'] == "true"){echo 'style="display:none"';} ?> >
                        <label for="pass1"><?php echo _PASSWORD;?> : </label>
                        <input name="pass1"  type="password" id="pass1"  value="" />
                    </p>
                    <p <?php if($_SESSION['config']['ldap'] == "true"){echo 'style="display:none"';} ?> >
                        <label for="pass2"><?php echo _REENTER_PSW;?> : </label>
                        <input name="pass2"  type="password" id="pass2" value="" />
                    </p>
                    <p>
                        <label for="LastName"><?php echo _LASTNAME;?> : </label>
                        <input name="LastName"   type="text" id="LastName" size="45" value="<?php functions::xecho($this->show_string($_SESSION['user']['LastName']));?>" />
                    </p>
                    <p>
                        <label for="FirstName"><?php echo _FIRSTNAME;?> : </label>
                        <input name="FirstName"  type="text" id="FirstName" size="45" value="<?php functions::xecho($this->show_string($_SESSION['user']['FirstName']));?>" />
                     </p>
                     <?php if(!$core->is_module_loaded("entities") )
                        {?>
                      <p>
                        <label for="Department"><?php echo _DEPARTMENT;?> : </label>
                            <input name="Department" id="Department" type="text"  disabled size="45" value="<?php functions::xecho($this->show_string($_SESSION['user']['department']));?>" />
                        </p>
                        <?php }?>
                      <p>
                        <label for="Phone"><?php echo _PHONE_NUMBER;?> : </label>
                        <input name="Phone"  type="text" id="Phone" value="<?php functions::xecho($_SESSION['user']['Phone']);?>" />
                      </p>
                     <p>
                        <label for="Mail"><?php echo _MAIL;?> : </label>
                        <input name="Mail"  type="text" id="Mail" size="45" value="<?php functions::xecho($_SESSION['user']['Mail']);?>" />
                      </p>

					   <p>
                        <label for="thumbprint"><?php echo _THUMBPRINT;  ?> : </label>
						<textarea name="thumbprint" id="thumbprint"><?php functions::xecho($_SESSION['user']['thumbprint']);?></textarea>
                      </p>
					  <p>
                        <label for="signature"><?php echo _SIGNATURE;  ?> : </label>
                        <input type="file" name="signature" id="signature"/>
                        <br />
                        <br />
                        <?php
                        if (file_exists($_SESSION['user']['pathToSignature'])) {
                            $extension = explode(".", $_SESSION['user']['pathToSignature']);
                            $count_level = count($extension)-1;
                            $the_ext = $extension[$count_level];
                            $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
                                . '_' . rand() . '.' . strtolower($the_ext);
                            $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;

                            if (copy($_SESSION['user']['pathToSignature'], $filePathOnTmp)) {
                                ?>
                                 <img src="<?php 
                                    echo $_SESSION['config']['businessappurl'] 
                                        . '/tmp/' . $fileNameOnTmp;
                                    ?>" alt="signature" id="signFromDs"/> 
                                <?php
                            } else {
                                echo _COPY_ERROR;
                            }
                        }
                        ?>
                        <canvas id="imageCanvas" style="display:none;"></canvas>
                        <script>
                            var signature = document.getElementById('signature');
                                signature.addEventListener('change', handleImage, false);
                            var canvas = document.getElementById('imageCanvas');
                            var signFromDs = document.getElementById('signFromDs');
                            var ctx = canvas.getContext('2d');

                            function handleImage(e){
                                var reader = new FileReader();
                                reader.onload = function(event){
                                    var img = new Image();
                                    img.onload = function(){
                                        canvas.width = img.width;
                                        canvas.height = img.height;
                                        ctx.drawImage(img,0,0);
                                        canvas.style.display = 'block';
                                        signFromDs.style.display = 'none';
                                    }
                                    img.src = event.target.result;
                                }
                                reader.readAsDataURL(e.target.files[0]);     
                            }
                        </script>  
                    </p>
                    <p class="buttons">
                            <input type="submit" name="Submit" value="<?php echo _VALIDATE;?>" class="button" />
                            <input type="button" name="cancel" value="<?php echo _CANCEL;?>" class="button" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php';" />
                    </p>
                    </div>

                    </form>
                    </div>
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
                "select user_id, firstname, lastname, mail, phone, status, thumbprint, signature_path, signature_file_name from " 
                . USERS_TABLE . " where user_id = '" . $user_id . "'"
            );
            if ($this->nb_result() >0) {
                $line = $this->fetch_object();
                if ($line->signature_path <> '' 
                    && $line->signature_file_name <> '' 
                ) {
                    $db = new dbquery();
                    $db->connect();
                    $query = "select path_template from " 
                        . _DOCSERVERS_TABLE_NAME 
                        . " where docserver_id = 'TEMPLATES'";
                    $db->query($query);
                    $resDs = $db->fetch_object();
                    $pathToDs = $resDs->path_template;
                    $pathToSignature = $pathToDs . str_replace(
                            "#", 
                            DIRECTORY_SEPARATOR, 
                            $line->signature_path
                        )
                        . $line->signature_file_name;
                }
                $user = array(
                    'id' => $line->user_id,
                    'firstname' => $this->show_string($line->firstname),
                    'lastname' => $this->show_string($line->lastname),
                    'mail' => $line->mail,
                    'phone' => $line->phone,
                    'status' => $line->status,
                    'thumbprint' => $line->thumbprint,
                    'signature_path' => $line->signature_path,
                    'signature_file_name' => $line->signature_file_name,
					'pathToSignature' => $pathToSignature,
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
