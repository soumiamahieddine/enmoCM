<?php
/*
 *
 *   Copyright 2010 Maarch
 *
 *   This file is part of Maarch Framework.
 *
 *   Maarch Framework is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Maarch Framework is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
 */

/*********************** ADMIN ***********************************/
if (!defined('_LIFE_CYCLE'))  define('_LIFE_CYCLE', 'Cycle de vie');
if (!defined('_ADMIN_LIFE_CYCLE_SHORT'))  define('_ADMIN_LIFE_CYCLE_SHORT', ' Administration des cycles de vie');
if (!defined('_ADMIN_LIFE_CYCLE'))  define('_ADMIN_LIFE_CYCLE', ' Administration des cycles de vie des ressources num&eacute;riques');
if (!defined('_ADMIN_LIFE_CYCLE_DESC'))  define('_ADMIN_LIFE_CYCLE_DESC', 'Administration des cycles de vie des ressources num&eacute;riques.');

if (!defined('_MANAGE_LC_CYCLES'))  define('_MANAGE_LC_CYCLES', 'G&eacute;rer les cycles de vie ("lc_cycles")');
if (!defined('_MANAGE_LC_CYCLE_STEPS'))  define('_MANAGE_LC_CYCLE_STEPS', 'G&eacute;rer les étapes de cycle de vie ("lc_cycles_steps")');
if (!defined('_MANAGE_LC_POLICIES'))  define('_MANAGE_LC_POLICIES', 'G&eacute;rer les polices de cycle de vie ("lc_policies")');

if (!defined('_MANAGE_DOCSERVERS'))  define('_MANAGE_DOCSERVERS', 'G&eacute;rer les serveurs de documents ("docservers")');
if (!defined('_MANAGE_DOCSERVERS_LOCATIONS'))  define('_MANAGE_DOCSERVERS_LOCATIONS', 'G&eacute;rer les emplacements des serveurs de documents ("docserver_locations")');
if (!defined('_MANAGE_DOCSERVER_TYPES'))  define('_MANAGE_DOCSERVER_TYPES', 'G&eacute;rer les types de serveurs de documents ("docserver_types")');

if (!defined('_ADMIN_DOCSERVERS'))  define('_ADMIN_DOCSERVERS', ' Administration des docservers');

/*****************CYCLE_STEPS************************************/
if (!defined('_LC_CYCLE_STEP'))  define('_LC_CYCLE_STEP', 'une étape de cycle de vie');
if (!defined('_LC_CYCLE_STEPS_LIST'))  define('_LC_CYCLE_STEPS_LIST', 'Liste des étapes de cycle de vie');
if (!defined('_ALL_LC_CYCLE_STEPS'))  define('_ALL_LC_CYCLE_STEPS', 'Tout afficher');
if (!defined('_POLICY_ID'))  define('_POLICY_ID', 'Identifiant de la politique d\'archivage');
if (!defined('_CYCLE_STEP_ID'))  define('_CYCLE_STEP_ID', 'Identifiant de l\'etape de cycle de vie ("lc_cycle_steps")');
if (!defined('_CYCLE_STEP_DESC'))  define('_CYCLE_STEP_DESC', 'Description de l\'étape de cycle de vie');
if (!defined('_STEPT_OPERATION'))  define('_STEP_OPERATION', 'Action sur les étapes de cycle de vie');
if (!defined('_IS_ALLOW_FAILURE'))  define('_IS_ALLOW_FAILURE', 'Permettre des drapeaux échec');
if (!defined('_IS_MUST_COMPLETE'))  define('_IS_MUST_COMPLETE', 'IS_MUST_COMPLETE');
if (!defined('_PREPROCESS_SCRIPT'))  define('_PREPROCESS_SCRIPT', 'Script de pré-traitement');
if (!defined('_POSTPROCESS_SCRIPT'))  define('_POSTPROCESS_SCRIPT', 'Script de post-traitement');
if (!defined('_LC_CYCLE_STEP_ADDITION'))  define('_LC_CYCLE_STEP_ADDITION', 'Ajouter une étape de cycle de vie');
if (!defined('_LC_CYCLE_STEP_UPDATED'))  define('_LC_CYCLE_STEP_UPDATED', 'Etape de cycle de vie mise à jour');
if (!defined('_LC_CYCLE_STEP_ADDED'))  define('_LC_CYCLE_STEP_ADDED', 'Etape de cycle de vie ajoutée');
if (!defined('_LC_CYCLE_STEP_DELETED'))  define('_LC_CYCLE_STEP_DELETED', 'Etape de cycle de vie supprimée');
if (!defined('_COLLECTION_IDENTIFIER'))  define('_COLLECTION_IDENTIFIER', 'identifiant de la collection');



