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
if (!defined('_ADD_ENTITY'))  define('_ADD_ENTITY','Entit&eacute; ajout&eacute;e');
if (!defined('_ENTITY_ADDITION'))  define('_ENTITY_ADDITION', 'Ajout d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_MODIFICATION'))  define('_ENTITY_MODIFICATION', 'Modification d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_AUTORIZATION'))  define('_ENTITY_AUTORIZATION', 'Autorisation d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_SUSPENSION'))  define('_ENTITY_SUSPENSION', 'Suspension d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_DELETION'))  define('_ENTITY_DELETION', 'Suppression d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_DELETED'))  define('_ENTITY_DELETED', 'Entit&eacute; supprim&eacute;e');
if (!defined('_ENTITY_UPDATED'))  define('_ENTITY_UPDATED', 'Entit&eacute; modifi&eacute;e');
if (!defined('_ENTITY_AUTORIZED'))  define('_ENTITY_AUTORIZED', 'Entit&eacute; autoris&eacute;');
if (!defined('_ENTITY_SUSPENDED'))  define('_ENTITY_SUSPENDED', 'Entit&eacute; suspendue');
if (!defined('_ENTITY'))  define('_ENTITY', 'Entit&eacute;');
if (!defined('_ENTITIES'))  define('_ENTITIES', 'Entit&eacute;s');
if (!defined('_ENTITIES_COMMENT'))  define('_ENTITIES_COMMENT', 'Entit&eacute;s');
if (!defined('_ALL_ENTITIES'))  define('_ALL_ENTITIES', 'Toutes les entit&eacute;s');
if (!defined('_ENTITIES_LIST'))  define('_ENTITIES_LIST', 'Liste des entit&eacute;s');
if (!defined('_MANAGE_ENTITIES'))  define('_MANAGE_ENTITIES', 'G&eacute;rer les Entit&eacute;s');
if (!defined('_MANAGE_ENTITIES_DESC'))  define('_MANAGE_ENTITIES_DESC', 'Administrer les Entit&eacute;s, ...');
if (!defined('_ENTITY_MISSING'))  define('_ENTITY_MISSING', 'Cette entit&eacute; n&rsquo;existe pas');
if (!defined('_ENTITY_TREE'))  define('_ENTITY_TREE', 'Arborescence des entit&eacute;s');
if (!defined('_ENTITY_TREE_DESC'))  define('_ENTITY_TREE_DESC', 'Voir l&rsquo;arborescence des entit&eacute;s');
if (!defined('_ENTITY_HAVE_CHILD'))  define('_ENTITY_HAVE_CHILD', 'cette entit&eacute; poss&egrave;de des sous entit&eacute;s');
if (!defined('_ENTITY_IS_RELATED'))  define('_ENTITY_IS_RELATED', 'cette entit&eacute; est reli&eacute;e &agrave; des utilisateurs');


if (!defined('_TYPE'))  define('_TYPE', 'Type');

/*************************** Users - Entites management *****************/
if (!defined('_ENTITY_USER_DESC'))  define('_ENTITY_USER_DESC', 'Mettre en relation des entit&eacute;s et des utilisateurs');
if (!defined('_ENTITIES_USERS'))  define('_ENTITIES_USERS', 'Relation entit&eacute;s - utilisateurs');
if (!defined('_ENTITIES_USERS_LIST'))  define('_ENTITIES_USERS_LIST', 'Liste des utilisateurs');

if (!defined('_USER_ENTITIES_TITLE'))  define('_USER_ENTITIES_TITLE', 'L&rsquo;utilisateur appartient aux entit&eacute;s suivantes');

if (!defined('_USER_ENTITIES_ADDITION'))  define('_USER_ENTITIES_ADDITION', 'Relation Utilisateur - Entit&eacute;s');

