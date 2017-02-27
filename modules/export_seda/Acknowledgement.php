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

class Acknowledgement {

	public function __construct()
	{
	}

	public function send($fileName, $comments = "")
	{
		$xml = simplexml_load_file($fileName);
		$messageObject = new stdClass();

		if ($comments) {
			$messageObject->comment = [];
			if (is_array($comments)) {
				foreach ($comments as $comment) {
					$messageObject->comment[] = $comment;
				}
			} else {
				$messageObject->comment[] = $comments;
			}
		}
		
		$messageIdentifier = (string) $xml->MessageIdentifier;
		$messageObject->date = date('Y-m-d h:i:s');
		$messageObject->messageIdentifier =  $messageIdentifier . "_Acknowledgement";
		//$messageObject->signature =  "";
		$messageObject->messageReceivedIdentifier = $messageIdentifier;
		$messageObject->sender = $xml->ArchivalAgency->Identifier;
		$messageObject->receiver = $xml->TransferringAgency->Identifier;

		$this->sendXml($messageObject);
	}

	public function sendXml($messageObject)
	{
		$DOMTemplate = new DOMDocument();
		$DOMTemplate->load(__DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'Acknowledgement.xml');
		$DOMTemplateProcessor = new DOMTemplateProcessor($DOMTemplate);
		$DOMTemplateProcessor->setSource('Acknowledgement', $messageObject);
		$DOMTemplateProcessor->merge();
		$DOMTemplateProcessor->removeEmptyNodes();

        file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$messageObject->messageReceivedIdentifier.'_Acknowledgement.xml', $DOMTemplate->saveXML());

		return $xml;
	}
}