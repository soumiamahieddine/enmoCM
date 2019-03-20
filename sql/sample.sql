TRUNCATE TABLE res_letterbox;
ALTER SEQUENCE res_id_mlb_seq restart WITH 1;
INSERT INTO res_letterbox VALUES (1, NULL, 'Demande de dérogation carte scolaire', '', 305, 'pdf', 'bblier', '2019-03-20 14:02:59.318229', '2019-03-20 14:02:59.318229', NULL, NULL, NULL, NULL, NULL, '2019-02-15 00:00:00', 'FASTHD_MAN', NULL, 'tests#', 'demande_derogation.pdf', '', '0', 24942, 'ESIG', 'PJS', NULL, NULL, NULL, 'poiuytre1357nbvc', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'res_letterbox', 'COU', 'bbain', 19, '2019-03-20 14:08:49.147061', 'N', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL);
INSERT INTO res_letterbox VALUES (2, NULL, 'Demande de travaux route 66', '', 918, 'pdf', 'bblier', '2019-03-20 16:50:45.143382', '2019-03-20 16:50:45.143382', NULL, NULL, NULL, NULL, NULL, '2019-02-15 00:00:00', 'FASTHD_MAN', NULL, 'tests#', 'sva_route_66.pdf', '', '0', 24877, 'ESIG', 'PTE', NULL, NULL, NULL, 'poiuytre1357nbvc', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'res_letterbox', 'COU', 'ccharles', NULL, NULL, 'N', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL);
INSERT INTO res_letterbox VALUES (3, NULL, 'Plainte voisin chien bruyant', '', 503, 'pdf', 'bblier', '2019-03-20 16:50:45.143382', '2019-03-20 16:50:45.143382', NULL, NULL, NULL, NULL, NULL, '2019-02-15 00:00:00', 'FASTHD_MAN', NULL, 'tests#', 'svr_route_chien_bruyant.pdf', '', '0', 24877, 'ESIG', 'DGS', NULL, NULL, NULL, 'poiuytre1357nbvc', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'res_letterbox', 'COU', 'rrenaud', NULL, NULL, 'N', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL);
INSERT INTO res_letterbox VALUES (4, NULL, 'Invitation pour échanges journées des sports', '', 110, 'pdf', 'bbain', '2019-03-20 17:53:42.472335', '2019-03-20 17:53:42.472335', NULL, NULL, NULL, 'with_empty_file', NULL, '2019-03-20 00:00:00', 'FASTHD_MAN', NULL, 'tests#', 'empty.pdf', '', '0', 111108, 'ESIG', 'PJS', NULL, NULL, NULL, 'poiuytre1357nbvc', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'res_letterbox', 'PJS', 'bbain', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL);
INSERT INTO res_letterbox VALUES (5, NULL, 'Demande de place en creche', '', 307, 'pdf', 'bblier', '2019-03-20 16:50:45.143382', '2019-03-20 16:50:45.143382', NULL, NULL, NULL, NULL, NULL, '2019-02-15 00:00:00', 'FASTHD_MAN', NULL, 'tests#', 'demande_place_creche.pdf', '', '0', 24877, 'ESIG', 'PE', NULL, NULL, NULL, 'poiuytre1357nbvc', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'res_letterbox', 'COU', 'ssaporta', NULL, NULL, 'N', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL);
INSERT INTO res_letterbox VALUES (6, NULL, 'Relance place en creche', '', 307, 'pdf', 'bblier', '2019-03-20 16:50:45.143382', '2019-03-20 16:50:45.143382', NULL, NULL, NULL, NULL, NULL, '2019-02-15 00:00:00', 'FASTHD_MAN', NULL, 'tests#', 'relance_place_creche.pdf', '', '0', 24877, 'ESIG', 'PE', NULL, NULL, NULL, 'poiuytre1357nbvc', NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'res_letterbox', 'COU', 'ssaporta', NULL, NULL, 'N', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL);
Select setval('res_id_mlb_seq', (select max(res_id)+1 from res_letterbox), false);

TRUNCATE TABLE adr_letterbox;
ALTER SEQUENCE adr_letterbox_id_seq restart WITH 1;
INSERT INTO adr_letterbox VALUES (1, 1, 'PDF', 'CONVERT_MLB', 'tests#', 'demande_derogation.pdf', '0');
INSERT INTO adr_letterbox VALUES (2, 2, 'PDF', 'CONVERT_MLB', 'tests#', 'sva_route_66.pdf', '0');
INSERT INTO adr_letterbox VALUES (3, 3, 'PDF', 'CONVERT_MLB', 'tests#', 'svr_route_chien_bruyant.pdf', '0');
INSERT INTO adr_letterbox VALUES (4, 4, 'PDF', 'CONVERT_MLB', 'tests#', 'emtpy.pdf', '0');
INSERT INTO adr_letterbox VALUES (5, 5, 'PDF', 'CONVERT_MLB', 'tests#', 'demande_place_creche.pdf', '0');
INSERT INTO adr_letterbox VALUES (6, 6, 'PDF', 'CONVERT_MLB', 'tests#', 'relance_place_creche.pdf', '0');
Select setval('adr_letterbox_id_seq', (select max(res_id)+1 from adr_letterbox), false);

