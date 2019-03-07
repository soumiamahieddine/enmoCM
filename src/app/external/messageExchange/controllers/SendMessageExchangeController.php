<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Send Message Exchange Review Controller
* @author dev@maarch.org
*/

namespace MessageExchange\controllers;

use MessageExchange\models\MessageExchangeModel;

class SendMessageExchangeController
{
    public static function saveMessageExchange($aArgs = [])
    {
        $dataObject = $aArgs['dataObject'];
        $oData                                        = new \stdClass();
        $oData->messageId                             = MessageExchangeModel::generateUniqueId();
        $oData->date                                  = $dataObject->Date;

        $oData->MessageIdentifier                     = new \stdClass();
        $oData->MessageIdentifier->value              = $dataObject->MessageIdentifier->value;
        
        $oData->TransferringAgency                    = new \stdClass();
        $oData->TransferringAgency->Identifier        = new \stdClass();
        $oData->TransferringAgency->Identifier->value = $dataObject->TransferringAgency->Identifier->value;
        
        $oData->ArchivalAgency                        = new \stdClass();
        $oData->ArchivalAgency->Identifier            = new \stdClass();
        $oData->ArchivalAgency->Identifier->value     = $dataObject->ArchivalAgency->Identifier->value;
        
        $oData->archivalAgreement                     = new \stdClass();
        $oData->archivalAgreement->value              = ""; // TODO : ???
        
        $replyCode = "";
        if (!empty($dataObject->ReplyCode)) {
            $replyCode = $dataObject->ReplyCode;
        }

        $oData->replyCode                             = new \stdClass();
        $oData->replyCode                             = $replyCode;

        $dataObject = self::cleanBase64Value(['dataObject' => $dataObject]);

        $aDataExtension = [
            'status'            => 'W',
            'fullMessageObject' => $dataObject,
            'resIdMaster'       => $aArgs['res_id_master'],
            'SenderOrgNAme'     => $dataObject->TransferringAgency->OrganizationDescriptiveMetadata->Contact[0]->DepartmentName,
            'RecipientOrgNAme'  => $dataObject->ArchivalAgency->OrganizationDescriptiveMetadata->Name,
            'filePath'          => $aArgs['file_path'],
        ];

        $messageId = MessageExchangeModel::insertMessage([
            "data"          => $oData,
            "type"          => $aArgs['type'],
            "dataExtension" => $aDataExtension,
            "userId"        => $aArgs['userId']
        ]);

        return $messageId;
    }

    protected static function cleanBase64Value($aArgs = [])
    {
        $dataObject = $aArgs['dataObject'];
        $aCleanDataObject = [];
        if (!empty($dataObject->DataObjectPackage->BinaryDataObject)) {
            foreach ($dataObject->DataObjectPackage->BinaryDataObject as $key => $value) {
                $value->Attachment->value = "";
                $aCleanDataObject[$key] = $value;
            }
            $dataObject->DataObjectPackage->BinaryDataObject = $aCleanDataObject;
        }
        return $dataObject;
    }
}
