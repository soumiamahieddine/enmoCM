#!/bin/sh
php ./exportCustomFields.php
php ./exportCases.php
php ./exportFileplans.php
php ./exportFolders.php
php ./migrateCategories.php
php ./migrateCustomFields.php
php ./migrateFileplans.php
php ./migrateFolders.php
php ./migrateFullText.php
php ./migrateRedirectKeywords.php
php ./migrateIndexing.php
php ./migrateListTemplates.php
php ./migrateServicesEntities.php
php ./migrateMenuEntities.php
php ./removeProcessModes.php
php ./migrateOldIndexingModels.php
php ./migrateWorkingDays.php
php ./migrateExtensions.php
php ./migrateM2MConfiguration.php
php ./removeNatureFromPrint.php
php ./migrateCustomValues.php
php ./migrateVersionAttachments.php
php ./migrateContacts.php
# migrateOutgoingTemplate always before migrateTemplates
php ./migrateOutgoingTemplate.php
php ./migrateTemplates.php
php ./migrateLinkedResources.php
