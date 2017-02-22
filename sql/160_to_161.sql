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




/* MIGRATION NOUVEL STRUCT MOTS CLEES*/
CREATE SEQUENCE tag_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 7
  CACHE 1;

ALTER TABLE tags ADD tag_id bigint NOT NULL DEFAULT nextval('tag_id_seq'::regclass);
ALTER TABLE tags ADD entity_id_owner character varying(32);

CREATE SEQUENCE tmp_tag_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 7
  CACHE 1;

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

CREATE TABLE tag_res
(
  res_id bigint NOT NULL,
  tag_id bigint NOT NULL,
  CONSTRAINT tag_res_pkey PRIMARY KEY (res_id,tag_id)
)
WITH (
  OIDS=FALSE
);

INSERT INTO tag_res (res_id,tag_id)
SELECT tags.res_id, tmp_tags.tag_id FROM tags, tmp_tags WHERE tmp_tags.tag_label = lower(tags.tag_label);

TRUNCATE TABLE tags;
ALTER TABLE tags DROP CONSTRAINT IF EXISTS tagsjoin_pkey;
ALTER TABLE tags DROP COLUMN IF EXISTS res_id;

INSERT INTO tags (tag_label, coll_id, tag_id)
SELECT tag_label, 'letterbox_coll', tag_id FROM tmp_tags;


DROP TABLE tmp_tags;
DROP SEQUENCE IF EXISTS tmp_tag_id_seq;

CREATE TABLE tags_entities
(
  tag_id bigint,
  entity_id character varying(32),
  CONSTRAINT tags_entities_pkey PRIMARY KEY (tag_id,entity_id)
)
WITH (
  OIDS=FALSE
);


