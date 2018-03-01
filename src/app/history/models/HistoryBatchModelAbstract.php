<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief HistoryBatch Model
* @author dev@maarch.org
*/

namespace History\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

require_once('apps/maarch_entreprise/tools/log4php/Logger.php'); //TODO composer

class HistoryBatchModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['event_date']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['history_batch'],
            'where'     => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by'  => ['event_date DESC']
        ]);

        return $aReturn;
    }

    public static function getFilter(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select','event_date']);
        ValidatorModel::stringType($aArgs, ['select']);

        $aReturn = DatabaseModel::select(
            [
            'select'    => ['DISTINCT('.$aArgs['select'].')'],
            'table'     => ['history_batch'],
            'where'     => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by'  => [$aArgs['select'].' ASC']
            ]
        );

        return $aReturn;
    }
}
