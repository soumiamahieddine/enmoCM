-- res_lc_a
CREATE SEQUENCE res_id_lc_a_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE res_lc_a
(
  res_id bigint NOT NULL DEFAULT nextval('res_id_mlb_seq'::regclass),
  title character varying(255) DEFAULT NULL::character varying,
  subject text,
  description text,
  publisher character varying(255) DEFAULT NULL::character varying,
  contributor character varying(255) DEFAULT NULL::character varying,
  type_id bigint NOT NULL,
  format character varying(50) NOT NULL,
  typist character varying(50) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  fulltext_result character varying(10) DEFAULT NULL,
  ocr_result character varying(10) DEFAULT NULL,
  converter_result character varying(10) DEFAULT NULL,
  author character varying(255) DEFAULT NULL::character varying,
  author_name text,
  identifier character varying(255) DEFAULT NULL::character varying,
  source character varying(255) DEFAULT NULL::character varying,
  doc_language character varying(50) DEFAULT NULL::character varying,
  relation bigint,
  coverage character varying(255) DEFAULT NULL::character varying,
  doc_date timestamp without time zone,
  docserver_id character varying(32) NOT NULL,
  folders_system_id bigint,
  arbox_id character varying(32) DEFAULT NULL::character varying,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  logical_adr character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  is_paper character(1) DEFAULT NULL::bpchar,
  page_count integer,
  scan_date timestamp without time zone,
  scan_user character varying(50) DEFAULT NULL::character varying,
  scan_location character varying(255) DEFAULT NULL::character varying,
  scan_wkstation character varying(255) DEFAULT NULL::character varying,
  scan_batch character varying(50) DEFAULT NULL::character varying,
  burn_batch character varying(50) DEFAULT NULL::character varying,
  scan_postmark character varying(50) DEFAULT NULL::character varying,
  envelop_id bigint,
  status character varying(10) DEFAULT NULL::character varying,
  destination character varying(50) DEFAULT NULL::character varying,
  approver character varying(50) DEFAULT NULL::character varying,
  validation_date timestamp without time zone,
  work_batch bigint,
  origin character varying(50) DEFAULT NULL::character varying,
  is_ingoing character(1) DEFAULT NULL::bpchar,
  priority smallint,
  arbatch_id bigint DEFAULT NULL,
  custom_t1 text,
  custom_n1 bigint,
  custom_f1 numeric,
  custom_d1 timestamp without time zone,
  custom_t2 character varying(255) DEFAULT NULL::character varying,
  custom_n2 bigint,
  custom_f2 numeric,
  custom_d2 timestamp without time zone,
  custom_t3 character varying(255) DEFAULT NULL::character varying,
  custom_n3 bigint,
  custom_f3 numeric,
  custom_d3 timestamp without time zone,
  custom_t4 character varying(255) DEFAULT NULL::character varying,
  custom_n4 bigint,
  custom_f4 numeric,
  custom_d4 timestamp without time zone,
  custom_t5 character varying(255) DEFAULT NULL::character varying,
  custom_n5 bigint,
  custom_f5 numeric,
  custom_d5 timestamp without time zone,
  custom_t6 character varying(255) DEFAULT NULL::character varying,
  custom_d6 timestamp without time zone,
  custom_t7 character varying(255) DEFAULT NULL::character varying,
  custom_d7 timestamp without time zone,
  custom_t8 character varying(255) DEFAULT NULL::character varying,
  custom_d8 timestamp without time zone,
  custom_t9 character varying(255) DEFAULT NULL::character varying,
  custom_d9 timestamp without time zone,
  custom_t10 character varying(255) DEFAULT NULL::character varying,
  custom_d10 timestamp without time zone,
  custom_t11 character varying(255) DEFAULT NULL::character varying,
  custom_t12 character varying(255) DEFAULT NULL::character varying,
  custom_t13 character varying(255) DEFAULT NULL::character varying,
  custom_t14 character varying(255) DEFAULT NULL::character varying,
  custom_t15 character varying(255) DEFAULT NULL::character varying,
  tablename character varying(32) DEFAULT 'res_invoices'::character varying,
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(50) DEFAULT NULL::character varying,
  video_batch integer DEFAULT NULL,
  video_time integer DEFAULT NULL,
  video_user character varying(50)  DEFAULT NULL,
  video_date timestamp without time zone,
  CONSTRAINT res_lc_a_pkey PRIMARY KEY  (res_id)
)
WITH (OIDS=FALSE);

