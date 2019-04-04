<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheur Controller
 * @author dev@maarch.org
 */


class MaarchParapheurController
{
    public static function getModal($config)
    {
        $initializeDatas = MaarchParapheurController::getInitializeDatas($config);
        if (!empty($initializeDatas['error'])) {
            return ['error' => $initializeDatas['error']];
        }
        $html .= '<label for="processingUser">' . _USER_MAARCH_PARAPHEUR . '</label><select name="processingUser" id="processingUser">';
        if (!empty($initializeDatas['users'])) {
            foreach ($initializeDatas['users'] as $value) {
                $html .= '<option value="';
                $html .= $value['id'];
                $html .= '">';
                $html .= $value['firstname'] . ' ' . $value['lastname'];
                $html .= '</option>';
            }
        }
        $html .= '</select><br /><br /><br /><br />';
        $html .= '<input type="radio" name="objectSent" id="objectSentNote" value="mail" checked="checked" /><label for="objectSentNote" style="float: none;display: unset;">' . _MAIL_NOTE . '</label><br/>';
        $html .= '<input type="radio" name="objectSent" id="objectSentSign" value="attachment" /><label for="objectSentSign" style="float: none;display: unset;">' . _ATTACHMENT_SIGNATURE .'</label><br /><br />';

        return $html;
    }

    public static function getInitializeDatas($config)
    {
        $rawResponse['users'] = MaarchParapheurController::getUsers(['config' => $config]);
        if (!empty($rawResponse['users']['error'])) {
            return ['error' => $rawResponse['users']['error']];
        }
        return $rawResponse;
    }

    public static function getUsers($aArgs)
    {
        $response = \SrcCore\models\CurlModel::exec([
            'url'      => $aArgs['config']['data']['url'] . '/rest/users',
            'user'     => $aArgs['config']['data']['userId'],
            'password' => $aArgs['config']['data']['password'],
            'method'   => 'GET'
        ]);

        if (!empty($response['error'])) {
            return ["error" => $response['error']];
        }

        return $response['users'];
    }

    public static function sendDatas($aArgs)
    {
        $attachmentToFreeze = [];

        $adrMainInfo              = \Convert\controllers\ConvertPdfController::getConvertedPdfById(['resId' => $aArgs['resIdMaster'], 'collId' => 'letterbox_coll']);
        $docserverMainInfo        = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id']]);
        $arrivedMailMainfilePath  = $docserverMainInfo['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
        $encodedMainZipFile       = MaarchParapheurController::createZip(['filepath' => $arrivedMailMainfilePath, 'filename' => 'courrier_arrivee.pdf']);

        $mainResource = \Resource\models\ResModel::getOnView([
            'select' => ['process_limit_date', 'status', 'category_id', 'alt_identifier', 'subject', 'priority', 'contact_firstname', 'contact_lastname', 'contact_society', 'category_id'],
            'where'  => ['res_id = ?'],
            'data'   => [$aArgs['resIdMaster']]
        ]);
        if (empty($mainResource[0]['process_limit_date'])) {
            $processLimitDate = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' + 14 days'));
        } else {
            $processLimitDate = $mainResource[0]['process_limit_date'];
        }

        $processingUser      = $aArgs['processingUser'];
        $priority            = \Priority\models\PriorityModel::getById(['select' => ['label'], 'id' => $mainResource[0]['priority']]);
        $sender              = \User\models\UserModel::getByLogin(['select' => ['firstname', 'lastname'], 'login' => $aArgs['userId']]);
        $senderPrimaryEntity = \User\models\UserModel::getPrimaryEntityByUserId(['userId' => $aArgs['userId']]);

        if ($aArgs['objectSent'] == 'attachment') {
            $excludeAttachmentTypes = ['converted_pdf', 'print_folder', 'incoming_mail_attachment', 'signed_response'];

            $attachments = \Attachment\models\AttachmentModel::getOnView([
                'select'    => [
                    'res_id', 'res_id_version', 'title', 'identifier', 'attachment_type',
                    'status', 'typist', 'docserver_id', 'path', 'filename', 'creation_date',
                    'validation_date', 'relation', 'attachment_id_master'
                ],
                'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP')", "in_signature_book = 'true'"],
                'data'      => [$aArgs['resIdMaster'], $excludeAttachmentTypes]
            ]);

