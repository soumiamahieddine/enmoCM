<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class ActionsModelAbstract extends \Apps_Table_Service
{
    public static function getList()
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if(empty($aReturn[0])){
            return [];
        }

        $aReturn=$aReturn[0];
        $aReturn['category_id']=static::select([
            'select'    => 'category_id',
            'table'     => ['actions_categories'],
            'where'     => ['action_id = ?'],
            'data'      => [$aArgs['id']]
        ]);
       
        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        $tmp=$aArgs['category_id'];
        unset($aArgs['category_id']);
        $aReturn = static::insertInto($aArgs,'actions');
        $tab['action_id']=max(ActionsModel::getList())['id'];

        for($i=0;$i<count($aArgs['category_id']);$i++)
        {
            $tab['category_id']=$aArgs['category_id'][$i];
            $aInsert = static::insertInto($tab,'actions_categories');
        }

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        
        $aReturn = parent::update([
            'table'     => 'actions',
            'set'       => [
                'keyword' => $aArgs['keyword'],          
                'label_action' => $aArgs['label_action'],
                'id_status' => $aArgs['id_status'],
                'action_page' => $aArgs['action_page'],
                'history' => $aArgs['history'],
                'is_folder_action' => $aArgs['is_folder_action'],
                'history' => $aArgs['history']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        $aDelete = static::deleteFrom([
                'table' => 'actions_categories',
                'where' => ['action_id = ?'],
                'data'  => [$aArgs['id']]
            ]);

        $tab['action_id']=$aArgs['id'];

        for($i=0;$i<count($aArgs['category_id']);$i++){
            $tab['category_id']=$aArgs['category_id'][$i]['id'];
            $aInsert = static::insertInto($tab,'actions_categories');
        }

 
        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);

        $aReturn = static::deleteFrom([
                'table' => 'actions',
                'where' => ['id = ?'],
                'data'  => [$aArgs['id']]
            ]);
        $aDelete = static::deleteFrom([
                'table' => 'actions_categories',
                'where' => ['action_id = ?'],
                'data'  => [$aArgs['id']]
            ]);
        return $aReturn;
    }

    public static function getLettersBoxCategories(){
        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/config.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/config.xml';
        } else {
            $path = 'apps/maarch_entreprise/xml/config.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $categoriesTypes=[];
        $categories= $xmlfile->COLLECTION->categories;
        if (count($categories) > 0) {
            foreach ($categories->category as $category) {
               $categoriesTmp = ['id' => (string)$category->id, 'label'=> constant((string)$category->label)];

                if($category->id == (string)$categories->default_category){
                    $categoriesTmp['default_category']=true;

                } else {
                    $categoriesTmp['default_category']=false;
                }
                $categoriesTypes[]=$categoriesTmp;
            }
        }
        return $categoriesTypes;

    }

    public static function getAction_pages(){
        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/core/xml/actions_pages.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/core/xml/actions_pages.xml';
        } else {
            $path = 'core/xml/actions_pages.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $modules=[];
        if (count($xmlfile) > 0) {
            foreach ($xmlfile->ACTIONPAGE as $actions_pages) {
                if(!empty($actions_pages->MODULE)){
                    $modules[]=(string)$actions_pages->MODULE;
                }
            }
        }
        $modules=array_unique($modules);
        $tabActions_pages=[];
        $tabActions_pages['modules']=$modules;
        $tmp=[];
       
        foreach ($xmlfile->ACTIONPAGE as $actions_pages) {

            if(!defined((string)$actions_pages->LABEL)){
                $label=$actions_pages->LABEL;
            }
            else {
                $label=constant((string)$actions_pages->LABEL);
            }
            if(!empty($actions_pages->MODULE)){
                    $tmp[]=['name' => (string)$actions_pages->NAME,'label' => $label,'module' => (string)$actions_pages->MODULE];
            }
            else {
                    $tmp[]=['name' => (string)$actions_pages->NAME,'label' => $label,'module' => 'Apps'];

            }
        }
        array_unshift($tmp, ['name' => '_','label' => _NO_PAGE]);
        
        $tabActions_pages['actions']=$tmp;

        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/modules/avis/xml/actions_pages.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/modules/avis/xml/actions_pages.xml';
        } else {
            $path = 'modules/avis/xml/actions_pages.xml';
        }

        $xmlfile = simplexml_load_file($path);

        $act_avis=[];
       
        foreach ($xmlfile->ACTIONPAGE as $actions_pages) {

            if(!defined((string)$actions_pages->LABEL)){
                $label=$actions_pages->LABEL;
            }
            else {
                $label=constant((string)$actions_pages->LABEL);
            }
            if(!empty($actions_pages->MODULE)){
                    $act_avis[]=['name' => (string)$actions_pages->NAME,'label' => $label,'module' => (string)$actions_pages->MODULE];
            }
            else {
                    $act_avis[]=['name' => (string)$actions_pages->NAME,'label' => $label,'module' => 'Apps'];

            }
        }
        $tabActions_pages['actions']=array_merge($tmp, $act_avis);
        
        return $tabActions_pages;        
    }

        public static function getKeywords(){
            $tabKeyword=[];
            $tabKeyword[]=['value' => '', label => _NO_KEYWORD];
            $tabKeyword[]=['value' => 'redirect', label => _REDIRECT];
            $tabKeyword[]=['value' => 'to_validate', label => _TO_VALIDATE];
            $tabKeyword[]=['value' => 'indexing', label => _INDEXING];
            $tabKeyword[]=['value' => 'workflow', label => _WF];

            return $tabKeyword;
        }
}

