<?php
if (isset($_SESSION['folder'])
    && ($_SESSION['folder']['is_frozen'] == 'N' || $_SESSION['folder']['is_frozen'] == 'Y')
) {
    if ($_SESSION['folder']['is_frozen'] == 'N') {
        ?>
        <input type="hidden" name="is_frozen" id="is_frozen" value="Y" />
        <input type="submit" name="freezeFolder" id="freezeFolder" class="button" value="<?php
        echo _FREEZE_FOLDER;
        ?>" />
        <?php
    } else {
        ?>
        <input type="hidden" name="is_frozen" id="is_frozen" value="N" />
        <input type="submit" name="unfreezeFolder" id="unfreezeFolder" class="button" value="<?php
        echo _UNFREEZE_FOLDER;
        ?>" />
        <?php
    }
}