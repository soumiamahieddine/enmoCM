-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.04 to develop                                --
--                                                                          --
--                                                                          --
-- *************************************************************************--
UPDATE parameters SET param_value_string = '19.10.1' WHERE id = 'database_version';

DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'users') AND attname = 'enabled') THEN
    UPDATE users SET status = 'SPD' WHERE enabled = 'N' and (status = 'OK' or status = 'ABS');
    ALTER TABLE users DROP COLUMN IF EXISTS enabled;
  END IF;
END$$;

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


/* REFACTORING */
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
