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
define('_ADMIN_FOLDERTYPES', 'Document types');
define('_ADMIN_FOLDERTYPES_DESC', 'Manage Document types. Define associated elements of qualification for each type, as well as the mandatory documents for a file to be complete.');

/*********************** Menu ***********************************/
define('_FOLDER_SEARCH', 'Search a file');
define('_SALARY_SHEET', 'Folder details sheet');
define('_FOLDER_OUT', 'Disarchive');
//////////////Recherche dossier
define('_SELECT_FOLDER_TITLE', 'Select a file');
define('_FOLDER_NUM', 'File nb.');
define('_COMPLETE', 'Complete');
define('_INCOMPLETE', 'Incomplete');
define('_FOUND_FOLDER', 'file(s) found.');
define('_CHOOSE', 'Select');
define('_ADV_SEARCH_FOLDER_TITLE', 'Search a file');
define('_SEARCH_ADV_FOLDER', 'Search a file');
define('_NEW_SEARCH', 'Reset search criteria');
define('_SELECT_FOLDER', 'S&eacute;lection Dossier');
define('_CREATE_FOLDER', 'Add a file');
define('_CREATE_FOLDER2', 'Add a file');
define('_FOLDER', 'File');
define('_MODIFY_FOLDER', 'Right to modify the indices of a file');
define('_FOLDERID', 'Nb.');
define('_FOLDERSYSTEMID', 'Maarch System Number');
define('_FOLDERID_LONG', 'File number');
define('_FOLDERNAME', 'Customer name');
define('_FOLDERDATE', 'Opening date');
define('_FOLDERDATE_START', 'Opening date, start ');
define('_FOLDERDATE_END', 'Opening date, end ');
define('_FOLDERHASNODOC','No document in this file');
define('_OTHER_INFOS','Other information : history of the file and missing documents');

define('_SEARCH_FOLDER','Search file');
define('_SELECTED_FOLDER','Selected file');
define('_FOUND_FOLDERS','Found files');
define('_FOLDERTYPE_LABEL','File type label');
define('_INFOS_FOLDERS','File info');

define('_CHOOSE_FOLDER', 'Select a file');


//////////////create_folder.php
define('_CREATE_THE_FOLDER', 'Add the file');
define('_NEW_EMPLOYEES_LIST','New employee list');
define('_FOLDERS_LIST', 'List of files');
define('_FOLDERS', 'files');
define('_FOLDERS_COMMENT', 'Files');
define('_CHOOSE2', 'Select&hellip;');
define('_IS_MANDATORY', 'is mandatory');
define('_FOLDER_CREATION', 'Creating the file');

///////////////delete_popup.php
define('_DEL_FOLDER_NUM', 'Deleting file nb.');
define('_DEL_FOLDER', 'Delete the file');



//Step in add_batch.php for physical_archive
define('_STEP_ONE', '1 - Choose a file');




/////////create file
define('_CHOOSE_SOCIETY', 'Select a company');
define('_THE_SOCIETY', 'The company ');

define('_MISSING_DOC', 'Missing document');
define('_MISSING_DOC2', 'Missing document(s)');
define('_PLEASE_SELECT_FOLDER', 'You must select a file');
define('_FOLDER_HISTORY', 'History of the file');
define('_CHOOSE_FOLDERTYPE', 'Select a type of file');

define('_BROWSE_BY_FOLDER', 'Search a file');

/*************************** Foldertypes management *****************/
define('_FOLDERTYPE_ADDITION', 'Add a type of file');
define('_FOLDERTYPE_MODIFICATION', 'Modifying a type of file');
define('_FOLDERTYPES_LIST', 'list of file types');
define('_TYPES', 'type(s)');
define('_ALL_FOLDERTYPES', 'All types');
define('_FOLDERTYPE', 'Types of file');

define('_FOLDERTYPE_MISSING', 'This file type does not exist');

/************************** Fiche salarie ***************************/

