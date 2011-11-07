ALTER TABLE templates_association ADD COLUMN description character varying(255);
ALTER TABLE templates_association ADD COLUMN diffusion_type character varying(50);
ALTER TABLE templates_association ADD COLUMN diffusion_properties character varying(255);
ALTER TABLE templates_association ADD COLUMN exclusion_type character varying(50);
ALTER TABLE templates_association ADD COLUMN exclusion_properties character varying(255);
ALTER TABLE templates_association ADD COLUMN is_attached boolean;


