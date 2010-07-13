<?php
$error = '';
function get_values_in_array($val)
{
	$tab = explode('$$',$val);
	$values = array();
	for($i=0; $i<count($tab);$i++)
	{
		$tmp = explode('#', $tab[$i]);
		array_push($values, array('ID' => $tmp[0], 'VALUE' => trim($tmp[1])));
	}
	return $values;
}

function get_value_fields($values, $field)
{
	for($i=0; $i<count($values);$i++)
	{
		if($values[$i]['ID'] == $field)
		{
			return 	$values[$i]['VALUE'];
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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
include('apps/maarch_entreprise/security_bitmask.php');
include('core/manage_bitmask.php');

$values = get_values_in_array($_REQUEST['form_values']);
$coll_id = get_value_fields($values, 'coll_id');
$comment = get_value_fields($values, 'comment');
$where = get_value_fields($values, 'where');
$start_date = get_value_fields($values, 'start_date');
$stop_date = get_value_fields($values, 'stop_date');
$mode = get_value_fields($values, 'mode');

$target_all = get_value_fields($values, 'target_all');
$target_doc = get_value_fields($values, 'target_doc');
$target_class = get_value_fields($values, 'target_class');
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

$sec = new security();
$sec->add_grouptmp_session($coll_id, $where , $target, $bitmask, $comment, $mode, $start_date, $stop_date);
echo "{status : 0, error_txt : '".$error."'}";
exit();
?>
