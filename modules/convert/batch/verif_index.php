<?php

date_default_timezone_set('Europe/Paris');

// load the config and prepare to process
include('load_fill_stack.php');


/******************************************************************************/
$GLOBALS['zendIndex'] =
        $GLOBALS['processIndexes']->createZendIndexObject(
                $GLOBALS['path_to_lucene'], $GLOBALS['ProcessIndexesSize']
        );
unlink($GLOBALS['lckFile']);
exit($GLOBALS['zendIndex']->numDocs());

