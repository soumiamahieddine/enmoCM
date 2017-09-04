<?php

namespace Core\Controllers;

use Core\Models\GroupModel;
use Core\Models\ServiceModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class GroupController
{
    public function get(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['groups' => GroupModel::get()]);
    }
}
