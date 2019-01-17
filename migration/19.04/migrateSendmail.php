<?php

require '../../vendor/autoload.php';

chdir('../..');

$migrated = 0;
$customs =  scandir('custom');
foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $xmlfile = null;
    $path = "custom/{$custom}/modules/sendmail/batch/config/config.xml";
    if (file_exists($path)) {
        $xmlfile = simplexml_load_file($path);
    }

    if ($xmlfile) {
        $pass = '';
        if (!empty((string)$xmlfile->MAILER->smtp_password)) {
            $pass = \SrcCore\models\PasswordModel::encrypt(['password' => (string)$xmlfile->MAILER->smtp_password]);
        }

        $data = [
            'type'     => (string)$xmlfile->MAILER->type,
            'host'     => (string)$xmlfile->MAILER->smtp_host,
            'port'     => (int)$xmlfile->MAILER->smtp_port,
            'user'     => (string)$xmlfile->MAILER->smtp_user,
            'password' => $pass,
            'auth'     => (string)$xmlfile->MAILER->smtp_auth == 'true' ? true : false,
            'from'     => (string)$xmlfile->MAILER->mailfrom,
            'secure'   => 'ssl',
            'charset'  => 'utf-8'
        ];
        $data = json_encode($data);
        \Configuration\models\ConfigurationModel::update(['set' => ['value' => $data], 'where' => ['service = ?'], 'data' => ['admin_email_server']]);

        $migrated++;
    }

    $sendmails = \SrcCore\models\DatabaseModel::select([
        'select'    => ['*'],
        'table'     => ['sendmail'],
        'order_by'  => ['creation_date']
    ]);

    foreach ($sendmails as $sendmail) {

        $user = \User\models\UserModel::getByLogin(['login' => $sendmail['user_id'], 'select' => ['id']]);
        $sender = explode(',', $sendmail['sender_email']);
        if (empty($sender[1])) {
            $sender = ['email' => $sender[0]];
        } else {
            $sender = ['email' => $sender[1], 'entityId' => $sender[0]];
        }
        $recipients = explode(',', $sendmail['to_list']);
        $cc = explode(',', $sendmail['cc_list']);
        $cc = empty($cc[0]) ? [] : $cc;
        $cci = explode(',', $sendmail['cci_list']);
        $cci = empty($cci[0]) ? [] : $cci;

        $document = [
            'id'        => $sendmail['res_id'],
            'isLinked'  => $sendmail['is_res_master_attached'] == 'Y',
            'original'  => false,
        ];

        $attachments = [];

        $rawAttachments = explode(',', $sendmail['res_attachment_id_list']);
        if (!empty($rawAttachments[0])) {
            foreach ($rawAttachments as $rawAttachment) {
                $id = $rawAttachment;
                $original = true;
                if (strpos($rawAttachment, '#') !== false) {
                    $id = substr($rawAttachment, 0, strpos($rawAttachment, '#'));
                    $original = false;
                }
                $attachments[] = [
                    'id'        => (int)$id,
                    'isVersion' => false,
                    'original'  => $original,
                ];
            }
        }

        $rawVersionAttachments = explode(',', $sendmail['res_version_att_id_list']);
        if (!empty($rawVersionAttachments[0])) {
            foreach ($rawVersionAttachments as $rawAttachment) {
                $id = $rawAttachment;
                $original = true;
                if (strpos($rawAttachment, '#') !== false) {
                    $id = substr($rawAttachment, 0, strpos($rawAttachment, '#'));
                    $original = false;
                }
                $attachments[] = [
                    'id'        => (int)$id,
                    'isVersion' => true,
                    'original'  => $original,
                ];
            }
        }
        if (!empty($attachments)) {
            $document['attachments'] = $attachments;
        }
        $notes = explode(',', $sendmail['note_id_list']);
        if (!empty($notes[0])) {
            $document['notes'] = [];
            foreach ($notes as $note) {
                $document['notes'][] = (int)$note;
            }
        }

        if ($sendmail['email_status'] == 'S') {
            $status = 'SENT';
        } elseif ($sendmail['email_status'] == 'D') {
            $status = 'DRAFT';
        } elseif ($sendmail['email_status'] == 'W') {
            $status = 'WAITING';
        } else {
            $status = 'ERROR';
        }

        \SrcCore\models\DatabaseModel::insert([
            'table'         => 'emails',
            'columnsValues' => [
                'user_id'                   => $user['id'],
                'sender'                    => json_encode($sender),
                'recipients'                => json_encode($recipients),
                'cc'                        => empty($cc) ? '[]' : json_encode($cc),
                'cci'                       => empty($cci) ? '[]' : json_encode($cci),
                'object'                    => empty($sendmail['email_object']) ? null : $sendmail['email_object'],
                'body'                      => empty($sendmail['email_body']) ? null : $sendmail['email_body'],
                'document'                  => empty($document) ? null : json_encode($document),
                'is_html'                   => $sendmail['is_html'] == 'Y' ? 'true' : 'false',
                'status'                    => $status,
                'message_exchange_id'       => empty($sendmail['message_exchange_id']) ? null : $sendmail['message_exchange_id'],
                'creation_date'             => $sendmail['creation_date'],
                'send_date'                 => empty($sendmail['send_date']) ? null : $sendmail['send_date']
            ]
        ]);
    }

    printf(count($sendmails) . " email(s) migré(s) du custom {$custom} vers la nouvelle table.\n");
}

printf($migrated . " custom(s) avec une configuration sendmail trouvé(s) et migré(s).\n");
