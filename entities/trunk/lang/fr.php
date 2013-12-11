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
if (!defined('_ADD_ENTITY'))
    define('_ADD_ENTITY','Entit&eacute; ajout&eacute;e');
if (!defined('_ENTITY_ADDITION'))
    define('_ENTITY_ADDITION', 'Ajout d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_MODIFICATION'))
    define('_ENTITY_MODIFICATION', 'Modification d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_AUTORIZATION'))
    define('_ENTITY_AUTORIZATION', 'Autorisation d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_SUSPENSION'))
    define('_ENTITY_SUSPENSION', 'Suspension d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_DELETION'))
    define('_ENTITY_DELETION', 'Suppression d&rsquo;une entit&eacute;');
if (!defined('_ENTITY_DELETED'))
    define('_ENTITY_DELETED', 'Entit&eacute; supprim&eacute;e');
if (!defined('_ENTITY_UPDATED'))
    define('_ENTITY_UPDATED', 'Entit&eacute; modifi&eacute;e');
if (!defined('_ENTITY_AUTORIZED'))
    define('_ENTITY_AUTORIZED', 'Entit&eacute; autoris&eacute;');
if (!defined('_ENTITY_SUSPENDED'))
    define('_ENTITY_SUSPENDED', 'Entit&eacute; suspendue');
if (!defined('_ENTITY'))
    define('_ENTITY', 'Entit&eacute;');
if (!defined('_ENTITIES'))
    define('_ENTITIES', 'Entit&eacute;s');
if (!defined('_ENTITIES_COMMENT'))
    define('_ENTITIES_COMMENT', 'Entit&eacute;s');
if (!defined('_ALL_ENTITIES'))
    define('_ALL_ENTITIES', 'Toutes les entit&eacute;s');
if (!defined('_ENTITIES_LIST'))
    define('_ENTITIES_LIST', 'Liste des entit&eacute;s');
if (!defined('_MANAGE_ENTITIES'))
    define('_MANAGE_ENTITIES', 'G&eacute;rer les Entit&eacute;s');
if (!defined('_MANAGE_ENTITIES_DESC'))
    define('_MANAGE_ENTITIES_DESC', 'Administrer les Entit&eacute;s, ...');
if (!defined('_ENTITY_MISSING'))
    define('_ENTITY_MISSING', 'Cette entit&eacute; n&rsquo;existe pas');
if (!defined('_ENTITY_TREE'))
    define('_ENTITY_TREE', 'Arborescence des entit&eacute;s');
if (!defined('_ENTITY_TREE_DESC'))
    define('_ENTITY_TREE_DESC', 'Voir l&rsquo;arborescence des entit&eacute;s');
if (!defined('_ENTITY_HAVE_CHILD'))
    define('_ENTITY_HAVE_CHILD', 'cette entit&eacute; poss&egrave;de des sous entit&eacute;s');
if (!defined('_ENTITY_IS_RELATED'))
    define('_ENTITY_IS_RELATED', 'cette entit&eacute; est reli&eacute;e &agrave; des utilisateurs');
if (!defined('_TYPE'))
    define('_TYPE', 'Type');

/*************************** Users - Entites management *****************/
if (!defined('_ENTITY_USER_DESC'))
    define('_ENTITY_USER_DESC', 'Mettre en relation des entit&eacute;s et des utilisateurs');
if (!defined('_ENTITIES_USERS'))
    define('_ENTITIES_USERS', 'Relation entit&eacute;s - utilisateurs');
if (!defined('_ENTITIES_USERS_LIST'))
    define('_ENTITIES_USERS_LIST', 'Liste des utilisateurs');
if (!defined('_USER_ENTITIES_TITLE'))
    define('_USER_ENTITIES_TITLE', 'L&rsquo;utilisateur appartient aux entit&eacute;s suivantes');
if (!defined('_USER_ENTITIES_ADDITION'))
    define('_USER_ENTITIES_ADDITION', 'Relation Utilisateur - Entit&eacute;s');
if (!defined('_USER_BELONGS_NO_ENTITY'))
    define('_USER_BELONGS_NO_ENTITY', 'L&rsquo;utilisateur n&rsquo;appartient &agrave; aucune entit&eacute;');
if (!defined('_CHOOSE_ONE_ENTITY'))
    define('_CHOOSE_ONE_ENTITY', 'Choisissez au moins une entit&eacute;');
