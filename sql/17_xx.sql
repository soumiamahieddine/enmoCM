-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 17.06 to 17.XX          --
--                                                                          --
--                                                                          --
-- *************************************************************************--

DROP SEQUENCE IF EXISTS priorities_seq CASCADE;

DROP TABLE IF EXISTS priorities;
CREATE TABLE priorities
(
  id character varying(16) NOT NULL,
  label character varying(128) NOT NULL,
  color character varying(128) NOT NULL,
  working_days boolean NOT NULL,
  delays integer NOT NULL,
  CONSTRAINT priorities_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


DROP TABLE IF EXISTS status_images;
CREATE TABLE status_images
(
  id serial,
  image_name character varying(128) NOT NULL,
  CONSTRAINT status_images_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

INSERT INTO status_images (image_name) VALUES ('fm-letter-status-new');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-inprogress');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-info');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-wait');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-validated');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-rejected');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-end');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-newmail');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-attr');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-arev');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aval');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aimp');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-imp');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aenv');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-acla');
INSERT INTO status_images (image_name) VALUES ('fm-letter-status-aarch');
INSERT INTO status_images (image_name) VALUES ('fm-letter');
INSERT INTO status_images (image_name) VALUES ('fm-letter-add');
INSERT INTO status_images (image_name) VALUES ('fm-letter-search');
INSERT INTO status_images (image_name) VALUES ('fm-letter-del');
INSERT INTO status_images (image_name) VALUES ('fm-letter-incoming');
INSERT INTO status_images (image_name) VALUES ('fm-letter-outgoing');
INSERT INTO status_images (image_name) VALUES ('fm-letter-internal');
INSERT INTO status_images (image_name) VALUES ('fm-file-fingerprint');
INSERT INTO status_images (image_name) VALUES ('fm-classification-plan-l1');


ALTER TABLE status DROP COLUMN IF EXISTS identifier;
ALTER TABLE status ADD COLUMN identifier serial;

ALTER TABLE users DROP COLUMN IF EXISTS signature_path;
ALTER TABLE users DROP COLUMN IF EXISTS signature_file_name;
ALTER TABLE users DROP COLUMN IF EXISTS docserver_location_id;
ALTER TABLE users DROP COLUMN IF EXISTS delay_number;
ALTER TABLE users DROP COLUMN IF EXISTS department;

DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'users') AND attname = 'id') = 0 THEN
    ALTER TABLE users ADD COLUMN id serial;
    ALTER TABLE users ADD UNIQUE (id);
  END IF;
END$$;

DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'user_signatures') AND attname = 'user_id') THEN
    ALTER TABLE user_signatures DROP COLUMN IF EXISTS user_serial_id;
    ALTER TABLE user_signatures ADD COLUMN user_serial_id integer;
    UPDATE user_signatures set user_serial_id = (select id FROM users where users.user_id = user_signatures.user_id);
    ALTER TABLE user_signatures ALTER COLUMN user_serial_id set not null;
    ALTER TABLE user_signatures DROP COLUMN IF EXISTS user_id;
  END IF;
END$$;

ALTER TABLE usergroups DROP COLUMN IF EXISTS administrator;
ALTER TABLE usergroups DROP COLUMN IF EXISTS custom_right1;
ALTER TABLE usergroups DROP COLUMN IF EXISTS custom_right2;
ALTER TABLE usergroups DROP COLUMN IF EXISTS custom_right3;
ALTER TABLE usergroups DROP COLUMN IF EXISTS custom_right4;

DO $$ BEGIN
  IF (SELECT count(attname) FROM pg_attribute WHERE attrelid = (SELECT oid FROM pg_class WHERE relname = 'usergroups') AND attname = 'id') = 0 THEN
    ALTER TABLE usergroups ADD COLUMN id serial NOT NULL;
    ALTER TABLE usergroups ADD UNIQUE (id);
  END IF;
END$$;


ALTER TABLE sendmail DROP COLUMN IF EXISTS res_version_att_id_list;
ALTER TABLE sendmail ADD COLUMN res_version_att_id_list character varying(255);

/*SALT*/
UPDATE users set password = '$2y$10$C.QSslBKD3yNMfRPuZfcaubFwPKiCkqqOUyAdOr5FSGKPaePwuEjG', change_password = 'Y' WHERE user_id != 'superadmin';
UPDATE users set password = '$2y$10$Vq244c5s2zmldjblmMXEN./Q2qZrqtGVgrbz/l1WfsUJbLco4E.e.' where user_id = 'superadmin';

/*BASKETS COLOR*/
ALTER TABLE baskets DROP COLUMN IF EXISTS color;
ALTER TABLE baskets ADD color character varying(16);
DROP TABLE IF EXISTS users_baskets;
CREATE TABLE users_baskets
(
  id serial NOT NULL,
  user_serial_id integer NOT NULL,
  basket_id character varying(32) NOT NULL,
  group_id character varying(32) NOT NULL,
  color character varying(16),
  CONSTRAINT users_baskets_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

/*ENTITIES FULL NAME*/
ALTER TABLE entities DROP COLUMN IF EXISTS entity_full_name;
ALTER TABLE entities ADD entity_full_name text;

/*SIGNATURE BOOK*/
ALTER TABLE res_attachments DROP COLUMN IF EXISTS in_signature_book;
ALTER TABLE res_attachments ADD in_signature_book boolean default false;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS in_signature_book;
ALTER TABLE res_version_attachments ADD in_signature_book boolean default false;
ALTER TABLE res_attachments DROP COLUMN IF EXISTS signatory_user_serial_id;
ALTER TABLE res_attachments ADD signatory_user_serial_id int;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS signatory_user_serial_id;
ALTER TABLE res_version_attachments ADD signatory_user_serial_id int;
ALTER TABLE listinstance DROP COLUMN IF EXISTS signatory;
ALTER TABLE listinstance ADD signatory boolean default false;
DROP VIEW IF EXISTS res_view_attachments;
CREATE VIEW res_view_attachments AS
  SELECT '0' as res_id, res_id as res_id_version, title, subject, description, publisher, contributor, type_id, format, typist,
    creation_date, fulltext_result, ocr_result, author, author_name, identifier, source,
    doc_language, relation, coverage, doc_date, docserver_id, folders_system_id, arbox_id, path,
    filename, offset_doc, logical_adr, fingerprint, filesize, is_paper, page_count,
    scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark,
    envelop_id, status, destination, approver, validation_date, effective_date, work_batch, origin, is_ingoing, priority, initiator, dest_user,
    coll_id, dest_contact_id, dest_address_id, updated_by, is_multicontacts, is_multi_docservers, res_id_master, attachment_type, attachment_id_master, in_signature_book, signatory_user_serial_id
  FROM res_version_attachments
  UNION ALL
  SELECT res_id, '0' as res_id_version, title, subject, description, publisher, contributor, type_id, format, typist,
    creation_date, fulltext_result, ocr_result, author, author_name, identifier, source,
    doc_language, relation, coverage, doc_date, docserver_id, folders_system_id, arbox_id, path,
    filename, offset_doc, logical_adr, fingerprint, filesize, is_paper, page_count,
    scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark,
    envelop_id, status, destination, approver, validation_date, effective_date, work_batch, origin, is_ingoing, priority, initiator, dest_user,
    coll_id, dest_contact_id, dest_address_id, updated_by, is_multicontacts, is_multi_docservers, res_id_master, attachment_type, '0', in_signature_book, signatory_user_serial_id
  FROM res_attachments;