/****************CYCLES*************************************/
if (!defined('_CYCLE_ID'))  define('_CYCLE_ID', 'Identifiant du cycle de vie');
if (!defined('_LC_CYCLE_ID'))  define('_LC_CYCLE_ID', 'Identifiant du cycle de vie');
if (!defined('_LC_CYCLE'))  define('_LC_CYCLE', 'un cycle de vie');
if (!defined('_CYCLE_DESC'))  define('_CYCLE_DESC', 'Descriptif du cycle de vie');
if (!defined('_VALIDATION_MODE'))  define('_VALIDATION_MODE', 'Mode de validation');
if (!defined('_ALL_LC_CYCLES'))  define('_ALL_LC_CYCLES', 'Tout afficher');
if (!defined('_LC_CYCLES_LIST'))  define('_LC_CYCLES_LIST', 'Liste des cycles de vie');
if (!defined('_SEQUENCE_NUMBER'))  define('_SEQUENCE_NUMBER', 'Numéro de séquence');
if (!defined('_LC_CYCLE_ADDITION'))  define('_LC_CYCLE_ADDITION', 'Ajouter un cycle de vie');
if (!defined('_LC_CYCLE_ADDED'))  define('_LC_CYCLE_ADDED', 'Cycle de vie ajouté');
if (!defined('_LC_CYCLE_UPDATED'))  define('_LC_CYCLE_UPDATED', 'Cycle de vie mis à jour');
if (!defined('_LC_CYCLE_DELETED'))  define('_LC_CYCLE_DELETED', 'Cycle de vie supprimé');


/***************DOCSERVERS TYPES*************************************/
if (!defined('_DOCSERVER_TYPE_ID'))  define('_DOCSERVER_TYPE_ID', 'Identifiant du type de docserver ');
if (!defined('_DOCSERVER_TYPE'))  define('_DOCSERVER_TYPE', 'un type de docserver ');
if (!defined('_DOCSERVER_TYPES_LIST'))  define('_DOCSERVER_TYPES_LIST', 'Liste de types de docserver ');
if (!defined('_ALL_DOCSERVER_TYPES'))  define('_ALL_DOCSERVER_TYPES', 'Tout afficher ');
if (!defined('_DOCSERVER_TYPE_LABEL'))  define('_DOCSERVER_TYPE_LABEL', 'Label de Type de docserver ');
if (!defined('_IS_COMPRESSED'))  define('_IS_COMPRESSED', 'Compressé');
if (!defined('_IS_META'))  define('_IS_META', 'Contient des métadonnées');
if (!defined('_DOCSERVER_TYPE_ADDITION'))  define('_DOCSERVER_TYPE_ADDITION', 'Ajouter un type de docserver ');
if (!defined('_COMPRESS_MODE'))  define('_COMPRESS_MODE', 'Mode de compression');
if (!defined('_META_TEMPLATE'))  define('_META_TEMPLATE', 'Modèle de métadonnées');
if (!defined('_SIGNATURE_MODE'))  define('_SIGNATURE_MODE', 'Mode de signature');
if (!defined('_CONTAINER_MAX_NUMBER'))  define('_CONTAINER_MAX_NUMBER', 'Taille maximale du conteneur');
if (!defined('_DOCSERVER_TYPE_MODIFICATION'))  define('_DOCSERVER_TYPE_MODIFICATION', 'Modification de type de docserver ');
if (!defined('_DOCSERVER_TYPE_ADDED'))  define('_DOCSERVER_TYPE_ADDED', 'Type de docserver ajouté ');


