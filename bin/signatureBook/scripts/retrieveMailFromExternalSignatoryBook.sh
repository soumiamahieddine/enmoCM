#!/bin/sh
cd /var/www/html/MaarchCourrier2103/bin/signatureBook/
filePath='/var/www/html/MaarchCourrier2103/bin/signatureBook/process_mailsFromSignatoryBook.php'
php $filePath -c /var/www/html/MaarchCourrier2103/apps/maarch_entreprise/xml/config.json
