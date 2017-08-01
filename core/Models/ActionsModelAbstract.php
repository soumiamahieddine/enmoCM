<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionsModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Core\Models;

class ActionsModelAbstract
{
    public static function getList()
    {
        $aReturn = DatabaseModel::select(
            [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            ]
        );

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
            ]
        );

        if (empty($aReturn[0])) {
            return [];
        }

        $aReturn = $aReturn[0];
        $aReturn['actionCategories']=DatabaseModel::select(
            [
            'select'    => ['category_id'],
            'table'     => ['actions_categories'],
            'where'     => ['action_id = ?'],
            'data'      => [$aArgs['id']]
            ]
        );
       
        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        $actioncategories = $aArgs['actionCategories'];
        unset($aArgs['actionCategories']);
        $aReturn = DatabaseModel::insert(
            [
            'table'         => 'actions',
            'columnsValues' => $aArgs
            ]
        );

        $tab['action_id'] = max(ActionsModel::getList())['id'];

        for ($i=0;$i<count($actioncategories);$i++) {

            $tab['category_id'] = $actioncategories[$i];
            $aInsert = DatabaseModel::insert(
                [
                'table'         => 'actions_categories',
                'columnsValues' => $tab
                ]
            );    
        }
        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        
        $aReturn = DatabaseModel::update(
            ['table'     => 'actions',
            'set'       => [
                'keyword'           => $aArgs['keyword'],          
                'label_action'      => $aArgs['label_action'],
                'id_status'         => $aArgs['id_status'],
                'action_page'       => $aArgs['action_page'],
                'history'           => $aArgs['history'],
                'is_folder_action'  => $aArgs['is_folder_action']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]]
        );

        $aDelete = DatabaseModel::delete(
            ['table' => 'actions_categories',
            'where' => ['action_id = ?'],
            'data'  => [$aArgs['id']]
            ]
        );

        $tab['action_id']=$aArgs['id'];

        for ($i=0;$i<count($aArgs['actionCategories']);$i++) {

            $tab['category_id']=$aArgs['actionCategories'][$i];
            $aInsert = DatabaseModel::insert(
                [
                'table'         => 'actions_categories',
                'columnsValues' => $tab
                ]
            );    
        }

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::delete(
            [
                'table' => 'actions',
                'where' => ['id = ?'],
                'data'  => [$aArgs['id']]
            ]
        );
        $aDelete = DatabaseModel::delete(
            [
                'table' => 'actions_categories',
                'where' => ['action_id = ?'],
                'data'  => [$aArgs['id']]
            ]
        );
        return $aReturn;
    }

    public static function getLettersBoxCategories()
    {
        $customId = CoreConfigModel::getCustomId();

        if (file_exists('custom/' .$customId. '/apps/maarch_entreprise/xml/config.xml')) {
            $path = 'custom/' .$customId. '/apps/maarch_entreprise/xml/config.xml';
        } else {
            $path = 'apps/maarch_entreprise/xml/config.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $categoriesTypes=[];
        $categories= $xmlfile->COLLECTION->categories;
        if (count($categories) > 0) {
            foreach ($categories->category as $category) {
                $categoriesTmp = ['id' => (string)$category->id, 'label'=> constant((string)$category->label)];

                if ($category->id == (string)$categories->default_category) {
                    $categoriesTmp['default_category']=true;

                } else {
                    $categoriesTmp['default_category']=false;
                }
                $categoriesTypes[]=$categoriesTmp;
            }
        }
        return $categoriesTypes;

    }

    public static function getAction_pages()
    {
        $customId = CoreConfigModel::getCustomId();

        if (file_exists('custom/' .$customId. '/core/xml/actions_pages.xml')) {
            $path = 'custom/' .$customId. '/core/xml/actions_pages.xml';
        } else {
            $path = 'core/xml/actions_pages.xml';
        }

        $tabActions_pages=[];
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
                    'id'          => (string) $actionPage->ID,
                    'label'       => $label,
                    'name'        => (string) $actionPage->NAME,
                    'desc'        => $desc,
                    'origin'      => ucfirst($origin),
                );
            }
        }
        // TODO Remove session
//        foreach ($_SESSION['modules'] as $key => $value) {
//
//            if (file_exists('custom/'. $_SESSION['custom_override_id'] . 'modules/' . $value['moduleid'] . '/xml/actions_pages.xml')) {
//                $path = $_SESSION['config']['corepath'] . 'custom/' . $_SESSION['custom_override_id'] . '/modules/' . $value['moduleid'] . '/xml/actions_pages.xml';
//            } else if (file_exists('modules/' . $value['moduleid'] . '/xml/actions_pages.xml')) {
//                $path = 'modules/' . $value['moduleid'] . '/xml/actions_pages.xml';
//            } else {
//                $path = '';
//            }
//
//            if (!empty($path)) {
//                $xmlfile = simplexml_load_file($path);
//                if (count($xmlfile) > 0) {
//                    foreach ($xmlfile->ACTIONPAGE as $actionPage) {
//                        if (!defined((string) $actionPage->LABEL)) {
//                            $label = $actionPage->LABEL;
//                        } else {
//                            $label = constant((string) $actionPage->LABEL);
//                        }
//                        if (!empty((string) $actionPage->MODULE)) {
//                            $origin = (string) $actionPage->MODULE;
//                        } else {
//                            $origin =  'apps';
//                        }
//                        if (!empty((string) $actionPage->DESC)) {
//                            $desc = constant((string) $actionPage->DESC);
//                        } else {
//                            $desc =  'no description';
//                        }
//                        $tabActions_pages['modules'][] = ucfirst($origin);
//
//                        $tabActions_pages['actionsPageList'][] = array(
//                            'id'          => (string) $actionPage->ID,
//                            'label'       => $label,
//                            'name'        => (string) $actionPage->NAME,
//                            'desc'        => $desc,
//                            'origin'      => ucfirst($origin),
//                        );
//                    }
//                }
//            }
//        }
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
        $tabKeyword=[];
        $tabKeyword[] = ['value' => '', label => _NO_KEYWORD];
        $tabKeyword[] = ['value' => 'redirect', label => _REDIRECT, desc => _KEYWORD_REDIRECT_DESC];
        //$tabKeyword[] = ['value' => 'to_validate', label => _TO_VALIDATE];
        $tabKeyword[] = ['value' => 'indexing', label => _INDEXING, desc => _KEYWORD_INDEXING_DESC];
        //$tabKeyword[] = ['value' => 'workflow', label => _WF];

        return $tabKeyword;
    }
}

