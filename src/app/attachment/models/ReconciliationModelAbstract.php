<?php
/**
 *​ ​Copyright​ ​Maarch​ since ​2008​ under licence ​GPLv3.
 *​ ​See​ LICENCE​.​txt file at the root folder ​for​ more details.
 *​ ​This​ file ​is​ part of ​Maarch​ software.
 *
 */


namespace Attachment\models;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ReconciliationModelAbstract {
    public static function getReconciliation (array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);

        $select = [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => gettype($aArgs['table']) == 'string' ? array($aArgs['table']) : $aArgs['table'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ];

        if (!empty($aArgs['orderBy'])) {
            $select['order_by'] = $aArgs['orderBy'];
        }

        $aReturn = $aAttachments = DatabaseModel::select($select);

        return $aReturn;
    }

public static function updateReconciliation (array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['set']);
        ValidatorModel::arrayType($aArgs, ['where', 'data', 'set']);

        DatabaseModel::update([
            'set'       => $aArgs['set'],
            'table'     => gettype($aArgs['table']) == 'array' ? (string) $aArgs['table'] : $aArgs['table'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ]);

        return true;
    }
}
