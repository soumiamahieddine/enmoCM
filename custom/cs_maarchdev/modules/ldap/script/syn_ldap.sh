#!/bin/bash
cd /var/www/html/maarchdev/modules/ldap/script/

#generation des fichiers xml
php /var/www/html/maarchdev/modules/ldap/process_ldap_to_xml.php /var/www/html/maarchdev/custom/cs_maarchdev/modules/ldap/xml/config.xml

#mise a jour bdd
php /var/www/html/maarchdev/modules/ldap/process_entities_to_maarch.php /var/www/html/maarchdev/custom/cs_maarchdev/modules/ldap/xml/config.xml
php /var/www/html/maarchdev/modules/ldap/process_users_to_maarch.php /var/www/html/maarchdev/custom/cs_maarchdev/modules/ldap/xml/config.xml
php /var/www/html/maarchdev/modules/ldap/process_users_entities_to_maarch.php /var/www/html/maarchdev/custom/cs_maarchdev/modules/ldap/xml/config.xml