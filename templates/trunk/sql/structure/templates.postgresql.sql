CREATE TABLE templates
(
  id serial NOT NULL,
  label character varying(50) DEFAULT NULL::character varying,
  creation_date timestamp without time zone,
  "template_comment" character varying(255) DEFAULT NULL::character varying,
  "content" text,
  CONSTRAINT templates_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
ALTER TABLE templates OWNER TO postgres;

CREATE TABLE templates_association
(
  template_id bigint NOT NULL,
  what character varying(255) NOT NULL,
  value_field character varying(255) NOT NULL,
  system_id bigserial NOT NULL,
  maarch_module character varying(255) NOT NULL DEFAULT 'apps'::character varying,
  CONSTRAINT templates_association_pkey PRIMARY KEY (system_id)
)
WITH (OIDS=FALSE);
ALTER TABLE templates_association OWNER TO postgres;;


CREATE TABLE templates_doctype_ext
(
  template_id bigint DEFAULT NULL,
  type_id integer NOT NULL,
  is_generated character(1) NOT NULL DEFAULT 'N'::bpchar
)
WITH (OIDS=FALSE);
ALTER TABLE templates_doctype_ext OWNER TO postgres;


