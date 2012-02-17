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

if (!defined('_MEP_VERSION')) define('_MEP_VERSION', 'Maarch Entreprise v1.2');

/************** Administration **************/
if (!defined('_ADMIN_USERS')) define('_ADMIN_USERS', 'Users');
if (!defined('_ADMIN_USERS_DESC')) define('_ADMIN_USERS_DESC', 'Add, suspend, or modify users profiles. Affect users to their groups and define their primary group.');
if (!defined('_ADMIN_GROUPS')) define('_ADMIN_GROUPS', 'User groups');
if (!defined('_ADMIN_GROUPS_DESC')) define('_ADMIN_GROUPS_DESC', 'Add, suspend, or modify user groups. Set privileges or authorization to access resources.');
if (!defined('_ADMIN_ARCHITECTURE')) define('_ADMIN_ARCHITECTURE', 'Classification scheme');
if (!defined('_ADMIN_ARCHITECTURE_DESC')) define('_ADMIN_ARCHITECTURE_DESC', 'Define classification schemes structure (File / sub-file / document type). For each, define their associated descriptors and whether they are mandatory for a file to be complete.');
if (!defined('_VIEW_HISTORY')) define('_VIEW_HISTORY', 'Logs');
if (!defined('_VIEW_HISTORY_BATCH')) define('_VIEW_HISTORY_BATCH', 'Batches logs');
if (!defined('_VIEW_HISTORY_DESC')) define('_VIEW_HISTORY_DESC', 'View the log of actions done in Maarch DMS.');
if (!defined('_VIEW_HISTORY_BATCH_DESC')) define('_VIEW_HISTORY_BATCH_DESC', 'View the log of batchs');
if (!defined('_ADMIN_MODULES')) define('_ADMIN_MODULES', 'Manage modules');
if (!defined('_ADMIN_SERVICE')) define('_ADMIN_SERVICE', 'Administration service');
if (!defined('_XML_PARAM_SERVICE_DESC')) define('_XML_PARAM_SERVICE_DESC', 'View servicex XML config');
if (!defined('_XML_PARAM_SERVICE')) define('_XML_PARAM_SERVICE', 'View servicex XML config');
if (!defined('_MODULES_SERVICES')) define('_MODULES_SERVICES', 'Services defined by modules');
if (!defined('_APPS_SERVICES')) define('_APPS_SERVICES', 'Services defined by the application');
if (!defined('_ADMIN_STATUS_DESC')) define('_ADMIN_STATUS_DESC', 'Add or modify status.');
if (!defined('_ADMIN_ACTIONS_DESC')) define('_ADMIN_ACTIONS_DESC', 'Add or modify actions.');
if (!defined('_ADMIN_SERVICES_UNKNOWN')) define('_ADMIN_SERVICES_UNKNOWN', 'Unknown administration service');
if (!defined('_NO_RIGHTS_ON')) define('_NO_RIGHTS_ON', 'No rights for');
if (!defined('_NO_LABEL_FOUND')) define('_NO_LABEL_FOUND', 'No label found for this service');

if (!defined('_FOLDERTYPES_LIST')) define('_FOLDERTYPES_LIST', 'List of file types');
if (!defined('_SELECTED_FOLDERTYPES')) define('_SELECTED_FOLDERTYPES', 'Selected file types');
if (!defined('_FOLDERTYPE_ADDED')) define('_FOLDERTYPE_ADDED', 'New file type added');
if (!defined('_FOLDERTYPE_DELETION')) define('_FOLDERTYPE_DELETION', 'File type deleted');
if (!defined('_VERSION_BASE_AND_XML_BASEVERSION_NOT_MATCH')) define( '_VERSION_BASE_AND_XML_BASEVERSION_NOT_MATCH', 'Warning: Database need to be updated...');


/*********************** commons ***********************************/
if (!defined('_MODE')) define('_MODE', 'Mode');

/************** Lists **************/
if (!defined('_GO_TO_PAGE')) define('_GO_TO_PAGE', 'Go to page');
if (!defined('_NEXT')) define('_NEXT', 'Next');
if (!defined('_PREVIOUS')) define('_PREVIOUS', 'Previous');
if (!defined('_ALPHABETICAL_LIST')) define('_ALPHABETICAL_LIST', 'Alphabetical list');
if (!defined('_ASC_SORT')) define('_ASC_SORT', 'Upwards sorting');
if (!defined('_DESC_SORT')) define('_DESC_SORT', 'Downwards sorting');
if (!defined('_ACCESS_LIST_STANDARD')) define('_ACCESS_LIST_STANDARD', 'Display simple lists');
if (!defined('_ACCESS_LIST_EXTEND')) define('_ACCESS_LIST_EXTEND', 'Display extended lists');
if (!defined('_DISPLAY')) define('_DISPLAY', 'Display');
/************** Actions **************/
if (!defined('_DELETE')) define('_DELETE', 'Delete');
if (!defined('_ADD')) define('_ADD', 'Add');
if (!defined('_REMOVE')) define('_REMOVE', 'Remove');
if (!defined('_MODIFY')) define('_MODIFY', 'Modify');
if (!defined('_SUSPEND')) define('_SUSPEND', 'Disable');
if (!defined('_AUTHORIZE')) define('_AUTHORIZE', 'Enable');
if (!defined('_SEND')) define('_SEND', 'Send');
if (!defined('_SEARCH')) define('_SEARCH', 'Search');
if (!defined('_RESET')) define('_RESET', 'Reset');
if (!defined('_VALIDATE')) define('_VALIDATE', 'Confirm');
if (!defined('_CANCEL')) define('_CANCEL', 'Cancel');
if (!defined('_ADDITION')) define('_ADDITION', 'Addition');
if (!defined('_MODIFICATION')) define('_MODIFICATION', 'Modification');
if (!defined('_DIFFUSION')) define('_DIFFUSION', 'Diffusion');
if (!defined('_DELETION')) define('_DELETION', 'Deletion');
if (!defined('_SUSPENSION')) define('_SUSPENSION', 'Suspension');
if (!defined('_VALIDATION')) define('_VALIDATION', 'Confirmation');
if (!defined('_REDIRECTION')) define('_REDIRECTION', 'Redirection');
if (!defined('_DUPLICATION')) define('_DUPLICATION', 'Duplication');
if (!defined('_PROPOSITION')) define('_PROPOSITION', 'Proposition');
if (!defined('_ERR')) define( '_ERR', 'Error');
if (!defined('_CLOSE')) define('_CLOSE', 'Close');
if (!defined('_CLOSE_WINDOW')) define('_CLOSE_WINDOW', 'Close the window');
if (!defined('_DIFFUSE')) define('_DIFFUSE', 'Diffuse');
if (!defined('_DOWN')) define('_DOWN', 'Move down');
if (!defined('_UP')) define('_UP', 'Move up');
if (!defined('_REDIRECT')) define('_REDIRECT', 'Redirect');
if (!defined('_DELETED')) define('_DELETED', 'Deleted');
if (!defined('_CONTINUE')) define('_CONTINUE', 'Continue');
if (!defined('_VIEW')) define('_VIEW','View');
if (!defined('_CHOOSE_ACTION')) define('_CHOOSE_ACTION', 'Choose an action');
if (!defined('_ACTIONS')) define('_ACTIONS', 'Actions');
if (!defined('_ACTION_PAGE')) define('_ACTION_PAGE', 'Result page for the action');
if (!defined('_DO_NOT_MODIFY_UNLESS_EXPERT')) define('_DO_NOT_MODIFY_UNLESS_EXPERT', ' Don&quot;t modify this section unless you know exactly what you do. Wrong settings can stop the application from working!');
if (!defined('_INFOS_ACTIONS')) define('_INFOS_ACTIONS', 'You must choose at least a status and/or a script file.');



/************** Forms ans lists **************/
define('_ID', 'Id');
define('_PASSWORD', 'Password');
if (!defined('_GROUP')) define('_GROUP', 'Group');
if (!defined('_USER')) define('_USER', 'User');
if (!defined('_DESC')) define('_DESC', 'Description');
if (!defined('_LASTNAME')) define('_LASTNAME', 'Name');
if (!defined('_THE_LASTNAME')) define('_THE_LASTNAME', 'The name');
if (!defined('_THE_FIRSTNAME')) define('_THE_FIRSTNAME', 'The first name');
if (!defined('_FIRSTNAME')) define('_FIRSTNAME', 'First name');
if (!defined('_STATUS')) define('_STATUS', 'Status');
if (!defined('_DEPARTMENT')) define('_DEPARTMENT', 'Department');
if (!defined('_FUNCTION')) define('_FUNCTION', 'Role');
if (!defined('_PHONE_NUMBER')) define('_PHONE_NUMBER', 'Phone number');
if (!defined('_MAIL')) define('_MAIL', 'E-mail');
if (!defined('_DOCTYPE')) define('_DOCTYPE', 'Document type');
if (!defined('_TYPE')) define('_TYPE', 'Type');
if (!defined('_SELECT_ALL')) define('_SELECT_ALL', 'Select all');
if (!defined('_DATE')) define('_DATE', 'Date');
if (!defined('_ACTION')) define('_ACTION', 'Action');
if (!defined('_COMMENTS')) define('_COMMENTS', 'Comments');
if (!defined('_ENABLED')) define('_ENABLED', 'Enabled');
if (!defined('_NOT_ENABLED')) define('_NOT_ENABLED', 'Disabled');
if (!defined('_RESSOURCES_COLLECTION')) define('_RESSOURCES_COLLECTION','Document collection');
if (!defined('_RECIPIENT')) define('_RECIPIENT', 'Recipient');
if (!defined('_START')) define('_START', 'Start');
if (!defined('_END')) define('_END', 'End');
if (!defined('_KEYWORD')) define( '_KEYWORD', 'Keyword');
if (!defined('_NO_KEYWORD')) define( '_NO_KEYWORD', 'No keyword');
if (!defined('_SYSTEM_PARAMETERS')) define( '_SYSTEM_PARAMETERS', 'system parameters');
if (!defined('_TO_VALIDATE')) define( '_TO_VALIDATE', 'To validate');
if (!defined('_INDEXING')) define( '_INDEXING', 'Indexing');
if (!defined('_QUALIFY')) define( '_QUALIFY', 'Qualify');

/************** Messages pop up **************/
if (!defined('_REALLY_SUSPEND')) define('_REALLY_SUSPEND', 'Do you really want to disable ');
if (!defined('_REALLY_AUTHORIZE')) define('_REALLY_AUTHORIZE', 'Do you really want to enable  ');
if (!defined('_REALLY_DELETE')) define('_REALLY_DELETE', 'Do you really want to remove ');
if (!defined('_DEFINITIVE_ACTION')) define('_DEFINITIVE_ACTION', 'This action is definitive.');

/************** Misc **************/
if (!defined('_YES')) define('_YES', 'yes');
if (!defined('_NO')) define('_NO', 'No');
if (!defined('_UNKNOWN')) define('_UNKNOWN', 'Unknown');
if (!defined('_SINCE')) define('_SINCE','Since');
if (!defined('_FOR')) define('_FOR','To');
if (!defined('_HELLO')) define('_HELLO','Hello');
if (!defined('_OBJECT')) define('_OBJECT','Object');
if (!defined('_BACK')) define('_BACK','Back');
if (!defined('_FORMAT')) define('_FORMAT','Format');
if (!defined('_SIZE')) define('_SIZE','Size');
if (!defined('_DOC')) define('_DOC', 'Document ');
if (!defined('_THE_DOC')) define('_THE_DOC', 'The document');
if (!defined('_BYTES')) define('_BYTES', 'bytes');
if (!defined('_OR')) define('_OR', 'or');
if (!defined('_NOT_AVAILABLE')) define('_NOT_AVAILABLE', 'Not available');
if (!defined('_SELECTION')) define('_SELECTION', 'Selection');
if (!defined('_AND')) define('_AND', ' and ' );
if (!defined('_FILE')) define('_FILE','File');
if (!defined('_UNTIL')) define('_UNTIL', 'to');
if (!defined('_ALL')) define('_ALL', 'All');

