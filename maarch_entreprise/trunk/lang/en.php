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

/************** Administration **************/
define('_ADMIN_USERS', 'Users');
define('_ADMIN_USERS_DESC', 'Add, suspend, or modify users profiles. Affect users to their groups and define their primary group.');
define('_ADMIN_GROUPS', 'User groups');
define('_ADMIN_GROUPS_DESC', 'Add, suspend, or modify user groups. Set privileges or authorization to access resources.');
define('_ADMIN_ARCHITECTURE', 'Classification scheme');
define('_ADMIN_ARCHITECTURE_DESC', 'Define classification schemes structure (File / sub-file / document type). For each, define their associated descriptors and whether they are mandatory for a file to be complete.');
define('_VIEW_HISTORY', 'Logs');
define('_VIEW_HISTORY_BATCH', 'Logs of batchs');
define('_VIEW_HISTORY_DESC', 'View the log of actions done in Maarch DMS.');
define('_VIEW_HISTORY_BATCH_DESC', 'View the log of batchs');
define('_ADMIN_MODULES', 'Manage modules');
define('_ADMIN_SERVICE', 'Administration service');
define('_XML_PARAM_SERVICE_DESC', 'View servicex XML config');
define('_XML_PARAM_SERVICE', 'View servicex XML config');
define('_MODULES_SERVICES', 'Services defined by modules');
define('_APPS_SERVICES', 'Services defined by the application');
define('_ADMIN_STATUS_DESC', 'Add or modify statuses.');
define('_ADMIN_ACTIONS_DESC', 'Add or modify actions.');
define('_ADMIN_SERVICES_UNKNOWN', 'Unknown administration service');
define('_NO_RIGHTS_ON', 'No rights for');
define('_NO_LABEL_FOUND', 'No label found for this service');

define('_FOLDERTYPES_LIST', 'List of file types');
define('_SELECTED_FOLDERTYPES', 'Selected file types');
define('_FOLDERTYPE_ADDED', 'New file type added');
define('_FOLDERTYPE_DELETION', 'File type deleted');



/*********************** communs ***********************************/

/************** Listes **************/
define('_GO_TO_PAGE', 'Go to page');
define('_NEXT', 'Next');
define('_PREVIOUS', 'Previous');
define('_ALPHABETICAL_LIST', 'Alphabetical list');
define('_ASC_SORT', 'Upwards sorting');
define('_DESC_SORT', 'Downwards sorting');
define('_ACCESS_LIST_STANDARD', 'Display simple lists');
define('_ACCESS_LIST_EXTEND', 'Display extended lists');
define('_DISPLAY', 'Display');
/************** Actions **************/
define('_DELETE', 'Delete');
define('_ADD', 'Add');
define('_REMOVE', 'Remove');
define('_MODIFY', 'Modify');
define('_SUSPEND', 'Disable');
define('_AUTHORIZE', 'Enable');
define('_SEND', 'Send');
define('_SEARCH', 'Search');
define('_RESET', 'Reset');
define('_VALIDATE', 'Confirm');
define('_CANCEL', 'Cancel');
define('_ADDITION', 'Addition');
define('_MODIFICATION', 'Modification');
define('_DIFFUSION', 'Diffusion');
define('_DELETION', 'Deletion');
define('_SUSPENSION', 'Suspension');
define('_VALIDATION', 'Confirmation');
define('_REDIRECTION', 'Redirection');
define('_DUPLICATION', 'Duplication');
define('_PROPOSITION', 'Proposition');
define('_CLOSE', 'Close');
define('_CLOSE_WINDOW', 'Close the window');
define('_DIFFUSE', 'Diffuse');
define('_DOWN', 'Move down');
define('_UP', 'Move up');
define('_REDIRECT', 'Redirect');
define('_DELETED', 'Deleted');
define('_CONTINUE', 'Continue');
define('_VIEW','View');
define('_CHOOSE_ACTION', 'Choose an action');
define('_ACTIONS', 'Actions');
define('_ACTION_PAGE', 'Result page for the action');
define('_DO_NOT_MODIFY_UNLESS_EXPERT', ' Don&quot;t modify this section unless you know exactly what you do. Wrong settings can stop the application from working!');
define('_INFOS_ACTIONS', 'You must choose at least a status and/or a script file.');



/************** Intitul&eacute;s formulaires et listes **************/
define("_ID", 'Id');
define("_PASSWORD", 'Password');
define('_GROUP', 'Group');
define('_USER', 'User');
define('_DESC', 'Description');
define('_LASTNAME', 'Name');
define('_THE_LASTNAME', 'The name');
define('_THE_FIRSTNAME', 'The first name');
define('_FIRSTNAME', 'First name');
define('_STATUS', 'Status');
define('_DEPARTMENT', 'Department');
define('_FUNCTION', 'Role');
define('_PHONE_NUMBER', 'Phone number');
define('_MAIL', 'E-mail');
define('_DOCTYPE', 'Document type');
define('_TYPE', 'Type');
define('_SELECT_ALL', 'Select all');
define('_DATE', 'Date');
define('_ACTION', 'Action');
define('_COMMENTS', 'Comments');
define('_ENABLED', 'Enabled');
define('_NOT_ENABLED', 'Disabled');
define('_RESSOURCES_COLLECTION','Document collection');
define('_RECIPIENT', 'Recipient');
define('_START', 'Start');
define('_END', 'End');

/************** Messages pop up **************/
define('_REALLY_SUSPEND', 'Do you really want to disable ');
define('_REALLY_AUTHORIZE', 'Do you really want to enable  ');
define('_REALLY_DELETE', 'Do you really want to remove ');
define('_DEFINITIVE_ACTION', 'This action is definitive.');

/************** Divers **************/
define('_YES', 'yes');
define('_NO', 'No');
define('_UNKNOWN', 'Unknown');
define('_SINCE','Since');
define('_FOR','To');
define('_HELLO','Hello');
define('_OBJECT','Object');
define('_BACK','Back');
define('_FORMAT','Format');
define('_SIZE','Size');
define('_DOC', 'Document ');
define('_THE_DOC', 'The document');
define('_BYTES', 'bytes');
define('_OR', 'or');
define('_NOT_AVAILABLE', 'Not available');
define('_SELECTION', 'Selection');
define('_AND', ' and ' );
define('_FILE','File');
define('_UNTIL', 'to');
define('_ALL', 'All');

