const lang : any = [];
export const PRIVILEGES = [
    //MENU
    {
        "id"        : "admin",
        "label2"     : "_ADMIN", // in menu.xml
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
        "label"     : lang.reports,
        "comment"   : lang.reports,
        "route"     : "index.php?page=reports&module=reports",
        "type"      : "menu",
        "style"     : "fa fa-chart-area",
        "angular"   : false
    },
    {
        "id"        : "save_numeric_package",
        "label2"     : "_SAVE_NUMERIC_PACKAGE", // in menu.xml of sendmail module
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
        "comment"   : lang.adminEntitiesDesc,
        "route"     : "/administration/entities",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-sitemap",
        "angular"   : true
    },
    {
        "id"        : "admin_listmodels",
        "label"     : lang.workflowModels,
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
        "comment"   : lang.adminTagsDesc,
        "route"     : "index.php?page=manage_tag_list_controller&module=tags",
        "type"      : "admin",
        "unit"      : "classement",
        "style"     : "fa fa-tags",
        "angular"   : false
    },
    {
        "id"        : "admin_baskets",
        "label"     : lang.baskets,
        "comment"   : lang.adminBasketsDesc,
        "route"     : "/administration/baskets",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-inbox",
        "angular"   : true
    },
    {
        "id"        : "admin_status",
        "label"     : lang.statuses,
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
        "label"     : lang.prioritiesAlt,
        "comment"   : lang.prioritiesAlt,
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
        "comment"   : lang.templatesAdmin,
        "route"     : "/administration/templates",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-file-alt",
        "angular"   : true
    },
    {
        "id"        : "admin_indexing_models",
        "label"     : lang.indexingModels,
        "comment"   : lang.indexingModels,
        "route"     : "/administration/indexingModels",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-wpforms",
        "angular"   : true
    },
    {
        "id"        : "admin_custom_fields",
        "label"     : lang.customFieldsAdmin,
        "comment"   : lang.customFieldsAdmin,
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
        "comment"   : lang.notificationsAdmin,
        "route"     : "/administration/notifications",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-bell",
        "angular"   : true
    },
    {
        "id"        : "update_status_mail",
        "label"     : lang.updateStatus,
        "comment"   : lang.updateStatus,
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
        "comment"   : lang.docserversAdmin,
        "route"     : "/administration/docservers",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-hdd",
        "angular"   : true
    },
    {
        "id"        : "admin_parameters",
        "label"     : lang.parameters,
        "comment"   : lang.parameters,
        "route"     : "/administration/parameters",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-wrench",
        "angular"   : true
    },
    {
        "id"        : "admin_password_rules",
        "label"     : lang.securities,
        "comment"   : lang.securities,
        "route"     : "/administration/securities",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-lock",
        "angular"   : true
    },
    {
        "id"        : "admin_email_server",
        "label"     : lang.emailServerParam,
        "comment"   : lang.emailServerParamDesc,
        "route"     : "/administration/sendmail",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-mail-bulk",
        "angular"   : true
    },
    {
        "id"        : "admin_shippings",
        "label"     : lang.mailevaAdmin,
        "comment"   : lang.mailevaAdminDesc,
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
        "route"     : "/administration/reports",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-chart-area",
        "angular"   : true
    },
    {
        "id"        : "view_history",
        "label"     : lang.history,
        "comment"   : lang.viewHistoryDesc,
        "route"     : "/administration/history",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-history",
        "angular"   : true
    },
    {
        "id"        : "view_history_batch",
        "label"     : lang.historyBatch,
        "comment"   : lang.historyBatchAdmin,
        "route"     : "/administration/history",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-history",
        "angular"   : true
    },
    {
        "id"        : "admin_update_control",
        "label"     : lang.updateControl,
        "comment"   : lang.updateControlDesc,
        "route"     : "/administration/versions-update",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-sync",
        "angular"   : true
    },
    //USE
    {
        "id"        : "view_doc_history",
        "label"     : lang.viewDocHistory,
        "comment"   : lang.viewHistoryDesc,
        "type"      : "use"
    },
    {
        "id"        : "view_full_history",
        "label"     : lang.viewFullHistory,
        "comment"   : lang.viewFullHistoryDesc,
        "type"      : "use"
    },
    {
        "id"        : "edit_document_in_detail",
        "label"     : lang.editDocumentInDetail,
        "comment"   : lang.editDocumentInDetailDesc,
        "type"      : "use"
    },
    {
        "id"        : "delete_document_in_detail",
        "label"     : lang.deleteDocumentInDetail,
        "comment"   : lang.deleteDocumentInDetail,
        "type"      : "use"
    },
    {
        "id"        : "manage_tags_application",
        "label"     : lang.manageTagsInApplication,
        "comment"   : lang.manageTagsInApplicationDesc,
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_indexing",
        "label"     : lang.updateDiffusionWhileIndexing,
        "comment"     : lang.updateDiffusionWhileIndexing,
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_except_recipient_indexing",
        "label"     : lang.updateDiffusionExceptRecipientWhileIndexing,
        "comment"   : lang.updateDiffusionExceptRecipientWhileIndexing,
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_details",
        "label"     : lang.updateDiffusionWhileDetails,
        "comment"   : lang.updateDiffusionWhileDetails,
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_except_recipient_details",
        "label"     : lang.updateDiffusionExceptRecipientWhileDetails,
        "comment"   : lang.updateDiffusionExceptRecipientWhileDetails,
        "type"      : "use"
    },
    {
        "id"        : "sendmail",
        "label2"    : "_SENDMAIL_COMMENT", // in config.xml of sendmail
        "label"     : lang.sendmail,
        "comment2"  : "_SENDMAIL_COMMENT",
        "comment"   : lang.sendmail,
        "type"      : "use"
    },
    {
        "id"        : "use_mail_services",
        "label"     : lang.useMailServices,
        "comment"   : lang.useMailServices,
        "type"      : "use"
    },
    {
        "id"        : "view_documents_with_notes",
        "label"     : lang.viewDocumentsWithNotes,
        "comment"   : lang.viewDocumentsWithNotesDesc,
        "type"      : "use"
    },
    {
        "id"        : "view_technical_infos",
        "label"     : lang.viewTechnicalInformation,
        "comment"   : lang.viewTechnicalInformation,
        "type"      : "use"
    },
    {
        "id"        : "config_avis_workflow",
        "label"     : lang.configAvisWorkflow,
        "comment"   : lang.configAvisWorkflowDesc,
        "type"      : "use"
    },
    {
        "id"        : "config_avis_workflow_in_detail",
        "label"     : lang.configAvisWorkflowInDetail,
        "comment"   : lang.configAvisWorkflowInDetailDesc,
        "type"      : "use"
    },
    {
        "id"        : "avis_documents",
        "label"     : lang.avisAnswer,
        "comment"   : lang.avisAnswerDesc,
        "type"      : "use"
    },
    {
        "id"        : "config_visa_workflow",
        "label"     : lang.configVisaWorkflow,
        "comment"   : lang.configVisaWorkflowDesc,
        "type"      : "use"
    },
    {
        "id"        : "config_visa_workflow_in_detail",
        "label"     : lang.configVisaWorkflowInDetail,
        "comment"   : lang.configVisaWorkflowInDetailDesc,
        "type"      : "use"
    },
    {
        "id"        : "visa_documents",
        "label"     : lang.visaAnswers,
        "comment"   : lang.visaAnswersDesc,
        "type"      : "use"
    },
    {
        "id"        : "sign_document",
        "label"     : lang.signDocs,
        "comment"   : lang.signDocs,
        "type"      : "use"
    },
    {
        "id"        : "modify_visa_in_signatureBook",
        "label"     : lang.modifyVisaInSignatureBook,
        "comment"   : lang.modifyVisaInSignatureBookDesc,
        "type"      : "use"
    },
    {
        "id"        : "use_date_in_signBlock",
        "label"     : lang.useDateInSignBlock,
        "comment"   : lang.useDateInSignBlockDesc,
        "type"      : "use"
    },
    {
        "id"        : "print_folder_doc",
        "label"     : lang.printFolderDoc,
        "comment"   : lang.printFolderDoc,
        "type"      : "use"
    }
];
