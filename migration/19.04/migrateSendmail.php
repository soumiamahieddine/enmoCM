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

//    $sendmails = \SrcCore\models\DatabaseModel::select([
//        'select'    => ['*'],
//        'table'     => ['sendmail'],
//        'order_by'  => ['creation_date']
//    ]);
//
//    foreach ($sendmails as $sendmail) {
//
//        $user = \User\models\UserModel::getByLogin(['login' => $sendmail['user_id'], 'select' => ['id']]);
//
//        \Email\models\EmailModel::create([
//            'userId'                => $user['id'],
//            'sender'                => json_encode($data['sender']),
//            'recipients'            => json_encode($data['recipients']),
//            'cc'                    => empty($data['cc']) ? '[]' : json_encode($data['cc']),
//            'cci'                   => empty($data['cci']) ? '[]' : json_encode($data['cci']),
//            'object'                => $data['object'],
//            'body'                  => $data['body'],
//            'document'              => empty($data['document']) ? null : json_encode($data['document']),
//            'isHtml'                => $data['isHtml'] ? 'true' : 'false',
//            'status'                => $data['status'] == 'DRAFT' ? 'DRAFT' : 'WAITING',
//            'messageExchangeId'     => $data['messageExchangeId']
//        ]);
//    }

}

printf($migrated . " custom(s) avec une configuration sendmail trouvé(s) et migré(s).\n");
