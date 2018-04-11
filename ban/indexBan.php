<?php

require '../vendor/autoload.php';
$indexFileDirectory = __DIR__ . '/indexes/';
$banDirectory       = __DIR__ . '/src/';

$filesBan = scandir($banDirectory);
if (!is_dir($indexFileDirectory)) {
    $index = Zend_Search_Lucene::create($indexFileDirectory);
} else {
    if (isDirEmpty($indexFileDirectory)) {
        $index = Zend_Search_Lucene::create($indexFileDirectory);
    } else {
        $index = Zend_Search_Lucene::open($indexFileDirectory);
    }
}
$index->setFormatVersion(Zend_Search_Lucene::FORMAT_2_3); // we set the lucene format to 2.3
Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
$index->setMaxBufferedDocs(1000);

$row = 1;
foreach ($filesBan as $fileBan) {
    if (!in_array($fileBan, ['.', '..']) && ($handle = fopen($banDirectory . $fileBan, "r")) !== false) {
        echo "$fileBan\n";
        $duplicateAddresses = [];
        $currentCity = '';
        $i = 1;
        while (($data = fgetcsv($handle, 0, ";")) !== false) {
            if ($i == 1) {
                $i++;
                continue;
            }

            if (!empty($data[9])) {
                if ($currentCity != $data[6]) {
                    $duplicateAddresses = [];
                }
                $currentCity = $data[6];

                if (empty($duplicateAddresses[$data[3] . $data[4] . $data[9] . $data[6]])) {
                    $doc = new Zend_Search_Lucene_Document();

                    $doc->addField(Zend_Search_Lucene_Field::UnIndexed('banId', \SrcCore\models\TextFormatModel::normalize(['string' => $data[0]])));
                    if (!empty($data[1])) {
                        $doc->addField(Zend_Search_Lucene_Field::Text('streetName', \SrcCore\models\TextFormatModel::normalize(['string' => $data[1]])));
                    }
                    $streetNumber = empty($data[4]) ? $data[3] : ($data[3] . ' ' . $data[4]);
                    $doc->addField(Zend_Search_Lucene_Field::Text('streetNumber', $streetNumber));
                    $doc->addField(Zend_Search_Lucene_Field::Text('postalCode', $data[6]));
                    $doc->addField(Zend_Search_Lucene_Field::Text('afnorName', $data[9]));
                    $doc->addField(Zend_Search_Lucene_Field::Text('city', \SrcCore\models\TextFormatModel::normalize(['string' => $data[10]])));

                    $index->addDocument($doc);
                    $duplicateAddresses[$data[3] . $data[4] . $data[9] . $data[6]] = true;
                }
            }
            if (fmod($row, 100) == 0) {
                echo "$row\n";
            }
            $row++;
        }
        fclose($handle);
    }
}

$index->commit();

/**
* Check if a folder is empty
* @param  $dir string path of the directory to chek
* @return boolean true if the directory exists
*/
function isDirEmpty($dir)
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
