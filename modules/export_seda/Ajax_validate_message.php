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

require_once 'vendor/autoload.php';
//require_once 'core/Controllers/ResController.php';
require_once 'apps/maarch_entreprise/Models/ContactsModel.php';
require_once __DIR__.'/RequestSeda.php';
require_once __DIR__.'/ArchiveTransfer.php';

$status = 0;
$error = $content = '';
if ($_REQUEST['reference']) {
    $validateMessage = new ValidateMessage();
    $res = $validateMessage->validate($_REQUEST['reference']);
    $status = $res['status'];
    if ($status != 0) {
        $error = $res['error'];
    } else {
        $content = $res['content'];
    }
} else {
    $status = 1;
}

echo "{status : " . $status . ", content : '" . addslashes($content) . "', error : '" . addslashes($error) . "'}";
exit ();

class ValidateMessage
{
    private $db;
    private $res;
    private $deleteData;

    public function __construct()
    {
        $this->db = new RequestSeda();
        $this->res = [];
        $this->res['status'] = 0;
        $this->res['content'] = "";

        $config = parse_ini_file(__DIR__.'/config.ini');
        $this->deleteData = $config['deleteData'];
    }

    public function validate($reference)
    {

        try {
            $message = $this->db->getMessageByReference($reference);
            $listResId = $this->db->getUnitIdentifierByMessageId($message->message_id);

            if ($this->deleteData) {
                for ($i=0; $i < count($listResId); $i++) {
                    $this->purgeResource($listResId[$i]->res_id);

                    $courrier = $this->db->getCourrier($listResId[$i]->res_id);
                    $this->purgeContact($courrier->contact_id);
                }
                $this->purgeMessage($message->message_id);
            }

            for ($i=0; $i < count($listResId); $i++) {
                $this->db->updateStatusLetterbox($listResId[$i]->res_id,'SENT_ARC');
            }


        } catch (Exception $e) {
            $this->res['status'] = 1;
            $this->res['error'] = "Données non supprimées";
        }

        return $this->res;
    }

    private function purgeMessage($messageId)
    {
        $this->db->deleteSeda($messageId);
        $this->db->deleteUnitIdentifier($messageId);
    }

    private function purgeResource($resId)
    {
        $action = new \Core\Controllers\ResController();
        $data = [];

        array_push($data, array(
                'column' => 'status',
                'value' => 'DEL',
                'type' => 'string'
                ));

        $aArgs = [
            'table' => 'res_letterbox',
            'res_id'=> $resId,
            'data'  => $data
            ];

        $response = $action->updateResource($aArgs);

        return $response;
    }

    private function purgeContact($contactId)
    {
        $contacts = new \ContactsModel();
        $contactDetails = $contacts->purgeContact([
            'id'=>$contactId
            ]);
    }
}
