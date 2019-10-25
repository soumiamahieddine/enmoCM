export const PRIVILEGES = [
    //MENU
    {
        "id"        : "admin",
        "label"     : "_ADMIN",
        "comment"   : "_ADMIN",
        "route"     : "/administration",
        "type"      : "menu",
        "style"     : "fa fa-cogs",
        "angular"   : true
    },
    {
        "id"        : "adv_search_mlb",
        "label"     : "_ADV_SEARCH_MLB",
        "comment"   : "_ADV_SEARCH_MLB",
        "route"     : "index.php?page=search_adv&amp;dir=indexing_searching",
        "type"      : "menu",
        "style"     : "fa fa-search",
        "angular"   : false
    },
    {
        "id"        : "entities_print_sep_mlb",
        "label"     : "_ENTITIES_PRINT_SEP_MLB",
        "comment"   : "_ENTITIES_PRINT_SEP_MLB",
        "route"     : "/separators/print",
        "type"      : "menu",
        "style"     : "fa fa-print",
        "angular"   : true
    },
    {
        "id"        : "reports",
        "label"     : "_REPORTS",
        "comment"   : "_REPORTS_DESC",
        "route"     : "index.php?page=reports&amp;module=reports",
        "type"      : "menu",
        "style"     : "fa fa-chart-area",
        "angular"   : false
    },
    {
        "id"        : "save_numeric_package",
        "label"     : "_SAVE_NUMERIC_PACKAGE",
        "comment"   : "_SAVE_NUMERIC_PACKAGE",
        "route"     : "/saveNumericPackage",
        "type"      : "menu",
        "style"     : "fa fa-file-archive",
        "angular"   : true
    },
    //ADMINISTRATION
    {
        "id"        : "admin_users",
        "label"     : "_USERS",
        "comment"   : "_ADMIN_USERS_DESC",
        "route"     : "/administration/users",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-user",
        "angular"   : true
    },
    {
        "id"        : "admin_groups",
        "label"     : "_GROUPS",
        "comment"   : "_ADMIN_GROUPS_DESC",
        "route"     : "/administration/groups",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-users",
        "angular"   : true
    },
    {
        "id"        : "manage_entities",
        "label"     : "_ENTITIES",
        "comment"   : "_MANAGE_ENTITIES_DESC",
        "route"     : "/administration/entities",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-sitemap",
        "angular"   : true
    },
    {
        "id"        : "admin_listmodels",
        "label"     : "_LISTMODELS_WORKFLOW",
        "comment"   : "_MANAGE_LISTMODELS_WORKFLOW_DESC",
        "route"     : "/administration/diffusionModels",
        "type"      : "admin",
        "unit"      : "organisation",
        "style"     : "fa fa-th-list",
        "angular"   : true
    },
    {
        "id"        : "admin_architecture",
        "label"     : "_DOCTYPES",
        "comment"   : "_ADMIN_DOCTYPES_DESC",
        "route"     : "/administration/doctypes",
        "type"      : "admin",
        "unit"      : "classement",
        "style"     : "fa fa-suitcase",
        "angular"   : true
    },
    {
        "id"        : "admin_tag",
        "label"     : "_TAGS",
        "comment"   : "_ADMIN_TAGS_DESC",
        "route"     : "index.php?page=manage_tag_list_controller&amp;module=tags",
        "type"      : "admin",
        "unit"      : "classement",
        "style"     : "fa fa-tags",
        "angular"   : false
    },
    {
        "id"        : "admin_baskets",
        "label"     : "_BASKETS",
        "comment"   : "_ADMIN_BASKETS_DESC",
        "route"     : "/administration/baskets",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-inbox",
        "angular"   : true
    },
    {
        "id"        : "admin_status",
        "label"     : "_STATUSES",
        "comment"   : "_ADMIN_STATUSES_DESC",
        "route"     : "/administration/statuses",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-check-circle",
        "angular"   : true
    },
    {
        "id"        : "admin_actions",
        "label"     : "_ACTIONS",
        "comment"   : "_ADMIN_ACTIONS_DESC",
        "route"     : "/administration/actions",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-exchange-alt",
        "angular"   : true
    },
    {
        "id"        : "admin_contacts",
        "label"     : "_CONTACTS",
        "comment"   : "_ADMIN_CONTACTS_DESC",
        "route"     : "index.php?page=admin_contacts&amp;admin=contacts",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-book",
        "angular"   : false
    },
    {
        "id"        : "admin_priorities",
        "label"     : "_PRIORITIES",
        "comment"   : "_PRIORITIES",
        "route"     : "/administration/priorities",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-clock",
        "angular"   : true
    },
    {
        "id"        : "admin_templates",
        "label"     : "_TEMPLATES",
        "comment"   : "_ADMIN_TEMPLATES_DESC",
        "route"     : "/administration/templates",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-file-alt",
        "angular"   : true
    },
    {
        "id"        : "admin_indexing_models",
        "label"     : "_ADMIN_INDEXING_MODELS",
        "comment"   : "_ADMIN_INDEXING_MODELS",
        "route"     : "/administration/indexingModels",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-wpforms",
        "angular"   : true
    },
    {
        "id"        : "admin_custom_fields",
        "label"     : "_ADMIN_CUSTOM_FIELDS",
        "comment"   : "_ADMIN_CUSTOM_FIELDS",
        "route"     : "/administration/customFields",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-code",
        "angular"   : true
    },
    {
        "id"        : "admin_notif",
        "label"     : "_NOTIFICATIONS",
        "comment"   : "_ADMIN_NOTIFICATIONS_DESC",
        "route"     : "/administration/notifications",
        "type"      : "admin",
        "unit"      : "production",
        "style"     : "fa fa-bell",
        "angular"   : true
    },
    {
        "id"        : "update_status_mail",
        "label"     : "_UPDATE_STATUS_MAIL",
        "comment"   : "_UPDATE_STATUS_MAIL",
        "route"     : "/administration/update-status",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-envelope-square",
        "angular"   : true
    },
    {
        "id"        : "admin_docservers",
        "label"     : "_DOCSERVERS",
        "comment"   : "_ADMIN_DOCSERVERS_DESC",
        "route"     : "/administration/docservers",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-hdd",
        "angular"   : true
    },
    {
        "id"        : "admin_parameters",
        "label"     : "_PARAMETERS",
        "comment"   : "_PARAMETERS",
        "route"     : "/administration/parameters",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-wrench",
        "angular"   : true
    },
    {
        "id"        : "admin_password_rules",
        "label"     : "_SECURITIES",
        "comment"   : "_SECURITIES",
        "route"     : "/administration/securities",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-lock",
        "angular"   : true
    },
    {
        "id"        : "admin_email_server",
        "label"     : "_EMAILSERVER_PARAM",
        "comment"   : "_EMAILSERVER_PARAM_DESC",
        "route"     : "/administration/sendmail",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-mail-bulk",
        "angular"   : true
    },
    {
        "id"        : "admin_shippings",
        "label"     : "_MAILEVA_ADMIN",
        "comment"   : "_MAILEVA_ADMIN_DESC",
        "route"     : "/administration/shippings",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-shipping-fast",
        "angular"   : true
    },
    {
        "id"        : "admin_reports",
        "label"     : "_REPORTS",
        "comment"   : "_ADMIN_REPORTS_DESC",
        "route"     : "/administration/reports",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-chart-area",
        "angular"   : true
    },
    {
        "id"        : "view_history",
        "label"     : "_HISTORY",
        "comment"   : "_ADMIN_HISTORY_DESC",
        "route"     : "/administration/history",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-history",
        "angular"   : true
    },
    {
        "id"        : "view_history_batch",
        "label"     : "_HISTORY_BATCH",
        "comment"   : "_ADMIN_HISTORY_BATCH_DESC",
        "route"     : "/administration/history",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-history",
        "angular"   : true
    },
    {
        "id"        : "admin_update_control",
        "label"     : "_UPDATE_CONTROL",
        "comment"   : "_ADMIN_UPDATE_CONTROL_DESC",
        "route"     : "/administration/versions-update",
        "type"      : "admin",
        "unit"      : "supervision",
        "style"     : "fa fa-sync",
        "angular"   : true
    },
    //USE
    {
        "id"        : "view_doc_history",
        "label"     : "_VIEW_DOC_HISTORY",
        "comment"   : "_VIEW_HISTORY_DESC",
        "type"      : "use"
    },
    {
        "id"        : "view_full_history",
        "label"     : "_VIEW_FULL_HISTORY",
        "comment"   : "_VIEW_FULL_HISTORY_DESC",
        "type"      : "use"
    },
    {
        "id"        : "edit_document_in_detail",
        "label"     : "_EDIT_DOCUMENT_IN_DETAIL",
        "comment"   : "_EDIT_DOCUMENT_IN_DETAIL_DESC",
        "type"      : "use"
    },
    {
        "id"        : "delete_document_in_detail",
        "label"     : "_DELETE_DOCUMENT_IN_DETAIL",
        "comment"   : "_DELETE_DOCUMENT_IN_DETAIL",
        "type"      : "use"
    },
    {
        "id"        : "manage_tags_application",
        "label"     : "_MANAGE_TAGS_IN_APPLICATION",
        "comment"   : "_MANAGE_TAGS_IN_APPLICATION_DESC",
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_indexing",
        "label"     : "_UPDATE_DIFFUSION_WHILE_INDEXING",
        "comment"   : "_UPDATE_DIFFUSION_WHILE_INDEXING",
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_except_recipient_indexing",
        "label"     : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_INDEXING",
        "comment"   : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_INDEXING",
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_details",
        "label"     : "_UPDATE_DIFFUSION_WHILE_DETAILS",
        "comment"   : "_UPDATE_DIFFUSION_WHILE_DETAILS",
        "type"      : "use"
    },
    {
        "id"        : "update_diffusion_except_recipient_details",
        "label"     : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_DETAILS",
        "comment"   : "_UPDATE_DIFFUSION_EXCEPT_RECIPIENT_WHILE_DETAILS",
        "type"      : "use"
    },
    {
        "id"        : "sendmail",
        "label"     : "_SENDMAIL_COMMENT",
        "comment"   : "_SENDMAIL_COMMENT",
        "type"      : "use"
    },
    {
        "id"        : "use_mail_services",
        "label"     : "_USE_MAIL_SERVICES",
        "comment"   : "_USE_MAIL_SERVICES_DESC",
        "type"      : "use"
    },
    {
        "id"        : "view_documents_with_notes",
        "label"     : "_VIEW_DOCUMENTS_WITH_NOTES",
        "comment"   : "_VIEW_DOCUMENTS_WITH_NOTES_DESC",
        "type"      : "use"
    },
    {
        "id"        : "view_technical_infos",
        "label"     : "_VIEW_TECHNICAL_INFORMATIONS",
        "comment"   : "_VIEW_TECHNICAL_INFORMATIONS",
        "type"      : "use"
    },
    {
        "id"        : "config_avis_workflow",
        "label"     : "_CONFIG_AVIS_WORKFLOW",
        "comment"   : "_CONFIG_AVIS_WORKFLOW_DESC",
        "type"      : "use"
    },
    {
        "id"        : "config_avis_workflow_in_detail",
        "label"     : "_CONFIG_AVIS_WORKFLOW_IN_DETAIL",
        "comment"   : "_CONFIG_AVIS_WORKFLOW_IN_DETAIL_DESC",
        "type"      : "use"
    },
    {
        "id"        : "avis_documents",
        "label"     : "_AVIS_ANSWERS",
        "comment"   : "_AVIS_ANSWERS_DESC",
        "type"      : "use"
    },
    {
        "id"        : "config_visa_workflow",
        "label"     : "_CONFIG_VISA_WORKFLOW",
        "comment"   : "_CONFIG_VISA_WORKFLOW_DESC",
        "type"      : "use"
    },
    {
        "id"        : "config_visa_workflow_in_detail",
        "label"     : "_CONFIG_VISA_WORKFLOW_IN_DETAIL",
        "comment"   : "_CONFIG_VISA_WORKFLOW_IN_DETAIL_DESC",
        "type"      : "use"
    },
    {
        "id"        : "visa_documents",
        "label"     : "_VISA_ANSWERS",
        "comment"   : "_VISA_ANSWERS_DESC",
        "type"      : "use"
    },
    {
        "id"        : "sign_document",
        "label"     : "_SIGN_DOCS",
        "comment"   : "_SIGN_DOCS",
        "type"      : "use"
    },
    {
        "id"        : "modify_visa_in_signatureBook",
        "label"     : "_MODIFY_VISA_IN_SIGNATUREBOOK",
        "comment"   : "_MODIFY_VISA_IN_SIGNATUREBOOK_DESC",
        "type"      : "use"
    },
    {
        "id"        : "use_date_in_signBlock",
        "label"     : "_USE_DATE_IN_SIGNBLOCK",
        "comment"   : "_USE_DATE_IN_SIGNBLOCK_DESC",
        "type"      : "use"
    },
    {
        "id"        : "print_folder_doc",
        "label"     : "_PRINT_FOLDER_DOC",
        "comment"   : "_PRINT_FOLDER_DOC",
        "type"      : "use"
    }
];
