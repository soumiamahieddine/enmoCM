<?php

require '../../vendor/autoload.php';

chdir('../..');

$nonReadableFiles = [];
$migrated = 0;
$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);
    $GLOBALS['customId'] = $custom;

    $configPath             = "custom/{$custom}/apps/maarch_entreprise/xml/config.xml";
    $visaConfigPath         = "modules/visa/batch/config/config.xml";
    if (file_exists($configPath)) {
        if (!is_readable($configPath) || !is_writable($configPath)) {
            $nonReadableFiles[] = $configPath;
            continue;
        }
        if (is_readable($visaConfigPath) && is_writable($visaConfigPath)) {
            $visaFilePath = $visaConfigPath;
        } elseif (is_readable("custom/{$custom}/{$visaConfigPath}") && is_writable("custom/{$custom}/{$visaConfigPath}")) {
            $visaFilePath = "custom/{$custom}/{$visaConfigPath}";
        } else {
            printf("Aucun fichier de configuration de parapheur externe trouvé pour le custom {$custom}\n");
            continue;
        }
        $loadedXml     = simplexml_load_file($configPath);
        $loadedVisaXml = simplexml_load_file($visaFilePath);
        
        if ($loadedXml && $loadedVisaXml) {
            $loadedXml->SIGNATUREBOOK->validatedStatus          = (string)$loadedVisaXml->CONFIG->validatedStatus;
            $loadedXml->SIGNATUREBOOK->validatedStatusOnlyVisa  = (string)$loadedVisaXml->CONFIG->validatedStatusOnlyVisa;
            $loadedXml->SIGNATUREBOOK->refusedStatus            = (string)$loadedVisaXml->CONFIG->refusedStatus;
            $loadedXml->SIGNATUREBOOK->validatedStatusAnnot     = (string)$loadedVisaXml->CONFIG->validatedStatusAnnot;
            $loadedXml->SIGNATUREBOOK->refusedStatusAnnot       = (string)$loadedVisaXml->CONFIG->refusedStatusAnnot;
            $loadedXml->SIGNATUREBOOK->userWS                   = (string)$loadedVisaXml->CONFIG->userWS;
            $loadedXml->SIGNATUREBOOK->passwordWS               = (string)$loadedVisaXml->CONFIG->passwordWS;
            if (empty($loadedXml->CONFIG->MaarchUrl)) {
                $loadedXml->CONFIG->MaarchUrl = (string)$loadedVisaXml->CONFIG->applicationUrl;
            }

            $res = formatXml($loadedXml);
            $fp  = fopen($configPath, "w+");
            if ($fp) {
                fwrite($fp, $res);
            }

            $migrated++;
        }
    }
}

foreach ($nonReadableFiles as $file) {
    printf("The file %s it is not readable or not writable.\n", $file);
}

printf($migrated . " custom(s) avec config.xml (visa) trouvé(s) et migré(s).\n");

function formatXml($simpleXMLElement)
{
    $xmlDocument = new DOMDocument('1.0');
    $xmlDocument->preserveWhiteSpace = false;
    $xmlDocument->formatOutput = true;
    $xmlDocument->loadXML($simpleXMLElement->asXML());

    return $xmlDocument->saveXML();
}
