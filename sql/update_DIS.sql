CREATE SEQUENCE allowed_ip_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE allowed_ip
(
  id integer NOT NULL DEFAULT nextval('allowed_ip_id_seq'::regclass),
  ip character varying(50) NOT NULL,
  CONSTRAINT allowed_ip_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);


CREATE SEQUENCE user_signatures_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE user_signatures
(
  id bigint NOT NULL DEFAULT nextval('user_signatures_seq'::regclass),
  user_id character varying(128) NOT NULL,
  signature_label character varying(255) DEFAULT NULL::character varying,
  signature_path character varying(255) DEFAULT NULL::character varying,
  signature_file_name character varying(255) DEFAULT NULL::character varying,
  fingerprint character varying(255) DEFAULT NULL::character varying,
  CONSTRAINT user_signatures_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);

ALTER TABLE users ADD ra_code character varying(255);
ALTER TABLE users ADD ra_expiration_date timestamp without time zone;