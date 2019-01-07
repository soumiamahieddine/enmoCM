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

use Attachment\controllers\AttachmentController;
use Attachment\models\AttachmentModel;
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
        $check = Validator::arrayType()->notEmpty()->validate($data['sender']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['sender']['email']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['recipients']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['object']);
        $check = $check && Validator::boolType()->validate($data['isHtml']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['status']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!empty($data['document'])) {
            $check = Validator::intVal()->notEmpty()->validate($data['document']['id']);
            $check = $check && Validator::boolType()->validate($data['document']['isLinked']);
            $check = $check && Validator::boolType()->validate($data['document']['original']);
            if (!$check) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad document data']);
            }
            if (!ResController::hasRightByResId(['resId' => $data['document']['id'], 'userId' => $GLOBALS['userId']])) {
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }
            if (!empty($data['document']['attachments'])) {
                if (!is_array($data['document']['attachments'])) {
                    return $response->withStatus(400)->withJson(['errors' => 'Document[attachments] is not an array']);
                }
                foreach ($data['document']['attachments'] as $attachment) {
                    $check = Validator::intVal()->notEmpty()->validate($attachment['id']);
                    $check = $check && Validator::boolType()->validate($attachment['isVersion']);
                    $check = $check && Validator::boolType()->validate($attachment['original']);
                    if (!$check) {
                        return $response->withStatus(400)->withJson(['errors' => 'Bad document[attachments] data']);
                    }
                    $checkAttachment = AttachmentModel::getById(['id' => $attachment['id'], 'isVersion' => $attachment['isVersion'], 'select' => ['res_id_master']]);
                    if (empty($checkAttachment) || $checkAttachment['res_id_master'] != $data['document']['id']) {
                        return $response->withStatus(400)->withJson(['errors' => 'Bad document[attachments][id]']);
                    }
                }
            }
        }

        $user = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $id = EmailModel::create([
            'userId'                => $user['id'],
            'sender'                => json_encode($data['sender']),
            'recipients'            => json_encode($data['recipients']),
            'cc'                    => empty($data['cc']) ? '[]' : json_encode($data['cc']),
            'cci'                   => empty($data['cci']) ? '[]' : json_encode($data['cci']),
            'object'                => $data['object'],
            'body'                  => $data['body'],
            'document'              => empty($data['document']) ? null : json_encode($data['document']),
            'isHtml'                => $data['isHtml'] ? 'true' : 'false',
            'status'                => $data['status'] == 'DRAFT' ? 'DRAFT' : 'WAITING',
            'messageExchangeId'     => $data['messageExchangeId']
        ]);

        if ($data['status'] != 'DRAFT') {
            $isSent = EmailController::sendEmail(['emailId' => $id, 'userId' => $user['id']]);

            if (!empty($isSent['success'])) {
                EmailModel::update(['set' => ['status' => 'SENT', 'send_date' => 'CURRENT_TIMESTAMP'], 'where' => ['id = ?'], 'data' => [$id]]);
            } else {
                EmailModel::update(['set' => ['status' => 'ERROR'], 'where' => ['id = ?'], 'data' => [$id]]);
            }
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

        if (!empty($email['document'])) {
            $email['document'] = (array)json_decode($email['document']);
            if ($email['document']['isLinked']) {

                $encodedDocument = ResController::getEncodedDocument(['resId' => $email['document']['id'], 'original' => $email['document']['original']]);
                if (empty($encodedDocument['errors'])) {
                    $phpmailer->addStringAttachment(base64_decode($encodedDocument['encodedDocument']), $encodedDocument['fileName']);
                }
            }
            if (!empty($email['document']['attachments'])) {
                $email['document']['attachments'] = (array)$email['document']['attachments'];
                foreach ($email['document']['attachments'] as $attachment) {
                    $attachment = (array)$attachment;
                    $encodedDocument = AttachmentController::getEncodedDocument(['id' => $attachment['id'], 'isVersion' => $attachment['isVersion'], 'original' => $attachment['original']]);
                    if (empty($encodedDocument['errors'])) {
                        $phpmailer->addStringAttachment(base64_decode($encodedDocument['encodedDocument']), $encodedDocument['fileName']);
                    }
                }
            }
            //TODO NOTES
        }


        $isSent = $phpmailer->send();
        if (!$isSent) {
            //TODO
            return ['errors' => 'errors'];
        }

        return ['success' => 'success'];
    }
}
