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
define('_ADMIN_ARCHITECTURE', 'Folders&quot; organisation');
define('_ADMIN_ARCHITECTURE_DESC', 'Define the inner structure of a folder (folder / sub-folder / document type). For each, define their associated descriptors and wether they are mandatory for a folder to be complete.');
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

define('_FOLDERTYPES_LIST', 'List of document types');
define('_SELECTED_FOLDERTYPES', 'Selected document types');
define('_FOLDERTYPE_ADDED', 'New document added');
define('_FOLDERTYPE_DELETION', 'Document deleted');


/*********************** communs ***********************************/

/************** Listes **************/
define('_GO_TO_PAGE', 'Go to page');
define('_NEXT', 'Next');
define('_PREVIOUS', 'Previous');
define('_ALPHABETICAL_LIST', 'Alphabetical list');
define('_ASC_SORT', 'Upwards sorting');
define('_DESC_SORT', 'Downwards sorting');
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
define('_UNTIL', 'To');

//class functions
define('_SECOND', 'second');
define('_SECONDS', 'seconds');
define('_PAGE_GENERATED_IN', 'Generated in');
define('_IS_EMPTY', 'is empty');
define('_MUST_MAKE_AT_LEAST', 'must contain at least' );
define('_CHARACTER', 'character');
define('_CHARACTERS', 'characters');
define('MUST_BE_LESS_THAN', 'must not be longer than');
define('_WRONG_FORMAT', 'is not well formated');
define('_WELCOME', 'Welcome to Maarch Framework 3.0!');
define('_HELP', 'Help');
define('_SEARCH_ADV_SHORT', 'Advanced search');
define('_RESULTS', 'Results');
define('_USERS_LIST_SHORT', 'User list');
define('_MODELS_LIST_SHORT', 'Template list');
define('_GROUPS_LIST_SHORT', 'Group list');
define('_DEPARTMENTS_LIST_SHORT', 'Service list');
define('_BITMASK', 'Bitmask parameter');
define('_DOCTYPES_LIST_SHORT', 'Type list');
define('_BAD_MONTH_FORMAT', 'The month is incorrect');
define('_BAD_DAY_FORMAT', 'The day is incorrect');
define('_BAD_YEAR_FORMAT', 'The year incorrect');
define('_BAD_FEBRUARY', 'February has 29 days or less');
define('_CHAPTER_SHORT', 'Chapt. ');
define('_PROCESS_SHORT', 'Processing');
define('_CARD', 'form');

/************************* First login ***********************************/
define('_MODIFICATION_PSW', 'Modifying Password');
define('_YOUR_FIRST_CONNEXION', 'Welcome to March Framework!<br /> This is your first connexion to the application.');
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
define('_VIEW_HISTORY_BATCH2', 'View logs of batchs');
define('_INDEX_FILE', 'Add a document');
define('_WORDING', 'Label');
define('_COLLECTION', 'Collection');
define('_VIEW_TREE_DOCTYPES', 'Folder organisation tree.');
define('_VIEW_TREE_DOCTYPES_DESC', 'View the visualisation tree (type of folders, structure, sub-folders and type of documents)');
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
define('_ARCHI_EXP', 'Folders, subfolders and document types');


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
define('_DELETE_GROUPS', 'Delete group(s)');
define('_ADD_TO_GROUP', 'Add a group');
define('_CHOOSE_PRIMARY_GROUP', 'Choose as primary group');
define('_USER_BELONGS_NO_GROUP', 'The user does not belong to any group');
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

define('_DOCTYPE_MODIFICATION', 'Modifying a document type');
define('_DOCTYPE_CREATION', 'Adding a document type');

define('_MODIFY_DOCTYPE', 'Confirm changes');
define('_ATTACH_SUBFOLDER', 'Attach to subfolder');
define('_CHOOSE_SUBFOLDER', 'Select a subfolder');
define('_MANDATORY_FOR_COMPLETE', 'Mandatory for a folder to be complete');
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
define('_ITERATIVE', 'Itérative');

define('_MASTER_TYPE', 'Master doc type');

/************** structures : Liste + Formulaire**************/
define('_STRUCTURE_LIST', 'Structure list');
define('_STRUCTURES', 'structures');
define('_STRUCTURE', 'Structure');
define('_ALL_STRUCTURES', 'All structures');

