<?php

/**
 * Class Test
 * This is a first step for a Model class for the plugin.
 */
namespace Mini\Model;

//use Mini\Core\Model;
use Mini\Libs\EWSExample;
//use Mini\Libs\Toto;

class Test //extends Model
{
    /**
     * Copied from Songs to serve as template for a db request
     * Maybe start with a request of existing users ?
     */
    public function get_data_from_mc()
    {
        $sql = "SELECT id, artist, track, link FROM song";
        $query = $this->db->prepare( $sql );
        $query->execute();

        // fetchAll() is the PDO method that gets all result rows, here in object-style because we defined this in
        // core/controller.php! If you prefer to get an associative array as the result, then do
        // $query->fetchAll(PDO::FETCH_ASSOC); or change core/controller.php's PDO options to
        // $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ...
        return $query->fetchAll();
    }

    public $mails = "that";
    public function get_mails_from_ews()
    {
        $ex = new EWSExample();
        $ex->init_client();
        $ex->init_request();
        $result = $ex->do_mess_find();
        return $result;
    }

    public function get_attachments_from_ews( $message_id )
    {
        $ex = new EWSExample();
        $ex->init_client();
        $ex->init_get_attach_request( $message_id );
        $result = $ex->do_get_attachments();
        return $result;
    }

}
