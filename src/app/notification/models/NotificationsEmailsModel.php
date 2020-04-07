<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Notifications Emails Model
* @author dev@maarch.org
*/

namespace Notification\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class NotificationsEmailsModel
{
    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['sender', 'recipient', 'subject', 'html_body', 'charset', 'module']);
        ValidatorModel::stringType($aArgs, ['sender', 'recipient', 'subject', 'html_body', 'charset', 'module']);

        $aReturn = DatabaseModel::insert([
            'table'         => 'notif_email_stack',
            'columnsValues' => $aArgs
        ]);

        return $aReturn;
    }
}