if (!defined('_CHOOSE_ENTITY'))
    define('_CHOOSE_ENTITY', 'Choisissez une entit&eacute;');
if (!defined('_CHOOSE_PRIMARY_ENTITY'))
    define('_CHOOSE_PRIMARY_ENTITY', 'Choisir comme entit&eacute; primaire');
if (!defined('_PRIMARY_ENTITY'))
    define('_PRIMARY_ENTITY', 'Entit&eacute; primaire');
if (!defined('_DELETE_ENTITY'))
    define('_DELETE_ENTITY', 'Supprimer le(s) entit&eacute;(s)');
if (!defined('USER_ADD_ENTITY'))
    define('USER_ADD_ENTITY', 'Ajouter une entit&eacute;');
if (!defined('_ADD_TO_ENTITY'))
    define('_ADD_TO_ENTITY', 'Ajouter &agrave; une entit&eacute;');
if (!defined('_NO_ENTITY_SELECTED'))
    define('_NO_ENTITY_SELECTED', 'Aucune entit&eacute; s&eacute;lectionn&eacute;e');
if (!defined('_NO_PRIMARY_ENTITY'))
    define('_NO_PRIMARY_ENTITY', 'L&rsquo;entit&eacute; primaire est obligatoire');
if (!defined('_NO_ENTITIES_DEFINED_FOR_YOU'))
    define('_NO_ENTITIES_DEFINED_FOR_YOU', 'Aucune entit&eacute; d&eacute;finie pour cet utilisateur');
if (!defined('_LABEL_MISSING'))
    define('_LABEL_MISSING', 'Il manque le nom de l&rsquo;entit&eacute;');
if (!defined('_SHORT_LABEL_MISSING'))
    define('_SHORT_LABEL_MISSING', 'Il manque le nom court de l&rsquo;entit&eacute;');
if (!defined('_ID_MISSING'))
    define('_ID_MISSING', 'Il manque l&rsquo;indentifiant de l&rsquo;entit&eacute;');
if (!defined('_TYPE_MISSING'))
    define('_TYPE_MISSING', 'Le type de l&rsquo;entit&eacute; est obligatoire');
if (!defined('_PARENT_MISSING'))
    define('_PARENT_MISSING', 'L&rsquo;entit&eacute; parente est obligatoire');
if (!defined('_ENTITY_UNKNOWN'))
    define('_ENTITY_UNKNOWN', 'Entit&eacute; Inconnue');

/*************************** Entites form *****************/
if (!defined('_ENTITY_LABEL'))
    define('_ENTITY_LABEL', 'Nom');
if (!defined('_SHORT_LABEL'))
    define('_SHORT_LABEL', 'Nom court');
if (!defined('_ENTITY_ADR_1'))
    define('_ENTITY_ADR_1', 'Adresse 1');
if (!defined('_ENTITY_ADR_2'))
    define('_ENTITY_ADR_2', 'Adresse 2');
if (!defined('_ENTITY_ADR_3'))
    define('_ENTITY_ADR_3', 'Adresse 3');
if (!defined('_ENTITY_ZIPCODE'))
    define('_ENTITY_ZIPCODE', 'Code Postal');
if (!defined('_ENTITY_CITY'))
    define('_ENTITY_CITY', 'Ville');
if (!defined('_ENTITY_COUNTRY'))
    define('_ENTITY_COUNTRY', 'Pays');
if (!defined('_ENTITY_EMAIL'))
    define('_ENTITY_EMAIL', 'Email');
if (!defined('_ENTITY_BUSINESS'))
    define('_ENTITY_BUSINESS', 'N&deg; SIRET');
if (!defined('_ENTITY_PARENT'))
    define('_ENTITY_PARENT', 'Entit&eacute; parente');
if (!defined('_CHOOSE_ENTITY_PARENT'))
    define('_CHOOSE_ENTITY_PARENT', 'Choisissez l&rsquo;entit&eacute; parente');
if (!defined('_CHOOSE_ENTITY_TYPE'))
    define('_CHOOSE_ENTITY_TYPE', 'Choisissez le type de l&rsquo;entit&eacute;');
if (!defined('_ENTITY_TYPE'))
    define('_ENTITY_TYPE', 'Type de l&rsquo;entit&eacute;');
if (!defined('_TO_USERS_OF_ENTITIES'))
    define('_TO_USERS_OF_ENTITIES', 'Vers des utilisateurs des services');
if (!defined('_ALL_ENTITIES'))
    define('_ALL_ENTITIES', 'Toutes les entit&eacute;s');
