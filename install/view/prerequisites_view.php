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
<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('prerequisites');" style="cursor: pointer;">
            <?php echo _PREREQUISITES;?>
        </h2>
    </div>
    <div class="contentBlock" id="prerequisites">
        <p>
            <h6>
                <div id="titleLink"><?php echo _PREREQUISITES_EXP;?></div>
                <br/>
                <img src="img/green_light.png" width="10px"/><?php echo _ACTIVATED;?>
                <img src="img/orange_light.png"  width="10px"/><?php echo _OPTIONNAL;?>
                <img src="img/red_light.png"  width="10px"/><?php echo _NOT_ACTIVATED;?>

            </h6>
            <table>
                <tr>
                    <td colspan="2">
                        <h2>
                           <?php echo _GENERAL;?>
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpVersion()
                        );?>
                    </td>
                    <td>
                        <?php echo _PHP_VERSION . ' -> ' . PHP_VERSION;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isMaarchPathWritable()
                        );?>
                    </td>
                    <td>
                        <?php echo _MAARCH_PATH_RIGHTS;?>
                    </td>
                </tr>
                 <tr>
                    <td id="maarchDependenciesLight" class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isDependenciesExist()
                        );?>
                    </td>
                    <td id="maarchDependenciesContent" name="maarchDependenciesContent">
                    <i class="fa fa-spinner" aria-hidden="true"></i>
                        <?php if($Class_Install->isDependenciesExist()){
                        echo _MAARCH_DEPENDENCIES;
                        } else {
                        echo _MAARCH_DEPENDENCIES . '<br />'
                            . '<a style=\'color: #800000; font-family:verdana;\' href="#" onclick="downloadMaarchDependencies()">'
                            . _DEPENDENCIES_CLICK_HERE_TO_DOWNLOAD . '</a><br />'
                            . '<a style=\'color: #102155; font-family:verdana;\' href=\'http://wiki.maarch.org/Maarch_Courrier/1.5/fr/Install/Debian-Ubuntu/latest#T.C3.A9l.C3.A9chargement_et_installation_de_Maarch_Courrier_depuis_les_d.C3.A9p.C3.B4ts_GIT\' target=\"_blank\"> ' . _DEPENDENCIES_ON_WIKI  . '</a>';
                        }
                        ?>
                        <div align="center">
                            <img src="img/wait.gif" width="100" class="wait" style="display: none; background-color: rgba(0, 0, 0, 0.2);"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;

                    </td>
                    <td>&nbsp;

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h2>
                            Libraires
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'pgsql'
                            )
                        );?>
                    </td>
                    <td>
                        <?php echo _PGSQL;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'gd'
                            )
                        );?>
                    </td>
                    <td>
                        <?php echo _GD;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'imap'
                            ),
                            true
                        );?>
                    </td>
                    <td>
                        <?php echo _IMAP;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'mbstring'
                            )
                        );?>
                    </td>
                    <td>
                        <?php echo _MBSTRING;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'xsl'
                            )
                        );?>
                    </td>
                    <td>
                        <?php echo _XSL;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'gettext'
                            ), true
                        );?>
                    </td>
                    <td>
                        <?php echo _GETTEXT;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'xmlrpc'
                            ), true
                        );?>
                    </td>
                    <td>
                        <?php echo _XMLRPC;?>
                    </td>
                </tr>
                <!--<tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'imagick'
                            ), false
                        );?>
                    </td>
                    <td>
                        <?php echo _IMAGICK;?>
                    </td>
                </tr>-->
                
                <?php if (DIRECTORY_SEPARATOR != '/') { ?>
                    <tr>
                        <td class="voyantPrerequisites">
                            <?php echo $Class_Install->checkPrerequisites(
                                $Class_Install->isPhpRequirements(
                                    'fileinfo'
                                )
                            );?>
                        </td>
                        <td>
                            <?php echo _FILEINFO;?>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&nbsp;
                    </td>
                    <td>&nbsp;

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h2>
                            PEAR
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPearRequirements(
                                'System.php'
                            )
                        );?>
                    </td>
                    <td>
                        <?php echo _PEAR;?>
                    </td>
                </tr>
                <!--tr>
                    <td class="voyantPrerequisites">
                        <?php 
                        // echo $Class_Install->checkPrerequisites(
                        //     $Class_Install->isPearRequirements(
                        //         'MIME/Type.php'
                        //     )
                        // );
                        ?>
                    </td>
                    <td>
                        <?php //echo _MIMETYPE;?>
                    </td>
                </tr-->
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPearRequirements(
                                'Maarch_CLITools/FileHandler.php'
                            ),
                            true
                        );?>
                    </td>
                    <td>
                        <?php echo _CLITOOLS;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPearRequirements(
                                'SOAP/Disco.php'
                            ),
                            true
                        );?>
                    </td>
                    <td>
                        <?php echo 'SOAP';?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;

                    </td>
                    <td>&nbsp;

                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h2>
                            php.ini
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isIniErrorRepportingRequirements()
                            , true);?>
                    </td>
                    <td>
                        <?php echo _ERROR_REPORTING;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isIniDisplayErrorRequirements()
                        );?>
                    </td>
                    <td>
                        <?php echo _DISPLAY_ERRORS;?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isIniShortOpenTagRequirements()
                        );?>
                    </td>
                    <td>
                        <?php echo _SHORT_OPEN_TAGS;?>
                    </td>
                </tr>
            </table>
        </p>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock" id="prerequisites">
        <p>
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=licence');">
                        <?php echo _PREVIOUS_INSTALL;?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <?php
                        if (!$canContinue) {
                            echo _MUST_FIX;
                        } else {
                            echo '<a href="#" onClick="goTo(\'index.php?step=database\');">'
                                ._NEXT_INSTALL
                            .'</a>';
                        }
                    ?>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>
