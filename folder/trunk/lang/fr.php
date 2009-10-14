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
define('_ADMIN_FOLDERTYPES', 'Types de dossier');
define('_ADMIN_FOLDERTYPES_DESC', 'Administrer les types de dossier. D&eacute;finir pour chaque type les qualificateurs li&eacute;s et les types de documents obligatoires pour la compl&eacute;tude du dossier.');

/*********************** Menu ***********************************/
define('_FOLDER_SEARCH', 'Recherche dossier');
define('_SALARY_SHEET', 'Fiche dossier');
define('_FOLDER_OUT', 'D&eacute;sarchivage');
//////////////Recherche dossier
define('_SELECT_FOLDER_TITLE', 'S&eacute;lection du dossier');
define('_FOLDER_NUM', 'N&deg; Dossier');
define('_COMPLETE', 'Complet');
define('_INCOMPLETE', 'Incomplet');
define('_FOUND_FOLDER', 'dossier(s) trouv&eacute;(s)');
define('_CHOOSE', 'Choisir');
define('_ADV_SEARCH_FOLDER_TITLE', 'Recherche de dossier');
define('_SEARCH_ADV_FOLDER', 'Recherche de dossier');
define('_NEW_SEARCH', 'Effacer les crit&egrave;res');
define('_SELECT_FOLDER', 'S&eacute;lection Dossier');
define('_CREATE_FOLDER', 'Cr&eacute;ation Dossier');
define('_CREATE_FOLDER2', 'Cr&eacute;er Dossier');
define('_FOLDER', 'Dossier');
define('_MODIFY_FOLDER', 'Droit de modification des index d&rsquo;un dossier');
define('_FOLDERID', 'Num&eacute;ro du Dossier/Sous-dossier');
define('_FOLDERSYSTEMID', 'Num&eacute;ro syst&egrave;me Maarch');
define('_FOLDERID_LONG', 'Num&eacute;ro de dossier');
define('_FOLDERNAME', 'Nom du Dossier/Sous-dossier');
define('_FOLDERDATE', 'Date de cr&eacute;ation');
define('_FOLDERDATE_START', 'Date de cr&eacute;ation d&eacute;but ');
define('_FOLDERDATE_END', 'Date de cr&eacute;ation fin ');
define('_FOLDERHASNODOC','Aucune pi&egrave;ce pour ce dossier');
define('_OTHER_INFOS','Autres informations : historique du dossier et pi&egrave;ces manquantes pour la compl&eacute;tude');

define('_SEARCH_FOLDER','Recherche dossier');
define('_SELECTED_FOLDER','Dossier s&eacute;lectionn&eacute;');
define('_FOUND_FOLDERS','Dossiers trouv&eacute;s');
define('_FOLDERTYPE_LABEL','Libell&eacute; dossier');
define('_INFOS_FOLDERS','Infos dossier');

define('_CHOOSE_FOLDER', 'Choisissez un dossier');


//////////////create_folder.php
define('_CREATE_THE_FOLDER', 'Cr&eacute;er le dossier');
define('_NEW_EMPLOYEES_LIST','Liste des nouveaux collaborateurs');
define('_FOLDERS_LIST', 'Liste de dossiers');
define('_FOLDERS', 'dossiers');
define('_FOLDERS_COMMENT', 'Dossiers');
define('_CHOOSE2', 'Choisissez&hellip;');
define('_IS_MANDATORY', 'est obligatoire');
define('_FOLDER_CREATION', 'Cr&eacute;ation du dossier');

///////////////delete_popup.php
define('_DEL_FOLDER_NUM', 'Suppression du dossier n&deg;');
define('_DEL_FOLDER', 'Supprimer le dossier');



//Step in add_batch.php for physical_archive
define('_STEP_ONE', '1 - Choisissez un dossier');




/////////create folder
define('_CHOOSE_SOCIETY', 'Choisissez une soci&eacute;t&eacute;');
define('_THE_SOCIETY', 'La soci&eacute;t&eacute; ');

define('_MISSING_DOC', 'Pi&egrave;ces manquantes');
define('_MISSING_DOC2', 'Pi&egrave;ce(s) manquante(s)');
define('_PLEASE_SELECT_FOLDER', 'Vous devez s&eacute;lectionner un dossier');
define('_FOLDER_HISTORY', 'Historique dossier');
define('_CHOOSE_FOLDERTYPE', 'Choisissez un type de dossier');

define('_BROWSE_BY_FOLDER', 'Recherche Dossier');

/*************************** Foldertypes management *****************/
define('_FOLDERTYPE_ADDITION', 'Ajout type de dossier');
define('_FOLDERTYPE_MODIFICATION', 'Modification du type de dossier');
define('_FOLDERTYPES_LIST', 'Liste des types de dossier');
define('_TYPES', 'type(s)');
define('_ALL_FOLDERTYPES', 'Tous les types');
define('_FOLDERTYPE', 'Type de dossier');

define('_FOLDERTYPE_MISSING', 'Type de dossier manquant');

/************************** Fiche salarie ***************************/

define('_CONTRACT_HISTORY','Historique des contrats');
define('_ARCHIVED_DOC', 'Pi&egrave;ces archiv&eacute;es');
define('_SEND_RELANCE_MAIL', 'Envoyer un mail de relance');
define('_DIRECTION_DEP', 'Direction/Dpt');
define('_DEP_AGENCY', 'Service/agence');

