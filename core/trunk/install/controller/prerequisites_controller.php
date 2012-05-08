<?php
//CONTROLLER

    //TITLE
        $shortTitle = _PREREQUISITES;
        $longTitle = _PREREQUISITES;

    //CAN CONTINUE
        $canContinue = $Class_Install->checkAllNeededPrerequisites();

    //PROGRESS
        $stepNb = 4;
        $stepNbTotal = 8;

//VIEW
    $view = 'prerequisites';
