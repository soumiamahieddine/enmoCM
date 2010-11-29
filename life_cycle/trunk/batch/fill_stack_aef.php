<?php
$statesArray = array("INIT", "PARAM_OK", "RESOURCES_SELECTED", "STACK_FILED", "END");
$nbStates = count($statesArray);
$conditionsArray = array("PARAM_CORRECT", "RESOURCES_FOUND", "DOCSERVERS_FOUND", "STACK_UPRIGHT");
$nbConditions = count($conditionsArray);

function begin_process() {
	echo "action:begin process\r\n";
}

function fill_resources() {
	echo "action:fill resources\r\n";
}

function reserve_aip() {
	echo "action:reserve aip\r\n";
}

function fill_stack() {
	echo "action:fill stack\r\n";
}

function simulation($cond, $state) {
	if($state <> "") {
		echo "condition:".$cond."\r\n";
	}
}

print_r($statesArray);
?>