if (!defined('_ENTITIES_JUST_BELOW'))
    define('_ENTITIES_JUST_BELOW', 'Imm&eacute;diatement inf&eacute;rieures &agrave; l&rsquo;entit&eacute; primaire');
if (!defined('_ALL_ENTITIES_BELOW'))
    define('_ALL_ENTITIES_BELOW', 'Inf&eacute;rieures &agrave; l&rsquo;entit&eacute; primaire');
if (!defined('_ENTITIES_JUST_UP'))
    define('_ENTITIES_JUST_UP', 'Imm&eacute;diatement sup&eacute;rieures  &agrave; l&rsquo;entit&eacute; primaire');
if (!defined('_MY_ENTITIES'))
    define('_MY_ENTITIES', 'Toutes les entit&eacute;s de l&rsquo;utilisateur');
if (!defined('_MY_PRIMARY_ENTITY'))
    define('_MY_PRIMARY_ENTITY', 'Entit&eacute; primaire');
if (!defined('_SAME_LEVEL_ENTITIES'))
    define('_SAME_LEVEL_ENTITIES', 'M&ecirc;me niveau de l&rsquo;entit&eacute; primaire');
if (!defined('_INDEXING_ENTITIES'))
    define('_INDEXING_ENTITIES', 'Indexer pour les services');
if (!defined('_SEARCH_DIFF_LIST'))
    define('_SEARCH_DIFF_LIST', 'Rechercher un service ou un utilisateur');
if (!defined('_ADD_CC'))
    define('_ADD_CC', 'Ajouter en copie');
if (!defined('_TO_DEST'))
    define('_TO_DEST', 'Destinataire');
if (!defined('_NO_DIFF_LIST_ASSOCIATED'))
    define('_NO_DIFF_LIST_ASSOCIATED', 'Aucune liste de diffusion');
if (!defined('_PRINCIPAL_RECIPIENT'))
    define('_PRINCIPAL_RECIPIENT', 'Destinataire principal');
if (!defined('_ADD_COPY_IN_PROCESS'))
    define('_ADD_COPY_IN_PROCESS', 'Ajouter des personnes en copie dans le traitement');
if (!defined('_UPDATE_LIST_DIFF_IN_DETAILS'))
    define('_UPDATE_LIST_DIFF_IN_DETAILS', 'Mettre &agrave jour la liste de diffusion depuis la page de d&eacute;tails');
if (!defined('_UPDATE_LIST_DIFF'))
    define('_UPDATE_LIST_DIFF', 'Modifier la liste de diffusion');
if (!defined('_DIFF_LIST_COPY'))
    define('_DIFF_LIST_COPY', 'Liste de diffusion, copies');
if (!defined('_NO_COPY'))
    define('_NO_COPY', 'Pas de copies');
if (!defined('_DIFF_LIST'))
    define('_DIFF_LIST', 'Liste de diffusion');
if (!defined('_NO_USER'))
    define('_NO_USER', 'Pas d&rsquo;utilisateur');
if (!defined('_MUST_CHOOSE_DEST'))
    define('_MUST_CHOOSE_DEST', 'Vous devez s&eacute;lectionner au moins un destinataire');
if (!defined('_ENTITIES__DEL'))
    define('_ENTITIES__DEL', 'Suppression');
if (!defined('_ENTITY_DELETION'))
    define('_ENTITY_DELETION', 'Suppression d&rsquo;entit&eacute;');
if (!defined('_THERE_ARE_NOW'))
    define('_THERE_ARE_NOW', 'Il y a actuellement');
if (!defined('_DOC_IN_THE_DEPARTMENT'))
    define('_DOC_IN_THE_DEPARTMENT', 'document(s) associ&eacute;(s) &agrave; l&rsquo;entit&eacute;');
if (!defined('_DEL_AND_REAFFECT'))
    define('_DEL_AND_REAFFECT', 'Supprimer et r&eacute;affecter');
if (!defined('_THE_ENTITY'))
    define('_THE_ENTITY', 'L&rsquo;entit&eacute;');
if (!defined('_USERS_LINKED_TO'))
    define('_USERS_LINKED_TO', 'utilisateur(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_ENTITY_MANDATORY_FOR_REDIRECTION'))
    define('_ENTITY_MANDATORY_FOR_REDIRECTION', 'Entit&eacute; obligatoire pour la r&eacute;affectation');