if (!defined('_USER_BELONGS_NO_ENTITY'))  define('_USER_BELONGS_NO_ENTITY', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucune entit&eacute;');

if (!defined('_CHOOSE_ONE_ENTITY'))  define('_CHOOSE_ONE_ENTITY', 'Choisissez au moins une entit&eacute;');

if (!defined('_CHOOSE_ENTITY'))  define('_CHOOSE_ENTITY', 'Choisissez une entit&eacute;');

if (!defined('_CHOOSE_PRIMARY_ENTITY'))  define('_CHOOSE_PRIMARY_ENTITY', 'Choisir comme entit&eacute; primaire');
if (!defined('_PRIMARY_ENTITY'))  define('_PRIMARY_ENTITY', 'Entit&eacute; primaire');
if (!defined('_DELETE_ENTITY'))  define('_DELETE_ENTITY', 'Supprimer le(s) entit&eacute;(s)');
if (!defined('USER_ADD_ENTITY'))  define('USER_ADD_ENTITY', 'Ajouter une entit&eacute;');
if (!defined('_ADD_TO_ENTITY'))  define('_ADD_TO_ENTITY', 'Ajouter &agrave; une entit&eacute;');
if (!defined('_NO_ENTITY_SELECTED'))  define('_NO_ENTITY_SELECTED', 'Aucune entit&eacute; s&eacute;lectionn&eacute;e');
if (!defined('_NO_PRIMARY_ENTITY'))  define('_NO_PRIMARY_ENTITY', 'L&rsquo;entit&eacute; primaire est obligatoire');
if (!defined('_NO_ENTITIES_DEFINED_FOR_YOU'))  define('_NO_ENTITIES_DEFINED_FOR_YOU', 'Aucune entit&eacute; d&eacute;finie pour cet utilisateur');
if (!defined('_LABEL_MISSING'))  define('_LABEL_MISSING', 'Il manque le nom de l&rsquo;entit&eacute;');
if (!defined('_SHORT_LABEL_MISSING'))  define('_SHORT_LABEL_MISSING', 'Il manque le nom court de l&rsquo;entit&eacute;');
if (!defined('_ID_MISSING'))  define('_ID_MISSING', 'Il manque l&rsquo;indentifiant de l&rsquo;entit&eacute;');
if (!defined('_TYPE_MISSING'))  define('_TYPE_MISSING', 'Le type de l&rsquo;entit&eacute; est obligatoire');
if (!defined('_PARENT_MISSING'))  define('_PARENT_MISSING', 'L&rsquo;entit&eacute; parente est obligatoire');
if (!defined('_ENTITY_UNKNOWN'))  define('_ENTITY_UNKNOWN', 'Entit&eacute; Inconnue');

/*************************** Entites form *****************/
if (!defined('_ENTITY_LABEL'))  define('_ENTITY_LABEL', 'Nom');
if (!defined('_SHORT_LABEL'))  define('_SHORT_LABEL', 'Nom court');
if (!defined('_ENTITY_ADR_1'))  define('_ENTITY_ADR_1', 'Adresse 1');
if (!defined('_ENTITY_ADR_2'))  define('_ENTITY_ADR_2', 'Adresse 2');
if (!defined('_ENTITY_ADR_3'))  define('_ENTITY_ADR_3', 'Adresse 3');
if (!defined('_ENTITY_ZIPCODE'))  define('_ENTITY_ZIPCODE', 'Code Postal');
if (!defined('_ENTITY_CITY'))  define('_ENTITY_CITY', 'Ville');
if (!defined('_ENTITY_COUNTRY'))  define('_ENTITY_COUNTRY', 'Pays');
if (!defined('_ENTITY_EMAIL'))  define('_ENTITY_EMAIL', 'Email');
if (!defined('_ENTITY_BUSINESS'))  define('_ENTITY_BUSINESS', 'N&deg; SIRET');
if (!defined('_ENTITY_PARENT'))  define('_ENTITY_PARENT', 'Entit&eacute; parente');
if (!defined('_CHOOSE_ENTITY_PARENT'))  define('_CHOOSE_ENTITY_PARENT', 'Choisissez l&rsquo;entit&eacute; parente');
if (!defined('_CHOOSE_ENTITY_TYPE'))  define('_CHOOSE_ENTITY_TYPE', 'Choisissez le type de l&rsquo;entit&eacute;');
if (!defined('_ENTITY_TYPE'))  define('_ENTITY_TYPE', 'Type de l&rsquo;entit&eacute;');

if (!defined('_TO_USERS_OF_ENTITIES'))  define('_TO_USERS_OF_ENTITIES', 'Vers des utilisateurs des services');
if (!defined('_ALL_ENTITIES'))  define('_ALL_ENTITIES', 'Toutes les entit&eacute;s');

if (!defined('_ENTITIES_JUST_BELOW'))  define('_ENTITIES_JUST_BELOW', 'Imm&eacute;diatement inf&eacute;rieures &agrave; l&rsquo;entit&eacute; primaire');
if (!defined('_ALL_ENTITIES_BELOW'))  define('_ALL_ENTITIES_BELOW', 'Inf&eacute;rieures &agrave; l&rsquo;entit&eacute; primaire');
if (!defined('_ENTITIES_JUST_UP'))  define('_ENTITIES_JUST_UP', 'Imm&eacute;diatement sup&eacute;rieures  &agrave; l&rsquo;entit&eacute; primaire');
if (!defined('_MY_ENTITIES'))  define('_MY_ENTITIES', 'Toutes les entit&eacute;s de l&rsquo;utilisateur');
if (!defined('_MY_PRIMARY_ENTITY'))  define('_MY_PRIMARY_ENTITY', 'Entit&eacute; primaire');
if (!defined('_SAME_LEVEL_ENTITIES'))  define('_SAME_LEVEL_ENTITIES', 'M&ecirc;me niveau de l&rsquo;entit&eacute; primaire');

if (!defined('_INDEXING_ENTITIES'))  define('_INDEXING_ENTITIES', 'Indexer pour les services');
if (!defined('_SEARCH_DIFF_LIST'))  define('_SEARCH_DIFF_LIST', 'Rechercher un service ou un utilisateur');
if (!defined('_ADD_CC'))  define('_ADD_CC', 'Ajouter en copie');
if (!defined('_TO_DEST'))  define('_TO_DEST', 'Destinataire');

if (!defined('_NO_DIFF_LIST_ASSOCIATED'))  define('_NO_DIFF_LIST_ASSOCIATED', 'Aucune liste de diffusion');
if (!defined('_PRINCIPAL_RECIPIENT'))  define('_PRINCIPAL_RECIPIENT', 'Destinataire principal');
if (!defined('_ADD_COPY_IN_PROCESS'))  define('_ADD_COPY_IN_PROCESS', 'Ajouter des personnes en copie dans le traitement');
if (!defined('_DIFF_LIST_COPY'))  define('_DIFF_LIST_COPY', 'Liste de diffusion, copies');
if (!defined('_NO_COPY'))  define('_NO_COPY', 'Pas de copies');
if (!defined('_DIFF_LIST'))  define('_DIFF_LIST', 'Liste de diffusion');

if (!defined('_NO_USER'))  define('_NO_USER', 'Pas d&rsquo;utilisateur');
if (!defined('_MUST_CHOOSE_DEST'))  define('_MUST_CHOOSE_DEST', 'Vous devez s&eacute;lectionner un destinataire principal');

if (!defined('_ENTITIES__DEL'))  define('_ENTITIES__DEL', 'Suppression');
if (!defined('_ENTITY_DELETION'))  define('_ENTITY_DELETION', 'Suppression d&rsquo;entit&eacute;');
if (!defined('_THERE_ARE_NOW'))  define('_THERE_ARE_NOW', 'Il y a actuellement');
if (!defined('_DOC_IN_THE_DEPARTMENT'))  define('_DOC_IN_THE_DEPARTMENT', 'document(s) associ&eacute;(s) &agrave; l&rsquo;entit&eacute;');
if (!defined('_DEL_AND_REAFFECT'))  define('_DEL_AND_REAFFECT', 'Supprimer et r&eacute;affecter');
if (!defined('_THE_ENTITY'))  define('_THE_ENTITY', 'L&rsquo;entit&eacute;');
if (!defined('_USERS_LINKED_TO'))  define('_USERS_LINKED_TO', 'utilisateur(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_ENTITY_MANDATORY_FOR_DOCUMENTS'))  define('_ENTITY_MANDATORY_FOR_REDIRECTION', 'Entit&eacute; obligatoire pour la r&eacute;affectation');
if (!defined('_WARNING_MESSAGE_DEL_ENTITY'))  define('_WARNING_MESSAGE_DEL_ENTITY', 'Avertissement :<br> La suppression d&rsquo;une entit&eacute; entraine la r&eacute;affectation des documents et utilisateurs &agrave une nouvelle entit&eacute; mais r&eacute;affecte &eacute;galement les documents (courriers) en attente de traitement, les mod&egrave;les de liste de diffusion, les mod&egrave;les de r&eacute;ponses et les unit&eacute;s d&rsquo;archivage vers l&rsquo;entit&eacute de remplacement.');