define('_CONTRACT_HISTORY','History of contracts');
define('_ARCHIVED_DOC', 'Archived documents');
define('_SEND_RELANCE_MAIL', 'Send an email reminder');
define('_DIRECTION_DEP', 'Direction/Dpt');
define('_DEP_AGENCY', 'Department/agency');

define('_DELETE_FOLDER', 'Delete a file');
define('_DELETE_FOLDER_NOTES1', 'Deleting a file cannot be undone, its constitutive documents will still be archived, but they will not be available for consultation.');
define('_REALLY_DELETE_FOLDER', 'Do you want to delete the file ?');
define('_DELETE_FOLDER_NOTES2','To definitely delete the file, enter &quot;EFFACER&quot; (in capitals) in the field below.');
define('_DELETE_FOLDER_NOTES3', 'The file will be deleted after this validation.');
define('_DELETE_FOLDER_NOTES4', 'The file cannot be deleted because the validation is wrong&hellip;');
define('_DELETE_FOLDER_NOTES5', 'The file is now deleted from the database.');

define('_FOLDER_INDEX_MODIF', 'Modify the indices of the file');
define('_FOLDERS_OUT', 'Archived files');

define('_VIEW', 'View');

///////////////// Class_admin_foldertype

//CUSTOM

define('_MANDATORY_DOCTYPES_COMP', 'Mandatory doctype for a file to be complete');

define('_FOLDER_ID', 'File ID');

define('_INDEX_FOR_FOLDERTYPES', 'Available indices for file types');
define('_SELECTED_DOCTYPES', 'Selected file types');
define('_SHOW_FOLDER', 'File details sheet');

define('_FOLDERTYPE_UPDATE', 'File type modified');

define('_FOLDER_ATTACH', 'Attach to a file');
define('_INCOMPATIBILITY_MARKET_PROJECT', 'Selected file and sub-file are not compatible');

define('_FOLDER_VIEW_STAT', 'Number of file viewed');
define('_FOLDER_VIEW_STAT_DESC', 'Number of file viewed');
define('_FOLDER_HISTORY_STAT', 'Log for the file');
define('_FOLDER_HISTORY_STAT_DESC', 'Log for the file');

define('_VIEW_FOLDER', 'View the file');
////////// Reports label
define('_TITLE_STATS_CHOICE_FOLDER_TYPE','By file type');
define('_TITLE_STATS_CHOICE_GROUP','by user groups');
define('_TITLE_STATS_CHOICE_USER','For a user');
define('_TITLE_STATS_CHOICE_PERIOD','For a given period');
define('_TITLE_STATS_CHOICE_USER2','by the user');

define('_TITLE_STATS_NO_FOLDERS_VIEW','No file has been viewed on given period');
define('_STATS_ERROR_CHOSE_USER','You must select an existing user.');
define('_NB_FOLDERS', 'Number of file');
define('_NB_VIEWED_FOLDERS','Number of files viewed');
define('_TITLE_STATS_CHOICE_ACTION','by action type');
define('_ACTION_TYPE', 'action type');

define('_NO_STRUCTURE_ATTACHED2', 'This type of file is not attached to any files');

define('_FOLDER_ADDED', 'New file created');
define('_FOLDER_DETAILLED_PROPERTIES', 'Information on the file');
define('_FOLDER_PROPERTIES', 'Properties of the file');
define('_SYSTEM_ID', 'System ID');
define('_MODIFICATION_DATE', 'Modification date');
define('_UPDATE_FOLDER', 'Modify informations');
define('_FOLDER_INDEX_UPDATED', 'File&apos;s indices have been modified');
define('_ALL_DOCS_WILL_BE_DELETED', 'All document for this file have been deleted!');

define('_STRING', 'String');
define('_INTEGER', 'Integer');
define('_FLOAT', 'Float');
define('_DATE', 'Date');
define('_MAX', 'maximum');
define('_MIN', 'minimum');
define('_ERROR_COMPATIBILITY_FOLDER', 'These file and document type are not vompatible');
define('_ADDED_TO_FOLDER', ' add  to the file');
define('_DELETED_FROM_FOLDER', ' deleted from the file');
define('_ALL_FOLDERS', 'All files');
?>