//class functions
define('_SECOND', 'second');
define('_SECONDS', 'seconds');
define('_PAGE_GENERATED_IN', 'Generated in');
define('_IS_EMPTY', 'is empty');
define('_MUST_MAKE_AT_LEAST', 'must contain at least' );
define('_CHARACTER', 'character');
define('_CHARACTERS', 'characters');
define('MUST_BE_LESS_THAN', 'must not be longer than');
define('_WRONG_FORMAT', 'is not valid');
define('_WELCOME', 'Welcome to Maarch Entreprise!');
define('_WELCOME_TITLE', 'Home');
define('_HELP', 'Help');
define('_SEARCH_ADV_SHORT', 'Advanced search');
define('_RESULTS', 'Results');
define('_USERS_LIST_SHORT', 'User list');
define('_MODELS_LIST_SHORT', 'Template list');
define('_GROUPS_LIST_SHORT', 'Group list');
define('_DEPARTMENTS_LIST_SHORT', 'Service list');
define('_BITMASK', 'Bitmask parameter');
define('_DOCTYPES_LIST_SHORT', 'Type list');
define('_BAD_MONTH_FORMAT', 'The month is not correct');
define('_BAD_DAY_FORMAT', 'The day is not correct');
define('_BAD_YEAR_FORMAT', 'The year not correct');
define('_BAD_FEBRUARY', 'February has 29 days or less');
define('_CHAPTER_SHORT', 'Chapt. ');
define('_PROCESS_SHORT', 'Processing');
define('_CARD', 'form');

/************************* First login ***********************************/
define('_MODIFICATION_PSW', 'Modifying Password');
define('_YOUR_FIRST_CONNEXION', 'Welcome to March Entreprise!<br /> This is your first connexion to the application.');
define('_PLEASE_CHANGE_PSW', ' Please modify your password.');
define('_ASKED_ONLY_ONCE', 'This will only be asked once');
define('_FIRST_CONN', 'First connection connection');
define('_LOGIN', 'Connection');
define('_RELOGIN', 'Reconnection');

/*************************  index  page***********************************/
define('_LOGO_ALT', 'Back to homepage');
define('_LOGOUT', 'Logout');
define('_MENU', 'Menu');
define('_ADMIN', 'Administration');
define('_SUMMARY', 'Admin panel');
define('_MANAGE_DIPLOMA', 'Manage diplomas');
define('_MANAGE_CONTRACT', 'Manage contracts types');
define('_MANAGE_REL_MODEL', 'Manage reminder template');
define('_MANAGE_DOCTYPES', 'Manage document types');
define('_MANAGE_DOCTYPES_DESC', 'Manage document types. Document types are attached to a resource collection. For each type, you can define the descriptors to fill in and whether they are mandatory.');
define('_VIEW_HISTORY2', 'View logs');
define('_VIEW_HISTORY_BATCH2', 'View batches logs');
define('_INDEX_FILE', 'Add a document');
define('_WORDING', 'Label');
define('_COLLECTION', 'Collection');
define('_VIEW_TREE_DOCTYPES', 'Tree view of classification scheme');
define('_VIEW_TREE_DOCTYPES_DESC', 'Tree view of your classification scheme (type of file, sub-file and type of documents)');
define('_WELCOME_ON', 'Welcome to');

/************************* Administration ***********************************/

/**************Sommaire**************/
define('_MANAGE_GROUPS_APP', 'Manage user groups');
define('_MANAGE_USERS_APP', 'Manage Users');
define('_MANAGE_DIPLOMA_APP', 'Manage diplomas');
define('_MANAGE_DOCTYPES_APP', 'Manage document types');
define('_MANAGE_ARCHI_APP', 'Manage documents types sorting tree');
define('_MANAGE_CONTRACT_APP', 'Manage types of contracts');
define('_HISTORY_EXPLANATION', 'Monitor modifications, deletions and additions in the application');
define('_ARCHI_EXP', 'Files, sub-files and document types');


/************** Groupes : Liste + Formulaire**************/

define('_GROUPS_LIST', 'Group list');
define('_ADMIN_GROUP', 'Admin Group');
define('_ADD_GROUP', 'Add a group');
define('_ALL_GROUPS', 'All the groups');
define('_GROUPS', 'groups');

define('_GROUP_ADDITION', 'Add a group');
define('_GROUP_MODIFICATION', 'Edit a group');
define('_SEE_GROUP_MEMBERS', 'See users of this groups');
define('_OTHER_RIGHTS', 'Other rights');
define('_MODIFY_GROUP', 'Accept changes');
define('_THE_GROUP', 'The group');
define('_HAS_NO_SECURITY', 'has no defined security' );

define('_DEFINE_A_GRANT', 'Define At least an access right');
define('_MANAGE_RIGHTS', 'This group has access to following resources');
define('_TABLE', 'Table');
define('_WHERE_CLAUSE', 'WHERE clause');
define('_INSERT', 'Insertion');
define('_UPDATE', 'Update');
define('_REMOVE_ACCESS', 'Remove access');
define('_MODIFY_ACCESS', 'Modify access');
define('_UPDATE_RIGHTS', 'update rights');
define('_ADD_GRANT', 'Add access');
define('_USERS_LIST_IN_GROUP', 'List of users in the group');

/************** Utilisateurs : Liste + Formulaire**************/

define('_USERS_LIST', 'User list');
define('_ADD_USER', 'Add a user');
define('_ALL_USERS', 'all users');
define('_USERS', 'users');
define('_USER_ADDITION', 'Add an user');
define('_USER_MODIFICATION', 'Modify an user');
define('_MODIFY_USER', 'Modify the user');

