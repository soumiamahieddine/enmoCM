<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('welcome');" style="cursor: pointer;">
            <?php echo _WELCOME; ?>
        </h2>
    </div>
    <div class="contentBlock" id="welcome">
        <p>
            <div align="center">
                <?php echo _WELCOME_DESC; ?>
            </div>
        </p>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock" id="welcome">
        <p>
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=language');">
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <a href="#" onClick="goTo('index.php?step=licence');">
                        <?php echo _NEXT; ?>
                    </a>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
</div>

