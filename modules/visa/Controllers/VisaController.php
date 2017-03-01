<?php

namespace Visa\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once 'modules/basket/class/class_modules_tools.php';
require_once 'apps/maarch_entreprise/Models/ResModel.php';
require_once 'apps/maarch_entreprise/Models/HistoryModel.php';
require_once 'apps/maarch_entreprise/Models/ContactsModel.php';
require_once 'apps/maarch_entreprise/Models/UsersModel.php';
require_once 'modules/basket/Models/BasketsModel.php';


class VisaController {

	public function getSignatureBook(RequestInterface $request, ResponseInterface $response, $aArgs) {

		$resId = $aArgs['resId'];
		$basketId = $aArgs['basketId'];

		$incomingMail = \ResModel::get([
			'resId'  => $resId,
			'select' => ['res_id', 'subject', 'alt_identifier']
		]);

		if (empty($incomingMail[0])) {
			return $response->withJson(['Error' => 'No Document Found']);
		}

		$basket = new \basket();
		$actions = $basket->get_actions_from_current_basket($resId, 'letterbox_coll', 'PAGE_USE', false);

		$actionsData = [];
		$actionsData[] = ['value' => '', 'label' => _CHOOSE_ACTION];
		foreach($actions as $value) {
			$actionsData[] = ['value' => $value['VALUE'], 'label' => $value['LABEL']];
		}

		if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/entreprise.xml')) {
			$path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/entreprise.xml';
		} else {
			$path = 'apps/maarch_entreprise/xml/entreprise.xml';
		}
		$xmlfile = simplexml_load_file($path);
		$attachmentTypes = [];
		$attachmentTypesXML = $xmlfile->attachment_types;
		if (count($attachmentTypesXML) > 0) {
			foreach ($attachmentTypesXML->type as $value) {
				$label = defined((string) $value->label) ? constant((string) $value->label) : (string) $value->label;
				$attachmentTypes[(string) $value->id] = ['label' => $label, 'icon' => (string) $value['icon']];
			}
		}

		$attachments = \ResModel::getAvailableLinkedAttachmentsNotIn([
			'resIdMaster' => $resId,
			'notIn' 	  => ['incoming_mail_attachment', 'print_folder'],
			'select' 	  => [
				'res_id', 'res_id_version', 'title', 'identifier', 'attachment_type',
				'status', 'typist', 'path', 'filename', 'updated_by', 'creation_date',
				'validation_date', 'format', 'relation', 'dest_user', 'dest_contact_id',
				'dest_address_id', 'origin'
			]
		]);

		foreach ($attachments as $key => $value) {
			if ($value['attachment_type'] == 'converted_pdf' || ($value['attachment_type'] == 'signed_response' && !empty($value['origin']))) {
				continue;
			}

			$collId = '';
			$realId = 0;
			if ($value['res_id'] == 0) {
				$collId = 'version_attachments_coll';
				$realId = $value['res_id_version'];
			} elseif ($value['res_id_version'] == 0) {
				$collId = 'attachments_coll';
				$realId = $value['res_id'];
			}

			$viewerId = $realId;
			$pathToFind = $value['path'] . str_replace(strrchr($value['filename'], '.'), '.pdf', $value['filename']);
			foreach ($attachments as $tmpKey => $tmpValue) {
				if ($tmpValue['attachment_type'] == 'converted_pdf' && ($tmpValue['path'] . $tmpValue['filename'] == $pathToFind)) {
					if ($value['status'] != 'SIGN') {
						$viewerId = $tmpValue['res_id'];
					}
					unset($attachments[$tmpKey]);
				}
				if ($value['status'] == 'SIGN' && $tmpValue['attachment_type'] == 'signed_response' && !empty($tmpValue['origin'])) {
					$signDaddy = explode(',', $tmpValue['origin']);
					if (($signDaddy[0] == $value['res_id'] && $signDaddy[1] == "res_attachments")
						|| ($signDaddy[0] == $value['res_id_version'] && $signDaddy[1] == "res_version_attachments"))
					{
						$viewerId = $tmpValue['res_id'];
						unset($attachments[$tmpKey]);
					}
				}
			}

			if (!empty($value['dest_user'])) {
				$attachments[$key]['destUser'] = \UsersModel::getLabelledUserById(['id' => $value['dest_user']]);
			} elseif (!empty($value['dest_contact_id']) && !empty($value['dest_address_id'])) {
				$attachments[$key]['destUser'] = \ContactsModel::getLabelledContactWithAddress(['contactId' => $value['dest_contact_id'], 'addressId' => $value['dest_address_id']]);
			}
			if (!empty($value['updated_by'])) {
				$attachments[$key]['updated_by'] = \UsersModel::getLabelledUserById(['id' => $value['updated_by']]);
			}
			if (!empty($value['typist'])) {
				$attachments[$key]['typist'] = \UsersModel::getLabelledUserById(['id' => $value['typist']]);
			}

			$attachments[$key]['truncateTitle'] = ((strlen($value['title']) > 20) ? (substr($value['title'], 0, 20) . '...') : $value['title']);
			$attachments[$key]['attachment_type'] = $attachmentTypes[$value['attachment_type']]['label'];
			$attachments[$key]['icon'] = $attachmentTypes[$value['attachment_type']]['icon'];

			$attachments[$key]['thumbnailLink'] = "index.php?page=doc_thumb&module=thumbnails&res_id={$realId}&coll_id={$collId}&display=true&advanced=true";
			$attachments[$key]['viewerLink'] = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master={$resId}&id={$viewerId}";

			unset($attachments[$key]['path'], $attachments[$key]['filename'], $attachments[$key]['dest_user'],
				$attachments[$key]['dest_contact_id'], $attachments[$key]['dest_address_id']);
		}

