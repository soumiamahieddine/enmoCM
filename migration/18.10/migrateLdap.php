<?php

require '../../vendor/autoload.php';

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    $xmlfile = null;
    $path = "custom/{$custom}/apps/maarch_entreprise/xml/config.xml";
    if (file_exists($path)) {
        $xmlfile = simplexml_load_file($path);
    }

    if ($xmlfile && (string)$xmlfile->CONFIG->ldap == 'true') {
        $loginMethodPath = "custom/{$custom}/apps/maarch_entreprise/xml/login_method.xml";

        if (!file_exists($loginMethodPath)) {
            $newXml = new DomDocument("1.0", "UTF-8");
            $rRootNode = $newXml->createElement('ROOT');
            $newXml->appendChild($rRootNode);
            $newXml->save($loginMethodPath);
        }
        $loginXmlfile = simplexml_load_file($loginMethodPath);

        $newloginMethod = $loginXmlfile->addChild('METHOD');
        $newloginMethod->addChild('ID', 'ldap');
        $newloginMethod->addChild('NAME', '_STANDARD_LOGIN');
        $newloginMethod->addChild('SCRIPT', 'standard_connect.php');
        $newloginMethod->addChild('ENABLED', 'true');

        $res = $loginXmlfile->asXML();
        $fp = @fopen($loginMethodPath, "w+");
        if ($fp) {
            fwrite($fp, $res);
        }
    }
}
