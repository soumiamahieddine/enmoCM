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
use RegisteredMail\models\RegisteredMailModel;
use Resource\models\ResModel;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;

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

    public static function isRegisteredMailClosed(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $registeredMail = RegisteredMailModel::getByResId(['select' => ['generated'], 'resId' => $args['resId']]);
        if (empty($registeredMail['generated'])) {
            return false;
        }

        $resource = ResModel::getById(['select' => ['departure_date'], 'resId' => $args['resId']]);
        if (empty($resource['departure_date'])) {
            return ['errors' => ['Departure date is empty']];
        }
        $departureDate = new \DateTime($resource['departure_date']);
        $today = new \DateTime();
        $today->setTime(16, 00);

        if ($departureDate > $today) {
            return false;
        }

        return true;
    }

    public function printTest(Request $request, Response $response)
    {
        $args = [
            'type' => 'RW',
            'number'    => '551',
            'warranty'  => 'R2',
            'letter'    => true,
            'reference'    => '15/08/2020 - ma ref',
            'recipient' => [
                'AFNOR',
                'PSG',
                'Eric Choupo',
                'Porte 160',
                '5 Rue de Paris',
                'Batiment C',
                '75001 Paris',
                'FRANCE'
            ],
            'sender' => [
                'AFNOR',
                'PSG',
                'Edinson Cavani',
                'Porte 140',
                '10 Rue de France',
                'Batiment B',
                '75016 Paris',
                'FRANCE'
            ],
        ];

        RegisteredMailController::getRegisteredMailPDF($args);

        return $response->withJson(['test' => 2]);
    }

    public function printDepositSlipTest(Request $request, Response $response)
    {
        $args = [
            'site' => [
                'label'           => 'Dunder Mifflin Scranton',
                'accountNumber'   => 42,
                'addressNumber'   => '1725',
                'addressStreet'   => 'Slough Avenue',
                'addressPostcode' => '18505',
                'addressTown'     => 'Scranton',
                'postOfficeLabel' => 'Scranton Post Office'
            ],
            'type' => '2D',
            'trackingNumber' => '1234567890',
            'departureDate' => '26/08/2010',
            'registeredMails' => [
                [
                    'type'      => '2D',
                    'number'    => '551',
                    'warranty'  => 'R2',
                    'letter'    => true,
                    'reference' => '15/08/2020 - ma ref',
                    'recipient' => [
                        'AFNOR',
                        'PSG',
                        'Eric Choupo',
                        'Porte 160',
                        '5 Rue de Paris',
                        'Batiment C',
                        '75001 Paris',
                        'FRANCE'
                    ]
                ]
            ]
        ];

        $result = RegisteredMailController::getDepositSlipPdf($args);

        return $response->withJson($result);
    }

    public static function getRegisteredMailPDF(array $args)
    {
        $registeredMailNumber = RegisteredMailController::getRegisteredMailNumber(['type' => $args['type'], 'rawNumber' => $args['number']]);

        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPagebreak(false);
        $pdf->addPage();
        $pdf->SetFont('times', '', 11);

        $barcode = new Barcode();

        if ($args['type'] != 'RW') {
            // TODO INFO FEUILLE 1 : GAUCHE
            $pdf->SetXY(50, 8);
            $pdf->cell(0, 0, $registeredMailNumber);

            if ($args['warranty'] == 'R1') {
                $pdf->SetXY(88, 17);
                $pdf->cell(0, 0, 'X');
            } else if ($args['warranty'] == 'R2') {
                $pdf->SetXY(101, 17);
                $pdf->cell(0, 0, 'X');
            } else {
                $pdf->SetXY(114, 17);
                $pdf->cell(0, 0, 'X');
            }
            if ($args['letter'] === true) {
                $pdf->SetXY(88, 23);
                $pdf->cell(0, 0, 'X');
            }
            $y = 31;
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
            $y = 31;
            $pdf->SetXY(130, $y);
            $pdf->cell(0, 0, $args['recipient'][1]);

            $y += 4;
            $pdf->SetXY(130, $y);
            $pdf->cell(0, 0, $args['recipient'][2]);

            $y += 4;
            $pdf->SetXY(130, $y);
            $pdf->cell(0, 0, $args['recipient'][3]);

            $y += 4;
            $pdf->SetXY(130, $y);
            $pdf->cell(0, 0, $args['recipient'][4]);

            $y += 4;
            $pdf->SetXY(130, $y);
            $pdf->cell(0, 0, $args['recipient'][5]);

            $y += 4;
            $pdf->SetXY(130, $y);
            $pdf->cell(0, 0, $args['recipient'][6]);

            $pdf->SetXY(140, 65);
            $pdf->cell(0, 0, $registeredMailNumber);
            $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber, -4, -100);
            $pdf->Image('@'.$barcodeObj->getPngData(), 140, 70, 60, 12, '', '', '', false, 300);


            //TODO INFO 2eme feuille
            $pdf->SetXY(63, 100);
            $pdf->cell(0, 0, $registeredMailNumber);
            $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber, -4, -100);
            $pdf->Image('@'.$barcodeObj->getPngData(), 63, 105, 60, 12, '', '', '', false, 300);


            if ($args['warranty'] == 'R1') {
                $pdf->SetXY(101, 125);
                $pdf->cell(0, 0, 'X');
            } else if ($args['warranty'] == 'R2') {
                $pdf->SetXY(114, 125);
                $pdf->cell(0, 0, 'X');
            } else {
                $pdf->SetXY(127, 125);
                $pdf->cell(0, 0, 'X');
            }
            if ($args['letter'] === true) {
                $pdf->SetXY(101, 130);
                $pdf->cell(0, 0, 'X');
            }

            $y = 140;
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

            $y = 170;
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
                $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber, -4, -100);
                $pdf->Image('@'.$barcodeObj->getPngData(), 37, 212, 60, 12, '', '', '', false, 300);

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

            $y = 267;
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

            $pdf->SetXY(5, 280);
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
            $barcodeObj = $barcode->getBarcodeObj('C128', $registeredMailNumber, -4, -100);
            $pdf->Image('@'.$barcodeObj->getPngData(), 56, 42, 60, 12, '', '', '', false, 300);

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

        $fileContent = $pdf->Output('', 'S');

        return ['fileContent' => $fileContent];
    }

    public static function getDepositSlipPdf(array $args)
    {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPagebreak(false);
        $pdf->addPage();
        $pdf->SetFont('times', '', 11);

        $nb = 0;
        $page = 1;

        $pdf->setFont('times', 'B', 11);
        $pdf->SetXY(10, 10);
        if ($args['type'] == '2D') {
            $pdf->MultiCell(0, 15, "DESCRIPTIF DE PLI - LETTRE RECOMMANDEE SANS AR", 'LRTB', 'C', 0);
        } else if ($args['type'] == '2C') {
            $pdf->MultiCell(0, 15, "DESCRIPTIF DE PLI - LETTRE RECOMMANDEE AVEC AR", 'LRTB', 'C', 0);

        } else {
            $pdf->MultiCell(0, 15, "DESCRIPTIF DE PLI - LETTRE RECOMMANDEE INTERNATIONALE AVEC AR", 'LRTB', 'C', 0);
        }

        $pdf->SetXY(10, 30);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(30, 10, "Raison sociale", 1);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(85, 10, $args['site']['label'], 1);
        $pdf->Ln();
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(30, 10, "Adresse", 1);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(85, 10, $args['site']['addressNumber'] . ' ' . $args['site']['addressStreet'], 1);
        $pdf->Ln();
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(30, 10, "Code postale", 1);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(15, 10, $args['site']['addressPostcode'], 1);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(15, 10, "Ville", 1);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(55, 10, $args['site']['addressTown'], 1);
        $pdf->Ln();

        $pdf->SetXY(145, 30);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(55, 10, "N° Client (Coclico)", 1);
        $pdf->Ln();
        $pdf->SetXY(145, 40);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(55, 10, $args['site']['accountNumber'], 1);
        $pdf->Ln();

        $pdf->SetXY(145, 50);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(55, 10, "N° Compte de suivi", 1);
        $pdf->Ln();

        $pdf->SetXY(145, 60);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(55, 10, $args['trackingNumber'], 1);
        $pdf->Ln();

        $pdf->SetXY(10, 80);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(30, 10, "Lieu", 1);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(100, 10, $args['site']['postOfficeLabel'], 1);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(20, 10, "Date", 1);
        $pdf->setFont('times', '', 11);
        $pdf->Cell(40, 10, date("d/m/y"), 1);
        $pdf->SetXY(10, 100);
        $pdf->Cell(10, 10, "", 1);
        $pdf->setFont('times', 'B', 11);
        $pdf->Cell(30, 10, "ID du pli", 1);
        $pdf->Cell(10, 10, "NG*", 1);
        $pdf->Cell(15, 10, "CRBT", 1);
        $pdf->Cell(30, 10, "Référence", 1);
        $pdf->Cell(95, 10, "Destinataire", 1);
        $pdf->Ln();

        // List
        foreach ($args['registeredMails'] as $position => $registeredMail) {
            if ($position % 9 == 0) {
                $nb++;
            }

            $registeredMailNumber = RegisteredMailController::getRegisteredMailNumber(['type' => $args['type'], 'rawNumber' => $registeredMail['number']]);

            $pdf->setFont('times', '', 9);
            $pdf->Cell(10, 10, $position + 1, 1);
            $pdf->setFont('times', '', 9);
            $pdf->Cell(30, 10, $registeredMailNumber, 1);
            $pdf->Cell(10, 10, $registeredMail['warranty'], 1);
            $pdf->Cell(15, 10, "", 1);
            if (strlen($registeredMail['reference']) > 19) {
                $pdf->Cell(30, 10, "", 1);

            } else {
//                    $pdf->Cell(30, 10, mb_strimwidth($registeredMail['reference'], 0, 10, ""), 1); // TODO strim width ???
                $pdf->Cell(30, 10, $registeredMail['reference'], 1);
            }

            $pdf->setFont('times', '', 6);
            if (strlen($registeredMail['recipient'][1] . " " . $registeredMail['recipient'][4] . " " . $registeredMail['recipient'][6]) > 60) {
                $pdf->Cell(95, 10, $registeredMail['recipient'][1], 1);
                $pdf->SetXY($pdf->GetX() - 95, $pdf->GetY() + 3);
                $pdf->Cell(95, 10, $registeredMail['recipient'][4] . " " . $registeredMail['recipient'][6], 0);
                $pdf->SetXY($pdf->GetX() + 95, $pdf->GetY() - 3);
            } else {
                $pdf->Cell(95, 10, $registeredMail['recipient'][1] . " " . $registeredMail['recipient'][4] . " " . $registeredMail['recipient'][6], 1);
            }


            $pdf->Ln();
            //contrôle du nb de reco présent sur la page. Si 16 lignes, changement de page et affichage du footer
            if ($position % 16 >= 15) {
                $pdf->SetXY(10, 276);
                $pdf->setFont('times', 'I', 8);
                $pdf->Cell(0, 0, "*Niveau de garantie (R1 pour tous ou R2, R3");
                $pdf->SetXY(-30, 276);
                $pdf->setFont('times', 'I', 8);
                $pdf->Cell(0, 0, $page . '/' . $nb);
                $pdf->addPage();
                $page++;
            }
        }

        $position = 0;
        //contrôle du nb de reco présent sur la page. Si trop, saut de page pour la partie réservé à la poste
        if ($position % 10 >= 9) {
            $pdf->SetXY(10, 276);
            $pdf->setFont('times', 'I', 8);
            $pdf->Cell(0, 0, "*Niveau de garantie (R1 pour tous ou R2, R3");
            $pdf->SetXY(-30, 276);
            $pdf->setFont('times', 'I', 8);
            $pdf->Cell(0, 0, $page . '/' . $nb);
            $pdf->addPage();
            $page++;
        }
        $pdf->setFont('times', 'B', 9);
        $pdf->SetXY(10, 228);
        $pdf->Cell(0, 0, 'Partie réservée au contrôle postal:');
        $pdf->SetXY(110, 238);
        $pdf->setFont('times', '', 11);
        $pdf->SetXY(10, 233);
        $pdf->Cell(90, 40, '', 1);
        $pdf->Cell(50, 40, '', 1);
        $pdf->Cell(11, 10, "Total", 1);
        $pdf->setFont('times', '', 9);
        $position = $position + 1;
        $pdf->Cell(0, 10, $position . " recommandé(s)", 1);
        $pdf->SetXY(10, 234);
        $pdf->Cell(0, 0, 'Commentaire:');
        $pdf->SetXY(110, 234);
        $pdf->Cell(0, 0, 'Timbre à date:');
        $pdf->setFont('times', 'I', 8);
        $pdf->SetXY(100, 268);
        $pdf->Cell(0, 0, 'Visa après contrôle des quantités.');

        $pdf->SetXY(10, 276);
        $pdf->setFont('times', 'I', 8);
        $pdf->Cell(0, 0, "*Niveau de garantie (R1 pour tous ou R2, R3");
        $pdf->SetXY(-30, 276);
        $pdf->setFont('times', 'I', 8);
        $pdf->Cell(0, 0, $page . '/' . $nb);

        $fileContent = $pdf->Output('', 'S');
        return ['encodedFileContent' => base64_encode($fileContent)];
    }
}
