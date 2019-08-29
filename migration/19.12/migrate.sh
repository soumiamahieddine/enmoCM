#!/bin/sh
php ./migrateCustomFields.php
php ./migrateFileplans.php
php ./migrateFolders.php
php ./migrateFullText.php
php ./migrateIndexing.php
php ./migrateServicesEntities.php
php ./migrateMenuEntities.php
php ./exportCases.php
