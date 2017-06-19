<?php

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\PrioritiesModel;

//require_once 'core/class/class_db_pdo.php';
//require_once 'modules/notes/Models/NotesModel.php';

class PrioritiesController
{

    public function getList(RequestInterface $request, ResponseInterface $response)
    {

        $obj =[
                    'prioritiesList'    =>  PrioritiesModel::getList(),
                    'lang'              =>  null
            ];
        return $response->withJson($obj);
    }

    public function getLang(RequestInterface $request, ResponseInterface $response){
        $obj = PrioritiesModel::getPrioritiesLang();
        return $response->withJson($obj);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {                    
        $obj = PrioritiesModel::getById(['id' => $aArgs['id']]);
        return $response->withJson($obj);             
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {        
        $errors = $this->control($request, 'create');

        if (!empty($errors)) {
            return $response
                ->withJson(['errors' => $errors]);
        }           
        
        $datas = $request->getParams();

        $return = PrioritiesModel::create($datas);
        if ($return) {
            $obj = PrioritiesModel::getById(['id' => $return]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_CREATE]);
        }
        return $response->withJson($obj);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs){
        $errors = $this->control($request, 'update');

        if (!empty($errors)) {
            return $response
                ->withJson(['errors' => $errors]);
        }

           
        $aArgs = $request->getParams();
        $checkExist = PrioritiesModel::getById([
                'id'    => $aArgs['id']
            ]);
            if($checkExist){
                $return = PrioritiesModel::update($aArgs);
                if($return) {
                    $obj = PrioritiesModel::getById([
                        'id'    => $aArgs['id']
                    ]);
                } else {
                    return $response
                        ->withStatus(500)
                        ->withJson(['errors'    => _NOT_UPDATE]);
                }
            } else {
                array_push($errors,'Cette priorité n\'existe pas');
                return $response
                    ->withJson(['errors' => $errors]);
            }
        $return = PrioritiesModel::update($aArgs);
        //var_dump($return);
        
        

        return $response->withJson($obj);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        
        $obj = PrioritiesModel::delete(['id' => $aArgs['id']]);
        return $response->withJson($obj);
    }

    protected static function control( $request, $mode){
        $errors = [];
        //if($mode == 'update'){
        $errors = [];
        if (empty($request))
            array_push($errors,'Tableau d\'arguments vide');

        if (!Validator::notEmpty()->validate($request->getParam('label_priority'))){
            array_push($errors,'Valeur label vide');
            //return false;
        } 
        if (!Validator::notEmpty()->validate($request->getParam('delays'))){
            array_push($errors,'Delai vide');
        }
        if (!Validator::notEmpty()->validate($request->getParam('working_days'))){
            array_push($errors,'jours vide');
        }
        if ($request->getParam('working_days')!= 'Y' && $request->getParam('working_days') != 'N') {
            array_push($errors,'Valeur working_days invalide');
            //return false;
        } /*elseif ($request->getParam(['number'] === '*') {
            array_push($errors,'Valeur');
        } */elseif (!ctype_digit($request->getParam('delays'))) {
            array_push($errors,'Valeur non numérique');
            //return false;
        } elseif ((int)$request->getParam('delays') < 0) {
            array_push($errors,'Valeur négative');
            //return false;
        }

        //}
        return $errors;
    }
}

?>