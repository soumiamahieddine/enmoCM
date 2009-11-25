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
if (!defined('_ADMIN_FOLDERTYPES')) define('_ADMIN_FOLDERTYPES', 'Document types');
if (!defined('_ADMIN_FOLDERTYPES_DESC')) define('_ADMIN_FOLDERTYPES_DESC', 'Manage Document types. Define associated elements of qualification for each type, as well as the mandatory documents for a file to be complete.');

/*********************** Menu ***********************************/
if (!defined('_FOLDER_SEARCH')) define('_FOLDER_SEARCH', 'Search a file');
if (!defined('_SALARY_SHEET')) define('_SALARY_SHEET', 'Folder details sheet');
if (!defined('_FOLDER_OUT')) define('_FOLDER_OUT', 'Disarchive');
//////////////Recherche dossier
if (!defined('_SELECT_FOLDER_TITLE')) define('_SELECT_FOLDER_TITLE', 'Select a file');
if (!defined('_FOLDER_NUM')) define('_FOLDER_NUM', 'File nb.');
if (!defined('_COMPLETE')) define('_COMPLETE', 'Complete');
if (!defined('_INCOMPLETE')) define('_INCOMPLETE', 'Incomplete');
if (!defined('_FOUND_FOLDER')) define('_FOUND_FOLDER', 'file(s) found.');
if (!defined('_CHOOSE')) define('_CHOOSE', 'Select');
if (!defined('_ADV_SEARCH_FOLDER_TITLE')) define('_ADV_SEARCH_FOLDER_TITLE', 'Search a file');
if (!defined('_SEARCH_ADV_FOLDER')) define('_SEARCH_ADV_FOLDER', 'Search a file');
if (!defined('_NEW_SEARCH')) define('_NEW_SEARCH', 'Reset search criteria');
if (!defined('_SELECT_FOLDER')) define('_SELECT_FOLDER', 'S&eacute;lection Dossier');
if (!defined('_CREATE_FOLDER')) define('_CREATE_FOLDER', 'Add a file');
if (!defined('_CREATE_FOLDER2')) define('_CREATE_FOLDER2', 'Add a file');
if (!defined('_FOLDER')) define('_FOLDER', 'File');
if (!defined('_MODIFY_FOLDER')) define('_MODIFY_FOLDER', 'Right to modify the indices of a file');
if (!defined('_FOLDERID')) define('_FOLDERID', 'Nb.');
if (!defined('_FOLDERSYSTEMID')) define('_FOLDERSYSTEMID', 'Maarch System Number');
if (!defined('_FOLDERID_LONG')) define('_FOLDERID_LONG', 'File number');
if (!defined('_FOLDERNAME')) define('_FOLDERNAME', 'Customer name');
if (!defined('_FOLDERDATE')) define('_FOLDERDATE', 'Opening date');
if (!defined('_FOLDERDATE_START')) define('_FOLDERDATE_START', 'Opening date, start ');
if (!defined('_FOLDERDATE_END')) define('_FOLDERDATE_END', 'Opening date, end ');
if (!defined('_FOLDERHASNODOC')) define('_FOLDERHASNODOC','No document in this file');
if (!defined('_OTHER_INFOS')) define('_OTHER_INFOS','Other information : history of the file and missing documents');

if (!defined('_SEARCH_FOLDER')) define('_SEARCH_FOLDER','Search file');
if (!defined('_SELECTED_FOLDER')) define('_SELECTED_FOLDER','Selected file');
if (!defined('_FOUND_FOLDERS')) define('_FOUND_FOLDERS','Found files');
if (!defined('_FOLDERTYPE_LABEL')) define('_FOLDERTYPE_LABEL','File type label');
if (!defined('_INFOS_FOLDERS')) define('_INFOS_FOLDERS','File info');

if (!defined('_CHOOSE_FOLDER')) define('_CHOOSE_FOLDER', 'Select a file');


//////////////create_folder.php
if (!defined('_CREATE_THE_FOLDER')) define('_CREATE_THE_FOLDER', 'Add the file');
if (!defined('_NEW_EMPLOYEES_LIST')) define('_NEW_EMPLOYEES_LIST','New employee list');
if (!defined('_FOLDERS_LIST')) define('_FOLDERS_LIST', 'List of files');
if (!defined('_FOLDERS')) define('_FOLDERS', 'files');
if (!defined('_FOLDERS_COMMENT')) define('_FOLDERS_COMMENT', 'Files');
if (!defined('_CHOOSE2')) define('_CHOOSE2', 'Select&hellip;');
if (!defined('_IS_MANDATORY')) define('_IS_MANDATORY', 'is mandatory');
if (!defined('_FOLDER_CREATION')) define('_FOLDER_CREATION', 'Creating the file');

