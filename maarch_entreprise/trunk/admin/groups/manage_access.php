<?php
$error = '';
function get_values_in_array($val)
{
    $tab = explode('$$',$val);
    $values = array();
    for($i=0; $i<count($tab);$i++)
    {
        $tmp = explode('#', $tab[$i]);
        if(isset($tmp[1]))
        {
            array_push($values, array('ID' => $tmp[0], 'VALUE' => trim($tmp[1])));
        }
    }
    return $values;
}

function get_value_fields($values, $field)
{
    for($i=0; $i<count($values);$i++)
    {
        if($values[$i]['ID'] == $field)
        {
            return  $values[$i]['VALUE'];
        }
    }
    return false;
}

if(!isset($_REQUEST['form_values']) || empty($_REQUEST['form_values']))
{
    $error = _ERROR_FORM_VALUES."<br/>";
    echo "{status : 1, error_txt : '".$error."'}";
    exit();
}

try{
    include('apps/'.$_SESSION['config']['app_id'].'/security_bitmask.php');
    include('core/manage_bitmask.php');
    include('core/class/class_security.php');
} catch (Exception $e){
    echo $e->getMessage();
}

$values = get_values_in_array($_REQUEST['form_values']);

$coll_id = get_value_fields($values, 'coll_id');
$comment = get_value_fields($values, 'comment');
$where = get_value_fields($values, 'where');
$start_date = get_value_fields($values, 'start_date');
$stop_date = get_value_fields($values, 'stop_date');
$mode = get_value_fields($values, 'mode');

$target_all = get_value_fields($values, 'target_ALL');
$target_doc = get_value_fields($values, 'target_DOC');
$target_class = get_value_fields($values, 'target_CLASS');
$target = 'ALL';
if(isset($target_all) && !empty($target_all))
{
    $target = $target_all;
}
elseif(isset($target_doc) && !empty($target_doc))
{
    $target = $target_doc;
}
elseif(isset($target_class) && !empty($target_class))
{
    $target = $target_class;
}

$bitmask = 0;
for($i=0; $i<count($_ENV['security_bitmask']); $i++)
{
    $tmp = get_value_fields($values, $_ENV['security_bitmask'][$i]['ID']);
    if(isset($tmp) && $tmp == 'true')
    {
        $bitmask = set_right($bitmask, $_ENV['security_bitmask'][$i]['ID'] );
    }
}

if($mode == "up")
{
    for($i=0;$i< count($_SESSION['m_admin']['groups']['security']);$i++)
    {
        if($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'] == $coll_id)
        {
            $_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'] = $where;
            $_SESSION['m_admin']['groups']['security'][$i]['COMMENT'] = $comment;
            $_SESSION['m_admin']['groups']['security'][$i]['WHERE_TARGET'] = $target;
            $_SESSION['m_admin']['groups']['security'][$i]['RIGHTS_BITMASK'] = $bitmask;
            $_SESSION['m_admin']['groups']['security'][$i]['START_DATE'] = $start_date;
            $_SESSION['m_admin']['groups']['security'][$i]['STOP_DATE'] = $stop_date;

            break;
        }
    }
}
else
{
    $ind = security::get_ind_collection($coll_id);
    array_push($_SESSION['m_admin']['groups']['security'] , array('GROUP_ID' => '' , 'COLL_ID' => $coll_id , 'IND_COLL_SESSION' => $ind,'WHERE_CLAUSE' => $where, 'COMMENT' => $comment , 'WHERE_TARGET' => $target, 'RIGHTS_BITMASK' => $bitmask, 'START_DATE' => $start_date, 'STOP_DATE' => $stop_date));
    $_SESSION['m_admin']['load_security'] = false;
}
echo "{status : 0, error_txt : '".$error."'}";
exit();
?>
