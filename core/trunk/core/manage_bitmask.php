<?php


function check_right($int_to_check, $right)
{
	if($int_to_check & $right)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function set_right($int_to_set = 0, $right)
{
	return $int_to_set | $right;	
}

?>
