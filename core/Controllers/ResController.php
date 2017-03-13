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
use Core\Models\ResModel;
use Entities\Models\EntitiesModel;
use Core\Controllers\DocserverController;

class ResController
{
    public function create(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!empty($aArgs)) {
            $aArgs = $aArgs;
        } else {
            $aArgs = $request->getQueryParams();
            //print_r($aArgs['data']);
            $aArgs['data'] = json_decode($aArgs['data']);
            $aArgs['data'] = $this->object2array($aArgs['data']);
        }

        $return = $this->storeResource($aArgs);

        if ($return['errors']) {
            return $response
                ->withStatus(500)
                ->withJson(
                    ['errors' => _NOT_CREATE . ' ' . $return['errors']]
                );
        }
        
        return $response->withJson($return);
    }

    /**
     * Store resource on database.
     * @param  $encodedFile  string
     * @param  $data array
     * @param  $collId  string
     * @param  $table  string
     * @param  $fileFormat  string
     * @param  $status  string
     * @return res_id
     */
    public function storeResource($aArgs)
    {
        if (empty($aArgs['encodedFile'])) {
            return ['errors' => 'encodedFile ' . _EMPTY];
        }

        if (empty($aArgs['data'])) {
            return ['errors' => 'data ' . _EMPTY];
        }

        if (empty($aArgs['collId'])) {
            return ['errors' => 'collId ' . _EMPTY];
        }

        if (empty($aArgs['table'])) {
            return ['errors' => 'table ' . _EMPTY];
        }

        if (empty($aArgs['fileFormat'])) {
            return ['errors' => 'fileFormat ' . _EMPTY];
        }

        if (empty($aArgs['status'])) {
            return ['errors' => 'status ' . _EMPTY];
        }
        $encodedFile = $aArgs['encodedFile'];
        $data = $aArgs['data'];
        $collId = $aArgs['collId'];
        $table = $aArgs['table'];
        $fileFormat = $aArgs['fileFormat'];
        $status = $aArgs['status'];

        try {
            $count = count($data);
            for ($i = 0; $i < $count; $i++) {
                $data[$i]['column'] = strtolower($data[$i]['column']);
            }
            
            $returnCode = 0;
            //copy sended file on tmp
            $fileContent = base64_decode($encodedFile);
            $random = rand();
            $fileName = 'tmp_file_' . $random . '.' . $fileFormat;
            $Fnm = $_SESSION['config']['tmppath'] . $fileName;
            $inF = fopen($Fnm, "w");
            fwrite($inF, $fileContent);
            fclose($inF);

            //store resource on docserver
            $ds = new DocserverController();
            $aArgs = [
                'collId' => $collId,
                'fileInfos' =>
                    [
                        'tmpDir'        => $_SESSION['config']['tmppath'],
                        'size'          => filesize($Fnm),
                        'format'        => $fileFormat,
                        'tmpFileName'   => $fileName,
                    ]
            ];
            
            $storeResult = array();
            $storeResult = $ds->storeResourceOnDocserver($aArgs);
            
            if (!empty($storeResult['errors'])) {
                return ['errors' => $storeResult['errors']];
            }

            //store resource metadata in database
            $aArgs = [
                'data'        => $data,
                'docserverId' => $storeResult['docserver_id'],
                'status'      => $status,
                'fileFormat'  => $fileFormat,
            ];
            
            $data = $this->prepareStorage($aArgs);
            
            unlink($Fnm);
            
            require_once 'core/class/class_resource.php';
            $resource = new \resource();
            $resId = $resource->load_into_db(
                $table,
                $storeResult['destination_dir'],
                $storeResult['file_destination_name'],
                $storeResult['path_template'],
                $storeResult['docserver_id'],
                $data,
                $_SESSION['config']['databasetype'],
                true
            );

            if (!is_numeric($resId)) {
                return ['errors' => 'Pb with SQL insertion : ' .$resId];
            }

            if ($resId == 0) {
                $resId = '';
            }

            return [$resId];
        } catch (Exception $e) {
            return ['errors' => 'unknown error' . $e->getMessage()];
        }
    }

    /**
     * Prepares storage on database.
     * @param  $data array
     * @param  $docserverId string
     * @param  $status string
     * @param  $fileFormat string
     * @return array $data
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
        $entityModel = new \Entities\Models\EntitiesModel();

        $countD = count($data);
        for ($i = 0; $i < $countD; $i++) {
            if (strtoupper($data[$i]['type']) == 'INTEGER' ||
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
                $userIdFound = $user[0]['user_id'];
                if (!empty($userIdFound)) {
                    $toAddressFound = true;
                    $destUser = $userIdFound;
                    $entity = $entityModel->getByUserId(['user_id' => $destUser]);
                    if (!empty($entity[0]['entity_id'])) {
                        $userEntity = $entity[0]['entity_id'];
                        $userPrimaryEntity = true;
                    }
                } else {
                    $entity = $entityModel->getByEmail(['email' => $mail[count($mail) -1]]);
                    if (!empty($entity[0]['entity_id'])) {
                        $userPrimaryEntity = true;
                    }
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
            for ($i = 0; $i < count($data); $i++) {
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
            for ($i = 0; $i<count($data); $i++) {
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

    /**
     * Store ext resource on database.
     * @param  $resId  integer
     * @param  $data array
     * @param  $table  string
     * @return res_id
     */
    public function storeExtResource($aArgs, $resId, $data, $table)
    {
        if (empty($aArgs['resId'])) {
            return ['errors' => 'resId ' . _EMPTY];
        }

        if (empty($aArgs['data'])) {
            return ['errors' => 'data ' . _EMPTY];
        }

        if (empty($aArgs['table'])) {
            return ['errors' => 'table ' . _EMPTY];
        }

        if (empty($aArgs['resTable'])) {
            return ['errors' => 'resTable ' . _EMPTY];
        }

        $resDetails = ResModel::getById([
            'resId'  => $aArgs['resId'],
            'table'  => $aArgs['resTable'],
            'select' => ['res_id']
        ]);
        
        if (empty($resDetails[0]['res_id'])) {
            return ['errors' => 'res_id ' . _OF . ' ' . $aArgs['resTable'] . ' ' . _NOT_EXISTS];
        }

        $resDetails = ResModel::getById([
            'resId' => $aArgs['resId'],
            'table' => $aArgs['table'],
            'select' => ['res_id']
        ]);
        
        if ($resDetails[0]['res_id'] > 0) {
            return ['errors' => 'res_id ' . _OF . ' '  . $aArgs['table'] . ' ' . _EXISTS];
        }
        
        $prepareData = $this->prepareStorageExt($aArgs);
        
        $resExtInsert = ResModel::create([
            'table' => 'mlb_coll_ext',
            'data'  => $prepareData
        ]);

        return true;
    }

    /**
    * Convert an object to an array
    * @param  $object object to convert
    */
    private function object2array($object)
    {
        $return = null;
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                $return[$key] = $this->object2array($value);
            }
        } else {
            if (is_object($object)) {
                $var = get_object_vars($object);
                if ($var) {
                    foreach ($var as $key => $value) {
                        $return[$key] = ($key && !$value) ? null : $this->object2array($value);
                    }
                } else {
                    return $object;
                }
            } else {
                return $object;
            }
        }
        return $return;
    }

    /**
     * Prepares storage on database for resExt.
     * @param  $data array
     * @return array $data
     */
    public function prepareStorageExt($aArgs)
    {
        if (empty($aArgs['resId'])) {
            return ['errors' => 'resId ' . _EMPTY];
        }
        if (empty($aArgs['data'])) {
            return ['errors' => 'data ' . _EMPTY];
        }
        if (empty($aArgs['table'])) {
            return ['errors' => 'table ' . _EMPTY];
        }
        $queryExtFields = '(';
        $queryExtValues = '(';
        $queryExtValuesFinal = '(';
        $parameters = array();
        $findProcessLimitDate = false;
        $findProcessNotes = false;
        $delayProcessNotes = 0;

        $resId = $aArgs['resId'];
        $table = $aArgs['table'];
        $data = $aArgs['data'];
        $countD = count($data);
        for ($i = 0; $i < $countD; $i++) {
            if ($data[$i]['column'] == 'process_limit_date') {
                $findProcessLimitDate = true;
            }
            if ($data[$i]['column'] == 'process_notes') {
                $findProcessNotes = true;
                $don = explode(',', $data[$i]['value']);
                $delayProcessNotes = $don['0'];
                $calendarType = $don['1'];
            }
        }

        if ($table == 'mlb_coll_ext') {
            if ($delayProcessNotes > 0) {
                $processLimitDate = ResModel::retrieveProcessLimitDate([
                    'resId' => $resId,
                    'delayProcessNotes' => $delayProcessNotes,
                    'calendarType' => $calendarType,
                ]);
            } else {
                $processLimitDate = ResModel::retrieveProcessLimitDate([
                    'resId' => $resId
                ]);
            }
        }

        if (!$findProcessLimitDate && $processLimitDate <> '') {
            array_push(
                $data,
                array(
                'column' => 'process_limit_date',
                'value' => $processLimitDate,
                'type' => 'date',
                )
            );
        }
        require_once 'apps/maarch_entreprise/class/class_chrono.php';
        $chronoX = new \chrono();
        require_once 'apps/maarch_entreprise/Models/ContactsModel.php';
        $contacts = new \ContactsModel();
        for ($i=0; $i<count($data); $i++) {
            if (strtoupper($data[$i]['type']) == 'INTEGER' ||
                strtoupper($data[$i]['type']) == 'FLOAT'
            ) {
                if ($data[$i]['value'] == '') {
                    $data[$i]['value'] = '0';
                }
                $data[$i]['value'] = str_replace(',', '.', $data[$i]['value']);
            }
            if (strtoupper($data[$i]['column']) == strtoupper('category_id')) {
                $categoryId = $data[$i]['value'];
            }
            if (strtoupper($data[$i]['column']) == strtoupper('alt_identifier') &&
                $data[$i]['value'] == ""
            ) {
                if ($table == 'mlb_coll_ext') {
                    $resDetails = ResModel::getById([
                        'resId' => $resId,
                        'table' => 'res_letterbox',
                        'select' => ['destination, type_id']
                    ]);
                    $myVars = array(
                        'entity_id' => $resDetails[0]['destination'],
                        'type_id' => $resDetails[0]['type_id'],
                        'category_id' => $categoryId,
                        'folder_id' => "",
                    );
                    $myChrono = $chronoX->generate_chrono($categoryId, $myVars, 'false');
                    $data[$i]['value'] = $myChrono;
                }
            }
            if (strtoupper($data[$i]['column']) == strtoupper('exp_contact_id') &&
                $data[$i]['value'] <> "" &&
                !is_numeric($data[$i]['value'])
            ) {
                $theString = str_replace(">", "", $data[$i]['value']);
                $mail = explode("<", $theString);
                $contactDetails =$contacts->getByEmail([
                    'email' => $mail[count($mail) -1],
                    'select' => ['contact_id']
                ]);
                if ($contactDetails[0]['contact_id'] <> "") {
                    $data[$i]['value'] = $contactDetails[0]['contact_id'];
                } else {
                    $data[$i]['value'] = 0;
                }
                $data[$i]['type'] = 'integer';
            }
            if (strtoupper($data[$i]['column']) == strtoupper('address_id') &&
                $data[$i]['value'] <> "" &&
                !is_numeric($data[$i]['value'])
            ) {
                $theString = str_replace(">", "", $data[$i]['value']);
                $mail = explode("<", $theString);
                $contactDetails =$contacts->getByEmail([
                    'email' => $mail[count($mail) -1],
                    'select' => ['ca_id']
                ]);
                if ($contactDetails[0]['ca_id'] <> "") {
                    $data[$i]['value'] = $contactDetails[0]['ca_id'];
                } else {
                    $data[$i]['value'] = 0;
                }
                $data[$i]['type'] = 'integer';
            }
            //COLUMN
            $data[$i]['column'] = strtolower($data[$i]['column']);
            //VALUE
            $parameters[$data[$i]['column']] = $data[$i]['value'];
        }
        $parameters['res_id'] = $resId;

        return $parameters;
    }
}
