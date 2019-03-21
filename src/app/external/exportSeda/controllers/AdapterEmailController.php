<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Adapter Email Controller
* @author dev@maarch.org
*/

namespace ExportSeda\controllers;

use Email\controllers\EmailController;
use MessageExchange\models\MessageExchangeModel;
use SrcCore\models\CoreConfigModel;
use User\models\UserModel;

class AdapterEmailController
{
    public function send($messageObject, $messageId)
    {
        $res['status'] = 0;
        $res['content'] = '';

        $xml = CoreConfigModel::getXmlLoaded(['path' => 'modules/export_seda/xml/config.xml']);
        $gec = strtolower($xml->M2M->gec);

        if ($gec == 'maarch_courrier') {
            $document = ['id' => $messageObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->OriginatingSystemId, 'isLinked' => false, 'original' => false];
            $userInfo = UserModel::getByLogin(['login' => $messageObject->TransferringAgency->OrganizationDescriptiveMetadata->UserIdentifier, 'select' => ['id']]);
            EmailController::createEmail([
                'userId'    => $userInfo['id'],
                'data'      => [
                    'sender'        => ['email' => $messageObject->TransferringAgency->OrganizationDescriptiveMetadata->Contact[0]->Communication[1]->value],
                    'recipients'    => [$messageObject->ArchivalAgency->OrganizationDescriptiveMetadata->Communication[0]->value],
                    'cc'            => '',
                    'cci'           => '',
                    'object'        => $messageObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->Title[0],
                    'body'          => $messageObject->Comment[0]->value,
                    'document'      => $document,
                    'isHtml'        => true,
                    'status'        => 'TO_SEND',
                    'messageExchangeId' => $messageId
                ]
            ]);

            MessageExchangeModel::updateStatusMessage(['reference' => $messageObject->MessageIdentifier->value, 'status' => 'I']);
        }

        return $res;
    }
}
