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

/* FOLDERS */
ALTER TABLE folders RENAME TO folder_tmp;
CREATE TABLE folders
(
  id serial NOT NULL,
  label character varying(255) NOT NULL,
  public boolean NOT NULL,
  sharing jsonb DEFAULT '{"entities" : []}',
  user_id INTEGER NOT NULL,
  parent_id INTEGER,
  CONSTRAINT folders_pkey PRIMARY KEY (id)
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

/* REFACTORING MODIFICATION */
ALTER TABLE notif_email_stack ALTER COLUMN attachments TYPE text;

/* REFACTORING SUPPRESSION */
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
DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'users') AND attname = 'enabled') THEN
    UPDATE users SET status = 'SPD' WHERE enabled = 'N' and (status = 'OK' or status = 'ABS');
    ALTER TABLE users DROP COLUMN IF EXISTS enabled;
  END IF;
END$$;
