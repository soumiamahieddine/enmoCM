-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 17.06 to 17.XX          --
--                                                                          --
--                                                                          --
-- *************************************************************************--

DROP VIEW IF EXISTS view_postindexing;
DROP VIEW IF EXISTS res_view_attachments;
DROP VIEW IF EXISTS res_view_letterbox;
DROP TABLE IF EXISTS ar_batch;

DROP SEQUENCE IF EXISTS priorities_seq CASCADE;

DROP TABLE IF EXISTS priorities;
CREATE TABLE priorities
(
  id character varying(16) NOT NULL,
  label character varying(128) NOT NULL,
  color character varying(128) NOT NULL,
  working_days boolean NOT NULL,
  delays integer,
  default_priority boolean NOT NULL DEFAULT FALSE,
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

ALTER TABLE entities DROP COLUMN IF EXISTS archival_agency;
ALTER TABLE entities ADD COLUMN archival_agency character varying(255) DEFAULT 'org_123456789_Archives';

/*PERFS ON VIEW*/
DROP VIEW IF EXISTS res_view_letterbox;
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
    r.arbox_id,
    r.page_count,
    r.is_paper,
    r.doc_date,
    r.scan_date,
    r.scan_user,
    r.scan_location,
    r.scan_wkstation,
    r.scan_batch,
    r.doc_language,
    r.description,
    r.source,
    r.author,
    r.reference_number,
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
ALTER TABLE listinstance DROP COLUMN IF EXISTS requested_signature;
ALTER TABLE listinstance ADD requested_signature boolean default false;
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

UPDATE res_attachments SET in_signature_book = TRUE;
UPDATE res_version_attachments SET in_signature_book = TRUE;
UPDATE listinstance SET signatory = TRUE WHERE item_mode = 'sign';

ALTER TABLE notif_event_stack ALTER COLUMN record_id TYPE character varying(128);

/*BASKETS*/
ALTER TABLE groupbasket DROP COLUMN IF EXISTS sequence;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS redirect_basketlist;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS redirect_grouplist;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS can_redirect;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS can_delete;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS can_insert;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS list_lock_clause;
ALTER TABLE groupbasket DROP COLUMN IF EXISTS sublist_lock_clause;
DROP TABLE IF EXISTS groupbasket_difflist_types;
DROP TABLE IF EXISTS groupbasket_difflist_roles;

/*LISTMODELS*/
ALTER TABLE listmodels DROP COLUMN IF EXISTS listmodel_type;
ALTER TABLE listmodels DROP COLUMN IF EXISTS coll_id;
ALTER TABLE listmodels ADD COLUMN id serial NOT NULL;

-- ************************************************************************* --
--                               CONVERT                             --
-- ************************************************************************* --


ALTER TABLE adr_x DROP COLUMN IF EXISTS adr_type;
ALTER TABLE adr_x ADD COLUMN adr_type character varying(32) NOT NULL DEFAULT 'DOC';
ALTER TABLE adr_attachments DROP COLUMN IF EXISTS adr_type;
ALTER TABLE adr_attachments ADD COLUMN adr_type character varying(32) NOT NULL DEFAULT 'DOC';


--convert result
ALTER TABLE res_attachments DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_attachments ADD COLUMN convert_result character varying(10) DEFAULT NULL::character varying;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_letterbox ADD COLUMN convert_result character varying(10) DEFAULT NULL::character varying;
ALTER TABLE res_x DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_x ADD COLUMN convert_result character varying(10) DEFAULT NULL::character varying;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS convert_result;
ALTER TABLE res_version_attachments ADD COLUMN convert_result character varying(10) DEFAULT NULL::character varying;


--convert attempts
ALTER TABLE res_attachments DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_attachments ADD COLUMN convert_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_letterbox ADD COLUMN convert_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_x DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_x ADD COLUMN convert_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS convert_attempts;
ALTER TABLE res_version_attachments ADD COLUMN convert_attempts integer DEFAULT NULL::integer;

--fulltext attempts
ALTER TABLE res_attachments DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_attachments ADD COLUMN fulltext_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_letterbox ADD COLUMN fulltext_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_x DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_x ADD COLUMN fulltext_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS fulltext_attempts;
ALTER TABLE res_version_attachments ADD COLUMN fulltext_attempts integer DEFAULT NULL::integer;

--tnl attempts
ALTER TABLE res_attachments DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_attachments ADD COLUMN tnl_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_letterbox ADD COLUMN tnl_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_x DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_x ADD COLUMN tnl_attempts integer DEFAULT NULL::integer;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS tnl_attempts;
ALTER TABLE res_version_attachments ADD COLUMN tnl_attempts integer DEFAULT NULL::integer;



--thumbnails result
ALTER TABLE res_attachments DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE res_attachments ADD COLUMN tnl_result character varying(10) DEFAULT NULL::character varying;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE res_letterbox ADD COLUMN tnl_result character varying(10) DEFAULT NULL::character varying;
ALTER TABLE res_x DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE res_x ADD COLUMN tnl_result character varying(10) DEFAULT NULL::character varying;
ALTER TABLE res_version_attachments DROP COLUMN IF EXISTS tnl_result;
ALTER TABLE res_version_attachments ADD COLUMN tnl_result character varying(10) DEFAULT NULL::character varying;

-- adr_letterbox
DROP TABLE IF EXISTS adr_letterbox;
CREATE TABLE adr_letterbox
(
  res_id bigint NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  adr_priority integer NOT NULL,
  adr_type character varying(32) NOT NULL DEFAULT 'DOC'::character varying,
  CONSTRAINT adr_letterbox_pkey PRIMARY KEY (res_id, docserver_id)
)
WITH (OIDS=FALSE);

-- adr_attachments
DROP TABLE IF EXISTS adr_attachments;
CREATE TABLE adr_attachments
(
  res_id bigint NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  adr_priority integer NOT NULL,
  adr_type character varying(32) NOT NULL DEFAULT 'DOC'::character varying,
  CONSTRAINT adr_attachments_pkey PRIMARY KEY (res_id, docserver_id)
)
WITH (OIDS=FALSE);

-- adr_attachments_version
DROP TABLE IF EXISTS adr_attachments_version;
CREATE TABLE adr_attachments_version
(
  res_id bigint NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  adr_priority integer NOT NULL,
  adr_type character varying(32) NOT NULL DEFAULT 'DOC'::character varying,
  CONSTRAINT adr_attachments_version_pkey PRIMARY KEY (res_id, docserver_id)
)
WITH (OIDS=FALSE);

-- convert working table
DROP TABLE IF EXISTS convert_stack;
CREATE TABLE convert_stack
(
  coll_id character varying(32) NOT NULL,
  res_id bigint NOT NULL,
  convert_format character varying(32) NOT NULL DEFAULT 'pdf'::character varying,
  cnt_retry integer,
  status character(1) NOT NULL,
  work_batch bigint,
  regex character varying(32),
  CONSTRAINT convert_stack_pkey PRIMARY KEY (coll_id, res_id, convert_format)
)
WITH (OIDS=FALSE);

-- docservers
UPDATE docservers set docserver_type_id = 'DOC' where docserver_type_id <> 'TEMPLATES' and docserver_type_id <> 'TNL';


DELETE FROM docserver_types where docserver_type_id = 'DOC';
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) 
VALUES ('DOC', 'Documents', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'Y', 'SHA512');

