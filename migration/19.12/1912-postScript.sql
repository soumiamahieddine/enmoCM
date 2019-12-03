-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.04 to 20.01 (Run this file after migrate.sh) --
--                                                                          --
--                                                                          --
-- *************************************************************************--

DROP VIEW IF EXISTS res_view_letterbox;
DROP VIEW IF EXISTS view_contacts;

DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS cases_res;

DROP TABLE IF EXISTS contact_addresses;
DROP TABLE IF EXISTS contact_communication;
DROP TABLE IF EXISTS contact_purposes;
DROP TABLE IF EXISTS contact_types;
DROP TABLE IF EXISTS contacts_v2;

DROP TABLE IF EXISTS fp_fileplan;
DROP TABLE IF EXISTS fp_fileplan_positions;
DROP TABLE IF EXISTS fp_res_fileplan_positions;

DROP TABLE IF EXISTS folder_tmp;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS folders_system_id;

DROP TABLE IF EXISTS groupbasket_status;
DROP TABLE IF EXISTS indexingmodels;

DROP TABLE IF EXISTS mlb_coll_ext;
DROP TABLE IF EXISTS res_version_attachments;
DROP TABLE IF EXISTS adr_attachments_version;
ALTER TABLE shippings DROP COLUMN IF EXISTS is_version;

ALTER TABLE priorities DROP COLUMN IF EXISTS default_priority;

DROP TABLE IF EXISTS doctypes_indexes;

ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t1;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t2;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t3;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t4;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t5;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t6;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t7;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t8;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t9;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t10;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t11;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t12;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t13;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t14;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_t15;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d1;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d2;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d3;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d4;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d5;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d6;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d7;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d8;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d9;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_d10;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_n1;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_n2;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_n3;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_n4;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_n5;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_f1;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_f2;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_f3;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_f4;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS custom_f5;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS scan_date;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS scan_user;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS scan_location;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS scan_wkstation;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS scan_batch;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS scan_postmark;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS description;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS author;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS reference_number;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS external_reference;


/* RE CREATE VIEWS */
CREATE OR REPLACE VIEW res_view_letterbox AS
SELECT r.res_id,
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
       r.docserver_id,
       r.path,
       r.filename,
       r.fingerprint,
       r.filesize,
       r.status,
       r.work_batch,
       r.doc_date,
       r.external_id,
       r.external_link,
       r.departure_date,
       r.opinion_limit_date,
       r.department_number_id,
       r.barcode,
       r.external_signatory_book_id,
       r.initiator,
       r.destination,
       r.dest_user,
       r.confidentiality,
       r.category_id,
       r.exp_contact_id,
       r.exp_user_id,
       r.dest_user_id,
       r.dest_contact_id,
       r.address_id,
       r.alt_identifier,
       r.admission_date,
       r.process_limit_date,
       r.closing_date,
       r.alarm1_date,
       r.alarm2_date,
       r.flag_alarm1,
       r.flag_alarm2,
       r.is_multicontacts,
       r.subject,
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
         LEFT JOIN contacts_v2 cont ON r.exp_contact_id = cont.contact_id OR r.dest_contact_id = cont.contact_id
         LEFT JOIN users u ON r.exp_user_id::text = u.user_id::text OR r.dest_user_id::text = u.user_id::text
WHERE r.type_id = d.type_id AND d.doctypes_first_level_id = dfl.doctypes_first_level_id AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;
