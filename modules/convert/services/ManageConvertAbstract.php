<?php

/*
*   Copyright 2008-2016 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Manage convert class
*
* <ul>
* <li>Services to Manage the convertion of resources</li>
* </ul>
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup convert
*/

require_once 'core/services/Abstract.php';
require_once 'core/class/class_functions.php';
require_once 'core/class/class_db_pdo.php';
require_once 'core/class/class_db.php';
require_once 'core/docservers_tools.php';
require_once 'core/class/docservers_controler.php';
require_once 'core/services/ManageDocservers.php';
require_once 'modules/convert/services/ProcessConvert.php';
require_once 'modules/convert/services/ProcessThumbnails.php';
require_once 'modules/convert/services/ProcessFulltext.php';

class Convert_ManageConvertAbstract_Service extends Core_Abstract_Service {

    /**
     * Ask for conversion in all mode
     *
     * @param string $collId collection
     * @param string $resTable resource table
     * @param string $adrTable adr table
     * @param long $resId res_id
     * @param string $tmpDir path to tmp
     * @throws Exception Check des valeurs d'entrées
     * @return array $returnArray the result
     */
    public function convertAll(array $args=[])
    {
        $timestart = microtime(true);
        // Prés-requis :
        $this->checkRequired($args, ['collId','resTable','adrTable','resId',]);
        $this->checkNumeric($args, ['resId',]);
        $this->checkString($args, ['collId','resTable','adrTable',]);

        // Variabilisation :
        $returnArray = array();
        $collId      = $args['collId'];
        $resTable    = $args['resTable'];
        $adrTable    = $args['adrTable'];
        $resId       = $args['resId'];
        

        if (!isset($args['tmpDir']) || $args['tmpDir'] == '') {
            $tmpDir = $_SESSION['config']['tmppath'];
        } else {
            $tmpDir = $args['tmpDir'];
        }

        $path_to_lucene = '';
        if (isset($args['path_to_lucene']) && !empty($args['path_to_lucene'])){
            $path_to_lucene = $args['path_to_lucene'];
        }

        $params = array(
            'collId'         => $collId, 
            'resTable'       => $resTable, 
            'adrTable'       => $adrTable, 
            'resId'          => $resId,
            'tmpDir'         => $tmpDir,
            'path_to_lucene' => $path_to_lucene
        );

        //CONV
        $ProcessConvertService = new Convert_ProcessConvert_Service();
        $resultOfConversion = $ProcessConvertService->convert($params);
        
        if ($resultOfConversion['status'] <> '0') {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'CONV:' . $resultOfConversion['error'],
            );
            return $returnArray;
        }

        //TNL
        $ProcessConvertService = new Convert_ProcessThumbnails_Service();
        $resultOfConversion = $ProcessConvertService->thumbnails($params);
        if ($resultOfConversion['status'] <> '0') {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'TNL:' . $resultOfConversion['error'],
            );
            return $returnArray;
        }

        //FULLTEXT
        if (!empty($args['zendIndex'])) {
            $zendIndex = $args['zendIndex'];
            $params = array(
                'collId'         => $collId, 
                'resTable'       => $resTable, 
                'adrTable'       => $adrTable, 
                'resId'          => $resId,
                'tmpDir'         => $tmpDir,
                'path_to_lucene' => $path_to_lucene,
                'zendIndex'      => $zendIndex
            );
        } else {
            $params = array(
                'collId'         => $collId, 
                'resTable'       => $resTable, 
                'adrTable'       => $adrTable, 
                'resId'          => $resId,
                'tmpDir'         => $tmpDir,
                'path_to_lucene' => $path_to_lucene
            );
        }
        $ProcessConvertService = new Convert_ProcessFulltext_Service();
        $resultOfConversion = $ProcessConvertService->fulltext($params);
        if ($resultOfConversion['status'] <> '0') {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'TXT:' . $resultOfConversion['error'],
            );
            Core_Logs_Service::executionTimeLog($timestart, '', 'debug', '[TIMER] Convert_ManageConvertAbstract_Service::convertAll aucun contenu a indexer dans fulltext');
            return $returnArray;
        }
        
        $returnArray = array(
            'status' => '0',
            'value' => '',
            'error' => '',
        );
        Core_Logs_Service::executionTimeLog($timestart, '', 'debug', '[TIMER] Convert_ManageConvertAbstract_Service::convertAll');
        return $returnArray;
    }

}
