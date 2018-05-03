<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Lang EN
 *
 * @author dev@maarch.org
 */
define('_ACTION_ADDED', 'Action added');
define('_ACTION_DELETED', 'Action deleted');
define('_ACTION_UPDATED', 'Action updated');
define('_ADD_NOTIFICATIONS', 'Notification created');
define('_ALREADY_EXISTS', 'already exists');
define('_AVIS_USER', 'For recommendation');
define('_AVIS_USER_COPY', 'On copy (recommendation)');
define('_AVIS_USER_INFO', 'For information (recommendation)');
define('_BASKET_CREATION', 'Basket creation');
define('_BASKET_GROUP_CREATION', 'Group added for basket');
define('_BASKET_GROUP_MODIFICATION', 'Group updated for basket');
define('_BASKET_GROUP_SUPPRESSION', 'Group deleted for basket');
define('_BASKET_MODIFICATION', 'Basket modification');
define('_BASKET_SUPPRESSION', 'Basket suppression');
define('_BASKET_REDIRECTION', 'Basket redirection');
define('_BASKET_REDIRECTION_SUPPRESSION', 'Basket redirection');
define('_BASKETS_SORT_MODIFICATION', 'Baskets order modification');
define('_BY_DEFAULT', 'by default');
define('_DELETE_NOTIFICATIONS', 'Notification deleted');
define('_DEST_USER', 'Recipient');
define('_DOCTYPE_FIRSTLEVEL_ADDED', 'Doctype first level added');
define('_DOCTYPE_FIRSTLEVEL_DELETED', 'Doctype first level deleted');
define('_DOCTYPE_FIRSTLEVEL_UPDATED', 'Doctype first level edited');
define('_DOCTYPE_SECONDLEVEL_ADDED', 'Doctype second level added');
define('_DOCTYPE_SECONDLEVEL_DELETED', 'Doctype second level deleted');
define('_DOCTYPE_SECONDLEVEL_UPDATED', 'Doctype second level edited');
define('_DOCUMENT_NOT_FOUND', 'Document not found');
define('_ENTITY_CREATION', 'Entity creation');
define('_ENTITY_MODIFICATION', 'Entity modification');
define('_ENTITY_SUPPRESSION', 'Entity suppression');
define('_ID_TO_DISPLAY', 'res_id');
define('_INVALID_CLAUSE', 'Clause is not valid');
define('_INVALID_REQUEST', 'Request is not valid');
define('_LIST_TEMPLATE_CREATION', 'List model creation');
define('_LIST_TEMPLATE_MODIFICATION', 'List model modification');
define('_LIST_TEMPLATE_SUPPRESSION', 'List model suppression');
define('_MODIFY_NOTIFICATIONS', 'Notification updated');
define('_MODIFY_STATUS', 'Statut updated');
define('_NOTIFICATION_SCHEDULE_UPDATED', 'Notification scheduled updated');
define('_NOTIFICATION_SCRIPT_ADDED', 'Script created');
define('_PARAMETER_CREATION', 'Parameter creation');
define('_PARAMETER_MODIFICATION', 'Parameter modification');
define('_PARAMETER_SUPPRESSION', 'Parameter suppression');
define('_PRIORITY_CREATION', 'Priority creation');
define('_PRIORITY_MODIFICATION', 'Priority modification');
define('_PRIORITY_SUPPRESSION', 'Priority suppression');
define('_PRIORITY_SORT_MODIFICATION', 'Priorities order modification');
define('_QUOTA_EXCEEDED', 'Quota exceeded');
define('_REPORT_MODIFICATION', 'Report modification');
define('_STATUS_ADDED', 'Statut added');
define('_STATUS_DELETED', 'Statut deleted');
define('_STATUS_NOT_FOUND', 'Status not found');
define('_TO_CC', 'On copy');
define('_TO_SIGN', 'For signature');
define('_UPDATE_STATUS', 'Status update');
define('_USER_ID_ALREADY_EXISTS', 'The user id already exists');
define('_USER_CREATED', 'User added');
define('_USER_UPDATED', 'User updated');
define('_USER_DELETED', 'User deleted');
define('_USER_GROUP_CREATION', 'Group added for user');
define('_USER_GROUP_MODIFICATION', 'Group updated for user');
define('_USER_GROUP_SUPPRESSION', 'Group deleted for user');
define('_USER_ENTITY_CREATION', 'Entity added for user');
define('_USER_ENTITY_MODIFICATION', 'Entity updated for user');
define('_USER_ENTITY_SUPPRESSION', 'Entity deleted for user');
define('_USER_ALREADY_LINK_GROUP', 'User is already linked to this group');
define('_USER_ALREADY_LINK_ENTITY', 'User is already linked to this entity');
define('_VISA_USER', 'For visa');
define('_WRONG_FILE_TYPE', 'This type of file is not allowed');
define('_CAN_NOT_MOVE_IN_CHILD_ENTITY', 'Parent entity must not be a subentity');
define('_UNREACHABLE_DOCSERVER', 'Unreachable docserver path');

