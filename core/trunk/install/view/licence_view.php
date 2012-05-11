<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('licence');" style="cursor: pointer;">
            <?php echo _LICENCE; ?>
        </h2>
    </div>
    <div class="contentBlock" id="licence">
        <p>
            <div align="center">
                <div id="licenceOverflow" style="min-height: 131px; width: 880px; overflow: auto; background-color: rgba(255, 255, 255, 0.6);">
                    <br />
                    <?php echo $txtLicence; ?>
                    <br />
                </div>
            </div>
        </p>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock" id="licence">
        <p>
            <div align="center">
                <input type="checkbox" id="checkboxLicence" onChange="checkLicence();"/>
                <label for="checkboxLicence">
                    <?php echo _OK_WITH_LICENCE; ?>
                </label>
            </div>
        </p>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock" id="licence">
        <p>
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=welcome');">
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <span id="returnCheckLicence" style="display: none;">
                        <a href="#" onClick="goTo('index.php?step=prerequisites');">
                            <?php echo _NEXT; ?>
                        </a>
                    </span>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>
