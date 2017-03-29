-- *************************************************************************--
--                                                                          --
--                                                                          --
--        THIS SCRIPT IS USE TO PASS FROM MAARCH 1.6 TO MAARCH 1.7          --
--                                                                          --
--                                                                          --
-- *************************************************************************--
CREATE FUNCTION order_alphanum(text) RETURNS text AS $$
  SELECT regexp_replace(regexp_replace(regexp_replace(regexp_replace($1,
    E'(^|\\D)(\\d{1,3}($|\\D))', E'\\1000\\2', 'g'),
      E'(^|\\D)(\\d{4,6}($|\\D))', E'\\1000\\2', 'g'),
        E'(^|\\D)(\\d{7}($|\\D))', E'\\100\\2', 'g'),
          E'(^|\\D)(\\d{8}($|\\D))', E'\\10\\2', 'g');
$$ LANGUAGE SQL;




/* MIGRATION NOUVEL STRUCT MOTS CLES*/
DROP SEQUENCE IF EXISTS tag_id_seq CASCADE;
CREATE SEQUENCE tag_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 7
  CACHE 1;

ALTER TABLE tags DROP COLUMN IF EXISTS tag_id;
ALTER TABLE tags ADD tag_id bigint NOT NULL DEFAULT nextval('tag_id_seq'::regclass);

ALTER TABLE tags DROP COLUMN IF EXISTS entity_id_owner;
ALTER TABLE tags ADD entity_id_owner character varying(32);

DROP SEQUENCE IF EXISTS tmp_tag_id_seq;
CREATE SEQUENCE tmp_tag_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 7
  CACHE 1;

DROP TABLE IF EXISTS tmp_tags;
CREATE TABLE tmp_tags
(
  tag_id bigint NOT NULL DEFAULT nextval('tmp_tag_id_seq'::regclass),
  tag_label character varying(255) NOT NULL
)
WITH (
  OIDS=FALSE
);

INSERT INTO tmp_tags (tag_label)
SELECT distinct(lower(tag_label)) from tags;

DROP TABLE IF EXISTS tag_res;
CREATE TABLE tag_res
(
  res_id bigint NOT NULL,
  tag_id bigint NOT NULL,
  CONSTRAINT tag_res_pkey PRIMARY KEY (res_id,tag_id)
)
WITH (
  OIDS=FALSE
);

DO $$ 
    BEGIN
        BEGIN
            ALTER TABLE tags ADD res_id bigint;
        EXCEPTION
            WHEN duplicate_column THEN RAISE NOTICE 'column res_id already exists in tags. skipping...';
        END;
    END;
$$;
INSERT INTO tag_res (res_id,tag_id)
SELECT tags.res_id, tmp_tags.tag_id FROM tags, tmp_tags WHERE tmp_tags.tag_label = lower(tags.tag_label) AND tags.res_id IS NOT NULL;

TRUNCATE TABLE tags;

ALTER TABLE tags DROP CONSTRAINT IF EXISTS tagsjoin_pkey;
ALTER TABLE tags DROP COLUMN IF EXISTS res_id;

INSERT INTO tags (tag_label, coll_id, tag_id)
SELECT tag_label, 'letterbox_coll', tag_id FROM tmp_tags;


DROP TABLE IF EXISTS tmp_tags;
DROP SEQUENCE IF EXISTS tmp_tag_id_seq;

DROP TABLE IF EXISTS tags_entities;
CREATE TABLE tags_entities
(
  tag_id bigint,
  entity_id character varying(32),
  CONSTRAINT tags_entities_pkey PRIMARY KEY (tag_id,entity_id)
)
WITH (
  OIDS=FALSE
);



DROP TABLE IF EXISTS seda;

CREATE TABLE seda
(
  "message_id" text NOT NULL,
  "schema" text,
  "type" text NOT NULL,
  "status" text NOT NULL,
  
  "date" timestamp NOT NULL,
  "reference" text NOT NULL,
  
  "account_id" text,
  "sender_org_identifier" text NOT NULL,
  "sender_org_name" text,
  "recipient_org_identifier" text NOT NULL,
  "recipient_org_name" text,

  "archival_agreement_reference" text,
  "reply_code" text,
  "operation_date" timestamp,
  "reception_date" timestamp,
  
  "related_reference" text,
  "request_reference" text,
  "reply_reference" text,
  "derogation" boolean,
  
  "data_object_count" integer,
  "size" numeric,
  
  "data" text,
  
  "active" boolean,
  "archived" boolean,

  PRIMARY KEY ("message_id")
)
WITH (
  OIDS=FALSE
);

