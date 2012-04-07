<?php

if ($_SESSION['service_tag'] == 'doctype_up') {
    $db = new dbquery();
    $db->connect();
    $db->query("select * from ".$_SESSION['tablename']['temp_templates_doctype_ext']." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']."");
    //$db->show();
    if ($db->nb_result() == 0) {
        $_SESSION['m_admin']['doctypes']['is_generated'] = 'N';
        $_SESSION['m_admin']['doctypes']['template_id'] = '';
    } else {
        $line = $db->fetch_object();
        $_SESSION['m_admin']['doctypes']['is_generated'] = $line->is_generated;
        $_SESSION['m_admin']['doctypes']['template_id'] = $line->template_id;
    }
} elseif ($_SESSION['service_tag'] == 'doctype_add') {
    $_SESSION['m_admin']['doctypes']['is_generated'] = 'N';
    $_SESSION['m_admin']['doctypes']['template_id'] = '';
} elseif ($_SESSION['service_tag'] == 'frm_doctype') {
    $db = new dbquery();
    $db->connect();
    $db->query("select template_id, template_label from "
        . $_SESSION['tablename']['temp_templates'] . " where template_type = 'HTML'"
    );
    $templates = array();
    while ($res = $db->fetch_object()) {
        array_push($templates, array('id' => $res->template_id, 'label' => $res->template_label));
    }
    ?>
    <p>
        <span><input type="radio"  class="check" onclick="javascript:show_templates(false);" name="e_file" id="load_file" value="N"  <?php  if ($_SESSION['m_admin']['doctypes']['is_generated'] == 'N') { echo 'checked="checked"';} ?>/><?php  echo _LOADED_FILE;?></span>
        <span><input type="radio" class="check" onclick="javascript:show_templates(true);" name="e_file" id="gen_file" value="Y"  <?php  if ($_SESSION['m_admin']['doctypes']['is_generated'] == 'Y') { echo 'checked="checked"';} ?> /><?php  echo _GENERATED_FILE;?></span>
    </p>
    <div id="templates_div" style="display:<?php if($_SESSION['m_admin']['doctypes']['is_generated'] == 'Y'){ echo "block";}else{ echo "none";}?>">
        <span><?php echo _CHOOSE_TEMPLATE;?> :</span>
        <select name="templates" id="templates" >
            <option value=""><?php echo _CHOOSE_TEMPLATE;?></option>
            <?php
                for ($i=0;$i<count($templates);$i++) {
                    echo '<option value="'.$templates[$i]['id'].'" ';
                    if ($_SESSION['m_admin']['doctypes']['template_id'] == $templates[$i]['id'] ) {
                        echo 'selected="selected"';
                    }
                    echo '>'.$templates[$i]['label'].'</option>';
                }
            ?>
        </select><span class="red_asterisk">*</span>
    </div>
    <?php
} elseif ($_SESSION['service_tag'] == "doctype_info") {
    if ($_REQUEST['e_file'] == "Y" && (empty($_REQUEST['templates']) || !isset($_REQUEST['templates']))) {
        $_SESSION['error'] .= _MUST_CHOOSE_TEMPLATE;
    } else if((empty($_REQUEST['templates']) || !isset($_REQUEST['templates']))) {
        $_SESSION['m_admin']['doctypes']['template_id'] = '';
    }
    $_SESSION['m_admin']['doctypes']['is_generated'] = $_REQUEST['e_file'];
    $_SESSION['m_admin']['doctypes']['template_id'] = $_REQUEST['templates'];

} elseif ($_SESSION['service_tag'] == "doctype_updatedb") {
    $db = new dbquery();
    $db->connect();
    $db->query("delete from ".$_SESSION['tablename']['temp_templates_doctype_ext']." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']."");
    if (!empty($_SESSION['m_admin']['doctypes']['template_id']) && isset($_SESSION['m_admin']['doctypes']['template_id'])) {
        $db->query("insert into ".$_SESSION['tablename']['temp_templates_doctype_ext']." (type_id, is_generated, template_id) values (".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", '".$_SESSION['m_admin']['doctypes']['is_generated']."', ".$_SESSION['m_admin']['doctypes']['template_id'].")");
    } else {
        $db->query("insert into ".$_SESSION['tablename']['temp_templates_doctype_ext']." (type_id, is_generated) values (".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", '".$_SESSION['m_admin']['doctypes']['is_generated']."')");
    }
    //$db->show();
    //exit();
} elseif ($_SESSION['service_tag'] == "doctype_insertdb") {
    $db = new dbquery();
    $db->connect();
    if (!empty($_SESSION['m_admin']['doctypes']['template_id']) && isset($_SESSION['m_admin']['doctypes']['template_id'])) {
        $db->query("insert into ".$_SESSION['tablename']['temp_templates_doctype_ext']." (type_id, is_generated, template_id) values (".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", '".$_SESSION['m_admin']['doctypes']['is_generated']."', ".$_SESSION['m_admin']['doctypes']['template_id'].")");
    } else {
        $db->query("insert into ".$_SESSION['tablename']['temp_templates_doctype_ext']." (type_id, is_generated) values (".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", '".$_SESSION['m_admin']['doctypes']['is_generated']."')");
    }
} elseif ($_SESSION['service_tag'] == "doctype_delete") {
    $db = new dbquery();
    $db->connect();
    $db->query("delete from ".$_SESSION['tablename']['temp_templates_doctype_ext']." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']."");
}