define('_NOTES', 'Notes');
define('_NOTE1', 'Mandatory fields are shown with a red star ');
define('_NOTE2', 'The primary group is mandatory');
define('_NOTE3', 'The first group selected will be the primary group of the user');
define('_USER_GROUPS_TITLE', 'The user belongs to the following groups');
define('_USER_ENTITIES_TITLE', 'The User belongs to following department(s)');
define('_DELETE_GROUPS', 'Delete group(s)');
define('_ADD_TO_GROUP', 'Add a group');
define('_CHOOSE_PRIMARY_GROUP', 'Choose as primary group');
define('_USER_BELONGS_NO_GROUP', 'The user does not belong to any group');
define('_USER_BELONGS_NO_ENTITY', 'The user does not belong to any department');
define('_CHOOSE_ONE_GROUP', 'Select at least one group');
define('_PRIMARY_GROUP', 'Primary Group');
define('_CHOOSE_GROUP', 'Select a  group');
define('_ROLE', 'Role');

define('_THE_PSW', 'The password');
define('_THE_PSW_VALIDATION', 'Verification for the password' );
define('_REENTER_PSW', 'Reenter the password');
define('_USER_ACCESS_DEPARTMENT', 'The user has access to following departments');
define('_FIRST_PSW', 'The first password ');
define('_SECOND_PSW', 'The second password ');

define('_PASSWORD_MODIFICATION', 'PAssword modification');
define('_PASSWORD_FOR_USER', 'the password for the usert');
define('_HAS_BEEN_RESET', 'has been reset');
define('_NEW_PASW_IS', 'the new password is ');
define('_DURING_NEXT_CONNEXION', 'on the next login ');
define('_MUST_CHANGE_PSW', 'must change his/her password');

define('_NEW_PASSWORD_USER', 'Resetting the password for the user');

/************** Types de document : Liste + Formulaire**************/

define('_DOCTYPES_LIST', 'List of document types');
define('_ADD_DOCTYPE', 'Add a document type');
define('_ALL_DOCTYPES', 'All types');
define('_TYPES', 'types');

define('_DOCTYPE_MODIFICATION', 'Modify a document type');
define('_DOCTYPE_CREATION', 'Add a document type');

define('_MODIFY_DOCTYPE', 'Confirm changes');
define('_ATTACH_SUBFOLDER', 'Attach to sub-file');
define('_CHOOSE_SUBFOLDER', 'Select a sub-file');
define('_MANDATORY_FOR_COMPLETE', 'Mandatory for a file to be complete');
define('_MORE_THAN_ONE', 'Iterative file');
define('_MANDATORY_FIELDS_IN_INDEX', 'Mandatory fields for indexing');
define('_DIPLOMA_LEVEL', 'Degree of the dipoma');
define('_THE_DIPLOMA_LEVEL', 'The Degree of the diploma');
define('_DATE_END_DETACH_TIME', 'Date de fin de p&eacute;riode de d&eacute;tachement');
define('_START_DATE', 'Beginning date');
define('_START_DATE_PROBATION', 'Probation beginning date');
define('_END_DATE', 'End date');
define('_END_DATE_PROBATION', 'Probation end date');
define('_START_DATE_TRIAL', 'Trial beginning date');
define('_START_DATE_MISSION', 'Mission beginning date');
define('_END_DATE_TRIAL', 'Trial end date');
define('_END_DATE_MISSION', 'Mission end date');
define('_EVENT_DATE', 'Date of the event');
define('_VISIT_DATE', 'Attendance date');
define('_CHANGE_DATE', 'Change date ');
define('_DOCTYPES_LIST2', 'List of document types');

define('_INDEX_FOR_DOCTYPES', 'Available descriptors for document types');
define('_FIELD', 'Field');
define('_USED', 'Used');
define('_MANDATORY', 'Mandatory');
define('_ITERATIVE', 'Iterative');

define('_MASTER_TYPE', 'Master doc type');

/************** structures : Liste + Formulaire**************/
define('_STRUCTURE_LIST', 'Classification scheme list');
define('_STRUCTURES', 'classification schemes');
define('_STRUCTURE', 'classification scheme');
define('_ALL_STRUCTURES', 'All classification schemes');

define('_THE_STRUCTURE', 'the classification scheme');
define('_STRUCTURE_MODIF', 'Modify the classification scheme');
define('_ID_STRUCTURE_PB', 'A problem occurs with the id of the classification scheme');
define('_NEW_STRUCTURE_ADDED', 'Add a new classification scheme');
define('_NEW_STRUCTURE', 'New classification scheme');
define('_DESC_STRUCTURE_MISSING', 'The description of the classification scheme is missing');
define('_STRUCTURE_DEL', 'Delete of the classification scheme');
define('_DELETED_STRUCTURE', 'Classification scheme deleted');

/************** sous-dossiers : Liste + Formulaire**************/
define('_SUBFOLDER_LIST', 'Sub-file list');
define('_SUBFOLDERS', 'sub-file');
define('_ALL_SUBFOLDERS', 'All sub-file');
define('_SUBFOLDER', 'Sub-file');

define('_ADD_SUBFOLDER', 'Add a new sub-file');
define('_THE_SUBFOLDER', 'The sub-file');
define('_SUBFOLDER_MODIF', 'Modify a sub-file');
define('_SUBFOLDER_CREATION', 'Add sub-file');
define('_SUBFOLDER_ID_PB', 'A problem occured with the id of the sub-file');
define('_SUBFOLDER_ADDED', 'Add a sub-file');
define('_NEW_SUBFOLDER', 'New sub-file');
define('_STRUCTURE_MANDATORY', 'A classification scheme is mandatory');
define('_SUBFOLDER_DESC_MISSING', 'The description of the sub-file is missing');

define('_ATTACH_STRUCTURE', 'Attach to a classification scheme');
define('_CHOOSE_STRUCTURE', 'Choose a classification scheme');

define('_DEL_SUBFOLDER', 'delete a sub-file');
define('_SUBFOLDER_DELETED', 'Sub-file deleted');


/************** Status **************/

define('_STATUS_LIST', 'Status list');
define('_ADD_STATUS', 'Add a new status');
define('_ALL_STATUS', 'All status');
define('_STATUS_PLUR', 'status(es)');
define('_STATUS_SING', 'status');