DROP TABLE IF EXISTS unit_identifier;

CREATE TABLE unit_identifier
(
  "message_id" text NOT NULL,
  "tablename" text NOT NULL,
  "res_id" text NOT NULL
);


ALTER TABLE doctypes DROP COLUMN IF EXISTS retention_final_disposition;
ALTER TABLE doctypes ADD COLUMN retention_final_disposition character varying(255) NOT NULL DEFAULT 'destruction';

ALTER TABLE doctypes DROP COLUMN IF EXISTS retention_rule;
ALTER TABLE doctypes ADD COLUMN retention_rule character varying(15) NOT NULL DEFAULT 'P10Y';


ALTER TABLE entities DROP COLUMN IF EXISTS archival_agency;
ALTER TABLE entities ADD COLUMN archival_agency character varying(255);

ALTER TABLE entities DROP COLUMN IF EXISTS archival_agreement;
ALTER TABLE entities ADD COLUMN archival_agreement character varying(255);

DELETE FROM docservers where docserver_id = 'FASTHD_ATTACH';
INSERT INTO docservers (docserver_id, docserver_type_id, device_label, is_readonly, enabled, size_limit_number, actual_size_number, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority_number, docserver_location_id, adr_priority_number) 
VALUES ('FASTHD_ATTACH', 'FASTHD', 'Fast internal disc bay for attachments', 'N', 'Y', 50000000000, 1, '/opt/maarch/docservers/manual_attachments/', NULL, NULL, NULL, '2011-01-13 14:47:49.197164', NULL, 'attachments_coll', 2, 'NANTERRE', 3);

ALTER TABLE basket_persistent_mode ALTER COLUMN user_id TYPE character varying(128);
ALTER TABLE res_mark_as_read ALTER COLUMN user_id TYPE character varying(128);

-- EXPORT SEDA
DROP TABLE IF EXISTS seda;
CREATE TABLE seda
(
  "message_id" text NOT NULL,
  "schema" text,
  "type" text NOT NULL,
  "status" text NOT NULL,
  
  "date" timestamp NOT NULL,
  "reference" text NOT NULL,
  
  "account_id" text,
  "sender_org_identifier" text NOT NULL,
  "sender_org_name" text,
  "recipient_org_identifier" text NOT NULL,
  "recipient_org_name" text,

  "archival_agreement_reference" text,
  "reply_code" text,
  "operation_date" timestamp,
  "reception_date" timestamp,
  
  "related_reference" text,
  "request_reference" text,
  "reply_reference" text,
  "derogation" boolean,
  
  "data_object_count" integer,
  "size" numeric,
  
  "data" text,
  
  "active" boolean,
  "archived" boolean,

  PRIMARY KEY ("message_id")
)
WITH (
  OIDS=FALSE
);

DROP TABLE IF EXISTS unit_identifier;
CREATE TABLE unit_identifier
(
  "message_id" text NOT NULL,
  "tablename" text NOT NULL,
  "res_id" text NOT NULL
);

--EXPORT SEDA DATAS
DELETE FROM USERGROUPS WHERE GROUP_ID = 'ARCHIVISTE';
INSERT INTO USERGROUPS VALUES ('ARCHIVISTE', 'Archiviste', 'N', 'N', 'N', 'N', 'N', 'Y');

DELETE FROM USERGROUPS_SERVICES WHERE GROUP_ID = 'ARCHIVISTE';
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'add_thesaurus_to_res');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'adv_search_mlb');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'export_seda_view');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'fileplan');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'my_contacts_menu');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'put_doc_in_fileplan');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'sendmail');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'tag_view');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'view_baskets');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'view_doc_history');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'view_technical_infos');
INSERT INTO USERGROUPS_SERVICES (group_id, service_id) Values ('ARCHIVISTE', 'avis_documents');