define('_DOCUMENTS_LIST_WITH_ATTACHMENTS', 'List with filters and responses');
define('_DOCUMENTS_LIST_WITH_AVIS', 'List of documents with recommendation');
define('_DOCUMENTS_LIST_COPIES', 'List of copies');
define('_CASES_LIST', 'Cases list');
define('_DOCUMENTS_LIST_WITH_SIGNATORY', 'Documents list with signatory');
define('_FOLDERS_LIST', 'folders list');
define('_DOCTYPE_UPDATED', 'Document type updated');
define('_DOCTYPE_ADDED', 'Document type added');
define('_DOCTYPE_DELETED', 'Document type deleted');

//BEGIN ALEX
define('_SIMPLE_MAIL', 'Simple mail');
define('_EMAIL', 'Email');
define('_FAX', 'Fax');
define('_CHRONOPOST', 'Chronopost');
define('_FEDEX', 'Fedex');
define('_REGISTERED_MAIL', 'registered letter with recorded delivery');
define('_COURIER', 'Courier');
define('_NUMERIC_PACKAGE', 'Numeric package');
define('_OTHER', 'Other');
define('_SEND_SIGNED_DOCS', 'Pass signed responses');
define('_SEND_SIGNED_DOCS_DESC', 'Check if response project are signed.');
define('_SEND_TO_VISA', 'send for visa');
define('_SEND_TO_VISA_DESC', 'Check if visa circuit is setup AND if one or several responses project are linked to document.');
define('_REJECTION_WORKFLOW_PREVIOUS', 'Visa rejection - back to the previous author');
define('_REJECTION_WORKFLOW_PREVIOUS_DESC', 'Reset visa date of previous supervisor in visa circuit of document (\'process_date\' of listinstance table).');
define('_REJECTION_WORKFLOW_REDACTOR', 'Visa rejection - back to the author');
define('_REJECTION_WORKFLOW_REDACTOR_DESC', 'Reset visa date of all supervisor in visa circuit of document (\'process_date\' of listinstance table).');
define('_INTERRUPT_WORKFLOW', 'Break the visa flow');
define('_INTERRUPT_WORKFLOW_DESC', 'Update visa date of current supervisor / signatory and all next supervisors in visa circuit of document (\'process_date\' of listinstance table). Insère également un message d\'interruption sur le viseur actuel (\'process_comment\' de la table listinstance).');
define('_PROCEED_WORKFLOW', 'continue the visa flow');
define('_PROCEED_WORKFLOW_DESC', 'Update visa date of current supervisor / signatory in visa circuit of document (\'process_date\' of listinstance table).');
define('_VISA_MAIL', 'Aim the mail');
define('_VISA_MAIL_DESC', 'Open signatory book to visa / sign the document.');
define('_SEND_TO_CONTACT_WITH_MANDATORY_ATTACHMENT', 'Send to the contact with a mandatory attachment');
define('_SEND_TO_CONTACT_WITH_MANDATORY_ATTACHMENT_DESC', 'Open sendmail modal with email of contact linked to document in recipient, attachment is MANDATORY.');
define('_SEND_ATTACHMENTS_TO_CONTACT', 'Send to the contact');
define('_SEND_ATTACHMENTS_TO_CONTACT_DESC', 'Open sendmail modal with email of contact linked to document in recipient.');