///////////////delete_popup.php
if (!defined('_DEL_FOLDER_NUM')) define('_DEL_FOLDER_NUM', 'Deleting file nb.');
if (!defined('_DEL_FOLDER')) define('_DEL_FOLDER', 'Delete the file');



//Step in add_batch.php for physical_archive
if (!defined('_STEP_ONE')) define('_STEP_ONE', '1 - Choose a file');




/////////create file
if (!defined('_CHOOSE_SOCIETY')) define('_CHOOSE_SOCIETY', 'Select a company');
if (!defined('_THE_SOCIETY')) define('_THE_SOCIETY', 'The company ');

if (!defined('_MISSING_DOC')) define('_MISSING_DOC', 'Missing document');
if (!defined('_MISSING_DOC2')) define('_MISSING_DOC2', 'Missing document(s)');
if (!defined('_PLEASE_SELECT_FOLDER')) define('_PLEASE_SELECT_FOLDER', 'You must select a file');
if (!defined('_FOLDER_HISTORY')) define('_FOLDER_HISTORY', 'History of the file');
if (!defined('_CHOOSE_FOLDERTYPE')) define('_CHOOSE_FOLDERTYPE', 'Select a type of file');

if (!defined('_BROWSE_BY_FOLDER')) define('_BROWSE_BY_FOLDER', 'Search a file');

/*************************** Foldertypes management *****************/
if (!defined('_FOLDERTYPE_ADDITION')) define('_FOLDERTYPE_ADDITION', 'Add a type of file');
if (!defined('_FOLDERTYPE_MODIFICATION')) define('_FOLDERTYPE_MODIFICATION', 'Modifying a type of file');
if (!defined('_FOLDERTYPES_LIST')) define('_FOLDERTYPES_LIST', 'list of file types');
if (!defined('_TYPES')) define('_TYPES', 'type(s)');
if (!defined('_ALL_FOLDERTYPES')) define('_ALL_FOLDERTYPES', 'All types');
if (!defined('_FOLDERTYPE')) define('_FOLDERTYPE', 'Types of file');

if (!defined('_FOLDERTYPE_MISSING')) define('_FOLDERTYPE_MISSING', 'This file type does not exist');

/************************** Fiche salarie ***************************/

if (!defined('_CONTRACT_HISTORY')) define('_CONTRACT_HISTORY','History of contracts');
if (!defined('_ARCHIVED_DOC')) define('_ARCHIVED_DOC', 'Archived documents');
if (!defined('_SEND_RELANCE_MAIL')) define('_SEND_RELANCE_MAIL', 'Send an email reminder');
if (!defined('_DIRECTION_DEP')) define('_DIRECTION_DEP', 'Direction/Dpt');
if (!defined('_DEP_AGENCY')) define('_DEP_AGENCY', 'Department/agency');

if (!defined('_DELETE_FOLDER')) define('_DELETE_FOLDER', 'Delete a file');
if (!defined('_DELETE_FOLDER_NOTES1')) define('_DELETE_FOLDER_NOTES1', 'Deleting a file cannot be undone, its constitutive documents will still be archived, but they will not be available for consultation.');
if (!defined('_REALLY_DELETE_FOLDER')) define('_REALLY_DELETE_FOLDER', 'Do you want to delete the file ?');
if (!defined('_DELETE_FOLDER_NOTES2')) define('_DELETE_FOLDER_NOTES2','To definitely delete the file, enter &quot;EFFACER&quot; (in capitals) in the field below.');
if (!defined('_DELETE_FOLDER_NOTES3')) define('_DELETE_FOLDER_NOTES3', 'The file will be deleted after this validation.');
if (!defined('_DELETE_FOLDER_NOTES4')) define('_DELETE_FOLDER_NOTES4', 'The file cannot be deleted because the validation is wrong&hellip;');
if (!defined('_DELETE_FOLDER_NOTES5')) define('_DELETE_FOLDER_NOTES5', 'The file is now deleted from the database.');

if (!defined('_FOLDER_INDEX_MODIF')) define('_FOLDER_INDEX_MODIF', 'Modify the indices of the file');
if (!defined('_FOLDERS_OUT')) define('_FOLDERS_OUT', 'Archived files');

if (!defined('_VIEW')) define('_VIEW', 'View');

///////////////// Class_admin_foldertype

//CUSTOM

if (!defined('_MANDATORY_DOCTYPES_COMP')) define('_MANDATORY_DOCTYPES_COMP', 'Mandatory doctype for a file to be complete');

if (!defined('_FOLDER_ID')) define('_FOLDER_ID', 'File ID');

if (!defined('_INDEX_FOR_FOLDERTYPES')) define('_INDEX_FOR_FOLDERTYPES', 'Available indices for file types');
if (!defined('_SELECTED_DOCTYPES')) define('_SELECTED_DOCTYPES', 'Selected file types');
if (!defined('_SHOW_FOLDER')) define('_SHOW_FOLDER', 'File details sheet');

