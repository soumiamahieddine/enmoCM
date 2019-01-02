<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Email Controller
 * @author dev@maarch.org
 */

namespace Email\controllers;

use Email\models\EmailModel;
use Group\models\ServiceModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class EmailController
{
    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'sendmail', 'userId' => $GLOBALS['userId'], 'location' => 'sendmail', 'type' => 'use'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::intVal()->notEmpty()->validate($data['resId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['sender']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['to']);
        $check = $check && Validator::arrayType()->validate($data['cc']);
        $check = $check && Validator::arrayType()->validate($data['cci']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['object']);
        $check = $check && Validator::stringType()->validate($data['body']);
        $check = $check && Validator::boolType()->validate($data['documentLinked']);
        $check = $check && Validator::boolType()->validate($data['isHtml']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }


        $user = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $id = EmailModel::create([
            'resId'                 => $data['resId'],
            'userId'                => $user['id'],
            'sender'                => $data['sender'],
            'to'                    => json_encode($data['to']),
            'cc'                    => empty($data['cc']) ? '[]' : json_encode($data['cc']),
            'cci'                   => empty($data['cci']) ? '[]' : json_encode($data['cci']),
            'object'                => $data['object'],
            'body'                  => $data['body'],
            'documentLinked'        => $data['documentLinked'] ? 'true' : 'false',
            'attachmentsId'         => empty($data['attachmentsId']) ? '[]' : json_encode($data['attachmentsId']),
            'versionAttachmentsId'  => empty($data['versionAttachmentsId']) ? '[]' : json_encode($data['versionAttachmentsId']),
            'notesId'               => empty($data['notesId']) ? '[]' : json_encode($data['notesId']),
            'isHtml'                => $data['isHtml'] ? 'true' : 'false',
            'messageExchangeId'     => $data['messageExchangeId'],
        ]);


        return $response->withJson(['success' => 'success']);
    }

    public static function sendEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['emailId']);
        ValidatorModel::intVal($args, ['emailId']);

        $email = EmailModel::getById(['id' => $args['emailId']]);

//        $mailerConfiguration = \Configuration\models\ConfigurationModel::getByName(['name' => 'mailer', 'select' => ['value']]);
//        $mailerConfiguration = (array)json_decode($mailerConfiguration['value']);
//        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
//        $phpmailer->isSMTP();
//        $phpmailer->Host = $mailerConfiguration['host'];
//        $phpmailer->Port = $mailerConfiguration['port'];
//        $phpmailer->SMTPAuth = $mailerConfiguration['auth'];
//        $phpmailer->SMTPSecure = $mailerConfiguration['secure'];
//        $phpmailer->Username = $mailerConfiguration['user'];
//        $phpmailer->Password = $mailerConfiguration['password'];
//        $phpmailer->CharSet = $mailerConfiguration['charset'];
//
//        if (!empty($mailerConfiguration['from'])) {
//            $phpmailer->setFrom($mailerConfiguration['from'], 'Mailer');
//        }
//
//        $phpmailer->Subject = $email->email_object . 'phpmailer';
//
//        $phpmailer->isHTML(true);
//        $phpmailer->Body = $body;
//
//        $phpmailer->addAttachment($resFile['file_path']);
//
//        foreach ($to as $value) {
//            $phpmailer->addAddress($value);
//        }
//        $mailerSent = $phpmailer->send();
//        if (!$mailerSent) {
//            $GLOBALS['logger']->write("SENDING EMAIL ERROR ! (" . $phpmailer->ErrorInfo. ")", 'ERROR');
//        }

    }
}
