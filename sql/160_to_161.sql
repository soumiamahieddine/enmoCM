-- *************************************************************************--
--                                                                          --
--                                                                          --
--        THIS SCRIPT IS USE TO PASS FROM MAARCH 1.5 TO MAARCH 1.5.1        --
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


ALTER TABLE doctypes DROP COLUMN IF EXISTS retention_rule;
ALTER TABLE doctypes ADD COLUMN retention_rule character varying(255) NOT NULL DEFAULT 'destruction';

ALTER TABLE doctypes DROP COLUMN IF EXISTS duration;
ALTER TABLE doctypes ADD COLUMN duration character varying(15) NOT NULL DEFAULT 'P10Y';


ALTER TABLE entities DROP COLUMN IF EXISTS transferring_agency;
ALTER TABLE entities ADD COLUMN transferring_agency character varying(255);

ALTER TABLE entities DROP COLUMN IF EXISTS archival_agreement;
ALTER TABLE entities ADD COLUMN archival_agreement character varying(255);
