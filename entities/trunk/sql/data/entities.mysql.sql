INSERT INTO `entities` VALUES ('ARCHIVECO', 'Archiveco SARL', 'Y', '1 rue du Bois', '', '', '44630', 'Villedieu la Blouère', 'France', 'archiveco@wanadoo.fr', '9876 5432 4321', 'ECONOMBOX', 'PARTENAIRE');
INSERT INTO `entities` VALUES ('ECONOMBOX', 'EconomBox SAS', 'Y', '65 rue de la Croix', '', '', '92000', 'Nanterre', 'France', 'info@econombox.com', '1234 5678 9012', '', 'ECONOMBOX');
INSERT INTO `entities` VALUES ('WARNER', 'Warner Music France', 'Y', '29 Avenue Mac Mahon', '', '', '75017', 'Paris 17', 'France', 'arnaud.duval@warnermusic.fr', '712029370 00129', 'ARCHIVECO', 'CLIENT');
INSERT INTO `entities` VALUES ('LAFARGE', 'Lafarge Ciments', 'Y', 'BP 302', '', '', '92214', 'ST CLOUD CEDEX', 'France', 'caroline.caumes@lafarge-ciments.fr', '302135561 00421', 'ARCHIVECO', 'CLIENT');
INSERT INTO `entities` VALUES ('DAKARCHIV', 'Dakarchiv SA', 'Y', '49 Machallah VDN', 'BP 430', '', '', 'Dakar', 'Senegal', 'mamadou.diallo@dakarchiv.sn', '8765432543 5433', 'ECONOMBOX', 'PARTENAIRE');
INSERT INTO `entities` VALUES ('APIX', 'APIX SA', 'Y', '52-54 rue Mohammed V', 'BP 430', '', '18524', 'Dakar', 'Senegal', 'moussdiallo@apix.sn', '5432345 454 98765', 'DAKARCHIV', 'CLIENT');

INSERT INTO `users_entities` VALUES ('pparker', 'ECONOMBOX', 'Manager EconomBox', 'Y');
INSERT INTO `users_entities` VALUES ('ccharles', 'ARCHIVECO', 'Responsable Archiveco', 'Y');
INSERT INTO `users_entities` VALUES ('bsaporta', 'DAKARCHIV', 'Responsable Dakarchiv', 'Y');

INSERT INTO actions (id, keyword, label_action, id_status, is_system, enabled, action_page, history, origin) VALUES
(1,'redirect', 'Redirection', 'NONE', 'Y', 'N', 'redirect', 'Y', 'entities');
