<?php

use Docserver\models\DocserverModel;
use Template\models\TemplateModel;

require '../../vendor/autoload.php';

include_once('../../vendor/tinybutstrong/opentbs/tbs_plugin_opentbs.php');

const OFFICE_EXTENSIONS = ['odt', 'ods', 'odp', 'xlsx', 'pptx', 'docx', 'odf'];

const DATA_TO_REPLACE = [
    'res_letterbox.destination' => '[destination.entity_id]',
    'res_letterbox.entity_label' => '[destination.entity_label]',
    'res_letterbox.process_notes' => '[notes]',

    'res_letterbox.nature_id' => '[res_letterbox.custom_1]',

    // Initiator
    'res_letterbox.initiator_entity_id' => '[initiator.entity_id]',
    'res_letterbox.initiator_entity_label' => '[initiator.entity_label]',
    'res_letterbox.initiator_short_label' => '[initiator.short_label]',
    'res_letterbox.initiator_email' => '[initiator.email]',
    'res_letterbox.initiator_parent_entity_id' => '[initiator.parent_entity_id]',
    'res_letterbox.initiator_parent_entity_label' => '[initiator.parent_entity_label]',
    'res_letterbox.initiator_entity_type' => '[initiator.entity_type]',
    'res_letterbox.initiator_entity_path' => '[initiator.entity_path]',
    'res_letterbox.initiator_entity_fullname' => '[initiator.entity_fullname]',
    'res_letterbox.initiator_zipcode' => '[initiator.zipcode]',
    'res_letterbox.initiator_city' => '[initiator.city]',
    'res_letterbox.initiator_country' => '[initiator.country]',
    'res_letterbox.initiator_ldap_id' => '[initiator.ldap_id]',
    'res_letterbox.initiator_archival_agence' => '[initiator.archival_agence]',
    'res_letterbox.initiator_archival_agreement' => '[initiator.archival_agreement]',
    'res_letterbox.initiator_business_id' => '[initiator.business_id]',

    'attachments.chrono' => '[attachment.chrono]',

    'visa.firstnameSign' => '',
    'visa.lastnameSign' => '[visas]',
    'visa.entitySign' => '',
    'visa.firstname1' => '',
    'visa.lastname1' => '[visas]',
    'visa.firstname2' => '',
    'visa.lastname2' => '[visas]',
    'visa.firstname3' => '',
    'visa.lastname3' => '[visas]',
    'visa.firstname4' => '',
    'visa.lastname4' => '[visas]',
    'visa.firstname5' => '',
    'visa.lastname5' => '[visas]',
    'visa.firstname6' => '',
    'visa.lastname6' => '[visas]',
    'visa.firstname7' => '',
    'visa.lastname7' => '[visas]',
    'visa.firstname8' => '',
    'visa.lastname8' => '[visas]',
    'visa.firstname9' => '',
    'visa.lastname9' => '[visas]',
    'visa.entity1' => '',

    'avis.firstname1' => '',
    'avis.lastname1' => '[opinions]',
    'avis.firstname2' => '',
    'avis.lastname2' => '[opinions]',
    'avis.firstname3' => '',
    'avis.lastname3' => '[opinions]',
    'avis.firstname4' => '',
    'avis.lastname4' => '[opinions]',
    'avis.firstname5' => '',
    'avis.lastname5' => '[opinions]',
    'avis.firstname6' => '',
    'avis.lastname6' => '[opinions]',
    'avis.firstname7' => '',
    'avis.lastname7' => '[opinions]',
    'avis.firstname8' => '',
    'avis.lastname8' => '[opinions]',
    'avis.firstname9' => '',
    'avis.lastname9' => '[opinions]',
    'avis.role1' => '',
    'avis.entity1' => '',
    'avis.note1' => '',

    'copies.firstname1' => '',
    'copies.lastname1' => '[copies]',
    'copies.firstname2' => '',
    'copies.lastname2' => '[copies]',
    'copies.firstname3' => '',
    'copies.lastname3' => '[copies]',
    'copies.firstname4' => '',
    'copies.lastname4' => '[copies]',
    'copies.firstname5' => '',
    'copies.lastname5' => '[copies]',
    'copies.firstname6' => '',
    'copies.lastname6' => '[copies]',
    'copies.firstname7' => '',
    'copies.lastname7' => '[copies]',
    'copies.firstname8' => '',
    'copies.lastname8' => '[copies]',
    'copies.firstname9' => '',
    'copies.lastname9' => '[copies]',
    'copies.entity1' => '',

    'user.role' => '[userPrimaryEntity.role]',
    'user.entity_id' => '[userPrimaryEntity.entity_id]',
    'user.entity_label' => '[userPrimaryEntity.entity_label]',
    'user.short_label' => '[userPrimaryEntity.short_label]',
    'user.adrs_1' => '[userPrimaryEntity.adrs_1]',
    'user.adrs_2' => '[userPrimaryEntity.adrs_2]',
    'user.adrs_3' => '[userPrimaryEntity.adrs_3]',
    'user.zipcode' => '[userPrimaryEntity.zipcode]',
    'user.city' => '[userPrimaryEntity.city]',
    'user.email' => '[userPrimaryEntity.email]',
    'user.parent_entity_id' => '[userPrimaryEntity.parent_entity_id]',
    'user.entity_type' => '[userPrimaryEntity.entity_type]',
    'user.entity_path' => '[userPrimaryEntity.path]',
];


chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $migrated = 0;

    $nonMigrated = 0;

    $docserver = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES']);

    $templatesPath = $docserver['path_template'];

    $templates = TemplateModel::get();

    foreach ($templates as $template) {
        if ($template['template_type'] == 'HTML' || $template['template_type'] == 'TXT' || $template['template_type'] == 'OFFICE_HTML') {
            $content = $template['template_content'];

            $newContent = $content;
            foreach (DATA_TO_REPLACE as $key => $value) {
                    $newContent = str_replace('[' . $key . ']', $value, $newContent);
            }

            if ($content != $newContent) {
                TemplateModel::update([
                    'set' => [
                        'template_content' => $newContent
                    ],
                    'where' => ['template_id = ?'],
                    'data' => [$template['template_id']]
                ]);
                $migrated++;
            } else {
                $nonMigrated++;
            }
        }
        if ($template['template_type'] == 'OFFICE' || $template['template_type'] == 'OFFICE_HTML') {
            $path = str_replace('#', '/', $template['template_path']);

            $pathToDocument = $templatesPath . $path . $template['template_file_name'];

            $pathInfo = pathinfo($pathToDocument);
            $extension = $pathInfo['extension'];

            if (!in_array($extension, OFFICE_EXTENSIONS)) {
                $nonMigrated++;
                continue;
            }

            if (!is_writable($pathToDocument) || !is_readable($pathToDocument)) {
                $nonMigrated++;
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

                $tbs->ReplaceFields(DATA_TO_REPLACE);
            }

            if (in_array($extension, OFFICE_EXTENSIONS)) {
                $tbs->Show(OPENTBS_STRING);
            } else {
                $tbs->Show(TBS_NOTHING);
            }

            $content = base64_encode($tbs->Source);

            $result = file_put_contents($pathToDocument, base64_decode($content));
            if ($result !== false) {
                $migrated++;
            } else {
                echo "Erreur lors de la migration du modèle : $pathToDocument\n";
                $nonMigrated++;
            }
        }
    }

    printf("Migration de Modèles d'enregistrements (CUSTOM {$custom}) : " . $migrated . " Modèle(s) migré(s), $nonMigrated non migré(s).\n");
}