define('_TO_PROCESS','To process');
define('_IN_PROGRESS','In progress');
define('_FIRST_WARNING','1st reminder');
define('_SECOND_WARNING','2nd reminder');
define('_CLOSED','Closed');
define('_NEW','New');
define('_LATE', 'Late');

define('_STATUS_DELETED', 'Delete status');
define('_DEL_STATUS', 'Status deleted');
define('_MODIFY_STATUS', 'Modify status');
define('_STATUS_ADDED','Status added');
define('_STATUS_MODIFIED','Status modified');
define('_NEW_STATUS', 'New status');
define('_IS_SYSTEM', 'System');
define('_CAN_BE_SEARCHED', 'Can documents be searched?');
define('_CAN_BE_MODIFIED', 'Can documents be modified?');
define('_THE_STATUS', 'The status ');
define('_ADMIN_STATUS', 'Statuses');
define('_ADMIN_STATUS_DESC', 'Manage the available statuses for documents in this application');
/************* Actions **************/

define('_ACTION_LIST', 'Actions list');
define('_ADD_ACTION', 'Add a new action');
define('_ALL_ACTIONS', 'All actions');
define('_ACTIONS', 'actions');
define('_ACTION', 'action');
define('_ACTION_HISTORY', 'Log the action');

define('_ACTION_DELETED', 'Delete the action');
define('_DEL_ACTION', 'Action deleted');
define('_MODIFY_ACTION', 'Modify the action');
define('_ACTION_ADDED','Action added');
define('_ACTION_MODIFIED','Action modified');
define('_NEW_ACTION', 'New action');
define('_THE_ACTION', 'The action ');
define('_ADMIN_ACTIONS', 'Actions');
define('_ADMIN_ACTIONS_DESC', 'Manage available actions in the application');

/************** Historique**************/
define('_HISTORY_TITLE', 'Events log');
define('_HISTORY_BATCH_TITLE', 'Batches event log');
define('_HISTORY', 'Log');
define('_HISTORY_BATCH', 'Batches log');
define('_BATCH_NAME', 'Batch name');
define('_CHOOSE_BATCH', 'Choose a batch');
define('_BATCH_ID', 'Batch id');
define('_TOTAL_PROCESSED', 'Total processed');
define('_TOTAL_ERRORS', 'Total errors');
define('_ONLY_ERRORS', 'Only with errors');
define('_INFOS', 'Infos');

/************** Admin de l'architecture  (plan de classement) **************/
define('_ADMIN_ARCHI', 'Administration of classification schemes');
define('_MANAGE_STRUCTURE', 'Manage files');
define('_MANAGE_STRUCTURE_DESC', 'Manage files. They are the highest element of the hierarchy. If the "Folder" module is enabled, you can attach a file type to a sorting tree.');
define('_MANAGE_SUBFOLDER', 'Manage sub-files');
define('_MANAGE_SUBFOLDER_DESC', 'Manage sub-files in files.');
define('_ARCHITECTURE', 'classification scheme');

/************************* Messages d'erreurs ***********************************/
define('_MORE_INFOS', 'Contact your admin for more information ');
define('_ALREADY_EXISTS', 'already exists!');

// class usergroups
define('_NO_GROUP', 'The group does not exist !');
define('_NO_SECURITY_AND_NO_SERVICES', 'has no defined security and no service');
define('_GROUP_ADDED', 'New group added');
define('_SYNTAX_ERROR_WHERE_CLAUSE', 'error in the WHERE clause syntax');
define('_GROUP_UPDATED', 'Group modified');
define('_AUTORIZED_GROUP', 'Group enabled');
define('_SUSPENDED_GROUP', 'Group disabled');
define('_DELETED_GROUP', 'Group deleted');
define('_GROUP_UPDATE', 'Modify a group');
define('_GROUP_AUTORIZATION', 'Enable a group');
define('_GROUP_SUSPENSION', 'Disable a group');
define('_GROUP_DELETION', 'Delete a group');
define('_GROUP_DESC', 'The description of a group ');
define('_GROUP_ID', 'The id of the group');
define('_EXPORT_RIGHT', 'Export right');

//class users
define('_USER_NO_GROUP', 'you do not belong to any group');
define('_SUSPENDED_ACCOUNT', 'Your account has been disabled');
define('_BAD_LOGIN_OR_PSW', 'Wrong username or password');
define('_WRONG_SECOND_PSW', 'the second password does not match the first one!');
define('_AUTORIZED_USER', 'User enabled');
define('_SUSPENDED_USER', 'User disabled');
define('_DELETED_USER', 'User deleted;');
define('_USER_DELETION', 'Delete the user');
define('_USER_AUTORIZATION', 'Enable the user');
define('_USER_SUSPENSION', 'Disable the user');
define('_USER_UPDATED', 'User modified');
define('_USER_UPDATE', 'Modify an user');
define('_USER_ADDED', 'New user added');
define('_NO_PRIMARY_GROUP', 'No primary group selected!');
define('_THE_USER', 'The user ');
define('_USER_ID', 'The id of the user');
define('_MY_INFO', 'My account');


//class types
define('_UNKNOWN_PARAM', 'Unknown parameters');
define('_DOCTYPE_UPDATED', 'Document type modified');
define('_DOCTYPE_UPDATE', 'Modify a document type');
define('_DOCTYPE_ADDED', 'New document type added');
define('_DELETED_DOCTYPE', 'Document type deleted');
define('_DOCTYPE_DELETION', 'delete a document type');
define('_THE_DOCTYPE', 'the document type ');
define('_THE_WORDING', 'the label ');
define('_THE_TABLE', 'The table ');
define('_PIECE_TYPE', 'type of file');

//class db
define('_CONNEXION_ERROR', 'An error occurs while connecting');
define('_SELECTION_BASE_ERROR', 'An error occurs while selecting the table');
define('_QUERY_ERROR', 'An error occurs while executing the query');
define('_CLOSE_CONNEXION_ERROR', 'An error occurs while while closing the connection');
define('_ERROR_NUM', 'Error num.');
define('_HAS_JUST_OCCURED', 'just occured');
define('_MESSAGE', 'Message');
define('_QUERY', 'Query');
define('_LAST_QUERY', 'Latest query');

