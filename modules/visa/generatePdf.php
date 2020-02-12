<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   generatePdf
* @author  dev <dev@maarch.org>
* @ingroup visa
*/

if (isset($_REQUEST["res_id"]) && isset($_REQUEST["coll_id"]) && isset($_REQUEST["is_version"])) {
    $convertedDocument =  \Convert\controllers\ConvertPdfController::getConvertedPdfById([
        'resId' => $_REQUEST["res_id"],
        'collId' => $_REQUEST["coll_id"],
    ]);

    if (!empty($convertedDocument['errors'])) {
        echo "{\"status\" : \"1\", \"error_txt\" : \""._CONVERSION_FAILED." : ".$convertedDocument['errors']."\"}";
    } else {
        echo "{\"status\" : \"0\", \"error_txt\" : \"\"}";
    }
}
