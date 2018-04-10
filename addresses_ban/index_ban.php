<?php

$indexFileDirectory = __DIR__ . '/indexes/';
$banDirectory       = __DIR__ . '/BAN/';

set_include_path(__DIR__ . '/../apps/maarch_entreprise/tools/' . PATH_SEPARATOR . get_include_path());
require_once('Zend/Search/Lucene.php');
require("../core/class/class_functions.php");

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
Zend_Search_Lucene_Analysis_Analyzer::setDefault(
    new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive() // we need utf8 for accents
);

foreach ($filesBan as $fileBan) {
    if (!in_array($fileBan, ['.', '..']) && ($handle = fopen($banDirectory . $fileBan, "r")) !== false) {
        $row = 1;
        while (($data = fgetcsv($handle, 0, ";")) !== false) {
            if ($row == 1) {
                $row++;
                continue;
            }
            if (!empty($data[9])) {
                $doc = new Zend_Search_Lucene_Document();

                $func = new functions();
                $doc->addField(Zend_Search_Lucene_Field::UnIndexed('Id', $func->normalize($data[0])));
                $doc->addField(Zend_Search_Lucene_Field::Text('streetName', $func->normalize($data[1])));
                $doc->addField(Zend_Search_Lucene_Field::UnIndexed('streetNumber', $data[3] . ' ' . $data[4]));
                $doc->addField(Zend_Search_Lucene_Field::Text('postalCode', $data[6]));
                $doc->addField(Zend_Search_Lucene_Field::Text('afnorName', $data[9]));
                $doc->addField(Zend_Search_Lucene_Field::Text('city', $func->normalize($data[10])));

                $index->addDocument($doc);

                if ($row == 200) {
                    break;
                }
                if (fmod($row, 100) == 0) {
                    echo $row;
                }
                $row++;
            }
        }
        fclose($handle);
    }
}

$index->commit();



// set_include_path(__DIR__ . '/../apps/maarch_entreprise/tools/' . PATH_SEPARATOR . get_include_path());
// $_ENV['maarch_tools_path'] = __DIR__ . '/../apps/maarch_entreprise/tools/';
// Zend_Search_Lucene_Analysis_Analyzer::setDefault(
//     new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive() // we need utf8 for accents
// );
// Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(Zend_Search_Lucene_Search_QueryParser::B_AND);
// Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
// $index = Zend_Search_Lucene::open($indexFileDirectory);
// $hits = $index->find('gambetta~');
// foreach ($hits as $hit) {
//     var_dump($hit->Id . ': ' . $hit->streetNumber . ' ' . $hit->streetName . ' ' . $hit->postalCode . ' ' . $hit->city . ' ' . $hit->afnorName);
// }

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
