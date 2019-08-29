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


    $cases = \SrcCore\models\DatabaseModel::select([
        'select' => ['*'],
        'table'  => ['cases']
    ]);

    if (!empty($cases)) {
        $file = fopen("migration/19.12/cases_{$custom}.csv", 'w+');
        $csvHead = ['Identifiant affaire', 'Libellé affaire', 'Date de cloture', 'Identifiant courrier', 'Sujet courrier'];
        fputcsv($file, $csvHead, ',');

        foreach ($cases as $case) {
            $resources = \SrcCore\models\DatabaseModel::select([
                'select'    => ['res_letterbox.res_id', 'res_letterbox.subject'],
                'table'     => ['cases_res, res_letterbox'],
                'where'     => ['cases_res.res_id = res_letterbox.res_id', 'case_id = ?'],
                'data'      => [$case['case_id']]
            ]);
            foreach ($resources as $resource) {
                $csvContent = [$case['case_id'], $case['case_label'], $case['case_closing_date'], $resource['res_id'], $resource['subject']];
                fputcsv($file, $csvContent, ',');
            }
            ++$migrated;
        }

        fclose($file);
    }

    printf("Export Affaires (CUSTOM {$custom}) : " . $migrated . " Affaires exportée(s) dans le fichier cases_{$custom}.csv\n");
}
