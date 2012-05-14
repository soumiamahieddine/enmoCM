<?php

//LANGUAGE
if (!defined('_LANGUAGE')) {
    define('_LANGUAGE', "Langue");
}
if (!defined('_CHOOSE_LANGUAGE')) {
    define('_CHOOSE_LANGUAGE', "Choisissez votre langue");
}

//WELCOME
if (!defined('_WELCOME')) {
    define('_WELCOME', "Bienvenue");
}

if (!defined('_DESC')) {
    define('_DESC', "Bienvenue dans l'assistant d'installation de Maarch !
					<br>Maarch est un Système d'Archivage Electronique doté de capacités de GED, Gestion de Courrier, travail collaboratif, et SAE normé OAIS et NFZ42-013. Maarch est un produit Open Source sur licence GPL v3 : il n'y a pas de coûts de licence, mais des services professionnels certifiés fournis par Maarch SAS et son réseau de partenaires à travers le monde.
					<br>Les services couvrent l'audit, l'installation, le paramétrage, la personnalisation, l'interfaçage avec votre SI, la formation, le support et la maintenance. Reportez vous sur http://www.maarch.com pour plus d'informations sur les services.");
}



//LICENCE
if (!defined('_LICENCE')) {
    define('_LICENCE', "Licence");
}

if (!defined('_OK_WITH_LICENCE')) {
    define('_OK_WITH_LICENCE', "J'accepte les termes de la licence");
}

//PREREQUISITES
if (!defined('_PREREQUISITES')) {
    define('_PREREQUISITES', "Pré-requis");
}
if (!defined('_PREREQUISITES_HEAD')) {
    define('_PREREQUISITES_HEAD', "Pré-requis");
}

if (!defined('_LINK')) {
    define('_LINK', "Voir <A>http://www.maarch.org/projets/entreprise/architecture-technique-et-prerequis-pour-maarch-entreprise-1.3</A>");
}
if (!defined('_PREREQUISITES_EXP')) {
    define('_PREREQUISITES_EXP', "L'installation de Maarch nécessite un certain nombre de pré-requis au niveau de l'installation de PHP. Reportez-vous à la page <A>http://www.maarch.org/projets/entreprise/architecture-technique-et-prerequis-pour-maarch-entreprise-1.3</A> pour les détails");
}
if (!defined('_ACTIVATED')) {
    define('_ACTIVATED', "Conforme");
}if (!defined('_OPTIONNAL')) {
    define('_OPTIONNAL', "Non conforme mais optionnel");
}if (!defined('_NOT_ACTIVATED')) {
    define('_NOT_ACTIVATED', "Non conforme");
}

if (!defined('_GENERAL')) {
    define('_GENERAL', "G&eacute;n&eacute;ral");
}
if (!defined('_PHP_VERSION')) {
    define('_PHP_VERSION', "Version de PHP (5.3 ou sup.)");
}
if (!defined('_MAARCH_PATH_RIGHTS')) {
    define('_MAARCH_PATH_RIGHTS', "Droits de lecture et d'&eacute;criture du r&eacute;pertoire racine de Maarch");
}
if (!defined('_THE_MAARCH_PATH_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS')) {
    define('_THE_MAARCH_PATH_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS', "Les droits du r&eacute;pertoire racine de Maarch ne sont pas corrects");
}
if (!defined('_PGSQL')) {
    define('_PGSQL', "Librairie pgsql");
}
if (!defined('_GD')) {
    define('_GD', "Librairie gd");
}
if (!defined('_SVN')) {
    define('_SVN', "librairie svn");
}
if (!defined('_PEAR')) {
    define('_PEAR', "PEAR");
}
if (!defined('_MIMETYPE')) {
    define('_MIMETYPE', "MIME-TYPE");
}
if (!defined('_CLITOOLS')) {
    define('_CLITOOLS', "CLITools");
}
if (!defined('_ERROR_REPORTING')) {
    define('_ERROR_REPORTING', "error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED)");
}
if (!defined('_DISPLAY_ERRORS')) {
    define('_DISPLAY_ERRORS', "display_errors (On)");
}
if (!defined('_SHORT_OPEN_TAGS')) {
    define('_SHORT_OPEN_TAGS', "short_open_tags (On)");
}
if (!defined('_MAGIC_QUOTES_GPC')) {
    define('_MAGIC_QUOTES_GPC', "magic_quotes_gpc (Off)");
}

if (!defined('_MUST_FIX')) {
    define('_MUST_FIX', "Vous devez corriger les erreurs avant de continuer");
}


//DOCSERVERS
if (!defined('_DOCSERVERS')) {
    define('_DOCSERVERS', "Zones de stockage");
}
if (!defined('_DOCSERVERS_EXP')) {
    define('_DOCSERVERS_EXP', "Explications sur les zones de stockage");
}
if (!defined('_DOCSERVER_ROOT')) {
    define('_DOCSERVER_ROOT', "Chemin du répertoire");
}
if (!defined('_CREATE_DOCSERVERS')) {
    define('_CREATE_DOCSERVERS', "Enregistrer");
}


//DATABASE
if (!defined('_DATABASE')) {
    define('_DATABASE', "Base de données");
}
if (!defined('_DATABASE_DESC')) {
    define('_DATABASE_ADD_INF', "L'installeur Maarch va créer une nouvelle base de données pour vous. Entrez le nom de la base (ex:maarch_db)");
}
if (!defined('_DATABASE_EXP')) {
    define('_DATABASE_EXP', "Maarch utilise en standard le moteur de base de données libre PostgreSQL (Version 8.3 ou supérieure - Recommandé : version 9). Précisez le nom du serveur, le n° de port, l'utlisateur et le mot de passe de connexion.");
}

if (!defined('_DATABASESERVER')) {
    define('_DATABASESERVER', "Serveur");
}
if (!defined('_DATABASESERVERPORT')) {
    define('_DATABASESERVERPORT', "Port");
}
if (!defined('_DATABASEUSER')) {
    define('_DATABASEUSER', "Utilisateur");
}
if (!defined('_DATABASEPASSWORD')) {
    define('_DATABASEPASSWORD', "Mot de passe");
}
if (!defined('_DATABASENAME')) {
    define('_DATABASENAME', "Nom");
}
if (!defined('_DATABASETYPE')) {
    define('_DATABASETYPE', "Type");
}
if (!defined('_DATASET_CHOICE')) {
    define('_DATASET_CHOICE', "Choix du jeu de données d'exemple à importer");
}
if (!defined('_DATASET_EXP')) {
    define('_DATASET_EXP', "L'installation propose deux jeux de données : l'un relativement simple mettant en avant les fonctionnalités SAE de base (data.sql), et l'autre plus complet illustrant un circuit courrier en collectivité (data_mlb.sql). Les deux jeux comprennent des utilisateurs, des types de documents, et l'ensemble du référentiel pour une compréhension globale de l'outil.");
}
if (!defined('_DATASET')) {
    define('_DATASET', "Jeu de données");
}
if (!defined('_CHOOSE')) {
    define('_CHOOSE', "Choisissez...");
}
if (!defined('_INSTALL_SUCCESS')) {
    define('_INSTALL_SUCCESS', "Installation terminée avec succès");
}
if (!defined('_SUBMIT')) {
    define('_SUBMIT', "Tester");
}


//PASSWORD
if (!defined('_PASSWORD')) {
    define('_PASSWORD', "Mot de passe");
}
if (!defined('_CHOOSE_ADMIN_PASSWORD')) {
    define('_CHOOSE_ADMIN_PASSWORD', "Choisissez le mot de passe administrateur");
}

//RESUME
if (!defined('_RESUME')) {
    define('_RESUME', "Résumé");
}

if (!defined('_START_MEP_1_3')) {
    define('_START_MEP_1_3', "Démarrer avec Maarch Entreprise 1.3");
}

//ERROR
if (!defined('_ERROR')) {
    define('_ERROR', "Erreur");
}
if (!defined('_NO_STEP')) {
    define('_NO_STEP', "Aucune étape choisie");
}
if (!defined('_BAD_STEP')) {
    define('_BAD_STEP', "L'étape choisie n'existe pas");
}
if (!defined('_INSTALL_ISSUE')) {
    define('_INSTALL_ISSUE', "Problème lors de l'installation");
}
if (!defined('_TRY_AGAIN')) {
    define('_TRY_AGAIN', "Veuillez réessayer");
}

//BUTTON
if (!defined('_PREVIOUS')) {
    define('_PREVIOUS', "Précédent");
}
if (!defined('_NEXT')) {
    define('_NEXT', "Suivant");
}






