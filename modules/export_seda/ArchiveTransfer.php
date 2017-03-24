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

require_once 'core/class/class_request.php';
require_once __DIR__.'/DOMTemplateProcessor.php';

class ArchiveTransfer {

	private $db;

	public function __construct() 
	{
		$this->db = new Database();
	}

	public function receive($listResId) {
		if (!$listResId) {
			return false;
		}

		$messageObject = new stdClass();
		$messageObject = $this->initMessage($messageObject);

		$result = [];
		foreach ($listResId as $resId) {
			$result .= $resId.'#';

			$letterbox = $this->getCourrier($resId);
			$attachments = $this->getAttachments($letterbox->res_id);

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

		$res = $this->insertMessage($messageObject);

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

		foreach ($listResId as $resId) {
			$unitIdentifiers = $this->getUnitIdentifier($resId);
			foreach ($unitIdentifiers as $unitIdentifier) {
				$this->deleteSeda($unitIdentifier->message_id);
				$this->deleteUnitIdentifier($unitIdentifier->message_id);
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
		$messageObject->date = date('Y-m-d h:i:s');
		$messageObject->messageIdentifier = new stdClass();
		$messageObject->messageIdentifier->value = $_SESSION['user']['UserId'] . "-" . date('Ymd-His');

		$messageObject->transferringAgency = new stdClass();
		$messageObject->transferringAgency->identifier = new stdClass();

		$messageObject->archivalAgency = new stdClass();
		$messageObject->archivalAgency->identifier = new stdClass();

		$messageObject->archivalAgreement = new stdClass();

		foreach ($_SESSION['user']['entities'] as $entitie) {
			$entitie = $this->getEntitie($entitie['ENTITY_ID']);
			if ($entitie) {
				$messageObject->transferringAgency->identifier->value = $entitie->business_id;
				$messageObject->archivalAgency->identifier->value = $entitie->archival_agency;
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
			$messageArchiveUnit->dataObjectReference->dataObjectReferenceId = $dataObjectReferenceId;
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
			$content->sentDate = $object->doc_date;
			$content->receivedDate = $object->admission_date;
			$content->receivedDate = $object->admission_date;

			$content->addressee = [];
			$content->keyword = [];

			if ($object->exp_contact_id) {
				
				$contact = $this->getContact($object->exp_contact_id);
				$entitie = $this->getEntitie($object->destination);

				$content->keyword[] = $this->getKeyword($contact);
				$content->addressee[] = $this->getAddresse($entitie,"entitie");
			} else if ($object->dest_contact_id) {
				$contact = $this->getContact($object->dest_contact_id);
				$entitie = $this->getEntitie($object->destination);

				$content->addressee[] = $this->getAddresse($contact);
				$content->keyword[] = $this->getKeyword($entitie,"entitie");
			} else if ($object->exp_user_id) {
				$user = $this->getUserInformation($object->exp_user_id);
				$entitie = $this->getEntitie($object->initiator);
				//$entitie = $this->getEntitie($letterbox->destination);

				$content->keyword[] = $this->getKeyword($user);
				$content->addressee[] = $this->getAddresse($entitie,"entitie");
			}
			
			$content->source = $_SESSION['mail_nature'][$object->nature_id];

			$content->documentType = $object->type_label;
			$content->originatingAgencyArchiveIdentifier = $object->alt_identifier;
			$content->originatingSystemId = $object->res_id;
			$content->title = [];
			$content->title[] = $object->subject;
			$content->endDate = $object->process_limit_date;

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

		$docTypes = $this->getDocTypes($letterbox->type_id);

		$management->appraisalRule = new stdClass();
		$management->appraisalRule->rule = new stdClass();
		$management->appraisalRule->rule->value = $docTypes->retention_rule;
		$management->appraisalRule->finalAction = $docTypes->retention_final_disposition;
		
		return $management;
	}

	private function getBinaryDataObject($object, $isAttachment = false)
	{
		$docServers = $this->getDocServer($object->docserver_id);

		$binaryDataObject = new stdClass();

		if ($isAttachment) {
			$binaryDataObject->id = "attachment_".$object->res_id;
		} else {
			$binaryDataObject->id = $object->res_id;
		}
		
		$binaryDataObject->messageDigest = new stdClass();
		$binaryDataObject->messageDigest->value = $object->fingerprint;

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
	///////////////////
	/// Requete SQL ///
	///////////////////

	private function getUnitIdentifier($resId)
	{
		$queryParams = [];

		$queryParams[] = $resId;

		$query = "SELECT * FROM unit_identifier WHERE res_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$unitIdentifier = [];
		while ($res = $smtp->fetchObject()) {
			$unitIdentifier[] = $res;
		}

		return $unitIdentifier;
	}

	private function getCourrier($resId) 
	{
		$queryParams = [];

		$queryParams[] = $resId;

		$query = "SELECT * FROM res_view_letterbox WHERE res_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$letterbox = $smtp->fetchObject();

		return $letterbox;
	}

	private function getDocTypes($typeId)
	{
		$queryParams = [];

		$queryParams[] = $typeId;

		$query = "SELECT * FROM doctypes WHERE type_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$docTypes = $smtp->fetchObject();

		return $docTypes;
	}

	private function getUserInformation($userId) 
	{
		$queryParams = [];

		$queryParams[] = $userId;

		$query = "SELECT * FROM users WHERE user_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$user = $smtp->fetchObject();

		return $user;
	}

	private function getNotes($letterboxId) 
	{
		$queryParams = [];

		$queryParams[] = $letterboxId;

		$query = "SELECT * FROM notes WHERE identifier = ?";

		$smtp = $this->db->query($query,$queryParams);

		$notes = [];
		while ($res = $smtp->fetchObject()) {
			$notes[] = $res;
		}

		return $notes;
	}

	private function getEntitie($entityId)
	{
		$queryParams = [];

		$queryParams[] = $entityId;

		$query = "SELECT * FROM entities WHERE entity_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$entitie = $smtp->fetchObject();

		return $entitie;
	}

	private function getContact($contactId)
	{
		$queryParams = [];

		$queryParams[] = $contactId;

		$query = "SELECT * FROM contacts_v2 WHERE contact_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$contact = $smtp->fetchObject();

		return $contact;
	}

	private function getDocServer($docServerId)
	{
		$queryParams = [];

		$queryParams[] = $docServerId;

		$query = "SELECT * FROM docservers WHERE docserver_id = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		$docServers = $smtp->fetchObject();

		return $docServers;
	}

	private function getAttachments($resIdMaster)
	{
		$queryParams = [];

		$queryParams[] = $resIdMaster;

		$query = "SELECT * FROM res_attachments WHERE res_id_master = ?";

		$smtp = $this->db->query($query,$queryParams);
		
		while ($res = $smtp->fetchObject()) {
			$attachments[] = $res;
		}

		return $attachments;
	}

	private function insertMessage($messageObject) 
	{
		$queryParams = [];
		$messageId = uniqid();

		try {
			$query = ("INSERT INTO seda (
				message_id,
				schema,
				type,
				status,
				date,
				reference,
	            account_id ,
				sender_org_identifier,
				sender_org_name,
				recipient_org_identifier,
				recipient_org_name,
				archival_agreement_reference,
				reply_code,
				size,
				data,
				active,
				archived)
				VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

			$queryParams[] = $messageId; // Message Id
			$queryParams[] = "2.1"; //Schema
			$queryParams[] = "ArchiveTransfer"; // Type
			$queryParams[] = "sent"; // Status
			$queryParams[] = $messageObject->date; // Date
			$queryParams[] = $messageObject->messageIdentifier->value; // Reference
			$queryParams[] = $_SESSION['user']['UserId']; // Account Id
			$queryParams[] = $messageObject->transferringAgency->identifier->value; // Sender org identifier id
			$queryParams[] = ""; //SenderOrgNAme
			$queryParams[] = $messageObject->archivalAgency->identifier->value; // Recipient org identifier id
			$queryParams[] = ""; //RecipientOrgNAme
			$queryParams[] = $messageObject->archivalAgreement->value; // Archival agreement reference
			$queryParams[] = ""; //ReplyCode
			$queryParams[] = 0; // size
			$queryParams[] = json_encode($messageObject);//$messageObject; // Data
			$queryParams[] = 1; // active
			$queryParams[] = 0; // archived

			$res = $this->db->query($query,$queryParams);

			//var_dump($messageObject);
			foreach ($messageObject->dataObjectPackage->binaryDataObject as $binaryDataObject) {
				$this->insertUnitIdentifier($messageId, "res_letterbox", $binaryDataObject->id);
			}
		} catch (Exception $e) {
			var_dump($e);
			return false;
		}

		return true;
	}

	private function insertUnitIdentifier($messageId, $tableName, $resId) 
	{
		try {
			$query = ("INSERT INTO unit_identifier VALUES (?,?,?)");
			$queryParams = [];

			$queryParams[] = $messageId;
			$queryParams[] = $tableName;
			$queryParams[] = $resId;

			$res = $this->db->query($query,$queryParams);
		} catch (Exception $e) {
			return false;
		}
		
		return true;
	}

	private function deleteSeda($messageId)
	{
		$queryParams = [];

		$queryParams[] = $messageId;
		try {
			$query = "DELETE FROM seda WHERE message_id = ?";

			$smtp = $this->db->query($query,$queryParams);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	private function deleteUnitIdentifier($messageId)
	{
		$queryParams = [];

		$queryParams[] = $messageId;
		try {
			$query = "DELETE FROM unit_identifier WHERE message_id = ?";

			$smtp = $this->db->query($query,$queryParams);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
}