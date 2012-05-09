<?php

include_once '../core/init.php';

//MODEL
    require_once('class/Class_Install.php');
    $Class_Install = new Install;

//CONTROLLER
    if (isset($_SESSION['previousStep']) && !empty($_SESSION['previousStep'])) {
        header("Location: index.php?step=".$_SESSION['previousStep']);
    }
    require_once('controller/error_controller.php');

//VIEW
    require_once('view/principal_view.php');