/***************DOCSERVERS*********************************/
if (!defined('_DOCSERVER_TYPES'))  define('_DOCSERVER_TYPES', 'Type de docserver ');
if (!defined('_DEVICE_LABEL'))  define('_DEVICE_LABEL', 'Label dispositif ');
if (!defined('_SIZE_FORMAT'))  define('_SIZE_FORMAT', 'Format de la taille ');
if (!defined('_SIZE_LIMIT'))  define('_SIZE_LIMIT', 'Taille maximale ');
if (!defined('_ACTUAL_SIZE'))  define('_ACTUAL_SIZE', 'Taille actuelle ');
if (!defined('_DOCSERVER_LOCATIONS'))  define('_DOCSERVER_LOCATIONS', 'Lieu de stockage des docservers ');
if (!defined('_DOCSERVER_MODIFICATION'))  define('_DOCSERVER_MODIFICATION', 'Modification de docservers');
if (!defined('_DOCSERVER_ADDITION'))  define('_DOCSERVER_ADDITION', 'Ajouter un docserver');
if (!defined('_DOCSERVERS_LIST'))  define('_DOCSERVERS_LIST', 'Liste des docservers ');
if (!defined('_ALL_DOCSERVERS'))  define('_ALL_DOCSERVERS', 'Tout afficher ');
if (!defined('_DOCSERVER'))  define('_DOCSERVER', 'un docserver');


/************DOCSERVER LOCATIONS******************************/
if (!defined('_DOCSERVER_LOCATION_ADDITION'))  define('_DOCSERVER_LOCATION_ADDITION', 'Ajouter un lieu de stockage de docservers');
if (!defined('_DOCSERVER_LOCATION_MODIFICATION'))  define('_DOCSERVER_LOCATION_MODIFICATION', 'Modification lieu de stockage de docservers');
if (!defined('_ALL_DOCSERVER_LOCATIONS'))  define('_ALL_DOCSERVER_LOCATIONS', 'Tout afficher');
if (!defined('_DOCSERVER_LOCATIONS_LIST'))  define('_DOCSERVER_LOCATIONS_LIST', 'Liste des lieux de stockage');
if (!defined('_DOCSERVER_LOCATION'))  define('_DOCSERVER_LOCATION', 'un lieu de stockage de docservers');
if (!defined('_IPV4'))  define('_IPV4', 'Adresse IPv4');
if (!defined('_IPV6'))  define('_IPV6', 'Adresse IPv6');
if (!defined('_NET_DOMAIN'))  define('_NET_DOMAIN', 'Domaine');
if (!defined('_DOCSERVER_LOCATION_ID'))  define('_DOCSERVER_LOCATION_ID', 'Identifiant de lieu de stockage de docservers');
if (!defined('_MASK'))  define('_MASK', 'Masque');


/*************CYCLE POLICIES*************************************/
if (!defined('_LC_POLICY'))  define('_LC_POLICY', 'une politique d\'archivage');
if (!defined('_POLICY_NAME'))  define('_POLICY_NAME', 'Nom de la politique');
if (!defined('_LC_POLICY_ID'))  define('_LC_POLICY_ID', 'ID de la politique');
if (!defined('_LC_POLICY_NAME'))  define('_LC_POLICY_NAME', 'Nom de la politique');
if (!defined('_POLICY_DESC'))  define('_POLICY_DESC', 'Descriptif de la politique');
if (!defined('_LC_POLICY_ADDITION'))  define('_LC_POLICY_ADDITION', 'Ajouter une politique de cycle de vie');
if (!defined('_LC_POLICIES_LIST'))  define('_LC_POLICIES_LIST', 'Liste des politiques de cycle de vie');
if (!defined('_ALL_LC_POLICIES'))  define('_ALL_LC_POLICIES', 'Tout afficher');
if (!defined('_LC_POLICY_UPDATED'))  define('_LC_POLICY_UPDATED', 'Politique de cycle de vie mise à jour');
if (!defined('_LC_POLICY_ADDED'))  define('_LC_POLICY_ADDED', 'Politique de cycle de vie ajoutée');
if (!defined('_LC_POLICY_DELETED'))  define('_LC_POLICY_DELETED', 'Politique de cycle de vie supprimée');
if (!defined('_LC_POLICY_MODIFICATION'))  define('_LC_POLICY_MODIFICATION', 'Modification de la politique d\'archivage');
?>
