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

/*********************** SERVICES ***********************************/
if (!defined('_ADMIN_FOLDERTYPES'))
    define('_ADMIN_FOLDERTYPES', 'Types de dossier');
if (!defined('_ADMIN_FOLDERTYPES_DESC'))
    define('_ADMIN_FOLDERTYPES_DESC', 'Administrer les types de dossier. D&eacute;finir pour chaque type les qualificateurs li&eacute;s et les types de documents obligatoires pour la compl&eacute;tude du dossier.');

/*********************** Menu ***********************************/
if (!defined('_FOLDER_SEARCH'))
    define('_FOLDER_SEARCH', 'Rechercher un dossier');
if (!defined('_SALARY_SHEET'))
    define('_SALARY_SHEET', 'Fiche dossier');
if (!defined('_FOLDER_OUT'))
    define('_FOLDER_OUT', 'D&eacute;sarchivage');

//////////////Recherche dossier
if (!defined('_SELECT_FOLDER_TITLE'))
    define('_SELECT_FOLDER_TITLE', 'S&eacute;lection du dossier');
if (!defined('_FOLDER_NUM'))
    define('_FOLDER_NUM', 'N&deg; Dossier');
if (!defined('_COMPLETE'))
    define('_COMPLETE', 'Complet');
if (!defined('_INCOMPLETE'))
    define('_INCOMPLETE', 'Incomplet');
if (!defined('_FOUND_FOLDER'))
    define('_FOUND_FOLDER', 'dossier(s) trouv&eacute;(s)');
if (!defined('_CHOOSE'))
    define('_CHOOSE', 'Choisir');
if (!defined('_ADV_SEARCH_FOLDER_TITLE'))
    define('_ADV_SEARCH_FOLDER_TITLE', 'Recherche de dossier');
if (!defined('_SEARCH_ADV_FOLDER'))
    define('_SEARCH_ADV_FOLDER', 'Recherche de dossier');
if (!defined('_NEW_SEARCH'))
    define('_NEW_SEARCH', 'Effacer les crit&egrave;res');
if (!defined('_SELECT_FOLDER'))
    define('_SELECT_FOLDER', 'S&eacute;lection Dossier');
if (!defined('_CREATE_FOLDER'))
    define('_CREATE_FOLDER', 'Cr&eacute;ation Dossier');
if (!defined('_CREATE_FOLDER2'))
    define('_CREATE_FOLDER2', 'Cr&eacute;er Dossier');
if (!defined('_FOLDER'))
    define('_FOLDER', 'Dossier');
if (!defined('_MODIFY_FOLDER'))
    define('_MODIFY_FOLDER', 'Droit de modification des index d&rsquo;un dossier');
if (!defined('_FOLDERID'))
    define('_FOLDERID', 'Num&eacute;ro du Dossier/Sous-dossier');
if (!defined('_FOLDERSYSTEMID'))
    define('_FOLDERSYSTEMID', 'Num&eacute;ro syst&egrave;me Maarch');
if (!defined('_FOLDERID_LONG'))
    define('_FOLDERID_LONG', 'Num&eacute;ro de dossier');
if (!defined('_FOLDERNAME'))
    define('_FOLDERNAME', 'Nom du Dossier/Sous-dossier');
if (!defined('_FOLDERDATE'))
    define('_FOLDERDATE', 'Date de cr&eacute;ation');
if (!defined('_FOLDERDATE_START'))
    define('_FOLDERDATE_START', 'Date de cr&eacute;ation d&eacute;but ');
if (!defined('_FOLDERDATE_END'))
    define('_FOLDERDATE_END', 'Date de cr&eacute;ation fin ');
if (!defined('_FOLDERHASNODOC'))
    define('_FOLDERHASNODOC','Aucune pi&egrave;ce pour ce dossier');
if (!defined('_OTHER_INFOS'))
    define('_OTHER_INFOS','Autres informations : historique du dossier et pi&egrave;ces manquantes pour la compl&eacute;tude');
if (!defined('_SEARCH_FOLDER'))
    define('_SEARCH_FOLDER','Recherche dossier');
if (!defined('_SELECTED_FOLDER'))
    define('_SELECTED_FOLDER','Dossier s&eacute;lectionn&eacute;');
