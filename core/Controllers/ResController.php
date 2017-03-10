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
            //print_r($aArgs['data']);exit;
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


        $func = new functions();
        
        $queryExtFields = '(';
        $queryExtValues = '(';
        $queryExtValuesFinal = '(';
        $parameters = array();
        $db = new Database();
        $findProcessLimitDate = false;
        $findProcessNotes = false;
        $delayProcessNotes = 0;

        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['column'] == 'process_limit_date') {
                $findProcessLimitDate = true;
            }
            if ($data[$i]['column'] == 'process_notes') {
                $findProcessNotes = true;
                $donnees = explode(',', $data[$i]['value']);
                $delayProcessNotes = $donnees['0'];
                $calendarType = $donnees['1'];
            }
        }

        if ($table == 'mlb_coll_ext') {
            if ($delayProcessNotes > 0) {
                $processLimitDate = $this->retrieveProcessLimitDate(
                    $resId,
                    $delayProcessNotes,
                    $calendarType
                );
            } else {
                $processLimitDate = $this->retrieveProcessLimitDate($resId);
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

        for ($i = 0; $i < count($data); $i++) {
            if (strtoupper($data[$i]['type']) == 'INTEGER' || 
                strtoupper($data[$i]['type']) == 'FLOAT') {
                if ($data[$i]['value'] == '') {
                    $data[$i]['value'] = '0';
                }
                $data[$i]['value'] = str_replace(',', '.', $data[$i]['value']);
            }
            if (strtoupper($data[$i]['column']) == strtoupper('category_id')) {
                $categoryId = $data[$i]['value'];
            }
            if (strtoupper($data[$i]['column']) == strtoupper('alt_identifier') && 
                $data[$i]['value'] == "") {
                require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
                    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_chrono.php';
                $chronoX = new chrono();
                for ($iColl=0; $iColl<=count($_SESSION['collections']); $iColl++) {
                    if ($_SESSION['collections'][$iColl]['extensions'][0] == $table) {
                        $resViewTable = $_SESSION['collections'][$iColl]['view'];
                        break;
                    }
                }
                $stmt = $db->query("SELECT destination, type_id FROM " . $resViewTable
                    . " WHERE res_id = ?", array($resId));
                $resView = $stmt->fetchObject();
                $myVars = array(
                    'entity_id' => $resView->destination,
                    'type_id' => $resView->type_id,
                    'category_id' => $categoryId,
                    'folder_id' => "",
                );
                $myChrono = $chronoX->generate_chrono($categoryId, $myVars, 'false');
                $data[$i]['value'] = $myChrono;
            }
            if (strtoupper($data[$i]['column']) == strtoupper('exp_contact_id') &&
                $data[$i]['value'] <> "" && !is_numeric($data[$i]['value'])) {
                $theString = str_replace(">", "", $data[$i]['value']);
                $mail = explode("<", $theString);
                $stmt = $db->query("SELECT contact_id FROM view_contacts WHERE email = ? "
                    . " and enabled = 'Y' order by creation_date asc", array($mail[count($mail) -1]));
                $contact = $stmt->fetchObject();

                if ($contact->contact_id <> "") {
                    $data[$i]['value'] = $contact->contact_id;
                } else {
                    $data[$i]['value'] = 0;
                }
            }
            if (strtoupper($data[$i]['column']) == strtoupper('address_id') &&
                $data[$i]['value'] <> "" && !is_numeric($data[$i]['value'])) {
                $theString = str_replace(">", "", $data[$i]['value']);
                $mail = explode("<", $theString);
                $stmt = $db->query("SELECT ca_id FROM view_contacts WHERE email = ? "
                    . " and enabled = 'Y' order by creation_date asc", array($mail[count($mail) -1]));
                $contact = $stmt->fetchObject();
                if ($contact->ca_id <> "") {
                    $data[$i]['value'] = $contact->ca_id;
                } else {
                    $data[$i]['value'] = 0;
                }
            }
            //COLUMN
            $data[$i]['column'] = strtolower($data[$i]['column']);
            $queryExtFields .= $data[$i]['column'] . ',';
            //VALUE
            if ($data[$i]['type'] == 'string' || $data[$i]['type'] == 'date') {
                $queryExtValues .= "'" . $data[$i]['value'] . "',";
            } else {
                $queryExtValues .= $data[$i]['value'] . ",";
            }
            $parameters[] = $data[$i]['value'];
            $queryExtValuesFinal .= "?,";
        }
        $queryExtFields = preg_replace('/,$/', ',res_id)', $queryExtFields);
        $queryExtValues = preg_replace(
            '/,$/',
            ',' . $resId . ')',
            $queryExtValues
        );
        $queryExtValuesFinal = preg_replace(
            '/,$/',
            ',' . $resId . ')',
            $queryExtValuesFinal
        );

        $queryExt = " insert into " . $table . " " . $queryExtFields
                . ' values ' . $queryExtValuesFinal ;

        $returnCode = 0;
        if ($db->query($queryExt, $parameters)) {
            $returnResArray = array(
                'returnCode' => (int) 0,
                'resId' => $resId,
                'error' => '',
            );
        } else {
            $returnResArray = array(
                'returnCode' => (int) -2,
                'resId' => '',
                'error' => 'Pb with SQL insertion',
            );
        }
        return $returnResArray;
        
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
}
