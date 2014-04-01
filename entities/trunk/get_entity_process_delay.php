<?php
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once('modules'.DIRECTORY_SEPARATOR.'reports'.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_graphics.php");
require_once("modules/entities/entities_tables.php");
$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";


$graph = new graphics();
$req = new request();
$db = new dbquery();
$sec = new security();
$period_type = $_REQUEST['period_type'];
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

if($period_type == 'period_year')
{
    if(empty($_REQUEST['the_year']) || !isset($_REQUEST['the_year']))
    {
        ?>
        <div class="error"><?php echo _YEAR.' '._MISSING;?></div>
        <?php
        exit();
    }
    if( !preg_match('/^[1-2](0|9)[0-9][0-9]$/', $_REQUEST['the_year']))
    {
        ?>
        <div class="error"><?php echo _YEAR.' '._WRONG_FORMAT;?></div>
        <?php
        exit();
    }
    $where_date = " and ".$req->extract_date('creation_date', 'year')." = '".$_REQUEST['the_year']."'";
    $date_title = _FOR_YEAR.' '.$_REQUEST['the_year'];
}
else if($period_type == 'period_month')
{
    $arr_month = array('01','02','03','04','05','06','07','08','09','10','11','12');
    if(empty($_REQUEST['the_month']) || !isset($_REQUEST['the_month']))
    {
        ?>
        <div class="error"><?php echo _MONTH.' '._MISSING;?></div>
        <?php
        exit();
    }
    if( !in_array($_REQUEST['the_month'], $arr_month))
    {
        ?>
        <div class="error"><?php echo _MONTH.' '._WRONG_FORMAT;?></div>
        <?php
        exit();
    }
    $where_date = " and ".$req->extract_date('creation_date', 'year')." = '".$default_year."' and ".$req->extract_date('creation_date', 'month')." = '".$_REQUEST['the_month']."'";
    $month = '';
    switch($_REQUEST['the_month'])
    {
        case '01':
            $month = _JANUARY;
            break;
        case '02':
            $month = _FEBRUARY;
            break;
        case '03':
            $month = _MARCH;
            break;
        case '04':
            $month = _APRIL;
            break;
        case '05':
            $month = _MAY;
            break;
        case '06':
            $month = _JUNE;
            break;
        case '07':
            $month = _JULY;
            break;
        case '08':
            $month = _AUGUST;
            break;
        case '09':
            $month = _SEPTEMBER;
            break;
        case '10':
            $month = _OCTOBER;
            break;
        case '11':
            $month = _NOVEMBER;
        case '12':
            $month = _DECEMBER;
            break;
        default:
            $month = '';
    }
    $date_title = _FOR_MONTH.' '.$month;
}
else
{
    ?>
    <div class="error"><?php echo _PERIOD.' '._MISSING;?></div>
    <?php
    exit();
}


$title = _ENTITY_PROCESS_DELAY.' '.$date_title ;
$db = new dbquery();

//Récupération de l'ensemble des types de documents
$db->query("select entity_id, short_label from ".ENT_ENTITIES." where enabled = 'Y' order by short_label");
$doctypes = array();
while($res = $db->fetch_object())
{
    array_push($doctypes, array('ID' => $res->entity_id, 'LABEL' => $res->short_label));
}


if($report_type == 'graph')
{
    $val_an = array();
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
	
$totalDocTypes = count($doctypes);
	
for($i=0; $i<count($doctypes);$i++)
{
    //Permet d'afficher ou non les entités dont le nombre de courrier est égal à 0
	$valid = true;
    if ($_SESSION['user']['entities']){
        foreach($_SESSION['user']['entities'] as $user_ent){
            if ($doctypes[$i]['ID'] == $user_ent['ENTITY_ID']){
                $valid = true;
            }
        }
    }
    if ($valid == 'true' || $_SESSION['user']['UserId'] == "superadmin")
    {

        $db->query("SELECT ".$req->get_date_diff('closing_date', 'creation_date' )." AS delay FROM ".$view." WHERE  status in ".$str_status." ".$where_date.$where_clause." AND status in ".$str_status." and destination = '".$doctypes[$i]['ID']."'");

        if( $db->nb_result() > 0)
        {
            $tmp = 0;
            while($res = $db->fetch_object())
            {
                $tmp = $tmp + $res->delay;
            }
            if($report_type == 'graph')
            {
                array_push($val_an, (string)$tmp / $db->nb_result());
            }
            elseif($report_type == 'array')
            {
                array_push($data, array('LABEL' => $db->show_string($doctypes[$i]['LABEL']), 'VALUE' => (string)$tmp / $db->nb_result()));
            }
            if($tmp / $db->nb_result() > 0)
            {
                $has_data = true;
            }
        }
        else
        {
            if($report_type == 'graph')
            {
                array_push($val_an, 0);
            }
            elseif($report_type == 'array')
            {
                array_push($data, array('LABEL' => $db->show_string($doctypes[$i]['LABEL']), 'VALUE' => _UNDEFINED));
            }
        }
        if($report_type == 'graph')
        {
            array_push($_SESSION['labels1'], $db->show_string($doctypes[$i]['LABEL']));
        }
    }
}

if($report_type == 'graph')
{
    $largeur=50*$totalDocTypes;
    if ($totalDocTypes<20){
        $largeur=1000;
    }

    $src1 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=histo&largeur=$largeur&hauteur=600&marge_bas=250&title=".$title."&labelY="._N_DAYS;
    for($i=0;$i<count($_SESSION['labels1']);$i++)
    {
        //$src1 .= "&labels[]=".$_SESSION['labels1'][$i];
    }
    $_SESSION['GRAPH']['VALUES']='';
    for($i=0;$i<count($val_an);$i++)
    {
        //$src1 .= "&values[]=".$val_an[$i];
        $_SESSION['GRAPH']['VALUES'][$i]=$val_an[$i];
    }
}
elseif($report_type == 'array')
{
    array_unshift($data, array('LABEL' => _DOCTYPE, 'VALUE' => _PROCESS_DELAY));
}

if ( $has_data)
{
    if($report_type == 'graph')
    {
    ?>
        <div style="overflow:auto"><img src="<?php echo $src1;?>" alt="<?php echo $title;?>"/></div><?php
     }
    elseif($report_type == 'array')
    {
		$data2=urlencode(serialize($data));
		$form =	"<input type='button' class='button' value='Exporter les données' onclick='record_data(\"".$data2."\")' style='float:right;'/>";
		echo $form;
		
        $graph->show_stats_array($title, $data);
    }
}
else
{
    echo '<br/><br/><div class="error">'._NO_DATA_MESSAGE.'</div>';
}
exit();

?>
