<?php

/**
 * Class Courrier
 * Handles get and post requests to maarch courrier.
 * 
 */
namespace Mini\Model;

//use Mini\Core\Service;

class Courrier //extends Model
{
    /**
     * Gets the list of doctypes from maarch courrier.
     */
    public function get_doctypes()
    {
        $url = COURRIER_URL . "/rest/doctypes/types";
        $headers = array( 'Authorization: Basic YmJsaWVyOm1hYXJjaA==' );

        $ch = curl_init( );
        curl_setopt( $ch,   CURLOPT_URL,            $url                );
        curl_setopt( $ch,   CURLOPT_HTTPHEADER,     $headers            );
        curl_setopt( $ch,   CURLOPT_RETURNTRANSFER, true                );
        $result = curl_exec( $ch );
        curl_close( $ch );

        return $result;
    }

    public function get_contacts()
    {
        $url = COURRIER_URL . "/rest/contacts";
        $headers = array( 'Authorization: Basic YmJsaWVyOm1hYXJjaA==' );

        $ch = curl_init( );
        curl_setopt( $ch,   CURLOPT_URL,            $url );
        curl_setopt( $ch,   CURLOPT_HTTPHEADER,     $headers );
        curl_setopt( $ch,   CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        curl_close( $ch );

        return $result;
    }

    public function create_contact( $firstname, $lastname )
    {
        $url = COURRIER_URL . "/rest/contacts";
        $headers = array( 'Authorization: Basic YmJsaWVyOm1hYXJjaA==',
                          'Content-Type:application/json' );

        $data = json_encode( array( "firstname"=>$firstname, "lastname"=>$lastname ) );

        $ch = curl_init( );
        curl_setopt( $ch,   CURLOPT_URL,            $url );
        curl_setopt( $ch,   CURLOPT_HTTPHEADER,     $headers );
        curl_setopt( $ch,   CURLOPT_POSTFIELDS,     $data );
        curl_setopt( $ch,   CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        curl_close( $ch );

        return $result;
    }

    public function register_courrier( $data )
    {
        $url = COURRIER_URL . "/rest/resources";
        $headers = array( 'Authorization: Basic YmJsaWVyOm1hYXJjaA==',
                          'Content-Type:application/json' );

        $ch = curl_init( );
        curl_setopt( $ch,   CURLOPT_URL,            $url );
        curl_setopt( $ch,   CURLOPT_HTTPHEADER,     $headers );
        curl_setopt( $ch,   CURLOPT_POSTFIELDS,     $data );
        curl_setopt( $ch,   CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        curl_close( $ch );

        echo $result;
    }

}
