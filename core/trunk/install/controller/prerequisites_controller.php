<?php
//CONTROLLER

    //TITLE
        $shortTitle = _PREREQUISITES;
        $longTitle = _PREREQUISITES;

    //CAN CONTINUE
        $canContinue = $Class_Install->checkAllNeededPrerequisites();

//VIEW
    $view = 'prerequisites';
