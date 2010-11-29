<?php 
require 'FSM.php';
$conditionsArray = array("PARAM_CORRECT", "RESOURCES_FOUND", "DOCSERVERS_FOUND", "STACK_UPRIGHT");
$statesArray = array();
$fsm = new FSM('INIT', $statesArray);

function ErrorCallback($symbol, $payload) {
    echo "This symbol does not compute: $symbol\n";
}

function begin_process($symbol, $payload) {
    echo "begin_process\n";
}

function fill_resources($symbol, $payload) {
    echo "fill_resources\n";
}

function reserve_aip($symbol, $payload) {
    echo "reserve_aip\n";
}

function fill_stack($symbol, $payload) {
    echo "fill_stack\n";
}

$fsm->addTransition('PARAM_CORRECT', 'INIT', 'PARAM_OK', 'begin_process');
$fsm->addTransition('RESOURCES_FOUND', 'PARAM_OK', 'RESOURCES_SELECTED', 'fill_resources');
$fsm->addTransition('DOCSERVERS_FOUND', 'RESOURCES_SELECTED', 'AIP_RESERVED', 'reserve_aip');
$fsm->addTransition('STACK_UPRIGHT', 'AIP_RESERVED', 'END', 'fill_stack');
$fsm->setDefaultTransition('INIT', 'ErrorCallback');

/*$fsm->process('PARAM_CORRECT');
$fsm->process('RESOURCES_FOUND');
$fsm->process('DOCSERVERS_FOUND');
$fsm->process('STACK_UPRIGHT');*/

$fsm->processList($conditionsArray);
//$fsm->process('PARAM_CORRECT');
//print_r($fsm->getTransition('RESOURCES_FOUND'));
/*require_once 'FSM/GraphViz.php';
$converter = new FSM_GraphViz($fsm);
$graph = $converter->export();
$graph->image('png');*/

?>