define('_THE_STRUCTURE', 'the structure');
define('_STRUCTURE_MODIF', 'Modifying the structure');
define('_ID_STRUCTURE_PB', 'A problem occurs with the id of the structure');
define('_NEW_STRUCTURE_ADDED', 'Adding a new structure');
define('_NEW_STRUCTURE', 'New structure');
define('_DESC_STRUCTURE_MISSING', 'The description of the structure is missing');
define('_STRUCTURE_DEL', 'Deletion of the structure');
define('_DELETED_STRUCTURE', 'Structure deleted');

/************** sous-dossiers : Liste + Formulaire**************/
define('_SUBFOLDER_LIST', 'Sub-folder list');
define('_SUBFOLDERS', 'sub-folders');
define('_ALL_SUBFOLDERS', 'All sub-folders');
define('_SUBFOLDER', 'Sub-folder');

define('_ADD_SUBFOLDER', 'Add a new subfolder');
define('_THE_SUBFOLDER', 'The subfolder');
define('_SUBFOLDER_MODIF', 'Modify a subfolder');
define('_SUBFOLDER_CREATION', 'Adding a sub-folder');
define('_SUBFOLDER_ID_PB', 'A proble occurs with the id of the subfolder');
define('_SUBFOLDER_ADDED', 'Adding a sub-folder');
define('_NEW_SUBFOLDER', 'New sub-folder');
define('_STRUCTURE_MANDATORY', 'A structure is mandatory');
define('_SUBFOLDER_DESC_MISSING', 'The description of the sub-folder is missing');

define('_ATTACH_STRUCTURE', 'Attach to a structure');
define('_CHOOSE_STRUCTURE', 'Choose a structure');

define('_DEL_SUBFOLDER', 'delete a sub-folder');
define('_SUBFOLDER_DELETED', 'Sub-folder deleted');


/************** Status **************/

define('_STATUS_LIST', 'Status list');
define('_ADD_STATUS', 'Add a new status');
define('_ALL_STATUS', 'All status');
define('_STATUS_PLUR', 'statuses');
define('_STATUS_SING', 'status');

define('_STATUS_DELETED', 'Delete status');
define('_DEL_STATUS', 'Status deleted');
define('_MODIFY_STATUS', 'Modify statut');
define('_STATUS_ADDED','Status added');
define('_STATUS_MODIFIED','Status modified');
define('_NEW_STATUS', 'New status');
define('_IS_SYSTEM', 'System');
define('_CAN_BE_SEARCHED', 'Appear in search form');
define('_THE_STATUS', 'The status ');
define('_ADMIN_STATUS', 'Statuses');
define('_ADMIN_STATUS_DESC', 'Manage the available statuses for documents in this application');
/************* Actions **************/

define('_ACTION_LIST', 'Actions list');
define('_ADD_ACTION', 'Add a new action');
define('_ALL_ACTIONS', 'Alls actions');
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
define('_HISTORY_BATCH_TITLE', 'Events log of batchs');
define('_HISTORY', 'Log');
define('_HISTORY_BATCH', 'Log of batch');
define('_BATCH_NAME', 'Batch name');
define('_CHOOSE_BATCH', 'Choose batch');
define('_BATCH_ID', 'Batch id');
define('_TOTAL_PROCESSED', 'Total processed');
define('_TOTAL_ERRORS', 'Total errors');
define('_ONLY_ERRORS', 'Only with errors');
define('_INFOS', 'Infos');

/************** Admin de l'architecture  (plan de classement) **************/
define('_ADMIN_ARCHI', 'Administration of document sorting trees');
define('_MANAGE_STRUCTURE', 'Manage folders');
define('_MANAGE_STRUCTURE_DESC', 'Manage folders. They are the highest element of the hierarchy. If the "Folder" module is enabled, you can attach a folder tyupe to a sorting tree.');
define('_MANAGE_SUBFOLDER', 'Manage sub-folders');
define('_MANAGE_SUBFOLDER_DESC', 'Manage su-foldsers in folders.');
define('_ARCHITECTURE', 'sorting tree');

/************************* Messages d'erreurs ***********************************/
define('_MORE_INFOS', 'Contact your admin for more information ');
define('_ALREADY_EXISTS', 'already exists!');

