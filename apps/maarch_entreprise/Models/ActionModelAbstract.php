<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Action Model
* @author dev@maarch.org
* @ingroup apps
*/

namespace Apps\Models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class ActionModelAbstract
{
    public static function getDefaultActionByGroupBasketId(array $aArgs) {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'basketId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'basketId']);

        $action = DatabaseModel::select([
            'select'    => ['id_action'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['group_id = ?', 'basket_id = ?', 'default_action_list = ?'],
            'data'      => [$aArgs['groupId'], $aArgs['basketId'], 'Y']
        ]);

        if (empty($action[0])) {
            return '';
        }

        return $action[0]['id_action'];
    }
}
