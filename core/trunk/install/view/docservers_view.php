<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('docservers');" style="cursor: pointer;">
            <?php echo _DOCSERVERS; ?>
        </h2>
    </div>
    <div class="contentBlock" id="docservers">
        <p>
            <br />
            <br />
            <div id="buttons">
                <div style="float: left;" class="previousButton" id="previous">
                    <a href="#" onClick="goTo('index.php?step=database');">
                        <?php echo _PREVIOUS; ?>
                    </a>
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <a href="#" onClick="goTo('index.php?step=password');">
                        <?php echo _NEXT; ?>
                    </a>
                </div>
            </div>
            <br />
            <br />
        </p>
    </div>
    <br />
</div>
