<?php

use Docserver\models\DocserverModel;

require '../../vendor/autoload.php';

include_once('/var/www/html/MaarchCourrierDev/vendor/tinybutstrong/opentbs/tbs_plugin_opentbs.php');

const OFFICE_EXTENSIONS = ['odt', 'ods', 'odp', 'xlsx', 'pptx', 'docx', 'odf'];

const DATA_TO_MERGE = [
    'res_letterbox' => [
        'destination' => '[destination.entity_id]',
        'entity_label' => '[destination.entity_label]',
        'process_notes' => '[notes]',

        'nature_id' => '[res_letterbox.custom_1]',

        // Initiator
        'initiator_entity_id' => '[initiator.entity_id]',
        'initiator_entity_label' => '[initiator.entity_label]',
        'initiator_short_label' => '[initiator.short_label]',
        'initiator_email' => '[initiator.email]',
        'initiator_parent_entity_id' => '[initiator.parent_entity_id]',
        'initiator_parent_entity_label' => '[initiator.parent_entity_label]',
        'initiator_entity_type' => '[initiator.entity_type]',
        'initiator_entity_path' => '[initiator.entity_path]',
        'initiator_entity_fullname' => '[initiator.entity_fullname]',
        'initiator_zipcode' => '[initiator.zipcode]',
        'initiator_city' => '[initiator.city]',
        'initiator_country' => '[initiator.country]',
        'initiator_ldap_id' => '[initiator.ldap_id]',
        'initiator_archival_agence' => '[initiator.archival_agence]',
        'initiator_archival_agreement' => '[initiator.archival_agreement]',
        'initiator_business_id' => '[initiator.business_id]',

        // Not changed
        'type_label' => '[res_letterbox.type_label]',
        'category_id' => '[res_letterbox.category_id]',
        'admission_date' => '[res_letterbox.admission_date]',
        'doc_date' => '[res_letterbox.doc_date]',
        'process_limit_date' => '[res_letterbox.process_limit_date]',
        'closing_date' => '[res_letterbox.closing_date]',
        'subject' => '[res_letterbox.subject]',
        'alt_identifier' => '[res_letterbox.alt_identifier]',
        'creation_date' => '[res_letterbox.creation_date]'
    ],
    'attachments' => [
        'chrono' => '[attachment.chrono]',
        'chronoBarCode' => '[attachments.chronoBarCode;ope=changepic;tagpos=inside;adjust;unique]'
    ],
    'visa' => [
        'firstnameSign' => '[visas]',
        'lastnameSign' => '[visas]',
        'entitySign' => '[visas]',
        'firstname1' => '[visas]',
        'lastname1' => '[visas]',
        'entity1' => '[visas]'
    ],
    'avis' => [
        'firstname1' => '[opinions]',
        'lastname1' => '[opinions]',
        'role1' => '[opinions]',
        'entity1' => '[opinions]',
        'note1' => '[opinions]'
    ],
    'copies' => [
        'firstname1' => '[copies]',
        'lastname1' => '[copies]',
        'entity1' => '[copies]'
    ],
    'user' => [
        'role' => '[userPrimaryEntity.role]',
        'entity_id' => '[userPrimaryEntity.entity_id]',
        'entity_label' => '[userPrimaryEntity.entity_label]',
        'short_label' => '[userPrimaryEntity.short_label]',
        'adrs_1' => '[userPrimaryEntity.adrs_1]',
        'adrs_2' => '[userPrimaryEntity.adrs_2]',
        'adrs_3' => '[userPrimaryEntity.adrs_3]',
        'zipcode' => '[userPrimaryEntity.zipcode]',
        'city' => '[userPrimaryEntity.city]',
        'email' => '[userPrimaryEntity.email]',
        'parent_entity_id' => '[userPrimaryEntity.parent_entity_id]',
        'entity_type' => '[userPrimaryEntity.entity_type]',
        'path' => '[userPrimaryEntity.path]',

        // Not changed
        'lastname' => '[user.lastname]',
        'firstname' => '[user.firstname]',
        'initials' => '[user.initials]',
        'phone' => '[user.phone]',
        'mail' => '[user.mail]'
    ]
];

function browseFiles($pathDirectory) {
    $files = scandir($pathDirectory);
    $nb = 0;

    if (count($files) == 2) {
        return 0;
    }

    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $pathToDocument = $pathDirectory . '/' . $file;

        if (is_dir($pathToDocument)) {
            $nb += browseFiles($pathToDocument);
            continue;
        }

        $pathInfo = pathinfo($pathToDocument);
        $extension = $pathInfo['extension'];

        if (!in_array($extension, OFFICE_EXTENSIONS)) {
            continue;
        }

        $tbs = new clsTinyButStrong();
        $tbs->NoErr = true;
        $tbs->PlugIn(TBS_INSTALL, OPENTBS_PLUGIN);

        $tbs->LoadTemplate($pathToDocument, OPENTBS_ALREADY_UTF8);

        $pages = 1;
        if ($extension == 'xlsx') {
            $pages = $tbs->PlugIn(OPENTBS_COUNT_SHEETS);
        }

        for ($i = 0; $i < $pages; ++$i) {
            if ($extension == 'xlsx') {
                $tbs->PlugIn(OPENTBS_SELECT_SHEET, $i + 1);
            }
            foreach (DATA_TO_MERGE as $key => $value) {
                $tbs->MergeField($key, $value);
            }
        }

        if (in_array($extension, OFFICE_EXTENSIONS)) {
            $tbs->Show(OPENTBS_STRING);
        } else {
            $tbs->Show(TBS_NOTHING);
        }

        $content = base64_encode($tbs->Source);

        $result = file_put_contents($pathToDocument, base64_decode($content));
        if ($result !== false) {
            $nb++;
        } else {
            echo "Erreur lors de la migration du modèle : $pathToDocument\n";
        }
    }

    return $nb;
}

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $migrated = 0;

    $docserver = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES']);

    $migrated = browseFiles($docserver['path_template']);

    printf("Migration de Modèles d'enregistrements (CUSTOM {$custom}) : " . $migrated . " Modèle(s) migré(s).\n");
}
