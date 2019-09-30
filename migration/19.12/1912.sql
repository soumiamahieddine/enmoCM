-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.04 to 19.12                                  --
--                                                                          --
--                                                                          --
-- *************************************************************************--
UPDATE parameters SET param_value_string = '19.12' WHERE id = 'database_version';


/* VIEWS */
DROP VIEW IF EXISTS res_view_letterbox;
DROP VIEW IF EXISTS res_view_attachments;
DROP VIEW IF EXISTS view_folders;


/* FULL TEXT */
DELETE FROM docservers where docserver_type_id = 'FULLTEXT';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FULLTEXT_DOCUMENT', 'FULLTEXT', 'Full text indexes for documents', 'N', 50000000000, 0, '/opt/maarch/docservers/indexes/documents/', '2019-11-01 12:00:00.123456', 'letterbox_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FULLTEXT_ATTACHMENT', 'FULLTEXT', 'Full text indexes for attachments', 'N', 50000000000, 0, '/opt/maarch/docservers/indexes/attachments/', '2019-11-01 12:00:00.123456', 'attachments_coll');
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, size_limit_number, actual_size_number, path_template, creation_date, coll_id)
VALUES ('FULLTEXT_ATTACHMENT_VERSION', 'FULLTEXT', 'Full text indexes for documents', 'N', 50000000000, 0, '/opt/maarch/docservers/indexes/attachments_version/', '2019-11-01 12:00:00.123456', 'attachments_version_coll');
UPDATE docserver_types SET fingerprint_mode = NULL WHERE docserver_type_id = 'FULLTEXT';
UPDATE res_letterbox SET fulltext_result = 'SUCCESS' WHERE fulltext_result = '1' OR fulltext_result = '2';
UPDATE res_letterbox SET fulltext_result = 'ERROR' WHERE fulltext_result = '-1' OR fulltext_result = '-2';
UPDATE res_attachments SET fulltext_result = 'SUCCESS' WHERE fulltext_result = '1' OR fulltext_result = '2';
UPDATE res_attachments SET fulltext_result = 'ERROR' WHERE fulltext_result = '-1' OR fulltext_result = '-2';
UPDATE res_version_attachments SET fulltext_result = 'SUCCESS' WHERE fulltext_result = '1' OR fulltext_result = '2';
UPDATE res_version_attachments SET fulltext_result = 'ERROR' WHERE fulltext_result = '-1' OR fulltext_result = '-2';


/* GROUPS INDEXING */
ALTER TABLE usergroups ALTER COLUMN group_desc DROP DEFAULT;
ALTER TABLE usergroups DROP COLUMN IF EXISTS can_index;
ALTER TABLE usergroups ADD COLUMN can_index boolean NOT NULL DEFAULT FALSE;
ALTER TABLE usergroups DROP COLUMN IF EXISTS indexation_parameters;
ALTER TABLE usergroups ADD COLUMN indexation_parameters jsonb NOT NULL DEFAULT '{"actions" : [], "entities" : [], "keywords" : []}';


/* BASKETS LIST EVENT */
ALTER TABLE groupbasket DROP COLUMN IF EXISTS list_event;
ALTER TABLE groupbasket ADD COLUMN list_event character varying(255);
UPDATE groupbasket SET list_event = 'processDocument'
FROM (
       SELECT basket_id, group_id
       FROM actions_groupbaskets ag
         LEFT JOIN actions a ON ag.id_action = a.id
       WHERE ag.default_action_list = 'Y' AND a.action_page in ('validate_mail', 'process')
     ) AS subquery
WHERE groupbasket.basket_id = subquery.basket_id AND groupbasket.group_id = subquery.group_id;
UPDATE groupbasket SET list_event = 'viewDoc'
FROM (
       SELECT basket_id, group_id
       FROM actions_groupbaskets ag
         LEFT JOIN actions a ON ag.id_action = a.id
       WHERE ag.default_action_list = 'Y' AND a.component = 'viewDoc'
     ) AS subquery
WHERE groupbasket.basket_id = subquery.basket_id AND groupbasket.group_id = subquery.group_id;
UPDATE groupbasket SET list_event = 'signatureBookAction'
FROM (
       SELECT basket_id, group_id
       FROM actions_groupbaskets ag
         LEFT JOIN actions a ON ag.id_action = a.id
       WHERE ag.default_action_list = 'Y' AND a.action_page in ('visa_mail')
     ) AS subquery
WHERE groupbasket.basket_id = subquery.basket_id AND groupbasket.group_id = subquery.group_id;
UPDATE actions SET component = 'confirmAction', action_page = 'confirm_status' WHERE action_page in ('validate_mail', 'process', 'visa_mail');
DELETE FROM actions WHERE action_page = 'view' OR component = 'viewDoc';


