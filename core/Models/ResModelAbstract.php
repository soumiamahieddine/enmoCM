<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Res Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class ResModelAbstract
{
    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_letterbox'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        if (empty($aReturn[0])) {
            return [];
        }

        return $aReturn[0];
    }

    public static function getExtById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['mlb_coll_ext'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        if (empty($aReturn[0])) {
            return [];
        }

        return $aReturn[0];
    }

    public static function updateStatus(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'status']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['status']);

        DatabaseModel::update([
            'table'     => 'res_letterbox',
            'set'       => [
                'status'    => $aArgs['status']
            ],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        return true;
    }
    
    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['format', 'typist', 'creation_date', 'docserver_id', 'path', 'filename', 'fingerprint', 'filesize', 'status']);
        ValidatorModel::stringType($aArgs, ['format', 'typist', 'creation_date', 'docserver_id', 'path', 'filename', 'fingerprint', 'status']);
        ValidatorModel::intVal($aArgs, ['filesize']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'res_id_mlb_seq']);
        $aArgs['res_id'] = $nextSequenceId;

        DatabaseModel::insert([
            'table'         => 'res_letterbox',
            'columnsValues' => $aArgs
        ]);

        return $nextSequenceId;
    }

    public static function createExt(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['res_id', 'category_id']);
        ValidatorModel::stringType($aArgs, ['category_id']);
        ValidatorModel::intVal($aArgs, ['res_id']);

        DatabaseModel::insert([
            'table'         => 'mlb_coll_ext',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    /**
     * update a resTable
     * @param  $aArgs array
     *
     * @return boolean
     */
    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['res_id']);
        ValidatorModel::intVal($aArgs, ['res_id']);
        ValidatorModel::stringType($aArgs, ['table']);
        ValidatorModel::arrayType($aArgs, ['data']);

        if (empty($aArgs['table'])) {
            $aArgs['table'] = 'res_letterbox';
        }

        DatabaseModel::update([
            'table' => $aArgs['table'],
            'set'   => $aArgs['data'],
            'where' => ['res_id = ?'],
            'data'  => [$aArgs['res_id']]
        ]);

        return true;
    }

    public static function isLockForCurrentUser(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aReturn = DatabaseModel::select([
            'select'    => ['locker_user_id', 'locker_time'],
            'table'     => ['res_letterbox'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        $lock = true;
        $lockBy = empty($aReturn[0]['locker_user_id']) ? '' : $aReturn[0]['locker_user_id'];

        if (empty($aReturn[0]['locker_user_id'] || empty($aReturn[0]['locker_time']))) {
            $lock = false;
        } elseif ($aReturn[0]['locker_user_id'] == $_SESSION['user']['UserId']) { //TODO SESSION
            $lock = false;
        } elseif (strtotime($aReturn[0]['locker_time']) < time()) {
            $lock = false;
        }

        return ['lock' => $lock, 'lockBy' => $lockBy];
    }

    public static function getDocsByClause(array $aArgs = [])
    {
		ValidatorModel::notEmpty($aArgs, ['clause']);

        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'res_view_letterbox';
        }

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => [$aArgs['clause']],
            'order_by'  => ['res_letterbox.res_id']
        ]);

        return $aReturn;
    }

    // In Progress
//    public static function getProcessLimitDate(array $aArgs)
//    {
//        ValidatorModel::notEmpty($aArgs, ['resId']);
//        ValidatorModel::intVal($aArgs, ['resId']);
//
//
//
//
//        if (!empty($aArgs['table'])) {
//            $table = $aArgs['table'];
//        } else {
//            $table = 'res_view_letterbox';
//        }
//        $aArgs['select'] = ['creation_date, admission_date, type_id'];
//        $aReturn = static::select([
//            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
//            'table'     => [$table],
//            'where'     => ['res_id = ?'],
//            'data'      => [$aArgs['resId']]
//        ]);
//        require_once('core/class/class_functions.php');
//        $func = new \functions();
//        if ($aReturn[0]['type_id'] <> '') {
//            $typeId = $aReturn[0]['type_id'];
//            $admissionDate = $aReturn[0]['admission_date'];
//            $creationDate = $aReturn[0]['creation_date'];
//            $aArgs['select'] = ['process_delay'];
//            $aReturnT = static::select([
//                'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
//                'table'     => ['mlb_doctype_ext'],
//                'where'     => ['type_id = ?'],
//                'data'      => [$aReturn[0]['type_id']]
//            ]);
//            $delay = $aReturnT[0]['process_delay'];
//        }
//        if ($admissionDate == '') {
//            $dateToCompute = $creationDate;
//        } else {
//            $dateToCompute = $admissionDate;
//        }
//
//
//
//
//
//        $document = ResModel::getById(['resId' => $aArgs['resId'], 'select' => ['creation_date', 'type_id']]);
//
//        if (!empty($document['type_id'])) {
//            $doctypeExt = DatabaseModel::select([
//                'select'    => ['process_delay'],
//                'table'     => ['mlb_doctype_ext'],
//                'where'     => ['type_id = ?'],
//                'data'      => [$document['type_id']]
//            ]);
//            $processDelay = $doctypeExt[0]['process_delay'];
//        }
//
//
//
//
//        require_once('core/class/class_alert_engine.php');
//        $alert_engine = new \alert_engine();
//        if (isset($dateToCompute) && !empty($dateToCompute)) {
//            $convertedDate = $alert_engine->dateFR2Time(
//                str_replace(
//                    "-",
//                    "/",
//                    $func->format_date_db(
//                        $dateToCompute,
//                        'true',
//                        '',
//                        'true'
//                    )
//                ),
//                true
//            );
//
//
//            $date = $alert_engine->WhenOpenDay(
//                $convertedDate,
//                $delay,
//                false,
//                $aArgs['calendarType']
//            );
//        } else {
//            $date = $alert_engine->date_max_treatment($delay, false);
//        }
//
//        $processLimitDate = $func->dateformat($date, '-');
//
//        return $processLimitDate;
//    }

}