//class functions
if (!defined('_SECOND')) define('_SECOND', 'second');
if (!defined('_SECONDS')) define('_SECONDS', 'seconds');
if (!defined('_PAGE_GENERATED_IN')) define('_PAGE_GENERATED_IN', 'Generated in');
if (!defined('_IS_EMPTY')) define('_IS_EMPTY', 'is empty');
if (!defined('_MUST_MAKE_AT_LEAST')) define('_MUST_MAKE_AT_LEAST', 'must contain at least' );
if (!defined('_CHARACTER')) define('_CHARACTER', 'character');
if (!defined('_CHARACTERS')) define('_CHARACTERS', 'characters');
if (!defined('MUST_BE_LESS_THAN')) define('MUST_BE_LESS_THAN', 'must not be longer than');
if (!defined('_WRONG_FORMAT')) define('_WRONG_FORMAT', 'is not valid');
if (!defined('_WELCOME')) define('_WELCOME', 'Welcome to Maarch Entreprise!');
if (!defined('_WELCOME_TITLE')) define('_WELCOME_TITLE', 'Home');
if (!defined('_HELP')) define('_HELP', 'Help');
if (!defined('_SEARCH_ADV_SHORT')) define('_SEARCH_ADV_SHORT', 'Advanced search');
if (!defined('_RESULTS')) define('_RESULTS', 'Results');
if (!defined('_USERS_LIST_SHORT')) define('_USERS_LIST_SHORT', 'User list');
if (!defined('_MODELS_LIST_SHORT')) define('_MODELS_LIST_SHORT', 'Template list');
if (!defined('_GROUPS_LIST_SHORT')) define('_GROUPS_LIST_SHORT', 'Group list');
if (!defined('_DEPARTMENTS_LIST_SHORT')) define('_DEPARTMENTS_LIST_SHORT', 'Service list');
if (!defined('_BITMASK')) define('_BITMASK', 'Bitmask parameter');
if (!defined('_DOCTYPES_LIST_SHORT')) define('_DOCTYPES_LIST_SHORT', 'Type list');
if (!defined('_BAD_MONTH_FORMAT')) define('_BAD_MONTH_FORMAT', 'The month is not correct');
if (!defined('_BAD_DAY_FORMAT')) define('_BAD_DAY_FORMAT', 'The day is not correct');
if (!defined('_BAD_YEAR_FORMAT')) define('_BAD_YEAR_FORMAT', 'The year not correct');
if (!defined('_BAD_FEBRUARY')) define('_BAD_FEBRUARY', 'February has 29 days or less');
if (!defined('_CHAPTER_SHORT')) define('_CHAPTER_SHORT', 'Chapt. ');
if (!defined('_PROCESS_SHORT')) define('_PROCESS_SHORT', 'Processing');
if (!defined('_CARD')) define('_CARD', 'form');

/************************* First login ***********************************/
if (!defined('_MODIFICATION_PSW')) define('_MODIFICATION_PSW', 'Modifying Password');
if (!defined('_YOUR_FIRST_CONNEXION')) define('_YOUR_FIRST_CONNEXION', 'Welcome to March Entreprise!<br /> This is your first connexion to the application.');
if (!defined('_PLEASE_CHANGE_PSW')) define('_PLEASE_CHANGE_PSW', ' Please modify your password.');
if (!defined('_ASKED_ONLY_ONCE')) define('_ASKED_ONLY_ONCE', 'This will only be asked once');
if (!defined('_FIRST_CONN')) define('_FIRST_CONN', 'First connection connection');
if (!defined('_LOGIN')) define('_LOGIN', 'Connection');
if (!defined('_RELOGIN')) define('_RELOGIN', 'Reconnection');

/*************************  index  page***********************************/
if (!defined('_LOGO_ALT')) define('_LOGO_ALT', 'Back to homepage');
if (!defined('_LOGOUT')) define('_LOGOUT', 'Logout');
if (!defined('_MENU')) define('_MENU', 'Menu');
if (!defined('_ADMIN')) define('_ADMIN', 'Administration');
if (!defined('_SUMMARY')) define('_SUMMARY', 'Admin panel');
if (!defined('_MANAGE_DIPLOMA')) define('_MANAGE_DIPLOMA', 'Manage diplomas');
if (!defined('_MANAGE_CONTRACT')) define('_MANAGE_CONTRACT', 'Manage contracts types');
if (!defined('_MANAGE_REL_MODEL')) define('_MANAGE_REL_MODEL', 'Manage reminder template');
if (!defined('_MANAGE_DOCTYPES')) define('_MANAGE_DOCTYPES', 'Manage document types');
if (!defined('_MANAGE_DOCTYPES_DESC')) define('_MANAGE_DOCTYPES_DESC', 'Manage document types. Document types are attached to a resource collection. For each type, you can define the descriptors to fill in and whether they are mandatory.');
if (!defined('_VIEW_HISTORY2')) define('_VIEW_HISTORY2', 'View logs');
if (!defined('_VIEW_HISTORY_BATCH2')) define('_VIEW_HISTORY_BATCH2', 'View batches logs');
if (!defined('_INDEX_FILE')) define('_INDEX_FILE', 'Add a document');
if (!defined('_WORDING')) define('_WORDING', 'Label');
if (!defined('_COLLECTION')) define('_COLLECTION', 'Collection');
if (!defined('_VIEW_TREE_DOCTYPES')) define('_VIEW_TREE_DOCTYPES', 'Tree view of classification scheme');
if (!defined('_VIEW_TREE_DOCTYPES_DESC')) define('_VIEW_TREE_DOCTYPES_DESC', 'Tree view of your classification scheme (type of file, sub-file and type of documents)');
if (!defined('_WELCOME_ON')) define('_WELCOME_ON', 'Welcome to');

/************************* Administration ***********************************/

/**************Summary**************/
if (!defined('_MANAGE_GROUPS_APP')) define('_MANAGE_GROUPS_APP', 'Manage user groups');
if (!defined('_MANAGE_USERS_APP')) define('_MANAGE_USERS_APP', 'Manage Users');
if (!defined('_MANAGE_DIPLOMA_APP')) define('_MANAGE_DIPLOMA_APP', 'Manage diplomas');
if (!defined('_MANAGE_DOCTYPES_APP')) define('_MANAGE_DOCTYPES_APP', 'Manage document types');
if (!defined('_MANAGE_ARCHI_APP')) define('_MANAGE_ARCHI_APP', 'Manage documents types sorting tree');
if (!defined('_MANAGE_CONTRACT_APP')) define('_MANAGE_CONTRACT_APP', 'Manage types of contracts');
if (!defined('_HISTORY_EXPLANATION')) define('_HISTORY_EXPLANATION', 'Monitor modifications, deletions and additions in the application');
if (!defined('_ARCHI_EXP')) define('_ARCHI_EXP', 'Files, sub-files and document types');


/************** Groups : Liste + Forms**************/

if (!defined('_GROUPS_LIST')) define('_GROUPS_LIST', 'Group list');
if (!defined('_ADMIN_GROUP')) define('_ADMIN_GROUP', 'Admin Group');
if (!defined('_ADD_GROUP')) define('_ADD_GROUP', 'Add a group');
if (!defined('_ALL_GROUPS')) define('_ALL_GROUPS', 'All the groups');
if (!defined('_GROUPS')) define('_GROUPS', 'groups');

if (!defined('_GROUP_ADDITION')) define('_GROUP_ADDITION', 'Add a group');
if (!defined('_GROUP_MODIFICATION')) define('_GROUP_MODIFICATION', 'Edit a group');
if (!defined('_SEE_GROUP_MEMBERS')) define('_SEE_GROUP_MEMBERS', 'See users of this groups');
if (!defined('_OTHER_RIGHTS')) define('_OTHER_RIGHTS', 'Other rights');
if (!defined('_MODIFY_GROUP')) define('_MODIFY_GROUP', 'Accept changes');
if (!defined('_THE_GROUP')) define('_THE_GROUP', 'The group');
if (!defined('_HAS_NO_SECURITY')) define('_HAS_NO_SECURITY', 'has no defined security' );

if (!defined('_DEFINE_A_GRANT')) define('_DEFINE_A_GRANT', 'Define At least an access right');
if (!defined('_MANAGE_RIGHTS')) define('_MANAGE_RIGHTS', 'This group has access to following resources');
if (!defined('_TABLE')) define('_TABLE', 'Table');
if (!defined('_WHERE_CLAUSE')) define('_WHERE_CLAUSE', 'WHERE clause');
if (!defined('_INSERT')) define('_INSERT', 'Insertion');
if (!defined('_UPDATE')) define('_UPDATE', 'Update');
if (!defined('_REMOVE_ACCESS')) define('_REMOVE_ACCESS', 'Remove access');
if (!defined('_MODIFY_ACCESS')) define('_MODIFY_ACCESS', 'Modify access');
if (!defined('_UPDATE_RIGHTS')) define('_UPDATE_RIGHTS', 'update rights');
if (!defined('_ADD_GRANT')) define('_ADD_GRANT', 'Add access');
if (!defined('_USERS_LIST_IN_GROUP')) define('_USERS_LIST_IN_GROUP', 'List of users in the group');

/************** Users : Liste + Forms **************/

if (!defined('_USERS_LIST')) define('_USERS_LIST', 'User list');
if (!defined('_ADD_USER')) define('_ADD_USER', 'Add a user');
if (!defined('_ALL_USERS')) define('_ALL_USERS', 'all users');
if (!defined('_USERS')) define('_USERS', 'users');
if (!defined('_USER_ADDITION')) define('_USER_ADDITION', 'Add an user');
if (!defined('_USER_MODIFICATION')) define('_USER_MODIFICATION', 'Modify an user');
if (!defined('_MODIFY_USER')) define('_MODIFY_USER', 'Modify the user');

if (!defined('_NOTES')) define('_NOTES', 'Notes');
if (!defined('_NOTE1')) define('_NOTE1', 'Mandatory fields are shown with a red star ');
if (!defined('_NOTE2')) define('_NOTE2', 'The primary group is mandatory');
if (!defined('_NOTE3')) define('_NOTE3', 'The first group selected will be the primary group of the user');
if (!defined('_USER_GROUPS_TITLE')) define('_USER_GROUPS_TITLE', 'The user belongs to the following group(s)');
if (!defined('_USER_ENTITIES_TITLE')) define('_USER_ENTITIES_TITLE', 'The user belongs to the following department(s)');
if (!defined('_DELETE_GROUPS')) define('_DELETE_GROUPS', 'Delete group(s)');
if (!defined('_ADD_TO_GROUP')) define('_ADD_TO_GROUP', 'Add a group');
if (!defined('_CHOOSE_PRIMARY_GROUP')) define('_CHOOSE_PRIMARY_GROUP', 'Choose as primary group');
if (!defined('_USER_BELONGS_NO_GROUP')) define('_USER_BELONGS_NO_GROUP', 'The user does not belong to any group');
if (!defined('_USER_BELONGS_NO_ENTITY')) define('_USER_BELONGS_NO_ENTITY', 'The user does not belong to any department');
if (!defined('_CHOOSE_ONE_GROUP')) define('_CHOOSE_ONE_GROUP', 'Select at least one group');
if (!defined('_PRIMARY_GROUP')) define('_PRIMARY_GROUP', 'Primary Group');
if (!defined('_CHOOSE_GROUP')) define('_CHOOSE_GROUP', 'Select a  group');
if (!defined('_ROLE')) define('_ROLE', 'Role');

if (!defined('_THE_PSW')) define('_THE_PSW', 'The password');
if (!defined('_THE_PSW_VALIDATION')) define('_THE_PSW_VALIDATION', 'Verification for the password' );
if (!defined('_REENTER_PSW')) define('_REENTER_PSW', 'Reenter the password');
if (!defined('_USER_ACCESS_DEPARTMENT')) define('_USER_ACCESS_DEPARTMENT', 'The user has access to following departments');
if (!defined('_FIRST_PSW')) define('_FIRST_PSW', 'The first password ');
if (!defined('_SECOND_PSW')) define('_SECOND_PSW', 'The second password ');

if (!defined('_PASSWORD_MODIFICATION')) define('_PASSWORD_MODIFICATION', 'PAssword modification');
if (!defined('_PASSWORD_FOR_USER')) define('_PASSWORD_FOR_USER', 'the password for the usert');
if (!defined('_HAS_BEEN_RESET')) define('_HAS_BEEN_RESET', 'has been reset');
if (!defined('_NEW_PASW_IS')) define('_NEW_PASW_IS', 'the new password is ');
if (!defined('_DURING_NEXT_CONNEXION')) define('_DURING_NEXT_CONNEXION', 'on the next login ');
if (!defined('_MUST_CHANGE_PSW')) define('_MUST_CHANGE_PSW', 'must change his/her password');

if (!defined('_NEW_PASSWORD_USER')) define('_NEW_PASSWORD_USER', 'Resetting the password for the user');

/************** Doctypes : Liste + Forms **************/

if (!defined('_DOCTYPES_LIST')) define('_DOCTYPES_LIST', 'List of document types');
if (!defined('_ADD_DOCTYPE')) define('_ADD_DOCTYPE', 'Add a document type');
if (!defined('_ALL_DOCTYPES')) define('_ALL_DOCTYPES', 'All types');
if (!defined('_TYPES')) define('_TYPES', 'types');

if (!defined('_DOCTYPE_MODIFICATION')) define('_DOCTYPE_MODIFICATION', 'Modify a document type');
if (!defined('_DOCTYPE_CREATION')) define('_DOCTYPE_CREATION', 'Add a document type');

