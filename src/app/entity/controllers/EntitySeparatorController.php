<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Print Separator Controller
* @author dev@maarch.org
*/

namespace Entity\controllers;

use Endroid\QrCode\QrCode;
use Entity\models\EntityModel;
use Group\models\ServiceModel;
use Parameter\models\ParameterModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;

class EntitySeparatorController
{
    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'entities_print_sep_mlb', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $bodyData = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($bodyData['type'])) {
            return $response->withStatus(403)->withJson(['errors' => 'type is not set or empty']);
        }

        if (!in_array($bodyData['type'], ['barcode', 'qrcode'])) {
            return $response->withStatus(403)->withJson(['errors' => 'type value must be qrcode or barcode']);
        }

        $entitiesList = [];
        if ($bodyData['target'] == 'generic') {
            $entitiesList['COURRIER'] = 'Maarch Courrier';
        } else {
            if (!Validator::arrayType()->notEmpty()->validate($bodyData['entities'])) {
                return $response->withStatus(403)->withJson(['errors' => 'entities is not set or empty']);
            }

            $entitiesInfo = EntityModel::get([
                'select'   => ['entity_label', 'entity_id'],
                'where'    => ['entity_id in (?)'],
                'data'     => [$bodyData['entities']],
                'order_by' => ['entity_label asc']
            ]);

            foreach ($entitiesInfo as $value) {
                $entitiesList[$value['entity_id']] = $value['entity_label'];
            }
        }

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);

        foreach ($entitiesList as $entityId => $entityLabel) {
            $pdf->AddPage();
            $pdf->SetFont('', 'B', 20);
            $pdf->Cell(250, 20, _PRINT_SEP_TITLE, 0, 1, 'C');
            $pdf->Cell(250, 20, $entityId, 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');

            if ($bodyData['type'] == 'qrcode') {
                $parameter = ParameterModel::getById(['select' => ['param_value_int'], 'id' => 'QrCodePrefix']);
                $prefix = '';
                if ($parameter['param_value_int'] == 1) {
                    $prefix = 'Maarch_';
                }
                $qrCode = new QrCode($prefix . $entityId);
                // $qrCode->setSize(110);
                // $qrCode->setMargin(25);
                $pdf->Image('@'.$qrCode->writeString(), 0, 0, 80);
            } else {
                // $p_cab = $cab_pdf->generateBarCode($type, $code, 40, '', '', '');
                // $pdf->Image($_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.$p_cab, 40, 50, 120);
            }
            
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, '', 0, 1, 'C');
            $pdf->Cell(180, 10, utf8_decode(_ENTITY), 1, 1, 'C');
            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(180, 10, utf8_decode($entityLabel), 1, 1, 'C');
        }

        $fileContent = $pdf->Output('', 'S');
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=entitySeparator.pdf");

        return $response->withHeader('Content-Type', $mimeType);
    }
}