/******************** Keywords Helper ************/
if (!defined('_HELP_KEYWORD1'))  define('_HELP_KEYWORD1', 'toutes les entit&eacute;s rattach&eacute;es &agrave; l&rsquo;utilisateur connect&eacute;. N&rsquo;inclue pas les sous-entit&eacute;s');
if (!defined('_HELP_KEYWORD2'))  define('_HELP_KEYWORD2', 'entit&eacute; primaire de l&rsquo;utilisateur connect&eacute;');
if (!defined('_HELP_KEYWORD3'))  define('_HELP_KEYWORD3', 'sous-entit&eacute;s de la liste d&rsquo;argument, qui peut aussi &ecirc;tre @my_entities ou @my_primary_entity');
if (!defined('_HELP_KEYWORD4'))  define('_HELP_KEYWORD4', 'entit&eacute; parente de l&rsquo;entit&eacute; en argument');
if (!defined('_HELP_KEYWORD5'))  define('_HELP_KEYWORD5', 'toutes les entit&eacute;s du m&ecirc;me niveau que l&rsquo;entit&eacute; en argument');
if (!defined('_HELP_KEYWORD6'))  define('_HELP_KEYWORD6', 'toutes les entit&eacute;s (actives)');
if (!defined('_HELP_KEYWORD7'))  define('_HELP_KEYWORD7', 'sous-entit&eacute;s imm&eacute;diates (n-1) des entit&eacute;s donn&eacute;es en argument');
if (!defined('_HELP_KEYWORDS'))  define('_HELP_KEYWORDS', 'Aide sur les mots cl&eacute;s');
if (!defined('_HELP_KEYWORD_EXEMPLE_TITLE'))  define('_HELP_KEYWORD_EXEMPLE_TITLE', 'Exemple dans la d&eacute;finition de la s&eacute;curit&eacute; d&rsquo;un groupe ("where clause") : acc&egrave;s sur les ressources concernant le service d&rsquo;appartenance principal de l&rsquo;utilisateur connect&eacute;, ou les sous-services de ce service.');
if (!defined('_HELP_KEYWORD_EXEMPLE'))  define('_HELP_KEYWORD_EXEMPLE', 'where_clause : (DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])');
if (!defined('_HELP_BY_ENTITY'))  define('_HELP_BY_ENTITY', 'Mots cl&eacute;s du module Entit&eacute;s');

if (!defined('_BASKET_REDIRECTIONS_OCCURS_LINKED_TO'))  define('_BASKET_REDIRECTIONS_OCCURS_LINKED_TO', 'occurence(s) de redirection(s) de corbeille(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_TEMPLATES_LINKED_TO'))  define('_TEMPLATES_LINKED_TO', 'mod&egrave;le(s) de r&eacute;ponse(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_LISTISTANCES_OCCURS_LINKED_TO'))  define('_LISTISTANCES_OCCURS_LINKED_TO', 'occurence(s) de courrier(s) &agrave; traiter ou en copie(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_LISTMODELS_OCCURS_LINKED_TO'))  define('_LISTMODELS_OCCURS_LINKED_TO', 'mod&egrave;le de diffusion associ&eacute; &agrave l&rsquo;entit&eacute;');

if (!defined('_CHOOSE_REPLACEMENT_DEPARTMENT'))  define('_CHOOSE_REPLACEMENT_DEPARTMENT', 'Choisissez un service rempla&ccedil;ant');

?>