            foreach ($attachments as $value) {
                if (!empty($value['res_id'])) {
                    $resId  = $value['res_id'];
                    $collId = 'attachments_coll';
                    $is_version = false;
                } else {
                    $resId  = $value['res_id_version'];
                    $collId = 'attachments_version_coll';
                    $is_version = true;
                }
                
                $adrInfo       = \Convert\controllers\ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => $collId, 'isVersion' => $is_version]);
                $docserverInfo = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id']]);
                $filePath      = $docserverInfo['path_template'] . str_replace('#', '/', $adrInfo['path']) . $adrInfo['filename'];
    
                $encodedZipDocument = MaarchParapheurController::createZip(['filepath' => $filePath, 'filename' => $adrInfo['filename']]);
    
                if ($mainResource[0]['category_id'] != 'outgoing') {
                    $attachmentsData = [[
                        'encodedDocument' => $encodedMainZipFile,
                        'subject'            => $mainResource[0]['subject'],
                        'reference'          => $mainResource[0]['alt_identifier']
                    ]];
                } else {
                    $attachmentsData = [];
                }

                $metadata = [];
                if (!empty($priority['label'])) {
                    $metadata[] = [_PRIORITY => $priority['label']];
                }
                if (!empty($senderPrimaryEntity['entity_label'])) {
                    $metadata[] = [_INITIATOR_ENTITY => $senderPrimaryEntity['entity_label']];
                }
                $contact = trim($mainResource[0]['contact_firstname'] . ' ' . $mainResource[0]['contact_lastname'] . ' ' . $mainResource[0]['contact_society']);
                if (!empty($contact)) {
                    $metadata[] = [_RECIPIENTS => $contact];
                }
    
                $bodyData = [
                    'title'           => $value['title'],
                    'reference'       => $value['identifier'],
                    'mode'            => $aArgs['config']['data']['signature'],
                    'encodedDocument' => $encodedZipDocument,
                    'processingUser'  => $processingUser,
                    'sender'          => trim($sender['firstname'] . ' ' .$sender['lastname']),
                    'deadline'        => $processLimitDate,
                    'attachments'     => $attachmentsData,
                    'metadata'        => json_encode($metadata)
                ];
    
                $response = \SrcCore\models\CurlModel::exec([
                    'url'      => $aArgs['config']['data']['url'] . '/rest/documents',
                    'user'     => $aArgs['config']['data']['userId'],
                    'password' => $aArgs['config']['data']['password'],
                    'method'   => 'POST',
                    'bodyData' => $bodyData
                ]);
    
                $attachmentToFreeze[$collId][$resId] = $response['id'];
            }
        } elseif ($aArgs['objectSent'] == 'mail') {
            $metadata = [];
            if (!empty($priority['label'])) {
                $metadata[] = [_PRIORITY => $priority['label']];
            }
            if (!empty($senderPrimaryEntity['entity_label'])) {
                $metadata[] = [_INITIATOR_ENTITY => $senderPrimaryEntity['entity_label']];
            }
            $contact = trim($mainResource[0]['contact_firstname'] . ' ' . $mainResource[0]['contact_lastname'] . ' ' . $mainResource[0]['contact_society']);
            if (!empty($contact)) {
                $metadata[] = [_RECIPIENTS => $contact];
            }

            $bodyData = [
                'title'              => $mainResource[0]['subject'],
                'reference'          => $mainResource[0]['alt_identifier'],
                'mode'               => $aArgs['config']['data']['annotation'],
                'encodedDocument'    => $encodedMainZipFile,
                'processingUser'     => $processingUser,
                'sender'             => trim($sender['firstname'] . ' ' .$sender['lastname']),
                'deadline'           => $processLimitDate,
                'metadata'           => json_encode($metadata)
            ];

            $response = \SrcCore\models\CurlModel::exec([
                'url'      => $aArgs['config']['data']['url'] . '/rest/documents',
                'user'     => $aArgs['config']['data']['userId'],
                'password' => $aArgs['config']['data']['password'],
                'method'   => 'POST',
                'bodyData' => $bodyData
            ]);

            $attachmentToFreeze['letterbox_coll'][$aArgs['resIdMaster']] = $response['id'];
        }

        return $attachmentToFreeze;
    }

    public static function createZip($aArgs)
    {
        $zip = new ZipArchive();

        $pathInfo    = pathinfo($aArgs['filepath'], PATHINFO_FILENAME);
        $tmpPath     = \SrcCore\models\CoreConfigModel::getTmpPath();
        $zipFilename = $tmpPath . $pathInfo."_".rand().".zip";

        if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {
            $zip->addFile($aArgs['filepath'], $aArgs['filename']);

            $zip->close();

            $fileContent = file_get_contents($zipFilename);
            $base64 =  base64_encode($fileContent);
            return $base64;
        } else {
            echo 'Impossible de créer l\'archive;';
        }
    }

    public static function getUserById($aArgs)
    {
        $response = \SrcCore\models\CurlModel::exec([
            'url'      => $aArgs['config']['data']['url'] . '/rest/users/'.$aArgs['id'],
            'user'     => $aArgs['config']['data']['userId'],
            'password' => $aArgs['config']['data']['password'],
            'method'   => 'GET'
        ]);

        return $response['user'];
    }
}
