
-- ************************************************************************* --
--                               ALL VIEWS DROP                              --
-- ************************************************************************* --

DROP VIEW IF EXISTS view_postindexing;
DROP VIEW IF EXISTS res_view_letterbox;
DROP VIEW IF EXISTS res_view_business;
DROP VIEW IF EXISTS res_view;
DROP VIEW IF EXISTS view_contacts;

-- ************************************************************************* --
--                               MULTICONTACTS                               --
-- ************************************************************************* --

-- multicontacts
DROP TABLE IF EXISTS contacts_res;
CREATE TABLE contacts_res
(
  coll_id character varying(32) NOT NULL,
  res_id bigint NOT NULL,
  contact_id character varying(128) NOT NULL,
  address_id bigint NOT NULL
);

-- ************************************************************************* --
--                          NEW CONTACTS MANAGEMENTS                         --
-- ************************************************************************* --

-- Contact types
DROP TABLE IF EXISTS contact_types;
DROP SEQUENCE IF EXISTS contact_types_id_seq;

CREATE SEQUENCE contact_types_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contact_types 
(
    id bigint NOT NULL DEFAULT nextval('contact_types_id_seq'::regclass),
    label character varying(255) NOT NULL,
    CONSTRAINT contact_types_pkey PRIMARY KEY  (id)
) WITH (OIDS=FALSE);

-- Contacts v2
DROP TABLE IF EXISTS contacts_v2;
DROP SEQUENCE IF EXISTS contact_v2_id_seq;

CREATE SEQUENCE contact_v2_id_seq
  INCREMENT 1
  MINVALUE 14
  MAXVALUE 9223372036854775807
  START 200
  CACHE 1;

CREATE TABLE contacts_v2 
(
    contact_id bigint NOT NULL DEFAULT nextval('contact_v2_id_seq'::regclass),
    contact_type bigint NOT NULL,
    is_corporate_person character(1) DEFAULT 'Y'::bpchar,
    society character varying(255),
    society_short character varying(32),
    firstname character varying(255),
    lastname character varying(255),
    title character varying(255),
    function character varying(255),
    other_data character varying(255),
    user_id character varying(255) NOT NULL,
    entity_id character varying(32) NOT NULL,
    creation_date timestamp without time zone NOT NULL,
    update_date timestamp without time zone,
    enabled character varying(1) NOT NULL DEFAULT 'Y'::bpchar,
    CONSTRAINT contacts_v2_pkey PRIMARY KEY  (contact_id)
) WITH (OIDS=FALSE);

-- Contact purposes
DROP TABLE IF EXISTS contact_purposes;
DROP SEQUENCE IF EXISTS contact_purposes_id_seq;

CREATE SEQUENCE contact_purposes_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contact_purposes 
(
    id bigint NOT NULL DEFAULT nextval('contact_purposes_id_seq'::regclass),
    label character varying(255) NOT NULL,
    CONSTRAINT contact_purposes_pkey PRIMARY KEY  (id)
) WITH (OIDS=FALSE);

-- Contact addresses
DROP TABLE IF EXISTS contact_addresses;
DROP SEQUENCE IF EXISTS contact_addresses_id_seq;

CREATE SEQUENCE contact_addresses_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contact_addresses 
(
    id bigint NOT NULL DEFAULT nextval('contact_addresses_id_seq'::regclass),
    contact_id bigint NOT NULL,
    contact_purpose_id bigint DEFAULT 1,
    departement character varying(255),
    firstname character varying(255),
    lastname character varying(255),
    title character varying(255),
    function character varying(255),
    occupancy character varying(1024),
    address_num character varying(32)  ,
    address_street character varying(255),
    address_complement character varying(255),
    address_town character varying(255),
    address_postal_code character varying(255),
    address_country character varying(255),
    phone character varying(20),
    email character varying(255),
    website character varying(255),
    salutation_header character varying(255),
    salutation_footer character varying(255),
    other_data character varying(255),
    user_id character varying(255) NOT NULL,
    entity_id character varying(32) NOT NULL,
    is_private character(1) NOT NULL DEFAULT 'N'::bpchar,
    enabled character varying(1) NOT NULL DEFAULT 'Y'::bpchar,
    CONSTRAINT contact_addresses_pkey PRIMARY KEY  (id)
) WITH (OIDS=FALSE);


