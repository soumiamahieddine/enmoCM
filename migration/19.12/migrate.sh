#!/bin/sh
php ./migrateCustomFields.php
php ./migrateFileplans.php
php ./migrateFolders.php
php ./migrateFullText.php
php ./migrateRedirectKeywords.php
php ./migrateIndexing.php
php ./migrateServicesEntities.php
php ./migrateMenuEntities.php
php ./exportCases.php
php ./exportFileplans.php
php ./exportFolders.php
php ./removeProcessModes.php
php ./migrateCategories.php
php ./migrateOldIndexingModels.php
