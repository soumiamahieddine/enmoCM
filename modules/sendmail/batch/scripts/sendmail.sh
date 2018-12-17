#!/bin/sh
cd /var/www/html/maarch_courrier_develop/modules/sendmail/batch/
emailsPath='/var/www/html/maarch_courrier_develop/modules/sendmail/batch/process_emails.php'
php $emailsPath -c /var/www/html/maarch_courrier_develop/modules/sendmail/batch/config/config.xml