if (!defined('_WARNING_MESSAGE_DEL_ENTITY'))
    define('_WARNING_MESSAGE_DEL_ENTITY', 'Avertissement :<br> La suppression d&rsquo;une entit&eacute; entraine la r&eacute;affectation des documents et utilisateurs &agrave une nouvelle entit&eacute; mais r&eacute;affecte &eacute;galement les documents (courriers) en attente de traitement, les mod&egrave;les de liste de diffusion, les mod&egrave;les de r&eacute;ponses et les unit&eacute;s d&rsquo;archivage vers l&rsquo;entit&eacute de remplacement.');

/******************** Keywords Helper ************/
if (!defined('_HELP_KEYWORD1'))
    define('_HELP_KEYWORD1', 'toutes les entit&eacute;s rattach&eacute;es &agrave; l&rsquo;utilisateur connect&eacute;. N&rsquo;inclue pas les sous-entit&eacute;s');
if (!defined('_HELP_KEYWORD2'))
    define('_HELP_KEYWORD2', 'entit&eacute; primaire de l&rsquo;utilisateur connect&eacute;');
if (!defined('_HELP_KEYWORD3'))
    define('_HELP_KEYWORD3', 'sous-entit&eacute;s de la liste d&rsquo;argument, qui peut aussi &ecirc;tre @my_entities ou @my_primary_entity');
if (!defined('_HELP_KEYWORD4'))
    define('_HELP_KEYWORD4', 'entit&eacute; parente de l&rsquo;entit&eacute; en argument');
if (!defined('_HELP_KEYWORD5'))
    define('_HELP_KEYWORD5', 'toutes les entit&eacute;s du m&ecirc;me niveau que l&rsquo;entit&eacute; en argument');
if (!defined('_HELP_KEYWORD6'))
    define('_HELP_KEYWORD6', 'toutes les entit&eacute;s (actives)');
if (!defined('_HELP_KEYWORD7'))
    define('_HELP_KEYWORD7', 'sous-entit&eacute;s imm&eacute;diates (n-1) des entit&eacute;s donn&eacute;es en argument');
if (!defined('_HELP_KEYWORDS'))
    define('_HELP_KEYWORDS', 'Aide sur les mots cl&eacute;s');
if (!defined('_HELP_KEYWORD_EXEMPLE_TITLE'))
    define('_HELP_KEYWORD_EXEMPLE_TITLE', 'Exemple dans la d&eacute;finition de la s&eacute;curit&eacute; d&rsquo;un groupe ("where clause") : acc&egrave;s sur les ressources concernant le service d&rsquo;appartenance principal de l&rsquo;utilisateur connect&eacute;, ou les sous-services de ce service.');
if (!defined('_HELP_KEYWORD_EXEMPLE'))
    define('_HELP_KEYWORD_EXEMPLE', 'where_clause : (DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity]))');
if (!defined('_HELP_BY_ENTITY'))
    define('_HELP_BY_ENTITY', 'Mots cl&eacute;s du module Entit&eacute;s');
if (!defined('_BASKET_REDIRECTIONS_OCCURS_LINKED_TO'))
    define('_BASKET_REDIRECTIONS_OCCURS_LINKED_TO', 'occurence(s) de redirection(s) de corbeille(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_TEMPLATES_LINKED_TO'))
    define('_TEMPLATES_LINKED_TO', 'mod&egrave;le(s) de r&eacute;ponse(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_LISTISTANCES_OCCURS_LINKED_TO'))
    define('_LISTISTANCES_OCCURS_LINKED_TO', 'occurence(s) de courrier(s) &agrave; traiter ou en copie(s) associ&eacute;(s) &agrave l&rsquo;entit&eacute;');
if (!defined('_LISTMODELS_OCCURS_LINKED_TO'))
    define('_LISTMODELS_OCCURS_LINKED_TO', 'mod&egrave;le de diffusion associ&eacute; &agrave l&rsquo;entit&eacute;');
if (!defined('_CHOOSE_REPLACEMENT_DEPARTMENT'))
    define('_CHOOSE_REPLACEMENT_DEPARTMENT', 'Choisissez un service rempla&ccedil;ant');

/******************** For reports ************/
if (!defined('_ENTITY_VOL_STAT'))
    define('_ENTITY_VOL_STAT', 'Volume des courriers par entit&eacute;');
if (!defined('_ENTITY_VOL_STAT_DESC'))
    define('_ENTITY_VOL_STAT_DESC', 'Volume des courriers par entit&eacute;');