CREATE OR REPLACE VIEW res_view_lc_a AS 
 SELECT r.tablename, r.res_id, r.type_id, d.description AS type_label, d.doctypes_first_level_id, dfl.doctypes_first_level_label, d.doctypes_second_level_id, dsl.doctypes_second_level_label, r.format, r.typist, r.creation_date, r.relation, r.docserver_id, r.folders_system_id, f.folder_id, r.path, r.filename, r.fingerprint, r.filesize, r.status, r.work_batch, r.arbatch_id, r.arbox_id, r.page_count, r.is_paper, r.doc_date, r.scan_date, r.scan_user, r.scan_location, r.scan_wkstation, r.scan_batch, r.doc_language, r.description, r.source, r.author, r.custom_t1 AS doc_custom_t1, r.custom_t2 AS doc_custom_t2, r.custom_t3 AS doc_custom_t3, r.custom_t4 AS doc_custom_t4, r.custom_t5 AS doc_custom_t5, r.custom_t6 AS doc_custom_t6, r.custom_t7 AS doc_custom_t7, r.custom_t8 AS doc_custom_t8, r.custom_t9 AS doc_custom_t9, r.custom_t10 AS doc_custom_t10, r.custom_t11 AS doc_custom_t11, r.custom_t12 AS doc_custom_t12, r.custom_t13 AS doc_custom_t13, r.custom_t14 AS doc_custom_t14, r.custom_t15 AS doc_custom_t15, r.custom_d1 AS doc_custom_d1, r.custom_d2 AS doc_custom_d2, r.custom_d3 AS doc_custom_d3, r.custom_d4 AS doc_custom_d4, r.custom_d5 AS doc_custom_d5, r.custom_d6 AS doc_custom_d6, r.custom_d7 AS doc_custom_d7, r.custom_d8 AS doc_custom_d8, r.custom_d9 AS doc_custom_d9, r.custom_d10 AS doc_custom_d10, r.custom_n1 AS doc_custom_n1, r.custom_n2 AS doc_custom_n2, r.custom_n3 AS doc_custom_n3, r.custom_n4 AS doc_custom_n4, r.custom_n5 AS doc_custom_n5, r.custom_f1 AS doc_custom_f1, r.custom_f2 AS doc_custom_f2, r.custom_f3 AS doc_custom_f3, r.custom_f4 AS doc_custom_f4, r.custom_f5 AS doc_custom_f5, f.foldertype_id, ft.foldertype_label, f.custom_t1 AS fold_custom_t1, f.custom_t2 AS fold_custom_t2, f.custom_t3 AS fold_custom_t3, f.custom_t4 AS fold_custom_t4, f.custom_t5 AS fold_custom_t5, f.custom_t6 AS fold_custom_t6, f.custom_t7 AS fold_custom_t7, f.custom_t8 AS fold_custom_t8, f.custom_t9 AS fold_custom_t9, f.custom_t10 AS fold_custom_t10, f.custom_t11 AS fold_custom_t11, f.custom_t12 AS fold_custom_t12, f.custom_t13 AS fold_custom_t13, f.custom_t14 AS fold_custom_t14, f.custom_t15 AS fold_custom_t15, f.custom_d1 AS fold_custom_d1, f.custom_d2 AS fold_custom_d2, f.custom_d3 AS fold_custom_d3, f.custom_d4 AS fold_custom_d4, f.custom_d5 AS fold_custom_d5, f.custom_d6 AS fold_custom_d6, f.custom_d7 AS fold_custom_d7, f.custom_d8 AS fold_custom_d8, f.custom_d9 AS fold_custom_d9, f.custom_d10 AS fold_custom_d10, f.custom_n1 AS fold_custom_n1, f.custom_n2 AS fold_custom_n2, f.custom_n3 AS fold_custom_n3, f.custom_n4 AS fold_custom_n4, f.custom_n5 AS fold_custom_n5, f.custom_f1 AS fold_custom_f1, f.custom_f2 AS fold_custom_f2, f.custom_f3 AS fold_custom_f3, f.custom_f4 AS fold_custom_f4, f.custom_f5 AS fold_custom_f5, f.is_complete AS fold_complete, f.status AS fold_status, f.subject AS fold_subject, f.parent_id AS fold_parent_id, f.folder_level, f.folder_name, f.creation_date AS fold_creation_date, r.initiator, r.destination, r.dest_user, mlb.category_id, mlb.exp_contact_id, mlb.exp_user_id, mlb.dest_user_id, mlb.dest_contact_id, mlb.nature_id, mlb.alt_identifier, mlb.admission_date, mlb.answer_type_bitmask, mlb.other_answer_desc, mlb.process_limit_date, mlb.closing_date, mlb.alarm1_date, mlb.alarm2_date, mlb.flag_notif, mlb.flag_alarm1, mlb.flag_alarm2, r.video_user, r.video_time, r.video_batch, r.subject, r.identifier, r.title, r.priority, mlb.process_notes, ca.case_id, ca.case_label, ca.case_description, en.entity_label, cont.firstname AS contact_firstname, cont.lastname AS contact_lastname, cont.society AS contact_society, u.lastname AS user_lastname, u.firstname AS user_firstname, list.item_id AS dest_user_from_listinstance
   FROM doctypes d, doctypes_first_level dfl, doctypes_second_level dsl, ar_batch a
   RIGHT JOIN res_lc_a r ON r.arbatch_id = a.arbatch_id
   LEFT JOIN entities en ON r.destination::text = en.entity_id::text
   LEFT JOIN folders f ON r.folders_system_id = f.folders_system_id
   LEFT JOIN cases_res cr ON r.res_id = cr.res_id
   LEFT JOIN mlb_coll_ext mlb ON mlb.res_id = r.res_id
   LEFT JOIN foldertypes ft ON f.foldertype_id = ft.foldertype_id AND f.status::text <> 'DEL'::text
   LEFT JOIN cases ca ON cr.case_id = ca.case_id
   LEFT JOIN contacts cont ON mlb.exp_contact_id = cont.contact_id OR mlb.dest_contact_id = cont.contact_id
   LEFT JOIN users u ON mlb.exp_user_id::text = u.user_id::text OR mlb.dest_user_id::text = u.user_id::text
   LEFT JOIN listinstance list ON r.res_id = list.res_id AND list.item_mode::text = 'dest'::text
  WHERE r.type_id = d.type_id AND d.doctypes_first_level_id = dfl.doctypes_first_level_id AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;

