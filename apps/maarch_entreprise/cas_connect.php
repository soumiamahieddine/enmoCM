<?php

include_once('apps/maarch_entreprise/tools/phpCAS/CAS.php');
require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_history.php');

// Les paramètres du serveur CAS
$cas_serveur   = "192.168.21.36";
$cas_port      = 443;
$cas_context   = "/cas-server-webapp-4.0.0";
// $cas_chemin_ac = "apps/maarch_entreprise/tools/phpCAS/AC-RGS-Certigna-Racine-SHA1.pem" ;

phpCAS::setDebug();
phpCAS::setVerbose(true);

// Initialisation phpCAS en protocole CAS 2.0
phpCAS::client(CAS_VERSION_2_0, $cas_serveur, $cas_port, $cas_context, true);

// Le certificat de l'autorité racine
// phpCAS::setCasServerCACert($cas_chemin_ac);
phpCAS::setNoCasServerValidation();

// // L'authentification.
phpCAS::forceAuthentication();

// // Lecture identifiant utilisateur (courriel)
$userId = phpCAS::getUser();
echo 'Identifiant : ' . phpCAS::getUser();
echo '<br/> phpCAS version : ' . phpCAS::getVersion();

$loginArray['password'] = 'maarch';

$_SESSION['web_cas_url'] = 'https://'. $cas_serveur . $cas_context .'/logout';

/**** CONNECTION A MAARCH ****/
header("location: " . $_SESSION['config']['businessappurl'] 
    . "log.php?login=" . $userId 
    . "&pass=" . $loginArray['password']);

//Traces fonctionnelles
$trace = new history();
$trace->add("users",
            $userId,
            "LOGIN",
            "userlogin",
            _CONNECTION_CAS_OK,
            $_SESSION['config']['databasetype'],
            "ADMIN",
            false);
exit();