// class usergroups
define('_NO_GROUP', 'The group does not exist !');
define('_NO_SECURITY_AND_NO_SERVICES', 'has no defined security and no service');
define('_GROUP_ADDED', 'Ne group added');
define('_SYNTAX_ERROR_WHERE_CLAUSE', 'error in the WHERE clause syntax');
define('_GROUP_UPDATED', 'Group modified');
define('_AUTORIZED_GROUP', 'Group enabled');
define('_SUSPENDED_GROUP', 'Groupdisabled');
define('_DELETED_GROUP', 'Group deleted');
define('_GROUP_UPDATE', 'Modifying a group');
define('_GROUP_AUTORIZATION', 'Enabling a group');
define('_GROUP_SUSPENSION', 'Disabling a group');
define('_GROUP_DELETION', 'Deleting a group');
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
define('_USER_DELETION', 'Deleting the user');
define('_USER_AUTORIZATION', 'Enabling the user');
define('_USER_SUSPENSION', 'Disabling the user');
define('_USER_UPDATED', 'User modified');
define('_USER_UPDATE', 'Modifying an user');
define('_USER_ADDED', 'New user added');
define('_NO_PRIMARY_GROUP', 'No primary group selected!');
define('_THE_USER', 'The user ');
define('_USER_ID', 'The id of the user');
define('_MY_INFO', 'My account');


//class types
define('_UNKNOWN_PARAM', 'Unknown parameters');
define('_DOCTYPE_UPDATED', 'Document type modified');
define('_DOCTYPE_UPDATE', 'Modifying a document type');
define('_DOCTYPE_ADDED', 'New document type added');
define('_DELETED_DOCTYPE', 'Document type deleted');
define('_DOCTYPE_DELETION', 'deleting a document type');
define('_THE_DOCTYPE', 'the document type ');
define('_THE_WORDING', 'the label ');
define('_THE_TABLE', 'The table ');
define('_PIECE_TYPE', "type of file");

//class db
define('_CONNEXION_ERROR', 'An error occurs while connecting');
define('_SELECTION_BASE_ERROR', 'An error occurs while selecting the table');
define('_QUERY_ERROR', 'An error occurs while executing the querry');
define('_CLOSE_CONNEXION_ERROR', 'An error occurs while while closing the connectionl');
define('_ERROR_NUM', 'Error num.');
define('_HAS_JUST_OCCURED', 'just occured');
define('_MESSAGE', 'Message');
define('_QUERY', 'Query');
define('_LAST_QUERY', 'Latest query');

//Autres
define('_NO_GROUP_SELECTED', 'No group selected');
#define('_NOW_LOG_OUT', 'You are logged out');
define('_DOC_NOT_FOUND', 'The document cannot be found');
define('_DOUBLED_DOC', 'Duplicate problem');
define('_NO_DOC_OR_NO_RIGHTS', 'This document does not exist, or you do not have sufficient right to view it.');
define('_INEXPLICABLE_ERROR', 'An unattended error occurs');
define('_TRY_AGAIN_SOON', 'Please try again in a few seconds');
define('_NO_OTHER_RECIPIENT', 'There is no other recipient for this document');
define('_WAITING_INTEGER', 'Entier attendu');

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
define('_FOLDER', 'Folder');
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
define('_EDIT_WITH_ASSISTANT', 'Clic here to edit the WHERE clause in assistant mode');
define('_VALID_THE_WHERE_CLAUSE', 'Clic here to validate the WHERE clause');
define('_DELETE_SHORT', 'Delete');
define('_CHOOSE_ANOTHER_SUBFOLDER', 'Select another subfolder'); 
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

define('_REDIRECT_TO', 'Redirect vers');
define('_NO_STRUCTURE_ATTACHED', 'This type of documents is not attached to any structure');


///// Credits
define('_MAARCH_CREDITS', 'About Maarch&nbsp;');
define('_CR_LONGTEXT_INFOS', '<p>Maarch Framework 3 is a <b>DMS Platform</b>. It addresses most of the needs an organisation cas express to the operative management of its content. A vast majority of it componants are released under the terms of the open source license GNU GPLv3. As a result, the total cost of ownership makes it affordable for any kind of organisation to use it (public sector, private companies associations, etc.).</p><p>Maarch Framework has been designed by two consultants whose experience in in records management and ADF sums up to 20 years. Thus this product <b>garanties a level of stability, integrity and performance</b> one can expect for that type of product. The architecture of the software has been particularly designed so that it can run on standard servers.</p><p>Maarch is developped in PHP5 object. It is compatiblewith 4 database engines: MySQL, PostgreSQL, SQL Server and soon Oracle.</p><p>Maarch is <b>fully modular</b>: all fonctiunnalities are grouped in modules. The modules expose services, which can be enabled or disabled according to the user functional profile. A trained engineer can add or replace an existing module without modifying thr core of the program.</p><p>Maarch offers a global model and necessary tools to <b>acquire, manage, archive and restitute production document streams</b>.<p>');

define('_CLOSED', 'Approved');
?>
