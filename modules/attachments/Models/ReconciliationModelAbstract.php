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
    public static function selectReconciliation (array $aArgs = [])
    {
         static::checkRequired($aArgs, ['where', 'data', 'table']);

        $select = [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => gettype($aArgs['table']) == 'string' ? array($aArgs['table']) : $aArgs['table'],
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

        $aReturn = static::update([
            'set'       => $aArgs['set'],
            'table'     => gettype($aArgs['table']) == 'array' ? (string) $aArgs['table'] : $aArgs['table'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ]);

        return $aReturn;
    }
}
