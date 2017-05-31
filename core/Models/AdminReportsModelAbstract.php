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

class AdminReportsModelAbstract extends \Apps_Table_Service
{


    public static function getList()
    {
        
        $aReturn = static::select([
            'select'    => ['*'],
            'table'     => ['usergroups'],
        ]);
       return $aReturn;
    }

public static function getUsers(array $aArgs = [])
    {
        $val = $this->group;
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::select([
            'select'    =>  ['*'],
            'table'     => ['usergroup-content'],
            'where'     => ['group_id = ?'],
            'data'      => [$val]
        ]);

        return $aReturn;

    }


    public static function getAllGroups()
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
                        'label' => (string)$value->LABEL,
                        'desc' => (string)$value->DESCRIPTION,
                        'url' => (string)$value->URL,
                        'in_menu_reports' =>(string)$value->IN_MENU_REPORTS,
                        'origin' => (string)$value->ORIGIN,
                        'module' => (string)$value->MODULE,
                        'module_label' => (string)$value->MODULE_LABEL,
                        'checked' => false
                         ];
                    $tab_id[] = $value->ID; // Array containing all possible values for report_id
                    }
                
             }
          
             $aReturn = static::select([
                  'select'    =>  ['*'],
                 'table'     => ['usergroups_reports'],
                 'where'     => ['report_id in (?)', 'group_id = ?'], 
                 'data'      => [$tab_id, $aArgs['id']]
                 ]);
        
             $tab_id_query = []; 
              foreach($aReturn as $rep ) { // First loop which stores the reports_id from the SQL query
                      $tab_id_query[] = $rep['report_id'];
                  }

    /*
     Loop testing each values of reports_id from the SQL query, if there is a match up then [checked] = true otherwise false
     */
            foreach($tab as $rep => $value) { // Checking match up and affecting boolean 
                    $tab[$rep]['checked'] = in_array($tab[$rep]['id'],$tab_id_query);
                }       
             return $tab;

        } else return ['error' => 'xml issue']; 
          


    }

    public static function getUserByGroupId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => ['*'],
            'table'     => ['usergroup_content'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::insertInto($aArgs, 'status');

        return $aReturn;
    }


    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

      
       $reps_by_id = [];
       $reps_by_id = $aArgs['data'];        
        var_dump($reps_by_id);               
        $tab_delete = [];
        $tab_update = [];
        $tab_id_args = [];

foreach($reps_by_id as $rep ) { //First loop which stores the report_id from the SQL query
            $tab_id_args[] = $rep['id'];
              }

             $aReturn = static::select([
            'select'    =>['*'],
            'table'     => ['usergroups_reports'],
            'where'     => ['group_id = ?','report_id in (?)'],
            'data'      => [$aArgs['id'], $tab_id_args]
            ]);

        $tabIdQuery = [];  
        foreach($aReturn as $rep ) { // First loop which stores the report_id from the SQL query
            $tab_id_query[] = $rep['report_id'];
              }
      /*Loop checking if there is a match up with the values from the SQL query
     */
         foreach($reps_by_id as $value) { //Checking if there is a match up with the values from $reps_by_id array and $tab_id_query,adding/deleting a line in the usergroups_reports table in the database
                 
              if(in_array($value['id'],$tab_id_query)) { 
                        if (!$value['checked']) {    // If the value is not checked ( = false) and in the array( i.e in the table usergroups_report) then we delete the line in the database
                            $tab_delete = [
                             'group_id' => $aArgs['id'],
                             'report_id' => $value['id']
                              ];
                             static::delete($tab_delete);
                        }

                   }else{
                        if ($value['checked']) { // If the value is checked ( = true) and not in the array( i.e in the table usergroups_report) then we add the line in the database
                             $tab_update = [
                             'group_id' => $aArgs['id'],
                             'report_id' => $value['id']                  
                              ];
                            static::insertInto($tab_update, 'usergroups_reports');                            
                        }                     
                     } 
                 }
                    $test_unitaire_true_only = static::select([
            'select'    =>['*'],
            'table'     => ['usergroups_reports'],
            'where'     => ['group_id = ?','report_id in (?)'],
            'data'      => [$aArgs['id'], $tab_id_args]
            ]);
        return $test_unitaire_true_only;
     
     
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
