<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   FirstLevelModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\models;

use Core\Models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

class FirstLevelModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $firstLevel = DatabaseModel::select([
            'select'   => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'    => ['doctypes_first_level'],
            'order_by' => ['doctypes_first_level_id asc']
        ]);

        return $firstLevel;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['doctypes_first_level'],
            'where'  => ['doctypes_first_level_id = ?'],
            'data'   => [$aArgs['id']]
            ]
        );

        if (empty($aReturn[0])) {
            return [];
        }

        $aReturn = $aReturn[0];
       
        return $aReturn;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['label']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'doctypes_first_level_id_seq']);
        DatabaseModel::insert([
            'table'         => 'doctypes_first_level',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        
        DatabaseModel::update([
            'table'     => 'actions',
            'set'       => [
                'keyword'           => $aArgs['keyword'],
                'label_action'      => $aArgs['label_action'],
                'id_status'         => $aArgs['id_status'],
                'action_page'       => $aArgs['action_page'],
                'history'           => $aArgs['history'],
                'is_folder_action'  => $aArgs['is_folder_action']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        DatabaseModel::delete([
            'table' => 'actions_categories',
            'where'  => ['action_id = ?'],
            'data'   => [$aArgs['id']]
        ]);

        $tab['action_id'] = $aArgs['id'];

        for ($i=0; $i < count($aArgs['actionCategories']); $i++) {
            $tab['category_id'] = $aArgs['actionCategories'][$i];
            DatabaseModel::insert([
                'table'         => 'actions_categories',
                'columnsValues' => $tab
            ]);
        }

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'actions',
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_categories',
            'where' => ['action_id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_groupbaskets',
            'where' => ['id_action = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }

    public static function getAction_pages()
    {
        $customId = CoreConfigModel::getCustomId();

        if (file_exists('custom/' .$customId. '/core/xml/actions_pages.xml')) {
            $path = 'custom/' .$customId. '/core/xml/actions_pages.xml';
        } else {
            $path = 'core/xml/actions_pages.xml';
        }

        $tabActions_pages              = [];
        $tabActions_pages['modules'][] = 'Apps';

        $xmlfile = simplexml_load_file($path);
        
        if (count($xmlfile) > 0) {
            foreach ($xmlfile->ACTIONPAGE as $actionPage) {
                if (!defined((string) $actionPage->LABEL)) {
                    $label = $actionPage->LABEL;
                } else {
                    $label = constant((string) $actionPage->LABEL);
                }
                if (!empty((string) $actionPage->MODULE)) {
                    $origin = (string) $actionPage->MODULE;
                } else {
                    $origin =  'apps';
                }
                if (!empty((string) $actionPage->DESC)) {
                    $desc = constant((string) $actionPage->DESC);
                } else {
                    $desc =  'no description';
                }
                $tabActions_pages['actionsPageList'][] = array(
                    'id'     => (string) $actionPage->ID,
                    'label'  => $label,
                    'name'   => (string) $actionPage->NAME,
                    'desc'   => $desc,
                    'origin' => ucfirst($origin),
                );
            }
        }

        array_multisort(
            array_map(
                function ($element) {
                    return $element['label'];
                }, $tabActions_pages['actionsPageList']
            ),
            SORT_ASC, $tabActions_pages['actionsPageList']
        );
        
        $tabActions_pages['modules'] = array_unique($tabActions_pages['modules']);
        sort($tabActions_pages['modules']);
        return $tabActions_pages;
    }

    public static function getKeywords()
    {
        $tabKeyword   = [];
        $tabKeyword[] = ['value' => '', label => _NO_KEYWORD];
        $tabKeyword[] = ['value' => 'redirect', label => _REDIRECT, desc => _KEYWORD_REDIRECT_DESC];
        //$tabKeyword[] = ['value' => 'to_validate', label => _TO_VALIDATE];
        $tabKeyword[] = ['value' => 'indexing', label => _INDEXING, desc => _KEYWORD_INDEXING_DESC];
        //$tabKeyword[] = ['value' => 'workflow', label => _WF];

        return $tabKeyword;
    }

    public static function getActionPageById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $action = DatabaseModel::select([
            'select' => ['action_page'],
            'table'  => ['actions'],
            'where'  => ['id = ? AND enabled = ?'],
            'data'   => [$aArgs['id'], 'Y']
        ]);

        if (empty($action[0])) {
            return '';
        }

        return $action[0]['action_page'];
    }

    public static function getDefaultActionByGroupBasketId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'basketId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'basketId']);

        $action = DatabaseModel::select([
            'select' => ['id_action'],
            'table'  => ['actions_groupbaskets'],
            'where'  => ['group_id = ?', 'basket_id = ?', 'default_action_list = ?'],
            'data'   => [$aArgs['groupId'], $aArgs['basketId'], 'Y']
        ]);

        if (empty($action[0])) {
            return '';
        }

        return $action[0]['id_action'];
    }
}