-- ************************************************************************* --
--                               ACTIONS IN RELATION WITH CATEGORIES         --
-- ************************************************************************* --

-- actions / category
ALTER TABLE actions DROP COLUMN IF EXISTS category_id; 
--ALTER TABLE actions ADD category_id character varying(255);

DROP TABLE IF EXISTS actions_categories;
CREATE TABLE actions_categories
(
  action_id bigint NOT NULL,
  category_id character varying(255) NOT NULL,
  CONSTRAINT actions_categories_pkey PRIMARY KEY (action_id,category_id)
);


-- ************************************************************************* --
--                               NEW COLUMNS IN EXTENSIONS TABLE             --
-- ************************************************************************* --

ALTER TABLE mlb_coll_ext DROP COLUMN IF EXISTS address_id;
ALTER TABLE mlb_coll_ext ADD address_id bigint;

ALTER TABLE business_coll_ext DROP COLUMN IF EXISTS address_id;
ALTER TABLE business_coll_ext ADD address_id bigint;


-- ************************************************************************* --
--                               NEW COLUMNS INTO TABLES                     --
-- ************************************************************************* --

ALTER TABLE templates DROP COLUMN IF EXISTS template_target; 
ALTER TABLE templates ADD template_target character varying(255);

ALTER TABLE entities DROP COLUMN IF EXISTS entity_path;
ALTER TABLE entities ADD entity_path character varying(2048);

ALTER TABLE mlb_coll_ext DROP COLUMN IF EXISTS is_multicontacts; 
ALTER TABLE mlb_coll_ext ADD is_multicontacts character(1);

ALTER TABLE res_x DROP COLUMN IF EXISTS reference_number; 
ALTER TABLE res_x ADD reference_number character varying(255) DEFAULT NULL::character varying;

ALTER TABLE res_x DROP COLUMN IF EXISTS locker_user_id; 
ALTER TABLE res_x ADD locker_user_id character varying(255) DEFAULT NULL::character varying;

ALTER TABLE res_x DROP COLUMN IF EXISTS locker_time; 
ALTER TABLE res_x ADD locker_time timestamp without time zone;

ALTER TABLE res_letterbox DROP COLUMN IF EXISTS reference_number; 
ALTER TABLE res_letterbox ADD reference_number character varying(255) DEFAULT NULL::character varying;

ALTER TABLE res_letterbox DROP COLUMN IF EXISTS locker_user_id; 
ALTER TABLE res_letterbox ADD locker_user_id character varying(255) DEFAULT NULL::character varying;

ALTER TABLE res_letterbox DROP COLUMN IF EXISTS locker_time; 
ALTER TABLE res_letterbox ADD locker_time timestamp without time zone;

ALTER TABLE res_business DROP COLUMN IF EXISTS reference_number; 
ALTER TABLE res_business ADD reference_number character varying(255) DEFAULT NULL::character varying;

ALTER TABLE res_business DROP COLUMN IF EXISTS locker_user_id; 
ALTER TABLE res_business ADD locker_user_id character varying(255) DEFAULT NULL::character varying;

ALTER TABLE res_business DROP COLUMN IF EXISTS locker_time; 
ALTER TABLE res_business ADD locker_time timestamp without time zone;

ALTER TABLE lc_stack ADD COLUMN work_batch bigint;
ALTER TABLE lc_stack ADD COLUMN regex character varying(32);

-- ************************************************************************* --
--                               RECREATE VIEWS                              --
-- ************************************************************************* --

