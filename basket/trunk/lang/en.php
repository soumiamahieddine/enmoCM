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
define('_ADMIN_BASKETS', 'Basket');
define('_ADMIN_BASKETS_DESC', 'Define basket contents and associate them with user groups. List available redirection for a user group. Associate the basket with result pages.');
define('_USE_BASKETS', 'Use baskets');
define('_DIFFUSION_LIST', 'Mailing list');

//class basket
define('_BASKET', 'Basket');
define('_THE_BASKET', 'The basket ');
define('_BASKETS_COMMENT', 'Baskets');
define('_THE_ID', 'the ID ');
define('_THE_DESC', 'The description ');
define('_BELONGS_TO_NO_GROUP', 'is not associated with any group');
define('_SYSTEM_BASKET_MESSAGE', 'This is a system basket. You can\'t modify the table or the where clause. They are only given as information.');
define('_BASKET_MISSING', 'The basket doesn\'t exists');
define('_BASKET_UPDATED', 'Basket updated');
define('_BASKET_UPDATE', 'Basket modified');
define('_BASKET_ADDED', 'Basket added');
define('_DELETED_BASKET', 'Basket deleted');
define('_BASKET_DELETION', 'Deleting basket');
define('_BASKET_AUTORIZATION', 'Enabling basket');
define('_BASKET_SUSPENSION', 'Disabling basket');
define('_AUTORIZED_BASKET', 'Enabled basket');
define('_SUSPENDED_BASKET', 'Disabled basket');
define('_NO_BASKET_DEFINED_FOR_YOU', 'No basket defined for this user');


define('_BASKETS_LIST', 'Basket list');

/////// frame corbeilles
define('_BASKETS', 'Baskets');
define('_CHOOSE_BASKET', 'Select a basket');
define('_PROCESS_BASKET', 'Mail to Process');
define('_VALIDATION_BASKET', 'Mail to validate');

define('_MANAGE_BASKETS', 'Baskets');
define('_MANAGE_BASKETS_APP', 'Manage baskets');

/************** Corbeille : Liste + Formulaire**************/
define('_ALL_BASKETS', 'All baskets');
define('_BASKET_LIST', 'Basket list');
define('_ADD_BASKET', 'Add a basket');
define('_BASKET_ADDITION', 'Add a basket');
define('_BASKET_MODIFICATION', 'Modify a basket');
define('_BASKET_VIEW', 'View on the table');
define('_MODIFY_BASKET', 'Modify a baskete');
define('_ADD_A_NEW_BASKET', 'Add a basket');
define('_ADD_A_GROUP_TO_BASKET', 'Associate a group to the basket');
define('_DEL_GROUPS', 'Remove groups');
define('_BASKET_NOT_USABLE', 'No group associated (the basket cannot be used)');
define('_ASSOCIATED_GROUP', 'Groups associated to the basket');
define('_BASKETS', 'Basket(s)');

define('_TITLE_GROUP_BASKET', 'Associate a group to the basket');
define('_ADD_TO_BASKET', 'Associate a group');
define('_TO_THE_GROUP', 'to the basket');
define('_ALLOWED_ACTIONS', 'Enabled actions');
define('_SERVICES_BASKETS', 'Department basket');
define('_USERGROUPS_BASKETS', 'Usergroup basket');
define('_BASKET_RESULT_PAGE', 'Result list');
define('_ADD_THIS_GROUP', 'Add a group');
define('_MODIFY_THIS_GROUP', 'Modify the group');
define('_DEFAULT_ACTION_LIST', 'Default action on the list<br/><i>(clic on the line)');
define('_NO_ACTION_DEFINED', 'No define action');

//BASKETS
define('_PROCESS_FOLDER_LIST', 'List of processed files');
define('_INCOMPLETE_FOLDERS_LIST', 'List of incomplete files');
define('_WAITING_VAL_LIST', 'List of document awaiting approval');
define('_WAITING_QUAL_LIST', 'List of document awaiting qualification');
define('_WAITING_DISTRIB_LIST', 'List of mail awaiting distribution');
define('_NO_REDIRECT_RIGHT', 'You have no redirection right on this basket');
define('_CLICK_LINE_BASKET1', 'Click on a line to qualify a document');