define('_USERS', 'Users');
define('_ADMIN_USERS_DESC', 'Add, suspend or modify users profiles. Place the users in their affiliation groups and define their primary group.');
define('_GROUPS', 'users groups');
define('_ADMIN_GROUPS_DESC', "Ajouter, suspendre, ou modifier des groupes d'utilisateurs. Attribuer des privilèges ou des autorisations d'accès aux ressources.");
define('_ENTITIES', 'Entities');
define('_MANAGE_ENTITIES_DESC', 'Manage your organization (poles,services, etc.). Also diffusion models and visa circuit linked to services.');
define('_LISTMODELS_WORKFLOW', 'Workflow models');
define('_MANAGE_LISTMODELS_DESC', 'Manage avis and visa circuit models, which can be used in document.');
define('_DOCTYPES', 'File plan');
define('_ADMIN_DOCTYPES_DESC', 'Define the intern layout of a file (file/ sub-file/ document type). For each, define the index list to enter, and their mandatory character for the file completeness.');
define('_FILEPLANS', 'organizational file plan');
define('_ADMIN_FILEPLANS_DESC', 'organizational file plan management (Allows to manage specific file plans to an entity).');
define('_FOLDERTYPES', 'Types of folders');
define('_ADMIN_FOLDERTYPES_DESC', "Administrate folder's types. For each type, define the linked qualifiers and the mandatory folder's types for the folder completeness.");
define('_TAGS', 'Tags');
define('_ADMIN_TAGS_DESC', 'Allows to modify, erase, add or merge tags');
define('_THESAURUS', 'Thesaurus (keywords)');
define('_ADMIN_THESAURUS_DESC', "The thesaurus is a linguistic tool which allows to connect users' natural language and the language contained in the resources.");
define('_HISTORY', 'History');
define('_ADMIN_HISTORY_DESC', 'Read batch history');
define('_HISTORY_BATCH', 'Batch history');
define('_ADMIN_HISTORY_BATCH_DESC', 'Read the events history linked to the utilisation of Maarch.');
define('_UPDATE_STATUS_MAIL', 'Document status modification');
define('_DOCSERVERS', 'Storage zones');
define('_ADMIN_DOCSERVERS_DESC', 'Add, suspend or modify storage zones. Put the storage zones by kind of affiliations and define their primary group.');
define('_PARAMETERS', 'Parameters');
define('_UPDATE_CONTROL', 'Verify update');
define('_ADMIN_UPDATE_CONTROL_DESC', 'Check new tags of Maarch Courrier and update application in latest tag.');
define('_REPORTS', 'Statistiques');
define('_ADMIN_REPORTS_DESC', 'States and editions administration');
define('_STATUSES', 'Statuses');
define('_ADMIN_STATUSES_DESC', 'Create or modify status.');
define('_ACTIONS', 'Actions');
define('_ADMIN_ACTIONS_DESC', 'Create or modify actions.');
define('_CONTACTS', 'Contacts');
define('_ADMIN_CONTACTS_DESC', 'Administration of contacts');
define('_PRIORITIES', 'Priorities');
define('_BASKETS', 'Baskets');
define('_ADMIN_BASKETS_DESC', 'Define the baskets content and associate them to users groups.');
define('_NOTIFICATIONS', 'Notifications');
define('_ADMIN_NOTIFICATIONS_DESC', " Create and manage users' notifications based on application events");
define('_TEMPLATES', 'Templates');
define('_ADMIN_TEMPLATES_DESC', 'Manage templates for attachments, notifications, document generation, sendmail and notes');