//Autres
define('_NO_GROUP_SELECTED', 'No group selected');
define('_NOW_LOG_OUT', 'You are logged out');
define('_DOC_NOT_FOUND', 'The document cannot be found');
define('_DOUBLED_DOC', 'Duplicate problem');
define('_NO_DOC_OR_NO_RIGHTS', 'This document does not exist, or you do not have sufficient right to view it.');
define('_INEXPLICABLE_ERROR', 'An unattended error occurs');
define('_TRY_AGAIN_SOON', 'Please try again in a few seconds');
define('_NO_OTHER_RECIPIENT', 'There is no other recipient for this document');
define('_WAITING_INTEGER', 'Integer expected');

define('_DEFINE', 'Complementary information :');
define('_NUM', '#');
define('_ROAD', 'Street');
define('_POSTAL_CODE','Zip code');
define('_CITY', 'City');

define('_CHOOSE_USER', 'Select an user');
define('_CHOOSE_USER2', 'Select an user');
define('_NUM2', 'nb');
define('_UNDEFINED', 'N/A');
define('_CONSULT_EXTRACTION', 'You can consult the documents here');
define('_SERVICE', 'Service');
define('_AVAILABLE_SERVICES', 'Available services');

// Mois
define('_JANUARY', 'January');
define('_FEBRUARY', 'February');
define('_MARCH', 'March');
define('_APRIL', 'April');
define('_MAY', 'May');
define('_JUNE', 'June');
define('_JULY', 'July');
define('_AUGUST', 'August');
define('_SEPTEMBER', 'September');
define('_OCTOBER', 'October');
define('_NOVEMBER', 'November');
define('_DECEMBER', 'December');

define('_NOW_LOGOUT', 'You are logged out');
define('_LOGOUT', 'Logout');

define('_WELCOME2', 'Welcome');
define('_WELCOME_NOTES1', 'To access the different parts of the application');
define('_WELCOME_NOTES2', 'use the <b>menu</b> above');
define('_WELCOME_NOTES3', 'Maarch Team is very proud to present this new framework, which represents an important milestone in the development of the solution.<br><br>In this sample application, you can:<ul><li>o create archive boxes to store the original paper documents you scanned<b>(<i>Physical Archive</i> module)</b></li><li>o Print barcode separator <b>(<i>Physical Archive</i> module)</b></li><li>o Index new documents in two separate collections (production documents and customer invoices) <b>(<i>Indexing & Searching</i> module)</b></li><li>o Mass import customer invoices <b>(<i>Maarch AutoImport</i> add on)</b></li><li>o consult the two document collections <b>(<i> Indexing & Searching</i> module)</b></li><li>o Browse the invoice collection through dynamic trees<b>(<i> AutoFoldering</i> module)</b></li></ul>');
define('_WELCOME_NOTES5', 'Refer to <u><a href="http://www.maarch.org/maarch_wiki/Maarch_Framework_3">maarch wiki</a></u> for more information.');
define('_WELCOME_NOTES6', 'You can also visit our <u><a href="http://www.maarch.org/">community website</a></u> or Maarch <u><a href="http://www.maarch.org/maarch_forum/">forum</a></u>.');
define('_WELCOME_NOTES7', 'If you need professional support or spefific integration, check <u><a href="http://www.maarch.fr/">our services offer</a></u>.');
define('_WELCOME_COUNT', 'Number of resources in the collection');

define('_CONTRACT_HISTORY', 'Contracts history');

define('_CLICK_CALENDAR', 'Clic to choose a date');
define('_MODULES', 'Modules');
define('_CHOOSE_MODULE', 'Select a module');
define('_FOLDER', 'File');
define('_INDEX', 'Index');

//COLLECTIONS
define('_MAILS', 'Mail');
define('_DOCUMENTS', 'Real estate loans');
define('_INVOICES', 'Customer invoice');
define('_CHOOSE_COLLECTION', 'Select a collection');

define('_EVENT', 'Event');
define('_LINK', 'Link');


//BITMASK
define('_BITMASK_VALUE_ALREADY_EXIST' , 'Bitmask already used');

define('_ASSISTANT_MODE', 'Assistant mode');
define('_EDIT_WITH_ASSISTANT', 'Click here to edit the WHERE clause in assistant mode');
define('_VALID_THE_WHERE_CLAUSE', 'Click here to validate the WHERE clause');
define('_DELETE_SHORT', 'Delete');
define('_CHOOSE_ANOTHER_SUBFOLDER', 'Select another sub-file');
define('_DOCUMENTS_EXISTS_FOR_COLLECTION', 'existing documents in the collection');
define('_MUST_CHOOSE_COLLECTION_FIRST', 'You must select a collection');
define('_CANTCHANGECOLL', 'You cannot change the collection');
define('_DOCUMENTS_EXISTS_FOR_COUPLE_FOLDER_TYPE_COLLECTION', 'existing documents for the duet doctype/collection');

define('_NO_RIGHT', 'Error');
define('_NO_RIGHT_TXT', 'The document you are trying to access does not exist or you do not have sufficient rights to access it...');
define('_NUM_GED', 'DMS #');

///// Manage action error
define('_AJAX_PARAM_ERROR', 'Wrong parameters given to the AJAX request.');
define('_ACTION_CONFIRM', 'Do you really want to make following action: ');
define('_ACTION_NOT_IN_DB', 'This action does not exist!');
define('_ERROR_PARAM_ACTION', 'Wrong parameters for the action');
define('_SQL_ERROR', 'SQL Error');
define('_ACTION_DONE', 'Action done');
define('_ACTION_PAGE_MISSING', 'The result page for this action cannot be found !');
define('_ERROR_SCRIPT', 'Action result page : an error occured in the script or a function is missing');
define('_SERVER_ERROR', 'Server error');
define('_CHOOSE_ONE_DOC', 'Select at least one document');

