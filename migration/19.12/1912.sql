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

DROP TABLE IF EXISTS users_pinned_folders;
CREATE TABLE users_pinned_folders
(
  id serial NOT NULL,
  folder_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  CONSTRAINT users_pinned_folders_pkey PRIMARY KEY (id),
  CONSTRAINT users_pinned_folders_unique_key UNIQUE (folder_id, user_id)
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

DROP TABLE IF EXISTS resources_custom_fields;
CREATE TABLE resources_custom_fields
(
    id serial NOT NULL,
    res_id INTEGER NOT NULL,
    custom_field_id INTEGER NOT NULL,
    value jsonb NOT NULL,
    CONSTRAINT resources_custom_fields_pkey PRIMARY KEY (id),
    CONSTRAINT resources_custom_fields_unique_key UNIQUE (res_id, custom_field_id)
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
  enabled BOOLEAN DEFAULT TRUE NOT NULL,
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


/* NOTES */
DO $$ BEGIN
    IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'notes') AND attname = 'type') THEN
        ALTER TABLE notes ADD COLUMN user_tmp_id integer;
        UPDATE notes set user_tmp_id = (select id FROM users where users.user_id = notes.user_id);
        UPDATE notes set user_tmp_id = 0 WHERE user_tmp_id IS NULL;
        ALTER TABLE notes ALTER COLUMN user_tmp_id set not null;
        ALTER TABLE notes DROP COLUMN IF EXISTS user_id;
        ALTER TABLE notes RENAME COLUMN user_tmp_id TO user_id;
        ALTER TABLE notes DROP COLUMN IF EXISTS type;
    END IF;
END$$;


/* RES_LETTERBOX */
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS model_id;
ALTER TABLE res_letterbox ADD COLUMN model_id INTEGER;
DO $$ BEGIN
    IF (SELECT count(column_name) from information_schema.columns where table_name = 'res_letterbox' and column_name = 'typist' and data_type != 'integer') THEN
        ALTER TABLE res_letterbox ADD COLUMN typist_tmp integer;
        UPDATE res_letterbox set typist_tmp = (select id FROM users where users.user_id = res_letterbox.typist);
        UPDATE res_letterbox set typist_tmp = 0 WHERE typist_tmp IS NULL;
        ALTER TABLE res_letterbox ALTER COLUMN typist_tmp set not null;
        ALTER TABLE res_letterbox DROP COLUMN IF EXISTS typist;
        ALTER TABLE res_letterbox RENAME COLUMN typist_tmp TO typist;
    END IF;
END$$;


/* MLB COLL EXT */
DO $$ BEGIN
    IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'res_letterbox') AND attname = 'category_id') = 0 THEN
        ALTER TABLE res_letterbox ADD COLUMN category_id character varying(32);
        UPDATE res_letterbox SET category_id = mlb_coll_ext.category_id FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;
        UPDATE res_letterbox set category_id = 'incoming' WHERE category_id IS NULL;
        ALTER TABLE res_letterbox ALTER COLUMN category_id set not null;

        ALTER TABLE res_letterbox ADD COLUMN exp_contact_id integer;
        UPDATE res_letterbox SET exp_contact_id = mlb_coll_ext.exp_contact_id FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN exp_user_id character varying(128);
        UPDATE res_letterbox SET exp_user_id = mlb_coll_ext.exp_user_id FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN dest_contact_id integer;
        UPDATE res_letterbox SET dest_contact_id = mlb_coll_ext.dest_contact_id FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN dest_user_id character varying(128);
        UPDATE res_letterbox SET dest_user_id = mlb_coll_ext.dest_user_id FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN alt_identifier character varying(256);
        UPDATE res_letterbox SET alt_identifier = mlb_coll_ext.alt_identifier FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN admission_date timestamp without time zone;
        UPDATE res_letterbox SET admission_date = mlb_coll_ext.admission_date FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN process_limit_date timestamp without time zone;
        UPDATE res_letterbox SET process_limit_date = mlb_coll_ext.process_limit_date FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN closing_date timestamp without time zone;
        UPDATE res_letterbox SET closing_date = mlb_coll_ext.closing_date FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN flag_alarm1 character(1) DEFAULT 'N'::character varying;
        UPDATE res_letterbox SET flag_alarm1 = mlb_coll_ext.flag_alarm1 FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN flag_alarm2 character(1) DEFAULT 'N'::character varying;
        UPDATE res_letterbox SET flag_alarm2 = mlb_coll_ext.flag_alarm2 FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN is_multicontacts character(1);
        UPDATE res_letterbox SET is_multicontacts = mlb_coll_ext.is_multicontacts FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN address_id INTEGER;
        UPDATE res_letterbox SET address_id = mlb_coll_ext.address_id FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN alarm1_date timestamp without time zone;
        UPDATE res_letterbox SET alarm1_date = mlb_coll_ext.alarm1_date FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;

        ALTER TABLE res_letterbox ADD COLUMN alarm2_date timestamp without time zone;
        UPDATE res_letterbox SET alarm2_date = mlb_coll_ext.alarm2_date FROM mlb_coll_ext WHERE res_letterbox.res_id = mlb_coll_ext.res_id;
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
DELETE FROM usergroups_services WHERE service_id = 'add_tag_to_res';
DELETE FROM usergroups_services WHERE service_id = 'tag_view';
DELETE FROM usergroups_services WHERE service_id = 'admin_thesaurus';
DELETE FROM usergroups_services WHERE service_id = 'thesaurus_view';
DELETE FROM usergroups_services WHERE service_id = 'add_thesaurus_to_res';
UPDATE usergroups_services SET service_id = 'manage_tags_application' WHERE service_id = 'create_tag';

