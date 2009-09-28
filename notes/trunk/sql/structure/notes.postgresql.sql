CREATE TABLE notes
(
  id bigserial NOT NULL ,
  identifier bigint NOT NULL,
  tablename character varying(50),
  user_id character varying(50) NOT NULL,
  date date NOT NULL,
  note_text text NOT NULL,
  coll_id character varying(50),
  CONSTRAINT notes_pkey PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
ALTER TABLE notes OWNER TO postgres;
