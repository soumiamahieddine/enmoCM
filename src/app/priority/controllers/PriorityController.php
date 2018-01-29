<?php

namespace Priority\controllers;

use Core\Models\ServiceModel;
use Priority\models\PriorityModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class PriorityController
{
    public function get(Request $request, Response $response)
    {
        return $response->withJson(['priorities' => PriorityModel::get()]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $priotity = PriorityModel::getById(['id' => $aArgs['id']]);

        if(empty($priotity)){
            return $response->withStatus(400)->withJson(['errors' => 'Priority not found']);
        }

        return $response->withJson(['priority'  => $priotity]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['color']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['delays']);
        $check = $check && Validator::boolType()->validate($data['working_days']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['working_days'] = $data['working_days'] ? 'true' : 'false';

        $id = PriorityModel::create($data);

        return $response->withJson(['priority'  => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['color']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['delays']);
        $check = $check && Validator::boolType()->validate($data['working_days']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['id'] = $aArgs['id'];
        $data['working_days'] = empty($data['working_days']) ? 'false' : 'true';

        PriorityModel::update($data);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        PriorityModel::delete(['id' => $aArgs['id']]);

        return $response->withJson(['priorities' => PriorityModel::get()]);
    }
}