if (!defined('_MODIFY_DOCTYPE')) define('_MODIFY_DOCTYPE', 'Confirm changes');
if (!defined('_ATTACH_SUBFOLDER')) define('_ATTACH_SUBFOLDER', 'Attach to sub-file');
if (!defined('_CHOOSE_SUBFOLDER')) define('_CHOOSE_SUBFOLDER', 'Select a sub-file');
if (!defined('_MANDATORY_FOR_COMPLETE')) define('_MANDATORY_FOR_COMPLETE', 'Mandatory for a file to be complete');
if (!defined('_MORE_THAN_ONE')) define('_MORE_THAN_ONE', 'Iterative file');
if (!defined('_MANDATORY_FIELDS_IN_INDEX')) define('_MANDATORY_FIELDS_IN_INDEX', 'Mandatory fields for indexing');
if (!defined('_DIPLOMA_LEVEL')) define('_DIPLOMA_LEVEL', 'Degree of the dipoma');
if (!defined('_THE_DIPLOMA_LEVEL')) define('_THE_DIPLOMA_LEVEL', 'The Degree of the diploma');
if (!defined('_DATE_END_DETACH_TIME')) define('_DATE_END_DETACH_TIME', 'Date de fin de p&eacute;riode de d&eacute;tachement');
if (!defined('_START_DATE')) define('_START_DATE', 'Beginning date');
if (!defined('_START_DATE_PROBATION')) define('_START_DATE_PROBATION', 'Probation beginning date');
if (!defined('_END_DATE')) define('_END_DATE', 'End date');
if (!defined('_END_DATE_PROBATION')) define('_END_DATE_PROBATION', 'Probation end date');
if (!defined('_START_DATE_TRIAL')) define('_START_DATE_TRIAL', 'Trial beginning date');
if (!defined('_START_DATE_MISSION')) define('_START_DATE_MISSION', 'Mission beginning date');
if (!defined('_END_DATE_TRIAL')) define('_END_DATE_TRIAL', 'Trial end date');
if (!defined('_END_DATE_MISSION')) define('_END_DATE_MISSION', 'Mission end date');
if (!defined('_EVENT_DATE')) define('_EVENT_DATE', 'Date of the event');
if (!defined('_VISIT_DATE')) define('_VISIT_DATE', 'Attendance date');
if (!defined('_CHANGE_DATE')) define('_CHANGE_DATE', 'Change date ');
if (!defined('_DOCTYPES_LIST2')) define('_DOCTYPES_LIST2', 'List of document types');

if (!defined('_INDEX_FOR_DOCTYPES')) define('_INDEX_FOR_DOCTYPES', 'Available descriptors for document types');
if (!defined('_FIELD')) define('_FIELD', 'Field');
if (!defined('_USED')) define('_USED', 'Used');
if (!defined('_MANDATORY')) define('_MANDATORY', 'Mandatory');
if (!defined('_ITERATIVE')) define('_ITERATIVE', 'Iterative');

if (!defined('_MASTER_TYPE')) define('_MASTER_TYPE', 'Master doc type');

/************** structures : Liste + Forms**************/
if (!defined('_STRUCTURE_LIST')) define('_STRUCTURE_LIST', 'Classification scheme list');
if (!defined('_STRUCTURES')) define('_STRUCTURES', 'classification schemes');
if (!defined('_STRUCTURE')) define('_STRUCTURE', 'classification scheme');
if (!defined('_ALL_STRUCTURES')) define('_ALL_STRUCTURES', 'All classification schemes');

if (!defined('_THE_STRUCTURE')) define('_THE_STRUCTURE', 'the classification scheme');
if (!defined('_STRUCTURE_MODIF')) define('_STRUCTURE_MODIF', 'Modify the classification scheme');
if (!defined('_ID_STRUCTURE_PB')) define('_ID_STRUCTURE_PB', 'A problem occurs with the id of the classification scheme');
if (!defined('_NEW_STRUCTURE_ADDED')) define('_NEW_STRUCTURE_ADDED', 'Add a new classification scheme');
if (!defined('_NEW_STRUCTURE')) define('_NEW_STRUCTURE', 'New classification scheme');
if (!defined('_DESC_STRUCTURE_MISSING')) define('_DESC_STRUCTURE_MISSING', 'The description of the classification scheme is missing');
if (!defined('_STRUCTURE_DEL')) define('_STRUCTURE_DEL', 'Delete of the classification scheme');
if (!defined('_DELETED_STRUCTURE')) define('_DELETED_STRUCTURE', 'Classification scheme deleted');

/************** sous-dossiers : Liste + Forms**************/
if (!defined('_SUBFOLDER_LIST')) define('_SUBFOLDER_LIST', 'Sub-file list');
if (!defined('_SUBFOLDERS')) define('_SUBFOLDERS', 'sub-file');
if (!defined('_ALL_SUBFOLDERS')) define('_ALL_SUBFOLDERS', 'All sub-file');
if (!defined('_SUBFOLDER')) define('_SUBFOLDER', 'Sub-file');

if (!defined('_ADD_SUBFOLDER')) define('_ADD_SUBFOLDER', 'Add a new sub-file');
if (!defined('_THE_SUBFOLDER')) define('_THE_SUBFOLDER', 'The sub-file');
if (!defined('_SUBFOLDER_MODIF')) define('_SUBFOLDER_MODIF', 'Modify a sub-file');
if (!defined('_SUBFOLDER_CREATION')) define('_SUBFOLDER_CREATION', 'Add sub-file');
if (!defined('_SUBFOLDER_ID_PB')) define('_SUBFOLDER_ID_PB', 'A problem occured with the id of the sub-file');
if (!defined('_SUBFOLDER_ADDED')) define('_SUBFOLDER_ADDED', 'Add a sub-file');
if (!defined('_NEW_SUBFOLDER')) define('_NEW_SUBFOLDER', 'New sub-file');
if (!defined('_STRUCTURE_MANDATORY')) define('_STRUCTURE_MANDATORY', 'A classification scheme is mandatory');
if (!defined('_SUBFOLDER_DESC_MISSING')) define('_SUBFOLDER_DESC_MISSING', 'The description of the sub-file is missing');

if (!defined('_ATTACH_STRUCTURE')) define('_ATTACH_STRUCTURE', 'Attach to a classification scheme');
if (!defined('_CHOOSE_STRUCTURE')) define('_CHOOSE_STRUCTURE', 'Choose a classification scheme');

if (!defined('_DEL_SUBFOLDER')) define('_DEL_SUBFOLDER', 'delete a sub-file');
if (!defined('_SUBFOLDER_DELETED')) define('_SUBFOLDER_DELETED', 'Sub-file deleted');


/************** Status **************/

if (!defined('_STATUS_LIST')) define('_STATUS_LIST', 'Status list');
if (!defined('_ADD_STATUS')) define('_ADD_STATUS', 'Add a new status');
if (!defined('_ALL_STATUS')) define('_ALL_STATUS', 'All status');
if (!defined('_STATUS_PLUR')) define('_STATUS_PLUR', 'status(es)');
if (!defined('_STATUS_SING')) define('_STATUS_SING', 'status');

if (!defined('_TO_PROCESS')) define('_TO_PROCESS','To process');
if (!defined('_IN_PROGRESS')) define('_IN_PROGRESS','In progress');
if (!defined('_FIRST_WARNING')) define('_FIRST_WARNING','1st reminder');
if (!defined('_SECOND_WARNING')) define('_SECOND_WARNING','2nd reminder');
if (!defined('_CLOSED')) define('_CLOSED','Closed');
if (!defined('_NEW')) define('_NEW','New');
if (!defined('_LATE')) define('_LATE', 'Late');

if (!defined('_STATUS_DELETED')) define('_STATUS_DELETED', 'Delete status');
if (!defined('_DEL_STATUS')) define('_DEL_STATUS', 'Status deleted');
if (!defined('_MODIFY_STATUS')) define('_MODIFY_STATUS', 'Modify status');
if (!defined('_STATUS_ADDED')) define('_STATUS_ADDED','Status added');
if (!defined('_STATUS_MODIFIED')) define('_STATUS_MODIFIED','Status modified');
if (!defined('_NEW_STATUS')) define('_NEW_STATUS', 'New status');
if (!defined('_IS_SYSTEM')) define('_IS_SYSTEM', 'System');
if (!defined('_CAN_BE_SEARCHED')) define('_CAN_BE_SEARCHED', 'Can documents be searched?');
if (!defined('_CAN_BE_MODIFIED')) define('_CAN_BE_MODIFIED', 'Can documents be modified?');
if (!defined('_THE_STATUS')) define('_THE_STATUS', 'The status ');
if (!defined('_ADMIN_STATUS')) define('_ADMIN_STATUS', 'Status');
/************* Actions **************/

if (!defined('_ACTION_LIST')) define('_ACTION_LIST', 'Actions list');
if (!defined('_ADD_ACTION')) define('_ADD_ACTION', 'Add a new action');
if (!defined('_ALL_ACTIONS')) define('_ALL_ACTIONS', 'All actions');
if (!defined('_ACTION_HISTORY')) define('_ACTION_HISTORY', 'Log the action');

if (!defined('_ACTION_DELETED')) define('_ACTION_DELETED', 'Delete the action');
if (!defined('_DEL_ACTION')) define('_DEL_ACTION', 'Action deleted');
if (!defined('_MODIFY_ACTION')) define('_MODIFY_ACTION', 'Modify the action');
if (!defined('_ACTION_ADDED')) define('_ACTION_ADDED','Action added');
if (!defined('_ACTION_MODIFIED')) define('_ACTION_MODIFIED','Action modified');
if (!defined('_NEW_ACTION')) define('_NEW_ACTION', 'New action');
if (!defined('_THE_ACTION')) define('_THE_ACTION', 'The action ');
if (!defined('_ADMIN_ACTIONS')) define('_ADMIN_ACTIONS', 'Actions');
if (!defined('_ADMIN_ACTIONS_DESC')) define('_ADMIN_ACTIONS_DESC', 'Manage available actions in the application');

/************** History **************/
if (!defined('_HISTORY_TITLE')) define('_HISTORY_TITLE', 'Events log');
if (!defined('_HISTORY_BATCH_TITLE')) define('_HISTORY_BATCH_TITLE', 'Batches event log');
if (!defined('_HISTORY')) define('_HISTORY', 'Log');
if (!defined('_HISTORY_BATCH')) define('_HISTORY_BATCH', 'Batches log');
if (!defined('_BATCH_NAME')) define('_BATCH_NAME', 'Batch name');
if (!defined('_CHOOSE_BATCH')) define('_CHOOSE_BATCH', 'Choose a batch');
if (!defined('_BATCH_ID')) define('_BATCH_ID', 'Batch id');
if (!defined('_TOTAL_PROCESSED')) define('_TOTAL_PROCESSED', 'Total processed');
if (!defined('_TOTAL_ERRORS')) define('_TOTAL_ERRORS', 'Total errors');
if (!defined('_ONLY_ERRORS')) define('_ONLY_ERRORS', 'Only with errors');
if (!defined('_INFOS')) define('_INFOS', 'Infos');

/************** Classification scheme **************/
if (!defined('_ADMIN_ARCHI')) define('_ADMIN_ARCHI', 'Administration of classification schemes');
if (!defined('_MANAGE_STRUCTURE')) define('_MANAGE_STRUCTURE', 'Manage files');
if (!defined('_MANAGE_STRUCTURE_DESC')) define('_MANAGE_STRUCTURE_DESC', 'Manage files. They are the highest element of the hierarchy. If the "Folder" module is enabled, you can attach a file type to a sorting tree.');
if (!defined('_MANAGE_SUBFOLDER')) define('_MANAGE_SUBFOLDER', 'Manage sub-files');
if (!defined('_MANAGE_SUBFOLDER_DESC')) define('_MANAGE_SUBFOLDER_DESC', 'Manage sub-files in files.');
if (!defined('_ARCHITECTURE')) define('_ARCHITECTURE', 'classification scheme');

/************************* Error Messages ***********************************/
if (!defined('_MORE_INFOS')) define('_MORE_INFOS', 'Contact your admin for more information ');
if (!defined('_ALREADY_EXISTS')) define('_ALREADY_EXISTS', 'already exists!');

// class usergroups
if (!defined('_NO_GROUP')) define('_NO_GROUP', 'The group does not exist !');
if (!defined('_NO_SECURITY_AND_NO_SERVICES')) define('_NO_SECURITY_AND_NO_SERVICES', 'has no defined security and no service');
if (!defined('_GROUP_ADDED')) define('_GROUP_ADDED', 'New group added');
if (!defined('_SYNTAX_ERROR_WHERE_CLAUSE')) define('_SYNTAX_ERROR_WHERE_CLAUSE', 'error in the WHERE clause syntax');
if (!defined('_GROUP_UPDATED')) define('_GROUP_UPDATED', 'Group modified');
if (!defined('_AUTORIZED_GROUP')) define('_AUTORIZED_GROUP', 'Group enabled');
if (!defined('_SUSPENDED_GROUP')) define('_SUSPENDED_GROUP', 'Group disabled');
if (!defined('_DELETED_GROUP')) define('_DELETED_GROUP', 'Group deleted');
if (!defined('_GROUP_UPDATE')) define('_GROUP_UPDATE', 'Modify a group');
if (!defined('_GROUP_AUTORIZATION')) define('_GROUP_AUTORIZATION', 'Enable a group');
if (!defined('_GROUP_SUSPENSION')) define('_GROUP_SUSPENSION', 'Disable a group');
if (!defined('_GROUP_DELETION')) define('_GROUP_DELETION', 'Delete a group');
if (!defined('_GROUP_DESC')) define('_GROUP_DESC', 'The description of a group ');
if (!defined('_GROUP_ID')) define('_GROUP_ID', 'The id of the group');
if (!defined('_EXPORT_RIGHT')) define('_EXPORT_RIGHT', 'Export right');