-- view for letterbox
CREATE VIEW res_view_letterbox AS
    SELECT r.tablename, r.is_multi_docservers, r.res_id, r.type_id, r.policy_id, r.cycle_id, 
    d.description AS type_label, d.doctypes_first_level_id,
    dfl.doctypes_first_level_label, dfl.css_style as doctype_first_level_style,
    d.doctypes_second_level_id, dsl.doctypes_second_level_label,
    dsl.css_style as doctype_second_level_style, r.format, r.typist,
    r.creation_date, r.relation, r.docserver_id, r.folders_system_id,
    f.folder_id, f.is_frozen as folder_is_frozen, r.path, r.filename, r.fingerprint, r.offset_doc, r.filesize,
    r.status, r.work_batch, r.arbatch_id, r.arbox_id, r.page_count, r.is_paper,
    r.doc_date, r.scan_date, r.scan_user, r.scan_location, r.scan_wkstation,
    r.scan_batch, r.doc_language, r.description, r.source, r.author, r.reference_number,
    r.custom_t1 AS doc_custom_t1, r.custom_t2 AS doc_custom_t2,
    r.custom_t3 AS doc_custom_t3, r.custom_t4 AS doc_custom_t4,
    r.custom_t5 AS doc_custom_t5, r.custom_t6 AS doc_custom_t6,
    r.custom_t7 AS doc_custom_t7, r.custom_t8 AS doc_custom_t8,
    r.custom_t9 AS doc_custom_t9, r.custom_t10 AS doc_custom_t10,
    r.custom_t11 AS doc_custom_t11, r.custom_t12 AS doc_custom_t12,
    r.custom_t13 AS doc_custom_t13, r.custom_t14 AS doc_custom_t14,
    r.custom_t15 AS doc_custom_t15, r.custom_d1 AS doc_custom_d1,
    r.custom_d2 AS doc_custom_d2, r.custom_d3 AS doc_custom_d3,
    r.custom_d4 AS doc_custom_d4, r.custom_d5 AS doc_custom_d5,
    r.custom_d6 AS doc_custom_d6, r.custom_d7 AS doc_custom_d7,
    r.custom_d8 AS doc_custom_d8, r.custom_d9 AS doc_custom_d9,
    r.custom_d10 AS doc_custom_d10, r.custom_n1 AS doc_custom_n1,
    r.custom_n2 AS doc_custom_n2, r.custom_n3 AS doc_custom_n3,
    r.custom_n4 AS doc_custom_n4, r.custom_n5 AS doc_custom_n5,
    r.custom_f1 AS doc_custom_f1, r.custom_f2 AS doc_custom_f2,
    r.custom_f3 AS doc_custom_f3, r.custom_f4 AS doc_custom_f4,
    r.custom_f5 AS doc_custom_f5, f.foldertype_id, ft.foldertype_label,
    f.custom_t1 AS fold_custom_t1, f.custom_t2 AS fold_custom_t2,
    f.custom_t3 AS fold_custom_t3, f.custom_t4 AS fold_custom_t4,
    f.custom_t5 AS fold_custom_t5, f.custom_t6 AS fold_custom_t6,
    f.custom_t7 AS fold_custom_t7, f.custom_t8 AS fold_custom_t8,
    f.custom_t9 AS fold_custom_t9, f.custom_t10 AS fold_custom_t10,
    f.custom_t11 AS fold_custom_t11, f.custom_t12 AS fold_custom_t12,
    f.custom_t13 AS fold_custom_t13, f.custom_t14 AS fold_custom_t14,
    f.custom_t15 AS fold_custom_t15, f.custom_d1 AS fold_custom_d1,
    f.custom_d2 AS fold_custom_d2, f.custom_d3 AS fold_custom_d3,
    f.custom_d4 AS fold_custom_d4, f.custom_d5 AS fold_custom_d5,
    f.custom_d6 AS fold_custom_d6, f.custom_d7 AS fold_custom_d7,
    f.custom_d8 AS fold_custom_d8, f.custom_d9 AS fold_custom_d9,
    f.custom_d10 AS fold_custom_d10, f.custom_n1 AS fold_custom_n1,
    f.custom_n2 AS fold_custom_n2, f.custom_n3 AS fold_custom_n3,
    f.custom_n4 AS fold_custom_n4, f.custom_n5 AS fold_custom_n5,
    f.custom_f1 AS fold_custom_f1, f.custom_f2 AS fold_custom_f2,
    f.custom_f3 AS fold_custom_f3, f.custom_f4 AS fold_custom_f4,
    f.custom_f5 AS fold_custom_f5, f.is_complete AS fold_complete,
    f.status AS fold_status, f.subject AS fold_subject,
    f.parent_id AS fold_parent_id, f.folder_level, f.folder_name,
    f.creation_date AS fold_creation_date, r.initiator, r.destination,
    r.dest_user, mlb.category_id, mlb.exp_contact_id, mlb.exp_user_id,
    mlb.dest_user_id, mlb.dest_contact_id, mlb.address_id, mlb.nature_id, mlb.alt_identifier,
    mlb.admission_date, mlb.answer_type_bitmask, mlb.other_answer_desc,
    mlb.process_limit_date, mlb.closing_date, mlb.alarm1_date, mlb.alarm2_date,
    mlb.flag_notif, mlb.flag_alarm1, mlb.flag_alarm2, mlb.is_multicontacts, r.video_user, r.video_time,
    r.video_batch, r.subject, r.identifier, r.title, r.priority, mlb.process_notes,
	r.locker_user_id, r.locker_time,
    ca.case_id, ca.case_label, ca.case_description, en.entity_label,
    cont.contact_id AS contact_id,
    cont.firstname AS contact_firstname, cont.lastname AS contact_lastname,
    cont.society AS contact_society, u.lastname AS user_lastname,
    u.firstname AS user_firstname, list.item_id AS dest_user_from_listinstance, list.viewed, 
    r.is_frozen as res_is_frozen, COALESCE(att.count_attachment, 0::bigint) AS count_attachment 
    FROM doctypes d, doctypes_first_level dfl, doctypes_second_level dsl,
    (((((((((((ar_batch a RIGHT JOIN res_letterbox r ON ((r.arbatch_id = a.arbatch_id)))
    LEFT JOIN (SELECT res_attachments.res_id_master, count(res_attachments.res_id_master) AS count_attachment
        FROM res_attachments WHERE res_attachments.status <> 'DEL' GROUP BY res_attachments.res_id_master) att ON (r.res_id = att.res_id_master))
    LEFT JOIN entities en ON (((r.destination)::text = (en.entity_id)::text)))
    LEFT JOIN folders f ON ((r.folders_system_id = f.folders_system_id)))
    LEFT JOIN cases_res cr ON ((r.res_id = cr.res_id)))
    LEFT JOIN mlb_coll_ext mlb ON ((mlb.res_id = r.res_id)))
    LEFT JOIN foldertypes ft ON (((f.foldertype_id = ft.foldertype_id)
        AND ((f.status)::text <> 'DEL'::text))))
    LEFT JOIN cases ca ON ((cr.case_id = ca.case_id)))
    LEFT JOIN contacts_v2 cont ON (((mlb.exp_contact_id = cont.contact_id)
        OR (mlb.dest_contact_id = cont.contact_id))))
    LEFT JOIN users u ON ((((mlb.exp_user_id)::text = (u.user_id)::text)
        OR ((mlb.dest_user_id)::text = (u.user_id)::text))))
    LEFT JOIN listinstance list ON (((r.res_id = list.res_id)
        AND ((list.item_mode)::text = 'dest'::text))))
    WHERE (((r.type_id = d.type_id) AND
    (d.doctypes_first_level_id = dfl.doctypes_first_level_id))
    AND (d.doctypes_second_level_id = dsl.doctypes_second_level_id));

