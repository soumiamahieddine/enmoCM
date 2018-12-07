-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 18.10 to develop                                --
--                                                                          --
--                                                                          --
-- *************************************************************************--

ALTER TABLE res_letterbox DROP COLUMN IF EXISTS external_signatory_book_id;
ALTER TABLE res_letterbox ADD COLUMN external_signatory_book_id integer;

ALTER TABLE users DROP COLUMN IF EXISTS external_id;
ALTER TABLE users ADD COLUMN external_id json DEFAULT '{}';