//class users
if (!defined('_USER_NO_GROUP')) define('_USER_NO_GROUP', 'you do not belong to any group');
if (!defined('_SUSPENDED_ACCOUNT')) define('_SUSPENDED_ACCOUNT', 'Your account has been disabled');
if (!defined('_BAD_LOGIN_OR_PSW')) define('_BAD_LOGIN_OR_PSW', 'Wrong username or password');
if (!defined('_WRONG_SECOND_PSW')) define('_WRONG_SECOND_PSW', 'the second password does not match the first one!');
if (!defined('_AUTORIZED_USER')) define('_AUTORIZED_USER', 'User enabled');
if (!defined('_SUSPENDED_USER')) define('_SUSPENDED_USER', 'User disabled');
if (!defined('_DELETED_USER')) define('_DELETED_USER', 'User deleted;');
if (!defined('_USER_DELETION')) define('_USER_DELETION', 'Delete the user');
if (!defined('_USER_AUTORIZATION')) define('_USER_AUTORIZATION', 'Enable the user');
if (!defined('_USER_SUSPENSION')) define('_USER_SUSPENSION', 'Disable the user');
if (!defined('_USER_UPDATED')) define('_USER_UPDATED', 'User modified');
if (!defined('_USER_UPDATE')) define('_USER_UPDATE', 'Modify an user');
if (!defined('_USER_ADDED')) define('_USER_ADDED', 'New user added');
if (!defined('_NO_PRIMARY_GROUP')) define('_NO_PRIMARY_GROUP', 'No primary group selected!');
if (!defined('_THE_USER')) define('_THE_USER', 'The user ');
if (!defined('_USER_ID')) define('_USER_ID', 'The id of the user');
if (!defined('_MY_INFO')) define('_MY_INFO', 'My account');


//class types
if (!defined('_UNKNOWN_PARAM')) define('_UNKNOWN_PARAM', 'Unknown parameters');
if (!defined('_DOCTYPE_UPDATED')) define('_DOCTYPE_UPDATED', 'Document type modified');
if (!defined('_DOCTYPE_UPDATE')) define('_DOCTYPE_UPDATE', 'Modify a document type');
if (!defined('_DOCTYPE_ADDED')) define('_DOCTYPE_ADDED', 'New document type added');
if (!defined('_DELETED_DOCTYPE')) define('_DELETED_DOCTYPE', 'Document type deleted');
if (!defined('_DOCTYPE_DELETION')) define('_DOCTYPE_DELETION', 'delete a document type');
if (!defined('_THE_DOCTYPE')) define('_THE_DOCTYPE', 'the document type ');
if (!defined('_THE_WORDING')) define('_THE_WORDING', 'the label ');
if (!defined('_THE_TABLE')) define('_THE_TABLE', 'The table ');
if (!defined('_PIECE_TYPE')) define('_PIECE_TYPE', 'type of file');

//class db
if (!defined('_CONNEXION_ERROR')) define('_CONNEXION_ERROR', 'An error occurs while connecting');
if (!defined('_SELECTION_BASE_ERROR')) define('_SELECTION_BASE_ERROR', 'An error occurs while selecting the table');
if (!defined('_QUERY_ERROR')) define('_QUERY_ERROR', 'An error occurs while executing the query');
if (!defined('_CLOSE_CONNEXION_ERROR')) define('_CLOSE_CONNEXION_ERROR', 'An error occurs while while closing the connection');
if (!defined('_ERROR_NUM')) define('_ERROR_NUM', 'Error num.');
if (!defined('_HAS_JUST_OCCURED')) define('_HAS_JUST_OCCURED', 'just occured');
if (!defined('_MESSAGE')) define('_MESSAGE', 'Message');
if (!defined('_QUERY')) define('_QUERY', 'Query');
if (!defined('_LAST_QUERY')) define('_LAST_QUERY', 'Latest query');

//Other
if (!defined('_NO_GROUP_SELECTED')) define('_NO_GROUP_SELECTED', 'No group selected');
if (!defined('_NOW_LOG_OUT')) define('_NOW_LOG_OUT', 'You are logged out');
if (!defined('_DOC_NOT_FOUND')) define('_DOC_NOT_FOUND', 'The document cannot be found');
if (!defined('_DOUBLED_DOC')) define('_DOUBLED_DOC', 'Duplicate problem');
if (!defined('_NO_DOC_OR_NO_RIGHTS')) define('_NO_DOC_OR_NO_RIGHTS', 'This document does not exist, or you do not have sufficient right to view it.');
if (!defined('_INEXPLICABLE_ERROR')) define('_INEXPLICABLE_ERROR', 'An unattended error occurs');
if (!defined('_TRY_AGAIN_SOON')) define('_TRY_AGAIN_SOON', 'Please try again in a few seconds');
if (!defined('_NO_OTHER_RECIPIENT')) define('_NO_OTHER_RECIPIENT', 'There is no other recipient for this document');
if (!defined('_WAITING_INTEGER')) define('_WAITING_INTEGER', 'Integer expected');
if (!defined('_WAITING_FLOAT')) define( '_WAITING_FLOAT', 'Floating number awaited');

if (!defined('_DEFINE')) define('_DEFINE', 'Complementary information :');
if (!defined('_NUM')) define('_NUM', '#');
if (!defined('_ROAD')) define('_ROAD', 'Street');
if (!defined('_POSTAL_CODE')) define('_POSTAL_CODE','Zip code');
if (!defined('_CITY')) define('_CITY', 'City');

if (!defined('_CHOOSE_USER')) define('_CHOOSE_USER', 'Select an user');
if (!defined('_CHOOSE_USER2')) define('_CHOOSE_USER2', 'Select an user');
if (!defined('_NUM2')) define('_NUM2', 'nb');
if (!defined('_UNDEFINED')) define('_UNDEFINED', 'N/A');
if (!defined('_CONSULT_EXTRACTION')) define('_CONSULT_EXTRACTION', 'You can consult the documents here');
if (!defined('_SERVICE')) define('_SERVICE', 'Service');
if (!defined('_AVAILABLE_SERVICES')) define('_AVAILABLE_SERVICES', 'Available services');

// Months
if (!defined('_JANUARY')) define('_JANUARY', 'January');
if (!defined('_FEBRUARY')) define('_FEBRUARY', 'February');
if (!defined('_MARCH')) define('_MARCH', 'March');
if (!defined('_APRIL')) define('_APRIL', 'April');
if (!defined('_MAY')) define('_MAY', 'May');
if (!defined('_JUNE')) define('_JUNE', 'June');
if (!defined('_JULY')) define('_JULY', 'July');
if (!defined('_AUGUST')) define('_AUGUST', 'August');
if (!defined('_SEPTEMBER')) define('_SEPTEMBER', 'September');
if (!defined('_OCTOBER')) define('_OCTOBER', 'October');
if (!defined('_NOVEMBER')) define('_NOVEMBER', 'November');
if (!defined('_DECEMBER')) define('_DECEMBER', 'December');

if (!defined('_NOW_LOGOUT')) define('_NOW_LOGOUT', 'You are logged out');


if (!defined('_WELCOME2')) define('_WELCOME2', 'Welcome');
if (!defined('_WELCOME_NOTES1')) define('_WELCOME_NOTES1', 'To access the different parts of the application');
if (!defined('_WELCOME_NOTES2')) define('_WELCOME_NOTES2', 'use the <b>menu</b> above');
if (!defined('_WELCOME_NOTES3')) define('_WELCOME_NOTES3', 'Maarch Team is very proud to present this new framework, which represents an important milestone in the development of the solution.<br><br>In this sample application, you can:<ul><li>o create archive boxes to store the original paper documents you scanned<b>(<i>Physical Archive</i> module)</b></li><li>o Print barcode separator <b>(<i>Physical Archive</i> module)</b></li><li>o Index new documents in two separate collections (production documents and customer invoices) <b>(<i>Indexing & Searching</i> module)</b></li><li>o Mass import customer invoices <b>(<i>Maarch AutoImport</i> add on)</b></li><li>o consult the two document collections <b>(<i> Indexing & Searching</i> module)</b></li><li>o Browse the invoice collection through dynamic trees<b>(<i> AutoFoldering</i> module)</b></li></ul>');
if (!defined('_WELCOME_NOTES5')) define('_WELCOME_NOTES5', 'Refer to <u><a href="http://www.maarch.org/maarch_wiki/Maarch_Framework_3">maarch wiki</a></u> for more information.');
if (!defined('_WELCOME_NOTES6')) define('_WELCOME_NOTES6', 'You can also visit our <u><a href="http://www.maarch.org/">community website</a></u> or Maarch <u><a href="http://www.maarch.org/maarch_forum/">forum</a></u>.');
if (!defined('_WELCOME_NOTES7')) define('_WELCOME_NOTES7', 'If you need professional support or spefific integration, check <u><a href="http://www.maarch.fr/">our services offer</a></u>.');
if (!defined('_WELCOME_COUNT')) define('_WELCOME_COUNT', 'Number of resources in the collection');
if (!defined('_CONTRACT_HISTORY')) define('_CONTRACT_HISTORY', 'Contracts history');

if (!defined('_CLICK_CALENDAR')) define('_CLICK_CALENDAR', 'Clic to choose a date');
if (!defined('_MODULES')) define('_MODULES', 'Modules');
if (!defined('_CHOOSE_MODULE')) define('_CHOOSE_MODULE', 'Select a module');
if (!defined('_FOLDER')) define('_FOLDER', 'File');
if (!defined('_INDEX')) define('_INDEX', 'Index');

//COLLECTIONS
if (!defined('_MAILS')) define('_MAILS', 'Mail');
if (!defined('_DOCUMENTS')) define('_DOCUMENTS', 'Real estate loans');
if (!defined('_INVOICES')) define('_INVOICES', 'Customer invoice');
if (!defined('_CHOOSE_COLLECTION')) define('_CHOOSE_COLLECTION', 'Select a collection');

if (!defined('_EVENT')) define('_EVENT', 'Event');
if (!defined('_LINK')) define('_LINK', 'Link');


//BITMASK
if (!defined('_BITMASK_VALUE_ALREADY_EXIST')) define('_BITMASK_VALUE_ALREADY_EXIST' , 'Bitmask already used');

if (!defined('_ASSISTANT_MODE')) define('_ASSISTANT_MODE', 'Assistant mode');
if (!defined('_EDIT_WITH_ASSISTANT')) define('_EDIT_WITH_ASSISTANT', 'Click here to edit the WHERE clause in assistant mode');
if (!defined('_VALID_THE_WHERE_CLAUSE')) define('_VALID_THE_WHERE_CLAUSE', 'Click here to validate the WHERE clause');
if (!defined('_DELETE_SHORT')) define('_DELETE_SHORT', 'Delete');
if (!defined('_CHOOSE_ANOTHER_SUBFOLDER')) define('_CHOOSE_ANOTHER_SUBFOLDER', 'Select another sub-file');
if (!defined('_DOCUMENTS_EXISTS_FOR_COLLECTION')) define('_DOCUMENTS_EXISTS_FOR_COLLECTION', 'existing documents in the collection');
if (!defined('_MUST_CHOOSE_COLLECTION_FIRST')) define('_MUST_CHOOSE_COLLECTION_FIRST', 'You must select a collection');
if (!defined('_CANTCHANGECOLL')) define('_CANTCHANGECOLL', 'You cannot change the collection');
if (!defined('_DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION')) define('_DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION', 'existing documents for the duet doctype/collection');

if (!defined('_NO_RIGHT')) define('_NO_RIGHT', 'Error');
if (!defined('_NO_RIGHT_TXT')) define('_NO_RIGHT_TXT', 'The document you are trying to access does not exist or you do not have sufficient rights to access it...');
if (!defined('_NUM_GED')) define('_NUM_GED', 'DMS #');

///// Manage action error
if (!defined('_AJAX_PARAM_ERROR')) define('_AJAX_PARAM_ERROR', 'Wrong parameters given to the AJAX request.');
if (!defined('_ACTION_CONFIRM')) define('_ACTION_CONFIRM', 'Do you really want to make following action: ');
if (!defined('_ACTION_NOT_IN_DB')) define('_ACTION_NOT_IN_DB', 'This action does not exist!');
if (!defined('_ERROR_PARAM_ACTION')) define('_ERROR_PARAM_ACTION', 'Wrong parameters for the action');
if (!defined('_SQL_ERROR')) define('_SQL_ERROR', 'SQL Error');
if (!defined('_ACTION_DONE')) define('_ACTION_DONE', 'Action done');
if (!defined('_ACTION_PAGE_MISSING')) define('_ACTION_PAGE_MISSING', 'The result page for this action cannot be found !');
if (!defined('_ERROR_SCRIPT')) define('_ERROR_SCRIPT', 'Action result page : an error occured in the script or a function is missing');
if (!defined('_SERVER_ERROR')) define('_SERVER_ERROR', 'Server error');
if (!defined('_CHOOSE_ONE_DOC')) define('_CHOOSE_ONE_DOC', 'Select at least one document');
if (!defined('_CHOOSE_ONE_OBJECT')) define( '_CHOOSE_ONE_OBJECT', 'Choisissez au moins un &eacute;l&eacute;ment');

