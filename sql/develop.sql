-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.04 to develop                                --
--                                                                          --
--                                                                          --
-- *************************************************************************--
UPDATE parameters SET param_value_string = '19.12' WHERE id = 'database_version';


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
  default_value text,
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
  "default" BOOLEAN NOT NULL,
  owner INTEGER NOT NULL,
  private BOOLEAN NOT NULL,
  CONSTRAINT indexing_models_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

DROP TYPE IF EXISTS indexing_models_fields_type;
CREATE TYPE indexing_models_fields_type AS ENUM ('standard', 'custom');

DROP TABLE IF EXISTS indexing_models_fields;
CREATE TABLE indexing_models_fields
(
  id SERIAL NOT NULL,
  model_id INTEGER NOT NULL,
  type indexing_models_fields_type NOT NULL,
  identifier INTEGER NOT NULL,
  mandatory BOOLEAN NOT NULL,
  value text,
  unit INTEGER,
  CONSTRAINT indexing_models_fields_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


/* REFACTORING DATA */
DELETE FROM usergroup_content WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM usergroups_reports WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM usergroups_services WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM security WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM groupbasket WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM groupbasket_redirect WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM groupbasket_status WHERE group_id in (SELECT group_id FROM usergroups WHERE enabled = 'N');
DELETE FROM users_baskets_preferences WHERE group_serial_id in (SELECT id FROM usergroups WHERE enabled = 'N');
DELETE FROM usergroups WHERE enabled = 'N';
DELETE FROM actions_categories WHERE action_id in (SELECT id FROM actions WHERE enabled = 'N');
DELETE FROM actions_groupbaskets WHERE id_action in (SELECT id FROM actions WHERE enabled = 'N');
DELETE FROM groupbasket_redirect WHERE action_id in (SELECT id FROM actions WHERE enabled = 'N');
DELETE FROM actions WHERE enabled = 'N';


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