if (!defined('_FOUND_FOLDERS'))
    define('_FOUND_FOLDERS','Dossiers trouv&eacute;s');
if (!defined('_FOLDERTYPE_LABEL'))
    define('_FOLDERTYPE_LABEL','Libell&eacute; dossier');
if (!defined('_INFOS_FOLDERS'))
    define('_INFOS_FOLDERS','Infos dossier');
if (!defined('_CHOOSE_FOLDER'))
    define('_CHOOSE_FOLDER', 'Choisissez un dossier');
if (!defined('_ON_FOLDER_NUM'))
    define('_ON_FOLDER_NUM', ' sur le dossier n&deg;');

//////////////create_folder.php
if (!defined('_CREATE_THE_FOLDER'))
    define('_CREATE_THE_FOLDER', 'Cr&eacute;er le dossier');
if (!defined('_NEW_EMPLOYEES_LIST'))
    define('_NEW_EMPLOYEES_LIST','Liste des nouveaux collaborateurs');
if (!defined('_FOLDERS_LIST'))
    define('_FOLDERS_LIST', 'Liste de dossiers');
if (!defined('_FOLDERS'))
    define('_FOLDERS', 'dossiers');
if (!defined('_FOLDERS_COMMENT'))
    define('_FOLDERS_COMMENT', 'Dossiers');
if (!defined('_CHOOSE2'))
    define('_CHOOSE2', 'Choisissez&hellip;');
if (!defined('_IS_MANDATORY'))
    define('_IS_MANDATORY', 'est obligatoire');
if (!defined('_FOLDER_CREATION'))
    define('_FOLDER_CREATION', 'Cr&eacute;ation du dossier');

///////////////delete_popup.php
if (!defined('_DEL_FOLDER_NUM'))
    define('_DEL_FOLDER_NUM', 'Suppression du dossier n&deg;');
if (!defined('_DEL_FOLDER'))
    define('_DEL_FOLDER', 'Supprimer le dossier');

//Step in add_batch.php for physical_archive
if (!defined('_STEP_ONE'))
    define('_STEP_ONE', '1 - Choisissez un dossier');

/////////create folder
if (!defined('_CHOOSE_SOCIETY'))
    define('_CHOOSE_SOCIETY', 'Choisissez une soci&eacute;t&eacute;');
if (!defined('_THE_SOCIETY'))
    define('_THE_SOCIETY', 'La soci&eacute;t&eacute; ');
if (!defined('_MISSING_DOC'))
    define('_MISSING_DOC', 'Pi&egrave;ces manquantes');
if (!defined('_MISSING_DOC2'))
    define('_MISSING_DOC2', 'Pi&egrave;ce(s) manquante(s)');
if (!defined('_PLEASE_SELECT_FOLDER'))
    define('_PLEASE_SELECT_FOLDER', 'Vous devez s&eacute;lectionner un dossier');
if (!defined('_FOLDER_HISTORY'))
    define('_FOLDER_HISTORY', 'Historique dossier');
if (!defined('_CHOOSE_FOLDERTYPE'))
    define('_CHOOSE_FOLDERTYPE', 'Choisissez un type de dossier');
if (!defined('_BROWSE_BY_FOLDER'))
    define('_BROWSE_BY_FOLDER', 'Recherche Dossier');

/*************************** Foldertypes management *****************/
if (!defined('_FOLDERTYPE_ADDITION'))
    define('_FOLDERTYPE_ADDITION', 'Ajout type de dossier');
if (!defined('_FOLDERTYPE_MODIFICATION'))
    define('_FOLDERTYPE_MODIFICATION', 'Modification du type de dossier');
if (!defined('_FOLDERTYPES_LIST'))
    define('_FOLDERTYPES_LIST', 'Liste des types de dossier');
if (!defined('_TYPES'))
    define('_TYPES', 'type(s)');
if (!defined('_ALL_FOLDERTYPES'))
    define('_ALL_FOLDERTYPES', 'Tous les types');
if (!defined('_FOLDERTYPE'))
    define('_FOLDERTYPE', 'Type de dossier');
if (!defined('_FOLDERTYPE_MISSING'))
    define('_FOLDERTYPE_MISSING', 'Type de dossier manquant');

/************************** Fiche salarie ***************************/

if (!defined('_ARCHIVED_DOC'))
    define('_ARCHIVED_DOC', 'Pi&egrave;ces archiv&eacute;es');