if (!defined('_CLICK_LINE_TO_CHECK_INVOICE')) define('_CLICK_LINE_TO_CHECK_INVOICE', 'Claick on a line to check an invoice.');
if (!defined('_FOUND_INVOICES')) define('_FOUND_INVOICES', ' Invoice(s) found');
if (!defined('_SIMPLE_CONFIRM')) define('_SIMPLE_CONFIRM', 'Simple confirmation');
if (!defined('_CHECK_INVOICE')) define('_CHECK_INVOICE', 'Verify invoice');

if (!defined('_REDIRECT_TO')) define('_REDIRECT_TO', 'Redirect to');
if (!defined('_NO_STRUCTURE_ATTACHED')) define('_NO_STRUCTURE_ATTACHED', 'This type of documents is not attached to any structure');


///// Credits
if (!defined('_MAARCH_CREDITS')) define('_MAARCH_CREDITS', 'About Maarch&nbsp;');
if (!defined('_CR_LONGTEXT_INFOS')) define('_CR_LONGTEXT_INFOS', '<p>Maarch Enterprise is a <b>DMS Platform</b>. It addresses most of the needs an organisation cas express to the operative management of its content. A vast majority of it components are released under the terms of the open source license GNU GPLv3. As a result, the total cost of ownership makes it affordable for any kind of organisation to use it (public sector, private companies associations, etc.).</p><p>Maarch Framework has been designed by two consultants whose experience in in records management and ADF sums up to 20 years. Thus this product <b>guarantees a level of stability, integrity and performance</b> one can expect for that type of product. The architecture of the software has been particularly designed so that it can run on standard servers.</p><p>Maarch is developed in PHP5 object. It is compatible with 4 database engines: MySQL, PostgreSQL, SQL Server and soon Oracle.</p><p>Maarch is <b>fully modular</b>: all functionalities are grouped in modules. The modules expose services, which can be enabled or disabled according to the user functional profile. A trained engineer can add or replace an existing module without modifying thr core of the program.</p><p>Maarch offers a global model and necessary tools to <b>acquire, manage, archive and retrieve production document streams</b>.<p>');

if (!defined('_CR_LONGTEXT_INFOS')) define( '_CR_LONGTEXT_INFOS', '<p>Maarch Framework 3 est une infrastructure de <b>GED de Production</b>, r&eacute;pondant en standard &agrave; la plupart des besoins de gestion op&eacute;rationnelle de contenu d\'une organisation. La tr&egrave;s grande majorit&eacute; des composants du Framework est diffus� sous les termes de la licence open source GNU GPLv3, de sorte que le co�t d\'impl&eacute;mentation rend la solution aborbable pour tout type d\'organisation (public, priv&eacute;, parapublic, monde associatif).</p> <p>Pour autant, Maarch Framework ayant &eacute;t&eacute; con�u par deux consultants cumulant &agrave; eux deux plus de 20 ans d\'expertise en Syst&egrave;mes d\'Archivage &Eacute;lectronique et en &Eacute;ditique, le produit offre <b>toutes les garanties de robustesse, d\'int&eacute;grit&eacute;, de performance</b> que l\'on doit attendre de ce type de produit. Un grand soin a &eacute;t&eacute; port&eacute; sur l\'architecture afin d\'autoriser des performances maximales sur du mat&eacute;riel standard.</p><p>Maarch est d&eacute;velopp&eacute; en PHP5 objet. Il est compatible avec les 4 moteurs de bases de donn&eacute;es suivants&nbsp;: MySQL, PostgreSQL, SQLServer, et bient�t Oracle.</p> <p>Maarch est <b>totalement modulaire</b>&nbsp;: toutes les fonctionnalit&eacute;s sont regroup&eacute;es dans des modules exposant des services qui peuvent �tre activ&eacute;s/d&eacute;sactiv&eacute;s en fonction du profil de l\'utilisateur. Un ing&eacute;nieur exp&eacute;riment&eacute; peut ajouter ou remplacer un module existant sans toucher au coeur du syst&egrave;me.</p><p>Maarch propose un sch&eacute;ma global et <b>tous les outils pour acqu&eacute;rir, g&eacute;rer, conserver puis restituer les flux documentaires de production</b>.');

if (!defined('_PROCESSING_DATE')) define('_PROCESSING_DATE', 'processing deadline');
if (!defined('_PROCESS_NUM')) define('_PROCESS_NUM','Processing mail nb.');
if (!defined('_PROCESS_LIMIT_DATE')) define('_PROCESS_LIMIT_DATE', 'Processing deadline');
if (!defined('_LATE_PROCESS')) define('_LATE_PROCESS', 'Late');
if (!defined('_PROCESS_DELAY')) define('_PROCESS_DELAY', 'Processing period');
if (!defined('_ALARM1_DELAY')) define('_ALARM1_DELAY', 'Period before 1st reminder');
if (!defined('_ALARM2_DELAY')) define('_ALARM2_DELAY', 'Period before 2nd reminder');
if (!defined('_CATEGORY')) define('_CATEGORY', 'Category');
if (!defined('_CHOOSE_CATEGORY')) define('_CHOOSE_CATEGORY', 'Choose a category');
if (!defined('_RECEIVING_DATE')) define('_RECEIVING_DATE', 'Reception date');
if (!defined('_SUBJECT')) define('_SUBJECT', 'Object');
if (!defined('_AUTHOR')) define('_AUTHOR', 'Author');
if (!defined('_DOCTYPE_MAIL')) define('_DOCTYPE_MAIL', 'Mail type');
if (!defined('_PROCESS_LIMIT_DATE_USE')) define('_PROCESS_LIMIT_DATE_USE', 'Enable processing deadline');
if (!defined('_DEPARTMENT_DEST')) define('_DEPARTMENT_DEST', 'Recipient department');
if (!defined('_DEPARTMENT_EXP')) define('_DEPARTMENT_EXP', 'Sender department');


// Mail Categories
if (!defined('_INCOMING')) define('_INCOMING', 'Incoming mail');
if (!defined('_OUTGOING')) define('_OUTGOING', 'Outgoing mail');
if (!defined('_INTERNAL')) define('_INTERNAL', 'Internal mail');
if (!defined('_MARKET_DOCUMENT')) define('_MARKET_DOCUMENT', 'Document to file');

// Mail Natures
if (!defined('_SIMPLE_MAIL')) define('_SIMPLE_MAIL', 'Simple mail');
if (!defined('_EMAIL')) define('_EMAIL', 'Email');
if (!defined('_FAX')) define('_FAX', 'Fax');
if (!defined('_CHRONOPOST')) define('_CHRONOPOST', 'UPS');
if (!defined('_FEDEX')) define('_FEDEX', 'Fedex');
if (!defined('_REGISTERED_MAIL')) define('_REGISTERED_MAIL', 'Recorded delivery');
if (!defined('_COURIER')) define('_COURIER', 'Courier');
if (!defined('_OTHER')) define('_OTHER', 'Other');

//Priorities
if (!defined('_NORMAL')) define('_NORMAL', 'Normal');
if (!defined('_VERY_HIGH')) define('_VERY_HIGH', 'Very high');
if (!defined('_HIGH')) define('_HIGH', 'High');
if (!defined('_LOW')) define('_LOW', 'Low');
if (!defined('_VERY_LOW')) define('_VERY_LOW', 'Very low');


if (!defined('_INDEXING_MLB')) define('_INDEXING_MLB', 'Record a document');
if (!defined('_ADV_SEARCH_MLB')) define('_ADV_SEARCH_MLB', 'Search a document');

if (!defined('_ADV_SEARCH_TITLE')) define('_ADV_SEARCH_TITLE', 'Document advanced search');
if (!defined('_MAIL_OBJECT')) define('_MAIL_OBJECT', 'Mail object');
//if (!defined('_SHIPPER')) define('_SHIPPER', 'Emetteur');
//if (!defined('_SENDER')) define('_SENDER', 'Exp&eacute;diteur');
//if (!defined('_SOCIETY')) define('_SOCIETY', 'Soci&eacute;t&eacute;');
//if (!defined('_SHIPPER_SEARCH')) define('_SHIPPER_SEARCH','Dans le champ &eacute;metteur, les recherches ne sont effectu&eacute;es ni sur les civilit&eacute;s, ni sur les pr&eacute;noms.');
//if (!defined('_MAIL_IDENTIFIER')) define('_MAIL_IDENTIFIER','R&eacute;f&eacute;rence de l&rsquo;affaire');
if (!defined('_N_GED')) define('_N_GED','DMS nb. ');
if (!defined('_GED_NUM')) define('_GED_NUM', 'DMS nb. ');
if (!defined('_CHOOSE_TYPE_MAIL')) define('_CHOOSE_TYPE_MAIL','Choose a type of document');
//if (!defined('_INVOICE_TYPE')) define('_INVOICE_TYPE','Nature de l&rsquo;envoi');
//if (!defined('_CHOOSE_INVOICE_TYPE')) define('_CHOOSE_INVOICE_TYPE','Choisissez la nature de l&rsquo;envoi');
if (!defined('_REG_DATE')) define('_REG_DATE','Record date');
if (!defined('_PROCESS_DATE')) define('_PROCESS_DATE','Processing deadline');
if (!defined('_CHOOSE_STATUS')) define('_CHOOSE_STATUS','Choose a status');
if (!defined('_PROCESS_RECEIPT')) define('_PROCESS_RECEIPT','Main recipient');
if (!defined('_CHOOSE_RECEIPT')) define('_CHOOSE_RECEIPT','Choose a recipient');
if (!defined('_TO_CC')) define('_TO_CC','In copy');
if (!defined('_ADD_COPIES')) define('_ADD_COPIES','Add users in copy');
//if (!defined('_ANSWER_TYPE')) define('_ANSWER_TYPE','Type(s) de r&eacute;ponse');
if (!defined('_PROCESS_NOTES')) define('_PROCESS_NOTES','Processing notes');
if (!defined('_DIRECT_CONTACT')) define('_DIRECT_CONTACT','Direct contact');
if (!defined('_NO_ANSWER')) define('_NO_ANSWER','No answer');
if (!defined('_DETAILS')) define('_DETAILS', 'Details sheet');
if (!defined('_DOWNLOAD')) define('_DOWNLOAD', 'Download document');
if (!defined('_SEARCH_RESULTS')) define('_SEARCH_RESULTS', 'Search results');
if (!defined('_DOCUMENTS')) define('_DOCUMENTS', 'documents');
if (!defined('_THE_SEARCH')) define('_THE_SEARCH', 'The search');
if (!defined('_CHOOSE_TABLE')) define('_CHOOSE_TABLE', 'Choose a collection');
if (!defined('_SEARCH_COPY_MAIL')) define('_SEARCH_COPY_MAIL','Search in copy mail');
if (!defined('_MAIL_PRIORITY')) define('_MAIL_PRIORITY', 'Mail priority');
if (!defined('_CHOOSE_PRIORITY')) define('_CHOOSE_PRIORITY', 'Choose a priority');
if (!defined('_ADD_PARAMETERS')) define('_ADD_PARAMETERS', 'Add criteria');
if (!defined('_CHOOSE_PARAMETERS')) define('_CHOOSE_PARAMETERS', 'Choose criteria');
if (!defined('_CHOOSE_ENTITES_SEARCH_TITLE')) define('_CHOOSE_ENTITES_SEARCH_TITLE', 'Add services to refine your search');
if (!defined('_CHOOSE_DOCTYPES_SEARCH_TITLE')) define('_CHOOSE_DOCTYPES_SEARCH_TITLE', 'Add document types to refine your search');
if (!defined('_DESTINATION_SEARCH')) define('_DESTINATION_SEARCH', 'Department)');
if (!defined('_ADD_PARAMETERS_HELP')) define('_ADD_PARAMETERS_HELP', 'Add criteria to refine your search');
if (!defined('_MAIL_OBJECT_HELP')) define('_MAIL_OBJECT_HELP', 'Add object keywords');
if (!defined('_N_GED_HELP')) define('_N_GED_HELP', '');
if (!defined('_CHOOSE_RECIPIENT_SEARCH_TITLE')) define('_CHOOSE_RECIPIENT_SEARCH_TITLE', 'Add recipients to refine the search');
if (!defined('_MULTI_FIELD')) define('_MULTI_FIELD','Multi-field');
if (!defined('_MULTI_FIELD_HELP')) define('_MULTI_FIELD_HELP','Object, description, title, chrono number, processing notes...');
if (!defined('_SAVE_QUERY')) define('_SAVE_QUERY', 'Save your search');
if (!defined('_SAVE_QUERY_TITLE')) define('_SAVE_QUERY_TITLE', 'Save your search criteria');
if (!defined('_QUERY_NAME')) define('_QUERY_NAME', 'Name');
if (!defined('_QUERY_SAVED')) define('_QUERY_SAVED', 'Search criteria saved');

