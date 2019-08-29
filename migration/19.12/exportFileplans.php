<?php

require '../../vendor/autoload.php';

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }
    $migrated = 0;

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);


    $fileplans = \SrcCore\models\DatabaseModel::select([
        'select'    => ['*'],
        'table'     => ['fp_fileplan'],
        'where'     => ['user_id is null']
    ]);

    if (!empty($fileplans)) {
        $file = fopen("migration/19.12/fileplans_{$custom}.csv", 'w+');
        $csvHead = ['Identifiant plan', 'Libellé plan', 'Identifiant position', 'Libellé position', 'Identifiant courrier', 'Sujet courrier'];
        fputcsv($file, $csvHead, ',');

        foreach ($fileplans as $fileplan) {
            $csvContent = [];
            $positions = \SrcCore\models\DatabaseModel::select([
                'select'    => ['*'],
                'table'     => ['fp_fileplan_positions'],
                'where'     => ['fileplan_id = ?'],
                'data'      => [$fileplan['fileplan_id']]
            ]);

            foreach ($positions as $position) {
                $resources = \SrcCore\models\DatabaseModel::select([
                    'select'    => ['res_letterbox.res_id', 'res_letterbox.subject'],
                    'table'     => ['fp_res_fileplan_positions, res_letterbox'],
                    'where'     => ['fp_res_fileplan_positions.res_id = res_letterbox.res_id', 'position_id = ?'],
                    'data'      => [$position['position_id']]
                ]);
                foreach ($resources as $resource) {
                    $csvContent = [$fileplan['fileplan_id'], $fileplan['fileplan_label'], $position['position_id'], $position['position_label'], $resource['res_id'], $resource['subject']];
                    fputcsv($file, $csvContent, ',');
                }
            }

            if (!empty($csvContent)) {
                ++$migrated;
            }
        }

        fclose($file);
    }

    printf("Export Plans de Classements (CUSTOM {$custom}) : " . $migrated . " Plan(s) de classement public exporté(s) dans le fichier fileplans_{$custom}.csv\n");
}
