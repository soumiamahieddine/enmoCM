<?php

namespace Visa\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once 'modules/basket/class/class_modules_tools.php';
require_once 'apps/maarch_entreprise/Models/ResModel.php';


class VisaController {

	public function getSignatureBook(RequestInterface $request, ResponseInterface $response, $aArgs) {

		$resId = $aArgs['resId'];

		$incomingMail = \ResModel::get([
			'resId' => $resId,
			'select'    => ['subject']
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

		$attachments = \ResModel::getAvailableLinkedAttachmentsNotIn([
			'resIdMaster' => $resId,
			'notIn' 	  => ['incoming_mail_attachment'],
			'select' 	  => ['res_id', 'res_id_version', 'title', 'identifier', 'attachment_type', 'status', 'typist', 'path', 'filename']
		]);

		foreach ($attachments as $key => $value) {
			if ($value['attachment_type'] == 'converted_pdf') {
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
					$viewerId = $tmpValue['res_id'];
					unset($attachments[$tmpKey]);
				}
			}

			$attachments[$key]['thumbnailLink'] = "index.php?page=doc_thumb&module=thumbnails&res_id={$realId}&coll_id={$collId}&display=true&advanced=true";
			$attachments[$key]['viewerLink'] = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master={$resId}&id={$viewerId}";

			unset($attachments[$key]['res_id']);
			unset($attachments[$key]['res_id_version']);
			unset($attachments[$key]['path']);
			unset($attachments[$key]['filename']);
		}

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


		$datas = [];
		$datas['actions'] = $actionsData;
		$datas['attachments'] = $attachments;
		$datas['documents'] = $documents;
		$datas['currentAction'] = $_SESSION['current_basket']['default_action']; //TODO Aller chercher l'id de la basket sans passer par la session
		$datas['linkNotes'] = 'index.php?display=true&module=notes&page=notes&identifier=' .$resId. '&origin=document&coll_id=letterbox_coll&load&size=medium';

		return $response->withJson($datas);
	}
}