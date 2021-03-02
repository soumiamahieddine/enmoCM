<?php

namespace Mini\Libs;

use \jamesiarmes\PhpEws\Client;
use \jamesiarmes\PhpEws\Request\FindItemType;
use \jamesiarmes\PhpEws\Request\GetAttachmentType;
use \jamesiarmes\PhpEws\Request\GetItemType;

use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfRequestAttachmentIdsType;

use \jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use \jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use \jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use \jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;

use \jamesiarmes\PhpEws\Type\AndType;
use \jamesiarmes\PhpEws\Type\ConstantValueType;
use \jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use \jamesiarmes\PhpEws\Type\FieldURIOrConstantType;
use \jamesiarmes\PhpEws\Type\IsGreaterThanOrEqualToType;
use \jamesiarmes\PhpEws\Type\IsLessThanOrEqualToType;
use \jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use \jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use \jamesiarmes\PhpEws\Type\RestrictionType;
use \jamesiarmes\PhpEws\Type\ItemIdType;
use \jamesiarmes\PhpEws\Type\RequestAttachmentIdType;

class EWSExample {

    public $client = null;
    public $request = null;
    public $ret = null;
    public function init_client() {
        $this->ret = array();
        $timezone = 'Eastern Standard Time';
        
        // Set connection information.
        $host = 'mail.hostedexchange2010.fr/EWS/Exchange.asmx';
        $username = 'hamza.hramchi@xelians.fr';
        $password = 'passwordHere';
        $version = Client::VERSION_2010;
        
        $this->client = new Client( $host, $username, $password, $version );
        return $this->client;
    }
        
    public function init_request() {
        // Replace with the date range you want to search in. As is, this will find all
        // messages within the current calendar year.
        $start_date = new \DateTime('November 10 00:00:00');
        $end_date = new \DateTime('November 20 23:59:59');

        $this->request = new FindItemType();
        $this->request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();
        
        // Build the start date restriction.
        $greater_than = new IsGreaterThanOrEqualToType();
        $greater_than->FieldURI = new PathToUnindexedFieldType();
        $greater_than->FieldURI->FieldURI = UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
        $greater_than->FieldURIOrConstant = new FieldURIOrConstantType();
        $greater_than->FieldURIOrConstant->Constant = new ConstantValueType();
        $greater_than->FieldURIOrConstant->Constant->Value = $start_date->format('c');
        
        // Build the end date restriction;
        $less_than = new IsLessThanOrEqualToType();
        $less_than->FieldURI = new PathToUnindexedFieldType();
        $less_than->FieldURI->FieldURI = UnindexedFieldURIType::ITEM_DATE_TIME_RECEIVED;
        $less_than->FieldURIOrConstant = new FieldURIOrConstantType();
        $less_than->FieldURIOrConstant->Constant = new ConstantValueType();
        $less_than->FieldURIOrConstant->Constant->Value = $end_date->format('c');
        
        // Build the restriction.
        $this->request->Restriction = new RestrictionType();
        $this->request->Restriction->And = new AndType();
        $this->request->Restriction->And->IsGreaterThanOrEqualTo = $greater_than;
        $this->request->Restriction->And->IsLessThanOrEqualTo = $less_than;
        
        // Return all message properties.
        $this->request->ItemShape = new ItemResponseShapeType();
        $this->request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
        
        // Search in the user's inbox.
        $folder_id = new DistinguishedFolderIdType();
        $folder_id->Id = DistinguishedFolderIdNameType::INBOX;
        $this->request->ParentFolderIds->DistinguishedFolderId[] = $folder_id;
        
        return $this->request;
    }