-- view for business
CREATE VIEW res_view_business AS
    SELECT r.tablename, r.is_multi_docservers, r.res_id, r.type_id,
    d.description AS type_label, d.doctypes_first_level_id,
    d.doctypes_second_level_id, dfl.doctypes_first_level_label, 
    dfl.css_style as doctype_first_level_style,
    dsl.doctypes_second_level_label,
    dsl.css_style as doctype_second_level_style, r.format, r.typist,
    r.creation_date, r.relation, r.docserver_id, r.folders_system_id,
    f.folder_id, f.is_frozen as folder_is_frozen, r.path, r.filename, 
    r.fingerprint, r.offset_doc, r.filesize,
    r.status, r.work_batch, r.arbatch_id, r.arbox_id, r.page_count, r.is_paper,
    r.doc_date, r.scan_date, r.scan_user, r.scan_location, r.scan_wkstation,
    r.scan_batch, r.doc_language, r.description, r.source, r.author, r.reference_number,
    r.custom_t1 AS doc_custom_t1, r.custom_t2 AS doc_custom_t2,
    r.custom_t3 AS doc_custom_t3, r.custom_t4 AS doc_custom_t4,
    r.custom_t5 AS doc_custom_t5, r.custom_t6 AS doc_custom_t6,
    r.custom_t7 AS doc_custom_t7, r.custom_t8 AS doc_custom_t8,
    r.custom_t9 AS doc_custom_t9, r.custom_t10 AS doc_custom_t10,
    r.custom_t11 AS doc_custom_t11, r.custom_t12 AS doc_custom_t12,
    r.custom_t13 AS doc_custom_t13, r.custom_t14 AS doc_custom_t14,
    r.custom_t15 AS doc_custom_t15, r.custom_d1 AS doc_custom_d1,
    r.custom_d2 AS doc_custom_d2, r.custom_d3 AS doc_custom_d3,
    r.custom_d4 AS doc_custom_d4, r.custom_d5 AS doc_custom_d5,
    r.custom_d6 AS doc_custom_d6, r.custom_d7 AS doc_custom_d7,
    r.custom_d8 AS doc_custom_d8, r.custom_d9 AS doc_custom_d9,
    r.custom_d10 AS doc_custom_d10, r.custom_n1 AS doc_custom_n1,
    r.custom_n2 AS doc_custom_n2, r.custom_n3 AS doc_custom_n3,
    r.custom_n4 AS doc_custom_n4, r.custom_n5 AS doc_custom_n5,
    r.custom_f1 AS doc_custom_f1, r.custom_f2 AS doc_custom_f2,
    r.custom_f3 AS doc_custom_f3, r.custom_f4 AS doc_custom_f4,
    r.custom_f5 AS doc_custom_f5, f.foldertype_id,
    f.custom_t1 AS fold_custom_t1, f.custom_t2 AS fold_custom_t2,
    f.custom_t3 AS fold_custom_t3, f.custom_t4 AS fold_custom_t4,
    f.custom_t5 AS fold_custom_t5, f.custom_t6 AS fold_custom_t6,
    f.custom_t7 AS fold_custom_t7, f.custom_t8 AS fold_custom_t8,
    f.custom_t9 AS fold_custom_t9, f.custom_t10 AS fold_custom_t10,
    f.custom_t11 AS fold_custom_t11, f.custom_t12 AS fold_custom_t12,
    f.custom_t13 AS fold_custom_t13, f.custom_t14 AS fold_custom_t14,
    f.custom_t15 AS fold_custom_t15, f.custom_d1 AS fold_custom_d1,
    f.custom_d2 AS fold_custom_d2, f.custom_d3 AS fold_custom_d3,
    f.custom_d4 AS fold_custom_d4, f.custom_d5 AS fold_custom_d5,
    f.custom_d6 AS fold_custom_d6, f.custom_d7 AS fold_custom_d7,
    f.custom_d8 AS fold_custom_d8, f.custom_d9 AS fold_custom_d9,
    f.custom_d10 AS fold_custom_d10, f.custom_n1 AS fold_custom_n1,
    f.custom_n2 AS fold_custom_n2, f.custom_n3 AS fold_custom_n3,
    f.custom_n4 AS fold_custom_n4, f.custom_n5 AS fold_custom_n5,
    f.custom_f1 AS fold_custom_f1, f.custom_f2 AS fold_custom_f2,
    f.custom_f3 AS fold_custom_f3, f.custom_f4 AS fold_custom_f4,
    f.custom_f5 AS fold_custom_f5, f.is_complete AS fold_complete,
    f.status AS fold_status, f.subject AS fold_subject,
    f.parent_id AS fold_parent_id, f.folder_level, f.folder_name,
    f.creation_date AS fold_creation_date, r.initiator, r.destination,
    r.dest_user, busi.category_id, busi.contact_id, busi.address_id, busi.currency,
	r.locker_user_id, r.locker_time,	
    busi.net_sum, busi.tax_sum, busi.total_sum, 
    busi.process_limit_date, busi.closing_date, busi.alarm1_date, busi.alarm2_date,
    busi.flag_notif, busi.flag_alarm1, busi.flag_alarm2, r.video_user, r.video_time,
    r.video_batch, r.subject, r.identifier, r.title, r.priority,
    en.entity_label,
    cont.firstname AS contact_firstname, cont.lastname AS contact_lastname,
    cont.society AS contact_society, list.item_id AS dest_user_from_listinstance,  list.viewed, 
    r.is_frozen as res_is_frozen, COALESCE(att.count_attachment, 0::bigint) AS count_attachment 
    FROM doctypes d, doctypes_first_level dfl, doctypes_second_level dsl, res_business r
    LEFT JOIN (SELECT res_attachments.res_id_master, coll_id, count(res_attachments.res_id_master) AS count_attachment
        FROM res_attachments WHERE res_attachments.status <> 'DEL' GROUP BY res_attachments.res_id_master, coll_id) att ON (r.res_id = att.res_id_master and att.coll_id = 'business_coll')
    LEFT JOIN entities en ON ((r.destination)::text = (en.entity_id)::text)
    LEFT JOIN folders f ON ((r.folders_system_id = f.folders_system_id))
    LEFT JOIN business_coll_ext busi ON (busi.res_id = r.res_id)
    LEFT JOIN contacts_v2 cont ON (busi.contact_id = cont.contact_id)
    LEFT JOIN listinstance list ON ((r.res_id = list.res_id)
        AND ((list.item_mode)::text = 'dest'::text))
    WHERE r.type_id = d.type_id 
    AND d.doctypes_first_level_id = dfl.doctypes_first_level_id
    AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;


