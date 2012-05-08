<?php
//CONTROLLER
    //TITLES
        $shortTitle = _LANGUAGE;
        $longTitle = _CHOOSE_LANGUAGE;

    //ALLOWED LANGUAGES
        $listLang = $Class_Install->getLangList();

    //PROGRESS
        $stepNb = 1;
        $stepNbTotal = 8;

//VIEW
    $view = 'language';
