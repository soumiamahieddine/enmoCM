ALTER TABLE "security" ADD COLUMN rights_bitmask integer;
ALTER TABLE "security" ADD COLUMN mr_start_date timestamp without time zone;
ALTER TABLE "security" ADD COLUMN mr_stop_date timestamp without time zone;
ALTER TABLE "security" ADD COLUMN where_target character varying(15) NOT NULL DEFAULT 'DOC';
ALTER TABLE "security" DROP CONSTRAINT security_pkey;
ALTER TABLE "security" ADD COLUMN security_id serial NOT NULL;
ALTER TABLE "security" ADD PRIMARY KEY (security_id);
