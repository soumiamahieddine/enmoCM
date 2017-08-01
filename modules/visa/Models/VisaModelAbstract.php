<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace Visa\Models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class VisaModelAbstract
{

    public static function hasVisaWorkflowByResId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aReturn = DatabaseModel::select([
            'select'    => ['COUNT(*)'],
            'table'     => ['listinstance'],
            'where'     => ['res_id = ?', 'item_mode in (?)'],
            'data'      => [$aArgs['resId'], ['visa', 'sign']]
        ]);

        return ((int)$aReturn[0]['count'] > 0);
    }
}