DELETE FROM SECURITY WHERE GROUP_ID = 'ARCHIVISTE';
INSERT INTO SECURITY (group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete, rights_bitmask, mr_start_date, mr_stop_date, where_target) 
VALUES ('ARCHIVISTE', 'letterbox_coll', '1=1', 'Tous les courriers','N','N','N', 24, NULL, NULL, 'DOC');

DELETE FROM USERS WHERE USER_ID = 'aarc';
INSERT INTO USERS (user_id, password, firstname, lastname, mail, enabled, change_password, status, loginmode) 
VALUES ('aarc', '65d1d802c2c5e7e9035c5cef3cfc0902b6d0b591bfa85977055290736bbfcdd7e19cb7cfc9f980d0c815bbf7fe329a4efd8da880515ba520b22c0aa3a96514cc', 'Alfred', 'ARC', 'info@maarch.org', 'Y', 'N', 'OK', 'standard');

DELETE FROM USERS_ENTITIES WHERE USER_ID = 'aarc';
INSERT INTO USERS_ENTITIES (user_id, entity_id, user_role, primary_entity) 
VALUES ('aarc', 'VILLE', '', 'Y');

DELETE FROM USERGROUP_CONTENT WHERE USER_ID = 'aarc';
INSERT INTO USERGROUP_CONTENT (user_id, group_id, primary_group, role) 
VALUES ('aarc', 'ARCHIVISTE', 'Y','');

DELETE FROM STATUS WHERE ID = 'EXP_SEDA';
INSERT INTO STATUS (id, label_status, is_system, is_folder_status, img_filename, maarch_module, can_be_searched, can_be_modified) 
VALUES ('EXP_SEDA', 'A exporter au format SEDA', 'Y', 'N', 'fm-letter-status-acla', 'apps', 'Y', 'Y');

DELETE FROM ACTIONS WHERE id = 418;
INSERT INTO ACTIONS (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id, category_id) 
VALUES (418, '', 'Exporter SEDA', '_NOSTATUS_', 'N', 'N', 'Y', 'export_seda', 'Y', 'export_seda', 'N', NULL);

DELETE FROM ACTIONS WHERE id = 419;
INSERT INTO ACTIONS (id, keyword, label_action, id_status, is_system, is_folder_action, enabled, action_page, history, origin, create_id, category_id) 
VALUES (419, '', 'Proposer export SEDA', 'EXP_SEDA', 'N', 'N', 'Y', '', 'Y', 'apps', 'N', NULL);

DELETE FROM BASKETS WHERE BASKET_ID = 'AExporterSeda';
INSERT INTO BASKETS (basket_id, basket_name, basket_desc, basket_clause, coll_id, is_visible, is_folder_basket, enabled, basket_order) 
VALUES ('AExporterSeda', 'Courriers à exporter SEDA', 'Courriers à exporter SEDA', 'status=''EXP_SEDA''', 'letterbox_coll', 'Y', 'N', 'Y',300);

DELETE FROM GROUPBASKET WHERE BASKET_ID = 'AExporterSeda';
INSERT INTO GROUPBASKET (group_id, basket_id, sequence, redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert, list_lock_clause, sublist_lock_clause) 
VALUES ('ARCHIVISTE', 'AExporterSeda', 1, NULL, NULL, 'list_with_attachments','N', 'N', 'N', NULL, NULL);

DELETE FROM ACTIONS_GROUPBASKETS WHERE id_action = 418;
INSERT INTO ACTIONS_GROUPBASKETS (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) 
VALUES (418, '', 'ARCHIVISTE', 'AExporterSeda', 'Y', 'N', 'N');

DELETE FROM ACTIONS_GROUPBASKETS WHERE id_action = 419;
INSERT INTO ACTIONS_GROUPBASKETS (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) 
VALUES (419, '', 'RESP_COURRIER', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO ACTIONS_GROUPBASKETS (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) 
VALUES (419, '', 'RESPONSABLE', 'MyBasket', 'N', 'Y', 'N');

UPDATE ENTITIES SET BUSINESS_ID = 'org_987654321_Versant';
UPDATE ENTITIES SET ARCHIVAL_AGENCY = 'org_123456789_Archives';
UPDATE ENTITIES SET ARCHIVAL_AGREEMENT = 'MAARCH_LES_BAINS_ACTES';

UPDATE parameters SET param_value_int = '170' WHERE id = 'database_version';