CREATE VIEW res_view AS
 SELECT r.tablename, r.is_multi_docservers, r.res_id, r.title, r.subject, r.page_count, r.identifier, r.doc_date, r.type_id,
 d.description AS type_label, d.doctypes_first_level_id, dfl.doctypes_first_level_label, dfl.css_style as doctype_first_level_style,
 d.doctypes_second_level_id, dsl.doctypes_second_level_label, dsl.css_style as doctype_second_level_style,
 r.format, r.typist, r.creation_date, r.relation, r.docserver_id,
 r.folders_system_id, r.path, r.filename, r.fingerprint, r.offset_doc, r.filesize, r.status,
 r.work_batch, r.arbatch_id, r.arbox_id,  r.is_paper, r.scan_date, r.scan_user,r.scan_location,r.scan_wkstation,
 r.scan_batch,r.doc_language,r.description,r.source,r.initiator,r.destination,r.dest_user,r.policy_id,r.cycle_id,r.cycle_date,
 r.custom_t1 AS doc_custom_t1, r.custom_t2 AS doc_custom_t2, r.custom_t3 AS doc_custom_t3,
 r.custom_t4 AS doc_custom_t4, r.custom_t5 AS doc_custom_t5, r.custom_t6 AS doc_custom_t6,
 r.custom_t7 AS doc_custom_t7, r.custom_t8 AS doc_custom_t8, r.custom_t9 AS doc_custom_t9,
 r.custom_t10 AS doc_custom_t10, r.custom_t11 AS doc_custom_t11, r.custom_t12 AS doc_custom_t12,
 r.custom_t13 AS doc_custom_t13, r.custom_t14 AS doc_custom_t14, r.custom_t15 AS doc_custom_t15,
 r.custom_d1 AS doc_custom_d1, r.custom_d2 AS doc_custom_d2, r.custom_d3 AS doc_custom_d3,
 r.custom_d4 AS doc_custom_d4, r.custom_d5 AS doc_custom_d5, r.custom_d6 AS doc_custom_d6,
 r.custom_d7 AS doc_custom_d7, r.custom_d8 AS doc_custom_d8, r.custom_d9 AS doc_custom_d9,
 r.custom_d10 AS doc_custom_d10, r.custom_n1 AS doc_custom_n1, r.custom_n2 AS doc_custom_n2,
 r.custom_n3 AS doc_custom_n3, r.custom_n4 AS doc_custom_n4, r.custom_n5 AS doc_custom_n5,
 r.custom_f1 AS doc_custom_f1, r.custom_f2 AS doc_custom_f2, r.custom_f3 AS doc_custom_f3,
 r.custom_f4 AS doc_custom_f4, r.custom_f5 AS doc_custom_f5, r.is_frozen as res_is_frozen,  
 r.reference_number, r.locker_user_id, r.locker_time
   FROM  doctypes d, doctypes_first_level dfl, doctypes_second_level dsl, res_x r
   WHERE r.type_id = d.type_id
   AND d.doctypes_first_level_id = dfl.doctypes_first_level_id
   AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;


