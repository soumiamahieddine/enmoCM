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

use Core\Models\StatusModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\ResModel;
use Notes\Models\NoteModel;

class ResController
{
    //*****************************************************************************************
    //LOG ONLY LOG FOR DEBUG
    // $file = fopen('storeResourceLogs.log', a);
    // fwrite($file, '[' . date('Y-m-d H:i:s') . '] new request' . PHP_EOL);
    // foreach ($data as $key => $value) {
    //     if ($key <> 'encodedFile') {
    //         fwrite($file, '[' . date('Y-m-d H:i:s') . '] ' . $key . ' : ' . $value . PHP_EOL);
    //     }
    // }
    // fclose($file);
    // ob_flush();
    // ob_start();
    // print_r($data);
    // file_put_contents("storeResourceLogs.log", ob_get_flush());
    //END LOG FOR DEBUG ONLY
    //*****************************************************************************************
    public function create(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        $check = Validator::notEmpty()->validate($data['encodedFile']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['fileFormat']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['status']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['collId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['table']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['data']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $resId = StoreController::storeResource($data);

        if (empty($resId) || !empty($resId['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => '[ResController create] ' . $resId['errors']]);
        }

        return $response->withJson(['resId' => $resId]);
    }

    public function createExt(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        $check = Validator::intVal()->notEmpty()->validate($data['resId']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['data']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $document = ResModel::getById(['resId' => $data['resId'], 'select' => ['1']]);
        if (empty($document)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document does not exist']);
        }
        $documentExt = ResModel::getExtById(['resId' => $data['resId'], 'select' => ['1']]);
        if (!empty($documentExt)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document already exists in mlb_coll_ext']);
        }

        $formatedData = StoreController::prepareExtStorage(['resId' => $data['resId'], 'data' => $data['data']]);

        $check = Validator::stringType()->notEmpty()->validate($formatedData['category_id']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        ResModel::createExt($formatedData);

        return $response->withJson(['resId' => $data['resId']]);
    }

    public function updateStatus(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $data = $request->getParams();

        if (empty($data['status'])) {
            $data['status'] = 'COU';
        }
        if (empty(StatusModel::getById(['id' => $data['status']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Status not found']);
        }
        if (empty($data['historyMessage'])) {
            $data['historyMessage'] = _UPDATE_STATUS;
        }

        $check = Validator::stringType()->notEmpty()->validate($data['status']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['historyMessage']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $document = ResModel::getById(['resId' => $aArgs['resId']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document not found']);
        }

        ResModel::updateStatus(['resId' => $aArgs['resId'], 'status' => $data['status']]);

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $aArgs['resId'],
            'eventType' => 'UP',
            'info'      => $data['historyMessage'],
            'moduleId'  => 'apps',
            'eventId'   => 'resup',
        ]);

        return $response->withJson(['success' => true]);
    }

    public function isLock(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        return $response->withJson(ResModel::isLockForCurrentUser(['resId' => $aArgs['resId']]));
    }

    public function getNotesCountForCurrentUserById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        return $response->withJson(NoteModel::countForCurrentUserByResId(['resId' => $aArgs['resId']]));
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if(empty($aArgs)){
            $aArgs = $request->getQueryParams();
            $aArgs['data'] = json_decode($aArgs['data']);
            $aArgs['data'] = $this->object2array($aArgs['data']);
        }

        $return = $this->updateResource($aArgs);

        if ($return) {
            $id = $aArgs['res_id'];
            $obj = ResModel::getById([
                'resId' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    public function updateResource($aArgs)
    {
        $data = $aArgs['data'];
        $prepareData = [];
        $countD = count($data);
        for ($i = 0; $i < $countD; $i++) {
            //COLUMN
            $data[$i]['column'] = strtolower($data[$i]['column']);
            //VALUE
            $prepareData[$data[$i]['column']] = $data[$i]['value'];
        }

        //print_r($prepareData);exit;
        $aArgs['data'] = $prepareData;

        $errors = [];

        $return = ResModel::update($aArgs);

        if ($return) {
            $id = $aArgs['res_id'];
            $obj = ResModel::getById([
                'resId' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        $datas = [
            $obj,
        ];

        return $datas;
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


    

    public function getListDocs(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $clause = $aArgs['clause'];
        $clause_elem = explode("&",$clause);

        $tab_where = array();
        foreach ($clause_elem as $elem) {
            $tmp = explode("=",$elem);
            $column = $tmp[0];
            $values = explode(",",$tmp[1]);
            $tmp_values = array();
            foreach ($values as $v) {
                if (!empty($v)){
                    if ($column == "date_begin"){
                        $v_date = explode("-",$v);
                        array_push($tmp_values, "creation_date >= '".$v_date[2]."-".$v_date[1]."-".$v_date[0]."'");
                    }
                    else if ($column == "date_end"){
                        $v_date = explode("-",$v);
                        array_push($tmp_values, "creation_date <= '".$v_date[2]."-".$v_date[1]."-".$v_date[0]." 23:59:59'");
                    }
                    else if ($column == "type_id"){
                        array_push($tmp_values, "type_id = '".trim($v)."'");
                    }
                    else
                        array_push($tmp_values, $column."='".trim($v)."'");
                }
            }
            if (count($tmp_values) > 0) array_push($tab_where, "(".implode(" OR ", $tmp_values).")");
        }

        $clause = implode(" AND ", $tab_where);
        if (empty($clause)) $clause = ' 1=1 ';

        $colSelect = $aArgs['select'];
        $select_elem = explode(",",$colSelect);
        $tab_tables = array();

        foreach ($select_elem as $col) {
            $c_elem=explode(".",$col);
            if (!in_array($c_elem[0], $tab_tables)){
                //ajout de la table
                array_push($tab_tables,$c_elem[0]);

                //ajout de la jointure
                if ($c_elem[0] == "mlb_coll_ext")
                    $clause .= " AND res_letterbox.res_id = mlb_coll_ext.res_id ";
                elseif ($c_elem[0] == "doctypes")
                    $clause .= " AND res_letterbox.type_id = doctypes.type_id ";
                elseif ($c_elem[0] == "entities")
                    $clause .= " AND res_letterbox.destination=entities.entity_id ";
            }
        }

        $result = array();        
        $resList = ResModel::getDocsByClause(
            [
                'select'  => [$colSelect],
                //'table'  => implode(",",$tab_tables),
                'clause'   => $clause
            ]
        );

        foreach ($resList as $doc) {
            $result_infos = array();
            foreach ($doc as $key => $value) {
                if (empty($value)) $result_infos[$key] = '';
                elseif ($key=='creation_date' || ($key=='closing_date' && !empty($value)) || ($key=='process_limit_date' && !empty($value)) || ($key=='admission_date' && !empty($value))) {
                    $result_infos[$key] = str_replace("-","/",\functions::format_date_db($value, false, '', false));
                }
                else $result_infos[$key] = $value;
            }
            array_push($result,$result_infos);            
        }
        return $response->withJson(['docs' => $result, 'nb_docs' => count($resList)]);
    }
}