define('_DELETE_FOLDER', 'Suppression de dossier');
define('_DELETE_FOLDER_NOTES1', 'La suppression de dossier est irr&eacute;versible, les pi&egrave;ces de ce dernier seront conserv&eacute;es mais ne seront plus accessibles en consultation.');
define('_REALLY_DELETE_FOLDER', 'Voulez vous supprimer le dossier ?');
define('_DELETE_FOLDER_NOTES2','Pour supprimer d&eacute;finitivement le dossier, saisissez &quot;EFFACER&quot; (en lettres majuscules) dans la case ci-dessous.');
define('_DELETE_FOLDER_NOTES3', 'Le dossier sera effac&eacute; apr&egrave;s cette validation.');
define('_DELETE_FOLDER_NOTES4', 'Le dossier ne peut &ecirc;tre supprim&eacute; car la confirmation est erron&eacute;e&hellip;');
define('_DELETE_FOLDER_NOTES5', 'Le dossier est d&eacute;sormais supprim&eacute; de la base de donn&eacute;es.');

define('_FOLDER_INDEX_MODIF', 'Modification des index du dossier');
define('_FOLDERS_OUT', 'Dossiers d&eacute;sarchiv&eacute;s');

define('_VIEW', 'Visualisation');

///////////////// Class_admin_foldertype

//CUSTOM

define('_MANDATORY_DOCTYPES_COMP', 'Types de document obligatoire pour la compl&eacute;tude du dossier');

define('_FOLDER_ID', 'Identifiant dossier');

define('_INDEX_FOR_FOLDERTYPES', 'Index possibles pour les types de dossier');
define('_SELECTED_DOCTYPES', 'Types de document selectionn&eacute;s');
define('_SHOW_FOLDER', 'Fiche dossier');

define('_FOLDERTYPE_UPDATE', 'Type de dossier modifi&eacute;');

define('_FOLDER_ATTACH', 'Rattachement &agrave; un dossier');
define('_INCOMPATIBILITY_MARKET_PROJECT', 'Incompatibilit&eacute; entre le Dossier et le Sous-dossier');

define('_FOLDER_VIEW_STAT', 'Nombre de dossiers consult&eacute;s');
define('_FOLDER_VIEW_STAT_DESC', 'Nombre de dossiers consult&eacute;s');
define('_FOLDER_HISTORY_STAT', 'Historique d&rsquo;un dossier');
define('_FOLDER_HISTORY_STAT_DESC', 'Historique d&rsquo;un dossier');

define('_VIEW_FOLDER', 'Visualisation du dossier');
////////// Reports label
define('_TITLE_STATS_CHOICE_FOLDER_TYPE','Par type de dossier');
define('_TITLE_STATS_CHOICE_GROUP','Par groupe d&rsquo;utilisateurs');
define('_TITLE_STATS_CHOICE_USER','Pour un utilisateur');
define('_TITLE_STATS_CHOICE_PERIOD','Pour une p&eacute;riode');
define('_TITLE_STATS_CHOICE_USER2','par l&rsquo;utilisateur');

define('_TITLE_STATS_NO_FOLDERS_VIEW','Aucun dossier consult&eacute; pour la p&eacute;riode');
define('_STATS_ERROR_CHOSE_USER','Il faut choisir un utilisateur existant.');
define('_NB_FOLDERS', 'Nombre de dossiers' );
define('_NB_VIEWED_FOLDERS','Nombre de dossiers consult&eacute;s');
define('_TITLE_STATS_CHOICE_ACTION','par type d&rsquo;action');
define('_ACTION_TYPE', 'Type d&rsquo;action');

define('_NO_STRUCTURE_ATTACHED2', 'Ce type de dossier n&rsquo;est attach&eacute; &agrave; aucune chemise');

define('_FOLDER_ADDED', 'Nouveau dossier cr&eacute;&eacute;');
define('_FOLDER_DETAILLED_PROPERTIES', 'Informations sur le dossier');
define('_FOLDER_PROPERTIES', 'Propri&eacute;t&eacute;s du dossier');
define('_SYSTEM_ID', 'ID Syst&egrave;me');
define('_MODIFICATION_DATE', 'Date de modification');
define('_UPDATE_FOLDER', 'Modifier des informations');
define('_FOLDER_INDEX_UPDATED', 'Index du dossier modifi&eacute;s');
define('_ALL_DOCS_WILL_BE_DELETED', 'tous les documents de ce dossier seront &eacute;lement supprim&eacute;s !');

define('_STRING', 'Chaine de caract&egrave;res');
define('_INTEGER', 'Entier');
define('_FLOAT', 'Flottant');
define('_DATE', 'Date');
define('_MAX', 'maximum');
define('_MIN', 'minimum');
define('_ERROR_COMPATIBILITY_FOLDER', 'Probl&egrave;me de compatibilit&eacute; entre<br/>le dossier et le type de document');
define('_ADDED_TO_FOLDER', ' ajout&eacute; au dossier');
define('_DELETED_FROM_FOLDER', ' supprim&eacute; du dossier');
define('_CHOOSE_PARENT_FOLDER', 'Associer ce dossier &agrave; un dossier existant');
define('_FOLDER_PARENT', 'Dossier parent');
define('_FOLDER_PARENT_DESC', 'Vous pouvez choisir de cr&eacute;er un dous-dossier en le rattachant &agrave; un dossier du m&ecirc;me type. Il y a seulement 2 niveaux : dossier et sous-dossier.');
?>
