<?php
//CONTROLLER

    //TITLE
        $shortTitle = _LICENCE;
        $longTitle = _LICENCE;

    //LICENCE FILE
        $pathToLicenceTxt = 'view/text/licence_'.$Class_Install->getActualLang().'.txt';
        if (!file_exists($pathToLicenceTxt)) {
            $pathToLicenceTxt = 'view/text/licence_en.txt';
        }

        $fileLicence = file($pathToLicenceTxt);
        $txtLicence = '';
        for ($i=0;$i<count($fileLicence);$i++) {
            $txtLicence .= str_replace(array('<', '>'), array('&lt;', '&gt;'),$fileLicence[$i]).'<br />';
        }

//VIEW
    $view = 'licence';