if (!defined('_FOLDERTYPE_UPDATE')) define('_FOLDERTYPE_UPDATE', 'File type modified');

if (!defined('_FOLDER_ATTACH')) define('_FOLDER_ATTACH', 'Attach to a file');
if (!defined('_INCOMPATIBILITY_MARKET_PROJECT')) define('_INCOMPATIBILITY_MARKET_PROJECT', 'Selected file and sub-file are not compatible');

if (!defined('_FOLDER_VIEW_STAT')) define('_FOLDER_VIEW_STAT', 'Number of file viewed');
if (!defined('_FOLDER_VIEW_STAT_DESC')) define('_FOLDER_VIEW_STAT_DESC', 'Number of file viewed');
if (!defined('_FOLDER_HISTORY_STAT')) define('_FOLDER_HISTORY_STAT', 'Log for the file');
if (!defined('_FOLDER_HISTORY_STAT_DESC')) define('_FOLDER_HISTORY_STAT_DESC', 'Log for the file');

if (!defined('_VIEW_FOLDER')) define('_VIEW_FOLDER', 'View the file');
////////// Reports label
if (!defined('_TITLE_STATS_CHOICE_FOLDER_TYPE')) define('_TITLE_STATS_CHOICE_FOLDER_TYPE','By file type');
if (!defined('_TITLE_STATS_CHOICE_GROUP')) define('_TITLE_STATS_CHOICE_GROUP','by user groups');
if (!defined('_TITLE_STATS_CHOICE_USER')) define('_TITLE_STATS_CHOICE_USER','For a user');
if (!defined('_TITLE_STATS_CHOICE_PERIOD')) define('_TITLE_STATS_CHOICE_PERIOD','For a given period');
if (!defined('_TITLE_STATS_CHOICE_USER2')) define('_TITLE_STATS_CHOICE_USER2','by the user');

if (!defined('_TITLE_STATS_NO_FOLDERS_VIEW')) define('_TITLE_STATS_NO_FOLDERS_VIEW','No file has been viewed on given period');
if (!defined('_STATS_ERROR_CHOSE_USER')) define('_STATS_ERROR_CHOSE_USER','You must select an existing user.');
if (!defined('_NB_FOLDERS')) define('_NB_FOLDERS', 'Number of file');
if (!defined('_NB_VIEWED_FOLDERS')) define('_NB_VIEWED_FOLDERS','Number of files viewed');
if (!defined('_TITLE_STATS_CHOICE_ACTION')) define('_TITLE_STATS_CHOICE_ACTION','by action type');
if (!defined('_ACTION_TYPE')) define('_ACTION_TYPE', 'action type');

if (!defined('_NO_STRUCTURE_ATTACHED2')) define('_NO_STRUCTURE_ATTACHED2', 'This type of file is not attached to any files');

if (!defined('_FOLDER_ADDED')) define('_FOLDER_ADDED', 'New file created');
if (!defined('_FOLDER_DETAILLED_PROPERTIES')) define('_FOLDER_DETAILLED_PROPERTIES', 'Information on the file');
if (!defined('_FOLDER_PROPERTIES')) define('_FOLDER_PROPERTIES', 'Properties of the file');
if (!defined('_SYSTEM_ID')) define('_SYSTEM_ID', 'System ID');
if (!defined('_MODIFICATION_DATE')) define('_MODIFICATION_DATE', 'Modification date');
if (!defined('_UPDATE_FOLDER')) define('_UPDATE_FOLDER', 'Modify informations');
if (!defined('_FOLDER_INDEX_UPDATED')) define('_FOLDER_INDEX_UPDATED', 'File&apos;s indices have been modified');
if (!defined('_ALL_DOCS_WILL_BE_DELETED')) define('_ALL_DOCS_WILL_BE_DELETED', 'All document for this file have been deleted!');

if (!defined('_STRING')) define('_STRING', 'String');
if (!defined('_INTEGER')) define('_INTEGER', 'Integer');
if (!defined('_FLOAT')) define('_FLOAT', 'Float');
if (!defined('_DATE')) define('_DATE', 'Date');
if (!defined('_MAX')) define('_MAX', 'maximum');
if (!defined('_MIN')) define('_MIN', 'minimum');
if (!defined('_ERROR_COMPATIBILITY_FOLDER')) define('_ERROR_COMPATIBILITY_FOLDER', 'These file and document type are not vompatible');
if (!defined('_ADDED_TO_FOLDER')) define('_ADDED_TO_FOLDER', ' add  to the file');
if (!defined('_DELETED_FROM_FOLDER')) define('_DELETED_FROM_FOLDER', ' deleted from the file');
if (!defined('_ALL_FOLDERS')) define('_ALL_FOLDERS', 'All files');
?>