define('_CLICK_LINE_TO_CHECK_INVOICE', 'Claick on a line to check an invoice.');
define('_FOUND_INVOICES', ' Invoice(s) found');
define('_TO_PROCESS', 'New invoice');
define('_IN_PROGRESS', 'Ongoing invoice');
define('_SIMPLE_CONFIRM', 'Simple confirmation');
define('_CHECK_INVOICE', 'Verify invoice');

define('_REDIRECT_TO', 'Redirect to');
define('_NO_STRUCTURE_ATTACHED', 'This type of documents is not attached to any structure');


///// Credits
define('_MAARCH_CREDITS', 'About Maarch&nbsp;');
define('_CR_LONGTEXT_INFOS', '<p>Maarch Enterprise is a <b>DMS Platform</b>. It addresses most of the needs an organisation cas express to the operative management of its content. A vast majority of it components are released under the terms of the open source license GNU GPLv3. As a result, the total cost of ownership makes it affordable for any kind of organisation to use it (public sector, private companies associations, etc.).</p><p>Maarch Framework has been designed by two consultants whose experience in in records management and ADF sums up to 20 years. Thus this product <b>guarantees a level of stability, integrity and performance</b> one can expect for that type of product. The architecture of the software has been particularly designed so that it can run on standard servers.</p><p>Maarch is developed in PHP5 object. It is compatible with 4 database engines: MySQL, PostgreSQL, SQL Server and soon Oracle.</p><p>Maarch is <b>fully modular</b>: all functionalities are grouped in modules. The modules expose services, which can be enabled or disabled according to the user functional profile. A trained engineer can add or replace an existing module without modifying thr core of the program.</p><p>Maarch offers a global model and necessary tools to <b>acquire, manage, archive and retrieve production document streams</b>.<p>');

define('_CLOSED', 'Approved');
define('_PROCESS_SHORT', 'Process');
define('_PROCESSING_DATE', 'processing deadline');
define('_PROCESS_NUM','Processing mail nb.');
define('_PROCESS_LIMIT_DATE', 'Processing deadline');
define('_TO_PROCESS', 'To process');
define('_IN_PROGRESS', 'In progress');
define('_LATE_PROCESS', 'Late');
define('_PROCESS_DELAY', 'Processing period');
define('_ALARM1_DELAY', 'Period before 1st reminder');
define('_ALARM2_DELAY', 'Period before 2nd reminder');
define('_CATEGORY', 'Category');
define('_CHOOSE_CATEGORY', 'Choose a category');
define('_RECEIVING_DATE', 'Reception date');
define('_SUBJECT', 'Object');
define('_AUTHOR', 'Author');
define('_DOCTYPE_MAIL', 'Mail type');
define('_PROCESS_LIMIT_DATE_USE', 'Enable processing deadline');
define('_DEPARTMENT_DEST', 'Recipient department');
define('_DEPARTMENT_EXP', 'Sender department');


// Mail Categories
define('_INCOMING', 'Incoming mail');
define('_OUTGOING', 'Outgoing mail');
define('_INTERNAL', 'Internal mail');
define('_MARKET_DOCUMENT', 'Document to file');

// Mail Natures
define('_SIMPLE_MAIL', 'Simple mail');
define('_EMAIL', 'Email');
define('_FAX', 'Fax');
define('_CHRONOPOST', 'UPS');
define('_FEDEX', 'Fedex');
define('_REGISTERED_MAIL', 'Recorded delivery');
define('_COURIER', 'Courier');
define('_OTHER', 'Other');

//Priorities
define('_NORMAL', 'Normal');
define('_VERY_HIGH', 'Very high');
define('_HIGH', 'High');
define('_LOW', 'Low');
define('_VERY_LOW', 'Very low');


define('_INDEXING_MLB', 'Record a document');
define('_ADV_SEARCH_MLB', 'Search a document');

define('_ADV_SEARCH_TITLE', 'Document advanced search');
define('_MAIL_OBJECT', 'Mail object');
//define('_SHIPPER', 'Emetteur');
//define('_SENDER', 'Exp&eacute;diteur');
//define('_SOCIETY', 'Soci&eacute;t&eacute;');
//define('_SHIPPER_SEARCH','Dans le champ &eacute;metteur, les recherches ne sont effectu&eacute;es ni sur les civilit&eacute;s, ni sur les pr&eacute;noms.');
//define('_MAIL_IDENTIFIER','R&eacute;f&eacute;rence de l&rsquo;affaire');
define('_N_GED','DMS nb. ');
define('_GED_NUM', 'DMS nb. ');
define('_CHOOSE_TYPE_MAIL','Choose a type of document');
//define('_INVOICE_TYPE','Nature de l&rsquo;envoi');
//define('_CHOOSE_INVOICE_TYPE','Choisissez la nature de l&rsquo;envoi');
define('_REG_DATE','Record date');
define('_PROCESS_DATE','Processing deadline');
define('_CHOOSE_STATUS','Choose a status');
define('_PROCESS_RECEIPT','Main recipient');
define('_CHOOSE_RECEIPT','Choose a recipient');
define('_TO_CC','In copy');
define('_ADD_COPIES','Add users in copy');
//define('_ANSWER_TYPE','Type(s) de r&eacute;ponse');
define('_PROCESS_NOTES','Processing notes');
define('_DIRECT_CONTACT','Direct contact');
define('_NO_ANSWER','No answer');
define('_DETAILS', 'Details sheet');
define('_DOWNLOAD', 'Download document');
define('_SEARCH_RESULTS', 'Search results');
define('_DOCUMENTS', 'documents');
define('_THE_SEARCH', 'The search');
define('_CHOOSE_TABLE', 'Choose a collection');
define('_SEARCH_COPY_MAIL','Search in copy mail');
define('_MAIL_PRIORITY', 'Mail priority');
define('_CHOOSE_PRIORITY', 'Choose a priority');
define('_ADD_PARAMETERS', 'Add criteria');
define('_CHOOSE_PARAMETERS', 'Choose criteria');
define('_CHOOSE_ENTITES_SEARCH_TITLE', 'Add services to refine your search');
define('_CHOOSE_DOCTYPES_SEARCH_TITLE', 'Add document types to refine your search');
define('_DESTINATION_SEARCH', 'Department)');
define('_ADD_PARAMETERS_HELP', 'Add criteria to refine your search');
define('_MAIL_OBJECT_HELP', 'Add object keywords');
define('_N_GED_HELP', '');
define('_CHOOSE_RECIPIENT_SEARCH_TITLE', 'Add recipients to refine the search');
define('_MULTI_FIELD','multi-field');
define('_MULTI_FIELD_HELP','Object, description, title, chrono number, processing notes...');
define('_SAVE_QUERY', 'Save your search');
define('_SAVE_QUERY_TITLE', 'Save your search criteria');
define('_QUERY_NAME', 'Name');
define('_QUERY_SAVED', 'Search criteria saved');
define('_SERVER_ERROR', 'An unknown server error occured during the request');
//define('_SQL_ERROR', 'Erreur SQL lors de l&acute;enregistrement de la recherche');
define('_LOAD_QUERY', 'Load custom search');
define('_DELETE_QUERY', 'Delete custom search');
define('_CHOOSE_SEARCH', 'Choose a custom search');
define('_THIS_SEARCH', 'This search');
define('_MY_SEARCHES', 'My searches');
define('_CLEAR_SEARCH', 'Reset criteria');
define('_CHOOSE_STATUS_SEARCH_TITLE', 'Add statuses to refine the search');
define('_ERROR_IE_SEARCH', 'This criterion is already selected');
//define('_CIVILITIES', 'Civilit&eacute;(s)');
//define('_CIVILITY', 'Civilit&eacute;');
//define('_CHOOSE_CIVILITY_SEARCH_TITLE', 'Ajoutez le/les civilit&eacute;(s) d&eacute;sir&eacute;(s) pour restreindre la recherche');