/* FOLDERS */
DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'folders') AND attname = 'folders_system_id') THEN
    ALTER TABLE folders RENAME TO folder_tmp;
    ALTER TABLE folder_tmp RENAME CONSTRAINT folders_pkey to folders_tmp_pkey;
  END IF;
END$$;

DROP TABLE IF EXISTS folders;
CREATE TABLE folders
(
  id serial NOT NULL,
  label character varying(255) NOT NULL,
  public boolean NOT NULL,   
  user_id INTEGER NOT NULL,
  parent_id INTEGER,
  level INTEGER NOT NULL,
  CONSTRAINT folders_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS resources_folders;
CREATE TABLE resources_folders
(
  id serial NOT NULL,
  folder_id INTEGER NOT NULL,
  res_id INTEGER NOT NULL,
  CONSTRAINT resources_folders_pkey PRIMARY KEY (id),
  CONSTRAINT resources_folders_unique_key UNIQUE (folder_id, res_id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS entities_folders;
CREATE TABLE entities_folders
(
  id serial NOT NULL,
  folder_id INTEGER NOT NULL,
  entity_id INTEGER NOT NULL,
  edition boolean NOT NULL,
  CONSTRAINT entities_folders_pkey PRIMARY KEY (id),
  CONSTRAINT entities_folders_unique_key UNIQUE (folder_id, entity_id)
)
WITH (OIDS=FALSE);


/* CUSTOM FIELDS */
DROP TABLE IF EXISTS custom_fields;
CREATE TABLE custom_fields
(
  id serial NOT NULL,
  label character varying(256) NOT NULL,
  type character varying(256) NOT NULL,
  values jsonb,
  CONSTRAINT custom_fields_pkey PRIMARY KEY (id),
  CONSTRAINT custom_fields_unique_key UNIQUE (label)
)
WITH (OIDS=FALSE);


/* INDEXING MODELS */
DROP TABLE IF EXISTS indexing_models;
CREATE TABLE indexing_models
(
  id SERIAL NOT NULL,
  label character varying(256) NOT NULL,
  category character varying(256) NOT NULL,
  "default" BOOLEAN NOT NULL,
  owner INTEGER NOT NULL,
  private BOOLEAN NOT NULL,
  master INTEGER DEFAULT NULL,
  CONSTRAINT indexing_models_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TABLE IF EXISTS indexing_models_fields;
CREATE TABLE indexing_models_fields
(
  id SERIAL NOT NULL,
  model_id INTEGER NOT NULL,
  identifier text NOT NULL,
  mandatory BOOLEAN NOT NULL,
  default_value json,
  unit text NOT NULL,
  CONSTRAINT indexing_models_fields_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


/* TAGS */
DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'tags') AND attname = 'tag_label') = 1 THEN
	  ALTER TABLE tags RENAME COLUMN tag_label TO label;
	  ALTER TABLE tags DROP COLUMN IF EXISTS coll_id;
	  ALTER TABLE tags ADD COLUMN id serial NOT NULL;
	  UPDATE tags SET id = tag_id;
      ALTER TABLE tags DROP COLUMN IF EXISTS tag_id;
  END IF;
END$$;
SELECT setval('tags_id_seq', (SELECT MAX(id) from tags));


/* DOCTYPES */
DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'mlb_doctype_ext')) > 0 THEN
	  ALTER TABLE doctypes ADD COLUMN process_delay INTEGER;
	  ALTER TABLE doctypes ADD COLUMN delay1 INTEGER;
	  ALTER TABLE doctypes ADD COLUMN delay2 INTEGER;
	  ALTER TABLE doctypes ADD COLUMN process_mode CHARACTER VARYING(256);
	  UPDATE doctypes SET process_delay = (SELECT process_delay FROM mlb_doctype_ext where doctypes.type_id = mlb_doctype_ext.type_id);
    UPDATE doctypes SET process_delay = 30 WHERE process_delay is null;
	  UPDATE doctypes SET delay1 = (SELECT delay1 FROM mlb_doctype_ext where doctypes.type_id = mlb_doctype_ext.type_id);
    UPDATE doctypes SET delay1 = 14 WHERE delay1 is null;
	  UPDATE doctypes SET delay2 = (SELECT delay2 FROM mlb_doctype_ext where doctypes.type_id = mlb_doctype_ext.type_id);
    UPDATE doctypes SET delay2 = 1 WHERE delay2 is null;
	  UPDATE doctypes SET process_mode = (SELECT process_mode FROM mlb_doctype_ext where doctypes.type_id = mlb_doctype_ext.type_id);
    UPDATE doctypes SET process_mode = 'NORMAL' WHERE process_mode is null;
	  ALTER TABLE doctypes ALTER COLUMN process_delay SET NOT NULL;
	  ALTER TABLE doctypes ALTER COLUMN delay1 SET NOT NULL;
	  ALTER TABLE doctypes ALTER COLUMN delay2 SET NOT NULL;
	  ALTER TABLE doctypes ALTER COLUMN process_mode SET NOT NULL;
  END IF;
END$$;


/* REFACTORING DATA */
DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'usergroups') AND attname = 'enabled') THEN
    DELETE FROM usergroup_content WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM usergroups_reports WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM usergroups_services WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM security WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM groupbasket WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM groupbasket_redirect WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM groupbasket_status WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
    DELETE FROM users_baskets_preferences WHERE group_serial_id in (SELECT id FROM usergroups WHERE enabled = 'N');
    DELETE FROM usergroups WHERE enabled = 'N';
  END IF;
