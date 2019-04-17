<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief XParaph Controller
* @author dev@maarch.org
*/

namespace ExternalSignatoryBook\controllers;

use Attachment\models\AttachmentModel;
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;

class XParaphController
{
    public static function sendDatas($aArgs)
    {
        $attachments = AttachmentModel::getOnView([
            'select'    => [
                'res_id', 'res_id_version', 'title'],
            'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP')", "in_signature_book = 'true'"],
            'data'      => [$aArgs['resIdMaster'], ['converted_pdf', 'incoming_mail_attachment', 'print_folder', 'signed_response']]
        ]);

        $attachmentToFreeze = [];

        foreach ($attachments as $value) {
            if (!empty($value['res_id'])) {
                $resId      = $value['res_id'];
                $collId     = 'attachments_coll';
                $is_version = false;
            } else {
                $resId      = $value['res_id_version'];
                $collId     = 'attachments_version_coll';
                $is_version = true;
            }

            $adrInfo       = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => $collId, 'isVersion' => $is_version]);
            $docserverInfo = DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id']]);
            $filePath      = $docserverInfo['path_template'] . str_replace('#', '/', $adrInfo['path']) . $adrInfo['filename'];
            $filesize      = filesize($filePath);
            $fileContent   = file_get_contents($filePath);
            
            $xmlStep = '';
            foreach ($aArgs['steps'] as $key => $step) {
                $xmlStep .= '<EtapeDepot>
                                <user_siret xsi:type="xsd:string">'.$aArgs['info']['siret'].'</user_siret>
                                <user_login xsi:type="xsd:string">'.$step['login'].'</user_login>
                                <action xsi:type="xsd:string">'.$step['action'].'</action>
                                <contexte xsi:type="xsd:string">'.$step['contexte'].'</contexte>
                                <norejet xsi:type="xsd:string">0</norejet>
                                <ordre xsi:type="xsd:int">'.$key.'</ordre>
                            </EtapeDepot>';
            }

            $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:parafwsdl" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
                <soapenv:Header/>
                <soapenv:Body>
                <urn:XPRF_Deposer soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <params xsi:type="urn:XPRF_Deposer_Param">
                        <reponse xsi:type="xsd:string">SOAP</reponse>
                        <siret xsi:type="xsd:string">'.$aArgs['info']['siret'].'</siret>
                        <login xsi:type="xsd:string">'.$aArgs['info']['login'].'</login>
                        <password xsi:type="xsd:string">'.$aArgs['info']['password'].'</password>
                        <docutype xsi:type="xsd:string">'.$aArgs['config']['data']['docutype'].'</docutype>
                        <docustype xsi:type="xsd:string">'.$aArgs['config']['data']['docustype'].'</docustype>
                        <objet xsi:type="xsd:string">'.$value['title'].'</objet>
                        <contenu xsi:type="xsd:base64Binary">'.base64_encode($fileContent).'</contenu>
                        <nom xsi:type="xsd:string">'.$value['title'].'</nom>
                        <taille xsi:type="xsd:int">'.$filesize.'</taille>
                        <pml xsi:type="xsd:string">1</pml>
                        <avertir xsi:type="xsd:string">1</avertir>
                        <etapes xsi:type="urn:EtapeDepot" soapenc:arrayType="urn:EtapeDepotItem[]">'.$xmlStep.'</etapes>
                    </params>
                </urn:XPRF_Deposer>
                </soapenv:Body>
            </soapenv:Envelope>';

            $response = CurlModel::execSOAP([
                'soapAction'    => 'urn:parafwsdl#paraf',
                'url'           => $aArgs['config']['data']['url'],
                'xmlPostString' => $xmlPostString,
                'options'       => [CURLOPT_SSL_VERIFYPEER => false]
            ]);

            $isError = $response['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
            if (!empty($isError->Fault[0])) {
                $error = $response['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->Fault[0]->children()->detail;
                return ['error' => $error];
            } else {
                $depotId = $response['response']->children('SOAP-ENV', true)->Body->children('ns1', true)->XPRF_DeposerResponse->children()->return->children()->depotid;
                $attachmentToFreeze[$collId][$resId] = (string)$depotId;
            }
        }

        return $attachmentToFreeze;
    }

    public static function getWorkflow(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getQueryParams();
        foreach (['login', 'siret', 'password'] as $value) {
            if (empty($data[$value])) {
                return $response->withStatus(400)->withJson(['errors' => $value . ' is empty']);
            }
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);
        $config = [];

        if (!empty($loadedXml)) {
            foreach ($loadedXml->signatoryBook as $value) {
                if ($value->id == "xParaph") {
                    $config['data'] = (array)$value;
                    break;
                }
            }
        } else {
            return $response->withStatus(403)->withJson(['errors' => 'xParaph is not enabled']);
        }

        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:parafwsdl">
            <soapenv:Header/>
            <soapenv:Body>
                <urn:XPRF_Initialisation_Deposer soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <params xsi:type="urn:XPRF_Initialisation_Deposer_Param">
                        <siret xsi:type="xsd:string">'.$data['siret'].'</siret>
                        <login xsi:type="xsd:string">'.$data['login'].'</login>
                        <password xsi:type="xsd:string">'.$data['password'].'</password>
                        <action xsi:type="xsd:string">DETAIL</action>
                        <scenario xsi:type="xsd:string">' . $config['data']['docutype'] . '-' . $config['data']['docustype'].'</scenario>
                        <version xsi:type="xsd:string">2</version>
                    </params>
                </urn:XPRF_Initialisation_Deposer>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curlResponse = CurlModel::execSOAP([
            'soapAction'    => 'urn:parafwsdl#paraf',
            'url'           => $config['data']['url'],
            'xmlPostString' => $xmlPostString,
            'options'       => [CURLOPT_SSL_VERIFYPEER => false]
        ]);

        $isError = $curlResponse['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        if (!empty($isError->Fault[0])) {
            $error = $curlResponse['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->Fault[0]->children()->detail;
            return $response->withStatus(403)->withJson(['errors' => $error]);
        } else {
            $details = $curlResponse['response']->children('SOAP-ENV', true)->Body->children('ns1', true)->XPRF_Initialisation_DeposerResponse->children()->return->children()->Retour_XML;
            $xmlData = simplexml_load_string($details);

            $userWorkflow = [];
            foreach ($xmlData->SCENARIO->AUTORISATIONS->VISEURS->VISEUR as $value) {
                $userWorkflow[(string)$value->ACTEUR_LOGIN]["userId"]      = (string)$value->ACTEUR_LOGIN;
                $userWorkflow[(string)$value->ACTEUR_LOGIN]["displayName"] = (string)$value->ACTEUR_NOM;
                $userWorkflow[(string)$value->ACTEUR_LOGIN]["roles"][]     = "visa";
            }
            foreach ($xmlData->SCENARIO->AUTORISATIONS->SIGNATAIRES->SIGNATAIRE as $value) {
                $userWorkflow[(string)$value->ACTEUR_LOGIN]["userId"]      = (string)$value->ACTEUR_LOGIN;
                $userWorkflow[(string)$value->ACTEUR_LOGIN]["displayName"] = (string)$value->ACTEUR_NOM;
                $userWorkflow[(string)$value->ACTEUR_LOGIN]["roles"][]     = "sign";
            }
            $workflow = [];
            foreach ($userWorkflow as $value) {
                $workflow[] = $value;
            }
    
            return $response->withJson(['workflow' => $workflow]);
        }
    }

    public static function retrieveSignedMails($aArgs)
    {
        $validatedSignature   = $aArgs['config']['data']['validatedStateSignature'];
        $validatedNoSignature = $aArgs['config']['data']['validatedStateNoSignature'];
        $refused              = $aArgs['config']['data']['refusedState'];

        foreach (['noVersion', 'isVersion'] as $version) {
            $depotids = [];
            foreach ($aArgs['idsToRetrieve'][$version] as $resId => $value) {
                $depotids[$value->external_id] = $resId;
            }

            if (!empty($depotids)) {
                $avancements = XParaphController::getAvancement(['config' => $aArgs['config'], 'depotsIds' => $depotids]);
            } else {
                unset($aArgs['idsToRetrieve'][$version]);
                continue;
            }

            foreach ($aArgs['idsToRetrieve'][$version] as $resId => $value) {
                $avancement = $avancements[$value->external_id];

                $state = XParaphController::getState(['avancement' => $avancement]);

                if ($state['id'] == 'refused') {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'refused';
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = $state['note'];
                } elseif ($state['id'] == 'validateSignature' || $state['id'] == 'validateNoSignature') {
                    $processedFile = XParaphController::getFile(['config' => $aArgs['config'], 'depotId' => $value->external_id]);
                    if (!empty($processedFile['errors'])) {
                        unset($aArgs['idsToRetrieve'][$version][$resId]);
                        continue;
                    }
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'validated';
                    $aArgs['idsToRetrieve'][$version][$resId]->format = 'pdf';

                    $file      = base64_decode($processedFile['zip']);
                    $unzipName = 'tmp_file_' .rand(). '_xParaph_' .rand();
                    $tmpName   = $unzipName . '.zip';
            
                    $tmpPath = CoreConfigModel::getTmpPath();
                    file_put_contents($tmpPath . $tmpName, $file);

                    $zip = new \ZipArchive();
                    $zip->open($tmpPath . $tmpName);
                    $zip->extractTo($tmpPath . $unzipName);

                    foreach (glob($tmpPath . $unzipName . '/*.pdf') as $filename) {
                        $encodedFile = base64_encode(file_get_contents($filename));
                    }
                    unlink($tmpPath . $tmpName);

                    $aArgs['idsToRetrieve'][$version][$resId]->encodedFile = $encodedFile;
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = $state['note'];
                } else {
                    unset($aArgs['idsToRetrieve'][$version][$resId]);
                }
            }
        }

        // retourner seulement les mails récupérés (validés ou signés)
        return $aArgs['idsToRetrieve'];
    }

    public static function getState($aArgs)
    {
        // remove first step. Always deposit
        unset($aArgs['avancement'][0]);
        $state['id'] = 'validateNoSignature';
        $signature   = false;

        foreach ($aArgs['avancement'] as $step) {
            if ($step['etat'] == 'ACTIVE') {
                $state['id'] = 'ACTIVE';
                break;
            } elseif ($step['etat'] == 'KO') {
                $state['id']   = 'refused';
                $state['note'] = $step['note'];
                break;
            }
            if ($step['typeEtape'] == 'SIGN') {
                $signature = true;
            }
        }

        if ($signature) {
            $state['id']   = 'validateSignature';
        }
        $state['note'] = $step['note'];

        return $state;
    }

    public static function getAvancement($aArgs)
    {
        $depotIds = '';

        foreach ($aArgs['depotsIds'] as $key => $step) {
            $depotIds .= '<listDepotIds>'.$key.'</listDepotIds>';
        }

        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:parafwsdl" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
            <soapenv:Header/>
            <soapenv:Body>
                <urn:XPRF_AvancementDepot soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <params xsi:type="urn:XPRF_AvancementDepot_Param">
                        <siret xsi:type="xsd:string">?</siret>
                        <login xsi:type="xsd:string">?</login>
                        <password xsi:type="xsd:string">?</password>
                        <depotids xsi:type="urn:listDepotIds" soapenc:arrayType="xsd:string[]">' . $depotIds . '</depotids>
                        <withNote xsi:type="xsd:string">1</withNote>
                    </params>
                </urn:XPRF_AvancementDepot>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curlResponse = CurlModel::execSOAP([
            'soapAction'    => 'urn:parafwsdl#paraf',
            'url'           => $aArgs['config']['data']['url'],
            'xmlPostString' => $xmlPostString,
            'options'       => [CURLOPT_SSL_VERIFYPEER => false]
        ]);

        $isError = $curlResponse['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        if (!empty($isError->Fault[0])) {
            $error = $curlResponse['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->Fault[0]->children()->detail;
            return ['errors' => $error];
        } else {
            $details = $curlResponse['response']->children('SOAP-ENV', true)->Body->children('ns1', true)->XPRF_AvancementDepotResponse->children()->return;
            return json_decode($details, true);
        }
    }

    public static function getFile($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:parafwsdl">
            <soapenv:Header/>
            <soapenv:Body>
                <urn:XPRF_getFiles soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <params xsi:type="urn:XPRF_getFiles_Param">
                        <siret xsi:type="xsd:string">?</siret>
                        <login xsi:type="xsd:string">?</login>
                        <password xsi:type="xsd:string">?</password>
                        <depotid xsi:type="xsd:string">'.$aArgs['depotId'].'</depotid>
                    </params>
                </urn:XPRF_getFiles>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curlResponse = CurlModel::execSOAP([
            'soapAction'    => 'urn:parafwsdl#paraf',
            'url'           => $aArgs['config']['data']['url'],
            'xmlPostString' => $xmlPostString,
            'options'       => [CURLOPT_SSL_VERIFYPEER => false]
        ]);

        $isError = $curlResponse['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        if (!empty($isError->Fault[0])) {
            $error = $curlResponse['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->Fault[0]->children()->detail;
            return ['errors' => $error];
        } else {
            $details = $curlResponse['response']->children('SOAP-ENV', true)->Body->children('ns1', true)->XPRF_getFilesResponse->children()->return;
            return json_decode($details, true);
        }
    }
}
