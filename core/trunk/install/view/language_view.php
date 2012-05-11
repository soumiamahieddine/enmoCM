<div class="blockWrapper">
    <div class="titleBlock">
        <h2 onClick="slide('chooseLanguage');" style="cursor: pointer;">
            <?php echo _CHOOSE_LANGUAGE; ?>
        </h2>
    </div>
    <div class="contentBlock" id="chooseLanguage">
        <p>
            <form action="scripts/language.php" method="post">
                <select name="languageSelect" id="languageSelect" onChange="checkLanguage(this.value)">
                    <option value="default">Select a language</option>
                    <?php
                        for($i=0; $i<count($listLang);$i++) {
                            echo '<option ';
                              echo 'value="'.$listLang[$i].'"';
                            echo '>';
                                echo $listLang[$i];
                            echo '</option>';
                        }
                    ?>
                </select>
            </form>
        </p>
    </div>
</div>
<br />
<div class="blockWrapper">
    <div class="contentBlock" id="chooseLanguage">
        <p>
            <div id="buttons">
                <div style="float: left;" class="previousButton">
                </div>
                <div style="float: right;" class="nextButton" id="next">
                    <span id="returnCheckLanguage" style="display: none;">
                        <a href="#" onClick="$('form').submit();">
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
