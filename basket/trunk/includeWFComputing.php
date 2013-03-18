<?php

$myTurnInTheWF = false;
//is it my turn in the WF ?
$myTurnInTheWF = $b->isItMyTurnInTheWF(
    $_SESSION['user']['UserId'],
    $res_id,
    $coll_id
);
if ($myTurnInTheWF) {
    $rolesArr = array();
    //get the roles in the wf of the user
    $rolesArr = $b->whatAreMyRoleInTheWF(
        $_SESSION['user']['UserId'],
        $res_id,
        $coll_id
    );
    //print_r($rolesArr);
    if (!empty($rolesArr)) {
        $rolesInTheWF = array();
        for ($cptRoles=0;$cptRoles<count($rolesArr);$cptRoles++) {
            $sequence = $b->whatIsMySequenceForMyRole(
                $_SESSION['user']['UserId'],
                $res_id,
                $coll_id,
                $rolesArr[$cptRoles]
            );
            array_push(
                $rolesInTheWF,
                array(
                    'role' => $rolesArr[$cptRoles],
                    'sequence' => $sequence,
                    'isThereSomeoneAfterMeInTheWF' =>$b->isThereSomeoneAfterMeInTheWF(
                        $res_id,
                        $coll_id,
                        $rolesArr[$cptRoles],
                        $sequence
                    ),
                    'theNextInTheWF' =>$b->whoseTheNextInTheWF(
                        $res_id,
                        $coll_id,
                        $rolesArr[$cptRoles],
                        $sequence
                    ),
                    'isThereSomeoneBeforeMeInTheWF' =>$b->isThereSomeoneBeforeMeInTheWF(
                        $res_id,
                        $coll_id,
                        $rolesArr[$cptRoles],
                        $sequence
                    ),
                    'thePreviousInTheWF' =>$b->whoseThePreviousInTheWF(
                        $res_id,
                        $coll_id,
                        $rolesArr[$cptRoles],
                        $sequence
                    ),
                )
            );
        }
    }
    //print_r($rolesInTheWF);
}
