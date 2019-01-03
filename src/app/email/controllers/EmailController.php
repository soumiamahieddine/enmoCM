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

use Configuration\models\ConfigurationModel;
use Email\models\EmailModel;
use Group\models\ServiceModel;
use PHPMailer\PHPMailer\PHPMailer;
use Resource\controllers\ResController;
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
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['recipients']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['object']);
        $check = $check && Validator::boolType()->validate($data['documentLinked']);
        $check = $check && Validator::boolType()->validate($data['isHtml']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!ResController::hasRightByResId(['resId' => $data['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $user = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $id = EmailModel::create([
            'resId'                 => $data['resId'],
            'userId'                => $user['id'],
            'sender'                => $data['sender'],
            'recipients'            => json_encode($data['recipients']),
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

        $isSent = EmailController::sendEmail(['emailId' => $id]);

        if (!empty($isSent['success'])) {
            EmailModel::update(['set' => ['status' => 'S'], 'where' => ['id = ?'], 'data' => [$id]]);
        } else {
            EmailModel::update(['set' => ['status' => 'E'], 'where' => ['id = ?'], 'data' => [$id]]);
        }

        return $response->withJson(['success' => 'success']);
    }

    public static function sendEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['emailId']);
        ValidatorModel::intVal($args, ['emailId']);

        $email = EmailModel::getById(['id' => $args['emailId']]);

        $configuration = ConfigurationModel::getByService(['service' => 'admin_email_server', 'select' => ['value']]);
        $configuration = (array)json_decode($configuration['value']);

        $phpmailer = new PHPMailer();

        if ($configuration['type'] == 'smtp') {
            $phpmailer->isSMTP();
            $phpmailer->Host = $configuration['host'];
            $phpmailer->Port = $configuration['port'];
            $phpmailer->SMTPSecure = $configuration['secure'];
            $phpmailer->SMTPAuth = $configuration['auth'];
            if ($configuration['auth']) {
                $phpmailer->Username = $configuration['user'];
                $phpmailer->Password = $configuration['password'];
            }
            if (!empty($configuration['from'])) {
                $phpmailer->setFrom($configuration['from'], 'Mailer'); //TODO
            }
        }
        $phpmailer->CharSet = $configuration['charset'];


        $phpmailer->Subject = $email['object'];

        $phpmailer->isHTML(true); //TODO
        $phpmailer->Body = $email['body']; // TODO

//        $phpmailer->addAttachment($resFile['file_path']); //TODO

        $recipients = json_decode($email['recipients']);
        foreach ($recipients as $recipient) {
            $phpmailer->addAddress($recipient);
        }

        $isSent = $phpmailer->send();
        if (!$isSent) {
            //TODO
            return ['errors' => 'errors'];
        }

        return ['success' => 'success'];
    }
}
