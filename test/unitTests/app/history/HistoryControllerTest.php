<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;
use SrcCore\models\DatabaseModel;

class HistoryControllerTest extends TestCase
{
    public function testGetHistoryByUserId()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $history     = new \History\controllers\HistoryController();

        $currentUser = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $response = $history->getByUserId($request, new \Slim\Http\Response(), ['userSerialId' => $currentUser['id']]);

        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->histories);
    }

    public function testGetHistory()
    {
        $history     = new \History\controllers\HistoryController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'startDate' => '1521100000',
            'endDate'   => time()
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response = $history->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->histories);
        $this->assertInternalType('bool', $responseBody->limitExceeded);
        $this->assertNotEmpty($responseBody->histories);
    }

    public function testGetBatchHistory()
    {
        $batchHistory     = new \History\controllers\BatchHistoryController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'startDate' => '1521100000',
            'endDate'   => time()
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response = $batchHistory->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->batchHistories);
        $this->assertInternalType('bool', $responseBody->limitExceeded);
        $this->assertNotNull($responseBody->batchHistories);
    }

    public function testRealDelete(){
        
        //get last notes
        $getResId = DatabaseModel::select([
            'select'    => ['res_id'],
            'table'     => ['res_letterbox'],
            'where'     => ['subject = ?','status = ?'],
            'data'      => ['Breaking News : Superman is alive - PHP unit', 'DEL'],
            'order_by'  => ['res_id DESC']
        ]);

        $resID['resId'] = $getResId[0]['res_id'];
        
        //  REAL DELETE
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'res_letterbox',
            'where' => ['res_id = ?'],
            'data'  => [$resID['resId']]
        ]);

        //  READ
        $res = \Resource\models\ResModel::getById(['resId' => $resID['resId']]);
        $this->assertInternalType('array', $res);
        $this->assertEmpty($res);
    }
}
