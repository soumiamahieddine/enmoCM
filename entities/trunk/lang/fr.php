<?php
/*
 *
 *    Copyright 2008,2009 Maarch
 *
 *  This file is part of Maarch Framework.
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
 *    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
 */

/*************************** Entites management *****************/
define('_ADD_ENTITY','Entit&eacute; ajout&eacute;e');
define('_ENTITY_ADDITION', 'Ajout d&rsquo;une entit&eacute;');
define('_ENTITY_MODIFICATION', 'Modification d&rsquo;une entit&eacute;');
define('_ENTITY_AUTORIZATION', 'Autorisation d&rsquo;une entit&eacute;');
define('_ENTITY_SUSPENSION', 'Suspension d&rsquo;une entit&eacute;');
define('_ENTITY_DELETION', 'Suppression d&rsquo;une entit&eacute;');
define('_ENTITY_DELETED', 'Entit&eacute; supprim&eacute;e');
define('_ENTITY_UPDATED', 'Entit&eacute; modifi&eacute;e');
define('_ENTITY_AUTORIZED', 'Entit&eacute; autoris&eacute;');
define('_ENTITY_SUSPENDED', 'Entit&eacute; suspendue');
define('_ENTITY', 'Entit&eacute;');
define('_ENTITIES', 'Entit&eacute;s');
define('_ENTITIES_COMMENT', 'Entit&eacute;s');
define('_ALL_ENTITIES', 'Toutes les entit&eacute;s');
define('_ENTITIES_LIST', 'Liste des entit&eacute;s');
define('_MANAGE_ENTITIES', 'G&eacute;rer les Entit&eacute;s');
define('_MANAGE_ENTITIES_DESC', 'Administrer les Entit&eacute;s, ...');
define('_ENTITY_MISSING', 'Cette entit&eacute; n&rsquo;existe pas');
define('_ENTITY_TREE', 'Arborescence des entit&eacute;s');
define('_ENTITY_TREE_DESC', 'Voir l&rsquo;arborescence des entit&eacute;s');
define('_ENTITY_HAVE_CHILD', 'Cette entit&eacute; poss&egrave;de des sous entit&eacute;s');
define('_ENTITY_IS_RELATED', 'Cette entit&eacute; est reli&eacute;e &agrave; des utilisateurs');
define('_TYPE', 'Type');

