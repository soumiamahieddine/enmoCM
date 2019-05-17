<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Full Text Script
 * @author dev@maarch.org
 */

namespace Convert\scripts;

require 'vendor/autoload.php';

use Attachment\models\AttachmentModel;
use Convert\controllers\FullTextController;
use Resource\models\ResModel;
use SrcCore\controllers\LogsController;
use SrcCore\models\DatabasePDO;
use User\models\UserModel;

//customId  = $argv[1];
//resId     = $argv[2];
//collId    = $argv[3];
//userId    = $argv[4];

FullTextScript::index(['customId' => $argv[1], 'resId' => $argv[2], 'collId' => $argv[3], 'userId' => $argv[4]]);

class FullTextScript
{
    public static function index(array $args)
    {
        DatabasePDO::reset();
        new DatabasePDO(['customId' => $args['customId']]);

        $currentUser = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);
        $GLOBALS['userId'] = $currentUser['user_id'];

        $isIndexed = FullTextController::indexDocument(['resId' => $args['resId'], 'collId' => $args['collId']]);
        if (!empty($isIndexed['success'])) {
            if ($args['collId'] == 'letterbox_coll') {
                ResModel::update(['set' => ['fulltext_result' => 'SUCCESS'], 'where' => ['res_id = ?'], 'data' => [$args['resId']]]);
            } else {
                AttachmentModel::update([
                    'set'       => ['fulltext_result' => 'SUCCESS'],
                    'where'     => ['res_id = ?'],
                    'data'      => [$args['resId']],
                    'isVersion' => $args['collId'] == 'attachments_version_coll'
                ]);
            }
        } else {
            if ($args['collId'] == 'letterbox_coll') {
                ResModel::update(['set' => ['fulltext_result' => 'ERROR'], 'where' => ['res_id = ?'], 'data' => [$args['resId']]]);
            } else {
                AttachmentModel::update([
                    'set'       => ['fulltext_result' => 'ERROR'],
                    'where'     => ['res_id = ?'],
                    'data'      => [$args['resId']],
                    'isVersion' => $args['collId'] == 'attachments_version_coll'
                ]);
            }
            LogsController::add([
                'isTech'    => true,
                'moduleId'  => 'fullText',
                'level'     => 'ERROR',
                'tableName' => $args['collId'],
                'recordId'  => $args['resId'],
                'eventType' => "Full Text failed : {$isIndexed['errors']}",
                'eventId'   => "resId : {$args['resId']} || collId : {$args['collId']}"
            ]);
        }

        return $isIndexed;
    }
}