define('_DEST_USER','Recipient');
define('_DOCTYPES','Document type');
define('_MAIL_NATURE', 'Mail nature');
define('_CHOOSE_MAIL_NATURE', 'Choose mail nature');
define('_ERROR_DOCTYPE', 'Document type is not valid');
define('_ADMISSION_DATE', 'Reception date');
define('_FOUND_DOC', 'document(s) found');
define('_PROCESS', 'Processing ');
define('_DOC_NUM', 'document nb. ');
define('_GENERAL_INFO', 'General information');
define('_ON_DOC_NUM', ' on document nb.');
define('_PRIORITY', 'Priority');
define('_MAIL_DATE', 'Mail date');
define('_DOC_HISTORY', 'Logs');
define('_DONE_ANSWERS','Answers to the mail');
define('_MUST_DEFINE_ANSWER_TYPE', 'You must set the type of the answer');
define('_MUST_CHECK_ONE_BOX', 'You must check at least one box');
define('_ANSWER_TYPE','Answer types');

define('_INDEXATION_TITLE', 'Record a document');
define('_CHOOSE_FILE', 'Select a file');
define('_CHOOSE_TYPE', 'Select the type of the document');

define('_FILE_LOADED_BUT_NOT_VISIBLE', 'The document has been saved on the server.<br/>');
define('_ONLY_FILETYPES_AUTHORISED', 'Only following document can be displayed in your browser');
define('_PROBLEM_LOADING_FILE_TMP_DIR', 'An error occured while trying to copy the file on the server');
define('_DOWNLOADED_FILE', 'File saved');
define('_WRONG_FILE_TYPE', 'This document type is not allowed');

define('_LETTERBOX', 'Mail management');
define('_APA_COLL', 'Physical archives - Do not use');
define('_REDIRECT_TO_ACTION', 'Redirect to an action');
define('_DOCUMENTS_LIST', 'List');


/********* Contacts ************/
define('_ADMIN_CONTACTS', 'Contacts');
define('_ADMIN_CONTACTS_DESC', 'Contacts administration');
define('_CONTACTS_LIST', 'Contacts list');
define('_CONTACT_ADDITION', 'Add a contact');
define('_CONTACTS', 'contact(s)');
define('_CONTACT', 'Contact');
define('_ALL_CONTACTS', 'All contacts');
define('_ADD_CONTACT', 'Add a contact');
define('_PHONE', 'Telephone');
define('_ADDRESS', 'Address');
define('_STREET', 'Street');
define('_COMPLEMENT', 'Address complement');
define('_TOWN', 'Town');
define('_COUNTRY', 'Country');
define('_SOCIETY', 'Organisation');
define('_COMP', 'Other');
define('_COMP_DATA', 'Additional information');
define('_CONTACT_ADDED', 'Contact added');
define('_CONTACT_MODIFIED', 'Contact mofifyed');
define('_CONTACT_DELETED', 'Contact deleted');
define('_MODIFY_CONTACT', 'Modify a contact');
define('_IS_CORPORATE_PERSON', 'Institution');
define('_TITLE2', 'Title');

define('_YOU_MUST_SELECT_CONTACT', 'You must select a contact ');
define('_CONTACT_INFO', 'Contact sheet');

define('_SHIPPER', 'Sender');
define('_DEST', 'Recipient');
define('_INTERNAL2', 'Internal');
define('_EXTERNAL', 'External');
define('_CHOOSE_SHIPPER', 'Select a sender');
define('_CHOOSE_DEST', 'Select a recipient');
define('_DOC_DATE', 'Document date');
define('_CONTACT_CARD', 'Fiche sheet');
define('_CREATE_CONTACT', 'Add a contact');
define('_USE_AUTOCOMPLETION', 'Use autocompletion');

define('_USER_DATA', 'User sheet');
define('_SHIPPER_TYPE', 'Sender type');
define('_DEST_TYPE', 'Recipient type');
define('_VALIDATE_MAIL', 'Validate document');
define('_LETTER_INFO','Information on document');
define('_DATE_START','Arrival date');
define('_LIMIT_DATE_PROCESS','Processing deadline');