END$$;

DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'actions') AND attname = 'enabled') THEN
    DELETE FROM actions_categories WHERE action_id in (SELECT id FROM actions WHERE enabled = 'N');
    DELETE FROM actions_groupbaskets WHERE id_action in (SELECT id FROM actions WHERE enabled = 'N');
    DELETE FROM groupbasket_redirect WHERE action_id in (SELECT id FROM actions WHERE enabled = 'N');
    DELETE FROM actions WHERE enabled = 'N';
  END IF;
END$$;

DELETE FROM usergroups_services WHERE service_id = 'admin_fileplan';
DELETE FROM usergroups_services WHERE service_id = 'put_doc_in_fileplan';
DELETE FROM usergroups_services WHERE service_id = 'fileplan';
DELETE FROM usergroups_services WHERE service_id = 'update_case';
DELETE FROM usergroups_services WHERE service_id = 'join_res_case';
DELETE FROM usergroups_services WHERE service_id = 'join_res_case_in_process';
DELETE FROM usergroups_services WHERE service_id = 'close_case';
DELETE FROM usergroups_services WHERE service_id = 'add_cases';
DELETE FROM usergroups_services WHERE service_id IN ('folder_search', 'view_folder_tree', 'select_folder', 'show_history_folder', 'modify_folder', 'associate_folder', 'delete_folder', 'admin_foldertypes', 'create_folder', 'folder_freeze', 'close_folder');


/* REFACTORING MODIFICATION */
ALTER TABLE notif_email_stack ALTER COLUMN attachments TYPE text;


/* REFACTORING SUPPRESSION */
DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'users') AND attname = 'enabled') THEN
    UPDATE users SET status = 'SPD' WHERE enabled = 'N' and (status = 'OK' or status = 'ABS');
    ALTER TABLE users DROP COLUMN IF EXISTS enabled;
  END IF;
END$$;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS converter_result;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS converter_result;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE usergroups DROP COLUMN IF EXISTS enabled;
ALTER TABLE actions DROP COLUMN IF EXISTS enabled;
ALTER TABLE actions DROP COLUMN IF EXISTS origin;
ALTER TABLE actions DROP COLUMN IF EXISTS create_id;
ALTER TABLE actions DROP COLUMN IF EXISTS category_id;
DROP VIEW IF EXISTS fp_view_fileplan;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS folders_system_id;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS folders_system_id;
DROP TABLE IF EXISTS foldertypes;
DROP TABLE IF EXISTS foldertypes_doctypes;
DROP TABLE IF EXISTS foldertypes_doctypes_level1;
DROP TABLE IF EXISTS foldertypes_indexes;
ALTER TABLE doctypes DROP COLUMN IF EXISTS coll_id;
DROP TABLE IF EXISTS mlb_doctype_ext;


/* RE CREATE VIEWS */
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
       r.path,
       r.filename,
       r.fingerprint,
       r.offset_doc,
       r.filesize,
       r.scan_date,
       r.scan_user,
       r.scan_location,
       r.scan_wkstation,
       r.scan_batch,
       r.scan_postmark,
       r.status,
       r.work_batch,
       r.doc_date,
       r.description,
       r.source,
       r.author,
       r.reference_number,
       r.external_reference,
       r.external_id,
       r.external_link,
       r.departure_date,
       r.opinion_limit_date,
       r.department_number_id,
       r.barcode,
       r.external_signatory_book_id,
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
       mlb.process_limit_date,
       mlb.closing_date,
       mlb.alarm1_date,
       mlb.alarm2_date,
       mlb.flag_alarm1,
       mlb.flag_alarm2,
       mlb.is_multicontacts,
       r.subject,
       r.identifier,
       r.title,
       r.priority,
       r.locker_user_id,
       r.locker_time,
       en.entity_label,
       en.entity_type AS entitytype,
       cont.contact_id,
       cont.firstname AS contact_firstname,
       cont.lastname AS contact_lastname,
       cont.society AS contact_society,
       u.lastname AS user_lastname,
       u.firstname AS user_firstname
