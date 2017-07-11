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

class ResExtModelAbstract
{
    /**
     * Retrieve info of resId
     * @param  $aArgs array
     *
     * @return array
     */
    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'mlb_coll_ext';
        }

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        return $aReturn;
    }

    /**
     * Retrieve info of last resId
     * @param  $aArgs array
     *
     * @return array
     */
    public static function getLastId(array $aArgs = [])
    {
        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'mlb_coll_ext';
        }

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'order_by'  => ['res_id desc'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    /**
     * Retrieve process_limit_date for resource in extension table if mlb
     * @param  $aArgs array
     *
     * @return integer $processLimitDate
     */
    public function retrieveProcessLimitDate(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'res_view_letterbox';
        }
        $aArgs['select'] = ['creation_date, admission_date, type_id'];
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);
        require_once('core/class/class_functions.php');
        $func = new \functions();

        if ($aReturn[0]['type_id'] <> '') {
            $typeId = $aReturn[0]['type_id'];
            $admissionDate = $aReturn[0]['admission_date'];
            $creationDate = $aReturn[0]['creation_date'];
            $aArgs['select'] = ['process_delay'];
            $aReturnT = DatabaseModel::select([
                'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
                'table'     => ['mlb_doctype_ext'],
                'where'     => ['type_id = ?'],
                'data'      => [$aReturn[0]['type_id']]
            ]);
            $delay = $aReturnT[0]['process_delay'];
        }

        if ($admissionDate == '') {
            $dateToCompute = $creationDate;
        } else {
            $dateToCompute = $admissionDate;
        }
        if ($aArgs['defaultDelay'] > 0) {
            $delay = $aArgs['defaultDelay'];
        } elseif ($delay == 0) {
            $delay = 5;
        }
        require_once('core/class/class_alert_engine.php');
        $alert_engine = new \alert_engine();
        if (isset($dateToCompute) && !empty($dateToCompute)) {
            $convertedDate = $alert_engine->dateFR2Time(
                str_replace(
                    "-",
                    "/",
                    $func->format_date_db(
                        $dateToCompute,
                        'true',
                        '',
                        'true'
                    )
                ),
                true
            );
            $date = $alert_engine->WhenOpenDay(
                $convertedDate,
                $delay,
                false,
                $aArgs['calendarType']
            );
        } else {
            $date = $alert_engine->date_max_treatment($delay, false);
        }
        
        $processLimitDate = $func->dateformat($date, '-');

        return $processLimitDate;
    }

    /**
     * insert into a resTable
     * @param  $aArgs array
     *
     * @return boolean
     */
    public static function create(array $aArgs = [])
    {
        if (empty($aArgs['table'])) {
            $aArgs['table'] = 'mlb_coll_ext';
        }

        DatabaseModel::insert([
            'table'         => $aArgs['table'],
            'columnsValues' => $aArgs['data']
        ]);

        return true;
    }

    /**
     * deletes into a resTable
     * @param  $aArgs array
     *
     * @return boolean
     */
    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        if (empty($aArgs['table'])) {
            $aArgs['table'] = 'mlb_coll_ext';
        }

        DatabaseModel::delete([
                'table' => $aArgs['table'],
                'where' => ['res_id = ?'],
                'data'  => [$aArgs['id']]
        ]);

        return true;
    }
}