if (!defined('_NO_DATA_MESSAGE'))
    define('_NO_DATA_MESSAGE', 'Pas assez de données');
if (!defined('_MAIL_VOL_BY_ENT_REPORT'))
    define('_MAIL_VOL_BY_ENT_REPORT', 'Volume de courrier par service');
if (!defined('_WRONG_DATE_FORMAT'))
    define('_WRONG_DATE_FORMAT', 'Format de date incorrect');
if (!defined('_ENTITY_PROCESS_DELAY'))
    define('_ENTITY_PROCESS_DELAY', 'D&eacute;lai moyen de traitement par entit&eacute;');
if (!defined('_ENTITY_LATE_MAIL'))
    define('_ENTITY_LATE_MAIL', 'Volume de courrier en retard par entit&eacute;');

/******************** Action put in copy ************/
if (!defined('_ADD_COPY_FOR_DOC'))
    define('_ADD_COPY_FOR_DOC', 'Ajouter en copie pour le document');
if (!defined('_VALIDATE_PUT_IN_COPY'))
    define('_VALIDATE_PUT_IN_COPY', 'Valider l&rsquo;ajout en copie');
if (!defined('_ALL_LIST'))
    define('_ALL_LIST', 'Afficher toute la liste');    

 /******************** Listinstance roles ***********/   
if (!defined('_DEST_OR_COPY'))
    define('_DEST_OR_COPY', 'Destinataire');       
if (!defined('_SUBMIT'))
    define('_SUBMIT', 'Valider'); 
if (!defined('_CANCEL'))
    define('_CANCEL', 'Annuler');     
if (!defined('_DIFFLIST_TYPE_ROLES'))
    define('_DIFFLIST_TYPE_ROLES', 'Rôles disponibles');
if (!defined('_NO_AVAILABLE_ROLE'))
    define('_NO_AVAILABLE_ROLE', 'Aucun rôle disponible');  
  
    
 /******************** Difflist types ***********/       
 if (!defined('_ALL_DIFFLIST_TYPES'))
    define('_ALL_DIFFLIST_TYPES', 'Tous les types');
if (!defined('_DIFFLIST_TYPES_DESC'))
    define('_DIFFLIST_TYPES_DESC', 'Types listes de diffusion');     
if (!defined('_DIFFLIST_TYPES'))
    define('_DIFFLIST_TYPES', 'Types de listes de diffusion');   
if (!defined('_DIFFLIST_TYPE'))
    define('_DIFFLIST_TYPE', 'Type(s) de liste');
if (!defined('_ADD_DIFFLIST_TYPE'))
   define('_ADD_DIFFLIST_TYPE', 'Ajouter un type');
if (!defined('_DIFFLIST_TYPE_ID'))
   define('_DIFFLIST_TYPE_ID', 'Identifiant');   
if (!defined('_DIFFLIST_TYPE_LABEL'))
   define('_DIFFLIST_TYPE_LABEL', 'Description');   
if (!defined('_ALLOW_ENTITIES'))
    define('_ALLOW_ENTITIES', 'Autoriser les services');     
   
 /******************** Listmodels ***********/   
if (!defined('_ALL_LISTMODELS'))
    define('_ALL_LISTMODELS', 'Toutes les listes');
if (!defined('_LISTMODELS_DESC'))
    define('_LISTMODELS_DESC', 'Modèles de listes de diffusion des documents et dossiers');     
if (!defined('_LISTMODELS'))
    define('_LISTMODELS', 'Modèles de listes de diffusion');   
if (!defined('_LISTMODEL'))
    define('_LISTMODEL', 'Modèle(s) de liste');
if (!defined('_ADD_LISTMODEL'))
    define('_ADD_LISTMODEL', 'Nouveau modèle');  
if (!defined('_ADMIN_LISTMODEL'))
    define('_ADMIN_LISTMODEL', 'Modèle de liste de diffusion'); 
if (!defined('_ADMIN_LISTMODEL_TITLE'))
    define('_ADMIN_LISTMODEL_TITLE', 'Identification du modèle de liste:');   
if (!defined('_OBJECT_TYPE'))
    define('_OBJECT_TYPE', 'Type de modèle de liste'); 
if (!defined('_SELECT_OBJECT_TYPE'))
    define('_SELECT_OBJECT_TYPE', 'Sélectionnez un type...'); 
