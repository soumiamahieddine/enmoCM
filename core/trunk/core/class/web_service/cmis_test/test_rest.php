<?php

if (!isset($_REQUEST['resource'])) {
    $_REQUEST['resource']  = 'folder';
}

//INIT CURL
$curl = curl_init();

//BASIC AUTHENTICATION
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, 'bblier:maarch');

//WS URL
$url = 'http://127.0.0.1/maarch_entreprise_trunk/ws_server.php?REST';
if (isset($_REQUEST['resource']) && !empty($_REQUEST['resource'])) {
    $url .= '/' . $_REQUEST['resource'];
}
if (isset($_REQUEST['idResource']) && !empty($_REQUEST['idResource'])) {
    $url .= '/' . $_REQUEST['idResource'];
}
curl_setopt($curl, CURLOPT_URL, $url . '/');

//POST CONTENT
$xmlAtomFileContent = base64_encode(file_get_contents('create_folder.atom.xml'));
curl_setopt($curl, CURLOPT_POSTFIELDS, 'atomFileContent=' . $xmlAtomFileContent);
//HTTP METHOD
if ($_REQUEST['method'] == 'post' || !isset($_REQUEST['method'])) {
    curl_setopt($curl, CURLOPT_POST, 1);
} else {
    //GET, PUT, DELETE METHOD
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($_REQUEST['method']));
    //curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: ' . $_REQUEST['method']));
}

//RESULT
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$page = curl_exec($curl);
curl_close($curl);
print($page);
