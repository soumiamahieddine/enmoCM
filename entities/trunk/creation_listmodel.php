<?php
/**
* File : creation_listmodel.php
*
* Pop up used to create and modify diffusion lists models
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require 'modules/entities/entities_tables.php';

function cmp_entity($a, $b)
{
    return strcmp($a['entity_label'], $b['entity_label']);
}
function cmp_users($a, $b)
{
    return strcmp($a['lastname'], $b['lastname']);
}
$core_tools = new core_tools();
$core_tools->load_lang();
if (isset($_POST['valid'])) {
    $_SESSION['popup_suite'] = true;
    //print_r($_SESSION['m_admin']['entity']['listmodel']);exit;
    ?>
    <script type="text/javascript">window.parent.opener.location.reload();
    self.close();</script>
    <?php
    exit;
}
$func = new functions();
if (isset($_POST['what_users']) && !empty($_POST['what_users']) ) {
    $_GET['what_users'] = $_POST['what_users'];
}
if (isset($_POST['what_services']) && ! empty($_POST['what_services']) ) {
    $_GET['what_services'] = $_POST['what_services'];
}
$users = array();
$entities = array();
$where_users = '';
$where_entities = '';
$orderby_users = '';
$orderby_entities = '';
$where_entities_users = '';
$what = '';
if (isset($_GET['what_users']) && ! empty($_GET['what_users']) ) {
    $what_users = $func->protect_string_db(
        $func->wash($_GET['what_users'], 'no', '', 'no'));
    if ($_SESSION['config']['databasetype'] == 'POSTGRESQL') {
        $where_users .= " and (u.lastname ilike '%" . strtolower($what_users)
                     . "%' or u.lastname ilike '%" . strtoupper($what_users)
                     . "%' or u.firstname ilike '%" . strtolower($what_users)
                     . "%' or u.firstname ilike '%" . strtoupper($what_users)
                     . "%' or u.user_id ilike '%" . strtolower($what_users)
                     . "%' or u.user_id ilike '%" . strtoupper($what_users)
                     . "%')";
    } else {
        $where_users .= " and (u.lastname like '%" . strtolower($what_users)
                     . "%' or u.lastname like '%" . strtoupper($what_users)
                     . "%' or u.firstname like '%" . strtolower($what_users)
                     . "%' or u.firstname like '%" . strtoupper($what_users)
                     . "%' or u.user_id like '%" . strtolower($what_users)
                     . "%' or u.user_id like '%" . strtoupper($what_users)
                     . "%')";
    }
    $orderby_users = ' order by u.user_id asc, u.lastname asc, u.firstname asc,'
                   . 'e.entity_label asc';

    if ($_SESSION['config']['databasetype'] == 'POSTGRESQL') {
        $where_entities_users .= " and (u.lastname ilike '%"
                              . strtolower($what_users) . "%' or u.lastname "
                              . "ilike '%" . strtoupper($what_users) . "%' or "
                              . "u.firstname ilike '%"
                              . strtolower($what_users) . "%' or u.firstname "
                              . "ilike '%" . strtoupper($what_users) . "%' or "
                              . "u.user_id ilike '%" . strtolower($what_users)
                              . "%' or u.user_id ilike '%"
                              . strtoupper($what_users) . "%')";
    } else {
        $where_entities_users .= " and (u.lastname like '%"
                              . strtolower($what_users) . "%' or u.lastname "
                              . "like '%" . strtoupper($what_users) . "%' or "
                              . "u.firstname like '%" . strtolower($what_users)
                              . "%' or u.firstname like '%"
                              . strtoupper($what_users) . "%' or u.user_id like"
                              . " '%" . strtolower($what_users) . "%' or "
                              . "u.user_id like '%" . strtoupper($what_users)
                              . "%')";
    }
    $orderby_entities = ' order by e.entity_label asc';
}
if (isset($_GET['what_services']) && ! empty($_GET['what_services'])) {
    //$where_entities_users = '';
    $what_services = addslashes(
        $func->wash($_GET['what_services'], 'no', '', 'no')
    );
    if ($_SESSION['config']['databasetype'] == 'POSTGRESQL') {
        $where_users .= " and (e.entity_label ilike '%"
                     . strtolower($what_services) . "%' or e.entity_id ilike '%"
                     . strtoupper($what_services) . "%')";
        $where_entities .= " and (e.entity_label ilike '%"
                        . strtolower($what_services) . "%' or e.entity_id ilike"
                        . " '%" . strtolower($what_services) . "%' )";
    } else {
        $where_users .= " and (e.entity_label like '%"
                     . strtolower($what_services) . "%' or e.entity_id like '%"
                     . strtoupper($what_services) . "%')";
        $where_entities .= " and (e.entity_label like '%"
                        . strtolower($what_services) . "%' or e.entity_id like"
                        . " '%" . strtolower($what_services) . "%' )";
    }
    $orderby_users = ' order by e.entity_label asc, u.user_id asc, '
                   . 'u.lastname asc, u.firstname asc';
    $orderby_entities = ' order by e.entity_label asc';
}
$db = new dbquery();
$db->connect();
$db->query(
    "select u.user_id, u.firstname, u.lastname,e.entity_id,  e.entity_label "
    . "FROM " . $_SESSION['tablename']['users'] . " u, " . ENT_ENTITIES . " e, "
    . ENT_USERS_ENTITIES . " ue WHERE u.status <> 'DEL' and u.enabled = 'Y' and"
    . " e.entity_id = ue.entity_id and u.user_id = ue.user_id and"
    . " e.enabled = 'Y' " . $where_users . $orderby_users
);
$i = 0;
while ($line = $db->fetch_object()) {
    array_push(
        $users,
        array(
            'ID'     => $db->show_string($line->user_id),
            'PRENOM' => $db->show_string($line->firstname),
            'NOM'    => $db->show_string($line->lastname),
            'DEP_ID' => $db->show_string($line->entity_id),
            'DEP'    => $db->show_string($line->entity_label)
        )
    );
}
if ($where_entities_users  == '') {
    $db->query(
        "select e.entity_id,  e.entity_label FROM  " . ENT_ENTITIES . " e WHERE"
        . " e.enabled = 'Y' " . $where_entities . $orderby_entities
    );
} else {
    $db->query(
        "select e.entity_id,  e.entity_label FROM "
        . $_SESSION['tablename']['users'] . " u, " . ENT_ENTITIES . " e, "
        . ENT_USERS_ENTITIES . " ue WHERE u.status <> 'DEL' and u.enabled = 'Y'"
        . "and  e.entity_id = ue.entity_id and u.user_id = ue.user_id and "
        . "e.enabled = 'Y' " . $where_entities_users . $orderby_users
    );
}

$i = 0;
while ($line = $db->fetch_object()) {
    array_push(
        $entities,
        array(
            'ID' => $db->show_string($line->entity_id),
            'DEP' =>$db->show_string($line->entity_label)
        )
    );
}

$id = '';
$desc = '';
if (! isset($_SESSION['m_admin']['entity']['listmodel']['copy']['users'])) {
    $_SESSION['m_admin']['entity']['listmodel']['copy']['users'] = array();
}
if (! isset($_SESSION['m_admin']['entity']['listmodel']['copy']['entities'])) {
    $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'] = array();
}
if (isset($_GET['action']) && $_GET['action'] == 'add_entity' ) {
    if (isset($_GET['id']) && ! empty($_GET['id'])) {
        $id = $_GET['id'];
        $find = false;
        for ($i = 0; $i < count(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['entities']
        ); $i ++) {
            if ($id ==
                $_SESSION['m_admin']['entity']['listmodel']['copy']['entities']
                    [$i]['entity_id']) {
                $find = true;
                break;
            }
        }
        if ($find == false) {
            $db->query(
                "SELECT  e.entity_id,  e.entity_label FROM " . ENT_ENTITIES
                . " e WHERE e.entity_id = '" . $db->protect_string_db($id) . "'"
            );
            $line = $db->fetch_object();
            array_push(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'],
                array(
                    'entity_id' => $db->show_string($id),
                    'entity_label' =>$db->show_string($line->entity_label)
                )
            );
        }
        usort(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'],
            'cmp_entity'
        );
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'add_user') {
    if (isset($_GET['id']) && ! empty($_GET['id'])) {
        $id = $_GET['id'];
        $find = false;
        if ($id ==
            $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) {
            $find = true;
        } else if (empty(
            $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']
        ) || ! isset(
            $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])
        ) {
            $db->query(
                "SELECT u.firstname, u.lastname, u.department, e.entity_id,  "
                . "e.entity_label FROM " . $_SESSION['tablename']['users']
                . " u,  " . ENT_ENTITIES . " e, " . ENT_USERS_ENTITIES
                . " ue WHERE  u.user_id='" . $db->protect_string_db($id)
                . "' and  e.entity_id = ue.entity_id and u.user_id = ue.user_id"
                . " and ue.primary_entity = 'Y'"
            );
            $line = $db->fetch_object();
            $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'] =
                $db->show_string($id);
            $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'] =
                $db->show_string($line->firstname);
            $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'] =
                $db->show_string($line->lastname);
            $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_id'] =
                $db->show_string($line->entity_id);
            $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label'] =
                $db->show_string($line->entity_label);
        } else {
            for ($i = 0; $i < count(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
            ); $i ++) {
                if ($id ==
                    $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
                        [$i]['user_id']) {
                    $find = true;
                    break;
                }
            }
            if ($find == false) {
                $db->query(
                    "SELECT u.firstname, u.lastname, u.department, e.entity_id,"
                    . " e.entity_label FROM " . $_SESSION['tablename']['users']
                    . " u,  " . ENT_ENTITIES . " e, " . ENT_USERS_ENTITIES
                    . " ue WHERE  u.user_id='" . $db->protect_string_db($id)
                    . "' and  e.entity_id = ue.entity_id and "
                    . "u.user_id = ue.user_id and ue.primary_entity = 'Y'"
                );
                $line = $db->fetch_object();
                array_push(
                    $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
                    array(
                        'user_id' => $db->show_string($id),
                        'firstname' =>$db->show_string($line->firstname),
                        'lastname' =>$db->show_string($line->lastname),
                        'entity_id' =>$db->show_string($line->entity_id),
                        'entity_label' =>$db->show_string($line->entity_label),
                    )
                );
            }
        }
        usort(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
            'cmp_users'
        );
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'remove_dest') {
    unset( $_SESSION['m_admin']['entity']['listmodel']['dest'] );
} else if (isset($_GET['action']) && $_GET['action'] == 'remove_entity' ) {
    $rank = $_GET['rank'];
    if (isset($_GET['id']) && ! empty($_GET['id'])) {
        $id = $_GET['id'];
        if ($_SESSION['m_admin']['entity']['listmodel']['copy']['entities']
            [$rank]['entity_id'] == $id) {
            unset(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['entities']
                    [$rank]
            );
            $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'] =
                array_values(
                    $_SESSION['m_admin']['entity']['listmodel']['copy']
                        ['entities']
            );
        }
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'remove_user' ) {
    $rank = $_GET['rank'];
    if (isset($_GET['id']) && ! empty($_GET['id'])) {
        $id = $_GET['id'];
        if ($_SESSION['m_admin']['entity']['listmodel']['copy']['users']
            [$rank]['user_id'] == $id) {
            unset(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
                    [$rank]
            );
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'] =
                array_values(
                    $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
            );
        }
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'dest_to_copy' ) {
    if (isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])
        && !empty(
            $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']
        )) {
        array_push(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
            array(
                'user_id' => $_SESSION['m_admin']['entity']['listmodel']
                                ['dest']['user_id'],
                'firstname' => $_SESSION['m_admin']['entity']['listmodel']
                                ['dest']['firstname'],
                'lastname' => $_SESSION['m_admin']['entity']['listmodel']
                                ['dest']['lastname'],
                'entity_id' =>$_SESSION['m_admin']['entity']['listmodel']
                                ['dest']['entity_id'],
                'entity_label' => $_SESSION['m_admin']['entity']['listmodel']
                                ['dest']['entity_label'],
           )
        );
        unset($_SESSION['m_admin']['entity']['listmodel']['dest']);
        usort(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
            'cmp_users'
        );
    }
} else if (isset($_GET['action']) && $_GET['action'] == 'copy_to_dest' ) {
    if (isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])
        && ! empty(
            $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']
        )) {
        array_push(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
            array(
                'user_id' => $_SESSION['m_admin']['entity']['listmodel']
                            ['dest']['user_id'],
                'firstname' => $_SESSION['m_admin']['entity']['listmodel']
                            ['dest']['firstname'],
                'lastname' => $_SESSION['m_admin']['entity']['listmodel']
                            ['dest']['lastname'],
                'entity_id' => $_SESSION['m_admin']['entity']['listmodel']
                            ['dest']['entity_id'],
                'entity_label' => $_SESSION['m_admin']['entity']['listmodel']
                            ['dest']['entity_label'],
                                                ));
        unset( $_SESSION['m_admin']['entity']['listmodel']['dest'] );
    }
    $rank = $_GET['rank'];
    if (isset(
        $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$rank]
        ['user_id'])
        && ! empty(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
                [$rank]['user_id']
    )) {
        $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'] =
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$rank]
                ['user_id'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'] =
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
                [$rank]['firstname'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'] =
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
                [$rank]['lastname'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_id'] =
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
                [$rank]['entity_id'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label'] =
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$rank]
                ['entity_label'];
        unset(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$rank]
        );
        $_SESSION['m_admin']['entity']['listmodel']['copy']['users'] =
            array_values(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['users']
            );
    }
    usort(
        $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
        'cmp_users'
    );
}

$core_tools->load_html();
$core_tools->load_header(_USER_ENTITIES_TITLE);
$time = $core_tools->get_session_time_expire();
?>
<body onload="setTimeout(window.close, <?php echo $time;?>*60*1000);">
<?php //$db->show_array($_SESSION['m_admin']['entity']['listmodel']);?>
    <?php $link = $_SESSION['config']['businessappurl']."index.php?display=true&module=entities&page=creation_listmodel";
        ?>
        <br/></br>
        <div align="center">
        <h2 class="tit"><?php echo _SEARCH_DIFF_LIST ?></h2>
        <form action="#" name="search_diff_list" >
            <input type="hidden" name="display" value="true" />
            <input type="hidden" name="module" value="entities" />
            <input type="hidden" name="page" value="creation_listmodel" />
        <table cellpadding="2" cellspacing="2" border="0">
            <tr>
                <th>
                    <label for="what_users" class="bold"><?php echo _LASTNAME;?> / <?php echo _FIRSTNAME;?> / <?php echo _ID; ?></label>
                </th>
                <th>
                    <input name="what_users" id="what_users" type="text" <?php if(isset($_GET["what_users"])) echo "value ='".$_GET["what_users"]."'"; ?> />
                </th>
            </tr>
            <tr>
                <th>
                    <label for="what_services" class="bold"><?php echo _DEPARTMENT;?></label>
                </th>
                <th>
                    <input name="what_services" id="what_services" type="text" <?php if(isset($_GET["what_services"])) echo "value ='".$_GET["what_services"]."'"; ?>/>
                </th>
            </tr>
        </table>
        </form>
        <script type="text/javascript">repost('<?php echo $link;?>',new Array('diff_list'),new Array('what_users','what_services'),'keyup',250);</script>
        <br/></br><br/></br><br/></br>
        </div>
        <?php if((isset($_GET['what_users']) && !empty($_GET['what_users'])) || (isset($_GET['what_services']) && !empty($_GET['what_services'])) || !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']  ) || count($_SESSION['m_admin']['entity']['listmodel']['copy']['users']) > 0 || count($_SESSION['m_admin']['entity']['listmodel']['copy']['entities']) > 0)
        { ?>

        <div id="diff_list" align="center">
        <h2 class="tit"><?php echo _DIFFUSION_LIST;?></h2>


        <?php if(isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) && !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']))
        {
            ?>
            <h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
            <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
             <tr>
             <td><img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=manage_users_entities_b.gif&module=entities';?>" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'];?></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'];?></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label']; ?></td>
                <td class="action_entities"><a href="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=creation_listmodel&action=remove_dest" class="delete"><?php echo _DELETE;?></a></td>
                <td class="action_entities"><a href="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=creation_listmodel&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=dest_to_copy" class="down"><?php echo _TO_CC;?></a></td>
         </tr>
    </table>
    <?php } ?>
    <br/>
    <?php if( count($_SESSION['m_admin']['entity']['listmodel']['copy']['users']) > 0 || count($_SESSION['m_admin']['entity']['listmodel']['copy']['entities']) > 0)
        {
            ?>
            <h2 class="sstit"><?php echo _TO_CC;?></h2>
            <table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec">
     <?php $color = ' class="col"';
    for($i=0;$i<count($_SESSION['m_admin']['entity']['listmodel']['copy']['entities']);$i++)
    {
        if($color == ' class="col"')
        {
            $color = '';
        }
        else
        {
            $color = ' class="col"';
        }
            ?>
     <tr <?php echo $color; ?> >
            <td><img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=manage_entities_b.gif&module=entities';?>" alt="<?php echo _ENTITY;?>" title="<?php echo _ENTITY;?>" /></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'][$i]['entity_id'];?></td>
            <td colspan="2"><?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'][$i]['entity_label'];?></td>
            <td class="action_entities"><a href="<?php echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=remove_entity&rank=<?php echo $i;?>&id=<?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['entities'][$i]['entity_id'];?>" class="delete"><?php echo _DELETE;?></a></td>

            <td  >&nbsp;</td>
    </tr>
<?php }
    for($i=0;$i<count($_SESSION['m_admin']['entity']['listmodel']['copy']['users']);$i++)
    {
        if($color == ' class="col"')
        {
            $color = '';
        }
        else
        {
            $color = ' class="col"';
        }
            ?>
        <tr <?php echo $color; ?> >
            <td><img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=manage_users_entities_b.gif&module=entities';?>" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$i]['lastname'];?></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$i]['firstname'];?></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$i]['entity_label']; ?></td>
            <td class="action_entities"><a href="<?php echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=remove_user&rank=<?php echo $i;?>&id=<?php echo $_SESSION['m_admin']['entity']['listmodel']['copy']['users'][$i]['user_id'];?>" class="delete"><?php echo _DELETE;?></a></td>
            <td class="action_entities"><a href="<?php echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=copy_to_dest&rank=<?php echo $i;?>" class="up"><?php echo _TO_DEST;?></a></td>
        </tr>
<?php }
        ?>
        </table>
        <br/>

    <?php }
        else
        {
            ?>
            <h2 class="sstit"><?php echo _NO_LINKED_DIFF_LIST;?></h2>
            <?php }
        ?>
        <br/>
        <form name="pop_diff" method="post" >
    <div align="center">
    <?php
    if(isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) && !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']))
        {?>
    <input align="middle" type="submit" value="<?php echo _VALIDATE;?>" class="button" name="valid"  />
    <?php }
    else
    {
        echo '<div class="error">'._MUST_CHOOSE_DEST.'</div>';
    } ?>
    <input align="middle" type="button" value="<?php echo _CANCEL;?>"  onclick="self.close();" class="button"/>

    </div>
    </form>
    <br/>
    <br/>
    <hr align="center" color="#6633CC" size="5" width="60%">
    <br/>

    <div align="center">
        <h2 class="tit"><?php echo _ENTITIES_LIST;?></h2>

            <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
            <thead>
                <tr>
                    <th><?php echo _ID;?></th>
                    <th><?php echo _DEPARTMENT;?></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>

            <?php $color = ' class="col"';
            for($j=0; $j < count($entities); $j++)
            {
                if($color == ' class="col"')
                {
                    $color = '';
                }
                else
                {
                    $color = ' class="col"';
                }
            ?>
                <tr <?php echo $color; ?>>
                    <td><?php echo $entities[$j]['ID'];?></td>
                    <td><?php echo $entities[$j]['DEP']; ?></td>
                    <td class="action_entities"><a href="<?php echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=add_entity&id=<?php echo $entities[$j]['ID'];?>" class="change"><?php echo _ADD_CC;?></a></td>
            </tr>
            <?php }
            ?>
        </table>
    </div>
    <div align="center">
        <h2 class="tit"><?php echo _USERS_LIST;?></h2>

            <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
            <thead>
                <tr>
                    <th><?php echo _ID;?></th>
                    <th ><?php echo _LASTNAME;?> </th>
                    <th ><?php echo _FIRSTNAME;?></th>
                    <th><?php echo _DEPARTMENT;?></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>

            <?php $color = ' class="col"';
            for($j=0; $j < count($users); $j++)
            {
                if($color == ' class="col"')
                {
                    $color = '';
                }
                else
                {
                    $color = ' class="col"';
                }
            ?>
                <tr <?php echo $color; ?>>
                <td><?php echo $users[$j]['ID'];?></td>
                <td><?php echo $users[$j]['NOM']; ?></td>
                <td><?php echo $users[$j]['PRENOM']; ?></td>
                <td><?php echo $users[$j]['DEP'];?></td>
                <td class="action_entities"><a href="<?php echo $link;?>&what_users=<?php echo $what_users; ?>&what_services=<?php echo $what_services; ?>&action=add_user&id=<?php echo $users[$j]['ID'];?>" class="change"><?php echo _ADD;?></a></td>
            </tr>
            <?php }
            ?>
        </table>
    </div>
</div>

<?php }
else
{
?>
<div id="diff_list" align="center">
 <br/>
  <br/>
  <br/>
  <br/>
   <h2 class="tit"><?php echo _MANAGE_MODEL_LIST_TITLE;?> </h2>
    <table width="79%" border="0">
    <tr>
      <td><p align="center"><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=separateur_1.jpg" width="800" height="1" alt="" /><br/><?php echo _WELCOME_MODEL_LIST_TITLE;?>.<br/><br/>
          <?php //echo _MODEL_LIST_EXPLANATION1;?>.</p>
       <!-- <p align="center"><?php echo _ADD_USER_TO_LIST_EXPLANATION.', '._CLICK_ON;?> : <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_change.gif" width="21" height="21" alt="" />.</p>
        <p align="center"><?php echo _REMOVE_USER_FROM_LIST_EXPLANATION.', '._CLICK_ON;?> : <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_delete.gif" width="19" height="19" alt="" />.</p>
        <p align="center"><?php echo _TO_MODIFY_LIST_ORDER_EXPLANATION;?> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=arrow_down.gif" width="16" height="16" alt="" /> <?php echo _AND;?> <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=arrow_up.gif" width="16" height="16" alt=""/>. <br/><br/><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=separateur_1.jpg" width="800" height="1" alt=""/></p>-->
        </td>
    </tr>
    </table>
    <input align="middle" type="button" value="<?php echo _CANCEL;?>" class="button"  onclick="self.close();"/>
    </div>
  <?php }
        ?>

<br/>
</body>
</html>
