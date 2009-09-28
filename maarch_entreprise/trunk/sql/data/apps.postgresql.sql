-- Maarch LetterBox v3 sample data : Application

-- DOCTYPES and LEVELS
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, enabled) VALUES (1, 'Courriers', 'Y');
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, enabled) VALUES (2, 'Autres', 'Y');
INSERT INTO doctypes_first_level (doctypes_first_level_id, doctypes_first_level_label, enabled) VALUES (10, 'C3', 'N');

INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, enabled) VALUES (1, 'Attribution', 1, 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, enabled) VALUES (2, 'Avis', 1, 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, enabled) VALUES (3, 'Decaissement', 1, 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, enabled) VALUES (4, 'Divers', 1, 'Y');
INSERT INTO doctypes_second_level (doctypes_second_level_id, doctypes_second_level_label, doctypes_first_level_id, enabled) VALUES (5, 'Suivi', 1, 'Y');

INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 24, 'ATR - Attribution de marche', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 25, 'LCO - Lettre de commande', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 26, 'OFT - Offre Technique', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 27, 'OFI - Offre Financiere', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 29, 'PVO - Proces Verbal d Ouverture des offres', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 30, 'PVD - Proces Verbal de Depouillement des offres', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 31, 'PVC - Proces Verbal de Commission Centrale des Marches et Contrat de l Etat', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 32, 'OSD - Ordre de Service de Demarrage', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 33, 'DAO - Dossier d Appel D Offres', 'Y', 1, 2, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 34, 'AAO - Avis d Appel d offre', 'Y', 1, 2, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 35, 'FAC - Facture', 'Y', 1, 3, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 36, 'DEC - Decompte', 'Y', 1, 3, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 37, 'INF - Diverses informations', 'Y', 1, 4, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 38, 'COU - Courrier', 'Y', 1, 4, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 39, 'REC - Reception', 'Y', 1, 4, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 40, 'DES - Devis estimatif confidentiel', 'Y', 1, 4, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 41, 'TRF - Terme De Reference', 'Y', 1, 4, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 42, 'DEQ - Devis Quantitatif', 'Y', 1, 4, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 43, 'CCC - Cahier de Charge pour le Controle', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 44, 'PEP - Plan d Execution du Projet', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 45, 'PIO - Proces Verbal de l Implantation de l ouvrage', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 46, 'PRP - Proces Verbal de Reception Provisoire', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 47, 'AVE - Avenant', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 48, 'RAM - Rapport mensuel', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 49, 'RAP - Rapport de mission', 'Y', 1, 5, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');
INSERT INTO doctypes (coll_id, type_id, description, enabled, doctypes_first_level_id, doctypes_second_level_id, primary_retention, secondary_retention, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, is_master) VALUES ('letterbox_coll', 28, 'MAR - Marche', 'Y', 1, 1, NULL, NULL, '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', '0000000000', 'N');

INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (24, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (25, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (26, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (27, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (28, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (29, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (30, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (31, 10, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (32, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (33, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (34, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (35, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (36, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (37, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (38, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (39, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (40, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (41, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (42, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (43, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (44, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (45, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (46, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (47, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (48, 21, 14, 1);
INSERT INTO mlb_doctype_ext (type_id, process_delay, delay1, delay2) VALUES (49, 21, 14, 1);


-- USERS, GROUPS and ENTITIES
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('ADMINISTRATEURS', 'Administrateurs fonctionnels', ' ', ' ', ' ', ' ', ' ', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('ARCHIVISTES', 'Archivistes et operateurs de scan', ' ', ' ', ' ', ' ', ' ', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('EMPLOYES', 'Employes', ' ', ' ', ' ', ' ', ' ', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('DIRECTEURS', 'Directeurs et personnes habilitees', ' ', ' ', ' ', ' ', ' ', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('VALIDEURS', 'Valideurs de courrier', ' ', ' ', ' ', ' ', ' ', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('REDACTEURS', 'Groupe des createurs de courriers', ' ', ' ', ' ', ' ', ' ', 'Y');
INSERT INTO usergroups (group_id, group_desc, administrator, custom_right1, custom_right2, custom_right3, custom_right4, enabled) VALUES ('CORRESPONDANTS', 'Correspondants Archive', ' ', ' ', ' ', ' ', ' ', 'Y');

INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ccharles', 'ef9689be896dacd901cae4f13593e90d', 'Charlotte', 'CHARLES', '+33 1 47 24 51 ', 'info@maarch.org', 'Accounting department - DFI', NULL, NULL, NULL, '2b67f8017119d7de32f300be3e97ccb4', '2008-09-10 15:09:23', 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ppetit', 'ef9689be896dacd901cae4f13593e90d', 'Patricia', 'PETIT', '+33 1 47 24 51 ', 'info@maarch.org', '', NULL, NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('pparker', 'ef9689be896dacd901cae4f13593e90d', 'Peter', 'PARKER', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('eerina', 'ef9689be896dacd901cae4f13593e90d', 'Edith', 'ERINA', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ddur', 'ef9689be896dacd901cae4f13593e90d', 'Dominique', 'DUR', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('rrizzo', 'ef9689be896dacd901cae4f13593e90d', 'Rita', 'RIZZO', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('rrenaud', 'ef9689be896dacd901cae4f13593e90d', 'Robert', 'RENAUD', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('bbina', 'ef9689be896dacd901cae4f13593e90d', 'Brigitte', 'BINA', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('mmanfred', 'ef9689be896dacd901cae4f13593e90d', 'Martin', 'MANFRED', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('kkaar', 'ef9689be896dacd901cae4f13593e90d', 'Kathy', 'KAAR', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('aackermann', 'ef9689be896dacd901cae4f13593e90d', 'Amanda', 'ACKERMANN', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ttong', 'ef9689be896dacd901cae4f13593e90d', 'Tony', 'TONG', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('jjane', 'ef9689be896dacd901cae4f13593e90d', 'Jenny', 'JANE', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('bboule', 'ef9689be896dacd901cae4f13593e90d', 'Bruno', 'BOULE', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('bbain', 'ef9689be896dacd901cae4f13593e90d', 'Barbara', 'BAIN', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ccohen', 'ef9689be896dacd901cae4f13593e90d', 'Celine', 'COHEN', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('jjoubert', 'ef9689be896dacd901cae4f13593e90d', 'Jules', 'JOUBERT', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ssaporta', 'ef9689be896dacd901cae4f13593e90d', 'Sabrina', 'SAPORTA', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ssissoko', 'ef9689be896dacd901cae4f13593e90d', 'Sessime', 'SISSOKO', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('ddogem', 'ef9689be896dacd901cae4f13593e90d', 'Dina', 'DOGEM', '', 'info@maarch.org', '', '0', NULL, NULL, '', NULL, 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('superadmin', '17c4520f6cfd1ab53d8745e84681eb49', 'Super', 'ADMIN', '', 'admin@maarch.org', 'Maarch', '11', NULL, NULL, '764759df274008fc4cffd89ced0449d8', '2009-09-14 10:09:52', 'Y', 'N', NULL, 'OK');
INSERT INTO users (user_id, "password", firstname, lastname, phone, mail, department, custom_t1, custom_t2, custom_t3, cookie_key, cookie_date, enabled, change_password, delay, status) VALUES ('bblier', 'ef9689be896dacd901cae4f13593e90d', 'Bernard', 'BLIER', '+33 1 47 24 51 ', 'info@maarch.org', '', NULL, NULL, NULL, '053123818f126439a94ce074acf71b92', '2009-09-14 11:09:04', 'Y', 'N', NULL, 'OK');

INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ccharles', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ppetit', 'DIRECTEURS', 'Y', 'Maire');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('pparker', 'ARCHIVISTES', 'Y', 'Archiviste');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ckesney', 'ADMINISTRATEURS', 'Y', 'Administratrice fonctionnelle');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('eerina', 'EMPLOYES', 'Y', 'Secretaire');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ddur', 'DIRECTEURS', 'Y', 'Elu');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('rrizzo', 'DIRECTEURS', 'Y', 'Elu');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('rrenaud', 'DIRECTEURS', 'Y', 'Directeur General des Services');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('bbina', 'REDACTEURS', 'Y', 'Agent');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('mmanfred', 'DIRECTEURS', 'Y', 'Directeur General Adjoint');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('kkaar', 'EMPLOYES', 'Y', 'Secretaire');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('aackermann', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ttibert', 'EMPLOYES', 'Y', 'Agent');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ttong', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('jjane', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('bboule', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('bbain', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ccohen', 'EMPLOYES', 'Y', 'Responsable de service');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('jjoubert', 'EMPLOYES', 'Y', 'Assistant');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ssaporta', 'EMPLOYES', 'Y', 'Assistante');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ssissoko', 'DIRECTEURS', 'Y', 'Directeur Informatique');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ssissoko', 'ADMINISTRATEURS', 'N', 'Directeur Informatique');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('bblier', 'REDACTEURS', 'Y', 'Responsable service Courrier');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ddogem', 'DIRECTEURS', 'N', 'Secretaire General');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ddogem', 'VALIDEURS', 'Y', 'Secretaire General');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('kkaar', 'CORRESPONDANTS', 'N', 'Correspondante Archives');
INSERT INTO usergroup_content (user_id, group_id, primary_group, "role") VALUES ('ssaporta', 'CORRESPONDANTS', 'N', 'Correspondante Archive');

INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_users');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_groups');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_architecture');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'view_history');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'view_history_batch');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'xml_param_services');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_status');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_actions');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_apa');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'manage_entities');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_foldertypes');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ADMINISTRATEURS', 'admin_templates');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'admin');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'admin_apa');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'manage_apa');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'physical_archive');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'physical_archive_box_read');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'physical_archive_box_manage');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'physical_archive_batch_read');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', 'physical_archive_batch_manage');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('ARCHIVISTES', '_print_sep');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'my_alerts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'use_alerts_on_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'use_alerts_on_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'folder_search');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('EMPLOYES', 'modify_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'adv_search_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'search_customer');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'my_alerts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'use_alerts_on_doc');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'use_alerts_on_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'folder_search');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'modify_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('DIRECTEURS', 'delete_folder');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('VALIDEURS', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('REDACTEURS', 'index_mlb');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('REDACTEURS', 'my_contacts');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('REDACTEURS', 'view_baskets');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('REDACTEURS', 'add_copy_in_process');
INSERT INTO usergroups_services (group_id, service_id) VALUES ('CORRESPONDANTS', 'reserve_apa');

INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('OrganisationX', 'Mairie Demo s/ Seine', 'Y', '', '', '', '', '', '', '', '', '', 'Direction');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DRH', 'Ressources Humaines', 'Y', '', '', '', '', '', '', '', '', 'PSF', 'Bureau');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('SGE', 'Secretariat General', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Bureau');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COU', 'Service Courrier', 'Y', '', '', '', '', '', '', '', '', 'SGE', 'Bureau');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('FIN', 'Service Financier', 'Y', '', '', '', '', '', '', '', '', 'PSF', 'Bureau');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('INF', 'Service Informatique', 'Y', '', '', '', '', '', '', '', '', 'PSF', 'Bureau');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('ARC', 'Service des Archives', 'Y', '', '', '', '', '', '', '', '', 'PSF', 'Bureau');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('CAB', 'Cabinet du Maire', 'Y', '', '', '', '', '', '', '', '', 'OrganisationX', 'Direction');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGA', 'Direction Generale Adjointe', 'Y', '', '', '', '', '', '', '', '', 'CAB', 'Direction');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('ELUS', 'Elus', 'Y', '', '', '', '', '', '', '', '', 'OrganisationX', 'Direction');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('DGS', 'Direction Generale des Services', 'Y', '', '', '', '', '', '', '', '', 'CAB', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PCU', 'Pole Culturel', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PJS', 'Pole Jeunesse et Sports', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSF', 'Pole Services Fonctionnels', 'Y', '', '', '', '', '', '', '', '', 'DGA', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PSO', 'Pole Social', 'Y', '', '', '', '', '', '', '', '', 'CAB', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('PTE', 'Pole Technique', 'Y', '', '', '', '', '', '', '', '', 'DGS', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COARCDGS', 'Correspondant Archive DGS', 'Y', '', '', '', '', '', '', '', '', 'ARC', 'Service');
INSERT INTO entities (entity_id, entity_label, enabled, adrs_1, adrs_2, adrs_3, zipcode, city, country, email, business_id, parent_entity_id, entity_type) VALUES ('COARCDGA', 'Correspondant Archive DGA', 'Y', '', '', '', '', '', '', '', '', 'ARC', 'Service');

INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ccharles', 'PTE', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ppetit', 'CAB', 'Maire', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('pparker', 'ARC', 'Archiviste', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('eerina', 'CAB', 'Secretaire', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddur', 'ELUS', 'Elu', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('rrizzo', 'ELUS', 'Elu', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('rrenaud', 'DGS', 'Directeur General des Services', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bbina', 'COU', 'Agent', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('mmanfred', 'DGA', 'Directeur General Adjoint', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('kkaar', 'DGA', 'Secretaire', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('kkaar', 'COARCDGA', 'Correspondante Archive', 'N');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('aackermann', 'PSF', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ttibert', 'PSF', 'Agent', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ttong', 'DRH', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjane', 'FIN', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bboule', 'PCU', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bbain', 'PJS', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ccohen', 'PSO', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('jjoubert', 'SGE', 'Assistant', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssaporta', 'DGS', 'Assistante', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssaporta', 'COARCDGS', 'Correspondante Archive', 'N');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ssissoko', 'INF', 'Directeur Informatique', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('bblier', 'COU', 'Responsable de service', 'Y');
INSERT INTO users_entities (user_id, entity_id, user_role, primary_entity) VALUES ('ddogem', 'SGE', 'Secretaire General', 'Y');

INSERT INTO "security" (group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete) VALUES ('EMPLOYES', 'letterbox_coll', 'DESTINATION = @my_primary_entity
', '', 'Y', 'Y', 'N');
INSERT INTO "security" (group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete) VALUES ('DIRECTEURS', 'letterbox_coll', '(DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])) or DESTINATION is NULL', '', 'Y', 'Y', 'Y');
INSERT INTO "security" (group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete) VALUES ('REDACTEURS', 'letterbox_coll', 'DESTINATION = @my_primary_entity or TYPIST=@user', '', 'Y', 'Y', 'Y');
INSERT INTO "security" (group_id, coll_id, where_clause, maarch_comment, can_insert, can_update, can_delete) VALUES ('CORRESPONDANTS', 'letterbox_coll', '(DESTINATION = @my_primary_entity or DESTINATION in (@subentities[@my_primary_entity])) or DESTINATION is NULL', '', 'N', 'N', 'N');

-- DOCSERVERS
INSERT INTO docservers (docserver_id, device_type, device_label, is_readonly, enabled, size_limit, actual_size, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority) VALUES ('letterbox_ai', NULL, NULL, 'N', 'Y', 100000000, 3271717, 'C:\\Maarch\\Docserver\\DGGT_ai\\', NULL, NULL, NULL, '2007-11-19 11:41:22', NULL, 'letterbox_coll', 20);
INSERT INTO docservers (docserver_id, device_type, device_label, is_readonly, enabled, size_limit, actual_size, path_template, ext_docserver_info, chain_before, chain_after, creation_date, closing_date, coll_id, priority) VALUES ('letterbox', NULL, NULL, 'N', 'Y', 100000000, 7949134, 'C:\\Maarch\\Docserver\\DGGT\\', NULL, NULL, NULL, '2007-11-19 11:41:22', NULL, 'letterbox_coll', 10);

-- ACTIONS and BASKETS
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (15, '', 'Prelever une archive', 'OUT', 'N', 'Y', 'confirm_status', 'Y', 'advanced_physical_archive', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (16, '', 'Reintegrer une archive', 'POS', 'N', 'Y', 'confirm_status', 'Y', 'advanced_physical_archive', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (2, 'to_validate', 'A valider', 'VAL', 'Y', 'N', 'confirm_status', 'N', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (21, 'indexing', 'Indexation', 'NEW', 'N', 'Y', 'index_mlb', 'Y', 'apps', 'Y');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (18, '', 'A valider', 'NEW', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (19, '', 'Traiter document', 'COU', 'N', 'Y', 'process', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (1, 'redirect', 'Redirection', 'NONE', 'Y', 'Y', 'redirect', 'Y', 'entities', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (22, '', 'En attente de validation', 'VAL', 'N', 'Y', '', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (23, 'indexing', 'Valider courrier', 'NEW', 'N', 'Y', 'validate_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (20, '', 'Cloturer', 'END', 'N', 'Y', 'close_mail', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (3, '', 'Retourner au service Courrier', 'RET', 'N', 'Y', 'confirm_status', 'Y', 'apps', 'N');
INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin, create_id) VALUES (100, '', 'Voir le document', '', 'N', 'Y', 'view', 'N', 'apps', 'N');

INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('OUT', 'Prelevee', 'N', '', 'advanced_physical_archive', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('POS', 'Reintegree', 'N', '', 'advanced_physical_archive', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('COU', 'En cours', 'Y', 'mail.gif', 'apps', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('NEW', 'Nouveau courrier', 'Y', 'mail_new.gif', 'apps', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('RSV', 'Reserve', 'N', '', 'apps', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('DEL', 'Supprime', 'Y', NULL, 'apps', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('END', 'Clos', 'Y', 'mail_end.gif', 'apps', 'Y');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('VAL', 'A valider', 'Y', NULL, 'apps', 'N');
INSERT INTO status (id, label_status, is_system, img_filename, maarch_module, can_be_searched) VALUES ('RET', 'Retour courrier', 'N', '', 'apps', 'Y');

INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('apa_coll', 'APA_reservation', 'Archives reservees', 'Corbeille des archives reservees', 'res_view_apa.status = ''RSV'' and (ORIGIN= @my_primary_entity or ORIGIN in (@subentities[@my_primary_entity]))', 'NO', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('apa_coll', 'APA_picking', 'Archives prelevees', 'Corbeille des archives prelevees', 'res_view_apa.status = ''OUT'' and (ORIGIN= @my_primary_entity or ORIGIN in (@subentities[@my_primary_entity]))', 'NO', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'CopyMailBasket', 'Mes courriers en copie', 'Mes courriers en copie', 'res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''user_id'' and item_id = @user and item_mode = ''cc'') or res_id in (select res_id from listinstance WHERE coll_id = ''letterbox_coll'' and item_type = ''entity_id'' and item_mode = ''cc'' and item_id in (@my_entities))', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'LateMailBasket', 'Mes courriers en retard', 'Mes courriers en retard', '1=1', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'RetourCourrier', 'Retours Courrier', 'Courriers retournes au service Courrier', 'STATUS=''RET'' ', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'IndexingBasket', 'Corbeille d''indexation', 'Corbeille d''indexation', ' ', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'MyBasket', 'Mes courriers a traiter', 'Mes courriers a traiter', '(status =''NEW'' or status =''COU'') and dest_user = @user', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'ValidationBasket', 'Mes courriers a valider', 'Mes courriers a valider', 'status = ''VAL''', 'N', 'Y');
INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES ('letterbox_coll', 'DepartmentBasket', 'Corbeille de supervision', 'Corbeille de supervision', 'destination in (@my_entities, @subentities[@my_primary_entity]) and (status <> ''DEL'' AND status <> ''REP'')', 'N', 'Y');

INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('REDACTEURS', 'IndexingBasket', 2, NULL, NULL, 'redirect_to_action', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('EMPLOYES', 'MyBasket', 1, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('DIRECTEURS', 'MyBasket', 1, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('VALIDEURS', 'ValidationBasket', 1, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('VALIDEURS', 'MyBasket', 2, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('DIRECTEURS', 'DepartmentBasket', 2, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('DIRECTEURS', 'CopyMailBasket', 4, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('EMPLOYES', 'CopyMailBasket', 5, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('VALIDEURS', 'CopyMailBasket', 6, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('REDACTEURS', 'CopyMailBasket', 7, NULL, NULL, 'auth_dep', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ARCHIVISTES', 'APA_reservation', 1, NULL, NULL, 'apa_basket_list', 'N', 'N', 'N');
INSERT INTO groupbasket (group_id, basket_id, "sequence", redirect_basketlist, redirect_grouplist, result_page, can_redirect, can_delete, can_insert) VALUES ('ARCHIVISTES', 'APA_picking', 2, NULL, NULL, 'apa_basket_list', 'N', 'N', 'N');

INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (22, '', 'REDACTEURS', 'IndexingBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (21, '', 'REDACTEURS', 'IndexingBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (23, '', 'VALIDEURS', 'ValidationBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'DIRECTEURS', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'DIRECTEURS', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'DIRECTEURS', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'EMPLOYES', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'EMPLOYES', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'EMPLOYES', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (20, '', 'VALIDEURS', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (1, '', 'VALIDEURS', 'MyBasket', 'N', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (19, '', 'VALIDEURS', 'MyBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'DIRECTEURS', 'DepartmentBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'DIRECTEURS', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'EMPLOYES', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'VALIDEURS', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (100, '', 'REDACTEURS', 'CopyMailBasket', 'N', 'N', 'Y');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (15, '', 'ARCHIVISTES', 'APA_reservation', 'Y', 'Y', 'N');
INSERT INTO actions_groupbaskets (id_action, where_clause, group_id, basket_id, used_in_basketlist, used_in_action_page, default_action_list) VALUES (16, '', 'ARCHIVISTES', 'APA_picking', 'Y', 'Y', 'N');

INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (5, 'REDACTEURS', 'IndexingBasket', 21, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (2, 'DIRECTEURS', 'MyBasket', 1, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (3, 'EMPLOYES', 'MyBasket', 1, '', 'MY_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (8, 'VALIDEURS', 'ValidationBasket', 23, '', 'ALL_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (100, 'DIRECTEURS', 'DepartmentBasket', 1, '', 'MY_ENTITIES', 'ENTITY');
INSERT INTO groupbasket_redirect (system_id, group_id, basket_id, action_id, entity_id, keyword, redirect_mode) VALUES (101, 'DIRECTEURS', 'DepartmentBasket', 1, '', 'MY_ENTITIES', 'USERS');

INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DRH', 'entity_id', 0, 'ttong', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'SGE', 'entity_id', 0, 'ddogem', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'COU', 'entity_id', 0, 'bblier', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'FIN', 'entity_id', 0, 'jjane', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'INF', 'entity_id', 0, 'ssissoko', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'ARC', 'entity_id', 0, 'pparker', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'ppetit', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'CAB', 'entity_id', 0, 'rrizzo', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DGA', 'entity_id', 0, 'mmanfred', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DGA', 'entity_id', 0, 'ppetit', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'ELUS', 'entity_id', 0, 'rrizzo', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'ELUS', 'entity_id', 0, 'ppetit', 'user_id', 'cc', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'DGS', 'entity_id', 0, 'rrenaud', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PCU', 'entity_id', 0, 'bboule', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PJS', 'entity_id', 0, 'bbain', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PSF', 'entity_id', 0, 'aackermann', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PSO', 'entity_id', 0, 'ccohen', 'user_id', 'dest', 'DOC');
INSERT INTO listmodels (coll_id, object_id, object_type, "sequence", item_id, item_type, item_mode, listmodel_type) VALUES ('letterbox_coll', 'PTE', 'entity_id', 0, 'ccharles', 'user_id', 'dest', 'DOC');



-- OTHER
INSERT INTO contacts (contact_id, lastname, firstname, society, function, address_num, address_street, address_complement, address_town, address_postal_code, address_country, email, phone, other_data, is_corporate_person, user_id, title, enabled) VALUES
(1, 'Chaplin', 'Charlie', 'Maarch', 'Directeur artistique', '65', 'rue de la croix', 'test', 'nanterre', '92000', 'France', 'test@maarch.org', '01010101', 'khjohpuief', 'N', '', 'Monsieur', 'Y'),
(3, '', '', 'Maarch', '', '', '', '', '', '', 'France', '', '', '', 'Y', '', '', 'Y'),
(4, '', '', 'Warner', '', '', '', '', '', '', 'USA', '', '', '', 'Y', '', '', 'Y'),
(5, 'Majestrix', 'Diana', 'Warner', 'Directeur artistique', '', '', '', '', '', 'USA', '', '', '', 'N', '', 'Madame', 'Y'),
(7, '', '', 'Maarch', '', '65', 'rue de la croix', 'test', 'nanterre', '92000', 'France', 'test@maarch.org', '01010101', 'autre', 'Y', 'pparker', '', 'Y'),
(8, 'Carlin', 'Bruno', 'Maarch', 'Directeur Marketing', '65', 'rue de la croix', 'test', 'nanterre', '92000', 'France', 'test@maarch.org', '01010101', 'auykiu', 'N', 'pparker', 'Monsieur', 'Y'),
(9, '', '', 'Adorateurs du Froid', '', '43', 'rue du froid', 'fait trop chaud', 'coldcity', '11111', 'coldcountry', 'Adorateurs@froid.cold', '01010101', 'fait vraiment trop chaud', 'Y', 'pparker', '', 'Y'),
(10, '', '', 'TOTO', '', '', '', '', '', '', '', '', '', '', 'Y', 'pparker', '', 'Y'),
(11, '', '', 'fggdgg', '', '', '', '', '', '', '', '', '', '', 'Y', 'pparker', '', 'Y'),
(12, '', '', 'yuiop', '', '', '', '', '', '', '', '', '', 'dffff', 'Y', 'pparker', '', 'Y'),
(13, '', '', 'ffff', '', '', '', '', '', '', '', '', '', 'fff', 'Y', 'pparker', '', 'Y');

INSERT INTO parameters (id, param_value_string, param_value_int) VALUES
('workbatch_rec', '', 7),
('folder_id_increment', '', 152),
('work_batch_autoimport_id', NULL, 1),
('ar_index__', NULL, 3),
('ar_index_pparker_incoming', NULL, 3),
('ar_index_pparker_outgoing', NULL, 3),
('ar_index_pparker_internal', NULL, 3),
('ar_index_pparker_market_document', NULL, 3);

INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (2, 'AR_MAARCH', '2009-08-20 16:01:00', 'Accus√© de r√©ception Maarch', '<p style="TEXT-ALIGN: left"><img src="img/default_maarch.gif" alt="" width="278" height="80" />&nbsp;</p>
<p><em><font face="Arial Black" size="2" color="#3366ff">La gestion de courriers Open source !</font></em><br />Mail : info@maarch.org<br />Web : http://www.maarch.org</p>
<p style="TEXT-ALIGN: right"><font size="2">Nanterre, le [NOW]</font></p>
<p><font size="2">Cher&nbsp;&nbsp;[CONTACT_LASTNAME]</font></p>
<p><font size="2">Nous accusons r&eacute;ception de votre courrier du <strong>BLABLA</strong>, et mettons tout en oeuvre pour vous r&eacute;pondre dans les plus brefs d&eacute;lais.</font></p>
<p><font size="2">Sachez qu''en cas de besoin urgent nos bureaux sont ouverts de 8h00 &agrave; 15h00, du lundi au samedi.</font></p>
<p><font size="2">Le num&eacute;ro vert o&ugrave; vous pouvez nous appeler est le 0800 455 24.</font></p>
<p><font size="2">Votre n&deg; de dossier &agrave; rappeler dans toute correspondance est le : [CHRONO]</font></p>
<p>&nbsp;</p>
<p><font size="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cordialement,</font></p>
<p><font size="2"><em>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [CURRENT_USER_FIRSTNAME] [CURRENT_USER_LASTNAME]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [DESTINATION]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; T&eacute;l : [CURRENT_USER_PHONE]<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Courriel : [CURRENT_USER_EMAIL]</em></font></p>
<p>&nbsp;</p>');
INSERT INTO templates (id, label, creation_date, template_comment, content) VALUES (1, 'TEST', '2009-08-20 16:01:00', 'Test des mots-cl√©s', '<h1>Liste des mots-cl&eacute;s utilisables dans les mod&egrave;les de reponse</h1>
<p>&nbsp;</p>
<table border="0">
<tbody>
<tr>
<td><font size="2">Contact externe: Civilit&eacute;&nbsp;</font></td>
<td><strong><font size="2">[CONTACT_TITLE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe: Nom</font></td>
<td><strong><font size="2">[CONTACT_LASTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe: Pr&eacute;nom</font></td>
<td><strong><font size="2">[CONTACT_FIRSTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Organisation</font></td>
<td><strong><font size="2">[CONTACT_SOCIETY]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse N&deg;</font></td>
<td><strong><font size="2">[CONTACT_ADRS_NUM]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Rue</font></td>
<td><strong><font size="2">[CONTACT_ADRS_STREET]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Complement</font></td>
<td><strong><font size="2">[CONTACT_ADRS_COMP]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Ville</font></td>
<td><strong><font size="2">[CONTACT_ADRS_TOWN]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse CP</font></td>
<td><strong><font size="2">[CONTACT_ADRS_PC]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact externe : Adresse Pays</font></td>
<td><strong><font size="2">[CONTACT_ADRS_COUNTRY]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact interne: Nom</font></td>
<td><strong><font size="2">[USER_LASTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Contact interne: Pr&eacute;nom</font></td>
<td><strong><font size="2">[USER_FIRSTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Service Traitant</font></td>
<td><strong><font size="2">[DESTINATION]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Type de document</font></td>
<td><strong><font size="2">[DOCTYPE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Cat&eacute;gorie</font></td>
<td><strong><font size="2">[CAT_ID]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Nature</font></td>
<td><strong><font size="2">[NATURE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date d''arriv&eacute;e&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[ADMISSION_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date du courrier&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[DOC_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date limite de traitement&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[PROCESS_LIMIT_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Notes de traitement&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[PROCESS_NOTES]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Date de cl&ocirc;ture&nbsp;&nbsp;</font></td>
<td><strong><font size="2">[CLOSING_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Objet</font></td>
<td><strong><font size="2">[SUBJECT]</font></strong></td>
</tr>
<tr>
<td><font size="2">Courrier: Num&eacute;ro chrono</font></td>
<td><strong><font size="2">[CHRONO]</font></strong></td>
</tr>
<tr>
<td><font size="2">Document: Auteur</font></td>
<td><strong><font size="2">[AUTHOR]</font></strong></td>
</tr>
<tr>
<td><font size="2">Document: Date d''enregistrement</font></td>
<td><strong><font size="2">[CREATION_DATE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Date du jour</font></td>
<td><strong><font size="2">[NOW]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Nom du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_LASTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Pr&eacute;nom du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_FIRSTNAME]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: T&eacute;l&eacute;phone du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_PHONE]</font></strong></td>
</tr>
<tr>
<td><font size="2">Sp&eacute;cial: Mail du destinataire traitant</font></td>
<td><strong><font size="2">[CURRENT_USER_EMAIL]</font></strong></td>
</tr>
</tbody>
</table>
<p><font size="2">&nbsp;</font></p>');


--
-- TOC entry 2326 (class 0 OID 39180)
-- Dependencies: 1461
-- Data for Name: templates_association; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (2, 'destination', 'PJS', 4, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'OrganisationX', 77, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'CAB', 78, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'DGA', 79, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'PSF', 80, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'DRH', 81, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'FIN', 82, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'INF', 83, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'ARC', 84, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'COARCDGS', 85, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'COARCDGA', 86, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'DGS', 87, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'SGE', 88, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'COU', 89, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'PCU', 90, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'PJS', 91, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'PTE', 92, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'PSO', 93, 'entities');
INSERT INTO templates_association (template_id, what, value_field, system_id, maarch_module) VALUES (1, 'destination', 'ELUS', 94, 'entities');


INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 47, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 46, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 45, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 48, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 44, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 43, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 49, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 50, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 51, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 52, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (NULL, 53, 'N');
INSERT INTO templates_doctype_ext (template_id, type_id, is_generated) VALUES (1, 54, 'Y');

-- DOCS
INSERT INTO res_letterbox (res_id, title, subject, description, publisher, contributor, type_id, format, typist, creation_date, fulltext_result, ocr_result, converter_result, author, author_name, identifier, source, doc_language, relation, coverage, doc_date, docserver_id, folders_system_id, arbox_id, path, filename, offset_doc, logical_adr, fingerprint, filesize, is_paper, page_count, scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark, envelop_id, status, destination, approver, validation_date, work_batch, origin, is_ingoing, priority, arbatch_id, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, tablename, initiator, dest_user, video_batch, video_time, video_user) VALUES (1, NULL, 'Demande de Formation DIF', NULL, NULL, NULL, 37, 'pdf', 'bblier', '2009-09-14 14:35:46.608', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-14 00:00:00', 'letterbox', 1, '3', '1#', '4.pdf', ' ', ' ', '371a976eb690e1b4a5ab4bccd763281b', 12032, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'COU', 'PJS', NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'res_invoices', NULL, 'bbain', NULL, NULL, NULL);
INSERT INTO res_letterbox (res_id, title, subject, description, publisher, contributor, type_id, format, typist, creation_date, fulltext_result, ocr_result, converter_result, author, author_name, identifier, source, doc_language, relation, coverage, doc_date, docserver_id, folders_system_id, arbox_id, path, filename, offset_doc, logical_adr, fingerprint, filesize, is_paper, page_count, scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark, envelop_id, status, destination, approver, validation_date, work_batch, origin, is_ingoing, priority, arbatch_id, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, tablename, initiator, dest_user, video_batch, video_time, video_user) VALUES (2, NULL, 'Facture n∞234c', NULL, NULL, NULL, 24, 'pdf', 'bblier', '2009-09-14 14:30:10.859', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-14 00:00:00', 'letterbox', 1, '3', '1#', '3.pdf', ' ', ' ', 'e17e86ab6f13f13abe5ce5ecced3a824', 83932, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'COU', 'PJS', NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'res_invoices', NULL, 'bbain', NULL, NULL, NULL);
INSERT INTO res_letterbox (res_id, title, subject, description, publisher, contributor, type_id, format, typist, creation_date, fulltext_result, ocr_result, converter_result, author, author_name, identifier, source, doc_language, relation, coverage, doc_date, docserver_id, folders_system_id, arbox_id, path, filename, offset_doc, logical_adr, fingerprint, filesize, is_paper, page_count, scan_date, scan_user, scan_location, scan_wkstation, scan_batch, burn_batch, scan_postmark, envelop_id, status, destination, approver, validation_date, work_batch, origin, is_ingoing, priority, arbatch_id, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_d6, custom_t7, custom_d7, custom_t8, custom_d8, custom_t9, custom_d9, custom_t10, custom_d10, custom_t11, custom_t12, custom_t13, custom_t14, custom_t15, tablename, initiator, dest_user, video_batch, video_time, video_user) VALUES (3, NULL, 'Demande de logement', NULL, NULL, NULL, 38, 'pdf', 'bblier', '2009-09-14 14:55:08.67', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-14 00:00:00', 'letterbox', NULL, '3', '1#', '5.pdf', ' ', ' ', '6c6a9a06268b3d651686162350778ff0', 111791, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'VAL', 'DRH', NULL, NULL, NULL, NULL, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'res_invoices', NULL, 'ttong', NULL, NULL, NULL);

INSERT INTO mlb_coll_ext (res_id, category_id, exp_contact_id, exp_user_id, dest_contact_id, dest_user_id, nature_id, alt_identifier, admission_date, answer_type_bitmask, other_answer_desc, process_limit_date, process_notes, closing_date, alarm1_date, alarm2_date, flag_notif, flag_alarm1, flag_alarm2) VALUES (1, 'incoming', NULL, 'bboule', NULL, NULL, 'simple_mail', 'PJS/3/37/14092009/2/2', '2009-09-14 00:00:00', '000000', '[PrÈciser]', '2009-09-24 00:00:00', '', NULL, NULL, NULL, 'N', 'N', 'N');
INSERT INTO mlb_coll_ext (res_id, category_id, exp_contact_id, exp_user_id, dest_contact_id, dest_user_id, nature_id, alt_identifier, admission_date, answer_type_bitmask, other_answer_desc, process_limit_date, process_notes, closing_date, alarm1_date, alarm2_date, flag_notif, flag_alarm1, flag_alarm2) VALUES (2, 'incoming', NULL, 'bbain', NULL, NULL, 'simple_mail', 'PJS/3/24/14092009/1/1', '2009-09-14 00:00:00', '000000', '[PrÈciser]', '2009-09-24 00:00:00', '', NULL, NULL, NULL, 'N', 'N', 'N');
INSERT INTO mlb_coll_ext (res_id, category_id, exp_contact_id, exp_user_id, dest_contact_id, dest_user_id, nature_id, alt_identifier, admission_date, answer_type_bitmask, other_answer_desc, process_limit_date, process_notes, closing_date, alarm1_date, alarm2_date, flag_notif, flag_alarm1, flag_alarm2) VALUES (3, 'incoming', NULL, 'rrizzo', NULL, NULL, 'simple_mail', 'DRH/3/38/14092009/3/1', '2009-09-14 00:00:00', NULL, NULL, '2009-09-24 00:00:00', NULL, NULL, NULL, NULL, 'N', 'N', 'N');

INSERT INTO listinstance (coll_id, res_id, listinstance_type, "sequence", item_id, item_type, item_mode, added_by_user, added_by_entity) VALUES ('letterbox_coll', 1, 'DOC', 0, 'bbain', 'user_id', 'dest', 'bbain', 'PJS');
INSERT INTO listinstance (coll_id, res_id, listinstance_type, "sequence", item_id, item_type, item_mode, added_by_user, added_by_entity) VALUES ('letterbox_coll', 2, 'DOC', 0, 'bbain', 'user_id', 'dest', 'bbain', 'PJS');
INSERT INTO listinstance (coll_id, res_id, listinstance_type, "sequence", item_id, item_type, item_mode, added_by_user, added_by_entity) VALUES ('letterbox_coll', 3, 'DOC', 0, 'ttong', 'user_id', 'dest', 'bblier', 'COU');


-- PHYSICAL ARCHIVING
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (1, 'Boite ENTRANT 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 15:59:34.436', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (2, 'Boite ENTRANT 002', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 15:59:54.176', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (3, 'Boite SORTANT 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 16:00:07.569', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (4, 'Boite INTERNE 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 16:00:29.896', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO ar_boxes (arbox_id, title, subject, description, entity_id, arcontainer_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (5, 'Boite PROJET 001', NULL, NULL, NULL, 0, 'NEW', '2009-09-16 16:01:00.765', NULL, NULL, NULL, NULL, NULL, 'PA', NULL, NULL, NULL, 'superadmin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO ar_batch (arbatch_id, title, subject, description, arbox_id, status, creation_date, retention_time, custom_t1, custom_n1, custom_f1, custom_d1, custom_t2, custom_n2, custom_f2, custom_d2, custom_t3, custom_n3, custom_f3, custom_d3, custom_t4, custom_n4, custom_f4, custom_d4, custom_t5, custom_n5, custom_f5, custom_d5, custom_t6, custom_t7, custom_t8, custom_t9, custom_t10, custom_t11) VALUES (1, '1', NULL, NULL, 1, 'NEW', '2009-09-16 18:26:27.979', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bblier', NULL, NULL, NULL, NULL, NULL, NULL, '2009-09-16 18:26:27.979', NULL, NULL, NULL, NULL, 'LETTERBOX', NULL, NULL, NULL, NULL, NULL);

INSERT INTO ar_container_types (ctype_id, ctype_desc, size_x, size_y, size_z) VALUES
('CAF20', 'Conteneur aere ferme 20 pieds', 6, 2.32, 2.37),
('CAF40', 'Conteneur aere ferme 40 pieds', 12, 2.32, 2.37),
('CPF20', 'Conteneur plate-forme 20 pieds', 6, 2.32, 2.37),
('SCPF40', 'Super conteneur plate-forme 20 pieds', 6, 2.32, 2.37),
('CTH40', 'Conteneur a caracteristiques thermiques 40 pieds', 12, 2.32, 2.37),
('CTH20', 'Conteneur a caracteristiques thermiques 20 pieds', 6, 2.32, 2.37);

INSERT INTO ar_sites (site_id, site_desc, entity_id) VALUES
('FR01', 'Site de Paris', 'ARC'),
('FR02', 'Site de Nanterre', 'ARC');

INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSPROJ', 'Dossiers de projet DGS', 10, 'COARCDGS', 'Y');
INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSTECH', 'Dossiers techniques DGS', 10, 'COARCDGS', 'Y');
INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSRH', 'Dossiers RH DGA', 30, 'COARCDGA', 'Y');
INSERT INTO ar_natures (arnature_id, arnature_desc, arnature_retention, entity_id, enabled) VALUES ('DOSCOMPTA', 'Dossiers comptables DGA', 10, 'COARCDGA', 'Y');

INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (1, 'FR01', 'A', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (2, 'FR01', 'A', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (3, 'FR01', 'A', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (4, 'FR01', 'A', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (5, 'FR01', 'A', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (6, 'FR01', 'A', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (7, 'FR01', 'A', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (8, 'FR01', 'A', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (9, 'FR01', 'A', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (10, 'FR01', 'A', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (11, 'FR01', 'A', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (12, 'FR01', 'A', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (13, 'FR01', 'A', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (14, 'FR01', 'A', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (15, 'FR01', 'A', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (16, 'FR01', 'A', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (17, 'FR01', 'A', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (18, 'FR01', 'A', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (19, 'FR01', 'A', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (20, 'FR01', 'A', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (21, 'FR01', 'A', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (22, 'FR01', 'A', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (23, 'FR01', 'A', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (24, 'FR01', 'A', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (25, 'FR01', 'A', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (26, 'FR01', 'A', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (27, 'FR01', 'A', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (28, 'FR01', 'A', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (29, 'FR01', 'A', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (30, 'FR01', 'A', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (31, 'FR01', 'B', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (32, 'FR01', 'B', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (33, 'FR01', 'B', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (34, 'FR01', 'B', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (35, 'FR01', 'B', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (36, 'FR01', 'B', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (37, 'FR01', 'B', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (38, 'FR01', 'B', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (39, 'FR01', 'B', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (40, 'FR01', 'B', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (41, 'FR01', 'B', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (42, 'FR01', 'B', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (43, 'FR01', 'B', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (44, 'FR01', 'B', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (45, 'FR01', 'B', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (46, 'FR01', 'B', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (47, 'FR01', 'B', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (48, 'FR01', 'B', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (49, 'FR01', 'B', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (50, 'FR01', 'B', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (51, 'FR01', 'B', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (52, 'FR01', 'B', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (53, 'FR01', 'B', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (54, 'FR01', 'B', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (55, 'FR01', 'B', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (56, 'FR01', 'B', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (57, 'FR01', 'B', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (58, 'FR01', 'B', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (59, 'FR01', 'B', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (60, 'FR01', 'B', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (61, 'FR01', 'C', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (62, 'FR01', 'C', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (63, 'FR01', 'C', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (64, 'FR01', 'C', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (65, 'FR01', 'C', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (66, 'FR01', 'C', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (67, 'FR01', 'C', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (68, 'FR01', 'C', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (69, 'FR01', 'C', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (70, 'FR01', 'C', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (71, 'FR01', 'C', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (72, 'FR01', 'C', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (73, 'FR01', 'C', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (74, 'FR01', 'C', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (75, 'FR01', 'C', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (76, 'FR01', 'C', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (77, 'FR01', 'C', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (78, 'FR01', 'C', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (79, 'FR01', 'C', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (80, 'FR01', 'C', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (81, 'FR01', 'C', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (82, 'FR01', 'C', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (83, 'FR01', 'C', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (84, 'FR01', 'C', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (85, 'FR01', 'C', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (86, 'FR01', 'C', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (87, 'FR01', 'C', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (88, 'FR01', 'C', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (89, 'FR01', 'C', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (90, 'FR01', 'C', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (91, 'FR01', 'D', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (92, 'FR01', 'D', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (93, 'FR01', 'D', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (94, 'FR01', 'D', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (95, 'FR01', 'D', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (96, 'FR01', 'D', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (97, 'FR01', 'D', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (98, 'FR01', 'D', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (99, 'FR01', 'D', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (100, 'FR01', 'D', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (101, 'FR01', 'D', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (102, 'FR01', 'D', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (103, 'FR01', 'D', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (104, 'FR01', 'D', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (105, 'FR01', 'D', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (106, 'FR01', 'D', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (107, 'FR01', 'D', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (108, 'FR01', 'D', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (109, 'FR01', 'D', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (110, 'FR01', 'D', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (111, 'FR01', 'D', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (112, 'FR01', 'D', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (113, 'FR01', 'D', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (114, 'FR01', 'D', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (115, 'FR01', 'D', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (116, 'FR01', 'D', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (117, 'FR01', 'D', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (118, 'FR01', 'D', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (119, 'FR01', 'D', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (120, 'FR01', 'D', 10, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (121, 'FR01', 'E', 1, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (122, 'FR01', 'E', 1, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (123, 'FR01', 'E', 1, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (124, 'FR01', 'E', 2, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (125, 'FR01', 'E', 2, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (126, 'FR01', 'E', 2, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (127, 'FR01', 'E', 3, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (128, 'FR01', 'E', 3, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (129, 'FR01', 'E', 3, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (130, 'FR01', 'E', 4, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (131, 'FR01', 'E', 4, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (132, 'FR01', 'E', 4, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (133, 'FR01', 'E', 5, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (134, 'FR01', 'E', 5, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (135, 'FR01', 'E', 5, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (136, 'FR01', 'E', 6, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (137, 'FR01', 'E', 6, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (138, 'FR01', 'E', 6, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (139, 'FR01', 'E', 7, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (140, 'FR01', 'E', 7, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (141, 'FR01', 'E', 7, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (142, 'FR01', 'E', 8, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (143, 'FR01', 'E', 8, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (144, 'FR01', 'E', 8, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (145, 'FR01', 'E', 9, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (146, 'FR01', 'E', 9, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (147, 'FR01', 'E', 9, 3, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (148, 'FR01', 'E', 10, 1, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (149, 'FR01', 'E', 10, 2, 4, 4);
INSERT INTO ar_positions (position_id, site_id, pos_row, pos_col, pos_level, pos_max_uc, pos_available_uc) VALUES (150, 'FR01', 'E', 10, 3, 4, 4);