INSERT INTO usergroups_services (group_id, service_id)
SELECT distinct(group_id), 'update_diffusion_indexing'
FROM usergroups_services WHERE service_id = 'edit_recipient_outside_process';
INSERT INTO usergroups_services (group_id, service_id)
SELECT distinct(group_id), 'update_diffusion_details'
FROM usergroups_services WHERE service_id = 'edit_recipient_outside_process';
INSERT INTO usergroups_services (group_id, service_id)
SELECT distinct(group_id), 'update_diffusion_except_recipient_details'
FROM usergroups_services WHERE service_id = 'update_list_diff_in_details';

INSERT INTO usergroups_services (group_id, service_id)
SELECT distinct(group_id), 'update_diffusion_except_recipient_indexing'
FROM usergroups_services WHERE group_id NOT IN (
SELECT group_id FROM usergroups_services
WHERE service_id = 'edit_recipient_outside_process' OR service_id = 'update_diffusion_indexing' OR service_id = 'update_diffusion_except_recipient_indexing'
);
DELETE FROM usergroups_services WHERE service_id = 'edit_recipient_outside_process';
DELETE FROM usergroups_services WHERE service_id = 'update_list_diff_in_details';
DELETE FROM usergroups_services WHERE service_id = 'edit_recipient_in_process';


/* REFACTORING MODIFICATION */
ALTER TABLE notif_email_stack ALTER COLUMN attachments TYPE text;
ALTER TABLE tags ALTER COLUMN label TYPE character varying(128);
UPDATE priorities SET delays = 30 WHERE delays IS NULL;
ALTER TABLE priorities ALTER COLUMN delays SET NOT NULL;


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
ALTER TABLE priorities DROP COLUMN IF EXISTS working_days;
DROP TABLE IF EXISTS thesaurus;
DROP TABLE IF EXISTS thesaurus_res;
DROP SEQUENCE IF EXISTS thesaurus_id_seq;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS title;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS description;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS author;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS identifier;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS source;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS relation;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS offset_doc;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS is_multi_docservers;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS tablename;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS validation_date;
ALTER TABLE listinstance DROP COLUMN IF EXISTS added_by_entity;
ALTER TABLE listinstance DROP COLUMN IF EXISTS coll_id;
ALTER TABLE listinstance DROP COLUMN IF EXISTS listinstance_type;
ALTER TABLE listinstance DROP COLUMN IF EXISTS visible;
ALTER TABLE listinstance_history_details DROP COLUMN IF EXISTS added_by_entity;



/* RE CREATE VIEWS */
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
INSERT INTO custom_fields (id, label, type, values) VALUES (2, 'N° recommandé', 'string', '[]');
SELECT setval('custom_fields_id_seq', (select max(id)+1 from custom_fields), false);

TRUNCATE TABLE indexing_models;
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (1, 'incoming', 'Courrier arrivée', TRUE, 23, FALSE);
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (2, 'outgoing', 'Courrier départ', FALSE, 23, FALSE);
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (3, 'internal', 'Courrier interne', FALSE, 23, FALSE);
INSERT INTO indexing_models (id, category, label, "default", owner, private) VALUES (4, 'ged_doc', 'Document ged', FALSE, 23, FALSE);
Select setval('indexing_models_id_seq', (select max(id)+1 from indexing_models), false);

TRUNCATE TABLE indexing_models_fields;
/* Arrivée */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'doctype', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'priority', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'confidential', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'documentDate', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'arrivalDate', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'subject', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'indexingCustomField_1', FALSE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'senders', TRUE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'getRecipients', FALSE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'initiator', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'destination', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'processLimitDate', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'folders', FALSE, null, 'classifying');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (1, 'tags', FALSE, null, 'classifying');

/* Départ */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'doctype', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'priority', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'confidential', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'documentDate', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'subject', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'indexingCustomField_1', FALSE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'senders', FALSE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'getRecipients', TRUE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'initiator', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'destination', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'processLimitDate', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'folders', FALSE, null, 'classifying');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (2, 'tags', FALSE, null, 'classifying');

/* Interne */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'doctype', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'priority', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'confidential', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'documentDate', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'subject', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'indexingCustomField_1', FALSE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'senders', FALSE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'getRecipients', FALSE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'initiator', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'destination', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'processLimitDate', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'folders', FALSE, null, 'classifying');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (3, 'tags', FALSE, null, 'classifying');

/* GED */
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'doctype', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'confidential', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'documentDate', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'subject', TRUE, null, 'mail');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'senders', FALSE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'getRecipients', FALSE, null, 'contact');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'initiator', TRUE, null, 'process');
INSERT INTO indexing_models_fields (model_id, identifier, mandatory, default_value, unit) VALUES (4, 'destination', TRUE, null, 'process');
