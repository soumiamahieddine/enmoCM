<?php

/**
 * Class CourrierController
 * Handles routes related to maarch courrier instance.
 * GET : /courrier/doctypes
 * GET : /courrier/contact?mail="contact_mail"
 * POST : /courrier/register
 */

namespace Mini\Controller;

use Mini\Model\Courrier;

class CourrierController
{
    /**
     * Gets the list of doctypes from maarch courrier to the front.
     */
    public function doctypes()
    {
        $courrier = new Courrier();
        $result = $courrier->get_doctypes();
        echo $result;
    }

    /**
     * Finds the contact in maarch courrier
     * Todo : Creates a new contact if it doesn't exist.
     */
    public function contacts() {
        if ( isset( $_GET['firstname'] ) && isset( $_GET['lastname'] ) ) {
            $firstname = $_GET['firstname'];
            $lastname = $_GET['lastname'];

            $courrier = new Courrier();
            $result = $courrier->get_contacts();

            $ret = new \stdClass();
            if ( empty( $result ) ) {
                $ret->Error = "Coudn't get contact list from MaarchCourrier.";
                echo json_encode( $ret );
                return ;
            }

            $contacts_obj = json_decode( $result );

            $found = false;
            foreach ( $contacts_obj->contacts as $contact ) {
                if ( isset( $contact->firstname ) && isset( $contact->lastname ) ) {
                    if ( $contact->firstname === $firstname  && $contact->lastname === $lastname ) {
                        $ret->contact_id = $contact->id;
                        echo json_encode( $ret );
                        $found = true;
                    }
                }
            }
            if ( $found == false ) {
                $create_result = $courrier->create_contact( $firstname, $lastname );
                $create_obj = json_decode( $create_result );
                $ret = new \stdClass();
                $ret->contact_id = $create_obj->id;
                echo json_encode( $ret );
            }
        }
        else
            echo json_encode( "Error : $_GET firstname and / or lastname undefined.\n" );
    }

    /**
     * Handles register_courrier
     */
    public function register_courrier()
    {
        $json = file_get_contents('php://input');
        $courrier = new Courrier();
        $result = $courrier->register_courrier( $json );
        echo $result;
    }


}
