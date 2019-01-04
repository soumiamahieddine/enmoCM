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
use Entity\models\EntityModel;
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
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['sender']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['sender']['email']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['recipients']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['object']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['document']);
        $check = $check && Validator::boolType()->validate($data['document']['isLinked']);
        $check = $check && Validator::boolType()->validate($data['document']['original']);
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
            'sender'                => json_encode($data['sender']),
            'recipients'            => json_encode($data['recipients']),
            'cc'                    => empty($data['cc']) ? '[]' : json_encode($data['cc']),
            'cci'                   => empty($data['cci']) ? '[]' : json_encode($data['cci']),
            'object'                => $data['object'],
            'body'                  => $data['body'],
            'document'              => json_encode($data['document']),
            'attachments'           => empty($data['attachments']) ? '[]' : json_encode($data['attachments']),
            'notes'                 => empty($data['notes']) ? '[]' : json_encode($data['notes']),
            'isHtml'                => $data['isHtml'] ? 'true' : 'false',
            'messageExchangeId'     => $data['messageExchangeId'],
        ]);

        $isSent = EmailController::sendEmail(['emailId' => $id, 'userId' => $user['id']]);

        if (!empty($isSent['success'])) {
            EmailModel::update(['set' => ['status' => 'S'], 'where' => ['id = ?'], 'data' => [$id]]);
        } else {
            EmailModel::update(['set' => ['status' => 'E'], 'where' => ['id = ?'], 'data' => [$id]]);
        }

        return $response->withJson(['success' => 'success']);
    }

    public static function sendEmail(array $args) //TODO LOGS
    {
        ValidatorModel::notEmpty($args, ['emailId', 'userId']);
        ValidatorModel::intVal($args, ['emailId', 'userId']);

        $email = EmailModel::getById(['id' => $args['emailId']]);
        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['firstname', 'lastname']]);

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

            $email['sender'] = (array)json_decode($email['sender']);
            $emailFrom = empty($configuration['from']) ? $email['sender']['email'] : $configuration['from'];
            if (empty($email['sender']['entityId'])) {
                $phpmailer->setFrom($emailFrom, "{$user['firstname']} {$user['lastname']}");
            } else {
                $entity = EntityModel::getById(['id' => $email['sender']['entityId'], 'select' => ['short_label']]);
                $phpmailer->setFrom($emailFrom, $entity['short_label']);
            }
            $phpmailer->addReplyTo($email['sender']['email']);
        }
        $phpmailer->CharSet = $configuration['charset'];

        $email['recipients'] = json_decode($email['recipients']);
        foreach ($email['recipients'] as $recipient) {
            $phpmailer->addAddress($recipient);
        }
        $email['cc'] = json_decode($email['cc']);
        foreach ($email['cc'] as $recipient) {
            $phpmailer->addCC($recipient);
        }
        $email['cci'] = json_decode($email['cci']);
        foreach ($email['cci'] as $recipient) {
            $phpmailer->addBCC($recipient);
        }

        $phpmailer->Subject = $email['object'];
        if ($email['is_html']) {
            $phpmailer->isHTML(true);
        }
        $phpmailer->Body = $email['body']; // TODO IMAGE

        //TODO M2M

        $email['document'] = (array)json_decode($email['document']);
        if ($email['document']['isLinked']) {

            $encodedDocument = ResController::getEncodedDocument(['resId' => $email['res_id'], 'original' => $email['document']['original']]);
            if (empty($encodedDocument['errors'])) {
                $phpmailer->addStringAttachment(base64_decode($encodedDocument['encodedDocument']), $encodedDocument['fileName']);
            }
        }

//        $phpmailer->addAttachment($resFile['file_path']); //TODO


        $isSent = $phpmailer->send();
        if (!$isSent) {
            //TODO
            return ['errors' => 'errors'];
        }

        return ['success' => 'success'];
    }
}
