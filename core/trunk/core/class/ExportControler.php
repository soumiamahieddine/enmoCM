<?php
/*
*    Copyright 2008,2009,2010 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the ExportControler Object
*
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup core
*/

//Loads the require class
try {
    require_once('core/class/class_functions.php');
    require_once('core/class/class_db.php');
    require_once('core/class/class_history.php');
    //require_once('');
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

class ExportControler
{
    private static $db;
    private static $db2;
    private static $db3;
    private static $coll;
    private static $functions;
    private static $return_loadXMLConf;

    function __construct()
    {
        $this->coll = $_SESSION['collection_id_choice'];
        $this->functions = new functions();
        $this->return_loadXMLConf = $this->functions->object2array($this->loadXMLConf());
        $this->db = new dbquery();
        $this->db->connect();
        $this->db2 = new dbquery();
        $this->db2->connect();
        $this->db3 = new dbquery();
        $this->db3->connect();
        $this->export();
    }

    private function export()
    {
        $return_createQuery = $this->createQuery();
        //echo $return_createQuery;exit;
        if (!$this->db->query($return_createQuery, true)) {
            $_SESSION['error'] = 'Erreur SQL 1';
        }
        $resultQuery[0] = $this->getHeaderOfCsv();
        $cpt = 1;
        while ($return_dbQuery = $this->db->fetch_object()) {
            foreach($return_dbQuery as $key => $value) {
                $return_dbQuery->$key = utf8_decode($return_dbQuery->$key);
            }

            $resultQuery[$cpt] = $this->functions->object2array($return_dbQuery);
            if (   isset($this->return_loadXMLConf[$this->coll]['FUNCTIONS']['COPIES'])
                && !empty($this->return_loadXMLConf[$this->coll]['FUNCTIONS']['COPIES'])) {
                $resultQuery[$cpt][$this->return_loadXMLConf[$this->coll]['FUNCTIONS']['COPIES']] = substr($this->functions_copies($return_dbQuery->res_id), 0, -2);
            }

            $resultQuery[$cpt]['commentaire'] = '';
            $cpt++;
        }
        $return_array2CSV = $this->array2CSV($resultQuery);
        $_SESSION['export']['filename'] = $return_array2CSV;
    }

    private function createQuery()
    {
        $query = 'SELECT ';
        for ($i=0; $i<count($this->return_loadXMLConf[$this->coll]['FIELD']); $i++) {
            $query .= $this->return_loadXMLConf[$this->coll]['FIELD'][$i]['DATABASE_FIELD'];
            if ($i <> (count($this->return_loadXMLConf[$this->coll]['FIELD']) - 1)) {
                $query .= ', ';
            }
        }

        $query .= ' '.substr($_SESSION['last_select_query'], stripos($_SESSION['last_select_query'], 'FROM'));

        return $query;
    }

    private function array2CSV($resultQuery)
    {
        do {
            $csvName = $_SESSION['user']['UserId'] . '-' . md5(date('Y-m-d H:i:s')) . '.csv';
            if (isset($pathToCsv) && !empty($pathToCsv)) {
                $csvName = $_SESSION['user']['UserId'] . '-' . md5($pathToCsv) . '.csv';
            }
            $pathToCsv = $_SESSION['config']['tmppath'] . $csvName;
        } while (file_exists($pathToCsv));

        $csvFile = fopen($pathToCsv, 'a+');

        foreach ($resultQuery as $fields) {
            fputcsv($csvFile, $fields, ';');
        }

        fclose($csvFile);

        return $csvName;
    }

    private function getHeaderOfCsv()
    {
        //echo '<pre>'.print_r($loadXMLConf, true).'</pre>';exit;
        for($i=0; $i < count($this->return_loadXMLConf[$this->coll]['FIELD']); $i++) {

            if (!empty($this->return_loadXMLConf[$this->coll]['FIELD'][$i]['LIBELLE'])) {
                $tabToReturn[$i] = $this->return_loadXMLConf[$this->coll]['FIELD'][$i]['LIBELLE'];
            } else {
                $tabToReturn[$i] = $this->return_loadXMLConf[$this->coll]['FIELD'][$i]['DATABASE_FIELD'];
            }
        }

        $temp = array_keys($this->return_loadXMLConf[$this->coll]['FUNCTIONS']);
        for ($k=0; $k<count($temp); $k++) {
            $j = $i+$k;
            $tabToReturn[$j] = $this->return_loadXMLConf[$this->coll]['FUNCTIONS'][$temp[$k]];
        }
        if ($k == 0) {
            $j = $i;
        }

        $tabToReturn[$j+1] = $this->return_loadXMLConf[$this->coll]['FIXE'];

        return $tabToReturn;
    }

    private function loadXMLConf()
    {
        $path = 'apps/maarch_entreprise/xml/export.xml';
        if (file_exists('custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/xml/export.xml')) {
            $path = 'custom/'.$_SESSION['custom_override_id'].'/apps/maarch_entreprise/xml/export.xml';
        }
        $exportConf = simplexml_load_file($path);
        return $exportConf;
    }

    private function functions_copies($res_id)
    {
        $return_functionsCopies = '';
        $queryListinstance = "SELECT item_id, item_type FROM listinstance WHERE res_id = ".$res_id." AND coll_id = '".$this->coll."' AND item_mode = 'cc'";
        if (!$this->db2->query($queryListinstance, true)) {
            $_SESSION['error'] = 'Erreur SQL 2';
        }
        $j = 0;
        while ($return_dbQueryListinstance = $this->db2->fetch_object()) {
            if ($return_dbQueryListinstance->item_type == 'user_id') {
                $queryUsersEntities = "SELECT entity_id FROM users_entities WHERE user_id = '".$return_dbQueryListinstance->item_id."' AND primary_entity = 'Y'";
                if (!$this->db3->query($queryUsersEntities, true)) {
                    $_SESSION['error'] = 'Erreur SQL 3';
                }
                while ($return_dbQueryUsersEntities = $this->db3->fetch_object()) {
                    $usersEntities = $return_dbQueryListinstance->item_id.' : '.$return_dbQueryUsersEntities->entity_id;
                }
            } else {
                $usersEntities = $return_dbQueryListinstance->item_id;
            }

            $return_functionsCopies .= $usersEntities . ' | ';
            $j++;
        }

        return $return_functionsCopies;
    }
}
