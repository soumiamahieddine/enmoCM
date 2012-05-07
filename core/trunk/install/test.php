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
echo 'php version:' . $install->isPhpVersion();
echo '<br>';
echo '<h1>LIBRARY TEST</h1>';
echo 'postgres library:' . $install->isPhpRequirements('pgsql');
echo '<br>';
echo 'GD library:' . $install->isPhpRequirements('gd');
echo '<br>';
echo 'svn library (optionnal and only under linux system):' . $install->isPhpRequirements('svn');
echo '<br>';
echo 'PEAR:' . $install->isPearRequirements('System.php');
echo '<br>';
echo 'PEAR MIME TYPE:' . $install->isPearRequirements('MIME/Type.php');
echo '<br>';
echo 'PEAR MAARCH CLITOOLS (optionnal, only for batchs):' . $install->isPearRequirements('Maarch_CLITools/FileHandler.php');
echo '<br>';
echo '<h1>INI TEST</h1>';
echo 'error_reporting (must be E_ALL & ~E_NOTICE & ~E_DEPRECATED):' . $install->isIniErrorRepportingRequirements();
echo '<br>';
echo 'display_errors:' . $install->isIniDisplayErrorRequirements();
echo '<br>';
echo 'short_open_tag:' . $install->isIniShortOpenTagRequirements();
echo '<br>';
echo 'magic_quotes_gpc:' . $install->isIniMagicQuotesGpcRequirements();
echo '<br>';
echo '</body><br>';
echo $install->loadFooter();
echo '</html>';