if (!defined('_SELECT_OBJECT_ID'))
    define('_SELECT_OBJECT_ID', 'Sélectionnez un lien...');
if (!defined('_USER_DEFINED_ID'))
    define('_USER_DEFINED_ID', 'Libre');    
if (!defined('_ALL_OBJECTS_ARE_LINKED'))
    define('_ALL_OBJECTS_ARE_LINKED', 'Toutes les listes sont déjà définies');
if (!defined('_SELECT_OBJECT_TYPE_AND_ID'))
    define('_SELECT_OBJECT_TYPE_AND_ID', 'Vous devez spécifier un type de liste et un identifiant!');
if (!defined('_SAVE_LISTMODEL'))
    define('_SAVE_LISTMODEL', 'Valider');
if (!defined('_OBJECT_ID_IS_NOT_VALID_ID'))
    define('_OBJECT_ID_IS_NOT_VALID_ID', 'Identifiant invalide: il ne doit contenir que des caractères alphabétiques, numériques ou des tiret bas (A-Z, a-z, 0-9, _)');  
if (!defined('_LISTMODEL_ID_ALREADY_USED'))
    define('_LISTMODEL_ID_ALREADY_USED', 'Cet identifiant est déjà utilisé!');    
if (!defined('_CONFIRM_LISTMODEL_SAVE'))
    define('_CONFIRM_LISTMODEL_SAVE', 'Sauvegarder la liste ?'); 

if (!defined('_ENTER_DESCRIPTION'))
    define('_ENTER_DESCRIPTION', 'Description obligatoire'); 

    
if (!defined('_PARAM_AVAILABLE_LISTMODELS_ON_GROUP_BASKETS')) define('_PARAM_AVAILABLE_LISTMODELS_ON_GROUP_BASKETS', 'Param&eacute;trer les types de modèle de liste de diffusion pour l&rsquo;indexation');
if (!defined('_INDEXING_DIFFLIST_TYPES')) define('_INDEXING_DIFFLIST_TYPES', 'Types de liste de diffusion');

if (!defined('_ADMIN_DIFFLIST_TYPES')) define('_ADMIN_DIFFLIST_TYPES', 'Types de liste de diffusion (Admin)');
if (!defined('_ADMIN_DIFFLIST_TYPES_DESC')) define('_ADMIN_DIFFLIST_TYPES_DESC', 'Administrer les différents types de liste de diffusion');
if (!defined('_ADMIN_LISTMODELS')) define('_ADMIN_LISTMODELS', 'Listes de diffusion (Admin)');
if (!defined('_ADMIN_LISTMODELS_DESC')) define('_ADMIN_LISTMODELS_DESC', 'Administrer les différents modèles de diffusion');

/******************** RM ENTITIES ************/
if (!defined('_STANDARD'))
    define('_STANDARD', 'Standard');
if (!defined('_5_ARCHIVAL'))
    define('_5_ARCHIVAL', '5 Archivistique');
if (!defined('_51_IDENTIFICATION'))
    define('_51_IDENTIFICATION', '5.1 Identification');
if (!defined('_52_DESCRIPTION'))
    define('_52_DESCRIPTION', '5.2 Description');
if (!defined('_53_RELATIONS'))
    define('_53_RELATIONS', '5.3 Relations');
if (!defined('_54_CONTROL'))
    define('_54_CONTROL', '5.4 Contr&ocirc;le');
    
if (!defined('_VISIBLE'))    
    define('_VISIBLE', 'Actif');
if (!defined('_NOT_VISIBLE')) 
    define('_NOT_VISIBLE', 'Inactif');
    
/******** NEW WF ************/
if (!defined('_TARGET_STATUS'))
    define('_TARGET_STATUS', 'Statut final &agrave; la validation de l&rsquo;&eacute;tape');
if (!defined('_TARGET_ROLE'))
    define('_TARGET_ROLE', 'R&ocirc;le &agrave; faire avancer dans le workflow');
if (!defined('_ITS_NOT_MY_TURN_IN_THE_WF'))
    define('_ITS_NOT_MY_TURN_IN_THE_WF', 'Ce n&rsquot;est pas mon tour de traiter dans le workflow');
if (!defined('_NO_AVAILABLE_ROLE_FOR_ME_IN_THE_WF'))
    define('_NO_AVAILABLE_ROLE_FOR_ME_IN_THE_WF', 'Il n&rsquo;y a pas de r&ocirc;le d&eacute;fini pour moi dans le workflow');
