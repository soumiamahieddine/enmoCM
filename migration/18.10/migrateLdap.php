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
            copy('apps/maarch_entreprise/xml/login_method.xml', "custom/{$custom}/apps/maarch_entreprise/xml/login_method.xml");
        }
        $loginXmlfile = simplexml_load_file($loginMethodPath);

        foreach ($loginXmlfile->METHOD as $method) {
            $method->ENABLED = 'false';
            if ($method->ID == 'ldap') {
                $method->ENABLED = 'true';
            }
        }

        $res = $loginXmlfile->asXML();
        $fp = @fopen($loginMethodPath, "w+");
        if ($fp) {
            fwrite($fp, $res);
        }
    }
}
