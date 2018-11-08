#!/bin/bash
eventStackPath='/var/www/html/maarchdev/modules/notifications/batch/process_event_stack.php'
cd /var/www/html/maarchdev/modules/notifications/batch/
#php $eventStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/notifications/batch/config/config.xml -n NCT
#php $eventStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/notifications/batch/config/config.xml -n NCC
php $eventStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/notifications/batch/config/config.xml -n ANC
php $eventStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/notifications/batch/config/config.xml -n AND
php $eventStackPath -c /var/www/html/maarchdev/custom/cs_maarchdev/modules/notifications/batch/config/config.xml -n RED