FROM doctypes d,
     doctypes_first_level dfl,
     doctypes_second_level dsl,
     res_letterbox r
         LEFT JOIN entities en ON r.destination::text = en.entity_id::text
         LEFT JOIN mlb_coll_ext mlb ON mlb.res_id = r.res_id
         LEFT JOIN contacts_v2 cont ON mlb.exp_contact_id = cont.contact_id OR mlb.dest_contact_id = cont.contact_id
         LEFT JOIN users u ON mlb.exp_user_id::text = u.user_id::text OR mlb.dest_user_id::text = u.user_id::text
WHERE r.type_id = d.type_id AND d.doctypes_first_level_id = dfl.doctypes_first_level_id AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;

CREATE VIEW res_view_attachments AS
  SELECT '0' as res_id, res_id as res_id_version, title, subject, description, type_id, format, typist,
  creation_date, fulltext_result, author, identifier, source, relation, doc_date, docserver_id, path,
  filename, offset_doc, fingerprint, filesize, status, destination, validation_date, effective_date, origin, priority, initiator, dest_user, external_id,
  coll_id, dest_contact_id, dest_address_id, updated_by, is_multicontacts, is_multi_docservers, res_id_master, attachment_type, attachment_id_master, in_signature_book, in_send_attach, signatory_user_serial_id
  FROM res_version_attachments
  UNION ALL
  SELECT res_id, '0' as res_id_version, title, subject, description, type_id, format, typist,
  creation_date, fulltext_result, author, identifier, source, relation, doc_date, docserver_id, path,
  filename, offset_doc, fingerprint, filesize, status, destination, validation_date, effective_date, origin, priority, initiator, dest_user, external_id,
  coll_id, dest_contact_id, dest_address_id, updated_by, is_multicontacts, is_multi_docservers, res_id_master, attachment_type, '0', in_signature_book, in_send_attach, signatory_user_serial_id
  FROM res_attachments;


/* DATA */
TRUNCATE TABLE custom_fields;
INSERT INTO custom_fields (id, label, type, values) VALUES (1, 'Nature', 'select', '["Courrier simple", "Courriel", "Chronopost", "Pli numérique"]');
INSERT INTO custom_fields (id, label, type, values) VALUES (2, 'N° recommandé', 'string', '""');
SELECT setval('custom_fields_id_seq', (select max(id)+1 from custom_fields), false);

TRUNCATE TABLE indexing_models;
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (1, 'incoming', 'Courrier arrivée', TRUE, 23, FALSE);
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (2, 'outgoing', 'Courrier départ', FALSE, 23, FALSE);
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (3, 'internal', 'Courrier interne', FALSE, 23, FALSE);
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (4, 'ged_doc', 'Document ged', FALSE, 23, FALSE);
Select setval('indexing_models_id_seq', (select max(id)+1 from indexing_models), false);

TRUNCATE TABLE indexing_models_fields;
/* Arrivée */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'doctype', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'priority', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'confidential', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'docDate', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'arrivalDate', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'subject', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'indexingCustomField_1', FALSE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'senders', TRUE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'getRecipients', FALSE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'initiator', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'destination', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'processLimitDate', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'folder', FALSE, '""', 'classement');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'tags', FALSE, '""', 'classement');

/* Départ */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'doctype', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'priority', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'confidential', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'docDate', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'subject', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'indexingCustomField_1', FALSE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'senders', FALSE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'getRecipients', TRUE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'initiator', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'destination', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'processLimitDate', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'folder', FALSE, '""', 'classement');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'tags', FALSE, '""', 'classement');

/* Interne */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'doctype', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'priority', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'confidential', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'docDate', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'subject', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'indexingCustomField_1', FALSE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'senders', FALSE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'getRecipients', FALSE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'initiator', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'destination', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'processLimitDate', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'folder', FALSE, '""', 'classement');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'tags', FALSE, '""', 'classement');

/* GED */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'doctype', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'confidential', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'docDate', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'subject', TRUE, '""', 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'senders', FALSE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'getRecipients', FALSE, '""', 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'initiator', TRUE, '""', 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'destination', TRUE, '""', 'process');
