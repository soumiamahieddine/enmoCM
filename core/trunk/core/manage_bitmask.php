<?php


function check_right($int_to_check, $right)
{
	if($int_to_check & $right)
	{
		echo 'true';
		return true;
	}
	else
	{
		echo 'false';
		return false;
	}
}

function set_right($int_to_set = 0, $right)
{
	return $int_to_set | $right;	
}

?>
