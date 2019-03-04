<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Merge Controller
 *
 * @author dev@maarch.org
 */

namespace ContentManagement\controllers;

use SrcCore\models\ValidatorModel;

include_once('vendor/tinybutstrong/opentbs/tbs_plugin_opentbs.php');


class MergeController
{
    public static function mergeDocument(array $args)
    {
        ValidatorModel::stringType($args, ['path', 'content']);

        $tbs = new \clsTinyButStrong();
        $tbs->NoErr = true;
        $tbs->PlugIn(TBS_INSTALL, OPENTBS_PLUGIN);

        if (!empty($args['path'])) {
            $pathInfo = pathinfo($args['path']);
            $extension = $pathInfo['extension'];
        } else {
            $tbs->Source = $args['content'];
            $extension = 'unknow';
            $args['path'] = null;
        }

        if ($extension == 'odt') {
            $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
//            $tbs->LoadTemplate("{$args['path']}#content.xml;styles.xml", OPENTBS_ALREADY_UTF8);
        } elseif ($extension == 'docx') {
            $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
//            $tbs->LoadTemplate("{$args['path']}#word/header1.xml;word/footer1.xml", OPENTBS_ALREADY_UTF8);
        } else {
            $tbs->LoadTemplate($args['path'], OPENTBS_ALREADY_UTF8);
        }

        //TODO
        $dataToBeMerge['contact']['contact_title'] = 'Mister';
        $dataToBeMerge['contact']['title'] = 'Miss';
        $dataToBeMerge['contact']['contact_firstname'] = 'Banane';
        $dataToBeMerge['contact']['lastname'] = 'Smith';

        foreach ($dataToBeMerge as $key => $value) {
            $tbs->MergeField($key, $value);
        }

        if (in_array($extension, ['odt', 'ods', 'odp', 'xlsx', 'pptx', 'docx', 'odf'])) {
            $tbs->Show(OPENTBS_STRING);
        } else {
            $tbs->Show(TBS_NOTHING);
        }

        return ['encodedDocument' => base64_encode($tbs->Source)];
    }
}