if (!defined('_SEND_RELANCE_MAIL'))
    define('_SEND_RELANCE_MAIL', 'Envoyer un mail de relance');
if (!defined('_DIRECTION_DEP'))
    define('_DIRECTION_DEP', 'Direction/Dpt');
if (!defined('_DEP_AGENCY'))
    define('_DEP_AGENCY', 'Service/agence');
if (!defined('_DELETE_FOLDER'))
    define('_DELETE_FOLDER', 'Suppression de dossier');
if (!defined('_DELETE_FOLDER_NOTES1'))
    define('_DELETE_FOLDER_NOTES1', 'La suppression de dossier est irr&eacute;versible, les pi&egrave;ces de ce dernier seront conserv&eacute;es mais ne seront plus accessibles en consultation.');
if (!defined('_REALLY_DELETE_FOLDER'))
    define('_REALLY_DELETE_FOLDER', 'Voulez vous supprimer le dossier ?');
if (!defined('_DELETE_FOLDER_NOTES2'))
    define('_DELETE_FOLDER_NOTES2','Pour supprimer d&eacute;finitivement le dossier, saisissez &quot;EFFACER&quot; (en lettres majuscules) dans la case ci-dessous.');
if (!defined('_DELETE_FOLDER_NOTES3'))
    define('_DELETE_FOLDER_NOTES3', 'Le dossier sera effac&eacute; apr&egrave;s cette validation.');
if (!defined('_DELETE_FOLDER_NOTES4'))
    define('_DELETE_FOLDER_NOTES4', 'Le dossier ne peut &ecirc;tre supprim&eacute; car la confirmation est erron&eacute;e&hellip;');
if (!defined('_DELETE_FOLDER_NOTES5'))
    define('_DELETE_FOLDER_NOTES5', 'Le dossier est d&eacute;sormais supprim&eacute; de la base de donn&eacute;es.');
if (!defined('_FOLDER_INDEX_MODIF'))
    define('_FOLDER_INDEX_MODIF', 'Modification des index du dossier');
if (!defined('_FOLDERS_OUT'))
    define('_FOLDERS_OUT', 'Dossiers d&eacute;sarchiv&eacute;s');

///////////////// Class_admin_foldertype
//CUSTOM
if (!defined('_MANDATORY_DOCTYPES_COMP'))
    define('_MANDATORY_DOCTYPES_COMP', 'Types de document obligatoire pour la compl&eacute;tude du dossier');
if (!defined('_FOLDER_ID'))
    define('_FOLDER_ID', 'Identifiant dossier');
if (!defined('_INDEX_FOR_FOLDERTYPES'))
    define('_INDEX_FOR_FOLDERTYPES', 'Index possibles pour les types de dossier');
if (!defined('_SELECTED_DOCTYPES'))
    define('_SELECTED_DOCTYPES', 'Types de document selectionn&eacute;s');
if (!defined('_SHOW_FOLDER'))
    define('_SHOW_FOLDER', 'Fiche dossier');
if (!defined('_FOLDERTYPE_UPDATE'))
    define('_FOLDERTYPE_UPDATE', 'Type de dossier modifi&eacute;');
if (!defined('_FOLDER_ATTACH'))
    define('_FOLDER_ATTACH', 'Rattachement &agrave; un dossier');
if (!defined('_INCOMPATIBILITY_MARKET_PROJECT'))
    define('_INCOMPATIBILITY_MARKET_PROJECT', 'Incompatibilit&eacute; entre le Dossier et le Sous-dossier');
if (!defined('_FOLDER_VIEW_STAT'))
    define('_FOLDER_VIEW_STAT', 'Nombre de dossiers consult&eacute;s');
if (!defined('_FOLDER_VIEW_STAT_DESC'))
    define('_FOLDER_VIEW_STAT_DESC', 'Nombre de dossiers consult&eacute;s');
if (!defined('_FOLDER_HISTORY_STAT'))
    define('_FOLDER_HISTORY_STAT', 'Historique d&rsquo;un dossier');
if (!defined('_FOLDER_HISTORY_STAT_DESC'))
    define('_FOLDER_HISTORY_STAT_DESC', 'Historique d&rsquo;un dossier');
if (!defined('_VIEW_FOLDER'))
    define('_VIEW_FOLDER', 'Visualisation du dossier');

