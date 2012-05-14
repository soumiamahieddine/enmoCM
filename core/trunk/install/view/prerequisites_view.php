<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('prerequisites');" style="cursor: pointer;">
            <?php echo _PREREQUISITES; ?>
            <a href="http://www.maarch.org/projets/entreprise/architecture-technique-et-prerequis-pour-maarch-entreprise-1.3">
				<?php echo _LINK;?>
			</a> 
        </h2>
        <h6>
			<i><?php echo _PREREQUISITES_EXP; ?></i>
			<br/>
			<img src="img/green_light.png" width="10px"/><?php echo _ACTIVATED; ?>
			<img src="img/orange_light.png"  width="10px"/><?php echo _OPTIONNAL; ?>
			<img src="img/red_light.png"  width="10px"/><?php echo _NOT_ACTIVATED; ?>
			
		</h6>
    </div>
    <div class="contentBlock" id="prerequisites">
        <p>
            <table>
            <tr>
                    <td colspan="2">
                        <h2>
                           <?php echo _GENERAL; ?>
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpVersion()
                        ); ?>
                    </td>
                    <td>
                        <?php echo _PHP_VERSION; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isMaarchPathWritable()
                        ); ?>
                    </td>
                    <td>
                        <?php echo _MAARCH_PATH_RIGHTS; ?>
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
                        ); ?>
                    </td>
                    <td>
                        <?php echo _PGSQL; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'gd'
                            )
                        ); ?>
                    </td>
                    <td>
                        <?php echo _GD; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPhpRequirements(
                                'svn'
                            ),
                            true
                        ); ?>
                    </td>
                    <td>
                        <?php echo _SVN; ?>
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
                        ); ?>
                    </td>
                    <td>
                        <?php echo _PEAR; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPearRequirements(
                                'MIME/Type.php'
                            )
                        ); ?>
                    </td>
                    <td>
                        <?php echo _MIMETYPE; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isPearRequirements(
                                'Maarch_CLITools/FileHandler.php'
                            ),
                            true
                        ); ?>
                    </td>
                    <td>
                        <?php echo _CLITOOLS; ?>
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
                        ); ?>
                    </td>
                    <td>
                        <?php echo _ERROR_REPORTING; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isIniDisplayErrorRequirements()
                        ); ?>
                    </td>
                    <td>
                        <?php echo _DISPLAY_ERRORS; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isIniShortOpenTagRequirements()
                        ); ?>
                    </td>
                    <td>
                        <?php echo _SHORT_OPEN_TAGS; ?>
                    </td>
                </tr>
                <tr>
                    <td class="voyantPrerequisites">
                        <?php echo $Class_Install->checkPrerequisites(
                            $Class_Install->isIniMagicQuotesGpcRequirements()
                        ); ?>
                    </td>
                    <td>
                        <?php echo _MAGIC_QUOTES_GPC; ?>
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
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <?php
                        if (!$canContinue) {
                            echo _MUST_FIX;
                        } else {
                            echo '<a href="#" onClick="goTo(\'index.php?step=database\');">'
                                ._NEXT
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
