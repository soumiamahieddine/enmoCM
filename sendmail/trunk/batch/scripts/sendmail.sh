#!/bin/bash
cd /var/www/html/maarch_trunk/modules/sendmail/batch/
emailStackPath='/var/www/html/maarch_trunk/modules/sendmail/batch/process_emails.php'
php $emailStackPath -c /var/www/html/maarch_trunk/modules/sendmail/batch/config/config.xml