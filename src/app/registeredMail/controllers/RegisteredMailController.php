<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*/

/**
 * @brief Registered Mail Controller
 * @author dev@maarch.org
 */

namespace RegisteredMail\controllers;

use Com\Tecnick\Barcode\Barcode;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;

class RegisteredMailController
{
    public function getCountries(Request $request, Response $response)
    {
        $countries = [];
        if (($handle = fopen("referential/liste-197-etats.csv", "r")) !== FALSE) {
            fgetcsv($handle, 0, ';');
            while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
                $countries[] = utf8_encode($data[0]);
            }
            fclose($handle);
        }
        return $response->withJson(['countries' => $countries]);
    }

    public static function getRegisteredMailNumber(array $args)
    {
        $number = str_pad($args['rawNumber'], 10, "0", STR_PAD_LEFT);
        $s1 = $number[1] + $number[3] + $number[5] + $number[7] + $number[9];
        $s2 = $number[0] + $number[2] + $number[4] + $number[6] + $number[8];
        $s3 = $s1 * 3 + $s2;

        $modS3 = $s3 % 10;
        if ($modS3 === 0) {
            $key = 0;
        } else {
            $key = 10 - $modS3;
        }

        $registeredMailNumber = "{$args['type']} {$number[0]}{$number[1]}{$number[2]} {$number[3]}{$number[4]}{$number[5]} {$number[6]}{$number[7]}{$number[8]}{$number[9]} {$key}";

        return $registeredMailNumber;
    }

    public function print(Request $request, Response $response)
    {

        $args = [
            'type' => '2C',
            'number'    => '551',
            'warranty'  => 'R1',
            'letter'    => true,
            'reference'    => '15/08/2020 - ma ref',
            'recipient' => [
                'AFNOR',
                '',
                'Eric Choupo',
                '',
                '5 Rue de Paris',
                '',
                '75001 Paris',
                'FRANCE'
            ],
            'sender' => [
                'AFNOR',
                '',
                'Edinson Cavani',
                '',
                '10 Rue de France',
                '',
                '75016 Paris',
                'FRANCE'
            ],
        ];

        $registeredMailNumber = RegisteredMailController::getRegisteredMailNumber(['type' => $args['type'], 'rawNumber' => $args['number']]);
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->SetAutoPagebreak(false);
        $pdf->addPage();

        $barcode = new Barcode();
//        $pdf->AddFont('LucidaConsole','','lucon.php');
        $pdf->SetFont('times', '', 11);

        if ($args['type'] != 'RW') {
            // TODO INFO FEUILLE 1 : GAUCHE
            $pdf->SetXY(50, 22);
            $pdf->cell(0, 0, $registeredMailNumber);

            if ($args['warranty'] == 'R1') {
                $pdf->SetXY(86, 28);
                $pdf->cell(0, 0, 'X');
            } else if ($args['warranty'] == 'R2') {
                $pdf->SetXY(99, 28);
                $pdf->cell(0, 0, 'X');
            } else {
                $pdf->SetXY(112, 28);
                $pdf->cell(0, 0, 'X');
            }
            if ($args['letter'] === true) {
                $pdf->SetXY(86, 36);
                $pdf->cell(0, 0, 'X');
            }
            $y = 45;
            $pdf->SetXY(36, $y);
            $pdf->cell(0, 0, $args['recipient'][1]);

            $y += 4;
            $pdf->SetXY(36, $y);
            $pdf->cell(0, 0, $args['recipient'][2]);

            $y += 4;
            $pdf->SetXY(36, $y);
            $pdf->cell(0, 0, $args['recipient'][3]);

            $y += 4;
            $pdf->SetXY(36, $y);
            $pdf->cell(0, 0, $args['recipient'][4]);

            $y += 4;
            $pdf->SetXY(36, $y);
            $pdf->cell(0, 0, $args['recipient'][5]);

            $y += 4;
            $pdf->SetXY(36, $y);
            $pdf->cell(0, 0, $args['recipient'][6]);


            // TODO INFO FEUILLE 1 : DROITE
            $pdf->SetXY(130, 77);
            $pdf->cell(0, 0, $registeredMailNumber);
            $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber);
            $pdf->Image('@'.$barcodeObj->getPngData(), 130, 82, 60, 15, '', '', '', false, 300);


            $y = 45;
            $pdf->SetXY(120, $y);
            $pdf->cell(0, 0, $args['recipient'][1]);

            $y += 4;
            $pdf->SetXY(120, $y);
            $pdf->cell(0, 0, $args['recipient'][2]);

            $y += 4;
            $pdf->SetXY(120, $y);
            $pdf->cell(0, 0, $args['recipient'][3]);

            $y += 4;
            $pdf->SetXY(120, $y);
            $pdf->cell(0, 0, $args['recipient'][4]);

            $y += 4;
            $pdf->SetXY(120, $y);
            $pdf->cell(0, 0, $args['recipient'][5]);

            $y += 4;
            $pdf->SetXY(120, $y);
            $pdf->cell(0, 0, $args['recipient'][6]);


            //TODO INFO 2eme feuille
            $pdf->SetXY(63, 107);
            $pdf->cell(0, 0, $registeredMailNumber);
            $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber);
            $pdf->Image('@'.$barcodeObj->getPngData(), 63, 112, 60, 15, '', '', '', false, 300);


            if ($args['warranty'] == 'R1') {
                $pdf->SetXY(99, 131);
                $pdf->cell(0, 0, 'X');
            } else if ($args['warranty'] == 'R2') {
                $pdf->SetXY(112, 131);
                $pdf->cell(0, 0, 'X');
            } else {
                $pdf->SetXY(125, 131);
                $pdf->cell(0, 0, 'X');
            }
            if ($args['letter'] === true) {
                $pdf->SetXY(99, 137);
                $pdf->cell(0, 0, 'X');
            }

            $y = 145;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['recipient'][1]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['recipient'][2]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['recipient'][3]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['recipient'][4]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['recipient'][5]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['recipient'][6]);

            $y =175;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][1]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][2]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][3]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][4]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][5]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][6]);


            //TODO INFO 3eme feuille
            if ($args['type'] == '2C') {
                $pdf->SetXY(37, 207);
                $pdf->cell(0, 0, $registeredMailNumber);
                $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber);
                $pdf->Image('@'.$barcodeObj->getPngData(), 37, 212, 60, 15, '', '', '', false, 300);

                $y = 235;
                $pdf->SetXY(57, $y);
                $pdf->cell(0, 0, $args['recipient'][1]);

                $y += 4;
                $pdf->SetXY(57, $y);
                $pdf->cell(0, 0, $args['recipient'][2]);

                $y += 4;
                $pdf->SetXY(57, $y);
                $pdf->cell(0, 0, $args['recipient'][3]);

                $y += 4;
                $pdf->SetXY(57, $y);
                $pdf->cell(0, 0, $args['recipient'][4]);

                $y += 4;
                $pdf->SetXY(57, $y);
                $pdf->cell(0, 0, $args['recipient'][5]);

                $y += 4;
                $pdf->SetXY(57, $y);
                $pdf->cell(0, 0, $args['recipient'][6]);
            }

            $y = 265;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][1]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][2]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][3]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][4]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][5]);

            $y += 4;
            $pdf->SetXY(57, $y);
            $pdf->cell(0, 0, $args['sender'][6]);

            $pdf->SetXY(7, 280);
            $pdf->Multicell(40, 5, $args['reference']);
        } else {
            //TODO INFO RW
            $pdf->setFont('times', '', '8');
            
            $y = 27;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][1]);

            $y += 4;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][2]);

            $y += 4;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][3]);

            $y += 4;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][4]);

            $y += 4;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][5]);

            $y += 4;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][6]);
            
            $y += 4;
            $pdf->SetXY(127, $y);
            $pdf->cell(0, 0, $args['recipient'][7]);


            $y = 7;
            $pdf->SetXY(10, $y);
            $pdf->cell(0, 0, $args['sender'][1]);

            $y += 3;
            $pdf->SetXY(10, $y);
            $pdf->cell(0, 0, $args['sender'][2]);

            $y += 3;
            $pdf->SetXY(10, $y);
            $pdf->cell(0, 0, $args['sender'][3]);

            $y += 3;
            $pdf->SetXY(10, $y);
            $pdf->cell(0, 0, $args['sender'][4]);

            $y += 3;
            $pdf->SetXY(10, $y);
            $pdf->cell(0, 0, $args['sender'][5]);

            $y += 3;
            $pdf->SetXY(10, $y);
            $pdf->cell(0, 0, "{$args['sender'][6]}, {$args['sender'][7]}");

            $pdf->SetFont('times', '', 11);

            if ($args['warranty'] == 'R1') {
                $pdf->SetXY(71, 27);
                $pdf->cell(0, 0, 'X');
            } else if ($args['warranty'] == 'R2') {
                $pdf->SetXY(78, 27);
                $pdf->cell(0, 0, 'X');
            }

            $pdf->SetXY(56, 37);
            $pdf->cell(0, 0, $registeredMailNumber);
            $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber);
            $pdf->Image('@'.$barcodeObj->getPngData(), 56, 42, 60, 15, '', '', '', false, 300);


            $pdf->SetXY(56, 53);
            $pdf->cell(0, 0, $registeredMailNumber);

            $pdf->setFont('times', '', '8');

            $y = 236;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][1]);

            $y += 3;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][2]);

            $y += 3;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][3]);

            $y += 3;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][4]);

            $y += 3;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][5]);

            $y += 3;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][6]);

            $y += 3;
            $pdf->SetXY(103, $y);
            $pdf->cell(0, 0, $args['sender'][7]);

            $pdf->SetFont('times', '', 11);


            if ($args['letter'] === true) {
                $pdf->SetXY(21, 239);
                $pdf->cell(0, 0, 'X');
            } else {
                $pdf->SetXY(29, 239);
                $pdf->cell(0, 0, 'X');
            }

            $pdf->setFont('times', '', '8');

            $pdf->SetXY(120, 210);
            $pdf->cell(0, 0, $registeredMailNumber);

            $pdf->SetXY(95, 219);
            $pdf->Multicell(70, 5, $args['reference']);

            $y = 208;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][1]);

            $y += 4;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][2]);

            $y += 4;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][3]);

            $y += 4;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][4]);

            $y += 4;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][5]);

            $y += 4;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][6]);
            
            $y += 4;
            $pdf->SetXY(20, $y);
            $pdf->cell(0, 0, $args['recipient'][7]);
        }

        $pdf->Output('/home/damien/reco.pdf', 'F');

        return true;
    }
}
