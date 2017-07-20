<?php
/**
 *​ ​Copyright​ ​Maarch​ since ​2008​ under licence ​GPLv3.
 *​ ​See​ LICENCE​.​txt file at the root folder ​for​ more details.
 *​ ​This​ file ​is​ part of ​Maarch​ software.
 *
 */


namespace Attachments\Models;

require_once 'apps/maarch_entreprise/services/Table.php';


class ReconciliationModelAbstract extends \Apps_Table_Service{
    public static function selectReconciliation(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['where', 'data']);
        static::checkArray($aArgs, ['where', 'data']);

        $select = [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => $aArgs['table'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ];

        if (!empty($aArgs['orderBy'])) {
            $select['order_by'] = $aArgs['orderBy'];
        }

        $aReturn = static::select($select);

        return $aReturn;
    }

    public static function updateReconciliation (array $aArgs = [])
    {
        static::checkRequired($aArgs, ['where', 'data', 'set']);
        static::checkArray($aArgs, ['set','where', 'data']);

        $update = [
            'set'       => $aArgs['set'],
            'table'     => $aArgs['table'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ];

        $aReturn = static::update($update);

        return $aReturn;
    }
}