DELETE FROM docserver_types where docserver_type_id = 'CONVERT';
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) 
VALUES ('CONVERT', 'Conversions', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'Y', 'SHA256');

DELETE FROM docservers where docserver_id = 'CONVERT_MLB';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('CONVERT_MLB', 'CONVERT', 'Server for mlb documents conversion', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/convert_mlb/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'letterbox_coll', 13, 'NANTERRE', 4);

DELETE FROM docservers where docserver_id = 'FASTHD_ATTACH';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FASTHD_ATTACH', 'FASTHD', 'Fast internal disc bay for attachments', 'N', 'Y', 50000000000, 1, '/opt/maarch/docservers/manual_attachments/', NULL, NULL, NULL, '2011-01-13 14:47:49.197164', NULL, 'attachments_coll', 2, 'NANTERRE', 3);

DELETE FROM docservers where docserver_id = 'FASTHD_ATTACH_VERSION';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FASTHD_ATTACH_VERSION', 'FASTHD', 'Fast internal disc bay for attachments version', 'N', 'Y', 50000000000, 1, '/opt/maarch/docservers/manual_attachments_version/', NULL, NULL, NULL, '2011-01-13 14:47:49.197164', NULL, 'attachments_version_coll', 100, 'NANTERRE', 100);

DELETE FROM docservers where docserver_id = 'CONVERT_ATTACH';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('CONVERT_ATTACH', 'CONVERT', 'Server for attachments documents conversion', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/convert_attachments/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'attachments_coll', 14, 'NANTERRE', 5);

DELETE FROM docservers where docserver_id = 'CONVERT_ATTACH_VERSION';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('CONVERT_ATTACH_VERSION', 'CONVERT', 'Server for attachments version documents conversion', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/convert_attachments_version/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'attachments_version_coll', 101, 'NANTERRE', 101);

-- for thumbnails, attachments and fulltext :
DELETE FROM docservers where docserver_id = 'TNL_ATTACH';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('TNL_ATTACH', 'TNL', 'Server for thumbnails of attachments', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/thumbnails_attachments/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'attachments_coll', 15, 'NANTERRE', 6);

DELETE FROM docservers where docserver_id = 'TNL_ATTACH_VERSION';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('TNL_ATTACH_VERSION', 'TNL', 'Server for thumbnails of attachments version', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/thumbnails_attachments_version/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'attachments_version_coll', 102, 'NANTERRE', 102);

