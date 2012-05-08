<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('database');" style="cursor: pointer;">
            <?php echo _DATABASE; ?>
        </h2>
    </div>
    <div class="contentBlock" id="database">
        <p>
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
