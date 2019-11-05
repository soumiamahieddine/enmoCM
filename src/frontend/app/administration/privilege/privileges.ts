const lang : any = [];
export const PRIVILEGES = [
    //MENU
    {
        "id"        : "admin",
        "label2"     : "_ADMIN",
        "label"     : lang.administration,
        "comment"   : lang.administration,
        "route"     : "/administration",
        "type"      : "menu",
        "style"     : "fa fa-cogs",
        "angular"   : true
    },
    {
        "id"        : "adv_search_mlb",
        "label2"     : "_ADV_SEARCH_MLB",
        "label"     : lang.search,
        "comment"   : lang.search,
        "route"     : "index.php?page=search_adv&dir=indexing_searching",
        "type"      : "menu",
        "style"     : "fa fa-search",
        "angular"   : false
    },
    {
        "id"        : "entities_print_sep_mlb",
        "label2"     : "_ENTITIES_PRINT_SEP_MLB",
        "label"     : lang.entitiesSeparator,
        "comment"   : lang.entitiesSeparator,
        "route"     : "/separators/print",
        "type"      : "menu",
        "style"     : "fa fa-print",
        "angular"   : true
    },
    {
        "id"        : "reports",
        "label2"     : "_REPORTS",
        "comment2"   : "_REPORTS_DESC",
        "label"     : lang.reports,
        "comment"   : lang.reports,
        "route"     : "index.php?page=reports&module=reports",
        "type"      : "menu",
        "style"     : "fa fa-chart-area",
        "angular"   : false
    },
    {
        "id"        : "save_numeric_package",
        "label2"     : "_SAVE_NUMERIC_PACKAGE",
        "label"     : lang.saveNumericPackage,
        "comment"   : lang.saveNumericPackage,
        "route"     : "/saveNumericPackage",
        "type"      : "menu",
        "style"     : "fa fa-file-archive",
        "angular"   : true
    },
    //ADMINISTRATION
    {
        "id"        : "admin_users",
        "label2"     : "_USERS",
        "label"     : lang.users,
        "comment2"   : "_ADMIN_USERS_DESC",
        "comment"   : lang.adminUsersDesc,
        "route"     : "/administration/users",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-user",
        "angular"   : true
    },
    {
        "id"        : "admin_groups",
        "label2"     : "_GROUPS",
        "label"     : lang.groups,
        "comment2"   : "_ADMIN_GROUPS_DESC",
        "comment"   : lang.adminGroupsDesc,
        "route"     : "/administration/groups",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-users",
        "angular"   : true
    },
    {
        "id"        : "manage_entities",
        "label2"     : "_ENTITIES",
        "label"     : lang.entities,
        "comment2"   : "_MANAGE_ENTITIES_DESC",
        "comment"   : lang.adminEntitiesDesc,
        "route"     : "/administration/entities",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-sitemap",
        "angular"   : true
    },
    {
        "id"        : "admin_listmodels",
        "label2"     : "_LISTMODELS_WORKFLOW",
        "label"     : lang.workflowModels,
        "comment2"   : "_MANAGE_LISTMODELS_WORKFLOW_DESC",
        "comment"   : lang.adminWorkflowModelsDesc,
        "route"     : "/administration/diffusionModels",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-th-list",
        "angular"   : true
    },
    {
        "id"        : "admin_architecture",
        "label2"     : "_DOCTYPES",
        "label"     : lang.documentTypes,
        "comment2"   : "_ADMIN_DOCTYPES_DESC",
        "comment"   : lang.adminDocumentTypesDesc,
        "route"     : "/administration/doctypes",
        "type"      : "admin",
        "unit"      : "classement",
        "style"     : "fa fa-suitcase",
        "angular"   : true
    },
    {
        "id"        : "admin_tag",
        "label2"     : "_TAGS",
        "label"     : lang.tags,
        "comment2"   : "_ADMIN_TAGS_DESC",
        "comment"   : lang.adminTagsDesc,
        "route"     : "index.php?page=manage_tag_list_controller&module=tags",
        "type"      : "admin",
        "unit"      : "classement",
        "style"     : "fa fa-tags",
        "angular"   : false
    },
    {
        "id"        : "admin_baskets",
        "label2"     : "_BASKETS",
        "label"     : lang.baskets,
        "comment2"   : "_ADMIN_BASKETS_DESC",
        "comment"   : lang.adminBasketsDesc,
        "route"     : "/administration/baskets",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-inbox",
        "angular"   : true
    },
    {
        "id"        : "admin_status",
        "label2"     : "_STATUSES",
        "label"     : lang.statuses,
        "comment2"   : "_ADMIN_STATUSES_DESC",
        "comment"   : lang.statusesAdmin,
        "route"     : "/administration/statuses",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-check-circle",
        "angular"   : true
    },
    {
        "id"        : "admin_actions",
        "label2"    : "_ACTIONS",
        "label"     : lang.actions,
        "comment2"  : "_ADMIN_ACTIONS_DESC",
        "comment"   : lang.actionsAdmin,
        "route"     : "/administration/actions",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-exchange-alt",
        "angular"   : true
    },
    {
        "id"        : "admin_contacts",
        "label2"    : "_CONTACTS",
        "label"     : lang.contacts,
        "comment2"  : "_ADMIN_CONTACTS_DESC",
        "comment"   : lang.contactsAdmin,
        "route"     : "index.php?page=admin_contacts&admin=contacts",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-book",
        "angular"   : false
    },
    {
        "id"        : "admin_priorities",
        "label2"    : "_PRIORITIES",
        "label"     : lang.prioritiesAlt,
        "comment"   : lang.prioritiesAlt,
        "comment2"   : "_PRIORITIES",
        "route"     : "/administration/priorities",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-clock",
        "angular"   : true
    },
    {
        "id"        : "admin_templates",
        "label2"    : "_TEMPLATES",
        "label"     : lang.templates,
        "comment2"  : "_ADMIN_TEMPLATES_DESC",
        "comment"   : lang.templatesAdmin,
        "route"     : "/administration/templates",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-file-alt",
        "angular"   : true
    },
    {
        "id"        : "admin_indexing_models",
        "label2"    : "_ADMIN_INDEXING_MODELS",
        "label"     : lang.indexingModels,
        "comment"   : lang.indexingModels,
        "comment2"  : "_ADMIN_INDEXING_MODELS",
        "route"     : "/administration/indexingModels",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-wpforms",
        "angular"   : true
    },
    {
        "id"        : "admin_custom_fields",
        "label2"    : "_ADMIN_CUSTOM_FIELDS",
        "label"     : lang.customFieldsAdmin,
        "comment"   : lang.customFieldsAdmin,
        "comment2"   : "_ADMIN_CUSTOM_FIELDS",
        "route"     : "/administration/customFields",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-code",
        "angular"   : true
    },
    {
        "id"        : "admin_notif",
        "label2"    : "_NOTIFICATIONS",
        "label"     : lang.notifications,
        "comment2"  : "_ADMIN_NOTIFICATIONS_DESC",
        "comment"   : lang.notificationsAdmin,
        "route"     : "/administration/notifications",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-bell",
        "angular"   : true
    },
    {
        "id"        : "update_status_mail",
        "label2"    : "_UPDATE_STATUS_MAIL",
        "label"     : lang.updateStatus,
        "comment"   : lang.updateStatus,
        "comment2"  : "_UPDATE_STATUS_MAIL",
        "route"     : "/administration/update-status",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-envelope-square",
        "angular"   : true
    },
    {
        "id"        : "admin_docservers",
        "label2"    : "_DOCSERVERS",
        "label"     : lang.docservers,
        "comment2"  : "_ADMIN_DOCSERVERS_DESC",
        "comment"   : lang.docserversAdmin,
        "route"     : "/administration/docservers",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-hdd",
        "angular"   : true
    },
    {
        "id"        : "admin_parameters",
        "label2"    : "_PARAMETERS",
        "label"     : lang.parameters,
        "comment"   : lang.parameters,
        "comment2"  : "_PARAMETERS",
        "route"     : "/administration/parameters",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-wrench",
        "angular"   : true
    },
    {
        "id"        : "admin_password_rules",
        "label2"    : "_SECURITIES",
        "label"     : lang.securities,
        "comment"   : lang.securities,
        "comment2"  : "_SECURITIES",
        "route"     : "/administration/securities",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-lock",
        "angular"   : true
    },
    {
        "id"        : "admin_email_server",
        "label2"    : "_EMAILSERVER_PARAM",
        "label"     : lang.emailServerParam,
        "comment"   : lang.emailServerParamDesc,
        "comment2"  : "_EMAILSERVER_PARAM_DESC",
        "route"     : "/administration/sendmail",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-mail-bulk",
        "angular"   : true
    },
    {
        "id"        : "admin_shippings",
        "label2"    : "_MAILEVA_ADMIN",
        "label"     : lang.mailevaAdmin,
        "comment"   : lang.mailevaAdminDesc,
        "comment2"  : "_MAILEVA_ADMIN_DESC",
        "route"     : "/administration/shippings",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-shipping-fast",
        "angular"   : true
    },
    {
        "id"        : "admin_reports",
        "label2"    : "_REPORTS",
        "label"     : lang.reports,
        "comment"   : lang.reportsAdmin,
        "comment2"  : "_ADMIN_REPORTS_DESC",
        "route"     : "/administration/reports",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-chart-area",
        "angular"   : true
    },
    {
        "id"        : "view_history",
        "label2"    : "_HISTORY",
        "label"     : lang.history,
        "comment2"  : "_ADMIN_HISTORY_DESC",
        "comment"   : lang.viewHistoryDesc,
        "route"     : "/administration/history",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-history",
        "angular"   : true
    },
    {
        "id"        : "view_history_batch",
        "label2"    : "_HISTORY_BATCH",
        "label"     : lang.historyBatch,
        "comment"   : lang.historyBatchAdmin,
        "comment2"  : "_ADMIN_HISTORY_BATCH_DESC",
        "route"     : "/administration/history",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-history",
        "angular"   : true
    },
    {
        "id"        : "admin_update_control",
        "label2"    : "_UPDATE_CONTROL",
        "label"     : lang.updateControl,
        "comment"   : lang.updateControlDesc,
        "comment2"  : "_ADMIN_UPDATE_CONTROL_DESC",
        "route"     : "/administration/versions-update",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-sync",
        "angular"   : true
    },
    //USE
    {
        "id"        : "view_doc_history",
        "label2"    : "_VIEW_DOC_HISTORY",
        "label"     : lang.viewDocHistory,
        "comment2"  : "_VIEW_HISTORY_DESC",
        "comment"   : lang.viewHistoryDesc,
        "type"      : "use"
    },
    {
        "id"        : "view_full_history",
        "label2"    : "_VIEW_FULL_HISTORY",
        "label"     : lang.viewFullHistory,
        "comment2"  : "_VIEW_FULL_HISTORY_DESC",
        "comment"   : lang.viewFullHistoryDesc,
        "type"      : "use"
    },
    {
        "id"        : "edit_document_in_detail",
        "label2"    : "_EDIT_DOCUMENT_IN_DETAIL",
        "label"     : lang.editDocumentInDetail,
        "comment2"  : "_EDIT_DOCUMENT_IN_DETAIL_DESC",
        "comment"   : lang.editDocumentInDetailDesc,
        "type"      : "use"
    },
    {
        "id"        : "delete_document_in_detail",
        "label2"    : "_DELETE_DOCUMENT_IN_DETAIL",
        "comment2"  : "_DELETE_DOCUMENT_IN_DETAIL",
        "label"     : lang.deleteDocumentInDetail,
        "comment"   : lang.deleteDocumentInDetail,
        "type"      : "use"
    },
    {
        "id"        : "manage_tags_application",
        "label2"    : "_MANAGE_TAGS_IN_APPLICATION",
        "label"     : lang.manageTagsInApplication,
        "comment2"  : "_MANAGE_TAGS_IN_APPLICATION_DESC",
        "comment"   : lang.manageTagsInApplicationDesc,
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_indexing",
        "label2"    : "_UPDATE_DIFFUSION_WHILE_INDEXING",
        "label"     : lang.updateDiffusionWhileIndexing,
        "comment"     : lang.updateDiffusionWhileIndexing,
        "comment2"   : "_UPDATE_DIFFUSION_WHILE_INDEXING",
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_except_recipient_indexing",
        "label2"    : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_INDEXING",
        "label"     : lang.updateDiffusionExceptRecipientWhileIndexing,
        "comment2"  : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_INDEXING",
        "comment"   : lang.updateDiffusionExceptRecipientWhileIndexing,
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_details",
        "label2"    : "_UPDATE_DIFFUSION_WHILE_DETAILS",
        "label"     : lang.updateDiffusionWhileDetails,
        "comment"   : lang.updateDiffusionWhileDetails,
        "comment2"  : "_UPDATE_DIFFUSION_WHILE_DETAILS",
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_except_recipient_details",
        "label2"    : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_DETAILS",
        "comment2"  : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_DETAILS",
        "label"     : lang.updateDiffusionExceptRecipientWhileDetails,
        "comment"   : lang.updateDiffusionExceptRecipientWhileDetails,
        "type"      : "use"
    },
    {
        "id"        : "sendmail",
        "label2"    : "_SENDMAIL_COMMENT",
        "label"     : lang.sendmail,
        "comment2"  : "_SENDMAIL_COMMENT",
        "comment"   : lang.sendmail,
        "type"      : "use"
    },
    {
        "id"        : "use_mail_services",
        "label2"    : "_USE_MAIL_SERVICES",
        "label"     : lang.useMailServices,
        "comment2"  : "_USE_MAIL_SERVICES_DESC",
        "comment"   : lang.useMailServices,
        "type"      : "use"
    },
    {
        "id"        : "view_documents_with_notes",
        "label2"    : "_VIEW_DOCUMENTS_WITH_NOTES",
        "label"     : lang.viewDocumentsWithNotes,
        "comment2"  : "_VIEW_DOCUMENTS_WITH_NOTES_DESC",
        "comment"   : lang.viewDocumentsWithNotesDesc,
        "type"      : "use"
    },
    {
        "id"        : "view_technical_infos",
        "label2"    : "_VIEW_TECHNICAL_INFORMATIONS",
        "label"     : lang.viewTechnicalInformation,
        "comment2"  : "_VIEW_TECHNICAL_INFORMATIONS",
        "comment"   : lang.viewTechnicalInformation,
        "type"      : "use"
    },
    {
        "id"        : "config_avis_workflow",
        "label2"    : "_CONFIG_AVIS_WORKFLOW",
        "label"     : lang.configAvisWorkflow,
        "comment"   : lang.configAvisWorkflowDesc,
        "comment2"  : "_CONFIG_AVIS_WORKFLOW_DESC",
        "type"      : "use"
    },
    {
        "id"        : "config_avis_workflow_in_detail",
        "label2"    : "_CONFIG_AVIS_WORKFLOW_IN_DETAIL",
        "label"     : lang.configAvisWorkflowInDetail,
        "comment2"  : "_CONFIG_AVIS_WORKFLOW_IN_DETAIL_DESC",
        "comment"   : lang.configAvisWorkflowInDetailDesc,
        "type"      : "use"
    },
    {
        "id"        : "avis_documents",
        "label2"    : "_AVIS_ANSWERS",
        "label"     : lang.avisAnswer,
        "comment2"  : "_AVIS_ANSWERS_DESC",
        "comment"   : lang.avisAnswerDesc,
        "type"      : "use"
    },
    {
        "id"        : "config_visa_workflow",
        "label2"     : "_CONFIG_VISA_WORKFLOW",
        "label"     : lang.configVisaWorkflow,
        "comment"   : lang.configVisaWorkflowDesc,
        "comment2"  : "_CONFIG_VISA_WORKFLOW_DESC",
        "type"      : "use"
    },
    {
        "id"        : "config_visa_workflow_in_detail",
        "label2"    : "_CONFIG_VISA_WORKFLOW_IN_DETAIL",
        "label"     : lang.configVisaWorkflowInDetail,
        "comment"   : lang.configVisaWorkflowInDetailDesc,
        "comment2"  : "_CONFIG_VISA_WORKFLOW_IN_DETAIL_DESC",
        "type"      : "use"
    },
    {
        "id"        : "visa_documents",
        "label2"    : "_VISA_ANSWERS",
        "label"     : lang.visaAnswers,
        "comment2"   : "_VISA_ANSWERS_DESC",
        "comment"   : lang.visaAnswersDesc,
        "type"      : "use"
    },
    {
        "id"        : "sign_document",
        "label2"    : "_SIGN_DOCS",
        "label"     : lang.signDocs,
        "comment"   : lang.signDocs,
        "comment2"  : "_SIGN_DOCS",
        "type"      : "use"
    },
    {
        "id"        : "modify_visa_in_signatureBook",
        "label2"    : "_MODIFY_VISA_IN_SIGNATUREBOOK",
        "label"     : lang.modifyVisaInSignatureBook,
        "comment"   : lang.modifyVisaInSignatureBookDesc,
        "comment2"  : "_MODIFY_VISA_IN_SIGNATUREBOOK_DESC",
        "type"      : "use"
    },
    {
        "id"        : "use_date_in_signBlock",
        "label2"    : "_USE_DATE_IN_SIGNBLOCK",
        "label"     : lang.useDateInSignBlock,
        "comment"   : lang.useDateInSignBlockDesc,
        "comment2"  : "_USE_DATE_IN_SIGNBLOCK_DESC",
        "type"      : "use"
    },
    {
        "id"        : "print_folder_doc",
        "label2"    : "_PRINT_FOLDER_DOC",
        "label"     : lang.printFolderDoc,
        "comment"   : lang.printFolderDoc,
        "comment2"  : "_PRINT_FOLDER_DOC",
        "type"      : "use"
    }
];
