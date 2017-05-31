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
                                        <option value="default">Select a version</option>
                                        <?php
                                        foreach ($tags as $key => $value) {
                                            echo $tags[$key]['name'] . '<br />';
                                            echo '<option ';
                                            echo 'value="' . $tags[$key]['name'] . '"';
                                            echo '>';
                                                echo $tags[$key]['name'];
                                            echo '</option>';
                                        }
                                        ?>
                                    </select>
                                    <?php
                                } else {
                                    ?>
                                    No version available for update
                                    <?php
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
