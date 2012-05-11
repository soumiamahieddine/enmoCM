<div class="blockWrapper" style="background-color: rgba(255, 255, 255, 1);">
    <div class="contentBlock">
        <div id="progressWrapper">
            <?php
                echo $Class_Install->getProgress(
                    $stepNb,
                    $stepNbTotal
                );
            ?>
        </div>
    </div>
</div>
<br />
