<?php

namespace Core\Controllers;

use Core\Models\ServiceModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\PriorityModel;

class PriorityController
{

    public function get(RequestInterface $request, ResponseInterface $response)
    {
        return $response->withJson(['priorities' => PriorityModel::get()]);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $priotity = PriorityModel::getById(['id' => $aArgs['id']]);

        if(empty($priotity)){
            return $response->withStatus(400)->withJson(['errors' => 'Priority not found']);
        }

        return $response->withJson(['priority'  => $priotity]);
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
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

        return $response->withJson([
            'success'   => _ADDED_PRIORITY,
            'priority'  => $id
        ]);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
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

        return $response->withJson(['success' => _UPDATED_PRIORITY]);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        PriorityModel::delete(['id' => $aArgs['id']]);

        return $response->withJson([
            'success' => _DELETED_PRIORITY,
            'priorities' => PriorityModel::get()
        ]);
    }
}