/*************************** Users - Entites management *****************/
define('_ENTITY_USER_DESC', 'Mettre en relation des entit&eacute;s et des utilisateurs');
define('_ENTITIES_USERS', 'Relation entit&eacute;s - utilisateurs');
define('_ENTITIES_USERS_LIST', 'Liste des utilisateurs');
define('_USER_ENTITIES_TITLE', 'L&rsquo;utilisateur appartient aux entit&eacute;s suivantes');
define('_USER_ENTITIES_ADDITION', 'Relation Utilisateur - Entit&eacute;s');
define('_USER_BELONGS_NO_ENTITY', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucune entit&eacute;');
define('_CHOOSE_ONE_ENTITY', 'Choisissez au moins une entit&eacute;');
define('_CHOOSE_ENTITY', 'Choisissez une entit&eacute;');
define('_CHOOSE_PRIMARY_ENTITY', 'Choisir comme entit&eacute; primaire');
define('_PRIMARY_ENTITY', 'Entit&eacute; primaire');
define('_DELETE_ENTITY', 'Supprimer le(s) entit&eacute;(s)');
define('USER_ADD_ENTITY', 'Ajouter une entit&eacute;');
define('_ADD_TO_ENTITY', 'Ajouter &agrave; une entit&eacute;');
define('_NO_ENTITY_SELECTED', 'Aucune entit&eacute; s&eacute;lectionn&eacute;e');
define('_NO_PRIMARY_ENTITY', 'L&rsquo;entit&eacute; primaire est obligatoire');
define('_NO_ENTITIES_DEFINED_FOR_YOU', 'Aucune entit&eacute; d&eacute;finie pour cet utilisateur');
define('_LABEL_MISSING', 'Il manque le nom de l&rsquo;entit&eacute;');
define('_ID_MISSING', 'Il manque l&rsquo;indentifiant de l&rsquo;entit&eacute;');
define('_TYPE_MISSING', 'Le type de l&rsquo;entit&eacute; est obligatoire');
define('_PARENT_MISSING', 'L&rsquo;entit&eacute; parente est obligatoire');
define('_ENTITY_UNKNOWN', 'Entit&eacute; Inconnue');

/*************************** Entites form *****************/
define('_ENTITY_LABEL', 'Nom');
define('_ENTITY_ADR_1', 'Adresse 1');
define('_ENTITY_ADR_2', 'Adresse 2');
define('_ENTITY_ADR_3', 'Adresse 3');
define('_ENTITY_ZIPCODE', 'Code Postal');
define('_ENTITY_CITY', 'Ville');
define('_ENTITY_COUNTRY', 'Pays');
define('_ENTITY_EMAIL', 'Email');
define('_ENTITY_BUSINESS', 'N&deg; SIRET');
define('_ENTITY_PARENT', 'Entit&eacute; parente');
define('_CHOOSE_ENTITY_PARENT', 'Choisissez l&rsquo;entit&eacute; parente');
define('_CHOOSE_ENTITY_TYPE', 'Choisissez le type de l&rsquo;entit&eacute;');
define('_ENTITY_TYPE', 'Type de l&rsquo;entit&eacute;');

define('_TO_USERS_OF_ENTITIES', 'Vers des utilisateurs des services');
define('_ALL_ENTITIES', 'Toutes les entit&eacute;s');
define('_ENTITIES_JUST_BELOW', 'Entit&eacute;s im&eacute;diatement inf&eacute;rieures &agrave; l&rsquo;entit&eacute; primaire');
define('_ALL_ENTITIES_BELOW', 'Toutes les entit&eacute;s inf&eacute;rieures &agrave; l&rsquo;entit&eacute; primaire');
define('_ENTITIES_JUST_UP', 'Entitit&eacute;s im&eacute;diatement sup&eacute;rieures  &agrave; l&rsquo;entit&eacute; primaire');
define('_MY_ENTITIES', 'Toutes les entit&eacute;s de l&rsquo;utilisateur');
define('_MY_PRIMARY_ENTITY', 'Entit&eacute; primaire');
define('_SAME_LEVEL_ENTITIES', 'Toutes les entit&eacute;s du m&ecirc;me niveau de l&rsquo;entit&eacute; primaire');

define('_INDEXING_ENTITIES', 'Indexer pour les services');
define('_SEARCH_DIFF_LIST', 'Rechercher un service ou un utilisateur');
define('_ADD_CC', 'Ajouter en copie');
define('_TO_DEST', 'Destinataire');

define('_NO_DIFF_LIST_ASSOCIATED', 'Aucune liste de diffusion');
define('_PRINCIPAL_RECIPIENT', 'Destinataire principal');
define('_ADD_COPY_IN_PROCESS', 'Ajouter des personnes en copie dans le traitement');
define('_DIFF_LIST_COPY', 'Liste de diffusion, copies');
define('_NO_COPY', 'Pas de copies');
define('_DIFF_LIST', 'Liste de diffusion');

define('_NO_USER', 'Pas d&rsquo;utilisateur');


/******************** Keywords Helper ************/
define('_HELP_KEYWORD1', 'toutes les entit&eacute;s rattach&eacute;es &agrave; l&rsquo;utilisateur connect&eacute;. N&rsquo;inclue pas les sous-entit&eacute;s');
define('_HELP_KEYWORD2', 'entit&eacute; primaire de l&rsquo;utilisateur connect&eacute;');
define('_HELP_KEYWORD3', 'sous-entit&eacute;s de la liste d&rsquo;argument, qui peut aussi &ecirc;tre @my_entities ou @my_primary_entity');
define('_HELP_KEYWORD4', 'entit&eacute; parente de l&rsquo;entit&eacute; en argument');
define('_HELP_KEYWORD5', 'toutes les entit&eacute;s du m&ecirc;me niveau que l&rsquo;entit&eacute; en argument');
define('_HELP_KEYWORD6', 'toutes les entit&eacute;s (actives)');
define('_HELP_KEYWORD7', 'sous-entit&eacute;s imm&eacute;diates (n-1) des entit&eacute;s donn&eacute;es en argument');
define('_HELP_KEYWORDS', 'Aide sur les mots cl&eacute;s');
define('_HELP_KEYWORD_EXEMPLE_TITLE', 'Exemple dans la d&eacute;finition de la s&eacute;curit&eacute; d&rsquo;un groupe ("where clause") : acc&egrave;s sur les ressources concernant le service d&rsquo;appartenance principal de l&rsquo;utilisateur connect&eacute;, ou les sous-services de ce service.');
define('_HELP_KEYWORD_EXEMPLE', 'where_clause : (DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])');
define('_HELP_BY_ENTITY', 'Mots cl&eacute;s du module Entit&eacute;s');
?>