    public function do_mess_find() {
        $response = $this->client->FindItem( $this->request );

        $response_messages = $response->ResponseMessages->FindItemResponseMessage;
        foreach ($response_messages as $response_message) {
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $code = $response_message->ResponseCode;
                $message = $response_message->MessageText;
                array_push( $this->ret, $code );
                array_push( $this->ret, $message );
                continue;
            }
            // Iterate over the messages that were found, printing the subject for each.
            $items = $response_message->RootFolder->Items->Message;
            foreach ($items as $item) {
                $subject = $item->Subject;
                $id = $item->ItemId->Id;
                array_push( $this->ret, $subject );
                array_push( $this->ret, $id );
            }
            //array_push( $this->ret, $items );
        }
        return $this->ret;
    }

    public function init_get_attach_request( $message_id )
    {
        array_push( $this->ret, $message_id );
        
        // Some fixes on the message id from outlook js API, seen at :
        // https://blog.mastykarz.nl/office-365-unified-api-mail/
        $message_id = str_replace( '-', '/', $message_id );
        $message_id = str_replace( '_', '+', $message_id );
        
        // Make sure the destination directory exists and is writeable.
        if ( !file_exists( $this->file_destination ) ) {
            mkdir( $this->file_destination, 0777, true );
        }
        
        if ( !is_writable( $this->file_destination ) ) {
            throw new Exception( "Destination $this->file_destination is not writable." );
        }
        
        // Build the get item request.
        $this->request = new GetItemType();
        $this->request->ItemShape = new ItemResponseShapeType();
        $this->request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
        $this->request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
        
        // Add the message id to the request.
        $item = new ItemIdType();
        $item->Id = $message_id;
        $this->request->ItemIds->ItemId[] = $item;
    }

    public function do_get_attachments()
    {
        $response = $this->client->GetItem( $this->request );
        // Iterate over the results, printing any error messages or receiving
        // attachments.
        $response_messages = $response->ResponseMessages->GetItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $code = $response_message->ResponseCode;
                $message = $response_message->MessageText;
                //fwrite(STDERR, "Failed to get message with \"$code: $message\"\n");
                array_push( $this->ret, $code );
                array_push( $this->ret, $message );
                continue;
            }
        
            // Iterate over the messages, getting the attachments for each.
            $attachments = array();
            foreach ($response_message->Items->Message as $item) {
                // If there are no attachments for the item, move on to the next
                // message.
                if (empty($item->Attachments)) {
                    continue;
                }
        
                // Iterate over the attachments for the message.
                foreach ($item->Attachments->FileAttachment as $attachment) {
                    $attachments[] = $attachment->AttachmentId->Id;
                }
            }
        
            // Build the request to get the attachments.
            $request = new GetAttachmentType();
            $request->AttachmentIds = new NonEmptyArrayOfRequestAttachmentIdsType();
        
            // Iterate over the attachments for the message.
            foreach ( $attachments as $attachment_id ) {
                $id = new RequestAttachmentIdType();
                $id->Id = $attachment_id;
                $request->AttachmentIds->AttachmentId[] = $id;
            }
        
            $response = $this->client->GetAttachment( $request );
    
            // Iterate over the response messages, printing any error messages or
            // saving the attachments.
            $attachment_response_messages = $response->ResponseMessages
                ->GetAttachmentResponseMessage;
            foreach ($attachment_response_messages as $attachment_response_message) {
                // Make sure the request succeeded.
                if ($attachment_response_message->ResponseClass
                    != ResponseClassType::SUCCESS) {
                    $code = $response_message->ResponseCode;
                    $message = $response_message->MessageText;
                    array_push( $this->ret, $code );
                    array_push( $this->ret, $message );
                    continue;
                }
    
                // Iterate over the file attachments, saving each one.
                $attachments = $attachment_response_message->Attachments
                    ->FileAttachment;
                foreach ($attachments as $attachment) {
                    $path = $this->file_destination . '/' . $attachment->Name;
                    $write_ret = file_put_contents( $path, $attachment->Content );
                    $path_message = "\nCreated attachment $path, wrote $write_ret bytes.";
                    array_push( $this->ret, $path_message );
                }
            }
        }
        return $this->ret;
    }

}
