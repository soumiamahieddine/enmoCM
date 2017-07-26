<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\ReportModel;
use Core\Models\LangModel;


class ReportController
{
    public function getGroups(RequestInterface $request, ResponseInterface $response)
    {
        $obj['group'] = ReportModel::getGroups();
        $obj['lang'] = LangModel::getReportsLang();
        
        
        return $response->withJson($obj);
    }






public function getReportsTypesByXML(RequestInterface $request, ResponseInterface $response, $aArgs)
    {

       if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = ReportModel::getReportsTypesByXML([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }
        

        /* $datas = [
            $obj,
        ];*/


        return $response->withJson($obj);
    }

     public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
      
      

         $data = $request->getParams();
         //$data = $aArgs['data'];
          /*
          *
          *{"id":{"id" : "entity_late_mail","checked" : false },{"id" : "process_delay","checked" : false },{"id" : "folder_view_stat","checked" : false }}
          A mettre dans le body du reste client, c'est ce qui sera dans getParams
          *
          *
          */
         $id = $aArgs['id'];
         $obj = ReportModel::update([
                'id' => $id,
                'data' => $data
                ]);

        return $response->withJson($obj);
       
    }
}

