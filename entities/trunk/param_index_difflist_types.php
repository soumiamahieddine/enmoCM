<?php
// Group - Basket Form : actions params
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'apps_tables.php';

require_once 'modules' . DIRECTORY_SEPARATOR . 'entities'
    . DIRECTORY_SEPARATOR . 'entities_tables.php';

require_once 'modules' . DIRECTORY_SEPARATOR . 'entities' . DIRECTORY_SEPARATOR . 'class'
    . DIRECTORY_SEPARATOR . 'class_manage_listdiff.php';

$difflist = new diffusion_list();

$difflist_types = $difflist->list_difflist_types();

if($_SESSION['service_tag'] == 'group_basket') {
    $current_groupbasket = $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']];
    $current_compteur = $_SESSION['m_admin']['compteur'];
    // This param is only for the actions with the keyword : indexing
    if( trim($_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['KEYWORD']) == 'indexing') // Indexing case
    {
        $_SESSION['m_admin']['show_where_clause'] = false;
        $is_default_action = false;
        // Is the action the default action for the group on this basket ?
        if( isset($current_groupbasket['DEFAULT_ACTION']) && $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'] == $current_groupbasket['DEFAULT_ACTION'])
        {
            $is_default_action = true;
        }
        // indexing difflist_types list
        ?>
    <p>
        <label><?php  echo _INDEXING_DIFFLIST_TYPES;?> :</label>
    </p>
    <table align="center" width="100%" id="index_difflist_types_baskets" >
        <tr>
            <td width="40%" align="center">
                <select name="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist[]" id="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist" size="7" ondblclick='moveclick(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen"));' multiple="multiple"  class="difflist_types" style="width:100%;">
                <?php
                // Browse all the difflist_types
                foreach($difflist_types as $difflist_type_id => $difflist_type_label) {
                    $state_status = false;
                    if (! $is_default_action ) {
                        if (isset($current_groupbasket['ACTIONS'])) {
                            for ($j = 0; $j < count($current_groupbasket['ACTIONS']); $j ++) {
                                for ($k = 0; $k < count($current_groupbasket['ACTIONS'][$j]['difflist_types']); $k ++) {
                                    if ($difflist_type_id == $current_groupbasket['ACTIONS'][$j]['difflist_types'][$k]['difflist_type_id']) {
                                        $state_difflist_type = true;
                                    }
                                }
                            }
                        }
                    } else {
                        for ($k = 0; $k < count($current_groupbasket['PARAM_DEFAULT_ACTION']['difflist_types']); $k ++)
                        {
                            if ($difflist_type_id == $current_groupbasket['PARAM_DEFAULT_ACTION']['difflist_types'][$k]['ID']) {
                                $state_difflist_type = true;
                            }
                        }
                    }
                    if($state_difflist_type == false)
                    {
                        ?>
                        <option value="<?php  echo $difflist_type_id; ?>"><?php  echo $difflist_type_label; ?></option>
                    <?php
                    }
                }
                ?>
                </select>
                <br/>
                <em><a href='javascript:selectall(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist"));' ><?php  echo _SELECT_ALL; ?></a></em>
            </td>
            <td width="20%" align="center">
                <input type="button" class="button" value="<?php  echo _ADD; ?> &gt;&gt;" onclick='Move(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen"));' />
                <br />
                <br />
                <input type="button" class="button" value="&lt;&lt; <?php  echo _REMOVE; ?>" onclick='Move(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist"));' />
            </td>
            <td width="40%" align="center">
                <select name="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen[]" id="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen" size="7" ondblclick='moveclick(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_typeslist"));' multiple="multiple"   class="difflist_types" style="width:100%;">
                <?php
                foreach($difflist_types as $difflist_type_id => $difflist_type_label) {
                    $state_difflist_type = false;
                    if (! $is_default_action) {
                        if (isset($current_groupbasket['ACTIONS'])) {
                            for($j=0;$j<count($current_groupbasket['ACTIONS']);$j++)
                            {
                                for($k=0; $k<count($current_groupbasket['ACTIONS'][$j]['difflist_types']);$k++)
                                {
                                    if($difflist_type_id == $current_groupbasket['ACTIONS'][$j]['difflist_types'][$k]['difflist_type_id'])
                                    {
                                        $state_difflist_type = true;
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        for($k=0; $k<count($current_groupbasket['PARAM_DEFAULT_ACTION']['difflist_types']);$k++)
                        {
                            if($difflist_type_id == $current_groupbasket['PARAM_DEFAULT_ACTION']['difflist_types'][$k]['difflist_type_id'])
                            {
                                $state_difflist_type = true;
                            }
                        }
                    }
                    if($state_difflist_type == true)
                    {
                    ?>
                        <option value="<?php  echo $difflist_type_id; ?>" selected="selected" ><?php  echo $difflist_type_label; ?></option>
                    <?php
                    }
                }
                ?>
                </select>
                <br/>
                <em><a href="javascript:selectall(document.getElementById('<?php echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];?>_difflist_types_chosen'));" >
                <?php  echo _SELECT_ALL; ?></a></em>
            </td>
        </tr>
    </table>
    <?php
    }
}
elseif($_SESSION['service_tag'] == 'manage_groupbasket')
{
    $db = new dbquery();
    $db->connect();
    /*
    echo 'before<br>';
    echo 'param status';
    $db->show_array($_SESSION['m_admin']['basket']['groups']);
    */
    $groupe = $_REQUEST['group'];
    if(isset($_REQUEST['old_group']) && !empty($_REQUEST['old_group']))
    {
        $old_group = $_REQUEST['old_group'];
    }
    $ind = -1;
    $find = false;
    for ($cpt=0;$cpt<count($_SESSION['m_admin']['basket']['groups']);$cpt++) {
        if (
            $_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID'] == $groupe 
            || $old_group == $_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']) {
            for ($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS']);$j++) {
                $chosen_difflist_types = array();
                if (isset($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_difflist_types_chosen']) && count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_difflist_types_chosen']) > 0) {
                    for ($k=0; $k < count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_difflist_types_chosen']); $k++) {
                        $difflist_type_id = $_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_difflist_types_chosen'][$k];
                        $difflist_type_label = $difflist_types[$difflist_type_id];
                        array_push($chosen_difflist_types , array( 'difflist_type_id' => $difflist_type_id, 'difflist_type_label' => $difflist_type_label));
                    }
                }
                $_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['difflist_types'] = $chosen_difflist_types ;
            }
            if ($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID'] == $groupe) {
                $ind = $cpt;
                $find = true;
                break;
            }
            if ($old_group == $_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']) {
                $ind = $cpt;
                $find = true;
                break;
            }
        }
    }

    if ($find && $ind >= 0) {
        //$_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION'] = array();
        $_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION']['difflist_types'] = array();

        if (isset($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_difflist_types_chosen']) && count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_difflist_types_chosen']) > 0) {
            for ($l=0; $l < count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_difflist_types_chosen']); $l++) {
                $difflist_type_id = $_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_difflist_types_chosen'][$l];
                $difflist_type_label = $difflist_types[$difflist_type_id];
                array_push($_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION']['difflist_types'] , array( 'difflist_type_id' => $difflist_type_id, 'difflist_type_label' => $difflist_type_label));
            }
        }
    }
    $_SESSION['m_admin']['load_groupbasket'] = false;
    /*
    echo 'after<br>';
    echo 'param status';
    $ent->show_array($_SESSION['m_admin']['basket']['groups']);
    exit;
    */
}
elseif($_SESSION['service_tag'] == 'load_basket_session')
{
    $db = new dbquery();
    $db->connect();
    
    for($cpt=0; $cpt < count($_SESSION['m_admin']['basket']['groups'] ); $cpt++)
    {
        //$_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION'] = array();
        $_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION']['difflist_types'] = array();
        if(!empty($_SESSION['m_admin']['basket']['groups'][$cpt]['DEFAULT_ACTION'] ))
        {
            $query = "SELECT lmt.difflist_type_id, lmt.difflist_type_label FROM " . ENT_GROUPBASKET_DIFFLIST_TYPES . " gblmt left join " . ENT_DIFFLIST_TYPES 
                . " lmt on lmt.difflist_type_id = gblmt.difflist_type_id "
                . " where basket_id= '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
                . "' and group_id = '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']))
                . "' and action_id = " . $_SESSION['m_admin']['basket']['groups'][$cpt]['DEFAULT_ACTION'];
            $db->query($query);
            $difflist_types = array();
            while($difflist_type = $db->fetch_object()) {
                $difflist_types[] = array( 'difflist_type_id' => $difflist_type->difflist_type_id, 'difflist_type_label' => $difflist_type->difflist_type_label);
            }
            $_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION']['difflist_types'] = $difflist_types;
        }
        for($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS']);$j++)
        {
            
            $query = "SELECT lmt.difflist_type_id, lmt.difflist_type_label FROM " . ENT_GROUPBASKET_DIFFLIST_TYPES . " gblmt left join " . ENT_DIFFLIST_TYPES 
                . " lmt on lmt.difflist_type_id = gblmt.difflist_type_id "
                . " where basket_id= '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
                . "' and group_id = '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']))
                . "' and action_id = " . $_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'];
            $db->query($query);
            $difflist_types = array();
            while($difflist_type = $db->fetch_object()) {
                $difflist_types[] = array( 'difflist_type_id' => $difflist_type->difflist_type_id, 'difflist_type_label' => $difflist_type->difflist_type_label);
            }
            $_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['difflist_types'] = $difflist_types;
        }
    }
}
elseif($_SESSION['service_tag'] == 'load_basket_db')
{
    $db = new dbquery();
    $db->connect();
    $indexing_actions = array();
    for($cpt=0; $cpt<count($_SESSION['m_admin']['basket']['all_actions']);$cpt++ )
    {
        if($_SESSION['m_admin']['basket']['all_actions'][$cpt]['KEYWORD'] == 'indexing')
        {
            array_push($indexing_actions,$_SESSION['m_admin']['basket']['all_actions'][$cpt]['ID']);
        }
    }

    for($cpt=0; $cpt < count($_SESSION['m_admin']['basket']['groups'] ); $cpt++)
    {
        $GroupBasket = $_SESSION['m_admin']['basket']['groups'][$cpt];
        if(!empty($GroupBasket['DEFAULT_ACTION']) && in_array($GroupBasket['DEFAULT_ACTION'], $indexing_actions))
        {   
            //$ent->update_redirect_groupbasket_db($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID'],  $_SESSION['m_admin']['basket']['basketId'],$_SESSION['m_admin']['basket']['groups'][$cpt]['DEFAULT_ACTION'],$_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION']['difflist_types']);
            $db->query(
            "DELETE FROM " . ENT_GROUPBASKET_DIFFLIST_TYPES
            . " where basket_id= '" . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
            . "' and group_id = '" . $db->protect_string_db(trim($GroupBasket['GROUP_ID']))
            . "' and action_id = " . $GroupBasket['DEFAULT_ACTION']);
            
            for ($k = 0; $k < count($GroupBasket['PARAM_DEFAULT_ACTION']['difflist_types']); $k++) {
                $difflist_type = $GroupBasket['PARAM_DEFAULT_ACTION']['difflist_types'][$k];
                $db->query(
                    "INSERT INTO " . ENT_GROUPBASKET_DIFFLIST_TYPES
                    . " (group_id, basket_id, action_id, difflist_type_id) values ('" 
                    . $db->protect_string_db(trim($GroupBasket['GROUP_ID'])) . "', '" 
                    . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId'])) . "', "
                    . $GroupBasket['DEFAULT_ACTION'] . ", '" 
                    . $difflist_type['difflist_type_id']. "')"
                );
            }
        }
        for($j=0;$j<count($GroupBasket['ACTIONS']);$j++)
        {
            $GroupBasketAction = $GroupBasket['ACTIONS'][$j];
            if(in_array($GroupBasketAction['ID_ACTION'], $indexing_actions)) {
                //$ent->update_redirect_groupbasket_db($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID'],  $_SESSION['m_admin']['basket']['basketId'],$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'],$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['difflist_types']);
                $db->query(
                "DELETE FROM " . ENT_GROUPBASKET_DIFFLIST_TYPES
                . " where basket_id= '" . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
                . "' and group_id = '" . $db->protect_string_db(trim($GroupBasket['GROUP_ID']))
                . "' and action_id = " . $GroupBasketAction['ID_ACTION']);

                if (isset($GroupBasketAction['ID_ACTION']['difflist_types'])) {
                    for ($k = 0; $k < count($GroupBasketAction['ID_ACTION']['difflist_types']); $k++) {
                        $difflist_type = $GroupBasketAction['ID_ACTION']['difflist_types'][$k];
                        $db->query(
                            "INSERT INTO " . ENT_GROUPBASKET_DIFFLIST_TYPES
                            . " (group_id, basket_id, action_id, difflist_type_id) values ('" 
                            . $db->protect_string_db(trim($GroupBasket['GROUP_ID'])) . "', '" 
                            . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId'])) . "', "
                            . $GroupBasketAction['ID_ACTION'] . ", '" 
                            . $difflist_type['difflist_type_id']. "')"
                        );
                    }
                }
            }
        }
    }
}
else if($_SESSION['service_tag'] == 'del_basket' && !empty($_SESSION['temp_basket_id']))
{
    $db = new dbquery();
    $db->query("delete from ".ENT_GROUPBASKET_DIFFLIST_TYPES." where basket_id = '".$_SESSION['temp_basket_id']."'");
    unset($_SESSION['temp_basket_id']);
}
?>
