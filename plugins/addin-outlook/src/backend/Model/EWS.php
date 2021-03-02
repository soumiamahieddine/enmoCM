<?php

/**
 * Class EWS
 * This is a first step for a Model class for the plugin.
 */
namespace Mini\Model;

use Mini\Libs\EWSExample;

class EWS //extends Model
{
    /**
     * Use 
     */
    public function get_attachments( $message_id )
    {
        $ex = new EWSExample();
        $ex->init_client();
        $ex->init_get_attach_request( $message_id );
        $result = $ex->do_get_attachments();
        return $result;
    }

}
