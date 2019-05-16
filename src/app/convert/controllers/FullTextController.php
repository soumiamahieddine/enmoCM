<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Full Text Controller
* @author dev@maarch.org
*/

namespace Convert\controllers;

use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;

class FullTextController
{
    public static function indexDocument(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'collId']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::stringType($args, ['collId']);

        $document = AdrModel::getConvertedDocumentById([
            'select'    => ['docserver_id', 'path', 'filename', 'fingerprint'],
            'resId'     => $args['resId'],
            'collId'    => $args['collId'],
            'type'      => 'PDF'
        ]);
        if (empty($document)) {
            return ['errors' => 'Converted document does not exist'];
        }

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !is_dir($docserver['path_template'])) {
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];
        if (!is_file($pathToDocument)) {
            return ['errors' => 'Document not found on docserver'];
        }

        $fullTextDocserver = DocserverModel::getCurrentDocserver(['collId' => 'letterbox_coll', 'typeId' => 'FULLTEXT']);
        if (empty($fullTextDocserver['path_template']) || !is_dir($fullTextDocserver['path_template'])) {
            return ['errors' => 'FullText docserver does not exist'];
        }

        if (FullTextController::isDirEmpty($fullTextDocserver['path_template'])) {
            $index = \Zend_Search_Lucene::create($fullTextDocserver['path_template']);
        } else {
            $index = \Zend_Search_Lucene::open($fullTextDocserver['path_template']);
        }

        $index->setFormatVersion(\Zend_Search_Lucene::FORMAT_2_3);
        \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
        $index->setMaxBufferedDocs(1000);

        $tmpFile = CoreConfigModel::getTmpPath() . basename($pathToDocument) . rand() . '.txt';
        exec("pdftotext " . escapeshellarg($pathToDocument) . " " . escapeshellarg($tmpFile));

        if (!is_file($tmpFile)) {
            return ['errors' => 'Command pdftotext did not work'];
        }

        $fp = fopen($tmpFile, "r");
        $fileContent = fread($fp, filesize($tmpFile));
        fclose($fp);

        $fileContent = trim($fileContent);
        $fileContent = TextFormatModel::normalize(['string' => $fileContent]);
        $fileContent = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fileContent);

        $doc = new \Zend_Search_Lucene_Document();

        $doc->addField(\Zend_Search_Lucene_Field::UnIndexed('Id', (integer)$args['resId']));
        $doc->addField(\Zend_Search_Lucene_Field::UnStored('contents', $fileContent));

        $index->addDocument($doc);
        $index->commit();
        $index->optimize();

        unlink($tmpFile);

        return ['success' => 'success'];
    }

    public static function isDirEmpty($dir)
    {
        $dir = opendir($dir);
        $isEmpty = true;
        while (($entry = readdir($dir)) !== false) {
            if ($entry !== '.' && $entry !== '..') {
                $isEmpty = false;
                break;
            }
        }
        closedir($dir);

        return $isEmpty;
    }
}