//ENTITY
define('_SELECT_ENTITY', 'Select a department');
define('_ENTITY', 'Department');
define('_LABEL', 'Label');
define('_THE_ENTITY', 'The department');
define('_ENTITIES', 'Departments');
define('_ALL_ENTITIES', 'All department');
define('_ENTITY_LIST', 'List of departments');
define('_SELECTED_ENTITIES', 'Selected departments');
define('_CHOOSE_ENTITY', 'Select a department');
define('_MUST_CHOOSE_AN_ENTITY', 'You must select a department');
define('_ADMIN_ENTITIES', 'Manage departments');
define('_ADMIN_ENTITIES_DESC', 'Manage departments and associated mailing lists');
define('_ENTITIES_LIST', 'List of departments');
define('_ENTITY_ADDITION', 'Add a department');
define('_ENTITY_MODIFICATION', 'Modify a department');
define('_ENTITY_MISSING', 'The department does not exist');
define('_ENTITY_DELETION', 'Delete an entity');
define('_ENTITY_ADDITION', 'Add a department');
define('_ENTITY_ADDED', 'Department added');
define('_ENTITY_UPDATED', 'Department modified');
define('_ENTITY_BASKETS','Available departments');
define('_PRINT_ENTITY_SEP','Print the barcode separator');
define('_PRINT_SEP_WILL_BE_START','Print will begin in a few seconds');
define('_PRINT_SEP_TITLE','DOCUMENT SEPARATOR');
define('_INGOING_UP','INCOMING');
define('_ONGOING_UP','OUTGOING');

//DIFFUSION LIST
define('_CHOOSE_DEPARTMENT_FIRST', 'You must select a department befor you can edit the mailing list');
define('_NO_LIST_DEFINED__FOR_THIS_MAIL', 'No mailing list defined for this mail');
define('_NO_LIST_DEFINED__FOR_THIS_DEPARTMENT', 'No mailing list defined for this department');
define('_NO_LIST_DEFINED', 'No mailing list');
define('_REDIRECT_MAIL', 'Redirect document');
define('_DISTRIBUTE_MAIL', 'Distribute mail');
define('_REDIRECT_TO_OTHER_DEP', 'Redirect to another department');
define('_REDIRECT_TO_USER', 'Rediriger to another user');
define('_LETTER_SERVICE_REDIRECT','Redirect to sender department');
define('_LETTER_SERVICE_REDIRECT_VALIDATION','Do you really want to redirect this document to sender department?');
define('_DOC_REDIRECT_TO_SENDER_ENTITY', 'Document redirected to sender department');
define('_DOC_REDIRECT_TO_ENTITY', 'Document redirected to the department ');
define('_DOC_REDIRECT_TO_USER', 'Document redirected to the department the user');

define('_WELCOME_DIFF_LIST', 'Welcome on the mailing list editor');
define('_START_DIFF_EXPLANATION', 'To begin distribution, select a user or a department above.');
define('_CLICK_ON', 'click on');
define('_ADD_USER_TO_LIST_EXPLANATION', 'To add a user to the mailing list');
define('_REMOVE_USER_FROM_LIST_EXPLANATION', 'To remove a user to the mailing list');
define('_TO_MODIFY_LIST_ORDER_EXPLANATION', 'To modify the order of the users in the mailing list click on');
define('_AND', ' and ' );
define('_LINKED_DIFF_LIST', 'Associated mailing list');
define('_NO_LINKED_DIFF_LIST', 'No mailing list associated');
define('_CREATE_LIST', 'Create a mailing list');
define('_MODIFY_LIST', 'Modify the list');
define('_THE_ENTITY_DO_NOT_CONTAIN_DIFF_LIST', 'This department has no mailing list associated');

//LIST MODEL
define('_MANAGE_MODEL_LIST_TITLE', 'Manage mailing list');
define('_SORT_BY', 'Sort by');
define('_WELCOME_MODEL_LIST_TITLE', 'Welcome on the mailing list editor');
define('_MODEL_LIST_EXPLANATION1', 'To begin distribution, select a user or a department above.');
define('_VALID_LIST', 'Save the list');

//LIST
define('_COPY_LIST', 'Department in copy');
define('_PROCESS_LIST', 'Document to process');
define('_CLICK_LINE_TO_VIEW', 'Click on a line to view');
define('_CLICK_LINE_TO_PROCESS', 'Click on a line to process');

define('_REDIRECT_TO_SENDER_ENTITY', 'Redirect to sender department');
define('_CHOOSE_DEPARTMENT', 'Select a department');
define('_REDIRECTION', 'Redirection');
define('_ENTITY_UPDATE', 'Department updated');
// USER ABS
define('_MY_ABS', 'Manage my leave');
define('_MY_ABS_TXT', 'Allows you to redirect your baskets while on leave');
define('_MY_ABS_REDIRECT', 'Your mail are currently redirected to');
define('_MY_ABS_DEL', 'To cancel the redirection, click here');
define('_ADMIN_ABS', 'Manage my leaves.');
define('_ADMIN_ABS_TXT', 'Allows you to redirect this user baskets while on leave');
define('_ADMIN_ABS_REDIRECT', 'Redirection for leave enabled');
define('_ADMIN_ABS_FIRST_PART', 'Mail of');
define('_ADMIN_ABS_SECOND_PART', 'is currently redirected to ');
define('_ADMIN_ABS_THIRD_PART', '. Click here to cancel.');
define('_ACTIONS_DONE', 'Actions on');
define('_PROCESSED_MAIL', 'Processed mail');
define('_INDEXED_MAIL', 'Recorded mail');
define('_REDIRECTED_MAIL', 'Redirected mails');
define('_PROCESS_MAIL_OF', 'Mail to process for');
define('_MISSING', 'absent');
define('_BACK_FROM_VACATION', ' back');
define('_MISSING_ADVERT_TITLE','Leave management');
define('_MISSING_ADVERT_01','This account is in leave mode and its mail are currently redirected to another user.');
define('_MISSING_ADVERT_02','If you log in this account, this mode will be disabled.<br/>');
define('_MISSING_CHOOSE','Do you want to continue?');


