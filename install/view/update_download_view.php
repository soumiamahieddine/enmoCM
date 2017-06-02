<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief class of install tools
*
* @file
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup install
*/
if ($_SESSION['user']['UserId'] <> 'superadmin') {
    header('location: ' . $_SESSION['config']['businessappurl']
        . 'index.php?page=update_control&admin=update_control');
    exit();
}

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

?>
<script>
    function launchProcess(
        myVar
    )
    {
        $(document).ready(function() {
            var oneIsEmpty = false;
            if (myVar.length < 1) {
                var oneIsEmpty = true;
            }

            if (oneIsEmpty) {
                $('#ajaxReturn_ko').html('<?php echo _MUST_CHOOSE_VERSION;?>');
                $('#ajaxReturn_button').css('display', 'block');
                return;
            }
            $('#ajaxReturn_ko').html('');

            ajax(
                'downloadVersion',
                'myVar|'+myVar,
                'ajaxReturn',
                'false'
            );

        });
    }
</script>
<div class="ajaxReturn_testConnect">
    <div class="blockWrapper">
        <div class="titleBlock">
            <h2 onClick="slide('database');" style="cursor: pointer;">
                <?php echo _LAST_RELEASE_INFOS;?>
            </h2>
        </div>
        <div class="contentBlock" id="database">
            <p>
                <h6>
                    <?php echo _LAST_RELEASE_DETAILS;?>
                </h6>
                <form>
                    <table>
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
                            <td>
                                <?php echo _CHOOSE_VERSION_TO_UPDATE;?>
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
                                } else {
                                    echo _NO_AVAILABLE_TAG_TO_UPDATE . '<br />';
                                }
                                ?>
                                
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <input
                                  type="button"
                                  name="Submit" id="ajaxReturn_button"  value="<?php echo _DOWNLOAD_VERSION;?>"
                                  onClick="$(this).css('display', 'none');launchProcess($('#version').val());"
                                />
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
                </form>
                <br />
                <div id="ajaxReturn_ko"></div>
                <div align="center">
                    <img src="img/wait.gif" width="100" class="wait" style="display: none; background-color: rgba(0, 0, 0, 0.2);"/>
                </div>
            </p>
        </div>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock">
        <p>
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=update_backup');">
                        <?php echo _PREVIOUS_INSTALL;?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <a href="#" onClick="goTo('index.php?step=update_deploy');" class="ajaxReturn" id="ajaxReturn_ok" style="display: none;">
                        <?php echo _NEXT_INSTALL;?>
                    </a>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>
