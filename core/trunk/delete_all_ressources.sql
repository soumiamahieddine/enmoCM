/* Warning : This script erase all data in Maarch Entreprise. It keep in database parameters. Howerver, all ressources (res_letterbox), history and other where be deleted */

TRUNCATE TABLE cases;
TRUNCATE TABLE cases_res;
TRUNCATE TABLE contacts;
TRUNCATE TABLE listinstance;
TRUNCATE TABLE history;
TRUNCATE TABLE history_batch;
TRUNCATE TABLE mlb_coll_ext;
TRUNCATE TABLE notes;
TRUNCATE TABLE res_letterbox;
TRUNCATE TABLE res_attachments;
TRUNCATE TABLE saved_queries;