<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Export seda transfer
* @author dev@maarch.org
* @ingroup export_seda
*/

$status = 0;
$error = $content = '';
if ($_REQUEST['reference']) {
    $transferToSAE = new TransferToSAE();
    $res = $transferToSAE->send($_REQUEST['reference']);
    $status = $res['status'];
    if ($status != 0) {
        $error = $res['error'];
    } else {
        $content = $res['content'];
    }
} else {
    $status = 1;
}

    
    echo "{status : " . $status . ", content : '" . addslashes($content) . "', error : '" . addslashes($error) . "'}";
    exit ();

class TransferToSAE
{
    protected $token;
    protected $SAE;

    public function __construct()
    {
        $config = parse_ini_file(__DIR__.'/config.ini');
        $this->token = $config['token'];
        $this->SAE = $config['urlSAE'];
    }

    public function send($reference)
    {
        $res = [];
        $res['status'] = 0;
        $res['content'] = _RECEIVED_MESSAGE;

        $data = new stdClass();
        $messageDirectory = __DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$reference;
        $messageFile = $reference.".xml";

        $files = scandir($messageDirectory);
        $attachments = [];
        foreach ($files as $file) {
            if ($file != $messageFile && $file != ".." && $file != ".") {
                $attachment = new stdClass;
                $attachment->data = base64_encode(
                    file_get_contents($messageDirectory . DIRECTORY_SEPARATOR . $file)
                );
                $attachment->filename = $file;

                $data->attachments[] = $attachment;
            }
        }

        $data->messageFile = base64_encode(
            file_get_contents($messageDirectory . DIRECTORY_SEPARATOR . $reference.".xml")
        );

        $header = [
            'accept:application/json',
            'content-type:application/json'
        ];

        $token = explode("LAABS-AUTH=", $this->token);
        if (count($token) != 0) {
            $urlencode = urlencode($token[1]);
            $this->token = "LAABS-AUTH=". $urlencode;
        }

        try {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $this->SAE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_COOKIE, $this->token);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
            
            $return = json_decode(curl_exec($curl));
            
            
            if (!$return) {
                $res['status'] = 1;
                $res['error'] = _ERROR_MESSAGE;
            } else {
                $res['content'] .= $return->reference;
            }

            curl_close($curl);
        } catch (Exception $e) {
            var_dump($e);
        }
        
        return $res;
    }
}
