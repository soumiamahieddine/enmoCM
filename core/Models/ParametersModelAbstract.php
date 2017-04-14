<?php
    namespace Core\Models;

    require_once 'apps/maarch_entreprise/services/Table.php';

    class ParametersModelAbstract extends \Apps_Table_Service
    {
        public static function getList()
        {
            $aReturn = static::select([
                'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
                'table'     => ['parameters'],
            ]);

            return $aReturn;
        }
        

        public static function getById(array $aArgs = [])
        {
            static::checkRequired($aArgs, ['id']);
            static::checkString($aArgs,['id']);

            $aReturn = static::select([
                'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
                'table'     =>['parameters'],
                'where'     => ['id = ?'],
                'data'      => [$aArgs['id']]
            ]);

            return $aReturn;

        }

        public static function create(array $aArgs = [])
        {
            static::checkRequired($aArgs, ['id']);
            static::checkString($aArgs, ['id']);

            $aReturn = static::insertInto($aArgs, 'parameters');

            return $aReturn;
        }

        public static function update(array $aArgs = [])
        {
            static::checkRequired($aArgs, ['id']);
            static::checkString($aArgs, ['id']);

            $where['id'] = $aArgs['id'];

            $aReturn = static::updateTable(
                $aArgs,
                'parameters',
                $where
            );

            return $aReturn;
        }

        public static function delete(array $aArgs = [])
        {
            static::checkRequired($aArgs, ['id']);
            static::checkString($aArgs, ['id']);

            $aReturn = static::deleteFrom([
                    'table' => 'parameters',
                    'where' => ['id = ?'],
                    'data'  => [$aArgs['id']]
                ]);

            return $aReturn;
        }

    }
?>