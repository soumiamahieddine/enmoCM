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
    public function updatePriorities(RequestInterface $request, ResponseInterface $response, $aArgs){
        $datas = $request->getParams();
        $errors = self::control($request,'update',$datas);        
        if(!empty($errors)){
            return $response->withJson(['errors'=>$errors]);
        }
        
        $return = PrioritiesModel::updatePriorities($datas);
        if(!empty($return)){
            return $return;
        }       

    }

    public function deletePriority(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $datas= $request->getParams();
        //$errors = self::control($request,'delete',$datas);
        $obj = PrioritiesModel::deletePriority(['id' => $aArgs['id']]);
        return $response->withJson($obj);
    }

    protected static function control($request, $mode, $aArgs){
        $errors = [];
        if($mode == 'update'){
            if (empty($aArgs))
                array_push($errors,'PRIORITE TABLEAU VIDE');
            foreach ($aArgs as $value) {
                if (empty($value['label']) || empty($value['number']) || empty($value['wdays'])) {
                    array_push($errors,'INFOS VIDES');
                    //return false;
                } 
                if ($value['wdays'] != 'true' && $value['wdays'] != 'false') {
                    array_push($errors,'INFOS wdays INVALIDE');
                    //return false;
                } 
                if ($value['number'] === '*') {
                } elseif (!ctype_digit($value['number'])) {
                    array_push($errors, 'NUMERO INVALIDE');
                    //return false;
                } elseif ((int)$value['number'] < 0) {
                    array_push($errors, 'NUMERO INFERIEUR A 0');
                }
            }
        }
        return $errors;
    }
}

?>