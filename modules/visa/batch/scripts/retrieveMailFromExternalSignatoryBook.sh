#!/bin/sh
cd /var/www/html/maarchdev/modules/visa/batch/
filePath='/var/www/html/maarchdev/modules/visa/batch/process_mailsFromSignatoryBook.php'
php $filePath -c /var/www/html/maarchdev/modules/visa/batch/config/config.xml
