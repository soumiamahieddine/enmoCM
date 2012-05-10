<div class="ajaxReturn_testConnect">
    <div class="blockWrapper">
        <div class="titleBlock">
            <h2 onClick="slide('database');" style="cursor: pointer;">
                <!-- <?php echo _DATABASE; ?>--> Informations de connexion
            </h2>
        </div>
        <div class="contentBlock" id="database">
            <p>
                <form>
                    <table>
                        <tr>
                            <td>
                                <?php echo _DATABASESERVER; ?>
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <input type="text" id="databaseserver" name="databaseserver"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo _DATABASESERVERPORT; ?>
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <input type="text" id="databaseserverport" name="databaseserverport"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo _DATABASEUSER; ?>
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <input type="text" id="databaseuser" name="databaseuser"/>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo _DATABASEPASSWORD; ?>
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <input type="password" id="databasepassword" name="databasepassword"/>
                            </td>
                        </tr>
                        <tr style="display: none;">
                            <td>
                                <?php echo _DATABASETYPE; ?>
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <input type="hidden" id="databasetype" name="databasetype" value="POSTGRESQL"/>
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
                                  value="tester"
                                  onClick="
                                    $('.wait').css('display','block');
                                    checkDatabaseInfo(
                                      $('#databaseserver').val(),
                                      $('#databaseserverport').val(),
                                      $('#databaseuser').val(),
                                      $('#databasepassword').val(),
                                      $('#databasetype').val(),
                                      'testConnect'
                                    );
                                  "
                                />
                            </td>
                        </tr>
                    </table>
                </form>
                <br />
                <div id="ajaxReturn_testConnect_ko"></div>
                <div align="center">
                    <img src="img/wait.gif" width="100" class="wait" style="display: none; background-color: rgba(0, 0, 0, 0.2);"/>
                </div>
            </p>
        </div>
    </div>
</div>
<div class="ajaxReturn_createDB">
    <div class="blockWrapper" id="ajaxReturn_testConnect" style="display: none;">
        <div class="titleBlock">
            <h2 onClick="slide('createdatabase');" style="cursor: pointer;">
                <!--<?php echo _DATABASE; ?>-->Création de la base de données
            </h2>
        </div>
        <div class="contentBlock" id="createdatabase">
            <p>
                <div id="ajaxReturn_testConnect_ok"></div>
                <form>
                    <table>
                        <tr>
                            <td>
                                <?php echo _DATABASENAME; ?>
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <input type="text" name="databasename" id="databasename" />
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
                                <input type="button" onclick="$('.wait').css('display','block');checkCreateDB($('#databasename').val(), 'createdatabase');" value="Créer la base" />
                            </td>
                        </tr>
                    </table>
                </form>
                <br />
                <div id="ajaxReturn_createDB_ko"></div>
                <div align="center">
                    <img src="img/wait.gif" width="100" class="wait" style="display: none; background-color: rgba(0, 0, 0, 0.2);"/>
                </div>
            </p>
        </div>
    </div>
</div>
<div class="ajaxReturn_loadDatas">
    <div class="blockWrapper" id="ajaxReturn_createDB" style="display: none;">
        <div class="titleBlock">
            <h2 onClick="slide('database');" style="cursor: pointer;">
                <!--<?php echo _DATABASE; ?>-->Choix des datas
            </h2>
        </div>
        <div class="contentBlock">
            <p>
                <div id="ajaxReturn_createDB_ok"></div>
                <form>
                    <table>
                        <tr>
                            <td>
                                Datas
                            </td>
                            <td>
                                :
                            </td>
                            <td>
                                <select onChange="checkDataDB($(this).val());" id="dataFilename">
                                    <option value="default"><?php echo _CHOOSE; ?></option>
                                    <?php
                                        for($i=0; $i<count($listSql);$i++) {
                                            echo '<option ';
                                              echo 'value="'.$listSql[$i].'"';
                                            echo '>';
                                                echo $listSql[$i];
                                            echo '</option>';
                                        }
                                    ?>
                                </select>
                            </td>
                            <td id="returnCheckDataClassic" style="display: none;">
                                ORIENTÉ ARCHIVAGE
                            </td>
                            <td id="returnCheckDataMlb" style="display: none;">
                                ORIENTÉ COURRIER
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="button" onclick="$('.wait').css('display','block');checkLoadDatas($('#dataFilename').val(), 'loadDatas');" value="Charger les datas" />
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </form>
                <br />
                <div id="ajaxReturn_loadDatas_ko"></div>
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
                    <a href="#" onClick="goTo('index.php?step=prerequisites');">
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <a href="#" onClick="goTo('index.php?step=docservers');" id="ajaxReturn_loadDatas" style=" display: none;">
                        <?php echo _NEXT; ?>
                    </a>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>