//if (!defined('_SQL_ERROR')) define('_SQL_ERROR', 'Erreur SQL lors de l&acute;enregistrement de la recherche');
if (!defined('_LOAD_QUERY')) define('_LOAD_QUERY', 'Load custom search');
if (!defined('_DELETE_QUERY')) define('_DELETE_QUERY', 'Delete custom search');
if (!defined('_CHOOSE_SEARCH')) define('_CHOOSE_SEARCH', 'Choose a custom search');
if (!defined('_THIS_SEARCH')) define('_THIS_SEARCH', 'This search');
if (!defined('_MY_SEARCHES')) define('_MY_SEARCHES', 'My searches');
if (!defined('_CLEAR_SEARCH')) define('_CLEAR_SEARCH', 'Reset criteria');
if (!defined('_CHOOSE_STATUS_SEARCH_TITLE')) define('_CHOOSE_STATUS_SEARCH_TITLE', 'Add status to refine the search');
if (!defined('_ERROR_IE_SEARCH')) define('_ERROR_IE_SEARCH', 'This criterion is already selected');
//if (!defined('_CIVILITIES')) define('_CIVILITIES', 'Civilit&eacute;(s)');
//if (!defined('_CIVILITY')) define('_CIVILITY', 'Civilit&eacute;');
//if (!defined('_CHOOSE_CIVILITY_SEARCH_TITLE')) define('_CHOOSE_CIVILITY_SEARCH_TITLE', 'Ajoutez le/les civilit&eacute;(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');

if (!defined('_DEST_USER')) define('_DEST_USER','Recipient');
if (!defined('_DOCTYPES')) define('_DOCTYPES','Document type');
if (!defined('_MAIL_NATURE')) define('_MAIL_NATURE', 'Mail nature');
if (!defined('_CHOOSE_MAIL_NATURE')) define('_CHOOSE_MAIL_NATURE', 'Choose mail nature');
if (!defined('_ERROR_DOCTYPE')) define('_ERROR_DOCTYPE', 'Document type is not valid');
if (!defined('_ADMISSION_DATE')) define('_ADMISSION_DATE', 'Reception date');
if (!defined('_FOUND_DOC')) define('_FOUND_DOC', 'document(s) found');
if (!defined('_PROCESS')) define('_PROCESS', 'Processing ');
if (!defined('_DOC_NUM')) define('_DOC_NUM', 'document nb. ');
if (!defined('_GENERAL_INFO')) define('_GENERAL_INFO', 'General information');
if (!defined('_ON_DOC_NUM')) define('_ON_DOC_NUM', ' on document nb.');
if (!defined('_PRIORITY')) define('_PRIORITY', 'Priority');
if (!defined('_MAIL_DATE')) define('_MAIL_DATE', 'Mail date');
if (!defined('_DOC_HISTORY')) define('_DOC_HISTORY', 'Logs');
if (!defined('_DONE_ANSWERS')) define('_DONE_ANSWERS','Answers to the mail');
if (!defined('_MUST_DEFINE_ANSWER_TYPE')) define('_MUST_DEFINE_ANSWER_TYPE', 'You must set the type of the answer');
if (!defined('_MUST_CHECK_ONE_BOX')) define('_MUST_CHECK_ONE_BOX', 'You must check at least one box');
if (!defined('_ANSWER_TYPE')) define('_ANSWER_TYPE','Answer types');

if (!defined('_INDEXATION_TITLE')) define('_INDEXATION_TITLE', 'Record a document');
if (!defined('_CHOOSE_FILE')) define('_CHOOSE_FILE', 'Select a file');
if (!defined('_CHOOSE_TYPE')) define('_CHOOSE_TYPE', 'Select a document type');

if (!defined('_FILE_LOADED_BUT_NOT_VISIBLE')) define('_FILE_LOADED_BUT_NOT_VISIBLE', 'The document has been saved on the server.<br/>');
if (!defined('_ONLY_FILETYPES_AUTHORISED')) define('_ONLY_FILETYPES_AUTHORISED', 'Only the following document types can be displayed in your browser');
if (!defined('_PROBLEM_LOADING_FILE_TMP_DIR')) define('_PROBLEM_LOADING_FILE_TMP_DIR', 'An error occured while trying to copy the file on the server');
if (!defined('_DOWNLOADED_FILE')) define('_DOWNLOADED_FILE', 'File saved');
if (!defined('_WRONG_FILE_TYPE')) define('_WRONG_FILE_TYPE', 'This document type is not allowed');

if (!defined('_LETTERBOX')) define('_LETTERBOX', 'Main collection');
if (!defined('_APA_COLL')) define('_APA_COLL', 'Physical archives - Do not use');
if (!defined('_REDIRECT_TO_ACTION')) define('_REDIRECT_TO_ACTION', 'Redirect to an action');
if (!defined('_DOCUMENTS_LIST')) define('_DOCUMENTS_LIST', 'List');


/********* Contacts ************/
if (!defined('_ADMIN_CONTACTS')) define('_ADMIN_CONTACTS', 'Contacts');
if (!defined('_ADMIN_CONTACTS_DESC')) define('_ADMIN_CONTACTS_DESC', 'Contacts administration');
if (!defined('_CONTACTS_LIST')) define('_CONTACTS_LIST', 'Contacts list');
if (!defined('_CONTACT_ADDITION')) define('_CONTACT_ADDITION', 'Add a contact');
if (!defined('_CONTACTS')) define('_CONTACTS', 'contact(s)');
if (!defined('_CONTACT')) define('_CONTACT', 'Contact');
if (!defined('_ALL_CONTACTS')) define('_ALL_CONTACTS', 'All contacts');
if (!defined('_ADD_CONTACT')) define('_ADD_CONTACT', 'Add a contact');
if (!defined('_PHONE')) define('_PHONE', 'Telephone');
if (!defined('_ADDRESS')) define('_ADDRESS', 'Address');
if (!defined('_STREET')) define('_STREET', 'Street');
if (!defined('_COMPLEMENT')) define('_COMPLEMENT', 'Address complement');
if (!defined('_TOWN')) define('_TOWN', 'Town');
if (!defined('_COUNTRY')) define('_COUNTRY', 'Country');
if (!defined('_SOCIETY')) define('_SOCIETY', 'Organisation');
if (!defined('_COMP')) define('_COMP', 'Other');
if (!defined('_COMP_DATA')) define('_COMP_DATA', 'Additional information');
if (!defined('_CONTACT_ADDED')) define('_CONTACT_ADDED', 'Contact added');
if (!defined('_CONTACT_MODIFIED')) define('_CONTACT_MODIFIED', 'Contact mofifyed');
if (!defined('_CONTACT_DELETED')) define('_CONTACT_DELETED', 'Contact deleted');
if (!defined('_MODIFY_CONTACT')) define('_MODIFY_CONTACT', 'Modify a contact');
if (!defined('_IS_CORPORATE_PERSON')) define('_IS_CORPORATE_PERSON', 'Institution');
if (!defined('_TITLE2')) define('_TITLE2', 'Title');

if (!defined('_YOU_MUST_SELECT_CONTACT')) define('_YOU_MUST_SELECT_CONTACT', 'You must select a contact ');
if (!defined('_CONTACT_INFO')) define('_CONTACT_INFO', 'Contact sheet');

if (!defined('_SHIPPER')) define('_SHIPPER', 'Sender');
if (!defined('_DEST')) define('_DEST', 'Recipient');
if (!defined('_INTERNAL2')) define('_INTERNAL2', 'Internal');
if (!defined('_EXTERNAL')) define('_EXTERNAL', 'External');
if (!defined('_CHOOSE_SHIPPER')) define('_CHOOSE_SHIPPER', 'Select a sender');
if (!defined('_CHOOSE_DEST')) define('_CHOOSE_DEST', 'Select a recipient');
if (!defined('_DOC_DATE')) define('_DOC_DATE', 'Document date');
if (!defined('_CONTACT_CARD')) define('_CONTACT_CARD', 'Fiche sheet');
if (!defined('_CREATE_CONTACT')) define('_CREATE_CONTACT', 'Add a contact');
if (!defined('_USE_AUTOCOMPLETION')) define('_USE_AUTOCOMPLETION', 'Use autocompletion');

if (!defined('_USER_DATA')) define('_USER_DATA', 'User sheet');
if (!defined('_SHIPPER_TYPE')) define('_SHIPPER_TYPE', 'Sender type');
if (!defined('_DEST_TYPE')) define('_DEST_TYPE', 'Recipient type');
if (!defined('_VALIDATE_MAIL')) define('_VALIDATE_MAIL', 'Validate document');
if (!defined('_LETTER_INFO')) define('_LETTER_INFO','Information on document');
if (!defined('_DATE_START')) define('_DATE_START','Arrival date');
if (!defined('_LIMIT_DATE_PROCESS')) define('_LIMIT_DATE_PROCESS','Processing deadline');


//// INDEXING SEARCHING
if (!defined('_NO_RESULTS')) define('_NO_RESULTS', 'No result found');
if (!defined('_CREATION_DATE')) define( '_CREATION_DATE', 'Creation date');
if (!defined('_NO_RESULTS')) define( '_NO_RESULTS', 'Aucun r&eacute;sultat');
if (!defined('_FOUND_DOCS')) define('_FOUND_DOCS', 'document(s) found');
if (!defined('_MY_CONTACTS')) define('_MY_CONTACTS', 'My contacts');
if (!defined('_DETAILLED_PROPERTIES')) define('_DETAILLED_PROPERTIES', 'Details');
if (!defined('_VIEW_DOC_NUM')) define('_VIEW_DOC_NUM', 'View document nb.');
if (!defined('_VIEW_DETAILS_NUM')) define('_VIEW_DETAILS_NUM', 'View details page of document nb.');
if (!defined('_TO')) define('_TO', 'to');
if (!defined('_FILE_PROPERTIES')) define('_FILE_PROPERTIES', 'file properties');
if (!defined('_FILE_DATA')) define('_FILE_DATA', 'Information about document');
if (!defined('_VIEW_DOC')) define('_VIEW_DOC', 'View the document');
if (!defined('_TYPIST')) define('_TYPIST', 'Operator');
if (!defined('_LOT')) define('_LOT', 'Batch');
if (!defined('_ARBOX')) define('_ARBOX', 'Box');
if (!defined('_ARBOXES')) define('_ARBOXES', 'Boxes');
if (!defined('_ARBATCHES')) define('_ARBATCHES', 'Batch');
if (!defined('_CHOOSE_BOXES_SEARCH_TITLE')) define('_CHOOSE_BOXES_SEARCH_TITLE', 'Add an archive box to refine your search');
if (!defined('_PAGECOUNT')) define('_PAGECOUNT', 'Nb of pages');
if (!defined('_ISPAPER')) define('_ISPAPER', 'Paper');
if (!defined('_SCANDATE')) define('_SCANDATE', 'Scan date');
if (!defined('_SCANUSER')) define('_SCANUSER', 'Scanner user');
if (!defined('_SCANLOCATION')) define('_SCANLOCATION', 'Scan place');
if (!defined('_SCANWKSATION')) define('_SCANWKSATION', 'Scan unit');
if (!defined('_SCANBATCH')) define('_SCANBATCH', 'Scan batch');
if (!defined('_SOURCE')) define('_SOURCE', 'Origin');
if (!defined('_DOCLANGUAGE')) define('_DOCLANGUAGE', 'Document language');
if (!defined('_MAILDATE')) define('_MAILDATE', 'Document date');
if (!defined('_MD5')) define('_MD5', 'fingerprint');
if (!defined('_WORK_BATCH')) define('_WORK_BATCH', 'Load batch');
if (!defined('_DONE')) define('_DONE','Description');
if (!defined('_ANSWER_TYPES_DONE')) define('_ANSWER_TYPES_DONE', 'Type of answer(s)');
if (!defined('_CLOSING_DATE')) define('_CLOSING_DATE', 'Closing date');
if (!defined('_FULLTEXT')) define('_FULLTEXT', 'Full text search');
if (!defined('_FULLTEXT_HELP')) define('_FULLTEXT_HELP', '');
if (!defined('_FILE_NOT_SEND')) define('_FILE_NOT_SEND', 'The document has not been sent');
if (!defined('_TRY_AGAIN')) define('_TRY_AGAIN', 'Please, try again');
if (!defined('_DOCTYPE_MANDATORY')) define('_DOCTYPE_MANDATORY', 'Document type is mandatory');
if (!defined('_INDEX_UPDATED')) define('_INDEX_UPDATED', 'Indices updated');
if (!defined('_DOC_DELETED')) define( '_DOC_DELETED', 'Document deleted');

if (!defined('_QUICKLAUNCH')) define('_QUICKLAUNCH', 'Shortcut');
if (!defined('_SHOW_DETAILS_DOC')) define('_SHOW_DETAILS_DOC', 'View document details');
if (!defined('_VIEW_DOC_FULL')) define('_VIEW_DOC_FULL', 'View this document');
if (!defined('_DETAILS_DOC_FULL')) define('_DETAILS_DOC_FULL', 'View document details');
if (!defined('_IDENTIFIER')) define('_IDENTIFIER', 'Reference');
if (!defined('_CHRONO_NUMBER')) define('_CHRONO_NUMBER', 'Chrono number');
if (!defined('_NO_CHRONO_NUMBER_DEFINED')) define('_NO_CHRONO_NUMBER_DEFINED', 'Chrono number is not defined');
if (!defined('_FOR_CONTACT_C')) define('_FOR_CONTACT_C', 'To');
if (!defined('_TO_CONTACT_C')) define('_TO_CONTACT_C', 'From');

