<?php

include_once '../core/init.php';

require_once 'install/class/class_install.php';

$install = new install();
$languages = $install->getlanguages();
$install->loadLang($languages[1]);

echo '<html>';
echo $install->loadHeader();
echo '<body>';
echo $install->loadview('helloWorld');
echo '<br>';
echo 'php version:' . $install->isPhpRequirements();
echo '<br>';
echo 'postgres library:' . $install->isPostgresRequirements();
echo '<br>';
echo 'GD library:' . $install->isGdRequirements();
echo '<br>';
echo 'Mime type:' . $install->isMimeTypeRequirements();
echo '<br>';
echo 'svn library (optionnal and only under linux system):' . $install->isSvnRequirements();
echo '<br>';
echo '</body>';
echo $install->loadFooter();
echo '</html>';