TRUNCATE TABLE res_attachments;
ALTER SEQUENCE res_attachment_res_id_seq restart WITH 1;
INSERT INTO res_attachments VALUES (1, 'ar_derogation', NULL, NULL, 0, 'pdf', 'bbain', '2019-03-20 14:04:12.826168', NULL, 'MAARCH/2019D/1', NULL, 1, NULL, 'FASTHD_MAN', NULL, 'tests#', 'ar_derogation.pdf', ' ', '0', 41682, 'FRZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'letterbox_coll', 1, 'response_project', 4, 6, NULL, NULL, 'N', NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO res_attachments VALUES (2, 'ar_sva', NULL, NULL, 0, 'pdf', 'ccharles', '2019-03-20 16:51:39.510776', NULL, 'MAARCH/2019D/2', NULL, 1, NULL, 'FASTHD_MAN', NULL, 'tests#', 'ar_sva.pdf', ' ', '0', 44907, 'FRZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'letterbox_coll', 2, 'response_project', 4, 6, NULL, NULL, 'N', NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '2');
INSERT INTO res_attachments VALUES (3, 'ar_svr', NULL, NULL, 0, 'pdf', 'rrenaud', '2019-03-20 16:51:39.510776', NULL, 'MAARCH/2019D/3', NULL, 1, NULL, 'FASTHD_MAN', NULL, 'tests#', 'ar_svr.pdf', ' ', '0', 44907, 'FRZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'letterbox_coll', 3, 'response_project', 4, 6, NULL, NULL, 'N', NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '3');
INSERT INTO res_attachments VALUES (4, 'invitation', NULL, NULL, 0, 'pdf', 'bbain', '2019-03-20 17:54:00.954235', NULL, 'MAARCH/2019D/4', NULL, 1, NULL, 'FASTHD_MAN', NULL, 'tests#', 'invitation.pdf', ' ', '0', 47379, 'FRZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'letterbox_coll', 4, 'outgoing_mail', 4, 6, NULL, NULL, 'N', NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '4');
INSERT INTO res_attachments VALUES (5, 'rep_creche', NULL, NULL, 0, 'pdf', 'ssaporta', '2019-03-20 16:51:39.510776', NULL, 'MAARCH/2019D/5', NULL, 1, NULL, 'FASTHD_MAN', NULL, 'tests#', 'rep_creche.pdf', ' ', '0', 44907, 'FRZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'letterbox_coll', 5, 'response_project', 4, 6, NULL, NULL, 'N', NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '5');
INSERT INTO res_attachments VALUES (6, 'rep_standard', NULL, NULL, 0, 'pdf', 'ssaporta', '2019-03-20 16:51:39.510776', NULL, 'MAARCH/2019D/6', NULL, 1, NULL, 'FASTHD_MAN', NULL, 'tests#', 'rep_standard.pdf', ' ', '0', 44907, 'FRZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'letterbox_coll', 6, 'response_project', 4, 6, NULL, NULL, 'N', NULL, NULL, NULL, true, NULL, NULL, NULL, NULL, NULL, NULL, '6');
Select setval('res_attachment_res_id_seq', (select max(res_id)+1 from res_attachments), false);

TRUNCATE TABLE adr_attachments;
ALTER SEQUENCE adr_attachments_id_seq restart WITH 1;
INSERT INTO adr_attachments VALUES (1, 1, 'PDF', 'CONVERT_ATTACH', 'tests#', 'ar_derogation.pdf', '0');
INSERT INTO adr_attachments VALUES (2, 2, 'PDF', 'CONVERT_ATTACH', 'tests#', 'ar_sva.pdf', '0');
INSERT INTO adr_attachments VALUES (3, 3, 'PDF', 'CONVERT_ATTACH', 'tests#', 'ar_svr.pdf', '0');
INSERT INTO adr_attachments VALUES (4, 4, 'PDF', 'CONVERT_ATTACH', 'tests#', 'invitation.pdf', '0');
INSERT INTO adr_attachments VALUES (5, 5, 'PDF', 'CONVERT_ATTACH', 'tests#', 'rep_creche.pdf', '0');
INSERT INTO adr_attachments VALUES (6, 6, 'PDF', 'CONVERT_ATTACH', 'tests#', 'rep_standard.pdf', '0');
Select setval('adr_attachments_id_seq', (select max(res_id)+1 from adr_attachments), false);

TRUNCATE TABLE mlb_coll_ext;
INSERT INTO mlb_coll_ext VALUES (1, 'incoming', 4, NULL, NULL, NULL, 'simple_mail', 'MAARCH/2019A/1', '2019-03-20 00:00:00', '2019-06-17 23:59:59', NULL, NULL, NULL, 'N', 'N', NULL, 6);
INSERT INTO mlb_coll_ext VALUES (2, 'incoming', 4, NULL, NULL, NULL, 'simple_mail', 'MAARCH/2019A/2', '2019-03-20 00:00:00', '2019-05-19 23:59:59', NULL, NULL, NULL, 'N', 'N', NULL, 6);
INSERT INTO mlb_coll_ext VALUES (3, 'incoming', 4, NULL, NULL, NULL, 'simple_mail', 'MAARCH/2019A/3', '2019-03-20 00:00:00', '2019-05-19 23:59:59', NULL, NULL, NULL, 'N', 'N', NULL, 6);
INSERT INTO mlb_coll_ext VALUES (4, 'outgoing', NULL, NULL, 4, NULL, 'simple_mail', 'MAARCH/2019D/4', NULL, '2019-05-19 23:59:59', NULL, NULL, NULL, 'N', 'N', NULL, 6);
INSERT INTO mlb_coll_ext VALUES (5, 'incoming', 5, NULL, NULL, NULL, 'simple_mail', 'MAARCH/2019A/4', '2019-03-20 00:00:00', '2019-05-19 23:59:59', NULL, NULL, NULL, 'N', 'N', NULL, 6);
INSERT INTO mlb_coll_ext VALUES (6, 'incoming', 6, NULL, NULL, NULL, 'simple_mail', 'MAARCH/2019A/5', '2019-03-20 00:00:00', '2019-05-19 23:59:59', NULL, NULL, NULL, 'N', 'N', NULL, 6);

TRUNCATE TABLE listinstance;
ALTER SEQUENCE listinstance_id_seq restart WITH 1;
INSERT INTO listinstance VALUES (1, 'letterbox_coll', 1, 'DOC', 0, 'bbain', 'user_id', 'dest', 'bbain', 'PJS', 'Y', 0, 'entity_id', NULL, '', false, false);
INSERT INTO listinstance VALUES (2, 'letterbox_coll', 1, 'DOC', 0, 'DSG', 'entity_id', 'cc', 'bbain', 'PJS', 'Y', 0, 'entity_id', NULL, NULL, false, false);
INSERT INTO listinstance VALUES (3, 'letterbox_coll', 2, 'DOC', 0, 'ccharles', 'user_id', 'dest', 'ccharles', 'PTE', 'Y', 0, 'entity_id', NULL, '', false, false);
INSERT INTO listinstance VALUES (4, 'letterbox_coll', 2, 'DOC', 0, 'DSG', 'entity_id', 'cc', 'ccharles', 'PTE', 'Y', 0, 'entity_id', NULL, NULL, false, false);
INSERT INTO listinstance VALUES (5, 'letterbox_coll', 3, 'DOC', 0, 'rrenaud', 'user_id', 'dest', 'rrenaud', 'DGS', 'Y', 0, 'entity_id', NULL, '', false, false);
INSERT INTO listinstance VALUES (6, 'letterbox_coll', 4, 'DOC', 0, 'bbain', 'user_id', 'dest', 'bbain', 'PJS', 'Y', 0, 'entity_id', NULL, '', false, false);
INSERT INTO listinstance VALUES (7, 'letterbox_coll', 5, 'DOC', 0, 'ssaporta', 'user_id', 'dest', 'ssaporta', 'PE', 'Y', 0, 'entity_id', NULL, '', false, false);
INSERT INTO listinstance VALUES (8, 'letterbox_coll', 5, 'DOC', 0, 'DSG', 'entity_id', 'cc', 'bbain', 'PJS', 'Y', 0, 'entity_id', NULL, NULL, false, false);
INSERT INTO listinstance VALUES (9, 'letterbox_coll', 6, 'DOC', 0, 'ssaporta', 'user_id', 'dest', 'ssaporta', 'PE', 'Y', 0, 'entity_id', NULL, '', false, false);
INSERT INTO listinstance VALUES (10, 'letterbox_coll', 6, 'DOC', 0, 'DSG', 'entity_id', 'cc', 'bbain', 'PJS', 'Y', 0, 'entity_id', NULL, NULL, false, false);
Select setval('listinstance_id_seq', (select max(listinstance_id)+1 from listinstance), false);

