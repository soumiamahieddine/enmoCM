<?php

namespace Mini\Controller;

class TaskpaneController
{
    public function index()
    {
        //$test = new Test();
        // get and print mails list
        //$val = $test->get_mails_from_ews();
        //$val = json_encode( $val );
echo "in taskpane controller, redirecting to home.";
        header('location: ' . URL . 'home/index');
       // load views. within the views we can echo out vars declared here
/*        require APP . 'view/_templates/header.php';
        require APP . 'view/home/index.php';
        require APP . 'view/_templates/footer.php';
*/    }

}
