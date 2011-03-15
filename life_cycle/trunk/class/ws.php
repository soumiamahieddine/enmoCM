<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

/**************************************************************************************************/
// LC_POLICIES
$SOAP_typedef['lcPolicies'] = array(    'policy_id' => 'string',
                                        'policy_name' => 'string',
                                        'policy_desc' => 'string',
                                        );
$SOAP_dispatch_map['policySave'] = array(
                                        'in'  => array('policy' => '{urn:MySoapServer}lcPolicies', 'mode' => 'string'),
                                        'out' => array('out' => '{urn:MySoapServer}returnArray'),
                                        'method' => "modules/life_cycle#lc_policies::save",
                                        );
$SOAP_dispatch_map['policyDelete'] = array(
                                        'in'  => array('policy' => '{urn:MySoapServer}lcPolicies'),
                                        'out' => array('out' => '{urn:MySoapServer}returnArray'),
                                        'method' => "modules/life_cycle#lc_policies::delete",
                                        );
$SOAP_dispatch_map['policyGet'] = array(
                                        'in'  => array('policyId' => 'string'),
                                        'out' => array('out' => '{urn:MySoapServer}lcPolicies'),
                                        'method' => "modules/life_cycle#lc_policies::getWs",
                                        );
/**************************************************************************************************/
// LC_CYCLES
$SOAP_typedef['lcCycles'] = array(      'policy_id' => 'string',
                                        'cycle_id' => 'string',
                                        'cycle_desc' => 'string',
                                        'sequence_number' => 'integer',
                                        'where_clause' => 'string',
                                        'break_key' => 'string',
                                        'validation_mode' => 'string',
                                        );
$SOAP_dispatch_map['cycleSave'] = array(
                                        'in'  => array('cycle' => '{urn:MySoapServer}lcCycles', 'mode' => 'string'),
                                        'out' => array('out' => '{urn:MySoapServer}returnArray'),
                                        'method' => "modules/life_cycle#lc_cycles::save",
                                        );
$SOAP_dispatch_map['cycleDelete'] = array(
                                        'in'  => array('cycle' => '{urn:MySoapServer}lcCycles'),
                                        'out' => array('out' => '{urn:MySoapServer}returnArray'),
                                        'method' => "modules/life_cycle#lc_cycles::delete",
                                        );
$SOAP_dispatch_map['cycleGet'] = array(
                                        'in'  => array('cycleId' => 'string'),
                                        'out' => array('out' => '{urn:MySoapServer}lcCycles'),
                                        'method' => "modules/life_cycle#lc_cycles::getWs",
                                        );
/**************************************************************************************************/
// LC_CYCLE_STEPS
$SOAP_typedef['lcCycleSteps'] = array(      'policy_id' => 'string',
                                            'cycle_id' => 'string',
                                            'cycle_step_id' => 'string',
                                            'cycle_step_desc' => 'string',
                                            'docserver_type_id' => 'string',
                                            //'is_allow_failure' => 'boolean',
                                            'step_operation' => 'string',
                                            'sequence_number' => 'integer',
                                            //'is_must_complete' => 'boolean',
                                            'preprocess_script' => 'string',
                                            'postprocess_script' => 'string',
                                            );
$SOAP_dispatch_map['cycleStepSave'] = array(
                                        'in'  => array('cycleStep' => '{urn:MySoapServer}lcCycleSteps', 'mode' => 'string'),
                                        'out' => array('out' => '{urn:MySoapServer}returnArray'),
                                        'method' => "modules/life_cycle#lc_cycle_steps::save",
                                        );
$SOAP_dispatch_map['cycleStepDelete'] = array(
                                        'in'  => array('cycleStep' => '{urn:MySoapServer}lcCycleSteps'),
                                        'out' => array('out' => '{urn:MySoapServer}returnArray'),
                                        'method' => "modules/life_cycle#lc_cycle_steps::delete",
                                        );
$SOAP_dispatch_map['cycleStepGet'] = array(
                                        'in'  => array('cycleStepId' => 'string'),
                                        'out' => array('out' => '{urn:MySoapServer}lcCycleSteps'),
                                        'method' => "modules/life_cycle#lc_cycle_steps::getWs",
                                        );
