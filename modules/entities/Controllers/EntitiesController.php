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
use Core\Models\UserModel;
//use Entities\Models\EntitiesModel;

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

        

        $datas = [
            'docserver' => $obj,
        ];

        return $datas;
    }

    /**
     * Prepares storage on database.
     * @param  $data array
     * @param  $docserverId string
     * @param  $status string
     * @param  $fileFormat string
     * @return $data
     */
    public function prepareStorage($aArgs)
    {
        if (empty($aArgs['data'])) {

            return ['errors' => 'data ' . _EMPTY];
        }

        if (empty($aArgs['docserverId'])) {

            return ['errors' => 'docserverId ' . _EMPTY];
        }

        if (empty($aArgs['status'])) {

            return ['errors' => 'status ' . _EMPTY];
        }

        if (empty($aArgs['fileFormat'])) {

            return ['errors' => 'fileFormat ' . _EMPTY];
        }

        $statusFound = false;
        $typistFound = false;
        $typeIdFound = false;
        $toAddressFound = false;
        $userPrimaryEntity = false;
        $destinationFound = false;
        $initiatorFound = false;
        
        $data = $aArgs['data'];
        $docserverId = $aArgs['docserverId'];
        $status = $aArgs['status'];
        $fileFormat = $aArgs['fileFormat'];

        $userModel = new UserModel();
        //$entityModel = new EntityModel();

        $countD = count($data);
        for ($i=0;$i<$countD;$i++) {

            if (
                strtoupper($data[$i]['type']) == 'INTEGER' || 
                strtoupper($data[$i]['type']) == 'FLOAT'
            ) {
                if ($data[$i]['value'] == '') {
                    $data[$i]['value'] = '0';
                }
            }

            if (strtoupper($data[$i]['type']) == 'STRING') {
               $data[$i]['value'] = $data[$i]['value'];
               $data[$i]['value'] = str_replace(";", "", $data[$i]['value']);
               $data[$i]['value'] = str_replace("--", "", $data[$i]['value']);
            }

            if (strtoupper($data[$i]['column']) == strtoupper('status')) {
                $statusFound = true;
            }

            if (strtoupper($data[$i]['column']) == strtoupper('typist')) {
                $typistFound = true;
            }

            if (strtoupper($data[$i]['column']) == strtoupper('type_id')) {
                $typeIdFound = true;
            }

            if (strtoupper($data[$i]['column']) == strtoupper('custom_t10')) {
                $mail = array();
                $theString = str_replace(">", "", $data[$i]['value']);
                $mail = explode("<", $theString);
                
                $user = $userModel->getByEmail(['mail' => $mail[count($mail) -1]]);
                //print_r($user);
                $userIdFound = $user[0]['user_id'];
                print_r($userIdFound);
                
                if (!empty($userIdFound)) {
                    $toAddressFound = true;
                    $destUser = $userIdFound;

                    $queryUserEntity = "SELECT entity_id FROM users_entities WHERE primary_entity = 'Y' and user_id = ?";
                    $stmt = $db->query($queryUserEntity, array($destUser));
                    $userEntityId = $stmt->fetchObject();
                    
                    if (!empty($userEntityId->entity_id)) {
                        $userEntity = $userEntityId->entity_id;
                        $userPrimaryEntity = true;
                    }
                } else {
                    $queryEntity = "SELECT entity_id FROM entities WHERE email = ? and enabled = 'Y'";
                    $stmt = $db->query($queryEntity, array($mail[count($mail) -1]));
                    $entityIdFound = $stmt->fetchObject();
                    $userEntity = $entityIdFound->entity_id;

                    // if (!empty($userEntity)) {
                    //     $userPrimaryEntity = true;
                    // }
                }
            }
        }

        if (!$typistFound && !$toAddressFound) {
            array_push(
                $data,
                array(
                    'column' => 'typist',
                    'value' => 'auto',
                    'type' => 'string',
                )
            );
        }

        if (!$typeIdFound) {
            array_push(
                $data,
                array(
                    'column' => 'type_id',
                    'value' => '10',
                    'type' => 'string',
                )
            );
        }
        
        if (!$statusFound) {
            array_push(
                $data,
                array(
                    'column' => 'status',
                    'value' => $status,
                    'type' => 'string',
                )
            );
        }
        
        if ($toAddressFound) {
            array_push(
                $data,
                array(
                    'column' => 'dest_user',
                    'value' => $destUser,
                    'type' => 'string',
                )
            );
            array_push(
                $data,
                array(
                    'column' => 'typist',
                    'value' => $destUser,
                    'type' => 'string',
                )
            );
        }
        
        if ($userPrimaryEntity) {
            for ($i=0;$i<count($data);$i++) {
                if (strtoupper($data[$i]['column']) == strtoupper('destination')) {
                    if ($data[$i]['value'] == "") {
                        $data[$i]['value'] = $userEntity;
                    }
                    $destinationFound = true;
                    break;
                }
            }
            if (!$destinationFound) {
                array_push(
                    $data,
                    array(
                        'column' => 'destination',
                        'value' => $userEntity,
                        'type' => 'string',
                    )
                );
            }
        }
        
        if ($userPrimaryEntity) {
            for ($i=0;$i<count($data);$i++) {
                if (strtoupper($data[$i]['column']) == strtoupper('initiator')) {
                    if ($data[$i]['value'] == "") {
                        $data[$i]['value'] = $userEntity;
                    }
                    $initiatorFound = true;
                    break;
                }
            }
            if (!$initiatorFound) {
                array_push(
                    $data,
                    array(
                        'column' => 'initiator',
                        'value' => $userEntity,
                        'type' => 'string',
                    )
                );
            }
        }    
        array_push(
            $data,
            array(
                'column' => 'format',
                'value' => $fileFormat,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'offset_doc',
                'value' => '',
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'logical_adr',
                'value' => '',
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'docserver_id',
                'value' => $docserverId,
                'type' => 'string',
            )
        );

        return $data;
    }
}