update docservers set docserver_id = 'TNL_MLB', priority_number = 12 where docserver_id = 'TNL';

DELETE FROM docserver_types where docserver_type_id = 'FULLTEXT';
INSERT INTO docserver_types (docserver_type_id, docserver_type_label, enabled, is_container, container_max_number, is_compressed, compression_mode, is_meta, meta_template, is_logged, log_template, is_signed, fingerprint_mode) 
VALUES ('FULLTEXT', 'FULLTEXT', 'Y', 'N', 0, 'N', 'NONE', 'N', 'NONE', 'N', 'NONE', 'Y', 'SHA256');

DELETE FROM docservers where docserver_id = 'FULLTEXT_MLB';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FULLTEXT_MLB', 'FULLTEXT', 'Server for mlb documents fulltext', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/fulltext_mlb/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'letterbox_coll', 16, 'NANTERRE', 7);

DELETE FROM docservers where docserver_id = 'FULLTEXT_ATTACH';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FULLTEXT_ATTACH', 'FULLTEXT', 'Server for attachments documents fulltext', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/fulltext_attachments/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'attachments_coll', 17, 'NANTERRE', 8);

DELETE FROM docservers where docserver_id = 'FULLTEXT_ATTACH_VERSION';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FULLTEXT_ATTACH_VERSION', 'FULLTEXT', 'Server for attachments version documents fulltext', 'N', 'Y', 50000000000, 0, '/opt/maarch/docservers/fulltext_attachments_version/', NULL, NULL, NULL, '2015-03-16 14:47:49.197164', NULL, 'attachments_version_coll', 103, 'NANTERRE', 103);

ALTER TABLE doctypes DROP COLUMN IF EXISTS primary_retention;
ALTER TABLE doctypes DROP COLUMN IF EXISTS secondary_retention;

UPDATE doctypes_first_level SET css_style = '#D2B48C' WHERE css_style = 'beige';
UPDATE doctypes_first_level SET css_style = '#0000FF' WHERE css_style = 'blue_style';
UPDATE doctypes_first_level SET css_style = '#0000FF' WHERE css_style = 'blue_style_big';
UPDATE doctypes_first_level SET css_style = '#808080' WHERE css_style = 'grey_style';
UPDATE doctypes_first_level SET css_style = '#FFFF00' WHERE css_style = 'yellow_style';
UPDATE doctypes_first_level SET css_style = '#800000' WHERE css_style = 'brown_style';
UPDATE doctypes_first_level SET css_style = '#000000' WHERE css_style = 'black_style';
UPDATE doctypes_first_level SET css_style = '#000000' WHERE css_style = 'black_style_big';
UPDATE doctypes_first_level SET css_style = '#FF4500' WHERE css_style = 'orange_style';
UPDATE doctypes_first_level SET css_style = '#FF4500' WHERE css_style = 'orange_style_big';
UPDATE doctypes_first_level SET css_style = '#FF00FF' WHERE css_style = 'pink_style';
UPDATE doctypes_first_level SET css_style = '#FF0000' WHERE css_style = 'red_style';
UPDATE doctypes_first_level SET css_style = '#008000' WHERE css_style = 'green_style';
UPDATE doctypes_first_level SET css_style = '#800080' WHERE css_style = 'violet_style';
UPDATE doctypes_first_level SET css_style = '#000000' WHERE css_style = 'default_style';

UPDATE doctypes_second_level SET css_style = '#D2B48C' WHERE css_style = 'beige';
UPDATE doctypes_second_level SET css_style = '#0000FF' WHERE css_style = 'blue_style';
UPDATE doctypes_second_level SET css_style = '#0000FF' WHERE css_style = 'blue_style_big';
UPDATE doctypes_second_level SET css_style = '#808080' WHERE css_style = 'grey_style';
UPDATE doctypes_second_level SET css_style = '#FFFF00' WHERE css_style = 'yellow_style';
UPDATE doctypes_second_level SET css_style = '#800000' WHERE css_style = 'brown_style';
UPDATE doctypes_second_level SET css_style = '#000000' WHERE css_style = 'black_style';
UPDATE doctypes_second_level SET css_style = '#000000' WHERE css_style = 'black_style_big';
UPDATE doctypes_second_level SET css_style = '#FF4500' WHERE css_style = 'orange_style';
UPDATE doctypes_second_level SET css_style = '#FF4500' WHERE css_style = 'orange_style_big';
UPDATE doctypes_second_level SET css_style = '#FF00FF' WHERE css_style = 'pink_style';
UPDATE doctypes_second_level SET css_style = '#FF0000' WHERE css_style = 'red_style';
UPDATE doctypes_second_level SET css_style = '#008000' WHERE css_style = 'green_style';
UPDATE doctypes_second_level SET css_style = '#800080' WHERE css_style = 'violet_style';
UPDATE doctypes_second_level SET css_style = '#000000' WHERE css_style = 'default_style';