		$attachments = array_values($attachments);

		$incomingMailAttachments = \ResModel::getAvailableLinkedAttachmentsIn([
			'resIdMaster' => $resId,
			'in' 	      => ['incoming_mail_attachment'],
			'select' 	  => ['res_id', 'title']
		]);

		$documents = [
			[
				'title'         => $incomingMail[0]['subject'],
				'truncateTitle' => ((strlen($incomingMail[0]['subject']) > 10) ? (substr($incomingMail[0]['subject'], 0, 10) . '...') : $incomingMail[0]['subject']),
				'viewerLink'    => "index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id={$resId}&collid=letterbox_coll",
				'thumbnailLink' => "index.php?page=doc_thumb&module=thumbnails&res_id={$resId}&coll_id=letterbox_coll&display=true&advanced=true"
			]
		];
		foreach ($incomingMailAttachments as $value) {
			$documents[] = [
				'title'         => $value['title'],
				'truncateTitle' => ((strlen($value['title']) > 10) ? (substr($value['title'], 0, 10) . '...') : $value['title']),
				'viewerLink'    => "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master={$resId}&id={$value['res_id']}",
				'thumbnailLink' => "index.php?page=doc_thumb&module=thumbnails&res_id={$value['res_id']}&coll_id=attachments_coll&display=true&advanced=true"
			];
		}

		$history = \HistoryModel::getByIdForActions([
			'id'      => $resId,
			'select'  => ['event_date', 'info', 'firstname', 'lastname'],
			'orderBy' => ['event_date DESC']
		]);

		$resList = \BasketsModel::getResListById([
			'basketId' => $basketId,
			'select'  => ['res_id', 'alt_identifier', 'subject', 'creation_date', 'process_limit_date', 'priority', 'contact_id', 'address_id', 'user_lastname', 'user_firstname']
		]);

		foreach ($resList as $key => $value) {
			if (!empty($value['contact_id'])) {
				$resList[$key]['sender'] = \ContactsModel::getLabelledContactWithAddress(['contactId' => $value['contact_id'], 'addressId' => $value['address_id']]);
			} else {
				$resList[$key]['sender'] = $value['user_firstname'] . ' ' . $value['user_lastname'];
			}
			$attachmentsInResList = \ResModel::getAvailableLinkedAttachmentsNotIn([
				'resIdMaster' => $value['res_id'],
				'notIn' 	  => ['incoming_mail_attachment', 'print_folder'],
				'select' 	  => ['status']
			]);
			$allSigned = true;
			foreach ($attachmentsInResList as $value2) {
				if ($value2['status'] == 'TRA' || $value2['status'] == 'A_TRA') {
					$allSigned = false;
				}
			}
			$resList[$key]['allSigned'] = $allSigned;
			$resList[$key]['priorityColor'] = $_SESSION['mail_priorities_color'][$value['priority']]; //TODO No Session
			unset($resList[$key]['priority'], $resList[$key]['contact_id'], $resList[$key]['address_id'], $resList[$key]['user_lastname'], $resList[$key]['user_firstname']);
		}

		$actionLabel = \BasketsModel::getActionByActionId(['actionId' => $_SESSION['current_basket']['default_action'], 'select' => ['label_action']])['label_action'] . ' n°';
		$actionLabel .= (_ID_TO_DISPLAY == 'res_id' ? $incomingMail[0]['res_id'] : $incomingMail[0]['alt_identifier']);
		$actionLabel .= ' : ' . $incomingMail[0]['subject'];
		$currentAction = [
			'id' => $_SESSION['current_basket']['default_action'], //TODO No Session
			'actionLabel' => $actionLabel
		];

		$datas = [];
		$datas['actions'] = $actionsData;
		$datas['attachments'] = $attachments;
		$datas['documents'] = $documents;
		$datas['currentAction'] = $currentAction;
		$datas['linkNotes'] = 'index.php?display=true&module=notes&page=notes&identifier=' .$resId. '&origin=document&coll_id=letterbox_coll&load&size=medium';
		$datas['histories'] = $history;
		$datas['resList'] = $resList;

		return $response->withJson($datas);
	}

	public function unsignFile(RequestInterface $request, ResponseInterface $response, $aArgs) {

		$resId = $aArgs['resId'];
		$collId = $aArgs['collId'];

		$bReturnSnd = false;
		$bReturnFirst = \ResModel::put(['collId' => $collId, 'set' => ['status' => 'A_TRA'], 'where' => ['res_id = ?'], 'data' => [$resId]]);
		if ($bReturnFirst) {
			$bReturnSnd = \ResModel::put(['collId' => $collId, 'set' => ['status' => 'DEL'], 'where' => ['origin = ?', 'status != ?'], 'data' => [$resId . ',' .$collId, 'DEL']]);
		}

		if ($bReturnFirst && $bReturnSnd) {
			return $response->withJson(['status' => 'OK']);
		} else {
			return $response->withJson(['status' => 'KO']);
		}
	}

}