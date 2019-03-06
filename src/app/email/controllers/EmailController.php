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
use History\controllers\HistoryController;
use History\models\HistoryModel;
use Note\controllers\NoteController;
use Note\models\NoteEntityModel;
use Note\models\NoteModel;
use PHPMailer\PHPMailer\PHPMailer;
use Resource\controllers\ResController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\PasswordModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class EmailController
{
    public function send(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'sendmail', 'userId' => $GLOBALS['userId'], 'location' => 'sendmail', 'type' => 'use'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        $user = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $isSent = EmailController::createEmail(['userId' => $user['id'], 'data' => $body]);

        if (!empty($isSent['errors'])) {
            return $response->withStatus($isSent['code'])->withJson(['errors' => $isSent['errors']]);
        }

        return $response->withStatus(204);
    }

    public static function createEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'data']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::arrayType($args, ['data']);

        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);

        $check = EmailController::controlCreateEmail(['login' => $user['user_id'], 'data' => $args['data']]);
        if (!empty($check['errors'])) {
            return ['errors' => $check['errors'], 'code' => $check['code']];
        }

        $id = EmailModel::create([
            'userId'                => $args['userId'],
            'sender'                => json_encode($args['data']['sender']),
            'recipients'            => json_encode($args['data']['recipients']),
            'cc'                    => empty($args['data']['cc']) ? '[]' : json_encode($args['data']['cc']),
            'cci'                   => empty($args['data']['cci']) ? '[]' : json_encode($args['data']['cci']),
            'object'                => empty($args['data']['object']) ? null : $args['data']['object'],
            'body'                  => empty($args['data']['body']) ? null : $args['data']['body'],
            'document'              => empty($args['data']['document']) ? null : json_encode($args['data']['document']),
            'isHtml'                => $args['data']['isHtml'] ? 'true' : 'false',
            'status'                => $args['data']['status'] == 'DRAFT' ? 'DRAFT' : 'WAITING',
            'messageExchangeId'     => empty($args['data']['messageExchangeId']) ? null : $args['data']['messageExchangeId']
        ]);

        HistoryController::add([
            'tableName'    => 'emails',
            'recordId'     => $id,
            'eventType'    => 'ADD',
            'eventId'      => 'emailCreation',
            'info'         => _EMAIL_ADDED
        ]);

        if ($args['data']['status'] != 'DRAFT') {
            $isSent = EmailController::sendEmail(['emailId' => $id, 'userId' => $args['userId']]);

            if (!empty($isSent['success'])) {
                EmailModel::update(['set' => ['status' => 'SENT', 'send_date' => 'CURRENT_TIMESTAMP'], 'where' => ['id = ?'], 'data' => [$id]]);
            } else {
                EmailModel::update(['set' => ['status' => 'ERROR'], 'where' => ['id = ?'], 'data' => [$id]]);
                return ['errors' => $isSent['errors'], 'code' => 502];
            }
        }

        return true;
    }

    public static function sendEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['emailId', 'userId']);
        ValidatorModel::intVal($args, ['emailId', 'userId']);

        $email = EmailModel::getById(['id' => $args['emailId']]);
        $email['sender']        = (array)json_decode($email['sender']);
        $email['recipients']    = json_decode($email['recipients']);
        $email['cc']            = json_decode($email['cc']);
        $email['cci']           = json_decode($email['cci']);

        $configuration = ConfigurationModel::getByService(['service' => 'admin_email_server', 'select' => ['value']]);
        $configuration = (array)json_decode($configuration['value']);
        if (empty($configuration)) {
            return ['errors' => 'Configuration is missing'];
        }

        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['firstname', 'lastname', 'user_id']]);

        $phpmailer = new PHPMailer();

        if ($configuration['type'] == 'smtp') {
            $phpmailer->isSMTP();
            $phpmailer->Host = $configuration['host'];
            $phpmailer->Port = $configuration['port'];
            if (!empty($configuration['secure'])) {
                $phpmailer->SMTPSecure = $configuration['secure'];
            }
            $phpmailer->SMTPAuth = $configuration['auth'];
            if ($configuration['auth']) {
                $phpmailer->Username = $configuration['user'];
                $phpmailer->Password = PasswordModel::decrypt(['cryptedPassword' => $configuration['password']]);
            }

            $emailFrom = empty($configuration['from']) ? $email['sender']['email'] : $configuration['from'];
            if (empty($email['sender']['entityId'])) {
                $phpmailer->setFrom($emailFrom, "{$user['firstname']} {$user['lastname']}");
            } else {
                $entity = EntityModel::getById(['id' => $email['sender']['entityId'], 'select' => ['short_label']]);
                $phpmailer->setFrom($emailFrom, $entity['short_label']);
            }
        } elseif ($configuration['type'] == 'sendmail') {
            $phpmailer->isSendmail();
        } elseif ($configuration['type'] == 'qmail') {
            $phpmailer->isQmail();
        }

        $phpmailer->addReplyTo($email['sender']['email']);
        $phpmailer->CharSet = $configuration['charset'];

        foreach ($email['recipients'] as $recipient) {
            $phpmailer->addAddress($recipient);
        }
        foreach ($email['cc'] as $recipient) {
            $phpmailer->addCC($recipient);
        }
        foreach ($email['cci'] as $recipient) {
            $phpmailer->addBCC($recipient);
        }

        if ($email['is_html']) {
            $phpmailer->isHTML(true);

            $dom = new \DOMDocument();
            $dom->loadHTML($email['body'], LIBXML_NOWARNING);
            $images = $dom->getElementsByTagName('img');

            foreach ($images as $key => $image) {
                $originalSrc = $image->getAttribute('src');
                if (preg_match('/^data:image\/(\w+);base64,/', $originalSrc)) {
                    $encodedImage = substr($originalSrc, strpos($originalSrc, ',') + 1);
                    $imageFormat = substr($originalSrc, 11, strpos($originalSrc, ';') - 11);
                    $phpmailer->addStringEmbeddedImage(base64_decode($encodedImage), "embeded{$key}", "embeded{$key}.{$imageFormat}");
                    $email['body'] = str_replace($originalSrc, "cid:embeded{$key}", $email['body']);
                }
            }
        }

        $phpmailer->Subject = $email['object'];
        $phpmailer->Body = $email['body'];
        if (empty($email['body'])) {
            $phpmailer->AllowEmpty = true;
        }

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
            if (!empty($email['document']['notes'])) {
                $email['document']['notes'] = (array)$email['document']['notes'];
                $encodedDocument = NoteController::getEncodedPdfByIds(['ids' => $email['document']['notes']]);
                if (empty($encodedDocument['errors'])) {
                    $phpmailer->addStringAttachment(base64_decode($encodedDocument['encodedDocument']), 'notes.pdf');
                }
            }
        }

        $phpmailer->Timeout = 30;
        $phpmailer->SMTPDebug = 1;
        $phpmailer->Debugoutput = function($str) {
            if (strpos($str, 'SMTP ERROR') !== false) {
                HistoryController::add([
                    'tableName'    => 'emails',
                    'recordId'     => 'email',
                    'eventType'    => 'ERROR',
                    'eventId'      => 'sendEmail',
                    'info'         => $str
                ]);
            }
        };

        $isSent = $phpmailer->send();
        if (!$isSent) {
            $history = HistoryModel::get([
                'select'    => ['info'],
                'where'     => ['user_id = ?', 'event_id = ?', 'event_type = ?'],
                'data'      => [$user['user_id'], 'sendEmail', 'ERROR'],
                'orderBy'   => ['event_date DESC'],
                'limit'     => 1
            ]);
            if (!empty($history[0]['info'])) {
                return ['errors' => $history[0]['info']];
            }

            return ['errors' => $phpmailer->ErrorInfo];
        }

        return ['success' => 'success'];
    }

    private static function controlCreateEmail(array $args)
    {
        ValidatorModel::notEmpty($args, ['login']);
        ValidatorModel::stringType($args, ['login']);
        ValidatorModel::arrayType($args, ['data']);

        if (!Validator::arrayType()->notEmpty()->validate($args['data']['sender']) || !Validator::stringType()->notEmpty()->validate($args['data']['sender']['email'])) {
            return ['errors' => 'Data sender email is not set', 'code' => 400];
        } elseif (!Validator::arrayType()->notEmpty()->validate($args['data']['recipients'])) {
            return ['errors' => 'Data recipients is not an array or empty', 'code' => 400];
        } elseif (!Validator::boolType()->validate($args['data']['isHtml'])) {
            return ['errors' => 'Data isHtml is not a boolean or empty', 'code' => 400];
        } elseif (!Validator::stringType()->notEmpty()->validate($args['data']['status'])) {
            return ['errors' => 'Data status is not a string or empty', 'code' => 400];
        }

        if (!empty($args['data']['document'])) {
            $check = Validator::intVal()->notEmpty()->validate($args['data']['document']['id']);
            $check = $check && Validator::boolType()->validate($args['data']['document']['isLinked']);
            $check = $check && Validator::boolType()->validate($args['data']['document']['original']);
            if (!$check) {
                return ['errors' => 'Data document errors', 'code' => 400];
            }
            if (!ResController::hasRightByResId(['resId' => $args['data']['document']['id'], 'userId' => $args['login']])) {
                return ['errors' => 'Document out of perimeter', 'code' => 403];
            }
            if (!empty($args['data']['document']['attachments'])) {
                if (!is_array($args['data']['document']['attachments'])) {
                    return ['errors' => 'Data document[attachments] is not an array', 'code' => 400];
                }
                foreach ($args['data']['document']['attachments'] as $attachment) {
                    $check = Validator::intVal()->notEmpty()->validate($attachment['id']);
                    $check = $check && Validator::boolType()->validate($attachment['isVersion']);
                    $check = $check && Validator::boolType()->validate($attachment['original']);
                    if (!$check) {
                        return ['errors' => 'Data document[attachments] errors', 'code' => 400];
                    }
                    $checkAttachment = AttachmentModel::getById(['id' => $attachment['id'], 'isVersion' => $attachment['isVersion'], 'select' => ['res_id_master']]);
                    if (empty($checkAttachment) || $checkAttachment['res_id_master'] != $args['data']['document']['id']) {
                        return ['errors' => 'Attachment out of perimeter', 'code' => 403];
                    }
                }
            }
            if (!empty($args['data']['document']['notes'])) {
                if (!is_array($args['data']['document']['notes'])) {
                    return ['errors' => 'Data document[notes] is not an array', 'code' => 400];
                }
                foreach ($args['data']['document']['notes'] as $note) {
                    if (!Validator::intVal()->notEmpty()->validate($note)) {
                        return ['errors' => 'Data document[notes] errors', 'code' => 400];
                    }
                    $checkNote = NoteModel::getById(['id' => $note, 'select' => ['identifier']]);
                    if (empty($checkNote) || $checkNote['identifier'] != $args['data']['document']['id']) {
                        return ['errors' => 'Note out of perimeter', 'code' => 403];
                    }

                    $rawUserEntities = EntityModel::getByLogin(['login' => $args['login'], 'select' => ['entity_id']]);
                    $userEntities = [];
                    foreach ($rawUserEntities as $rawUserEntity) {
                        $userEntities[] = $rawUserEntity['entity_id'];
                    }
                    $noteEntities = NoteEntityModel::get(['select' => ['item_id'], 'where' => ['note_id = ?'], 'data' => [$note]]);
                    if (!empty($noteEntities)) {
                        $found = false;
                        foreach ($noteEntities as $noteEntity) {
                            if (in_array($noteEntity['item_id'], $userEntities)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            return ['errors' => 'Note out of perimeter', 'code' => 403];
                        }
                    }
                }
            }
        }

        return ['success' => 'success'];
    }
}
