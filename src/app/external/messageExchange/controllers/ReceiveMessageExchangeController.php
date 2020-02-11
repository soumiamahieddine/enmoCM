<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Receive Message Exchange Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace MessageExchange\Controllers;

use Basket\models\BasketModel;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Note\models\NoteModel;
use Resource\controllers\StoreController;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use ExportSeda\controllers\SendMessageController;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use User\models\UserModel;

require_once 'modules/export_seda/Controllers/ReceiveMessage.php';

class ReceiveMessageExchangeController
{
    private static $aComments = [];

    public function saveMessageExchange(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'save_numeric_package', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (empty($GLOBALS['userId'])) {
            return $response->withStatus(401)->withJson(['errors' => 'User Not Connected']);
        }

        $data = $request->getParams();

        $this->addComment('['.date("d/m/Y H:i:s") . '] Réception du pli numérique');
        $tmpName = self::createFile(['base64' => $data['base64'], 'extension' => $data['extension'], 'size' => $data['size']]);
        if (!empty($tmpName['errors'])) {
            return $response->withStatus(400)->withJson($tmpName);
        }
        $this->addComment('['.date("d/m/Y H:i:s") . '] Pli numérique déposé sur le serveur');
        $this->addComment('['.date("d/m/Y H:i:s") . '] Validation du pli numérique');
        /********** EXTRACTION DU ZIP ET CONTROLE *******/
        $receiveMessage = new \ReceiveMessage();
        $tmpPath = CoreConfigModel::getTmpPath();
        $res = $receiveMessage->receive($tmpPath, $tmpName, 'ArchiveTransfer');

        if ($res['status'] == 1) {
            return $response->withStatus(400)->withJson(["errors" => _ERROR_RECEIVE_FAIL. ' ' . $res['content']]);
        }
        self::$aComments[] = '['.date("d/m/Y H:i:s") . '] Pli numérique validé';

        $sDataObject = $res['content'];
        $sDataObject = json_decode($sDataObject);
        
        $acknowledgementReturn = self::sendAcknowledgement(["dataObject" => $sDataObject]);
        if (!empty($acknowledgementReturn['error'])) {
            return $response->withStatus(400)->withJson(["errors" => $acknowledgementReturn['error']]);
        }

        $aDefaultConfig = self::readXmlConfig();

        /*************** CONTACT **************/
        $this->addComment('['.date("d/m/Y H:i:s") . '] Selection ou création du contact');
        $contactReturn = self::saveContact(["dataObject" => $sDataObject, "defaultConfig" => $aDefaultConfig]);

        if ($contactReturn['returnCode'] <> 0) {
            return $response->withStatus(400)->withJson(["errors" => $contactReturn['errors']]);
        }
        self::$aComments[] = '['.date("d/m/Y H:i:s") . '] Contact sélectionné ou créé';

        /*************** RES LETTERBOX **************/
        $this->addComment('['.date("d/m/Y H:i:s") . '] Enregistrement du message');
        $resLetterboxReturn = self::saveResLetterbox(["dataObject" => $sDataObject, "defaultConfig" => $aDefaultConfig, "contact" => $contactReturn]);

        if (!empty($resLetterboxReturn['errors'])) {
            return $response->withStatus(400)->withJson(["errors" => $resLetterboxReturn['errors']]);
        }

        self::$aComments[] = '['.date("d/m/Y H:i:s") . '] Message enregistré';
        /************** NOTES *****************/
        $notesReturn = self::saveNotes(["dataObject" => $sDataObject, "resId" => $resLetterboxReturn, "userId" => $GLOBALS['id']]);
        if (!empty($notesReturn['errors'])) {
            return $response->withStatus(400)->withJson(["errors" => $notesReturn['errors']]);
        }
        /************** RES ATTACHMENT *****************/
        $resAttachmentReturn = self::saveResAttachment(["dataObject" => $sDataObject, "resId" => $resLetterboxReturn, "defaultConfig" => $aDefaultConfig]);

        if (!empty($resAttachmentReturn['errors'])) {
            return $response->withStatus(400)->withJson(["errors" => $resAttachmentReturn['errors']]);
        }

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $resLetterboxReturn,
            'eventType' => 'ADD',
            'eventId'   => 'resadd',
            'info'      => _NUMERIC_PACKAGE_IMPORTED
        ]);

        $basketRedirection = null;
        $userBaskets = BasketModel::getBasketsByLogin(['login' => $GLOBALS['userId']]);
        if (!empty($userBaskets)) {
            foreach ($userBaskets as $value) {
                if ($value['basket_id'] == $aDefaultConfig['basketRedirection_afterUpload'][0]) {
                    $userGroups = UserModel::getGroupsByLogin(['login' => $GLOBALS['userId']]);
                    $basketRedirection = 'index.php#/basketList/users/'.$GLOBALS['id'].'/groups/'.$userGroups[0]['id'].'/baskets/'.$value['id'];
                    $resource = ResModel::getById(['id' => $resLetterboxReturn]);
                    if (!empty($resource['alt_identifier'])) {
                        $basketRedirection .= '?chrono='.$resource['alt_identifier'];
                    }
                    break;
                }
            }
        }

        if (empty($basketRedirection)) {
            $basketRedirection = 'index.php';
        }

        self::sendReply(['dataObject' => $sDataObject, 'Comment' => self::$aComments, 'replyCode' => '000 : OK', 'res_id_master' => $resLetterboxReturn]);

        return $response->withJson([
            "resId"             => $resLetterboxReturn,
            'basketRedirection' => $basketRedirection
        ]);
    }

    public static function checkNeededParameters($aArgs = [])
    {
        foreach ($aArgs['needed'] as $value) {
            if (empty($aArgs['data'][$value])) {
                return false;
            }
        }

        return true;
    }

    public function createFile($aArgs = [])
    {
        if (!self::checkNeededParameters(['data' => $aArgs, 'needed' => ['base64', 'extension', 'size']])) {
            return ['errors' => 'Bad Request'];
        }

        $file     = base64_decode($aArgs['base64']);

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($file);
        $ext      = $aArgs['extension'];
        $tmpName  = 'tmp_file_' .$GLOBALS['userId']. '_ArchiveTransfer_' .rand(). '.' . $ext;

        if (!in_array(strtolower($ext), ['zip', 'tar'])) {
            return ["errors" => _WRONG_FILE_TYPE_M2M];
        }

        if ($mimeType != "application/x-tar" && $mimeType != "application/zip" && $mimeType != "application/tar" && $mimeType != "application/x-gzip") {
            return ['errors' => _WRONG_FILE_TYPE];
        }

        $tmpPath = CoreConfigModel::getTmpPath();
        file_put_contents($tmpPath . $tmpName, $file);

        return $tmpName;
    }

    public static function readXmlConfig()
    {
        $customId = CoreConfigModel::getCustomId();

        if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/m2m_config.xml")) {
            $path = "custom/{$customId}/apps/maarch_entreprise/xml/m2m_config.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/m2m_config.xml';
        }
        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/m2m_config.xml']);

        $aDefaultConfig = [];
        if (!empty($loadedXml)) {
            foreach ($loadedXml as $key => $value) {
                $aDefaultConfig[$key] = (array)$value;
            }
        }

        $aDefaultConfig['m2m_communication'] = explode(",", $aDefaultConfig['m2m_communication'][0]);
        foreach ($aDefaultConfig['m2m_communication'] as $value) {
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $aDefaultConfig['m2m_communication_type']['email'] = $value;
            } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                $aDefaultConfig['m2m_communication_type']['url'] = $value;
            }
        }

        return $aDefaultConfig;
    }

    protected static function saveResLetterbox($aArgs = [])
    {
        $dataObject    = $aArgs['dataObject'];
        $defaultConfig = $aArgs['defaultConfig']['res_letterbox'];

        $DescriptiveMetadata = $dataObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0];

        $mainDocumentMetaData  = $DescriptiveMetadata->Content;
        $DataObjectReferenceId = $DescriptiveMetadata->ArchiveUnit[0]->DataObjectReference[0]->DataObjectReferenceId;

        $documentMetaData = self::getBinaryDataObjectInfo(['binaryDataObject' => $dataObject->DataObjectPackage->BinaryDataObject, 'binaryDataObjectId' => $DataObjectReferenceId]);

        $filename         = $documentMetaData->Attachment->filename;
        $fileFormat       = substr($filename, strrpos($filename, '.') + 1);

        $archivalAgency = $dataObject->ArchivalAgency;
        $destination    = EntityModel::getByBusinessId(['businessId' => $archivalAgency->Identifier->value]);
        $Communication  = $archivalAgency->OrganizationDescriptiveMetadata->Contact[0]->Communication;

        foreach ($Communication as $value) {
            if ($value->Channel == 'email') {
                $email = $value->value;
                break;
            }
        }

        if (!empty($email)) {
            $destUser = UserModel::getByEmail(['mail' => $email]);
        }

        $dataValue = [];
        $user      = UserModel::getByLogin(['login' => 'superadmin', 'select' => ['id']]);
        $entityId  = EntityModel::getByEntityId(['entityId' => $destination[0]['entity_id'], 'select' => ['id']]);
        array_push($dataValue, ['typist'           => $user['id']]);
        array_push($dataValue, ['doctype'          => $defaultConfig['type_id']]);
        array_push($dataValue, ['subject'          => str_replace("[CAPTUREM2M]", "", $mainDocumentMetaData->Title[0])]);
        array_push($dataValue, ['documentDate'     => $mainDocumentMetaData->CreatedDate]);
        array_push($dataValue, ['destination'      => $entityId['id']]);
        array_push($dataValue, ['initiator'        => $entityId['id']]);
        array_push($dataValue, ['diffusionList'    => ['id' => $destUser[0]['user_id'], 'type' => 'user', 'mode' => 'dest']]);
        array_push($dataValue, ['externalId'       => ['m2m' => $dataObject->MessageIdentifier->value]]);
        array_push($dataValue, ['priority'         => $defaultConfig['priority']]);
        array_push($dataValue, ['confidentiality'  => false]);
        array_push($dataValue, ['chrono'           => true]);
        $date = new \DateTime();
        array_push($dataValue, ['arrivalDate'  => $date->format('d-m-Y H:i')]);
        array_push($dataValue, ['encodedFile'  => $documentMetaData->Attachment->value]);
        array_push($dataValue, ['format'       => $fileFormat]);
        array_push($dataValue, ['status'       => $defaultConfig['status']]);
        array_push($dataValue, ['modelId'      => $defaultConfig['indexingModelId']]);

        $storeResource = StoreController::storeResource($dataValue);
        if (!empty($storeResource['errors'])) {
            ResourceContactModel::create(['res_id' => $storeResource, 'item_id' => $aArgs['contact']['id'], 'type' => 'contact', 'mode' => 'sender']);
        }

        return $storeResource;
    }

    protected static function saveContact($aArgs = [])
    {
        $dataObject                 = $aArgs['dataObject'];
        $transferringAgency         = $dataObject->TransferringAgency;
        $transferringAgencyMetadata = $transferringAgency->OrganizationDescriptiveMetadata;

        if (strrpos($transferringAgencyMetadata->Communication[0]->value, "/rest/") !== false) {
            $contactCommunicationValue = substr($transferringAgencyMetadata->Communication[0]->value, 0, strrpos($transferringAgencyMetadata->Communication[0]->value, "/rest/")+1);
        } else {
            $contactCommunicationValue = $transferringAgencyMetadata->Communication[0]->value;
        }
        
        if (filter_var($contactCommunicationValue, FILTER_VALIDATE_EMAIL)) {
            $aCommunicationMeans['email'] = $contactCommunicationValue;
            $whereAlreadyExist = "communication_means->>'email' = ?";
        } elseif (filter_var($contactCommunicationValue, FILTER_VALIDATE_URL)) {
            $aCommunicationMeans['url'] = $contactCommunicationValue;
            $whereAlreadyExist = "communication_means->>'url' = ?";
        }
        $dataAlreadyExist = $contactCommunicationValue;

        $contactAlreadyCreated = ContactModel::get([
            'select'    => ['id', 'communication_means'],
            'where'     => ["external_id->>'m2m' = ?", $whereAlreadyExist],
            'data'      => [$transferringAgency->Identifier->value, $dataAlreadyExist],
            'limit'     => 1
        ]);

        if (!empty($contactAlreadyCreated[0]['id'])) {
            $contact = [
                'id'         => $contactAlreadyCreated[0]['id'],
                'returnCode' => (int) 0
            ];
        } else {
            $aDataContact = [
                'company'             => $transferringAgencyMetadata->LegalClassification,
                'external_id'         => json_encode(['m2m' => $transferringAgency->Identifier->value]),
                'department'          => $transferringAgencyMetadata->Name,
                'communication_means' => json_encode($aCommunicationMeans)
            ];

            $contactId = ContactModel::create(['data' => $aDataContact]);
            if (empty($contactId)) {
                $contact = [
                    'returnCode'  => (int) -1,
                    'error'       => 'Contact creation error',
                ];
            } else {
                $contact = [
                    'id'         => $contactId,
                    'returnCode' => (int) 0
                ];
            }
        }

        return $contact;
    }

    protected static function saveNotes($aArgs = [])
    {
        $countNote = 0;
        foreach ($aArgs['dataObject']->Comment as $value) {
            NoteModel::create([
                "resId" => $aArgs['resId'],
                "user_id"    => $aArgs['userId'],
                "note_text"  => $value->value
            ]);

            HistoryController::add([
                'tableName' => 'notes',
                'recordId'  => $aArgs['resId'],
                'eventType' => 'ADD',
                'eventId'   => 'noteadd',
                'info'       => _NOTES_ADDED
            ]);

            $countNote++;
        }
        self::$aComments[] = '['.date("d/m/Y H:i:s") . '] '.$countNote . ' note(s) enregistrée(s)';
        return true;
    }

    protected static function saveResAttachment($aArgs = [])
    {
        $dataObject        = $aArgs['dataObject'];
        $resIdMaster       = $aArgs['resId'];
        $defaultConfig     = $aArgs['defaultConfig']['res_attachments'];
        $dataObjectPackage = $dataObject->DataObjectPackage;

        $attachments = $dataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->ArchiveUnit;

        // First one is the main document. Already added
        unset($attachments[0]);
        $countAttachment = 0;
        if (!empty($attachments)) {
            foreach ($attachments as $value) {
                $attachmentContent      = $value->Content;
                $attachmentDataObjectId = $value->DataObjectReference[0]->DataObjectReferenceId;

                $BinaryDataObjectInfo = self::getBinaryDataObjectInfo(["binaryDataObject" => $dataObjectPackage->BinaryDataObject, "binaryDataObjectId" => $attachmentDataObjectId]);
                $filename             = $BinaryDataObjectInfo->Attachment->filename;
                $fileFormat           = substr($filename, strrpos($filename, '.') + 1);

                $dataValue = [];
                array_push($dataValue, ['column' => 'typist',          'value' => 'superadmin',                      'type' => 'string']);
                array_push($dataValue, ['column' => 'type_id',         'value' => '0',                               'type' => 'integer']);
                array_push($dataValue, ['column' => 'res_id_master',   'value' => $resIdMaster,                      'type' => 'integer']);
                array_push($dataValue, ['column' => 'attachment_type', 'value' => $defaultConfig['attachment_type'], 'type' => 'string']);
                array_push($dataValue, ['column' => 'relation',        'value' => '1',                               'type' => 'integer']);
                array_push($dataValue, ['column' => 'coll_id',         'value' => 'letterbox_coll',                  'type' => 'string']);
                array_push($dataValue, ['column' => 'doc_date',        'value' => $attachmentContent->CreatedDate,   'type' => 'date']);
                array_push($dataValue, ['column' => 'title',           'value' => $attachmentContent->Title[0],      'type' => 'string']);

                $allDatas = [
                    "encodedFile" => $BinaryDataObjectInfo->Attachment->value,
                    "data"        => $dataValue,
                    "version"       => false,
                    "fileFormat"  => $fileFormat,
                    "status"      => 'TRA'
                ];
                
                $resId = StoreController::storeAttachment($allDatas);
                $countAttachment++;
            }
        }
        self::$aComments[] = '['.date("d/m/Y H:i:s") . '] '.$countAttachment . ' attachement(s) enregistré(s)';
        return $resId;
    }

    protected function getBinaryDataObjectInfo($aArgs = [])
    {
        $dataObject   = $aArgs['binaryDataObject'];
        $dataObjectId = $aArgs['binaryDataObjectId'];

        foreach ($dataObject as $value) {
            if ($value->id == $dataObjectId) {
                return $value;
            }
        }
        return null;
    }

    protected function sendAcknowledgement($aArgs = [])
    {
        $dataObject = $aArgs['dataObject'];
        $date       = new \DateTime;

        $acknowledgementObject                                   = new \stdClass();
        $acknowledgementObject->Date                             = $date->format(\DateTime::ATOM);

        $acknowledgementObject->MessageIdentifier                = new \stdClass();
        $acknowledgementObject->MessageIdentifier->value         = $dataObject->MessageIdentifier->value . '_AckSent';

        $acknowledgementObject->MessageReceivedIdentifier        = new \stdClass();
        $acknowledgementObject->MessageReceivedIdentifier->value = $dataObject->MessageIdentifier->value;

        $acknowledgementObject->Sender                           = $dataObject->ArchivalAgency;
        $acknowledgementObject->Receiver                         = $dataObject->TransferringAgency;

        if ($acknowledgementObject->Receiver->OrganizationDescriptiveMetadata->Communication[0]->Channel == 'url') {
            $acknowledgementObject->Receiver->OrganizationDescriptiveMetadata->Communication[0]->value .= '/rest/saveMessageExchangeReturn';
        }

        $acknowledgementObject->MessageIdentifier->value          = $dataObject->MessageIdentifier->value . '_Ack';
        $tmpPath = CoreConfigModel::getTmpPath();
        $filePath = SendMessageController::generateMessageFile($acknowledgementObject, 'Acknowledgement', $tmpPath);

        $acknowledgementObject->ArchivalAgency = $acknowledgementObject->Receiver;
        $acknowledgementObject->TransferringAgency = $acknowledgementObject->Sender;

        $acknowledgementObject->TransferringAgency->OrganizationDescriptiveMetadata->UserIdentifier = $GLOBALS['userId'];

        $acknowledgementObject->MessageIdentifier->value          = $dataObject->MessageIdentifier->value . '_AckSent';
        $messageExchangeSaved = SendMessageExchangeController::saveMessageExchange(['dataObject' => $acknowledgementObject, 'res_id_master' => 0, 'type' => 'Acknowledgement', 'file_path' => $filePath]);

        $acknowledgementObject->DataObjectPackage = new \stdClass();
        $acknowledgementObject->DataObjectPackage->DescriptiveMetadata = new \stdClass();
        $acknowledgementObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit = array();
        $acknowledgementObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0] = new \stdClass();
        $acknowledgementObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content = new \stdClass();
        $acknowledgementObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->Title[0] = '[CAPTUREM2M_ACK]'.date("Ymd_his");

        SendMessageController::send($acknowledgementObject, $messageExchangeSaved['messageId'], 'Acknowledgement');

        return $messageExchangeSaved;
    }

    protected function sendReply($aArgs = [])
    {
        $dataObject = $aArgs['dataObject'];
        $date       = new \DateTime;

        $replyObject                                    = new \stdClass();
        $replyObject->Comment                           = $aArgs['Comment'];
        $replyObject->Date                              = $date->format(\DateTime::ATOM);

        $replyObject->MessageIdentifier                 = new \stdClass();
        $replyObject->MessageIdentifier->value          = $dataObject->MessageIdentifier->value . '_ReplySent';

        $replyObject->ReplyCode                         = $aArgs['replyCode'];

        $replyObject->MessageRequestIdentifier        = new \stdClass();
        $replyObject->MessageRequestIdentifier->value = $dataObject->MessageIdentifier->value;

        $replyObject->TransferringAgency                = $dataObject->ArchivalAgency;
        $replyObject->TransferringAgency->OrganizationDescriptiveMetadata->UserIdentifier = $GLOBALS['userId'];
        $replyObject->ArchivalAgency                    = $dataObject->TransferringAgency;

        $replyObject->MessageIdentifier->value          = $dataObject->MessageIdentifier->value . '_Reply';
        $tmpPath = CoreConfigModel::getTmpPath();
        $filePath = SendMessageController::generateMessageFile($replyObject, "ArchiveTransferReply", $tmpPath);

        $replyObject->MessageIdentifier->value          = $dataObject->MessageIdentifier->value . '_ReplySent';
        $messageExchangeSaved = SendMessageExchangeController::saveMessageExchange(['dataObject' => $replyObject, 'res_id_master' => $aArgs['res_id_master'], 'type' => 'ArchiveTransferReply', 'file_path' => $filePath]);

        $replyObject->MessageIdentifier->value          = $dataObject->MessageIdentifier->value . '_Reply';

        $replyObject->DataObjectPackage = new \stdClass();
        $replyObject->DataObjectPackage->DescriptiveMetadata = new \stdClass();
        $replyObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit = array();
        $replyObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0] = new \stdClass();
        $replyObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content = new \stdClass();
        $replyObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->OriginatingSystemId = $aArgs['res_id_master'];

        $replyObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->Title[0] = '[CAPTUREM2M_REPLY]'.date("Ymd_his");

        SendMessageController::send($replyObject, $messageExchangeSaved['messageId'], 'ArchiveTransferReply');
    }

    public function saveMessageExchangeReturn(Request $request, Response $response)
    {
        if (empty($GLOBALS['userId'])) {
            return $response->withStatus(401)->withJson(['errors' => 'User Not Connected']);
        }

        $data = $request->getParams();

        if (!self::checkNeededParameters(['data' => $data, 'needed' => ['type']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $tmpName = self::createFile(['base64' => $data['base64'], 'extension' => $data['extension'], 'size' => $data['size']]);

        $receiveMessage = new \ReceiveMessage();
        $tmpPath = CoreConfigModel::getTmpPath();
        $res = $receiveMessage->receive($tmpPath, $tmpName, $data['type']);

        $sDataObject = $res['content'];
        $dataObject = json_decode($sDataObject);

        if ($dataObject->type == 'Acknowledgement') {
            $messageExchange = MessageExchangeModel::getMessageByReference(['select' => ['message_id', 'res_id_master'], 'reference' => $dataObject->MessageReceivedIdentifier->value]);
            $dataObject->TransferringAgency = $dataObject->Sender;
            $dataObject->ArchivalAgency     = $dataObject->Receiver;
            MessageExchangeModel::updateReceptionDateMessage(['reception_date' => $dataObject->Date, 'message_id' => $messageExchange['message_id']]);
        } elseif ($dataObject->type == 'ArchiveTransferReply') {
            $messageExchange = MessageExchangeModel::getMessageByReference(['select' => ['message_id', 'res_id_master'], 'reference' => $dataObject->MessageRequestIdentifier->value]);
        }

        $messageExchangeSaved = SendMessageExchangeController::saveMessageExchange(['dataObject' => $dataObject, 'res_id_master' => $messageExchange['res_id_master'], 'type' => $data['type']]);
        if (!empty($messageExchangeSaved['error'])) {
            return $response->withStatus(400)->withJson(['errors' => $messageExchangeSaved['error']]);
        }

        return $response->withJson([
            "messageId" => $messageExchangeSaved['messageId']
        ]);
    }

    protected function addComment($str)
    {
        $comment = new \stdClass();
        $comment->value = $str;

        self::$aComments[] = $comment;
    }
}