////////// Reports label
if (!defined('_TITLE_STATS_CHOICE_FOLDER_TYPE'))
    define('_TITLE_STATS_CHOICE_FOLDER_TYPE','Par type de dossier');
if (!defined('_TITLE_STATS_CHOICE_GROUP'))
    define('_TITLE_STATS_CHOICE_GROUP','Par groupe d&rsquo;utilisateurs');
if (!defined('_TITLE_STATS_CHOICE_USER'))
    define('_TITLE_STATS_CHOICE_USER','Pour un utilisateur');
if (!defined('_TITLE_STATS_CHOICE_USER2'))
    define('_TITLE_STATS_CHOICE_USER2','par l&rsquo;utilisateur');
if (!defined('_TITLE_STATS_NO_FOLDERS_VIEW'))
    define('_TITLE_STATS_NO_FOLDERS_VIEW','Aucun dossier consult&eacute; pour la p&eacute;riode');
if (!defined('_STATS_ERROR_CHOSE_USER'))
    define('_STATS_ERROR_CHOSE_USER','Il faut choisir un utilisateur existant.');
if (!defined('_NB_FOLDERS'))
    define('_NB_FOLDERS', 'Nombre de dossiers' );
if (!defined('_NB_VIEWED_FOLDERS'))
    define('_NB_VIEWED_FOLDERS','Nombre de dossiers consult&eacute;s');
if (!defined('_TITLE_STATS_CHOICE_ACTION'))
    define('_TITLE_STATS_CHOICE_ACTION','par type d&rsquo;action');
if (!defined('_ACTION_TYPE'))
    define('_ACTION_TYPE', 'Type d&rsquo;action');
if (!defined('_NO_STRUCTURE_ATTACHED2'))
    define('_NO_STRUCTURE_ATTACHED2', 'Ce type de dossier n&rsquo;est attach&eacute; &agrave; aucune chemise');
if (!defined('_FOLDER_ADDED'))
    define('_FOLDER_ADDED', 'Nouveau dossier cr&eacute;&eacute;');
if (!defined('_FOLDER_DETAILLED_PROPERTIES'))
    define('_FOLDER_DETAILLED_PROPERTIES', 'Informations sur le dossier');
if (!defined('_FOLDER_PROPERTIES'))
    define('_FOLDER_PROPERTIES', 'Propri&eacute;t&eacute;s du dossier');
if (!defined('_SYSTEM_ID'))
    define('_SYSTEM_ID', 'ID Syst&egrave;me');
if (!defined('_MODIFICATION_DATE'))
    define('_MODIFICATION_DATE', 'Date de modification');
if (!defined('_UPDATE_FOLDER'))
    define('_UPDATE_FOLDER', 'Modifier des informations');
if (!defined('_FOLDER_INDEX_UPDATED'))
    define('_FOLDER_INDEX_UPDATED', 'Index du dossier modifi&eacute;s');
if (!defined('_ALL_DOCS_AND_SUFOLDERS_WILL_BE_DELETED'))
    define('_ALL_DOCS_AND_SUFOLDERS_WILL_BE_DELETED', 'tous les documents de ce dossier, ainsi que tous les sous-dossiers seront &eacute;galement supprim&eacute;s !');
if (!defined('_STRING'))
    define('_STRING', 'Chaine de caract&egrave;res');
if (!defined('_INTEGER'))
    define('_INTEGER', 'Entier');
if (!defined('_FLOAT'))
    define('_FLOAT', 'Flottant');
if (!defined('_DATE'))
    define('_DATE', 'Date');
if (!defined('_MAX'))
    define('_MAX', 'maximum');
if (!defined('_MIN'))
    define('_MIN', 'minimum');
if (!defined('_ERROR_COMPATIBILITY_FOLDER'))
    define('_ERROR_COMPATIBILITY_FOLDER', 'Probl&egrave;me de compatibilit&eacute; entre<br/>le dossier et le type de document');
if (!defined('_ADDED_TO_FOLDER'))
    define('_ADDED_TO_FOLDER', ' ajout&eacute; au dossier');
if (!defined('_DELETED_FROM_FOLDER'))
    define('_DELETED_FROM_FOLDER', ' supprim&eacute; du dossier');
