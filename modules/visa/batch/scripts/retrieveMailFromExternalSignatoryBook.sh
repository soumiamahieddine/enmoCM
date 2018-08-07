#!/bin/sh
cd /var/www/html/maarch_v2/modules/visa/batch/
filePath='/var/www/html/maarch_v2/modules/visa/batch/process_mailsFromSignatoryBook.php'
php $filePath -c /var/www/html/maarch_v2/modules/visa/batch/config/config.xml
