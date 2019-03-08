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

use Sendmail\Models\MailModel;
use SrcCore\models\CoreConfigModel;
use MessageExchange\models\MessageExchangeModel;

class AdapterEmailController
{
    public function send($messageObject, $messageId)
    {
        $res['status'] = 0;
        $res['content'] = '';

        $xml = CoreConfigModel::getXmlLoaded(['path' => 'modules/export_seda/xml/config.xml']);
        $gec = strtolower($xml->M2M->gec);

        if ($gec == 'maarch_courrier') {
            $sendmail = new \stdClass();
            $sendmail->coll_id                = 'letterbox_coll';
            $sendmail->res_id                 = $messageObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->OriginatingSystemId;
            $sendmail->user_id                = $messageObject->TransferringAgency->OrganizationDescriptiveMetadata->UserIdentifier;
            $sendmail->to_list                = $messageObject->ArchivalAgency->OrganizationDescriptiveMetadata->Communication[0]->value;
            $sendmail->cc_list                = '';
            $sendmail->cci_list               = '';
            $sendmail->email_object           = $messageObject->DataObjectPackage->DescriptiveMetadata->ArchiveUnit[0]->Content->Title[0];
            $sendmail->email_body             = $messageObject->Comment[0]->value;
            $sendmail->is_res_master_attached = 'N';
            $sendmail->email_status           = 'W';
            $sendmail->sender_email           = $messageObject->TransferringAgency->OrganizationDescriptiveMetadata->Contact[0]->Communication[1]->value;

            $sendmail->message_exchange_id = $messageId;

            $date = new \DateTime;
            $sendmail->creation_date = $date->format(\DateTime::ATOM);

            MailModel::createMail($sendmail);

            MessageExchangeModel::updateStatusMessage(['reference' => $messageObject->MessageIdentifier->value, 'status' => 'I']);
        }

        return $res;
    }
}
