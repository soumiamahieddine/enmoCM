<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/
/**
* @brief Maarch version test
*
* @file
* @author dev@maarch.org
* @date $date$
* @version $Revision$
* @ingroup admin
*/

core_tools::load_lang();
$core_tools = new core_tools();
//$core_tools->test_admin('admin_update_control', 'apps');

$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}

$pagePath = $_SESSION['config']['businessappurl'] . 'index.php?page='
               . 'update_control&admin=update_control';
$pageLabel = _ADMIN_UPDATE_CONTROL;
$pageId = 'update_control';
$level = '';
if (isset($_REQUEST['level'])
    && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3
        || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)) {
    $level = $_REQUEST['level'];
}
$core_tools->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);

//retrives tags
$client = new \Gitlab\Client('https://labs.maarch.org/api/v4/');
//$client->authenticate('aSecretToken', \Gitlab\Client::AUTH_URL_TOKEN);

// $project = $client->api('projects')->show('12');
// var_dump($project);

$tags = $client->api('tags')->all('12');
//var_dump($tags);

//retrieve current version
$db = new Database();
$query = "select param_value_int, param_value_string from parameters where id = 'database_version'";
$stmt = $db->query($query, []);
$currentVersion = $stmt->fetchObject();
// var_dump($currentVersion);
$currentVersionNumeric = preg_replace("/[^0-9,]/", "", $currentVersion->param_value_int);
if (!empty($currentVersion->param_value_string)) {
    $currentVersionTagNumeric = preg_replace("/[^0-9,]/", "", $currentVersion->param_value_string);
}

$allTagsNumeric = [];
$allCurrentTags = [];
$allNextTags = [];
$cptCurrentTags = 0;
$isAnyAvailableTag = false;
$isAnyAvailableVersion = false;

foreach ($tags as $key => $value) {
    //echo $tags[$key]['name'] . ' ' . preg_replace("/[^0-9,]/", "", $tags[$key]['name']) . '<br />';
    $tagNumeric = preg_replace("/[^0-9,]/", "", $tags[$key]['name']);
    $allTagsNumeric[] = $tagNumeric;
    $pos = strpos($tagNumeric, $currentVersionNumeric);
    if ($pos === false) {
        //echo 'tag not in currentVersion:';
        $isAnyAvailableVersion = true;
        $allNextTags[] = $tags[$key]['name'];
    } else {
        //echo 'tag in currentVersion:';
        $allCurrentTags[$cptCurrentTags] = [];
        $allCurrentTags[$cptCurrentTags]['name'] = $tags[$key]['name'];
        $allCurrentTags[$cptCurrentTags]['numeric'] = $tagNumeric;
        if ($tagNumeric > $currentVersionTagNumeric) {
            $allCurrentTags[$cptCurrentTags]['enabled'] = true;
            $isAnyAvailableTag = true;
        } else {
            $allCurrentTags[$cptCurrentTags]['enabled'] = false;
        }
        $cptCurrentTags++;
    }
    //echo $tagNumeric . '<br />';
}
//var_dump($allCurrentTags);
?>
<h1><?php echo _ADMIN_UPDATE_CONTROL;?></h1>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<table align="center">
    <tr>
        <td>
            <?php echo _YOUR_VERSION;?>
        </td>
        <td>
            :
        </td>
        <td>
            <?php echo _BRANCH_VERSION . ':' . $currentVersion->param_value_int 
                . ' ' . _TAG_VERSION . ':' . $currentVersion->param_value_string;?>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>
            <?php echo _AVAILABLE_VERSION_TO_UPDATE;?>
        </td>
        <td>
            :
        </td>
        <td>
            <?php
            if (count($tags)>0) {
                ?>
                <select id="version" id="name">
                    <!--option value="default"><?php echo _SELECT_A_VERSION;?></option-->
                    <?php
                    for ($i=0;$i<count($allCurrentTags);$i++) {
                        if ($allCurrentTags[$i]['enabled']) {
                            echo '<option ';
                            echo 'value="' . $allCurrentTags[$i]['name'] . '"';
                            echo '>';
                                echo $allCurrentTags[$i]['name'];
                            echo '</option>';
                        } else {
                            echo '<option ';
                            echo 'value="' . $allCurrentTags[$i]['name'] . '"';
                            echo ' disabled>';
                                echo $allCurrentTags[$i]['name'];
                            echo '</option>';
                        }
                    }
                    ?>
                </select>
                <?php
                if ($_SESSION['user']['UserId'] == 'superadmin') {
                    ?>
                    <a href="<?php echo $_SESSION['config']['coreurl'];?>install/index.php?step=update_welcome"><?php echo ' ' . _CLICK_HERE_TO_GO_TO_UPDATE_MANAGEMENT;?></a>
                    <?php
                } else {
                    echo _CONNECT_YOU_IN_SUPERADMIN;
                }
            } else {
                echo _NO_AVAILABLE_TAG_TO_UPDATE . '<br />';
            }
            ?>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>
            <?php
            if (!$isAnyAvailableTag) {
                echo _NO_AVAILABLE_TAG_TO_UPDATE . '<br />';
            }?>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>
            <?php
            if ($isAnyAvailableVersion) {
                echo '<b>' . _NEW_MAJOR_VERSION_AVAILABLE . '</b>:';
                for ($j=0;$j<count($allNextTags);$j++) {
                    echo $allNextTags[$j] . '<br />';
                }
            }?>
        </td>
    </tr>
</table>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>