if (!defined('_CHOOSE_PARENT_FOLDER'))
    define('_CHOOSE_PARENT_FOLDER', 'Associer ce dossier &agrave; un dossier existant');
if (!defined('_FOLDER_PARENT'))
    define('_FOLDER_PARENT', 'Dossier parent');
if (!defined('_FOLDER_PARENT_DESC'))
    define('_FOLDER_PARENT_DESC', 'Vous pouvez choisir de cr&eacute;er un sous-dossier en le rattachant &agrave; un dossier du m&ecirc;me type. Il y a seulement 2 niveaux : dossier et sous-dossier.');
if (!defined('_THIS_FOLDER'))
    define('_THIS_FOLDER', 'ce dossier');
if (!defined('_LABEL'))
    define('_LABEL', 'Libelle');
if (!defined('_ALL_FOLDERS'))
    define('_ALL_FOLDERS', 'Tous les dossiers');
if (!defined('_FOLDER_DELETED'))
    define('_FOLDER_DELETED', 'Dossier supprim&eacute;');
if (!defined('_FREEZE_FOLDER_SERVICE'))
    define('_FREEZE_FOLDER_SERVICE', 'Gel et d&eacute;gel des dossiers');
if (!defined('_FREEZE_FOLDER'))
    define('_FREEZE_FOLDER', 'Geler le dossier');
if (!defined('_UNFREEZE_FOLDER'))
    define('_UNFREEZE_FOLDER', 'D&eacute;geler le dossier');
if (!defined('_CLOSE_FOLDER'))
    define('_CLOSE_FOLDER', 'Cl&ocirc;turer le dossier');
if (!defined('_FOLDER_CLOSED'))
    define('_FOLDER_CLOSED', 'Dossier clotur&eacute;');
if (!defined('_FROZEN_FOLDER'))
    define('_FROZEN_FOLDER', 'Gel du dossier');
if (!defined('_UNFROZEN_FOLDER'))
    define('_UNFROZEN_FOLDER', 'D&eacute;gel du dossier');
if (!defined('_REALLY_FREEZE_THIS_FOLDER'))
    define('_REALLY_FREEZE_THIS_FOLDER', 'Voulez-vous vraiment geler ce dossier');
if (!defined('_REALLY_CLOSE_THIS_FOLDER'))
    define('_REALLY_CLOSE_THIS_FOLDER', 'Voulez-vous vraiment cloturer ce dossier');
if (!defined('_SUBFOLDER'))                         
    define('_SUBFOLDER', 'Sous-dossier');
if (!defined('_VIEW_FOLDER_TREE'))                  
    define('_VIEW_FOLDER_TREE', 'Consultation Dossiers Sous-dossiers');
if (!defined('_SEARCH_FOLDER_TREE'))                
    define('_SEARCH_FOLDER_TREE', 'Recherche Dossiers Sous-dossiers');
if (!defined('_NB_DOCS_IN_FOLDER'))                 
    define('_NB_DOCS_IN_FOLDER', 'Nombre de documents');
if (!defined('_IS_FOLDER_BASKET'))                  
    define('_IS_FOLDER_BASKET', 'Corbeille de dossier');
if (!defined('_IS_FOLDER_STATUS'))                  
    define('_IS_FOLDER_STATUS', 'Statut de dossier');
if (!defined('_IS_FOLDER_ACTION'))                  
    define('_IS_FOLDER_ACTION', 'Action de dossier');
if (!defined('_CONFIRM_FOLDER_STATUS'))             
    define('_CONFIRM_FOLDER_STATUS', 'Confirmation simple (dossiers)');
if (!defined('_REDIRECT_FOLDER'))                   
    define('_REDIRECT_FOLDER', 'Redirection du dossier');
if (!defined('_REDIRECT_ALL_DOCUMENTS_IN_FOLDER'))  
    define('_REDIRECT_ALL_DOCUMENTS_IN_FOLDER', 'Rediriger tous les documents du dossier');
if (!defined('_CHOOSE_ONE_FOLDER'))                 
    define('_CHOOSE_ONE_FOLDER', 'Choisissez au moins un dossier');
if (!defined('_MUST_CHOOSE_DEP_OR_USER'))           
    define('_MUST_CHOOSE_DEP_OR_USER', 'Vous devez s&eacute;lectionner un service ou un utilisateur!');

