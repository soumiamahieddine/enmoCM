<?php
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once('modules'.DIRECTORY_SEPARATOR.'reports'.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_graphics.php");
require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'entities_tables.php');

$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";


$graph = new graphics();
$req = new request();
$db = new dbquery();
$sec = new security();

$status_obj = new manage_status();
$ind_coll = $sec->get_ind_collection('letterbox_coll');
$table = $_SESSION['collections'][$ind_coll]['table'];
$view = $_SESSION['collections'][$ind_coll]['view'];
$search_status = $status_obj->get_searchable_status();
$default_year = date('Y');
$report_type = $_REQUEST['report_type'];
$core_tools = new core_tools();
$core_tools->load_lang();

//Limitation aux documents pouvant être recherchés
$str_status = '(';
for($i=0;$i<count($search_status);$i++)
{
    $str_status .= "'".$search_status[$i]['ID']."',";
}
$str_status = preg_replace('/,$/', ')', $str_status);

$title = _ENTITY_LATE_MAIL.' '.$date_title ;
$db = new dbquery();

//Récupération de l'ensemble des types de documents
$db->query("select entity_id, short_label from ".ENT_ENTITIES." where enabled = 'Y' order by short_label");
$entities = array();
while($res = $db->fetch_object())
{
    array_push($entities, array('ID' => $res->entity_id, 'LABEL' => $res->short_label));
}

if($report_type == 'graph')
{
    $vol = array();
    $_SESSION['labels1'] = array();
}
elseif($report_type == 'array')
{
    $data = array();
}
$has_data = false;

//Utilisation de la clause de sécurité de Maarch

$where_clause = $sec->get_where_clause_from_coll_id('letterbox_coll');
//var_dump($where_clause);
if ($where_clause)
    $where_clause = " and ".$where_clause;

for($i=0; $i<count($entities);$i++)
{
    $valid = false;
    if ($_SESSION['user']['entities']){
        foreach($_SESSION['user']['entities'] as $user_ent){
            if ($entities[$i]['ID'] == $user_ent['ENTITY_ID']){
                $valid = true;
            }
        }
    }
    if ($valid == 'true' || $_SESSION['user']['UserId'] == "superadmin")
    {
/*
    $this->query("select l.res_id  from ".$_SESSION['ressources']['letterbox_view']." r, ".$_SESSION['tablename']['listinstance']." l  where r.res_id=l.res_id and l.item_id='".$user['ID']."'  and item_type = 'user_id' and  r.flag_alarm1 = 'N' and (r.status = 'NEW' or r.status = 'COU') and date(r.alarm1_date) =date(now()) and l.item_mode = 'dest' and item_type='user_id'");
*/


        $db->query("SELECT count(res_id) AS total FROM ".$view." WHERE  status in ".$str_status." ".$where_clause." and destination = '".$entities[$i]['ID']."' and date(alarm1_date) <= date(now()) ");
        if( $db->nb_result() > 0)
        {
            $tmp = 0;
            $res = $db->fetch_object();

            if($report_type == 'graph')
            {

                array_push($vol, $res->total);
            }
            elseif($report_type == 'array')
            {
                array_push($data, array('LABEL' => $entities[$i]['LABEL'], 'VALUE' => $res->total ));
            }
            if($res->total > 0)
            {
                $has_data = true;
            }
        }
        else
        {
            if($report_type == 'graph')
            {

                array_push($vol, 0);
            }
            elseif($report_type == 'array')
            {
                array_push($data, array('LABEL' => $entities[$i]['LABEL'], 'VALUE' => _UNDEFINED));
            }
        }
        if($report_type == 'graph')
        {
            array_push($_SESSION['labels1'], $db->wash_html($entities[$i]['LABEL'], 'NO_ACCENT'));
        }
    }
}

if($report_type == 'graph')
{
    $src1 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=histo&largeur=1000&hauteur=400&title=".$title."&labelX="._MONTH."&labelY="._N_DAYS;
    for($i=0;$i<count($_SESSION['labels1']);$i++)
    {
        $src1 .= "&labels[]=".$_SESSION['labels1'][$i];
    }
    for($i=0;$i<count($vol);$i++)
    {
        $src1 .= "&values[]=".$vol[$i];
    }
}
elseif($report_type == 'array')
{
    array_unshift($data, array('LABEL' => _ENTITY, 'VALUE' => _NB_DOCS));
}

if ($has_data) {
    if($report_type == 'graph') {
    ?>
        <img src="<?php echo $src1;?>" alt="<?php echo $title;?>"/><?php
    } elseif($report_type == 'array') {
        $graph->show_stats_array($title, $data);
    }
} else {
    echo '<br/><br/><div class="error">'._NO_DATA_MESSAGE.'</div>';
}
exit();

?>