CREATE TABLE docserver_types
(
  docserver_types_id character varying(32) NOT NULL,
  dstype_label character varying(255) DEFAULT NULL::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  is_container boolean NOT NULL DEFAULT false,
  container_max_number integer NOT NULL DEFAULT (0)::integer,
  is_compressed boolean NOT NULL DEFAULT false,
  compression_mode character varying(32) DEFAULT NULL::character varying,
  is_meta boolean NOT NULL DEFAULT false,
  meta_template character varying(32) DEFAULT NULL::character varying,
  is_logged boolean NOT NULL DEFAULT false,
  log_template character varying(32) DEFAULT NULL::character varying,
  is_signed boolean NOT NULL DEFAULT false,
  signature_mode character varying(32) DEFAULT NULL::character varying,
  CONSTRAINT docserver_types_pkey PRIMARY KEY (docserver_types_id)
)
WITH (OIDS=FALSE);

CREATE TABLE docservers
(
  docservers_id character varying(32) NOT NULL DEFAULT '1'::character varying,
  docserver_types_id character varying(32) NOT NULL,
  device_label character varying(255) DEFAULT NULL::character varying,
  is_readonly boolean NOT NULL DEFAULT false,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  size_limit_number bigint NOT NULL DEFAULT (0)::bigint,
  actual_size_number bigint NOT NULL DEFAULT (0)::bigint,
  path_template character varying(255) NOT NULL,
  ext_docserver_info character varying(255) DEFAULT NULL::character varying,
  chain_before character varying(32) DEFAULT NULL::character varying,
  chain_after character varying(32) DEFAULT NULL::character varying,
  creation_date timestamp without time zone NOT NULL,
  closing_date timestamp without time zone,
  coll_id character varying(32) NOT NULL DEFAULT 'coll_1'::character varying,
  priority_number integer NOT NULL DEFAULT 10,
  docserver_locations_id character varying(32) NOT NULL,
  adr_priority_number integer NOT NULL DEFAULT 1,
  CONSTRAINT docservers_pkey PRIMARY KEY (docservers_id)
)
WITH (OIDS=FALSE);

