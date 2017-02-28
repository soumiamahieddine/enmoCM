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

class ArchiveTransfertReply {

	public function __construct() 
	{
		$this->db = new Database();
	}

	public function receive($fileName) {

		$xml = simplexml_load_file($fileName);

		$message = new stdClass();
		$message->reference = $xml->MessageRequestIdentifier;
		$message->status = "receive";
		$message->replyCode = $xml->DataObjectPackage->ReplyCode;
		$message->operationDate = (string) $xml->DataObjectPackage->GrantDate;
		$message->replyReference = (string) $xml->MessageIdentifier;
		$message->comment = $xml->comment;

		$this->updateMessage($message);
	}

	private function updateMessage($message) 
	{
		$queryParams = [];

		try {
			$query = ("UPDATE seda SET status = ?, reply_code = ?, operation_date = ?, reply_reference = ?  WHERE reference = ?");

			$queryParams[] = $message->status; 
			$queryParams[] = $message->replyCode;
			$queryParams[] = $message->operationDate; 
			$queryParams[] = $message->replyReference; 
			$queryParams[] = $message->reference;

			$this->db->query($query,$queryParams);

		} catch (Exception $e) {
			var_dump($e);
		}
	}
}