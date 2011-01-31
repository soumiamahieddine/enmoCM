<?php

if($_SESSION['service_tag'] == 'doctype_up')
{
    $db = new dbquery();
    $db->connect();
    $db->query("select * from ".$_SESSION['tablename']['mlb_doctype_ext']." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']."");
    //$db->show();
    if($db->nb_result() == 0)
    {
        $_SESSION['m_admin']['doctypes']['process_delay'] = 21;
        $_SESSION['m_admin']['doctypes']['delay1'] = 14;
        $_SESSION['m_admin']['doctypes']['delay2'] = 1;
    }
    else
    {
        $line = $db->fetch_object();
        $_SESSION['m_admin']['doctypes']['process_delay'] = $line->process_delay;
        $_SESSION['m_admin']['doctypes']['delay1'] = $line->delay1;
        $_SESSION['m_admin']['doctypes']['delay2'] = $line->delay2;
    }
}
elseif($_SESSION['service_tag'] == 'doctype_add')
{
    $_SESSION['m_admin']['doctypes']['process_delay'] = 21;
    $_SESSION['m_admin']['doctypes']['delay1'] = 14;
    $_SESSION['m_admin']['doctypes']['delay2'] = 1;

}
elseif($_SESSION['service_tag'] == 'frm_doctype')
{
    $func = new functions();
    ?>
    <p>
        <label for="label"><?php echo _PROCESS_DELAY; ?> : </label>
        <input name="process_delay" type="text" class="textbox" id="label" maxlength="2" value="<?php echo $func->show_str($_SESSION['m_admin']['doctypes']['process_delay']); ?>"/>
    </p>
    <p>
        <label for="label"><?php echo _ALARM1_DELAY; ?> : </label>
        <input name="delay1" type="text" class="textbox" id="label" maxlength="2" value="<?php echo $func->show_str($_SESSION['m_admin']['doctypes']['delay1']); ?>"/>
    </p>
    <p>
        <label for="label"><?php echo _ALARM2_DELAY; ?> : </label>
        <input name="delay2" type="text" class="textbox" id="label" maxlength="2" value="<?php echo $func->show_str($_SESSION['m_admin']['doctypes']['delay2']); ?>"/>
    </p>
    <?php
}
elseif($_SESSION['service_tag'] == "doctype_info")
{
    $func = new functions();
    if(isset($_REQUEST['process_delay']) && $_REQUEST['process_delay'] >= 0)
    {
        $_SESSION['m_admin']['doctypes']['process_delay'] = $func->wash($_REQUEST['process_delay'], "num", _TREATMENT_DELAY);
    }
    if(isset($_REQUEST['delay1']) && $_REQUEST['delay1'] >= 0)
    {
        $_SESSION['m_admin']['doctypes']['delay1'] = $func->wash($_REQUEST['delay1'], "num", _ALERT_DELAY_1);
    }
    if(isset($_REQUEST['delay2']) &&  $_REQUEST['delay2'] >= 0)
    {
        $_SESSION['m_admin']['doctypes']['delay2'] = $func->wash($_REQUEST['delay2'], "num", _ALERT_DELAY_2);
    }
}
elseif($_SESSION['service_tag'] == "doctype_updatedb")
{
    $db = new dbquery();
    $db->connect();

    $db->query("select type_id from ".$_SESSION['tablename']['mlb_doctype_ext']." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']);
    if($db->nb_result() > 0)
    {
        $db->query("update ".$_SESSION['tablename']['mlb_doctype_ext']." set process_delay = ".$_SESSION['m_admin']['doctypes']['process_delay'].", delay1 = ".$_SESSION['m_admin']['doctypes']['delay1'].", delay2 = ".$_SESSION['m_admin']['doctypes']['delay2']." where type_id = '".$_SESSION['m_admin']['doctypes']['TYPE_ID']."'");
    }
    else
    {
        $db->query("insert into ".$_SESSION['tablename']['mlb_doctype_ext']." (type_id, process_delay, delay1, delay2) values (".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", ".$_SESSION['m_admin']['doctypes']['process_delay'].", ".$_SESSION['m_admin']['doctypes']['delay1'].", ".$_SESSION['m_admin']['doctypes']['delay2'].")");
    }

}
elseif($_SESSION['service_tag'] == "doctype_insertdb")
{
    $db = new dbquery();
    $db->connect();
    $db->query("insert into ".$_SESSION['tablename']['mlb_doctype_ext']." (type_id, process_delay, delay1, delay2) values (".$_SESSION['m_admin']['doctypes']['TYPE_ID'].", ".$_SESSION['m_admin']['doctypes']['process_delay'].", ".$_SESSION['m_admin']['doctypes']['delay1'].", ".$_SESSION['m_admin']['doctypes']['delay2'].")");
}
elseif($_SESSION['service_tag'] == "doctype_delete")
{
    $db = new dbquery();
    $db->connect();
    $db->query("delete from ".$_SESSION['tablename']['mlb_doctype_ext']." where type_id = ".$_SESSION['m_admin']['doctypes']['TYPE_ID']."");
}
?>
