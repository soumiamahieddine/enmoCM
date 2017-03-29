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

class TransferToSAE
{
	protected $token;
	protected $urlSAE;

	public function __construct()
	{
		$this->token = file_get_contents(__DIR__.DIRECTORY_SEPARATOR."token.txt");
		$this->urlSAE = "http://192.168.21.18/maarchrmap";
	}

	public function transfer($reference)
	{
		
		$messageDirectory = __DIR__.DIRECTORY_SEPARATOR.'seda2'.DIRECTORY_SEPARATOR.$reference;
		$messageFile = $reference.".xml";

		$files = scandir($messageDirectory);
		$attachments = [];
		foreach ($files as $file) {
			if ($file != $messageFile && $file != ".." && $file != ".") {
				$attachments[] = $file;
			}
		}

		$post = [
			'messageFile' => $messageDirectory.DIRECTORY_SEPARATOR.$reference.".xml",
			'attachments' => $attachments
		];

		$header = [
			'cookie' => 'LAABS-AUTH='.urlencode($this->token),
			'accept'	=> 'application/json'
		];

		$ch = curl_init($this->urlSAE."/medona/Archivetransfer");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$response = curl_exec($ch);

		curl_close($ch);

/*php cli.php CREATE medona/Archivetransfer messageFile="$LAABS_PATH/data/maarchRM/samples/seda/ArchiveTransfer_Actes_04/ArchiveTransfer_Actes_04.xml" attachments="$LAABS_PATH/data/maarchRM/samples/seda/ArchiveTransfer_Actes_04" -tokenfile:"$SCRIPT_PATH/0-token.txt" -accept:"application/json"*/
		return $respone;
	}
}