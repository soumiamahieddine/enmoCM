<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Controller
* @author dev@maarch.org
*/

namespace ExportSeda\controllers;

use SrcCore\models\CoreConfigModel;

class SendMessageController
{
    public static function send($messageObject, $messageId, $type)
    {
        $channel = $messageObject->ArchivalAgency->OrganizationDescriptiveMetadata->Communication[0]->Channel;

        $adapter = '';
        if ($channel == 'url') {
            $adapter = new AdapterWS();
        } elseif ($channel == 'email') {
            $adapter = new AdapterEmail();
        } else {
            return false;
        }

        $res = $adapter->send($messageId, $type);

        return $res;
    }

    public static function generateMessageFile($aArgs = [])
    {
        $messageObject = $aArgs['messageObject'];
        $type          = $aArgs['type'];

        $DOMTemplate = new DOMDocument();
        $DOMTemplate->load('../../../../../modules/exportSeda/resources/'.$type.'.xml');
        $DOMTemplateProcessor = new DOMTemplateProcessorController($DOMTemplate);
        $DOMTemplateProcessor->setSource($type, $messageObject);
        $DOMTemplateProcessor->merge();
        $DOMTemplateProcessor->removeEmptyNodes();

        $tmpPath = CoreConfigModel::getTmpPath();
        file_put_contents($tmpPath . $messageObject->MessageIdentifier->value . ".xml", $DOMTemplate->saveXML());

        if ($messageObject->DataObjectPackage) {
            foreach ($messageObject->DataObjectPackage->BinaryDataObject as $binaryDataObject) {
                $base64_decoded = base64_decode($binaryDataObject->Attachment->value);
                $file = fopen($tmpPath . $binaryDataObject->Attachment->filename, 'w');
                fwrite($file, $base64_decoded);
                fclose($file);
            }
        }
        $filename = self::generateZip($messageObject, $tmpPath);

        return $filename;
    }

    private static function generateZip($messageObject, $tmpPath)
    {
        $zip = new \ZipArchive();
        $filename = $tmpPath.$messageObject->MessageIdentifier->value. ".zip";

        $zip->open($filename, \ZipArchive::CREATE);

        $zip->addFile($tmpPath . $messageObject->MessageIdentifier->value . ".xml", $messageObject->MessageIdentifier->value . ".xml");

        if ($messageObject->DataObjectPackage) {
            foreach ($messageObject->DataObjectPackage->BinaryDataObject as $binaryDataObject) {
                $zip->addFile($tmpPath . $binaryDataObject->Attachment->filename, $binaryDataObject->Attachment->filename);
            }
        }

        return $filename;
    }
}
