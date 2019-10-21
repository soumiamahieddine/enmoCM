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
*/

namespace Resource\models;

use Doctype\models\DoctypeModel;
use Resource\controllers\IndexingController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class ResModelAbstract
{
    public static function getOnView(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy', 'groupBy']);
        ValidatorModel::intType($aArgs, ['limit', 'offset']);

        $aResources = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['res_view_letterbox'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'groupBy'   => empty($aArgs['groupBy']) ? [] : $aArgs['groupBy'],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);
        
        return $aResources;
    }

    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $aResources = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['res_letterbox'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aResources;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aResources = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => empty($aArgs['table']) ? ['res_letterbox'] : array_merge(['res_letterbox'], $aArgs['table']),
            'left_join' => empty($aArgs['leftJoin']) ? [] : $aArgs['leftJoin'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        if (empty($aResources[0])) {
            return [];
        }

        return $aResources[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['res_id', 'format', 'typist', 'creation_date', 'docserver_id', 'path', 'filename', 'fingerprint', 'filesize', 'category_id']);
        ValidatorModel::stringType($args, ['format', 'creation_date', 'docserver_id', 'path', 'filename', 'fingerprint', 'category_id']);
        ValidatorModel::intVal($args, ['filesize', 'res_id', 'typist']);

        DatabaseModel::insert([
            'table'         => 'res_letterbox',
            'columnsValues' => $args
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'res_letterbox',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        DatabaseModel::update([
            'table' => 'res_letterbox',
            'set'   => [
                'status'    => 'DEL'
            ],
            'where' => ['res_id = ?'],
            'data'  => [$aArgs['resId']]
        ]);

        return true;
    }

    public static function getLastResources(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['limit', 'userId', 'select']);
        ValidatorModel::intType($aArgs, ['limit']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $resources = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['history, res_letterbox r, status, priorities'],
            'where'     => [
                'history.user_id = ?', 'history.table_name IN (?)',
                'history.record_id IS NOT NULL', 'history.record_id != ?',
                'history.event_id != ?', 'history.event_id NOT LIKE ?',
                'CAST(history.record_id AS INT) = r.res_id',
                'r.res_id = r.res_id', 'r.status != ?',
                'r.status = status.id',
                'r.priority = priorities.id'
            ],
            'data'      => [$aArgs['userId'], ['res_letterbox', 'res_view_letterbox'], 'none', 'linkup', 'attach%', 'DEL'],
            'groupBy'   => ['r.subject', 'r.creation_date', 'r.res_id', 'r.alt_identifier', 'r.closing_date', 'r.process_limit_date', 'status.id', 'status.label_status', 'status.img_filename', 'priorities.color', 'priorities.label'],
            'order_by'  => ['MAX(history.event_date) DESC'],
            'limit'     => $aArgs['limit']
        ]);

        return $resources;
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
            'order_by'  => ['res_id']
        ]);

        return $aReturn;
    }

    public static function getByAltIdentifier(array $args)
    {
        ValidatorModel::notEmpty($args, ['altIdentifier']);
        ValidatorModel::stringType($args, ['altIdentifier']);

        $resource = DatabaseModel::select([
            'select'    => empty($args['select']) ? ['*'] : $args['select'],
            'table'     => ['res_letterbox'],
            'where'     => ['alt_identifier = ?'],
            'data'      => [$args['altIdentifier']]
        ]);

        if (empty($resource[0])) {
            return [];
        }

        return $resource[0];
    }

    public static function getCategories()
    {
        $categories = [
            [
                'id'              => 'incoming',
                'label'           => _INCOMING
            ],
            [
                'id'              => 'outgoing',
                'label'           =>  _OUTGOING
            ],
            [
                'id'              => 'internal',
                'label'           => _INTERNAL
            ],
            [
                'id'              => 'ged_doc',
                'label'           => _GED_DOC
            ]
        ];

        return $categories;
    }

    public static function getCategoryLabel(array $args)
    {
        ValidatorModel::stringType($args, ['categoryId']);

        $categories = ResModel::getCategories();
        foreach ($categories as $category) {
            if ($category['id'] == $args['categoryId']) {
                return $category['label'];
            }
        }

        return '';
    }

    public static function getNatures()
    {
        static $natures;

        if (!empty($natures)) {
            return $natures;
        }

        $natures = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/entreprise.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->mail_natures->nature as $nature) {
                $withReference = (string)$nature['with_reference'] == 'true' ? true : false;
                $nature = (array)$nature;

                $natures[] = [
                    'id'            => $nature['id'],
                    'label'         => defined($nature['label']) ? constant($nature['label']) : $nature['label'],
                    'withReference' => $withReference,
                    'defaultNature' => $nature['id'] == $loadedXml->mail_natures->default_nature
                ];
            }
        }

        return $natures;
    }

    public static function getNatureLabel(array $args)
    {
        ValidatorModel::stringType($args, ['natureId']);

        $natures = ResModel::getNatures();
        foreach ($natures as $nature) {
            if ($nature['id'] == $args['natureId']) {
                return $nature['label'];
            }
        }

        return '';
    }

    public static function getNbContactsByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aResources = DatabaseModel::select([
            'select'    => ['count(1) as nb_contacts'],
            'table'     => ['contacts_res'],
            'where'     => ['res_id = ?', 'mode = ?'],
            'data'      => [$aArgs['resId'], 'multi']
        ]);
        return $aResources[0]['nb_contacts'];
    }
}