CREATE TABLE docserver_locations
(
  docserver_locations_id character varying(32) NOT NULL,
  ipv4 character varying(255) DEFAULT NULL::character varying,
  ipv6 character varying(255) DEFAULT NULL::character varying,
  net_domain character varying(32) DEFAULT NULL::character varying,
  mask character varying(255) DEFAULT NULL::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT docserver_locations_pkey PRIMARY KEY (docserver_locations_id)
)
WITH (OIDS=FALSE);

CREATE TABLE lc_policies
(
   lc_policies_id character varying(32) NOT NULL, 
   policy_name character varying(255) NOT NULL,
   policy_desc character varying(255) NOT NULL,
   CONSTRAINT lc_policies_pkey PRIMARY KEY (lc_policies_id)
) 
WITH (OIDS = FALSE);


CREATE TABLE lc_cycles
(
   lc_policies_id character varying(32) NOT NULL,
   lc_cycles_id character varying(32) NOT NULL, 
   cycle_desc character varying(255) NOT NULL,
   sequence_number integer NOT NULL,
   where_clause text, 
   validation_mode character varying(32) NOT NULL, 
   CONSTRAINT lc_cycle_pkey PRIMARY KEY (lc_policies_id, lc_cycles_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE lc_cycle_steps
(
   lc_policies_id character varying(32) NOT NULL,
   lc_cycles_id character varying(32) NOT NULL, 
   lc_cycle_steps_id character varying(32) NOT NULL, 
   step_desc character varying(255) NOT NULL,
   docserver_types_id character varying(32) NOT NULL,
   is_allow_failure boolean NOT NULL DEFAULT false,
   coll_id character varying(32) NOT NULL DEFAULT 'coll_1'::character varying,
   step_operation character varying(32) NOT NULL,
   sequence_number integer NOT NULL,
   is_must_complete boolean NOT NULL DEFAULT false,
   preprocess_script character varying(255) DEFAULT NULL, 
   postprocess_script character varying(255) DEFAULT NULL,
   CONSTRAINT lc_cycle_steps_pkey PRIMARY KEY (lc_policies_id, lc_cycles_id, lc_cycle_steps_id, docserver_types_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE lc_stack
(
   lc_policies_id character varying(32) NOT NULL,
   lc_cycles_id character varying(32) NOT NULL, 
   lc_cycle_steps_id character varying(32) NOT NULL, 
   coll_id character varying(32) NOT NULL,
   res_id bigint NOT NULL, 
   cnt_retry integer DEFAULT NULL, 
   status character(1) NOT NULL,
   work_fields character varying(32),
   CONSTRAINT lc_stack_pkey PRIMARY KEY (lc_policies_id, lc_cycles_id, lc_cycle_steps_id, res_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE adr_x
(
  res_id bigint NOT NULL,
  docservers_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  logical_adr character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  filesize bigint,
  lc_policies_id character varying(32) NOT NULL,
  lc_cycles_id character varying(32) NOT NULL, 
  lc_cycle_steps_id character varying(32) NOT NULL, 
  adr_priority integer NOT NULL,
  CONSTRAINT adr_x_pkey PRIMARY KEY (res_id, docservers_id)
)
WITH (OIDS=FALSE);