if (!defined('_APPS_COMMENT')) define('_APPS_COMMENT', 'Maarch Entreprise App');
if (!defined('_CORE_COMMENT')) define('_CORE_COMMENT', 'Maarch Entreprise core');
if (!defined('_CLEAR_FORM')) define('_CLEAR_FORM', 'Reset');

if (!defined('_MAX_SIZE_UPLOAD_REACHED')) define('_MAX_SIZE_UPLOAD_REACHED', 'Your file exceeds the maximum size allowed');
if (!defined('_NOT_ALLOWED')) define('_NOT_ALLOWED', 'not allowed');
if (!defined('_CHOOSE_TITLE')) define('_CHOOSE_TITLE', 'Select a title');

/////////////////// Reports
if (!defined('_USERS_LOGS')) define('_USERS_LOGS', 'Access to the application by user');
if (!defined('_USERS_LOGS_DESC')) define('_USERS_LOGS_DESC', 'Access to the application by user');
if (!defined('_PROCESS_DELAY_REPORT')) define('_PROCESS_DELAY_REPORT', 'Average processing time by document type');
if (!defined('_PROCESS_DELAY_REPORT_DESC')) define('_PROCESS_DELAY_REPORT_DESC', 'Average processing time by document type');
if (!defined('_MAIL_TYPOLOGY_REPORT')) define('_MAIL_TYPOLOGY_REPORT', 'Volume of documents per type over a period');
if (!defined('_MAIL_TYPOLOGY_REPORT_DESC')) define('_MAIL_TYPOLOGY_REPORT_DESC', 'Volume of documents per type over a period');
if (!defined('_MAIL_VOL_BY_CAT_REPORT')) define('_MAIL_VOL_BY_CAT_REPORT', 'Volume of documents per category over a period');
if (!defined('_MAIL_VOL_BY_CAT_REPORT_DESC')) define('_MAIL_VOL_BY_CAT_REPORT_DESC', 'Volume of documents per category over a period');
if (!defined('_SHOW_FORM_RESULT')) define('_SHOW_FORM_RESULT', 'Display results with ');
if (!defined('_GRAPH')) define('_GRAPH', 'Charts');
if (!defined('_ARRAY')) define('_ARRAY', 'Table');
if (!defined('_SHOW_YEAR_GRAPH')) define('_SHOW_YEAR_GRAPH', 'Display report for year ');
if (!defined('_SHOW_GRAPH_MONTH')) define('_SHOW_GRAPH_MONTH', 'Display report for month');
if (!defined('_OF_THIS_YEAR')) define('_OF_THIS_YEAR', ' of this year');
if (!defined('_NB_MAILS1')) define('_NB_MAILS1', 'Number of recorded documents');
if (!defined('_FOR_YEAR')) define('_FOR_YEAR', 'for year');
if (!defined('_FOR_MONTH')) define('_FOR_MONTH', 'for');
if (!defined('_N_DAYS')) define('_N_DAYS','NB on days');

/******************** Specific  ************/
if (!defined('_PROJECT')) define('_PROJECT', 'Folder');
if (!defined('_MARKET')) define('_MARKET', 'Sub-folder');
if (!defined('_SEARCH_CUSTOMER')) define('_SEARCH_CUSTOMER', 'View a folder');
if (!defined('_SEARCH_CUSTOMER_TITLE')) define('_SEARCH_CUSTOMER_TITLE', 'View a folder');
if (!defined('_TO_SEARCH_DEFINE_A_SEARCH_ADV')) define('_TO_SEARCH_DEFINE_A_SEARCH_ADV', 'To start a search, please enter a folder or sub-folder id.');
if (!defined('_DAYS')) define('_DAYS', 'days');
if (!defined('_LAST_DAY')) define('_LAST_DAY', 'last day');
if (!defined('_CONTACT_NAME')) define( '_CONTACT_NAME', 'Invoice contact');
if (!defined('_AMOUNT')) define( '_AMOUNT', 'Invoice amount');
if (!defined('_CUSTOMER')) define( '_CUSTOMER', 'Invoice customer');
if (!defined('_PO_NUMBER')) define( '_PO_NUMBER', 'Invoice PO');
if (!defined('_INVOICE_NUMBER')) define( '_INVOICE_NUMBER', 'Invoice nb');


/******************** Keywords Helper ************/
if (!defined('_HELP_KEYWORD0')) define('_HELP_KEYWORD0', 'id of the user of the basket');
if (!defined('_HELP_BY_CORE')) define('_HELP_BY_CORE', 'Keywords defined by Maarch Core');

if (!defined('_FIRSTNAME_UPPERCASE')) define('_FIRSTNAME_UPPERCASE', 'FIRST NAME');
if (!defined('_TITLE_STATS_USER_LOG')) define('_TITLE_STATS_USER_LOG', 'Access to the application');

if (!defined('_DELETE_DOC')) define('_DELETE_DOC', 'Delete this document');
if (!defined('_THIS_DOC')) define('_THIS_DOC', 'this document');
if (!defined('_MODIFY_DOC')) define('_MODIFY_DOC', 'Modify this document information');
if (!defined('_BACK_TO_WELCOME')) define('_BACK_TO_WELCOME', 'Back to home page');
if (!defined('_CLOSE_MAIL')) define('_CLOSE_MAIL', 'Close this document');

/************** R&eacute;ouverture courrier **************/
if (!defined('_MAIL_SENTENCE2')) define('_MAIL_SENTENCE2', 'Enter the number of a document and switch its status to "In progress".');
if (!defined('_MAIL_SENTENCE3')) define('_MAIL_SENTENCE3', ' This feature enables to reopen a document that was closed too early.');
if (!defined('_ENTER_DOC_ID')) define('_ENTER_DOC_ID', 'Enter the document number');
if (!defined('_TO_KNOW_ID')) define('_TO_KNOW_ID', 'To get the document number, perform a search or ask your administrator');
if (!defined('_MODIFY_STATUS')) define('_MODIFY_STATUS', 'Modify the status');
if (!defined('_REOPEN_MAIL')) define('_REOPEN_MAIL', 'Reopen mail');
if (!defined('_REOPEN_THIS_MAIL')) define('_REOPEN_THIS_MAIL', 'Reopen the mail');

if (!defined('_OWNER')) define('_OWNER', 'Owner');
if (!defined('_CONTACT_OWNER_COMMENT')) define('_CONTACT_OWNER_COMMENT', 'Leave this field empty to make this contact public.');

if (!defined('_OPT_INDEXES')) define('_OPT_INDEXES', 'Additional information');
if (!defined('_NUM_BETWEEN')) define('_NUM_BETWEEN', 'Between');
if (!defined('_MUST_CORRECT_ERRORS')) define('_MUST_CORRECT_ERRORS', 'Please correct following errors: ');
if (!defined('_CLICK_HERE_TO_CORRECT')) define('_CLICK_HERE_TO_CORRECT', 'Click here to correct them');

if (!defined('_FILETYPE')) define('_FILETYPE', 'file type');
if (!defined('_WARNING')) define('_WARNING', 'Warning ');
if (!defined('_STRING')) define('_STRING', 'String');
if (!defined('_INTEGER')) define('_INTEGER', 'Integer');
if (!defined('_FLOAT')) define('_FLOAT', 'Float');
if (!defined('_CUSTOM_T1')) define( '_CUSTOM_T1', 'Text field 1');
if (!defined('_CUSTOM_T2')) define( '_CUSTOM_T2', 'Text field 2');
if (!defined('_CUSTOM_D1')) define( '_CUSTOM_D1', 'Date field');
if (!defined('_CUSTOM_N1')) define( '_CUSTOM_N1', 'Integer field');
if (!defined('_CUSTOM_F1')) define( '_CUSTOM_F1', 'Floating field');

if (!defined('_ITEM_NOT_IN_LIST')) define( '_ITEM_NOT_IN_LIST', 'item not in authorised values');
if (!defined('_PB_WITH_FINGERPRINT_OF_DOCUMENT')) define( '_PB_WITH_FINGERPRINT_OF_DOCUMENT', 'Document checksum does not fit ! Issue with document integrity');
if (!defined('_MISSING')) define( '_MISSING', 'missing');
if (!defined('_NATURE')) define( '_NATURE', 'Nature');
if (!defined('_NO_DEFINED_TREES')) define( '_NO_DEFINED_TREES', 'No defined tree');

if (!defined('_IF_CHECKS_MANDATORY_MUST_CHECK_USE')) define( '_IF_CHECKS_MANDATORY_MUST_CHECK_USE', 'If you check &rsquo;mandatory&rsquo;, you must also check &rsquo;used&rsquo;');

if (!defined('_SEARCH_DOC')) define( '_SEARCH_DOC', 'Search document');
if (!defined('_DOCSERVER_COPY_ERROR')) define( '_DOCSERVER_COPY_ERROR', ' Error during copy to docserver');
if (!defined('_MAKE_NEW_SEARCH')) define( '_MAKE_NEW_SEARCH', 'Perform a new search');
if (!defined('_NO_PAGE')) define( '_NO_PAGE', 'No page');
if (!defined('_VALIDATE_QUALIF')) define( '_VALIDATE_QUALIF', 'Validation/Qualification');


if (!defined('_DB_CONNEXION_ERROR')) define( '_DB_CONNEXION_ERROR', 'Database connection error');
if (!defined('_DATABASE_SERVER')) define( '_DATABASE_SERVER', 'Database server');
if (!defined('_DB_PORT')) define( '_DB_PORT', 'Port');
if (!defined('_DB_TYPE')) define( '_DB_TYPE', 'Type');
if (!defined('_DB_USER')) define( '_DB_USER', 'User');
if (!defined('_DATABASE')) define( '_DATABASE', 'Database');


if (!defined('_TREE_ROOT')) define( '_TREE_ROOT', 'Tree root');

if (!defined('_TITLE_STATS_CHOICE_PERIOD')) define('_TITLE_STATS_CHOICE_PERIOD','For a given period');

/******************Docservers: List + form****************/
if (!defined('_SEE_DOCSERVERS_')) define( '_SEE_DOCSERVERS_', 'Display docservers of this type');
if (!defined('_GO_MANAGE_DOCSERVER'))  define('_GO_MANAGE_DOCSERVER', 'Modify');
if (!defined('_SEE_DOCSERVERS_LOCATION')) define( '_SEE_DOCSERVERS_LOCATION', 'Display docservers of this location');
if (!defined('_MANAGE_DOCSERVERS'))  define('_MANAGE_DOCSERVERS', 'Docservers management ("docservers")');
if (!defined('_MANAGE_DOCSERVERS_LOCATIONS'))  define('_MANAGE_DOCSERVERS_LOCATIONS', 'Docservers locations management ("docserver_locations")');
if (!defined('_MANAGE_DOCSERVER_TYPES'))  define('_MANAGE_DOCSERVER_TYPES', 'Docservers types management  ("docserver_types")');
if (!defined('_ADMIN_DOCSERVERS'))  define('_ADMIN_DOCSERVERS', ' Docservers administration');

/***************DOCSERVERS TYPES*************************************/
if (!defined('_DOCSERVER_TYPE_ID'))  define('_DOCSERVER_TYPE_ID', 'Docserver type ID ');
if (!defined('_DOCSERVER_TYPE'))  define('_DOCSERVER_TYPE', 'Docserver type');
if (!defined('_DOCSERVER_TYPES_LIST'))  define('_DOCSERVER_TYPES_LIST', 'Docserver type list ');
if (!defined('_ALL_DOCSERVER_TYPES'))  define('_ALL_DOCSERVER_TYPES', 'View all');
if (!defined('_DOCSERVER_TYPE_LABEL'))  define('_DOCSERVER_TYPE_LABEL', 'Docserver type label ');
if (!defined('_DOCSERVER_TYPES'))  define('_DOCSERVER_TYPES', 'Docserver types');
if (!defined('_IS_CONTAINER'))  define('_IS_CONTAINER', 'Container');
if (!defined('_IS_COMPRESSED'))  define('_IS_COMPRESSED', 'is compressed');
if (!defined('_IS_META'))  define('_IS_META', 'is meta');
if (!defined('_IS_LOGGED'))  define('_IS_LOGGED', 'is logged');
if (!defined('_IS_SIGNED'))  define('_IS_SIGNED', 'is signed');
if (!defined('_COMPRESS_MODE'))  define('_COMPRESS_MODE', 'Compression mode');
if (!defined('_META_TEMPLATE'))  define('_META_TEMPLATE', 'Meta template');
if (!defined('_LOG_TEMPLATE'))  define('_LOG_TEMPLATE', 'log template');
if (!defined('_FINGERPRINT_MODE'))  define('_FINGERPRINT_MODE', 'Signature mode');
if (!defined('_CONTAINER_MAX_NUMBER'))  define('_CONTAINER_MAX_NUMBER', 'Container max number');
if (!defined('_DOCSERVER_TYPE_MODIFICATION'))  define('_DOCSERVER_TYPE_MODIFICATION', 'Docserver type modification');
if (!defined('_DOCSERVER_TYPE_ADDITION'))  define('_DOCSERVER_TYPE_ADDITION', 'Docserver type addition');
if (!defined('_DOCSERVER_TYPE_ADDED'))  define('_DOCSERVER_TYPE_ADDED', 'Docserver type added ');
if (!defined('_DOCSERVER_TYPE_UPDATED'))  define('_DOCSERVER_TYPE_UPDATED', 'Docserver type updated ');
if (!defined('_DOCSERVER_TYPE_DELETED'))  define('_DOCSERVER_TYPE_DELETED', 'Docserver type deleted ');
if (!defined('_NOT_CONTAINER'))  define('_NOT_CONTAINER', 'Not a container');
if (!defined('_CONTAINER'))  define('_CONTAINER', 'A Container');
if (!defined('_NOT_COMPRESSED'))  define('_NOT_COMPRESSED', 'Non compressed');
if (!defined('_COMPRESSED'))  define('_COMPRESSED', 'Compressed');
if (!defined('_COMPRESSION_MODE'))  define('_COMPRESSION_MODE', 'Compression mode');
if (!defined('_GZIP_COMPRESSION_MODE'))  define('_GZIP_COMPRESSION_MODE', 'GZIP compression mode (tar.gz) only avaible for the consultation');

