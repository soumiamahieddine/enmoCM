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
*/

namespace Resource\controllers;

use Basket\models\BasketModel;
use Note\models\NoteModel;
use SrcCore\controllers\StoreController;
use Group\models\ServiceModel;
use Status\models\StatusModel;
use SrcCore\models\ValidatorModel;
use History\controllers\HistoryController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserModel;

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
    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'index_mlb', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

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

    public function createExt(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'index_mlb', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

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

        return $response->withJson(['status' => true]);
    }

    public function updateStatus(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (empty($data['status'])) {
            $data['status'] = 'COU';
        }
        if (empty(StatusModel::getById(['id' => $data['status']]))) {
            return $response->withStatus(400)->withJson(['errors' => _STATUS_NOT_FOUND]);
        }
        if (empty($data['historyMessage'])) {
            $data['historyMessage'] = _UPDATE_STATUS;
        }

        $check = Validator::stringType()->notEmpty()->validate($data['chrono']) || Validator::intVal()->notEmpty()->validate($data['resId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['status']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['historyMessage']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!empty($data['chrono'])) {
            $document = ResModel::getResIdByAltIdentifier(['altIdentifier' => $data['chrono']]);
        } else {
            $document = ResModel::getById(['resId' => $data['resId'], 'select' => ['res_id']]);
        }
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => _DOCUMENT_NOT_FOUND]);
        }
        if (!ResController::hasRightByResId(['resId' => $document['res_id'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        ResModel::update(['set' => ['status' => $data['status']], 'where' => ['res_id = ?'], 'data' => [$document['res_id']]]);

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $document['res_id'],
            'eventType' => 'UP',
            'info'      => $data['historyMessage'],
            'moduleId'  => 'apps',
            'eventId'   => 'resup',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function isLock(Request $request, Response $response, array $aArgs)
    {
        return $response->withJson(ResModel::isLock(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']]));
    }

    public function getNotesCountForCurrentUserById(Request $request, Response $response, array $aArgs)
    {
        return $response->withJson(NoteModel::countByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']]));
    }

    public static function hasRightByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'userId']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        if ($aArgs['userId'] == 'superadmin') {
            return true;
        }
        $groups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        $groupsClause = '';
        foreach ($groups as $key => $group) {
            if (!empty($group['where_clause'])) {
                $groupClause = PreparedClauseController::getPreparedClause(['clause' => $group['where_clause'], 'userId' => $aArgs['userId']]);
                if ($key > 0) {
                    $groupsClause .= ' or ';
                }
                $groupsClause .= "({$groupClause})";
            }
        }

        if (!empty($groupsClause)) {
            $res = ResModel::getOnView(['select' => [1], 'where' => ['res_id = ?', "({$groupsClause})"], 'data' => [$aArgs['resId']]]);
            if (!empty($res)) {
                return true;
            }
        }

        $baskets = BasketModel::getBasketsByUserId(['userId' => $aArgs['userId'], 'unneededBasketId' => ['IndexingBasket']]);
        $basketsClause = '';
        foreach ($baskets as $key => $basket) {
            if (!empty($basket['basket_clause'])) {
                $basketClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'userId' => $aArgs['userId']]);
                if ($key > 0) {
                    $basketsClause .= ' or ';
                }
                $basketsClause .= "({$basketClause})";
            }
        }

        if (!empty($basketsClause)) {
            $res = ResModel::getOnView(['select' => [1], 'where' => ['res_id = ?', "({$basketsClause})"], 'data' => [$aArgs['resId']]]);
            if (!empty($res)) {
                return true;
            }
        }

        return false;
    }


    //TODO REFACTORING
    public function getListDocs(Request $request, Response $response, array $aArgs)
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

        $securityClause = $_SESSION['user']['security']['letterbox_coll']['DOC']['where'];
        if(empty($securityClause)){
            $securityClause = '1=2';
        }

        $clause .= ' AND ' . $securityClause;

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