//// INDEXING SEARCHING
define('_NO_RESULTS', 'No result found');
define('_FOUND_DOCS', 'document(s) found');
define('_MY_CONTACTS', 'My contacts');
define('_DETAILLED_PROPERTIES', 'Details sheets');
define('_VIEW_DOC_NUM', 'View document nb.');
define('_TO', 'to');
define('_FILE_PROPERTIES', 'file properties');
define('_FILE_DATA', 'Information about document');
define('_VIEW_DOC', 'View the document');
define('_TYPIST', 'Operator');
define('_LOT', 'Batch');
define('_ARBOX', 'Box');
define('_ARBOXES', 'Boxes');
define('_ARBATCHES', 'Batch');
define('_CHOOSE_BOXES_SEARCH_TITLE', 'Add an archive box to refine your search');
define('_PAGECOUNT', 'Nb of pages');
define('_ISPAPER', 'Paper');
define('_SCANDATE', 'Scan date');
define('_SCANUSER', 'Scanner user');
define('_SCANLOCATION', 'Scan place');
define('_SCANWKSATION', 'Scan unit');
define('_SCANBATCH', 'Scan batch');
define('_SOURCE', 'Origin');
define('_DOCLANGUAGE', 'Document language');
define('_MAILDATE', 'Document date');
define('_MD5', 'MD5 hash');
define('_WORK_BATCH', 'Load batch');
define('_DONE','Description');
define('_ANSWER_TYPES_DONE', 'Type of answer(s)');
define('_CLOSING_DATE', 'Closing date');
define('_FULLTEXT', 'Full text search');
define('_FULLTEXT_HELP', '');
define('_FILE_NOT_SEND', 'The document has not been sent');
define('_TRY_AGAIN', 'Please, try again');
define('_DOCTYPE_MANDATORY', 'Document type is mandatory');
define('_INDEX_UPDATED', 'Indices updated');

define('_QUICKLAUNCH', 'Shortcut');
define('_SHOW_DETAILS_DOC', 'View this document&apos;s details sheet');
define('_VIEW_DOC_FULL', 'View this document');
define('_DETAILS_DOC_FULL', 'View this document&apos;s details sheet');
define('_IDENTIFIER', 'Reference');
define('_CHRONO_NUMBER', 'Chrono number');
define('_NO_CHRONO_NUMBER_DEFINED', 'Chrono number is not defined');
define('_FOR_CONTACT_C', 'To');
define('_TO_CONTACT_C', 'From');

define('_APPS_COMMENT', 'Maarch Entreprise App');
define('_CORE_COMMENT', 'Maarch Entreprise core');
define('_CLEAR_FORM', 'Reset');

define('_MAX_SIZE_UPLOAD_REACHED', 'Your file exceeds the maximum size allowed');
define('_NOT_ALLOWED', 'not allowed');
define('_CHOOSE_TITLE', 'Select a title');

/////////////////// Reports
define('_USERS_LOGS', 'Access to the application by user');
define('_USERS_LOGS_DESC', 'Access to the application by user');
define('_PROCESS_DELAY_REPORT', 'Average processing time by document type');
define('_PROCESS_DELAY_REPORT_DESC', 'Average processing time by document type');
define('_MAIL_TYPOLOGY_REPORT', 'Volume of documents per type over a period');
define('_MAIL_TYPOLOGY_REPORT_DESC', 'Volume of documents per type over a period');
define('_MAIL_VOL_BY_CAT_REPORT', 'Volume of documents per category over a period');
define('_MAIL_VOL_BY_CAT_REPORT_DESC', 'Volume of documents per category over a period');
define('_SHOW_FORM_RESULT', 'Display results with ');
define('_GRAPH', 'Charts');
define('_ARRAY', 'Table');
define('_SHOW_YEAR_GRAPH', 'Display report for year ');
define('_SHOW_GRAPH_MONTH', 'Display report for month');
define('_OF_THIS_YEAR', ' of this year');
define('_NB_MAILS1', 'Number of recorded documents');
define('_FOR_YEAR', 'for year');
define('_FOR_MONTH', 'for');
define('_N_DAYS','NB on days');

/******************** Specific DGGT ************/
define('_PROJECT', 'File');
define('_MARKET', 'Sub-file');
define('_SEARCH_CUSTOMER', 'File consultation');
define('_SEARCH_CUSTOMER_TITLE', 'Search a file');
define('_TO_SEARCH_DEFINE_A_SEARCH_ADV', 'To start a search, please enter a file or sub-file number.');
define('_DAYS', 'days');
define('_LAST_DAY', 'last day');



/******************** Keywords Helper ************/
define('_HELP_KEYWORD0', 'id of the user of the basket');
define('_HELP_BY_CORE', 'Keywords defined by Maarch Core');

define('_FIRSTNAME_UPPERCASE', 'FIRST NAME');
define('_TITLE_STATS_USER_LOG', 'Access to the application');

define('_DELETE_DOC', 'Delete this document');
define('_THIS_DOC', 'this document');
define('_MODIFY_DOC', 'Modify this document information');
define('_BACK_TO_WELCOME', 'back to home page');
define('_CLOSE_MAIL', 'Close this document');

/************** R&eacute;ouverture courrier **************/
define('_MAIL_SENTENCE2', 'Enter the number of a document and switch its statute to "In progress".');
define('_MAIL_SENTENCE3', 'This feature enables to reopen a document that was closed to early.');
define('_ENTER_DOC_ID', 'Enter the identifier of the document');
define('_TO_KNOW_ID', 'To know the identifier of the document, make a research or ask it the operator');
define('_MODIFY_STATUS', 'Modify the status');
define('_REOPEN_MAIL', 'Reopen mail');
define('_REOPEN_THIS_MAIL', 'Reopen the mail');

define('_OWNER', 'Owner');
define('_CONTACT_OWNER_COMMENT', 'Leave this field empty to make this contact public.');

define('_OPT_INDEXES', 'Additional information');
define('_NUM_BETWEEN', 'Between');
define('_MUST_CORRECT_ERRORS', 'Please correct following errors: ');
define('_CLICK_HERE_TO_CORRECT', 'Click here to correct them');

define('_FILETYPE', 'file type');
define('_WARNING', 'Warning ');
define('_STRING', 'String');
define('_INTEGER', 'Integer');
define('_FLOAT', 'Float');
?>