/***************DOCSERVERS*************************************/
if (!defined('_DOCSERVER_ID'))  define('_DOCSERVER_ID', 'Docserver ID');
if (!defined('_DEVICE_LABEL'))  define('_DEVICE_LABEL', 'Device label ');
if (!defined('_SIZE_FORMAT'))  define('_SIZE_FORMAT', 'Size format ');
if (!defined('_SIZE_LIMIT'))  define('_SIZE_LIMIT', 'Size limit');
if (!defined('_ACTUAL_SIZE'))  define('_ACTUAL_SIZE', 'Actual size');
if (!defined('_DOCSERVERS_LIST'))  define('_DOCSERVERS_LIST', 'Docservers list ');
if (!defined('_ALL_DOCSERVERS'))  define('_ALL_DOCSERVERS', 'View all ');
if (!defined('_DOCSERVER'))  define('_DOCSERVER', 'a docserver');
if (!defined('_COLL_ID'))  define('_COLL_ID', 'Collection ID');
if (!defined('_PERCENTAGE_FULL'))  define('_PERCENTAGE_FULL', 'Filling percentage');
if (!defined('_IS_LOGGED'))  define('_IS_LOGGED', 'Is logged');
if (!defined('_IS_CONTAINER'))  define('_IS_CONTAINER', 'Is contained');
if (!defined('_LOG_TEMPLATE'))  define('_LOG_TEMPLATE', 'Templates for resources logged');
if (!defined('_IS_SIGNED'))  define('_IS_SIGNED', 'Is signed');
if (!defined('_FINGERPRINT_MODE'))  define('_FINGERPRINT_MODE', 'Signature mode');
if (!defined('_DOCSERVER_LOCATIONS'))  define('_DOCSERVER_LOCATIONS', 'Docserver locations ');
if (!defined('_DOCSERVER_MODIFICATION'))  define('_DOCSERVER_MODIFICATION', 'Docserver modification');
if (!defined('_DOCSERVER_ADDITION'))  define('_DOCSERVER_ADDITION', 'Add a docserver');
if (!defined('_DOCSERVER_ADDED'))  define('_DOCSERVER_ADDED', 'Docserver added');
if (!defined('_DOCSERVER_DELETED'))  define('_DOCSERVER_ADDED', 'Docserver deleted');
if (!defined('_DOCSERVER_UPDATED'))  define('_DOCSERVER_UPDATED', 'Docserver updated');
if (!defined('_SIZE_LIMIT_NUMBER')) define( '_SIZE_LIMIT_NUMBER', 'Limit size');
if (!defined('_DOCSERVER_ATTACHED_TO_RES_X')) define( '_DOCSERVER_ATTACHED_TO_RES_X', 'Resources linked to this docserver');

/************DOCSERVER LOCATIONS******************************/
if (!defined('_ALL_DOCSERVER_LOCATIONS'))  define('_ALL_DOCSERVER_LOCATIONS', 'View all');
if (!defined('_DOCSERVER_LOCATIONS_LIST'))  define('_DOCSERVER_LOCATIONS_LIST', 'Docserver location list');
if (!defined('_DOCSERVER_LOCATION'))  define('_DOCSERVER_LOCATION', 'a docserver location');
if (!defined('_IPV4'))  define('_IPV4', 'IPv4 Address');
if (!defined('_IPV6'))  define('_IPV6', 'IPv6 Address');
if (!defined('_NET_DOMAIN'))  define('_NET_DOMAIN', 'Net domain');
if (!defined('_DOCSERVER_LOCATION_ID'))  define('_DOCSERVER_LOCATION_ID', 'Docserver location ID');
if (!defined('_MASK'))  define('_MASK', 'Mask');
if (!defined('_DOCSERVER_LOCATION_ADDITION'))  define('_DOCSERVER_LOCATION_ADDITION', 'Add a docserver location');
if (!defined('_DOCSERVER_LOCATION_ADDED'))  define('_DOCSERVER_LOCATION_ADDED', 'Docserver location added');
if (!defined('_DOCSERVER_LOCATION_UPDATED'))  define('_DOCSERVER_LOCATION_UPDATED', 'Docserver location updated');
if (!defined('_DOCSERVER_LOCATION_DELETED'))  define('_DOCSERVER_LOCATION_DELETED', 'Docserver location deleted');
if (!defined('_DOCSERVER_LOCATION_DISABLED'))  define('_DOCSERVER_LOCATION_DISABLED', 'Docserver location disabled');
if (!defined('_DOCSERVER_LOCATION_ENABLED'))  define('_DOCSERVER_LOCATION_ENABLED', 'Docserver location enabled');
if (!defined('_IP_V4_ADRESS_NOT_VALID')) define('_IP_V4_ADRESS_NOT_VALID', 'IPV4 address not valid');
if (!defined('_IP_V4_FORMAT_NOT_VALID')) define('_IP_V4_FORMAT_NOT_VALID', 'IPV4 address not valid');
if (!defined('_IP_V6_NOT_VALID')) define('_IP_V4_FORMAT_NOT_VALID', 'IPV6 address not valid');
if (!defined('_MASK_NOT_VALID')) define('_MASK_NOT_VALID', 'Mask not valid');

/******************** Authentification method  ************/
if (!defined('_STANDARD_LOGIN')) define( '_STANDARD_LOGIN', 'Standard authentification');
if (!defined('_ACTIVEX_LOGIN')) define( '_ACTIVEX_LOGIN', 'Ms Ie - ActiveX authentification');
if (!defined('_HOW_CAN_I_LOGIN')) define( '_HOW_CAN_I_LOGIN', 'Read me if i can\'t log into Maarch..');
if (!defined('_CONNECT')) define( '_CONNECT', 'Connect');
if (!defined('_LOGIN_MODE')) define( '_LOGIN_MODE', 'Login process type');
if (!defined('_SSO_LOGIN')) define( '_SSO_LOGIN', 'Login via SSO');

/************FAILOVER******************************/
if (!defined('_FAILOVER'))  define('_FAILOVER', 'Failover');
if (!defined('_FILE_NOT_EXISTS_ON_THE_SERVER'))  define('_FILE_NOT_EXISTS_ON_THE_SERVER', 'File not exists on the document server');
if (!defined('_NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS'))  define('_NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS', 'No right on resource or not exists');

/************TECHNICAL INFOS******************************/
if (!defined('_TECHNICAL_INFORMATIONS'))  define('_TECHNICAL_INFORMATIONS', 'Technical Informations');
if (!defined('_VIEW_TECHNICAL_INFORMATIONS'))  define('_VIEW_TECHNICAL_INFORMATIONS', 'View technical Informations');
if (!defined('_SOURCE_FILE_PROPERTIES')) define('_SOURCE_FILE_PROPERTIES', 'Source file properties');
if (!defined('_OFFSET'))  define('_OFFSET', 'Offset');
if (!defined('_SETUP'))  define('_SETUP', 'Setup');
if (!defined('_LINK_EXISTS')) define('_LINK_EXISTS', 'A link exists with another object');

if (!defined('_LOGIN_HISTORY')) {
    define('_LOGIN_HISTORY', 'Login of user');
}

if (!defined('_LOGOUT_HISTORY')) {
    define('_LOGOUT_HISTORY', 'Logout of user');
}

if (!defined('_TO_MASTER_DOCUMENT')) {
    define('_TO_MASTER_DOCUMENT', 'to master document #');
}

if (!defined('_WHERE_CLAUSE_NOT_SECURE')) {
    define('_WHERE_CLAUSE_NOT_SECURE', 'where clause not secure');
}

if (!defined('_SQL_QUERY_NOT_SECURE')) {
    define(
        '_SQL_QUERY_NOT_SECURE', 
        'sql query not secure'
    );
}

/*******************************************************************************
 * RA_CODE
*******************************************************************************/
if (!defined('_ASK_RA_CODE_1')) {
    define( '_ASK_RA_CODE_1', 'An email will be send to : ');
}

if (!defined('_ASK_RA_CODE_2')) {
    define( '_ASK_RA_CODE_2', 'Try again when you receive the remote access code.');
}

if (!defined('_CONFIRM_ASK_RA_CODE_1')) {
    define( '_CONFIRM_ASK_RA_CODE_1', 'Hello, ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_2')) {
    define( '_CONFIRM_ASK_RA_CODE_2', 'your Maarch remote access code is : ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_3')) {
    define( '_CONFIRM_ASK_RA_CODE_3', 'This code is available until ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_4')) {
    define( '_CONFIRM_ASK_RA_CODE_4', 'To connect, ');
}

if (!defined('_CONFIRM_ASK_RA_CODE_5')) {
    define( '_CONFIRM_ASK_RA_CODE_5', 'click here');
}

if (!defined('_CONFIRM_ASK_RA_CODE_6')) {
    define( '_CONFIRM_ASK_RA_CODE_6', 'Your Maarch remote access code');
}

if (!defined('_CONFIRM_ASK_RA_CODE_7')) {
    define( '_CONFIRM_ASK_RA_CODE_7', 'An email has been sent to your address');
}

if (!defined('_CONFIRM_ASK_RA_CODE_8')) {
    define( '_CONFIRM_ASK_RA_CODE_8', 'Try to reconnect');
}

if (!defined('_TRYING_TO_CONNECT_FROM_NOT_ALLOWED_IP')) {
    define( '_TRYING_TO_CONNECT_FROM_NOT_ALLOWED_IP', 'You are trying to connect from an unknown host.');
}

if (!defined('_PLEASE_ENTER_YOUR_RA_CODE')) {
    define( '_PLEASE_ENTER_YOUR_RA_CODE', 'Please enter your remote access code.');
}

if (!defined('_ASK_AN_RA_CODE')) {
    define( '_ASK_AN_RA_CODE', 'Ask a remote access code');
}

if (!defined('_RA_CODE_1')) {
    define( '_RA_CODE_1', 'Remote access code');
}

if (!defined('_CAN_T_CONNECT_WITH_THIS_IP')) {
    define( '_CAN_T_CONNECT_WITH_THIS_IP', 'You can\'t connect to Maarch from an unknown host.');
}

/*******************************************************************************
* admin => svn_monitoring
*******************************************************************************/
if (!defined('_SVN_MONITORING')) {
    define( '_SVN_MONITORING', 'SVN Monitoring');
}

if (!defined('_LOADING_INFORMATIONS')) {
    define( '_LOADING_INFORMATIONS', 'Loading informations');
}

if (!defined('_RELEASE_NUMBER')) {
    define( '_RELEASE_NUMBER', 'Release number');
}

if (!defined('_BY')) {
    define( '_BY', 'by');
}

if (!defined('_UP_TO_DATE')) {
    define( '_UP_TO_DATE', 'up to date');
}

if (!defined('_ACTUAL_INSTALLATION')) {
    define( '_ACTUAL_INSTALLATION', 'actual installation');
}

if (!defined('_MAKE_UPDATE')) {
    define( '_MAKE_UPDATE', 'update');
}

if (!defined('_TO_GET_LOG_PLEASE_CONNECT')) {
    define( '_TO_GET_LOG_PLEASE_CONNECT', 'Please connect to get the full log informations');
}

if (!defined('_MANAGE_MEP_RELEASE')) {
    define( '_MANAGE_MEP_RELEASE', 'Manage Maarch Entreprise releases');
}

if (!defined('_INSTALL_SVN_EXTENSION')) {
    define( '_INSTALL_SVN_EXTENSION', 'You must install svn library to view svn log.');
}

if (!defined('_REVERSE_CHECK')) {
    define( '_REVERSE_CHECK', 'Reverse check');
}

//EXPORT

if (!defined('_EXPORT_LIST')) {
    define( '_EXPORT_LIST', 'Export');
}

/******************** Action put in copy ************/
if (!defined('_PUT_IN_COPY')) {
    define('_PUT_IN_COPY', 'Put in copy');
}

if (!defined('_POWERED_BY')) {
    define('_POWERED_BY', 'Powered by Maarch&trade;.');
}
