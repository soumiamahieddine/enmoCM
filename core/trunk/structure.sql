

-- core/sql/structure/core.postgresql.sql


SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

DROP PROCEDURAL LANGUAGE IF EXISTS plpgsql  CASCADE;
CREATE PROCEDURAL LANGUAGE plpgsql;

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

CREATE SEQUENCE actions_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 101
  CACHE 1;

CREATE TABLE actions
(
  id integer NOT NULL DEFAULT nextval('actions_id_seq'::regclass),
  keyword character varying(32) NOT NULL DEFAULT ''::bpchar,
  label_action character varying(255),
  id_status character varying(10),
  is_system character(1) NOT NULL DEFAULT 'N'::bpchar,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  action_page character varying(255),
  history character(1) NOT NULL DEFAULT 'N'::bpchar,
  origin character varying(255) NOT NULL DEFAULT 'apps'::bpchar,
  create_id  character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT actions_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


CREATE TABLE docserver_types
(
  docserver_type_id character varying(32) NOT NULL,
  docserver_type_label character varying(255) DEFAULT NULL::character varying,
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
  fingerprint_mode character varying(32) DEFAULT NULL::character varying,
  CONSTRAINT docserver_types_pkey PRIMARY KEY (docserver_type_id)
)
WITH (OIDS=FALSE);

CREATE TABLE docservers
(
  docserver_id character varying(32) NOT NULL DEFAULT '1'::character varying,
  docserver_type_id character varying(32) NOT NULL,
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
  docserver_location_id character varying(32) NOT NULL,
  adr_priority_number integer NOT NULL DEFAULT 1,
  CONSTRAINT docservers_pkey PRIMARY KEY (docserver_id)
)
WITH (OIDS=FALSE);

CREATE TABLE docserver_locations
(
  docserver_location_id character varying(32) NOT NULL,
  ipv4 character varying(255) DEFAULT NULL::character varying,
  ipv6 character varying(255) DEFAULT NULL::character varying,
  net_domain character varying(32) DEFAULT NULL::character varying,
  mask character varying(255) DEFAULT NULL::character varying,
  net_link character varying(255) DEFAULT NULL::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT docserver_locations_pkey PRIMARY KEY (docserver_location_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE doctypes_type_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 80
  CACHE 1;

CREATE TABLE doctypes
(
  coll_id character varying(32) NOT NULL DEFAULT ''::character varying,
  type_id integer NOT NULL DEFAULT nextval('doctypes_type_id_seq'::regclass),
  description character varying(255) NOT NULL DEFAULT ''::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  doctypes_first_level_id integer,
  doctypes_second_level_id integer,
  primary_retention  character varying(50) DEFAULT NULL,
  secondary_retention  character varying(50) DEFAULT NULL,
  CONSTRAINT doctypes_pkey PRIMARY KEY (type_id)
)
WITH (OIDS=FALSE);

CREATE TABLE ext_docserver
(
  doc_id character varying(255) NOT NULL,
  path character varying(255) NOT NULL,
  CONSTRAINT ext_docserver_pkey PRIMARY KEY (doc_id)
)
WITH (OIDS=FALSE);

CREATE TABLE groupsecurity
(
  group_id character varying(32) NOT NULL,
  resgroup_id character varying(32) NOT NULL,
  can_view character(1) NOT NULL,
  can_add character(1) NOT NULL,
  can_delete character(1) NOT NULL,
  CONSTRAINT groupsecurity_pkey PRIMARY KEY (group_id, resgroup_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE history_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE history
(
  id bigint NOT NULL DEFAULT nextval('history_id_seq'::regclass),
  table_name character varying(32) DEFAULT NULL::character varying,
  record_id character varying(255) DEFAULT NULL::character varying,
  event_type character varying(32) NOT NULL,
  user_id character varying(50) NOT NULL,
  event_date timestamp without time zone NOT NULL,
  info text,
  id_module character varying(50) NOT NULL DEFAULT 'admin'::character varying,
  remote_ip character varying(32) DEFAULT NULL,
  CONSTRAINT history_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE history_batch_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE history_batch
(
  id bigint NOT NULL DEFAULT nextval('history_batch_id_seq'::regclass),
  module_name character varying(32) DEFAULT NULL::character varying,
  batch_id bigint DEFAULT NULL::bigint,
  event_date timestamp without time zone NOT NULL,
  total_processed bigint DEFAULT NULL::bigint,
  total_errors bigint DEFAULT NULL::bigint,
  info text,
  CONSTRAINT history_batch_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE parameters
(
  id character varying(50) NOT NULL,
  param_value_string character varying(50) DEFAULT NULL::character varying,
  param_value_int integer,
  param_value_date timestamp without time zone,
  CONSTRAINT parameters_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE resgroup_content
(
  coll_id character varying(32) NOT NULL,
  res_id bigint NOT NULL,
  resgroup_id character varying(32) NOT NULL,
  "sequence" integer NOT NULL,
  CONSTRAINT resgroup_content_pkey PRIMARY KEY (coll_id, res_id, resgroup_id)
)
WITH (OIDS=FALSE);

CREATE TABLE resgroups
(
  resgroup_id character varying(32) NOT NULL,
  resgroup_desc character varying(255) NOT NULL,
  created_by character varying(255) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  CONSTRAINT resgroups_pkey PRIMARY KEY (resgroup_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE security_security_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;

CREATE TABLE "security"
(
  security_id bigint NOT NULL DEFAULT nextval('security_security_id_seq'::regclass),
  group_id character varying(32) NOT NULL,
  coll_id character varying(32) NOT NULL,
  where_clause text,
  maarch_comment text,
  can_insert character(1) NOT NULL DEFAULT 'N'::bpchar,
  can_update character(1) NOT NULL DEFAULT 'N'::bpchar,
  can_delete character(1) NOT NULL DEFAULT 'N'::bpchar,
  rights_bitmask integer NOT NULL DEFAULT 0,
  mr_start_date timestamp without time zone DEFAULT NULL,
  mr_stop_date timestamp without time zone DEFAULT NULL,
  where_target character varying(15) DEFAULT 'DOC'::character varying,
  CONSTRAINT security_pkey PRIMARY KEY (security_id)
)
WITH (OIDS=FALSE);

CREATE TABLE status
(
  id character varying(10) NOT NULL,
  label_status character varying(50) NOT NULL,
  is_system character(1) NOT NULL DEFAULT 'Y'::bpchar,
  img_filename character varying(255),
  maarch_module character varying(255) NOT NULL DEFAULT 'apps'::character varying,
  can_be_searched character(1) NOT NULL DEFAULT 'Y'::bpchar,
  can_be_modified character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT status_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE usergroup_content
(
  user_id character varying(32) NOT NULL,
  group_id character varying(32) NOT NULL,
  primary_group character(1) NOT NULL,
  "role" character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT usergroup_content_pkey PRIMARY KEY (user_id, group_id)
)
WITH (OIDS=FALSE);

CREATE TABLE usergroups
(
  group_id character varying(32) NOT NULL,
  group_desc character varying(255) DEFAULT NULL::character varying,
  administrator character(1) NOT NULL DEFAULT 'N'::bpchar,
  custom_right1 character(1) NOT NULL DEFAULT 'N'::bpchar,
  custom_right2 character(1) NOT NULL DEFAULT 'N'::bpchar,
  custom_right3 character(1) NOT NULL DEFAULT 'N'::bpchar,
  custom_right4 character(1) NOT NULL DEFAULT 'N'::bpchar,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT usergroups_pkey PRIMARY KEY (group_id)
)
WITH (OIDS=FALSE);

CREATE TABLE usergroups_services
(
  group_id character varying NOT NULL,
  service_id character varying NOT NULL,
  CONSTRAINT usergroups_services_pkey PRIMARY KEY (group_id, service_id)
)
WITH (OIDS=FALSE);

CREATE TABLE users
(
  user_id character varying(32) NOT NULL,
  "password" character varying(255) DEFAULT NULL::character varying,
  firstname character varying(255) DEFAULT NULL::character varying,
  lastname character varying(255) DEFAULT NULL::character varying,
  phone character varying(15) DEFAULT NULL::character varying,
  mail character varying(255) DEFAULT NULL::character varying,
  department character varying(50) DEFAULT NULL::character varying,
  custom_t1 character varying(50) DEFAULT '0'::character varying,
  custom_t2 character varying(50) DEFAULT NULL::character varying,
  custom_t3 character varying(50) DEFAULT NULL::character varying,
  cookie_key character varying(255) DEFAULT NULL::character varying,
  cookie_date timestamp without time zone,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  change_password character(1) NOT NULL DEFAULT 'Y'::bpchar,
  delay_number integer DEFAULT NULL,
  status character varying(10) NOT NULL DEFAULT 'OK'::character varying,
  loginmode character varying(50) DEFAULT NULL::character varying,
  docserver_location_id character varying(32) DEFAULT NULL::character varying,
  CONSTRAINT users_pkey PRIMARY KEY (user_id)
)
WITH (OIDS=FALSE);


-- modules/advanced_physical_archive/sql/structure/advanced_physical_archive.postgresql.sql

CREATE SEQUENCE arbox_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 10
  CACHE 1;

CREATE TABLE  ar_boxes (
  arbox_id bigint NOT NULL DEFAULT nextval('arbox_id_seq'::regclass),
  title character varying(255)  default NULL::character varying,
  subject character varying(255)  default NULL::character varying,
  description text ,
  entity_id character varying(32)  default NULL::character varying,
  arcontainer_id integer NOT NULL,
  status character varying(3)  default NULL::character varying,
  creation_date timestamp without time zone,
  retention_time timestamp without time zone,
  custom_t1 character varying(3)  default NULL::character varying,
  custom_n1 integer default NULL,
  custom_f1 numeric default NULL,
  custom_d1 timestamp without time zone,
  custom_t2 character varying(3)  default  NULL::character varying,
  custom_n2 integer default NULL,
  custom_f2 numeric default NULL,
  custom_d2 timestamp without time zone,
  custom_t3 character varying(50)  default  NULL::character varying,
  custom_n3 integer default NULL,
  custom_f3 numeric default NULL,
  custom_d3 timestamp without time zone,
  custom_t4 character varying(50)  default  NULL::character varying,
  custom_n4 integer default NULL,
  custom_f4 numeric default NULL,
  custom_d4 timestamp without time zone,
  custom_t5 character varying(255)  default  NULL::character varying,
  custom_n5 integer default NULL,
  custom_f5 numeric default NULL,
  custom_d5 timestamp without time zone,
  custom_t6 character varying(255)  default  NULL::character varying,
  custom_t7 character varying(255)  default  NULL::character varying,
  custom_t8 character varying(255)  default  NULL::character varying,
  custom_t9 character varying(255)  default  NULL::character varying,
  custom_t10 character varying(255)  default  NULL::character varying,
  custom_t11 character varying(255)  default  NULL::character varying,
   CONSTRAINT ar_boxes_pkey PRIMARY KEY (arbox_id)
)
WITH (OIDS=FALSE);

CREATE TABLE  ar_containers (
  arcontainer_id integer NOT NULL,
  arcontainer_desc character varying(255)  default NULL,
  status character varying(3)  default NULL,
  ctype_id character varying(32)  default NULL,
  position_id bigint default NULL,
  creation_date timestamp without time zone,
  entity_id character varying(32)  NOT NULL,
  retention_time timestamp without time zone,
  custom_t1 character varying(50)  default NULL,
  custom_n1 integer default NULL,
  custom_f1 numeric default NULL,
  custom_d1 timestamp without time zone,
  custom_t2 character varying(3)  default NULL,
  custom_n2 integer default NULL,
  custom_f2 numeric default NULL,
  custom_d2 timestamp without time zone,
 CONSTRAINT ar_containers_pkey PRIMARY KEY (arcontainer_id)
)
WITH (OIDS=FALSE);

CREATE TABLE  ar_container_types (
  ctype_id character varying(32)   NOT NULL,
  ctype_desc character varying(255)   NOT NULL,
  size_x float NOT NULL default '0',
  size_y float NOT NULL default '0',
  size_z float NOT NULL default '0',
  CONSTRAINT ar_container_types_pkey PRIMARY KEY (ctype_id)
)
WITH (OIDS=FALSE);

CREATE TABLE  ar_deposits (
  deposit_id bigint NOT NULL,
  deposit_label character varying(255)  NOT NULL,
  deposit_desc text  NOT NULL,
  flg_closed smallint NOT NULL,
  closing_date timestamp without time zone NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  user_id character varying(32)  NOT NULL,
  CONSTRAINT ar_deposits_pkey PRIMARY KEY (deposit_id)
)
WITH (OIDS=FALSE);

CREATE TABLE  ar_header (
  header_id bigserial NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  ctype_id character varying(32)   NOT NULL default '0',
  year_1 integer NOT NULL default '0',
  year_2 integer NOT NULL default '0',
  site_id character varying(32)   NOT NULL default '0',
  destruction_date timestamp without time zone,
  allow_transmission_date timestamp without time zone,
  weight integer default NULL,
  reservation_id bigint default NULL,
  deposit_id bigint default NULL,
  header_desc text  ,
  entity_id character varying(32)   default NULL,
  arnature_id character varying(32)   default NULL,
  arbox_id integer default NULL,
  arcontainer_id integer default NULL,
  CONSTRAINT ar_header_pkey PRIMARY KEY (header_id)
)
WITH (OIDS=FALSE);

CREATE TABLE  ar_natures (
  arnature_id character varying(32)  NOT NULL,
  arnature_desc character varying(255)  default NULL,
  arnature_retention integer NOT NULL,
  entity_id character varying(32)  default NULL,
  enabled character varying(1)  default NULL,
 CONSTRAINT ar_natures_pkey PRIMARY KEY (arnature_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE position_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 200
  CACHE 1;

CREATE TABLE  ar_positions (
  position_id bigint NOT NULL DEFAULT nextval('position_id_seq'::regclass),
  site_id character varying(32)  NOT NULL,
  pos_row character varying(32)  NOT NULL,
  pos_col integer NOT NULL,
  pos_level integer NOT NULL,
  pos_max_uc integer NOT NULL,
  pos_available_uc integer NOT NULL,
 CONSTRAINT ar_positions_pkey PRIMARY KEY (position_id)
)
WITH (OIDS=FALSE);

CREATE TABLE  ar_sites (
  site_id character varying(32)  NOT NULL default '0',
  site_desc character varying(255)  NOT NULL,
  entity_id character varying(32)  default NULL,
 CONSTRAINT ar_sites_pkey PRIMARY KEY (site_id)
)
WITH (OIDS=FALSE);

 CREATE  TABLE  res_apa (
 res_id serial  NOT  NULL ,
 title character varying( 255  )    default NULL ,
 subject text ,
 description text ,
 publisher character varying( 255  )    default NULL ,
 contributor character varying( 255  )    default NULL ,
 type_id integer  default  NULL ,
 format character varying( 50  )   default NULL ,
 typist character varying( 50  )   default NULL ,
 creation_date timestamp without time zone NOT  NULL ,
 author character varying( 255  )    default NULL ,
 author_name text ,
 identifier character varying( 255  )    default NULL ,
 source character varying( 255  )    default NULL ,
 doc_language character varying( 50  )    default NULL ,
 relation integer  default NULL ,
 coverage character varying( 255  )    default NULL ,
 doc_date timestamp without time zone  default NULL ,
 docserver_id character varying( 32  )   default NULL ,
 folders_system_id integer  default NULL ,
 arbox_id character varying( 32  )    default NULL ,
 path character varying( 255  )    default NULL ,
 filename character varying( 255  )    default NULL ,
 offset_doc character varying( 255  )    default NULL ,
 logical_adr character varying( 255  )    default NULL ,
 fingerprint character varying( 255  )    default NULL ,
 filesize integer  default NULL ,
 is_paper char( 1  )    default NULL ,
 page_count integer  default NULL ,
 scan_date timestamp without time zone  default NULL ,
 scan_user character varying( 50  )    default NULL ,
 scan_location character varying( 255  )    default NULL ,
 scan_wkstation character varying( 255  )    default NULL ,
 scan_batch character varying( 50  )    default NULL ,
 burn_batch character varying( 50  )    default NULL ,
 scan_postmark character varying( 50  )    default NULL ,
 envelop_id integer  default NULL ,
 status character varying( 3  )    default NULL ,
 destination character varying( 50  )    default NULL ,
 approver character varying( 50  )    default NULL ,
 validation_date timestamp without time zone  default NULL ,
 work_batch integer  default NULL ,
 origin character varying( 50  )    default NULL ,
 is_ingoing char( 1  )    default NULL ,
 priority smallint default NULL ,
 arbatch_id character varying( 32  )    default NULL ,
 fulltext_result character varying(10)  DEFAULT NULL,
 ocr_result character varying(10)  DEFAULT NULL,
 converter_result  character varying(10)  DEFAULT NULL,
 custom_t1  text default NULL,
 custom_n1 integer  default NULL ,
 custom_f1 numeric  default NULL ,
 custom_d1 timestamp without time zone  default NULL ,
 custom_t2 character varying( 255  )    default NULL ,
 custom_n2 integer  default NULL ,
 custom_f2 numeric  default NULL ,
 custom_d2 timestamp without time zone  default NULL ,
 custom_t3 character varying( 255  )    default NULL ,
 custom_n3 integer  default NULL ,
 custom_f3 numeric  default NULL ,
 custom_d3 timestamp without time zone  default NULL ,
 custom_t4 character varying( 255  )    default NULL ,
 custom_n4 integer  default NULL ,
 custom_f4 numeric  default NULL ,
 custom_d4 timestamp without time zone  default NULL ,
 custom_t5 character varying( 255  )    default NULL ,
 custom_n5 integer  default NULL ,
 custom_f5 numeric  default NULL ,
 custom_d5 timestamp without time zone  default NULL ,
 custom_t6 character varying( 255  )    default NULL ,
 custom_d6 timestamp without time zone  default NULL ,
 custom_t7 character varying( 255  )    default NULL ,
 custom_d7 timestamp without time zone  default NULL ,
 custom_t8 character varying( 255  )    default NULL ,
 custom_d8 timestamp without time zone  default NULL ,
 custom_t9 character varying( 255  )    default NULL ,
 custom_d9 timestamp without time zone  default NULL ,
 custom_t10 character varying( 255  )    default NULL ,
 custom_d10 timestamp without time zone  default NULL ,
 custom_t11 character varying( 255  )    default NULL ,
 custom_t12 character varying( 255  )    default NULL ,
 custom_t13 character varying( 255  )    default NULL ,
 custom_t14 character varying( 255  )    default NULL ,
 custom_t15 character varying( 255  )    default NULL ,
 tablename character varying( 32  )   default  'res_apa',
 initiator character varying( 50  )    default NULL ,
 dest_user character varying( 50  )    default NULL ,
 video_batch integer  default NULL ,
 video_time timestamp NULL  default NULL ,
 video_user character varying( 50  )    default NULL ,
 video_date timestamp without time zone,
 CONSTRAINT res_apa_pkey PRIMARY KEY (res_id)
)
WITH (OIDS=FALSE);


-- modules/alert_diffusion/sql/structure/alert_diffusion.postgresql.sql


CREATE SEQUENCE alerts_alert_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE SEQUENCE alerts_insts_alert_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE SEQUENCE alerts_users_alert_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE alerts
(
  alert_id integer NOT NULL DEFAULT nextval('alerts_alert_id_seq'::regclass),
  alert_label character varying(255) NOT NULL,
  alert_type character varying(255) NOT NULL,
  coll_id character varying(255),
  alert_table character varying(255),
  identifier character varying(255),
  model_id character varying(255),
  use_listinstance character(1) DEFAULT 'n'::bpchar,
  use_alerts_users character(1) DEFAULT 'n'::bpchar,
  alert_unit character varying(15),
  alert_frequency integer,
  alert_parameter character varying(255),
  alert_begin_date timestamp without time zone,
  alert_end_date timestamp without time zone,
  alert_creation_date timestamp with time zone NOT NULL,
  alert_status character varying(5) NOT NULL,
  alert_text text,
  alert_sql_clause character varying(1024),
  preprocess_script character varying(1024),
  alert_creator character varying(255) NOT NULL,
  postprocess_script character varying(1024),
  CONSTRAINT alerts_pkey PRIMARY KEY (alert_id)
)
WITH (OIDS=FALSE);

CREATE TABLE alert_users
(
  alert_id integer NOT NULL DEFAULT nextval('alerts_users_alert_id_seq'::regclass),
  user_id character varying(255) NOT NULL,
  CONSTRAINT alerts_users_pkey PRIMARY KEY (alert_id, user_id)
)
WITH (OIDS=FALSE);

CREATE TABLE alert_insts
(
  alert_id bigint NOT NULL DEFAULT nextval('alerts_insts_alert_id_seq'::regclass),
  "sequence" integer NOT NULL,
  due_date timestamp without time zone NOT NULL,
  status character varying(5) NOT NULL,
  CONSTRAINT alerts_insts_pkey PRIMARY KEY (alert_id, sequence)
)
WITH (OIDS=FALSE);


-- modules/attachments/sql/structure/attachments.postgresql.sql


CREATE SEQUENCE res_attachment_res_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE res_attachments
(
  res_id bigint NOT NULL DEFAULT nextval('res_attachment_res_id_seq'::regclass),
  title character varying(255) DEFAULT NULL::character varying,
  subject text,
  description text,
  publisher character varying(255) DEFAULT NULL::character varying,
  contributor character varying(255) DEFAULT NULL::character varying,
  type_id bigint ,
  format character varying(50) NOT NULL,
  typist character varying(50) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  fulltext_result character varying(10) DEFAULT NULL::character varying,
  ocr_result character varying(10) DEFAULT NULL::character varying,
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
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(50) DEFAULT NULL::character varying,
  coll_id character varying(32) NOT NULL,
  res_id_master bigint,
  CONSTRAINT res_attachments_pkey PRIMARY KEY (res_id)
)
WITH (OIDS=FALSE);


-- modules/autofoldering/sql/structure/autofoldering.postgresql.sql


CREATE TABLE af_security
(
  af_security_id bigint NOT NULL,
  af_security_label character varying(255) NOT NULL,
  group_id character varying(50) NOT NULL,
  tree_id character varying(50) NOT NULL,
  where_clause text NOT NULL,
  start_date timestamp without time zone,
  stop_date timestamp without time zone,
  CONSTRAINT af_security_pkey PRIMARY KEY (af_security_id)
)
WITH (OIDS=FALSE);

-- Filled during autofoldering load
-- If you create your on table for a new tree
-- It is very important to respect the order of fields : DO NOT PUT IDS IN THE END OF THE TABLE!!!
CREATE TABLE af_view_year_target
(
  level1 character varying(255) NOT NULL , -- Pays / Country : custom_t3
  level1_id integer NOT NULL,
  level2 character(4) ,          -- Année / Year : date_part( 'year', doc_date)
  level2_id integer NOT NULL,
  level3 character varying(255) ,          -- Client / Customer : custom_t4
  level3_id integer NOT NULL,
  CONSTRAINT af_view_year_target_pkey PRIMARY KEY (level1, level2, level3)
)
WITH (OIDS=FALSE);

CREATE TABLE af_view_customer_target
(
  level1 character varying(255) NOT NULL , -- 1ère lettre client / Customer 1st letter : substring(custom_t4, 1, 1)
  level1_id integer NOT NULL,
  level2 character varying(255) ,          -- Client / Customer : custom_t4
  level2_id integer NOT NULL,
  level3 character(4) ,                    -- Année / Year : date_part( 'year', doc_date)
  level3_id integer NOT NULL,
  CONSTRAINT af_view_customer_target_pkey PRIMARY KEY (level1, level2, level3)
)
WITH (OIDS=FALSE);


-- modules/basket/sql/structure/basket.postgresql.sql

CREATE TABLE actions_groupbaskets
(
  id_action bigint NOT NULL,
  where_clause text,
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  used_in_basketlist character(1) NOT NULL DEFAULT 'Y'::bpchar,
  used_in_action_page character(1) NOT NULL DEFAULT 'Y'::bpchar,
  default_action_list character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT actions_groupbaskets_pkey PRIMARY KEY (id_action, group_id, basket_id)
)
WITH (OIDS=FALSE);

CREATE TABLE baskets
(
  coll_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  basket_name character varying(255) NOT NULL,
  basket_desc character varying(255) NOT NULL,
  basket_clause text NOT NULL,
  is_generic character varying(6) NOT NULL DEFAULT 'N'::character varying,
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT baskets_pkey PRIMARY KEY (coll_id, basket_id)
)
WITH (OIDS=FALSE);

CREATE TABLE groupbasket
(
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  "sequence" integer NOT NULL DEFAULT 0,
  redirect_basketlist character varying(2048) DEFAULT NULL::character varying,
  redirect_grouplist character varying(2048) DEFAULT NULL::character varying,
  result_page character varying(255) DEFAULT 'show_list1.php'::character varying,
  can_redirect character(1) NOT NULL DEFAULT 'N'::bpchar,
  can_delete character(1) NOT NULL DEFAULT 'N'::bpchar,
  can_insert character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT groupbasket_pkey PRIMARY KEY (group_id, basket_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE user_abs_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE user_abs
(
  system_id bigint NOT NULL DEFAULT nextval('user_abs_seq'::regclass),
  user_abs character varying(32) NOT NULL,
  new_user character varying(32) NOT NULL,
  basket_id character varying(255) NOT NULL,
  basket_owner character varying(255),
  is_virtual character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT user_abs_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);


-- modules/cases/sql/structure/cases.postgresql.sql

CREATE SEQUENCE case_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE cases
(
  case_id integer NOT NULL DEFAULT nextval('case_id_seq'::regclass),
  case_label character varying(255) NOT NULL DEFAULT ''::bpchar,
  case_description character varying(255),
  case_type character varying(32),
  case_closing_date timestamp without time zone,
  case_last_update_date timestamp without time zone NOT NULL,
  case_creation_date timestamp without time zone NOT NULL,
  case_typist character varying(32) NOT NULL DEFAULT ''::bpchar,
  case_parent integer,
  case_custom_t1 character varying(255),
  case_custom_t2 character varying(255),
  case_custom_t3 character varying(255),
  case_custom_t4 character varying(255),
  CONSTRAINT cases_pkey PRIMARY KEY (case_id)
);

CREATE TABLE cases_res
(
  case_id integer NOT NULL,
  res_id integer NOT NULL,
  CONSTRAINT cases_res_pkey PRIMARY KEY (case_id,res_id)
);



-- modules/entities/sql/structure/entities.postgresql.sql


CREATE TABLE entities
(
  entity_id character varying(32) NOT NULL,
  entity_label character varying(255),
  short_label character varying(50),
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  adrs_1 character varying(255),
  adrs_2 character varying(255),
  adrs_3 character varying(255),
  zipcode character varying(32),
  city character varying(255),
  country character varying(255),
  email character varying(255),
  business_id character varying(32),
  parent_entity_id character varying(32),
  entity_type character varying(64),
  CONSTRAINT entities_pkey PRIMARY KEY (entity_id)
)
WITH (OIDS=FALSE);

CREATE TABLE listinstance
(
  coll_id character varying(50) NOT NULL,
  res_id bigint NOT NULL,
  listinstance_type character varying(50) DEFAULT 'DOC'::character varying,
  "sequence" bigint NOT NULL,
  item_id character varying(50) NOT NULL,
  item_type character varying(255) NOT NULL,
  item_mode character varying(50) NOT NULL,
  added_by_user character varying(50) NOT NULL,
  added_by_entity character varying(50) NOT NULL,
  viewed bigint
)
WITH (OIDS=FALSE);

CREATE TABLE listmodels
(
  coll_id character varying(50) NOT NULL,
  object_id character varying(50) NOT NULL,
  object_type character varying(255) NOT NULL,
  "sequence" bigint NOT NULL,
  item_id character varying(50) NOT NULL,
  item_type character varying(255) NOT NULL,
  item_mode character varying(50) NOT NULL,
  listmodel_type character varying(50) DEFAULT 'DOC'::character varying
)
WITH (OIDS=FALSE);

CREATE TABLE users_entities
(
  user_id character varying(32) NOT NULL,
  entity_id character varying(32) NOT NULL,
  user_role character varying(255),
  primary_entity character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT users_entities_pkey PRIMARY KEY (user_id, entity_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE groupbasket_redirect_system_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE groupbasket_redirect
(
  system_id integer NOT NULL DEFAULT nextval('groupbasket_redirect_system_id_seq'::regclass),
  group_id character varying(32) NOT NULL,
  basket_id character varying(32) NOT NULL,
  action_id int NOT NULL,
  entity_id character varying(32),
  keyword character varying(255),
  redirect_mode character varying(32) NOT NULL,
  CONSTRAINT groupbasket_redirect_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);


-- modules/folder/sql/structure/folder.postgresql.sql

CREATE SEQUENCE folders_system_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;

CREATE TABLE folders
(
  folders_system_id bigint NOT NULL DEFAULT nextval('folders_system_id_seq'::regclass),
  folder_id character varying(255) NOT NULL,
  foldertype_id integer,
  parent_id bigint DEFAULT (0)::bigint,
  folder_name character varying(255) DEFAULT NULL::character varying,
  subject character varying(255) DEFAULT NULL::character varying,
  description character varying(255) DEFAULT NULL::character varying,
  author character varying(255) DEFAULT NULL::character varying,
  typist character varying(255) DEFAULT NULL::character varying,
  status character varying(50) NOT NULL DEFAULT 'NEW'::character varying,
  folder_level smallint DEFAULT (1)::smallint,
  creation_date timestamp without time zone NOT NULL,
  folder_out_id bigint,
  video_status character varying(10) DEFAULT NULL,
  video_user character varying(32) DEFAULT NULL,
  is_frozen character(1) NOT NULL DEFAULT 'N',
  custom_t1 character varying(255) DEFAULT NULL::character varying,
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
  custom_d11 timestamp without time zone,
  custom_t12 character varying(255) DEFAULT NULL::character varying,
  custom_d12 timestamp without time zone,
  custom_t13 character varying(255) DEFAULT NULL::character varying,
  custom_d13 timestamp without time zone,
  custom_t14 character varying(255) DEFAULT NULL::character varying,
  custom_d14 timestamp without time zone,
  custom_t15 character varying(255) DEFAULT NULL::character varying,
  is_complete character(1) DEFAULT 'N'::bpchar,
  is_folder_out character(1) DEFAULT 'N'::bpchar,
  last_modified_date timestamp without time zone,
  CONSTRAINT folders_pkey PRIMARY KEY (folders_system_id)
)
WITH (OIDS=FALSE);

CREATE TABLE folders_out (
  folder_out_id serial NOT NULL,
  folder_system_id integer NOT NULL,
  last_name character varying(255) NOT NULL,
  first_name character varying(255)  NOT NULL,
  last_name_folder_out character varying(255)  NOT NULL,
  first_name_folder_out character varying(255)  NOT NULL,
  put_out_pattern character varying(255)  NOT NULL,
  put_out_date timestamp without time zone NOT NULL,
  return_date timestamp without time zone NOT NULL,
  return_flag character(1) NOT NULL default 'N'::bpchar,
  CONSTRAINT folders_out_pkey PRIMARY KEY  (folder_out_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE foldertype_id_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 5
  CACHE 1;

CREATE TABLE foldertypes
(
  foldertype_id  bigint NOT NULL DEFAULT nextval('foldertype_id_id_seq'::regclass),
  foldertype_label character varying(255) NOT NULL,
  maarch_comment text,
  retention_time character varying(50),
  custom_d1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t1 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t2 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t3 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t4 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_f5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_n5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t5 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d6 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t6 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d7 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t7 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d8 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t8 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d9 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t9 character varying(10) DEFAULT '0000000000'::character varying,
  custom_d10 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t10 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t11 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t12 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t13 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t14 character varying(10) DEFAULT '0000000000'::character varying,
  custom_t15 character varying(10) DEFAULT '0000000000'::character varying,
  coll_id character varying(32),
  CONSTRAINT foldertypes_pkey PRIMARY KEY (foldertype_id)
)
WITH (OIDS=FALSE);

CREATE TABLE foldertypes_doctypes
(
  foldertype_id integer NOT NULL,
  doctype_id integer NOT NULL,
  CONSTRAINT foldertypes_doctypes_pkey PRIMARY KEY (foldertype_id, doctype_id)
)
WITH (OIDS=FALSE);

CREATE TABLE foldertypes_doctypes_level1
(
  foldertype_id integer NOT NULL,
  doctypes_first_level_id integer NOT NULL,
  CONSTRAINT foldertypes_doctypes_level1_pkey PRIMARY KEY (foldertype_id, doctypes_first_level_id)
)
WITH (OIDS=FALSE);

CREATE TABLE foldertypes_indexes
(
  foldertype_id bigint NOT NULL,
  field_name character varying(255) NOT NULL,
  mandatory character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT foldertypes_indexes_pkey PRIMARY KEY (foldertype_id, field_name)
)
WITH (OIDS=FALSE);


-- modules/full_text/sql/structure/full_text.postgresql.sql

CREATE TABLE fulltext
(
  coll_id character varying(32) NOT NULL,
  res_id bigint NOT NULL,
  text_type character varying(10) NOT NULL DEFAULT 'CON'::character varying,
  fulltext_content text,
  CONSTRAINT coll_id_res_id PRIMARY KEY (coll_id, res_id)
)
WITH (
  OIDS=FALSE
);


-- modules/life_cycle/sql/structure/life_cycle.postgresql.sql

CREATE TABLE lc_policies
(
   policy_id character varying(32) NOT NULL, 
   policy_name character varying(255) NOT NULL,
   policy_desc character varying(255) NOT NULL,
   CONSTRAINT lc_policies_pkey PRIMARY KEY (policy_id)
) 
WITH (OIDS = FALSE);


CREATE TABLE lc_cycles
(
   policy_id character varying(32) NOT NULL,
   cycle_id character varying(32) NOT NULL, 
   cycle_desc character varying(255) NOT NULL,
   sequence_number integer NOT NULL,
   where_clause text, 
   break_key character varying(255) DEFAULT NULL,
   validation_mode character varying(32) NOT NULL, 
   CONSTRAINT lc_cycle_pkey PRIMARY KEY (policy_id, cycle_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE lc_cycle_steps
(
   policy_id character varying(32) NOT NULL,
   cycle_id character varying(32) NOT NULL, 
   cycle_step_id character varying(32) NOT NULL, 
   cycle_step_desc character varying(255) NOT NULL,
   docserver_type_id character varying(32) NOT NULL,
   is_allow_failure boolean NOT NULL DEFAULT false,
   step_operation character varying(32) NOT NULL,
   sequence_number integer NOT NULL,
   is_must_complete boolean NOT NULL DEFAULT false,
   preprocess_script character varying(255) DEFAULT NULL, 
   postprocess_script character varying(255) DEFAULT NULL,
   CONSTRAINT lc_cycle_steps_pkey PRIMARY KEY (policy_id, cycle_id, cycle_step_id, docserver_type_id)
) 
WITH (OIDS = FALSE);

CREATE TABLE lc_stack
(
   policy_id character varying(32) NOT NULL,
   cycle_id character varying(32) NOT NULL, 
   cycle_step_id character varying(32) NOT NULL, 
   coll_id character varying(32) NOT NULL,
   res_id bigint NOT NULL, 
   cnt_retry integer DEFAULT NULL, 
   status character(1) NOT NULL,
   CONSTRAINT lc_stack_pkey PRIMARY KEY (policy_id, cycle_id, cycle_step_id, res_id)
) 
WITH (OIDS = FALSE);



-- modules/notes/sql/structure/notes.postgresql.sql

CREATE SEQUENCE notes_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;


CREATE TABLE notes
(
  id bigint NOT NULL DEFAULT nextval('notes_seq'::regclass),
  identifier bigint NOT NULL,
  tablename character varying(50),
  user_id character varying(50) NOT NULL,
  date_note date NOT NULL,
  note_text text NOT NULL,
  coll_id character varying(50),
  CONSTRAINT notes_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


-- modules/physical_archive/sql/structure/physical_archive.postgresql.sql

create or replace function update_the_db() returns void as
$$
begin

    if not exists(select * from information_schema.tables where table_name = 'ar_boxes') then

      CREATE TABLE ar_boxes (
	  arbox_id serial NOT NULL,
	  title character varying(255)  DEFAULT NULL,
	  subject character varying(255)  DEFAULT NULL,
	  description text ,
	  entity_id character varying(32)  DEFAULT NULL,
	  arcontainer_id integer NOT NULL,
	  status character varying(3)  DEFAULT NULL,
	  creation_date  timestamp without time zone DEFAULT NULL,
	  retention_time character varying(50)  DEFAULT NULL,
	  custom_t1 character varying(3)  DEFAULT NULL,
	  custom_n1 integer,
	  custom_f1 numeric,
	  custom_d1 timestamp without time zone DEFAULT NULL,
	  custom_t2 character varying(3)  DEFAULT NULL,
	  custom_n2 integer,
	  custom_f2 numeric,
	  custom_d2 timestamp without time zone DEFAULT NULL,
	  custom_t3 character varying(50)  DEFAULT NULL,
	  custom_n3 integer,
	  custom_f3 numeric,
	  custom_d3 timestamp without time zone DEFAULT NULL,
	  custom_t4 character varying(50)  DEFAULT NULL,
	  custom_n4 integer,
	  custom_f4 numeric,
	  custom_d4 timestamp without time zone DEFAULT NULL,
	  custom_t5 character varying(255)  DEFAULT NULL,
	  custom_n5 integer,
	  custom_f5 numeric,
	  custom_d5 timestamp without time zone DEFAULT NULL,
	  custom_t6 character varying(255)  DEFAULT NULL,
	  custom_t7 character varying(255)  DEFAULT NULL,
	  custom_t8 character varying(255)  DEFAULT NULL,
	  custom_t9 character varying(255)  DEFAULT NULL,
	  custom_t10 character varying(255)  DEFAULT NULL,
	  custom_t11 character varying(255)  DEFAULT NULL,
	  CONSTRAINT ar_boxes_pkey PRIMARY KEY  (arbox_id)
	) ;

    end if;

end;
$$
language 'plpgsql';

select update_the_db();
drop function update_the_db();


create or replace function update_the_db() returns void as
$$
begin

    if not exists(select * from information_schema.tables where table_name = 'ar_containers') then

        CREATE TABLE ar_containers
	(
	  arcontainer_id serial NOT NULL ,
	  arcontainer_desc character varying(255)  DEFAULT NULL,
	  status character varying(3)  DEFAULT NULL,
	  ctype_id character varying(32)  DEFAULT NULL,
	  position_id bigint  DEFAULT NULL,
	  creation_date timestamp without time zone DEFAULT NULL,
	  entity_id character varying(32)  DEFAULT NULL,
	  retention_time character varying(50)  DEFAULT NULL,
	  custom_t1 character varying(50)  DEFAULT NULL,
	  custom_n1 integer,
	  custom_f1 numeric,
	  custom_d1 timestamp without time zone DEFAULT NULL,
	  custom_t2 character varying(3)  DEFAULT NULL,
	  custom_n2 integer,
	  custom_f2 numeric,
	  custom_d2 timestamp without time zone DEFAULT NULL,
	  CONSTRAINT ar_containers_pkey PRIMARY KEY  (arcontainer_id)
	) ;

    end if;

end;
$$
language 'plpgsql';

select update_the_db();
drop function update_the_db();

CREATE TABLE ar_batch
(
  arbatch_id serial NOT NULL ,
  title character varying(255)  DEFAULT NULL,
  subject character varying(255)  DEFAULT NULL,
  description text,
  arbox_id bigint,
  status character varying(3)  DEFAULT NULL,
  creation_date timestamp without time zone DEFAULT NULL,
  retention_time character varying(50)  DEFAULT NULL,
  custom_t1 character varying(3)  DEFAULT NULL,
  custom_n1 integer,
  custom_f1 numeric,
  custom_d1 timestamp without time zone DEFAULT NULL,
  custom_t2 character varying(3)  DEFAULT NULL,
  custom_n2 integer,
  custom_f2 numeric,
  custom_d2 timestamp without time zone DEFAULT NULL,
  custom_t3 character varying(50)  DEFAULT NULL,
  custom_n3 integer,
  custom_f3 numeric,
  custom_d3 timestamp without time zone DEFAULT NULL,
  custom_t4 character varying(50)  DEFAULT NULL,
  custom_n4 integer,
  custom_f4 numeric,
  custom_d4 timestamp without time zone DEFAULT NULL,
  custom_t5 character varying(255)  DEFAULT NULL,
  custom_n5 integer,
  custom_f5 numeric,
  custom_d5 timestamp without time zone DEFAULT NULL,
  custom_t6 character varying(255)  DEFAULT NULL,
  custom_t7 character varying(255)  DEFAULT NULL,
  custom_t8 character varying(255)  DEFAULT NULL,
  custom_t9 character varying(255)  DEFAULT NULL,
  custom_t10 character varying(255)  DEFAULT NULL,
  custom_t11 character varying(255)  DEFAULT NULL,
  CONSTRAINT ar_batch_pkey PRIMARY KEY  (arbatch_id)
) ;


-- modules/postindexing/sql/structure/postindexing.postgresql.sql



-- modules/reports/sql/structure/reports.postgresql.sql

CREATE TABLE usergroups_reports
(
  group_id character varying(32) NOT NULL,
  report_id character varying(50) NOT NULL,
  CONSTRAINT usergroups_reports_pkey PRIMARY KEY (group_id, report_id)
)
WITH (OIDS=FALSE);


-- modules/templates/sql/structure/templates.postgresql.sql


CREATE SEQUENCE templates_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;

CREATE SEQUENCE templates_association_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 20
  CACHE 1;

CREATE TABLE templates
(
  id bigint NOT NULL DEFAULT nextval('templates_seq'::regclass),
  label character varying(50) DEFAULT NULL::character varying,
  creation_date timestamp without time zone,
  "template_comment" character varying(255) DEFAULT NULL::character varying,
  "content" text,
  CONSTRAINT templates_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

CREATE TABLE templates_association
(
  template_id bigint NOT NULL,
  what character varying(255) NOT NULL,
  value_field character varying(255) NOT NULL,
  system_id bigint NOT NULL DEFAULT nextval('templates_association_seq'::regclass),
  maarch_module character varying(255) NOT NULL DEFAULT 'apps'::character varying,
  CONSTRAINT templates_association_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);

CREATE TABLE templates_doctype_ext
(
  template_id bigint DEFAULT NULL,
  type_id integer NOT NULL,
  is_generated character(1) NOT NULL DEFAULT 'N'::bpchar
)
WITH (OIDS=FALSE);


-- apps/maarch_entreprise/sql/structure/apps.postgresql.sql

CREATE SEQUENCE contact_id_seq
  INCREMENT 1
  MINVALUE 14
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE contacts (
contact_id bigint NOT NULL DEFAULT nextval('contact_id_seq'::regclass),
lastname character varying( 255 )  ,
firstname character varying( 255 )  ,
society character varying( 255 )  ,
function character varying( 255 ),
address_num character varying( 32 )  ,
address_street character varying( 255 )  ,
address_complement character varying( 255 )  ,
address_town character varying( 255 )  ,
address_postal_code character varying( 255 ) ,
address_country character varying( 255 )  ,
email character varying( 255 )  ,
phone character varying( 20 )  ,
other_data text  ,
is_corporate_person character( 1 ) NOT NULL DEFAULT 'Y'::bpchar,
user_id character varying( 32 )  ,
title character varying( 255 ) ,
enabled character( 1 ) NOT NULL DEFAULT 'Y'::bpchar,
CONSTRAINT contacts_pkey PRIMARY KEY  (contact_id)
) WITH (OIDS=FALSE);

CREATE SEQUENCE query_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 10
  CACHE 1;


CREATE TABLE saved_queries (
  query_id bigint NOT NULL DEFAULT nextval('query_id_seq'::regclass),
  user_id character varying(32)  default NULL,
  query_name character varying(255) NOT NULL,
  creation_date timestamp without time zone NOT NULL,
  created_by character varying(32)  NOT NULL,
  query_type character varying(50) NOT NULL,
  query_txt text  NOT NULL,
  last_modification_date timestamp without time zone,
  CONSTRAINT saved_queries_pkey PRIMARY KEY  (query_id)
) WITH (OIDS=FALSE);

CREATE SEQUENCE doctypes_first_level_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 10
  CACHE 1;

CREATE TABLE doctypes_first_level
(
  doctypes_first_level_id integer NOT NULL DEFAULT nextval('doctypes_first_level_id_seq'::regclass),
  doctypes_first_level_label character varying(255) NOT NULL,
  css_style character varying(255),
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT doctypes_first_level_pkey PRIMARY KEY (doctypes_first_level_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE doctypes_second_level_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 50
  CACHE 1;

CREATE TABLE doctypes_second_level
(
  doctypes_second_level_id integer NOT NULL DEFAULT nextval('doctypes_second_level_id_seq'::regclass),
  doctypes_second_level_label character varying(255) NOT NULL,
  doctypes_first_level_id integer NOT NULL,
  css_style character varying(255),
  enabled character(1) NOT NULL DEFAULT 'Y'::bpchar,
  CONSTRAINT doctypes_second_level_pkey PRIMARY KEY (doctypes_second_level_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE res_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE res_x
(
  res_id bigint NOT NULL DEFAULT nextval('res_id_seq'::regclass),
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
  status character varying(10) NOT NULL,
  destination character varying(50) DEFAULT NULL::character varying,
  approver character varying(50) DEFAULT NULL::character varying,
  validation_date timestamp without time zone,
  work_batch bigint,
  origin character varying(50) DEFAULT NULL::character varying,
  is_ingoing character(1) DEFAULT NULL::bpchar,
  priority smallint,
  arbatch_id bigint DEFAULT NULL,
  policy_id character varying(32) DEFAULT NULL::character varying,
  cycle_id character varying(32) DEFAULT NULL::character varying,
  cycle_date timestamp without time zone,
  is_multi_docservers character(1) NOT NULL DEFAULT 'N'::bpchar,
  is_frozen character(1) NOT NULL DEFAULT 'N'::bpchar,
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
  tablename character varying(32) DEFAULT 'res_x'::character varying,
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(50) DEFAULT NULL::character varying,
  video_batch integer DEFAULT NULL,
  video_time integer DEFAULT NULL,
  video_user character varying(50)  DEFAULT NULL,
  video_date timestamp without time zone,
  CONSTRAINT res_x_pkey PRIMARY KEY  (res_id)
)
WITH (OIDS=FALSE);

CREATE TABLE adr_x
(
  res_id bigint NOT NULL,
  docserver_id character varying(32) NOT NULL,
  path character varying(255) DEFAULT NULL::character varying,
  filename character varying(255) DEFAULT NULL::character varying,
  offset_doc character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  adr_priority integer NOT NULL,
  CONSTRAINT adr_x_pkey PRIMARY KEY (res_id, docserver_id)
)
WITH (OIDS=FALSE);

CREATE SEQUENCE res_id_mlb_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 100
  CACHE 1;

CREATE TABLE res_letterbox
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
  status character varying(10) NOT NULL,
  destination character varying(50) DEFAULT NULL::character varying,
  approver character varying(50) DEFAULT NULL::character varying,
  validation_date timestamp without time zone,
  work_batch bigint,
  origin character varying(50) DEFAULT NULL::character varying,
  is_ingoing character(1) DEFAULT NULL::bpchar,
  priority smallint,
  arbatch_id bigint DEFAULT NULL,
  policy_id character varying(32) DEFAULT NULL::character varying,
  cycle_id character varying(32) DEFAULT NULL::character varying,
  cycle_date timestamp without time zone,
  is_multi_docservers character(1) NOT NULL DEFAULT 'N'::bpchar,
  is_frozen character(1) NOT NULL DEFAULT 'N'::bpchar,
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
  tablename character varying(32) DEFAULT 'res_letterbox'::character varying,
  initiator character varying(50) DEFAULT NULL::character varying,
  dest_user character varying(50) DEFAULT NULL::character varying,
  video_batch integer DEFAULT NULL,
  video_time integer DEFAULT NULL,
  video_user character varying(50)  DEFAULT NULL,
  video_date timestamp without time zone,
  CONSTRAINT res_letterbox_pkey PRIMARY KEY  (res_id)
)
WITH (OIDS=FALSE);

CREATE TABLE mlb_coll_ext (
  res_id bigint NOT NULL,
  category_id character varying(50)  NOT NULL,
  exp_contact_id integer default NULL,
  exp_user_id character varying(52) default NULL,
  dest_contact_id integer default NULL,
  dest_user_id character varying(52) default NULL,
  nature_id character varying(50),
  alt_identifier character varying(255)  default NULL,
  admission_date timestamp without time zone,
  answer_type_bitmask character varying(7)  default NULL,
  other_answer_desc character varying(255)  DEFAULT NULL::character varying,
  process_limit_date timestamp without time zone default NULL,
  process_notes text,
  closing_date timestamp without time zone default NULL,
  alarm1_date timestamp without time zone default NULL,
  alarm2_date timestamp without time zone default NULL,
  flag_notif char(1)  default 'N'::character varying ,
  flag_alarm1 char(1)  default 'N'::character varying ,
  flag_alarm2 char(1) default 'N'::character varying
)WITH (OIDS=FALSE);

CREATE TABLE mlb_doctype_ext (
  type_id bigint NOT NULL,
  process_delay bigint NOT NULL DEFAULT '21',
  delay1 bigint NOT NULL DEFAULT '14',
  delay2 bigint NOT NULL DEFAULT '1',
  CONSTRAINT type_id PRIMARY KEY (type_id)
)
WITH (OIDS=FALSE);

CREATE OR REPLACE VIEW res_view AS
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
 r.custom_f4 AS doc_custom_f4, r.custom_f5 AS doc_custom_f5, r.is_frozen as res_is_frozen
   FROM  doctypes d, doctypes_first_level dfl, doctypes_second_level dsl, res_x r
   WHERE r.type_id = d.type_id
   AND d.doctypes_first_level_id = dfl.doctypes_first_level_id
   AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;

-- View without cases :
--CREATE OR REPLACE VIEW res_view_letterbox AS
 --SELECT r.tablename, r.res_id, r.type_id, d.description AS type_label, d.doctypes_first_level_id, dfl.doctypes_first_level_label, dfl.css_style as doctype_first_level_style,
 -- d.doctypes_second_level_id, dsl.doctypes_second_level_label, dsl.css_style as doctype_second_level_style,
 -- r.format, r.typist, r.creation_date, r.relation, r.docserver_id, r.folders_system_id, f.folder_id, r.path, r.filename, r.fingerprint, r.filesize, r.status, r.work_batch, r.arbatch_id, r.arbox_id, r.page_count, r.is_paper, r.doc_date, r.scan_date, r.scan_user, r.scan_location, r.scan_wkstation, r.scan_batch, r.doc_language, r.description, r.source, r.author, r.custom_t1 AS doc_custom_t1, r.custom_t2 AS doc_custom_t2, r.custom_t3 AS doc_custom_t3, r.custom_t4 AS doc_custom_t4, r.custom_t5 AS doc_custom_t5, r.custom_t6 AS doc_custom_t6, r.custom_t7 AS doc_custom_t7, r.custom_t8 AS doc_custom_t8, r.custom_t9 AS doc_custom_t9, r.custom_t10 AS doc_custom_t10, r.custom_t11 AS doc_custom_t11, r.custom_t12 AS doc_custom_t12, r.custom_t13 AS doc_custom_t13, r.custom_t14 AS doc_custom_t14, r.custom_t15 AS doc_custom_t15, r.custom_d1 AS doc_custom_d1, r.custom_d2 AS doc_custom_d2, r.custom_d3 AS doc_custom_d3, r.custom_d4 AS doc_custom_d4, r.custom_d5 AS doc_custom_d5, r.custom_d6 AS doc_custom_d6, r.custom_d7 AS doc_custom_d7, r.custom_d8 AS doc_custom_d8, r.custom_d9 AS doc_custom_d9, r.custom_d10 AS doc_custom_d10, r.custom_n1 AS doc_custom_n1, r.custom_n2 AS doc_custom_n2, r.custom_n3 AS doc_custom_n3, r.custom_n4 AS doc_custom_n4, r.custom_n5 AS doc_custom_n5, r.custom_f1 AS doc_custom_f1, r.custom_f2 AS doc_custom_f2, r.custom_f3 AS doc_custom_f3, r.custom_f4 AS doc_custom_f4, r.custom_f5 AS doc_custom_f5, f.foldertype_id, ft.foldertype_label, f.custom_t1 AS fold_custom_t1, f.custom_t2 AS fold_custom_t2, f.custom_t3 AS fold_custom_t3, f.custom_t4 AS fold_custom_t4, f.custom_t5 AS fold_custom_t5, f.custom_t6 AS fold_custom_t6, f.custom_t7 AS fold_custom_t7, f.custom_t8 AS fold_custom_t8, f.custom_t9 AS fold_custom_t9, f.custom_t10 AS fold_custom_t10, f.custom_t11 AS fold_custom_t11, f.custom_t12 AS fold_custom_t12, f.custom_t13 AS fold_custom_t13, f.custom_t14 AS fold_custom_t14, f.custom_t15 AS fold_custom_t15, f.custom_d1 AS fold_custom_d1, f.custom_d2 AS fold_custom_d2, f.custom_d3 AS fold_custom_d3, f.custom_d4 AS fold_custom_d4, f.custom_d5 AS fold_custom_d5, f.custom_d6 AS fold_custom_d6, f.custom_d7 AS fold_custom_d7, f.custom_d8 AS fold_custom_d8, f.custom_d9 AS fold_custom_d9, f.custom_d10 AS fold_custom_d10, f.custom_n1 AS fold_custom_n1, f.custom_n2 AS fold_custom_n2, f.custom_n3 AS fold_custom_n3, f.custom_n4 AS fold_custom_n4, f.custom_n5 AS fold_custom_n5, f.custom_f1 AS fold_custom_f1, f.custom_f2 AS fold_custom_f2, f.custom_f3 AS fold_custom_f3, f.custom_f4 AS fold_custom_f4, f.custom_f5 AS fold_custom_f5, f.is_complete AS fold_complete, f.status AS fold_status, f.subject AS fold_subject, f.parent_id AS fold_parent_id, f.folder_level, f.folder_name, f.creation_date AS fold_creation_date, r.initiator, r.destination, r.dest_user, mlb.category_id, mlb.exp_contact_id, mlb.exp_user_id, mlb.dest_user_id, mlb.dest_contact_id, mlb.nature_id, mlb.alt_identifier, mlb.admission_date, mlb.answer_type_bitmask, mlb.other_answer_desc, mlb.process_limit_date, mlb.closing_date, mlb.alarm1_date, mlb.alarm2_date, mlb.flag_notif, mlb.flag_alarm1, mlb.flag_alarm2, r.video_user, r.video_time, r.video_batch, r.subject, r.identifier, r.title, r.priority, mlb.process_notes
  -- FROM doctypes d, doctypes_first_level dfl, doctypes_second_level dsl, res_letterbox r
   --LEFT JOIN ar_batch a ON r.arbatch_id = a.arbatch_id
   --LEFT JOIN folders f ON r.folders_system_id = f.folders_system_id
   --LEFT JOIN mlb_coll_ext mlb ON mlb.res_id = r.res_id
   --LEFT JOIN foldertypes ft ON f.foldertype_id = ft.foldertype_id AND f.status::text <> 'DEL'::text
 -- WHERE r.type_id = d.type_id AND d.doctypes_first_level_id = dfl.doctypes_first_level_id AND d.doctypes_second_level_id = dsl.doctypes_second_level_id;

CREATE VIEW res_view_letterbox AS
    SELECT r.tablename, r.is_multi_docservers, r.res_id, r.type_id, 
    d.description AS type_label, d.doctypes_first_level_id, 
    dfl.doctypes_first_level_label, dfl.css_style as doctype_first_level_style,
    d.doctypes_second_level_id, dsl.doctypes_second_level_label, 
    dsl.css_style as doctype_second_level_style, r.format, r.typist, 
    r.creation_date, r.relation, r.docserver_id, r.folders_system_id, 
    f.folder_id, f.is_frozen as folder_is_frozen, r.path, r.filename, r.fingerprint, r.offset_doc, r.filesize, 
    r.status, r.work_batch, r.arbatch_id, r.arbox_id, r.page_count, r.is_paper, 
    r.doc_date, r.scan_date, r.scan_user, r.scan_location, r.scan_wkstation,
    r.scan_batch, r.doc_language, r.description, r.source, r.author, 
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
    mlb.dest_user_id, mlb.dest_contact_id, mlb.nature_id, mlb.alt_identifier, 
    mlb.admission_date, mlb.answer_type_bitmask, mlb.other_answer_desc,
    mlb.process_limit_date, mlb.closing_date, mlb.alarm1_date, mlb.alarm2_date, 
    mlb.flag_notif, mlb.flag_alarm1, mlb.flag_alarm2, r.video_user, r.video_time,
    r.video_batch, r.subject, r.identifier, r.title, r.priority, mlb.process_notes,
    ca.case_id, ca.case_label, ca.case_description, en.entity_label, 
    cont.firstname AS contact_firstname, cont.lastname AS contact_lastname, 
    cont.society AS contact_society, u.lastname AS user_lastname,
    u.firstname AS user_firstname, list.item_id AS dest_user_from_listinstance,
    r.is_frozen as res_is_frozen 
    FROM doctypes d, doctypes_first_level dfl, doctypes_second_level dsl,
    ((((((((((ar_batch a RIGHT JOIN res_letterbox r ON ((r.arbatch_id = a.arbatch_id))) 
    LEFT JOIN entities en ON (((r.destination)::text = (en.entity_id)::text))) 
    LEFT JOIN folders f ON ((r.folders_system_id = f.folders_system_id))) 
    LEFT JOIN cases_res cr ON ((r.res_id = cr.res_id)))
    LEFT JOIN mlb_coll_ext mlb ON ((mlb.res_id = r.res_id))) 
    LEFT JOIN foldertypes ft ON (((f.foldertype_id = ft.foldertype_id)
        AND ((f.status)::text <> 'DEL'::text))))
    LEFT JOIN cases ca ON ((cr.case_id = ca.case_id))) 
    LEFT JOIN contacts cont ON (((mlb.exp_contact_id = cont.contact_id) 
        OR (mlb.dest_contact_id = cont.contact_id)))) 
    LEFT JOIN users u ON ((((mlb.exp_user_id)::text = (u.user_id)::text) 
        OR ((mlb.dest_user_id)::text = (u.user_id)::text)))) 
    LEFT JOIN listinstance list ON (((r.res_id = list.res_id)
        AND ((list.item_mode)::text = 'dest'::text))))
    WHERE (((r.type_id = d.type_id) AND 
    (d.doctypes_first_level_id = dfl.doctypes_first_level_id))
    AND (d.doctypes_second_level_id = dsl.doctypes_second_level_id));
CREATE OR REPLACE VIEW res_view_apa AS
 select * from res_apa;



CREATE TABLE doctypes_indexes
(
  type_id bigint NOT NULL,
  coll_id character varying(32) NOT NULL,
  field_name character varying(255) NOT NULL,
  mandatory character(1) NOT NULL DEFAULT 'N'::bpchar,
  CONSTRAINT doctypes_indexes_pkey PRIMARY KEY (type_id, coll_id, field_name)
)
WITH (OIDS=FALSE);


-- Resource view used to fill af_target, we exclude from res_x the branches already in af_target table

CREATE OR REPLACE VIEW af_view_year_view AS
 SELECT r.custom_t3 AS level1, date_part( 'year', r.doc_date) AS level2, r.custom_t4 AS level3,
        r.res_id, r.creation_date, r.status -- for where clause
   FROM  res_x r
   WHERE  NOT (EXISTS ( SELECT t.level1, t.level2, t.level3
           FROM af_view_year_target t
          WHERE r.custom_t3::text = t.level1::text AND cast(date_part( 'year', r.doc_date) as character) = t.level2 AND r.custom_t4 = t.level3));

CREATE OR REPLACE VIEW af_view_customer_view AS
 SELECT substring(r.custom_t4, 1, 1) AS level1,  r.custom_t4 AS level2, date_part( 'year', r.doc_date) AS level3,
        r.res_id, r.creation_date, r.status -- for where clause
   FROM  res_x r
   WHERE status <> 'DEL' and date_part( 'year', doc_date) is not null
   AND NOT (EXISTS ( SELECT t.level1, t.level2, t.level3
           FROM af_view_customer_target t
          WHERE substring(r.custom_t4, 1, 1)::text = t.level1::text AND r.custom_t4::text = t.level2::text
          AND cast(date_part( 'year', r.doc_date) as character) = t.level3)) ;

-- View used to display trees
CREATE OR REPLACE VIEW af_view_year_target_view AS
 SELECT af.level1, af.level1_id, af.level1 as level1_label, af.level2, af.level2_id, af.level2 as level2_label, af.level3, af.level3_id, af.level3 as level3_label
   FROM af_view_year_target af;

CREATE OR REPLACE VIEW af_view_customer_target_view AS
 SELECT af.level1, af.level1_id, af.level1 as level1_label, af.level2, af.level2_id, af.level2 as level2_label, af.level3, af.level3_id, af.level3 as level3_label
   FROM af_view_customer_target af ;

-- Views for postindexing
 CREATE OR REPLACE VIEW view_folders AS 
 SELECT folders.folders_system_id, folders.folder_id, folders.foldertype_id, foldertypes.foldertype_label, (folders.folder_id::text || ' - '::text) || folders.folder_name::text AS folder_full_label, folders.parent_id, folders.folder_name, folders.subject, folders.description, folders.author, folders.typist, folders.status, folders.folder_level, folders.creation_date, folders.folder_out_id, folders.custom_t1, folders.custom_n1, folders.custom_f1, folders.custom_d1, folders.custom_t2, folders.custom_n2, folders.custom_f2, folders.custom_d2, folders.custom_t3, folders.custom_n3, folders.custom_f3, folders.custom_d3, folders.custom_t4, folders.custom_n4, folders.custom_f4, folders.custom_d4, folders.custom_t5, folders.custom_n5, folders.custom_f5, folders.custom_d5, folders.custom_t6, folders.custom_d6, folders.custom_t7, folders.custom_d7, folders.custom_t8, folders.custom_d8, folders.custom_t9, folders.custom_d9, folders.custom_t10, folders.custom_d10, folders.custom_t11, folders.custom_d11, folders.custom_t12, folders.custom_d12, folders.custom_t13, folders.custom_d13, folders.custom_t14, folders.custom_d14, folders.custom_t15, folders.is_complete, folders.is_folder_out, folders.last_modified_date, count(res_view_letterbox.folders_system_id) AS count_document, folders.video_status
   FROM foldertypes, folders
   LEFT JOIN res_view_letterbox ON res_view_letterbox.folders_system_id = folders.folders_system_id
  WHERE folders.foldertype_id = foldertypes.foldertype_id
  GROUP BY folders.folders_system_id, folders.folder_id, folders.foldertype_id, foldertypes.foldertype_label, folder_full_label, folders.parent_id, folders.folder_name, folders.subject, folders.description, folders.author, folders.typist, folders.status, folders.folder_level, folders.creation_date, folders.folder_out_id, folders.custom_t1, folders.custom_n1, folders.custom_f1, folders.custom_d1, folders.custom_t2, folders.custom_n2, folders.custom_f2, folders.custom_d2, folders.custom_t3, folders.custom_n3, folders.custom_f3, folders.custom_d3, folders.custom_t4, folders.custom_n4, folders.custom_f4, folders.custom_d4, folders.custom_t5, folders.custom_n5, folders.custom_f5, folders.custom_d5, folders.custom_t6, folders.custom_d6, folders.custom_t7, folders.custom_d7, folders.custom_t8, folders.custom_d8, folders.custom_t9, folders.custom_d9, folders.custom_t10, folders.custom_d10, folders.custom_t11, folders.custom_d11, folders.custom_t12, folders.custom_d12, folders.custom_t13, folders.custom_d13, folders.custom_t14, folders.custom_d14, folders.custom_t15, folders.is_complete, folders.is_folder_out, folders.last_modified_date, folders.video_status;

  
  CREATE OR REPLACE VIEW view_postindexing AS 
 SELECT res_view_letterbox.video_user, (users.firstname::text || ' '::text) || users.lastname::text AS user_name, res_view_letterbox.video_batch, res_view_letterbox.video_time, count(res_view_letterbox.res_id) AS count_documents, res_view_letterbox.folders_system_id, (folders.folder_id::text || ' / '::text) || folders.folder_name::text AS folder_full_label, folders.video_status
   FROM res_view_letterbox
   LEFT JOIN users ON res_view_letterbox.video_user::text = users.user_id::text
   LEFT JOIN folders ON folders.folders_system_id = res_view_letterbox.folders_system_id
  WHERE res_view_letterbox.video_batch IS NOT NULL
  GROUP BY res_view_letterbox.video_user, (users.firstname::text || ' '::text) || users.lastname::text, res_view_letterbox.video_batch, res_view_letterbox.video_time, res_view_letterbox.folders_system_id, (folders.folder_id::text || ' / '::text) || folders.folder_name::text, folders.video_status;

   
