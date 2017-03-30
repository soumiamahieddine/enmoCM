<?php

/*
*   Copyright 2008-2017 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once __DIR__.'/RequestSeda.php';
require_once __DIR__.'/DOMTemplateProcessor.php';

class ArchiveTransfer
{
    private $db;

    public function __construct()
    {
        $this->db = new RequestSeda();
        $_SESSION['error'] = "";
    }

    public function receive($listResId)
    {
        if (!$listResId) {
            return false;
        }
        
        $messageObject = new stdClass();
        $messageObject = $this->initMessage($messageObject);

        $result = [];
        foreach ($listResId as $resId) {
            $result .= $resId.'#';

            $letterbox = $this->db->getCourrier($resId);
            $attachments = $this->db->getAttachments($letterbox->res_id);

            $archiveUnitId = uniqid();
            if ($letterbox->filename) {
                $messageObject->dataObjectPackage->descriptiveMetadata->archiveUnit[] = $this->getArchiveUnit($letterbox, "File", $attachments,$archiveUnitId, $letterbox->res_id, null);
                $messageObject->dataObjectPackage->binaryDataObject[] = $this->getBinaryDataObject($letterbox);
            } else {
                $messageObject->dataObjectPackage->descriptiveMetadata->archiveUnit[] = $this->getArchiveUnit($letterbox, "File");
            }

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    if ($attachment->attachment_type == "simple_attachment" || $attachment->attachment_type == "signed_response") {
                        if ($attachment->attachment_type == "signed_response" && $attachment->res_id_master == $letterbox->res_id) {
                            $messageObject->dataObjectPackage->descriptiveMetadata->archiveUnit[] = $this->getArchiveUnit($attachment, "Response", null, null,"attachment_".$attachment->res_id, $archiveUnitId);
                        }

                        $messageObject->dataObjectPackage->binaryDataObject[] = $this->getBinaryDataObject($attachment,true);
                    }
                }
            }
        }

        $res = $this->db->insertMessage($messageObject,$listResId);

        if ($res) {
            $this->sendXml($messageObject);
        } else {
            return $res;
        }
        
        return $result;
    }

    public function sendXml($messageObject)
    {
        $DOMTemplate = new DOMDocument();
        $DOMTemplate->load(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'ArchiveTransfer.xml');
        $DOMTemplateProcessor = new DOMTemplateProcessor($DOMTemplate);
        $DOMTemplateProcessor->setSource('ArchiveTransfer', $messageObject);
        $DOMTemplateProcessor->merge();
        $DOMTemplateProcessor->removeEmptyNodes();

        if (!is_dir(__DIR__.DIRECTORY_SEPARATOR.'seda2')) {
            mkdir(__DIR__.DIRECTORY_SEPARATOR.'seda2', 0777, true);
        }

        $messageId = $messageObject->messageIdentifier->value;
        if (!is_dir(__DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$messageId)) {
            mkdir(__DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$messageId, 0777, true);
        }

        file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$messageId.DIRECTORY_SEPARATOR.$messageId.'.xml', $DOMTemplate->saveXML());

        $this->sendAttachment($messageObject);

        return $xml;
    }

    public function deleteMessage($listResId)
    {
        if (!$listResId) {
            return false;
        }

        $resIds = [];
        if (!is_array($listResId)) {
            $resIds[] = $listResId;
        } else {
            $resIds = $listResId;
        }


        foreach ($resIds as $resId) {
            $unitIdentifiers = $this->db->getUnitIdentifierByResId($resId);
            foreach ($unitIdentifiers as $unitIdentifier) {
                $this->db->deleteSeda($unitIdentifier->message_id);
                $this->db->deleteUnitIdentifier($unitIdentifier->message_id);
            }
        }

        return true;
    }
    private function sendAttachment($messageObject)
    {
        $messageId = $messageObject->messageIdentifier->value;

        foreach ($messageObject->dataObjectPackage->binaryDataObject as $binaryDataObject) {
            $basename = basename($binaryDataObject->uri);
            $dest = __DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$messageId.DIRECTORY_SEPARATOR.$basename;

            copy($binaryDataObject->uri, $dest);
        }
    }

    private function initMessage($messageObject)
    {
        $date = new DateTime;
        $messageObject->date = $date->format(DateTime::ATOM);
        $messageObject->messageIdentifier = new stdClass();
        $messageObject->messageIdentifier->value = $_SESSION['user']['UserId'] . "-" . date('Ymd-His');

        $messageObject->transferringAgency = new stdClass();
        $messageObject->transferringAgency->identifier = new stdClass();

        $messageObject->archivalAgency = new stdClass();
        $messageObject->archivalAgency->identifier = new stdClass();

        $messageObject->archivalAgreement = new stdClass();

        foreach ($_SESSION['user']['entities'] as $entitie) {
            $entitie = $this->db->getEntitie($entitie['ENTITY_ID']);
            if ($entitie) {
                $messageObject->transferringAgency->identifier->value = $entitie->business_id;
                $messageObject->archivalAgency->identifier->value = $entitie->archival_agency;

                if (!$entitie->business_id) {
                    $_SESSION['error'] .= _TRANSFERRING_AGENCY_SIREN_COMPULSORY;
                }

                if (!$entitie->archival_agency) {
                    $_SESSION['error'] .= _ARCHIVAL_AGENCY_SIREN_COMPULSORY;
                }

                $messageObject->archivalAgreement->value = $entitie->archival_agreement;
            } else {
                // TODO return error;
            }
        }
        
        $messageObject->dataObjectPackage = new stdClass();
        $messageObject->dataObjectPackage->binaryDataObject = [];
        $messageObject->dataObjectPackage->descriptiveMetadata = new stdClass();
        $messageObject->dataObjectPackage->managementMetadata = new stdClass();
        $messageObject->dataObjectPackage->descriptiveMetadata->archiveUnit = [];

        return $messageObject;
    }

    private function getArchiveUnit($object, $type, $attachments =null, $archiveUnitId = null, $dataObjectReferenceId = null, $relatedObjectReference =null)
    {
        $messageArchiveUnit = new stdClass();

        if ($archiveUnitId) {
            $messageArchiveUnit->id = $archiveUnitId;
        } else {
            $messageArchiveUnit->id = uniqid();
        }
        
        if ($relatedObjectReference) {
            $messageArchiveUnit->content = $this->getContent($object, $type, $relatedObjectReference);
        } else {
            $messageArchiveUnit->content = $this->getContent($object, $type);
        }

        if ($object->type_id != 0) {
            $messageArchiveUnit->management = $this->getManagement($object);
        }

        if ($dataObjectReferenceId) {
            $messageArchiveUnit->dataObjectReference = new stdClass();
            $messageArchiveUnit->dataObjectReference->dataObjectReferenceId = "doc_".$dataObjectReferenceId;
        }

        
        if ($attachments) {
            $messageArchiveUnit->archiveUnit = [];
            foreach ($attachments as $attachment) {
                if ($attachment->res_id_master == $object->res_id) {
                    if ($attachment->attachment_type == "simple_attachment") {
                        $messageArchiveUnit->archiveUnit[] = $this->getArchiveUnit($attachment, "Item", null, null, "attachment_".$attachment->res_id);
                    }
                }
            }
        }

        if (count($messageArchiveUnit->archiveUnit) == 0) {
            unset($messageArchiveUnit->archiveUnit);
        }
        
        return $messageArchiveUnit;
    }

    private function getContent($object, $type, $relatedObjectReference = null)
    {
        $content = new stdClass();

        if ($type == "File") {
            $content->descriptionLevel = $type;
            $content->receivedDate = $object->admission_date;
            $sentDate = new DateTime($object->doc_date);
            $receivedDate = new DateTime($object->admission_date);
            $content->sentDate = $sentDate->format(DateTime::ATOM);
            $content->receivedDate = $receivedDate->format(DateTime::ATOM);

            $content->addressee = [];
            $content->keyword = [];

            if ($object->exp_contact_id) {
                
                $contact = $this->db->getContact($object->exp_contact_id);
                $entitie = $this->db->getEntitie($object->destination);

                $content->keyword[] = $this->getKeyword($contact);
                $content->addressee[] = $this->getAddresse($entitie,"entitie");
            } else if ($object->dest_contact_id) {
                $contact = $this->db->getContact($object->dest_contact_id);
                $entitie = $this->db->getEntitie($object->destination);

                $content->addressee[] = $this->getAddresse($contact);
                $content->keyword[] = $this->getKeyword($entitie,"entitie");
            } else if ($object->exp_user_id) {
                $user = $this->db->getUserInformation($object->exp_user_id);
                $entitie = $this->db->getEntitie($object->initiator);
                //$entitie = $this->getEntitie($letterbox->destination);

                $content->keyword[] = $this->getKeyword($user);
                $content->addressee[] = $this->getAddresse($entitie,"entitie");
            }
            
            $content->source = $_SESSION['mail_nature'][$object->nature_id];

            $content->documentType = $object->type_label;
            $content->originatingAgencyArchiveUnitIdentifier = $object->alt_identifier;
            $content->originatingSystemId = $object->res_id;
            $content->title = [];
            $content->title[] = $object->subject;
            $endDate = new DateTime($object->process_limit_date);
            $content->endDate = $endDate->format(DateTime::ATOM);

        } else {
            $content->descriptionLevel = "Item";
            $content->title = [];
            $content->title[] = $object->title;
            $content->originatingSystemId = $object->res_id;
            $content->documentType = "Attachment";

            if ($type == "Response") {
                $reference = new stdClass();
                $reference->repositoryArchiveUnitPID = $relatedObjectReference;

                $content->relatedObjectReference = new stdClass();
                $content->relatedObjectReference->references = [];

                $repositoryArchiveUnitPID = new stdClass();
                $repositoryArchiveUnitPID = $reference;
                $content->relatedObjectReference->references[] = $repositoryArchiveUnitPID;
            }
        }

        /*$notes = $this->getNotes($letterbox->res_id);
        $content->custodialHistory = new stdClass();
        $content->custodialHistory->custodialHistoryItem = [];

        foreach ($notes as $note) {
            $content->custodialHistory->custodialHistoryItem[] = $this->getCustodialHistoryItem($note);
        }*/

        return $content;
    }

    private function getManagement($letterbox) {
        $management = new stdClass();

        $docTypes = $this->db->getDocTypes($letterbox->type_id);

        $management->appraisalRule = new stdClass();
        $management->appraisalRule->rule = new stdClass();
        $management->appraisalRule->rule->value = $docTypes->retention_rule;
        if ($docTypes->retention_final_disposition == "preservation") {
            $management->appraisalRule->finalAction = "Keep";
        } else {
            $management->appraisalRule->finalAction = "Destroy";
        }
        
        
        return $management;
    }

    private function getBinaryDataObject($object, $isAttachment = false)
    {
        $docServers = $this->db->getDocServer($object->docserver_id);

        $binaryDataObject = new stdClass();

        if ($isAttachment) {
            $binaryDataObject->id = "attachment_".$object->res_id;
        } else {
            $binaryDataObject->id = $object->res_id;
        }
        
        $binaryDataObject->messageDigest = new stdClass();
        $binaryDataObject->messageDigest->value = $object->fingerprint;
        $binaryDataObject->messageDigest->algorithm = "xxx";

        $binaryDataObject->size = new stdClass();
        $binaryDataObject->size->value = $object->filesize;

        $uri = str_replace("##", DIRECTORY_SEPARATOR, $object->path);
        $uri =  str_replace("#", DIRECTORY_SEPARATOR, $uri);
        $uri .= $object->filename;
        $binaryDataObject->uri = $docServers->path_template.$uri;

        return $binaryDataObject;
    }

    private function getKeyword($informations, $type = null)
    {
        $keyword = new stdClass();
        $keyword->keywordContent = new stdClass();

        if ($type == "entitie") {
            $keyword->keywordType = "corpname";
            $keyword->keywordContent = $informations->business_id;
        } else if ($informations->is_corporate_person == "Y") {
            $keyword->keywordType = "corpname";
            $keyword->keywordContent->value = $informations->society;
        } else {
            $keyword->keywordType = "personname";
            $keyword->keywordContent->value = $informations->lastname . " " . $informations->firstname;
        }

        return $keyword;
    }

    private function getAddresse($informations, $type = null)
    {
        $addressee = new stdClass();
        if ($type == "entitie") {
            $addressee->corpname = $informations->entity_label;
            $addressee->identifier = $informations->business_id;
        } else if ($informations->is_corporate_person == "Y") {
            $addressee->corpname = $informations->society;
            $addressee->identifier = $informations->contact_id;
        } else {
            $addressee->firstName = $informations->firstname;
            $addressee->birthName = $informations->lastname;
        }
            

        return $addressee;
    }

    private function getCustodialHistoryItem($note) 
    {
        $custodialHistoryItem = new stdClass();

        $custodialHistoryItem->value = $note->note_text;
        $custodialHistoryItem->when = $note->date_note;

        return $custodialHistoryItem;
    }
}