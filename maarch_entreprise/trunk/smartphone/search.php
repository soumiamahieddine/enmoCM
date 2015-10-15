<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}

$_SESSION['nombreDeLignesTotale'] = null;

require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
$_SESSION['collection_id_choice'] = '';
?>
<div id="search" title="<?php echo _SEARCH;?>" class="panel">
    <form id="search" title="SearchRes" class="panel" action="search_result.php" method="POST" selected="true">
        <fieldset>
            <div class="row">
                <table style="width:100%;">
                    <tr>
                        <td width="50%" align="left">
                            <b><?php echo _COLLECTION;?></b>
                        </td>
                         </tr>
                          <tr>
                        <td width="50%" align="right">
                            <select name="collection" style="width:100%;padding:5px;background:white;">
                                <?php
                                for ($cptColl=0;$cptColl<count($_SESSION['collections']);$cptColl++) {
                                    if ($_SESSION['collections'][$cptColl]['script_add'] <> '') {
                                        ?>
                                        <option value="<?php echo $_SESSION['collections'][$cptColl]['id'];?>">
                                            <?php echo $_SESSION['collections'][$cptColl]['label'];?>
                                        </option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="row">
                <table style="width:100%;">
                    <tr>
                        <td width="50%" align="left">
                            <b><?php echo _FULLTEXT;?></b>
                        </td>
                         </tr>
                          <tr>
                        <td width="50%" align="right">
                            <input type="text" name="fulltext" value="" style="width:96%;padding:5px;"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%" align="left">
                            <b><?php echo _CONTACT." <div style='font-size:9px'>(destinataire / expéditeur)<div>";?></b>
                        </td>
                         </tr>
                          <tr>
                        <td width="50%" align="right">
                            <input type="text" name="contact" value="" style="width:96%;padding:5px;"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%" align="left">
                            <b><?php echo _OBJECT;?></b>
                        </td>
                         </tr>
                          <tr>
                        <td width="50%" align="right">
                            <input type="text" name="subject" value="" style="width:96%;padding:5px;"/>
                        </td>
                    </tr>
                </table>
            </div>
        </fieldset>
        <a type="submit" href="#" style="text-decoration:none"><input type="button" class="whiteButton" value="<?php echo _SEARCH;?>"/></a>
    </form>
    <div class="error">
        <?php
        if (isset($_SESSION['error'])) {
            echo $_SESSION['error'];
        }
        $_SESSION['error'] = '';
        ?>
    </div>
</div>
