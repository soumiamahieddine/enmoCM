<?php

namespace Core\Controllers;

use Core\Models\ServiceModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\PriorityModel;

class PriorityController
{

    public function getPrioritiesForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $return = [
            'priorities'    =>  PriorityModel::get(),
            'lang'          =>  []
        ];

        return $response->withJson($return);
    }

    public function getPriorityForAdministration(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $priotity = PriorityModel::getById(['id' => $aArgs['id']]);

        if(empty($priotity)){
            return $response->withStatus(400)->withJson(['errors' => 'Priority not found']);
        }

        return $response->withJson([
            'priority'  => $priotity,
            'lang'      =>  []
        ]);
    }

    public function getNewPriorityForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $priority = [];
        $priority['lang'] = [];

        return $response->withJson($priority);
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $data['working_days'] = $data['working_days'] ? 'true' : 'false';

        $id = PriorityModel::create($data);

        return $response->withJson([
            'success'   => _USER_ADDED,
            'priority'  => $id
        ]);
    }

//    public function update(RequestInterface $request, ResponseInterface $response, $aArgs){
//        $errors = $this->control($request, 'update');
//
//        if (!empty($errors)) {
//            return $response
//                ->withJson(['errors' => $errors]);
//        }
//
//
//        $aArgs = $request->getParams();
//        $checkExist = PrioritiesModel::getById([
//                'id'    => $aArgs['id']
//            ]);
//            if($checkExist){
//                $return = PrioritiesModel::update($aArgs);
//                if($return) {
//                    $obj = PrioritiesModel::getById([
//                        'id'    => $aArgs['id']
//                    ]);
//                } else {
//                    return $response
//                        ->withStatus(500)
//                        ->withJson(['errors'    => _NOT_UPDATE]);
//                }
//            } else {
//                array_push($errors,'Cette priorité n\'existe pas');
//                return $response
//                    ->withJson(['errors' => $errors]);
//            }
//        $return = PrioritiesModel::update($aArgs);
//        return $response->withJson($obj);
//    }
//
//    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
//    {
//
//        $obj = PrioritiesModel::delete(['id' => $aArgs['id']]);
//        return $response->withJson($obj);
//    }
//
//    protected static function control( $request, $mode){
//        $errors = [];
//        if (empty($request))
//            array_push($errors,'Tableau d\'arguments vide');
//
//        if (!Validator::notEmpty()->validate($request->getParam('label_priority'))){
//            array_push($errors,'Valeur label vide');
//        }
//        if (!Validator::notEmpty()->validate($request->getParam('color_priority'))) {
//            array_push($errors, 'Aucune Couleur assignée');
//        }
//        else if(!Validator::regex('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/')->validate($request->getParam('color_priority')) && Validator::notEmpty()->validate($request->getParam('color_priority')) || $request->getParam('color_priority')=='#ffffff'){
//            array_push($errors,'Format couleur invalide');
//        }
//        if (!Validator::notEmpty()->validate($request->getParam('delays'))){
//            array_push($errors,'Delai vide');
//        }
//        if (!Validator::notEmpty()->validate($request->getParam('working_days'))){
//            array_push($errors,'jours vide');
//        }
//        else if ($request->getParam('working_days')!= 'Y' && $request->getParam('working_days') != 'N') {
//            array_push($errors,'Valeur working_days invalide');
//            //return false;
//        }
//        if ($request->getParam('delays') !== '*' &&!ctype_digit($request->getParam('delays'))&&Validator::notEmpty()->validate($request->getParam('delays'))) {
//            array_push($errors,'Valeur delays invalide');
//        }
//        if ((int)$request->getParam('delays') < 0) {
//            array_push($errors,'Valeur négative');
//        }
//        return $errors;
//    }
}

?>