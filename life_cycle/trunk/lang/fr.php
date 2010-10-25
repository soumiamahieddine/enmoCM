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
if (!defined('_LIFE_CYCLE_COMMENT'))  define('_LIFE_CYCLE_COMMENT', 'Gestion du cycle de vie');
if (!defined('_ADMIN_LIFE_CYCLE_SHORT'))  define('_ADMIN_LIFE_CYCLE_SHORT', ' Administration des cycles de vie');
if (!defined('_ADMIN_LIFE_CYCLE'))  define('_ADMIN_LIFE_CYCLE', ' Politiques d&rsquo;archivage');
if (!defined('_ADMIN_LIFE_CYCLE_DESC'))  define('_ADMIN_LIFE_CYCLE_DESC', 'Définition des politiques d&rsquo;archivage, cycles et étapes jusqu&rsquo;au sort final');
if (!defined('_MANAGE_LC_CYCLES'))  define('_MANAGE_LC_CYCLES', 'G&eacute;rer les cycles de vie ');
if (!defined('_MANAGE_LC_CYCLE_STEPS'))  define('_MANAGE_LC_CYCLE_STEPS', 'G&eacute;rer les étapes de cycle de vie');
if (!defined('_MANAGE_LC_POLICIES'))  define('_MANAGE_LC_POLICIES', 'G&eacute;rer les politiques de cycle de vie ');


/*****************CYCLE_STEPS************************************/
if (!defined('_LC_CYCLE_STEPS'))  define('_LC_CYCLE_STEPS', '');
if (!defined('_LC_CYCLE_STEP'))  define('_LC_CYCLE_STEP', 'une étape de cycle de vie');
if (!defined('_COLLECTION_IDENTIFIER'))  define('_COLLECTION_IDENTIFIER', 'identifiant de la collection');
if (!defined('_LC_CYCLE_STEPS_LIST'))  define('_LC_CYCLE_STEPS_LIST', 'Liste des étapes de cycle de vie');
if (!defined('_ALL_LC_CYCLE_STEPS'))  define('_ALL_LC_CYCLE_STEPS', 'Tout afficher');
if (!defined('_POLICY_ID'))  define('_POLICY_ID', 'Identifiant de la politique d\'archivage');
if (!defined('_CYCLE_STEP_ID'))  define('_CYCLE_STEP_ID', 'Identifiant de l\'etape de cycle de vie ');
if (!defined('_CYCLE_STEP_DESC'))  define('_CYCLE_STEP_DESC', 'Description de l\'étape de cycle de vie');
if (!defined('_STEP_OPERATION'))  define('_STEP_OPERATION', 'Action sur les étapes de cycle de vie');
if (!defined('_IS_ALLOW_FAILURE'))  define('_IS_ALLOW_FAILURE', 'Permettre des drapeaux échec');
if (!defined('_IS_MUST_COMPLETE'))  define('_IS_MUST_COMPLETE', 'IS_MUST_COMPLETE');
if (!defined('_PREPROCESS_SCRIPT'))  define('_PREPROCESS_SCRIPT', 'Script de pré-traitement');
if (!defined('_POSTPROCESS_SCRIPT'))  define('_POSTPROCESS_SCRIPT', 'Script de post-traitement');

if (!defined('_LC_CYCLE_STEP_ADDITION'))  define('_LC_CYCLE_STEP_ADDITION', 'Ajouter une étape de cycle de vie');
if (!defined('_LC_CYCLE_STEP_UPDATED'))  define('_LC_CYCLE_STEP_UPDATED', 'Etape de cycle de vie mise à jour');
if (!defined('_LC_CYCLE_STEP_ADDED'))  define('_LC_CYCLE_STEP_ADDED', 'Etape de cycle de vie ajoutée');
if (!defined('_LC_CYCLE_STEP_DELETED'))  define('_LC_CYCLE_STEP_DELETED', 'Etape de cycle de vie supprimée');
if (!defined('_LC_CYCLE_STEP_MODIFICATION'))  define('_LC_CYCLE_STEP_MODIFICATION', 'Modification d\' une étape de cycle de vie');
//if (!defined('_LC_CYCLE_STEPS'))  define('_LC_CYCLE_STEPS', 'une étape de cycle de vie');


/****************CYCLES*************************************/
if (!defined('_CYCLE_ID'))  define('_CYCLE_ID', 'Identifiant du cycle de vie');
if (!defined('_LC_CYCLE_ID'))  define('_LC_CYCLE_ID', 'Identifiant du cycle de vie');
if (!defined('_LC_CYCLE'))  define('_LC_CYCLE', 'un cycle de vie');
if (!defined('_LC_CYCLES'))  define('_LC_CYCLES', 'Cycle(s) de vie');
if (!defined('_CYCLE_DESC'))  define('_CYCLE_DESC', 'Descriptif du cycle de vie');
if (!defined('_VALIDATION_MODE'))  define('_VALIDATION_MODE', 'Mode de validation');
if (!defined('_ALL_LC_CYCLES'))  define('_ALL_LC_CYCLES', 'Tout afficher');
if (!defined('_LC_CYCLES_LIST'))  define('_LC_CYCLES_LIST', 'Liste des cycles de vie');
if (!defined('_SEQUENCE_NUMBER'))  define('_SEQUENCE_NUMBER', 'Numero de séquence');

if (!defined('_LC_CYCLE_ADDITION'))  define('_LC_CYCLE_ADDITION', 'Ajouter un cycle de vie');
if (!defined('_LC_CYCLE_ADDED'))  define('_LC_CYCLE_ADDED', 'Cycle de vie ajouté');
if (!defined('_LC_CYCLE_UPDATED'))  define('_LC_CYCLE_UPDATED', 'Cycle de vie mis à jour');
if (!defined('_LC_CYCLE_DELETED'))  define('_LC_CYCLE_DELETED', 'Cycle de vie supprimé');
if (!defined('_LC_CYCLE_MODIFICATION'))  define('_LC_CYCLE_MODIFICATION', 'Modification du cycle de vie');


/*************CYCLE POLICIES*************************************/
if (!defined('_LC_POLICIES'))  define('_LC_POLICIES', '');
if (!defined('_LC_POLICY'))  define('_LC_POLICY', 'une politique d\'archivage');
if (!defined('_POLICY_NAME'))  define('_POLICY_NAME', 'Nom de la politique');
if (!defined('_LC_POLICY_ID'))  define('_LC_POLICY_ID', 'Identifiant de la politique');
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
