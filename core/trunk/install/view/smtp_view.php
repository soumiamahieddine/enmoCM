<?php
/*
*   Copyright 2008-2012 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief class of install tools
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup install
*/
?>
<!--script type="text/javascript" src="js/prototype.js"></script-->
<script>
   
    function envoiMailTestSmtp(url,smtpHost,smtpType,smtpPort,smtpUser,smtpPassword,smtpAuth,smtpMailTo){
        //alert(url);

        $(document).ready(function() {
            var oneIsEmpty = false;
            if (smtpHost.length < 1) {
                var oneIsEmpty = true;
            }
            if (smtpType.length < 1) {
                var oneIsEmpty = true;
            }
            if (smtpPort.length < 1) {
                var oneIsEmpty = true;
            }
            if (smtpUser.length < 1) {
                var oneIsEmpty = true;
            }
            if (smtpPassword.length < 1) {
                var oneIsEmpty = true;
            }
            if (smtpAuth.length < 1) {
                var oneIsEmpty = true;
            }
            if (smtpMailTo.length < 1) {
                var oneIsEmpty = true;
            }

            if (oneIsEmpty) {
                $('#ajaxReturn_testConnect_ko').html('<?php echo _ONE_FIELD_EMPTY; ?>');
                return;
             }
             $('.wait').css('display','block');
             $('#ajaxReturn_testConnect_ko').html('');

            ajaxDB(
                'testSmtp',
                  'smtpHost|'+smtpHost
                  +'|smtpType|'+smtpType
                  +'|smtpPort|'+smtpPort
                  +'|smtpUser|'+smtpUser
                  +'|smtpPassword|'+smtpPassword
                  +'|smtpAuth|'+smtpAuth
                  +'|smtpMailTo|'+smtpMailTo,
                'ajaxReturn_testConnect',
                'false'
            );

        });
 

    }


</script>
<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('configNotificationSendmail');" style="cursor: pointer;">
            <?php echo _SMTP_INFO; ?>
        </h2>
    </div>
    <div class="contentBlock" id="docservers">
        <p>
            <h6>
                <?php echo _SMTP_EXP; ?>
            </h6>
            <form>
                <table>
                    <tr>
                        <td>
                            <?php echo _SMTP_HOST; ?>
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            <input type="text" name="smtpHost" id="smtpHost" value="smtp.yourdomain.com"/>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _SMTP_TYPE; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpType" id="smtpType" value="smtp"/></td>
                    </tr>
                    <tr>
                        <td><?php echo _SMTP_PORT; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpPort" id="smtpPort" value="25"/></td>
                    </tr>
                    <tr>
                        <td><?php echo _SMTP_USER; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpUser" id="smtpUser" value="notif@yourdomain.com"/></td>
                    </tr>
                    <tr>
                        <td><?php echo _SMTP_PASSWORD; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpPassword" id="smtpPassword" value="password"/></td>
                    </tr>
                    <!--tr>
                        <td><?php echo _SMTP_AUTH; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpAuth" id="smtpAuth" value="true or false"/></td>
                    </tr-->
                    <tr>
                        <td><?php echo _SMTP_AUTH; ?></td>
                        <td>:</td>
                        <td>
                            <SELECT name="smtpAuth" id="smtpAuth">
                                <OPTION selected>true
                                <OPTION>false
                            </SELECT>
                        </td>
                    </tr>
                    <!--tr>
                        <td><?php echo _SMTP_CHARSET; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpCharset" id="smtpCharset" value="utf-8"/></td>
                    </tr-->
                    <!--tr>
                        <td><?php echo _SMTP_SECURE; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpSecure" id="smtpSecure" value="tls or ssl"/></td>
                    </tr-->
                    <!--tr>
                        <td><?php echo _SMTP_MAILFROM; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpMailFrom" id="smtpMailFrom" value="notifications@maarch.fr"/></td>
                    </tr-->
                    <tr>
                        <td><?php echo _SMTP_MAILTO; ?></td>
                        <td>:</td>
                        <td><input type="text" name="smtpMailTo" id="smtpMailTo" value="votreAdresse@domain.com"/></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            <input type="button" id="ajaxReturn_testConnect_button" onClick="envoiMailTestSmtp( 'testSmtp',
                                                                                                                $('#smtpHost').val(),
                                                                                                                $('#smtpType').val(),
                                                                                                                $('#smtpPort').val(),
                                                                                                                $('#smtpUser').val(),
                                                                                                                $('#smtpPassword').val(),
                                                                                                                $('#smtpAuth').val(),
                                                                                                                $('#smtpMailTo').val())"; value="<?php echo _VERIF_SMTP; ?>"/>
                        </td>
                    </tr>
                </table>
            </form>
            <br />
            <div id="ajaxReturn_testConnect_ko"></div>
        </p>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock" id="docservers">
        <p>
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=docservers');" style="display:none;">
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <a href="#" onClick="goTo('index.php?step=password');" id="ajaxReturn_testConnect" style=" display: none;">
                        <?php echo _NEXT; ?>
                    </a>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>

