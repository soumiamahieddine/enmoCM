-- *************************************************************************--
--                                                                          --
--                                                                          --
-- Model migration script - 19.04 to 19.12 (Run this file after migrate.sh) --
--                                                                          --
--                                                                          --
-- *************************************************************************--

DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS cases_res;

DROP TABLE IF EXISTS fp_fileplan;
DROP TABLE IF EXISTS fp_fileplan_positions;
DROP TABLE IF EXISTS fp_res_fileplan_positions;

DROP TABLE IF EXISTS folder_tmp;
ALTER TABLE res_letterbox DROP COLUMN IF EXISTS folders_system_id;

DROP TABLE IF EXISTS groupbasket_status;
DROP TABLE IF EXISTS indexingmodels;

DROP TABLE IF EXISTS mlb_coll_ext;
