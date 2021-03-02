<?php

/**
 * Class EWSController
 * Handles routes related to Exchange web services instance.
 * GET : /ews/get_attachments?message_id="message_ews_id"
 */

namespace Mini\Controller;

use Mini\Model\EWS;

class EwsController
{
    /**
     * Handles get_attach
     */
    public function get_attachments()
    {
        if ( isset( $_GET["message_id"] ) ) {
            $ews = new EWS();
            $result = $ews->get_attachments( $_GET["message_id"] );
            $res = json_encode( $result );
            echo $res;
        }
    }

}
