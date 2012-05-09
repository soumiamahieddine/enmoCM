<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('database');" style="cursor: pointer;">
            <?php echo _DATABASE; ?>
        </h2>
    </div>
    <div class="contentBlock" id="database">
        <p>
            <form>
                <table>
                    <tr>
                        <td colspan="3" id="returnCheckDatabaseInfo">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
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
                    <tr>
                        <td>
                            <?php echo _DATABASENAME; ?>
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            <input type="text" id="databasename" name="databasename"/>
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
                                checkDatabaseInfo(
                                  $('#databaseserver').val(),
                                  $('#databaseserverport').val(),
                                  $('#databaseuser').val(),
                                  $('#databasepassword').val(),
                                  $('#databasename').val(),
                                  $('#databasetype').val()
                                );
                              "
                            />
                        </td>
                    </tr>
                </table>
            </form>
            <br />
            <br />
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=prerequisites');">
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <a href="#" onClick="goTo('index.php?step=docservers');">
                        <?php echo _NEXT; ?>
                    </a>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>
