<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Reports Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class ReportModelAbstract extends \Apps_Table_Service
{


    public static function getGroups() 
    {
        $aReturn = static::select([
            'select'    => ['*'],
            'table'     => ['usergroups'],
        ]);

        return $aReturn;
    }



    public static function  getReportsTypesByXML(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/entreprise.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/entreprise.xml';
        } else {
            $path = 'modules/reports/xml/reports.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $reportsTypes = [];
        $reportsTypes = $xmlfile->REPORT;
        $tab = [];
        $tab_id = [];
        

        if (count($reportsTypes) > 0) {
            foreach ($reportsTypes as $value) {
                if ($value->ENABLED == "true") {
                    $tab[] = [
                        'id' => (string)$value->ID,
                        'label' => constant((string)$value->LABEL),
                        'desc' => constant((string)$value->DESCRIPTION),
                        'url' => (string)$value->URL,
                        'in_menu_reports' =>(string)$value->IN_MENU_REPORTS,
                        'origin' => (string)$value->ORIGIN,
                        'module' => (string)$value->MODULE,
                        'module_label' => (string)$value->MODULE_LABEL,
                        'checked' => false
                    ];
                    $tab_id[] = $value->ID;
                }
            }
            
            $aReturn = static::select([
                'select'    =>  ['*'],
                'table'     => ['usergroups_reports'],
                'where'     => ['report_id in (?)', 'group_id = ?'], 
                'data'      => [$tab_id, $aArgs['id']]
            ]);
                
            $tab_id_query = []; 
            foreach($aReturn as $rep ) {
                $tab_id_query[] = $rep['report_id'];
            }
            foreach($tab as $rep => $value) { 
                $tab[$rep]['checked'] = in_array($tab[$rep]['id'],$tab_id_query);
            
            }       
            return $tab;

        } else {
            return ['error' => 'xml issue']; 
        }
    }

    public static function update(array $aArgs = []) 
    {    
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);
    
        $aReturn = static::select([
            'select'    => ['*'],
            'table'     => ['usergroups_reports'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        $alreadyCheckedReports = [];
            
        foreach($aReturn as $value) {
            $alreadyCheckedReports[] = $value['report_id'];
        }

        foreach($aArgs['data'] as $value) {
                        
            if (!empty($alreadyCheckedReports) && in_array($value['id'], $alreadyCheckedReports)) {
                if (!$value['checked']) {
                    $aReturn = static::deleteFrom([
                        'table' => 'usergroups_reports',
                        'where' => ['report_id = ?','group_id = ?'],
                        'data'  => [$value['id'],$aArgs['id']]
                    ]);
                }
            } elseif ($value['checked']) {
                static::insertInto(
                [
                    'group_id' => $aArgs['id'],
                    'report_id' => $value['id']                  
                ],
                    'usergroups_reports'
                );                            
            }                     
        }

        $checkedReports = static::select([
            'select'    => ['*'],
            'table'     => ['usergroups_reports'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $checkedReports;
    }

    

    public static function delete(array $aArgs = [])
    {
        /*static::checkRequired($aArgs, ['report_id']);
        static::checkString($aArgs, ['group_id']);*/
        $aReturn = static::deleteFrom([
            'table' => 'usergroups_reports',
            'where' => ['report_id = ?','group_id = ?'],
            'data'  => [$aArgs['report_id'],$aArgs['group_id']]
        ]);

        return $aReturn;
    }
}      
