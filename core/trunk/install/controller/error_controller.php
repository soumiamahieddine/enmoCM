<?php
//CONTROLLER
    $error = $_REQUEST['error'];
    switch ($error) {
        case 'noStep':
            $infosError = _NO_STEP;
            break;

        case 'badStep':
            $infosError = _BAD_STEP;
            break;

        default:
           $infosError  = _INSTALL_ISSUE . '. ' . _TRY_AGAIN . '.';
    }

    //TITLE
    $shortTitle = _ERROR;
    $longTitle = _ERROR;

//VIEW
    $view = 'error';
