-- SQL for the current dev only
DROP VIEW IF EXISTS res_view_letterbox;
DROP VIEW IF EXISTS res_view_attachments;

DROP TABLE IF EXISTS contacts_groups;
CREATE TABLE contacts_groups
(
  id serial,
  label character varying(32) NOT NULL,
  description character varying(255) NOT NULL,
  public boolean NOT NULL,
  owner integer NOT NULL,
  entity_owner character varying(32) NOT NULL,
  CONSTRAINT contacts_groups_pkey PRIMARY KEY (id),
  CONSTRAINT contacts_groups_key UNIQUE (label, owner)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS contacts_groups_lists;
CREATE TABLE contacts_groups_lists
(
  id serial,
  contacts_groups_id integer NOT NULL,
  contact_addresses_id integer NOT NULL,
  CONSTRAINT contacts_groups_lists_pkey PRIMARY KEY (id),
  CONSTRAINT contacts_groups_lists_key UNIQUE (contacts_groups_id, contact_addresses_id)
)
WITH (OIDS=FALSE);

UPDATE actions_groupbaskets SET used_in_basketlist = 'Y', used_in_action_page = 'Y' WHERE default_action_list = 'Y';
UPDATE actions_groupbaskets SET used_in_action_page = 'Y' WHERE used_in_basketlist = 'N' AND used_in_action_page = 'N';
DELETE FROM usergroups_services WHERE service_id = 'view_baskets';
ALTER TABLE groupbasket_status DROP COLUMN IF EXISTS "order";
ALTER TABLE groupbasket_status ADD COLUMN "order" integer;
UPDATE groupbasket_status SET "order" = 1;
ALTER TABLE groupbasket_status ALTER COLUMN "order" SET NOT NULL;


/* Docservers */
ALTER TABLE docservers DROP COLUMN IF EXISTS docserver_location_id;
ALTER TABLE docservers DROP COLUMN IF EXISTS ext_docserver_info;
ALTER TABLE docservers DROP COLUMN IF EXISTS chain_before;
ALTER TABLE docservers DROP COLUMN IF EXISTS chain_after;
ALTER TABLE docservers DROP COLUMN IF EXISTS closing_date;
ALTER TABLE docservers DROP COLUMN IF EXISTS enabled;
ALTER TABLE docservers DROP COLUMN IF EXISTS adr_priority_number;
ALTER TABLE docservers DROP COLUMN IF EXISTS priority_number;
ALTER TABLE docservers DROP COLUMN IF EXISTS id;
ALTER TABLE docservers ADD COLUMN id serial;
ALTER TABLE docservers ADD UNIQUE (id);
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_container;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS container_max_number;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_compressed;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS compression_mode;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_meta;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS meta_template;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_logged;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS log_template;
ALTER TABLE docserver_types DROP COLUMN IF EXISTS is_signed;
DROP TABLE IF EXISTS docserver_locations;
UPDATE docservers set is_readonly = 'Y' WHERE docserver_id = 'FASTHD_AI';

/* Templates */
ALTER TABLE templates_association DROP COLUMN IF EXISTS system_id;
ALTER TABLE templates_association DROP COLUMN IF EXISTS what;
ALTER TABLE templates_association DROP COLUMN IF EXISTS maarch_module;
ALTER TABLE templates_association DROP COLUMN IF EXISTS id;
ALTER TABLE templates_association ADD COLUMN id serial;
ALTER TABLE templates_association ADD UNIQUE (id);
UPDATE templates SET template_content = REPLACE(template_content, '###', ';');
UPDATE templates SET template_content = REPLACE(template_content, '___', '--');

/* Password Management */
DROP TABLE IF EXISTS password_rules;
CREATE TABLE password_rules
(
  id serial,
  label character varying(64) NOT NULL,
  "value" integer NOT NULL,
  enabled boolean DEFAULT FALSE,
  CONSTRAINT password_rules_pkey PRIMARY KEY (id),
  CONSTRAINT password_rules_label_key UNIQUE (label)
)
WITH (OIDS=FALSE);
INSERT INTO password_rules (label, "value") VALUES ('renewal', 90);
ALTER TABLE users DROP COLUMN IF EXISTS password_modification_date;
ALTER TABLE users ADD COLUMN password_modification_date timestamp without time zone;

/* Refactoring */
DROP VIEW IF EXISTS af_view_customer_target_view;
DROP VIEW IF EXISTS af_view_customer_view;
DROP VIEW IF EXISTS af_view_year_target_view;
DROP VIEW IF EXISTS af_view_year_view;
DROP TABLE IF EXISTS allowed_ip;
DROP TABLE IF EXISTS af_security;
DROP TABLE IF EXISTS af_view_customer_target;
DROP TABLE IF EXISTS af_view_year_target;
DROP VIEW IF EXISTS res_view;
DROP TABLE IF EXISTS res_x;
DROP TABLE IF EXISTS res_version_x;
DROP TABLE IF EXISTS adr_x;
DROP TABLE IF EXISTS res_version_letterbox;
ALTER TABLE baskets DROP COLUMN IF EXISTS is_generic;
ALTER TABLE baskets DROP COLUMN IF EXISTS except_notif;
ALTER TABLE baskets DROP COLUMN IF EXISTS is_folder_basket;
ALTER TABLE actions DROP COLUMN IF EXISTS is_folder_action;
ALTER TABLE status DROP COLUMN IF EXISTS is_folder_status;
ALTER TABLE security DROP COLUMN IF EXISTS can_insert;
ALTER TABLE security DROP COLUMN IF EXISTS can_update;
ALTER TABLE security DROP COLUMN IF EXISTS can_delete;
ALTER TABLE security DROP COLUMN IF EXISTS rights_bitmask;
ALTER TABLE security DROP COLUMN IF EXISTS mr_start_date;
ALTER TABLE security DROP COLUMN IF EXISTS mr_stop_date;
ALTER TABLE security DROP COLUMN IF EXISTS where_target;
ALTER TABLE users DROP COLUMN IF EXISTS ra_code;
ALTER TABLE users DROP COLUMN IF EXISTS ra_expiration_date;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS publisher;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS publisher;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS publisher;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS contributor;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS contributor;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS contributor;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS author_name;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS author_name;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS author_name;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS doc_language;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS doc_language;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS doc_language;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS arbox_id;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS arbox_id;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS arbox_id;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS logical_adr;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS logical_adr;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS logical_adr;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS is_paper;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS is_paper;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS is_paper;

CREATE OR REPLACE VIEW res_view_letterbox AS
 SELECT r.tablename,
    r.is_multi_docservers,
    r.res_id,
    r.type_id,
    r.policy_id,
    r.cycle_id,
    d.description AS type_label,
    d.doctypes_first_level_id,
    dfl.doctypes_first_level_label,
    dfl.css_style AS doctype_first_level_style,
    d.doctypes_second_level_id,
    dsl.doctypes_second_level_label,
    dsl.css_style AS doctype_second_level_style,
    r.format,
    r.typist,
    r.creation_date,
    r.modification_date,
    r.relation,
    r.docserver_id,
    r.folders_system_id,
    f.folder_id,
    f.destination AS folder_destination,
    f.is_frozen AS folder_is_frozen,
    r.path,
    r.filename,
    r.fingerprint,
    r.offset_doc,
    r.filesize,
    r.status,
    r.work_batch,
    r.arbatch_id,
    r.page_count,
    r.doc_date,
    r.scan_date,
    r.scan_user,
    r.scan_location,
    r.scan_wkstation,
    r.scan_batch,
    r.description,
    r.source,
    r.author,
    r.reference_number,
    r.external_id,
    r.external_link,
    r.custom_t1 AS doc_custom_t1,
    r.custom_t2 AS doc_custom_t2,
    r.custom_t3 AS doc_custom_t3,
    r.custom_t4 AS doc_custom_t4,
    r.custom_t5 AS doc_custom_t5,
    r.custom_t6 AS doc_custom_t6,
    r.custom_t7 AS doc_custom_t7,
    r.custom_t8 AS doc_custom_t8,
    r.custom_t9 AS doc_custom_t9,
    r.custom_t10 AS doc_custom_t10,
    r.custom_t11 AS doc_custom_t11,
    r.custom_t12 AS doc_custom_t12,
    r.custom_t13 AS doc_custom_t13,
    r.custom_t14 AS doc_custom_t14,
    r.custom_t15 AS doc_custom_t15,
    r.custom_d1 AS doc_custom_d1,
    r.custom_d2 AS doc_custom_d2,
    r.custom_d3 AS doc_custom_d3,
    r.custom_d4 AS doc_custom_d4,
    r.custom_d5 AS doc_custom_d5,
    r.custom_d6 AS doc_custom_d6,
    r.custom_d7 AS doc_custom_d7,
    r.custom_d8 AS doc_custom_d8,
    r.custom_d9 AS doc_custom_d9,
    r.custom_d10 AS doc_custom_d10,
    r.custom_n1 AS doc_custom_n1,
    r.custom_n2 AS doc_custom_n2,
    r.custom_n3 AS doc_custom_n3,
    r.custom_n4 AS doc_custom_n4,
    r.custom_n5 AS doc_custom_n5,
    r.custom_f1 AS doc_custom_f1,
    r.custom_f2 AS doc_custom_f2,
    r.custom_f3 AS doc_custom_f3,
    r.custom_f4 AS doc_custom_f4,
    r.custom_f5 AS doc_custom_f5,
    f.foldertype_id,
    ft.foldertype_label,
    f.custom_t1 AS fold_custom_t1,
    f.custom_t2 AS fold_custom_t2,
    f.custom_t3 AS fold_custom_t3,
    f.custom_t4 AS fold_custom_t4,
    f.custom_t5 AS fold_custom_t5,
    f.custom_t6 AS fold_custom_t6,
    f.custom_t7 AS fold_custom_t7,
    f.custom_t8 AS fold_custom_t8,
    f.custom_t9 AS fold_custom_t9,
    f.custom_t10 AS fold_custom_t10,
    f.custom_t11 AS fold_custom_t11,
    f.custom_t12 AS fold_custom_t12,
    f.custom_t13 AS fold_custom_t13,
    f.custom_t14 AS fold_custom_t14,
    f.custom_t15 AS fold_custom_t15,
    f.custom_d1 AS fold_custom_d1,
    f.custom_d2 AS fold_custom_d2,
    f.custom_d3 AS fold_custom_d3,
    f.custom_d4 AS fold_custom_d4,
    f.custom_d5 AS fold_custom_d5,
    f.custom_d6 AS fold_custom_d6,
    f.custom_d7 AS fold_custom_d7,
    f.custom_d8 AS fold_custom_d8,
    f.custom_d9 AS fold_custom_d9,
    f.custom_d10 AS fold_custom_d10,
    f.custom_n1 AS fold_custom_n1,
    f.custom_n2 AS fold_custom_n2,
    f.custom_n3 AS fold_custom_n3,
    f.custom_n4 AS fold_custom_n4,
    f.custom_n5 AS fold_custom_n5,
    f.custom_f1 AS fold_custom_f1,
    f.custom_f2 AS fold_custom_f2,
    f.custom_f3 AS fold_custom_f3,
    f.custom_f4 AS fold_custom_f4,
    f.custom_f5 AS fold_custom_f5,
    f.is_complete AS fold_complete,
    f.status AS fold_status,
    f.subject AS fold_subject,
    f.parent_id AS fold_parent_id,
    f.folder_level,
    f.folder_name,
    f.creation_date AS fold_creation_date,
    r.initiator,
    r.destination,
    r.dest_user,
    r.confidentiality,
    mlb.category_id,
    mlb.exp_contact_id,
    mlb.exp_user_id,
    mlb.dest_user_id,
    mlb.dest_contact_id,
    mlb.address_id,
    mlb.nature_id,
    mlb.alt_identifier,
    mlb.admission_date,
    mlb.answer_type_bitmask,
    mlb.other_answer_desc,
    mlb.sve_start_date,
    mlb.sve_identifier,
    mlb.process_limit_date,
    mlb.recommendation_limit_date,
    mlb.closing_date,
    mlb.alarm1_date,
    mlb.alarm2_date,
    mlb.flag_notif,
    mlb.flag_alarm1,
    mlb.flag_alarm2,
    mlb.is_multicontacts,
    r.video_user,
    r.video_time,
    r.video_batch,
    r.subject,
    r.identifier,
    r.title,
    r.priority,
    mlb.process_notes,
    r.locker_user_id,
    r.locker_time,
    ca.case_id,
    ca.case_label,
    ca.case_description,
    en.entity_label,
    en.entity_type AS entitytype,
    cont.contact_id,
    cont.firstname AS contact_firstname,
    cont.lastname AS contact_lastname,
    cont.society AS contact_society,
    u.lastname AS user_lastname,
    u.firstname AS user_firstname,
    r.is_frozen AS res_is_frozen
   FROM doctypes d,
    doctypes_first_level dfl,
    doctypes_second_level dsl,
    res_letterbox r
     LEFT JOIN entities en ON r.destination::text = en.entity_id::text
     LEFT JOIN folders f ON r.folders_system_id = f.folders_system_id
     LEFT JOIN cases_res cr ON r.res_id = cr.res_id
     LEFT JOIN mlb_coll_ext mlb ON mlb.res_id = r.res_id
     LEFT JOIN foldertypes ft ON f.foldertype_id = ft.foldertype_id AND f.status::text <> 'DEL'::text
     LEFT JOIN cases ca ON cr.case_id = ca.case_id
     LEFT JOIN contacts_v2 cont ON mlb.exp_contact_id = cont.contact_id OR mlb.dest_contact_id = cont.contact_id
     LEFT JOIN users u ON mlb.exp_user_id::text = u.user_id::text OR mlb.dest_user_id::text = u.user_id::text
  WHERE r.type_id = d.type_id AND d.doctypes_first_level_id = dfl.doctypes_first_level_id AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;

CREATE VIEW res_view_attachments AS
  SELECT '0' as res_id, res_id as res_id_version, title, subject, description, type_id, format, typist,
  creation_date, fulltext_result, ocr_result, author, identifier, source, relation, coverage, doc_date, docserver_id, folders_system_id, path,
  filename, offset_doc, fingerprint, filesize, page_count,
  scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark,
  envelop_id, status, destination, approver, validation_date, effective_date, work_batch, origin, is_ingoing, priority, initiator, dest_user,
  coll_id, dest_contact_id, dest_address_id, updated_by, is_multicontacts, is_multi_docservers, res_id_master, attachment_type, attachment_id_master, in_signature_book, signatory_user_serial_id
  FROM res_version_attachments
  UNION ALL
  SELECT res_id, '0' as res_id_version, title, subject, description, type_id, format, typist,
  creation_date, fulltext_result, ocr_result, author, identifier, source, relation, coverage, doc_date, docserver_id, folders_system_id, path,
  filename, offset_doc, fingerprint, filesize, page_count,
  scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark,
  envelop_id, status, destination, approver, validation_date, effective_date, work_batch, origin, is_ingoing, priority, initiator, dest_user,
  coll_id, dest_contact_id, dest_address_id, updated_by, is_multicontacts, is_multi_docservers, res_id_master, attachment_type, '0', in_signature_book, signatory_user_serial_id
  FROM res_attachments;