define('_CHOOSE_PERSON_TO_REDIRECT', 'Choose the user you want to redirect this email to');
define('_CLICK_ON_THE_LINE_OR_ICON', 'click on the line or on the icone');
define('_TO_SELECT_USER', 'to select a user');

define('_DIFFUSION_DISTRIBUTION', 'Mail Distribution');
define('_VALIDATED_ANSWERS', 'DGS R&eacute;ponses valid&eacute;es');
define('_REJECTED_ANSWERS', 'DGS R&eacute;ponses rejet&eacute;es');
define('_MUST_HAVE_DIFF_LIST', 'Vous devez d&eacute;finir une liste de diffusion');


define('_ASSOCIATED_STATUS', 'Associated status');
define('_SYSTEM_ACTION', 'System action');
define('_CANNOT_MODIFY_STATUS', 'You cannot modify the status');
define('_ASSOCIATED_ACTIONS', 'No available action on the result page');
define('_NO_ACTIONS_DEFINED', 'No defined action');
define('_CONFIG', '(configure)');
define('_CONFIG_ACTION', 'Action parameters');
define('_WHERE_CLAUSE_ACTION_TEXT', 'You can define a condition to choose wether the action apprear or not with the where clause (optional):');
define('_IN_ACTION', ' in the action');

define('_TO_ENTITIES', 'Vers des services');
define('_TO_USERGROUPS', 'To usergroups');
define('_USE_IN_MASS', 'Available action in the list.');
define('_USE_ONE', 'Available action in the action page');
define('_MUST_CHOOSE_WHERE_USE_ACTION','You must select where the action can be used ');

define('_MUST_CHOOSE_DEP', 'You must select a entity!');
define('_MUST_CHOOSE_USER', 'You must select an user!');
define('_REDIRECT_TO_DEP_OK', 'Redirection vers un service effectu&eacute;e');
define('_REDIRECT_TO_USER_OK', 'Redirection vers un utilisateur effectu&eacute;e');

define('_SAVE_CHANGES', 'Save changes');
define('_VIEW_BASKETS', 'My baskets');
define('_VIEW_BASKETS_TITLE', 'My baskets');

define('_INVOICE_LIST_TO_VAL', 'Invoices to approve');
define('_POSTINDEXING_LIST', 'Documents to check');
define('_MY_BASKETS', 'My baskets');


define('_INVOICE_LIST_TO_VAL', 'Invoices to validate');
define('_POSTINDEXING_LIST', 'Documents to validate');
define('_MY_BASKETS', 'My baskets');
define('_REDIRECT_MY_BASKETS', 'Redirect the baskets');
define('_NAME', 'Name');
define('_CHOOSE_USER_TO_REDIRECT', 'You must redirect at least One basket to another user.');
define('_FORMAT_ERROR_ON_USER_FIELD', 'one of the field is incorrect. Good format is: Surname, Firstname (Identifier)');
define('_BASKETS_OWNER_MISSING', 'Basket owner is not set.');
define('_FORM_ERROR', 'An error occured while saving the form');
define('_USER_ABS', 'The user is already set on leave.');
define('_ABS_LOG_OUT', 'If you continue, the leave mode will be disabled');
define('_ABS_USER', 'User on vacation');
define('_ABSENCE', 'Absent');
define('_BASK_BACK', 'Back');

define('_CANCEL_ABS', 'Disable leave mode');
define('_REALLY_CANCEL_ABS', 'Do you really want to disable leave mode?');
define('_ABS_MODE', 'Leave management');
define('_REALLY_ABS_MODE', 'Do you want to enablme leave mode?');

define('_REDIRECT_TO_ACTION', 'Redirect to an action');
define('_DOCUMENTS_LIST', 'Simple list');
define('_DOCUMENTS_LIST_WITH_FILTERS', 'List with filters');
define('_AUTHORISED_ENTITIES', 'List of available departments');
define('_ARCHIVE_LIST', 'List of archives unit');

define('_FILTER_BY_ENTITY', 'filter by department');
define('_FILTER_BY', 'filter by');
define('_OTHER_BASKETS', 'Other baskets');


define('_BASKET_WELCOME_TXT1', 'While browsing your baskets,');
define('_BASKET_WELCOME_TXT2', 'you can click on the menu above to change basket');

?>