--view for postindexing
CREATE VIEW view_postindexing AS
SELECT res_view_letterbox.video_user, (users.firstname::text || ' '::text) || users.lastname::text AS user_name, res_view_letterbox.video_batch, res_view_letterbox.video_time, count(res_view_letterbox.res_id) AS count_documents, res_view_letterbox.folders_system_id, (folders.folder_id::text || ' / '::text) || folders.folder_name::text AS folder_full_label, folders.video_status
FROM res_view_letterbox
LEFT JOIN users ON res_view_letterbox.video_user::text = users.user_id::text
LEFT JOIN folders ON folders.folders_system_id = res_view_letterbox.folders_system_id
WHERE res_view_letterbox.video_batch IS NOT NULL
GROUP BY res_view_letterbox.video_user, (users.firstname::text || ' '::text) || users.lastname::text, res_view_letterbox.video_batch, res_view_letterbox.video_time, res_view_letterbox.folders_system_id, (folders.folder_id::text || ' / '::text) || folders.folder_name::text, folders.video_status;


--view for contacts_v2
CREATE VIEW view_contacts AS 
 SELECT c.contact_id, c.contact_type, c.is_corporate_person, c.society, c.society_short, c.firstname AS contact_firstname
, c.lastname AS contact_lastname, c.title AS contact_title, c.function AS contact_function, c.other_data AS contact_other_data
, c.user_id AS contact_user_id, c.entity_id AS contact_entity_id, c.creation_date, c.update_date, c.enabled AS contact_enabled, ca.id AS ca_id
, ca.contact_purpose_id, ca.departement, ca.firstname, ca.lastname, ca.title, ca.function, ca.occupancy
, ca.address_num, ca.address_street, ca.address_complement, ca.address_town, ca.address_postal_code, ca.address_country
, ca.phone, ca.email, ca.website, ca.salutation_header, ca.salutation_footer, ca.other_data, ca.user_id, ca.entity_id, ca.is_private, ca.enabled
, cp.label as contact_purpose_label, ct.label as contact_type_label
   FROM contacts_v2 c
   RIGHT JOIN contact_addresses ca ON c.contact_id = ca.contact_id
   LEFT JOIN contact_purposes cp ON ca.contact_purpose_id = cp.id
   LEFT JOIN contact_types ct ON c.contact_type = ct.id;


-- ************************************************************************* --
--                               DATABASE VERSION                            --
-- ************************************************************************* --

UPDATE parameters SET param_value_int = 150 where id='database_version';
