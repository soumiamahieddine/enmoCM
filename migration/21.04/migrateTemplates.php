<?php

use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use SrcCore\models\CoreConfigModel;
use Template\models\TemplateModel;

require '../../vendor/autoload.php';

include_once('../../vendor/tinybutstrong/opentbs/tbs_plugin_opentbs.php');

const OFFICE_EXTENSIONS = ['odt', 'ods', 'odp', 'xlsx', 'pptx', 'docx', 'odf'];

const DATA_TO_REPLACE = [
    'res_letterbox.admission_date'     => '[res_letterbox.admission_date;frm=dd/mm/yyyy]',
    'res_letterbox.doc_date'           => '[res_letterbox.doc_date;frm=dd/mm/yyyy]',
    'res_letterbox.process_limit_date' => '[res_letterbox.process_limit_date;frm=dd/mm/yyyy]',
    'res_letterbox.closing_date'       => '[res_letterbox.closing_date;frm=dd/mm/yyyy]',
    'res_letterbox.creation_date'      => '[res_letterbox.creation_date;frm=dd/mm/yyyy]',
    'res_letterbox.departure_date'     => '[res_letterbox.departure_date;frm=dd/mm/yyyy]',
    'res_letterbox.opinion_limit_date' => '[res_letterbox.opinion_limit_date;frm=dd/mm/yyyy]',
];

chdir('../..');

$customs = scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $migrated    = 0;
    $nonMigrated = 0;

    $docserver     = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES']);
    $templatesPath = $docserver['path_template'];
    $templates     = TemplateModel::get();

    $tmpPath = CoreConfigModel::getTmpPath();

    foreach ($templates as $template) {
        if ($template['template_type'] == 'HTML' || $template['template_type'] == 'TXT' || $template['template_type'] == 'OFFICE_HTML') {
            $content = $template['template_content'];

            $newContent = $content;
            foreach (DATA_TO_REPLACE as $key => $value) {
                $newContent = str_replace('[' . $key . ']', $value, $newContent);
            }

            if ($template['template_target'] == 'doctypes') {
                $pathFilename = $tmpPath . 'template_migration_' . rand() . '_'. rand() .'.html';
                file_put_contents($pathFilename, $newContent);

                $resource = file_get_contents($pathFilename);
                $pathInfo = pathinfo($pathFilename);
                $storeResult = DocserverController::storeResourceOnDocServer([
                    'collId'           => 'templates',
                    'docserverTypeId'  => 'TEMPLATES',
                    'encodedResource'  => base64_encode($resource),
                    'format'           => $pathInfo['extension']
                ]);

                if (!empty($storeResult['errors'])) {
                    echo $storeResult['errors'];
                    continue;
                }

                TemplateModel::update([
                        'set'   => [
                            'template_content'    => '',
                            'template_type'       => 'OFFICE',
                            'template_path'       => $storeResult['destination_dir'],
                            'template_file_name'  => $storeResult['file_destination_name'],
                            'template_style'      => '',
                            'template_datasource' => 'letterbox_attachment',
                            'template_target'     => 'indexingFile',
                            'template_attachment_type' => 'all'
                        ],
                        'where' => ['template_id = ?'],
                        'data'  => [$template['template_id']]
                    ]);
                unlink($pathFilename);
            } else {
                if ($content != $newContent) {
                    TemplateModel::update([
                        'set'   => [
                            'template_content' => $newContent
                        ],
                        'where' => ['template_id = ?'],
                        'data'  => [$template['template_id']]
                    ]);
                    $migrated++;
                } else {
                    $nonMigrated++;
                }
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
            $tbs->Protect = false;
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

    printf("Migration de Modèles de documents (CUSTOM {$custom}) : " . $migrated . " Modèle(s) migré(s), $nonMigrated non migré(s).\n");
}
