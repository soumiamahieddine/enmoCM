<?php

/**
 * Class HomeController
 */

namespace Mini\Controller;

use Mini\Model\Test;

class HomeController
{
    /**
     * This method handles what happens when you move to http://yourproject/test/index
     */
    public function index()
    {

       // load views. within the views we can echo out vars declared here
        require APP . 'view/_templates/header.php';
        require APP . 'view/home/index.php';
        require APP . 'view/_templates/footer.php';
    }

}
