INSERT INTO baskets (coll_id, basket_id, basket_name, basket_desc, basket_clause, is_generic, enabled) VALUES
('coll_2', 'NewInvoicesSup10000', 'Nouvelles Factures > 10.000 euros - New incoming invoices > 10.000 euros', 'Nouvelles Factures > 10.000 euros - New incoming invoices > 10.000 euros', 'status =''NEW'' and doc_custom_n1 > 10000', 'NO', 'Y'),
('coll_2', 'RejectedInvoices', 'Factures rejetees - Rejected invoices', 'Factures Rejetees - Rejected invoices', 'status = ''REJ''', 'NO', 'Y'),
('coll_2', 'ValidatedInvoices', 'Factures validees - Approved invoices', 'Factures validees - Approved invoices', 'status = ''END''', 'NO', 'Y');

INSERT INTO groupbasket VALUES ('Accountants', 'NewInvoicesSup10000', 4, '', '', 'Y', 'N', 'N', 'invoices_list');
INSERT INTO groupbasket VALUES ('Accountants', 'RejectedInvoices', 5, '', '', 'Y', 'N', 'N', 'invoices_list');
INSERT INTO groupbasket VALUES ('Accountants', 'ValidatedInvoices', 6, '', '', 'Y', 'N', 'N', 'invoices_list');

INSERT INTO `actions_groupbaskets` VALUES(6, '', 'Accountants', 'NewInvoicesSup10000', 'N', 'N', 'Y');
INSERT INTO `actions_groupbaskets` VALUES(4, '', 'Accountants', 'NewInvoicesSup10000', 'Y', 'Y', 'N');
INSERT INTO `actions_groupbaskets` VALUES(5, '', 'Accountants', 'NewInvoicesSup10000', 'Y', 'Y', 'N');