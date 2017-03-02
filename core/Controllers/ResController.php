<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\ResModel;

class ResController
{

    /**
     * Store resource on database.
     * @param  $resTable  string 
     * @param  $destinationDir string
     * @param  $pathTemplate  string 
     * @param  $docserverId  string 
     * @param  $data  array  
     * @return res_id
     */
    public function storeResourceOnDB($aArgs)
    {

        // storeResult['destination_dir'],
        // $storeResult['file_destination_name'] ,
        // $storeResult['path_template'],
        // $storeResult['docserver_id'], $_SESSION['data'],
        // $_SESSION['config']['databasetype']
        if (empty($aArgs['resTable'])) {

            return ['errors' => 'resTable ' . _EMPTY];
        }

        if (empty($aArgs['destinationDir'])) {

            return ['errors' => 'destinationDir ' . _EMPTY];
        }

        if (empty($aArgs['pathTemplate'])) {

            return ['errors' => 'pathTemplate ' . _EMPTY];
        }

        if (empty($aArgs['docserverId'])) {

            return ['errors' => 'docserverId ' . _EMPTY];
        }

        if (empty($aArgs['data'])) {

            return ['errors' => 'data ' . _EMPTY];
        }

        if (isset($aArgs['collId'])) {
            $collId = $aArgs['collId'];
            $obj = DocserverModel::getDocserverToInsert([
                'collId' => $collId
            ]);
        } else {

            return ['errors' => 'collId ' . _EMPTY];
        }

        $datas = [
            'docserver' => $obj,
        ];

        return $datas;
    }
}