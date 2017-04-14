<?php

namespace MaarchTest;

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once __DIR__.'/define.php';

class AdminReportsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetList()
    {
         $client = new \GuzzleHttp\Client([
            'base uri' => '127.0.0.1/MaarchCourrier/rest/report/groups',
            'timeout' => 42.0,]      
         );
        $response  = $client->request('GET','127.0.0.1/MaarchCourrier/rest/report/groups',[
            'auth' => ['superadmin','superadmin']
        ]);


        $decoded_response = json_decode($response->getBody(), true);        
        $this->assertNotNull($decoded_response);
    }


    public function testGetReportsTypesByXML(){

             $client = new \GuzzleHttp\Client([
            'base uri' => '127.0.0.1/MaarchCourrier/rest/report/groups',
            'timeout' => 42.0,]      
              );
           $aArgs = [
            'id'=> 'ELU'
                 ];   
        $response  = $client->request('GET','127.0.0.1/MaarchCourrier/rest/report/groups/'.$aArgs['id'],[
            'auth' => ['superadmin','superadmin']
              ]);

        $decoded_response = json_decode($response->getBody(), true);        
        $this->assertNotNull($decoded_response);

    }

  /*  public function testGetUserByGroupId()
    {
        $action = new \Core\Controllers\AdminReportsController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $aArgs = [
            'id'=> 'NEW'
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->getUserByGroupId($request, $response, $aArgs);
        $compare = '[[{"user_id":"NEW","group_id":"Nouveau",'
            . '"primary_group":"Y","role":"N"}]]';

        $this->assertSame((string)$response->getBody(), $compare);
    }
*/

    public function testUpdate()
    {
        $client = new \GuzzleHttp\Client([
            'base uri' => '127.0.0.1/MaarchCourrier/rest/report/groups',
            'timeout' => 42.0,]        
                 );
         $aArgs = [
            'id'=> 'ELU'
              ];        
                
        $response_guzzle_XML = $client->request('GET','127.0.0.1/MaarchCourrier/rest/report/groups/'.$aArgs['id'],[
            'auth' => ['superadmin','superadmin']
        ]);
        $d = [];
        $d = json_decode($response_guzzle_XML->getBody(), true);      
        $checked_val_reverse = !$d[0]['checked'];

       $tab_test_unitaire [] = [
            'id' => "folder_view_stat", 
            'checked' => $checked_val_reverse
            ];   
        $aArgs['data'] =  $tab_test_unitaire;
     
        $response_guzzle = $client->request('PUT','127.0.0.1/MaarchCourrier/rest/report/groups/'.$aArgs['id'],[
            'auth' => ['superadmin','superadmin'],'form_params' => $aArgs['data']
        ]);
        $response_guzzle_XML_after = $client->request('GET','127.0.0.1/MaarchCourrier/rest/report/groups/'.$aArgs['id'],[
            'auth' => ['superadmin','superadmin']
        ]);
        $guzzle_xml_after = json_decode($response_guzzle_XML_after->getBody(), true);
        $checked_val_after_update = $guzzle_xml_after[0]['checked'];

        $this->assertSame($checked_val_reverse, $checked_val_after_update);
    }
 
}
