<?php

require '../../vendor/autoload.php';

chdir('../..');

$migrated = 0;
$customs =  scandir('custom');
foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $xmlfile = null;
    $path = "custom/{$custom}/apps/maarch_entreprise/xml/config.xml";
    $xmlfile = simplexml_load_file($path);

    if ($xmlfile) {
        foreach ($xmlfile->COLLECTION as $collection) {
            if ((string)$collection->table == 'res_letterbox') {
                $collId = 'letterbox_coll';
            } elseif ((string)$collection->table == 'res_attachments') {
                $collId = 'attachments_coll';
            } else {
                $collId = 'attachments_version_coll';
            }

            \Docserver\models\DocserverModel::update([
                'set'   => [
                    'path_template' => (string)$collection->path_to_lucene_index
                ],
                'where' => ['docserver_type_id = ?', 'coll_id = ?'],
                'data'  => ['FULLTEXT', $collId]
            ]);
        }

        foreach ($xmlfile->MODULES as $key => $module) {
            if ((string)$module->moduleid == 'full_text') {
                unset($xmlfile->MODULES[$key]);
            }
        }

        $res = $xmlfile->asXML();
        $fp = fopen($path, "w+");
        if ($fp) {
            fwrite($fp, $res);
        }

        $migrated++;
    }
}

printf("Migration Full Text : " . $migrated . " custom(s) avec un fichier config.xml trouvé(s) et migré(s).\n");
